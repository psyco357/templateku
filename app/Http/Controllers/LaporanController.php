<?php

namespace App\Http\Controllers;

use App\Models\AnggotaModel;
use App\Models\Angsuran;
use App\Models\AsetKoperasi;
use App\Models\Koperasi;
use App\Models\Pinjaman;
use App\Models\Simpanan;
use App\Services\ProfitLossReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function __construct(protected ProfitLossReportService $profitLossReportService) {}

    public function neracaKeuangan(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $reportDate = $request->date('tanggal')
            ? Carbon::parse($request->date('tanggal'))->endOfDay()
            : now()->endOfDay();

        $simpananTotal = (float) Simpanan::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('status', Simpanan::STATUS_POSTED)
            ->whereDate('tanggal_transaksi', '<=', $reportDate->toDateString())
            ->sum('jumlah');

        $pinjaman = Pinjaman::query()
            ->with(['anggota.profile', 'angsuran'])
            ->where('koperasi_id', $koperasi->id)
            ->where('status', '!=', Pinjaman::STATUS_DITOLAK)
            ->whereDate('tanggal_pinjaman', '<=', $reportDate->toDateString())
            ->get();

        $angsuranMasuk = (float) Angsuran::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('status', Angsuran::STATUS_DIBAYAR)
            ->whereDate('tanggal_bayar', '<=', $reportDate->toDateString())
            ->sum('jumlah_bayar');

        $bungaDiterima = (float) Angsuran::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('status', Angsuran::STATUS_DIBAYAR)
            ->whereDate('tanggal_bayar', '<=', $reportDate->toDateString())
            ->sum('bunga');

        $asetKoperasi = $this->getAssetSnapshotQuery($koperasi->id, $reportDate)->get();

        $asetNonaktifSampaiTanggal = AsetKoperasi::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('status', AsetKoperasi::STATUS_NONAKTIF)
            ->whereNotNull('tanggal_nonaktif')
            ->whereDate('tanggal_nonaktif', '<=', $reportDate->toDateString())
            ->get();

        $asetDijual = $asetNonaktifSampaiTanggal->filter(function (AsetKoperasi $item) {
            return $item->tipe_nonaktif === AsetKoperasi::TIPE_NONAKTIF_JUAL
                || ($item->tipe_nonaktif === null && (float) ($item->nilai_pelepasan ?? 0) > 0);
        });

        $asetNonaktifTanpaPenjualan = $asetNonaktifSampaiTanggal->filter(function (AsetKoperasi $item) {
            return $item->tipe_nonaktif === AsetKoperasi::TIPE_NONAKTIF_TANPA_PENJUALAN
                || ($item->tipe_nonaktif === null && (float) ($item->nilai_pelepasan ?? 0) <= 0);
        });

        $hasilPelepasanAset = (float) $asetDijual
            ->sum(fn(AsetKoperasi $item) => (float) ($item->nilai_pelepasan ?? $item->nilai_perolehan ?? 0));
        $nilaiBukuAsetTerjual = (float) $asetDijual->sum('nilai_perolehan');
        $penurunanAsetTanpaPenjualan = (float) $asetNonaktifTanpaPenjualan->sum('nilai_perolehan');
        $labaRugiPelepasanAset = round($hasilPelepasanAset - $nilaiBukuAsetTerjual, 2);

        $asetInvestasi = (float) $asetKoperasi->sum('nilai_perolehan');
        $asetEmas = (float) $asetKoperasi->where('jenis_aset', AsetKoperasi::JENIS_EMAS)->sum('nilai_perolehan');

        $pinjamanDisalurkan = (float) $pinjaman->sum('jumlah_pinjaman');
        $loanSummaries = $pinjaman->map(fn(Pinjaman $loan) => $this->summarizeLoan($loan, $reportDate));
        $piutangPinjaman = (float) $loanSummaries->sum('sisa_pokok');
        $kasTersedia = round($simpananTotal + $angsuranMasuk + $hasilPelepasanAset - $pinjamanDisalurkan - $asetInvestasi, 2);
        $totalAset = round($kasTersedia + $piutangPinjaman + $asetInvestasi, 2);
        $totalKewajiban = round($simpananTotal, 2);
        $ekuitas = round($totalAset - $totalKewajiban, 2);
        $totalPasiva = round($totalKewajiban + $ekuitas, 2);

        return view('pages.laporan.neraca-keuangan', [
            'koperasi' => $koperasi,
            'reportDate' => $reportDate,
            'summary' => [
                'kas_tersedia' => $kasTersedia,
                'piutang_pinjaman' => $piutangPinjaman,
                'aset_investasi' => $asetInvestasi,
                'aset_emas' => $asetEmas,
                'hasil_pelepasan_aset' => $hasilPelepasanAset,
                'nilai_buku_aset_terjual' => $nilaiBukuAsetTerjual,
                'laba_rugi_pelepasan_aset' => $labaRugiPelepasanAset,
                'penurunan_aset_tanpa_penjualan' => $penurunanAsetTanpaPenjualan,
                'total_aset' => $totalAset,
                'total_kewajiban' => $totalKewajiban,
                'ekuitas' => $ekuitas,
                'total_pasiva' => $totalPasiva,
                'simpanan_anggota' => $simpananTotal,
                'bunga_diterima' => $bungaDiterima,
            ],
            'assetRows' => $asetKoperasi->sortByDesc('nilai_perolehan')->take(10)->values(),
            'loanSummaries' => $loanSummaries->sortByDesc('sisa_pokok')->take(10)->values(),
        ]);
    }

    public function arusKas(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $startDate = $request->date('tanggal_mulai')
            ? Carbon::parse($request->date('tanggal_mulai'))->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->date('tanggal_selesai')
            ? Carbon::parse($request->date('tanggal_selesai'))->endOfDay()
            : now()->endOfMonth();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $simpananRows = Simpanan::query()
            ->with(['anggota.profile', 'jenisSimpanan'])
            ->where('koperasi_id', $koperasi->id)
            ->where('status', Simpanan::STATUS_POSTED)
            ->whereBetween('tanggal_transaksi', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->map(function (Simpanan $item) {
                return [
                    'tanggal' => $item->tanggal_transaksi,
                    'kategori' => 'Kas Masuk',
                    'sumber' => 'Simpanan - ' . ($item->jenisSimpanan?->nama_jenis ?? 'Tanpa jenis'),
                    'referensi' => $item->no_bukti,
                    'anggota' => $item->anggota?->profile?->nama_lengkap ?? '-',
                    'masuk' => (float) $item->jumlah,
                    'keluar' => 0,
                ];
            });

        $angsuranRows = Angsuran::query()
            ->with(['pinjaman.anggota.profile'])
            ->where('koperasi_id', $koperasi->id)
            ->where('status', Angsuran::STATUS_DIBAYAR)
            ->whereBetween('tanggal_bayar', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->map(function (Angsuran $item) {
                return [
                    'tanggal' => $item->tanggal_bayar,
                    'kategori' => 'Kas Masuk',
                    'sumber' => 'Pembayaran Angsuran',
                    'referensi' => $item->pinjaman?->no_pinjaman,
                    'anggota' => $item->pinjaman?->anggota?->profile?->nama_lengkap ?? '-',
                    'masuk' => (float) $item->jumlah_bayar,
                    'keluar' => 0,
                ];
            });

        $pinjamanRows = Pinjaman::query()
            ->with('anggota.profile')
            ->where('koperasi_id', $koperasi->id)
            ->where('status', '!=', Pinjaman::STATUS_DITOLAK)
            ->whereBetween('tanggal_pinjaman', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->map(function (Pinjaman $item) {
                return [
                    'tanggal' => $item->tanggal_pinjaman,
                    'kategori' => 'Kas Keluar',
                    'sumber' => 'Pencairan Pinjaman',
                    'referensi' => $item->no_pinjaman,
                    'anggota' => $item->anggota?->profile?->nama_lengkap ?? '-',
                    'masuk' => 0,
                    'keluar' => (float) $item->jumlah_pinjaman,
                ];
            });

        $assetRows = AsetKoperasi::query()
            ->where('koperasi_id', $koperasi->id)
            ->whereBetween('tanggal_perolehan', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->map(function (AsetKoperasi $item) {
                return [
                    'tanggal' => $item->tanggal_perolehan,
                    'kategori' => 'Kas Keluar',
                    'sumber' => 'Pembelian Aset - ' . $item->nama_aset,
                    'referensi' => $item->kode_aset,
                    'anggota' => strtoupper($item->jenis_aset),
                    'masuk' => 0,
                    'keluar' => (float) $item->nilai_perolehan,
                ];
            });

        $assetDisposalRows = AsetKoperasi::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('status', AsetKoperasi::STATUS_NONAKTIF)
            ->whereNotNull('tanggal_nonaktif')
            ->whereBetween('tanggal_nonaktif', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->filter(function (AsetKoperasi $item) {
                $isSale = $item->tipe_nonaktif === AsetKoperasi::TIPE_NONAKTIF_JUAL
                    || ($item->tipe_nonaktif === null && (float) ($item->nilai_pelepasan ?? 0) > 0);

                return $isSale && (float) ($item->nilai_pelepasan ?? $item->nilai_perolehan ?? 0) > 0;
            })
            ->map(function (AsetKoperasi $item) {
                $nilaiPelepasan = (float) ($item->nilai_pelepasan ?? $item->nilai_perolehan ?? 0);

                return [
                    'tanggal' => $item->tanggal_nonaktif,
                    'kategori' => 'Kas Masuk',
                    'sumber' => 'Pelepasan Aset - ' . $item->nama_aset,
                    'referensi' => $item->kode_aset,
                    'anggota' => strtoupper($item->jenis_aset),
                    'masuk' => $nilaiPelepasan,
                    'keluar' => 0,
                ];
            });

        $movements = $simpananRows
            ->concat($angsuranRows)
            ->concat($pinjamanRows)
            ->concat($assetRows)
            ->concat($assetDisposalRows)
            ->sortBy([
                ['tanggal', 'asc'],
                ['kategori', 'desc'],
            ])
            ->values();

        $runningBalance = 0;
        $movements = $movements->map(function (array $row) use (&$runningBalance) {
            $runningBalance += $row['masuk'] - $row['keluar'];
            $row['saldo_berjalan'] = $runningBalance;

            return $row;
        });

        return view('pages.laporan.arus-kas', [
            'koperasi' => $koperasi,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => [
                'total_masuk' => (float) $movements->sum('masuk'),
                'total_keluar' => (float) $movements->sum('keluar'),
                'arus_bersih' => (float) $movements->sum(fn(array $row) => $row['masuk'] - $row['keluar']),
                'jumlah_transaksi' => $movements->count(),
            ],
            'movements' => $movements,
        ]);
    }

    public function rugiLaba(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $startDate = $request->date('tanggal_mulai')
            ? Carbon::parse($request->date('tanggal_mulai'))->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->date('tanggal_selesai')
            ? Carbon::parse($request->date('tanggal_selesai'))->endOfDay()
            : now()->endOfMonth();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $report = $this->profitLossReportService->build($koperasi->id, $startDate, $endDate);

        return view('pages.laporan.rugi-laba', [
            'koperasi' => $koperasi,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $report['summary'],
            'rows' => $report['rows'],
        ]);
    }

    public function rugiLabaTahunan(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $year = max(2020, (int) $request->integer('tahun', (int) now()->year));
        $startDate = Carbon::create($year, 1, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfYear();
        $report = $this->profitLossReportService->build($koperasi->id, $startDate, $endDate);

        $monthlyRows = collect(range(1, 12))->map(function (int $month) use ($year, $report) {
            $monthDate = Carbon::create($year, $month, 1)->startOfMonth();
            $monthRows = $report['rows']
                ->filter(fn(array $row) => $row['tanggal']->year === $year && $row['tanggal']->month === $month)
                ->values();

            $pendapatan = (float) $monthRows->where('kategori', 'Pendapatan')->sum('nilai');
            $beban = (float) $monthRows->where('kategori', 'Beban')->sum('nilai');

            return [
                'bulan' => $monthDate,
                'pendapatan' => $pendapatan,
                'beban' => $beban,
                'laba_bersih' => round($pendapatan - $beban, 2),
                'jumlah_transaksi' => $monthRows->count(),
            ];
        });

        $shuDasar = max((float) $report['summary']['laba_bersih'], 0);

        return view('pages.laporan.rugi-laba-tahunan', [
            'koperasi' => $koperasi,
            'year' => $year,
            'summary' => array_merge($report['summary'], [
                'shu_dasar' => $shuDasar,
                'shu_status' => $shuDasar > 0 ? 'siap-dibahas' : 'belum-tersedia',
            ]),
            'rows' => $report['rows'],
            'monthlyRows' => $monthlyRows,
        ]);
    }

    public function tunggakanPinjaman(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $referenceDate = $request->date('tanggal')
            ? Carbon::parse($request->date('tanggal'))->endOfDay()
            : now()->endOfDay();

        $rows = Pinjaman::query()
            ->with(['anggota.profile', 'angsuran'])
            ->where('koperasi_id', $koperasi->id)
            ->where('status', Pinjaman::STATUS_AKTIF)
            ->get()
            ->map(fn(Pinjaman $loan) => $this->summarizeLoan($loan, $referenceDate))
            ->filter(fn(array $row) => $row['jumlah_tunggakan'] > 0)
            ->sortByDesc('jumlah_tunggakan')
            ->values();

        return view('pages.laporan.tunggakan-pinjaman', [
            'koperasi' => $koperasi,
            'referenceDate' => $referenceDate,
            'rows' => $rows,
            'summary' => [
                'pinjaman_menunggak' => $rows->count(),
                'angsuran_menunggak' => (int) $rows->sum('angsuran_menunggak'),
                'jumlah_tunggakan' => (float) $rows->sum('jumlah_tunggakan'),
                'terlama_hari' => (int) $rows->max('hari_tunggakan'),
            ],
        ]);
    }

    public function rat(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $year = max(2020, (int) $request->integer('tahun', (int) now()->year));
        $startDate = Carbon::create($year, 1, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfYear();

        $anggota = AnggotaModel::query()->with('profile.user')
            ->whereHas('profile.user', fn($query) => $query->where('koperasi_id', $koperasi->id))
            ->get();

        $simpananTahun = Simpanan::query()
            ->with('jenisSimpanan')
            ->where('koperasi_id', $koperasi->id)
            ->where('status', Simpanan::STATUS_POSTED)
            ->whereBetween('tanggal_transaksi', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $pinjamanTahun = Pinjaman::query()
            ->with(['anggota.profile', 'angsuran'])
            ->where('koperasi_id', $koperasi->id)
            ->where('status', '!=', Pinjaman::STATUS_DITOLAK)
            ->whereBetween('tanggal_pinjaman', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $angsuranTahun = Angsuran::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('status', Angsuran::STATUS_DIBAYAR)
            ->whereBetween('tanggal_bayar', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $topBorrowers = $pinjamanTahun
            ->groupBy(fn(Pinjaman $item) => $item->anggota?->profile?->nama_lengkap ?? 'Tanpa nama')
            ->map(fn(Collection $items, string $name) => [
                'nama' => $name,
                'jumlah_pinjaman' => (float) $items->sum('jumlah_pinjaman'),
                'total_pinjaman' => $items->count(),
            ])
            ->sortByDesc('jumlah_pinjaman')
            ->take(5)
            ->values();

        $simpananByJenis = $simpananTahun
            ->groupBy(fn(Simpanan $item) => $item->jenisSimpanan?->nama_jenis ?? 'Tanpa jenis')
            ->map(fn(Collection $items, string $jenis) => [
                'jenis' => $jenis,
                'jumlah_transaksi' => $items->count(),
                'total' => (float) $items->sum('jumlah'),
            ])
            ->sortByDesc('total')
            ->values();

        $outstandingNow = Pinjaman::query()
            ->with('angsuran')
            ->where('koperasi_id', $koperasi->id)
            ->whereIn('status', [Pinjaman::STATUS_AKTIF, Pinjaman::STATUS_LUNAS])
            ->get()
            ->map(fn(Pinjaman $loan) => $this->summarizeLoan($loan, now()->endOfDay()))
            ->sum('sisa_pokok');

        $asetTahun = AsetKoperasi::query()
            ->where('koperasi_id', $koperasi->id)
            ->whereBetween('tanggal_perolehan', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $asetBerjalan = $this->getAssetSnapshotQuery($koperasi->id, now()->endOfDay())->sum('nilai_perolehan');
        $asetAktifTahun = $this->getAssetSnapshotQuery($koperasi->id, $endDate)
            ->whereBetween('tanggal_perolehan', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();
        $asetPelepasanTahun = AsetKoperasi::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('status', AsetKoperasi::STATUS_NONAKTIF)
            ->whereNotNull('tanggal_nonaktif')
            ->whereBetween('tanggal_nonaktif', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->sum(function (AsetKoperasi $item) {
                $isSale = $item->tipe_nonaktif === AsetKoperasi::TIPE_NONAKTIF_JUAL
                    || ($item->tipe_nonaktif === null && (float) ($item->nilai_pelepasan ?? 0) > 0);

                return $isSale ? (float) ($item->nilai_pelepasan ?? $item->nilai_perolehan ?? 0) : 0;
            });

        return view('pages.laporan.rat', [
            'koperasi' => $koperasi,
            'year' => $year,
            'summary' => [
                'anggota_total' => $anggota->count(),
                'anggota_aktif' => $anggota->where('status', AnggotaModel::STATUS_AKTIF)->count(),
                'anggota_nonaktif' => $anggota->where('status', AnggotaModel::STATUS_NONAKTIF)->count(),
                'anggota_cuti' => $anggota->where('status', AnggotaModel::STATUS_CUTI)->count(),
                'simpanan_tahun' => (float) $simpananTahun->sum('jumlah'),
                'pinjaman_tahun' => (float) $pinjamanTahun->sum('jumlah_pinjaman'),
                'angsuran_tahun' => (float) $angsuranTahun->sum('jumlah_bayar'),
                'bunga_tahun' => (float) $angsuranTahun->sum('bunga'),
                'piutang_berjalan' => (float) $outstandingNow,
                'aset_tahun' => (float) $asetTahun->sum('nilai_perolehan'),
                'aset_emas_tahun' => (float) $asetTahun->where('jenis_aset', AsetKoperasi::JENIS_EMAS)->sum('nilai_perolehan'),
                'aset_berjalan' => (float) $asetBerjalan,
                'aset_pelepasan_tahun' => (float) $asetPelepasanTahun,
            ],
            'simpananByJenis' => $simpananByJenis,
            'topBorrowers' => $topBorrowers,
            'assetsByType' => $asetAktifTahun
                ->groupBy('jenis_aset')
                ->map(fn(Collection $items, string $jenis) => [
                    'jenis' => ucfirst($jenis),
                    'jumlah' => $items->count(),
                    'total' => (float) $items->sum('nilai_perolehan'),
                ])
                ->sortByDesc('total')
                ->values(),
        ]);
    }

    protected function getAssetSnapshotQuery(int $koperasiId, Carbon $asOfDate)
    {
        return AsetKoperasi::query()
            ->where('koperasi_id', $koperasiId)
            ->whereDate('tanggal_perolehan', '<=', $asOfDate->toDateString())
            ->where(function ($query) use ($asOfDate) {
                $query->where('status', AsetKoperasi::STATUS_AKTIF)
                    ->orWhere(function ($subQuery) use ($asOfDate) {
                        $subQuery->where('status', AsetKoperasi::STATUS_NONAKTIF)
                            ->whereNotNull('tanggal_nonaktif')
                            ->whereDate('tanggal_nonaktif', '>', $asOfDate->toDateString());
                    });
            });
    }

    protected function getActiveKoperasi(): ?Koperasi
    {
        return Auth::user()?->koperasi ?? Koperasi::query()->first();
    }

    protected function summarizeLoan(Pinjaman $loan, Carbon $asOfDate): array
    {
        $terms = $this->buildLoanTerms(
            (float) $loan->jumlah_pinjaman,
            (int) $loan->tenor_bulan,
            $loan->tanggal_pinjaman,
            (float) ($loan->bunga_nominal_bulanan ?? Pinjaman::DEFAULT_MONTHLY_INTEREST),
            (int) ($loan->tanggal_tagihan_bulanan ?? Pinjaman::DEFAULT_PAYMENT_DAY)
        );

        $paidInstallments = $loan->angsuran
            ->where('status', Angsuran::STATUS_DIBAYAR)
            ->sortBy('angsuran_ke')
            ->values();

        $paidInstallmentsCount = $paidInstallments->count();
        $paidPrincipal = (float) $paidInstallments->sum('pokok');
        $remainingPrincipal = max(round((float) $loan->jumlah_pinjaman - $paidPrincipal, 2), 0);

        $dueInstallments = $terms['schedule']
            ->filter(fn(array $row) => $row['tanggal_jatuh_tempo']->lte($asOfDate))
            ->values();

        $overdueCount = max($dueInstallments->count() - $paidInstallmentsCount, 0);
        $overdueInstallments = $dueInstallments->slice($paidInstallmentsCount)->values();
        $firstOverdue = $overdueInstallments->first();

        return [
            'id' => $loan->id,
            'no_pinjaman' => $loan->no_pinjaman,
            'anggota' => $loan->anggota?->profile?->nama_lengkap ?? '-',
            'tanggal_pinjaman' => $loan->tanggal_pinjaman,
            'tanggal_jatuh_tempo' => $loan->tanggal_jatuh_tempo,
            'jumlah_pinjaman' => (float) $loan->jumlah_pinjaman,
            'angsuran_terbayar' => $paidInstallmentsCount,
            'angsuran_total' => $terms['schedule']->count(),
            'sisa_pokok' => $remainingPrincipal,
            'angsuran_menunggak' => $overdueCount,
            'jumlah_tunggakan' => (float) $overdueInstallments->sum('jumlah_tagihan'),
            'jatuh_tempo_terdekat' => $terms['first_due_date'],
            'jatuh_tempo_tertua' => $firstOverdue['tanggal_jatuh_tempo'] ?? null,
            'hari_tunggakan' => $firstOverdue ? $firstOverdue['tanggal_jatuh_tempo']->diffInDays($asOfDate) : 0,
            'status' => $loan->status,
        ];
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
            'first_due_date' => $firstDueDate,
            'final_due_date' => $finalDueDate,
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
}
