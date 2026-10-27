<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\ClassSchedule;
use App\Models\Grade;
use App\Models\LearningMaterial;
use App\Models\MaterialView;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Rules\NoBadWords;
use App\Services\EnrollmentService;
use App\Services\GradeService;
use Illuminate\Http\Request;

/*
 * No page here sets its own Cache-Control: Laravel's default (no-cache) keeps data fresh after
 * attendance/submissions and stops pages from showing again after logout on a shared computer.
 */
class StudentDashboardController extends Controller
{
    public function index()
    {
        $user       = auth()->user();
        $enrollment = $user->studentEnrollment;
        $section    = $enrollment?->section ?? $user->section;
        $grade      = $enrollment?->grade_level ?? $user->grade_level;

        $subjects = $section
            ? TeacherSubject::with(['subject', 'teacher'])
                ->where('section_id', $section->id)
                ->get()
                ->unique('subject_id')
            : collect();

        // Published, still open, and not yet submitted
        $sectionId = $user->studentSectionId();
        $pendingAssignments = $sectionId
            ? Assignment::where('section_id', $sectionId)
                ->where('is_published', true)
                ->where(fn ($q) => $q->whereNull('due_date')->orWhere('due_date', '>', now()))
                ->whereDoesntHave('submissions', fn ($q) => $q->where('user_id', $user->id))
                ->count()
            : 0;

        return view('student.dashboard', [
            'user'               => $user,
            'enrollment'         => $enrollment,
            // Decides the next step shown: fill in the form / wait for the adviser / returned
            'enrollmentRequest'  => $enrollment ? null : $user->enrollmentRequest,
            'section'            => $section,
            'grade'              => $grade,
            'subjects'           => $subjects,
            'subjectCount'       => $subjects->count(),
            'pendingAssignments' => $pendingAssignments,
            'unreadMessages'     => $user->receivedMessages()->where('is_read', false)->count(),
            'announcements'      => Announcement::published()
                                        ->forAudience('student')
                                        ->visibleToSections([$section?->id])
                                        ->with('author')
                                        ->latest()
                                        ->take(5)
                                        ->get(),
        ]);
    }

    public function modules()
    {
        $user       = auth()->user();
        $enrollment = $user->studentEnrollment;
        $section    = $enrollment?->section ?? $user->section;

        $teacherSubjects = $section
            ? TeacherSubject::with(['subject', 'teacher'])   // teacher eager-loaded (not one query per subject)
                ->where('section_id', $section->id)
                ->get()
                ->unique('subject_id')
                ->sortBy(fn ($ts) => mb_strtolower($ts->subject?->name ?? ''))
            : collect();

        $materials = LearningMaterial::with(['subject', 'teacher'])
            ->whereIn('subject_id', $teacherSubjects->pluck('subject_id'))
            ->where('is_published', true)
            ->latest()
            ->get()
            ->groupBy('subject_id');

        return view('student.modules', compact('materials', 'teacherSubjects', 'section'));
    }

    /**
     * Open a material's file and count the view for the teacher (web views count too, not only the app).
     */
    public function openMaterial(LearningMaterial $material)
    {
        $sectionId  = auth()->user()->studentSectionId();
        $subjectIds = $sectionId ? TeacherSubject::where('section_id', $sectionId)->pluck('subject_id') : collect();

        abort_unless($material->is_published && $subjectIds->contains($material->subject_id) && $material->file_path, 404);

        MaterialView::firstOrCreate([
            'learning_material_id' => $material->id,
            'user_id'              => auth()->id(),
        ]);

        return redirect()->away(asset('storage/' . $material->file_path));
    }

    /**
     * The student's class schedule — Monday to Friday.
     *
     * Read through the SECTION, so it appears on its own once the student
     * is enrolled and placed in a section.
     */
    public function schedule()
    {
        $user       = auth()->user();
        $enrollment = $user->studentEnrollment;
        $section    = $enrollment?->section ?? $user->section;

        $section?->loadMissing('adviser:id,name');   // used by the view

        $schedules = $section
            ? ClassSchedule::with(['subject', 'teacher'])
                ->where('section_id', $section->id)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get()
                ->groupBy('day_of_week')
            : collect();

        return view('student.schedule', [
            'section'    => $section,
            'enrollment' => $enrollment,
            'schedules'  => $schedules,
            'days'       => ClassSchedule::DAYS,
        ]);
    }

    /** Grades per school year (newest first): Q1–Q4 and an average per subject, plus a general average. */
    public function grades()
    {
        $grades = auth()->user()->grades()->with('subject:id,name,code')->get();

        return view('student.grades', [
            'years'   => GradeService::reportCard($grades),
            'passing' => Grade::PASSING,
        ]);
    }

