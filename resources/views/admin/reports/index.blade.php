@extends('layouts.admin')
@section('title', 'Reports')

@section('content')

<style>


    :root {
        --text-1:    var(--slate-900);
        --text-2:    var(--slate-700);
        --text-3:    var(--slate-500);
        --surface:   var(--white);
        --border:    var(--slate-200);
        --bg:        var(--slate-50);
        --accent:    var(--blue-500);
        --accent-lt: var(--blue-50);
        --green:      #1D9E75;
        --green-lt:   #E1F5EE;
        --green-dark: #0F6E56;
    }

    
    .rp-page { max-width: 1280px; margin: 0 auto; }

    .rp-header { margin-bottom: 1.75rem; }
    .rp-title  { font-size: 22px; font-weight: 700; color: var(--text-1); letter-spacing: -0.02em; line-height:1.2; }
    .rp-sub    { font-size: 13px; color: var(--text-3); margin-top: 4px; }

    /* Stat cards */
    .rp-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem; margin-bottom: 1.5rem;
    }
    .rp-stat-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--r-lg); padding: 1.25rem 1.375rem;
        position: relative; overflow: hidden;
        transition: box-shadow .18s, transform .18s;
    }
    .rp-stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .rp-stat-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0;
        height: 3px; border-radius: var(--r-lg) var(--r-lg) 0 0;
    }
    .rp-stat-card.blue::before  { background: var(--accent); }
    .rp-stat-card.teal::before  { background: #1D9E75; }
    .rp-stat-card.amber::before { background: #BA7517; }
    .rp-stat-card.green::before { background: var(--green); }

    .rp-stat-icon {
        width: 40px; height: 40px; border-radius: var(--r-md);
        display: flex; align-items: center; justify-content: center; margin-bottom: .875rem;
    }
    .rp-stat-icon svg { width: 20px; height: 20px; }
    .rp-stat-icon.blue  { background: var(--accent-lt);  color: var(--accent); }
    .rp-stat-icon.teal  { background: #E1F5EE;           color: #0F6E56; }
    .rp-stat-icon.amber { background: #FAEEDA;           color: #854F0B; }
    .rp-stat-icon.green { background: var(--green-lt);   color: var(--green-dark); }

    .rp-stat-label {
        font-size: 11px; font-weight: 700; letter-spacing: .07em;
        text-transform: uppercase; color: var(--text-3); margin-bottom: 4px;
    }
    .rp-stat-value { font-size: 32px; font-weight: 700; line-height: 1; letter-spacing: -0.03em; margin-top: 4px; }
    .rp-stat-meta  { font-size: 12px; font-weight: 500; margin-top: 6px; display: flex; align-items: center; gap: 4px; }
    .rp-stat-meta svg { width: 13px; height: 13px; }

    /* Shared panel */
    .rp-panel {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--r-lg); overflow: hidden; margin-bottom: 1.25rem;
    }
    .rp-panel-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1.125rem 1.5rem;
        border-bottom: 1px solid var(--border);
        background: var(--bg);
    }
    .rp-panel-title {
        font-size: 14.5px; font-weight: 700; color: var(--text-1);
        display: flex; align-items: center; gap: 8px;
    }
    .rp-panel-title svg { width: 17px; height: 17px; color: var(--accent); }
    .rp-panel-body { padding: 1.25rem 1.5rem; }

    /* Doughnut grid */
    .rp-donut-grid {
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem; margin-bottom: 1.25rem;
    }
    @media (max-width: 900px) { .rp-donut-grid { grid-template-columns: 1fr; } }

    /* Legend rows */
    .rp-leg-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 6px 0; border-bottom: 1px solid var(--border); font-size: 13px;
    }
    .rp-leg-row:last-child { border-bottom: none; }
    .rp-leg-left { display: flex; align-items: center; gap: 8px; color: var(--text-2); }
    .rp-leg-dot  { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .rp-leg-val  { font-weight: 600; color: var(--text-1); }

    /* Bar chart legend */
    .rp-bar-legend {
        display: flex; align-items: center; gap: 1.25rem;
        padding: .75rem 1.5rem; border-bottom: 1px solid var(--border);
    }
    .rp-bar-leg-item { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: var(--text-3); }
    .rp-bar-swatch   { width: 28px; height: 3px; border-radius: 2px; }

    /* Teacher table */
    .rp-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .rp-table thead th {
        padding-bottom: 10px; text-align: left;
        font-size: 11px; font-weight: 700; letter-spacing: .07em;
        text-transform: uppercase; color: var(--text-3);
    }
    .rp-table tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
    .rp-table tbody tr:last-child { border-bottom: none; }
    .rp-table tbody tr:hover { background: var(--accent-lt); }
    .rp-table td { padding: 11px 0; }

    /* No-data overlay */
    .rp-no-data {
        position: absolute; inset: 0;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        font-size: 12px; color: var(--text-3); font-weight: 500;
        pointer-events: none;
    }
    .rp-no-data svg { width: 28px; height: 28px; margin-bottom: 6px; color: var(--border); }
</style>

<div class="rp-page">

    {{-- Page header --}}
    <div class="rp-header">
        <div class="rp-title">Reports &amp; Analytics</div>
        <div class="rp-sub">System-wide statistics for school year 2025–2026</div>
    </div>

    {{-- Stat cards --}}
    <div class="rp-stat-grid">

        <div class="rp-stat-card blue">
            <div class="rp-stat-icon blue">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l-9-5m9 5l9-5"/>
                </svg>
            </div>
            <div class="rp-stat-label">Total Students</div>
            <div class="rp-stat-value" style="color:var(--accent);">{{ $totalStudents }}</div>
            <div class="rp-stat-meta" style="color:var(--accent);">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $approvedStudents }} approved
            </div>
        </div>

        <div class="rp-stat-card teal">
            <div class="rp-stat-icon teal">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87a4 4 0 100-8 4 4 0 000 8zm6-12a3 3 0 11-6 0 3 3 0 016 0zM6 8a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="rp-stat-label">Teachers</div>
            <div class="rp-stat-value" style="color:#1D9E75;">{{ $activeTeachers }}</div>
            <div class="rp-stat-meta" style="color:#0F6E56;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Active accounts
            </div>
        </div>

        <div class="rp-stat-card amber">
            <div class="rp-stat-icon amber">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <div class="rp-stat-label">Parents</div>
            <div class="rp-stat-value" style="color:#BA7517;">{{ $totalParents }}</div>
            <div class="rp-stat-meta" style="color:#854F0B;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Registered
            </div>
        </div>

        <div class="rp-stat-card green">
            <div class="rp-stat-icon green">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="rp-stat-label">Enrollments</div>
            <div class="rp-stat-value" style="color:var(--green);">{{ $totalEnrollments }}</div>
            <div class="rp-stat-meta" style="color:var(--green-dark);">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                This school year
            </div>
        </div>

    </div>

    {{-- Doughnut charts --}}
    <div class="rp-donut-grid">

        {{-- 1. Users by role --}}
        <div class="rp-panel">
            <div class="rp-panel-header">
                <div class="rp-panel-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87a4 4 0 100-8 4 4 0 000 8zm6-12a3 3 0 11-6 0 3 3 0 016 0zM6 8a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Users by Role
                </div>
            </div>
            <div class="rp-panel-body">
                <div style="position:relative;height:200px;margin-bottom:1rem;">
                    <canvas id="roleChart"></canvas>
                    @if($registrationsByRole->sum('total') == 0)
                    <div class="rp-no-data">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        No data yet
                    </div>
                    @endif
                </div>
                @foreach($registrationsByRole as $item)
                @php $dotColors = ['student'=>'#2478e4','teacher'=>'#1D9E75','parent'=>'#BA7517']; @endphp
                <div class="rp-leg-row">
                    <div class="rp-leg-left">
                        <div class="rp-leg-dot" style="background:{{ $dotColors[$item->role] ?? '#94a3b8' }};"></div>
                        <span class="capitalize">{{ $item->role }}</span>
                    </div>
                    <span class="rp-leg-val">{{ $item->total }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- 2. Student status --}}
        <div class="rp-panel">
            <div class="rp-panel-header">
                <div class="rp-panel-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Student Status
                </div>
            </div>
            <div class="rp-panel-body">
                <div style="position:relative;height:200px;margin-bottom:1rem;">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="rp-leg-row">
                    <div class="rp-leg-left"><div class="rp-leg-dot" style="background:#1D9E75;"></div><span>Approved</span></div>
                    <span class="rp-leg-val">{{ $approvedStudents }}</span>
                </div>
                <div class="rp-leg-row">
                    <div class="rp-leg-left"><div class="rp-leg-dot" style="background:#BA7517;"></div><span>Pending</span></div>
                    <span class="rp-leg-val">{{ $pendingStudents }}</span>
                </div>
                <div class="rp-leg-row">
                    <div class="rp-leg-left"><div class="rp-leg-dot" style="background:#EF4444;"></div><span>Rejected</span></div>
                    <span class="rp-leg-val">{{ $rejectedStudents }}</span>
                </div>
            </div>
        </div>

        {{-- 3. Enrolled by grade --}}
        <div class="rp-panel">
            <div class="rp-panel-header">
                <div class="rp-panel-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l-9-5m9 5l9-5"/>
                    </svg>
                    Enrolled by Grade
                </div>
            </div>
            <div class="rp-panel-body">
                <div style="position:relative;height:200px;margin-bottom:1rem;">
                    <canvas id="gradeChart"></canvas>
                    @if($enrollmentsByGrade->sum('total') == 0)
                    <div class="rp-no-data">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        No data yet
                    </div>
                    @endif
                </div>
                <div style="max-height:96px;overflow-y:auto;">
                    @forelse($enrollmentsByGrade as $item)
                    <div class="rp-leg-row">
                        <span style="color:var(--text-2);">Grade {{ $item->grade_level }}</span>
                        <span class="rp-leg-val">{{ $item->total }}</span>
                    </div>
                    @empty
                    <p style="font-size:12px;color:var(--text-3);text-align:center;padding:8px 0;">No enrollments yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    {{-- Bar chart: Enrollment Overview by Grade --}}
    <div class="rp-panel">
        <div class="rp-panel-header">
            <div class="rp-panel-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                </svg>
                Enrollment Overview by Grade
            </div>
        </div>
        <div class="rp-bar-legend">
            <div class="rp-bar-leg-item">
                <span class="rp-bar-swatch" style="background:#2478e4;"></span> Students Enrolled
            </div>
            <div class="rp-bar-leg-item">
                <span class="rp-bar-swatch" style="background:#1D9E75;"></span> Approved
            </div>
        </div>
        <div class="rp-panel-body">
            <div style="position:relative;height:260px;">
                <canvas id="enrollBarChart" aria-label="Enrollment bar chart" role="img"></canvas>
            </div>
        </div>
    </div>

    {{-- Teacher breakdown --}}
    <div class="rp-panel">
        <div class="rp-panel-header">
            <div class="rp-panel-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87a4 4 0 100-8 4 4 0 000 8z"/>
                </svg>
                Teacher Overview
            </div>
        </div>
        <div style="padding:0 1.5rem 1.25rem;">
            <table class="rp-table">
                <thead>
                    <tr>
                        <th>Teacher</th>
                        <th>Subjects</th>
                        <th>Sections</th>
                        <th>Students</th>
                        <th>Grade Level</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teacherStats as $teacher)
                    <tr>
                        <td style="color:var(--text-1);font-weight:600;">{{ $teacher->name }}</td>
                        <td style="color:var(--text-2);">{{ $teacher->teacherSubjects->unique('subject_id')->count() }}</td>
                        <td style="color:var(--text-2);">{{ $teacher->teacherSubjects->unique('section_id')->count() }}</td>
                        <td>
                            @php
                                $sids  = $teacher->teacherSubjects->pluck('section_id')->unique();
                                $count = \App\Models\User::where('role','student')
                                    ->where('status','approved')
                                    ->whereIn('section_id', $sids)
                                    ->count();
                            @endphp
                            <span style="color:#1D9E75;font-weight:600;">{{ $count }}</span>
                        </td>
                        <td style="color:var(--text-2);">Grade {{ $teacher->grade_level ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding:2rem;text-align:center;color:var(--text-3);">
                            No teachers yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    function startReports() {

    var FONT = "'Plus Jakarta Sans', sans-serif";

    function ensureChart(cb) {
        if (typeof Chart !== 'undefined') return cb();
        var sc = document.createElement('script');
        sc.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js';
        sc.onload = cb;
        document.head.appendChild(sc);
    }

    var TOOLTIP = {
        backgroundColor : '#0c3566',
        titleColor      : '#bcd6fc',
        bodyColor       : '#e0edff',
        borderColor     : 'rgba(36,120,228,0.35)',
        borderWidth     : 1,
        padding         : 12,
        cornerRadius    : 10,
        boxPadding      : 6,
        usePointStyle   : true,
        titleFont : { family: FONT, size: 13, weight: '700' },
        bodyFont  : { family: FONT, size: 12, weight: '500' },
    };

    /* Total in the middle of each doughnut */
    var centerText = {
        id: 'centerText',
        afterDraw: function (chart) {
            if (chart.config.type !== 'doughnut') return;
            var ds = chart.data.datasets[0];
            if (!ds || !ds._real) return;
            var total = ds._real.reduce(function (a, b) { return a + b; }, 0);
            if (total <= 0) return;
            var ca = chart.chartArea, ctx = chart.ctx;
            var cx = (ca.left + ca.right) / 2, cy = (ca.top + ca.bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            ctx.fillStyle = '#0f172a'; ctx.font = '700 26px ' + FONT;
            ctx.fillText(total.toLocaleString(), cx, cy - 6);
            ctx.fillStyle = '#94a3b8'; ctx.font = '600 11px ' + FONT;
            ctx.fillText('Total', cx, cy + 14);
            ctx.restore();
        }
    };

    function make(id, config) {
        var el = document.getElementById(id);
        if (!el) return;
        var ex = Chart.getChart(el);   /* prevent "canvas already in use" */
        if (ex) ex.destroy();
        return new Chart(el, config);
    }

    function donut(labels, values, colors) {
        var has = values.some(function (v) { return v > 0; });
        return {
            type: 'doughnut',
            plugins: [centerText],
            data: {
                labels: labels,
                datasets: [{
                    _real: values,
                    data: has ? values : [1],
                    backgroundColor: has ? colors : ['#e2e8f0'],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverOffset: 10,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 750, easing: 'easeOutQuart' },
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: Object.assign({}, TOOLTIP, {
                        callbacks: {
                            label: function (i) {
                                var real = i.dataset._real || [];
                                var total = real.reduce(function (a, b) { return a + b; }, 0) || 1;
                                var v = real[i.dataIndex] || 0;
                                var pct = Math.round(v / total * 100);
                                return '  ' + i.label + ': ' + v.toLocaleString() + '  (' + pct + '%)';
                            }
                        }
                    }),
                },
            },
        };
    }

    ensureChart(function buildCharts() {

        /* 1. Users by role */
        make('roleChart', donut(
            {!! json_encode($registrationsByRole->pluck('role')->map(fn($r) => ucfirst($r))->values()) !!},
            {!! json_encode($registrationsByRole->pluck('total')->map(fn($t) => (int) $t)->values()) !!},
            ['#2478e4','#1D9E75','#BA7517','#534AB7']
        ));

        /* 2. Student status */
        make('statusChart', donut(
            ['Approved','Pending','Rejected'],
            [{{ (int) $approvedStudents }}, {{ (int) $pendingStudents }}, {{ (int) $rejectedStudents }}],
            ['#1D9E75','#BA7517','#EF4444']
        ));

        /* 3. Enrolled by grade */
        make('gradeChart', donut(
            {!! json_encode($enrollmentsByGrade->pluck('grade_level')->map(fn($g) => 'Grade '.$g)->values()) !!},
            {!! json_encode($enrollmentsByGrade->pluck('total')->map(fn($t) => (int) $t)->values()) !!},
            ['#2478e4','#1D9E75','#BA7517','#534AB7','#38bdf8','#f472b6']
        ));

        /* 4. Enrollment bar chart */
        var DEFAULT_LABELS = ['Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12'];
        var gradeLabels    = {!! json_encode($enrollmentsByGrade->pluck('grade_level')->map(fn($g) => 'Grade '.$g)->values()) !!};
        var enrolledTotals = {!! json_encode($enrollmentsByGrade->pluck('total')->map(fn($t) => (int) $t)->values()) !!};
        var approvedTotals = {!! json_encode($approvedByGrade->map(fn($t) => (int) $t)->values()) !!};
        var hasBar = enrolledTotals.length > 0 && enrolledTotals.some(function (v) { return v > 0; });

        var gradientPlugin = {
            id: 'rpGradient',
            beforeDatasetsDraw: function (chart) {
                var ca = chart.chartArea, ctx = chart.ctx;
                if (!ca) return;
                chart.data.datasets.forEach(function (ds, i) {
                    var hex = i === 0 ? '#2478e4' : '#1D9E75';
                    var r = parseInt(hex.slice(1,3),16), gg = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16);
                    var g = ctx.createLinearGradient(0, ca.top, 0, ca.bottom);
                    g.addColorStop(0, 'rgba('+r+','+gg+','+b+',0.95)');
                    g.addColorStop(1, 'rgba('+r+','+gg+','+b+',0.5)');
                    ds.backgroundColor = g;
                });
            }
        };

        make('enrollBarChart', {
            type: 'bar',
            plugins: [gradientPlugin],
            data: {
                labels: hasBar ? gradeLabels : DEFAULT_LABELS,
                datasets: [
                    { label: 'Students Enrolled', data: hasBar ? enrolledTotals : [0,0,0,0,0,0], borderRadius: 6, borderSkipped: false, barPercentage: 0.55, categoryPercentage: 0.7 },
                    { label: 'Approved',           data: hasBar ? approvedTotals : [0,0,0,0,0,0], borderRadius: 6, borderSkipped: false, barPercentage: 0.55, categoryPercentage: 0.7 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                animation: { duration: 750, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: Object.assign({}, TOOLTIP, {
                        callbacks: { label: function (i) { return '  ' + i.dataset.label + ': ' + i.parsed.y.toLocaleString(); } }
                    }),
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false },
                         ticks: { color: '#94a3b8', font: { family: FONT, size: 11.5, weight: '600' }, padding: 8 } },
                    y: { grid: { color: '#f1f5f9' }, border: { display: false }, min: 0,
                         ticks: { color: '#94a3b8', font: { family: FONT, size: 11, weight: '500' }, padding: 10, maxTicksLimit: 6, stepSize: 1, callback: function (v) { return Number.isInteger(v) ? v : ''; } } },
                },
            },
        });

    });

    } /* end startReports */

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startReports);
    } else {
        startReports();   /* DOM already parsed (cached/normal refresh) */
    }
})();
</script>
@endsection