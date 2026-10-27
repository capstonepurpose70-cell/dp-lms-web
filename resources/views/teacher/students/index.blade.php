@extends('layouts.teacher')
@section('title', 'My Students')

@section('content')
<style>
    /* ════════════════════════════════════════════════
       PAGE — MY STUDENTS
       Design tokens match layouts/teacher.blade.php.
    ════════════════════════════════════════════════ */

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .fade-up   { animation: fadeUp .38s cubic-bezier(0.16,1,0.3,1) both; }
    .fade-up-1 { animation-delay: .04s; }
    .fade-up-2 { animation-delay: .08s; }
    .fade-up-3 { animation-delay: .13s; }

    /* ── Stat strip ── */
    .stu-stat-strip {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 22px;
    }
    .stu-stat-card {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 14px;
        padding: 16px 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,.04);
        display: flex;
        align-items: center;
        gap: 14px;
        transition: transform .22s cubic-bezier(0.34,1.56,0.64,1), box-shadow .22s;
        cursor: default;
    }
    .stu-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,.08);
    }
    .stu-stat-icon {
        width: 42px; height: 42px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        transition: transform .28s cubic-bezier(0.34,1.56,0.64,1);
    }
    .stu-stat-card:hover .stu-stat-icon { transform: scale(1.12) rotate(-5deg); }
    .stu-stat-number { font-size: 26px; font-weight: 800; color: #0F172A; line-height: 1; }
    .stu-stat-label  { font-size: 12px; font-weight: 600; color: #6b7280; margin-top: 2px; }

    /* ── Toolbar ── */
    .stu-toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .stu-search-wrap { position: relative; flex: 1; min-width: 200px; }
    .stu-search-icon {
        position: absolute; left: 12px; top: 50%;
        transform: translateY(-50%);
        pointer-events: none; color: #94a3b8;
    }
    .stu-search {
        width: 100%;
        border: 1.5px solid #e2e8f4;
        border-radius: 10px;
        padding: 9px 14px 9px 38px;
        font-size: 13px; color: #0F172A;
        background: #fff; outline: none; font-family: inherit;
        transition: border-color .18s, box-shadow .18s;
    }
    .stu-search:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,.1);
    }
    .stu-search::placeholder { color: #94a3b8; }

    .stu-filter-select {
        border: 1.5px solid #e2e8f4; border-radius: 10px;
        padding: 9px 34px 9px 12px;
        font-size: 13px; color: #0F172A; background: #fff;
        outline: none; font-family: inherit; cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 10px center;
        background-size: 13px; flex-shrink: 0;
        transition: border-color .18s, box-shadow .18s;
    }
    .stu-filter-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,.1);
    }

    .stu-count-badge {
        font-size: 12px; font-weight: 600; color: #64748b;
        padding: 5px 12px; background: #f1f5f9; border-radius: 20px;
        white-space: nowrap; flex-shrink: 0;
    }

    /* ── Table card ── */
    .stu-table-card {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(0,0,0,.04);
        overflow: hidden;
    }

    /* ── Student rows ── */
    .stu-row {
        display: flex; align-items: center; gap: 14px;
        padding: 13px 20px;
        border-bottom: 1px solid #f3f4f6;
        cursor: pointer;
        transition: background .15s;
        outline: none;
    }
    .stu-row:last-child { border-bottom: none; }
    .stu-row:hover { background: #f8faff; }
    .stu-row:focus-visible { background: #f0f7ff; outline: 2px solid #2563eb; outline-offset: -2px; }
    .stu-row:hover .stu-arrow { color: #2563eb; transform: translateX(3px); }

    .stu-avatar {
        width: 40px; height: 40px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 800; color: #fff;
        flex-shrink: 0;
        transition: transform .22s cubic-bezier(0.34,1.56,0.64,1);
    }
    .stu-row:hover .stu-avatar { transform: scale(1.1); }

    /* Avatar colour palette — deterministic by student id mod 8 */
    .av-0 { background: linear-gradient(135deg,#3b82f6,#1d4ed8); }
    .av-1 { background: linear-gradient(135deg,#8b5cf6,#6d28d9); }
    .av-2 { background: linear-gradient(135deg,#10b981,#047857); }
    .av-3 { background: linear-gradient(135deg,#f43f5e,#be123c); }
    .av-4 { background: linear-gradient(135deg,#f59e0b,#b45309); }
    .av-5 { background: linear-gradient(135deg,#06b6d4,#0e7490); }
    .av-6 { background: linear-gradient(135deg,#6366f1,#4338ca); }
    .av-7 { background: linear-gradient(135deg,#ec4899,#be185d); }

    .stu-name {
        font-size: 13px; font-weight: 700; color: #0F172A;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .stu-meta { font-size: 12px; color: var(--text-3); margin-top: 1px; }

    /* Page shell */
    .stu-wrap { max-width: 1040px; margin: 0 auto; }
    .stu-head { margin-bottom: 22px; }
    .stu-title { font-size: 24px; font-weight: 800; color: var(--text-1); letter-spacing: -.01em; }
    .stu-sub { font-size: 13px; color: var(--text-3); margin-top: 4px; }
    .stu-row-main { flex: 1; min-width: 0; }
    .stu-empty-title { font-size: 14px; font-weight: 600; color: var(--text-2); }
    .stu-empty-sub { font-size: 12.5px; color: var(--text-3); margin-top: 4px; }

    .stu-grade-badge {
        padding: 3px 10px; border-radius: 20px;
        font-size: 11px; font-weight: 600;
        background: #f1f5f9; color: #475569;
        white-space: nowrap; flex-shrink: 0;
    }
    .stu-status-pill {
        padding: 3px 10px; border-radius: 20px;
        font-size: 11px; font-weight: 600; flex-shrink: 0;
    }
    .pill-approved { background: #dcfce7; color: #15803d; }
    .pill-pending   { background: #fef9c3; color: #854d0e; }
    .pill-rejected  { background: #fee2e2; color: #991b1b; }

    .stu-arrow {
        color: #d1d5db; flex-shrink: 0;
        transition: transform .18s, color .18s;
    }

    /* ── Empty / no-results states ── */
    .stu-empty, #stuNoResults {
        padding: 56px 20px; text-align: center;
    }
    #stuNoResults { display: none; }
    .stu-empty-icon {
        width: 54px; height: 54px;
        background: #f8faff; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 14px;
    }

    /* ════════════════════════════════════════════════
       STUDENT DETAIL MODAL
       Placed outside .main-content (same trick as
       #uploadModal) — AJAX partial swap won't touch it.
    ════════════════════════════════════════════════ */
    #stuModal {
        position: fixed !important;
        inset: 0 !important;
        z-index: 9999 !important;
        background: rgba(15,23,42,0) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 20px !important;
        pointer-events: none !important;
        opacity: 1 !important;
        transition: background 220ms cubic-bezier(0.23,1,0.32,1) !important;
    }
    #stuModal.is-open {
        background: rgba(15,23,42,.58) !important;
        backdrop-filter: blur(6px) !important;
        pointer-events: all !important;
    }
    #stuModal.is-closing {
        background: rgba(15,23,42,0) !important;
        transition: background 160ms cubic-bezier(0.23,1,0.32,1) !important;
    }

    #stuModal .sm-panel {
        background: #fff;
        border-radius: 22px;
        box-shadow: 0 24px 64px rgba(15,23,42,.18), 0 8px 24px rgba(15,23,42,.08);
        width: 100%; max-width: 660px;
        max-height: 92dvh;
        overflow: hidden;
        display: flex; flex-direction: column;
        opacity: 0;
        transform: scale(0.96) translateY(8px);
        transition: opacity 220ms cubic-bezier(0.23,1,0.32,1),
                    transform 280ms cubic-bezier(0.34,1.56,0.64,1);
        will-change: transform, opacity;
        border: 1.5px solid rgba(255,255,255,.06);
    }
    #stuModal.is-open   .sm-panel { opacity: 1; transform: scale(1) translateY(0); }
    #stuModal.is-closing .sm-panel {
        opacity: 0; transform: scale(0.97) translateY(4px);
        transition: opacity 160ms cubic-bezier(0.23,1,0.32,1),
                    transform 160ms cubic-bezier(0.23,1,0.32,1);
    }

    /* Hero strip */
    #stuModal .sm-hero {
        background: linear-gradient(135deg,#0F172A 0%,#1E293B 100%);
        padding: 26px 28px 22px;
        display: flex; align-items: flex-start; gap: 16px;
        flex-shrink: 0; position: relative; overflow: hidden;
    }
    #stuModal .sm-hero::before {
        content: ''; position: absolute;
        top: -50px; right: -50px;
        width: 180px; height: 180px; border-radius: 50%;
        background: rgba(37,99,235,.18); pointer-events: none;
    }
    #stuModal .sm-hero::after {
        content: ''; position: absolute;
        bottom: -30px; left: 28%;
        width: 120px; height: 120px; border-radius: 50%;
        background: rgba(37,99,235,.1); pointer-events: none;
    }

    #stuModal .sm-big-av {
        width: 62px; height: 62px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; font-weight: 800; color: #fff;
        flex-shrink: 0; border: 3px solid rgba(255,255,255,.18);
        position: relative; z-index: 1;
    }
    #stuModal .sm-hero-info { flex: 1; min-width: 0; position: relative; z-index: 1; }
    #stuModal .sm-hero-name {
        font-size: 19px; font-weight: 800; color: #fff;
        letter-spacing: -.02em; line-height: 1.25;
    }
    #stuModal .sm-hero-sub {
        font-size: 12px; color: #94a3b8; margin-top: 5px;   /* #64748b was too dark on the navy hero */
        display: flex; align-items: center; gap: 7px; flex-wrap: wrap;
    }
    #stuModal .sm-hero-pills { display: flex; gap: 7px; flex-wrap: wrap; margin-top: 10px; }
    #stuModal .sm-pill {
        padding: 4px 11px; border-radius: 20px;
        font-size: 11px; font-weight: 700;
        background: rgba(255,255,255,.09); color: #cbd5e1;
        border: 1px solid rgba(255,255,255,.1);
    }
    #stuModal .sm-pill.green  { background: rgba(34,197,94,.18); color: #4ade80; border-color: rgba(34,197,94,.25); }
    #stuModal .sm-pill.yellow { background: rgba(234,179,8,.18); color: #fde047; border-color: rgba(234,179,8,.25); }
    #stuModal .sm-pill.red    { background: rgba(239,68,68,.18); color: #fca5a5; border-color: rgba(239,68,68,.25); }

    #stuModal .sm-close {
        width: 33px; height: 33px; border-radius: 10px;
        background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; flex-shrink: 0; color: #cbd5e1;
        transition: background .15s, color .15s,
                    transform .15s cubic-bezier(0.34,1.56,0.64,1);
        position: relative; z-index: 1;
    }
    #stuModal .sm-close:hover {
        background: rgba(239,68,68,.22); border-color: rgba(239,68,68,.3);
        color: #fca5a5; transform: scale(1.08) rotate(8deg);
    }

    /* Scrollable body */
    #stuModal .sm-body {
        overflow-y: auto; overflow-x: hidden; flex: 1;
        scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent;
    }
    #stuModal .sm-body::-webkit-scrollbar { width: 5px; }
    #stuModal .sm-body::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 99px; }

    /* Section blocks inside modal */
    #stuModal .sm-sect {
        padding: 18px 28px;
        border-bottom: 1px solid #f1f5f9;
    }
    #stuModal .sm-sect:last-child { border-bottom: none; padding-bottom: 24px; }
    #stuModal .sm-sect-title {
        font-size: 10px; font-weight: 700; letter-spacing: .1em;
        text-transform: uppercase; color: #64748b;
        margin-bottom: 12px;
        display: flex; align-items: center; gap: 8px;
    }
    #stuModal .sm-sect-title::after { content: ''; flex: 1; height: 1px; background: #f1f5f9; }

    /* Detail grid */
    #stuModal .sm-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
    }
    #stuModal .sm-grid.cols-3 { grid-template-columns: repeat(3,1fr); }
    #stuModal .sm-grid.cols-1 { grid-template-columns: 1fr; }

    #stuModal .sm-item {
        background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 10px;
        padding: 10px 13px; transition: border-color .15s;
    }
    #stuModal .sm-item:hover { border-color: #dbeafe; }
    #stuModal .sm-item-label {
        font-size: 10px; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; color: #64748b; margin-bottom: 4px;
    }
    #stuModal .sm-item-val {
        font-size: 13px; font-weight: 600; color: #0F172A; word-break: break-word;
    }
    #stuModal .sm-item-val.muted { color: #94a3b8; font-weight: 500; }

    /* Grade table */
    #stuModal .sm-grade-wrap {
        border: 1.5px solid #f1f5f9; border-radius: 12px; overflow: hidden;
    }
    #stuModal .sm-grade-tbl { width: 100%; border-collapse: collapse; font-size: 12px; }
    #stuModal .sm-grade-tbl thead th {
        background: #f8faff; padding: 9px 13px;
        text-align: left; font-size: 10px; font-weight: 700;
        letter-spacing: .08em; text-transform: uppercase;
        color: #64748b; border-bottom: 1px solid #f1f5f9; white-space: nowrap;
    }
    #stuModal .sm-grade-tbl tbody tr {
        border-bottom: 1px solid #f9fafb; transition: background .12s;
    }
    #stuModal .sm-grade-tbl tbody tr:last-child { border-bottom: none; }
    #stuModal .sm-grade-tbl tbody tr:hover { background: #f8faff; }
    #stuModal .sm-grade-tbl tbody td { padding: 10px 13px; font-weight: 500; color: #374151; }
    #stuModal .sm-grade-tbl .gp  { color: #15803d; font-weight: 700; } /* passed  */
    #stuModal .sm-grade-tbl .gf  { color: #dc2626; font-weight: 700; } /* failed  */
    #stuModal .sm-grade-tbl .gnd { color: #94a3b8; }                    /* no data */
    #stuModal .sm-grade-tbl .gip { color: #64748b; font-weight: 600; }  /* in progress */
    #stuModal .sm-note { font-size: 11.5px; color: #64748b; margin-top: 8px; line-height: 1.5; }

    .stu-meta-grade { display: none; }
    @media (max-width: 640px) {
        /* compact 3-up stats; grade moves into the meta line so the name has room */
        .stu-stat-strip { gap: 10px; }
        .stu-stat-card { padding: 14px; flex-direction: column; align-items: flex-start; gap: 4px; }
        .stu-stat-icon { display: none; }
        .stu-stat-number { font-size: 22px; }
        .stu-search-wrap { flex-basis: 100%; }
        .stu-filter-select { flex: 1 1 140px; }
        .stu-row { padding: 12px 14px; gap: 12px; }
        .stu-grade-badge { display: none; }
        .stu-meta-grade { display: inline; }
        #stuModal .sm-grid    { grid-template-columns: 1fr; }
        #stuModal .sm-grid.cols-3 { grid-template-columns: 1fr 1fr; }
        #stuModal .sm-hero { padding: 18px 20px; }
        #stuModal .sm-sect { padding: 16px 20px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .fade-up { animation: none; }
        #stuModal .sm-panel { transition-duration: 80ms !important; }
    }
