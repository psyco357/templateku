<?php

namespace App\Services;

use App\Models\Angsuran;
use App\Models\AsetKoperasi;
use Illuminate\Support\Carbon;

class ProfitLossReportService
{
    public function build(int $koperasiId, Carbon $startDate, Carbon $endDate): array
    {
        $pendapatanBungaRows = Angsuran::query()
            ->with(['pinjaman.anggota.profile'])
            ->where('koperasi_id', $koperasiId)
            ->where('status', Angsuran::STATUS_DIBAYAR)
            ->whereBetween('tanggal_bayar', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->filter(fn(Angsuran $item) => (float) $item->bunga > 0)
            ->map(function (Angsuran $item) {
                return [
                    'tanggal' => $item->tanggal_bayar,
                    'kategori' => 'Pendapatan',
                    'komponen' => 'Pendapatan bunga pinjaman',
                    'referensi' => $item->pinjaman?->no_pinjaman,
                    'keterangan' => $item->pinjaman?->anggota?->profile?->nama_lengkap ?? '-',
                    'nilai' => (float) $item->bunga,
                ];
            });

        $asetNonaktifRows = AsetKoperasi::query()
            ->where('koperasi_id', $koperasiId)
            ->where('status', AsetKoperasi::STATUS_NONAKTIF)
            ->whereNotNull('tanggal_nonaktif')
            ->whereBetween('tanggal_nonaktif', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $labaRugiPelepasanRows = $asetNonaktifRows
            ->filter(function (AsetKoperasi $item) {
                return $item->tipe_nonaktif === AsetKoperasi::TIPE_NONAKTIF_JUAL
                    || ($item->tipe_nonaktif === null && (float) ($item->nilai_pelepasan ?? 0) > 0);
            })
            ->map(function (AsetKoperasi $item) {
                $nilaiPelepasan = (float) ($item->nilai_pelepasan ?? $item->nilai_perolehan ?? 0);
                $nilaiBuku = (float) ($item->nilai_perolehan ?? 0);
                $selisih = round($nilaiPelepasan - $nilaiBuku, 2);

                return [
                    'tanggal' => $item->tanggal_nonaktif,
                    'kategori' => $selisih >= 0 ? 'Pendapatan' : 'Beban',
                    'komponen' => $selisih >= 0 ? 'Laba pelepasan aset' : 'Rugi pelepasan aset',
                    'referensi' => $item->kode_aset,
                    'keterangan' => $item->nama_aset,
                    'nilai' => abs($selisih),
                ];
            })
            ->filter(fn(array $row) => $row['nilai'] > 0)
            ->values();

        $bebanAsetNonaktifRows = $asetNonaktifRows
            ->filter(function (AsetKoperasi $item) {
                return $item->tipe_nonaktif === AsetKoperasi::TIPE_NONAKTIF_TANPA_PENJUALAN
                    || ($item->tipe_nonaktif === null && (float) ($item->nilai_pelepasan ?? 0) <= 0);
            })
            ->map(function (AsetKoperasi $item) {
                return [
                    'tanggal' => $item->tanggal_nonaktif,
                    'kategori' => 'Beban',
                    'komponen' => 'Beban penghapusan aset',
                    'referensi' => $item->kode_aset,
                    'keterangan' => $item->nama_aset,
                    'nilai' => (float) ($item->nilai_perolehan ?? 0),
                ];
            })
            ->filter(fn(array $row) => $row['nilai'] > 0)
            ->values();

        $rows = $pendapatanBungaRows
            ->concat($labaRugiPelepasanRows)
            ->concat($bebanAsetNonaktifRows)
            ->sortBy([
                ['tanggal', 'asc'],
                ['kategori', 'asc'],
            ])
            ->values();

        $totalPendapatan = (float) $rows->where('kategori', 'Pendapatan')->sum('nilai');
        $totalBeban = (float) $rows->where('kategori', 'Beban')->sum('nilai');

        return [
            'summary' => [
                'pendapatan_bunga' => (float) $pendapatanBungaRows->sum('nilai'),
                'laba_pelepasan_aset' => (float) $labaRugiPelepasanRows->where('kategori', 'Pendapatan')->sum('nilai'),
                'rugi_pelepasan_aset' => (float) $labaRugiPelepasanRows->where('kategori', 'Beban')->sum('nilai'),
                'beban_penghapusan_aset' => (float) $bebanAsetNonaktifRows->sum('nilai'),
                'total_pendapatan' => $totalPendapatan,
                'total_beban' => $totalBeban,
                'laba_bersih' => round($totalPendapatan - $totalBeban, 2),
            ],
            'rows' => $rows,
        ];
    }
}
