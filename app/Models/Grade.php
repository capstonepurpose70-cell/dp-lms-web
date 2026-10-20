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

    public function isPassed(): bool
    {
        return $this->final_grade !== null && $this->final_grade >= self::PASSING;
    }

    /**
     * Final grade: WW 25% + PT 50% + QA 25%.
     * Iisang formula para sa web gradebook at mobile API (GradeService).
     */
    public static function computeFinal(?float $ww, ?float $pt, ?float $qa): float
    {
        return round(($ww ?? 0) * 0.25 + ($pt ?? 0) * 0.50 + ($qa ?? 0) * 0.25, 2);
    }
}