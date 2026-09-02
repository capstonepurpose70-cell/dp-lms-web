<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClassScheduleController extends Controller
{
    /** Weekly schedule ng napiling section. */
    public function index(Request $request)
    {
        $sections = Section::where('is_active', true)
            ->orderByRaw('CAST(grade_level AS UNSIGNED)')
            ->orderBy('name')
            ->get();

        $sectionId = $request->integer('section_id') ?: $sections->first()?->id;
        $section   = $sectionId ? Section::find($sectionId) : null;

        $schedules = collect();
        $subjects  = collect();

        if ($section) {
            $schedules = ClassSchedule::with(['subject', 'teacher'])
                ->where('section_id', $section->id)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get()
                ->groupBy('day_of_week');

            // Ang subject ay dapat tugma sa grade level ng section
            $subjects = Subject::where('is_active', true)
                ->where('grade_level', $section->grade_level)
                ->orderBy('name')
                ->get();
        }

        $teachers = User::where('role', 'teacher')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.schedules.index', compact(
            'sections', 'section', 'schedules', 'subjects', 'teachers'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->guardAgainstConflicts($data);

        $schedule = ClassSchedule::create($data);

        AuditLogService::log(
            'Created class schedule',
            'Schedules',
            $this->describe($schedule)
        );

        return back()->with('success', 'Schedule added.');
    }

    public function update(Request $request, ClassSchedule $schedule)
    {
        $data = $this->validated($request);
        $this->guardAgainstConflicts($data, $schedule->id);

        $schedule->update($data);

        AuditLogService::log(
            'Updated class schedule',
            'Schedules',
            $this->describe($schedule->fresh())
        );

        return back()->with('success', 'Schedule updated.');
    }

    public function destroy(ClassSchedule $schedule)
    {
        $label = $this->describe($schedule);
        $schedule->delete();

        AuditLogService::log('Deleted class schedule', 'Schedules', $label);

        return back()->with('success', 'Schedule removed.');
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'section_id'  => 'required|exists:sections,id',
            'subject_id'  => 'required|exists:subjects,id',
            'teacher_id'  => 'nullable|exists:users,id',
            'day_of_week' => 'required|integer|between:1,5',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'room'        => 'nullable|string|max:60',
        ], [
            'day_of_week.between' => 'Classes can only be scheduled Monday to Friday.',
            'end_time.after'      => 'End time must be later than the start time.',
        ]);

        $section = Section::findOrFail($data['section_id']);
        $subject = Subject::findOrFail($data['subject_id']);

        // Dapat tugma ang grade level — walang Grade 7 Math sa Grade 10 section
        if ((string) $subject->grade_level !== (string) $section->grade_level) {
            throw ValidationException::withMessages([
                'subject_id' => "\"{$subject->name}\" is a Grade {$subject->grade_level} subject "
                              . "and cannot be scheduled for a Grade {$section->grade_level} section.",
            ]);
        }

        // Dapat guro talaga ang napili
        if (!empty($data['teacher_id'])) {
            $isTeacher = User::where('id', $data['teacher_id'])->where('role', 'teacher')->exists();
            if (!$isTeacher) {
                throw ValidationException::withMessages([
                    'teacher_id' => 'The selected person is not a teacher.',
                ]);
            }
        }

        $data['room']        = $data['room'] !== null ? trim($data['room']) : null;
        $data['room']        = $data['room'] === '' ? null : $data['room'];
        $data['school_year'] = $section->school_year
            ?? optional(SchoolYear::current())->label
            ?? '2026-2027';

        return $data;
    }

    /** Ihinto ang pag-save kung may sumasapaw na klase. */
    private function guardAgainstConflicts(array $data, ?int $ignoreId = null): void
    {
        $conflict = ClassSchedule::findConflict($data, $ignoreId);

        if (!$conflict) {
            return;
        }

        /** @var ClassSchedule $other */
        $other = $conflict['schedule'];
        $when  = $other->day_name . ', ' . $other->time_range;

        $message = match ($conflict['type']) {
            'section' => "This section already has {$other->subject?->name} on {$when}.",
            'teacher' => "{$other->teacher?->name} is already teaching "
                       . "{$other->subject?->name} ({$other->section?->name}) on {$when}.",
            'room'    => "Room {$other->room} is already used by "
                       . "{$other->section?->name} on {$when}.",
        };

        $field = match ($conflict['type']) {
            'section' => 'start_time',
            'teacher' => 'teacher_id',
            'room'    => 'room',
        };

        throw ValidationException::withMessages([$field => $message]);
    }

    private function describe(ClassSchedule $s): string
    {
        $s->loadMissing(['section', 'subject']);

        return sprintf(
            '%s — %s, %s (%s)',
            $s->section?->name ?? '?',
            $s->subject?->name ?? '?',
            $s->day_name,
            $s->time_range
        );
    }
}