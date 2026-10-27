<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EnrollmentRequest;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Reports & Analytics + CSV exports (enrollment masterlist, attendance log).
 * Figures follow the active school year; teacher counts are loaded in one query.
 */
class ReportController extends Controller
{
    public function index()
    {
        $sy    = SchoolYear::current()?->label;
        $today = now()->toDateString();

        // ── Accounts ────────────────────────────────────────────────────
        $roleStatus = User::whereIn('role', ['student', 'teacher', 'parent'])
            ->selectRaw('role, status, COUNT(*) as c')->groupBy('role', 'status')->get();
        $count = fn ($role, $status = null) => (int) $roleStatus->where('role', $role)
            ->when($status, fn ($c) => $c->where('status', $status))->sum('c');

        // ── Enrollment (this school year) ───────────────────────────────
        $enrolledByGrade = StudentEnrollment::where('status', 'enrolled')
            ->when($sy, fn ($q) => $q->where('school_year', $sy))
            ->selectRaw('grade_level, COUNT(*) as c')->groupBy('grade_level')->pluck('c', 'grade_level');
        $pendingByGrade = EnrollmentRequest::where('status', 'pending')
            ->selectRaw('grade_level, COUNT(*) as c')->groupBy('grade_level')->pluck('c', 'grade_level');
        $grades = ['7', '8', '9', '10', '11', '12'];

        // ── Students placed per section (+ gender, average) ─────────────
        $placed = DB::table('users')
            ->leftJoin('student_profiles', 'student_profiles.user_id', '=', 'users.id')
            ->where('users.role', 'student')->where('users.status', 'approved')->whereNotNull('users.section_id')
            ->selectRaw("users.section_id,
                COUNT(*) as total,
                SUM(CASE WHEN student_profiles.gender = 'Male' THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN student_profiles.gender = 'Female' THEN 1 ELSE 0 END) as female,
                AVG(student_profiles.general_average) as avg_ga")
            ->groupBy('users.section_id')->get()->keyBy('section_id');

        // ── Attendance today (from the Raspberry Pi) ────────────────────
        $todayRows = Attendance::whereDate('attended_at', $today)->whereNotNull('user_id')
            ->get(['user_id', 'section_id', 'status']);
        $todayBySection = $todayRows->groupBy('section_id');

        $sections = Section::with('adviser:id,name')->where('is_active', true)->get()
            ->sortBy(fn ($s) => sprintf('%02d|%s', (int) $s->grade_level, mb_strtolower($s->name)))
            ->values()
            ->map(function ($s) use ($placed, $todayBySection) {
                $p = $placed[$s->id] ?? null;
                $t = $todayBySection[$s->id] ?? collect();
                return [
                    'section' => $s,
                    'total'   => (int) ($p->total ?? 0),
                    'male'    => (int) ($p->male ?? 0),
                    'female'  => (int) ($p->female ?? 0),
                    'avg'     => isset($p->avg_ga) ? round((float) $p->avg_ga, 1) : null,
                    'present' => $t->where('status', 'present')->count(),
                    'late'    => $t->where('status', 'late')->count(),
                ];
            });

        $placedTotal = (int) $sections->sum('total');
        $presentToday = $todayRows->where('status', 'present')->count();
        $lateToday    = $todayRows->where('status', 'late')->count();

        // ── Attendance last 14 days (present / late per day) ────────────
        $from14 = now()->subDays(13)->startOfDay();
        $daily  = Attendance::where('attended_at', '>=', $from14)->whereNotNull('user_id')
            ->selectRaw('DATE(attended_at) as d, status, COUNT(*) as c')->groupBy('d', 'status')->get();
        $days = [];
        for ($d = $from14->copy(); $d->lte(now()); $d->addDay()) {
            $key = $d->toDateString();
            $days[] = [
                'label'   => $d->format('M j'),
                'present' => (int) $daily->where('d', $key)->where('status', 'present')->sum('c'),
                'late'    => (int) $daily->where('d', $key)->where('status', 'late')->sum('c'),
            ];
        }

        // ── Teachers (load from teacher_subjects, students counted in one query) ──
        $teachers = User::where('role', 'teacher')->where('status', 'approved')
            ->with(['teacherSubjects.subject:id,name', 'teacherSubjects.section:id,name,grade_level'])
            ->orderBy('name')->get();
        $advisories = Section::whereIn('adviser_id', $teachers->pluck('id'))->get(['id', 'name', 'grade_level', 'adviser_id'])->groupBy('adviser_id');
        $studentsPerSection = $placed->map(fn ($p) => (int) $p->total);

        return view('admin.reports.index', [
            'sy'                => $sy,
            'students'          => $count('student', 'approved'),
            'enrolledTotal'     => (int) $enrolledByGrade->sum(),
            'pendingEnrollment' => (int) $pendingByGrade->sum(),
            'teachersActive'    => $count('teacher', 'approved'),
            'teachersWaiting'   => User::where('role', 'teacher')->where('status', 'pending')->whereNotNull('invite_token')->count(),
            'parents'           => $count('parent', 'approved'),
            'parentsPending'    => $count('parent', 'pending'),
            'roleCounts'        => ['Students' => $count('student'), 'Teachers' => $count('teacher'), 'Parents' => $count('parent')],
            'grades'            => $grades,
            'enrolledByGrade'   => $enrolledByGrade,
            'pendingByGrade'    => $pendingByGrade,
            'sections'          => $sections,
            'placedTotal'       => $placedTotal,
            'presentToday'      => $presentToday,
            'lateToday'         => $lateToday,
            'days'              => $days,
            'teachers'          => $teachers,
            'advisories'        => $advisories,
            'studentsPerSection'=> $studentsPerSection,
        ]);
    }

    // ── CSV exports ─────────────────────────────────────────────────────────

    /** Enrollment masterlist for the current school year (opens in Excel). */
    public function exportEnrollment()
    {
        $sy = SchoolYear::current()?->label;

        $rows = DB::table('student_enrollments as se')
            ->join('users as u', 'u.id', '=', 'se.user_id')
            ->leftJoin('sections as s', 's.id', '=', 'se.section_id')
            ->leftJoin('users as adv', 'adv.id', '=', 's.adviser_id')
            ->leftJoin('student_profiles as p', 'p.user_id', '=', 'u.id')
            ->where('se.status', 'enrolled')
            ->when($sy, fn ($q) => $q->where('se.school_year', $sy))
            ->orderByRaw('CAST(se.grade_level AS UNSIGNED)')->orderBy('s.name')->orderBy('u.name')
            ->get(['u.lrn', 'u.name', 'p.gender', 'p.birthdate', 'se.grade_level', 's.name as section', 'adv.name as adviser',
                   'p.student_type', 'p.general_average', 'se.school_year', 'se.enrolled_at']);

        AuditLogService::log('Exported enrollment masterlist', 'Reports', "SY {$sy} · {$rows->count()} students");

        return $this->csv("enrollment-masterlist-{$sy}.csv",
            ['LRN', 'Name', 'Sex', 'Birthdate', 'Grade', 'Section', 'Adviser', 'Type', 'General Average', 'School Year', 'Enrolled On'],
            $rows->map(fn ($r) => [
                $r->lrn, $r->name, $r->gender, $r->birthdate, $r->grade_level, $r->section, $r->adviser,
                $r->student_type, $r->general_average, $r->school_year,
                $r->enrolled_at ? Carbon::parse($r->enrolled_at)->format('Y-m-d') : '',
            ]));
    }

    /** Attendance log (Raspberry Pi arrivals) for a date range, optional section. */
    public function exportAttendance(Request $request)
    {
        $data = $request->validate([
            'from'       => 'required|date',
            'to'         => 'required|date|after_or_equal:from',
            'section_id' => 'nullable|exists:sections,id',
        ]);
        $from = Carbon::parse($data['from'])->startOfDay();
        $to   = Carbon::parse($data['to'])->endOfDay();
        abort_if($from->diffInDays($to) > 370, 422, 'Please choose a range of one year or less.');

        $rows = DB::table('attendances as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->leftJoin('sections as s', 's.id', '=', 'a.section_id')
            ->whereBetween('a.attended_at', [$from, $to])
            ->when($data['section_id'] ?? null, fn ($q, $sid) => $q->where('a.section_id', $sid))
            ->orderBy('a.attended_at')
            ->get(['a.attended_at', 'u.lrn', 'u.name', 'a.student_name', 's.grade_level', 's.name as section', 'a.status', 'a.source']);

        AuditLogService::log('Exported attendance log', 'Reports',
            $from->toDateString() . ' to ' . $to->toDateString() . " · {$rows->count()} rows");

        return $this->csv('attendance-' . $from->toDateString() . '-to-' . $to->toDateString() . '.csv',
            ['Date', 'Time In', 'LRN', 'Name', 'Grade', 'Section', 'Status', 'Source'],
            $rows->map(fn ($r) => [
                Carbon::parse($r->attended_at)->format('Y-m-d'), Carbon::parse($r->attended_at)->format('h:i A'),
                $r->lrn, $r->name ?? $r->student_name, $r->grade_level, $r->section, ucfirst((string) $r->status), $r->source,
            ]));
    }

    /** Class attendance (teacher-posted, per subject) for a date range, optional section — for SF2. */
    public function exportClassAttendance(Request $request)
    {
        $data = $request->validate([
            'from'       => 'required|date',
            'to'         => 'required|date|after_or_equal:from',
            'section_id' => 'nullable|exists:sections,id',
        ]);
        $from = Carbon::parse($data['from'])->startOfDay();
        $to   = Carbon::parse($data['to'])->endOfDay();
        abort_if($from->diffInDays($to) > 370, 422, 'Please choose a range of one year or less.');

        $rows = DB::table('class_attendance_records as r')
            ->join('class_attendance_sessions as s', 's.id', '=', 'r.session_id')
            ->join('users as u', 'u.id', '=', 'r.student_id')
            ->leftJoin('sections as sec', 'sec.id', '=', 's.section_id')
            ->leftJoin('subjects as sub', 'sub.id', '=', 's.subject_id')
            ->leftJoin('users as t', 't.id', '=', 's.teacher_id')
            ->whereBetween('s.opened_at', [$from, $to])
            ->when($data['section_id'] ?? null, fn ($q, $sid) => $q->where('s.section_id', $sid))
            ->orderBy('s.opened_at')->orderBy('u.name')
            ->get(['s.opened_at', 'r.marked_at', 'u.lrn', 'u.name', 'sec.grade_level', 'sec.name as section',
                   'sub.name as subject', 't.name as teacher', 'r.status', 'r.marked_by']);

        AuditLogService::log('Exported class attendance', 'Reports',
            $from->toDateString() . ' to ' . $to->toDateString() . " · {$rows->count()} rows");

        return $this->csv('class-attendance-' . $from->toDateString() . '-to-' . $to->toDateString() . '.csv',
            ['Date', 'Class Start', 'Checked In', 'LRN', 'Name', 'Grade', 'Section', 'Subject', 'Teacher', 'Status', 'Marked By'],
            $rows->map(fn ($r) => [
                Carbon::parse($r->opened_at)->format('Y-m-d'), Carbon::parse($r->opened_at)->format('h:i A'),
                $r->marked_at ? Carbon::parse($r->marked_at)->format('h:i A') : '',
                $r->lrn, $r->name, $r->grade_level, $r->section, $r->subject, $r->teacher,
                ucfirst((string) $r->status), ucfirst((string) $r->marked_by),
            ]));
    }

    /** UTF-8 CSV with BOM (Excel shows ñ correctly) and spreadsheet-formula injection guard. */
    private function csv(string $filename, array $header, $rows)
    {
        $safe = function ($v) {
            $v = (string) ($v ?? '');
            return preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
        };

        return response()->streamDownload(function () use ($header, $rows, $safe) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $header);
            foreach ($rows as $row) {
                fputcsv($out, array_map($safe, $row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
