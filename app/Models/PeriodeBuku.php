<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodeBuku extends Model
{
    protected $table = 'periode_buku';

    protected $fillable = [
        'koperasi_id',
        'tahun_buku',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_tutup_buku',
        'tutup_buku_bulan_hijriah',
        'tutup_buku_hari_hijriah',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'tanggal_tutup_buku' => 'date',
        ];
    }

    public function koperasi(): BelongsTo
    {
        return $this->belongsTo(Koperasi::class);
    }
}
