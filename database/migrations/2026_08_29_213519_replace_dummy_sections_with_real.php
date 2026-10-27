<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the DUMMY sections with the REAL sections of Sto. Domingo NHS
 * for S.Y. 2026-2027.
 *
 * The old sections (Sampaguita, Rosal, Narra, Rizal, STEM-A, etc.) came from
 * SectionSeeder — placeholders while the system was being built.
 *
 * ⚠️ IMPORTANT — why they are not simply deleted:
 * sections cascade on delete into enrollments, student_enrollments,
 * assignments, quizzes, teacher_subjects and teacher_assignments. Deleting a
 * section deletes every record attached to it. So each dummy is checked
 * first: if anything is attached it is NOT deleted, only deactivated
 * (is_active = false) so it no longer shows in dropdowns.
 *
 * Grade 11-12: STEM-A / ABM-A / HUMSS-A stay as TEMPORARY names.
 * Change them in the admin panel (RENAME, not delete) once the real
 * names are known.
 *
 * Safe to run more than once (idempotent).
 */
return new class extends Migration
{
    /** Real sections — Sto. Domingo NHS, S.Y. 2026-2027 */
    private const REAL_SECTIONS = [
        ['grade' => '7',  'name' => 'CALIMLIM'],
        ['grade' => '7',  'name' => 'GUTIERREZ'],
        ['grade' => '8',  'name' => 'TUVERA'],
        ['grade' => '9',  'name' => 'GANTE'],
        ['grade' => '9',  'name' => 'LAMPIOS'],
        ['grade' => '9',  'name' => 'SARIBAY'],
        ['grade' => '10', 'name' => 'FABRO'],
        ['grade' => '10', 'name' => 'NICOLAS'],
    ];

    /** Dummies created by SectionSeeder */
    private const DUMMY_SECTIONS = [
        ['7',  'Sampaguita'], ['7',  'Rosal'],   ['7',  'Jasmine'],
        ['8',  'Narra'],      ['8',  'Molave'],  ['8',  'Acacia'],
        ['9',  'Rizal'],      ['9',  'Bonifacio'], ['9', 'Mabini'],
        ['10', 'Magiting'],   ['10', 'Maagap'],  ['10', 'Masipag'],
    ];

    /**
     * SENIOR HIGH — TEMPORARY NAMES.
     *
     * The real SHS section names are not known yet, so the strand-based
     * names stay as placeholders so Grade 11-12 students have a section.
     *
     * ONCE THE REAL NAMES ARE KNOWN:
     * just EDIT them in Admin > Sections & Advisers — do NOT delete and
     * create new ones. A rename keeps the same row, so the attached
     * enrollments, assignments, quizzes and attendance stay connected.
     * Deleting cascades.
     */
    private const PLACEHOLDER_SHS = [
        ['grade' => '11', 'name' => 'STEM-A'],
        ['grade' => '11', 'name' => 'ABM-A'],
        ['grade' => '11', 'name' => 'HUMSS-A'],
        ['grade' => '12', 'name' => 'STEM-A'],
        ['grade' => '12', 'name' => 'ABM-A'],
        ['grade' => '12', 'name' => 'HUMSS-A'],
    ];

    /** Tables to check before deleting a section */
    private const DEPENDENTS = [
        'users'                => 'section_id',
        'enrollments'          => 'section_id',
        'student_enrollments'  => 'section_id',
        'assignments'          => 'section_id',
        'quizzes'              => 'section_id',
        'teacher_subjects'     => 'section_id',
        'teacher_assignments'  => 'section_id',
        'attendances'          => 'section_id',
        'announcements'        => 'section_id',
    ];

    public function up(): void
    {
        $schoolYear = DB::table('school_years')
            ->where('is_active', true)
            ->value('label') ?? '2026-2027';

        // ── 1. Create the real sections ───────────────────────────────────
        foreach (array_merge(self::REAL_SECTIONS, self::PLACEHOLDER_SHS) as $s) {
            $exists = DB::table('sections')
                ->where('name', $s['name'])
                ->where('grade_level', $s['grade'])
                ->exists();

            if ($exists) {
                // Make sure it is active and has the right school year
                DB::table('sections')
                    ->where('name', $s['name'])
                    ->where('grade_level', $s['grade'])
                    ->update([
                        'school_year' => $schoolYear,
                        'is_active'   => true,
                        'updated_at'  => now(),
                    ]);
                continue;
            }

            DB::table('sections')->insert([
                'name'        => $s['name'],
                'grade_level' => $s['grade'],
                'school_year' => $schoolYear,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // ── 2. Remove the dummies — the safe way ──────────────────────────
        foreach (self::DUMMY_SECTIONS as [$grade, $name]) {
            $section = DB::table('sections')
                ->where('name', $name)
                ->where('grade_level', $grade)
                ->first();

            if (!$section) {
                continue;
            }

            if ($this->hasDependents($section->id)) {
                // Data is attached — do NOT delete, only deactivate.
                DB::table('sections')
                    ->where('id', $section->id)
                    ->update(['is_active' => false, 'updated_at' => now()]);
                continue;
            }

            // Nothing attached — safe to delete.
            DB::table('sections')->where('id', $section->id)->delete();
        }
    }

    /** Does any table have a record attached to this section? */
    private function hasDependents(int $sectionId): bool
    {
        foreach (self::DEPENDENTS as $table => $column) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                continue;
            }
            if (DB::table($table)->where($column, $sectionId)->exists()) {
                return true;
            }
        }
        return false;
    }

    public function down(): void
    {
        // Re-activate the deactivated dummies. Deleted ones are not restored —
        // they were empty when they were deleted.
        foreach (self::DUMMY_SECTIONS as [$grade, $name]) {
            DB::table('sections')
                ->where('name', $name)
                ->where('grade_level', $grade)
                ->update(['is_active' => true, 'updated_at' => now()]);
        }

        // Remove the real sections — only while they are empty.
        foreach (array_merge(self::REAL_SECTIONS, self::PLACEHOLDER_SHS) as $s) {
            $section = DB::table('sections')
                ->where('name', $s['name'])
                ->where('grade_level', $s['grade'])
                ->first();

            if ($section && !$this->hasDependents($section->id)) {
                DB::table('sections')->where('id', $section->id)->delete();
            }
        }
    }
};