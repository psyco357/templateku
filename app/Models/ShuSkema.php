<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShuSkema extends Model
{
    protected $table = 'shu_skema';

    protected $fillable = [
        'koperasi_id',
        'user_id',
        'tahun',
        'cadangan',
        'jasa_modal',
        'jasa_usaha',
        'dana_pengurus',
        'dana_sosial',
        'total_persen',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'cadangan' => 'decimal:2',
            'jasa_modal' => 'decimal:2',
            'jasa_usaha' => 'decimal:2',
            'dana_pengurus' => 'decimal:2',
            'dana_sosial' => 'decimal:2',
            'total_persen' => 'decimal:2',
        ];
    }

    public function koperasi(): BelongsTo
    {
        return $this->belongsTo(Koperasi::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ShuSkemaHistory::class, 'shu_skema_id')->latest('created_at')->latest('id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ShuPayment::class, 'shu_skema_id')->latest('tanggal_bayar')->latest('id');
    }
}
