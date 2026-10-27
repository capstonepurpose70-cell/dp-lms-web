<?php

namespace Database\Seeders;

use App\Models\ClassSchedule;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherSubject;
use Illuminate\Database\Seeder;

/**
 * TEMPORARY class schedule for every active section.
 *
 * ⚠️ THE TIMES HERE ARE GUESSES. This exists so students see something
 * while the school's real timetable is pending. Replace it in
 * Admin > Class Schedules once the real one is known.
 *
 * Method: each subject of a section gets one period per weekday, in
 * rotation — so nothing ever overlaps.
 *
 * Safe to run more than once (idempotent) — any section that already has
 * a schedule is skipped, so what the admin set is never wiped.
 */
class ClassScheduleSeeder extends Seeder
{
    /** Periods — 8:00 AM to 3:00 PM, with recess and lunch. */
    private const PERIODS = [
        ['08:00', '09:00'],
        ['09:00', '10:00'],
        // 10:00-10:15 recess
        ['10:15', '11:15'],
        ['11:15', '12:15'],
        // 12:15-13:00 lunch
        ['13:00', '14:00'],
        ['14:00', '15:00'],
    ];

    private const DAYS = [1, 2, 3, 4, 5]; // Lunes-Biyernes

    public function run(): void
    {
        $schoolYear = SchoolYear::current()?->label ?? '2026-2027';

        $sections = Section::where('is_active', true)
            ->orderByRaw('CAST(grade_level AS UNSIGNED)')
            ->orderBy('name')
            ->get();

        foreach ($sections as $section) {
            // Leave sections that already have a schedule alone.
            if (ClassSchedule::where('section_id', $section->id)->exists()) {
                $this->command?->line("  Nilaktawan (may schedule na): Grade {$section->grade_level} - {$section->name}");
                continue;
            }

            $subjects = Subject::where('is_active', true)
                ->where('grade_level', $section->grade_level)
                ->orderBy('name')
                ->get();

            if ($subjects->isEmpty()) {
                $this->command?->warn("  Walang subject para sa Grade {$section->grade_level} - {$section->name}");
                continue;
            }

            // Who teaches each subject in this section?
            $teacherBySubject = TeacherSubject::where('section_id', $section->id)
                ->pluck('user_id', 'subject_id');

            $created    = 0;
            $unassigned = 0;
            $slot       = 0; // walks through every (day x period)

            foreach ($subjects as $subject) {
                // Spread across the week before adding periods, so every day
                // has classes. (Filling periods first would fill Monday
                // before Tuesday even starts.)
                $dayIndex    = $slot % count(self::DAYS);
                $periodIndex = intdiv($slot, count(self::DAYS));
                $slot++;

                // Stop once every period is used.
                if ($periodIndex >= count(self::PERIODS)) {
                    break;
                }

                [$start, $end] = self::PERIODS[$periodIndex];

                $row = [
                    'section_id'  => $section->id,
                    'subject_id'  => $subject->id,
                    'teacher_id'  => $teacherBySubject[$subject->id] ?? null,
                    'day_of_week' => self::DAYS[$dayIndex],
                    'start_time'  => $start,
                    'end_time'    => $end,
                    'room'        => null, // set by the admin
                    'school_year' => $schoolYear,
                ];

                // One teacher can handle several sections. Since every
                // section starts at 8:00 on Monday, the teacher could be
                // double-booked. If they are busy at this time, leave the
                // period WITHOUT a teacher — a gap the admin fills in is
                // better than data that breaks the system's own rules.
                if (!empty($row['teacher_id']) && ClassSchedule::findConflict($row)) {
                    $row['teacher_id'] = null;
                    $unassigned++;
                }

                ClassSchedule::create($row);
                $created++;
            }

            $note = $unassigned ? " ({$unassigned} walang guro — abala sa ibang section)" : '';
            $this->command?->info("  Grade {$section->grade_level} - {$section->name}: {$created} class{$note}");
        }
    }
}