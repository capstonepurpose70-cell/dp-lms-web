<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = [
        'assignment_id',
        'user_id',
        'file_path',
        'remarks',   // sagot / note ng ESTUDYANTE
        'feedback',  // komento ng TEACHER (hiwalay para hindi mapalitan ang sagot)
        'score',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ─── Helper Methods ───────────────────────────────────────────────────────

    public function isGraded(): bool
    {
        return $this->status === 'graded';
    }
}