<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_ANGGOTA = 'anggota';
    public const ROLE_PENGURUS = 'pengurus';
    public const ROLE_FOUNDER = 'founder';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'koperasi_id',
        'username',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public static function roles(): array
    {
        return [
            self::ROLE_ANGGOTA,
            self::ROLE_PENGURUS,
            self::ROLE_FOUNDER,
        ];
    }

    public function hasRole(string|array $roles): bool
    {
        $allowedRoles = is_array($roles) ? $roles : [$roles];

        return in_array($this->role, $allowedRoles, true);
    }

    public function profile()
    {
        return $this->hasOne(ProfilesModel::class);
    }

    public function koperasi()
    {
        return $this->belongsTo(Koperasi::class);
    }

    public function anggota()
    {
        return $this->hasOneThrough(
            AnggotaModel::class,
            ProfilesModel::class,
            'user_id',
            'profile_id',
            'id',
            'id'
        );
    }
}
