<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\SchoolYear;
use Illuminate\Database\Seeder;

/**
 * Tunay na sections ng Sto. Domingo NHS — S.Y. 2026-2027.
 *
 * Dating naglalaman ito ng mga panakip-butas na pangalan (Sampaguita,
 * Rosal, Narra, Rizal, STEM-A...). Pinalitan na ng aktwal na sections
 * mula sa class lists ng paaralan.
 *
 * Grade 11-12: placeholder muna (STEM-A/ABM-A/HUMSS-A).
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

            // Grade 11-12 — PANSAMANTALANG pangalan habang hindi pa alam
            // ang tunay. Palitan sa Admin > Sections & Advisers gamit ang
            // EDIT (rename), hindi delete — cascade delete ang pagbura.
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