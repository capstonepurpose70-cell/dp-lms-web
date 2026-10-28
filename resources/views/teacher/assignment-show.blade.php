@extends('layouts.teacher')
@section('title', $assignment->title)

@section('content')
<style>
    .qs-head { padding: 22px; }
    .qs-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 12px; margin-top: 8px; font-size: 13px; color: var(--text-2); }
    .qs-instr { font-size: 14px; color: var(--text-2); margin-top: 14px; line-height: 1.6; white-space: pre-line; overflow-wrap: anywhere; }
    .qs-file { margin-top: 14px; }
    .qs-err { background: #FEF2F2; border: 1px solid #FECACA; color: #B91C1C; padding: 12px 16px; border-radius: var(--r-md); margin-bottom: 14px; font-size: 14px; font-weight: 600; }

    .qs-h2 { display: flex; align-items: baseline; gap: 10px; font-size: 16px; font-weight: 800; color: var(--text-1); margin: 22px 0 12px; }
    .qs-h2 span { font-size: 13px; font-weight: 600; color: var(--text-2); }

    .qs-sub { padding: 18px; margin-bottom: 12px; }
    .qs-sub-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px 12px; flex-wrap: wrap; }
    .qs-name { font-size: 15px; font-weight: 700; color: var(--text-1); }
    .qs-when { display: block; font-size: 12.5px; color: var(--text-3); margin-top: 2px; }
    .qs-ans { font-size: 14px; color: var(--text-2); margin-top: 12px; line-height: 1.55; white-space: pre-line; overflow-wrap: anywhere; }
    .qs-ans b { color: var(--text-1); }
    .qs-dl { margin-top: 10px; }

    .qs-grade { display: grid; grid-template-columns: 120px minmax(0, 1fr) auto; align-items: end; gap: 10px; margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--border); }
    .qs-grade label { display: block; font-size: 12.5px; font-weight: 700; color: var(--text-2); margin-bottom: 5px; }
    .qs-input { width: 100%; min-height: 44px; border: 1.5px solid var(--border); border-radius: var(--r-md); padding: 0 12px; font-size: 14px; font-family: inherit; color: var(--text-1); background: #F8FAFC; }
    .qs-input:focus { outline: none; border-color: var(--accent); background: #fff; box-shadow: 0 0 0 3px var(--accent-glow); }

    @media (max-width: 600px) {
        .qs-grade { grid-template-columns: 1fr; }
    }
</style>

@php $closed = $assignment->due_date && $assignment->due_date->isPast(); @endphp

<div class="tp-page">
    <a href="{{ route('teacher.assignments.index') }}" class="tp-back">
        <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Quizzes and assignments
    </a>

    @if($errors->any())
        <div class="qs-err" role="alert">{{ $errors->first() }}</div>
    @endif

    <section class="tp-card qs-head">
        <h1 class="tp-title">{{ $assignment->title }}</h1>
        <p class="qs-meta">
            <span class="tp-pill {{ $assignment->is_published ? 'ok' : 'muted' }}">{{ $assignment->is_published ? 'Published' : 'Draft' }}</span>
            @if($closed)<span class="tp-pill muted">Closed</span>@endif
            <span>{{ $assignment->subject?->name ?? 'Subject removed' }}</span>
            <span>{{ $assignment->section?->name ?? 'Section removed' }}</span>
            <span>{{ $assignment->max_score }} points</span>
            <span>
                @if(!$assignment->due_date) No deadline
                @elseif($closed) Closed {{ $assignment->due_date->format('M j, Y g:i A') }}
                @else Open until {{ $assignment->due_date->format('M j, Y g:i A') }}
                @endif
            </span>
        </p>
        @if($assignment->instructions)
            <p class="qs-instr">{{ $assignment->instructions }}</p>
        @endif
        @if($assignment->file_path)
            <div class="qs-file">
                <a href="{{ asset('storage/' . ltrim($assignment->file_path, '/')) }}" target="_blank" rel="noopener" class="tp-btn ghost sm">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Quiz file
                </a>
            </div>
        @endif
    </section>

    @php $toGrade = $submissions->where('status', '!=', 'graded')->count(); @endphp
    <h2 class="qs-h2">Submissions <span>{{ $submissions->count() }} received{{ $toGrade ? ", {$toGrade} to grade" : '' }}</span></h2>

    @forelse($submissions as $s)
        <article class="tp-card qs-sub" aria-label="Submission of {{ $s->student?->name ?? 'a student' }}">
            <div class="qs-sub-head">
                <div>
                    <span class="qs-name">{{ $s->student?->name ?? 'Student' }}</span>
                    @if($s->submitted_at)
                        <time class="qs-when" datetime="{{ $s->submitted_at->toIso8601String() }}">Submitted {{ $s->submitted_at->format('M j, g:i A') }}</time>
                    @endif
                </div>
                <span class="tp-pill {{ $s->status === 'graded' ? 'ok' : 'warn' }}">
                    {{ $s->status === 'graded' ? 'Graded ' . $s->score . ' / ' . $assignment->max_score : 'To grade' }}
                </span>
            </div>

            @if($s->remarks)
                <p class="qs-ans"><b>Answer:</b> {{ $s->remarks }}</p>
            @endif
            @if($s->file_path)
                <div class="qs-dl">
                    <a href="{{ asset('storage/' . ltrim($s->file_path, '/')) }}" target="_blank" rel="noopener" class="tp-btn ghost sm">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Student's file
                    </a>
                </div>
            @endif

            <form method="POST" action="{{ route('teacher.submissions.grade', $s) }}" class="qs-grade" data-once>
                @csrf
                <div>
                    <label for="score-{{ $s->id }}">Score (max {{ $assignment->max_score }})</label>
                    <input type="number" id="score-{{ $s->id }}" name="score" class="qs-input" min="0" max="{{ $assignment->max_score }}" value="{{ $s->score }}" required>
                </div>
                <div>
                    <label for="feedback-{{ $s->id }}">Feedback (optional)</label>
                    {{-- `feedback` — never overwrites the student's answer (`remarks`) --}}
                    <input type="text" id="feedback-{{ $s->id }}" name="feedback" class="qs-input" value="{{ $s->feedback }}" maxlength="5000" placeholder="What they did well, what to improve">
                </div>
                <button type="submit" class="tp-btn">{{ $s->status === 'graded' ? 'Update grade' : 'Save grade' }}</button>
            </form>
        </article>
    @empty
        <div class="tp-card tp-empty">
            <b>No submissions yet</b>
            {{ $closed ? 'The deadline has passed and nobody submitted.' : 'Students appear here as soon as they submit.' }}
        </div>
    @endforelse
</div>

<script>
    // One click = one save; the browser's Back cache could otherwise show the button stuck
    document.querySelectorAll('.qs-grade[data-once]').forEach(function (form) {
        var btn = form.querySelector('button[type="submit"]');
        var label = btn.textContent;
        form.addEventListener('submit', function () { btn.disabled = true; btn.textContent = 'Saving…'; });
        window.addEventListener('pageshow', function () { btn.disabled = false; btn.textContent = label; });
    });
</script>
@endsection
