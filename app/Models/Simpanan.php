<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Simpanan extends Model
{
    public const STATUS_POSTED = 'posted';
    public const STATUS_BATAL = 'batal';

    protected $table = 'simpanan';

    protected $fillable = [
        'koperasi_id',
        'anggota_id',
        'jenis_simpanan_id',
        'periode_buku_id',
        'no_bukti',
        'jumlah',
        'tanggal_transaksi',
        'status',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'tanggal_transaksi' => 'date',
        ];
    }

    public function koperasi(): BelongsTo
    {
        return $this->belongsTo(Koperasi::class);
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(AnggotaModel::class, 'anggota_id');
    }

    public function jenisSimpanan(): BelongsTo
    {
        return $this->belongsTo(JenisSimpanan::class);
    }

    public function periodeBuku(): BelongsTo
    {
        return $this->belongsTo(PeriodeBuku::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(SimpananAudit::class)->latest('created_at')->latest('id');
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_POSTED,
            self::STATUS_BATAL,
        ];
    }
}
