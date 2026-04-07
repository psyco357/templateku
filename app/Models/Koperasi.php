<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Koperasi extends Model
{
    protected $table = 'koperasi';

    protected $fillable = [
        'kode_koperasi',
        'nama_koperasi',
        'nomor_badan_hukum',
        'tanggal_berdiri',
        'email',
        'telepon',
        'alamat',
        'logo',
        'landing_theme',
        'landing_hero_title',
        'landing_hero_subtitle',
        'landing_hero_image',
        'landing_about_title',
        'landing_about_description',
        'ketua',
        'status',
        'siklus_tutup_buku',
        'tutup_buku_bulan_hijriah',
        'tutup_buku_hari_hijriah',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_berdiri' => 'date',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function periodeBuku(): HasMany
    {
        return $this->hasMany(PeriodeBuku::class);
    }

    public function jenisSimpanan(): HasMany
    {
        return $this->hasMany(JenisSimpanan::class);
    }

    public function simpanan(): HasMany
    {
        return $this->hasMany(Simpanan::class);
    }

    public function akunKeuangan(): HasMany
    {
        return $this->hasMany(AkunKeuangan::class);
    }

    public function jurnalUmum(): HasMany
    {
        return $this->hasMany(JurnalUmum::class);
    }

    public function akunMappings(): HasMany
    {
        return $this->hasMany(AkunKeuanganMapping::class);
    }

    public function shuPayments(): HasMany
    {
        return $this->hasMany(ShuPayment::class);
    }
}
