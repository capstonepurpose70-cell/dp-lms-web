@extends('layouts.faculty')
@section('title', 'Faculty Dashboard')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">

<style>
    *, *::before, *::after { box-sizing: border-box; }

    :root {
        --ink:        #0f0d1a;
        --ink-soft:   #3d3750;
        --ink-muted:  #8b82a7;
        --ink-ghost:  #c5bfda;
        --surface:    #faf9fc;
        --surface-2:  #f3f0f9;
        --surface-3:  #ebe6f5;
        --border:     #e6e0f4;
        --border-2:   #d4cce8;
        --violet:     #6c3fc5;
        --violet-mid: #7e52d4;
        --violet-lt:  #a982f0;
        --violet-bg:  #f0eaff;
        --teal:       #0d9488;
        --teal-bg:    #e6faf8;
        --amber:      #c07a00;
        --amber-bg:   #fef7e6;
        --blue:       #2563eb;
        --blue-bg:    #eff4ff;
        --red:        #c0392b;
        --red-bg:     #fdecea;
        --radius-sm:  8px;
        --radius-md:  12px;
        --radius-lg:  18px;
        --radius-xl:  24px;
        --shadow-sm:  0 1px 3px rgba(15,13,26,0.06), 0 1px 2px rgba(15,13,26,0.04);
        --shadow-md:  0 4px 16px rgba(108,63,197,0.08), 0 1px 4px rgba(15,13,26,0.05);
        --shadow-lg:  0 12px 40px rgba(108,63,197,0.12), 0 2px 8px rgba(15,13,26,0.06);
    }

    .fd-page {
        font-family: 'DM Sans', sans-serif;
        color: var(--ink);
        background: var(--surface);
        min-height: 100vh;
        padding-bottom: 3rem;
    }

    /* ─────────────────────────────────────────
       BANNER
    ───────────────────────────────────────── */
    .fd-banner {
        position: relative;
        background: var(--ink);
        border-radius: var(--radius-xl);
        padding: 0;
        margin-bottom: 1.75rem;
        overflow: hidden;
        display: grid;
        grid-template-columns: 1fr auto;
        align-items: stretch;
        min-height: 148px;
    }

    /* Subtle grain texture */
    .fd-banner::before {
        content: '';
        position: absolute; inset: 0;
        background-image:
            url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.035'/%3E%3C/svg%3E");
        pointer-events: none; z-index: 0;
    }

    /* Color accent strip */
    .fd-banner::after {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, var(--violet-lt), var(--teal));
        border-radius: 0 2px 2px 0;
    }

    /* Decorative circles */
    .fd-banner-orb {
        position: absolute;
        border-radius: 50%;
        pointer-events: none; z-index: 0;
    }

    .fd-banner-left {
        position: relative; z-index: 1;
        padding: 30px 32px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 6px;
    }

    .fd-banner-eyebrow {
        display: flex; align-items: center; gap: 8px;
        font-size: 10.5px; font-weight: 600;
        letter-spacing: 0.12em; text-transform: uppercase;
        color: var(--violet-lt);
        margin-bottom: 2px;
    }
    .fd-banner-eyebrow-dot {
        width: 5px; height: 5px; border-radius: 50%;
        background: var(--teal);
        flex-shrink: 0;
        box-shadow: 0 0 6px rgba(13,148,136,0.7);
        animation: fd-pulse 2.4s ease-in-out infinite;
    }
    @keyframes fd-pulse {
        0%,100% { opacity:1; transform:scale(1); }
        50%      { opacity:0.5; transform:scale(0.8); }
    }

    .fd-banner-title {
        font-family: 'Instrument Serif', serif;
        font-size: 1.9rem; font-weight: 400;
        color: #fff; line-height: 1.2;
        letter-spacing: -0.01em;
    }
    .fd-banner-title em {
        font-style: italic;
        color: var(--violet-lt);
    }

    .fd-banner-sub {
        font-size: 12.5px;
        color: rgba(255,255,255,0.4);
        font-weight: 400;
        display: flex; align-items: center; gap: 8px;
        flex-wrap: wrap;
    }
    .fd-banner-sub-sep { color: rgba(255,255,255,0.15); }
    .fd-banner-alert {
        display: inline-flex; align-items: center; gap: 5px;
        font-weight: 600;
        padding: 2px 8px; border-radius: 20px;
    }
    .fd-banner-alert.amber { color: #fbbf24; background: rgba(251,191,36,0.1); }
    .fd-banner-alert.green { color: #34d399; background: rgba(52,211,153,0.1); }

    /* Stats panel */
    .fd-banner-right {
        position: relative; z-index: 1;
        display: flex;
        border-left: 1px solid rgba(255,255,255,0.06);
    }
    .fd-banner-stat {
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        padding: 0 28px;
        border-right: 1px solid rgba(255,255,255,0.06);
        gap: 4px;
        min-width: 88px;
    }
    .fd-banner-stat:last-child { border-right: none; }
    .fd-banner-stat-num {
        font-family: 'Instrument Serif', serif;
        font-size: 2.1rem; font-weight: 400;
        line-height: 1; color: #fff;
        letter-spacing: -0.02em;
    }
    .fd-banner-stat-num.amber { color: #fbbf24; }
    .fd-banner-stat-num.green { color: #34d399; }
    .fd-banner-stat-num.blue  { color: #93c5fd; }
    .fd-banner-stat-label {
        font-size: 10px; font-weight: 600;
        color: rgba(255,255,255,0.3);
        text-transform: uppercase; letter-spacing: 0.1em;
        text-align: center;
    }

    /* ─────────────────────────────────────────
       PAGE ANIMATION
    ───────────────────────────────────────── */
    .fd-page { animation: fd-in 0.4s cubic-bezier(0.22,1,0.36,1) both; }
    @keyframes fd-in {
        from { opacity:0; transform:translateY(12px); }
        to   { opacity:1; transform:translateY(0); }
    }
    .fd-grid { animation: fd-in 0.45s 0.05s cubic-bezier(0.22,1,0.36,1) both; }

    /* ─────────────────────────────────────────
       GRID
    ───────────────────────────────────────── */
    .fd-grid {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 960px) { .fd-grid { grid-template-columns: 1fr; } }
    @media (max-width: 640px) {
        .fd-banner { grid-template-columns: 1fr; }
        .fd-banner-right { border-left: none; border-top: 1px solid rgba(255,255,255,0.06); }
        .fd-banner-stat { flex: 1; padding: 16px 0; }
    }

    /* ─────────────────────────────────────────
       CARD BASE
    ───────────────────────────────────────── */
    .fd-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        transition: box-shadow 0.2s;
    }

    .fd-card-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--surface-2);
    }
    .fd-card-header-left {
        display: flex; align-items: center; gap: 10px;
    }
    .fd-card-icon {
        width: 32px; height: 32px;
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .fd-card-icon svg { width: 15px; height: 15px; }

    .fd-card-title {
        font-size: 13px; font-weight: 600;
        color: var(--ink);
        letter-spacing: -0.01em;
    }

    .fd-count-badge {
        font-size: 10px; font-weight: 700;
        padding: 2px 7px; border-radius: 20px;
        background: var(--amber-bg);
        color: var(--amber);
        border: 1px solid #f3d89a;
        letter-spacing: 0.02em;
    }

    .fd-view-all {
        font-size: 11.5px; font-weight: 600;
        color: var(--violet);
        text-decoration: none;
        padding: 5px 11px;
        border-radius: var(--radius-sm);
        background: var(--violet-bg);
        border: 1px solid #ddd4f8;
        transition: all 0.18s;
        letter-spacing: -0.01em;
    }
    .fd-view-all:hover {
        background: #e4d9ff;
        color: #5a32b0;
        border-color: #c9b8f5;
    }

    /* ─────────────────────────────────────────
       ENROLLMENT ROWS
    ───────────────────────────────────────── */
    .fd-enrollment-row {
        display: flex; align-items: center; gap: 13px;
        padding: 13px 1.25rem;
        border-bottom: 1px solid var(--surface-2);
        text-decoration: none;
        transition: background 0.15s;
        position: relative;
    }
    .fd-enrollment-row:last-child { border-bottom: none; }
    .fd-enrollment-row:hover { background: #fdfcff; }
    .fd-enrollment-row:hover .fd-row-arrow { color: var(--violet); }

    /* Hover left accent line */
    .fd-enrollment-row::before {
        content: '';
        position: absolute; left: 0; top: 10px; bottom: 10px;
        width: 2px; border-radius: 2px;
        background: var(--violet);
        opacity: 0; transition: opacity 0.18s;
    }
    .fd-enrollment-row:hover::before { opacity: 1; }

    .fd-avatar {
        width: 38px; height: 38px;
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700; color: #fff;
        flex-shrink: 0;
        background: linear-gradient(135deg, var(--violet-mid), #5a32b0);
        letter-spacing: -0.01em;
    }
    .fd-row-name {
        font-size: 13px; font-weight: 600;
        color: var(--ink);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        letter-spacing: -0.01em;
    }
    .fd-row-meta {
        font-size: 11.5px; color: var(--ink-muted);
        margin-top: 2px; font-weight: 400;
    }
    .fd-badge-pending {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 9px; border-radius: 20px;
        font-size: 10.5px; font-weight: 600;
        background: var(--amber-bg);
        color: var(--amber);
        border: 1px solid #f3d89a;
        flex-shrink: 0;
        letter-spacing: 0.02em;
    }
    .fd-badge-pending::before {
        content: '';
        width: 5px; height: 5px; border-radius: 50%;
        background: #e8a800;
        flex-shrink: 0;
    }
    .fd-row-arrow {
        color: var(--ink-ghost);
        flex-shrink: 0; margin-left: auto;
        transition: color 0.18s, transform 0.18s;
    }
    .fd-enrollment-row:hover .fd-row-arrow { transform: translateX(2px); }

    /* ─────────────────────────────────────────
       EMPTY STATE
    ───────────────────────────────────────── */
    .fd-empty {
        padding: 44px 1.25rem;
        text-align: center;
        display: flex; flex-direction: column; align-items: center; gap: 8px;
    }
    .fd-empty-icon {
        width: 48px; height: 48px; border-radius: 14px;
        background: var(--violet-bg);
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 4px;
    }
    .fd-empty-icon svg { width: 22px; height: 22px; color: var(--violet-lt); }
    .fd-empty-title { font-size: 13px; font-weight: 600; color: var(--ink-soft); }
    .fd-empty-sub { font-size: 12px; color: var(--ink-muted); }

    /* ─────────────────────────────────────────
       SIDE CARDS
    ───────────────────────────────────────── */
    .fd-side-stack {
        display: flex; flex-direction: column; gap: 1.1rem;
    }

    /* ─────────────────────────────────────────
       MINI STATS
    ───────────────────────────────────────── */
    .fd-mini-stat {
        display: flex; align-items: center; gap: 12px;
        padding: 13px 1.25rem;
        border-bottom: 1px solid var(--surface-2);
        transition: background 0.15s;
    }
    .fd-mini-stat:last-child { border-bottom: none; }
    .fd-mini-stat:hover { background: #fdfcff; }

    .fd-mini-icon {
        width: 34px; height: 34px; border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .fd-mini-icon svg { width: 15px; height: 15px; }
    .fd-mini-label { font-size: 12.5px; color: var(--ink-soft); font-weight: 400; flex: 1; }
    .fd-mini-val {
        font-family: 'Instrument Serif', serif;
        font-size: 1.3rem; font-weight: 400;
        letter-spacing: -0.02em;
        line-height: 1;
    }

    /* ─────────────────────────────────────────
       QUICK ACTION BUTTON
    ───────────────────────────────────────── */
    .fd-action-btn {
        display: flex; align-items: center; gap: 12px;
        padding: 13px 14px;
        border-radius: var(--radius-md);
        background: var(--surface);
        border: 1px solid var(--border);
        text-decoration: none;
        transition: all 0.18s;
        margin: 1rem;
    }
    .fd-action-btn:hover {
        background: var(--violet-bg);
        border-color: #ddd4f8;
        box-shadow: 0 2px 12px rgba(108,63,197,0.08);
        transform: translateY(-1px);
    }
    .fd-action-btn-icon {
        width: 34px; height: 34px; border-radius: var(--radius-sm);
        background: var(--ink); display: flex; align-items: center;
        justify-content: center; flex-shrink: 0;
        transition: background 0.18s;
    }
    .fd-action-btn:hover .fd-action-btn-icon { background: var(--violet); }
    .fd-action-btn-icon svg { width: 15px; height: 15px; }
    .fd-action-btn-label {
        font-size: 13px; font-weight: 600; color: var(--ink);
        letter-spacing: -0.01em;
    }
    .fd-action-btn-sub {
        font-size: 11.5px; color: var(--ink-muted); font-weight: 400;
    }
    .fd-action-btn-arrow {
        margin-left: auto; color: var(--ink-ghost);
        transition: color 0.18s, transform 0.18s;
        flex-shrink: 0;
    }
    .fd-action-btn:hover .fd-action-btn-arrow {
        color: var(--violet);
        transform: translateX(2px);
    }

    /* ─────────────────────────────────────────
       STATUS PILLS (utility)
    ───────────────────────────────────────── */
    .fd-pill {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 10.5px; font-weight: 600;
        padding: 3px 9px; border-radius: 20px;
        border: 1px solid transparent; letter-spacing: 0.02em;
    }
    .fd-pill::before {
        content: ''; width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0;
    }
    .fd-pill-approved { background: var(--teal-bg); color: var(--teal); border-color: #99e6e1; }
    .fd-pill-approved::before { background: var(--teal); }
    .fd-pill-pending  { background: var(--amber-bg); color: var(--amber); border-color: #f3d89a; }
    .fd-pill-pending::before  { background: #e8a800; }
    .fd-pill-rejected { background: var(--red-bg); color: var(--red); border-color: #f5b3ac; }
    .fd-pill-rejected::before { background: var(--red); }

    /* ─────────────────────────────────────────
       SECTION LABEL
    ───────────────────────────────────────── */
    .fd-section-label {
        font-size: 10.5px; font-weight: 700;
        letter-spacing: 0.1em; text-transform: uppercase;
        color: var(--ink-muted); margin-bottom: 0.6rem;
        padding-left: 2px;
    }
</style>

<div class="fd-page">

    {{-- ── Welcome Banner ── --}}
    <div class="fd-banner">
        {{-- Decorative orbs --}}
        <div class="fd-banner-orb" style="width:260px;height:260px;top:-100px;left:40%;background:radial-gradient(circle,rgba(108,63,197,0.18) 0%,transparent 70%);"></div>
        <div class="fd-banner-orb" style="width:180px;height:180px;bottom:-70px;left:55%;background:radial-gradient(circle,rgba(13,148,136,0.12) 0%,transparent 70%);"></div>

        <div class="fd-banner-left">
            <div class="fd-banner-eyebrow">
                <div class="fd-banner-eyebrow-dot"></div>
                Faculty Portal
            </div>
            <div class="fd-banner-title">
                Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
                <em>{{ explode(' ', auth()->user()->name)[0] }}</em>
            </div>
            <div class="fd-banner-sub">
                <span>{{ now()->format('l, F d, Y') }}</span>
                <span class="fd-banner-sub-sep">·</span>
                @if($pendingCount > 0)
                    <span class="fd-banner-alert amber">
                        {{ $pendingCount }} enrollment{{ $pendingCount > 1 ? 's' : '' }} awaiting review
                    </span>
                @else
                    <span class="fd-banner-alert green">All enrollments are up to date</span>
                @endif
            </div>
        </div>

        <div class="fd-banner-right">
            <div class="fd-banner-stat">
                <div class="fd-banner-stat-num amber">{{ $pendingCount }}</div>
                <div class="fd-banner-stat-label">Pending</div>
            </div>
            <div class="fd-banner-stat">
                <div class="fd-banner-stat-num green">{{ $approvedToday }}</div>
                <div class="fd-banner-stat-label">Today</div>
            </div>
            <div class="fd-banner-stat">
                <div class="fd-banner-stat-num blue">{{ $totalEnrolled }}</div>
                <div class="fd-banner-stat-label">Enrolled</div>
            </div>
        </div>
    </div>

    {{-- ── Main Grid ── --}}
    <div class="fd-grid">

        {{-- LEFT: Pending enrollments --}}
        <div>
            <div class="fd-section-label">Enrollment Queue</div>
            <div class="fd-card">
                <div class="fd-card-header">
                    <div class="fd-card-header-left">
                        <div class="fd-card-icon" style="background:var(--amber-bg);">
                            <svg fill="none" stroke="#c07a00" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="fd-card-title">Pending Enrollment Requests</span>
                        @if($pendingCount > 0)
                            <span class="fd-count-badge">{{ $pendingCount }}</span>
                        @endif
                    </div>
                    <a href="{{ route('faculty.enrollments') }}" class="fd-view-all">View all →</a>
                </div>

                @forelse($recentRequests as $req)
                <a href="{{ route('faculty.enrollments.show', $req) }}" class="fd-enrollment-row">
                    <div class="fd-avatar">
                        {{ strtoupper(substr($req->student->name ?? '?', 0, 1)) }}
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div class="fd-row-name">{{ $req->full_name }}</div>
                        <div class="fd-row-meta">
                            Grade {{ $req->grade_level }}
                            &nbsp;·&nbsp;
                            {{ ucfirst($req->student_type) }}
                            &nbsp;·&nbsp;
                            {{ $req->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <span class="fd-badge-pending">Pending</span>
                    <svg class="fd-row-arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6"/>
                    </svg>
                </a>
                @empty
                <div class="fd-empty">
                    <div class="fd-empty-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="fd-empty-title">All caught up!</div>
                    <div class="fd-empty-sub">No pending enrollment requests at the moment.</div>
                </div>
                @endforelse
            </div>
        </div>

        {{-- RIGHT: Side cards --}}
        <div class="fd-side-stack">

            {{-- Enrollment Summary --}}
            <div>
                <div class="fd-section-label">Summary</div>
                <div class="fd-card">
                    <div class="fd-card-header" style="padding: 0.85rem 1.25rem;">
                        <div class="fd-card-header-left">
                            <div class="fd-card-icon" style="background:var(--surface-2);">
                                <svg fill="none" stroke="var(--violet)" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <span class="fd-card-title">Enrollment Summary</span>
                        </div>
                    </div>

                    <div class="fd-mini-stat">
                        <div class="fd-mini-icon" style="background:var(--amber-bg);">
                            <svg fill="none" stroke="#c07a00" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="fd-mini-label">Pending review</span>
                        <span class="fd-mini-val" style="color:var(--amber);">{{ $pendingCount }}</span>
                    </div>

                    <div class="fd-mini-stat">
                        <div class="fd-mini-icon" style="background:var(--teal-bg);">
                            <svg fill="none" stroke="var(--teal)" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="fd-mini-label">Approved today</span>
                        <span class="fd-mini-val" style="color:var(--teal);">{{ $approvedToday }}</span>
                    </div>

                    <div class="fd-mini-stat">
                        <div class="fd-mini-icon" style="background:var(--blue-bg);">
                            <svg fill="none" stroke="var(--blue)" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87a4 4 0 100-8 4 4 0 000 8z"/>
                            </svg>
                        </div>
                        <span class="fd-mini-label">Total enrolled this SY</span>
                        <span class="fd-mini-val" style="color:var(--blue);">{{ $totalEnrolled }}</span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div>
                <div class="fd-section-label">Quick Actions</div>
                <div class="fd-card">
                    <a href="{{ route('faculty.enrollments') }}" class="fd-action-btn">
                        <div class="fd-action-btn-icon">
                            <svg width="15" height="15" fill="none" stroke="#fff" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div>
                            <div class="fd-action-btn-label">Review Enrollments</div>
                            <div class="fd-action-btn-sub">{{ $pendingCount }} pending</div>
                        </div>
                        <svg class="fd-action-btn-arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6"/>
                        </svg>
                    </a>
                </div>
            </div>

        </div>{{-- /right --}}
    </div>{{-- /fd-grid --}}
</div>{{-- /fd-page --}}
@endsection