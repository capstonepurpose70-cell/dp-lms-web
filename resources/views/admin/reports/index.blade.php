@extends('layouts.admin')
@section('title', 'Reports')

@section('content')
@php
    $notYet      = max(0, $placedTotal - $presentToday - $lateToday);
    $arrivedPct  = $placedTotal ? round(($presentToday + $lateToday) / $placedTotal * 100) : 0;
    $monthStart  = now()->startOfMonth()->toDateString();
    $todayStr    = now()->toDateString();
@endphp
<style>
    /* Uses admin layout tokens so dark mode works */
    .rp-page { animation: rp-fade .22s ease both; max-width:1280px; }
    @keyframes rp-fade { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }

    .rp-head { display:flex; justify-content:space-between; align-items:flex-end; gap:12px; flex-wrap:wrap; margin-bottom:18px; }
    .rp-title { font-size:22px; font-weight:800; color:var(--slate-900); margin:0; letter-spacing:-0.01em; }
    .rp-sub { font-size:13px; color:var(--slate-500); margin:4px 0 0; }
    .rp-sy { display:inline-block; padding:2px 9px; border-radius:999px; background:rgba(37,99,235,.10); color:#1d4ed8; font-weight:700; font-size:12px; }
    [data-theme="dark"] .rp-sy { color:#93c5fd; }
    .rp-btn { display:inline-flex; align-items:center; gap:7px; height:38px; padding:0 14px; border-radius:10px; font-size:13px; font-weight:700;
        cursor:pointer; text-decoration:none; border:1px solid var(--border-default); background:var(--white); color:var(--slate-700); }
    .rp-btn:hover { border-color:#2563eb; color:#2563eb; }
    .rp-btn.primary { background:#1d4ed8; border-color:#1d4ed8; color:#fff; }
    .rp-btn.primary:hover { background:#1e40af; color:#fff; }
    .rp-btn svg { width:15px; height:15px; }

    .rp-stats { display:grid; grid-template-columns:repeat(5, minmax(0,1fr)); gap:12px; margin-bottom:16px; }
    .rp-stat { background:var(--white); border:1px solid var(--border-default); border-radius:14px; padding:14px 16px; box-shadow:var(--shadow-sm); }
    .rp-stat b { display:block; font-size:24px; font-weight:800; color:var(--slate-900); line-height:1.1; }
    .rp-stat span { font-size:12.5px; color:var(--slate-500); font-weight:600; }
    .rp-stat small { display:block; font-size:11.5px; color:var(--slate-500); margin-top:3px; }
    .rp-stat small.warn { color:#b45309; font-weight:700; }
    [data-theme="dark"] .rp-stat small.warn { color:#fbbf24; }

    .rp-grid2 { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:16px; margin-bottom:16px; }
    .rp-card { background:var(--white); border:1px solid var(--border-default); border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; margin-bottom:16px; }
    .rp-grid2 .rp-card { margin-bottom:0; }
    .rp-card-h { display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; padding:14px 18px; border-bottom:1px solid var(--border-default); background:var(--slate-50); }
    .rp-card-h h3 { margin:0; font-size:14.5px; font-weight:800; color:var(--slate-900); }
    .rp-card-h p { margin:2px 0 0; font-size:12px; color:var(--slate-500); }
    .rp-card-b { padding:16px 18px; }
    .rp-chart { position:relative; height:240px; }
    .rp-legend { display:flex; gap:14px; font-size:12px; color:var(--slate-500); font-weight:600; }
    .rp-legend i { display:inline-block; width:10px; height:10px; border-radius:3px; margin-right:5px; vertical-align:-1px; }
    .rp-nochart { position:absolute; inset:0; display:grid; place-items:center; text-align:center; color:var(--slate-500); font-size:13px; padding:0 16px; }

    .rp-today { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:10px; margin-bottom:14px; }
    .rp-today div { border-radius:12px; padding:12px 14px; }
    .rp-today b { display:block; font-size:22px; font-weight:800; line-height:1.1; }
    .rp-today span { font-size:12px; font-weight:600; }
    .rp-t-p { background:rgba(22,163,74,.10); color:#15803d; }
    .rp-t-l { background:rgba(217,119,6,.12); color:#b45309; }
    .rp-t-n { background:var(--slate-100); color:var(--slate-700); }
    [data-theme="dark"] .rp-t-p { color:#4ade80; } [data-theme="dark"] .rp-t-l { color:#fbbf24; }
    .rp-bar { height:10px; border-radius:999px; background:var(--slate-100); overflow:hidden; display:flex; }
    .rp-bar i { display:block; height:100%; }

    .rp-scroll { overflow-x:auto; }
    .rp-table { width:100%; border-collapse:collapse; font-size:13px; min-width:760px; }
    .rp-table th { text-align:left; font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--slate-500);
        padding:10px 14px; background:var(--slate-50); border-bottom:1px solid var(--border-default); white-space:nowrap; }
    .rp-table td { padding:10px 14px; border-bottom:1px solid var(--border-default); color:var(--slate-700); vertical-align:middle; }
    .rp-table tr:last-child td { border-bottom:0; }
    .rp-table tr.grp td { background:var(--slate-50); font-size:11.5px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; color:var(--slate-700); padding:7px 14px; }
    .rp-strong { font-weight:700; color:var(--slate-900); }
    .rp-dim { color:var(--slate-400); }
    .rp-ok { color:#15803d; font-weight:700; } .rp-late { color:#b45309; font-weight:700; }
    [data-theme="dark"] .rp-ok { color:#4ade80; } [data-theme="dark"] .rp-late { color:#fbbf24; }
    .rp-tag { display:inline-block; padding:2px 8px; border-radius:7px; font-size:12px; font-weight:600; background:rgba(37,99,235,.10); color:#1d4ed8; margin:1px 3px 1px 0; }
    [data-theme="dark"] .rp-tag { color:#93c5fd; }

    .rp-dl { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1.4fr); gap:16px; }
    .rp-dl-box { border:1px solid var(--border-default); border-radius:12px; padding:14px; }
    .rp-dl-box b { display:block; color:var(--slate-900); font-size:13.5px; margin-bottom:3px; }
    .rp-dl-box p { margin:0 0 10px; font-size:12.5px; color:var(--slate-500); line-height:1.5; }
    .rp-dl-form { display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end; }
    .rp-dl-form label { font-size:11.5px; font-weight:700; color:var(--slate-700); display:block; margin-bottom:4px; }
    .rp-in { height:38px; padding:0 10px; border:1.5px solid var(--border-default); border-radius:9px; font-size:13px; background:var(--white); color:var(--slate-900); }
    .rp-in:focus { outline:none; border-color:#2563eb; }

    @media (max-width: 1100px) { .rp-stats { grid-template-columns:repeat(3, minmax(0,1fr)); } .rp-grid2, .rp-dl { grid-template-columns:1fr; } }
    @media (max-width: 640px)  { .rp-stats { grid-template-columns:repeat(2, minmax(0,1fr)); } }

    @media print {
        body * { visibility:hidden !important; }
        .rp-page, .rp-page * { visibility:visible !important; }
        .rp-page { position:absolute; left:0; top:0; width:100%; }
        .rp-noprint { display:none !important; }
        .rp-card, .rp-stat { box-shadow:none; break-inside:avoid; }
    }
</style>

<div class="rp-page">
    <div class="rp-head">
        <div>
            <h1 class="rp-title">Reports &amp; Analytics</h1>
            <p class="rp-sub">
                @if($sy) School year <span class="rp-sy">{{ $sy }}</span> · @else No active school year set · @endif
                as of {{ now()->format('M j, Y g:i A') }}
            </p>
        </div>
        <div class="rp-noprint" style="display:flex; gap:8px;">
            <button type="button" class="rp-btn" onclick="window.print()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print
            </button>
            <a href="#rpDownloads" class="rp-btn primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Downloads
            </a>
        </div>
    </div>

    <div class="rp-stats">
        <div class="rp-stat"><span>Enrolled this school year</span><b>{{ number_format($enrolledTotal) }}</b>
            <small class="{{ $pendingEnrollment ? 'warn' : '' }}">{{ $pendingEnrollment }} {{ Str::plural('request', $pendingEnrollment) }} waiting for review</small></div>
        <div class="rp-stat"><span>Student accounts</span><b>{{ number_format($students) }}</b><small>Activated with their LRN</small></div>
        <div class="rp-stat"><span>Active teachers</span><b>{{ number_format($teachersActive) }}</b>
            <small class="{{ $teachersWaiting ? 'warn' : '' }}">{{ $teachersWaiting }} awaiting activation</small></div>
        <div class="rp-stat"><span>Linked parents</span><b>{{ number_format($parents) }}</b>
            <small class="{{ $parentsPending ? 'warn' : '' }}">{{ $parentsPending }} pending verification</small></div>
        <div class="rp-stat"><span>Arrived today</span><b>{{ $presentToday + $lateToday }}<span style="font-size:14px;color:var(--slate-500);"> / {{ $placedTotal }}</span></b>
            <small>{{ $arrivedPct }}% of enrolled students</small></div>
    </div>

    <div class="rp-grid2">
        {{-- Attendance --}}
        <div class="rp-card">
            <div class="rp-card-h">
                <div><h3>Attendance (Raspberry Pi gate camera)</h3><p>Today and the last 14 days · late after {{ config('attendance.late_after', '07:30') }}</p></div>
                <div class="rp-legend"><span><i style="background:#16a34a"></i>On time</span><span><i style="background:#d97706"></i>Late</span></div>
            </div>
            <div class="rp-card-b">
                <div class="rp-today">
                    <div class="rp-t-p"><b>{{ $presentToday }}</b><span>On time today</span></div>
                    <div class="rp-t-l"><b>{{ $lateToday }}</b><span>Late today</span></div>
                    <div class="rp-t-n"><b>{{ $notYet }}</b><span>Not yet scanned</span></div>
                </div>
                <div class="rp-chart"><canvas id="rpAttChart" role="img" aria-label="Attendance in the last 14 days"></canvas></div>
            </div>
        </div>

        {{-- Enrollment by grade --}}
        <div class="rp-card">
            <div class="rp-card-h">
                <div><h3>Enrollment by grade</h3><p>Enrolled this school year vs. requests still waiting</p></div>
                <div class="rp-legend"><span><i style="background:#2563eb"></i>Enrolled</span><span><i style="background:#94a3b8"></i>Waiting</span></div>
            </div>
            <div class="rp-card-b">
                <div class="rp-chart" style="height:310px;"><canvas id="rpEnrChart" role="img" aria-label="Enrollment by grade"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Sections --}}
    <div class="rp-card">
        <div class="rp-card-h"><div><h3>Sections</h3><p>Students placed, gender split, average grade and today's arrivals</p></div></div>
        <div class="rp-scroll">
            <table class="rp-table">
                <thead><tr><th>Section</th><th>Adviser</th><th>Students</th><th>Boys / Girls</th><th>Avg. grade</th><th>Today</th></tr></thead>
                <tbody>
                    @forelse($sections->groupBy(fn ($r) => (string) $r['section']->grade_level) as $g => $rows)
                        <tr class="grp"><td colspan="6">Grade {{ $g }} · {{ $rows->sum('total') }} students</td></tr>
                        @foreach($rows as $r)
                            @php $arr = $r['present'] + $r['late']; @endphp
                            <tr>
                                <td class="rp-strong">{{ $r['section']->name }}</td>
                                <td>{!! $r['section']->adviser ? e($r['section']->adviser->name) : '<span class="rp-late">No adviser</span>' !!}</td>
                                <td class="rp-strong">{{ $r['total'] }}</td>
                                <td>{{ $r['male'] }} / {{ $r['female'] }}</td>
                                <td>{!! $r['avg'] !== null ? e(number_format($r['avg'], 1)) : '<span class="rp-dim">—</span>' !!}</td>
                                <td>
                                    @if($r['total'])
                                        <span class="rp-ok">{{ $r['present'] }}</span> on time ·
                                        <span class="rp-late">{{ $r['late'] }}</span> late ·
                                        <span class="rp-dim">{{ max(0, $r['total'] - $arr) }} not yet</span>
                                    @else
                                        <span class="rp-dim">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="6" class="rp-dim" style="text-align:center; padding:28px;">No active sections.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Teachers --}}
    <div class="rp-card">
        <div class="rp-card-h"><div><h3>Teachers</h3><p>Teaching load from Class Schedules</p></div></div>
        <div class="rp-scroll">
            <table class="rp-table">
                <thead><tr><th>Teacher</th><th>Advisory</th><th>Subjects</th><th>Sections</th><th>Students taught</th></tr></thead>
                <tbody>
                    @forelse($teachers as $t)
                        @php
                            $secIds   = $t->teacherSubjects->pluck('section_id')->unique();
                            $students = $secIds->sum(fn ($id) => $studentsPerSection[$id] ?? 0);
                            $subjects = $t->teacherSubjects->pluck('subject.name')->filter()->unique()->values();
                        @endphp
                        <tr>
                            <td class="rp-strong">{{ $t->name }}</td>
                            <td>
                                @forelse($advisories[$t->id] ?? [] as $a)
                                    <span class="rp-tag">Grade {{ $a->grade_level }} – {{ $a->name }}</span>
                                @empty
                                    <span class="rp-dim">—</span>
                                @endforelse
                            </td>
                            <td>{!! $subjects->isNotEmpty() ? e($subjects->take(4)->implode(', ')) . ($subjects->count() > 4 ? ' +' . ($subjects->count() - 4) : '') : '<span class="rp-dim">No classes yet</span>' !!}</td>
                            <td>{{ $secIds->count() }}</td>
                            <td class="rp-strong">{{ $students }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="rp-dim" style="text-align:center; padding:28px;">No active teachers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Downloads --}}
    <div class="rp-card rp-noprint" id="rpDownloads">
        <div class="rp-card-h"><div><h3>Downloads</h3><p>CSV files open in Excel or Google Sheets</p></div></div>
        <div class="rp-card-b rp-dl">
            <div class="rp-dl-box">
                <b>Enrollment masterlist</b>
                <p>Every enrolled student this school year — LRN, name, sex, birthdate, grade, section, adviser and general average.</p>
                <a class="rp-btn primary" href="{{ route('admin.reports.export.enrollment') }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download masterlist
                </a>
            </div>
            <div class="rp-dl-box">
                <b>Attendance log</b>
                <p>Gate-camera arrivals with date, time in and status — useful for the SF2 daily attendance report.</p>
                <form method="GET" action="{{ route('admin.reports.export.attendance') }}" class="rp-dl-form">
                    <div><label for="rpFrom">From</label><input id="rpFrom" type="date" name="from" class="rp-in" value="{{ $monthStart }}" max="{{ $todayStr }}" required></div>
                    <div><label for="rpTo">To</label><input id="rpTo" type="date" name="to" class="rp-in" value="{{ $todayStr }}" max="{{ $todayStr }}" required></div>
                    <div>
                        <label for="rpSec">Section</label>
                        <select id="rpSec" name="section_id" class="rp-in">
                            <option value="">All sections</option>
                            @foreach($sections as $r)
                                <option value="{{ $r['section']->id }}">Grade {{ $r['section']->grade_level }} – {{ $r['section']->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="rp-btn primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    var days = @json($days);
    var grades = @json($grades);
    var enrolled = grades.map(function (g) { return Number(@json($enrolledByGrade)[g] || 0); });
    var waiting  = grades.map(function (g) { return Number(@json($pendingByGrade)[g] || 0); });

    // Chart.js is from a CDN — without internet show a note instead of breaking the page
    if (typeof Chart === 'undefined') {
        ['rpAttChart', 'rpEnrChart'].forEach(function (id) {
            var c = document.getElementById(id);
            if (c) c.parentNode.innerHTML = '<div class="rp-nochart">Chart unavailable offline — the numbers above and the tables below are up to date.</div>';
        });
        return;
    }

    var dark = document.documentElement.getAttribute('data-theme') === 'dark';
    var tick = dark ? '#93a0b4' : '#94a3b8', grid = dark ? 'rgba(255,255,255,0.07)' : '#f1f5f9';
    var font = { family: "'Plus Jakarta Sans', sans-serif", size: 11.5, weight: '600' };
    var base = {
        responsive: true, maintainAspectRatio: false, animation: { duration: 500 },
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { display: false } },
        scales: {
            x: { stacked: true, grid: { display: false }, border: { display: false }, ticks: { color: tick, font: font } },
            y: { stacked: true, beginAtZero: true, grid: { color: grid }, border: { display: false },
                 ticks: { color: tick, font: font, precision: 0, maxTicksLimit: 6 } }
        }
    };
    function make(id, cfg) {
        var el = document.getElementById(id); if (!el) return;
        var old = Chart.getChart(el); if (old) old.destroy();
        new Chart(el, cfg);
    }

    make('rpAttChart', { type: 'bar', options: base, data: {
        labels: days.map(function (d) { return d.label; }),
        datasets: [
            { label: 'On time', data: days.map(function (d) { return d.present; }), backgroundColor: '#16a34a', borderRadius: 4, maxBarThickness: 26 },
            { label: 'Late',    data: days.map(function (d) { return d.late; }),    backgroundColor: '#d97706', borderRadius: 4, maxBarThickness: 26 }
        ] } });

    make('rpEnrChart', { type: 'bar', options: base, data: {
        labels: grades.map(function (g) { return 'Grade ' + g; }),
        datasets: [
            { label: 'Enrolled', data: enrolled, backgroundColor: '#2563eb', borderRadius: 4, maxBarThickness: 38 },
            { label: 'Waiting for review', data: waiting, backgroundColor: '#94a3b8', borderRadius: 4, maxBarThickness: 38 }
        ] } });
})();
</script>
@endsection
