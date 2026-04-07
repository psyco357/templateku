<?php

namespace App\Http\Controllers;

use App\Models\AnggotaModel;
use App\Models\Angsuran;
use App\Models\Koperasi;
use App\Models\ShuSkema;
use App\Models\ShuSkemaHistory;
use App\Models\ShuPayment;
use App\Models\Simpanan;
use App\Services\JournalPostingService;
use App\Services\ProfitLossReportService;
use App\Services\ShuDistributionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function __construct(
        protected ProfitLossReportService $profitLossReportService,
        protected JournalPostingService $journalPostingService,
        protected ShuDistributionService $shuDistributionService,
    ) {}

    public function perhitungan(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $year = $this->resolveYear($request);
        $savedScheme = $this->findScheme($koperasi, $year);
        [$percentages, $totalPercentage] = $this->resolvePercentages($request, $savedScheme);
        $context = $this->shuDistributionService->buildContext($koperasi, $year, $percentages);
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

        $scheme = null;

        \Illuminate\Support\Facades\DB::transaction(function () use ($koperasi, $year, $percentages, $request, $totalPercentage, $existingScheme, &$scheme) {
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

            $context = $this->shuDistributionService->buildContext($koperasi, $year, $percentages);
            $distributionAmount = (float) ($context['distributedTotals']['total_shu'] ?? 0);
            $this->journalPostingService->syncShuDistribution($scheme, $distributionAmount, $request->user()?->id);
        });

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
        $paymentDateMin = $this->resolveShuPaymentStartDate($year);
        $savedScheme = $this->findScheme($koperasi, $year);
        [$percentages, $totalPercentage] = $this->resolvePercentages($request, $savedScheme);
        $context = $this->shuDistributionService->buildContext($koperasi, $year, $percentages);
        $schemeHistory = $this->getSchemeHistory($koperasi, $year);
        $paymentSummary = $this->buildPaymentSummary($koperasi, $year, $context['memberRows']);

        return view('pages.shu.distribusi', $context + [
            'koperasi' => $koperasi,
            'year' => $year,
            'savedScheme' => $savedScheme,
            'schemeHistory' => $schemeHistory,
            'percentages' => $percentages,
            'totalPercentage' => $totalPercentage,
            'paymentDateMin' => $paymentDateMin,
            'paymentSummary' => $paymentSummary,
        ]);
    }

    public function bayarShu(Request $request): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $validated = $request->validate([
            'tahun' => ['required', 'integer', 'min:2020'],
            'anggota_id' => ['required', 'exists:anggota,id'],
            'tanggal_bayar' => ['required', 'date'],
            'jumlah_bayar' => ['required', 'numeric', 'gt:0'],
            'keterangan' => ['nullable', 'string'],
        ], [], [
            'tahun' => 'tahun',
            'anggota_id' => 'anggota',
            'tanggal_bayar' => 'tanggal bayar',
            'jumlah_bayar' => 'jumlah bayar',
            'keterangan' => 'keterangan',
        ]);

        $scheme = $this->findScheme($koperasi, (int) $validated['tahun']);

        if (! $scheme) {
            throw new HttpResponseException(
                redirect()->back()->withInput()->with([
                    'status' => 'Simpan skema SHU tahun ini terlebih dahulu sebelum mencatat pembayaran.',
                    'status_type' => 'warning',
                ])
            );
        }

        $paymentDateMin = $this->resolveShuPaymentStartDate((int) $validated['tahun']);

        if (Carbon::parse($validated['tanggal_bayar'])->lt(Carbon::parse($paymentDateMin))) {
            throw new HttpResponseException(
                redirect()->back()->withInput()->with([
                    'status' => 'Tanggal pembayaran SHU tidak boleh sebelum ' . Carbon::parse($paymentDateMin)->translatedFormat('d M Y') . ' karena kewajiban SHU baru diakui pada akhir tahun buku.',
                    'status_type' => 'warning',
                ])
            );
        }

        $percentages = [
            'cadangan' => (float) $scheme->cadangan,
            'jasa_modal' => (float) $scheme->jasa_modal,
            'jasa_usaha' => (float) $scheme->jasa_usaha,
            'dana_pengurus' => (float) $scheme->dana_pengurus,
            'dana_sosial' => (float) $scheme->dana_sosial,
        ];
        $context = $this->shuDistributionService->buildContext($koperasi, (int) $validated['tahun'], $percentages);
        $paymentSummary = $this->buildPaymentSummary($koperasi, (int) $validated['tahun'], $context['memberRows']);
        $memberPayment = $paymentSummary['members']->firstWhere('anggota_id', (int) $validated['anggota_id']);

        if (! $memberPayment || (float) $memberPayment['total_shu'] <= 0) {
            throw new HttpResponseException(
                redirect()->back()->withInput()->with([
                    'status' => 'Anggota tersebut tidak memiliki alokasi SHU yang bisa dibayarkan pada tahun ini.',
                    'status_type' => 'warning',
                ])
            );
        }

        if ((float) $validated['jumlah_bayar'] > (float) $memberPayment['sisa_bayar']) {
            throw new HttpResponseException(
                redirect()->back()->withInput()->with([
                    'status' => 'Jumlah bayar melebihi sisa kewajiban SHU anggota.',
                    'status_type' => 'warning',
                ])
            );
        }

        DB::transaction(function () use ($koperasi, $validated, $scheme, $request, &$payment) {
            $payment = ShuPayment::query()->create([
                'koperasi_id' => $koperasi->id,
                'shu_skema_id' => $scheme->id,
                'anggota_id' => $validated['anggota_id'],
                'periode_buku_id' => $this->resolvePeriodeBukuId($koperasi, $validated['tanggal_bayar']),
                'no_bukti' => $this->generateShuPaymentNumber($koperasi, $validated['tanggal_bayar']),
                'tahun' => $validated['tahun'],
                'tanggal_bayar' => $validated['tanggal_bayar'],
                'jumlah_bayar' => $validated['jumlah_bayar'],
                'status' => ShuPayment::STATUS_DIBAYAR,
                'keterangan' => $validated['keterangan'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            $payment->loadMissing('anggota.profile');
            $this->journalPostingService->syncShuPayment($payment, $request->user()?->id);
        });

        return redirect()->route('shu.distribusi', ['tahun' => $validated['tahun']])->with([
            'status' => 'Pembayaran SHU anggota berhasil dicatat.',
            'status_type' => 'success',
        ]);
    }

    public function exportDistribusiExcel(Request $request): Response
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $year = $this->resolveYear($request);
        $savedScheme = $this->findScheme($koperasi, $year);
        [$percentages] = $this->resolvePercentages($request, $savedScheme);
        $context = $this->shuDistributionService->buildContext($koperasi, $year, $percentages);

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
        $context = $this->shuDistributionService->buildContext($koperasi, $year, $percentages);

        $pdf = Pdf::loadView('pages.shu.exports.distribusi-pdf', $context + [
            'koperasi' => $koperasi,
            'year' => $year,
            'percentages' => $percentages,
            'printedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('distribusi-shu-' . $koperasi->id . '-' . $year . '.pdf');
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

    protected function buildPaymentSummary(Koperasi $koperasi, int $year, Collection $memberRows): array
    {
        $payments = ShuPayment::query()
            ->with(['anggota.profile', 'creator.profile'])
            ->where('koperasi_id', $koperasi->id)
            ->where('tahun', $year)
            ->orderByDesc('tanggal_bayar')
            ->orderByDesc('id')
            ->get();

        $paidByAnggota = $payments->groupBy('anggota_id')->map(fn(Collection $items) => (float) $items->sum('jumlah_bayar'));
        $members = $memberRows->map(function (array $row) use ($paidByAnggota) {
            $paid = (float) ($paidByAnggota[$row['anggota_id']] ?? 0);
            $remaining = max((float) $row['total_shu'] - $paid, 0);

            return $row + [
                'shu_terbayar' => $paid,
                'sisa_bayar' => $remaining,
            ];
        });

        return [
            'members' => $members,
            'payments' => $payments,
            'total_terbayar' => (float) $payments->sum('jumlah_bayar'),
            'total_sisa' => (float) $members->sum('sisa_bayar'),
        ];
    }

    protected function resolvePeriodeBukuId(Koperasi $koperasi, string $tanggal): ?int
    {
        return \App\Models\PeriodeBuku::query()
            ->where('koperasi_id', $koperasi->id)
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->value('id');
    }

    protected function generateShuPaymentNumber(Koperasi $koperasi, string $tanggal): string
    {
        $datePart = Carbon::parse($tanggal)->format('Ymd');
        $prefix = sprintf('SHU-%d-%s-', $koperasi->id, $datePart);

        $lastCode = ShuPayment::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('no_bukti', 'like', $prefix . '%')
            ->orderByDesc('no_bukti')
            ->value('no_bukti');

        $nextSequence = $lastCode ? ((int) \Illuminate\Support\Str::afterLast($lastCode, '-')) + 1 : 1;

        return $prefix . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }

    protected function resolveShuPaymentStartDate(int $year): string
    {
        return Carbon::create($year, 12, 31)->toDateString();
    }

    protected function resolveYear(Request $request): int
    {
        return max(2020, (int) $request->integer('tahun', (int) now()->year));
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
