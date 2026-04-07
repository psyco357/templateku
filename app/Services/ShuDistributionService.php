<?php

namespace App\Services;

use App\Models\AnggotaModel;
use App\Models\Angsuran;
use App\Models\Koperasi;
use App\Models\Simpanan;
use Illuminate\Support\Carbon;

class ShuDistributionService
{
    public function __construct(protected ProfitLossReportService $profitLossReportService) {}

    public function buildContext(Koperasi $koperasi, int $year, array $percentages): array
    {
        [$startDate, $endDate] = $this->resolveYearRange($year);
        $report = $this->profitLossReportService->build($koperasi->id, $startDate, $endDate);
        $shuDasar = max((int) round((float) $report['summary']['laba_bersih'], 0), 0);
        $allocation = $this->allocateExactAmount($shuDasar, $percentages);
        [$memberRows, $weightSummary, $distributedTotals] = $this->buildMemberDistribution($koperasi, $startDate, $endDate, $allocation);

        return [
            'summary' => array_merge($report['summary'], [
                'shu_dasar' => $shuDasar,
                'shu_status' => $shuDasar > 0 ? 'siap-distribusikan' : 'belum-tersedia',
                'anggota_penerima' => $memberRows->filter(fn(array $row) => $row['total_shu'] > 0)->count(),
            ]),
            'allocation' => $allocation,
            'memberRows' => $memberRows,
            'weightSummary' => $weightSummary,
            'distributedTotals' => $distributedTotals,
            'yearlyRows' => $report['rows'],
        ];
    }

    protected function buildMemberDistribution(Koperasi $koperasi, Carbon $startDate, Carbon $endDate, array $allocation): array
    {
        $anggota = AnggotaModel::query()
            ->with('profile.user')
            ->whereHas('profile.user', fn($query) => $query->where('koperasi_id', $koperasi->id))
            ->get();

        $simpananByAnggota = Simpanan::query()
            ->selectRaw('anggota_id, SUM(jumlah) as total_simpanan')
            ->where('koperasi_id', $koperasi->id)
            ->where('status', Simpanan::STATUS_POSTED)
            ->whereBetween('tanggal_transaksi', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('anggota_id')
            ->get()
            ->keyBy('anggota_id');

        $usahaByAnggota = Angsuran::query()
            ->join('pinjaman', 'pinjaman.id', '=', 'angsuran.pinjaman_id')
            ->selectRaw('pinjaman.anggota_id as anggota_id, SUM(angsuran.bunga) as total_jasa_usaha')
            ->where('angsuran.koperasi_id', $koperasi->id)
            ->where('angsuran.status', Angsuran::STATUS_DIBAYAR)
            ->whereBetween('angsuran.tanggal_bayar', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('pinjaman.anggota_id')
            ->get()
            ->keyBy('anggota_id');

        $memberBaseRows = $anggota->map(function (AnggotaModel $anggotaItem) use ($simpananByAnggota, $usahaByAnggota) {
            return [
                'anggota_id' => $anggotaItem->id,
                'no_anggota' => $anggotaItem->no_anggota,
                'nama' => $anggotaItem->profile?->nama_lengkap ?? '-',
                'status' => $anggotaItem->status,
                'total_simpanan' => max((float) ($simpananByAnggota->get($anggotaItem->id)->total_simpanan ?? 0), 0),
                'total_jasa_usaha' => max((float) ($usahaByAnggota->get($anggotaItem->id)->total_jasa_usaha ?? 0), 0),
            ];
        })->values();

        $modalAllocation = $this->allocateExactAmountByWeights(
            (int) ($allocation['jasa_modal'] ?? 0),
            $memberBaseRows->pluck('total_simpanan', 'anggota_id')->all()
        );
        $usahaAllocation = $this->allocateExactAmountByWeights(
            (int) ($allocation['jasa_usaha'] ?? 0),
            $memberBaseRows->pluck('total_jasa_usaha', 'anggota_id')->all()
        );

        $memberRows = $memberBaseRows->map(function (array $row) use ($modalAllocation, $usahaAllocation) {
            $modal = (int) ($modalAllocation['amounts'][$row['anggota_id']] ?? 0);
            $usaha = (int) ($usahaAllocation['amounts'][$row['anggota_id']] ?? 0);
            $adjustment = (int) ($modalAllocation['extra_units'][$row['anggota_id']] ?? 0) + (int) ($usahaAllocation['extra_units'][$row['anggota_id']] ?? 0);

            return $row + [
                'bagian_modal' => $modal,
                'bagian_usaha' => $usaha,
                'penyesuaian_pembulatan' => $adjustment,
                'total_shu' => $modal + $usaha,
            ];
        })->sortByDesc('total_shu')->values();

        return [
            $memberRows,
            [
                'total_simpanan' => (float) $memberBaseRows->sum('total_simpanan'),
                'total_jasa_usaha' => (float) $memberBaseRows->sum('total_jasa_usaha'),
            ],
            [
                'bagian_modal' => (int) $memberRows->sum('bagian_modal'),
                'bagian_usaha' => (int) $memberRows->sum('bagian_usaha'),
                'total_shu' => (int) $memberRows->sum('total_shu'),
                'penyesuaian_pembulatan' => (int) $memberRows->sum('penyesuaian_pembulatan'),
            ],
        ];
    }

    protected function allocateExactAmount(int $totalAmount, array $weights): array
    {
        $result = $this->allocateExactAmountByWeights($totalAmount, $weights);

        return $result['amounts'];
    }

    protected function allocateExactAmountByWeights(int $totalAmount, array $weights): array
    {
        $normalizedWeights = collect($weights)
            ->map(fn($weight) => max((float) $weight, 0))
            ->all();
        $weightTotal = array_sum($normalizedWeights);
        $amounts = [];
        $extraUnits = [];
        $fractions = [];

        foreach ($normalizedWeights as $key => $weight) {
            if ($totalAmount <= 0 || $weightTotal <= 0 || $weight <= 0) {
                $amounts[$key] = 0;
                $extraUnits[$key] = 0;
                $fractions[$key] = 0.0;
                continue;
            }

            $rawShare = ($totalAmount * $weight) / $weightTotal;
            $floorShare = (int) floor($rawShare);
            $amounts[$key] = $floorShare;
            $extraUnits[$key] = 0;
            $fractions[$key] = $rawShare - $floorShare;
        }

        $remaining = $totalAmount - array_sum($amounts);

        if ($remaining > 0) {
            $orderedKeys = collect(array_keys($normalizedWeights))
                ->sortByDesc(fn($key) => sprintf('%0.12f-%0.12f-%s', $fractions[$key], $normalizedWeights[$key], (string) $key))
                ->values()
                ->all();

            $index = 0;
            $orderedCount = count($orderedKeys);

            while ($remaining > 0 && $orderedCount > 0) {
                $key = $orderedKeys[$index % $orderedCount];
                if (($normalizedWeights[$key] ?? 0) > 0) {
                    $amounts[$key]++;
                    $extraUnits[$key]++;
                    $remaining--;
                }
                $index++;
            }
        }

        foreach ($normalizedWeights as $key => $_weight) {
            $amounts[$key] = (int) ($amounts[$key] ?? 0);
            $extraUnits[$key] = (int) ($extraUnits[$key] ?? 0);
        }

        return [
            'amounts' => $amounts,
            'extra_units' => $extraUnits,
        ];
    }

    protected function resolveYearRange(int $year): array
    {
        $startDate = Carbon::create($year, 1, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfYear();

        return [$startDate, $endDate];
    }
}
