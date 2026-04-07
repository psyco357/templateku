<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShuSkemaHistory extends Model
{
    protected $table = 'shu_skema_histories';

    protected $fillable = [
        'shu_skema_id',
        'koperasi_id',
        'user_id',
        'tahun',
        'aksi',
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

    public function skema(): BelongsTo
    {
        return $this->belongsTo(ShuSkema::class, 'shu_skema_id');
    }

    public function koperasi(): BelongsTo
    {
        return $this->belongsTo(Koperasi::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
