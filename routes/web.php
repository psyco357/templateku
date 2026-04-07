<?php

use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AsetController;
use App\Helpers\LandingPageHelper;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KoperasiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PinjamanController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShuController;
use App\Http\Controllers\SimpananController;
use App\Models\Koperasi;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome', [
        'landingPage' => LandingPageHelper::build(Koperasi::query()->first()),
    ]);
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');

    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->name('register.store');

    Route::get('/password/reset', [PasswordController::class, 'request'])->name('password.request');
    Route::post('/password/email', [PasswordController::class, 'email'])->name('password.email');
    Route::get('/password/reset/{token}', [PasswordController::class, 'reset'])->name('password.reset');
    Route::post('/password/reset', [PasswordController::class, 'update'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    $modulePlaceholderPages = [
        [
            'uri' => '/anggota/kartu',
            'name' => 'anggota.kartu',
            'middleware' => 'role:anggota,pengurus,founder',
            'data' => [
                'title' => 'Kartu Anggota',
                'description' => 'Halaman ini disiapkan untuk cetak dan kelola kartu anggota koperasi.',
                'status' => 'Dalam pengembangan',
            ],
        ],
        [
            'uri' => '/anggota/riwayat-transaksi',
            'name' => 'anggota.riwayat-transaksi',
            'middleware' => 'role:anggota,pengurus,founder',
            'data' => [
                'title' => 'Riwayat Transaksi Anggota',
                'description' => 'Halaman ini disiapkan untuk melihat riwayat simpanan, pinjaman, dan transaksi toko per anggota.',
                'status' => 'Dalam pengembangan',
            ],
        ],
        [
            'uri' => '/toko/kasir',
            'name' => 'toko.kasir',
            'middleware' => 'role:pengurus,founder',
            'data' => [
                'title' => 'Kasir / POS',
                'description' => 'Halaman ini disiapkan untuk transaksi penjualan toko koperasi di kasir.',
                'status' => 'Dalam pengembangan',
            ],
        ],
        [
            'uri' => '/toko/stok',
            'name' => 'toko.stok',
            'middleware' => 'role:pengurus,founder',
            'data' => [
                'title' => 'Manajemen Stok Produk',
                'description' => 'Halaman ini disiapkan untuk kelola stok, produk, dan mutasi persediaan toko.',
                'status' => 'Dalam pengembangan',
            ],
        ],
        [
            'uri' => '/toko/laporan-penjualan',
            'name' => 'toko.laporan-penjualan',
            'middleware' => 'role:pengurus,founder',
            'data' => [
                'title' => 'Laporan Penjualan',
                'description' => 'Halaman ini disiapkan untuk laporan omzet dan penjualan toko koperasi.',
                'status' => 'Dalam pengembangan',
            ],
        ],
    ];

    foreach ($modulePlaceholderPages as $page) {
        Route::view($page['uri'], 'pages.placeholders.module', $page['data'])
            ->middleware($page['middleware'])
            ->name($page['name']);
    }

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/settings/profile', [SettingsController::class, 'editProfile'])
        ->name('settings.profile.edit');

    Route::patch('/settings/profile', [SettingsController::class, 'updateProfile'])
        ->name('settings.profile.update');

    Route::get('/settings/password', [SettingsController::class, 'editPassword'])
        ->name('settings.password.edit');

    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])
        ->name('settings.password.update');

    Route::get('/settings/appearance', [SettingsController::class, 'editAppearance'])
        ->middleware('role:pengurus,founder')
        ->name('settings.appearance');

    Route::put('/settings/appearance', [SettingsController::class, 'updateAppearance'])
        ->middleware('role:pengurus,founder')
        ->name('settings.appearance.update');

    Route::get('/simpanan/transaksi', [SimpananController::class, 'index'])
        ->middleware('role:pengurus,founder')
        ->name('simpanan.transaksi');

    Route::get('/simpanan/jenis', [SimpananController::class, 'masterJenis'])
        ->middleware('role:pengurus,founder')
        ->name('simpanan.jenis.index');

    Route::post('/simpanan/jenis', [SimpananController::class, 'storeJenis'])
        ->middleware('role:pengurus,founder')
        ->name('simpanan.jenis.store');

    Route::patch('/simpanan/jenis/{jenisSimpanan}', [SimpananController::class, 'updateJenis'])
        ->middleware('role:pengurus,founder')
        ->name('simpanan.jenis.update');

    Route::patch('/simpanan/jenis/{jenisSimpanan}/nonaktif', [SimpananController::class, 'deactivateJenis'])
        ->middleware('role:pengurus,founder')
        ->name('simpanan.jenis.deactivate');

    Route::delete('/simpanan/jenis/{jenisSimpanan}', [SimpananController::class, 'destroyJenis'])
        ->middleware('role:pengurus,founder')
        ->name('simpanan.jenis.destroy');

    Route::post('/simpanan/transaksi', [SimpananController::class, 'store'])
        ->middleware('role:pengurus,founder')
        ->name('simpanan.store');

    Route::get('/simpanan/transaksi/{simpanan}', [SimpananController::class, 'show'])
        ->middleware('role:pengurus,founder')
        ->name('simpanan.show');

    Route::get('/simpanan/transaksi/{simpanan}/cetak', [SimpananController::class, 'printReceipt'])
        ->middleware('role:pengurus,founder')
        ->name('simpanan.print');

    Route::patch('/simpanan/transaksi/{simpanan}', [SimpananController::class, 'update'])
        ->middleware('role:pengurus,founder')
        ->name('simpanan.update');

    Route::patch('/simpanan/transaksi/{simpanan}/batal', [SimpananController::class, 'cancel'])
        ->middleware('role:pengurus,founder')
        ->name('simpanan.cancel');

    Route::get('/simpanan/rekap-saldo', [SimpananController::class, 'recap'])
        ->middleware('role:anggota,pengurus,founder')
        ->name('simpanan.rekap-saldo');

    Route::get('/simpanan/rekap-saldo/export', [SimpananController::class, 'exportRecap'])
        ->middleware('role:anggota,pengurus,founder')
        ->name('simpanan.rekap-saldo.export');

    Route::get('/simpanan/mutasi', [SimpananController::class, 'mutation'])
        ->middleware('role:anggota,pengurus,founder')
        ->name('simpanan.mutasi');

    Route::get('/simpanan/saya', [SimpananController::class, 'mutation'])
        ->middleware('role:anggota')
        ->name('simpanan.mine');

    Route::get('/simpanan/mutasi/export', [SimpananController::class, 'exportMutation'])
        ->middleware('role:anggota,pengurus,founder')
        ->name('simpanan.mutasi.export');

    Route::get('/simpanan/mutasi/cetak', [SimpananController::class, 'printMutation'])
        ->middleware('role:anggota,pengurus,founder')
        ->name('simpanan.mutasi.print');

    Route::get('/pinjaman/pengajuan', [PinjamanController::class, 'pengajuan'])
        ->middleware('role:anggota,pengurus,founder')
        ->name('pinjaman.pengajuan');

    Route::post('/pinjaman/pengajuan', [PinjamanController::class, 'store'])
        ->middleware('role:anggota,pengurus,founder')
        ->name('pinjaman.store');

    Route::patch('/pinjaman/{pinjaman}/approve', [PinjamanController::class, 'approve'])
        ->middleware('role:pengurus,founder')
        ->name('pinjaman.approve');

    Route::patch('/pinjaman/{pinjaman}/reject', [PinjamanController::class, 'reject'])
        ->middleware('role:pengurus,founder')
        ->name('pinjaman.reject');

    Route::get('/pinjaman/simulasi', [PinjamanController::class, 'simulasi'])
        ->middleware('role:anggota,pengurus,founder')
        ->name('pinjaman.simulasi');

    Route::get('/pinjaman/jadwal-angsuran', [PinjamanController::class, 'jadwalAngsuran'])
        ->middleware('role:anggota,pengurus,founder')
        ->name('pinjaman.jadwal-angsuran');

    Route::get('/pinjaman/status-pembayaran', [PinjamanController::class, 'statusPembayaran'])
        ->middleware('role:anggota,pengurus,founder')
        ->name('pinjaman.status-pembayaran');

    Route::post('/pinjaman/{pinjaman}/bayar', [PinjamanController::class, 'bayarAngsuran'])
        ->middleware('role:anggota,pengurus,founder')
        ->name('pinjaman.bayar');

    Route::get('/shu/perhitungan', [ShuController::class, 'perhitungan'])
        ->middleware('role:founder')
        ->name('shu.perhitungan');

    Route::post('/shu/perhitungan/simpan', [ShuController::class, 'simpanSkema'])
        ->middleware('role:founder')
        ->name('shu.simpan-skema');

    Route::get('/shu/distribusi', [ShuController::class, 'distribusi'])
        ->middleware('role:founder')
        ->name('shu.distribusi');

    Route::get('/shu/distribusi/export/excel', [ShuController::class, 'exportDistribusiExcel'])
        ->middleware('role:founder')
        ->name('shu.distribusi.export-excel');

    Route::get('/shu/distribusi/export/pdf', [ShuController::class, 'exportDistribusiPdf'])
        ->middleware('role:founder')
        ->name('shu.distribusi.export-pdf');

    Route::get('/laporan/neraca-keuangan', [LaporanController::class, 'neracaKeuangan'])
        ->middleware('role:pengurus,founder')
        ->name('laporan.neraca-keuangan');

    Route::get('/laporan/arus-kas', [LaporanController::class, 'arusKas'])
        ->middleware('role:pengurus,founder')
        ->name('laporan.arus-kas');

    Route::get('/laporan/rugi-laba', [LaporanController::class, 'rugiLaba'])
        ->middleware('role:pengurus,founder')
        ->name('laporan.rugi-laba');

    Route::get('/laporan/rugi-laba-tahunan', [LaporanController::class, 'rugiLabaTahunan'])
        ->middleware('role:founder')
        ->name('laporan.rugi-laba-tahunan');

    Route::get('/laporan/tunggakan-pinjaman', [LaporanController::class, 'tunggakanPinjaman'])
        ->middleware('role:pengurus,founder')
        ->name('laporan.tunggakan-pinjaman');

    Route::get('/laporan/rat', [LaporanController::class, 'rat'])
        ->middleware('role:pengurus,founder')
        ->name('laporan.rat');

    Route::get('/aset', [AsetController::class, 'index'])
        ->middleware('role:pengurus,founder')
        ->name('aset.index');

    Route::post('/aset', [AsetController::class, 'store'])
        ->middleware('role:pengurus,founder')
        ->name('aset.store');

    Route::patch('/aset/{aset}', [AsetController::class, 'update'])
        ->middleware('role:pengurus,founder')
        ->name('aset.update');

    Route::patch('/aset/{aset}/nonaktif', [AsetController::class, 'deactivate'])
        ->middleware('role:pengurus,founder')
        ->name('aset.deactivate');

    Route::get('/anggota', [AnggotaController::class, 'index'])
        ->middleware('role:pengurus,founder')
        ->name('anggota.index');

    Route::get('/anggota/create', [AnggotaController::class, 'create'])
        ->middleware('role:pengurus,founder')
        ->name('anggota.create');

    Route::get('/anggota/generate-member-number', [AnggotaController::class, 'generateMemberNumberResponse'])
        ->middleware('role:pengurus,founder')
        ->name('anggota.generate-member-number');

    Route::post('/anggota', [AnggotaController::class, 'store'])
        ->middleware('role:pengurus,founder')
        ->name('anggota.store');

    Route::get('/anggota/{user}', [AnggotaController::class, 'show'])
        ->middleware('role:pengurus,founder')
        ->name('anggota.show');

    Route::patch('/anggota/{user}', [AnggotaController::class, 'update'])
        ->middleware('role:pengurus,founder')
        ->name('anggota.update');

    Route::patch('/anggota/{user}/role', [AnggotaController::class, 'updateRole'])
        ->middleware('role:founder')
        ->name('anggota.update-role');

    Route::get('/koperasi', [KoperasiController::class, 'show'])
        ->middleware('role:founder')
        ->name('koperasi.show');

    Route::put('/koperasi', [KoperasiController::class, 'update'])
        ->middleware('role:founder')
        ->name('koperasi.update');

    Route::post('/koperasi/periode-buku/generate', [KoperasiController::class, 'generatePeriodeBuku'])
        ->middleware('role:founder')
        ->name('koperasi.periode-buku.generate');

    Route::get('/accounts', [AccountController::class, 'index'])
        ->middleware('role:founder')
        ->name('accounts.index');

    Route::get('/accounts/{user}', [AccountController::class, 'show'])
        ->middleware('role:founder')
        ->name('accounts.show');

    Route::patch('/accounts/{user}', [AccountController::class, 'update'])
        ->middleware('role:founder')
        ->name('accounts.update');

    Route::patch('/accounts/{user}/status', [AccountController::class, 'toggleStatus'])
        ->middleware('role:founder')
        ->name('accounts.toggle-status');

    Route::patch('/accounts/{user}/reset-password', [AccountController::class, 'resetPassword'])
        ->middleware('role:founder')
        ->name('accounts.reset-password');
});
