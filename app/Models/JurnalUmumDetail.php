<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JurnalUmumDetail extends Model
{
    protected $table = 'jurnal_umum_details';

    protected $fillable = [
        'jurnal_umum_id',
        'akun_keuangan_id',
        'urutan',
        'uraian',
        'debit',
        'kredit',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'kredit' => 'decimal:2',
            'urutan' => 'integer',
        ];
    }

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(JurnalUmum::class, 'jurnal_umum_id');
    }

    public function akun(): BelongsTo
    {
        return $this->belongsTo(AkunKeuangan::class, 'akun_keuangan_id');
    }
}