</style>

<div class="stu-wrap">

    {{-- ── Header ── --}}
    <div class="stu-head fade-up fade-up-1">
        <h1 class="stu-title">My Students</h1>
        <p class="stu-sub">Students in your classes and advisory section. Select a student to see their details and grades.</p>
    </div>

    {{-- ── Stat strip ── --}}
    <div class="stu-stat-strip fade-up fade-up-1">
        <div class="stu-stat-card">
            <div class="stu-stat-icon" style="background:var(--accent-lt);color:var(--accent);" aria-hidden="true">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <p class="stu-stat-number">{{ $students->count() }}</p>
                <p class="stu-stat-label">Total students</p>
            </div>
        </div>
        <div class="stu-stat-card">
            <div class="stu-stat-icon" style="background:var(--accent-lt);color:var(--accent);" aria-hidden="true">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <p class="stu-stat-number">{{ $students->pluck('section_id')->filter()->unique()->count() }}</p>
                <p class="stu-stat-label">Sections</p>
            </div>
        </div>
        <div class="stu-stat-card">
            <div class="stu-stat-icon" style="background:var(--accent-lt);color:var(--accent);" aria-hidden="true">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <p class="stu-stat-number">{{ $students->pluck('grade_level')->filter()->unique()->count() }}</p>
                <p class="stu-stat-label">Grade levels</p>
            </div>
        </div>
    </div>

    {{-- ── Toolbar ── --}}
    <div class="stu-toolbar fade-up fade-up-2">
        <div class="stu-search-wrap">
            <span class="stu-search-icon" aria-hidden="true">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="search" id="stuSearch" class="stu-search" placeholder="Search by name or LRN…"
                   autocomplete="off" aria-label="Search students by name or LRN">
        </div>

        <select class="stu-filter-select" id="stuSectionFilter" aria-label="Filter by section">
            <option value="">All sections</option>
            @foreach($students->pluck('section')->filter()->unique('id')->sortBy(fn ($s) => sprintf('%02d|%s', (int) $s->grade_level, $s->name)) as $sec)
                <option value="{{ $sec->id }}">Grade {{ $sec->grade_level }} – {{ $sec->name }}</option>
            @endforeach
        </select>

        <select class="stu-filter-select" id="stuGradeFilter" aria-label="Filter by grade level">
            <option value="">All grades</option>
            @foreach($students->pluck('grade_level')->filter()->unique()->sortBy(fn ($g) => (int) $g)->values() as $gl)
                <option value="{{ $gl }}">Grade {{ $gl }}</option>
            @endforeach
        </select>

        <span class="stu-count-badge" id="stuCountBadge" aria-live="polite">
            {{ $students->count() }} {{ Str::plural('student', $students->count()) }}
        </span>
    </div>

    {{-- ── Student list ── --}}
    <div class="stu-table-card fade-up fade-up-3">
        @forelse($students as $student)
            <div class="stu-row" role="button" tabindex="0"
                 data-id="{{ $student->id }}"
                 data-search="{{ mb_strtolower($student->name . ' ' . $student->lrn) }}"
                 data-section="{{ $student->section_id }}"
                 data-grade="{{ $student->grade_level }}"
                 aria-label="View details for {{ $student->name }}">

                <div class="stu-avatar av-{{ $student->id % 8 }}" aria-hidden="true">{{ mb_strtoupper(mb_substr($student->name, 0, 1)) }}</div>

                <div class="stu-row-main">
                    <p class="stu-name">{{ $student->name }}</p>
                    <p class="stu-meta">
                        <span class="stu-meta-grade">Grade {{ $student->grade_level ?? '—' }} &nbsp;·&nbsp;</span>
                        {{ $student->section->name ?? '—' }}
                        @if($student->lrn) &nbsp;·&nbsp; LRN {{ $student->lrn }} @endif
                    </p>
                </div>

                <span class="stu-grade-badge">Grade {{ $student->grade_level ?? '—' }}</span>
                @if($details[$student->id]['adviser'] ?? false)
                    <span class="stu-status-pill pill-approved">My advisory</span>
                @endif

                <svg class="stu-arrow" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </div>
        @empty
            <div class="stu-empty">
                <div class="stu-empty-icon" aria-hidden="true">
                    <svg width="24" height="24" fill="none" stroke="#94a3b8" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <p class="stu-empty-title">No students yet.</p>
                <p class="stu-empty-sub">Students appear here once they are enrolled in your sections.</p>
            </div>
        @endforelse

        <div id="stuNoResults">
            <div class="stu-empty-icon" style="margin:0 auto 12px;" aria-hidden="true">
                <svg width="22" height="22" fill="none" stroke="#94a3b8" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <p class="stu-empty-title">No students match your search.</p>
            <p class="stu-empty-sub">Try a different name, LRN, or filter.</p>
        </div>
    </div>
