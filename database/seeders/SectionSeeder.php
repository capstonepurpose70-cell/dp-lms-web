<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['7', 'Sampaguita'], ['7', 'Rosal'], ['7', 'Jasmine'],
            ['8', 'Narra'],      ['8', 'Molave'], ['8', 'Acacia'],
            ['9', 'Rizal'],      ['9', 'Bonifacio'], ['9', 'Mabini'],
            ['10', 'Magiting'],  ['10', 'Maagap'], ['10', 'Masipag'],
            ['11', 'STEM-A'],    ['11', 'ABM-A'],  ['11', 'HUMSS-A'],
            ['12', 'STEM-A'],    ['12', 'ABM-A'],  ['12', 'HUMSS-A'],
        ];

        foreach ($sections as [$grade, $name]) {
            Section::firstOrCreate(
                ['name' => $name, 'grade_level' => $grade],
                ['school_year' => '2025-2026', 'is_active' => true]
            );
        }
    }
}