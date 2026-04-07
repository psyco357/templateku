<?php

namespace App\Http\Controllers;

use App\Models\AkunKeuangan;
use App\Models\JurnalUmum;
use App\Models\Koperasi;
use App\Models\PeriodeBuku;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KeuanganJurnalController extends Controller
{
    public function index(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $filters = [
            'tanggal_mulai' => $request->string('tanggal_mulai')->toString(),
            'tanggal_selesai' => $request->string('tanggal_selesai')->toString(),
        ];

        $jurnal = JurnalUmum::query()
            ->with(['details.akun', 'poster.profile'])
            ->where('koperasi_id', $koperasi->id)
            ->when($filters['tanggal_mulai'] !== '', fn($query) => $query->whereDate('tanggal_jurnal', '>=', $filters['tanggal_mulai']))
            ->when($filters['tanggal_selesai'] !== '', fn($query) => $query->whereDate('tanggal_jurnal', '<=', $filters['tanggal_selesai']))
            ->latest('tanggal_jurnal')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $akunOptions = AkunKeuangan::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('status', AkunKeuangan::STATUS_AKTIF)
            ->where('can_post', true)
            ->orderBy('kode_akun')
            ->get();

        $summaryQuery = JurnalUmum::query()->where('koperasi_id', $koperasi->id);

        return view('pages.keuangan.jurnal.index', [
            'koperasi' => $koperasi,
            'jurnal' => $jurnal,
            'akunOptions' => $akunOptions,
            'filters' => $filters,
            'summary' => [
                'total_jurnal' => $summaryQuery->count(),
                'total_debit' => (float) $summaryQuery->sum('total_debit'),
                'total_kredit' => (float) $summaryQuery->sum('total_kredit'),
            ],
            'lineDefaults' => old('lines', array_fill(0, 4, ['akun_keuangan_id' => '', 'debit' => '', 'kredit' => '', 'uraian' => ''])),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $validated = $request->validate([
            'tanggal_jurnal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.akun_keuangan_id' => [
                'nullable',
                Rule::exists('akun_keuangan', 'id')->where(fn($query) => $query
                    ->where('koperasi_id', $koperasi->id)
                    ->where('status', AkunKeuangan::STATUS_AKTIF)
                    ->where('can_post', true)),
            ],
            'lines.*.debit' => ['nullable', 'numeric', 'gte:0'],
            'lines.*.kredit' => ['nullable', 'numeric', 'gte:0'],
            'lines.*.uraian' => ['nullable', 'string'],
        ], [
            'required' => ':attribute wajib diisi.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'array' => ':attribute harus berupa daftar.',
            'min' => ':attribute minimal terdiri dari :min baris.',
            'exists' => ':attribute yang dipilih tidak valid.',
            'numeric' => ':attribute harus berupa angka.',
            'gte' => ':attribute tidak boleh negatif.',
            'string' => ':attribute harus berupa teks.',
        ], [
            'tanggal_jurnal' => 'tanggal jurnal',
            'keterangan' => 'keterangan',
            'lines' => 'baris jurnal',
            'lines.*.akun_keuangan_id' => 'akun jurnal',
            'lines.*.debit' => 'nilai debit',
            'lines.*.kredit' => 'nilai kredit',
            'lines.*.uraian' => 'uraian',
        ]);

        $lines = collect($validated['lines'])
            ->map(fn(array $line) => [
                'akun_keuangan_id' => $line['akun_keuangan_id'] ?? null,
                'debit' => (float) ($line['debit'] ?? 0),
                'kredit' => (float) ($line['kredit'] ?? 0),
                'uraian' => $line['uraian'] ?? null,
            ])
            ->filter(fn(array $line) => $line['akun_keuangan_id'] && ($line['debit'] > 0 || $line['kredit'] > 0))
            ->values();

        if ($lines->count() < 2) {
            throw ValidationException::withMessages([
                'lines' => 'Isi minimal dua baris jurnal yang memiliki akun dan nominal.',
            ]);
        }

        if ($lines->contains(fn(array $line) => $line['debit'] > 0 && $line['kredit'] > 0)) {
            throw ValidationException::withMessages([
                'lines' => 'Setiap baris jurnal hanya boleh memiliki nilai debit atau kredit, bukan keduanya sekaligus.',
            ]);
        }

        $totalDebit = round((float) $lines->sum('debit'), 2);
        $totalKredit = round((float) $lines->sum('kredit'), 2);

        if ($totalDebit <= 0 || $totalKredit <= 0 || abs($totalDebit - $totalKredit) > 0.0001) {
            throw ValidationException::withMessages([
                'lines' => 'Total debit dan kredit harus seimbang dan lebih besar dari nol.',
            ]);
        }

        DB::transaction(function () use ($koperasi, $request, $validated, $lines, $totalDebit, $totalKredit) {
            $jurnal = JurnalUmum::query()->create([
                'koperasi_id' => $koperasi->id,
                'periode_buku_id' => $this->resolvePeriodeBukuId($koperasi, $validated['tanggal_jurnal']),
                'no_jurnal' => $this->generateJournalNumber($koperasi, $validated['tanggal_jurnal']),
                'tanggal_jurnal' => $validated['tanggal_jurnal'],
                'jenis_jurnal' => JurnalUmum::JENIS_MANUAL,
                'status' => JurnalUmum::STATUS_POSTED,
                'keterangan' => $validated['keterangan'] ?? null,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'posted_by' => $request->user()?->id,
                'posted_at' => now(),
            ]);

            foreach ($lines as $index => $line) {
                $jurnal->details()->create([
                    'akun_keuangan_id' => $line['akun_keuangan_id'],
                    'urutan' => $index + 1,
                    'uraian' => $line['uraian'],
                    'debit' => $line['debit'],
                    'kredit' => $line['kredit'],
                ]);
            }
        });

        return redirect()->route('keuangan.jurnal.index')->with([
            'status' => 'Jurnal umum berhasil disimpan.',
            'status_type' => 'success',
        ]);
    }

    protected function getActiveKoperasi(): ?Koperasi
    {
        return Auth::user()?->koperasi ?? Koperasi::query()->first();
    }

    protected function resolvePeriodeBukuId(Koperasi $koperasi, string $tanggal): ?int
    {
        return PeriodeBuku::query()
            ->where('koperasi_id', $koperasi->id)
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->value('id');
    }

    protected function generateJournalNumber(Koperasi $koperasi, string $tanggalJurnal): string
    {
        $datePart = now()->parse($tanggalJurnal)->format('Ymd');
        $prefix = sprintf('JRN-%d-%s-', $koperasi->id, $datePart);

        $lastNumber = JurnalUmum::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('no_jurnal', 'like', $prefix . '%')
            ->orderByDesc('no_jurnal')
            ->value('no_jurnal');

        $nextSequence = $lastNumber ? ((int) Str::afterLast($lastNumber, '-')) + 1 : 1;

        return $prefix . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
