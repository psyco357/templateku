<?php

namespace App\Http\Controllers;

use App\Models\AkunKeuangan;
use App\Models\JurnalUmum;
use App\Models\Koperasi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KeuanganBukuBesarController extends Controller
{
    public function index(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $filters = $this->resolveDateFilters($request);
        $akunOptions = AkunKeuangan::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('status', AkunKeuangan::STATUS_AKTIF)
            ->where('can_post', true)
            ->orderBy('kode_akun')
            ->get();

        $selectedAkunId = $request->integer('akun_keuangan_id');
        $selectedAkun = $selectedAkunId
            ? $akunOptions->firstWhere('id', $selectedAkunId)
            : $akunOptions->first();

        $entries = collect();
        $summary = [
            'saldo_awal' => 0.0,
            'total_debit' => 0.0,
            'total_kredit' => 0.0,
            'saldo_akhir' => 0.0,
        ];

        if ($selectedAkun) {
            $baseQuery = JurnalUmum::query()
                ->with(['details' => fn($query) => $query->where('akun_keuangan_id', $selectedAkun->id), 'poster.profile'])
                ->where('koperasi_id', $koperasi->id)
                ->whereHas('details', fn($query) => $query->where('akun_keuangan_id', $selectedAkun->id));

            $openingBalance = (float) JurnalUmum::query()
                ->join('jurnal_umum_details', 'jurnal_umum_details.jurnal_umum_id', '=', 'jurnal_umum.id')
                ->where('jurnal_umum.koperasi_id', $koperasi->id)
                ->where('jurnal_umum_details.akun_keuangan_id', $selectedAkun->id)
                ->whereDate('jurnal_umum.tanggal_jurnal', '<', $filters['tanggal_mulai']->toDateString())
                ->selectRaw('COALESCE(SUM(jurnal_umum_details.debit - jurnal_umum_details.kredit), 0) as saldo')
                ->value('saldo');

            $rows = $baseQuery
                ->whereBetween('tanggal_jurnal', [$filters['tanggal_mulai']->toDateString(), $filters['tanggal_selesai']->toDateString()])
                ->orderBy('tanggal_jurnal')
                ->orderBy('id')
                ->get();

            $runningBalance = $openingBalance;

            $entries = $rows->map(function (JurnalUmum $jurnal) use (&$runningBalance, $selectedAkun) {
                $detail = $jurnal->details->firstWhere('akun_keuangan_id', $selectedAkun->id);
                $debit = (float) ($detail?->debit ?? 0);
                $kredit = (float) ($detail?->kredit ?? 0);
                $runningBalance += $debit - $kredit;

                return [
                    'tanggal' => $jurnal->tanggal_jurnal,
                    'no_jurnal' => $jurnal->no_jurnal,
                    'jenis_jurnal' => $jurnal->jenis_jurnal,
                    'sumber_referensi' => $jurnal->sumber_referensi,
                    'keterangan' => $jurnal->keterangan,
                    'debit' => $debit,
                    'kredit' => $kredit,
                    'saldo' => $runningBalance,
                ];
            });

            $summary = [
                'saldo_awal' => $openingBalance,
                'total_debit' => (float) $entries->sum('debit'),
                'total_kredit' => (float) $entries->sum('kredit'),
                'saldo_akhir' => $runningBalance,
            ];
        }

        return view('pages.keuangan.buku-besar.index', [
            'koperasi' => $koperasi,
            'akunOptions' => $akunOptions,
            'selectedAkun' => $selectedAkun,
            'entries' => $entries,
            'summary' => $summary,
            'filters' => $filters,
        ]);
    }

    public function neracaSaldo(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $filters = $this->resolveDateFilters($request, false);

        $accounts = AkunKeuangan::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('status', AkunKeuangan::STATUS_AKTIF)
            ->where('can_post', true)
            ->orderBy('kode_akun')
            ->get();

        $balances = JurnalUmum::query()
            ->join('jurnal_umum_details', 'jurnal_umum_details.jurnal_umum_id', '=', 'jurnal_umum.id')
            ->where('jurnal_umum.koperasi_id', $koperasi->id)
            ->whereDate('jurnal_umum.tanggal_jurnal', '<=', $filters['tanggal_selesai']->toDateString())
            ->groupBy('jurnal_umum_details.akun_keuangan_id')
            ->selectRaw('jurnal_umum_details.akun_keuangan_id, COALESCE(SUM(jurnal_umum_details.debit), 0) as total_debit, COALESCE(SUM(jurnal_umum_details.kredit), 0) as total_kredit')
            ->get()
            ->keyBy('akun_keuangan_id');

        $rows = $accounts->map(function (AkunKeuangan $akun) use ($balances) {
            $row = $balances->get($akun->id);
            $debit = (float) ($row->total_debit ?? 0);
            $kredit = (float) ($row->total_kredit ?? 0);
            $net = round($debit - $kredit, 2);

            return [
                'akun' => $akun,
                'total_debit' => $debit,
                'total_kredit' => $kredit,
                'saldo_debit' => $net > 0 ? $net : 0,
                'saldo_kredit' => $net < 0 ? abs($net) : 0,
            ];
        })->filter(fn(array $row) => $row['total_debit'] > 0 || $row['total_kredit'] > 0 || $row['saldo_debit'] > 0 || $row['saldo_kredit'] > 0)->values();

        return view('pages.keuangan.neraca-saldo.index', [
            'koperasi' => $koperasi,
            'filters' => $filters,
            'rows' => $rows,
            'summary' => [
                'total_debit' => (float) $rows->sum('total_debit'),
                'total_kredit' => (float) $rows->sum('total_kredit'),
                'saldo_debit' => (float) $rows->sum('saldo_debit'),
                'saldo_kredit' => (float) $rows->sum('saldo_kredit'),
            ],
        ]);
    }

    protected function resolveDateFilters(Request $request, bool $withStartDate = true): array
    {
        $startDate = $withStartDate
            ? ($request->date('tanggal_mulai') ? Carbon::parse($request->date('tanggal_mulai'))->startOfDay() : now()->startOfMonth())
            : now()->startOfMonth();
        $endDate = $request->date('tanggal_selesai')
            ? Carbon::parse($request->date('tanggal_selesai'))->endOfDay()
            : now()->endOfMonth();

        if ($withStartDate && $startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        return [
            'tanggal_mulai' => $startDate,
            'tanggal_selesai' => $endDate,
        ];
    }

    protected function getActiveKoperasi(): ?Koperasi
    {
        return Auth::user()?->koperasi ?? Koperasi::query()->first();
    }
}
