<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Models\OfficialLrn;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SmsService;
use App\Services\TeacherAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UserManagementController extends Controller
{
    /**
     * Search sa name/email/LRN/mobile — NAKA-GROUP sa iisang (…) .
     * Dati: `role = x AND name LIKE … OR email LIKE …` — dahil sa OR,
     * lumalabas ang ibang role sa tab at nalalampasan ang status filter.
     */
    private function search($query, string $term)
    {
        return $query->where(function ($w) use ($term) {
            $w->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('lrn', 'like', "%{$term}%")
              ->orWhere('contact_number', 'like', "%{$term}%");
        });
    }

    // ── Main index ────────────────────────────────────────────────
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'students');

        $sectionsByGrade = Section::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->groupBy('grade_level');

        $students = User::where('role', 'student')
            ->when($request->search, fn($q) => $this->search($q, $request->search))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->grade, fn($q) => $q->where('grade_level', $request->grade))
            ->when($request->section_id, fn($q) => $q->where('section_id', $request->section_id))
            ->with(['section', 'studentProfile'])
            ->latest()
            ->paginate(15, ['*'], 'student_page');

        $teachers = User::where('role', 'teacher')
            ->when($request->search, fn($q) => $this->search($q, $request->search))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->with(['teacherSubjects.section'])
            ->latest()
            ->paginate(15, ['*'], 'teacher_page');

        $parents = User::where('role', 'parent')
            ->when($request->search, fn($q) => $this->search($q, $request->search))
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

        // Isang magulang kada anak — dati tahimik na napapalitan ang dating naka-link.
        if ($student->parent_id !== null && (int) $student->parent_id !== (int) $user->id) {
            return back()->withErrors(['student_id' => "{$student->name} is already linked to another parent. Unlink them first."]);
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

        // Magulang na walang email — laktawan ang email (dati: exception na nilulunok)
        if ($user->email) {
            try {
                Mail::send(
                    'emails.account-approved',
                    ['user' => $user],
                    fn ($m) => $m->to($user->email)->subject('Account Approved — DP-LMS')
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        if ($user->contact_number || $user->phone_number) {
            $phone = $user->phone_number ?? $user->contact_number;
            $message = "Hello {$user->name}! Your DP-LMS account at Sto. Domingo NHS has been approved. "
                     . "You can now log in at: " . config('app.url') . "/login - DP-LMS";
            SmsService::send($phone, $message);
        }

        AuditLogService::log("Approved user: {$user->name}", 'User Management');
        return back()->with('success', "{$user->name} approved. Email/SMS notification sent.");
    }

    // ── Reject user ───────────────────────────────────────────────
    public function reject(User $user)
    {
        $user->update(['status' => 'rejected']);

        // Rejected na magulang — tanggalin ang link para makagawa ulit ng
        // bagong parent code ang anak.
        if ($user->role === 'parent') {
            $user->children()->update(['parent_id' => null]);
        }

        AuditLogService::log("Rejected user: {$user->name}", 'User Management');
        return back()->with('success', "{$user->name} has been rejected.");
    }

    // ── Reset student account (pekeng activation) ─────────────────
    /**
     * Kapag may nag-report na "hindi ako ang nag-activate": idi-disable ang
     * account na ito at ibabalik ang LRN para ma-activate ng totoong may-ari.
     * Hindi binubura ang account — nananatili para sa audit.
     */
    public function releaseLrn(User $user)
    {
        abort_unless($user->role === 'student' && $user->lrn, 404);

        $lrn = $user->lrn;

        DB::transaction(function () use ($user, $lrn) {
            OfficialLrn::where('lrn', $lrn)->update(['claimed_by' => null]);

            $user->update(['status' => 'rejected', 'lrn' => null]);
            $user->tokens()->delete();
            $user->trustedDevices()->delete();
        });

        AuditLogService::log("Released LRN (account reset): {$user->name}", 'User Management', "LRN: {$lrn}");

        return back()->with('success', "Account reset. LRN {$lrn} can now be activated by its real owner.");
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
}
