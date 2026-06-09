@extends('layouts.admin')
@section('title', $user->name)

@section('content')
<style>
    .up-page {
        max-width: 860px;
        margin: 0 auto;
        animation: up-fadein 0.32s cubic-bezier(0.22,1,0.36,1) both;
    }
    @keyframes up-fadein {
        from { opacity:0; transform:translateY(10px); }
        to   { opacity:1; transform:translateY(0); }
    }

    /* ── Back link ── */
    .up-back {
        display: inline-flex; align-items: center; gap: 0.4rem;
        font-size: 13px; font-weight: 500; color: var(--slate-400);
        text-decoration: none; margin-bottom: 1.5rem;
        transition: color 0.15s;
    }
    .up-back:hover { color: var(--blue-600); }
    .up-back svg { width: 14px; height: 14px; }

    /* ── Hero card ── */
    .up-hero {
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-xl);
        box-shadow: var(--shadow-md);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    /* Colored top strip */
    .up-hero-strip {
        height: 5px;
        background: linear-gradient(90deg, var(--blue-500), var(--blue-300));
    }

    .up-hero-body {
        padding: 1.75rem 2rem;
        display: flex; align-items: flex-start;
        justify-content: space-between; gap: 1.5rem;
        flex-wrap: wrap;
    }

    .up-hero-left { display: flex; align-items: center; gap: 1.25rem; }

    /* Avatar */
    .up-avatar {
        width: 64px; height: 64px;
        border-radius: var(--r-lg);
        display: flex; align-items: center; justify-content: center;
        font-family: var(--font-display);
        font-size: 22px; font-weight: 700; color: #fff;
        flex-shrink: 0;
        box-shadow: 0 4px 16px rgba(36,120,228,0.3);
    }
    .up-avatar.student { background: linear-gradient(135deg, #0d9488, #0891b2); }
    .up-avatar.teacher { background: linear-gradient(135deg, var(--blue-500), var(--blue-700)); }
    .up-avatar.parent  { background: linear-gradient(135deg, #d97706, #b45309); }

    .up-hero-name {
        font-family: var(--font-display);
        font-size: 1.3rem; font-weight: 700;
        color: var(--slate-900); letter-spacing: -0.02em;
        margin: 0 0 0.2rem;
    }
    .up-hero-email {
        font-size: 13px; color: var(--slate-500); margin: 0 0 0.5rem;
    }
    .up-hero-meta {
        display: flex; align-items: center; gap: 0.5rem;
        flex-wrap: wrap;
    }

    /* Pills */
    .up-pill {
        display: inline-flex; align-items: center; gap: 0.3rem;
        padding: 0.2rem 0.7rem;
        border-radius: var(--r-full);
        font-size: 11.5px; font-weight: 700;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .up-pill::before {
        content: ''; width: 5px; height: 5px;
        border-radius: 50%; flex-shrink: 0;
    }
    .up-pill-role-student { background: #e0f2f1; color: #0f766e; border-color: #5eead4; }
    .up-pill-role-student::before { background: #0d9488; }
    .up-pill-role-teacher { background: var(--blue-50); color: var(--blue-700); border-color: var(--blue-200); }
    .up-pill-role-teacher::before { background: var(--blue-500); }
    .up-pill-role-parent  { background: #fffbeb; color: #b45309; border-color: #fcd34d; }
    .up-pill-role-parent::before  { background: #d97706; }
    .up-pill-approved { background: #ecfdf5; color: #047857; border-color: #6ee7b7; }
    .up-pill-approved::before { background: #10b981; }
    .up-pill-pending  { background: #fffbeb; color: #b45309; border-color: #fcd34d; }
    .up-pill-pending::before  { background: #f59e0b; }
    .up-pill-rejected { background: #fef2f2; color: #b91c1c; border-color: #fca5a5; }
    .up-pill-rejected::before { background: #ef4444; }

    /* Registered date chip */
    .up-date-chip {
        font-size: 11.5px; color: var(--slate-400);
        display: flex; align-items: center; gap: 0.3rem;
    }
    .up-date-chip svg { width: 12px; height: 12px; }

    /* ── Info grid ── */
    .up-hero-info {
        padding: 0 2rem 1.75rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
        border-top: 1px solid var(--border-default);
        padding-top: 1.25rem;
        margin-top: 0.25rem;
    }

    .up-info-item {}
    .up-info-label {
        font-size: 10.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.08em;
        color: var(--slate-400); margin-bottom: 3px;
    }
    .up-info-value {
        font-size: 13.5px; font-weight: 600;
        color: var(--slate-800);
    }
    .up-info-value.muted { color: var(--slate-400); font-weight: 400; font-style: italic; }

    /* ── Approve/Reject buttons ── */
    .up-actions {
        padding: 1.25rem 2rem 1.75rem;
        border-top: 1px solid var(--border-default);
        display: flex; gap: 0.75rem; flex-wrap: wrap;
    }
    .up-btn {
        display: inline-flex; align-items: center; gap: 0.5rem;
        padding: 0.6rem 1.25rem;
        border-radius: var(--r-md);
        font-family: var(--font-ui);
        font-size: 13px; font-weight: 600;
        border: none; cursor: pointer;
        transition: all 0.18s var(--ease-out);
    }
    .up-btn svg { width: 15px; height: 15px; }
    .up-btn-approve {
        background: #059669; color: #fff;
        box-shadow: 0 2px 10px rgba(5,150,105,0.25);
    }
    .up-btn-approve:hover {
        background: #047857;
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(5,150,105,0.35);
    }
    .up-btn-reject {
        background: var(--white); color: #b91c1c;
        border: 1px solid #fca5a5;
    }
    .up-btn-reject:hover {
        background: #fef2f2;
        transform: translateY(-1px);
    }

    /* ── Panel card ── */
    .up-panel {
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .up-panel-header {
        display: flex; align-items: center; gap: 0.6rem;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border-default);
        background: var(--slate-50);
    }
    .up-panel-icon {
        width: 30px; height: 30px;
        border-radius: var(--r-sm);
        background: var(--blue-50);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .up-panel-icon svg { width: 15px; height: 15px; color: var(--blue-500); }
    .up-panel-title {
        font-family: var(--font-display);
        font-size: 13.5px; font-weight: 700;
        color: var(--slate-800);
    }
    .up-panel-body { padding: 1.25rem 1.5rem; }

    /* ── Subject rows ── */
    .up-subject-row {
        display: flex; align-items: center;
        justify-content: space-between; gap: 1rem;
        padding: 0.75rem 1rem;
        border-radius: var(--r-md);
        background: var(--slate-50);
        border: 1px solid var(--border-default);
        margin-bottom: 0.5rem;
        transition: background 0.15s;
    }
    .up-subject-row:last-child { margin-bottom: 0; }
    .up-subject-row:hover { background: var(--blue-50); }
    html[data-theme="dark"] .up-subject-row:hover { background: var(--slate-100); }
    html[data-theme="dark"] .up-subject-name, html[data-theme="dark"] .up-student-name { color: var(--slate-900); }
    .up-subject-name {
        font-size: 13.5px; font-weight: 600; color: var(--slate-800);
    }
    .up-subject-meta {
        font-size: 12px; color: var(--slate-500); margin-top: 1px;
    }
    .up-sy-chip {
        font-size: 11px; font-weight: 600;
        color: var(--blue-600); background: var(--blue-50);
        border: 1px solid var(--blue-200);
        padding: 2px 10px; border-radius: var(--r-full);
        white-space: nowrap;
    }

    /* ── Student grid ── */
    .up-student-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0.6rem;
    }
    .up-student-card {
        display: flex; align-items: center; gap: 0.65rem;
        padding: 0.65rem 0.875rem;
        background: var(--slate-50);
        border: 1px solid var(--border-default);
        border-radius: var(--r-md);
        transition: background 0.15s, border-color 0.15s;
    }
    .up-student-card:hover { background: var(--blue-50); border-color: var(--blue-200); }
    .up-student-avatar {
        width: 32px; height: 32px;
        border-radius: var(--r-sm);
        background: linear-gradient(135deg, var(--blue-400), var(--blue-600));
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700; color: #fff; flex-shrink: 0;
    }
    .up-student-name {
        font-size: 12.5px; font-weight: 600; color: var(--slate-800);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .up-student-section {
        font-size: 11px; color: var(--slate-400);
    }

    /* ── Section divider inside panel ── */
    .up-section-divider {
        border: none; border-top: 1px solid var(--border-default);
        margin: 1.25rem 0;
    }
    .up-section-sub {
        font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.08em;
        color: var(--slate-400); margin-bottom: 0.75rem;
        display: flex; align-items: center; gap: 0.4rem;
    }
    .up-section-sub span {
        background: var(--slate-100);
        border: 1px solid var(--border-default);
        padding: 1px 8px; border-radius: var(--r-full);
        font-size: 10px;
    }

    /* ── Log rows ── */
    .up-log-row {
        display: flex; align-items: flex-start;
        gap: 0.875rem; padding: 0.875rem 0;
        border-bottom: 1px solid var(--slate-100);
    }
    .up-log-row:last-child { border-bottom: none; }
    .up-log-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: var(--blue-400); flex-shrink: 0; margin-top: 5px;
    }
    .up-log-body { flex: 1; min-width: 0; }
    .up-log-action {
        font-size: 13.5px; font-weight: 500;
        color: var(--slate-800); line-height: 1.4;
    }
    .up-log-meta {
        font-size: 11.5px; color: var(--slate-400); margin-top: 2px;
        display: flex; align-items: center; gap: 0.4rem;
    }
    .up-log-module {
        display: inline-block;
        background: var(--blue-50); color: var(--blue-700);
        font-size: 10px; font-weight: 700;
        letter-spacing: 0.04em; text-transform: uppercase;
        padding: 1px 7px; border-radius: var(--r-sm);
    }
    .up-log-time {
        font-size: 11.5px; color: var(--slate-400);
        white-space: nowrap; flex-shrink: 0; padding-top: 2px;
    }

    /* ── Empty state ── */
    .up-empty {
        text-align: center; padding: 2.5rem 1rem;
        color: var(--slate-400); font-size: 13px;
    }
    .up-empty svg {
        width: 36px; height: 36px; opacity: 0.2;
        margin: 0 auto 0.5rem; display: block;
    }
</style>

{{-- ── Confirm Modal ── --}}
<style>
.um-confirm-backdrop {
    position: fixed; inset: 0;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 1000;
    display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none;
    transition: opacity 0.22s ease;
}
.um-confirm-backdrop.open { opacity: 1; pointer-events: auto; }
.um-confirm-box {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 24px 56px -8px rgba(0,0,0,0.18);
    width: 360px; max-width: calc(100vw - 2rem);
    padding: 1.75rem;
    transform: scale(0.94) translateY(12px);
    transition: transform 0.28s cubic-bezier(0.34,1.56,0.64,1);
}
.um-confirm-backdrop.open .um-confirm-box { transform: scale(1) translateY(0); }
.um-confirm-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 1.1rem;
}
.um-confirm-icon.approve { background: #ecfdf5; }
.um-confirm-icon.approve svg { color: #059669; }
.um-confirm-icon.reject  { background: #fef2f2; }
.um-confirm-icon.reject  svg { color: #dc2626; }
.um-confirm-icon svg { width: 24px; height: 24px; }
.um-confirm-title { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 0.4rem; }
.um-confirm-desc  { font-size: 13px; color: #64748b; line-height: 1.6; margin-bottom: 1.5rem; }
.um-confirm-actions { display: flex; gap: 0.65rem; }
.um-confirm-cancel {
    flex: 1; padding: 0.65rem;
    background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 10px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13px; font-weight: 600; color: #334155;
    cursor: pointer; transition: background 0.15s;
}
.um-confirm-cancel:hover { background: #e2e8f0; }
.um-confirm-proceed {
    flex: 1; padding: 0.65rem; border: none; border-radius: 10px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13px; font-weight: 700; color: #fff;
    cursor: pointer; transition: all 0.18s;
    display: flex; align-items: center; justify-content: center; gap: 0.4rem;
}
.um-confirm-proceed.approve { background: #059669; }
.um-confirm-proceed.approve:hover { background: #047857; transform: translateY(-1px); }
.um-confirm-proceed.reject  { background: #dc2626; }
.um-confirm-proceed.reject:hover  { background: #b91c1c; transform: translateY(-1px); }
</style>

<div class="up-page">

    {{-- Back --}}
    <a href="{{ route('admin.users.index') }}" class="up-back">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to users
    </a>

    {{-- ── Hero Card ── --}}
    <div class="up-hero">
        <div class="up-hero-strip"></div>

        <div class="up-hero-body">
            <div class="up-hero-left">
                <div class="up-avatar {{ $user->role }}">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="up-hero-name">{{ $user->name }}</h1>
                    <p class="up-hero-email">{{ $user->email }}</p>
                    <div class="up-hero-meta">
                        <span class="up-pill up-pill-role-{{ $user->role }}">
                            {{ ucfirst($user->role) }}
                        </span>
                        <span class="up-pill up-pill-{{ $user->status }}">
                            {{ ucfirst($user->status) }}
                        </span>
                        <span class="up-date-chip">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Joined {{ $user->created_at->format('F d, Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info grid --}}
        <div class="up-hero-info">
            <div class="up-info-item">
                <div class="up-info-label">Contact</div>
                <div class="up-info-value {{ !$user->contact_number ? 'muted' : '' }}">
                    {{ $user->contact_number ?? 'Not provided' }}
                </div>
            </div>

            @if($user->role === 'student')
            <div class="up-info-item">
                <div class="up-info-label">Grade Level</div>
                <div class="up-info-value {{ !$user->grade_level ? 'muted' : '' }}">
                    {{ $user->grade_level ? 'Grade ' . $user->grade_level : 'Not enrolled' }}
                </div>
            </div>
            <div class="up-info-item">
                <div class="up-info-label">Section</div>
                <div class="up-info-value {{ !$user->section ? 'muted' : '' }}">
                    {{ $user->section->name ?? 'Not assigned' }}
                </div>
            </div>
            <div class="up-info-item">
                <div class="up-info-label">Enrollment Status</div>
                <div class="up-info-value">
                    @php $enroll = $user->studentEnrollment; @endphp
                    @if($enroll)
                        <span class="up-pill up-pill-approved" style="font-size:11px;">
                            {{ ucfirst($enroll->status) }}
                        </span>
                    @else
                        <span class="muted" style="font-size:13px; font-style:italic; color:var(--slate-400);">No enrollment</span>
                    @endif
                </div>
            </div>
            @endif

            @if($user->role === 'teacher')
            <div class="up-info-item">
                <div class="up-info-label">Employee ID</div>
                <div class="up-info-value {{ !$user->employee_id ? 'muted' : '' }}">
                    {{ $user->employee_id ?? 'Not set' }}
                </div>
            </div>
            <div class="up-info-item">
                <div class="up-info-label">Grade Level</div>
                <div class="up-info-value {{ !$user->grade_level ? 'muted' : '' }}">
                    {{ $user->grade_level ? 'Grade ' . $user->grade_level : 'Not set' }}
                </div>
            </div>
            <div class="up-info-item">
                <div class="up-info-label">Subject Load</div>
                <div class="up-info-value">
                    {{ $user->teacherSubjects->pluck('subject_id')->unique()->count() }} subjects
                </div>
            </div>
            @endif
        </div>

        {{-- Approve / Reject (pending only) --}}
@if($user->status === 'pending')
<div class="up-actions">
    <button type="button" class="up-btn up-btn-approve"
        data-action="approve"
        data-url="{{ route('admin.users.approve', $user) }}"
        data-name="{{ $user->name }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Approve Registration
    </button>
    <button type="button" class="up-btn up-btn-reject"
        data-action="reject"
        data-url="{{ route('admin.users.reject', $user) }}"
        data-name="{{ $user->name }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Reject Registration
    </button>
</div>
@endif
 </div>
    {{-- ── Teacher Subjects Panel ── --}}
    @if($user->role === 'teacher' && $user->teacherSubjects->count())
    @php
        $sectionIds = $user->teacherSubjects->pluck('section_id')->unique();
        $myStudents = \App\Models\User::where('role','student')
            ->where('status','approved')
            ->whereIn('section_id', $sectionIds)
            ->with('section')
            ->get();
    @endphp
    <div class="up-panel">
        <div class="up-panel-header">
            <div class="up-panel-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div class="up-panel-title">Assigned Subjects & Sections</div>
        </div>
        <div class="up-panel-body">

            @foreach($user->teacherSubjects as $ts)
            <div class="up-subject-row">
                <div>
                    <div class="up-subject-name">{{ $ts->subject->name ?? '—' }}</div>
                    <div class="up-subject-meta">
                        {{ $ts->section->name ?? '—' }} · Grade {{ $ts->grade_level }}
                    </div>
                </div>
                <span class="up-sy-chip">{{ $ts->school_year }}</span>
            </div>
            @endforeach

            @if($myStudents->count())
            <hr class="up-section-divider">
            <div class="up-section-sub">
                Students
                <span>{{ $myStudents->count() }}</span>
            </div>
            <div class="up-student-grid">
                @foreach($myStudents as $student)
                <div class="up-student-card">
                    <div class="up-student-avatar">
                        {{ strtoupper(substr($student->name, 0, 1)) }}
                    </div>
                    <div style="min-width:0;">
                        <div class="up-student-name">{{ $student->name }}</div>
                        <div class="up-student-section">{{ $student->section->name ?? '—' }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

        </div>
    </div>
    @endif

    {{-- ── Linked Children Panel (Parent) ── --}}
    @if($user->role === 'parent')
    <div class="up-panel">
        <div class="up-panel-header">
            <div class="up-panel-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z"/>
                </svg>
            </div>
            <div class="up-panel-title">Linked Children</div>
        </div>
        <div class="up-panel-body">

            @if($user->child_name)
            <div class="up-section-sub" style="margin-top:0;">
                Parent-stated child
                <span>{{ $user->child_name }}</span>
            </div>
            <p style="font-size:13px;color:var(--slate-500,#64748b);margin:-2px 0 14px;">
                Ito ang pangalan ng anak na inilagay sa registration. I-link ito sa tamang student account sa ibaba.
            </p>
            @endif

            {{-- Currently linked children --}}
            @forelse($user->children as $child)
            <div class="up-student-card" style="justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:12px;min-width:0;">
                    <div class="up-student-avatar">{{ strtoupper(substr($child->name, 0, 1)) }}</div>
                    <div style="min-width:0;">
                        <div class="up-student-name">{{ $child->name }}</div>
                        <div class="up-student-section">{{ $child->section->name ?? 'No section' }} · {{ $child->email }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.users.unlink-child', [$user, $child]) }}"
                      onsubmit="return confirm('Alisin ang link kay {{ $child->name }}?');" style="margin:0;">
                    @csrf @method('DELETE')
                    <button type="submit" style="border:1px solid var(--danger,#dc2626);color:var(--danger,#dc2626);background:#fff;border-radius:var(--r-md,10px);padding:7px 13px;font-size:13px;font-weight:600;cursor:pointer;">
                        Unlink
                    </button>
                </form>
            </div>
            @empty
            <p style="font-size:14px;color:var(--slate-500,#64748b);margin:0 0 14px;">
                Wala pang naka-link na anak sa account na ito.
            </p>
            @endforelse

            <hr class="up-section-divider">

            {{-- Link a new child --}}
            <div class="up-section-sub">Link a student</div>
            <form method="POST" action="{{ route('admin.users.link-child', $user) }}"
                  style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:10px;">
                @csrf
                <select name="student_id" required
                    style="flex:1;min-width:220px;padding:10px 12px;border:1px solid var(--slate-200,#e2e8f0);border-radius:var(--r-md,10px);font-size:14px;background:#fff;color:var(--slate-700,#334155);">
                    <option value="">— Select a student —</option>
                    @foreach(($linkableStudents ?? collect()) as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->email }})</option>
                    @endforeach
                </select>
                <button type="submit" style="background:var(--blue-600,#2563eb);color:#fff;border:none;border-radius:var(--r-md,10px);padding:10px 18px;font-size:14px;font-weight:600;cursor:pointer;">
                    Link child
                </button>
            </form>
            @if(($linkableStudents ?? collect())->isEmpty())
            <p style="font-size:12.5px;color:var(--slate-500,#64748b);margin-top:8px;">
                Walang available na unlinked na approved student. (Ang naka-link na sa ibang magulang ay hindi lalabas dito.)
            </p>
            @endif

        </div>
    </div>
    @endif

    {{-- ── Activity Logs Panel ── --}}
    <div class="up-panel">
        <div class="up-panel-header">
            <div class="up-panel-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="up-panel-title">Activity History</div>
        </div>
        <div class="up-panel-body">
            @forelse($user->auditLogs()->latest()->take(15)->get() as $log)
            <div class="up-log-row">
                <div class="up-log-dot"></div>
                <div class="up-log-body">
                    <div class="up-log-action">{{ $log->action }}</div>
                    <div class="up-log-meta">
                        @if($log->module)
                            <span class="up-log-module">{{ $log->module }}</span>
                        @endif
                        @if($log->ip_address)
                            <span>· {{ $log->ip_address }}</span>
                        @endif
                    </div>
                </div>
                <div class="up-log-time">{{ $log->created_at->format('M d, Y · H:i') }}</div>
            </div>
            @empty
            <div class="up-empty">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                </svg>
                No activity recorded yet.
            </div>
            @endforelse
        </div>
    </div>

</div>

<div class="um-confirm-backdrop" id="um-confirm-backdrop">
    <div class="um-confirm-box">
        <div class="um-confirm-icon" id="um-confirm-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"></svg>
        </div>
        <div class="um-confirm-title" id="um-confirm-title"></div>
        <p class="um-confirm-desc" id="um-confirm-desc"></p>
        <div class="um-confirm-actions">
            <button class="um-confirm-cancel" id="um-confirm-cancel">Cancel</button>
            <button class="um-confirm-proceed" id="um-confirm-proceed"></button>
        </div>
    </div>
</div>

<form method="POST" id="um-approve-form" style="display:none;">
    @csrf @method('PATCH')
</form>
<form method="POST" id="um-reject-form" style="display:none;">
    @csrf @method('PATCH')
</form>

<script>
(function () {
    const backdrop    = document.getElementById('um-confirm-backdrop');
    const iconEl      = document.getElementById('um-confirm-icon');
    const titleEl     = document.getElementById('um-confirm-title');
    const descEl      = document.getElementById('um-confirm-desc');
    const cancelBtn   = document.getElementById('um-confirm-cancel');
    const proceedBtn  = document.getElementById('um-confirm-proceed');
    const approveForm = document.getElementById('um-approve-form');
    const rejectForm  = document.getElementById('um-reject-form');

    let pendingUrl    = null;
    let pendingAction = null;

    const config = {
        approve: {
            iconClass: 'approve',
            iconPath:  'M5 13l4 4L19 7',
            title: name => `Approve ${name}?`,
            desc:  name => `This will grant ${name} full access to DP-LMS. You can reject them later if needed.`,
            btnText:  'Yes, Approve',
            btnClass: 'approve',
        },
        reject: {
            iconClass: 'reject',
            iconPath:  'M6 18L18 6M6 6l12 12',
            title: name => `Reject ${name}?`,
            desc:  name => `${name}'s account will be rejected and they won't be able to log in.`,
            btnText:  'Yes, Reject',
            btnClass: 'reject',
        }
    };

    function openConfirm(action, url, name) {
        const c = config[action];
        pendingUrl    = url;
        pendingAction = action;

        iconEl.className = `um-confirm-icon ${c.iconClass}`;
        iconEl.querySelector('svg').innerHTML =
            `<path stroke-linecap="round" stroke-linejoin="round" d="${c.iconPath}"/>`;
        titleEl.textContent    = c.title(name);
        descEl.textContent     = c.desc(name);
        proceedBtn.textContent = c.btnText;
        proceedBtn.className   = `um-confirm-proceed ${c.btnClass}`;

        backdrop.classList.add('open');
        cancelBtn.focus();
    }

    function closeConfirm() {
        backdrop.classList.remove('open');
        pendingUrl = pendingAction = null;
    }

    proceedBtn.addEventListener('click', function () {
        if (!pendingUrl || !pendingAction) return;
        const form = pendingAction === 'approve' ? approveForm : rejectForm;
        form.action      = pendingUrl;
        this.disabled    = true;
        this.textContent = 'Processing…';
        form.submit();
    });

    cancelBtn.addEventListener('click', closeConfirm);
    backdrop.addEventListener('click', e => { if (e.target === backdrop) closeConfirm(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeConfirm(); });

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-action="approve"],[data-action="reject"]');
        if (!btn) return;
        openConfirm(btn.dataset.action, btn.dataset.url, btn.dataset.name);
    });
})();
</script>
@endsection