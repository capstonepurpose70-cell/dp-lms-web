<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'section_id',
        'subject_id',
        'school_year_id',
        'grade_level',
        'school_year_label',
        'is_adviser',
        'status',
    ];

    protected $casts = [
        'is_adviser' => 'boolean',
    ];

    // ── Relationships ───────────────────────────────────────────────────────────

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    // ── Scopes ──────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForCurrentYear($query)
    {
        $year = SchoolYear::current();
        return $year
            ? $query->where('school_year_id', $year->id)
            : $query;
    }

    public function scopeAdvisers($query)
    {
        return $query->where('is_adviser', true);
    }

    public function scopeSubjectTeachers($query)
    {
        return $query->where('is_adviser', false)->whereNotNull('subject_id');
    }
}