<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

/**
 * A device that is not asked for an OTP until expires_at.
 * Only the SHA-256 of the cookie token is stored (token_hash).
 */
class TrustedDevice extends Model
{
    use Prunable;

    protected $fillable = ['user_id', 'token_hash', 'user_agent', 'ip_address', 'last_used_at', 'expires_at'];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** `php artisan model:prune` deletes the expired ones. */
    public function prunable(): Builder
    {
        return static::where('expires_at', '<', now());
    }
}
