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
    .stu-meta { font-size: 11px; color: #94a3b8; margin-top: 1px; }

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
        font-size: 12px; color: #64748b; margin-top: 5px;
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
        cursor: pointer; flex-shrink: 0; color: #64748b;
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
        text-transform: uppercase; color: #94a3b8;
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
        text-transform: uppercase; color: #94a3b8; margin-bottom: 4px;
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
    #stuModal .sm-grade-tbl .gnd { color: #d1d5db; }                    /* no data */

    @media (max-width: 640px) {
        .stu-stat-strip { grid-template-columns: 1fr; }
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

<div class="max-w-5xl mx-auto">

    {{-- ── Header ── --}}
    <div class="mb-6 fade-up fade-up-1">
        <h1 class="text-2xl font-bold text-gray-800">My Students</h1>
        <p class="text-sm text-gray-400 mt-1">
            All students enrolled in your sections. Click any row to view full details.
        </p>
    </div>

    {{-- ── Stat strip ── --}}
    <div class="stu-stat-strip fade-up fade-up-1">

        <div class="stu-stat-card">
            <div class="stu-stat-icon" style="background:#eff6ff;">
                <svg width="20" height="20" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
                           M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857
                           m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="stu-stat-number">{{ $students->count() }}</p>
                <p class="stu-stat-label">Total Students</p>
            </div>
        </div>

        <div class="stu-stat-card">
            <div class="stu-stat-icon" style="background:#faf5ff;">
                <svg width="20" height="20" fill="none" stroke="#a855f7" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5
                           m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1
                           0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <p class="stu-stat-number">{{ $students->pluck('section_id')->unique()->count() }}</p>
                <p class="stu-stat-label">Sections</p>
            </div>
        </div>

        <div class="stu-stat-card">
            <div class="stu-stat-icon" style="background:#f0fdf4;">
                <svg width="20" height="20" fill="none" stroke="#22c55e" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0
                           0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0
                           0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <p class="stu-stat-number">{{ $students->pluck('grade_level')->unique()->filter()->count() }}</p>
                <p class="stu-stat-label">Grade Levels</p>
            </div>
        </div>

    </div>

    {{-- ── Toolbar ── --}}
    <div class="stu-toolbar fade-up fade-up-2">

        <div class="stu-search-wrap">
            <span class="stu-search-icon">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" id="stuSearch" class="stu-search"
                placeholder="Search by name…" autocomplete="off"
                oninput="stuFilter()">
        </div>

        {{-- Section filter — built from actual data --}}
        <select class="stu-filter-select" id="stuSectionFilter" onchange="stuFilter()">
            <option value="">All Sections</option>
            @foreach($students->pluck('section')->unique('id')->filter()->sortBy('name') as $sec)
                <option value="{{ $sec->id }}">{{ $sec->name }}</option>
            @endforeach
        </select>

        {{-- Grade level filter — built from actual data --}}
        <select class="stu-filter-select" id="stuGradeFilter" onchange="stuFilter()">
            <option value="">All Grades</option>
            @foreach($students->pluck('grade_level')->unique()->filter()->sort()->values() as $gl)
                <option value="{{ $gl }}">Grade {{ $gl }}</option>
            @endforeach
        </select>

        <span class="stu-count-badge" id="stuCountBadge">
            {{ $students->count() }} {{ Str::plural('student', $students->count()) }}
        </span>

    </div>

    {{-- ── Student list ── --}}
    <div class="stu-table-card fade-up fade-up-3">

        @forelse($students as $student)
        @php
            $avIdx       = $student->id % 8;
            $statusLabel = match(strtolower($student->status ?? '')) {
                'pending'  => 'Pending',
                'rejected' => 'Rejected',
                default    => 'Enrolled',
            };
            $statusClass = match(strtolower($student->status ?? '')) {
                'pending'  => 'pill-pending',
                'rejected' => 'pill-rejected',
                default    => 'pill-approved',
            };
        @endphp

        <div class="stu-row"
             data-id="{{ $student->id }}"
             data-name="{{ strtolower($student->name) }}"
             data-section="{{ $student->section_id }}"
             data-grade="{{ $student->grade_level }}"
             onclick="stuOpen({{ $student->id }})"
             onkeydown="if(event.key==='Enter'||event.key===' '){stuOpen({{ $student->id }});event.preventDefault();}"
             tabindex="0"
             role="button"
             aria-label="View details for {{ $student->name }}">

            <div class="stu-avatar av-{{ $avIdx }}">
                {{ strtoupper(substr($student->name, 0, 1)) }}
            </div>

            <div class="flex-1 min-w-0">
                <p class="stu-name">{{ $student->name }}</p>
                <p class="stu-meta">
                    {{ $student->section->name ?? '—' }}
                    &nbsp;·&nbsp;
                    Grade {{ $student->grade_level ?? '—' }}
                </p>
            </div>

            <span class="stu-grade-badge">Grade {{ $student->grade_level ?? '—' }}</span>
            <span class="stu-status-pill {{ $statusClass }}">{{ $statusLabel }}</span>

            <svg class="stu-arrow" width="15" height="15" fill="none"
                stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>

        </div>
        @empty
        <div class="stu-empty">
            <div class="stu-empty-icon">
                <svg width="24" height="24" fill="none" stroke="#d1d5db" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
                           M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857
                           m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-gray-400 text-sm font-medium">No students assigned yet.</p>
            <p class="text-gray-300 text-xs mt-1">Students will appear here once enrolled in your sections.</p>
        </div>
        @endforelse

        {{-- No search results --}}
        <div id="stuNoResults">
            <div class="stu-empty-icon" style="margin:0 auto 12px;">
                <svg width="22" height="22" fill="none" stroke="#d1d5db" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <p class="text-gray-400 text-sm font-medium">No students match your search.</p>
            <p class="text-gray-300 text-xs mt-1">Try a different name or filter.</p>
        </div>

    </div>

    @if(method_exists($students, 'links') && $students->total() > $students->perPage())
    <div class="mt-4">{{ $students->links() }}</div>
    @endif

</div>

{{-- ══════════════════════════════════════════════
     STUDENT DETAIL MODAL
     Outside .main-content — AJAX swap safe.
══════════════════════════════════════════════ --}}
<div id="stuModal"
     role="dialog" aria-modal="true" aria-hidden="true"
     onclick="stuOverlayClick(event)">

    <div class="sm-panel">

        {{-- Hero --}}
        <div class="sm-hero">
            <div class="sm-big-av av-0" id="smAv">A</div>
            <div class="sm-hero-info">
                <p class="sm-hero-name" id="smName">—</p>
                <div class="sm-hero-sub">
                    <span id="smSubSection">—</span>
                    <span style="color:#334155;">·</span>
                    <span id="smSubGrade">—</span>
                </div>
                <div class="sm-hero-pills" id="smPills"></div>
            </div>
            <button type="button" class="sm-close" onclick="stuClose()" aria-label="Close">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="sm-body">

            {{-- Enrollment --}}
            <div class="sm-sect">
                <div class="sm-sect-title">Enrollment</div>
                <div class="sm-grid cols-3">
                    <div class="sm-item">
                        <div class="sm-item-label">Section</div>
                        <div class="sm-item-val" id="smSection">—</div>
                    </div>
                    <div class="sm-item">
                        <div class="sm-item-label">Grade Level</div>
                        <div class="sm-item-val" id="smGrade">—</div>
                    </div>
                    <div class="sm-item">
                        <div class="sm-item-label">Status</div>
                        <div class="sm-item-val" id="smStatus">—</div>
                    </div>
                </div>
            </div>

            {{-- Account info --}}
            <div class="sm-sect">
                <div class="sm-sect-title">Account</div>
                <div class="sm-grid">
                    <div class="sm-item">
                        <div class="sm-item-label">Email</div>
                        <div class="sm-item-val" id="smEmail">—</div>
                    </div>
                    <div class="sm-item">
                        <div class="sm-item-label">Registered</div>
                        <div class="sm-item-val" id="smCreated">—</div>
                    </div>
                </div>
            </div>

            {{-- Grade summary --}}
            <div class="sm-sect">
                <div class="sm-sect-title">Grade Summary</div>
                <div class="sm-grade-wrap">
                    <table class="sm-grade-tbl">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Q1</th>
                                <th>Q2</th>
                                <th>Q3</th>
                                <th>Q4</th>
                                <th>Final</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="smGradesTbody">
                            <tr>
                                <td colspan="7" style="text-align:center;padding:22px;color:#94a3b8;font-size:12px;">
                                    No grades recorded yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>{{-- /.sm-body --}}
    </div>{{-- /.sm-panel --}}
</div>{{-- /#stuModal --}}

{{-- ── Prepare student data in PHP before injecting into JS ── --}}
@php
    $studentJson = $students->keyBy('id')->map(function ($s) {

        // Grades grouped by subject name → quarters
        // Grades not loaded yet (no Grade model data) — safe empty default
        $gradeMap = [];
        if ($s->relationLoaded('grades')) {
            foreach ($s->grades as $g) {
                $sub = optional($g->subject)->name ?? 'Unknown Subject';
                if (!isset($gradeMap[$sub])) {
                    $gradeMap[$sub] = [
                        'Q1' => null, 'Q2' => null,
                        'Q3' => null, 'Q4' => null,
                        'final' => null, 'remarks' => null,
                    ];
                }
                if (isset($gradeMap[$sub][$g->quarter])) {
                    $gradeMap[$sub][$g->quarter] = $g->final_grade;
                }
                $gradeMap[$sub]['final']   = $g->final_grade;
                $gradeMap[$sub]['remarks'] = $g->remarks;
            }
        }

        return [
            'id'          => $s->id,
            'name'        => $s->name,
            'email'       => $s->email,
            'section'     => optional($s->section)->name ?? '—',
            'grade_level' => $s->grade_level ?? '—',
            'status'      => ucfirst($s->status ?? 'enrolled'),
            'created_at'  => $s->created_at
                                ? \Carbon\Carbon::parse($s->created_at)->format('M d, Y')
                                : '—',
            'grades'      => $gradeMap,
        ];
    })->values()->keyBy('id');
@endphp

<script>
(function () {

    /* ── Data map keyed by student id ── */
    const DATA = @json($studentJson);

    /* ── Modal refs ── */
    const modal  = document.getElementById('stuModal');
    let closing  = false;

    /* ── Open ── */
    window.stuOpen = function (id) {
        const s = DATA[id];
        if (!s) return;

        /* hero */
        const av = document.getElementById('smAv');
        av.className   = 'sm-big-av av-' + (id % 8);
        av.textContent = s.name.charAt(0).toUpperCase();

        document.getElementById('smName').textContent       = s.name;
        document.getElementById('smSubSection').textContent = s.section;
        document.getElementById('smSubGrade').textContent   = 'Grade ' + s.grade_level;

        /* pills */
        const statusColor = s.status.toLowerCase() === 'approved' || s.status.toLowerCase() === 'enrolled'
            ? 'green'
            : s.status.toLowerCase() === 'pending' ? 'yellow' : 'red';
        document.getElementById('smPills').innerHTML =
            `<span class="sm-pill ${statusColor}">${s.status}</span>`;

        /* enrollment */
        document.getElementById('smSection').textContent = s.section;
        document.getElementById('smGrade').textContent   = 'Grade ' + s.grade_level;
        document.getElementById('smStatus').textContent  = s.status;

        /* account */
        document.getElementById('smEmail').textContent   = s.email;
        document.getElementById('smCreated').textContent = s.created_at;

        /* grades */
        const tbody    = document.getElementById('smGradesTbody');
        const subjects = Object.keys(s.grades);

        if (!subjects.length) {
            tbody.innerHTML =
                `<tr><td colspan="7" style="text-align:center;padding:22px;
                    color:#94a3b8;font-size:12px;">No grades recorded yet.</td></tr>`;
        } else {
            tbody.innerHTML = subjects.map(sub => {
                const g = s.grades[sub];

                function gcell(val) {
                    if (val === null || val === undefined)
                        return `<td class="gnd">—</td>`;
                    return `<td class="${val >= 75 ? 'gp' : 'gf'}">${val}</td>`;
                }

                const finalCls = g.final === null ? 'gnd'
                               : g.final >= 75    ? 'gp' : 'gf';
                const remCls   = !g.remarks ? 'gnd'
                               : g.remarks.toLowerCase() === 'passed' ? 'gp' : 'gf';

                return `<tr>
                    <td style="font-weight:600;color:#0F172A;">${sub}</td>
                    ${gcell(g.Q1)}${gcell(g.Q2)}${gcell(g.Q3)}${gcell(g.Q4)}
                    <td class="${finalCls}">${g.final ?? '—'}</td>
                    <td class="${remCls}">${g.remarks ?? '—'}</td>
                </tr>`;
            }).join('');
        }

        /* open */
        document.body.style.overflow = 'hidden';
        modal.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(() => modal.classList.add('is-open'));
    };

    /* ── Close ── */
    window.stuClose = function () {
        if (closing) return;
        closing = true;
        modal.classList.add('is-closing');
        modal.classList.remove('is-open');
        setTimeout(() => {
            modal.classList.remove('is-closing');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            closing = false;
        }, 160);
    };

    window.stuOverlayClick = function (e) {
        if (e.target === modal) stuClose();
    };

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) stuClose();
    });

    /* ── Client-side filter ── */
    window.stuFilter = function () {
        const q       = document.getElementById('stuSearch').value.toLowerCase().trim();
        const section = document.getElementById('stuSectionFilter').value;
        const grade   = document.getElementById('stuGradeFilter').value;

        const rows = document.querySelectorAll('.stu-row');
        let visible = 0;

        rows.forEach(row => {
            const matchQ = !q       || row.dataset.name.includes(q);
            const matchS = !section || row.dataset.section === section;
            const matchG = !grade   || row.dataset.grade   === grade;
            const show   = matchQ && matchS && matchG;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        document.getElementById('stuNoResults').style.display =
            (rows.length && !visible) ? 'block' : 'none';

        document.getElementById('stuCountBadge').textContent =
            visible + (visible === 1 ? ' student' : ' students');
    };

})();
</script>
@endsection