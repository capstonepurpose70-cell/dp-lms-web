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
                $this->ensure($name, $grade);
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
                $this->ensure($name, $grade);
            }
        }
    }

    /**
     * Looked up by name + grade (not code). Codes stay unique even when names
     * start the same ("Core English/Filipino/Math/Science" are not all "CORE11").
     */
    private function ensure(string $name, string $grade): void
    {
        if (Subject::where('name', $name)->where('grade_level', $grade)->exists()) {
            return;
        }

        $base = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 4)) . $grade;
        $code = $base;
        for ($i = 2; Subject::where('code', $code)->exists(); $i++) {
            $code = $base . '-' . $i;
        }

        Subject::create([
            'code'        => $code,
            'name'        => $name,
            'grade_level' => $grade,
            'is_active'   => true,
        ]);
    }
}