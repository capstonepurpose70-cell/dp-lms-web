<?php

namespace Database\Seeders;

use App\Models\ClassSchedule;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherSubject;
use Illuminate\Database\Seeder;

/**
 * PANSAMANTALANG class schedule para sa lahat ng aktibong section.
 *
 * ⚠️ HULA LANG ANG MGA ORAS DITO. Ginawa ito para may makita agad ang
 * mga estudyante habang hinihintay ang tunay na timetable mula sa
 * paaralan. Palitan sa Admin > Class Schedules kapag alam na ang totoo.
 *
 * Paraan: bawat subject ng section ay binibigyan ng isang period kada
 * araw ng linggo, paikot — kaya walang nagsasapawan kailanman.
 *
 * Ligtas patakbuhin nang paulit-ulit (idempotent) — nilalaktawan ang
 * anumang section na may schedule na, para hindi mabura ang tunay na
 * naitakda na ng admin.
 */
class ClassScheduleSeeder extends Seeder
{
    /** Mga period — 8:00 AM hanggang 3:00 PM, may recess at lunch. */
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
            // Huwag galawin ang section na may schedule na.
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

            // Sino ang nagtuturo ng bawat subject sa section na ito?
            $teacherBySubject = TeacherSubject::where('section_id', $section->id)
                ->pluck('user_id', 'subject_id');

            $created    = 0;
            $unassigned = 0;
            $slot       = 0; // tumatakbo sa lahat ng (araw x period)

            foreach ($subjects as $subject) {
                // Ikalat muna sa buong linggo bago dumagdag ng period, para
                // may klase ang bawat araw. (Kung period muna ang pupunuin,
                // mapupuno ang Lunes bago pa magsimula ang Martes.)
                $dayIndex    = $slot % count(self::DAYS);
                $periodIndex = intdiv($slot, count(self::DAYS));
                $slot++;

                // Kung naubos na ang lahat ng period, tumigil.
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
                    'room'        => null, // itatakda ng admin
                    'school_year' => $schoolYear,
                ];

                // Iisang guro ay maaaring humawak ng maraming section. Dahil
                // pare-parehong 8:00 ng Lunes ang simula ng bawat section,
                // posibleng doble ang booking ng guro. Kung abala siya sa
                // oras na ito, iwanang WALANG guro ang period — mas mabuti
                // ang butas na pupunan ng admin kaysa sa datos na labag sa
                // sariling panuntunan ng system.
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