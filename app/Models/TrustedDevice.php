<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

/**
 * Device na hindi na hihingan ng OTP hanggang expires_at.
 * SHA-256 lang ng cookie token ang naka-store (token_hash).
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

    /** Binubura ng `php artisan model:prune` ang mga expired na. */
    public function prunable(): Builder
    {
        return static::where('expires_at', '<', now());
    }
}
