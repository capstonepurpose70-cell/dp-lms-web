@extends('layouts.teacher')
@section('title', 'Teacher Dashboard')

@section('content')
{{-- Plain CSS + layout tokens (the Tailwind build is stale).
     Class names are prefixed td- so they never clash with the layout's classes. --}}
<style>
    .td-wrap { max-width: 1040px; margin: 0 auto; }

    .td-head { margin-bottom: 22px; }
    .td-title { font-size: 24px; font-weight: 800; color: var(--text-1); letter-spacing: -.01em; }
    .td-sub { font-size: 13px; color: var(--text-3); margin-top: 4px; }

    .td-advisory {
        display: inline-flex; align-items: center; gap: 8px; flex-wrap: wrap;
        margin-top: 12px; padding: 7px 12px; border-radius: 12px;
        background: var(--accent-lt); border: 1px solid #bfdbfe; color: var(--accent-dark); font-size: 12.5px; font-weight: 700;
    }
    .td-advisory a { color: var(--accent-dark); }

    .td-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 22px; }
    .td-stat {
        background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg);
        padding: 18px 20px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 14px;
    }
    .td-stat-icon {
        width: 44px; height: 44px; border-radius: var(--r-md); flex-shrink: 0;
        display: grid; place-items: center; background: var(--accent-lt); color: var(--accent);
    }
    .td-stat-num { font-size: 28px; font-weight: 800; color: var(--text-1); line-height: 1; }
    .td-stat-label { font-size: 13px; font-weight: 600; color: var(--text-2); margin-top: 3px; }

    .td-quick { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 22px; }
    .td-qcard {
        display: flex; align-items: center; gap: 12px; min-height: 64px; padding: 14px 16px;
        background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-md);
        text-decoration: none; box-shadow: var(--shadow-sm);
        transition: border-color .18s, box-shadow .18s, transform .18s;
    }
    .td-qcard:hover { border-color: var(--accent); box-shadow: 0 6px 18px var(--accent-glow); transform: translateY(-2px); }
    .td-qicon {
        width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
        display: grid; place-items: center; background: var(--accent-lt); color: var(--accent);
    }
    .td-qtitle { display: block; font-size: 13.5px; font-weight: 700; color: var(--text-1); }
    .td-qdesc { display: block; font-size: 12px; color: var(--text-3); margin-top: 1px; }

    .td-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start; }
    .td-panel { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); overflow: hidden; }
    .td-panel-head {
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
        padding: 14px 20px; border-bottom: 1px solid var(--border);
    }
    .td-panel-title { font-size: 14px; font-weight: 700; color: var(--text-1); }
    .td-panel-meta { font-size: 12px; color: var(--text-3); }

    .td-row { display: flex; align-items: center; gap: 12px; padding: 11px 20px; border-bottom: 1px solid var(--border); }
    .td-row:last-child { border-bottom: none; }
    .td-row-main { flex: 1; min-width: 0; }
    .td-row-title { font-size: 13px; font-weight: 600; color: var(--text-1); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .td-row-sub { font-size: 12px; color: var(--text-3); margin-top: 1px; }
    .td-dot {
        width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0; display: grid; place-items: center;
        background: var(--accent-lt); color: var(--accent); font-size: 13px; font-weight: 800;
    }
    .td-pill { padding: 2px 9px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #dcfce7; color: #166534; flex-shrink: 0; }

    .td-more { display: block; padding: 12px 20px; border-top: 1px solid var(--border); text-align: center; font-size: 13px; font-weight: 700; color: var(--accent); text-decoration: none; }
    .td-more:hover { background: var(--accent-lt); }

    .td-empty { padding: 34px 20px; text-align: center; color: var(--text-3); font-size: 13px; line-height: 1.6; }

    @media (max-width: 900px) { .td-quick { grid-template-columns: repeat(2, 1fr); } .td-grid { grid-template-columns: 1fr; } }
    /* Phones: compact 3-up stats and 2-up shortcuts instead of seven full-width cards */
    @media (max-width: 600px) {
        .td-title { font-size: 21px; }
        .td-stats { gap: 10px; }
        .td-stat { flex-direction: column; align-items: flex-start; gap: 4px; padding: 14px; }
        .td-stat-icon { display: none; }
        .td-stat-num { font-size: 24px; }
        .td-stat-label { font-size: 12px; }
        .td-quick { grid-template-columns: 1fr 1fr; gap: 10px; }
        .td-qcard { padding: 12px; gap: 10px; }
        .td-qdesc { display: none; }
    }
</style>

<div class="td-wrap">

    <div class="td-head">
        <h1 class="td-title">
            Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
            {{ explode(' ', trim($teacher->name))[0] }}!
        </h1>
        <p class="td-sub">{{ now()->format('l, F j, Y') }} · Teacher Dashboard</p>

        @foreach($advisory as $sec)
            <div class="td-advisory">
                Adviser · Grade {{ $sec->grade_level }} – {{ $sec->name }}
                <a href="{{ route('teacher.enrollments.index') }}">Enrollment requests</a>
            </div>
        @endforeach
    </div>

    {{-- Stats --}}
    <div class="td-stats">
        <div class="td-stat">
            <div class="td-stat-icon" aria-hidden="true">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div><p class="td-stat-num">{{ $studentCount }}</p><p class="td-stat-label">My students</p></div>
        </div>
        <div class="td-stat">
            <div class="td-stat-icon" aria-hidden="true">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <div><p class="td-stat-num">{{ $subjectCount }}</p><p class="td-stat-label">Subjects I teach</p></div>
        </div>
        <div class="td-stat">
            <div class="td-stat-icon" aria-hidden="true">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div><p class="td-stat-num">{{ $sectionCount }}</p><p class="td-stat-label">My sections</p></div>
        </div>
    </div>

    {{-- Quick links --}}
    <div class="td-quick">
        @foreach([
            ['teacher.attendance.index',    'Attendance',    'Open class attendance', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
            ['teacher.gradebook.index',     'Gradebook',     'Encode grades',         'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['teacher.materials.index',     'Materials',     'Upload lessons',        'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
            ['teacher.announcements.index', 'Announcements', 'Post class updates',    'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 16h2a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 5.882A2 2 0 0112.83 4h.842a2 2 0 011.995 1.858L17 16H8.343M11 5.882L8.343 16'],
        ] as [$route, $label, $desc, $icon])
            <a href="{{ route($route) }}" class="td-qcard">
                <span class="td-qicon" aria-hidden="true">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </span>
                <span>
                    <span class="td-qtitle">{{ $label }}</span>
                    <span class="td-qdesc">{{ $desc }}</span>
                </span>
            </a>
        @endforeach
    </div>

    <div class="td-grid">

        {{-- My classes: one row per subject + section --}}
        <section class="td-panel" aria-labelledby="td-classes">
            <div class="td-panel-head">
                <h2 class="td-panel-title" id="td-classes">My classes</h2>
                <span class="td-panel-meta">{{ $classList->count() }} {{ Str::plural('class', $classList->count()) }}</span>
            </div>
            @forelse($classList as $class)
                <div class="td-row">
                    <span class="td-dot" aria-hidden="true">{{ mb_strtoupper(mb_substr($class->subject->name, 0, 1)) }}</span>
                    <div class="td-row-main">
                        <p class="td-row-title">{{ $class->subject->name }}</p>
                        <p class="td-row-sub">Grade {{ $class->section->grade_level }} – {{ $class->section->name }}</p>
                    </div>
                </div>
            @empty
                <div class="td-empty">
                    No classes yet.<br>The school admin assigns your classes in Class Schedules.
                </div>
            @endforelse
        </section>

        {{-- My students --}}
        <section class="td-panel" aria-labelledby="td-students">
            <div class="td-panel-head">
                <h2 class="td-panel-title" id="td-students">My students</h2>
                <span class="td-panel-meta">{{ $studentCount }} enrolled</span>
            </div>
            @forelse($students->take(8) as $student)
                <div class="td-row">
                    <span class="td-dot" aria-hidden="true">{{ mb_strtoupper(mb_substr($student->name, 0, 1)) }}</span>
                    <div class="td-row-main">
                        <p class="td-row-title">{{ $student->name }}</p>
                        <p class="td-row-sub">Grade {{ $student->grade_level ?? '—' }} – {{ $student->section->name ?? '—' }}</p>
                    </div>
                    <span class="td-pill">Enrolled</span>
                </div>
            @empty
                <div class="td-empty">
                    No students yet.<br>They appear here once enrolled in your sections.
                </div>
            @endforelse
            @if($studentCount > 8)
                <a href="{{ route('teacher.students.index') }}" class="td-more">View all {{ $studentCount }} students</a>
            @endif
        </section>

    </div>
</div>
@endsection
