<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pinjaman extends Model
{
    public const STATUS_DIAJUKAN = 'diajukan';
    public const STATUS_AKTIF = 'aktif';
    public const STATUS_LUNAS = 'lunas';
    public const STATUS_DITOLAK = 'ditolak';

    public const DEFAULT_MONTHLY_INTEREST = 20000;
    public const DEFAULT_PAYMENT_DAY = 5;
    public const MAX_SAVINGS_RATIO = 0.9;

    protected $table = 'pinjaman';

    protected $fillable = [
        'koperasi_id',
        'anggota_id',
        'periode_buku_id',
        'no_pinjaman',
        'jumlah_pinjaman',
        'bunga_persen',
        'bunga_nominal_bulanan',
        'tanggal_tagihan_bulanan',
        'tenor_bulan',
        'tanggal_pengajuan',
        'tanggal_pinjaman',
        'tanggal_jatuh_tempo',
        'status',
        'keterangan',
        'alasan_penolakan',
        'disetujui_oleh',
        'disetujui_pada',
        'ditolak_oleh',
        'ditolak_pada',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_pinjaman' => 'decimal:2',
            'bunga_persen' => 'decimal:2',
            'bunga_nominal_bulanan' => 'decimal:2',
            'tanggal_pengajuan' => 'date',
            'tanggal_pinjaman' => 'date',
            'tanggal_jatuh_tempo' => 'date',
            'disetujui_pada' => 'datetime',
            'ditolak_pada' => 'datetime',
            'tanggal_tagihan_bulanan' => 'integer',
            'tenor_bulan' => 'integer',
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

    public function periodeBuku(): BelongsTo
    {
        return $this->belongsTo(PeriodeBuku::class);
    }

    public function angsuran(): HasMany
    {
        return $this->hasMany(Angsuran::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(PinjamanStatusLog::class)->latest();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditolak_oleh');
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DIAJUKAN,
            self::STATUS_AKTIF,
            self::STATUS_LUNAS,
            self::STATUS_DITOLAK,
        ];
    }
}
