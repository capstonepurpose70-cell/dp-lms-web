@extends('layouts.app')
@section('title', 'Student Dashboard')

@section('content')
<style>
    /* ═══════════════════════════════════════════════════════════
       STUDENT DASHBOARD
       Ginagamit ang design tokens ng layouts/app.blade.php
       (--accent, --text-1..3, --border, --r-*, --shadow-*)
       para tugma sa buong sistema. Walang bagong Tailwind class —
       plain CSS lang, kaya hindi apektado ng build state.
    ═══════════════════════════════════════════════════════════ */

    .sd-wrap { max-width: 1080px; margin: 0 auto; }

    /* ─── Page header ─────────────────────────────────────────── */
    .sd-head { margin-bottom: 24px; }
    .sd-greet {
        font-size: 24px; font-weight: 700; letter-spacing: -.02em;
        color: var(--text-1); line-height: 1.25;
    }
    .sd-date { font-size: 13.5px; color: var(--text-2); margin-top: 4px; }

    /* ─── Cards ───────────────────────────────────────────────── */
    .sd-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r-lg);
        box-shadow: var(--shadow-sm);
    }
    .sd-card-pad { padding: 20px 22px; }

    .sd-section-title {
        font-size: 12px; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; color: var(--text-2);
    }

    /* ─── Enrollment strip ────────────────────────────────────── */
    .sd-enroll { display: flex; flex-wrap: wrap; align-items: center; gap: 28px; }
    .sd-enroll-item { min-width: 96px; }
    .sd-enroll-label {
        font-size: 11px; font-weight: 600; letter-spacing: .06em;
        text-transform: uppercase; color: var(--text-3);
    }
    .sd-enroll-value {
        font-size: 17px; font-weight: 700; color: var(--text-1); margin-top: 3px;
    }
    .sd-pill {
        margin-left: auto; display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 12px; border-radius: 999px;
        font-size: 12px; font-weight: 600;
        background: var(--green-lt); color: var(--green-dark);
    }
    .sd-pill-dot {
        width: 6px; height: 6px; border-radius: 50%; background: var(--green);
    }

    /* ─── Notice (hindi pa enrolled) ──────────────────────────── */
    .sd-notice {
        display: flex; gap: 12px; align-items: flex-start;
        background: #FFFBEB; border: 1px solid #FDE68A;
        border-radius: var(--r-md); padding: 14px 16px; margin-top: 14px;
    }
    .sd-notice-title { font-size: 13.5px; font-weight: 700; color: #92400E; }
    .sd-notice-text  { font-size: 12.5px; color: #A16207; margin-top: 2px; line-height: 1.5; }

    /* ─── Grid ────────────────────────────────────────────────── */
    .sd-grid { display: grid; gap: 14px; }
    .sd-grid-3 { grid-template-columns: repeat(3, 1fr); }
    .sd-grid-2 { grid-template-columns: repeat(2, 1fr); }

    /* ─── Stat cards ──────────────────────────────────────────── */
    .sd-stat { padding: 18px 20px; }
    .sd-stat-top { display: flex; align-items: center; gap: 10px; }
    .sd-ico {
        width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: var(--accent-lt); color: var(--accent);
    }
    .sd-stat-label {
        font-size: 12.5px; font-weight: 600; color: var(--text-2);
    }
    .sd-stat-num {
        font-size: 30px; font-weight: 700; color: var(--text-1);
        line-height: 1.1; margin-top: 12px; letter-spacing: -.02em;
    }
    .sd-stat-sub { font-size: 12px; color: var(--text-3); margin-top: 3px; }

    /* ─── Subject list ────────────────────────────────────────── */
    .sd-subj {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 14px;
        border: 1px solid var(--border); border-radius: var(--r-md);
        background: var(--surface);
        transition: border-color .2s ease, background .2s ease;
    }
    .sd-subj:hover { border-color: var(--accent); background: var(--accent-lt); }
    .sd-subj-name {
        font-size: 13.5px; font-weight: 600; color: var(--text-1);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .sd-subj-teacher {
        font-size: 12px; color: var(--text-2); margin-top: 2px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    /* ─── Quick action cards ──────────────────────────────────── */
    .sd-nav {
        display: flex; align-items: center; gap: 14px;
        padding: 16px 18px; min-height: 68px;   /* >=44px touch target */
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r-lg);
        box-shadow: var(--shadow-sm);
        text-decoration: none; cursor: pointer;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .sd-nav:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        border-color: var(--accent);
    }
    .sd-nav:focus-visible {
        outline: 2px solid var(--accent);
        outline-offset: 2px;
    }
    .sd-nav-body { flex: 1; min-width: 0; }
    .sd-nav-title { font-size: 14px; font-weight: 600; color: var(--text-1); }
    .sd-nav-desc  { font-size: 12.5px; color: var(--text-2); margin-top: 2px; }
    .sd-nav-arrow { color: var(--text-3); flex-shrink: 0; transition: transform .2s ease, color .2s ease; }
    .sd-nav:hover .sd-nav-arrow { color: var(--accent); transform: translateX(3px); }

    /* ─── Announcements ───────────────────────────────────────── */
    .sd-ann { padding: 14px 0; border-bottom: 1px solid var(--border); }
    .sd-ann:last-child { border-bottom: 0; padding-bottom: 0; }
    .sd-ann:first-of-type { padding-top: 4px; }
    .sd-ann-row { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; }
    .sd-ann-title { font-size: 13.5px; font-weight: 600; color: var(--text-1); }
    .sd-ann-body {
        font-size: 12.5px; color: var(--text-2); margin-top: 3px; line-height: 1.55;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .sd-ann-meta { font-size: 12px; color: var(--text-3); margin-top: 5px; }
    .sd-ann-time { font-size: 12px; color: var(--text-3); white-space: nowrap; flex-shrink: 0; }

    /* ─── Empty state ─────────────────────────────────────────── */
    .sd-empty { text-align: center; padding: 32px 16px; }
    .sd-empty-ico {
        width: 44px; height: 44px; border-radius: 50%; margin: 0 auto 12px;
        background: var(--bg); color: var(--text-3);
        display: flex; align-items: center; justify-content: center;
    }
    .sd-empty-text { font-size: 13px; color: var(--text-2); }
    .sd-empty-sub  { font-size: 12px; color: var(--text-3); margin-top: 3px; }

    /* ─── Spacing ─────────────────────────────────────────────── */
    .sd-mb { margin-bottom: 18px; }

    /* ─── Motion (subtle, 300-400ms) ──────────────────────────── */
    .sd-in { animation: sdIn .35s cubic-bezier(.22,.61,.36,1) both; }
    .sd-d1 { animation-delay: .04s; }
    .sd-d2 { animation-delay: .09s; }
    .sd-d3 { animation-delay: .14s; }
    .sd-d4 { animation-delay: .19s; }
    @keyframes sdIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }

    /* ─── Responsive ──────────────────────────────────────────── */
    @media (max-width: 860px) {
        .sd-grid-3, .sd-grid-2 { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 560px) {
        .sd-grid-3, .sd-grid-2 { grid-template-columns: 1fr; }
        .sd-greet { font-size: 21px; }
        .sd-enroll { gap: 18px; }
        .sd-pill { margin-left: 0; }
        .sd-card-pad { padding: 16px 18px; }
    }

    /* ─── Reduced motion ──────────────────────────────────────── */
    @media (prefers-reduced-motion: reduce) {
        .sd-in { animation: none; }
        .sd-nav, .sd-subj, .sd-nav-arrow { transition: none; }
        .sd-nav:hover { transform: none; }
    }
</style>

<div class="sd-wrap">

    {{-- ── Header ─────────────────────────────────────────────── --}}
    <header class="sd-head sd-in">
        <h1 class="sd-greet">
            Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
            {{ explode(' ', $user->name)[0] }}
        </h1>
        <p class="sd-date">{{ now()->format('l, F j, Y') }}</p>

        @unless($enrollment)
        <div class="sd-notice" role="status">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D97706"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                 aria-hidden="true" style="flex-shrink:0;margin-top:1px;">
                <circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/>
            </svg>
            <div>
                <p class="sd-notice-title">Not yet enrolled</p>
                <p class="sd-notice-text">
                    Please wait for the administrator to assign your section and grade level.
                </p>
            </div>
        </div>
        @endunless
    </header>

    {{-- ── Enrollment summary ─────────────────────────────────── --}}
    @if($enrollment)
    <section class="sd-card sd-card-pad sd-mb sd-in sd-d1" aria-label="Enrollment details">
        <div class="sd-enroll">
            <div class="sd-enroll-item">
                <p class="sd-enroll-label">Grade Level</p>
                <p class="sd-enroll-value">Grade {{ $grade }}</p>
            </div>
            <div class="sd-enroll-item">
                <p class="sd-enroll-label">Section</p>
                <p class="sd-enroll-value">{{ $section->name ?? '—' }}</p>
            </div>
            <div class="sd-enroll-item">
                <p class="sd-enroll-label">School Year</p>
                <p class="sd-enroll-value">{{ $enrollment->school_year }}</p>
            </div>
            <span class="sd-pill">
                <span class="sd-pill-dot" aria-hidden="true"></span>
                Enrolled
            </span>
        </div>
    </section>
    @endif

    {{-- ── Stats ──────────────────────────────────────────────── --}}
    <section class="sd-grid sd-grid-3 sd-mb" aria-label="Summary">

        <div class="sd-card sd-stat sd-in sd-d1">
            <div class="sd-stat-top">
                <span class="sd-ico" aria-hidden="true">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                </span>
                <span class="sd-stat-label">Subjects</span>
            </div>
            <p class="sd-stat-num">{{ $subjectCount }}</p>
            <p class="sd-stat-sub">Enrolled this term</p>
        </div>

        <div class="sd-card sd-stat sd-in sd-d2">
            <div class="sd-stat-top">
                <span class="sd-ico" aria-hidden="true">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-2"/>
                        <rect x="9" y="2" width="6" height="4" rx="1"/><path d="m9 14 2 2 4-4"/>
                    </svg>
                </span>
                <span class="sd-stat-label">Assignments</span>
            </div>
            <p class="sd-stat-num">{{ $pendingAssignments }}</p>
            <p class="sd-stat-sub">Pending submission</p>
        </div>

        <div class="sd-card sd-stat sd-in sd-d3">
            <div class="sd-stat-top">
                <span class="sd-ico" aria-hidden="true">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </span>
                <span class="sd-stat-label">Messages</span>
            </div>
            <p class="sd-stat-num">{{ $unreadMessages }}</p>
            <p class="sd-stat-sub">Unread</p>
        </div>
    </section>

    {{-- ── Subjects ───────────────────────────────────────────── --}}
    @if($enrollment && $subjects->count())
    <section class="sd-card sd-card-pad sd-mb sd-in sd-d2" aria-label="My subjects">
        <h2 class="sd-section-title" style="margin-bottom:14px;">My Subjects</h2>
        <div class="sd-grid sd-grid-2">
            @foreach($subjects as $ts)
            <div class="sd-subj">
                <span class="sd-ico" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                </span>
                <div style="min-width:0;">
                    <p class="sd-subj-name">{{ $ts->subject->name }}</p>
                    <p class="sd-subj-teacher">{{ $ts->teacher->name ?? 'Teacher to be assigned' }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ── Quick actions ──────────────────────────────────────── --}}
    <section class="sd-mb sd-in sd-d3" aria-label="Quick actions">
        <h2 class="sd-section-title" style="margin-bottom:12px;">Quick Actions</h2>
        <div class="sd-grid sd-grid-2">

            <a href="{{ route('student.modules') }}" class="sd-nav">
                <span class="sd-ico" aria-hidden="true">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                </span>
                <span class="sd-nav-body">
                    <span class="sd-nav-title" style="display:block;">Learning Modules</span>
                    <span class="sd-nav-desc" style="display:block;">View and download your materials</span>
                </span>
                <svg class="sd-nav-arrow" width="17" height="17" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </a>

            <a href="{{ route('student.quizzes') }}" class="sd-nav">
                <span class="sd-ico" aria-hidden="true">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-2"/>
                        <rect x="9" y="2" width="6" height="4" rx="1"/><path d="m9 14 2 2 4-4"/>
                    </svg>
                </span>
                <span class="sd-nav-body">
                    <span class="sd-nav-title" style="display:block;">Quizzes</span>
                    <span class="sd-nav-desc" style="display:block;">Take quizzes assigned by your teachers</span>
                </span>
                <svg class="sd-nav-arrow" width="17" height="17" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </a>

            <a href="{{ route('student.grades') }}" class="sd-nav">
                <span class="sd-ico" aria-hidden="true">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3v18h18"/><path d="M18 17V9M13 17V5M8 17v-3"/>
                    </svg>
                </span>
                <span class="sd-nav-body">
                    <span class="sd-nav-title" style="display:block;">My Grades</span>
                    <span class="sd-nav-desc" style="display:block;">View your academic performance</span>
                </span>
                <svg class="sd-nav-arrow" width="17" height="17" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </a>

            <a href="{{ route('student.messages') }}" class="sd-nav">
                <span class="sd-ico" aria-hidden="true">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </span>
                <span class="sd-nav-body">
                    <span class="sd-nav-title" style="display:block;">Messages</span>
                    <span class="sd-nav-desc" style="display:block;">Chat with your teachers</span>
                </span>
                <svg class="sd-nav-arrow" width="17" height="17" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </a>

            <a href="{{ route('student.face.register') }}" class="sd-nav">
                <span class="sd-ico" aria-hidden="true">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/>
                        <circle cx="12" cy="11" r="2.5"/><path d="M8.5 16.5a4.5 4.5 0 0 1 7 0"/>
                    </svg>
                </span>
                <span class="sd-nav-body">
                    <span class="sd-nav-title" style="display:block;">Register Face</span>
                    <span class="sd-nav-desc" style="display:block;">Set up attendance camera recognition</span>
                </span>
                <svg class="sd-nav-arrow" width="17" height="17" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>
    </section>

    {{-- ── Announcements ──────────────────────────────────────── --}}
    <section class="sd-card sd-card-pad sd-in sd-d4" aria-label="Recent announcements">
        <h2 class="sd-section-title" style="margin-bottom:6px;">Recent Announcements</h2>

        @forelse($announcements as $announcement)
        <article class="sd-ann">
            <div class="sd-ann-row">
                <div style="min-width:0;">
                    <h3 class="sd-ann-title">{{ $announcement->title }}</h3>
                    <p class="sd-ann-body">{{ $announcement->body }}</p>
                    <p class="sd-ann-meta">{{ $announcement->author->name ?? 'School Administration' }}</p>
                </div>
                <time class="sd-ann-time" datetime="{{ $announcement->created_at->toIso8601String() }}">
                    {{ $announcement->created_at->diffForHumans() }}
                </time>
            </div>
        </article>
        @empty
        <div class="sd-empty">
            <div class="sd-empty-ico" aria-hidden="true">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.268 21a2 2 0 0 0 3.464 0"/>
                    <path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"/>
                </svg>
            </div>
            <p class="sd-empty-text">No announcements yet</p>
            <p class="sd-empty-sub">New updates from your school will appear here.</p>
        </div>
        @endforelse
    </section>

</div>
@endsection