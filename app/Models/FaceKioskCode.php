<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One-time QR for registering a student's face at the Raspberry Pi kiosk (10 min, single use). */
class FaceKioskCode extends Model
{
    protected $fillable = ['student_id', 'token_hash', 'issued_by_role', 'issued_by_id', 'expires_at', 'used_at', 'used_ip'];

    protected $hidden = ['token_hash'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    public function student() { return $this->belongsTo(User::class, 'student_id'); }

    public function isUsable(): bool
    {
        return $this->used_at === null && now()->lt($this->expires_at);
    }
}
