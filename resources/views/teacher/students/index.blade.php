@extends('layouts.teacher')
@section('title', 'My students')

@section('content')
<style>
    /* ════════════════════════════════════════════════
       PAGE — MY STUDENTS
       Design tokens match layouts/teacher.blade.php.
    ════════════════════════════════════════════════ */

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
    }
    .stu-stat-icon {
        width: 42px; height: 42px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
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
        font-size: 13px; font-weight: 800;
        background: var(--accent-lt); color: var(--accent);
        flex-shrink: 0;
    }

    .stu-name {
        font-size: 13px; font-weight: 700; color: #0F172A;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .stu-meta { font-size: 12px; color: var(--text-3); margin-top: 1px; display: flex; flex-wrap: wrap; gap: 0 12px; }

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
        background: rgba(15,23,42,.45) !important;
        pointer-events: all !important;
    }
    #stuModal.is-closing {
        background: rgba(15,23,42,0) !important;
        transition: background 120ms ease !important;
    }

    #stuModal .sm-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r-lg);
        box-shadow: 0 12px 32px rgba(15,23,42,.14);
        width: 100%; max-width: 640px;
        max-height: 92dvh;
        overflow: hidden;
        display: flex; flex-direction: column;
        opacity: 0;
        transform: translateY(6px);
        transition: opacity 160ms ease, transform 160ms ease;
    }
    #stuModal.is-open    .sm-panel { opacity: 1; transform: none; }
    #stuModal.is-closing .sm-panel { opacity: 0; transition: opacity 120ms ease; }

    #stuModal .sm-head {
        display: flex; align-items: center; gap: 14px;
        padding: 18px 22px;
        border-bottom: 1px solid var(--border);
        flex-shrink: 0;
    }
    #stuModal .sm-big-av {
        width: 46px; height: 46px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px; font-weight: 800;
        background: var(--accent-lt); color: var(--accent);
        flex-shrink: 0;
    }
    #stuModal .sm-head-info { flex: 1; min-width: 0; }
    #stuModal .sm-name { font-size: 17px; font-weight: 800; color: var(--text-1); line-height: 1.3; }
    #stuModal .sm-sub {
        display: flex; align-items: center; flex-wrap: wrap; gap: 4px 10px;
        font-size: 13px; color: var(--text-2); margin-top: 3px;
    }
    #stuModal .sm-pill { padding: 2px 9px; border-radius: 6px; font-size: 12px; font-weight: 700; background: #F1F5F9; color: var(--text-2); }
    #stuModal .sm-pill.green { background: #DCFCE7; color: #15803D; }
    #stuModal .sm-close {
        width: 38px; height: 38px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        background: transparent; border: 0; color: var(--text-2);
        cursor: pointer; flex-shrink: 0;
        transition: background-color .15s ease, color .15s ease;
    }
    #stuModal .sm-close:hover { background: #F1F5F9; color: var(--text-1); }
    #stuModal .sm-close:focus-visible { outline: 2px solid var(--accent); outline-offset: 1px; }

    #stuModal .sm-body { overflow-y: auto; overflow-x: hidden; flex: 1; }
    #stuModal .sm-sect { padding: 16px 22px; }
    #stuModal .sm-sect + .sm-sect { border-top: 1px solid var(--border); }
    #stuModal .sm-sect-title { font-size: 14px; font-weight: 800; color: var(--text-1); margin: 0 0 10px; }

    #stuModal .sm-dl { display: grid; grid-template-columns: 1fr 1.7fr 1fr; gap: 12px 20px; margin: 0; }
    #stuModal .sm-dl dt { font-size: 12.5px; color: var(--text-2); }
    #stuModal .sm-dl dd { margin: 2px 0 0; font-size: 14px; font-weight: 600; color: var(--text-1); overflow-wrap: anywhere; }

    #stuModal .sm-grade-wrap { overflow-x: auto; }
    #stuModal .sm-grade-tbl { width: 100%; min-width: 520px; border-collapse: collapse; font-size: 13px; font-variant-numeric: tabular-nums; }
    #stuModal .sm-grade-tbl thead th {
        padding: 8px 10px 8px 0;
        text-align: left; font-size: 12.5px; font-weight: 700;
        color: var(--text-2); border-bottom: 1px solid var(--border); white-space: nowrap;
    }
    #stuModal .sm-grade-tbl tbody tr + tr td { border-top: 1px solid var(--border); }
    #stuModal .sm-grade-tbl tbody td { padding: 9px 10px 9px 0; font-weight: 500; color: var(--text-1); }
    #stuModal .sm-grade-tbl .gp  { color: #15803d; font-weight: 700; } /* passed  */
    #stuModal .sm-grade-tbl .gf  { color: #dc2626; font-weight: 700; } /* failed  */
    #stuModal .sm-grade-tbl .gnd { color: #94a3b8; }                    /* no data */
    #stuModal .sm-grade-tbl .gip { color: var(--text-2); font-weight: 600; }  /* in progress */
    #stuModal .sm-note { font-size: 12.5px; color: var(--text-2); margin-top: 10px; line-height: 1.5; }

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
        #stuModal .sm-dl { grid-template-columns: 1fr; gap: 10px; }
        #stuModal .sm-head, #stuModal .sm-sect { padding-left: 16px; padding-right: 16px; }
    }
    @media (prefers-reduced-motion: reduce) {
        #stuModal .sm-panel { transition-duration: 80ms !important; }
    }
