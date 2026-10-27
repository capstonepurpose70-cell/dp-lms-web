<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Models\OfficialLrn;
use App\Models\Section;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UserManagementController extends Controller
{
    /**
     * Search name/email/LRN/mobile — GROUPED inside one (…), otherwise the OR
     * would let other roles into the tab and bypass the status filter.
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

        // Advisory section(s) of each teacher on this page (Teachers tab)
        $advisories = Section::whereIn('adviser_id', $teachers->pluck('id'))
            ->get(['id', 'name', 'grade_level', 'adviser_id'])
            ->groupBy('adviser_id');

        // Number waiting for approval per role (tab badges)
        $pendingCounts = User::where('status', 'pending')
            ->whereNull('invite_token') // imported teachers awaiting their own activation aren't approvals
            ->whereIn('role', ['student', 'teacher', 'parent'])
            ->selectRaw('role, COUNT(*) as c')
            ->groupBy('role')
            ->pluck('c', 'role');

        $data = compact('students', 'teachers', 'parents', 'tab', 'sectionsByGrade', 'advisories', 'pendingCounts');

        // AJAX — return JSON + the rendered partial
        if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'html' => view('admin.users._partials.table', $data)->render(),
                'counts' => [
                    'students' => $students->total(),
                    'teachers' => $teachers->total(),
                    'parents'  => $parents->total(),
                ],
            ]);
        }

        return view('admin.users.index', $data);
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

        // One parent per child — never silently replace an existing link.
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
        abort_unless((int) $child->parent_id === (int) $user->id, 404);

        $child->update(['parent_id' => null]);

        AuditLogService::log("Unlinked child {$child->name} from parent {$user->name}", 'User Management');
        return back()->with('success', "{$child->name} unlinked from {$user->name}.");
    }

    // ── Approve user ──────────────────────────────────────────────
    public function approve(User $user)
    {
        $user->update(['status' => 'approved']);

        // Parents may have no email — skip the email instead of throwing
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
        return back()->with('success', $user->email
            ? "{$user->name} approved. A notification email was sent."
            : "{$user->name} approved.");
    }

    // ── Reject user ───────────────────────────────────────────────
    public function reject(User $user)
    {
        $user->update(['status' => 'rejected']);

        // Rejected parent — remove the link so the child can create
        // a new parent code.
        if ($user->role === 'parent') {
            $user->children()->update(['parent_id' => null]);
        }

        AuditLogService::log("Rejected user: {$user->name}", 'User Management');
        return back()->with('success', "{$user->name} has been rejected.");
    }

    // ── Reset student account (pekeng activation) ─────────────────
    /**
     * For reports of "I did not activate this account": disables this account
     * and frees the LRN so the real owner can activate it.
     * The account is kept (not deleted) for the audit trail.
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

    // ── Import Teachers (bulk, no emails needed) ──────────────────
    public function importTeachersForm()
    {
        // Advisory codes the admin can copy, e.g. "7-GUTIERREZ" (sections without an adviser first)
        $sections = Section::where('is_active', true)->get(['id', 'name', 'grade_level', 'adviser_id'])
            ->sortBy(fn ($s) => sprintf('%d|%02d|%s', $s->adviser_id ? 1 : 0, (int) $s->grade_level, $s->name))
            ->values();

        // Teachers who have not activated their account yet
        $awaiting = User::where('role', 'teacher')
            ->where('status', 'pending')
            ->whereNotNull('invite_token')
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id', 'invite_expires_at']);
        $advisoryOf = Section::whereIn('adviser_id', $awaiting->pluck('id'))->get(['name', 'grade_level', 'adviser_id'])->keyBy('adviser_id');

        return view('admin.users.import-teachers', compact('sections', 'awaiting', 'advisoryOf'));
    }

    public function importTeachers(Request $request, \App\Services\TeacherRosterService $roster)
    {
        $request->validate(['roster' => 'required|string|max:20000'], [
            'roster.required' => 'Paste at least one teacher (one per line).',
        ]);

        $result = $roster->import($request->roster);

        // Links are shown ONCE (only their SHA-256 hash is stored)
        return redirect()->route('admin.users.import-teachers')
            ->with('import_result', $result)
            ->with('success', $result['created']
                ? "{$result['created']} teacher " . \Illuminate\Support\Str::plural('account', $result['created']) . ' created. Give each teacher their activation link.'
                : 'No new teacher accounts were created — see the notes below.');
    }

    public function newActivationLink(User $user, \App\Services\TeacherRosterService $roster)
    {
        // Only for teachers who never activated — never for an active account (would allow takeover)
        abort_unless($user->role === 'teacher' && $user->status === 'pending', 404);

        $link = $roster->newLink($user);

        return redirect()->route('admin.users.import-teachers')
            ->with('import_result', ['created' => 0, 'results' => [[
                'line' => null, 'name' => $user->name, 'ok' => true, 'link' => $link,
                'advisory' => null, 'note' => 'New link — the old link no longer works.',
            ]]])
            ->with('success', "New activation link for {$user->name}.");
    }

    // ── Create Teacher ────────────────────────────────────────────
    public function createTeacher()
    {
        // Advisory choices: active sections, ones without an adviser first
        $sections = Section::with('adviser:id,name')->where('is_active', true)->get()
            ->sortBy(fn ($s) => sprintf('%02d|%s', (int) $s->grade_level, mb_strtolower($s->name)))
            ->values();

        return view('admin.users.create-teacher', compact('sections'));
    }

    /**
     * Single teacher — same activation-link flow as Import Teachers:
     * the account stays pending until the teacher activates it (no temporary password).
     */
    public function storeTeacher(StoreTeacherRequest $request, \App\Services\TeacherRosterService $roster)
    {
        $r = $roster->createOne($request->validated());

        $note = trim(($r['emailed'] ? "Invite emailed to {$r['teacher']->email}. " : ($r['teacher']->email
                    ? 'The invite email could not be sent — give the teacher this link instead. ' : ''))
                . $r['note']);

        return redirect()->route('admin.users.import-teachers')
            ->with('import_result', ['created' => 1, 'results' => [[
                'line' => null, 'name' => $r['teacher']->name, 'ok' => true, 'link' => $r['link'],
                'advisory' => $r['section'] ? "Grade {$r['section']->grade_level} – {$r['section']->name}" : null,
                'note' => $note,
            ]]])
            ->with('success', "Teacher account for {$r['teacher']->name} created. Give them their activation link.");
    }
}
