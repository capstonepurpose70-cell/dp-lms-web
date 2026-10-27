@extends('layouts.app')
@section('title', 'Quizzes')

@section('content')
<style>
    .sq-wrap { max-width: 920px; margin: 0 auto; }
    .sq-title { font-size: 24px; font-weight: 800; color: var(--text-1); letter-spacing: -.01em; }
    .sq-sub { font-size: 13.5px; color: var(--text-2); margin: 4px 0 22px; }

    .sq-card {
        display: flex; align-items: center; justify-content: space-between; gap: 14px;
        background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg);
        padding: 16px 20px; margin-bottom: 12px; box-shadow: var(--shadow-sm);
    }
    .sq-card.is-todo { border-left: 4px solid var(--accent); }
    .sq-main { min-width: 0; }
    .sq-name { font-size: 15.5px; font-weight: 700; color: var(--text-1); overflow-wrap: anywhere; }
    .sq-meta { font-size: 12.5px; color: var(--text-2); margin-top: 3px; }
    .sq-overdue { color: #B91C1C; font-weight: 700; }
    .sq-right { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }

    .sq-pill { font-size: 11.5px; font-weight: 700; padding: 4px 11px; border-radius: 99px; white-space: nowrap; }
    .sq-todo      { background: #FEF3C7; color: #92400E; }
    .sq-submitted { background: #DBEAFE; color: #1E40AF; }
    .sq-graded    { background: #DCFCE7; color: #166534; }
    .sq-missed    { background: #FEE2E2; color: #991B1B; }

    .sq-open {
        display: inline-flex; align-items: center; min-height: 40px; padding: 0 16px; border-radius: 10px;
        background: var(--accent); color: #fff; font-weight: 700; font-size: 13px; text-decoration: none; white-space: nowrap;
    }
    .sq-open:hover { background: var(--accent-dark); }
    .sq-open.is-quiet { background: var(--accent-lt); color: var(--accent-dark); }

    .sq-empty { text-align: center; padding: 56px 20px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); }
    .sq-empty b { display: block; font-size: 15px; color: var(--text-1); }
    .sq-empty p { font-size: 13px; color: var(--text-2); margin-top: 4px; }

    @media (max-width: 600px) {
        .sq-card { flex-direction: column; align-items: stretch; padding: 14px 16px; }
        .sq-right { justify-content: space-between; }
    }
</style>

<div class="sq-wrap">
    <h1 class="sq-title">Quizzes &amp; Assignments</h1>
    <p class="sq-sub">Things to do come first, sorted by deadline. Open one to read it and submit your answer.</p>

    @forelse($assignments as $a)
        @php
            $sub   = $a->mySubmission;
            $label = [
                'todo'      => 'To do',
                'submitted' => 'Submitted',
                'graded'    => 'Graded: ' . ($sub->score ?? '') . '/' . $a->max_score,
                'missed'    => 'Missed',
            ][$a->state];
        @endphp
        <article class="sq-card {{ $a->state === 'todo' ? 'is-todo' : '' }}">
            <div class="sq-main">
                <h2 class="sq-name">{{ $a->title }}</h2>
                <p class="sq-meta">
                    {{ $a->subject?->name ?? 'Subject' }} ·
                    @if($a->due_date)
                        Due {{ $a->due_date->format('M j, Y g:i A') }}
                        @if($a->isOverdue()) <span class="sq-overdue">(closed)</span>
                        @elseif($a->state === 'todo') ({{ $a->due_date->diffForHumans() }}) @endif
                    @else
                        No deadline
                    @endif
                    · {{ $a->max_score }} pts
                </p>
            </div>
            <div class="sq-right">
                <span class="sq-pill sq-{{ $a->state }}">{{ $label }}</span>
                <a href="{{ route('student.quizzes.show', $a) }}" class="sq-open {{ $a->state === 'todo' ? '' : 'is-quiet' }}">
                    {{ $a->state === 'todo' ? 'Answer' : 'View' }}<span class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);"> {{ $a->title }}</span>
                </a>
            </div>
        </article>
    @empty
        <div class="sq-empty">
            <b>No quizzes yet</b>
            <p>Quizzes and assignments from your teachers will show up here.</p>
        </div>
    @endforelse
</div>
@endsection
