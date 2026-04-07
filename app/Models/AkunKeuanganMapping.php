<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AkunKeuanganMapping extends Model
{
    protected $table = 'akun_keuangan_mappings';

    protected $fillable = [
        'koperasi_id',
        'mapping_key',
        'akun_keuangan_id',
    ];

    public static function templates(): array
    {
        return [
            ['key' => 'simpanan_setoran_debit', 'module' => 'simpanan', 'label' => 'Setoran Simpanan - Debit', 'description' => 'Akun debit saat setoran simpanan diterima.', 'default_code' => '1100'],
            ['key' => 'simpanan_setoran_kredit', 'module' => 'simpanan', 'label' => 'Setoran Simpanan - Kredit', 'description' => 'Akun kredit untuk kewajiban atau modal simpanan anggota.', 'default_code' => '2200'],
            ['key' => 'pinjaman_pencairan_debit', 'module' => 'pinjaman', 'label' => 'Pencairan Pinjaman - Debit', 'description' => 'Akun piutang pinjaman saat pinjaman disetujui dan dicairkan.', 'default_code' => '1300'],
            ['key' => 'pinjaman_pencairan_kredit', 'module' => 'pinjaman', 'label' => 'Pencairan Pinjaman - Kredit', 'description' => 'Akun kas atau bank yang berkurang saat pinjaman dicairkan.', 'default_code' => '1100'],
            ['key' => 'pinjaman_angsuran_debit', 'module' => 'pinjaman', 'label' => 'Angsuran Pinjaman - Debit', 'description' => 'Akun kas atau bank saat angsuran diterima.', 'default_code' => '1100'],
            ['key' => 'pinjaman_angsuran_pokok_kredit', 'module' => 'pinjaman', 'label' => 'Angsuran Pokok - Kredit', 'description' => 'Akun piutang pinjaman yang berkurang saat pokok dibayar.', 'default_code' => '1300'],
            ['key' => 'pinjaman_angsuran_jasa_kredit', 'module' => 'pinjaman', 'label' => 'Angsuran Jasa - Kredit', 'description' => 'Akun pendapatan jasa pinjaman dari pembayaran angsuran.', 'default_code' => '4100'],
            ['key' => 'shu_distribusi_debit', 'module' => 'shu', 'label' => 'Distribusi SHU - Debit', 'description' => 'Akun SHU belum dibagi saat SHU dialokasikan.', 'default_code' => '3300'],
            ['key' => 'shu_distribusi_kredit', 'module' => 'shu', 'label' => 'Distribusi SHU - Kredit', 'description' => 'Akun kewajiban SHU anggota setelah distribusi ditetapkan.', 'default_code' => '2300'],
            ['key' => 'shu_pembayaran_debit', 'module' => 'shu', 'label' => 'Pembayaran SHU - Debit', 'description' => 'Akun kewajiban SHU anggota yang berkurang saat dibayar.', 'default_code' => '2300'],
            ['key' => 'shu_pembayaran_kredit', 'module' => 'shu', 'label' => 'Pembayaran SHU - Kredit', 'description' => 'Akun kas atau bank yang keluar saat SHU dibayarkan.', 'default_code' => '1100'],
            ['key' => 'aset_perolehan_debit', 'module' => 'aset', 'label' => 'Perolehan Aset - Debit', 'description' => 'Akun aset tetap saat koperasi memperoleh aset.', 'default_code' => '1500'],
            ['key' => 'aset_perolehan_kredit', 'module' => 'aset', 'label' => 'Perolehan Aset - Kredit', 'description' => 'Akun kas atau bank yang digunakan untuk memperoleh aset.', 'default_code' => '1100'],
            ['key' => 'aset_pelepasan_kas_debit', 'module' => 'aset', 'label' => 'Pelepasan Aset - Kas Masuk', 'description' => 'Akun kas atau bank saat aset dijual.', 'default_code' => '1100'],
            ['key' => 'aset_pelepasan_aset_kredit', 'module' => 'aset', 'label' => 'Pelepasan Aset - Pengurang Aset', 'description' => 'Akun aset yang dikredit saat aset dilepas atau dihapus.', 'default_code' => '1500'],
            ['key' => 'aset_pelepasan_laba_kredit', 'module' => 'aset', 'label' => 'Pelepasan Aset - Laba', 'description' => 'Akun pendapatan saat nilai jual lebih tinggi dari nilai buku.', 'default_code' => '4300'],
            ['key' => 'aset_pelepasan_rugi_debit', 'module' => 'aset', 'label' => 'Pelepasan Aset - Rugi/Beban', 'description' => 'Akun beban saat aset dilepas di bawah nilai buku atau dihapus.', 'default_code' => '5300'],
        ];
    }

    public function koperasi(): BelongsTo
    {
        return $this->belongsTo(Koperasi::class);
    }

    public function akun(): BelongsTo
    {
        return $this->belongsTo(AkunKeuangan::class, 'akun_keuangan_id');
    }
}
