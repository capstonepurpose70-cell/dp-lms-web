<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameQuestion extends Model
{
    protected $fillable = [
        'grade_level', 'topic', 'difficulty', 'question', 'options', 'correct_index',
    ];

    protected $casts = [
        'options'       => 'array',
        'correct_index' => 'integer',
    ];
}