</style>

<div class="stu-wrap">

    {{-- ── Header ── --}}
    <div class="stu-head">
        <h1 class="stu-title">My students</h1>
        <p class="stu-sub">Students in your classes and advisory section. Select a student to see their details and grades.</p>
    </div>

    {{-- ── Stat strip ── --}}
    <div class="stu-stat-strip">
        <div class="stu-stat-card">
            <div class="stu-stat-icon" style="background:var(--accent-lt);color:var(--accent);" aria-hidden="true">
                <x-ui-icon name="students" />
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
    <div class="stu-toolbar">
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
    <div class="stu-table-card">
        @forelse($students as $student)
            <div class="stu-row" role="button" tabindex="0"
                 data-id="{{ $student->id }}"
                 data-search="{{ mb_strtolower($student->name . ' ' . $student->lrn) }}"
                 data-section="{{ $student->section_id }}"
                 data-grade="{{ $student->grade_level }}"
                 aria-label="View details for {{ $student->name }}">

                <div class="stu-avatar" aria-hidden="true">{{ $student->initials() }}</div>

                <div class="stu-row-main">
                    <p class="stu-name">{{ $student->name }}</p>
                    <p class="stu-meta">
                        <span class="stu-meta-grade">Grade {{ $student->grade_level ?? '—' }}</span>
                        <span>{{ $student->section->name ?? 'No section' }}</span>
                        @if($student->lrn)<span>LRN {{ $student->lrn }}</span>@endif
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
        <div class="sm-head">
            <div class="sm-big-av" id="smAv" aria-hidden="true"></div>
            <div class="sm-head-info">
                <h2 class="sm-name" id="smName">—</h2>
                <div class="sm-sub"><span id="smSub">—</span><span id="smPills" style="display:contents;"></span></div>
            </div>
            <button type="button" class="sm-close" id="smClose" aria-label="Close">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="sm-body">
            <div class="sm-sect">
                <dl class="sm-dl">
                    <div><dt>LRN</dt><dd id="smLrn">—</dd></div>
                    <div><dt>Email</dt><dd id="smEmail">—</dd></div>
                    <div><dt>Activated</dt><dd id="smCreated">—</dd></div>
                </dl>
            </div>

            <div class="sm-sect">
                <h3 class="sm-sect-title">Grades for school year {{ $schoolYear }}</h3>
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

        set('smAv', s.initials);
        set('smName', s.name);
        set('smSub', 'Grade ' + s.grade_level + ' ' + s.section);
        document.getElementById('smPills').innerHTML =
            '<span class="sm-pill green">Enrolled</span>' + (s.adviser ? '<span class="sm-pill">My advisory</span>' : '');

        set('smLrn', s.lrn || '—');
        set('smEmail', s.email || '—');
        set('smCreated', s.registered);

        const tbody = document.getElementById('smGradesTbody');
        if (!s.grades.length) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px 0;color:var(--text-2);font-size:13px;">No grades recorded yet this school year.</td></tr>';
        } else {
            const cell = (v) => v === null ? '<td class="gnd">—</td>' : `<td class="${v >= PASS ? 'gp' : 'gf'}">${Number(v).toFixed(2)}</td>`;
            tbody.innerHTML = s.grades.map(g => {
                const remarks = g.average === null ? '<td class="gnd">—</td>'
                    : !g.complete ? '<td class="gip">In progress</td>'
                    : `<td class="${g.passed ? 'gp' : 'gf'}">${g.passed ? 'Passed' : 'Failed'}</td>`;
                return `<tr>
                    <td style="font-weight:600;">${esc(g.subject)}</td>
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
