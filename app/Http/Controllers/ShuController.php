<?php

namespace App\Http\Controllers;

use App\Models\AnggotaModel;
use App\Models\Angsuran;
use App\Models\Koperasi;
use App\Models\ShuSkema;
use App\Models\ShuSkemaHistory;
use App\Models\Simpanan;
use App\Services\ProfitLossReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ShuController extends Controller
{
    private const DEFAULT_PERCENTAGES = [
        'cadangan' => 25,
        'jasa_modal' => 35,
        'jasa_usaha' => 30,
        'dana_pengurus' => 5,
        'dana_sosial' => 5,
    ];

    public function __construct(protected ProfitLossReportService $profitLossReportService) {}

    public function perhitungan(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $year = $this->resolveYear($request);
        $savedScheme = $this->findScheme($koperasi, $year);
        [$percentages, $totalPercentage] = $this->resolvePercentages($request, $savedScheme);
        $context = $this->buildShuContext($koperasi, $year, $percentages);
        $schemeHistory = $this->getSchemeHistory($koperasi, $year);

        return view('pages.shu.perhitungan', $context + [
            'koperasi' => $koperasi,
            'year' => $year,
            'savedScheme' => $savedScheme,
            'schemeHistory' => $schemeHistory,
            'percentages' => $percentages,
            'totalPercentage' => $totalPercentage,
        ]);
    }

    public function simpanSkema(Request $request): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $year = $this->resolveYear($request);
        [$percentages, $totalPercentage] = $this->resolvePercentages($request);

        $existingScheme = $this->findScheme($koperasi, $year);

        $scheme = ShuSkema::query()->updateOrCreate(
            [
                'koperasi_id' => $koperasi->id,
                'tahun' => $year,
            ],
            $percentages + [
                'user_id' => $request->user()?->id,
                'total_persen' => $totalPercentage,
            ]
        );

        ShuSkemaHistory::query()->create($percentages + [
            'shu_skema_id' => $scheme->id,
            'koperasi_id' => $koperasi->id,
            'user_id' => $request->user()?->id,
            'tahun' => $year,
            'aksi' => $existingScheme ? 'update' : 'create',
            'total_persen' => $totalPercentage,
        ]);

        return redirect()
            ->route('shu.perhitungan', ['tahun' => $year])
            ->with([
                'status' => 'Skema SHU tahun ' . $year . ' berhasil disimpan dan akan dipakai sebagai default berikutnya.',
                'status_type' => 'success',
            ]);
    }

    public function distribusi(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $year = $this->resolveYear($request);
        $savedScheme = $this->findScheme($koperasi, $year);
        [$percentages, $totalPercentage] = $this->resolvePercentages($request, $savedScheme);
        $context = $this->buildShuContext($koperasi, $year, $percentages);
        $schemeHistory = $this->getSchemeHistory($koperasi, $year);

        return view('pages.shu.distribusi', $context + [
            'koperasi' => $koperasi,
            'year' => $year,
            'savedScheme' => $savedScheme,
            'schemeHistory' => $schemeHistory,
            'percentages' => $percentages,
            'totalPercentage' => $totalPercentage,
        ]);
    }

    public function exportDistribusiExcel(Request $request): Response
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $year = $this->resolveYear($request);
        $savedScheme = $this->findScheme($koperasi, $year);
        [$percentages] = $this->resolvePercentages($request, $savedScheme);
        $context = $this->buildShuContext($koperasi, $year, $percentages);

        $html = view('pages.shu.exports.distribusi-excel', $context + [
            'koperasi' => $koperasi,
            'year' => $year,
            'percentages' => $percentages,
            'printedAt' => now(),
        ])->render();

        return response(
            $html,
            200,
            $this->excelHeaders('distribusi-shu-' . $koperasi->id . '-' . $year . '.xls')
        );
    }

    public function exportDistribusiPdf(Request $request): Response
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $year = $this->resolveYear($request);
        $savedScheme = $this->findScheme($koperasi, $year);
        [$percentages] = $this->resolvePercentages($request, $savedScheme);
        $context = $this->buildShuContext($koperasi, $year, $percentages);

        $pdf = Pdf::loadView('pages.shu.exports.distribusi-pdf', $context + [
            'koperasi' => $koperasi,
            'year' => $year,
            'percentages' => $percentages,
            'printedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('distribusi-shu-' . $koperasi->id . '-' . $year . '.pdf');
    }

    protected function buildShuContext(Koperasi $koperasi, int $year, array $percentages): array
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

    protected function findScheme(Koperasi $koperasi, int $year): ?ShuSkema
    {
        return ShuSkema::query()
            ->with('user.profile')
            ->where('koperasi_id', $koperasi->id)
            ->where('tahun', $year)
            ->first();
    }

    protected function getSchemeHistory(Koperasi $koperasi, int $year): Collection
    {
        return ShuSkemaHistory::query()
            ->with('user.profile')
            ->where('koperasi_id', $koperasi->id)
            ->where('tahun', $year)
            ->latest('created_at')
            ->latest('id')
            ->limit(12)
            ->get();
    }

    protected function resolveYear(Request $request): int
    {
        return max(2020, (int) $request->integer('tahun', (int) now()->year));
    }

    protected function resolveYearRange(int $year): array
    {
        $startDate = Carbon::create($year, 1, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfYear();

        return [$startDate, $endDate];
    }

    protected function resolvePercentages(Request $request, ?ShuSkema $savedScheme = null): array
    {
        $validated = $request->validate([
            'tahun' => ['nullable', 'integer', 'min:2020'],
            'cadangan' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'jasa_modal' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'jasa_usaha' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'dana_pengurus' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'dana_sosial' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $schemeDefaults = $savedScheme
            ? [
                'cadangan' => (float) $savedScheme->cadangan,
                'jasa_modal' => (float) $savedScheme->jasa_modal,
                'jasa_usaha' => (float) $savedScheme->jasa_usaha,
                'dana_pengurus' => (float) $savedScheme->dana_pengurus,
                'dana_sosial' => (float) $savedScheme->dana_sosial,
            ]
            : self::DEFAULT_PERCENTAGES;

        $percentageKeys = array_keys(self::DEFAULT_PERCENTAGES);
        $hasInput = collect($percentageKeys)->contains(fn(string $key) => $request->has($key));

        $percentages = collect($schemeDefaults)
            ->map(fn(float|int $default, string $key) => (float) ($hasInput ? ($validated[$key] ?? $default) : $default))
            ->all();

        $totalPercentage = round(array_sum($percentages), 2);

        if (abs($totalPercentage - 100) > 0.01) {
            throw new HttpResponseException(
                redirect()
                    ->back()
                    ->withInput()
                    ->with([
                        'status' => 'Total persentase SHU harus tepat 100%.',
                        'status_type' => 'warning',
                    ])
            );
        }

        return [$percentages, $totalPercentage];
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

    protected function getActiveKoperasi(): ?Koperasi
    {
        return Auth::user()?->koperasi ?? Koperasi::query()->first();
    }
}
