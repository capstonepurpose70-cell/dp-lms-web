@extends('layouts.app')
@section('title', 'Student Dashboard')

@section('content')
<style>
    /* Student dashboard — layouts/app tokens (--accent, --text-1..3, --border, --r-*) */
    .sd { max-width: 1120px; margin: 0 auto; }
    .sd-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); }

    /* Header: greeting left, enrollment right */
    .sd-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 14px 24px; margin-bottom: 20px; }
    .sd-greet { font-size: 24px; font-weight: 800; letter-spacing: -.02em; color: var(--text-1); line-height: 1.25; }
    .sd-date { font-size: 14px; color: var(--text-2); margin-top: 4px; }
    .sd-enrolled { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; font-size: 13.5px; color: var(--text-2); }
    .sd-enrolled b { color: var(--text-1); font-weight: 700; }
    .sd-enrolled .sep { width: 4px; height: 4px; border-radius: 50%; background: #CBD5E1; }
    .sd-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 99px; font-size: 12px; font-weight: 700; background: var(--green-lt); color: #166534; }
    .sd-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--green); }

    /* Next step when not enrolled */
    .sd-notice { display: flex; gap: 12px; align-items: flex-start; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: var(--r-md); padding: 14px 16px; margin-bottom: 18px; }
    .sd-notice svg { flex-shrink: 0; margin-top: 1px; color: #B45309; }
    .sd-notice-title { font-size: 14px; font-weight: 700; color: #92400E; }
    .sd-notice-text { font-size: 13px; color: #92400E; margin-top: 2px; line-height: 1.55; }
    .sd-notice-btn { display: inline-flex; align-items: center; min-height: 40px; margin-top: 10px; padding: 0 16px; border-radius: 10px; background: #B45309; color: #fff; font-size: 13.5px; font-weight: 700; text-decoration: none; }
    .sd-notice-btn:hover { background: #92400E; }

    /* Summary strip: each figure opens its page */
    .sd-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); margin-bottom: 18px; overflow: hidden; }
    .sd-stat { display: flex; align-items: center; gap: 14px; padding: 16px 20px; text-decoration: none; color: inherit; transition: background-color .15s ease; }
    .sd-stat + .sd-stat { border-left: 1px solid var(--border); }
    .sd-stat:hover { background: #F8FAFF; }
    .sd-stat-ico { width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0; display: grid; place-items: center; background: var(--accent-lt); color: var(--accent-dark); }
    .sd-stat-ico.warn { background: #FEF3C7; color: #B45309; }
    .sd-stat-num { display: block; font-size: 24px; font-weight: 800; color: var(--text-1); line-height: 1.1; font-variant-numeric: tabular-nums; }
    .sd-stat-label { display: block; font-size: 13px; color: var(--text-2); margin-top: 2px; }

    /* Two columns */
    .sd-grid { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); gap: 18px; align-items: start; }
    .sd-col { display: grid; gap: 18px; min-width: 0; }
    .sd-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 20px 12px; }
    .sd-panel-title { font-size: 15.5px; font-weight: 800; color: var(--text-1); }
    .sd-panel-sub { font-size: 12.5px; color: var(--text-3); margin-top: 1px; }
    .sd-link { display: inline-flex; align-items: center; gap: 4px; min-height: 36px; font-size: 13px; font-weight: 700; color: var(--accent-dark); text-decoration: none; white-space: nowrap; }
    .sd-link:hover { text-decoration: underline; text-underline-offset: 3px; }
    .sd-link svg { width: 14px; height: 14px; }

    /* Today's classes */
    .sd-class { display: grid; grid-template-columns: 112px minmax(0, 1fr) auto; align-items: center; gap: 14px; padding: 12px 20px; border-top: 1px solid var(--border); }
    .sd-class-time { font-size: 13px; font-weight: 700; color: var(--text-1); font-variant-numeric: tabular-nums; line-height: 1.35; }
    .sd-class-time span { display: block; font-weight: 500; color: var(--text-3); }
    .sd-class-name { font-size: 14px; font-weight: 700; color: var(--text-1); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sd-class-meta { font-size: 12.5px; color: var(--text-2); margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sd-class.now { background: #F5F8FF; box-shadow: inset 3px 0 0 var(--accent); }
    .sd-class.done .sd-class-name, .sd-class.done .sd-class-time { color: var(--text-3); }
    .sd-tag { padding: 3px 9px; border-radius: 99px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
    .sd-tag.now { background: var(--accent); color: #fff; }
    .sd-tag.next { background: var(--accent-lt); color: var(--accent-dark); }
    .sd-tag.done { background: #F1F5F9; color: var(--text-3); }

    /* To submit */
    .sd-todo { display: flex; align-items: center; gap: 14px; padding: 12px 20px; border-top: 1px solid var(--border); text-decoration: none; color: inherit; transition: background-color .15s ease; }
    .sd-todo:hover { background: #F8FAFF; }
    .sd-todo-body { flex: 1; min-width: 0; }
    .sd-todo-title { display: block; font-size: 14px; font-weight: 700; color: var(--text-1); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sd-todo-meta { display: block; font-size: 12.5px; color: var(--text-2); margin-top: 2px; }
    .sd-due { font-size: 12px; font-weight: 700; white-space: nowrap; padding: 3px 9px; border-radius: 99px; background: #F1F5F9; color: var(--text-2); }
    .sd-due.soon { background: #FEF3C7; color: #92400E; }

    /* Subjects */
    .sd-subj { display: flex; align-items: center; gap: 12px; padding: 11px 20px; border-top: 1px solid var(--border); }
    .sd-subj-mark { width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0; display: grid; place-items: center; background: #F1F5F9; color: var(--text-2); font-size: 12.5px; font-weight: 800; }
    .sd-subj-name { font-size: 14px; font-weight: 700; color: var(--text-1); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sd-subj-teacher { font-size: 12.5px; color: var(--text-2); margin-top: 1px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* Announcements */
    .sd-ann { padding: 12px 20px; border-top: 1px solid var(--border); }
    .sd-ann-title { font-size: 14px; font-weight: 700; color: var(--text-1); }
    .sd-ann-body { font-size: 13px; color: var(--text-2); margin-top: 3px; line-height: 1.55; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .sd-ann-meta { font-size: 12px; color: var(--text-3); margin-top: 5px; }

    .sd-empty { padding: 18px 20px 20px; border-top: 1px solid var(--border); font-size: 13.5px; color: var(--text-2); line-height: 1.5; }
    .sd-empty b { display: block; color: var(--text-1); font-size: 14px; margin-bottom: 2px; }

    .sd-in { animation: sdIn .3s cubic-bezier(.22,.61,.36,1) both; }
    @keyframes sdIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

    @media (max-width: 960px) { .sd-grid { grid-template-columns: minmax(0, 1fr); } }
    @media (max-width: 600px) {
        .sd-greet { font-size: 21px; }
        .sd-stat { flex-direction: column; align-items: flex-start; gap: 8px; padding: 14px; }
        .sd-stat-ico { width: 34px; height: 34px; }
        .sd-stat-num { font-size: 21px; }
        .sd-stat-label { font-size: 12px; }
        .sd-class { grid-template-columns: 84px minmax(0, 1fr); padding: 12px 16px; }
        .sd-class .sd-tag { grid-column: 2; justify-self: start; }
        .sd-panel-head, .sd-todo, .sd-subj, .sd-ann, .sd-empty { padding-left: 16px; padding-right: 16px; }
    }
    @media (prefers-reduced-motion: reduce) { .sd-in { animation: none; } }
</style>

@php
    $chevron = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>';
    $now = now();
    // Mark the class in progress and the first one still to come, so the student sees where to be
    $nextMarked = false;
    $classes = $todayClasses->map(function ($c) use ($now, &$nextMarked) {
        $start = $now->copy()->setTimeFromTimeString($c->start_time);
        $end   = $now->copy()->setTimeFromTimeString($c->end_time);
        $state = $now->gte($end) ? 'done' : ($now->gte($start) ? 'now' : 'later');
        if ($state === 'later' && !$nextMarked) {
            $state = 'next';
            $nextMarked = true;
        }
        return (object) ['c' => $c, 'start' => $start, 'end' => $end, 'state' => $state];
    });
@endphp

<div class="sd">
    <header class="sd-head sd-in">
        <div>
            <h1 class="sd-greet">Good {{ $now->hour < 12 ? 'morning' : ($now->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', $user->name)[0] }}</h1>
            <p class="sd-date">{{ $now->format('l, F j, Y') }}</p>
        </div>
        @if($enrollment)
            <p class="sd-enrolled" aria-label="Enrollment">
                <span><b>Grade {{ $grade }}</b></span><span class="sep" aria-hidden="true"></span>
                <span><b>{{ $section->name ?? '—' }}</b></span><span class="sep" aria-hidden="true"></span>
                <span>SY {{ $enrollment->school_year }}</span>
                <span class="sd-pill">Enrolled</span>
            </p>
        @endif
    </header>

    {{-- Not enrolled yet: the real next step (form / waiting for review / returned) --}}
    @unless($enrollment)
        <div class="sd-notice sd-in" role="status">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
            <div>
                @if($enrollmentRequest?->status === 'pending')
                    <p class="sd-notice-title">Enrollment form submitted</p>
                    <p class="sd-notice-text">Your adviser is reviewing it. Once approved, you'll be placed in a section and your subjects, modules and grades will appear here.</p>
                @elseif($enrollmentRequest?->status === 'rejected')
                    <p class="sd-notice-title">Your adviser returned your enrollment form</p>
                    <p class="sd-notice-text">
                        @if($enrollmentRequest->remarks) Reason: {{ $enrollmentRequest->remarks }}<br> @endif
                        Fix the details and submit it again — your previous answers are already filled in.
                    </p>
                    <a href="{{ route('student.enroll') }}" class="sd-notice-btn">Open enrollment form</a>
                @else
                    <p class="sd-notice-title">Next step: fill out the enrollment form</p>
                    <p class="sd-notice-text">Your adviser needs it to place you in a section. It takes about 5 minutes.</p>
                    <a href="{{ route('student.enroll') }}" class="sd-notice-btn">Fill out the form</a>
                @endif
            </div>
        </div>
    @endunless

    <nav class="sd-card sd-stats sd-in" aria-label="Summary">
        <a class="sd-stat" href="{{ route('student.subjects') }}">
            <span class="sd-stat-ico" aria-hidden="true"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></span>
            <span><span class="sd-stat-num">{{ $subjectCount }}</span><span class="sd-stat-label">{{ \Illuminate\Support\Str::plural('Subject', $subjectCount) }}</span></span>
        </a>
        <a class="sd-stat" href="{{ route('student.quizzes') }}">
            <span class="sd-stat-ico {{ $pendingAssignments ? 'warn' : '' }}" aria-hidden="true"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-2"/><rect x="9" y="2" width="6" height="4" rx="1"/><path d="m9 14 2 2 4-4"/></svg></span>
            <span><span class="sd-stat-num">{{ $pendingAssignments }}</span><span class="sd-stat-label">To submit</span></span>
        </a>
        <a class="sd-stat" href="{{ route('student.messages') }}">
            <span class="sd-stat-ico" aria-hidden="true"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></span>
            <span><span class="sd-stat-num">{{ $unreadMessages }}</span><span class="sd-stat-label">Unread {{ $unreadMessages === 1 ? 'message' : 'messages' }}</span></span>
        </a>
    </nav>

    <div class="sd-grid">
        <div class="sd-col">
            <section class="sd-card sd-in" aria-labelledby="sdToday">
                <div class="sd-panel-head">
                    <div>
                        <h2 class="sd-panel-title" id="sdToday">Today's classes</h2>
                        <p class="sd-panel-sub">{{ $now->format('l') }}</p>
                    </div>
                    <a class="sd-link" href="{{ route('student.schedule') }}">Full schedule {!! $chevron !!}</a>
                </div>
                @forelse($classes as $row)
                    <div class="sd-class {{ in_array($row->state, ['now', 'done'], true) ? $row->state : '' }}">
                        <p class="sd-class-time">{{ $row->start->format('g:i A') }}<span>to {{ $row->end->format('g:i A') }}</span></p>
                        <div style="min-width:0;">
                            <p class="sd-class-name">{{ $row->c->subject->name ?? 'Subject' }}</p>
                            <p class="sd-class-meta">{{ $row->c->teacher->name ?? 'Teacher to be assigned' }}{{ $row->c->room ? ' · ' . $row->c->room : '' }}</p>
                        </div>
                        @if($row->state === 'now')
                            <span class="sd-tag now">Now</span>
                        @elseif($row->state === 'next')
                            <span class="sd-tag next">Next</span>
                        @elseif($row->state === 'done')
                            <span class="sd-tag done">Done</span>
                        @endif
                    </div>
                @empty
                    <div class="sd-empty">
                        @if(!$enrollment)
                            <b>No classes yet</b>Your schedule appears here once you are placed in a section.
                        @elseif($now->isWeekend())
                            <b>No classes today</b>Your schedule picks up again on Monday.
                        @else
                            <b>No classes scheduled today</b>Check the full schedule for the rest of the week.
                        @endif
                    </div>
                @endforelse
            </section>

            <section class="sd-card sd-in" aria-labelledby="sdTodo">
                <div class="sd-panel-head">
                    <div>
                        <h2 class="sd-panel-title" id="sdTodo">To submit</h2>
                        <p class="sd-panel-sub">Quizzes and activities you haven't turned in</p>
                    </div>
                    <a class="sd-link" href="{{ route('student.quizzes') }}">All quizzes {!! $chevron !!}</a>
                </div>
                @forelse($dueSoon as $a)
                    @php $soon = $a->due_date && $a->due_date->lte($now->copy()->addDays(2)); @endphp
                    <a class="sd-todo" href="{{ route('student.quizzes.show', $a) }}">
                        <span class="sd-todo-body">
                            <span class="sd-todo-title">{{ $a->title }}</span>
                            <span class="sd-todo-meta">{{ $a->subject->name ?? 'Subject' }}{{ $a->due_date ? ' · Due ' . $a->due_date->format('D, M j · g:i A') : '' }}</span>
                        </span>
                        <span class="sd-due {{ $soon ? 'soon' : '' }}">{{ $a->due_date ? 'Due ' . $a->due_date->diffForHumans() : 'No deadline' }}</span>
                    </a>
                @empty
                    <div class="sd-empty"><b>You're all caught up</b>New quizzes and activities from your teachers appear here.</div>
                @endforelse
            </section>
        </div>

        <div class="sd-col">
            <section class="sd-card sd-in" aria-labelledby="sdSubjects">
                <div class="sd-panel-head">
                    <h2 class="sd-panel-title" id="sdSubjects">My subjects</h2>
                    <a class="sd-link" href="{{ route('student.modules') }}">Modules {!! $chevron !!}</a>
                </div>
                @forelse($subjects as $ts)
                    <div class="sd-subj">
                        <span class="sd-subj-mark" aria-hidden="true">{{ mb_strtoupper(mb_substr($ts->subject?->name ?? '?', 0, 2)) }}</span>
                        <div style="min-width:0;">
                            <p class="sd-subj-name">{{ $ts->subject?->name ?? 'Subject removed' }}</p>
                            <p class="sd-subj-teacher">{{ $ts->teacher->name ?? 'Teacher to be assigned' }}</p>
                        </div>
                    </div>
                @empty
                    <div class="sd-empty"><b>No subjects yet</b>They appear once you are placed in a section.</div>
                @endforelse
            </section>

            <section class="sd-card sd-in" aria-labelledby="sdAnn">
                <div class="sd-panel-head"><h2 class="sd-panel-title" id="sdAnn">Announcements</h2></div>
                @forelse($announcements as $announcement)
                    <article class="sd-ann">
                        <h3 class="sd-ann-title">{{ $announcement->title }}</h3>
                        <p class="sd-ann-body">{{ $announcement->body }}</p>
                        <p class="sd-ann-meta">{{ $announcement->author->name ?? 'School Administration' }} · <time datetime="{{ $announcement->created_at->toIso8601String() }}">{{ $announcement->created_at->diffForHumans() }}</time></p>
                    </article>
                @empty
                    <div class="sd-empty"><b>No announcements</b>Updates from your teachers and the school appear here.</div>
                @endforelse
            </section>
        </div>
    </div>
</div>
@endsection
