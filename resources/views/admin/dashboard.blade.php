@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')

<style>
    .db-page { max-width: 1280px; margin: 0 auto; }

    /* Page header */
    .db-page-header {
        display: flex; align-items: flex-end; justify-content: space-between;
        margin-bottom: 1.75rem; gap: 1rem; flex-wrap: wrap;
    }
    .db-page-title {
        font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 700;
        color: var(--slate-900); line-height: 1.2; letter-spacing: -0.02em;
    }
    .db-page-sub { font-size: 13px; color: var(--slate-500); margin-top: 4px; }
    .db-date-badge {
        display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600;
        color: var(--slate-500); background: var(--slate-100); border: 1px solid var(--border-default);
        padding: 6px 14px; border-radius: var(--r-full); white-space: nowrap;
    }
    .db-date-badge svg { width: 14px; height: 14px; color: var(--blue-500); }

    /* Clickable date + calendar popup */
    .db-date-wrap { position: relative; }
    .db-date-badge { cursor: pointer; transition: background .18s, border-color .18s; }
    .db-date-badge:hover { background: var(--slate-200, #e2e8f0); }
    .db-date-caret { width: 12px !important; height: 12px !important; color: var(--slate-400) !important; transition: transform .2s; }
    .db-date-badge[aria-expanded="true"] .db-date-caret { transform: rotate(180deg); }

    .db-cal {
        position: absolute; top: calc(100% + 8px); right: 0; z-index: 60;
        width: 270px; background: var(--white, #fff);
        border: 1px solid var(--border-default); border-radius: var(--r-lg, 14px);
        box-shadow: var(--shadow-lg); padding: 14px; animation: dbCalIn .16s ease;
    }
    .db-cal[hidden] { display: none; }
    @keyframes dbCalIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
    .db-cal-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .db-cal-title { font-family: 'Outfit', sans-serif; font-size: 13.5px; font-weight: 700; color: var(--slate-900); }
    .db-cal-nav { width: 28px; height: 28px; border: none; background: var(--slate-100); border-radius: 8px;
        cursor: pointer; color: var(--slate-600); font-size: 17px; line-height: 1;
        display: flex; align-items: center; justify-content: center; }
    .db-cal-nav:hover { background: var(--slate-200, #e2e8f0); color: var(--slate-900); }
    .db-cal-dow { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; margin-bottom: 4px; }
    .db-cal-dow span { text-align: center; font-size: 10px; font-weight: 700; color: var(--slate-400); text-transform: uppercase; letter-spacing: .03em; }
    .db-cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
    .db-cal-cell { height: 32px; display: flex; align-items: center; justify-content: center; font-size: 12.5px; color: var(--slate-700); border-radius: 8px; }
    .db-cal-cell.muted { color: var(--slate-300); }
    .db-cal-cell.today { background: var(--blue-600, #2563eb); color: #fff; font-weight: 700; }
    .db-cal-today { margin-top: 10px; width: 100%; border: 1px solid var(--border-default); background: var(--white, #fff);
        color: var(--blue-600, #2563eb); font-weight: 600; font-size: 12px; padding: 7px; border-radius: 9px; cursor: pointer; transition: background .18s; }
    .db-cal-today:hover { background: var(--blue-50, #eff6ff); }

    /* Stat cards */
    .stat-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem; margin-bottom: 1.5rem;
    }
    .stat-card {
        background: var(--white); border: 1px solid var(--border-default);
        border-radius: var(--r-lg); padding: 1.25rem 1.375rem;
        position: relative; overflow: hidden;
        transition: box-shadow 0.18s var(--ease-out), transform 0.18s var(--ease-out);
    }
    .stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .stat-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0;
        height: 3px; border-radius: var(--r-lg) var(--r-lg) 0 0;
    }
    .stat-card.blue::before   { background: var(--blue-500); }
    .stat-card.teal::before   { background: #1D9E75; }
    .stat-card.amber::before  { background: #BA7517; }
    .stat-card.purple::before { background: #534AB7; }

    .stat-icon {
        width: 40px; height: 40px; border-radius: var(--r-md);
        display: flex; align-items: center; justify-content: center; margin-bottom: 0.875rem;
    }
    .stat-icon svg { width: 20px; height: 20px; }
    .stat-icon.blue   { background: var(--blue-50); color: var(--blue-600); }
    .stat-icon.teal   { background: #E1F5EE; color: #0F6E56; }
    .stat-icon.amber  { background: #FAEEDA; color: #854F0B; }
    .stat-icon.purple { background: #EEEDFE; color: #534AB7; }

    .stat-label { font-size: 11px; font-weight: 700; letter-spacing: 0.07em; text-transform: uppercase; color: var(--slate-400); margin-bottom: 4px; }
    .stat-value { font-family: 'Outfit', sans-serif; font-size: 32px; font-weight: 700; color: var(--slate-900); line-height: 1; letter-spacing: -0.03em; }
    .stat-meta { font-size: 12px; font-weight: 500; margin-top: 6px; display: flex; align-items: center; gap: 4px; }
    .stat-meta.blue   { color: var(--blue-600); }
    .stat-meta.teal   { color: #0F6E56; }
    .stat-meta.amber  { color: #854F0B; }
    .stat-meta.purple { color: #534AB7; }
    .stat-meta svg { width: 13px; height: 13px; }

    /* Chart panel */
    .chart-panel {
        background: var(--white); border: 1px solid var(--border-default);
        border-radius: var(--r-lg); overflow: hidden; margin-bottom: 1.25rem;
    }
    .chart-panel-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1.125rem 1.5rem; border-bottom: 1px solid var(--border-default);
        background: var(--slate-50); flex-wrap: wrap; gap: 0.75rem;
    }
    .chart-panel-title {
        font-family: 'Outfit', sans-serif; font-size: 14.5px; font-weight: 700;
        color: var(--slate-800); display: flex; align-items: center; gap: 8px;
    }
    .chart-panel-title svg { width: 17px; height: 17px; color: var(--blue-500); }

    /* Period tabs */
    .period-tabs {
        display: flex; background: var(--slate-100); border: 1px solid var(--border-default);
        border-radius: var(--r-md); padding: 3px; gap: 2px;
    }
    .period-tab {
        font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; font-weight: 600;
        color: var(--slate-500); padding: 5px 14px; border-radius: var(--r-sm);
        border: none; background: transparent; cursor: pointer; transition: all 0.18s var(--ease-out);
    }
    .period-tab.active { background: var(--white); color: var(--blue-700); box-shadow: 0 1px 4px rgba(0,0,0,0.10); }
    .period-tab:hover:not(.active) { color: var(--slate-700); background: rgba(255,255,255,0.6); }

    /* Chart legend */
    .chart-legend {
        display: flex; align-items: center; gap: 1.25rem;
        padding: 0.75rem 1.5rem; border-bottom: 1px solid var(--border-default);
    }
    .legend-item { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: var(--slate-500); }
    .legend-swatch { width: 28px; height: 3px; border-radius: 2px; }
    .legend-swatch.reg   { background: #2478e4; }
    .legend-swatch.apr   { background: #1D9E75; }
    .legend-swatch.log   { background: #534AB7; }

    .chart-body { padding: 1.25rem 1.5rem 1rem; }
    .chart-canvas-wrap { position: relative; height: 260px; }

    /* Bottom grid */
    .db-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
    @media (max-width: 900px) { .db-grid { grid-template-columns: 1fr; } }

    /* Panel */
    .panel { background: var(--white); border: 1px solid var(--border-default); border-radius: var(--r-lg); overflow: hidden; }
    .panel-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.375rem; border-bottom: 1px solid var(--border-default); background: var(--slate-50);
    }
    .panel-title { font-family: 'Outfit', sans-serif; font-size: 13.5px; font-weight: 700; color: var(--slate-800); display: flex; align-items: center; gap: 8px; }
    .panel-title svg { width: 16px; height: 16px; color: var(--blue-500); }
    .panel-link { font-size: 12px; font-weight: 600; color: var(--blue-600); text-decoration: none; display: flex; align-items: center; gap: 3px; transition: color 0.15s; }
    .panel-link:hover { color: var(--blue-800); }
    .panel-link svg { width: 13px; height: 13px; }
    .panel-body { padding: 0 1.375rem; }
    .panel-empty { padding: 2.5rem 1.375rem; text-align: center; color: var(--slate-400); font-size: 13px; }

    /* Pending row */
    .pending-row { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.875rem 0; border-bottom: 1px solid var(--slate-100); }
    .pending-row:last-child { border-bottom: none; }
    .pending-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--blue-400), var(--blue-700)); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; letter-spacing: 0.03em; }
    .pending-info { flex: 1; min-width: 0; }
    .pending-name { font-size: 13.5px; font-weight: 600; color: var(--slate-800); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pending-email { font-size: 11.5px; color: var(--slate-400); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pending-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }

    .role-pill { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 3px 9px; border-radius: var(--r-full); }
    .role-pill.student { background: #E1F5EE; color: #0F6E56; }
    .role-pill.parent  { background: #FAEEDA; color: #854F0B; }
    .role-pill.teacher { background: var(--blue-50); color: var(--blue-800); }

    .action-btn { display: inline-flex; align-items: center; gap: 4px; font-size: 11.5px; font-weight: 600; padding: 5px 11px; border-radius: var(--r-sm); border: none; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif; transition: all 0.15s var(--ease-out); }
    .action-btn svg { width: 12px; height: 12px; }
    .action-btn.approve { background: #EAF3DE; color: #3B6D11; }
    .action-btn.approve:hover { background: #C0DD97; transform: translateY(-1px); }
    .action-btn.reject  { background: #FCEBEB; color: #A32D2D; }
    .action-btn.reject:hover  { background: #F7C1C1; transform: translateY(-1px); }

    /* Log row */
    .log-row { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.875rem 0; border-bottom: 1px solid var(--slate-100); }
    .log-row:last-child { border-bottom: none; }
    .log-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--blue-400); flex-shrink: 0; margin-top: 5px; }
    .log-body { flex: 1; min-width: 0; }
    .log-action { font-size: 13px; font-weight: 600; color: var(--slate-800); line-height: 1.4; }
    .log-meta { font-size: 11.5px; color: var(--slate-400); margin-top: 2px; }
    .log-module { display: inline-flex; align-items: center; background: var(--blue-50); color: var(--blue-700); font-size: 10px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; padding: 2px 7px; border-radius: var(--r-sm); margin-left: 4px; }
    .log-time { font-size: 11px; color: var(--slate-400); white-space: nowrap; flex-shrink: 0; }
</style>

<div class="db-page">

    {{-- Page Header --}}
    <div class="db-page-header">
        <div>
            <div class="db-page-title">Dashboard</div>
            <div class="db-page-sub">Welcome back — here's what's happening in DP-LMS today.</div>
        </div>
        <div class="db-date-wrap">
            <button type="button" class="db-date-badge" id="dateBadge" aria-haspopup="true" aria-expanded="false">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span id="dashboardDate">—</span>
                <svg class="db-date-caret" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="db-cal" id="dateCalendar" hidden>
                <div class="db-cal-head">
                    <button type="button" class="db-cal-nav" id="calPrev" aria-label="Previous month">&lsaquo;</button>
                    <span class="db-cal-title" id="calTitle">—</span>
                    <button type="button" class="db-cal-nav" id="calNext" aria-label="Next month">&rsaquo;</button>
                </div>
                <div class="db-cal-dow"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div>
                <div class="db-cal-grid" id="calGrid"></div>
                <button type="button" class="db-cal-today" id="calToday">Jump to today</button>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="stat-grid">
        <div class="stat-card blue">
            <div class="stat-icon blue">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l-9-5m9 5l9-5"/></svg>
            </div>
            <div class="stat-label">Total Students</div>
            <div class="stat-value">{{ $totalStudents }}</div>
            <div class="stat-meta blue">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $pendingStudents }} pending approval
            </div>
        </div>
        <div class="stat-card teal">
            <div class="stat-icon teal">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87a4 4 0 100-8 4 4 0 000 8zm6-12a3 3 0 11-6 0 3 3 0 016 0zM6 8a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="stat-label">Total Teachers</div>
            <div class="stat-value">{{ $totalTeachers }}</div>
            <div class="stat-meta teal">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Active accounts
            </div>
        </div>
        <div class="stat-card amber">
            <div class="stat-icon amber">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </div>
            <div class="stat-label">Total Parents</div>
            <div class="stat-value">{{ $totalParents }}</div>
            <div class="stat-meta amber">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $pendingParents }} pending approval
            </div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon purple">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div class="stat-label">Today's Activity</div>
            <div class="stat-value">{{ $todayLogs }}</div>
            <div class="stat-meta purple">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Audit log entries
            </div>
        </div>
    </div>

    {{-- Activity Chart Panel --}}
    <div class="chart-panel">
        <div class="chart-panel-header">
            <div class="chart-panel-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                </svg>
                System Activity Overview
            </div>
            <div class="period-tabs" role="group" aria-label="Chart period">
                <button class="period-tab active" data-period="weekly"  aria-pressed="true">Weekly</button>
                <button class="period-tab"         data-period="monthly" aria-pressed="false">Monthly</button>
                <button class="period-tab"         data-period="yearly"  aria-pressed="false">Yearly</button>
            </div>
        </div>

        <div class="chart-legend">
            <div class="legend-item"><span class="legend-swatch reg"></span> Registrations</div>
            <div class="legend-item"><span class="legend-swatch apr"></span> Approvals</div>
            <div class="legend-item"><span class="legend-swatch log"></span> Logins</div>
        </div>

        <div class="chart-body">
            <div class="chart-canvas-wrap">
                <canvas id="activityChart" aria-label="System activity area chart" role="img"></canvas>
            </div>
        </div>
    </div>

    {{-- Bottom Grid --}}
    <div class="db-grid">

        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Pending Registrations
                </div>
                <a href="{{ route('admin.users.index') }}?status=pending" class="panel-link">
                    View all
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="panel-body">
                @forelse($pendingUsers as $user)
                <div class="pending-row">
                    <div class="pending-avatar">
                        {{ collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0]))->take(2)->join('') }}
                    </div>
                    <div class="pending-info">
                        <div class="pending-name">{{ $user->name }}</div>
                        <div class="pending-email">{{ $user->email }}</div>
                    </div>
                    <div class="pending-actions">
                        <span class="role-pill {{ $user->role }}">{{ ucfirst($user->role) }}</span>
                        <form method="POST" action="{{ route('admin.users.approve', $user) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit" class="action-btn approve">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Approve
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.reject', $user) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit" class="action-btn reject">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                Reject
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="panel-empty">
                    <svg style="width:36px;height:36px;color:var(--slate-300);margin:0 auto 8px;display:block;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    No pending registrations.
                </div>
                @endforelse
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Recent Activity
                </div>
                <a href="{{ route('admin.audit-logs.index') }}" class="panel-link">
                    View all
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="panel-body">
                @forelse($recentLogs as $log)
                <div class="log-row">
                    <div class="log-dot"></div>
                    <div class="log-body">
                        <div class="log-action">{{ $log->action }}</div>
                        <div class="log-meta">{{ $log->user_name }}<span class="log-module">{{ $log->module }}</span></div>
                    </div>
                    <div class="log-time">{{ $log->created_at->diffForHumans() }}</div>
                </div>
                @empty
                <div class="panel-empty">
                    <svg style="width:36px;height:36px;color:var(--slate-300);margin:0 auto 8px;display:block;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                    No recent activity.
                </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<script>
(function () {

    /* Live date */
    document.getElementById('dashboardDate').textContent =
        new Date().toLocaleDateString('en-PH', { weekday:'short', month:'short', day:'numeric', year:'numeric' });

    /* Date badge -> calendar popup */
    (function () {
        const badge = document.getElementById('dateBadge');
        const cal   = document.getElementById('dateCalendar');
        const grid  = document.getElementById('calGrid');
        const title = document.getElementById('calTitle');
        if (!badge || !cal) return;

        const MONTHS = ['January','February','March','April','May','June',
                        'July','August','September','October','November','December'];
        const today = new Date();
        let vy = today.getFullYear(), vm = today.getMonth();

        function cell(n, muted, isToday) {
            const el = document.createElement('div');
            el.className = 'db-cal-cell' + (muted ? ' muted' : '') + (isToday ? ' today' : '');
            el.textContent = n;
            return el;
        }
        function render() {
            title.textContent = MONTHS[vm] + ' ' + vy;
            grid.innerHTML = '';
            const first    = new Date(vy, vm, 1).getDay();
            const days     = new Date(vy, vm + 1, 0).getDate();
            const prevDays = new Date(vy, vm, 0).getDate();
            for (let i = first - 1; i >= 0; i--) grid.appendChild(cell(prevDays - i, true, false));
            for (let d = 1; d <= days; d++) {
                const isToday = d === today.getDate() && vm === today.getMonth() && vy === today.getFullYear();
                grid.appendChild(cell(d, false, isToday));
            }
            const trail = (7 - ((first + days) % 7)) % 7;
            for (let d = 1; d <= trail; d++) grid.appendChild(cell(d, true, false));
        }
        function open(show) {
            const willOpen = (show === undefined) ? cal.hidden : show;
            cal.hidden = !willOpen;
            badge.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        }

        badge.addEventListener('click', e => { e.stopPropagation(); open(); });
        cal.addEventListener('click', e => e.stopPropagation());
        document.getElementById('calPrev').addEventListener('click', () => { if (--vm < 0) { vm = 11; vy--; } render(); });
        document.getElementById('calNext').addEventListener('click', () => { if (++vm > 11) { vm = 0; vy++; } render(); });
        document.getElementById('calToday').addEventListener('click', () => { vy = today.getFullYear(); vm = today.getMonth(); render(); });
        document.addEventListener('click', () => open(false));
        document.addEventListener('keydown', e => { if (e.key === 'Escape') open(false); });

        render();
    })();

    const C = { blue:'#2478e4', teal:'#1D9E75', purple:'#534AB7' };

    function gradient(ctx, hex) {
        const g = ctx.createLinearGradient(0, 0, 0, 260);
        const r = parseInt(hex.slice(1,3),16), gr = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16);
        g.addColorStop(0, `rgba(${r},${gr},${b},0.20)`);
        g.addColorStop(1, `rgba(${r},${gr},${b},0.00)`);
        return g;
    }

    const canvas = document.getElementById('activityChart');
    const ctx    = canvas.getContext('2d');

    const BASE_DS = {
        tension: 0.42,
        borderWidth: 2.5,
        pointRadius: 0,
        pointHoverRadius: 6,
        pointHoverBorderWidth: 2.5,
        pointHoverBackgroundColor: '#fff',
        fill: true,
    };

    // Helper to build datasets from raw data arrays
    const buildDatasets = (dataObj) => [
        { ...BASE_DS, label:'Registrations', data: dataObj.reg,
          borderColor: C.blue,   backgroundColor: gradient(ctx,C.blue),   pointHoverBorderColor: C.blue   },
        { ...BASE_DS, label:'Approvals',     data: dataObj.apr,
          borderColor: C.teal,   backgroundColor: gradient(ctx,C.teal),   pointHoverBorderColor: C.teal   },
        { ...BASE_DS, label:'Logins',        data: dataObj.log,
          borderColor: C.purple, backgroundColor: gradient(ctx,C.purple), pointHoverBorderColor: C.purple,
          yAxisID: 'y2' },
    ];

    // Initial Data Load
    let currentPeriod = 'weekly';
    
    // Fallback if data is missing/empty (prevents JS errors)
    const safeData = (arr) => arr || [];

    const initialData = {
        labels: safeData(@json($chartDataWeekly['labels'] ?? [])),
        reg:    safeData(@json($chartDataWeekly['reg'] ?? [])),
        apr:    safeData(@json($chartDataWeekly['apr'] ?? [])),
        log:    safeData(@json($chartDataWeekly['log'] ?? [])),
    };

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: initialData.labels,
            datasets: buildDatasets(initialData),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode:'index', intersect:false },
            animation: { duration:460, easing:'easeOutQuart' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0c3566',
                    titleColor: '#bcd6fc',
                    bodyColor: '#e0edff',
                    borderColor: 'rgba(36,120,228,0.35)',
                    borderWidth: 1,
                    padding: { x:14, y:10 },
                    cornerRadius: 10,
                    titleFont: { family:"'Outfit',sans-serif", size:13, weight:'700' },
                    bodyFont: { family:"'Plus Jakarta Sans',sans-serif", size:12, weight:'500' },
                    displayColors: true,
                    boxWidth: 8, boxHeight: 8,
                    usePointStyle: true,
                    callbacks: {
                        label: i => '  ' + i.dataset.label + ': ' + i.parsed.y.toLocaleString(),
                    },
                },
            },
            scales: {
                x: {
                    grid: { color:'#f1f5f9' },
                    border: { display:false },
                    ticks: { color:'#94a3b8', font:{ family:"'Plus Jakarta Sans',sans-serif", size:11.5, weight:'600' }, padding:8 },
                },
                y: {
                    position: 'left',
                    grid: { color:'#f1f5f9' },
                    border: { display:false },
                    ticks: { color:'#94a3b8', font:{ family:"'Plus Jakarta Sans',sans-serif", size:11, weight:'500' }, padding:10, maxTicksLimit:6, callback: v => v.toLocaleString() },
                },
                y2: {
                    position: 'right',
                    grid: { drawOnChartArea:false },
                    border: { display:false },
                    ticks: { color:'#a89ee0', font:{ family:"'Plus Jakarta Sans',sans-serif", size:11, weight:'500' }, padding:10, maxTicksLimit:6, callback: v => v.toLocaleString() },
                },
            },
        },
    });

    /* Period switcher */
    document.querySelectorAll('.period-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.period-tab').forEach(b => { b.classList.remove('active'); b.setAttribute('aria-pressed','false'); });
            this.classList.add('active'); this.setAttribute('aria-pressed','true');

            const p = this.dataset.period;
            currentPeriod = p;

            // Get data from Laravel Blade variables
            let rawData;
            if (p === 'weekly') rawData = @json($chartDataWeekly ?? []);
            else if (p === 'monthly') rawData = @json($chartDataMonthly ?? []);
            else rawData = @json($chartDataYearly ?? []);

            const d = {
                labels: safeData(rawData['labels']),
                reg:    safeData(rawData['reg']),
                apr:    safeData(rawData['apr']),
                log:    safeData(rawData['log']),
            };

            chart.data.labels             = d.labels;
            chart.data.datasets[0].data   = d.reg;
            chart.data.datasets[1].data   = d.apr;
            chart.data.datasets[2].data   = d.log;
            
            // Update gradients
            chart.data.datasets[0].backgroundColor = gradient(ctx, C.blue);
            chart.data.datasets[1].backgroundColor = gradient(ctx, C.teal);
            chart.data.datasets[2].backgroundColor = gradient(ctx, C.purple);
            
            chart.update('active');
        });
    });

    /* Entrance animations */
    if (typeof anime !== 'undefined') {
        anime({ targets:'.stat-card',         opacity:[0,1], translateY:[16,0], duration:420, easing:'easeOutQuad', delay:anime.stagger(70) });
        anime({ targets:'.chart-panel,.panel',opacity:[0,1], translateY:[12,0], duration:380, easing:'easeOutQuad', delay:anime.stagger(80,{start:280}) });
    }

})();
</script>
@endsection