<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfilesModel extends Model
{
    use SoftDeletes;
    protected $table = 'profiles';

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'no_hp',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'foto_profil',
        'bio',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function anggota()
    {
        return $this->hasOne(AnggotaModel::class, 'profile_id');
    }
}
