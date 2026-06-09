<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Copy all existing teacher_subjects rows into teacher_assignments.
     * Uses insertOrIgnore so re-running is always safe.
     * The legacy teacher_subjects table is NOT modified or dropped.
     */
    public function up(): void
    {
        $schoolYearId = DB::table('school_years')
            ->where('is_active', true)
            ->value('id');

        $rows = DB::table('teacher_subjects')->get();

        foreach ($rows as $row) {
            DB::table('teacher_assignments')->insertOrIgnore([
                'user_id'           => $row->user_id,
                'section_id'        => $row->section_id,
                'subject_id'        => $row->subject_id,
                'school_year_id'    => $schoolYearId,
                'grade_level'       => $row->grade_level,
                'school_year_label' => $row->school_year,
                'is_adviser'        => false,
                'status'            => 'active',
                'created_at'        => $row->created_at ?? now(),
                'updated_at'        => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Remove only the backfilled rows; leave any rows added by the new system
        $schoolYearId = DB::table('school_years')
            ->where('label', '2025-2026')
            ->value('id');

        if ($schoolYearId) {
            DB::table('teacher_assignments')
                ->where('school_year_id', $schoolYearId)
                ->where('is_adviser', false)
                ->delete();
        }
    }
};