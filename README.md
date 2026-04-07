# Sistem Informasi Koperasi

Sistem ini adalah aplikasi manajemen koperasi berbasis Laravel untuk mengelola data anggota, simpanan, pinjaman, SHU, aset, laporan keuangan, dan administrasi koperasi dalam satu dashboard.

## Ringkasan Fitur

- Autentikasi pengguna dengan login, registrasi, logout, dan reset password.
- Manajemen anggota koperasi, profil anggota, dan pengaturan peran pengguna.
- Pengelolaan data koperasi dan periode buku.
- Modul simpanan dengan master jenis simpanan, transaksi, mutasi, rekap saldo, cetak bukti, dan ekspor data.
- Modul pinjaman dengan pengajuan, simulasi, jadwal angsuran, status pembayaran, dan pembayaran angsuran.
- Modul SHU untuk perhitungan skema distribusi serta ekspor hasil ke Excel dan PDF.
- Laporan koperasi seperti neraca keuangan, arus kas, rugi laba, rugi laba tahunan, tunggakan pinjaman, dan laporan RAT.
- Pengelolaan aset koperasi.
- Manajemen akun pengguna oleh founder.

## Peran Pengguna

Sistem saat ini membedakan hak akses utama menjadi:

- `founder`: akses penuh, termasuk pengaturan koperasi, akun, dan distribusi SHU.
- `pengurus`: akses operasional untuk anggota, simpanan, pinjaman, aset, dan laporan.
- `anggota`: akses ke fitur personal seperti simpanan, pengajuan pinjaman, simulasi, jadwal angsuran, dan status pembayaran.

## Modul yang Tersedia

### 1. Dashboard

Dashboard sebagai pusat ringkasan operasional koperasi setelah pengguna login.

### 2. Keanggotaan

- Daftar anggota
- Tambah anggota baru
- Detail dan pembaruan data anggota
- Generate nomor anggota
- Pengaturan role anggota

### 3. Simpanan

- Master jenis simpanan
- Input transaksi simpanan
- Detail transaksi dan pembatalan transaksi
- Cetak bukti transaksi
- Rekap saldo simpanan
- Mutasi simpanan per anggota
- Export rekap dan mutasi

### 4. Pinjaman

- Pengajuan pinjaman
- Simulasi pinjaman
- Jadwal angsuran
- Status pembayaran pinjaman
- Pembayaran angsuran

Aturan bisnis yang sudah tercermin di aplikasi:

- Maksimal pinjaman adalah 90% dari saldo simpanan yang sudah diposting.
- Bunga pinjaman menggunakan nominal tetap Rp20.000 per bulan.
- Tagihan bulanan jatuh pada tanggal 5.

### 5. SHU

- Perhitungan skema SHU
- Penyimpanan skema distribusi
- Distribusi SHU
- Export distribusi ke Excel dan PDF

### 6. Laporan

- Neraca keuangan
- Arus kas
- Rugi laba
- Rugi laba tahunan
- Tunggakan pinjaman
- Laporan RAT

### 7. Aset Koperasi

- Daftar aset
- Tambah aset
- Ubah aset
- Nonaktifkan aset

### 8. Pengaturan dan Akun

- Edit profil pengguna
- Ubah password
- Pengaturan tampilan aplikasi
- Manajemen akun pengguna
- Reset password akun oleh founder
- Aktivasi atau nonaktifkan akun

## Modul Dalam Pengembangan

Beberapa halaman sudah disiapkan sebagai placeholder dan belum selesai diimplementasikan:

- Kartu anggota
- Riwayat transaksi anggota
- Kasir / POS toko koperasi
- Manajemen stok produk
- Laporan penjualan toko

## Teknologi yang Digunakan

- PHP 8.2+
- Laravel 12
- MySQL atau database relasional yang kompatibel dengan Laravel
- Vite
- Tailwind CSS 4
- Alpine.js
- Pest untuk testing
- DomPDF untuk export PDF

## Instalasi

1. Clone repository ini.
2. Salin file environment jika belum ada.
3. Install dependency PHP dan JavaScript.
4. Generate application key.
5. Jalankan migration.

Contoh langkah cepat:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm install
```

Atau gunakan script bawaan proyek:

```bash
composer run setup
```

## Menjalankan Aplikasi

Untuk mode development, jalankan:

```bash
composer run dev
```

Perintah ini akan menjalankan:

- server Laravel
- queue listener
- Vite dev server

Jika ingin menjalankan manual secara terpisah:

```bash
php artisan serve
npm run dev
```

Untuk build asset production:

```bash
npm run build
```

## Pengujian

Menjalankan test:

```bash
composer run test
```

## Struktur Singkat Aplikasi

- `app/Http/Controllers`: controller utama seperti autentikasi, anggota, simpanan, pinjaman, laporan, SHU, aset, dan pengaturan.
- `app/Models`: model domain koperasi.
- `database/migrations`: skema database aplikasi.
- `resources/views`: tampilan antarmuka aplikasi.
- `routes/web.php`: definisi route web dan hak akses per modul.

## Catatan

- Registrasi pengguna saat ini membuat data profil sekaligus dan langsung login ke dashboard.
- Aplikasi menggunakan middleware role untuk membatasi akses berdasarkan jenis pengguna.
- Landing page publik mengambil data profil koperasi dari database.
