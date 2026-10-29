@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')

<style>
    .db { max-width: 1280px; margin: 0 auto; --db-line: var(--border-default, #E2E8F0); }
    .db .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
    .db-chart { position: relative; }
    .db-card { background: var(--surface-card); border: 1px solid var(--db-line); border-radius: 14px; }

    /* Header */
    .db-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 10px 16px; margin-bottom: 20px; }
    .db-title { font-size: 26px; font-weight: 800; color: var(--slate-900); letter-spacing: -.02em; line-height: 1.2; }
    .db-sub { font-size: 14px; color: var(--slate-600); margin-top: 4px; }
    .db-live { display: inline-flex; align-items: center; gap: 10px; font-size: 13px; color: var(--slate-600); }
    .db-live-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: var(--success-light, #DCFCE7); color: #166534; font-weight: 700; }
    .db-live-pill i { width: 7px; height: 7px; border-radius: 50%; background: #16A34A; }
    .db-live.paused .db-live-pill { background: var(--slate-100); color: var(--slate-600); }
    .db-live.paused .db-live-pill i { background: var(--slate-400); }
    .db-live.updating .db-live-pill i { animation: db-blink 1s ease-in-out infinite; }
    @keyframes db-blink { 50% { opacity: .25; } }

    /* Summary strip */
    .db-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); margin-bottom: 16px; }
    .db-kpi { padding: 18px 22px; }
    .db-kpi + .db-kpi { border-left: 1px solid var(--db-line); }
    .db-kpi-label { display: flex; align-items: center; gap: 8px; font-size: 13.5px; font-weight: 600; color: var(--slate-600); }
    .db-kpi-label svg { width: 18px; height: 18px; color: var(--slate-400); flex-shrink: 0; }
    .db-kpi-num { display: block; margin-top: 8px; font-size: 30px; font-weight: 800; color: var(--slate-900); line-height: 1.1; letter-spacing: -.02em; font-variant-numeric: tabular-nums; }
    .db-kpi-note { display: block; margin-top: 4px; font-size: 13px; color: var(--slate-500); font-variant-numeric: tabular-nums; }
    .db-kpi-note.warn { color: #B45309; font-weight: 600; }

    /* Needs attention */
    .db-att { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); margin-bottom: 16px; overflow: hidden; }
    .db-att a { display: flex; align-items: center; gap: 12px; padding: 14px 18px; text-decoration: none; color: inherit; transition: background-color .15s ease; }
    .db-att a + a { border-left: 1px solid var(--db-line); }
    .db-att a:hover { background: var(--slate-50); }
    .db-att-ic { width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center; flex-shrink: 0; background: var(--slate-100); color: var(--slate-600); }
    .db-att a.warn .db-att-ic { background: #FEF3C7; color: #B45309; }
    .db-att a.ok .db-att-ic { background: #DCFCE7; color: #15803D; }
    .db-att-ic svg { width: 19px; height: 19px; }
    .db-att-txt { flex: 1; min-width: 0; font-size: 13.5px; color: var(--slate-600); line-height: 1.4; }
    .db-att-txt b { display: block; font-size: 18px; color: var(--slate-900); font-variant-numeric: tabular-nums; }
    .db-att-go { width: 16px; height: 16px; color: var(--slate-400); flex-shrink: 0; transition: transform .15s ease; }
    .db-att a:hover .db-att-go { transform: translateX(2px); }

    /* Period filter, one row above both charts */
    .db-filter { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin: 4px 0 12px; }
    .db-filter h2 { font-size: 16px; font-weight: 800; color: var(--slate-900); }
    .db-tabs { display: inline-flex; padding: 3px; border-radius: 10px; background: var(--slate-100); border: 1px solid var(--db-line); }
    .db-tabs button { min-height: 34px; padding: 0 14px; border-radius: 8px; border: 0; background: transparent; font: inherit; font-size: 13px; font-weight: 700; color: var(--slate-600); cursor: pointer; transition: background-color .15s ease, color .15s ease; }
    .db-tabs button[aria-pressed="true"] { background: var(--surface-card); color: var(--slate-900); box-shadow: 0 1px 2px rgba(15,23,42,.08); }

    .db-charts { display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr); gap: 16px; margin-bottom: 16px; }
    .db-chart { padding: 18px 20px 14px; }
    .db-chart-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
    .db-chart-title { font-size: 14.5px; font-weight: 700; color: var(--slate-700); }
    .db-chart-total { display: block; margin-top: 2px; font-size: 24px; font-weight: 800; color: var(--slate-900); letter-spacing: -.02em; font-variant-numeric: tabular-nums; }
    .db-chart-total small { font-size: 13px; font-weight: 600; color: var(--slate-500); letter-spacing: 0; }
    .db-legend { display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 12.5px; font-weight: 600; color: var(--slate-600); }
    .db-legend span { display: inline-flex; align-items: center; gap: 6px; }
    .db-legend i { width: 10px; height: 10px; border-radius: 3px; }
    .db-canvas { position: relative; height: 250px; }
    .db-chart-note { display: grid; place-items: center; height: 100%; text-align: center; padding: 0 16px; font-size: 13px; color: var(--slate-500); }


    /* Change vs the previous period: arrow + words, never colour alone */
    .db-delta { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-size: 12.5px; font-weight: 700; white-space: nowrap; background: var(--slate-100); color: var(--slate-600); }
    .db-delta svg path { display: none; }
    .db-delta[data-tone="up"] { background: #DCFCE7; color: #166534; }
    .db-delta[data-tone="down"] { background: #FEE2E2; color: #991B1B; }
    .db-delta[data-tone="up"] .up, .db-delta[data-tone="down"] .down, .db-delta[data-tone="flat"] .flat { display: block; }
    [data-theme="dark"] .db-delta[data-tone="up"] { background: rgba(22,163,74,.18); color: #86EFAC; }
    [data-theme="dark"] .db-delta[data-tone="down"] { background: rgba(220,38,38,.18); color: #FCA5A5; }

    /* Chart tooltip card */
    .db-tip { position: absolute; left: 0; top: 0; z-index: 5; min-width: 150px; padding: 10px 12px; border-radius: 10px; pointer-events: none;
              background: var(--surface-card); border: 1px solid var(--db-line); box-shadow: 0 10px 30px -8px rgba(15,23,42,.25), 0 2px 6px rgba(15,23,42,.06);
              font-size: 12.5px; color: var(--slate-700); opacity: 0; transition: opacity .12s ease, transform .12s ease; }
    .db-tip[data-open="true"] { opacity: 1; }
    .db-tip-title { font-weight: 700; color: var(--slate-900); margin-bottom: 6px; }
    .db-tip-row { display: flex; align-items: center; gap: 8px; }
    .db-tip-row + .db-tip-row { margin-top: 4px; }
    .db-tip-row i { width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0; }
    .db-tip-row b { margin-left: auto; padding-left: 14px; color: var(--slate-900); font-variant-numeric: tabular-nums; }

    /* Sparkline in "Activity today" */
    .db-kpi { position: relative; }
    .db-spark { position: absolute; right: 18px; top: 16px; width: 96px; height: 32px; overflow: visible; }
    [data-theme="dark"] .db-spark-line { stroke: #3B82F6; }
    [data-theme="dark"] .db-spark-area { fill: rgba(59,130,246,.16); }
    @media (max-width: 1100px) { .db-spark { display: none; } }
    .db-spark-area { fill: rgba(37,99,235,.10); }
    .db-spark-line { fill: none; stroke: #2563EB; stroke-width: 2; stroke-linejoin: round; stroke-linecap: round; vector-effect: non-scaling-stroke; }
    .db-chart .db-legend { margin: -4px 0 10px; }

    /* Lists */
    .db-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .panel-header { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 16px 20px 12px; border-bottom: 1px solid var(--db-line); }
    .panel-title { font-size: 15px; font-weight: 800; color: var(--slate-900); }
    .panel-link { display: inline-flex; align-items: center; gap: 4px; min-height: 32px; font-size: 13px; font-weight: 700; color: var(--blue-600); text-decoration: none; }
    .panel-link:hover { text-decoration: underline; text-underline-offset: 3px; }
    .panel-body { max-height: 420px; overflow-y: auto; }

    .pending-row { display: flex; align-items: center; gap: 12px; padding: 12px 20px; border-bottom: 1px solid var(--db-line); }
    .pending-row:last-child { border-bottom: 0; }
    .pending-avatar { width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0; display: grid; place-items: center; background: var(--blue-50); color: var(--blue-700); font-size: 13px; font-weight: 800; }
    .pending-info { flex: 1; min-width: 0; }
    .pending-name { font-size: 14px; font-weight: 700; color: var(--slate-900); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pending-email { font-size: 12.5px; color: var(--slate-500); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pending-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
    .pending-actions form { display: inline; margin: 0; }
    .role-pill { padding: 3px 9px; border-radius: 999px; font-size: 11.5px; font-weight: 700; background: var(--slate-100); color: var(--slate-700); }
    .role-pill.student { background: #DBEAFE; color: #1E40AF; }
    .role-pill.teacher { background: #CCFBF1; color: #115E59; }
    .role-pill.parent  { background: #FEF3C7; color: #92400E; }
    .action-btn { display: inline-flex; align-items: center; gap: 5px; min-height: 34px; padding: 0 11px; border-radius: 8px; font-size: 12.5px; font-weight: 700; cursor: pointer; border: 1px solid transparent; transition: background-color .15s ease; }
    .action-btn svg { width: 14px; height: 14px; }
    .action-btn.approve { background: #16A34A; color: #fff; }
    .action-btn.approve:hover { background: #15803D; }
    .action-btn.reject { background: transparent; color: #B91C1C; border-color: #FECACA; }
    .action-btn.reject:hover { background: #FEF2F2; }

    .log-row { display: flex; align-items: flex-start; gap: 12px; padding: 12px 20px; border-bottom: 1px solid var(--db-line); }
    .log-row:last-child { border-bottom: 0; }
    .log-dot { width: 8px; height: 8px; border-radius: 50%; margin-top: 6px; flex-shrink: 0; background: var(--slate-400); }
    .log-dot.good { background: #16A34A; }
    .log-dot.bad { background: #DC2626; }
    .log-body { flex: 1; min-width: 0; }
    .log-action { font-size: 13.5px; font-weight: 600; color: var(--slate-900); overflow-wrap: anywhere; }
    .log-meta { display: flex; flex-wrap: wrap; gap: 2px 10px; font-size: 12.5px; color: var(--slate-500); margin-top: 1px; }
    .log-module { padding: 0 7px; border-radius: 6px; background: var(--slate-100); color: var(--slate-600); font-weight: 600; }
    .log-time { font-size: 12px; color: var(--slate-500); white-space: nowrap; flex-shrink: 0; }

    .panel-empty { padding: 32px 20px; text-align: center; font-size: 13.5px; color: var(--slate-500); line-height: 1.5; }
    .panel-empty svg { width: 34px; height: 34px; color: var(--slate-300); margin: 0 auto 8px; display: block; }
    .panel-empty b { display: block; color: var(--slate-800); }

    /* One quiet entrance on first load */
    .db-in { animation: db-rise .45s cubic-bezier(.2,.7,.2,1) both; animation-delay: calc(var(--i, 0) * 60ms); }
    @keyframes db-rise { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
    .db-flash { animation: db-flash 1.2s ease; }
    @keyframes db-flash { 0% { background: rgba(37,99,235,.10); } 100% { background: transparent; } }

    @media (max-width: 1100px) { .db-charts { grid-template-columns: 1fr; } }
    @media (max-width: 900px) {
        .db-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .db-kpi:nth-child(3) { border-left: 0; }
        .db-kpi:nth-child(n+3) { border-top: 1px solid var(--db-line); }
        .db-att { grid-template-columns: 1fr; }
        .db-att a + a { border-left: 0; border-top: 1px solid var(--db-line); }
        .db-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 520px) {
        .db-kpi { padding: 14px 14px; }
        .db-kpi-num { font-size: 24px; }
        .pending-row { flex-wrap: wrap; }
        .pending-actions { width: 100%; justify-content: flex-end; }
    }
    @media (prefers-reduced-motion: reduce) {
        .db-in, .db-flash, .db-live.updating .db-live-pill i { animation: none !important; }
    }
</style>

@php
    $icons = [
        'students' => 'M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l-9-5m9 5l9-5',
        'teachers' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87a4 4 0 100-8 4 4 0 000 8zm6-12a3 3 0 11-6 0 3 3 0 016 0zM6 8a3 3 0 11-6 0 3 3 0 016 0z',
        'parents'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'activity' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
    ];
@endphp

<div class="db" id="dbRoot" data-live="{{ route('admin.dashboard.live') }}"
     data-charts="{{ json_encode(['weekly' => $chartDataWeekly, 'monthly' => $chartDataMonthly, 'yearly' => $chartDataYearly]) }}">

    <header class="db-head db-in" style="--i:0">
        <div>
            <h1 class="db-title">Dashboard</h1>
            <p class="db-sub">Accounts, enrollment and activity across DP-LMS.</p>
        </div>
        <p class="db-live" id="dbLive" aria-live="polite">
            <span class="db-live-pill"><i aria-hidden="true"></i><span id="dbLiveLabel">Live</span></span>
            <span id="dbUpdated">Updated {{ now()->format('g:i A') }}</span>
        </p>
    </header>

    <section class="db-card db-kpis db-in" style="--i:1" aria-label="Summary">
        <div class="db-kpi">
            <span class="db-kpi-label"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['students'] }}"/></svg>Students</span>
            <span class="db-kpi-num" data-count="totalStudents">{{ number_format($totalStudents) }}</span>
            <span class="db-kpi-note {{ $pendingStudents ? 'warn' : '' }}" data-note="pendingStudents" data-warn data-text=" waiting for approval">{{ $pendingStudents }} waiting for approval</span>
        </div>
        <div class="db-kpi">
            <span class="db-kpi-label"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['teachers'] }}"/></svg>Teachers</span>
            <span class="db-kpi-num" data-count="totalTeachers">{{ number_format($totalTeachers) }}</span>
            <span class="db-kpi-note" data-note="approvedTeachers" data-text=" with active accounts">{{ $approvedTeachers }} with active accounts</span>
        </div>
        <div class="db-kpi">
            <span class="db-kpi-label"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['parents'] }}"/></svg>Parents</span>
            <span class="db-kpi-num" data-count="totalParents">{{ number_format($totalParents) }}</span>
            <span class="db-kpi-note {{ $pendingParents ? 'warn' : '' }}" data-note="pendingParents" data-warn data-text=" waiting for approval">{{ $pendingParents }} waiting for approval</span>
        </div>
        <div class="db-kpi">
            <span class="db-kpi-label"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['activity'] }}"/></svg>Activity today</span>
            <span class="db-kpi-num" data-count="todayLogs">{{ number_format($todayLogs) }}</span>
            <span class="db-kpi-note">Audit log entries since midnight</span>
            <svg class="db-spark" id="dbSpark" viewBox="0 0 120 36" preserveAspectRatio="none" aria-hidden="true"></svg>
        </div>
    </section>

    <nav class="db-card db-att db-in" style="--i:2" aria-label="Needs attention">
        <a href="{{ route('admin.enrollment.index', ['status' => 'pending'], false) }}" class="{{ $pendingEnrollments ? 'warn' : 'ok' }}" data-att="pendingEnrollments"
           data-one="enrollment request to review" data-many="enrollment requests to review" data-none="No enrollment requests waiting">
            <span class="db-att-ic" aria-hidden="true"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></span>
            <span class="db-att-txt"><b data-count="pendingEnrollments">{{ $pendingEnrollments }}</b><span data-att-text>{{ $pendingEnrollments ? Str::plural('enrollment request', $pendingEnrollments) . ' to review' : 'No enrollment requests waiting' }}</span></span>
            <svg class="db-att-go" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('admin.sections.index', [], false) }}" class="{{ $sectionsNoAdviser ? 'warn' : 'ok' }}" data-att="sectionsNoAdviser"
           data-one="active section without an adviser" data-many="active sections without an adviser" data-none="Every active section has an adviser">
            <span class="db-att-ic" aria-hidden="true"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></span>
            <span class="db-att-txt"><b data-count="sectionsNoAdviser">{{ $sectionsNoAdviser }}</b><span data-att-text>{{ $sectionsNoAdviser ? 'active ' . Str::plural('section', $sectionsNoAdviser) . ' without an adviser' : 'Every active section has an adviser' }}</span></span>
            <svg class="db-att-go" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('admin.lrns.index', ['status' => 'available'], false) }}" data-att="availableLrns">
            <span class="db-att-ic" aria-hidden="true"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg></span>
            <span class="db-att-txt"><b data-count="availableLrns">{{ number_format($availableLrns) }}</b><span>LRNs not yet activated by students</span></span>
            <svg class="db-att-go" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
    </nav>

    <div class="db-filter db-in" style="--i:3">
        <h2>Activity</h2>
        <div class="db-tabs" role="group" aria-label="Time range for the charts">
            <button type="button" data-period="weekly" aria-pressed="true">This week</button>
            <button type="button" data-period="monthly" aria-pressed="false">This month</button>
            <button type="button" data-period="yearly" aria-pressed="false">This year</button>
        </div>
    </div>

    <div class="db-charts">
        <section class="db-card db-chart db-in" style="--i:4" aria-labelledby="dbSignTitle">
            <div class="db-chart-head">
                <div>
                    <h3 class="db-chart-title" id="dbSignTitle">Sign-ins</h3>
                    <span class="db-chart-total"><span id="dbSignTotal">0</span> <small id="dbSignRange">this week</small></span>
                </div>
                <span class="db-delta" id="dbSignDelta" data-tone="flat"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path class="up" stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M9 7h8v8"/><path class="down" stroke-linecap="round" stroke-linejoin="round" d="M7 7l10 10M17 9v8H9"/><path class="flat" stroke-linecap="round" d="M5 12h14"/></svg><span>—</span></span>
            </div>
            <div class="db-canvas"><canvas id="dbSignChart" role="img" aria-label="Sign-ins over the selected time range"></canvas><div class="db-tip" id="dbSignTip" data-open="false" aria-hidden="true"></div></div>
            <table class="sr-only" id="dbSignTable"></table>
        </section>

        <section class="db-card db-chart db-in" style="--i:5" aria-labelledby="dbRegTitle">
            <div class="db-chart-head">
                <div>
                    <h3 class="db-chart-title" id="dbRegTitle">Registrations and approvals</h3>
                    <span class="db-chart-total"><span id="dbRegTotal">0</span> <small>new accounts</small></span>
                </div>
                <span class="db-delta" id="dbRegDelta" data-tone="flat"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path class="up" stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M9 7h8v8"/><path class="down" stroke-linecap="round" stroke-linejoin="round" d="M7 7l10 10M17 9v8H9"/><path class="flat" stroke-linecap="round" d="M5 12h14"/></svg><span>—</span></span>
            </div>
            <div class="db-legend" aria-hidden="true">
                <span><i data-swatch="reg"></i>Registrations</span>
                <span><i data-swatch="apr"></i>Approvals</span>
            </div>
            <div class="db-canvas"><canvas id="dbRegChart" role="img" aria-label="Registrations and approvals over the selected time range"></canvas><div class="db-tip" id="dbRegTip" data-open="false" aria-hidden="true"></div></div>
            <table class="sr-only" id="dbRegTable"></table>
        </section>
    </div>

    <div class="db-grid">
        <section class="db-card db-in" style="--i:6;overflow:hidden;" aria-labelledby="dbPendingTitle">
            <div class="panel-header">
                <h2 class="panel-title" id="dbPendingTitle">Waiting for approval</h2>
                <a href="{{ route('admin.users.index') }}?status=pending" class="panel-link">View all
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="panel-body" id="dbPending">@include('admin.partials.dashboard-pending', ['pendingUsers' => $pendingUsers])</div>
        </section>

        <section class="db-card db-in" style="--i:7;overflow:hidden;" aria-labelledby="dbActivityTitle">
            <div class="panel-header">
                <h2 class="panel-title" id="dbActivityTitle">Recent activity</h2>
                <a href="{{ route('admin.audit-logs.index') }}" class="panel-link">Audit log
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="panel-body" id="dbActivity">@include('admin.partials.dashboard-activity', ['recentLogs' => $recentLogs])</div>
        </section>
    </div>
</div>

{{-- Chart.js (CDN) first, then the page script; the admin layout runs both again on every visit --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/admin-dashboard.js') }}?v={{ filemtime(public_path('js/admin-dashboard.js')) }}"></script>
@endsection
