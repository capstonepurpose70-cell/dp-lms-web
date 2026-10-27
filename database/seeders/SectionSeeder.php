<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\SchoolYear;
use Illuminate\Database\Seeder;

/**
 * Real sections of Sto. Domingo NHS — S.Y. 2026-2027,
 * taken from the school's class lists.
 *
 * Grade 11-12: placeholders for now (STEM-A/ABM-A/HUMSS-A).
 */
class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $schoolYear = SchoolYear::current()?->label ?? '2026-2027';

        $sections = [
            ['7',  'CALIMLIM'],
            ['7',  'GUTIERREZ'],
            ['8',  'TUVERA'],
            ['9',  'GANTE'],
            ['9',  'LAMPIOS'],
            ['9',  'SARIBAY'],
            ['10', 'FABRO'],
            ['10', 'NICOLAS'],

            // Grade 11-12 — TEMPORARY names until the real ones are
            // known. Change them in Admin > Sections & Advisers with
            // EDIT (rename), not delete — deleting cascades.
            ['11', 'STEM-A'],
            ['11', 'ABM-A'],
            ['11', 'HUMSS-A'],
            ['12', 'STEM-A'],
            ['12', 'ABM-A'],
            ['12', 'HUMSS-A'],
        ];

        foreach ($sections as [$grade, $name]) {
            Section::firstOrCreate(
                ['name' => $name, 'grade_level' => $grade],
                ['school_year' => $schoolYear, 'is_active' => true]
            );
        }
    }
}