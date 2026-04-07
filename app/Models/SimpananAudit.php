<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimpananAudit extends Model
{
    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_CANCEL = 'cancel';

    protected $table = 'simpanan_audits';

    protected $fillable = [
        'simpanan_id',
        'user_id',
        'action',
        'description',
        'metadata',
        'created_at',
    ];

    public $updated_at = false;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function simpanan(): BelongsTo
    {
        return $this->belongsTo(Simpanan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
