<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class MenuHelper
{
    public static function getMainNavItems()
    {
        return [
            [
                'icon' => 'dashboard',
                'name' => 'Dashboard Utama',
                'path' => '/dashboard',
                'roles' => User::roles(),
            ],
            [
                'icon' => 'users',
                'name' => 'Modul Anggota',
                'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER],
                'subItems' => [
                    ['name' => 'Daftar Anggota', 'path' => '/anggota', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                    ['name' => 'Form Pendaftaran', 'path' => '/anggota/create', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                    ['name' => 'Kartu Anggota', 'path' => '/anggota/kartu', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                    ['name' => 'Riwayat Transaksi', 'path' => '/anggota/riwayat-transaksi', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                ],
            ],
            [
                'icon' => 'users',
                'name' => 'Kartu Anggota',
                'path' => '/anggota/kartu',
                'roles' => [User::ROLE_ANGGOTA],
            ],
            [
                'icon' => 'savings',
                'name' => 'Simpanan Saya',
                'path' => '/simpanan/mutasi',
                'roles' => [User::ROLE_ANGGOTA],
            ],
            [
                'icon' => 'loan',
                'name' => 'Pinjaman Saya',
                'path' => '/pinjaman/status-pembayaran',
                'roles' => [User::ROLE_ANGGOTA],
            ],
            [
                'icon' => 'savings',
                'name' => 'Modul Simpanan',
                'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER],
                'subItems' => [
                    ['name' => 'Master Jenis Simpanan', 'path' => '/simpanan/jenis', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                    ['name' => 'Setoran / Tarik Simpanan', 'path' => '/simpanan/transaksi', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                    ['name' => 'Rekap Saldo Simpanan', 'path' => '/simpanan/rekap-saldo', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                    ['name' => 'Buku Simpanan Anggota', 'path' => '/simpanan/mutasi', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                ],
            ],
            [
                'icon' => 'loan',
                'name' => 'Modul Pinjaman',
                'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER],
                'subItems' => [
                    ['name' => 'Pengajuan Pinjaman', 'path' => '/pinjaman/pengajuan', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                    ['name' => 'Simulasi Cicilan', 'path' => '/pinjaman/simulasi', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                    ['name' => 'Jadwal Angsuran', 'path' => '/pinjaman/jadwal-angsuran', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                    ['name' => 'Status Pembayaran', 'path' => '/pinjaman/status-pembayaran', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                ],
            ],
        ];
    }

    public static function getOthersItems()
    {
        return [
            [
                'icon' => 'store',
                'name' => 'Modul Toko',
                'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER],
                'subItems' => [
                    ['name' => 'Kasir / POS', 'path' => '/toko/kasir', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                    ['name' => 'Manajemen Stok Produk', 'path' => '/toko/stok', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                    ['name' => 'Laporan Penjualan', 'path' => '/toko/laporan-penjualan', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                ],
            ],
            [
                'icon' => 'shu',
                'name' => 'Modul SHU',
                'roles' => [User::ROLE_FOUNDER],
                'subItems' => [
                    ['name' => 'Perhitungan SHU Tahunan', 'path' => '/shu/perhitungan', 'pro' => false, 'roles' => [User::ROLE_FOUNDER]],
                    ['name' => 'Distribusi SHU Anggota', 'path' => '/shu/distribusi', 'pro' => false, 'roles' => [User::ROLE_FOUNDER]],
                ],
            ],
            [
                'icon' => 'report',
                'name' => 'Laporan',
                'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER],
                'subItems' => [
                    ['name' => 'Neraca Keuangan', 'path' => '/laporan/neraca-keuangan', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                    ['name' => 'Arus Kas', 'path' => '/laporan/arus-kas', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                    ['name' => 'Rugi Laba', 'path' => '/laporan/rugi-laba', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                    ['name' => 'Rugi Laba Tahunan', 'path' => '/laporan/rugi-laba-tahunan', 'pro' => false, 'roles' => [User::ROLE_FOUNDER]],
                    ['name' => 'Tunggakan Pinjaman', 'path' => '/laporan/tunggakan-pinjaman', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                    ['name' => 'Rekap RAT', 'path' => '/laporan/rat', 'pro' => false, 'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER]],
                ],
            ],
            [
                'icon' => 'asset',
                'name' => 'Aset Koperasi',
                'path' => '/aset',
                'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER],
            ],
        ];
    }

    public static function getConfig()
    {
        return [
            [
                'icon' => 'calendar',
                'name' => 'Management Users',
                // 'path' => '/users',
                'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER],
                'subItems' => [

                    ['name' => 'Daftar Akun', 'path' => '/accounts', 'pro' => false, 'roles' => [User::ROLE_FOUNDER]],
                ],
            ],
            [
                'icon' => 'configuration',
                'name' => 'Master Koperasi',
                'path' => '/koperasi',
                'roles' => [User::ROLE_FOUNDER],
            ],
            [
                'icon' => 'configuration',
                'name' => 'Settings',
                'roles' => User::roles(),
                'subItems' => [
                    [
                        'name' => 'Profile',
                        'path' => '/settings/profile',
                        'icon' => 'user-profile',
                        'pro' => false,
                        'roles' => User::roles(),
                    ],
                    [
                        'name' => 'Password',
                        'path' => '/settings/password',
                        'icon' => 'authentication',
                        'pro' => false,
                        'roles' => User::roles(),
                    ],
                    [
                        'name' => 'Appearance',
                        'path' => '/settings/appearance',
                        'icon' => 'configuration',
                        'pro' => false,
                        'roles' => [User::ROLE_PENGURUS, User::ROLE_FOUNDER],
                    ],
                ],
            ],
            [
                'icon' => 'logout',
                'name' => 'Logout',
                'path' => '/logout',
                'roles' => User::roles(),
            ]

        ];
    }

    public static function getMenuGroups()
    {
        $user = Auth::user();

        return array_values(array_filter([
            [
                'title' => 'Menu',
                'items' => self::filterMenuItems(self::getMainNavItems(), $user)
            ],
            [
                'title' => 'Others',
                'items' => self::filterMenuItems(self::getOthersItems(), $user)
            ],
            [
                'title' => 'Config',
                'items' => self::filterMenuItems(self::getConfig(), $user)
            ]
        ], fn(array $group) => $group['items'] !== []));
    }

    protected static function filterMenuItems(array $items, ?User $user): array
    {
        return array_values(array_filter(array_map(function (array $item) use ($user) {
            if (! self::canAccess($item, $user)) {
                return null;
            }

            if (isset($item['subItems'])) {
                $item['subItems'] = self::filterMenuItems($item['subItems'], $user);

                if ($item['subItems'] === [] && ! Arr::has($item, 'path')) {
                    return null;
                }
            }

            return $item;
        }, $items)));
    }

    protected static function canAccess(array $item, ?User $user): bool
    {
        $allowedRoles = $item['roles'] ?? User::roles();

        if ($user === null) {
            return false;
        }

        return $user->hasRole($allowedRoles);
    }

    public static function isActive($path)
    {
        return request()->is(ltrim($path, '/'));
    }

    public static function getIconSvg($iconName)
    {
        $icons = [
            'dashboard' => '<i class="fa-solid fa-gauge"></i>',
            'calendar' => '<i class="fa-solid fa-calendar"></i>',
            'user-profile' => '<i class="fa-solid fa-user"></i>',
            'forms' => '<i class="fa-solid fa-list-check"></i>',
            'tables' => '<i class="fa-solid fa-table"></i>',
            'pages' => '<i class="fa-solid fa-file"></i>',
            'charts' => '<i class="fa-solid fa-chart-line"></i>',
            'ui-elements' => '<i class="fa-solid fa-cubes"></i>',
            'authentication' => '<i class="fa-solid fa-lock"></i>',
            'configuration' => '<i class="fa-solid fa-gear"></i>',
            'logout' => '<i class="fa-solid fa-arrow-right-from-bracket"></i>',
            'users' => '<i class="fa-solid fa-users"></i>',
            'savings' => '<i class="fa-solid fa-piggy-bank"></i>',
            'loan' => '<i class="fa-solid fa-hand-holding-dollar"></i>',
            'store' => '<i class="fa-solid fa-store"></i>',
            'shu' => '<i class="fa-solid fa-coins"></i>',
            'report' => '<i class="fa-solid fa-file-invoice-dollar"></i>',
            'asset' => '<i class="fa-solid fa-gem"></i>',
        ];

        return $icons[$iconName] ?? '<i class="fa-solid fa-circle"></i>';
    }

    // Method untuk Font Awesome icon classes
    public static function getIconClass($iconName)
    {
        $icons = [
            'dashboard' => 'fa-solid fa-gauge',
            'calendar' => 'fa-solid fa-calendar',
            'user-profile' => 'fa-solid fa-user',
            'forms' => 'fa-solid fa-list-check',
            'tables' => 'fa-solid fa-table',
            'pages' => 'fa-solid fa-file',
            'charts' => 'fa-solid fa-chart-line',
            'ui-elements' => 'fa-solid fa-cubes',
            'authentication' => 'fa-solid fa-lock',
            'configuration' => 'fa-solid fa-gear',
            'logout' => 'fa-solid fa-arrow-right-from-bracket',
            'users' => 'fa-solid fa-users',
            'savings' => 'fa-solid fa-piggy-bank',
            'loan' => 'fa-solid fa-hand-holding-dollar',
            'store' => 'fa-solid fa-store',
            'shu' => 'fa-solid fa-coins',
            'report' => 'fa-solid fa-file-invoice-dollar',
            'asset' => 'fa-solid fa-gem',
        ];

        return $icons[$iconName] ?? 'fa-solid fa-circle';
    }
}
