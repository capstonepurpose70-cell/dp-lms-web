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

<div class="db" id="dbRoot" data-live="{{ route('admin.dashboard.live') }}">

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
            <span class="db-kpi-note {{ $pendingStudents ? 'warn' : '' }}" data-note="pendingStudents" data-text=" waiting for approval">{{ $pendingStudents }} waiting for approval</span>
        </div>
        <div class="db-kpi">
            <span class="db-kpi-label"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['teachers'] }}"/></svg>Teachers</span>
            <span class="db-kpi-num" data-count="totalTeachers">{{ number_format($totalTeachers) }}</span>
            <span class="db-kpi-note" data-note="approvedTeachers" data-text=" with active accounts">{{ $approvedTeachers }} with active accounts</span>
        </div>
        <div class="db-kpi">
            <span class="db-kpi-label"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['parents'] }}"/></svg>Parents</span>
            <span class="db-kpi-num" data-count="totalParents">{{ number_format($totalParents) }}</span>
            <span class="db-kpi-note {{ $pendingParents ? 'warn' : '' }}" data-note="pendingParents" data-text=" waiting for approval">{{ $pendingParents }} waiting for approval</span>
        </div>
        <div class="db-kpi">
            <span class="db-kpi-label"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['activity'] }}"/></svg>Activity today</span>
            <span class="db-kpi-num" data-count="todayLogs">{{ number_format($todayLogs) }}</span>
            <span class="db-kpi-note">Audit log entries since midnight</span>
        </div>
    </section>

    <nav class="db-card db-att db-in" style="--i:2" aria-label="Needs attention">
        <a href="{{ route('admin.enrollment.index', ['status' => 'pending'], false) }}" class="{{ $pendingEnrollments ? 'warn' : 'ok' }}" data-att="pendingEnrollments">
            <span class="db-att-ic" aria-hidden="true"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></span>
            <span class="db-att-txt"><b data-count="pendingEnrollments">{{ $pendingEnrollments }}</b><span data-att-text>{{ $pendingEnrollments ? Str::plural('enrollment request', $pendingEnrollments) . ' to review' : 'No enrollment requests waiting' }}</span></span>
            <svg class="db-att-go" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('admin.sections.index', [], false) }}" class="{{ $sectionsNoAdviser ? 'warn' : 'ok' }}" data-att="sectionsNoAdviser">
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
            </div>
            <div class="db-canvas"><canvas id="dbSignChart" role="img" aria-label="Sign-ins over the selected time range"></canvas></div>
            <table class="sr-only" id="dbSignTable"></table>
        </section>

        <section class="db-card db-chart db-in" style="--i:5" aria-labelledby="dbRegTitle">
            <div class="db-chart-head">
                <div>
                    <h3 class="db-chart-title" id="dbRegTitle">Registrations and approvals</h3>
                    <span class="db-chart-total"><span id="dbRegTotal">0</span> <small>new accounts</small></span>
                </div>
                <div class="db-legend" aria-hidden="true">
                    <span><i class="sw-reg"></i>Registrations</span>
                    <span><i class="sw-apr"></i>Approvals</span>
                </div>
            </div>
            <div class="db-canvas"><canvas id="dbRegChart" role="img" aria-label="Registrations and approvals over the selected time range"></canvas></div>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    const root = document.getElementById('dbRoot');
    if (!root) return;

    const POLL_MS = 20000;
    const RANGE_WORD = { weekly: 'this week', monthly: 'this month', yearly: 'this year' };
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let data = {
        weekly:  @json($chartDataWeekly),
        monthly: @json($chartDataMonthly),
        yearly:  @json($chartDataYearly),
    };
    let period = 'weekly';
    const gone = () => !document.body.contains(root);   // the admin layout swapped in another page
    const sum = (arr) => (arr || []).reduce((a, b) => a + (Number(b) || 0), 0);
    const fmt = (n) => Number(n).toLocaleString('en-US');

    /* ── Counting numbers (first load and every change) ── */
    function countTo(el, to) {
        const from = Number(String(el.textContent).replace(/[^\d-]/g, '')) || 0;
        if (from === to) return;
        if (reduceMotion) { el.textContent = fmt(to); return; }
        const start = performance.now(), dur = 700;
        const step = (now) => {
            const t = Math.min(1, (now - start) / dur), e = 1 - Math.pow(1 - t, 3);
            el.textContent = fmt(Math.round(from + (to - from) * e));
            if (t < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    }

    /* ── Theme-aware colours (validated palette: light #2563EB/#0D9488, dark #3B82F6/#0D9488) ── */
    function palette() {
        const dark = document.documentElement.getAttribute('data-theme') === 'dark';
        const css = getComputedStyle(root);
        return {
            dark,
            blue: dark ? '#3B82F6' : '#2563EB',
            teal: '#0D9488',
            grid: dark ? 'rgba(255,255,255,.07)' : '#EEF2F7',
            tick: css.getPropertyValue('--slate-500').trim() || '#64748B',
            surface: css.getPropertyValue('--surface-card').trim() || '#FFFFFF',
            ink: css.getPropertyValue('--slate-900').trim() || '#0F172A',
        };
    }
    function paintLegend(p) {
        root.querySelectorAll('.sw-reg').forEach(el => el.style.background = p.blue);
        root.querySelectorAll('.sw-apr').forEach(el => el.style.background = p.teal);
    }

    /* Accessible table view of what each chart shows */
    function writeTable(id, caption, labels, series) {
        const t = document.getElementById(id);
        if (!t) return;
        const head = '<tr><th scope="col">Period</th>' + series.map(s => `<th scope="col">${s.name}</th>`).join('') + '</tr>';
        const rows = labels.map((l, i) => `<tr><th scope="row">${l}</th>` + series.map(s => `<td>${s.values[i] ?? 0}</td>`).join('') + '</tr>').join('');
        t.innerHTML = `<caption>${caption}</caption><thead>${head}</thead><tbody>${rows}</tbody>`;
    }

    function updateTotals() {
        const d = data[period];
        countTo(document.getElementById('dbSignTotal'), sum(d.log));
        countTo(document.getElementById('dbRegTotal'), sum(d.reg));
        document.getElementById('dbSignRange').textContent = RANGE_WORD[period];
        writeTable('dbSignTable', 'Sign-ins ' + RANGE_WORD[period], d.labels, [{ name: 'Sign-ins', values: d.log }]);
        writeTable('dbRegTable', 'Registrations and approvals ' + RANGE_WORD[period], d.labels,
            [{ name: 'Registrations', values: d.reg }, { name: 'Approvals', values: d.apr }]);
    }

    /* ── Charts ── */
    let signChart = null, regChart = null;

    if (typeof Chart === 'undefined') {
        // Chart.js comes from a CDN: without internet the numbers and lists still work
        ['dbSignChart', 'dbRegChart'].forEach(id => {
            const c = document.getElementById(id);
            c.parentNode.innerHTML = '<div class="db-chart-note">The chart could not load (no internet connection). The numbers on this page are up to date.</div>';
        });
    } else {
        const p = palette();
        paintLegend(p);
        Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";

        // Vertical guide line under the tooltip on the line chart
        const crosshair = {
            id: 'dbCrosshair',
            afterDatasetsDraw(chart) {
                const a = chart.tooltip && chart.tooltip.getActiveElements();
                if (!a || !a.length) return;
                const x = a[0].element.x, { top, bottom } = chart.chartArea, ctx = chart.ctx;
                ctx.save();
                ctx.beginPath(); ctx.moveTo(x, top); ctx.lineTo(x, bottom);
                ctx.lineWidth = 1; ctx.strokeStyle = palette().tick; ctx.globalAlpha = .45; ctx.stroke();
                ctx.restore();
            },
        };

        const axes = (pp) => ({
            x: { grid: { display: false }, border: { display: false }, ticks: { color: pp.tick, maxRotation: 0, autoSkipPadding: 12, font: { size: 11.5, weight: '600' } } },
            y: { beginAtZero: true, grid: { color: pp.grid }, border: { display: false }, ticks: { color: pp.tick, precision: 0, maxTicksLimit: 5, padding: 8, font: { size: 11 } } },
        });
        const tooltip = (pp) => ({
            backgroundColor: pp.dark ? '#E2E8F0' : '#0F172A',
            titleColor: pp.dark ? '#0F172A' : '#F8FAFC',
            bodyColor: pp.dark ? '#1E293B' : '#E2E8F0',
            padding: 10, cornerRadius: 8, boxPadding: 4, usePointStyle: true,
            titleFont: { size: 12.5, weight: '700' }, bodyFont: { size: 12.5 },
            callbacks: { label: (c) => ' ' + c.dataset.label + ': ' + fmt(c.parsed.y) },
        });

        // Line draws in from the left, one point after another; bars rise with a short stagger
        const lineIn = (n) => reduceMotion ? false : {
            x: { type: 'number', easing: 'easeOutQuart', duration: 500, from: NaN, delay: (c) => c.type === 'data' && c.mode === 'default' ? c.dataIndex * (700 / Math.max(n, 1)) : 0 },
            y: { type: 'number', easing: 'easeOutQuart', duration: 500, from: (c) => c.chart.scales.y ? c.chart.scales.y.getPixelForValue(0) : 0, delay: (c) => c.type === 'data' && c.mode === 'default' ? c.dataIndex * (700 / Math.max(n, 1)) : 0 },
        };

        const d0 = data[period];
        signChart = new Chart(document.getElementById('dbSignChart'), {
            type: 'line',
            data: { labels: d0.labels, datasets: [{
                label: 'Sign-ins', data: d0.log, borderColor: p.blue, backgroundColor: p.blue + '14', fill: 'origin',
                borderWidth: 2, cubicInterpolationMode: 'monotone', pointRadius: 0, pointHoverRadius: 5,
                pointHoverBackgroundColor: p.blue, pointHoverBorderColor: p.surface, pointHoverBorderWidth: 2,
            }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                animations: lineIn(d0.labels.length),
                animation: reduceMotion ? false : { duration: 500 },
                plugins: { legend: { display: false }, tooltip: tooltip(p) },
                scales: axes(p),
            },
            plugins: [crosshair],
        });

        regChart = new Chart(document.getElementById('dbRegChart'), {
            type: 'bar',
            data: { labels: d0.labels, datasets: [
                { label: 'Registrations', data: d0.reg, backgroundColor: p.blue, borderRadius: { topLeft: 4, topRight: 4 }, borderSkipped: 'bottom', maxBarThickness: 18 },
                { label: 'Approvals', data: d0.apr, backgroundColor: p.teal, borderRadius: { topLeft: 4, topRight: 4 }, borderSkipped: 'bottom', maxBarThickness: 18 },
            ] },
            options: {
                responsive: true, maintainAspectRatio: false,
                datasets: { bar: { categoryPercentage: .62, barPercentage: .86 } },
                interaction: { mode: 'index', intersect: false },
                animation: reduceMotion ? false : {
                    duration: 600, easing: 'easeOutQuart',
                    delay: (c) => c.type === 'data' && c.mode === 'default' ? c.dataIndex * 35 + c.datasetIndex * 80 : 0,
                },
                plugins: { legend: { display: false }, tooltip: tooltip(p) },
                scales: axes(p),
            },
        });

        // Dark / light switch: recolour without rebuilding
        const themeWatch = new MutationObserver(() => {
            if (gone()) { themeWatch.disconnect(); return; }
            const q = palette();
            paintLegend(q);
            const s = signChart.data.datasets[0];
            s.borderColor = q.blue; s.backgroundColor = q.blue + '14'; s.pointHoverBackgroundColor = q.blue; s.pointHoverBorderColor = q.surface;
            regChart.data.datasets[0].backgroundColor = q.blue;
            regChart.data.datasets[1].backgroundColor = q.teal;
            [signChart, regChart].forEach(ch => { ch.options.scales = axes(q); ch.options.plugins.tooltip = tooltip(q); ch.update('none'); });
        });
        themeWatch.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
    }

    function showPeriod(next, animate) {
        period = next;
        const d = data[period];
        if (signChart) {
            const labelsChanged = JSON.stringify(signChart.data.labels) !== JSON.stringify(d.labels);
            signChart.data.labels = d.labels; signChart.data.datasets[0].data = d.log;
            regChart.data.labels = d.labels; regChart.data.datasets[0].data = d.reg; regChart.data.datasets[1].data = d.apr;
            // New range = draw in again; same range with new numbers = values glide to their new height
            if (labelsChanged && animate) { signChart.reset(); regChart.reset(); }
            signChart.update(); regChart.update();
        }
        updateTotals();
    }

    root.querySelector('.db-tabs').addEventListener('click', (e) => {
        const b = e.target.closest('button[data-period]');
        if (!b || b.dataset.period === period) return;
        root.querySelectorAll('.db-tabs button').forEach(x => x.setAttribute('aria-pressed', x === b ? 'true' : 'false'));
        showPeriod(b.dataset.period, true);
    });

    /* ── Live refresh ── */
    const live = document.getElementById('dbLive');
    const liveLabel = document.getElementById('dbLiveLabel');
    const updated = document.getElementById('dbUpdated');
    let timer = null, busy = false;

    function setText(el, text) { if (el && el.textContent !== text) el.textContent = text; }
    function swapList(id, html) {
        const el = document.getElementById(id);
        if (!el || el.innerHTML.trim() === html.trim()) return;
        // Never pull a button out from under the admin's cursor or keyboard focus
        if (el.contains(document.activeElement) || el.matches(':hover')) return;
        el.innerHTML = html;
        el.classList.remove('db-flash'); void el.offsetWidth; el.classList.add('db-flash');
    }

    function apply(json) {
        const c = json.counts || {};
        root.querySelectorAll('[data-count]').forEach(el => { if (c[el.dataset.count] !== undefined) countTo(el, Number(c[el.dataset.count])); });
        root.querySelectorAll('[data-note]').forEach(el => {
            const n = Number(c[el.dataset.note] ?? 0);
            setText(el, n + el.dataset.text);
            if (el.dataset.note !== 'approvedTeachers') el.classList.toggle('warn', n > 0);
        });
        const att = (key, yes, no) => {
            const a = root.querySelector(`[data-att="${key}"]`), n = Number(c[key] ?? 0);
            if (!a) return;
            a.classList.toggle('warn', n > 0); a.classList.toggle('ok', n === 0);
            setText(a.querySelector('[data-att-text]'), n > 0 ? yes(n) : no);
        };
        att('pendingEnrollments', n => (n === 1 ? 'enrollment request' : 'enrollment requests') + ' to review', 'No enrollment requests waiting');
        att('sectionsNoAdviser', n => 'active ' + (n === 1 ? 'section' : 'sections') + ' without an adviser', 'Every active section has an adviser');

        if (json.charts) { data = json.charts; showPeriod(period, false); }
        if (typeof json.pendingHtml === 'string') swapList('dbPending', json.pendingHtml);
        if (typeof json.activityHtml === 'string') swapList('dbActivity', json.activityHtml);
        setText(updated, 'Updated ' + new Date(json.updatedAt || Date.now()).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }));
    }

    function refresh() {
        if (gone()) { clearInterval(timer); return; }
        if (document.hidden || busy) return;
        busy = true;
        live.classList.add('updating');
        fetch(root.dataset.live, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(r => {
                if (r.status === 401 || r.status === 419 || (r.redirected && !r.url.includes('/admin/dashboard'))) { window.location.href = '/admin/login'; throw new Error('signed out'); }
                if (!r.ok) throw new Error('http ' + r.status);
                return r.json();
            })
            .then(json => { if (!gone()) { apply(json); live.classList.remove('paused'); setText(liveLabel, 'Live'); } })
            .catch(() => { live.classList.add('paused'); setText(liveLabel, 'Reconnecting'); })
            .finally(() => { busy = false; live.classList.remove('updating'); });
    }

    // First paint: numbers count up from zero, charts draw in
    root.querySelectorAll('.db-kpi-num[data-count]').forEach(el => { const to = Number(el.textContent.replace(/[^\d-]/g, '')) || 0; el.textContent = '0'; countTo(el, to); });
    updateTotals();

    timer = setInterval(refresh, POLL_MS);
    document.addEventListener('visibilitychange', () => { if (!document.hidden && !gone()) refresh(); });
})();
</script>
@endsection
