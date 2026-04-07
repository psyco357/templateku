<?php

namespace App\Http\Controllers;

use App\Models\AnggotaModel;
use App\Models\JenisSimpanan;
use App\Models\Koperasi;
use App\Models\PeriodeBuku;
use App\Models\Simpanan;
use App\Models\SimpananAudit;
use App\Models\User;
use App\Services\JournalPostingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SimpananController extends Controller
{
    public function __construct(protected JournalPostingService $journalPostingService) {}

    public function index(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $filters = [
            'anggota_id' => $request->string('anggota_id')->toString(),
            'jenis_simpanan_id' => $request->string('jenis_simpanan_id')->toString(),
            'tipe' => $request->string('tipe')->toString(),
            'status' => $request->string('status')->toString(),
        ];

        $anggotaOptions = $this->getAnggotaOptions($koperasi);
        $jenisSimpanan = $this->getJenisSimpananOptions($koperasi);

        $transaksi = Simpanan::query()
            ->with(['anggota.profile.user', 'jenisSimpanan', 'periodeBuku'])
            ->where('koperasi_id', $koperasi->id)
            ->when($filters['anggota_id'] !== '', function ($query) use ($filters) {
                $query->where('anggota_id', $filters['anggota_id']);
            })
            ->when($filters['jenis_simpanan_id'] !== '', function ($query) use ($filters) {
                $query->where('jenis_simpanan_id', $filters['jenis_simpanan_id']);
            })
            ->when($filters['tipe'] === 'setor', function ($query) {
                $query->where('jumlah', '>', 0);
            })
            ->when($filters['tipe'] === 'tarik', function ($query) {
                $query->where('jumlah', '<', 0);
            })
            ->when($filters['status'] !== '', function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->latest('tanggal_transaksi')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $saldoPerJenis = JenisSimpanan::query()
            ->leftJoin('simpanan', function ($join) use ($koperasi) {
                $join->on('simpanan.jenis_simpanan_id', '=', 'jenis_simpanan.id')
                    ->where('simpanan.koperasi_id', '=', $koperasi->id)
                    ->where('simpanan.status', '=', Simpanan::STATUS_POSTED);
            })
            ->where('jenis_simpanan.koperasi_id', $koperasi->id)
            ->groupBy('jenis_simpanan.id', 'jenis_simpanan.nama_jenis', 'jenis_simpanan.kode_jenis')
            ->orderBy('jenis_simpanan.nama_jenis')
            ->get([
                'jenis_simpanan.id',
                'jenis_simpanan.nama_jenis',
                'jenis_simpanan.kode_jenis',
                DB::raw('COALESCE(SUM(simpanan.jumlah), 0) as total_saldo'),
            ]);

        return view('pages.simpanan.index', [
            'koperasi' => $koperasi,
            'anggotaOptions' => $anggotaOptions,
            'jenisSimpanan' => $jenisSimpanan,
            'transaksi' => $transaksi,
            'saldoPerJenis' => $saldoPerJenis,
            'filters' => $filters,
            'transactionStatuses' => Simpanan::statuses(),
        ]);
    }

    public function masterJenis(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $editingJenis = null;

        if ($request->filled('edit')) {
            $editingJenis = JenisSimpanan::query()
                ->where('koperasi_id', $koperasi->id)
                ->findOrFail($request->integer('edit'));
        }

        $jenisSimpanan = JenisSimpanan::query()
            ->where('koperasi_id', $koperasi->id)
            ->withCount(['simpanan as transaksi_count'])
            ->withSum(['simpanan as total_saldo' => function ($query) {
                $query->where('status', Simpanan::STATUS_POSTED);
            }], 'jumlah')
            ->orderBy('nama_jenis')
            ->get();

        return view('pages.simpanan.jenis', [
            'koperasi' => $koperasi,
            'jenisSimpanan' => $jenisSimpanan,
            'editingJenis' => $editingJenis,
            'statusOptions' => JenisSimpanan::statuses(),
        ]);
    }

    public function storeJenis(Request $request): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $validated = $this->validateJenisSimpanan($request, $koperasi);

        $koperasi->jenisSimpanan()->create($validated);

        return redirect()->route('simpanan.jenis.index')->with([
            'status' => 'Jenis simpanan berhasil ditambahkan.',
            'status_type' => 'success',
        ]);
    }

    public function updateJenis(Request $request, JenisSimpanan $jenisSimpanan): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);
        $this->abortIfJenisSimpananNotOwned($jenisSimpanan, $koperasi);

        $validated = $this->validateJenisSimpanan($request, $koperasi, $jenisSimpanan);

        $jenisSimpanan->update($validated);

        return redirect()->route('simpanan.jenis.index')->with([
            'status' => 'Jenis simpanan berhasil diperbarui.',
            'status_type' => 'success',
        ]);
    }

    public function deactivateJenis(JenisSimpanan $jenisSimpanan): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);
        $this->abortIfJenisSimpananNotOwned($jenisSimpanan, $koperasi);

        if ($jenisSimpanan->status === JenisSimpanan::STATUS_NONAKTIF) {
            return redirect()->route('simpanan.jenis.index')->with([
                'status' => 'Jenis simpanan sudah berstatus nonaktif.',
                'status_type' => 'info',
            ]);
        }

        $jenisSimpanan->update(['status' => JenisSimpanan::STATUS_NONAKTIF]);

        return redirect()->route('simpanan.jenis.index')->with([
            'status' => 'Jenis simpanan berhasil dinonaktifkan.',
            'status_type' => 'success',
        ]);
    }

    public function destroyJenis(JenisSimpanan $jenisSimpanan): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);
        $this->abortIfJenisSimpananNotOwned($jenisSimpanan, $koperasi);

        if ($jenisSimpanan->simpanan()->exists()) {
            return redirect()->route('simpanan.jenis.index')->with([
                'status' => 'Jenis simpanan tidak bisa dihapus karena sudah dipakai transaksi. Nonaktifkan jenis ini jika sudah tidak dipakai lagi.',
                'status_type' => 'info',
            ]);
        }

        $jenisSimpanan->delete();

        return redirect()->route('simpanan.jenis.index')->with([
            'status' => 'Jenis simpanan berhasil dihapus.',
            'status_type' => 'success',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $validated = $this->validateTransaksiRequest($request, $koperasi);
        $anggota = $this->getOwnedAnggota($koperasi, (int) $validated['anggota_id']);
        $this->ensureSufficientBalance(
            $koperasi,
            $anggota->id,
            (int) $validated['jenis_simpanan_id'],
            $validated['tipe_transaksi'],
            (float) $validated['jumlah']
        );

        DB::transaction(function () use ($koperasi, $anggota, $validated, $request, &$simpanan) {
            $simpanan = Simpanan::query()->create([
                'koperasi_id' => $koperasi->id,
                'anggota_id' => $anggota->id,
                'jenis_simpanan_id' => $validated['jenis_simpanan_id'],
                'periode_buku_id' => $this->resolvePeriodeBukuId($koperasi, $validated['tanggal_transaksi']),
                'no_bukti' => $this->generateReceiptNumber($koperasi, $validated['tanggal_transaksi']),
                'jumlah' => $this->normalizeSignedAmount($validated['tipe_transaksi'], (float) $validated['jumlah']),
                'tanggal_transaksi' => $validated['tanggal_transaksi'],
                'status' => Simpanan::STATUS_POSTED,
                'keterangan' => $validated['keterangan'],
            ]);

            $this->recordAudit(
                $simpanan,
                SimpananAudit::ACTION_CREATE,
                'Transaksi simpanan dibuat.',
                [
                    'anggota_id' => $anggota->id,
                    'jenis_simpanan_id' => (int) $validated['jenis_simpanan_id'],
                    'tipe_transaksi' => $validated['tipe_transaksi'],
                    'jumlah' => $this->normalizeSignedAmount($validated['tipe_transaksi'], (float) $validated['jumlah']),
                    'tanggal_transaksi' => $validated['tanggal_transaksi'],
                ]
            );

            $simpanan->loadMissing(['anggota.profile', 'jenisSimpanan']);
            $this->journalPostingService->syncSimpanan($simpanan, $request->user()?->id);
        });

        return redirect()->route('simpanan.transaksi')->with([
            'status' => 'Transaksi simpanan berhasil disimpan.',
            'status_type' => 'success',
        ]);
    }

    public function show(Simpanan $simpanan): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);
        $this->abortIfSimpananNotOwned($simpanan, $koperasi);
        $simpanan->load(['anggota.profile.user', 'jenisSimpanan', 'periodeBuku', 'audits.user.profile']);

        return view('pages.simpanan.show', [
            'koperasi' => $koperasi,
            'simpanan' => $simpanan,
            'anggotaOptions' => $this->getAnggotaOptions($koperasi),
            'jenisSimpanan' => $this->getJenisSimpananOptions($koperasi),
        ]);
    }

    public function update(Request $request, Simpanan $simpanan): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);
        $this->abortIfSimpananNotOwned($simpanan, $koperasi);

        if ($simpanan->status === Simpanan::STATUS_BATAL) {
            return redirect()->route('simpanan.show', $simpanan)->with([
                'status' => 'Transaksi yang sudah dibatalkan tidak bisa diedit lagi.',
                'status_type' => 'info',
            ]);
        }

        $validated = $this->validateTransaksiRequest($request, $koperasi);
        $anggota = $this->getOwnedAnggota($koperasi, (int) $validated['anggota_id']);
        $this->ensureSufficientBalance(
            $koperasi,
            $anggota->id,
            (int) $validated['jenis_simpanan_id'],
            $validated['tipe_transaksi'],
            (float) $validated['jumlah'],
            $simpanan->id
        );

        $before = [
            'anggota_id' => $simpanan->anggota_id,
            'jenis_simpanan_id' => $simpanan->jenis_simpanan_id,
            'tanggal_transaksi' => optional($simpanan->tanggal_transaksi)->format('Y-m-d'),
            'jumlah' => (float) $simpanan->jumlah,
            'keterangan' => $simpanan->keterangan,
        ];

        DB::transaction(function () use ($simpanan, $anggota, $validated, $koperasi, $before, $request) {
            $simpanan->update([
                'anggota_id' => $anggota->id,
                'jenis_simpanan_id' => $validated['jenis_simpanan_id'],
                'periode_buku_id' => $this->resolvePeriodeBukuId($koperasi, $validated['tanggal_transaksi']),
                'no_bukti' => $simpanan->no_bukti ?: $this->generateReceiptNumber($koperasi, $validated['tanggal_transaksi']),
                'jumlah' => $this->normalizeSignedAmount($validated['tipe_transaksi'], (float) $validated['jumlah']),
                'tanggal_transaksi' => $validated['tanggal_transaksi'],
                'keterangan' => $validated['keterangan'],
            ]);

            $this->recordAudit(
                $simpanan,
                SimpananAudit::ACTION_UPDATE,
                'Transaksi simpanan diperbarui.',
                [
                    'before' => $before,
                    'after' => [
                        'anggota_id' => $simpanan->anggota_id,
                        'jenis_simpanan_id' => $simpanan->jenis_simpanan_id,
                        'tanggal_transaksi' => optional($simpanan->tanggal_transaksi)->format('Y-m-d'),
                        'jumlah' => (float) $simpanan->jumlah,
                        'keterangan' => $simpanan->keterangan,
                    ],
                ]
            );

            $simpanan->loadMissing(['anggota.profile', 'jenisSimpanan']);
            $this->journalPostingService->syncSimpanan($simpanan, $request->user()?->id);
        });

        return redirect()->route('simpanan.show', $simpanan)->with([
            'status' => 'Transaksi simpanan berhasil diperbarui.',
            'status_type' => 'success',
        ]);
    }

    public function cancel(Request $request, Simpanan $simpanan): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);
        $this->abortIfSimpananNotOwned($simpanan, $koperasi);

        if ($simpanan->status === Simpanan::STATUS_BATAL) {
            return redirect()->route('simpanan.show', $simpanan)->with([
                'status' => 'Transaksi ini sudah dibatalkan sebelumnya.',
                'status_type' => 'info',
            ]);
        }

        $validated = $request->validate([
            'alasan_batal' => ['nullable', 'string', 'max:500'],
        ], [], [
            'alasan_batal' => 'alasan pembatalan',
        ]);

        $catatanBatal = trim(($simpanan->keterangan ? $simpanan->keterangan . PHP_EOL : '') . 'Dibatalkan pada ' . now()->format('d-m-Y H:i') . ($validated['alasan_batal'] ? ' - ' . $validated['alasan_batal'] : ''));

        DB::transaction(function () use ($simpanan, $catatanBatal, $validated, $request) {
            $simpanan->update([
                'status' => Simpanan::STATUS_BATAL,
                'keterangan' => $catatanBatal,
            ]);

            $this->recordAudit(
                $simpanan,
                SimpananAudit::ACTION_CANCEL,
                'Transaksi simpanan dibatalkan.',
                [
                    'alasan_batal' => $validated['alasan_batal'] ?? null,
                    'status' => $simpanan->status,
                ]
            );

            $this->journalPostingService->syncSimpanan($simpanan, $request->user()?->id);
        });

        return redirect()->route('simpanan.transaksi')->with([
            'status' => 'Transaksi simpanan berhasil dibatalkan.',
            'status_type' => 'success',
        ]);
    }

    public function printReceipt(Simpanan $simpanan): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);
        $this->abortIfSimpananNotOwned($simpanan, $koperasi);

        $simpanan->load(['anggota.profile.user', 'jenisSimpanan', 'periodeBuku', 'audits.user.profile']);

        return view('pages.simpanan.receipt', [
            'koperasi' => $koperasi,
            'simpanan' => $simpanan,
            'printedAt' => now(),
        ]);
    }

    public function recap(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $recapData = $this->getRecapData($request, $koperasi);

        return view('pages.simpanan.rekap', $recapData + [
            'koperasi' => $koperasi,
        ]);
    }

    public function exportRecap(Request $request): Response
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $recapData = $this->getRecapData($request, $koperasi);
        $rows = $recapData['rekap'];

        $html = view('pages.simpanan.exports.rekap', [
            'koperasi' => $koperasi,
            'rows' => $rows,
            'filters' => $recapData['filters'],
            'summary' => $recapData['summary'],
        ])->render();

        return response($html, 200, $this->excelHeaders('rekap-saldo-simpanan-' . now()->format('Ymd-His') . '.xls'));
    }

    public function mutation(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $book = $this->buildMemberBookData($request, $koperasi);

        return view('pages.simpanan.mutasi', $book + [
            'koperasi' => $koperasi,
        ]);
    }

    public function exportMutation(Request $request): Response
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $book = $this->buildMemberBookData($request, $koperasi);
        $html = view('pages.simpanan.exports.mutasi', $book + [
            'koperasi' => $koperasi,
            'printedAt' => now(),
        ])->render();

        $filename = 'buku-simpanan-' . ($book['selectedAnggota']?->no_anggota ?? 'anggota') . '-' . now()->format('Ymd-His') . '.xls';

        return response($html, 200, $this->excelHeaders($filename));
    }

    public function printMutation(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $book = $this->buildMemberBookData($request, $koperasi);

        return view('pages.simpanan.print', $book + [
            'koperasi' => $koperasi,
            'printedAt' => now(),
        ]);
    }

    protected function getRecapData(Request $request, Koperasi $koperasi): array
    {
        $user = $request->user();
        $anggotaId = $user?->hasRole(User::ROLE_ANGGOTA) ? (string) ($user->anggota?->id ?? '') : $request->string('anggota_id')->toString();
        $jenisSimpananId = $request->string('jenis_simpanan_id')->toString();

        $rekap = DB::table('simpanan')
            ->join('anggota', 'simpanan.anggota_id', '=', 'anggota.id')
            ->join('profiles', 'anggota.profile_id', '=', 'profiles.id')
            ->join('users', 'profiles.user_id', '=', 'users.id')
            ->join('jenis_simpanan', 'simpanan.jenis_simpanan_id', '=', 'jenis_simpanan.id')
            ->where('simpanan.koperasi_id', $koperasi->id)
            ->where('simpanan.status', Simpanan::STATUS_POSTED)
            ->when($anggotaId !== '', function ($query) use ($anggotaId) {
                $query->where('simpanan.anggota_id', $anggotaId);
            })
            ->when($jenisSimpananId !== '', function ($query) use ($jenisSimpananId) {
                $query->where('simpanan.jenis_simpanan_id', $jenisSimpananId);
            })
            ->groupBy(
                'simpanan.anggota_id',
                'anggota.no_anggota',
                'profiles.nama_lengkap',
                'simpanan.jenis_simpanan_id',
                'jenis_simpanan.kode_jenis',
                'jenis_simpanan.nama_jenis'
            )
            ->orderBy('profiles.nama_lengkap')
            ->orderBy('jenis_simpanan.nama_jenis')
            ->get([
                'simpanan.anggota_id',
                'anggota.no_anggota',
                'profiles.nama_lengkap',
                'simpanan.jenis_simpanan_id',
                'jenis_simpanan.kode_jenis',
                'jenis_simpanan.nama_jenis',
                DB::raw('COALESCE(SUM(simpanan.jumlah), 0) as saldo'),
            ]);

        return [
            'rekap' => $rekap,
            'anggotaOptions' => $this->getAnggotaOptions($koperasi, $user?->hasRole(User::ROLE_ANGGOTA) ? $user->id : null),
            'jenisSimpanan' => $this->getJenisSimpananOptions($koperasi),
            'filters' => [
                'anggota_id' => $anggotaId,
                'jenis_simpanan_id' => $jenisSimpananId,
            ],
            'summary' => [
                'total_saldo' => $rekap->sum('saldo'),
                'total_anggota' => $rekap->pluck('anggota_id')->unique()->count(),
                'total_jenis' => $rekap->pluck('jenis_simpanan_id')->unique()->count(),
            ],
            'isAnggotaView' => $user?->hasRole(User::ROLE_ANGGOTA) ?? false,
        ];
    }

    protected function getActiveKoperasi(): ?Koperasi
    {
        return Auth::user()?->koperasi ?? Koperasi::query()->first();
    }

    protected function getAnggotaOptions(Koperasi $koperasi, ?int $userId = null): Collection
    {
        return AnggotaModel::query()
            ->with('profile.user')
            ->whereHas('profile.user', function ($query) use ($koperasi, $userId) {
                $query->where('koperasi_id', $koperasi->id);

                if ($userId !== null) {
                    $query->whereKey($userId);
                }
            })
            ->orderBy('no_anggota')
            ->get();
    }

    protected function getJenisSimpananOptions(Koperasi $koperasi): Collection
    {
        return JenisSimpanan::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('status', JenisSimpanan::STATUS_AKTIF)
            ->orderBy('nama_jenis')
            ->get();
    }

    protected function validateJenisSimpanan(Request $request, Koperasi $koperasi, ?JenisSimpanan $jenisSimpanan = null): array
    {
        return $request->validate([
            'kode_jenis' => [
                'required',
                'string',
                'max:50',
                Rule::unique('jenis_simpanan', 'kode_jenis')
                    ->where(fn($query) => $query->where('koperasi_id', $koperasi->id))
                    ->ignore($jenisSimpanan?->id),
            ],
            'nama_jenis' => ['required', 'string', 'max:255'],
            'bunga_persen' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nominal_default' => ['nullable', 'numeric', 'min:0'],
            'is_wajib' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(JenisSimpanan::statuses())],
            'keterangan' => ['nullable', 'string'],
        ], [], [
            'kode_jenis' => 'kode jenis',
            'nama_jenis' => 'nama jenis',
            'bunga_persen' => 'bunga',
            'nominal_default' => 'nominal default',
            'is_wajib' => 'jenis wajib',
            'status' => 'status',
            'keterangan' => 'keterangan',
        ]) + [
            'is_wajib' => $request->boolean('is_wajib'),
            'bunga_persen' => $request->filled('bunga_persen') ? $request->input('bunga_persen') : 0,
        ];
    }

    protected function validateTransaksiRequest(Request $request, Koperasi $koperasi): array
    {
        return $request->validate([
            'anggota_id' => ['required', Rule::exists('anggota', 'id')],
            'jenis_simpanan_id' => [
                'required',
                Rule::exists('jenis_simpanan', 'id')->where(function ($query) use ($koperasi) {
                    $query->where('koperasi_id', $koperasi->id);
                }),
            ],
            'tipe_transaksi' => ['required', Rule::in(['setor', 'tarik'])],
            'jumlah' => ['required', 'numeric', 'gt:0'],
            'tanggal_transaksi' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
        ], [
            'required' => ':attribute wajib diisi.',
            'numeric' => ':attribute harus berupa angka.',
            'gt' => ':attribute harus lebih besar dari nol.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'in' => ':attribute yang dipilih tidak valid.',
            'exists' => ':attribute yang dipilih tidak valid.',
            'string' => ':attribute harus berupa teks.',
        ], [
            'anggota_id' => 'anggota',
            'jenis_simpanan_id' => 'jenis simpanan',
            'tipe_transaksi' => 'tipe transaksi',
            'jumlah' => 'jumlah',
            'tanggal_transaksi' => 'tanggal transaksi',
            'keterangan' => 'keterangan',
        ]);
    }

    protected function getOwnedAnggota(Koperasi $koperasi, int $anggotaId): AnggotaModel
    {
        return AnggotaModel::query()
            ->whereKey($anggotaId)
            ->whereHas('profile.user', function (Builder $query) use ($koperasi) {
                $query->where('koperasi_id', $koperasi->id);
            })
            ->firstOrFail();
    }

    protected function ensureSufficientBalance(Koperasi $koperasi, int $anggotaId, int $jenisSimpananId, string $tipeTransaksi, float $jumlah, ?int $ignoreTransactionId = null): void
    {
        if ($tipeTransaksi !== 'tarik') {
            return;
        }

        $saldoSaatIni = (float) Simpanan::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('anggota_id', $anggotaId)
            ->where('jenis_simpanan_id', $jenisSimpananId)
            ->where('status', Simpanan::STATUS_POSTED)
            ->when($ignoreTransactionId !== null, function ($query) use ($ignoreTransactionId) {
                $query->whereKeyNot($ignoreTransactionId);
            })
            ->sum('jumlah');

        if ($saldoSaatIni < $jumlah) {
            throw ValidationException::withMessages([
                'jumlah' => 'Saldo simpanan tidak mencukupi untuk penarikan ini.',
            ]);
        }
    }

    protected function resolvePeriodeBukuId(Koperasi $koperasi, string $tanggalTransaksi): ?int
    {
        return PeriodeBuku::query()
            ->where('koperasi_id', $koperasi->id)
            ->whereDate('tanggal_mulai', '<=', $tanggalTransaksi)
            ->whereDate('tanggal_selesai', '>=', $tanggalTransaksi)
            ->value('id');
    }

    protected function normalizeSignedAmount(string $tipeTransaksi, float $jumlah): float
    {
        return $tipeTransaksi === 'tarik' ? -1 * $jumlah : $jumlah;
    }

    protected function generateReceiptNumber(Koperasi $koperasi, string $tanggalTransaksi): string
    {
        $datePart = Carbon::parse($tanggalTransaksi)->format('Ymd');
        $prefix = sprintf('SMP-%d-%s-', $koperasi->id, $datePart);

        $lastReceipt = Simpanan::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('no_bukti', 'like', $prefix . '%')
            ->orderByDesc('no_bukti')
            ->value('no_bukti');

        $nextSequence = 1;

        if ($lastReceipt) {
            $lastSequence = (int) Str::afterLast($lastReceipt, '-');
            $nextSequence = $lastSequence + 1;
        }

        return $prefix . str_pad((string) $nextSequence, 5, '0', STR_PAD_LEFT);
    }

    protected function abortIfJenisSimpananNotOwned(JenisSimpanan $jenisSimpanan, Koperasi $koperasi): void
    {
        abort_unless($jenisSimpanan->koperasi_id === $koperasi->id, 404);
    }

    protected function abortIfSimpananNotOwned(Simpanan $simpanan, Koperasi $koperasi): void
    {
        abort_unless($simpanan->koperasi_id === $koperasi->id, 404);
    }

    protected function buildMemberBookData(Request $request, Koperasi $koperasi): array
    {
        $user = $request->user();
        $isAnggotaView = $user?->hasRole(User::ROLE_ANGGOTA) ?? false;
        $anggotaId = $isAnggotaView ? (string) ($user->anggota?->id ?? '') : $request->string('anggota_id')->toString();
        $jenisSimpananId = $request->string('jenis_simpanan_id')->toString();
        $startDate = $request->string('start_date')->toString();
        $endDate = $request->string('end_date')->toString();

        $selectedAnggota = null;
        $transactions = collect();
        $openingBalance = 0.0;
        $runningBalance = 0.0;

        if ($anggotaId !== '') {
            $selectedAnggota = $this->getOwnedAnggota($koperasi, (int) $anggotaId)->load('profile.user');

            $baseQuery = Simpanan::query()
                ->with(['jenisSimpanan', 'periodeBuku'])
                ->where('koperasi_id', $koperasi->id)
                ->where('anggota_id', $selectedAnggota->id)
                ->where('status', Simpanan::STATUS_POSTED)
                ->when($jenisSimpananId !== '', function ($query) use ($jenisSimpananId) {
                    $query->where('jenis_simpanan_id', $jenisSimpananId);
                });

            if ($startDate !== '') {
                $openingBalance = (float) (clone $baseQuery)
                    ->whereDate('tanggal_transaksi', '<', $startDate)
                    ->sum('jumlah');
            }

            $transactions = (clone $baseQuery)
                ->when($startDate !== '', function ($query) use ($startDate) {
                    $query->whereDate('tanggal_transaksi', '>=', $startDate);
                })
                ->when($endDate !== '', function ($query) use ($endDate) {
                    $query->whereDate('tanggal_transaksi', '<=', $endDate);
                })
                ->orderBy('tanggal_transaksi')
                ->orderBy('id')
                ->get();

            $runningBalance = $openingBalance;

            $transactions = $transactions->map(function (Simpanan $item) use (&$runningBalance) {
                $runningBalance += (float) $item->jumlah;
                $item->running_balance = $runningBalance;

                return $item;
            });
        }

        return [
            'selectedAnggota' => $selectedAnggota,
            'anggotaOptions' => $this->getAnggotaOptions($koperasi, $isAnggotaView ? $user?->id : null),
            'jenisSimpanan' => $this->getJenisSimpananOptions($koperasi),
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
            'closingBalance' => $runningBalance,
            'filters' => [
                'anggota_id' => $anggotaId,
                'jenis_simpanan_id' => $jenisSimpananId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'isAnggotaView' => $isAnggotaView,
        ];
    }

    protected function recordAudit(Simpanan $simpanan, string $action, string $description, array $metadata = []): void
    {
        $simpanan->audits()->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    protected function excelHeaders(string $filename): array
    {
        return [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'public',
            'Cache-Control' => 'max-age=0',
        ];
    }
}
