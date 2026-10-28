@extends('layouts.teacher')
@section('title', 'Quizzes & Assignments')

@section('content')
<style>
    /* Same row pattern as Learning materials: title + meta | state | actions */
    .qz-list { overflow: hidden; }
    .qz-row {
        display: grid; grid-template-columns: minmax(0, 1fr) auto auto; align-items: center;
        gap: 10px 16px; padding: 16px 20px; border-bottom: 1px solid var(--border);
    }
    .qz-row:last-child { border-bottom: none; }
    .qz-main { min-width: 0; }
    .qz-title { font-size: 15px; font-weight: 700; color: var(--text-1); overflow-wrap: anywhere; }
    .qz-meta { display: flex; flex-wrap: wrap; gap: 2px 12px; font-size: 12.5px; color: var(--text-3); margin-top: 3px; }
    .qz-due { font-size: 13px; color: var(--text-2); margin-top: 4px; }
    .qz-state { display: flex; flex-direction: column; align-items: flex-end; gap: 5px; }
    .qz-count { font-size: 12.5px; color: var(--text-2); white-space: nowrap; }
    .qz-count b { color: #92400E; }
    .qz-actions { display: flex; gap: 8px; }
    .qz-actions form { margin: 0; }
    .qz-pages { margin-top: 16px; }

    @media (max-width: 760px) {
        .qz-row { grid-template-columns: minmax(0, 1fr); padding: 14px 16px; }
        .qz-state { flex-direction: row; flex-wrap: wrap; align-items: center; gap: 6px 10px; }
        .qz-actions > * { flex: 1; }
        .qz-actions .tp-btn { width: 100%; }
    }
</style>

<div class="tp-page">
    <div class="tp-head">
        <div>
            <h1 class="tp-title">Quizzes and assignments</h1>
            <p class="tp-sub">Post an activity with a deadline; students submit their answers here and you grade them.</p>
        </div>
        <a href="{{ route('teacher.assignments.create') }}" class="tp-btn">
            <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Create quiz
        </a>
    </div>

    <div class="tp-card qz-list">
        @forelse($assignments as $a)
            @php
                $closed = $a->due_date && $a->due_date->isPast();
                $toGrade = (int) ($a->to_grade_count ?? 0);
            @endphp
            <article class="qz-row">
                <div class="qz-main">
                    <h2 class="qz-title">{{ $a->title }}</h2>
                    <p class="qz-meta">
                        <span>{{ $a->subject?->name ?? 'Subject removed' }}</span>
                        <span>{{ $a->section?->name ?? 'Section removed' }}</span>
                        <span>{{ $a->max_score }} points</span>
                    </p>
                    <p class="qz-due">
                        @if(!$a->due_date)
                            No deadline
                        @elseif($closed)
                            Closed {{ $a->due_date->format('M j, g:i A') }}
                        @else
                            Open until {{ $a->due_date->format('M j, g:i A') }}
                        @endif
                    </p>
                </div>
                <div class="qz-state">
                    <span>
                        <span class="tp-pill {{ $a->is_published ? 'ok' : 'muted' }}">{{ $a->is_published ? 'Published' : 'Draft' }}</span>
                        @if($closed)<span class="tp-pill muted">Closed</span>@endif
                    </span>
                    <span class="qz-count">
                        {{ $a->submissions_count }} {{ Str::plural('submission', $a->submissions_count) }}@if($toGrade), <b>{{ $toGrade }} to grade</b>@endif
                    </span>
                </div>
                <div class="qz-actions">
                    <a href="{{ route('teacher.assignments.show', $a) }}" class="tp-btn sm {{ $toGrade ? '' : 'ghost' }}">{{ $toGrade ? 'Grade' : 'Open' }}</a>
                    <form method="POST" action="{{ route('teacher.assignments.publish', $a) }}"
                          onsubmit="return confirm('{{ $a->is_published ? 'Unpublish this quiz? Students will no longer see it.' : 'Publish this quiz? Students in the section will be notified.' }}');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="tp-btn ghost sm">{{ $a->is_published ? 'Unpublish' : 'Publish' }}</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="tp-empty">
                <b>No quizzes yet</b>
                Create one for a class; students in that section are notified when it is published.
                <div style="margin-top:16px;"><a href="{{ route('teacher.assignments.create') }}" class="tp-btn">Create quiz</a></div>
            </div>
        @endforelse
    </div>

    @if($assignments->hasPages())
        <div class="qz-pages">{{ $assignments->links() }}</div>
    @endif
</div>
@endsection
