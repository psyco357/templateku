<?php

namespace App\Http\Controllers;

use App\Models\Angsuran;
use App\Models\AnggotaModel;
use App\Models\Koperasi;
use App\Models\PeriodeBuku;
use App\Models\Pinjaman;
use App\Models\PinjamanStatusLog;
use App\Models\Simpanan;
use App\Models\User;
use App\Services\JournalPostingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PinjamanController extends Controller
{
    public function __construct(protected JournalPostingService $journalPostingService) {}

    public function pengajuan(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $user = $request->user();
        $isAnggotaView = $user?->hasRole(User::ROLE_ANGGOTA) ?? false;
        $anggotaOptions = $this->getAnggotaOptions($koperasi, $isAnggotaView ? $user?->id : null);
        $snapshots = $this->getSavingsSnapshots($koperasi, $anggotaOptions);
        $selectedAnggotaId = $isAnggotaView
            ? (string) ($user?->resolveAnggotaId() ?? '')
            : $request->string('anggota_id')->toString();

        $pinjaman = $this->getAccessiblePinjamanQuery($koperasi, $user)
            ->when($selectedAnggotaId !== '', function (Builder $query) use ($selectedAnggotaId) {
                $query->where('anggota_id', $selectedAnggotaId);
            })
            ->latest('tanggal_pinjaman')
            ->latest('id')
            ->take(12)
            ->get()
            ->map(fn(Pinjaman $item) => $this->decoratePinjamanSummary($item));

        return view('pages.pinjaman.pengajuan', [
            'koperasi' => $koperasi,
            'anggotaOptions' => $anggotaOptions,
            'selectedAnggotaOption' => $this->resolveSelectedAnggotaOption($koperasi, (int) old('anggota_id', (int) $selectedAnggotaId)),
            'anggotaSnapshots' => $snapshots,
            'pinjaman' => $pinjaman,
            'selectedAnggotaId' => $selectedAnggotaId,
            'isAnggotaView' => $isAnggotaView,
            'canReviewPinjaman' => $user?->hasRole([User::ROLE_PENGURUS, User::ROLE_FOUNDER]) ?? false,
            'monthlyInterest' => Pinjaman::DEFAULT_MONTHLY_INTEREST,
            'paymentDay' => Pinjaman::DEFAULT_PAYMENT_DAY,
            'maxSavingsRatio' => Pinjaman::MAX_SAVINGS_RATIO,
        ]);
    }

    public function searchAnggota(Request $request): JsonResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $term = trim($request->string('q')->toString());
        $user = $request->user();
        $userId = $user?->hasRole(User::ROLE_ANGGOTA) ? $user?->id : null;

        if (mb_strlen($term) < 3) {
            return response()->json([
                'data' => [],
            ]);
        }

        $anggota = $this->searchAnggotaOptions($koperasi, $term, $userId, 4)
            ->map(fn(AnggotaModel $item) => [
                'id' => $item->id,
                'label' => sprintf('%s - %s', $item->no_anggota, $item->profile?->nama_lengkap ?? 'Tanpa nama'),
            ])
            ->values();

        return response()->json([
            'data' => $anggota,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $validated = $this->validatePinjamanRequest($request, $koperasi);
        $anggota = $this->resolveTargetAnggota($request, $koperasi, (int) $validated['anggota_id']);
        $saldoTabungan = $this->getSavingsBalance($koperasi, $anggota->id);
        $maksimalPinjaman = round($saldoTabungan * Pinjaman::MAX_SAVINGS_RATIO, 2);

        if ((float) $validated['jumlah_pinjaman'] > $maksimalPinjaman) {
            throw ValidationException::withMessages([
                'jumlah_pinjaman' => 'Nominal pinjaman melebihi batas 90% dari saldo tabungan anggota. Maksimal pinjaman saat ini Rp ' . number_format($maksimalPinjaman, 0, ',', '.') . '.',
            ]);
        }

        $terms = $this->buildLoanTerms(
            (float) $validated['jumlah_pinjaman'],
            (int) $validated['tenor_bulan'],
            $validated['tanggal_pinjaman']
        );

        $pinjaman = Pinjaman::query()->create([
            'koperasi_id' => $koperasi->id,
            'anggota_id' => $anggota->id,
            'periode_buku_id' => $this->resolvePeriodeBukuId($koperasi, $validated['tanggal_pinjaman']),
            'no_pinjaman' => $this->generateLoanNumber($koperasi, $validated['tanggal_pinjaman']),
            'jumlah_pinjaman' => $validated['jumlah_pinjaman'],
            'bunga_persen' => 0,
            'bunga_nominal_bulanan' => Pinjaman::DEFAULT_MONTHLY_INTEREST,
            'tanggal_tagihan_bulanan' => Pinjaman::DEFAULT_PAYMENT_DAY,
            'tenor_bulan' => $validated['tenor_bulan'],
            'tanggal_pengajuan' => now()->toDateString(),
            'tanggal_pinjaman' => $validated['tanggal_pinjaman'],
            'tanggal_jatuh_tempo' => $terms['final_due_date']->toDateString(),
            'status' => Pinjaman::STATUS_DIAJUKAN,
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        $this->logStatusChange(
            $pinjaman,
            null,
            Pinjaman::STATUS_DIAJUKAN,
            $request->user(),
            'Pengajuan pinjaman dibuat.'
        );

        return redirect()->route('pinjaman.pengajuan')->with([
            'status' => 'Pengajuan pinjaman berhasil disimpan dan menunggu verifikasi. Jika disetujui, angsuran pertama akan jatuh tempo pada ' . $terms['first_due_date']->translatedFormat('d M Y') . '.',
            'status_type' => 'success',
        ]);
    }

    public function approve(Request $request, Pinjaman $pinjaman): RedirectResponse
    {
        $allowedPinjaman = $this->resolveReviewablePinjaman($request, $pinjaman);
        $reviewer = $request->user();

        if ($allowedPinjaman->status !== Pinjaman::STATUS_DIAJUKAN) {
            throw ValidationException::withMessages([
                'pinjaman' => 'Hanya pengajuan dengan status diajukan yang dapat disetujui.',
            ]);
        }

        DB::transaction(function () use ($allowedPinjaman, $reviewer) {
            $allowedPinjaman->forceFill([
                'status' => Pinjaman::STATUS_AKTIF,
                'alasan_penolakan' => null,
                'disetujui_oleh' => $reviewer?->id,
                'disetujui_pada' => now(),
                'ditolak_oleh' => null,
                'ditolak_pada' => null,
            ])->save();

            $this->logStatusChange(
                $allowedPinjaman,
                Pinjaman::STATUS_DIAJUKAN,
                Pinjaman::STATUS_AKTIF,
                $reviewer,
                'Pengajuan pinjaman disetujui.'
            );

            $allowedPinjaman->loadMissing('anggota.profile');
            $this->journalPostingService->syncPinjamanPencairan($allowedPinjaman, $reviewer?->id);
        });

        return redirect()->route('pinjaman.pengajuan')->with([
            'status' => 'Pengajuan pinjaman berhasil disetujui dan sekarang berstatus aktif.',
            'status_type' => 'success',
        ]);
    }

    public function reject(Request $request, Pinjaman $pinjaman): RedirectResponse
    {
        $allowedPinjaman = $this->resolveReviewablePinjaman($request, $pinjaman);
        $reviewer = $request->user();

        if ($allowedPinjaman->status !== Pinjaman::STATUS_DIAJUKAN) {
            throw ValidationException::withMessages([
                'pinjaman' => 'Hanya pengajuan dengan status diajukan yang dapat ditolak.',
            ]);
        }

        $validated = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'max:1000'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max.string' => ':attribute tidak boleh lebih dari :max karakter.',
        ], [
            'alasan_penolakan' => 'alasan penolakan',
        ]);

        $allowedPinjaman->forceFill([
            'status' => Pinjaman::STATUS_DITOLAK,
            'alasan_penolakan' => $validated['alasan_penolakan'],
            'ditolak_oleh' => $reviewer?->id,
            'ditolak_pada' => now(),
            'disetujui_oleh' => null,
            'disetujui_pada' => null,
        ])->save();

        $this->logStatusChange(
            $allowedPinjaman,
            Pinjaman::STATUS_DIAJUKAN,
            Pinjaman::STATUS_DITOLAK,
            $reviewer,
            'Pengajuan pinjaman ditolak. Alasan: ' . $validated['alasan_penolakan']
        );

        return redirect()->route('pinjaman.pengajuan')->with([
            'status' => 'Pengajuan pinjaman ditolak.',
            'status_type' => 'warning',
        ]);
    }

    public function simulasi(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $user = $request->user();
        $isAnggotaView = $user?->hasRole(User::ROLE_ANGGOTA) ?? false;
        $anggotaOptions = $this->getAnggotaOptions($koperasi, $isAnggotaView ? $user?->id : null);
        $snapshots = $this->getSavingsSnapshots($koperasi, $anggotaOptions);

        $filters = [
            'anggota_id' => $isAnggotaView ? (string) ($user?->resolveAnggotaId() ?? '') : $request->string('anggota_id')->toString(),
            'jumlah_pinjaman' => $request->string('jumlah_pinjaman')->toString(),
            'tenor_bulan' => $request->string('tenor_bulan')->toString(),
            'tanggal_pinjaman' => $request->string('tanggal_pinjaman')->toString() ?: now()->toDateString(),
        ];

        $simulation = null;
        $selectedSnapshot = null;

        if ($filters['anggota_id'] !== '' && isset($snapshots[(int) $filters['anggota_id']])) {
            $selectedSnapshot = $snapshots[(int) $filters['anggota_id']];
        }

        if ($filters['jumlah_pinjaman'] !== '' && $filters['tenor_bulan'] !== '' && $filters['tanggal_pinjaman'] !== '') {
            $simulation = $this->buildLoanTerms(
                (float) $filters['jumlah_pinjaman'],
                max(1, (int) $filters['tenor_bulan']),
                $filters['tanggal_pinjaman']
            );
        }

        return view('pages.pinjaman.simulasi', [
            'koperasi' => $koperasi,
            'anggotaOptions' => $anggotaOptions,
            'anggotaSnapshots' => $snapshots,
            'selectedSnapshot' => $selectedSnapshot,
            'simulation' => $simulation,
            'filters' => $filters,
            'isAnggotaView' => $isAnggotaView,
            'monthlyInterest' => Pinjaman::DEFAULT_MONTHLY_INTEREST,
            'paymentDay' => Pinjaman::DEFAULT_PAYMENT_DAY,
            'maxSavingsRatio' => Pinjaman::MAX_SAVINGS_RATIO,
        ]);
    }

    public function jadwalAngsuran(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $user = $request->user();
        $pinjamanOptions = $this->getAccessiblePinjamanQuery($koperasi, $user)
            ->latest('tanggal_pinjaman')
            ->latest('id')
            ->get();

        $selectedPinjaman = null;
        $schedule = collect();
        $selectedId = $request->integer('pinjaman_id');

        if ($selectedId) {
            $selectedPinjaman = $pinjamanOptions->firstWhere('id', $selectedId);
        }

        if (! $selectedPinjaman && $pinjamanOptions->isNotEmpty()) {
            $selectedPinjaman = $pinjamanOptions->first();
            $selectedId = $selectedPinjaman->id;
        }

        if ($selectedPinjaman) {
            $schedule = $this->buildLoanTerms(
                (float) $selectedPinjaman->jumlah_pinjaman,
                (int) $selectedPinjaman->tenor_bulan,
                $selectedPinjaman->tanggal_pinjaman,
                (float) ($selectedPinjaman->bunga_nominal_bulanan ?? Pinjaman::DEFAULT_MONTHLY_INTEREST),
                (int) ($selectedPinjaman->tanggal_tagihan_bulanan ?? Pinjaman::DEFAULT_PAYMENT_DAY)
            )['schedule'];
            $selectedPinjaman = $this->decoratePinjamanSummary($selectedPinjaman);
        }

        return view('pages.pinjaman.jadwal-angsuran', [
            'koperasi' => $koperasi,
            'pinjamanOptions' => $pinjamanOptions,
            'selectedPinjaman' => $selectedPinjaman,
            'selectedId' => $selectedId,
            'schedule' => $schedule,
        ]);
    }

    public function statusPembayaran(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $user = $request->user();
        $isAnggotaView = $user?->hasRole(User::ROLE_ANGGOTA) ?? false;
        $anggotaOptions = $this->getAnggotaOptions($koperasi, $isAnggotaView ? $user?->id : null);
        $filters = [
            'anggota_id' => $isAnggotaView ? (string) ($user?->resolveAnggotaId() ?? '') : $request->string('anggota_id')->toString(),
            'status' => $request->string('status')->toString(),
        ];

        $pinjaman = $this->getAccessiblePinjamanQuery($koperasi, $user)
            ->when($filters['anggota_id'] !== '', function (Builder $query) use ($filters) {
                $query->where('anggota_id', $filters['anggota_id']);
            })
            ->when($filters['status'] !== '', function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->latest('tanggal_pinjaman')
            ->latest('id')
            ->get()
            ->map(fn(Pinjaman $item) => $this->decoratePinjamanSummary($item));

        return view('pages.pinjaman.status-pembayaran', [
            'koperasi' => $koperasi,
            'anggotaOptions' => $anggotaOptions,
            'pinjaman' => $pinjaman,
            'filters' => $filters,
            'statusOptions' => Pinjaman::statuses(),
            'isAnggotaView' => $isAnggotaView,
            'summary' => [
                'total_pinjaman' => $pinjaman->count(),
                'total_berjalan' => $pinjaman->where('status', Pinjaman::STATUS_AKTIF)->count(),
                'total_tagihan' => $pinjaman->sum('total_tagihan'),
                'total_sisa' => $pinjaman->sum('sisa_tagihan'),
            ],
        ]);
    }

    public function bayarAngsuran(Request $request, Pinjaman $pinjaman): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $allowedPinjaman = $this->getAccessiblePinjamanQuery($koperasi, $request->user())
            ->whereKey($pinjaman->id)
            ->first();

        abort_unless($allowedPinjaman, 404);

        if ($allowedPinjaman->status !== Pinjaman::STATUS_AKTIF) {
            throw ValidationException::withMessages([
                'pinjaman' => 'Pembayaran hanya dapat dilakukan untuk pinjaman aktif.',
            ]);
        }

        $validated = $request->validate([
            'tanggal_bayar' => ['required', 'date', 'after_or_equal:' . $allowedPinjaman->tanggal_pinjaman->toDateString()],
        ], [
            'required' => ':attribute wajib diisi.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'after_or_equal' => ':attribute tidak boleh sebelum tanggal pinjaman.',
        ], [
            'tanggal_bayar' => 'tanggal bayar',
        ]);

        $allowedPinjaman = $this->decoratePinjamanSummary($allowedPinjaman);
        $terms = $this->buildLoanTerms(
            (float) $allowedPinjaman->jumlah_pinjaman,
            (int) $allowedPinjaman->tenor_bulan,
            $allowedPinjaman->tanggal_pinjaman,
            (float) ($allowedPinjaman->bunga_nominal_bulanan ?? Pinjaman::DEFAULT_MONTHLY_INTEREST),
            (int) ($allowedPinjaman->tanggal_tagihan_bulanan ?? Pinjaman::DEFAULT_PAYMENT_DAY)
        );

        $nextInstallment = $terms['schedule']->get($allowedPinjaman->angsuran_terbayar);

        if (! $nextInstallment) {
            throw ValidationException::withMessages([
                'pinjaman' => 'Pinjaman ini sudah lunas dan tidak memiliki angsuran tersisa.',
            ]);
        }

        DB::transaction(function () use ($koperasi, $allowedPinjaman, $validated, $nextInstallment, $request) {
            $angsuran = Angsuran::query()->updateOrCreate(
                [
                    'pinjaman_id' => $allowedPinjaman->id,
                    'angsuran_ke' => $nextInstallment['angsuran_ke'],
                ],
                [
                    'koperasi_id' => $koperasi->id,
                    'periode_buku_id' => $this->resolvePeriodeBukuId($koperasi, $validated['tanggal_bayar']),
                    'jumlah_bayar' => $nextInstallment['jumlah_tagihan'],
                    'pokok' => $nextInstallment['pokok'],
                    'bunga' => $nextInstallment['bunga'],
                    'denda' => 0,
                    'tanggal_bayar' => $validated['tanggal_bayar'],
                    'status' => Angsuran::STATUS_DIBAYAR,
                ]
            );

            $hasRemainingInstallment = $allowedPinjaman->tenor_bulan > $nextInstallment['angsuran_ke'];
            $newStatus = $hasRemainingInstallment ? Pinjaman::STATUS_AKTIF : Pinjaman::STATUS_LUNAS;

            $allowedPinjaman->forceFill([
                'status' => $newStatus,
            ])->save();

            if ($newStatus !== Pinjaman::STATUS_AKTIF) {
                $this->logStatusChange(
                    $allowedPinjaman,
                    Pinjaman::STATUS_AKTIF,
                    $newStatus,
                    $request->user(),
                    'Pinjaman dinyatakan lunas setelah pembayaran angsuran ke-' . $nextInstallment['angsuran_ke'] . '.'
                );
            }

            $allowedPinjaman->loadMissing('anggota.profile');
            $this->journalPostingService->syncAngsuran($allowedPinjaman, $angsuran, $request->user()?->id);
        });

        $paymentDate = Carbon::parse($validated['tanggal_bayar']);
        $dueDate = $nextInstallment['tanggal_jatuh_tempo'];
        $message = 'Angsuran ke-' . $nextInstallment['angsuran_ke'] . ' berhasil dibayar pada ' . $paymentDate->translatedFormat('d M Y') . '.';

        if ($paymentDate->lt($dueDate)) {
            $message .= ' Pembayaran diproses lebih awal sebelum jatuh tempo ' . $dueDate->translatedFormat('d M Y') . '.';
        }

        return redirect()->route('pinjaman.status-pembayaran')->with([
            'status' => $message,
            'status_type' => 'success',
        ]);
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

    protected function searchAnggotaOptions(Koperasi $koperasi, string $term, ?int $userId = null, int $limit = 4): Collection
    {
        return AnggotaModel::query()
            ->with('profile.user')
            ->whereHas('profile.user', function ($query) use ($koperasi, $userId) {
                $query->where('koperasi_id', $koperasi->id);

                if ($userId !== null) {
                    $query->whereKey($userId);
                }
            })
            ->where(function (Builder $query) use ($term) {
                $query->where('no_anggota', 'like', "%{$term}%")
                    ->orWhereHas('profile', function (Builder $profileQuery) use ($term) {
                        $profileQuery->where('nama_lengkap', 'like', "%{$term}%");
                    });
            })
            ->orderBy('no_anggota')
            ->limit($limit)
            ->get();
    }

    protected function resolveSelectedAnggotaOption(Koperasi $koperasi, int $anggotaId): ?AnggotaModel
    {
        if ($anggotaId <= 0) {
            return null;
        }

        return AnggotaModel::query()
            ->with('profile.user')
            ->whereKey($anggotaId)
            ->whereHas('profile.user', fn(Builder $query) => $query->where('koperasi_id', $koperasi->id))
            ->first();
    }

    protected function getOwnedAnggota(Koperasi $koperasi, int $anggotaId): AnggotaModel
    {
        return AnggotaModel::query()
            ->with('profile.user')
            ->whereKey($anggotaId)
            ->whereHas('profile.user', function (Builder $query) use ($koperasi) {
                $query->where('koperasi_id', $koperasi->id);
            })
            ->firstOrFail();
    }

    protected function resolveTargetAnggota(Request $request, Koperasi $koperasi, int $anggotaId): AnggotaModel
    {
        $user = $request->user();

        if ($user?->hasRole(User::ROLE_ANGGOTA)) {
            $anggotaId = (int) ($user->resolveAnggotaId() ?? 0);

            if ($anggotaId === 0) {
                throw ValidationException::withMessages([
                    'anggota_id' => 'Akun Anda belum terhubung ke data anggota koperasi.',
                ]);
            }
        }

        return $this->getOwnedAnggota($koperasi, $anggotaId);
    }

    protected function getAccessiblePinjamanQuery(Koperasi $koperasi, ?User $user): Builder
    {
        return Pinjaman::query()
            ->with(['anggota.profile.user', 'periodeBuku', 'angsuran', 'approver.profile', 'rejector.profile'])
            ->where('koperasi_id', $koperasi->id)
            ->when($user?->hasRole(User::ROLE_ANGGOTA), function (Builder $query) use ($user) {
                $query->whereHas('anggota.profile.user', function (Builder $memberQuery) use ($user) {
                    $memberQuery->whereKey($user?->id);
                });
            });
    }

    protected function validatePinjamanRequest(Request $request, Koperasi $koperasi): array
    {
        $user = $request->user();

        return $request->validate([
            'anggota_id' => [
                Rule::requiredIf(! ($user?->hasRole(User::ROLE_ANGGOTA) ?? false)),
                Rule::exists('anggota', 'id'),
            ],
            'jumlah_pinjaman' => ['required', 'numeric', 'gt:0'],
            'tenor_bulan' => ['required', 'integer', 'between:1,60'],
            'tanggal_pinjaman' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
        ], [
            'required' => ':attribute wajib diisi.',
            'numeric' => ':attribute harus berupa angka.',
            'gt' => ':attribute harus lebih besar dari nol.',
            'integer' => ':attribute harus berupa angka bulat.',
            'between' => ':attribute di luar rentang yang diizinkan.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'exists' => ':attribute yang dipilih tidak valid.',
            'string' => ':attribute harus berupa teks.',
        ], [
            'anggota_id' => 'anggota',
            'jumlah_pinjaman' => 'jumlah pinjaman',
            'tenor_bulan' => 'tenor',
            'tanggal_pinjaman' => 'tanggal pinjaman',
            'keterangan' => 'keterangan',
        ]);
    }

    protected function resolveReviewablePinjaman(Request $request, Pinjaman $pinjaman): Pinjaman
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $allowedPinjaman = $this->getAccessiblePinjamanQuery($koperasi, $request->user())
            ->whereKey($pinjaman->id)
            ->first();

        abort_unless($allowedPinjaman, 404);

        return $allowedPinjaman;
    }

    protected function logStatusChange(Pinjaman $pinjaman, ?string $previousStatus, string $newStatus, ?User $actor, ?string $note = null): void
    {
        PinjamanStatusLog::query()->create([
            'pinjaman_id' => $pinjaman->id,
            'koperasi_id' => $pinjaman->koperasi_id,
            'status_sebelumnya' => $previousStatus,
            'status_baru' => $newStatus,
            'diproses_oleh' => $actor?->id,
            'catatan' => $note,
        ]);
    }

    protected function getSavingsSnapshots(Koperasi $koperasi, Collection $anggotaOptions): array
    {
        if ($anggotaOptions->isEmpty()) {
            return [];
        }

        $balances = Simpanan::query()
            ->selectRaw('anggota_id, COALESCE(SUM(jumlah), 0) as saldo')
            ->where('koperasi_id', $koperasi->id)
            ->where('status', Simpanan::STATUS_POSTED)
            ->whereIn('anggota_id', $anggotaOptions->pluck('id'))
            ->groupBy('anggota_id')
            ->pluck('saldo', 'anggota_id');

        return $anggotaOptions->mapWithKeys(function (AnggotaModel $anggota) use ($balances) {
            $saldo = max(0, (float) ($balances[$anggota->id] ?? 0));

            return [
                $anggota->id => [
                    'anggota_id' => $anggota->id,
                    'label' => trim(($anggota->no_anggota ?? '-') . ' - ' . ($anggota->profile?->nama_lengkap ?? 'Tanpa nama')),
                    'saldo_tabungan' => $saldo,
                    'maksimal_pinjaman' => round($saldo * Pinjaman::MAX_SAVINGS_RATIO, 2),
                ],
            ];
        })->all();
    }

    protected function getSavingsBalance(Koperasi $koperasi, int $anggotaId): float
    {
        return max(0, (float) Simpanan::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('anggota_id', $anggotaId)
            ->where('status', Simpanan::STATUS_POSTED)
            ->sum('jumlah'));
    }

    protected function buildLoanTerms(float $amount, int $tenor, Carbon|string $loanDate, ?float $monthlyInterest = null, ?int $paymentDay = null): array
    {
        $loanDate = $loanDate instanceof Carbon ? $loanDate->copy()->startOfDay() : Carbon::parse($loanDate)->startOfDay();
        $monthlyInterest = $monthlyInterest ?? Pinjaman::DEFAULT_MONTHLY_INTEREST;
        $paymentDay = $paymentDay ?? Pinjaman::DEFAULT_PAYMENT_DAY;
        $firstDueDate = $this->calculateFirstDueDate($loanDate, $paymentDay);
        $finalDueDate = $firstDueDate->copy()->addMonthsNoOverflow(max($tenor - 1, 0));
        $basePrincipal = round($amount / max($tenor, 1), 2);
        $remainingPrincipal = round($amount, 2);

        $schedule = collect();

        for ($installment = 1; $installment <= $tenor; $installment++) {
            $principal = $installment === $tenor ? round($remainingPrincipal, 2) : $basePrincipal;
            $remainingPrincipal = round($remainingPrincipal - $principal, 2);

            $schedule->push([
                'angsuran_ke' => $installment,
                'tanggal_jatuh_tempo' => $firstDueDate->copy()->addMonthsNoOverflow($installment - 1),
                'pokok' => $principal,
                'bunga' => $monthlyInterest,
                'jumlah_tagihan' => round($principal + $monthlyInterest, 2),
                'sisa_pokok' => max($remainingPrincipal, 0),
            ]);
        }

        return [
            'loan_date' => $loanDate,
            'first_due_date' => $firstDueDate,
            'final_due_date' => $finalDueDate,
            'monthly_interest' => $monthlyInterest,
            'total_interest' => round($monthlyInterest * $tenor, 2),
            'total_payment' => round($amount + ($monthlyInterest * $tenor), 2),
            'schedule' => $schedule,
        ];
    }

    protected function calculateFirstDueDate(Carbon $loanDate, int $paymentDay): Carbon
    {
        $firstDueDate = $loanDate->copy()->addMonthNoOverflow()->startOfMonth()->day($paymentDay);

        if ($loanDate->day > $paymentDay) {
            $firstDueDate->addMonthNoOverflow()->startOfMonth()->day($paymentDay);
        }

        return $firstDueDate;
    }

    protected function decoratePinjamanSummary(Pinjaman $pinjaman): Pinjaman
    {
        $terms = $this->buildLoanTerms(
            (float) $pinjaman->jumlah_pinjaman,
            (int) $pinjaman->tenor_bulan,
            $pinjaman->tanggal_pinjaman,
            (float) ($pinjaman->bunga_nominal_bulanan ?? Pinjaman::DEFAULT_MONTHLY_INTEREST),
            (int) ($pinjaman->tanggal_tagihan_bulanan ?? Pinjaman::DEFAULT_PAYMENT_DAY)
        );

        $paidInstallments = $pinjaman->angsuran->where('status', Angsuran::STATUS_DIBAYAR);
        $totalPaid = round((float) $paidInstallments->sum('jumlah_bayar'), 2);
        $installmentsPaid = $paidInstallments->count();
        $nextInstallment = $terms['schedule']->get($installmentsPaid);

        $pinjaman->first_due_date = $terms['first_due_date'];
        $pinjaman->final_due_date = $terms['final_due_date'];
        $pinjaman->total_bunga = $terms['total_interest'];
        $pinjaman->total_tagihan = $terms['total_payment'];
        $pinjaman->total_terbayar = $totalPaid;
        $pinjaman->sisa_tagihan = max(round($terms['total_payment'] - $totalPaid, 2), 0);
        $pinjaman->angsuran_terbayar = $installmentsPaid;
        $pinjaman->angsuran_total = $terms['schedule']->count();
        $pinjaman->next_due_date = $nextInstallment['tanggal_jatuh_tempo'] ?? null;
        $pinjaman->next_due_amount = $nextInstallment['jumlah_tagihan'] ?? 0;
        $pinjaman->next_installment_number = $nextInstallment['angsuran_ke'] ?? null;
        $pinjaman->can_pay_now = $pinjaman->status === Pinjaman::STATUS_AKTIF && $nextInstallment !== null;
        $pinjaman->syncOriginalAttributes([
            'first_due_date',
            'final_due_date',
            'total_bunga',
            'total_tagihan',
            'total_terbayar',
            'sisa_tagihan',
            'angsuran_terbayar',
            'angsuran_total',
            'next_due_date',
            'next_due_amount',
            'next_installment_number',
            'can_pay_now',
        ]);

        return $pinjaman;
    }

    protected function resolvePeriodeBukuId(Koperasi $koperasi, string $tanggal): ?int
    {
        return PeriodeBuku::query()
            ->where('koperasi_id', $koperasi->id)
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->value('id');
    }

    protected function generateLoanNumber(Koperasi $koperasi, string $tanggalPinjaman): string
    {
        $datePart = Carbon::parse($tanggalPinjaman)->format('Ymd');
        $prefix = sprintf('PJM-%d-%s-', $koperasi->id, $datePart);

        $lastNumber = Pinjaman::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('no_pinjaman', 'like', $prefix . '%')
            ->orderByDesc('no_pinjaman')
            ->value('no_pinjaman');

        $nextSequence = 1;

        if ($lastNumber) {
            $nextSequence = ((int) Str::afterLast($lastNumber, '-')) + 1;
        }

        return $prefix . str_pad((string) $nextSequence, 5, '0', STR_PAD_LEFT);
    }
}
