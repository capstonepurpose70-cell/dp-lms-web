<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\LearningMaterial;
use App\Models\TeacherSubject;
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
        $sectionId = $user->studentEnrollment?->section?->id ?? $user->section_id;

        // Teachers who teach this student's section
        $teacherIds = collect();
        if ($sectionId) {
            $teacherIds = TeacherSubject::where('section_id', $sectionId)->pluck('user_id')
                ->merge(\App\Models\TeacherAssignment::where('section_id', $sectionId)->pluck('user_id'))
                ->filter()->unique();
        }
        $teachers = \App\Models\User::whereIn('id', $teacherIds)->orderBy('name')->get();

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
            'body'       => 'required|string|max:2000',
        ]);

        // The recipient must be a teacher of the student's section
        $sectionId = $user->studentEnrollment?->section?->id ?? $user->section_id;
        $allowed = collect();
        if ($sectionId) {
            $allowed = TeacherSubject::where('section_id', $sectionId)->pluck('user_id')
                ->merge(\App\Models\TeacherAssignment::where('section_id', $sectionId)->pluck('user_id'))
                ->filter()->unique();
        }
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

public function submitEnrollment(\Illuminate\Http\Request $request)
{
    $user = auth()->user();

    $request->validate([
        // grade_level at school_year: HINDI na pinipili ng estudyante —
        // server ang nagtatakda (aktibong SY + grade mula sa records/LRN list)
        'student_type'        => 'required|in:new,old,transfer',
        'full_name'           => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.]+$/'],
        'age'                 => 'required|integer|min:10|max:25',
        'birthdate'           => 'required|date',
        'gender'              => 'required|in:Male,Female',
        'address'             => 'required|string|max:500',
        'mother_name'         => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.]*$/'],
        'father_name'         => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.]*$/'],
        'guardian_name'       => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.]*$/'],
        'guardian_contact'    => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s]*$/'],
        'last_school'         => 'nullable|string|max:255',
        'last_grade_completed'=> 'nullable|string|max:50',
    ], [
        'full_name.regex'        => 'Full name must contain letters only, no numbers.',
        'mother_name.regex'      => 'Mother\'s name must contain letters only.',
        'father_name.regex'      => 'Father\'s name must contain letters only.',
        'guardian_name.regex'    => 'Guardian\'s name must contain letters only.',
        'guardian_contact.regex' => 'Contact number must contain numbers only.',
    ]);

    \App\Models\EnrollmentRequest::updateOrCreate(
        ['user_id' => $user->id, 'status' => 'pending'],
        [
            'grade_level'          => $user->grade_level ?: 'To be assigned',
            'school_year'          => optional(\App\Models\SchoolYear::active()->first())->label ?? 'Current',
            'student_type'         => $request->student_type,
            'full_name'            => $request->full_name,
            'age'                  => $request->age,
            'birthdate'            => $request->birthdate,
            'gender'               => $request->gender,
            'address'              => $request->address,
            'mother_name'          => $request->mother_name,
            'father_name'          => $request->father_name,
            'guardian_name'        => $request->guardian_name,
            'guardian_contact'     => $request->guardian_contact,
            'last_school'          => $request->last_school,
            'last_grade_completed' => $request->last_grade_completed,
            'status'               => 'pending',
        ]
    );

    return redirect()->route('student.dashboard')
        ->with('success', 'Enrollment form submitted! Please wait for faculty review.');
}
}