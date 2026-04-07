<?php

namespace App\Services;

use App\Models\AkunKeuangan;
use App\Models\JurnalUmum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AccountingReportService
{
    public function buildCashFlow(int $koperasiId, Carbon $startDate, Carbon $endDate): array
    {
        $cashAccountIds = $this->getCashAccounts($koperasiId)->pluck('id');

        $openingBalance = (float) JurnalUmum::query()
            ->join('jurnal_umum_details', 'jurnal_umum_details.jurnal_umum_id', '=', 'jurnal_umum.id')
            ->where('jurnal_umum.koperasi_id', $koperasiId)
            ->whereIn('jurnal_umum_details.akun_keuangan_id', $cashAccountIds)
            ->whereDate('jurnal_umum.tanggal_jurnal', '<', $startDate->toDateString())
            ->selectRaw('COALESCE(SUM(jurnal_umum_details.debit - jurnal_umum_details.kredit), 0) as saldo')
            ->value('saldo');

        $movements = JurnalUmum::query()
            ->with(['details.akun'])
            ->where('koperasi_id', $koperasiId)
            ->whereBetween('tanggal_jurnal', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereHas('details', fn($query) => $query->whereIn('akun_keuangan_id', $cashAccountIds))
            ->orderBy('tanggal_jurnal')
            ->orderBy('id')
            ->get()
            ->map(function (JurnalUmum $journal) use ($cashAccountIds) {
                $cashDetails = $journal->details->filter(fn($detail) => $cashAccountIds->contains($detail->akun_keuangan_id));
                $masuk = (float) $cashDetails->sum('debit');
                $keluar = (float) $cashDetails->sum('kredit');

                return [
                    'tanggal' => $journal->tanggal_jurnal,
                    'kategori' => $masuk >= $keluar ? 'Kas Masuk' : 'Kas Keluar',
                    'sumber' => $journal->keterangan ?: strtoupper($journal->jenis_jurnal),
                    'referensi' => $journal->sumber_referensi ?: $journal->no_jurnal,
                    'anggota' => '-',
                    'masuk' => $masuk,
                    'keluar' => $keluar,
                ];
            })
            ->values();

        $runningBalance = $openingBalance;
        $movements = $movements->map(function (array $row) use (&$runningBalance) {
            $runningBalance += $row['masuk'] - $row['keluar'];
            $row['saldo_berjalan'] = $runningBalance;

            return $row;
        });

        return [
            'summary' => [
                'total_masuk' => (float) $movements->sum('masuk'),
                'total_keluar' => (float) $movements->sum('keluar'),
                'arus_bersih' => (float) $movements->sum(fn(array $row) => $row['masuk'] - $row['keluar']),
                'jumlah_transaksi' => $movements->count(),
            ],
            'movements' => $movements,
        ];
    }

    public function buildBalanceSheet(int $koperasiId, Carbon $reportDate): array
    {
        $accounts = AkunKeuangan::query()
            ->where('koperasi_id', $koperasiId)
            ->get()
            ->keyBy('id');

        $balances = JurnalUmum::query()
            ->join('jurnal_umum_details', 'jurnal_umum_details.jurnal_umum_id', '=', 'jurnal_umum.id')
            ->where('jurnal_umum.koperasi_id', $koperasiId)
            ->whereDate('jurnal_umum.tanggal_jurnal', '<=', $reportDate->toDateString())
            ->groupBy('jurnal_umum_details.akun_keuangan_id')
            ->selectRaw('jurnal_umum_details.akun_keuangan_id, COALESCE(SUM(jurnal_umum_details.debit), 0) as total_debit, COALESCE(SUM(jurnal_umum_details.kredit), 0) as total_kredit')
            ->get();

        $rows = $balances->map(function ($row) use ($accounts) {
            $account = $accounts->get($row->akun_keuangan_id);
            $net = round((float) $row->total_debit - (float) $row->total_kredit, 2);

            return [
                'akun' => $account,
                'net' => $net,
                'debit' => (float) $row->total_debit,
                'kredit' => (float) $row->total_kredit,
            ];
        })->filter(fn(array $row) => $row['akun'] !== null)->values();

        $cashAccountIds = $this->getCashAccounts($koperasiId)->pluck('id')->all();
        $kasTersedia = (float) $rows->filter(fn(array $row) => in_array($row['akun']->id, $cashAccountIds, true))->sum('net');
        $piutangPinjaman = (float) $rows->filter(fn(array $row) => $row['akun']->kode_akun === '1300')->sum('net');
        $asetInvestasi = (float) $rows->filter(fn(array $row) => $row['akun']->tipe_akun === AkunKeuangan::TIPE_ASET && !in_array($row['akun']->id, $cashAccountIds, true) && $row['akun']->kode_akun !== '1300')->sum('net');
        $totalAset = (float) $rows->filter(fn(array $row) => $row['akun']->tipe_akun === AkunKeuangan::TIPE_ASET)->sum('net');
        $totalKewajiban = (float) $rows->filter(fn(array $row) => $row['akun']->tipe_akun === AkunKeuangan::TIPE_KEWAJIBAN)->sum(fn(array $row) => abs(min($row['net'], 0)));
        $ekuitas = round(-(float) $rows->filter(fn(array $row) => in_array($row['akun']->tipe_akun, [AkunKeuangan::TIPE_MODAL, AkunKeuangan::TIPE_PENDAPATAN, AkunKeuangan::TIPE_BEBAN], true))->sum('net'), 2);

        $profitLoss = $this->buildProfitLoss($koperasiId, Carbon::create($reportDate->year, 1, 1)->startOfDay(), $reportDate->copy()->endOfDay());
        $assetDisposalEntries = $profitLoss['rows']->filter(fn(array $row) => str_starts_with((string) ($row['referensi'] ?? ''), 'aset-pelepasan:'));

        $hasilPelepasanAset = (float) $assetDisposalEntries->where('kategori', 'Pendapatan')->sum('nilai');
        $rugiPelepasanAset = (float) $assetDisposalEntries->where('kategori', 'Beban')->where('komponen', 'Rugi pelepasan aset')->sum('nilai');

        return [
            'summary' => [
                'kas_tersedia' => round($kasTersedia, 2),
                'piutang_pinjaman' => round($piutangPinjaman, 2),
                'aset_investasi' => round($asetInvestasi, 2),
                'aset_emas' => 0.0,
                'hasil_pelepasan_aset' => $hasilPelepasanAset,
                'nilai_buku_aset_terjual' => (float) $assetDisposalEntries->sum('nilai_buku_aset_terjual'),
                'laba_rugi_pelepasan_aset' => round($hasilPelepasanAset - $rugiPelepasanAset, 2),
                'penurunan_aset_tanpa_penjualan' => (float) $assetDisposalEntries->where('komponen', 'Beban penghapusan aset')->sum('nilai'),
                'total_aset' => round($totalAset, 2),
                'total_kewajiban' => round($totalKewajiban, 2),
                'ekuitas' => round($ekuitas, 2),
                'total_pasiva' => round($totalKewajiban + $ekuitas, 2),
                'simpanan_anggota' => (float) $rows->filter(fn(array $row) => ($row['akun']->kode_akun ?? null) === '2200')->sum(fn(array $row) => abs(min($row['net'], 0))),
                'bunga_diterima' => (float) $profitLoss['summary']['pendapatan_bunga'],
            ],
        ];
    }

    public function buildProfitLoss(int $koperasiId, Carbon $startDate, Carbon $endDate): array
    {
        $rows = JurnalUmum::query()
            ->with(['details.akun'])
            ->where('koperasi_id', $koperasiId)
            ->whereBetween('tanggal_jurnal', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereHas('details.akun', fn($query) => $query->whereIn('tipe_akun', [AkunKeuangan::TIPE_PENDAPATAN, AkunKeuangan::TIPE_BEBAN]))
            ->orderBy('tanggal_jurnal')
            ->orderBy('id')
            ->get()
            ->flatMap(function (JurnalUmum $journal) {
                return $journal->details
                    ->filter(fn($detail) => in_array($detail->akun?->tipe_akun, [AkunKeuangan::TIPE_PENDAPATAN, AkunKeuangan::TIPE_BEBAN], true))
                    ->map(function ($detail) use ($journal) {
                        $isRevenue = $detail->akun->tipe_akun === AkunKeuangan::TIPE_PENDAPATAN;
                        $nilai = $isRevenue
                            ? max(round((float) $detail->kredit - (float) $detail->debit, 2), 0)
                            : max(round((float) $detail->debit - (float) $detail->kredit, 2), 0);

                        if ($nilai <= 0) {
                            return null;
                        }

                        $sourceReference = (string) ($journal->sumber_referensi ?? '');
                        $komponen = $detail->akun->nama_akun;
                        $bookValue = 0.0;

                        if (str_starts_with($sourceReference, 'pinjaman-angsuran:')) {
                            $komponen = 'Pendapatan bunga pinjaman';
                        } elseif (str_starts_with($sourceReference, 'aset-pelepasan:')) {
                            $komponen = $isRevenue ? 'Laba pelepasan aset' : (str_contains(Str::lower($journal->keterangan ?? ''), 'tanpa penjualan') ? 'Beban penghapusan aset' : 'Rugi pelepasan aset');
                            preg_match('/nilai buku Rp ([\d\.,]+)/i', $journal->keterangan ?? '', $matches);
                            $bookValue = isset($matches[1]) ? (float) str_replace(['.', ','], ['', '.'], $matches[1]) : 0.0;
                        }

                        return [
                            'tanggal' => $journal->tanggal_jurnal,
                            'kategori' => $isRevenue ? 'Pendapatan' : 'Beban',
                            'komponen' => $komponen,
                            'referensi' => $sourceReference ?: $journal->no_jurnal,
                            'keterangan' => $journal->keterangan,
                            'nilai' => $nilai,
                            'nilai_buku_aset_terjual' => $bookValue,
                        ];
                    })
                    ->filter();
            })
            ->values();

        $totalPendapatan = (float) $rows->where('kategori', 'Pendapatan')->sum('nilai');
        $totalBeban = (float) $rows->where('kategori', 'Beban')->sum('nilai');

        return [
            'summary' => [
                'pendapatan_bunga' => (float) $rows->where('komponen', 'Pendapatan bunga pinjaman')->sum('nilai'),
                'laba_pelepasan_aset' => (float) $rows->where('komponen', 'Laba pelepasan aset')->sum('nilai'),
                'rugi_pelepasan_aset' => (float) $rows->where('komponen', 'Rugi pelepasan aset')->sum('nilai'),
                'beban_penghapusan_aset' => (float) $rows->where('komponen', 'Beban penghapusan aset')->sum('nilai'),
                'total_pendapatan' => $totalPendapatan,
                'total_beban' => $totalBeban,
                'laba_bersih' => round($totalPendapatan - $totalBeban, 2),
            ],
            'rows' => $rows,
        ];
    }

    public function getCashAccounts(int $koperasiId): Collection
    {
        return AkunKeuangan::query()
            ->where('koperasi_id', $koperasiId)
            ->where('status', AkunKeuangan::STATUS_AKTIF)
            ->where('can_post', true)
            ->where(function ($query) {
                $query->whereIn('kode_akun', ['1100', '1200'])
                    ->orWhere('nama_akun', 'like', '%Kas%')
                    ->orWhere('nama_akun', 'like', '%Bank%');
            })
            ->orderBy('kode_akun')
            ->get();
    }
}
