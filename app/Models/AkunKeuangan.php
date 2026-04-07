<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AkunKeuangan extends Model
{
    public const TIPE_ASET = 'aset';
    public const TIPE_KEWAJIBAN = 'kewajiban';
    public const TIPE_MODAL = 'modal';
    public const TIPE_PENDAPATAN = 'pendapatan';
    public const TIPE_BEBAN = 'beban';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';

    protected $table = 'akun_keuangan';

    protected $fillable = [
        'koperasi_id',
        'parent_id',
        'kode_akun',
        'nama_akun',
        'tipe_akun',
        'level',
        'is_header',
        'can_post',
        'status',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'is_header' => 'boolean',
            'can_post' => 'boolean',
        ];
    }

    public static function tipeOptions(): array
    {
        return [
            self::TIPE_ASET,
            self::TIPE_KEWAJIBAN,
            self::TIPE_MODAL,
            self::TIPE_PENDAPATAN,
            self::TIPE_BEBAN,
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_AKTIF,
            self::STATUS_NONAKTIF,
        ];
    }

    public static function defaultTemplates(): array
    {
        return [
            ['kode_akun' => '1000', 'nama_akun' => 'Aset', 'tipe_akun' => self::TIPE_ASET, 'parent_kode' => null, 'is_header' => true, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Kelompok akun aset koperasi.'],
            ['kode_akun' => '1100', 'nama_akun' => 'Kas', 'tipe_akun' => self::TIPE_ASET, 'parent_kode' => '1000', 'is_header' => false, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Kas operasional koperasi.'],
            ['kode_akun' => '1200', 'nama_akun' => 'Bank', 'tipe_akun' => self::TIPE_ASET, 'parent_kode' => '1000', 'is_header' => false, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Saldo rekening bank koperasi.'],
            ['kode_akun' => '1300', 'nama_akun' => 'Piutang Pinjaman', 'tipe_akun' => self::TIPE_ASET, 'parent_kode' => '1000', 'is_header' => false, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Piutang pinjaman anggota yang masih berjalan.'],
            ['kode_akun' => '1400', 'nama_akun' => 'Persediaan Toko', 'tipe_akun' => self::TIPE_ASET, 'parent_kode' => '1000', 'is_header' => false, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Nilai persediaan toko koperasi.'],
            ['kode_akun' => '1500', 'nama_akun' => 'Aset Tetap', 'tipe_akun' => self::TIPE_ASET, 'parent_kode' => '1000', 'is_header' => false, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Aset tetap dan inventaris koperasi.'],
            ['kode_akun' => '2000', 'nama_akun' => 'Kewajiban', 'tipe_akun' => self::TIPE_KEWAJIBAN, 'parent_kode' => null, 'is_header' => true, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Kelompok kewajiban koperasi.'],
            ['kode_akun' => '2100', 'nama_akun' => 'Utang Usaha', 'tipe_akun' => self::TIPE_KEWAJIBAN, 'parent_kode' => '2000', 'is_header' => false, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Utang operasional kepada pihak ketiga.'],
            ['kode_akun' => '2200', 'nama_akun' => 'Simpanan Anggota', 'tipe_akun' => self::TIPE_KEWAJIBAN, 'parent_kode' => '2000', 'is_header' => false, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Kewajiban koperasi atas simpanan anggota.'],
            ['kode_akun' => '2300', 'nama_akun' => 'Utang SHU Anggota', 'tipe_akun' => self::TIPE_KEWAJIBAN, 'parent_kode' => '2000', 'is_header' => false, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Kewajiban distribusi SHU kepada anggota.'],
            ['kode_akun' => '3000', 'nama_akun' => 'Modal', 'tipe_akun' => self::TIPE_MODAL, 'parent_kode' => null, 'is_header' => true, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Kelompok modal koperasi.'],
            ['kode_akun' => '3100', 'nama_akun' => 'Simpanan Pokok', 'tipe_akun' => self::TIPE_MODAL, 'parent_kode' => '3000', 'is_header' => false, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Modal dari simpanan pokok anggota.'],
            ['kode_akun' => '3200', 'nama_akun' => 'Cadangan SHU', 'tipe_akun' => self::TIPE_MODAL, 'parent_kode' => '3000', 'is_header' => false, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Cadangan hasil usaha koperasi.'],
            ['kode_akun' => '3300', 'nama_akun' => 'SHU Belum Dibagi', 'tipe_akun' => self::TIPE_MODAL, 'parent_kode' => '3000', 'is_header' => false, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Saldo SHU yang belum didistribusikan.'],
            ['kode_akun' => '4000', 'nama_akun' => 'Pendapatan', 'tipe_akun' => self::TIPE_PENDAPATAN, 'parent_kode' => null, 'is_header' => true, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Kelompok pendapatan koperasi.'],
            ['kode_akun' => '4100', 'nama_akun' => 'Pendapatan Jasa Pinjaman', 'tipe_akun' => self::TIPE_PENDAPATAN, 'parent_kode' => '4000', 'is_header' => false, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Pendapatan bunga atau jasa pinjaman anggota.'],
            ['kode_akun' => '4200', 'nama_akun' => 'Pendapatan Toko', 'tipe_akun' => self::TIPE_PENDAPATAN, 'parent_kode' => '4000', 'is_header' => false, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Pendapatan penjualan toko koperasi.'],
            ['kode_akun' => '4300', 'nama_akun' => 'Pendapatan Lain-lain', 'tipe_akun' => self::TIPE_PENDAPATAN, 'parent_kode' => '4000', 'is_header' => false, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Pendapatan lain di luar usaha utama koperasi.'],
            ['kode_akun' => '5000', 'nama_akun' => 'Beban', 'tipe_akun' => self::TIPE_BEBAN, 'parent_kode' => null, 'is_header' => true, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Kelompok beban koperasi.'],
            ['kode_akun' => '5100', 'nama_akun' => 'Beban Operasional', 'tipe_akun' => self::TIPE_BEBAN, 'parent_kode' => '5000', 'is_header' => false, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Beban operasional harian koperasi.'],
            ['kode_akun' => '5200', 'nama_akun' => 'Beban Administrasi', 'tipe_akun' => self::TIPE_BEBAN, 'parent_kode' => '5000', 'is_header' => false, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Beban administrasi dan umum.'],
            ['kode_akun' => '5300', 'nama_akun' => 'Beban Lain-lain', 'tipe_akun' => self::TIPE_BEBAN, 'parent_kode' => '5000', 'is_header' => false, 'status' => self::STATUS_AKTIF, 'keterangan' => 'Beban lain di luar operasional utama koperasi.'],
        ];
    }

    public function koperasi(): BelongsTo
    {
        return $this->belongsTo(Koperasi::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('kode_akun');
    }

    public function jurnalDetails(): HasMany
    {
        return $this->hasMany(JurnalUmumDetail::class, 'akun_keuangan_id');
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(AkunKeuanganMapping::class, 'akun_keuangan_id');
    }
}
