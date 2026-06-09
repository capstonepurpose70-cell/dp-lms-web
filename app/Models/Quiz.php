<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'subject_id',
        'user_id',
        'section_id',
        'title',
        'description',
        'time_limit',
        'max_score',
        'available_from',
        'available_until',
        'is_published',
    ];

    protected $casts = [
        'available_from'  => 'datetime',
        'available_until' => 'datetime',
        'is_published'    => 'boolean',
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

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order');
    }

    // ─── Helper Methods ───────────────────────────────────────────────────────

    public function isAvailable(): bool
    {
        $now = now();
        return $this->is_published
            && (!$this->available_from || $now->gte($this->available_from))
            && (!$this->available_until || $now->lte($this->available_until));
    }
}