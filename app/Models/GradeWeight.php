<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Percentages of one subject in one section for one quarter. Read and written through GradeService. */
class GradeWeight extends Model
{
    protected $fillable = [
        'section_id',
        'subject_id',
        'quarter',
        'school_year',
        'written_works',
        'performance_tasks',
        'quarterly_assessment',
    ];

    /** @return array{written_works: int, performance_tasks: int, quarterly_assessment: int} */
    public function parts(): array
    {
        return [
            'written_works'        => (int) $this->written_works,
            'performance_tasks'    => (int) $this->performance_tasks,
            'quarterly_assessment' => (int) $this->quarterly_assessment,
        ];
    }
}
