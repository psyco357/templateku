<?php

use App\Models\Angsuran;
use App\Models\AsetKoperasi;
use App\Models\Pinjaman;
use App\Models\ShuSkema;
use App\Models\ShuPayment;
use App\Models\Simpanan;
use App\Services\JournalPostingService;
use App\Services\ShuDistributionService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('keuangan:backfill-jurnal-otomatis', function (JournalPostingService $journalPostingService, ShuDistributionService $shuDistributionService) {
    $counts = [
        'simpanan' => 0,
        'pinjaman' => 0,
        'angsuran' => 0,
        'shu' => 0,
        'shu_payment' => 0,
        'aset' => 0,
    ];

    Simpanan::query()
        ->with(['anggota.profile', 'jenisSimpanan'])
        ->where('status', Simpanan::STATUS_POSTED)
        ->chunkById(100, function ($rows) use ($journalPostingService, &$counts) {
            foreach ($rows as $simpanan) {
                $journalPostingService->syncSimpanan($simpanan);
                $counts['simpanan']++;
            }
        });

    Pinjaman::query()
        ->with('anggota.profile')
        ->where('status', Pinjaman::STATUS_AKTIF)
        ->chunkById(100, function ($rows) use ($journalPostingService, &$counts) {
            foreach ($rows as $pinjaman) {
                $journalPostingService->syncPinjamanPencairan($pinjaman, $pinjaman->disetujui_oleh);
                $counts['pinjaman']++;
            }
        });

    Angsuran::query()
        ->with('pinjaman.anggota.profile')
        ->where('status', Angsuran::STATUS_DIBAYAR)
        ->chunkById(100, function ($rows) use ($journalPostingService, &$counts) {
            foreach ($rows as $angsuran) {
                if ($angsuran->pinjaman) {
                    $journalPostingService->syncAngsuran($angsuran->pinjaman, $angsuran);
                    $counts['angsuran']++;
                }
            }
        });

    ShuSkema::query()
        ->with('koperasi')
        ->chunkById(50, function ($rows) use ($journalPostingService, $shuDistributionService, &$counts) {
            foreach ($rows as $skema) {
                if (! $skema->koperasi) {
                    continue;
                }

                $context = $shuDistributionService->buildContext($skema->koperasi, (int) $skema->tahun, [
                    'cadangan' => (float) $skema->cadangan,
                    'jasa_modal' => (float) $skema->jasa_modal,
                    'jasa_usaha' => (float) $skema->jasa_usaha,
                    'dana_pengurus' => (float) $skema->dana_pengurus,
                    'dana_sosial' => (float) $skema->dana_sosial,
                ]);

                $journalPostingService->syncShuDistribution(
                    $skema,
                    (float) ($context['distributedTotals']['total_shu'] ?? 0),
                    $skema->user_id
                );
                $counts['shu']++;
            }
        });

    AsetKoperasi::query()
        ->whereNotNull('tanggal_perolehan')
        ->chunkById(100, function ($rows) use ($journalPostingService, &$counts) {
            foreach ($rows as $aset) {
                $journalPostingService->syncAsetPerolehan($aset);
                $journalPostingService->syncAsetDisposal($aset);
                $counts['aset']++;
            }
        });

    ShuPayment::query()
        ->with('anggota.profile')
        ->where('status', ShuPayment::STATUS_DIBAYAR)
        ->chunkById(100, function ($rows) use ($journalPostingService, &$counts) {
            foreach ($rows as $payment) {
                $journalPostingService->syncShuPayment($payment, $payment->created_by);
                $counts['shu_payment']++;
            }
        });

    $this->info('Backfill jurnal otomatis selesai.');
    foreach ($counts as $label => $total) {
        $this->line(sprintf('%s: %d', ucfirst($label), $total));
    }
})->purpose('Backfill jurnal otomatis dari transaksi yang sudah ada');
