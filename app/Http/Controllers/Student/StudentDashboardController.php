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
use Illuminate\Http\Request;

/*
 * Dati: lahat ng page dito ay may "Cache-Control: private, max-age=30" — hanggang 30 segundong
 * lumang data (hal. pagkatapos mag-attendance/mag-submit), at nakikita pa ang page pagkatapos
 * mag-logout sa shared na computer. Tinanggal; ang default ng Laravel (no-cache) na ang gamit.
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

        // Published, still open, and not yet submitted (dati laging 0)
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
            // Para sa tamang susunod na hakbang: sagutan ang form / hintayin ang adviser / na-reject
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
            ? TeacherSubject::with(['subject', 'teacher'])   // teacher: dati isang query kada subject
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
     * Open a material's file and count the view for the teacher.
     * Dati: ang "Views" sa teacher ay galing lang sa mobile app — hindi nabibilang ang web.
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
     * Class schedule ng estudyante — Lunes hanggang Biyernes.
     *
     * Nakuha sa pamamagitan ng SECTION, kaya awtomatiko itong lumalabas
     * kapag na-enroll na siya at may naitalagang section.
     */
    public function schedule()
    {
        $user       = auth()->user();
        $enrollment = $user->studentEnrollment;
        $section    = $enrollment?->section ?? $user->section;

        $section?->loadMissing('adviser:id,name');   // B6-10: ginagamit ng view

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

    /**
     * Grades per school year (newest first) → per subject: Q1–Q4, average of the recorded
     * quarters, and a general average. Dati: walang average, at ibang hitsura sa ibang page.
     */
    public function grades()
    {
        $years = auth()->user()
            ->grades()
            ->with('subject:id,name,code')
            ->get()
            ->groupBy(fn ($g) => $g->school_year ?: 'Unspecified')
            ->sortKeysDesc()
            ->map(function ($yearGrades) {
                $subjects = $yearGrades->groupBy('subject_id')->map(function ($rows) {
                    $byQuarter = $rows->keyBy('quarter');
                    $finals    = $rows->pluck('final_grade')->filter(fn ($v) => $v !== null)->map(fn ($v) => (float) $v);
                    $average   = $finals->isNotEmpty() ? round($finals->avg(), 2) : null;

                    return [
                        'subject'  => $rows->first()->subject,
                        'quarters' => collect(['Q1', 'Q2', 'Q3', 'Q4'])->mapWithKeys(fn ($q) => [$q => $byQuarter->get($q)]),
                        'average'  => $average,
                        'complete' => $finals->count() === 4,
                        'passed'   => $average !== null && $average >= Grade::PASSING,
                    ];
                })->sortBy(fn ($s) => mb_strtolower($s['subject']?->name ?? ''))->values();

                $averages = $subjects->pluck('average')->filter(fn ($v) => $v !== null);

                return [
                    'subjects' => $subjects,
                    'general'  => $averages->isNotEmpty() ? round($averages->avg(), 2) : null,
                    'complete' => $subjects->isNotEmpty() && $subjects->every(fn ($s) => $s['complete']),
                ];
            });

        return view('student.grades', ['years' => $years, 'passing' => Grade::PASSING]);
    }

    /**
     * One conversation per teacher (B6-2). Dati: iisang halo-halong listahan para sa lahat ng teacher,
     * bago→luma, at hindi kailanman nami-mark as read sa web (B6-1) — kaya hindi nababawasan ang "Unread".
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

        // My teachers + any teacher I already talked to (hal. dating teacher — mababasa pa, hindi na masasagot)
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

        // Back to the same conversation (B6-2)
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

        // Pending — or approved for THIS school year. Dati: kahit approved noong nakaraang taon ay
        // "You are already enrolled", kaya hindi na makaka-enroll sa susunod na school year.
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
            // Awtomatiko: aktibong school year + grade mula sa LRN masterlist
            'activeSy'  => \App\Models\SchoolYear::active()->first(),
            'userGrade' => $user->grade_level,
        ]);
    }

    public function submitEnrollment(Request $request, EnrollmentService $enrollments)
    {
        // Iisang rules para sa web at API (EnrollmentService)
        $data = $request->validate(EnrollmentService::rules(), EnrollmentService::messages());

        $enrollments->submit(auth()->user(), $data);

        return redirect()->route('student.dashboard')
            ->with('success', 'Enrollment form submitted! Please wait for faculty review.');
    }
}
