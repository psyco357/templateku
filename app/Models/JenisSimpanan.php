<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisSimpanan extends Model
{
    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';

    protected $table = 'jenis_simpanan';

    protected $fillable = [
        'koperasi_id',
        'kode_jenis',
        'nama_jenis',
        'bunga_persen',
        'nominal_default',
        'is_wajib',
        'status',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'bunga_persen' => 'decimal:2',
            'nominal_default' => 'decimal:2',
            'is_wajib' => 'boolean',
        ];
    }

    public function koperasi(): BelongsTo
    {
        return $this->belongsTo(Koperasi::class);
    }

    public function simpanan(): HasMany
    {
        return $this->hasMany(Simpanan::class);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_AKTIF,
            self::STATUS_NONAKTIF,
        ];
    }
}
