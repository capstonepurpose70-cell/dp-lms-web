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
        return view('admin.dashboard', $this->data());
    }

    /**
     * GET /admin/dashboard/live — the same numbers as the page, for the live refresh.
     * The two lists come back as HTML from the same partials the page uses (forms, CSRF and all).
     */
    public function live()
    {
        $d = $this->data();

        return response()->json([
            'counts' => [
                'totalStudents'      => $d['totalStudents'],
                'totalTeachers'      => $d['totalTeachers'],
                'totalParents'       => $d['totalParents'],
                'pendingStudents'    => $d['pendingStudents'],
                'pendingParents'     => $d['pendingParents'],
                'approvedTeachers'   => $d['approvedTeachers'],
                'todayLogs'          => $d['todayLogs'],
                'pendingEnrollments' => $d['pendingEnrollments'],
                'sectionsNoAdviser'  => $d['sectionsNoAdviser'],
                'availableLrns'      => $d['availableLrns'],
            ],
            'charts' => [
                'weekly'  => $d['chartDataWeekly'],
                'monthly' => $d['chartDataMonthly'],
                'yearly'  => $d['chartDataYearly'],
            ],
            'pendingHtml'  => view('admin.partials.dashboard-pending', ['pendingUsers' => $d['pendingUsers']])->render(),
            'activityHtml' => view('admin.partials.dashboard-activity', ['recentLogs' => $d['recentLogs']])->render(),
            'updatedAt'    => now()->toIso8601String(),
        ])->header('Cache-Control', 'no-store');
    }

    private function data(): array
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
        // From the start of LAST year: the previous week / month / year are needed for the
        // "vs last period" comparison. The week may also end in next year.
        $from      = $weekStart->copy()->subWeek()->min(now()->subYear()->startOfYear());
        $to        = $weekEnd->copy()->max(now()->endOfYear());

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

        // Totals of each category between two dates (inclusive), for the previous period
        $total = function (Carbon $start, Carbon $end) use ($byDay) {
            $out = ['reg' => 0, 'apr' => 0, 'log' => 0];
            for ($d = $start->copy()->startOfDay(); $d->lte($end); $d->addDay()) {
                foreach ($out as $cat => $n) {
                    $out[$cat] += $byDay[$d->toDateString()][$cat] ?? 0;
                }
            }
            return $out;
        };

        // labels = short axis labels, titles = full dates for the tooltip, prev = totals of the period before
        $series = function (array $days, array $labels, array $titles, array $prev) use ($byDay) {
            $out = ['labels' => $labels, 'titles' => $titles, 'prev' => $prev, 'reg' => [], 'apr' => [], 'log' => []];
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
        $chartDataWeekly = $series(
            $weekDays,
            array_map(fn ($d) => Carbon::parse($d)->format('D'), $weekDays),
            array_map(fn ($d) => Carbon::parse($d)->format('l, M j'), $weekDays),
            $total($weekStart->copy()->subWeek(), $weekEnd->copy()->subWeek()),
        );

        // Monthly: every day of this month
        $monthDays = [];
        for ($d = now()->startOfMonth(); $d->lte(now()->endOfMonth()); $d->addDay()) {
            $monthDays[] = $d->toDateString();
        }
        $lastMonth = now()->startOfMonth()->subMonth();
        $chartDataMonthly = $series(
            $monthDays,
            array_map(fn ($d) => Carbon::parse($d)->format('M j'), $monthDays),
            array_map(fn ($d) => Carbon::parse($d)->format('l, M j'), $monthDays),
            $total($lastMonth, $lastMonth->copy()->endOfMonth()),
        );

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
        $chartDataYearly = $series(
            $monthBuckets,
            ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
            array_map(fn ($m) => Carbon::create($year, $m, 1)->format('F Y'), range(1, 12)),
            $total(Carbon::create($year - 1, 1, 1), Carbon::create($year - 1, 12, 31)),
        );

        // ── Counts ───────────────────────────────────────────────────────
        $roleStatus = User::whereIn('role', ['student', 'teacher', 'parent'])
            ->selectRaw('role, status, COUNT(*) as c')
            ->groupBy('role', 'status')
            ->get();
        $count = fn ($role, $status = null) => (int) $roleStatus
            ->where('role', $role)
            ->when($status, fn ($c) => $c->where('status', $status))
            ->sum('c');

        return [
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
        ];
    }
}
