@extends('layouts.teacher') 
@section('title', 'Teacher Dashboard')

@section('sidebar')
    <a href="{{ route('teacher.dashboard') }}"
        class="{{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>
    <a href="{{ route('teacher.gradebook.index') }}"
        class="{{ request()->routeIs('teacher.gradebook.*') ? 'active' : '' }}">
        Gradebook
    </a>
    <a href="{{ route('teacher.materials.index') }}"
        class="{{ request()->routeIs('teacher.materials.*') ? 'active' : '' }}">
        Learning Materials
    </a>
    <a href="{{ route('teacher.announcements.index') }}"
        class="{{ request()->routeIs('teacher.announcements.*') ? 'active' : '' }}">
        Announcements
    </a>
@endsection

@section('content')
<style>
    /* ── Animations ───────────────────────────────────────────── */
    .fade-up {
        animation: fadeUp 0.4s cubic-bezier(0.16,1,0.3,1) both;
    }

    .fade-up-1 { animation-delay: 0.05s; }
    .fade-up-2 { animation-delay: 0.10s; }
    .fade-up-3 { animation-delay: 0.15s; }
    .fade-up-4 { animation-delay: 0.20s; }
    .fade-up-5 { animation-delay: 0.25s; }

    @keyframes fadeUp {
        from { opacity:0; transform:translateY(14px); }
        to   { opacity:1; transform:translateY(0); }
    }

    /* ── Stat cards ───────────────────────────────────────────── */
    .stat-card {
        background: #fff;
        border-radius: 16px;
        border: 1.5px solid #f1f5f9;
        padding: 20px 22px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: transform 0.22s cubic-bezier(0.34,1.56,0.64,1),
                    box-shadow 0.22s ease,
                    border-color 0.2s ease;
        cursor: default;
    }

    .stat-card:hover {
        transform: translateY(-4px) scale(1.01);
        box-shadow: 0 10px 28px rgba(0,0,0,0.08);
        border-color: #e2e8f0;
    }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
        transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1);
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.12) rotate(-5deg);
    }

    .stat-number {
        font-size: 30px;
        font-weight: 800;
        color: #111827;
        line-height: 1;
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
    }

    .stat-sub {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 2px;
    }

    /* ── Quick nav cards ──────────────────────────────────────── */
    .nav-card {
        background: #fff;
        border-radius: 14px;
        border: 1.5px solid #f1f5f9;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        text-decoration: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        transition: transform 0.22s cubic-bezier(0.34,1.56,0.64,1),
                    box-shadow 0.22s ease,
                    border-color 0.2s ease,
                    background 0.2s ease;
    }

    .nav-card:hover {
        transform: translateY(-3px) scale(1.01);
        box-shadow: 0 10px 24px rgba(0,0,0,0.08);
        border-color: #dbeafe;
        background: #f8faff;
    }

    .nav-card:active {
        transform: scale(0.98);
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    .nav-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1);
    }

    .nav-card:hover .nav-icon {
        transform: scale(1.15) rotate(-5deg);
    }

    .nav-title {
        font-size: 13px;
        font-weight: 700;
        color: #111827;
    }

    .nav-desc {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 1px;
    }

    .nav-arrow {
        margin-left: auto;
        color: #d1d5db;
        flex-shrink: 0;
        transition: transform 0.2s ease, color 0.2s ease;
    }

    .nav-card:hover .nav-arrow {
        transform: translateX(4px);
        color: #3b82f6;
    }

    /* ── Section panel ────────────────────────────────────────── */
    .panel {
        background: #fff;
        border-radius: 16px;
        border: 1.5px solid #f1f5f9;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        overflow: hidden;
    }

    .panel-header {
        padding: 14px 20px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .panel-title {
        font-size: 13px;
        font-weight: 700;
        color: #111827;
    }

    .panel-link {
        font-size: 12px;
        color: #3b82f6;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.15s ease;
    }

    .panel-link:hover { color: #1d4ed8; text-decoration: underline; }

    /* ── Student rows ─────────────────────────────────────────── */
    .student-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        border-bottom: 1px solid #f9fafb;
        transition: background 0.15s ease;
    }

    .student-row:last-child { border-bottom: none; }
    .student-row:hover { background: #f8faff; }

    .student-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 13px;
        font-weight: 700;
        color: white;
        transition: transform 0.2s cubic-bezier(0.34,1.56,0.64,1);
    }

    .student-row:hover .student-avatar {
        transform: scale(1.1);
    }

    /* ── Subject chips ────────────────────────────────────────── */
    .subject-chip {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        background: #f8faff;
        border: 1px solid #e0e7ff;
        border-radius: 10px;
        transition: background 0.15s ease,
                    border-color 0.15s ease,
                    transform 0.2s cubic-bezier(0.34,1.56,0.64,1);
    }

    .subject-chip:hover {
        background: #eff6ff;
        border-color: #bfdbfe;
        transform: translateY(-2px);
    }

    /* ── Empty state ──────────────────────────────────────────── */
    .empty-state {
        padding: 40px 20px;
        text-align: center;
    }

    .empty-icon {
        width: 48px;
        height: 48px;
        background: #f8faff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
    }
</style>

<div class="max-w-5xl mx-auto">

    {{-- ── Welcome Header ────────────────────────────────────── --}}
    <div class="mb-6 fade-up fade-up-1">
        <h1 class="text-2xl font-bold text-gray-800">
            Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
            {{ explode(' ', auth()->user()->name)[0] }}!
        </h1>
        <p class="text-sm text-gray-400 mt-1">
            {{ now()->format('l, F d, Y') }}
            &nbsp;·&nbsp; Teacher Dashboard
        </p>
    </div>

    {{-- ── Stat Cards ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

        <div class="stat-card fade-up fade-up-1">
            <div class="stat-icon" style="background:#eff6ff;">
                <svg width="20" height="20" fill="none" stroke="#3b82f6"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10
                           0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3
                           3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0
                           0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="stat-number">{{ $studentCount }}</p>
            <p class="stat-label">My Students</p>
            <p class="stat-sub">Enrolled in my sections</p>
        </div>

        <div class="stat-card fade-up fade-up-2">
            <div class="stat-icon" style="background:#faf5ff;">
                <svg width="20" height="20" fill="none" stroke="#a855f7"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5
                           5S4.168 5.477 3 6.253v13C4.168 18.477 5.754
                           18 7.5 18s3.332.477 4.5 1.253m0-13C13.168
                           5.477 14.754 5 16.5 5c1.747 0 3.332.477
                           4.5 1.253v13C19.832 18.477 18.247 18 16.5
                           18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <p class="stat-number">{{ $subjectCount }}</p>
            <p class="stat-label">My Subjects</p>
            <p class="stat-sub">Assigned this year</p>
        </div>

        <div class="stat-card fade-up fade-up-3">
            <div class="stat-icon" style="background:#f0fdf4;">
                <svg width="20" height="20" fill="none" stroke="#22c55e"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14
                           0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1
                           4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <p class="stat-number">{{ $sections->count() }}</p>
            <p class="stat-label">My Sections</p>
            <p class="stat-sub">Handling this term</p>
        </div>
    </div>

    {{-- ── Quick Navigation ────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">

        <a href="{{ route('teacher.gradebook.index') }}"
            class="nav-card fade-up fade-up-2">
            <div class="nav-icon" style="background:#fff7ed;">
                <svg width="18" height="18" fill="none" stroke="#f97316"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0
                           002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0
                           012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2
                           2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2
                           0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <p class="nav-title">Gradebook</p>
                <p class="nav-desc">Encode student grades</p>
            </div>
            <svg class="nav-arrow" width="16" height="16" fill="none"
                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <a href="{{ route('teacher.materials.index') }}"
            class="nav-card fade-up fade-up-3">
            <div class="nav-icon" style="background:#eff6ff;">
                <svg width="18" height="18" fill="none" stroke="#3b82f6"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1
                           1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="nav-title">Materials</p>
                <p class="nav-desc">Upload learning content</p>
            </div>
            <svg class="nav-arrow" width="16" height="16" fill="none"
                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <a href="{{ route('teacher.announcements.index') }}"
            class="nav-card fade-up fade-up-4">
            <div class="nav-icon" style="background:#faf5ff;">
                <svg width="18" height="18" fill="none" stroke="#a855f7"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18
                           16h2a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 5.882A2
                           2 0 0112.83 4h.842a2 2 0 011.995 1.858L17 16H8.343M11
                           5.882L8.343 16"/>
                </svg>
            </div>
            <div>
                <p class="nav-title">Announcements</p>
                <p class="nav-desc">Post class updates</p>
            </div>
            <svg class="nav-arrow" width="16" height="16" fill="none"
                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- ── Bottom grid: subjects + students ──────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- My Subjects --}}
        <div class="panel fade-up fade-up-3">
            <div class="panel-header">
                <p class="panel-title">My Subjects</p>
                <span class="text-xs text-gray-400">
                    {{ $assignments->unique('subject_id')->count() }} assigned
                </span>
            </div>

            @if($assignments->count())
            <div class="p-4 grid grid-cols-1 gap-2">
                @foreach($assignments->unique('subject_id') as $assignment)
                <div class="subject-chip">
                    <div style="width:32px;height:32px;border-radius:8px;
                                background:#eff6ff;display:flex;
                                align-items:center;justify-content:center;
                                flex-shrink:0;">
                        <svg width="15" height="15" fill="none" stroke="#3b82f6"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5
                                   5S4.168 5.477 3 6.253v13C4.168 18.477 5.754
                                   18 7.5 18s3.332.477 4.5 1.253m0-13C13.168
                                   5.477 14.754 5 16.5 5c1.747 0 3.332.477
                                   4.5 1.253v13C19.832 18.477 18.247 18 16.5
                                   18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div>
                        <p style="font-size:13px;font-weight:600;color:#111827;">
                            {{ $assignment->subject->name }}
                        </p>
                        <p style="font-size:11px;color:#9ca3af;">
                            {{ $assignment->section->name }}
                            · Grade {{ $assignment->grade_level }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="20" height="20" fill="none" stroke="#d1d5db"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168
                               5.477 3 6.253v13C4.168 18.477 5.754 18 7.5
                               18s3.332.477 4.5 1.253"/>
                    </svg>
                </div>
                <p style="font-size:13px;color:#9ca3af;">No subjects assigned yet.</p>
            </div>
            @endif
        </div>

        {{-- My Students --}}
        <div class="panel fade-up fade-up-4">
            <div class="panel-header">
                <p class="panel-title">My Students</p>
                <span class="text-xs text-gray-400">
                    {{ $studentCount }} enrolled
                </span>
            </div>

            @forelse($students->take(8) as $student)
            <div class="student-row">
                <div class="student-avatar">
                    {{ strtoupper(substr($student->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p style="font-size:13px;font-weight:600;color:#111827;
                               white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $student->name }}
                    </p>
                    <p style="font-size:11px;color:#9ca3af;">
                        {{ $student->section->name ?? '—' }}
                        · Grade {{ $student->grade_level ?? '—' }}
                    </p>
                </div>
                <span style="padding:2px 8px;border-radius:20px;font-size:10px;
                             font-weight:600;background:#dcfce7;color:#15803d;
                             flex-shrink:0;">
                    Enrolled
                </span>
            </div>
            @empty
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="20" height="20" fill="none" stroke="#d1d5db"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10
                               0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3
                               3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0
                               0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p style="font-size:13px;color:#9ca3af;">No students assigned yet.</p>
            </div>
            @endforelse

            @if($students->count() > 8)
            <div style="padding:10px 20px;border-top:1px solid #f3f4f6;text-align:center;">
                <p style="font-size:12px;color:#9ca3af;">
                    +{{ $students->count() - 8 }} more students
                </p>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection