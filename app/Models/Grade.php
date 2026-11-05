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
        'weight_written_works',
        'weight_performance_tasks',
        'weight_quarterly_assessment',
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

    /** Percentages of a quarter grade unless the teacher set others for the class (GradeService). */
    public const DEFAULT_WEIGHTS = [
        'written_works'        => 25,
        'performance_tasks'    => 50,
        'quarterly_assessment' => 25,
    ];

    public function isPassed(): bool
    {
        return $this->final_grade !== null && $this->final_grade >= self::PASSING;
    }

    /** The percentages this grade was computed with. Grades older than class percentages used the default. */
    public function weights(): array
    {
        if ($this->weight_written_works === null) {
            return self::DEFAULT_WEIGHTS;
        }

        return [
            'written_works'        => (int) $this->weight_written_works,
            'performance_tasks'    => (int) $this->weight_performance_tasks,
            'quarterly_assessment' => (int) $this->weight_quarterly_assessment,
        ];
    }

    /** Quarter grade from its components. Shared by the web gradebook and the API (GradeService). */
    public static function computeFinal(?float $ww, ?float $pt, ?float $qa, array $weights = self::DEFAULT_WEIGHTS): float
    {
        return round(
            (($ww ?? 0) * $weights['written_works']
            + ($pt ?? 0) * $weights['performance_tasks']
            + ($qa ?? 0) * $weights['quarterly_assessment']) / 100,
            2
        );
    }
}