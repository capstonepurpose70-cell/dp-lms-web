@extends('layouts.teacher')
@section('title', 'Create Quiz')

@section('content')
<style>
    .cq-wrap { max-width: 720px; margin:0 auto; padding:4px 0 48px; font-family:'Plus Jakarta Sans', system-ui, sans-serif; }
    .cq-back { display:inline-flex; align-items:center; gap:6px; color:#94A3B8; font-size:13px; font-weight:500; text-decoration:none; margin-bottom:20px; }
    .cq-back:hover { color:#2563EB; }
    .cq-title { font-size:24px; font-weight:800; color:#0F172A; margin-bottom:4px; }
    .cq-sub { font-size:13px; color:#64748B; margin-bottom:24px; }
    .cq-card { background:#fff; border:1px solid #E2E8F0; border-radius:18px; padding:26px; box-shadow:0 1px 3px rgba(15,23,42,.05); }
    .cq-label { display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:7px; }
    .cq-input, .cq-select, .cq-textarea {
        width:100%; border:1.5px solid #E2E8F0; border-radius:11px; padding:11px 13px; font-size:14px; color:#0F172A;
        font-family:inherit; background:#F8FAFC; transition:.18s; box-sizing:border-box;
    }
    .cq-input:focus, .cq-select:focus, .cq-textarea:focus { outline:none; border-color:#2563EB; background:#fff; box-shadow:0 0 0 4px rgba(37,99,235,.10); }
    .cq-textarea { min-height:110px; resize:vertical; }
    .cq-field { margin-bottom:18px; }
    .cq-grid2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .cq-err { color:#DC2626; font-size:12.5px; margin-top:5px; }
    .cq-check { display:flex; align-items:center; gap:10px; font-size:14px; color:#334155; }
    .cq-check input { width:18px; height:18px; accent-color:#2563EB; }
    .cq-submit { width:100%; background:#2563EB; color:#fff; font-weight:700; font-size:15px; border:none; padding:14px; border-radius:12px; cursor:pointer; box-shadow:0 6px 16px rgba(37,99,235,.28); transition:.2s; }
    .cq-submit:hover { background:#1D4ED8; transform:translateY(-1px); }
    .cq-hint { font-size:12px; color:#94A3B8; margin-top:5px; }
    @media(max-width:560px){ .cq-grid2{ grid-template-columns:1fr; } }
</style>

<div class="cq-wrap">
    <a href="{{ route('teacher.assignments.index') }}" class="cq-back">← Back to quizzes</a>
    <div class="cq-title">Create Quiz / Assignment</div>
    <div class="cq-sub">Upload the file students will answer, set a deadline, then publish.</div>

    <form method="POST" action="{{ route('teacher.assignments.store') }}" enctype="multipart/form-data" class="cq-card">
        @csrf

        <div class="cq-field">
            <label class="cq-label">Subject &amp; Section</label>
            <select name="subject_section" class="cq-select" id="pairSelect" onchange="syncPair()" required>
                <option value="">— Select subject &amp; section —</option>
                @foreach($pairs as $p)
                    <option value="{{ $p['subject_id'] }}|{{ $p['section_id'] }}"
                        {{ old('subject_id').'|'.old('section_id') == $p['subject_id'].'|'.$p['section_id'] ? 'selected' : '' }}>
                        {{ $p['subject_name'] }} — {{ $p['section_name'] }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="subject_id" id="subjectId" value="{{ old('subject_id') }}">
            <input type="hidden" name="section_id" id="sectionId" value="{{ old('section_id') }}">
            @error('subject_id') <div class="cq-err">{{ $message }}</div> @enderror
            @if($pairs->isEmpty())
                <div class="cq-hint" style="color:#DC2626">You have no assigned subjects/sections yet. Ask the admin to assign you first.</div>
            @endif
        </div>

        <div class="cq-field">
            <label class="cq-label">Title</label>
            <input type="text" name="title" class="cq-input" value="{{ old('title') }}" placeholder="e.g. Quiz 1 — Chapter 3" required>
            @error('title') <div class="cq-err">{{ $message }}</div> @enderror
        </div>

        <div class="cq-field">
            <label class="cq-label">Instructions <span style="color:#94A3B8;font-weight:400">(optional)</span></label>
            <textarea name="instructions" class="cq-textarea" placeholder="Write instructions for the students...">{{ old('instructions') }}</textarea>
        </div>

        <div class="cq-grid2">
            <div class="cq-field">
                <label class="cq-label">Deadline <span style="color:#94A3B8;font-weight:400">(optional)</span></label>
                <input type="datetime-local" name="due_date" class="cq-input" value="{{ old('due_date') }}">
            </div>
            <div class="cq-field">
                <label class="cq-label">Max Score</label>
                <input type="number" name="max_score" class="cq-input" value="{{ old('max_score', 100) }}" min="1" max="1000">
            </div>
        </div>

        <div class="cq-field">
            <label class="cq-label">Quiz File <span style="color:#94A3B8;font-weight:400">(optional)</span></label>
            <input type="file" name="file" class="cq-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.ppt,.pptx,.xls,.xlsx">
            <div class="cq-hint">PDF, Word, Image, PowerPoint, or Excel • max 10MB</div>
            @error('file') <div class="cq-err">{{ $message }}</div> @enderror
        </div>

        <div class="cq-field">
            <label class="cq-check">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', '1') ? 'checked' : '' }}>
                Publish now (students can see it immediately)
            </label>
        </div>

        <button type="submit" class="cq-submit" {{ $pairs->isEmpty() ? 'disabled style=opacity:.5;cursor:not-allowed' : '' }}>Create Quiz</button>
    </form>
</div>

<script>
    function syncPair() {
        const v = document.getElementById('pairSelect').value;
        const [subj, sec] = v.split('|');
        document.getElementById('subjectId').value = subj || '';
        document.getElementById('sectionId').value = sec || '';
    }
    syncPair();
</script>
@endsection