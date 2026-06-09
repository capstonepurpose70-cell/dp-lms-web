<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        // Grade 7-10 subjects (8 subjects each)
        $juniorSubjects = [
            'Filipino', 'English', 'Mathematics', 'Science',
            'Araling Panlipunan', 'MAPEH', 'TLE', 'Values Education',
        ];

        foreach (['7','8','9','10'] as $grade) {
            foreach ($juniorSubjects as $name) {
                Subject::firstOrCreate(
                    ['code' => strtoupper(substr($name, 0, 4)) . $grade],
                    [
                        'name'        => $name,
                        'grade_level' => $grade,
                        'is_active'   => true,
                    ]
                );
            }
        }

        // Grade 11-12 subjects (9 subjects)
        $seniorSubjects = [
            'Core English', 'Core Filipino', 'Core Math',
            'Core Science', 'Personal Development', 'Earth and Life Science',
            'Understanding Culture', 'Applied Economics', 'Empowerment Technologies',
        ];

        foreach (['11','12'] as $grade) {
            foreach ($seniorSubjects as $name) {
                Subject::firstOrCreate(
                    ['code' => strtoupper(substr($name, 0, 4)) . $grade],
                    [
                        'name'        => $name,
                        'grade_level' => $grade,
                        'is_active'   => true,
                    ]
                );
            }
        }
    }
}