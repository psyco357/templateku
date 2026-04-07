<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PinjamanStatusLog extends Model
{
    protected $table = 'pinjaman_status_logs';

    protected $fillable = [
        'pinjaman_id',
        'koperasi_id',
        'status_sebelumnya',
        'status_baru',
        'diproses_oleh',
        'catatan',
    ];

    public function pinjaman(): BelongsTo
    {
        return $this->belongsTo(Pinjaman::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }
}
