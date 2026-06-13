@extends('layouts.app')
@section('title', $assignment->title)

@section('content')
<style>
    .qs-wrap { max-width:720px; margin:0 auto; padding:4px 0 48px; font-family:'Plus Jakarta Sans', system-ui, sans-serif; }
    .qs-back { display:inline-flex; align-items:center; gap:6px; color:#94A3B8; font-size:13px; font-weight:500; text-decoration:none; margin-bottom:18px; }
    .qs-back:hover { color:#2563EB; }
    .qs-card { background:#fff; border:1px solid #E2E8F0; border-radius:18px; padding:24px; margin-bottom:20px; box-shadow:0 1px 3px rgba(15,23,42,.05); }
    .qs-title { font-size:22px; font-weight:800; color:#0F172A; }
    .qs-meta { font-size:13px; color:#64748B; margin-top:6px; }
    .qs-instr { font-size:14px; color:#334155; margin-top:16px; line-height:1.6; white-space:pre-wrap; }
    .qs-file { display:inline-flex; align-items:center; gap:8px; margin-top:16px; background:#EFF6FF; color:#1D4ED8; font-weight:600; font-size:13px; padding:10px 15px; border-radius:10px; text-decoration:none; }
    .qs-graded { background:#F0FDF4; border:1px solid #86EFAC; border-radius:14px; padding:16px; margin-bottom:20px; }
    .qs-graded h4 { font-size:13px; font-weight:700; color:#166534; margin-bottom:4px; }
    .qs-score { font-size:28px; font-weight:800; color:#15803D; }
    .qs-label { display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:7px; }
    .qs-textarea { width:100%; border:1.5px solid #E2E8F0; border-radius:11px; padding:12px; font-size:14px; font-family:inherit; min-height:120px; resize:vertical; background:#F8FAFC; box-sizing:border-box; }
    .qs-textarea:focus { outline:none; border-color:#2563EB; background:#fff; box-shadow:0 0 0 4px rgba(37,99,235,.10); }
    .qs-input { width:100%; border:1.5px solid #E2E8F0; border-radius:11px; padding:11px; font-size:14px; font-family:inherit; background:#F8FAFC; box-sizing:border-box; }
    .qs-field { margin-bottom:18px; }
    .qs-hint { font-size:12px; color:#94A3B8; margin-top:5px; }
    .qs-err { color:#DC2626; font-size:12.5px; margin-top:5px; }
    .qs-submit { width:100%; background:#2563EB; color:#fff; font-weight:700; font-size:15px; border:none; padding:14px; border-radius:12px; cursor:pointer; box-shadow:0 6px 16px rgba(37,99,235,.28); transition:.2s; }
    .qs-submit:hover { background:#1D4ED8; transform:translateY(-1px); }
    .qs-note { font-size:12.5px; color:#64748B; margin-top:12px; text-align:center; }
    .qs-current { display:inline-flex; align-items:center; gap:6px; margin-top:8px; color:#2563EB; font-weight:600; font-size:13px; text-decoration:none; }
</style>

<div class="qs-wrap">
    <a href="{{ route('student.quizzes') }}" class="qs-back">← Back to quizzes</a>

    <div class="qs-card">
        <div class="qs-title">{{ $assignment->title }}</div>
        <div class="qs-meta">
            {{ $assignment->subject?->name }} •
            @if($assignment->due_date)
                Due {{ $assignment->due_date->format('M d, Y g:i A') }}
                @if($assignment->isOverdue()) <span style="color:#DC2626;font-weight:700">(Overdue)</span> @endif
            @else No deadline @endif
            • {{ $assignment->max_score }} pts
        </div>
        @if($assignment->instructions)
            <div class="qs-instr">{{ $assignment->instructions }}</div>
        @endif
        @if($assignment->file_path)
            <a href="{{ Storage::url($assignment->file_path) }}" target="_blank" class="qs-file">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download quiz file
            </a>
        @endif
    </div>

    @if($submission && $submission->status === 'graded')
        <div class="qs-graded">
            <h4>YOUR GRADE</h4>
            <div class="qs-score">{{ $submission->score }}<span style="font-size:16px;color:#64748B">/{{ $assignment->max_score }}</span></div>
            @if($submission->remarks)
                <div style="font-size:13px; color:#334155; margin-top:6px"><strong>Teacher remarks:</strong> {{ $submission->remarks }}</div>
            @endif
        </div>
    @endif

    <div class="qs-card">
        <div style="font-size:16px; font-weight:700; color:#0F172A; margin-bottom:16px">
            {{ $submission ? 'Update your answer' : 'Submit your answer' }}
        </div>

        @if($submission)
            <div style="background:#EFF6FF; border-radius:10px; padding:10px 14px; margin-bottom:16px; font-size:13px; color:#1D4ED8">
                You submitted {{ $submission->submitted_at?->diffForHumans() }}.
                @if($submission->file_path)
                    <a href="{{ Storage::url($submission->file_path) }}" target="_blank" class="qs-current">View your file</a>
                @endif
            </div>
        @endif

        <form method="POST" action="{{ route('student.quizzes.submit', $assignment) }}" enctype="multipart/form-data">
            @csrf
            <div class="qs-field">
                <label class="qs-label">Your answer (typed) <span style="color:#94A3B8;font-weight:400">— optional if uploading a file</span></label>
                <textarea name="remarks" class="qs-textarea" placeholder="Type your answer here...">{{ old('remarks', $submission->remarks ?? '') }}</textarea>
            </div>
            <div class="qs-field">
                <label class="qs-label">Or upload your answer file</label>
                <input type="file" name="file" class="qs-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.ppt,.pptx,.xls,.xlsx">
                <div class="qs-hint">PDF, Word, Image, PowerPoint, or Excel • max 10MB</div>
                @error('file') <div class="qs-err">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="qs-submit">{{ $submission ? 'Update Submission' : 'Submit Answer' }}</button>
            <div class="qs-note">You can re-submit anytime before your teacher grades it.</div>
        </form>
    </div>
</div>
@endsection