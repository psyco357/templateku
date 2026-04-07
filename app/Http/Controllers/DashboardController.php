<?php

namespace App\Http\Controllers;

use App\Models\AnggotaModel;
use App\Models\JenisSimpanan;
use App\Models\Koperasi;
use App\Models\Simpanan;
use App\Models\ShuSkema;
use App\Models\ShuSkemaHistory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $koperasi = $this->getActiveKoperasi();
        $currentMonth = Carbon::now();
        $user = Auth::user();

        $anggotaAktif = 0;
        $totalSimpanan = 0;
        $totalPinjamanBerjalan = 0;
        $omzetTokoBulanIni = 0;
        $transaksiSimpananTerbaru = collect();
        $ringkasanJenisSimpanan = collect();
        $latestShuSchemes = collect();
        $shuSchemeHistory = collect();
        $isFounder = $user instanceof User && $user->hasRole(User::ROLE_FOUNDER);

        if ($koperasi) {
            $anggotaAktif = AnggotaModel::query()
                ->where('status', AnggotaModel::STATUS_AKTIF)
                ->whereHas('profile.user', function ($query) use ($koperasi) {
                    $query
                        ->where('koperasi_id', $koperasi->id)
                        ->where('is_active', true);
                })
                ->count();

            $totalSimpanan = (float) Simpanan::query()
                ->where('koperasi_id', $koperasi->id)
                ->where('status', 'posted')
                ->sum('jumlah');

            $totalPinjamanBerjalan = (float) DB::table('pinjaman')
                ->where('koperasi_id', $koperasi->id)
                ->whereNotIn('status', ['lunas', 'ditolak', 'batal'])
                ->sum('jumlah_pinjaman');

            $omzetTokoBulanIni = (float) DB::table('transaksi_toko')
                ->where('koperasi_id', $koperasi->id)
                ->where('status', 'selesai')
                ->whereBetween('tanggal_transaksi', [
                    $currentMonth->copy()->startOfMonth()->toDateString(),
                    $currentMonth->copy()->endOfMonth()->toDateString(),
                ])
                ->sum('total');

            $transaksiSimpananTerbaru = Simpanan::query()
                ->with(['anggota.profile.user', 'jenisSimpanan'])
                ->where('koperasi_id', $koperasi->id)
                ->latest('tanggal_transaksi')
                ->latest('id')
                ->limit(8)
                ->get();

            $ringkasanJenisSimpanan = JenisSimpanan::query()
                ->leftJoin('simpanan', function ($join) use ($koperasi) {
                    $join->on('simpanan.jenis_simpanan_id', '=', 'jenis_simpanan.id')
                        ->where('simpanan.koperasi_id', '=', $koperasi->id)
                        ->where('simpanan.status', '=', 'posted');
                })
                ->where('jenis_simpanan.koperasi_id', $koperasi->id)
                ->groupBy('jenis_simpanan.id', 'jenis_simpanan.nama_jenis', 'jenis_simpanan.kode_jenis')
                ->orderBy('jenis_simpanan.nama_jenis')
                ->get([
                    'jenis_simpanan.id',
                    'jenis_simpanan.nama_jenis',
                    'jenis_simpanan.kode_jenis',
                    DB::raw('COALESCE(SUM(simpanan.jumlah), 0) as total_saldo'),
                ]);

            if ($isFounder) {
                $latestShuSchemes = ShuSkema::query()
                    ->with('user.profile')
                    ->where('koperasi_id', $koperasi->id)
                    ->latest('tahun')
                    ->limit(3)
                    ->get();

                $shuSchemeHistory = ShuSkemaHistory::query()
                    ->with('user.profile')
                    ->where('koperasi_id', $koperasi->id)
                    ->latest('created_at')
                    ->latest('id')
                    ->limit(5)
                    ->get();
            }
        }

        return view('pages.beranda.index', [
            'koperasi' => $koperasi,
            'anggotaAktif' => $anggotaAktif,
            'totalSimpanan' => $totalSimpanan,
            'totalPinjamanBerjalan' => $totalPinjamanBerjalan,
            'omzetTokoBulanIni' => $omzetTokoBulanIni,
            'transaksiSimpananTerbaru' => $transaksiSimpananTerbaru,
            'ringkasanJenisSimpanan' => $ringkasanJenisSimpanan,
            'latestShuSchemes' => $latestShuSchemes,
            'shuSchemeHistory' => $shuSchemeHistory,
            'isFounder' => $isFounder,
            'bulanBerjalanLabel' => $currentMonth->translatedFormat('F Y'),
        ]);
    }

    protected function getActiveKoperasi(): ?Koperasi
    {
        return Auth::user()?->koperasi ?? Koperasi::query()->first();
    }
}
