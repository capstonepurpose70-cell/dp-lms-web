<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameScore extends Model
{
    protected $fillable = [
        'user_id', 'grade_level', 'world', 'score', 'accuracy',
        'correct', 'incorrect', 'max_combo', 'avg_response_ms',
    ];

    protected $casts = [
        'accuracy' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}