<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\ClassSchedule;
use App\Models\LearningMaterial;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Rules\NoBadWords;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user       = auth()->user();
        $enrollment = $user->studentEnrollment;
        $section    = $enrollment?->section ?? $user->section;
        $grade      = $enrollment?->grade_level ?? $user->grade_level;

        // Get subjects for this student's section
        $subjects = $section
            ? TeacherSubject::with(['subject', 'teacher'])
                ->where('section_id', $section->id)
                ->get()
                ->unique('subject_id')
            : collect();

        $subjectCount = in_array($grade, ['11', '12']) ? 9 : 8;

        return response()->view('student.dashboard', [
            'user'               => $user,
            'enrollment'         => $enrollment,
            'section'            => $section,
            'grade'              => $grade,
            'subjects'           => $subjects,
            'subjectCount'       => $subjects->count(),
            'pendingAssignments' => 0,
            'unreadMessages'     => $user->receivedMessages()
                                        ->where('is_read', false)->count(),
            'announcements'      => Announcement::published()
                                        ->forAudience('student')
                                        ->visibleToSections([$section?->id])
                                        ->with('author')
                                        ->latest()
                                        ->take(5)
                                        ->get(),
        ])->header('Cache-Control', 'private, max-age=30');
    }

    public function modules()
    {
        $user       = auth()->user();
        $enrollment = $user->studentEnrollment;
        $section    = $enrollment?->section ?? $user->section;

        // Get teacher subjects for this section
        $teacherSubjects = $section
            ? TeacherSubject::with('subject')
                ->where('section_id', $section->id)
                ->get()
                ->unique('subject_id')
            : collect();

        $subjectIds = $teacherSubjects->pluck('subject_id');

        // Get published materials for these subjects
        $materials = LearningMaterial::with(['subject', 'teacher'])
            ->whereIn('subject_id', $subjectIds)
            ->where('is_published', true)
            ->latest()
            ->get()
            ->groupBy('subject_id');

        return response()->view('student.modules', compact('materials', 'teacherSubjects', 'section'))
            ->header('Cache-Control', 'private, max-age=30');
        
    }

    public function quizzes()
    {
        return response()->view('student.quizzes')
            ->header('Cache-Control', 'private, max-age=30');
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

        $schedules = $section
            ? ClassSchedule::with(['subject', 'teacher'])
                ->where('section_id', $section->id)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get()
                ->groupBy('day_of_week')
            : collect();

        return response()->view('student.schedule', [
            'section'    => $section,
            'enrollment' => $enrollment,
            'schedules'  => $schedules,
            'days'       => ClassSchedule::DAYS,
        ])->header('Cache-Control', 'private, max-age=30');
    }

    public function grades()
    {
        $grades = auth()->user()
            ->grades()
            ->with('subject')
            ->orderBy('quarter')
            ->get();
        return response()->view('student.grades', compact('grades'))
            ->header('Cache-Control', 'private, max-age=30');
    }

    public function messages()
    {
        $user = auth()->user();

        // Teachers of this student's section (subject teachers + adviser)
        $teacherIds = User::teacherIdsForSection($user->studentSectionId());
        $teachers   = User::whereIn('id', $teacherIds)->orderBy('name')->get();

        // All messages involving this student (newest first)
        $messages = \App\Models\Message::with(['sender', 'receiver'])
            ->where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return view('student.messages', compact('teachers', 'messages'));
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

        return back()->with('success', 'Message sent!');
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

    return response()->view('student.subjects', compact('subjects', 'enrollment', 'section'))
        ->header('Cache-Control', 'private, max-age=30');
}

public function enrollmentForm()
{
    $user    = auth()->user();
    $request = $user->enrollmentRequest;

    // Already has pending or approved enrollment
    if ($request && in_array($request->status, ['pending', 'approved'])) {
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