    /**
     * One conversation per teacher, oldest message first. Opening a conversation marks
     * that teacher's messages as read, so the dashboard's unread count goes down.
     */
    public function messages(Request $request)
    {
        $user    = auth()->user();
        $me      = (int) $user->id;
        $allowed = User::teacherIdsForSection($user->studentSectionId());   // teachers of my section

        $all = \App\Models\Message::where(fn ($q) => $q->where('sender_id', $me)->orWhere('receiver_id', $me))
            ->orderBy('created_at')->orderBy('id')
            ->get();
        $threads = $all->groupBy(fn ($m) => (int) ((int) $m->sender_id === $me ? $m->receiver_id : $m->sender_id));

        // My teachers + any teacher I already talked to (e.g. a former teacher: readable, but no replies)
        $contacts = User::whereIn('id', $allowed->merge($threads->keys())->unique())
            ->where('role', 'teacher')
            ->get(['id', 'name'])
            ->map(function ($t) use ($threads, $me, $allowed) {
                $msgs          = $threads->get($t->id, collect());
                $t->last       = $msgs->last();
                $t->unread     = $msgs->where('receiver_id', $me)->where('is_read', false)->count();
                $t->canMessage = $allowed->contains((int) $t->id);
                return $t;
            })
            // conversations with activity first (newest), then the rest by name
            ->sortBy(fn ($t) => sprintf('%d|%015d|%s', $t->last ? 0 : 1, PHP_INT_MAX - ($t->last?->created_at?->timestamp ?? 0), mb_strtolower($t->name)))
            ->values();

        $activeId = (int) $request->query('with');
        $active   = $contacts->firstWhere('id', $activeId)
            ?? $contacts->first(fn ($t) => $t->unread > 0)
            ?? $contacts->first();

        $thread = collect();
        if ($active) {
            \App\Models\Message::where('sender_id', $active->id)->where('receiver_id', $me)->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
            $active->unread = 0;
            $thread = $threads->get($active->id, collect());
        }

        return view('student.messages', compact('contacts', 'active', 'thread'));
    }

    public function storeMessage(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'body'       => ['required', 'string', 'max:2000', new NoBadWords],
        ]);

        // The recipient must be a teacher of the student's section
        $allowed = User::teacherIdsForSection($user->studentSectionId());
        if (! $allowed->contains((int) $data['teacher_id'])) {
            return back()->withErrors(['teacher_id' => 'You can only message your own teachers.'])->withInput();
        }

        \App\Models\Message::create([
            'sender_id'   => $user->id,
            'receiver_id' => $data['teacher_id'],
            'body'        => $data['body'],
            'is_read'     => false,
        ]);

        // Back to the same conversation
        return redirect()->route('student.messages', ['with' => $data['teacher_id']]);
    }

    public function subjects()
    {
        $user       = auth()->user();
        $enrollment = $user->studentEnrollment;
        $section    = $enrollment?->section ?? $user->section;

        $subjects = $section
            ? TeacherSubject::with(['subject', 'teacher'])
                ->where('section_id', $section->id)
                ->get()
                ->unique('subject_id')
            : collect();

        return view('student.subjects', compact('subjects', 'enrollment', 'section'));
    }

    public function enrollmentForm()
    {
        $user    = auth()->user();
        $request = $user->enrollmentRequest;

        // Pending — or approved for THIS school year. An approval from last year must not
        // block enrolling for the next school year.
        $currentSy = EnrollmentService::currentSchoolYear();
        if ($request && ($request->status === 'pending'
                || ($request->status === 'approved' && $request->school_year === $currentSy))) {
            return redirect()->route('student.dashboard')
                ->with('info', $request->status === 'pending'
                    ? 'Your enrollment is pending review.'
                    : 'You are already enrolled.');
        }

        return view('student.enrollment-form', [
            'user'      => $user,
            'request'   => $request,
            // Set by the server: active school year + grade from the LRN masterlist
            'activeSy'  => \App\Models\SchoolYear::active()->first(),
            'userGrade' => $user->grade_level,
        ]);
    }

    public function submitEnrollment(Request $request, EnrollmentService $enrollments)
    {
        // Same rules for web and API (EnrollmentService)
        $data = $request->validate(EnrollmentService::rules(), EnrollmentService::messages());

        $enrollments->submit(auth()->user(), $data);

        return redirect()->route('student.dashboard')
            ->with('success', 'Enrollment form submitted! Please wait for faculty review.');
    }
}
