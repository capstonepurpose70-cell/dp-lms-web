<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SmsService;
use App\Services\TeacherAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    // ── Main index ────────────────────────────────────────────────
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'students');

        $sectionsByGrade = Section::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->groupBy('grade_level');

        $students = User::where('role', 'student')
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            )
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->grade, fn($q) => $q->where('grade_level', $request->grade))
            ->when($request->section_id, fn($q) => $q->where('section_id', $request->section_id))
            ->with(['section', 'studentProfile'])
            ->latest()
            ->paginate(15, ['*'], 'student_page');

        $teachers = User::where('role', 'teacher')
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            )
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->with(['teacherSubjects.section'])
            ->latest()
            ->paginate(15, ['*'], 'teacher_page');

        $parents = User::where('role', 'parent')
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            )
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->with(['children.section'])
            ->latest()
            ->paginate(15, ['*'], 'parent_page');

        // AJAX — i-return ang JSON + rendered partial
        if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'html' => view('admin.users._partials.table', compact(
                    'students', 'teachers', 'parents', 'tab', 'sectionsByGrade'
                ))->render(),
                'counts' => [
                    'students' => $students->total(),
                    'teachers' => $teachers->total(),
                    'parents'  => $parents->total(),
                ],
            ]);
        }

        return view('admin.users.index', compact(
            'students', 'teachers', 'parents', 'tab', 'sectionsByGrade'
        ));
    }

    // ── Show user ─────────────────────────────────────────────────
    public function show(User $user)
    {
        $user->load([
            'section',
            'teacherSubjects.subject',
            'teacherSubjects.section',
            'studentProfile',
            'children.section',
        ]);

        // For parents: approved students not yet linked to any parent.
        $linkableStudents = collect();
        if ($user->role === 'parent') {
            $linkableStudents = User::where('role', 'student')
                ->where('status', 'approved')
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        return view('admin.users.show', compact('user', 'linkableStudents'));
    }

    // ── Link a child (student) to a parent ────────────────────────
    public function linkChild(Request $request, User $user)
    {
        abort_unless($user->role === 'parent', 404);

        $data = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
        ]);

        $student = User::where('id', $data['student_id'])
                       ->where('role', 'student')
                       ->first();

        if (!$student) {
            return back()->withErrors(['student_id' => 'Selected account is not a student.']);
        }

        $student->update(['parent_id' => $user->id]);

        AuditLogService::log("Linked child {$student->name} to parent {$user->name}", 'User Management');
        return back()->with('success', "{$student->name} linked to {$user->name}.");
    }

    // ── Unlink a child from a parent ──────────────────────────────
    public function unlinkChild(User $user, User $child)
    {
        abort_unless($child->parent_id === $user->id, 404);

        $child->update(['parent_id' => null]);

        AuditLogService::log("Unlinked child {$child->name} from parent {$user->name}", 'User Management');
        return back()->with('success', "{$child->name} unlinked from {$user->name}.");
    }

    // ── Approve user ──────────────────────────────────────────────
    public function approve(User $user)
    {
        $user->update(['status' => 'approved']);

        try {
            \Illuminate\Support\Facades\Mail::send(
                'emails.account-approved',
                ['user' => $user],
                function($m) use ($user) {
                    $m->to($user->email)
                      ->subject('Account Approved — DP-LMS');
                }
            );
        } catch (\Exception $e) {}

        if ($user->contact_number || $user->phone_number) {
            $phone = $user->phone_number ?? $user->contact_number;
            $message = "Hello {$user->name}! Your DP-LMS account at Sto. Domingo NHS has been approved. "
                     . "You can now log in at: " . config('app.url') . "/login - DP-LMS";
            \App\Services\SmsService::send($phone, $message);
        }

        AuditLogService::log("Approved user: {$user->name}", 'User Management');
        return back()->with('success', "{$user->name} approved. Email/SMS notification sent.");
    }

    // ── Reject user ───────────────────────────────────────────────
    public function reject(User $user)
    {
        $user->update(['status' => 'rejected']);
        AuditLogService::log("Rejected user: {$user->name}", 'User Management');
        return back()->with('success', "{$user->name} has been rejected.");
    }

    // ── Create Teacher ────────────────────────────────────────────
    public function createTeacher()
    {
        $sections   = Section::where('is_active', true)->orderBy('grade_level')->get();
        $subjects   = Subject::where('is_active', true)->orderBy('grade_level')->get();
        $schoolYear = SchoolYear::current();
 
        return view('admin.users.create-teacher', compact('sections', 'subjects', 'schoolYear'));
    }
 
    public function storeTeacher(
        StoreTeacherRequest $request,
        TeacherAssignmentService $assignmentService
        // ← TeacherInviteService REMOVED — invite is handled inside the service
    ) {
        try {
            $teacher = $assignmentService->createTeacherWithAssignments(
                $request->validated()
            );
 
            AuditLogService::log("Created teacher: {$teacher->name}", 'User Management');

            $tempPw = $assignmentService->lastTempPassword;

            return redirect()
                ->route('admin.users.index', ['tab' => 'teachers'])
                ->with('success', "Teacher account for {$teacher->name} created. "
                    . "Temporary password: {$tempPw}  —  ibigay ito sa teacher; papalitan nila ito sa unang pag-login.");
 
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['general' => 'Failed to create teacher: ' . $e->getMessage()]);
        }
    }
 
 
    // ── Enroll Student ────────────────────────────────────────────
    public function showEnrollStudent(User $user)
    {
        $sections = Section::where('is_active', true)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $grades   = range(7, 12);
        $profile  = $user->studentProfile;
        $student  = $user;

        return view('admin.users.enroll-student', compact('student', 'sections', 'grades', 'profile'));
    }

    public function enrollStudent(Request $request, User $user)
    {
        $request->validate([
            'section_id'           => 'required|exists:sections,id',
            'grade_level' => 'required|string|in:7,8,9,10,11,12',
            'phone_number'         => 'nullable|string|max:20',
            'school_year' => 'required|string|exists:school_years,name',
            'student_type'         => 'required|in:new,old,transfer',
            'full_name'            => 'required|string|max:255',
            'age'                  => 'nullable|integer|min:10|max:25',
            'birthdate'            => 'nullable|date',
            'gender'               => 'nullable|in:Male,Female',
            'address'              => 'nullable|string',
            'mother_name'          => 'nullable|string|max:255',
            'father_name'          => 'nullable|string|max:255',
            'guardian_name'        => 'nullable|string|max:255',
            'guardian_contact'     => 'nullable|string|max:20',
            'last_school'          => 'nullable|string|max:255',
            'last_grade_completed' => 'nullable|string|max:50',
        ]);

        $section = Section::find($request->section_id);

        $user->update([
            'section_id'   => $request->section_id,
            'grade_level'  => $request->grade_level,
            'phone_number' => $request->phone_number ?? $user->contact_number,
            'status'       => 'approved',
        ]);

        StudentProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name'            => $request->full_name,
                'age'                  => $request->age,
                'birthdate'            => $request->birthdate,
                'gender'               => $request->gender,
                'address'              => $request->address,
                'mother_name'          => $request->mother_name,
                'father_name'          => $request->father_name,
                'guardian_name'        => $request->guardian_name,
                'guardian_contact'     => $request->guardian_contact,
                'student_type'         => $request->student_type,
                'last_school'          => $request->last_school,
                'last_grade_completed' => $request->last_grade_completed,
            ]
        );

        StudentEnrollment::updateOrCreate(
            ['user_id' => $user->id, 'school_year' => $request->school_year],
            [
                'section_id'  => $request->section_id,
                'grade_level' => $request->grade_level,
                'status'      => 'enrolled',
                'enrolled_at' => now(),
            ]
        );

        $phone        = $request->phone_number ?? $user->contact_number;
        $subjectCount = in_array($request->grade_level, ['11', '12']) ? 9 : 8;

        if ($phone) {
            $message = "Hello {$user->name}! You are now enrolled at Sto. Domingo NHS. "
                     . "Grade: {$request->grade_level} | Section: {$section->name} | "
                     . "Subjects: {$subjectCount} | SY: {$request->school_year}. "
                     . "Log in to DP-LMS to view your dashboard. - Admin";
            SmsService::send($phone, $message);
        }

        AuditLogService::log(
            "Enrolled student: {$user->name}",
            'Enrollment',
            "Section: {$section->name} | Grade: {$request->grade_level} | Type: {$request->student_type}"
        );

        return redirect()->route('admin.users.index', ['tab' => 'students'])
            ->with('success', "{$user->name} enrolled in {$section->name}."
                . ($phone ? ' SMS sent.' : ''));
    }

    // ── Assign Teacher ────────────────────────────────────────────
    public function showAssignTeacher(User $user)
    {
        $allTeachers = User::where('role', 'teacher')
            ->where('status', 'approved')
            ->with(['teacherSubjects.subject', 'teacherSubjects.section'])
            ->get();

        $subjects = Subject::where('is_active', true)
            ->where('grade_level', $user->grade_level)
            ->get();

        return view('admin.users.assign-teacher', compact('user', 'allTeachers', 'subjects'));
    }

    public function assignTeacher(Request $request, User $user)
    {
        $request->validate([
            'assignments'              => 'required|array|min:1',
            'assignments.*.teacher_id' => 'required|exists:users,id',
            'assignments.*.subject_id' => 'required|exists:subjects,id',
        ]);

        foreach ($request->assignments as $assignment) {
            TeacherSubject::updateOrCreate(
                [
                    'user_id'    => $assignment['teacher_id'],
                    'subject_id' => $assignment['subject_id'],
                    'section_id' => $user->section_id,
                ],
                [
                    'grade_level' => $user->grade_level,
                   'school_year' => \App\Models\SchoolYear::current()?->label ?? '2025-2026',
                ]
            );
        }

        AuditLogService::log("Assigned teachers to student: {$user->name}", 'User Management');

        return redirect()->route('admin.users.index', ['tab' => 'students'])
            ->with('success', "Teachers assigned to {$user->name} successfully.");
    }

}