<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnggotaModel extends Model
{
    use SoftDeletes;

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';
    public const STATUS_CUTI = 'cuti';

    //
    protected $table = 'anggota';
    protected $fillable = [
        'profile_id',
        'no_anggota',
        'jabatan',
        'status',
    ];
    public function profile()
    {
        return $this->belongsTo(ProfilesModel::class, 'profile_id');
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_AKTIF,
            self::STATUS_NONAKTIF,
            self::STATUS_CUTI,
        ];
    }
}
