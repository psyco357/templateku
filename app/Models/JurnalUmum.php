<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JurnalUmum extends Model
{
    public const JENIS_MANUAL = 'manual';
    public const JENIS_OTOMATIS = 'otomatis';
    public const STATUS_POSTED = 'posted';

    protected $table = 'jurnal_umum';

    protected $fillable = [
        'koperasi_id',
        'periode_buku_id',
        'no_jurnal',
        'tanggal_jurnal',
        'jenis_jurnal',
        'status',
        'sumber_referensi',
        'keterangan',
        'total_debit',
        'total_kredit',
        'posted_by',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_jurnal' => 'date',
            'posted_at' => 'datetime',
            'total_debit' => 'decimal:2',
            'total_kredit' => 'decimal:2',
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

    public function details(): HasMany
    {
        return $this->hasMany(JurnalUmumDetail::class)->orderBy('urutan');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
