<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\EnrollmentRequest;
use App\Models\OfficialLrn;
use App\Models\Section;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        // ── Chart data ───────────────────────────────────────────────────
        // One grouped query (≤ ~370 rows) instead of loading every audit log row
        // of the year three times and counting in PHP. Counting rules:
        //   log = starts with "login" but not "blocked" ("Failed login" is not counted)
        //   apr = contains "approve"
        //   reg = "account activated" / starts with "parent linked" / contains "registration"
        $weekStart = now()->startOfWeek();
        $weekEnd   = now()->endOfWeek();
        $from      = $weekStart->copy()->min(now()->startOfYear());   // week may start in last year
        $to        = $weekEnd->copy()->max(now()->endOfYear());       // or end in next year

        $a = 'LOWER(action)';
        $category = "CASE
            WHEN {$a} LIKE 'login%' AND {$a} NOT LIKE '%blocked%' THEN 'log'
            WHEN {$a} LIKE '%approve%' THEN 'apr'
            WHEN {$a} LIKE '%account activated%' OR {$a} LIKE 'parent linked%' OR {$a} LIKE '%registration%' THEN 'reg'
            ELSE NULL END";

        $daily = AuditLog::whereBetween('created_at', [$from, $to])
            ->selectRaw("DATE(created_at) as d, {$category} as cat, COUNT(*) as c")
            ->groupBy('d', 'cat')
            ->get()
            ->filter(fn ($r) => $r->cat !== null);

        // [Y-m-d][cat] => count
        $byDay = [];
        foreach ($daily as $r) {
            $byDay[(string) $r->d][$r->cat] = (int) $r->c;
        }

        $series = function (array $days, array $labels) use ($byDay) {
            $out = ['labels' => $labels, 'reg' => [], 'apr' => [], 'log' => []];
            foreach ($days as $dayKeys) {
                foreach (['reg', 'apr', 'log'] as $cat) {
                    $sum = 0;
                    foreach ((array) $dayKeys as $d) {
                        $sum += $byDay[$d][$cat] ?? 0;
                    }
                    $out[$cat][] = $sum;
                }
            }
            return $out;
        };

        // Weekly: Mon..Sun of this week
        $weekDays = [];
        for ($d = $weekStart->copy(); $d->lte($weekEnd); $d->addDay()) {
            $weekDays[] = $d->toDateString();
        }
        $chartDataWeekly = $series($weekDays, array_map(fn ($d) => Carbon::parse($d)->format('D'), $weekDays));

        // Monthly: every day of this month
        $monthDays = [];
        for ($d = now()->startOfMonth(); $d->lte(now()->endOfMonth()); $d->addDay()) {
            $monthDays[] = $d->toDateString();
        }
        $chartDataMonthly = $series($monthDays, array_map(fn ($d) => Carbon::parse($d)->format('M j'), $monthDays));

        // Yearly: 12 months, each bucket = all days of that month (this year)
        $year = now()->year;
        $monthBuckets = [];
        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create($year, $m, 1);
            $bucket = [];
            for ($d = $start->copy(); $d->month === $m; $d->addDay()) {
                $bucket[] = $d->toDateString();
            }
            $monthBuckets[] = $bucket;
        }
        $chartDataYearly = $series($monthBuckets, ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']);

        // ── Counts ───────────────────────────────────────────────────────
        $roleStatus = User::whereIn('role', ['student', 'teacher', 'parent'])
            ->selectRaw('role, status, COUNT(*) as c')
            ->groupBy('role', 'status')
            ->get();
        $count = fn ($role, $status = null) => (int) $roleStatus
            ->where('role', $role)
            ->when($status, fn ($c) => $c->where('status', $status))
            ->sum('c');

        return view('admin.dashboard', [
            'totalStudents'    => $count('student'),
            'totalTeachers'    => $count('teacher'),
            'totalParents'     => $count('parent'),
            'pendingStudents'  => $count('student', 'pending'),
            'pendingParents'   => $count('parent', 'pending'),
            'approvedTeachers' => $count('teacher', 'approved'),
            'todayLogs'        => AuditLog::whereDate('created_at', $today)->count(),
            // Imported teachers waiting to activate their own link are not "registrations to approve"
            'pendingUsers'     => User::where('status', 'pending')->whereNull('invite_token')->latest()->take(5)->get(),
            'recentLogs'       => AuditLog::latest()->take(8)->get(),

            // "Needs attention" — things the admin has to act on
            'pendingEnrollments'  => EnrollmentRequest::where('status', 'pending')->count(),
            'sectionsNoAdviser'   => Section::where('is_active', true)->whereNull('adviser_id')->count(),
            'availableLrns'       => OfficialLrn::whereNull('claimed_by')->count(),

            'chartDataWeekly'  => $chartDataWeekly,
            'chartDataMonthly' => $chartDataMonthly,
            'chartDataYearly'  => $chartDataYearly,
        ]);
    }
}
