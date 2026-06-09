<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\LearningMaterial;
use App\Models\TeacherSubject;
use App\Support\BadWordFilter;
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
        $user       = auth()->user();
        $enrollment = $user->studentEnrollment;
        $section    = $enrollment?->section ?? $user->section;

        // Teachers ng student's section (gamit existing TeacherSubject relationship)
        $teachers = $section
            ? TeacherSubject::with('teacher')
                ->where('section_id', $section->id)
                ->get()
                ->pluck('teacher')
                ->filter()
                ->unique('id')
                ->values()
            : collect();

        return response()->view('student.messages', compact('teachers'))
            ->header('Cache-Control', 'private, max-age=30');
    }

    public function storeMessage(Request $request)
    {
        $user = auth()->user();

        // 1) Validations
        $validated = $request->validate([
            'teacher_id' => ['required', 'exists:users,id'],
            'subject'    => ['required', 'string', 'min:3', 'max:100'],
            'body'       => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        // 2) Bad-word check -> auto ban
        $combined = $validated['subject'] . ' ' . $validated['body'];

        if (BadWordFilter::containsBadWord($combined)) {
            $user->update([
                'is_banned'  => true,
                'banned_at'  => now(),
                'ban_reason' => 'Inappropriate language in a message.',
            ]);

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with(
                'error',
                'Your account has been banned for using inappropriate language.'
            );
        }

        // 3) Save message (i-adjust ang field names base sa Message model mo)
        \App\Models\Message::create([
            'sender_id'   => $user->id,
            'receiver_id' => $validated['teacher_id'],
            'subject'     => $validated['subject'],
            'body'        => $validated['body'],
            'is_read'     => false,
        ]);

        return back()->with('status', 'Message sent successfully!');
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
            'user'        => $user,
            'request'     => $request,
            'schoolYears' => \App\Models\SchoolYear::orderByDesc('starts_at')->get(),
        ]);
    }

    public function submitEnrollment(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'grade_level' => 'required|string|in:7,8,9,10,11,12',
            'school_year'         => 'required|string',
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
                'grade_level'          => $request->grade_level,
                'school_year'          => $request->school_year,
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