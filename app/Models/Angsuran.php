<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Angsuran extends Model
{
    public const STATUS_DIBAYAR = 'dibayar';
    public const STATUS_MENUNGGAK = 'menunggak';

    protected $table = 'angsuran';

    protected $fillable = [
        'koperasi_id',
        'pinjaman_id',
        'periode_buku_id',
        'angsuran_ke',
        'jumlah_bayar',
        'pokok',
        'bunga',
        'denda',
        'tanggal_bayar',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_bayar' => 'decimal:2',
            'pokok' => 'decimal:2',
            'bunga' => 'decimal:2',
            'denda' => 'decimal:2',
            'tanggal_bayar' => 'date',
            'angsuran_ke' => 'integer',
        ];
    }

    public function koperasi(): BelongsTo
    {
        return $this->belongsTo(Koperasi::class);
    }

    public function pinjaman(): BelongsTo
    {
        return $this->belongsTo(Pinjaman::class);
    }

    public function periodeBuku(): BelongsTo
    {
        return $this->belongsTo(PeriodeBuku::class);
    }
}
