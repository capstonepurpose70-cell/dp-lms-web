<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'subject_id',
        'user_id',
        'section_id',
        'title',
        'instructions',
        'file_path',
        'due_date',
        'max_score',
        'is_published',
    ];

    protected $casts = [
        'due_date'     => 'datetime',
        'is_published' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    // ─── Helper Methods ───────────────────────────────────────────────────────

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }

    public function submissionByStudent(int $userId)
    {
        return $this->submissions()->where('user_id', $userId)->first();
    }
}