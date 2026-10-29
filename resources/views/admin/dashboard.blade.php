@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')

<style>
    .db { max-width: 1280px; margin: 0 auto; --db-line: var(--border-default, #E2E8F0); --db-brand: #1D4ED8; }
    [data-theme="dark"] .db { --db-brand: #1E40AF; }
    .db .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
    .db-card { background: var(--surface-card); border: 1px solid var(--db-line); border-radius: 16px; }
    .db-num { font-variant-numeric: tabular-nums; }

    /* Header */
    .db-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 10px 16px; margin-bottom: 20px; }
    .db-title { font-size: 26px; font-weight: 800; color: var(--slate-900); letter-spacing: -.02em; line-height: 1.2; }
    .db-sub { font-size: 14px; color: var(--slate-600); margin-top: 4px; text-wrap: pretty; }
    .db-live { display: inline-flex; align-items: center; gap: 10px; font-size: 13px; color: var(--slate-600); }
    .db-live-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: var(--success-light, #DCFCE7); color: #166534; font-weight: 700; }
    .db-live-pill i { width: 7px; height: 7px; border-radius: 50%; background: #16A34A; }
    .db-live.paused .db-live-pill { background: var(--slate-100); color: var(--slate-600); }
    .db-live.paused .db-live-pill i { background: var(--slate-400); }
    .db-live.updating .db-live-pill i { animation: db-blink 1s ease-in-out infinite; }
    @keyframes db-blink { 50% { opacity: .25; } }

    /* Change chip: arrow + words */
    .db-delta { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px 3px 7px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap; background: var(--slate-100); color: var(--slate-600); font-variant-numeric: tabular-nums; }
    .db-delta svg { width: 13px; height: 13px; flex-shrink: 0; }
    .db-delta svg path { display: none; }
    .db-delta[data-tone="up"] .up, .db-delta[data-tone="down"] .down, .db-delta[data-tone="flat"] .flat { display: block; }
    .db-delta[data-tone="up"] { background: #DCFCE7; color: #166534; }
    .db-delta[data-tone="down"] { background: #FEE2E2; color: #991B1B; }
    [data-theme="dark"] .db-delta[data-tone="up"] { background: rgba(22,163,74,.18); color: #86EFAC; }
    [data-theme="dark"] .db-delta[data-tone="down"] { background: rgba(220,38,38,.18); color: #FCA5A5; }

    /* KPI row: one brand card for what the chart shows, then the account totals */
    .db-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 14px; }
    .db-kpi { padding: 18px 20px 14px; display: flex; flex-direction: column; min-width: 0; }
    .db-kpi-line { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 10px; margin: 10px 0 14px; }
    .db-kpi-line .db-kpi-big { margin: 0; }
    .db-kpi-label { display: flex; align-items: center; gap: 8px; font-size: 13.5px; font-weight: 600; color: var(--slate-600); }
    .db-kpi-label svg { width: 17px; height: 17px; color: var(--slate-400); flex-shrink: 0; }
    .db-kpi-big { display: block; margin: 10px 0 14px; font-size: 32px; font-weight: 800; color: var(--slate-900); line-height: 1; letter-spacing: -.025em; }
    .db-rows { margin-top: auto; }
    .db-rows div { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; padding: 7px 0; border-top: 1px solid var(--db-line); font-size: 13px; }
    .db-rows dt { color: var(--slate-500); }
    .db-rows dd { margin: 0; font-weight: 700; color: var(--slate-800); font-variant-numeric: tabular-nums; }
    .db-rows dd.warn { color: #B45309; }
    [data-theme="dark"] .db-rows dd.warn { color: #FBBF24; }

    .db-hero { background: var(--db-brand); border-color: var(--db-brand); color: #fff; }
    .db-hero .db-kpi-label, .db-hero .db-kpi-label svg { color: rgba(255,255,255,.82); }
    .db-hero .db-kpi-big { color: #fff; }
    .db-hero .db-rows div { border-top-color: rgba(255,255,255,.18); }
    .db-hero .db-rows dt { color: rgba(255,255,255,.75); }
    .db-hero .db-rows dd { color: #fff; }
    .db-hero .db-delta, .db-hero .db-delta[data-tone] { background: rgba(255,255,255,.16); color: #fff; }

    /* Needs attention */
    .db-att { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); margin-bottom: 14px; overflow: hidden; }
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

    /* Activity chart */
    .db-chart { padding: 20px 22px 16px; margin-bottom: 14px; }
    .db-chart-head { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 12px 16px; }
    .db-chart-title { font-size: 16px; font-weight: 800; color: var(--slate-900); }
    .db-chart-sub { font-size: 13px; color: var(--slate-500); margin-top: 2px; }
    .db-controls { display: flex; flex-wrap: wrap; gap: 8px; }
    .db-tabs { display: inline-flex; padding: 3px; border-radius: 10px; background: var(--slate-100); }
    .db-tabs button { min-height: 32px; padding: 0 12px; border-radius: 7px; border: 0; background: transparent; font: inherit; font-size: 13px; font-weight: 700; color: var(--slate-600); cursor: pointer; transition: background-color .15s ease, color .15s ease, box-shadow .15s ease; }
    .db-tabs button:hover { color: var(--slate-900); }
    .db-tabs button[aria-pressed="true"] { background: var(--surface-card); color: var(--slate-900); box-shadow: 0 1px 2px rgba(15,23,42,.10), 0 0 0 1px rgba(15,23,42,.04); }
    .db-tabs button:focus-visible { outline: 2px solid #2563EB; outline-offset: 1px; }

    .db-summary { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 10px 20px; margin: 18px 0 10px; }
    .db-total { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 12px; }
    .db-total-num { font-size: 30px; font-weight: 800; color: var(--slate-900); letter-spacing: -.025em; line-height: 1; }
    .db-total-word { font-size: 13.5px; font-weight: 600; color: var(--slate-500); }
    .db-legend { display: flex; flex-wrap: wrap; gap: 6px 16px; font-size: 12.5px; font-weight: 600; color: var(--slate-600); }
    .db-legend span { display: inline-flex; align-items: center; gap: 7px; }
    .db-legend b { color: var(--slate-900); font-variant-numeric: tabular-nums; }
    .db-key-bar { width: 10px; height: 10px; border-radius: 3px; background: #2563EB; }
    .db-key-dash { width: 14px; height: 2px; border-radius: 2px; background: #0F172A; }
    .db-key-soon { width: 10px; height: 10px; border-radius: 3px; background: repeating-linear-gradient(135deg, #DCE3EE 0 1.5px, #F1F4F9 1.5px 4px); }
    [data-theme="dark"] .db-key-bar { background: #3B82F6; }
    [data-theme="dark"] .db-key-dash { background: #E2E8F0; }
    [data-theme="dark"] .db-key-soon { background: repeating-linear-gradient(135deg, rgba(148,163,184,.3) 0 1.5px, rgba(148,163,184,.08) 1.5px 4px); }

    .db-canvas { position: relative; height: 300px; }
    .db-chart-note { display: grid; place-items: center; height: 100%; text-align: center; padding: 0 16px; font-size: 13px; color: var(--slate-500); }
    .db-tip { position: absolute; left: 0; top: 0; z-index: 5; min-width: 168px; padding: 10px 12px; border-radius: 12px; pointer-events: none;
              background: var(--surface-card); border: 1px solid var(--db-line); box-shadow: 0 12px 32px -10px rgba(15,23,42,.28), 0 2px 6px rgba(15,23,42,.06);
              font-size: 12.5px; color: var(--slate-600); opacity: 0; transition: opacity .12s ease, transform .16s cubic-bezier(.2,.7,.2,1); }
    .db-tip[data-open="true"] { opacity: 1; }
    .db-tip-title { font-weight: 700; color: var(--slate-900); margin-bottom: 7px; }
    .db-tip-row { display: flex; align-items: center; gap: 8px; }
    .db-tip-row + .db-tip-row { margin-top: 4px; }
    .db-tip-row i { width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0; }
    .db-tip-row i.dash { width: 10px; height: 2px; }
    .db-tip-row b { margin-left: auto; padding-left: 16px; color: var(--slate-900); font-variant-numeric: tabular-nums; }
    .db-tip-chip { display: inline-block; margin-top: 8px; padding: 2px 8px; border-radius: 999px; font-size: 11.5px; font-weight: 700; background: var(--slate-100); color: var(--slate-600); }
    .db-tip-chip[data-tone="up"] { background: #DCFCE7; color: #166534; }
    .db-tip-chip[data-tone="down"] { background: #FEE2E2; color: #991B1B; }
    [data-theme="dark"] .db-tip-chip[data-tone="up"] { background: rgba(22,163,74,.18); color: #86EFAC; }
    [data-theme="dark"] .db-tip-chip[data-tone="down"] { background: rgba(220,38,38,.18); color: #FCA5A5; }

    /* Lists */
    .db-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
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
    .db-in { animation: db-rise .45s cubic-bezier(.2,.7,.2,1) both; animation-delay: calc(var(--i, 0) * 50ms); }
    @keyframes db-rise { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
    .db-flash { animation: db-flash 1.2s ease; }
    @keyframes db-flash { 0% { background: rgba(37,99,235,.10); } 100% { background: transparent; } }

    @media (max-width: 1100px) { .db-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 900px) {
        .db-att { grid-template-columns: 1fr; }
        .db-att a + a { border-left: 0; border-top: 1px solid var(--db-line); }
        .db-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 560px) {
        .db-kpis { grid-template-columns: 1fr; gap: 10px; }
        .db-kpi { padding: 14px 16px 8px; }
        .db-kpi-big { font-size: 26px; margin: 8px 0 10px; }
        .db-kpi-line { margin: 8px 0 10px; }
        .db-chart { padding: 16px 14px 12px; }
        .db-controls, .db-tabs { width: 100%; }
        .db-tabs button { flex: 1; padding: 0 6px; }
        .db-canvas { height: 240px; }
        .pending-row { flex-wrap: wrap; }
        .pending-actions { width: 100%; justify-content: flex-end; }
    }
    @media (prefers-reduced-motion: reduce) {
        .db-in, .db-flash, .db-live.updating .db-live-pill i { animation: none !important; }
        .db-tip { transition: none; }
    }
</style>

@php
    $icons = [
        'signin'   => 'M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3',
        'students' => 'M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l-9-5m9 5l9-5',
        'teachers' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87a4 4 0 100-8 4 4 0 000 8zm6-12a3 3 0 11-6 0 3 3 0 016 0zM6 8a3 3 0 11-6 0 3 3 0 016 0z',
        'parents'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    ];
    $week = $chartDataWeekly;
    $signToday = $week['current'] >= 0 ? ($week['log'][$week['current']] ?? 0) : 0;
    $delta = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path class="up" stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M9 7h8v8"/><path class="down" stroke-linecap="round" stroke-linejoin="round" d="M7 7l10 10M17 9v8H9"/><path class="flat" stroke-linecap="round" d="M5 12h14"/></svg><span>—</span>';
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

    <section class="db-kpis" aria-label="Summary">
        <article class="db-card db-kpi db-hero db-in" style="--i:1">
            <span class="db-kpi-label"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['signin'] }}"/></svg>Sign-ins today</span>
            <div class="db-kpi-line">
                <span class="db-kpi-big db-num" id="dbSignToday">{{ number_format($signToday) }}</span>
                <span class="db-delta" id="dbSignDelta" data-tone="flat">{!! $delta !!}</span>
            </div>
            <dl class="db-rows">
                <div><dt>This week</dt><dd class="db-num" id="dbSignWeek">{{ number_format(array_sum($week['log'])) }}</dd></div>
                <div><dt>This month</dt><dd class="db-num" id="dbSignMonth">{{ number_format(array_sum($chartDataMonthly['log'])) }}</dd></div>
            </dl>
        </article>

        <article class="db-card db-kpi db-in" style="--i:2">
            <span class="db-kpi-label"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['students'] }}"/></svg>Students</span>
            <span class="db-kpi-big db-num" data-count="totalStudents">{{ number_format($totalStudents) }}</span>
            <dl class="db-rows">
                <div><dt>Approved</dt><dd class="db-num" data-count="approvedStudents">{{ number_format($approvedStudents) }}</dd></div>
                <div><dt>Waiting for approval</dt><dd class="db-num {{ $pendingStudents ? 'warn' : '' }}" data-count="pendingStudents" data-warn="pendingStudents">{{ number_format($pendingStudents) }}</dd></div>
            </dl>
        </article>

        <article class="db-card db-kpi db-in" style="--i:3">
            <span class="db-kpi-label"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['teachers'] }}"/></svg>Teachers</span>
            <span class="db-kpi-big db-num" data-count="totalTeachers">{{ number_format($totalTeachers) }}</span>
            <dl class="db-rows">
                <div><dt>Active accounts</dt><dd class="db-num" data-count="approvedTeachers">{{ number_format($approvedTeachers) }}</dd></div>
                <div><dt>Not active yet</dt><dd class="db-num" data-diff="totalTeachers-approvedTeachers">{{ number_format(max(0, $totalTeachers - $approvedTeachers)) }}</dd></div>
            </dl>
        </article>

        <article class="db-card db-kpi db-in" style="--i:4">
            <span class="db-kpi-label"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['parents'] }}"/></svg>Parents</span>
            <span class="db-kpi-big db-num" data-count="totalParents">{{ number_format($totalParents) }}</span>
            <dl class="db-rows">
                <div><dt>Approved</dt><dd class="db-num" data-count="approvedParents">{{ number_format($approvedParents) }}</dd></div>
                <div><dt>Waiting for approval</dt><dd class="db-num {{ $pendingParents ? 'warn' : '' }}" data-count="pendingParents" data-warn="pendingParents">{{ number_format($pendingParents) }}</dd></div>
            </dl>
        </article>
    </section>

    <nav class="db-card db-att db-in" style="--i:5" aria-label="Needs attention">
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

    <section class="db-card db-chart db-in" style="--i:6" aria-labelledby="dbChartTitle">
        <div class="db-chart-head">
            <div>
                <h2 class="db-chart-title" id="dbChartTitle">Activity</h2>
                <p class="db-chart-sub">Compared with the same point in the period before.</p>
            </div>
            <div class="db-controls">
                <div class="db-tabs" id="dbMetric" role="group" aria-label="What the chart shows">
                    <button type="button" data-value="log" aria-pressed="true">Sign-ins</button>
                    <button type="button" data-value="reg" aria-pressed="false">Registrations</button>
                    <button type="button" data-value="apr" aria-pressed="false">Approvals</button>
                </div>
                <div class="db-tabs" id="dbRange" role="group" aria-label="Time range">
                    <button type="button" data-value="weekly" aria-pressed="true">Week</button>
                    <button type="button" data-value="monthly" aria-pressed="false">Month</button>
                    <button type="button" data-value="yearly" aria-pressed="false">Year</button>
                </div>
            </div>
        </div>

        <div class="db-summary">
            <div class="db-total">
                <span class="db-total-num db-num" id="dbTotal">0</span>
                <span class="db-total-word" id="dbTotalWord">sign-ins this week</span>
                <span class="db-delta" id="dbDelta" data-tone="flat">{!! $delta !!}</span>
            </div>
            <div class="db-legend" aria-hidden="true">
                <span><i class="db-key-bar"></i><span id="dbNowLabel">This week</span></span>
                <span><i class="db-key-dash"></i><span id="dbThenLabel">Last week</span> <b id="dbThenValue">0</b></span>
                <span><i class="db-key-soon"></i>Still to come</span>
            </div>
        </div>

        <div class="db-canvas">
            <canvas id="dbChart" role="img" aria-label="Sign-ins per day, this week compared with last week"></canvas>
            <div class="db-tip" id="dbTip" data-open="false" aria-hidden="true"></div>
        </div>
        <table class="sr-only" id="dbTable"></table>
    </section>

    <div class="db-grid">
        <section class="db-card db-in" style="--i:7;overflow:hidden;" aria-labelledby="dbPendingTitle">
            <div class="panel-header">
                <h2 class="panel-title" id="dbPendingTitle">Waiting for approval</h2>
                <a href="{{ route('admin.users.index') }}?status=pending" class="panel-link">View all
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="panel-body" id="dbPending">@include('admin.partials.dashboard-pending', ['pendingUsers' => $pendingUsers])</div>
        </section>

        <section class="db-card db-in" style="--i:8;overflow:hidden;" aria-labelledby="dbActivityTitle">
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