</div>

{{-- ══════════════ STUDENT DETAIL MODAL ══════════════ --}}
<div id="stuModal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="smName">
    <div class="sm-panel">
        <div class="sm-hero">
            <div class="sm-big-av av-0" id="smAv" aria-hidden="true">A</div>
            <div class="sm-hero-info">
                <h2 class="sm-hero-name" id="smName">—</h2>
                <div class="sm-hero-sub">
                    <span id="smSubSection">—</span>
                    <span aria-hidden="true">·</span>
                    <span id="smSubGrade">—</span>
                </div>
                <div class="sm-hero-pills" id="smPills"></div>
            </div>
            <button type="button" class="sm-close" id="smClose" aria-label="Close">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="sm-body">
            <div class="sm-sect">
                <div class="sm-sect-title">Student</div>
                <div class="sm-grid cols-3">
                    <div class="sm-item"><div class="sm-item-label">LRN</div><div class="sm-item-val" id="smLrn">—</div></div>
                    <div class="sm-item"><div class="sm-item-label">Section</div><div class="sm-item-val" id="smSection">—</div></div>
                    <div class="sm-item"><div class="sm-item-label">Grade level</div><div class="sm-item-val" id="smGrade">—</div></div>
                </div>
            </div>

            <div class="sm-sect">
                <div class="sm-sect-title">Account</div>
                <div class="sm-grid">
                    <div class="sm-item"><div class="sm-item-label">Email</div><div class="sm-item-val" id="smEmail">—</div></div>
                    <div class="sm-item"><div class="sm-item-label">Activated</div><div class="sm-item-val" id="smCreated">—</div></div>
                </div>
            </div>

            <div class="sm-sect">
                <div class="sm-sect-title">Grades · SY {{ $schoolYear }}</div>
                <div class="sm-grade-wrap">
                    <table class="sm-grade-tbl">
                        <thead>
                            <tr><th scope="col">Subject</th><th scope="col">Q1</th><th scope="col">Q2</th><th scope="col">Q3</th><th scope="col">Q4</th><th scope="col">Average</th><th scope="col">Remarks</th></tr>
                        </thead>
                        <tbody id="smGradesTbody"></tbody>
                    </table>
                </div>
                <p class="sm-note" id="smGradesNote"></p>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const DATA  = @json($details);
    const modal = document.getElementById('stuModal');
    const PASS  = {{ \App\Models\Grade::PASSING }};
    let closing = false;
    let opener  = null;   // row to focus again after closing

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const set = (id, text) => { document.getElementById(id).textContent = text; };

    function open(id, row) {
        const s = DATA[id];
        if (!s) return;
        opener = row || null;

        const av = document.getElementById('smAv');
        av.className   = 'sm-big-av av-' + (id % 8);
        av.textContent = s.name.charAt(0).toUpperCase();

        set('smName', s.name);
        set('smSubSection', s.section);
        set('smSubGrade', 'Grade ' + s.grade_level);
        document.getElementById('smPills').innerHTML =
            '<span class="sm-pill green">Enrolled</span>' + (s.adviser ? '<span class="sm-pill">My advisory</span>' : '');

        set('smLrn', s.lrn || '—');
        set('smSection', s.section);
        set('smGrade', 'Grade ' + s.grade_level);
        set('smEmail', s.email || '—');
        set('smCreated', s.registered);

        const tbody = document.getElementById('smGradesTbody');
        if (!s.grades.length) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:22px;color:#64748b;font-size:12px;">No grades recorded yet this school year.</td></tr>';
        } else {
            const cell = (v) => v === null ? '<td class="gnd">—</td>' : `<td class="${v >= PASS ? 'gp' : 'gf'}">${Number(v).toFixed(2)}</td>`;
            tbody.innerHTML = s.grades.map(g => {
                const remarks = g.average === null ? '<td class="gnd">—</td>'
                    : !g.complete ? '<td class="gip">In progress</td>'
                    : `<td class="${g.passed ? 'gp' : 'gf'}">${g.passed ? 'Passed' : 'Failed'}</td>`;
                return `<tr>
                    <td style="font-weight:600;color:#0F172A;">${esc(g.subject)}</td>
                    ${cell(g.quarters.Q1)}${cell(g.quarters.Q2)}${cell(g.quarters.Q3)}${cell(g.quarters.Q4)}
                    ${cell(g.average)}${remarks}
                </tr>`;
            }).join('');
        }
        set('smGradesNote', s.adviser
            ? 'All subjects (you are this student\'s adviser). Average = mean of the quarters recorded so far.'
            : 'Only the subjects you teach this student. Average = mean of the quarters recorded so far.');

        document.body.style.overflow = 'hidden';
        modal.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(() => {
            modal.classList.add('is-open');
            document.getElementById('smClose').focus();
        });
    }

    function close() {
        if (closing || !modal.classList.contains('is-open')) return;
        closing = true;
        modal.classList.add('is-closing');
        modal.classList.remove('is-open');
        setTimeout(() => {
            modal.classList.remove('is-closing');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            closing = false;
            if (opener && document.body.contains(opener)) opener.focus();
        }, 160);
    }

    document.querySelectorAll('.stu-row').forEach(row => {
        const id = Number(row.dataset.id);
        row.addEventListener('click', () => open(id, row));
        row.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(id, row); }
        });
    });
    document.getElementById('smClose').addEventListener('click', close);
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });

    /* ── Client-side filter (name or LRN, section, grade) ── */
    function filter() {
        const q       = document.getElementById('stuSearch').value.toLowerCase().trim();
        const section = document.getElementById('stuSectionFilter').value;
        const grade   = document.getElementById('stuGradeFilter').value;
        const rows    = document.querySelectorAll('.stu-row');
        let visible   = 0;

        rows.forEach(row => {
            const show = (!q || row.dataset.search.includes(q))
                && (!section || row.dataset.section === section)
                && (!grade   || row.dataset.grade   === grade);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        document.getElementById('stuNoResults').style.display = (rows.length && !visible) ? 'block' : 'none';
        document.getElementById('stuCountBadge').textContent = visible + (visible === 1 ? ' student' : ' students');
    }
    document.getElementById('stuSearch').addEventListener('input', filter);
    document.getElementById('stuSectionFilter').addEventListener('change', filter);
    document.getElementById('stuGradeFilter').addEventListener('change', filter);
})();
</script>
@endsection
