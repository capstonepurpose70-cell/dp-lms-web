<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Palitan ang mga DUMMY section ng TUNAY na section ng Sto. Domingo NHS
 * para sa S.Y. 2026-2027.
 *
 * Ang mga dating section (Sampaguita, Rosal, Narra, Rizal, STEM-A, atbp.)
 * ay galing sa SectionSeeder — panakip-butas lang habang binubuo ang system.
 *
 * ⚠️ MAHALAGA — bakit hindi basta-basta binubura:
 * Ang sections ay may cascadeOnDelete sa enrollments, student_enrollments,
 * assignments, quizzes, teacher_subjects, at teacher_assignments. Kapag
 * binura ang isang section, KASAMA nitong mabubura ang lahat ng record
 * na nakakabit dito. Kaya bago burahin ang bawat dummy, tinitingnan muna
 * kung may nakakabit dito. Kung meron, HINDI ito binubura — ide-deactivate
 * lang (is_active = false) para hindi na ito lumabas sa mga dropdown.
 *
 * Grade 11-12: pinapanatili ang STEM-A / ABM-A / HUMSS-A bilang
 * PANSAMANTALANG pangalan. Palitan sa admin panel (RENAME, hindi
 * delete) kapag alam na ang tunay na pangalan.
 *
 * Ligtas patakbuhin nang paulit-ulit (idempotent).
 */
return new class extends Migration
{
    /** Tunay na sections — Sto. Domingo NHS, S.Y. 2026-2027 */
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

    /** Mga dummy na galing sa SectionSeeder */
    private const DUMMY_SECTIONS = [
        ['7',  'Sampaguita'], ['7',  'Rosal'],   ['7',  'Jasmine'],
        ['8',  'Narra'],      ['8',  'Molave'],  ['8',  'Acacia'],
        ['9',  'Rizal'],      ['9',  'Bonifacio'], ['9', 'Mabini'],
        ['10', 'Magiting'],   ['10', 'Maagap'],  ['10', 'Masipag'],
    ];

    /**
     * SENIOR HIGH — PANSAMANTALANG PANGALAN.
     *
     * Hindi pa alam ang tunay na pangalan ng SHS sections, kaya
     * pinapanatili ang mga strand-based na pangalan bilang placeholder
     * para may mapaglagyan ang mga Grade 11-12 na estudyante.
     *
     * KAPAG ALAM NA ANG TUNAY NA PANGALAN:
     * I-EDIT lang sa Admin > Sections & Advisers — HUWAG burahin at
     * gumawa ng bago. Ang pag-rename ay nananatili sa parehong row,
     * kaya hindi mapuputol ang mga naka-attach na enrollment,
     * assignment, quiz, at attendance. Ang pagbura ay cascade delete.
     */
    private const PLACEHOLDER_SHS = [
        ['grade' => '11', 'name' => 'STEM-A'],
        ['grade' => '11', 'name' => 'ABM-A'],
        ['grade' => '11', 'name' => 'HUMSS-A'],
        ['grade' => '12', 'name' => 'STEM-A'],
        ['grade' => '12', 'name' => 'ABM-A'],
        ['grade' => '12', 'name' => 'HUMSS-A'],
    ];

    /** Mga table na kailangang i-check bago magbura ng section */
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

        // ── 1. Gawin ang tunay na sections ──────────────────────────────
        foreach (array_merge(self::REAL_SECTIONS, self::PLACEHOLDER_SHS) as $s) {
            $exists = DB::table('sections')
                ->where('name', $s['name'])
                ->where('grade_level', $s['grade'])
                ->exists();

            if ($exists) {
                // Siguraduhing aktibo at tama ang school year
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

        // ── 2. Alisin ang mga dummy — ligtas na paraan ──────────────────
        foreach (self::DUMMY_SECTIONS as [$grade, $name]) {
            $section = DB::table('sections')
                ->where('name', $name)
                ->where('grade_level', $grade)
                ->first();

            if (!$section) {
                continue;
            }

            if ($this->hasDependents($section->id)) {
                // May nakakabit na data — HUWAG burahin, i-deactivate lang.
                DB::table('sections')
                    ->where('id', $section->id)
                    ->update(['is_active' => false, 'updated_at' => now()]);
                continue;
            }

            // Walang nakakabit — ligtas nang burahin.
            DB::table('sections')->where('id', $section->id)->delete();
        }
    }

    /** May record ba sa kahit anong table na nakakabit sa section na ito? */
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
        // Ibalik ang mga dummy na na-deactivate. Hindi ibinabalik ang mga
        // nabura dahil wala naman silang laman noong binura sila.
        foreach (self::DUMMY_SECTIONS as [$grade, $name]) {
            DB::table('sections')
                ->where('name', $name)
                ->where('grade_level', $grade)
                ->update(['is_active' => true, 'updated_at' => now()]);
        }

        // Alisin ang tunay na sections — kung wala lang silang laman.
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