<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShuPayment extends Model
{
    public const STATUS_DIBAYAR = 'dibayar';

    protected $table = 'shu_payments';

    protected $fillable = [
        'koperasi_id',
        'shu_skema_id',
        'anggota_id',
        'periode_buku_id',
        'no_bukti',
        'tahun',
        'tanggal_bayar',
        'jumlah_bayar',
        'status',
        'keterangan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'tanggal_bayar' => 'date',
            'jumlah_bayar' => 'decimal:2',
        ];
    }

    public function koperasi(): BelongsTo
    {
        return $this->belongsTo(Koperasi::class);
    }

    public function skema(): BelongsTo
    {
        return $this->belongsTo(ShuSkema::class, 'shu_skema_id');
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(AnggotaModel::class, 'anggota_id');
    }

    public function periodeBuku(): BelongsTo
    {
        return $this->belongsTo(PeriodeBuku::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
