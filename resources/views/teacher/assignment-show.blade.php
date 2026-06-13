@extends('layouts.teacher')
@section('title', $assignment->title)

@section('content')
<style>
    .sb-wrap { max-width:980px; margin:0 auto; padding:4px 0 48px; font-family:'Plus Jakarta Sans', system-ui, sans-serif; }
    .sb-back { display:inline-flex; align-items:center; gap:6px; color:#94A3B8; font-size:13px; font-weight:500; text-decoration:none; margin-bottom:18px; }
    .sb-back:hover { color:#2563EB; }
    .sb-flash { background:#F0FDF4; border:1px solid #86EFAC; color:#166534; padding:12px 16px; border-radius:12px; margin-bottom:18px; font-size:14px; font-weight:600; }
    .sb-head { background:#fff; border:1px solid #E2E8F0; border-radius:18px; padding:22px; margin-bottom:22px; box-shadow:0 1px 3px rgba(15,23,42,.05); }
    .sb-title { font-size:22px; font-weight:800; color:#0F172A; }
    .sb-meta { font-size:13px; color:#64748B; margin-top:6px; }
    .sb-instr { font-size:14px; color:#334155; margin-top:14px; line-height:1.6; white-space:pre-wrap; }
    .sb-file { display:inline-flex; align-items:center; gap:8px; margin-top:14px; background:#EFF6FF; color:#1D4ED8; font-weight:600; font-size:13px; padding:9px 14px; border-radius:10px; text-decoration:none; }
    .sb-sec-title { font-size:16px; font-weight:700; color:#0F172A; margin-bottom:14px; }
    .sb-sub { background:#fff; border:1px solid #E2E8F0; border-radius:16px; padding:18px; margin-bottom:14px; box-shadow:0 1px 3px rgba(15,23,42,.04); }
    .sb-sub-head { display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; }
    .sb-name { font-size:15px; font-weight:700; color:#0F172A; }
    .sb-when { font-size:12px; color:#94A3B8; }
    .sb-pill { font-size:11px; font-weight:700; padding:3px 9px; border-radius:99px; }
    .sb-graded { background:#F0FDF4; color:#166534; }
    .sb-pending { background:#FEF9C3; color:#854D0E; }
    .sb-ans { font-size:13.5px; color:#475569; margin-top:10px; white-space:pre-wrap; line-height:1.5; }
    .sb-dl { display:inline-flex; align-items:center; gap:6px; margin-top:10px; color:#2563EB; font-weight:600; font-size:13px; text-decoration:none; }
    .sb-grade-form { display:flex; align-items:flex-end; gap:10px; margin-top:14px; padding-top:14px; border-top:1px solid #F1F5F9; flex-wrap:wrap; }
    .sb-grade-form label { font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px; }
    .sb-score { width:90px; border:1.5px solid #E2E8F0; border-radius:10px; padding:9px; font-size:14px; font-family:inherit; }
    .sb-remarks { flex:1; min-width:160px; border:1.5px solid #E2E8F0; border-radius:10px; padding:9px; font-size:13px; font-family:inherit; }
    .sb-save { background:#16A34A; color:#fff; border:none; font-weight:700; font-size:13px; padding:10px 16px; border-radius:10px; cursor:pointer; }
    .sb-save:hover { background:#15803D; }
    .sb-empty { text-align:center; padding:50px 20px; color:#94A3B8; background:#fff; border:1px dashed #CBD5E1; border-radius:16px; }
</style>

<div class="sb-wrap">
    <a href="{{ route('teacher.assignments.index') }}" class="sb-back">← Back to quizzes</a>

    @if(session('success'))
        <div class="sb-flash">{{ session('success') }}</div>
    @endif

    <div class="sb-head">
        <div class="sb-title">{{ $assignment->title }}</div>
        <div class="sb-meta">
            {{ $assignment->subject?->name }} • {{ $assignment->section?->name }} •
            @if($assignment->due_date) Due {{ $assignment->due_date->format('M d, Y g:i A') }} @else No deadline @endif
            • {{ $assignment->max_score }} pts • {{ $assignment->is_published ? 'Published' : 'Draft' }}
        </div>
        @if($assignment->instructions)
            <div class="sb-instr">{{ $assignment->instructions }}</div>
        @endif
        @if($assignment->file_path)
            <a href="{{ Storage::url($assignment->file_path) }}" target="_blank" class="sb-file">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download quiz file
            </a>
        @endif
    </div>

    <div class="sb-sec-title">Submissions ({{ $submissions->count() }})</div>

    @forelse($submissions as $s)
        <div class="sb-sub">
            <div class="sb-sub-head">
                <div>
                    <span class="sb-name">{{ $s->student?->name ?? 'Student' }}</span>
                    <span class="sb-when">• {{ $s->submitted_at?->diffForHumans() }}</span>
                </div>
                <span class="sb-pill {{ $s->status === 'graded' ? 'sb-graded' : 'sb-pending' }}">
                    {{ $s->status === 'graded' ? 'Graded: '.$s->score.'/'.$assignment->max_score : 'Needs grading' }}
                </span>
            </div>

            @if($s->remarks)
                <div class="sb-ans"><strong>Answer/Note:</strong> {{ $s->remarks }}</div>
            @endif
            @if($s->file_path)
                <a href="{{ Storage::url($s->file_path) }}" target="_blank" class="sb-dl">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download student's file
                </a>
            @endif

            <form method="POST" action="{{ route('teacher.submissions.grade', $s) }}" class="sb-grade-form">
                @csrf
                <div>
                    <label>Score (max {{ $assignment->max_score }})</label>
                    <input type="number" name="score" class="sb-score" min="0" max="{{ $assignment->max_score }}" value="{{ $s->score }}" required>
                </div>
                <div style="flex:1">
                    <label>Remarks (optional)</label>
                    <input type="text" name="remarks" class="sb-remarks" value="{{ $s->remarks }}" placeholder="Feedback for the student...">
                </div>
                <button type="submit" class="sb-save">Save Grade</button>
            </form>
        </div>
    @empty
        <div class="sb-empty">No submissions yet. Students will appear here once they submit.</div>
    @endforelse
</div>
@endsection