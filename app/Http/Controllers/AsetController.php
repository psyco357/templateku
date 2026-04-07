<?php

namespace App\Http\Controllers;

use App\Models\AsetKoperasi;
use App\Models\Koperasi;
use App\Models\PeriodeBuku;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AsetController extends Controller
{
    public function index(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $filters = [
            'jenis_aset' => $request->string('jenis_aset')->toString(),
            'status' => $request->string('status')->toString(),
        ];

        $aset = AsetKoperasi::query()
            ->where('koperasi_id', $koperasi->id)
            ->when($filters['jenis_aset'] !== '', fn($query) => $query->where('jenis_aset', $filters['jenis_aset']))
            ->when($filters['status'] !== '', fn($query) => $query->where('status', $filters['status']))
            ->latest('tanggal_perolehan')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $summaryQuery = AsetKoperasi::query()->where('koperasi_id', $koperasi->id);
        $editAssetId = $request->integer('edit');
        $editAsset = $editAssetId
            ? AsetKoperasi::query()->where('koperasi_id', $koperasi->id)->find($editAssetId)
            : null;

        return view('pages.aset.index', [
            'koperasi' => $koperasi,
            'aset' => $aset,
            'editAsset' => $editAsset,
            'filters' => $filters,
            'jenisOptions' => AsetKoperasi::jenisOptions(),
            'tipeNonaktifOptions' => AsetKoperasi::tipeNonaktifOptions(),
            'statusOptions' => AsetKoperasi::statuses(),
            'summary' => [
                'total_aset' => $summaryQuery->count(),
                'aset_aktif' => (clone $summaryQuery)->where('status', AsetKoperasi::STATUS_AKTIF)->count(),
                'total_nilai' => (float) $summaryQuery->sum('nilai_perolehan'),
                'total_emas' => (float) (clone $summaryQuery)->where('jenis_aset', AsetKoperasi::JENIS_EMAS)->sum('nilai_perolehan'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $validated = $request->validate([
            'nama_aset' => ['required', 'string', 'max:255'],
            'jenis_aset' => ['required', Rule::in(AsetKoperasi::jenisOptions())],
            'tanggal_perolehan' => ['required', 'date'],
            'nilai_perolehan' => ['required', 'numeric', 'gt:0'],
            'kuantitas' => ['nullable', 'numeric', 'gt:0'],
            'satuan' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute terlalu panjang.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'numeric' => ':attribute harus berupa angka.',
            'gt' => ':attribute harus lebih besar dari nol.',
            'in' => ':attribute yang dipilih tidak valid.',
        ], [
            'nama_aset' => 'nama aset',
            'jenis_aset' => 'jenis aset',
            'tanggal_perolehan' => 'tanggal perolehan',
            'nilai_perolehan' => 'nilai perolehan',
            'kuantitas' => 'kuantitas',
            'satuan' => 'satuan',
            'keterangan' => 'keterangan',
        ]);

        AsetKoperasi::query()->create([
            'koperasi_id' => $koperasi->id,
            'periode_buku_id' => $this->resolvePeriodeBukuId($koperasi, $validated['tanggal_perolehan']),
            'kode_aset' => $this->generateAssetCode($koperasi, $validated['tanggal_perolehan']),
            'nama_aset' => $validated['nama_aset'],
            'jenis_aset' => $validated['jenis_aset'],
            'tanggal_perolehan' => $validated['tanggal_perolehan'],
            'nilai_perolehan' => $validated['nilai_perolehan'],
            'kuantitas' => $validated['kuantitas'] ?? null,
            'satuan' => $validated['satuan'] ?? null,
            'status' => AsetKoperasi::STATUS_AKTIF,
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        return redirect()->route('aset.index')->with([
            'status' => 'Aset koperasi berhasil dicatat.',
            'status_type' => 'success',
        ]);
    }

    public function update(Request $request, AsetKoperasi $aset): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);
        abort_unless((int) $aset->koperasi_id === (int) $koperasi->id, 404);

        $validated = $request->validate([
            'nama_aset' => ['required', 'string', 'max:255'],
            'jenis_aset' => ['required', Rule::in(AsetKoperasi::jenisOptions())],
            'tanggal_perolehan' => ['required', 'date'],
            'nilai_perolehan' => ['required', 'numeric', 'gt:0'],
            'kuantitas' => ['nullable', 'numeric', 'gt:0'],
            'satuan' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute terlalu panjang.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'numeric' => ':attribute harus berupa angka.',
            'gt' => ':attribute harus lebih besar dari nol.',
            'in' => ':attribute yang dipilih tidak valid.',
        ], [
            'nama_aset' => 'nama aset',
            'jenis_aset' => 'jenis aset',
            'tanggal_perolehan' => 'tanggal perolehan',
            'nilai_perolehan' => 'nilai perolehan',
            'kuantitas' => 'kuantitas',
            'satuan' => 'satuan',
            'keterangan' => 'keterangan',
        ]);

        $aset->update([
            'periode_buku_id' => $this->resolvePeriodeBukuId($koperasi, $validated['tanggal_perolehan']),
            'nama_aset' => $validated['nama_aset'],
            'jenis_aset' => $validated['jenis_aset'],
            'tanggal_perolehan' => $validated['tanggal_perolehan'],
            'nilai_perolehan' => $validated['nilai_perolehan'],
            'kuantitas' => $validated['kuantitas'] ?? null,
            'satuan' => $validated['satuan'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        return redirect()->route('aset.index')->with([
            'status' => 'Data aset berhasil diperbarui.',
            'status_type' => 'success',
        ]);
    }

    public function deactivate(Request $request, AsetKoperasi $aset): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);
        abort_unless((int) $aset->koperasi_id === (int) $koperasi->id, 404);

        if ($aset->status === AsetKoperasi::STATUS_NONAKTIF) {
            return redirect()->route('aset.index')->with([
                'status' => 'Aset tersebut sudah nonaktif.',
                'status_type' => 'warning',
            ]);
        }

        $validated = $request->validate([
            'tanggal_nonaktif' => ['required', 'date', 'after_or_equal:' . $aset->tanggal_perolehan->toDateString()],
            'tipe_nonaktif' => ['required', Rule::in(AsetKoperasi::tipeNonaktifOptions())],
            'nilai_pelepasan' => ['nullable', 'numeric', 'gte:0'],
        ], [
            'required' => ':attribute wajib diisi.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'after_or_equal' => ':attribute tidak boleh sebelum tanggal perolehan.',
            'numeric' => ':attribute harus berupa angka.',
            'gte' => ':attribute tidak boleh negatif.',
            'in' => ':attribute yang dipilih tidak valid.',
        ], [
            'tanggal_nonaktif' => 'tanggal nonaktif',
            'tipe_nonaktif' => 'aksi nonaktif',
            'nilai_pelepasan' => 'nilai pelepasan',
        ]);

        $isSale = $validated['tipe_nonaktif'] === AsetKoperasi::TIPE_NONAKTIF_JUAL;
        $nilaiPelepasan = $isSale
            ? ((array_key_exists('nilai_pelepasan', $validated) && $validated['nilai_pelepasan'] !== null && $validated['nilai_pelepasan'] !== '')
                ? (float) $validated['nilai_pelepasan']
                : (float) $aset->nilai_perolehan)
            : null;

        $aset->update([
            'periode_buku_id' => $this->resolvePeriodeBukuId($koperasi, $validated['tanggal_nonaktif']),
            'status' => AsetKoperasi::STATUS_NONAKTIF,
            'tanggal_nonaktif' => $validated['tanggal_nonaktif'],
            'tipe_nonaktif' => $validated['tipe_nonaktif'],
            'nilai_pelepasan' => $nilaiPelepasan,
        ]);

        return redirect()->route('aset.index')->with([
            'status' => $isSale
                ? 'Aset berhasil dijual. Arus kas akan mencatat kas masuk sebesar Rp ' . number_format((float) $nilaiPelepasan, 0, ',', '.') . ' pada tanggal tersebut.'
                : 'Aset berhasil dinonaktifkan tanpa penjualan. Neraca akan menyesuaikan mulai tanggal tersebut tanpa kas masuk baru.',
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

    protected function generateAssetCode(Koperasi $koperasi, string $tanggal): string
    {
        $datePart = Carbon::parse($tanggal)->format('Ymd');
        $prefix = sprintf('AST-%d-%s-', $koperasi->id, $datePart);

        $lastCode = AsetKoperasi::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('kode_aset', 'like', $prefix . '%')
            ->orderByDesc('kode_aset')
            ->value('kode_aset');

        $nextSequence = $lastCode ? ((int) Str::afterLast($lastCode, '-')) + 1 : 1;

        return $prefix . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
