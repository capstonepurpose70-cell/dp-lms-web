<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceRegistration extends Model
{
    protected $fillable = [
        'user_id', 'status', 'images_count',
        'reject_reason', 'inappropriate', 'reviewed_at',
    ];

    protected $casts = [
        'inappropriate' => 'boolean',
        'reviewed_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}