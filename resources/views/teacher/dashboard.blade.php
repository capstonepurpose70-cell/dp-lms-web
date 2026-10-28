@extends('layouts.teacher')
@section('title', 'Teacher Dashboard')

@section('content')
{{-- Plain CSS + layout tokens (the Tailwind build is stale).
     Class names are prefixed td- so they never clash with the layout's classes. --}}
<style>
    .td-wrap { max-width: 1080px; margin: 0 auto; }

    .td-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 4px 16px; margin-bottom: 20px; }
    .td-title { font-size: 26px; font-weight: 800; color: var(--text-1); letter-spacing: -.02em; line-height: 1.2; text-wrap: balance; }
    .td-date { font-size: 14px; font-weight: 600; color: var(--text-2); }

    .td-note {
        display: flex; flex-wrap: wrap; align-items: center; gap: 6px 14px;
        padding: 12px 16px; margin-bottom: 20px; border-radius: var(--r-md);
        background: var(--surface); border: 1px solid var(--border);
        font-size: 14px; color: var(--text-2);
    }
    .td-note b { color: var(--text-1); }
    .td-note svg { color: var(--accent); flex-shrink: 0; }
    .td-note-text { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 220px; }
    .td-link {
        display: inline-flex; align-items: center; gap: 4px; min-height: 32px;
        font-size: 14px; font-weight: 700; color: var(--accent-dark); text-decoration: none;
    }
    .td-link:hover { text-decoration: underline; text-underline-offset: 3px; }

    /* One summary strip instead of three separate cards */
    .td-summary {
        display: grid; grid-template-columns: repeat(3, minmax(0, 1fr));
        background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg);
        margin-bottom: 20px;
    }
    .td-sum { padding: 18px 24px; }
    .td-sum + .td-sum { border-left: 1px solid var(--border); }
    .td-sum-num { display: block; font-size: 30px; font-weight: 800; color: var(--text-1); line-height: 1.1; font-variant-numeric: tabular-nums; letter-spacing: -.02em; }
    .td-sum-label { display: block; font-size: 13.5px; font-weight: 600; color: var(--text-2); margin-top: 4px; }

    .td-grid { display: grid; grid-template-columns: minmax(0, 1.45fr) minmax(0, 1fr); gap: 20px; align-items: start; }
    .td-side { display: grid; gap: 20px; }

    .td-panel { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); overflow: hidden; }
    .td-panel-head { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; padding: 16px 20px 12px; }
    .td-panel-title { font-size: 15px; font-weight: 800; color: var(--text-1); }
    .td-panel-meta { font-size: 13px; color: var(--text-3); font-variant-numeric: tabular-nums; }

    .td-list { list-style: none; margin: 0; padding: 0; }
    .td-row { display: flex; align-items: center; gap: 12px; padding: 12px 20px; border-top: 1px solid var(--border); }
    .td-row-main { flex: 1; min-width: 0; }
    .td-row-title { font-size: 14px; font-weight: 700; color: var(--text-1); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .td-row-sub { font-size: 13px; color: var(--text-2); margin-top: 1px; }
    .td-mark {
        width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0; display: grid; place-items: center;
        background: var(--accent-lt); color: var(--accent-dark); font-size: 14px; font-weight: 800;
    }
    .td-avatar { border-radius: 50%; background: #F1F5F9; color: var(--text-2); }

    /* Shortcuts: a list of actions, not a grid of cards */
    .td-action { text-decoration: none; transition: background-color .15s ease; }
    .td-action:hover { background: #F8FAFC; }
    .td-action-icon { width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0; display: grid; place-items: center; border: 1px solid var(--border); color: var(--text-2); }
    .td-action:hover .td-action-icon { color: var(--accent-dark); border-color: #C7D7FE; }
    .td-action .td-row-title, .td-action .td-row-sub { display: block; }
    .td-chev { color: #94A3B8; flex-shrink: 0; }

    .td-more { display: flex; align-items: center; justify-content: center; min-height: 44px; border-top: 1px solid var(--border); font-size: 14px; font-weight: 700; color: var(--accent-dark); text-decoration: none; }
    .td-more:hover { background: #F8FAFC; }

    .td-empty { padding: 28px 20px 32px; border-top: 1px solid var(--border); text-align: center; color: var(--text-2); font-size: 14px; line-height: 1.6; }
    .td-empty b { display: block; color: var(--text-1); }

    @media (max-width: 960px) { .td-grid { grid-template-columns: 1fr; } }
    @media (max-width: 600px) {
        .td-title { font-size: 22px; }
        .td-sum { padding: 14px 12px; }
        .td-sum-num { font-size: 24px; }
        .td-sum-label { font-size: 12.5px; }
        .td-row, .td-panel-head { padding-left: 16px; padding-right: 16px; }
    }
</style>

@php
    $firstName = explode(' ', trim($teacher->name))[0];
    $partOfDay = now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening');
    $chevron = '<svg class="td-chev" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>';
@endphp

<div class="td-wrap">

    <header class="td-head">
        <h1 class="td-title">Good {{ $partOfDay }}, {{ $firstName }}</h1>
        <p class="td-date">{{ now()->format('l, F j, Y') }}</p>
    </header>

    @foreach($advisory as $sec)
        <div class="td-note">
            <span class="td-note-text">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>You are the adviser of <b>Grade {{ $sec->grade_level }} {{ $sec->name }}</b>.</span>
            </span>
            <a class="td-link" href="{{ route('teacher.enrollments.index') }}">
                Review enrollment requests
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
            </a>
        </div>
    @endforeach

    <section class="td-summary" aria-label="Summary">
        <div class="td-sum">
            <span class="td-sum-num">{{ $studentCount }}</span>
            <span class="td-sum-label">{{ Str::plural('Student', $studentCount) }}</span>
        </div>
        <div class="td-sum">
            <span class="td-sum-num">{{ $subjectCount }}</span>
            <span class="td-sum-label">{{ Str::plural('Subject', $subjectCount) }} taught</span>
        </div>
        <div class="td-sum">
            <span class="td-sum-num">{{ $sectionCount }}</span>
            <span class="td-sum-label">{{ Str::plural('Section', $sectionCount) }}</span>
        </div>
    </section>

    <div class="td-grid">

        {{-- My classes: one row per subject + section --}}
        <section class="td-panel" aria-labelledby="td-classes">
            <div class="td-panel-head">
                <h2 class="td-panel-title" id="td-classes">My classes</h2>
                <span class="td-panel-meta">{{ $classList->count() }} {{ Str::plural('class', $classList->count()) }}</span>
            </div>
            @if($classList->isEmpty())
                <div class="td-empty">
                    <b>No classes yet</b>
                    The school admin assigns your classes in Class Schedules.
                </div>
            @else
                <ul class="td-list">
                    @foreach($classList as $class)
                        <li class="td-row">
                            <span class="td-mark" aria-hidden="true">{{ mb_strtoupper(mb_substr($class->subject->name, 0, 1)) }}</span>
                            <div class="td-row-main">
                                <p class="td-row-title">{{ $class->subject->name }}</p>
                                <p class="td-row-sub">Grade {{ $class->section->grade_level }}, {{ $class->section->name }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="td-side">
            <section class="td-panel" aria-labelledby="td-actions">
                <div class="td-panel-head">
                    <h2 class="td-panel-title" id="td-actions">Shortcuts</h2>
                </div>
                <ul class="td-list">
                    @foreach([
                        ['teacher.attendance.index',    'Take attendance',  'Open a class and show the code', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                        ['teacher.gradebook.index',     'Encode grades',    'Quarter scores in the gradebook', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                        ['teacher.materials.index',     'Upload a material', 'Handouts, slides and videos', 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                        ['teacher.announcements.index', 'Post an announcement', 'For your classes or parents', 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 16h2a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 5.882A2 2 0 0112.83 4h.842a2 2 0 011.995 1.858L17 16H8.343M11 5.882L8.343 16'],
                    ] as [$route, $label, $desc, $icon])
                        <li>
                            <a href="{{ route($route) }}" class="td-row td-action">
                                <span class="td-action-icon" aria-hidden="true">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                                </span>
                                <span class="td-row-main">
                                    <span class="td-row-title">{{ $label }}</span>
                                    <span class="td-row-sub">{{ $desc }}</span>
                                </span>
                                {!! $chevron !!}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- My students --}}
            <section class="td-panel" aria-labelledby="td-students">
                <div class="td-panel-head">
                    <h2 class="td-panel-title" id="td-students">My students</h2>
                    <span class="td-panel-meta">{{ $studentCount }} enrolled</span>
                </div>
                @if($students->isEmpty())
                    <div class="td-empty">
                        <b>No students yet</b>
                        They appear here once they are enrolled in your sections.
                    </div>
                @else
                    <ul class="td-list">
                        @foreach($students->take(8) as $student)
                            <li class="td-row">
                                <span class="td-mark td-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($student->name, 0, 1)) }}</span>
                                <div class="td-row-main">
                                    <p class="td-row-title">{{ $student->name }}</p>
                                    <p class="td-row-sub">Grade {{ $student->grade_level ?? '—' }}, {{ $student->section->name ?? 'no section' }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    @if($studentCount > 8)
                        <a href="{{ route('teacher.students.index') }}" class="td-more">View all {{ $studentCount }} students</a>
                    @endif
                @endif
            </section>
        </div>

    </div>
</div>
@endsection
