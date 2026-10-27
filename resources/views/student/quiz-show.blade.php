@extends('layouts.app')
@section('title', $assignment->title)

@section('content')
<style>
    .qs-wrap { max-width: 720px; margin: 0 auto; }
    .qs-back { display: inline-flex; align-items: center; gap: 6px; min-height: 36px; color: var(--text-2); font-size: 13px; font-weight: 600; text-decoration: none; margin-bottom: 12px; }
    .qs-back:hover { color: var(--accent); }
    .qs-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); padding: 22px 24px; margin-bottom: 18px; box-shadow: var(--shadow-sm); }
    .qs-title { font-size: 22px; font-weight: 800; color: var(--text-1); overflow-wrap: anywhere; }
    .qs-meta { font-size: 13px; color: var(--text-2); margin-top: 6px; }
    .qs-closed { color: #B91C1C; font-weight: 700; }
    .qs-instr { font-size: 14px; color: #334155; margin-top: 16px; line-height: 1.65; white-space: pre-wrap; overflow-wrap: anywhere; }
    .qs-file { display: inline-flex; align-items: center; gap: 8px; min-height: 42px; margin-top: 16px; background: var(--accent-lt); color: var(--accent-dark); font-weight: 700; font-size: 13px; padding: 0 15px; border-radius: 10px; text-decoration: none; }
    .qs-file:hover { background: #DBEAFE; }

    .qs-graded { background: #F0FDF4; border: 1px solid #86EFAC; border-radius: 14px; padding: 16px 18px; margin-bottom: 18px; }
    .qs-graded h2 { font-size: 12px; font-weight: 800; letter-spacing: .06em; color: #166534; margin-bottom: 4px; }
    .qs-score { font-size: 28px; font-weight: 800; color: #15803D; }
    .qs-score small { font-size: 16px; color: var(--text-2); }
    .qs-feedback { font-size: 13.5px; color: #334155; margin-top: 6px; line-height: 1.55; white-space: pre-wrap; }

    .qs-h { font-size: 16px; font-weight: 700; color: var(--text-1); margin-bottom: 14px; }
    .qs-info { background: var(--accent-lt); border-radius: 10px; padding: 10px 14px; margin-bottom: 16px; font-size: 13px; color: #1E40AF; }
    .qs-info a { color: #1E40AF; font-weight: 700; margin-left: 6px; }
    .qs-errors { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 10px; padding: 10px 14px; margin-bottom: 16px; font-size: 13px; }
    .qs-errors ul { padding-left: 18px; }

    .qs-field { margin-bottom: 18px; }
    .qs-label { display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 7px; }
    .qs-label span { font-weight: 500; color: var(--text-3); }
    .qs-textarea, .qs-input { width: 100%; border: 1.5px solid var(--border); border-radius: 11px; font-size: 14px; font-family: inherit; background: #F8FAFC; color: var(--text-1); }
    .qs-textarea { padding: 12px; min-height: 140px; resize: vertical; line-height: 1.55; }
    .qs-input { padding: 10px; }
    .qs-textarea:focus, .qs-input:focus { outline: none; border-color: var(--accent); background: #fff; box-shadow: 0 0 0 4px var(--accent-glow); }
    .qs-hint { font-size: 12px; color: var(--text-3); margin-top: 5px; }

    .qs-submit { width: 100%; min-height: 48px; background: var(--accent); color: #fff; font-weight: 700; font-size: 15px; border: none; border-radius: 12px; cursor: pointer; font-family: inherit; box-shadow: 0 6px 16px var(--accent-glow); }
    .qs-submit:hover { background: var(--accent-dark); }
    .qs-submit[disabled] { opacity: .7; cursor: progress; }
    .qs-note { font-size: 12.5px; color: var(--text-2); margin-top: 12px; text-align: center; }
    .qs-locked { background: #F8FAFC; border: 1px solid var(--border); border-radius: 10px; padding: 14px; font-size: 13.5px; color: var(--text-2); }
</style>

@php
    $isGraded = $submission && $submission->status === 'graded';
    $isClosed = $assignment->isOverdue();
    // asset(): dati Storage::url() — nakadepende sa APP_URL, kaya sira kapag iba ang host/subfolder
    $fileUrl  = fn ($path) => asset('storage/' . ltrim($path, '/'));
@endphp

<div class="qs-wrap">
    <a href="{{ route('student.quizzes') }}" class="qs-back">← Back to quizzes</a>

    <section class="qs-card" aria-labelledby="qs-title">
        <h1 class="qs-title" id="qs-title">{{ $assignment->title }}</h1>
        <p class="qs-meta">
            {{ $assignment->subject?->name ?? 'Subject' }} ·
            @if($assignment->due_date)
                Due {{ $assignment->due_date->format('M j, Y g:i A') }}
                @if($isClosed) <span class="qs-closed">(closed)</span> @else ({{ $assignment->due_date->diffForHumans() }}) @endif
            @else
                No deadline
            @endif
            · {{ $assignment->max_score }} pts
        </p>
        @if($assignment->instructions)
            <div class="qs-instr">{{ $assignment->instructions }}</div>
        @endif
        @if($assignment->file_path)
            <a href="{{ $fileUrl($assignment->file_path) }}" target="_blank" rel="noopener" class="qs-file">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download quiz file
            </a>
        @endif
    </section>

    @if($isGraded)
        <section class="qs-graded" aria-label="Your grade">
            <h2>YOUR GRADE</h2>
            <p class="qs-score">{{ $submission->score }}<small>/{{ $assignment->max_score }}</small></p>
            @if($submission->feedback)
                <p class="qs-feedback"><strong>Teacher feedback:</strong> {{ $submission->feedback }}</p>
            @endif
        </section>
    @endif

    <section class="qs-card" aria-labelledby="qs-answer">
        <h2 class="qs-h" id="qs-answer">
            @if($isGraded || $isClosed)
                {{ $submission ? 'Your submission' : 'Submission closed' }}
            @else
                {{ $submission ? 'Update your answer' : 'Submit your answer' }}
            @endif
        </h2>

        @if($submission)
            <div class="qs-info">
                You submitted {{ $submission->submitted_at?->diffForHumans() }}.
                @if($submission->file_path)
                    <a href="{{ $fileUrl($submission->file_path) }}" target="_blank" rel="noopener">View your file</a>
                @endif
            </div>
        @endif

        {{-- Dati: error ng file lang ang lumalabas; tahimik ang error ng typed answer --}}
        @if($errors->any())
            <div class="qs-errors" role="alert">
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @if(!$isGraded && !$isClosed)
            <form method="POST" action="{{ route('student.quizzes.submit', $assignment) }}" enctype="multipart/form-data" id="qsForm">
                @csrf
                <div class="qs-field">
                    <label class="qs-label" for="qs-remarks">Your answer <span>— optional if you upload a file</span></label>
                    <textarea id="qs-remarks" name="remarks" class="qs-textarea" maxlength="20000"
                              placeholder="Type your answer here…">{{ old('remarks', $submission->remarks ?? '') }}</textarea>
                </div>
                <div class="qs-field">
                    <label class="qs-label" for="qs-file">Or upload your answer file</label>
                    <input type="file" id="qs-file" name="file" class="qs-input" aria-describedby="qs-file-hint"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.ppt,.pptx,.xls,.xlsx">
                    <p class="qs-hint" id="qs-file-hint">PDF, Word, image, PowerPoint, or Excel · up to 10 MB</p>
                </div>
                <button type="submit" class="qs-submit">{{ $submission ? 'Update submission' : 'Submit answer' }}</button>
                <p class="qs-note">You can change your answer until your teacher grades it{{ $assignment->due_date ? ' or the deadline passes' : '' }}.</p>
            </form>
        @else
            <p class="qs-locked">
                {{ $isGraded ? 'This activity has been graded. Submissions are locked.' : 'This quiz is closed — the deadline has passed.' }}
            </p>
        @endif
    </section>
</div>

@if(!$isGraded && !$isClosed)
<script>
    // No double submit while a file uploads; the Back button restores the page with it re-enabled
    (function () {
        const form = document.getElementById('qsForm');
        const btn  = form.querySelector('button[type="submit"]');
        const label = btn.textContent;
        form.addEventListener('submit', function () { btn.disabled = true; btn.textContent = 'Submitting…'; });
        window.addEventListener('pageshow', function (e) { if (e.persisted) { btn.disabled = false; btn.textContent = label; } });
    })();
</script>
@endif
@endsection
