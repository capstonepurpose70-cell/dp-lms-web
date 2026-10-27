<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One-time code a student creates for their parent.
 * The plaintext code is NOT stored — only its SHA-256 (code_hash).
 */
class ParentLinkCode extends Model
{
    public const PURPOSE_LINK    = 'link';
    public const PURPOSE_RECOVER = 'recover';

    protected $fillable = ['student_id', 'code_hash', 'purpose', 'expires_at', 'used_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /** Not used yet and not expired. */
    public function isUsable(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }
}
