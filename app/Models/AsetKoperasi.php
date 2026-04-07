<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsetKoperasi extends Model
{
    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';
    public const TIPE_NONAKTIF_JUAL = 'jual';
    public const TIPE_NONAKTIF_TANPA_PENJUALAN = 'tanpa_penjualan';

    public const JENIS_EMAS = 'emas';
    public const JENIS_INVENTARIS = 'inventaris';
    public const JENIS_PROPERTI = 'properti';
    public const JENIS_LAINNYA = 'lainnya';

    protected $table = 'aset_koperasi';

    protected $fillable = [
        'koperasi_id',
        'periode_buku_id',
        'kode_aset',
        'nama_aset',
        'jenis_aset',
        'tanggal_perolehan',
        'tanggal_nonaktif',
        'tipe_nonaktif',
        'nilai_perolehan',
        'nilai_pelepasan',
        'kuantitas',
        'satuan',
        'status',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_perolehan' => 'date',
            'tanggal_nonaktif' => 'date',
            'nilai_perolehan' => 'decimal:2',
            'nilai_pelepasan' => 'decimal:2',
            'kuantitas' => 'decimal:4',
        ];
    }

    public function koperasi(): BelongsTo
    {
        return $this->belongsTo(Koperasi::class);
    }

    public function periodeBuku(): BelongsTo
    {
        return $this->belongsTo(PeriodeBuku::class);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_AKTIF,
            self::STATUS_NONAKTIF,
        ];
    }

    public static function jenisOptions(): array
    {
        return [
            self::JENIS_EMAS,
            self::JENIS_INVENTARIS,
            self::JENIS_PROPERTI,
            self::JENIS_LAINNYA,
        ];
    }

    public static function tipeNonaktifOptions(): array
    {
        return [
            self::TIPE_NONAKTIF_JUAL,
            self::TIPE_NONAKTIF_TANPA_PENJUALAN,
        ];
    }
}
