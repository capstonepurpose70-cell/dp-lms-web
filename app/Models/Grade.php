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

    public const PASSING = 75;

    /** Component weights of a quarter grade. Views read these for their labels. */
    public const WEIGHTS = [
        'written_works'        => 0.25,
        'performance_tasks'    => 0.50,
        'quarterly_assessment' => 0.25,
    ];

    public function isPassed(): bool
    {
        return $this->final_grade !== null && $this->final_grade >= self::PASSING;
    }

    /** Quarter grade from its components. Shared by the web gradebook and the API (GradeService). */
    public static function computeFinal(?float $ww, ?float $pt, ?float $qa): float
    {
        return round(
            ($ww ?? 0) * self::WEIGHTS['written_works']
            + ($pt ?? 0) * self::WEIGHTS['performance_tasks']
            + ($qa ?? 0) * self::WEIGHTS['quarterly_assessment'],
            2
        );
    }
}