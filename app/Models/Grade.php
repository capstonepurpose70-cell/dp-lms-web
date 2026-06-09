<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = [
        'user_id',
        'subject_id',
        'quarter',
        'school_year',
        'written_works',
        'performance_tasks',
        'quarterly_assessment',
        'final_grade',
        'remarks',
    ];

    protected $casts = [
        'written_works'        => 'decimal:2',
        'performance_tasks'    => 'decimal:2',
        'quarterly_assessment' => 'decimal:2',
        'final_grade'          => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // ─── Helper Methods ───────────────────────────────────────────────────────

    public function isPassed(): bool
    {
        return $this->final_grade !== null && $this->final_grade >= 75;
    }

    public function computeFinalGrade(): float
    {
        // DepEd grading formula
        $ww = ($this->written_works ?? 0) * 0.25;
        $pt = ($this->performance_tasks ?? 0) * 0.50;
        $qa = ($this->quarterly_assessment ?? 0) * 0.25;
        return round($ww + $pt + $qa, 2);
    }
}