<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClassScheduleController extends Controller
{
    /** Weekly schedule of the selected section. */
    public function index(Request $request)
    {
        $sections = Section::where('is_active', true)
            ->orderByRaw('CAST(grade_level AS UNSIGNED)')
            ->orderBy('name')
            ->get();

        // Only from the active list — an unknown or inactive section_id falls back
        // to the first section instead of "Create a section first".
        $section = $sections->firstWhere('id', $request->integer('section_id')) ?? $sections->first();

        $schedules = collect();
        $subjects  = collect();
        // Senior High: one semester at a time (the one in the address, else the current one)
        $currentSemester = SchoolYear::currentSemester();
        $isShs = $section && ClassSchedule::isShsGrade($section->grade_level);
        $semester = $isShs ? (in_array($request->integer('semester'), [1, 2], true) ? $request->integer('semester') : $currentSemester) : null;

        if ($section) {
            $schedules = ClassSchedule::with(['subject', 'teacher'])
                ->where('section_id', $section->id)
                ->when($isShs, fn ($q) => $q->where('semester', $semester))
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get()
                ->groupBy('day_of_week');

            // The subject must match the section's grade level
            $subjects = Subject::where('is_active', true)
                ->where('grade_level', $section->grade_level)
                ->orderBy('name')
                ->get();
        }

        $teachers = User::where('role', 'teacher')
            ->where('status', '!=', 'inactive')   // teachers who left the school
            ->orderBy('name')
            ->get(['id', 'name', 'status']);

        return view('admin.schedules.index', compact(
            'sections', 'section', 'schedules', 'subjects', 'teachers', 'isShs', 'semester', 'currentSemester'
        ));
    }

    /** Which semester Senior High students see now (the school's switch from 1st to 2nd semester). */
    public function setSemester(Request $request)
    {
        $data = $request->validate(['semester' => 'required|integer|in:1,2'], ['semester.in' => 'Choose the 1st or 2nd semester.']);
        $year = SchoolYear::current();
        if (!$year) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Set an active school year first.', 'errors' => ['general' => ['Set an active school year first.']]], 422);
            }
            return back()->withErrors(['general' => 'Set an active school year first.']);
        }
        $year->forceFill(['current_semester' => (int) $data['semester']])->save();
        $label = ClassSchedule::SEMESTERS[(int) $data['semester']];
        AuditLogService::log("Current semester set to {$label}", 'Schedules', "School year {$year->label}");

        $message = "Grade 11 and 12 students now see the {$label} timetable.";
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /** Add a class on one or more days (days[]), all or nothing: one clash and none are saved. */
    public function store(Request $request)
    {
        $request->validate([
            'days'   => 'nullable|array|min:1|max:5',
            'days.*' => 'integer|between:1,5|distinct',
        ], ['days.min' => 'Choose at least one day.', 'days.*.between' => 'Classes can only be scheduled Monday to Friday.']);

        $days = $request->filled('days')
            ? array_values(array_unique(array_map('intval', $request->input('days'))))
            : [(int) $request->input('day_of_week')];
        sort($days);
        $base = $this->validated($request->merge(['day_of_week' => $days[0]]));

        $created = DB::transaction(function () use ($days, $base) {
            $rows = [];
            foreach ($days as $day) {
                $data = ['day_of_week' => $day] + $base;
                $this->guardAgainstConflicts($data);
                $rows[] = ClassSchedule::create($data);
            }

            return $rows;
        });
        $this->grantLoad($created[0]);

        foreach ($created as $schedule) {
            AuditLogService::log('Created class schedule', 'Schedules', $this->describe($schedule));
        }

        $dayNames = implode(', ', array_map(fn ($d) => substr(ClassSchedule::DAYS[$d], 0, 3), $days));
        $message  = ($created[0]->subject?->name ?? 'Class') . " added on {$dayNames}.";
        $last     = ['days' => $days, 'end' => substr((string) $base['end_time'], 0, 5)];

        // From the page itself: answer in place; the refreshed timetable continues at the next period
        if ($request->expectsJson()) {
            session()->flash('sch_last', $last);
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return back()->with('success', $message)->with('sch_last', $last);
    }

    public function update(Request $request, ClassSchedule $schedule)
    {
        $data = $this->validated($request);
        $this->guardAgainstConflicts($data, $schedule->id);

        $before = $schedule->only(['teacher_id', 'subject_id', 'section_id']);
        $schedule->update($data);
        $this->grantLoad($schedule);
        $this->releaseLoad($before);

        AuditLogService::log(
            'Updated class schedule',
            'Schedules',
            $this->describe($schedule->fresh())
        );

        if ($request->expectsJson()) {
            $fresh = $schedule->fresh(['subject']);
            return response()->json(['ok' => true, 'message' => ($fresh->subject?->name ?? 'Class') . " on {$fresh->day_name} updated."]);
        }

        return back()->with('success', 'Schedule updated.');
    }

    public function destroy(Request $request, ClassSchedule $schedule)
    {
        $label  = $this->describe($schedule);
        $before = $schedule->only(['teacher_id', 'subject_id', 'section_id']);
        $schedule->delete();
        $this->releaseLoad($before);

        AuditLogService::log('Deleted class schedule', 'Schedules', $label);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'Class removed from the timetable.']);
        }

        return back()->with('success', 'Schedule removed.');
    }

    // ─── Teaching load (teacher_subjects) ─────────────────────────────────────
    //
    // teacher_subjects drives the gradebook, materials, assignments and the
    // student's "My Subjects", and no other screen creates it — assigning a
    // teacher to a class here is what gives them the teaching load.

    private function grantLoad(ClassSchedule $s): void
    {
        if (!$s->teacher_id) {
            return;
        }
        TeacherSubject::firstOrCreate(
            ['user_id' => $s->teacher_id, 'subject_id' => $s->subject_id, 'section_id' => $s->section_id],
            ['grade_level' => (string) ($s->section?->grade_level ?? ''), 'school_year' => $s->school_year]
        );
    }

    /** Remove the load only when no other class still gives this teacher this subject in this section. */
    private function releaseLoad(array $before): void
    {
        if (empty($before['teacher_id'])) {
            return;
        }
        $stillTeaches = ClassSchedule::where('teacher_id', $before['teacher_id'])
            ->where('subject_id', $before['subject_id'])
            ->where('section_id', $before['section_id'])
            ->exists();

        if (!$stillTeaches) {
            TeacherSubject::where('user_id', $before['teacher_id'])
                ->where('subject_id', $before['subject_id'])
                ->where('section_id', $before['section_id'])
                ->delete();
        }
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
            'semester'    => 'nullable|integer|in:1,2',
        ], [
            'semester.in'         => 'Choose the 1st or 2nd semester.',
            'day_of_week.between' => 'Classes can only be scheduled Monday to Friday.',
            'end_time.after'      => 'End time must be later than the start time.',
        ]);

        $section = Section::findOrFail($data['section_id']);
        $subject = Subject::findOrFail($data['subject_id']);

        // Senior High classes belong to one semester; Junior High classes run the whole year
        if (ClassSchedule::isShsGrade($section->grade_level)) {
            if (empty($data['semester'])) {
                throw ValidationException::withMessages(['semester' => 'Choose the 1st or 2nd semester for a Senior High class.']);
            }
            $data['semester'] = (int) $data['semester'];
        } else {
            $data['semester'] = null;
        }

        // Grade levels must match — no Grade 7 Math in a Grade 10 section
        if ((string) $subject->grade_level !== (string) $section->grade_level) {
            throw ValidationException::withMessages([
                'subject_id' => "\"{$subject->name}\" is a Grade {$subject->grade_level} subject "
                              . "and cannot be scheduled for a Grade {$section->grade_level} section.",
            ]);
        }

        // The selected user must really be a teacher
        if (!empty($data['teacher_id'])) {
            $isTeacher = User::where('id', $data['teacher_id'])->where('role', 'teacher')->where('status', '!=', 'inactive')->exists();
            if (!$isTeacher) {
                throw ValidationException::withMessages([
                    'teacher_id' => 'The selected person is not an active teacher.',
                ]);
            }
        }

        $room                = trim((string) ($data['room'] ?? ''));   // the field may be left out entirely
        $data['room']        = $room === '' ? null : $room;
        $data['school_year'] = $section->school_year
            ?? optional(SchoolYear::current())->label
            ?? '2026-2027';

        return $data;
    }

    /** Stop the save when another class overlaps. */
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
            '%s — %s, %s (%s)' . ($s->semester ? ', ' . ClassSchedule::SEMESTERS[$s->semester] : ''),
            $s->section?->name ?? '?',
            $s->subject?->name ?? '?',
            $s->day_name,
            $s->time_range
        );
    }
}