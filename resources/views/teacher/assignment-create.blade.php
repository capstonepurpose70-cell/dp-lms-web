@extends('layouts.teacher')
@section('title', 'Create Quiz')

@section('content')
<style>
    .cq-card { padding: 24px; }
    .cq-field { margin-bottom: 18px; }
    .cq-label { display: block; font-size: 13.5px; font-weight: 700; color: var(--text-1); margin-bottom: 7px; }
    .cq-opt { font-weight: 500; color: var(--text-3); }
    .cq-input {
        width: 100%; min-height: 44px; border: 1.5px solid var(--border); border-radius: var(--r-md); padding: 10px 13px;
        font-size: 14px; color: var(--text-1); font-family: inherit; background: #F8FAFC;
    }
    .cq-input:focus { outline: none; border-color: var(--accent); background: #fff; box-shadow: 0 0 0 3px var(--accent-glow); }
    .cq-input.is-error { border-color: #DC2626; }
    textarea.cq-input { min-height: 110px; resize: vertical; line-height: 1.5; }
    .cq-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .cq-err { color: #B91C1C; font-size: 12.5px; margin-top: 5px; }
    .cq-hint { font-size: 12.5px; color: var(--text-3); margin-top: 5px; }

    /* File picker: the real input stays in the form (keyboard + screen readers), styled through its label */
    .cq-file { position: relative; display: flex; align-items: center; gap: 12px; padding: 12px; border: 1.5px dashed #CBD5E1; border-radius: var(--r-md); background: #F8FAFC; }
    .cq-file input { position: absolute; width: 1px; height: 1px; opacity: 0; }
    .cq-file input:focus-visible + label { outline: 3px solid var(--accent); outline-offset: 2px; }
    .cq-file-name { flex: 1; min-width: 0; font-size: 13.5px; color: var(--text-2); overflow-wrap: anywhere; }
    .cq-file-clear { display: none; }
    .cq-file.has-file { border-style: solid; border-color: var(--accent); background: var(--accent-lt); }
    .cq-file.has-file .cq-file-name { color: var(--text-1); font-weight: 600; }
    .cq-file.has-file .cq-file-clear { display: inline-flex; }

    .cq-publish { display: flex; align-items: flex-start; gap: 12px; padding: 14px; border: 1.5px solid var(--border); border-radius: var(--r-md); cursor: pointer; }
    .cq-publish input { width: 20px; height: 20px; margin-top: 1px; accent-color: var(--accent); flex-shrink: 0; }
    .cq-publish b { display: block; font-size: 14px; color: var(--text-1); }
    .cq-publish span { font-size: 13px; color: var(--text-2); }
    .cq-actions { display: flex; gap: 10px; margin-top: 22px; flex-wrap: wrap; }
    .cq-warn { background: #FEF2F2; border: 1px solid #FECACA; color: #B91C1C; border-radius: var(--r-md); padding: 12px 14px; font-size: 13.5px; margin-bottom: 18px; }

    @media (max-width: 560px) {
        .cq-card { padding: 18px; }
        .cq-grid2 { grid-template-columns: 1fr; gap: 0; }
        .cq-actions .tp-btn { flex: 1; }
    }
</style>

<div class="tp-page tp-narrow">
    <a href="{{ route('teacher.assignments.index') }}" class="tp-back">
        <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Quizzes and assignments
    </a>
    <div class="tp-head">
        <div>
            <h1 class="tp-title">Create a quiz</h1>
            <p class="tp-sub">Pick the class, add instructions or a file, and set a deadline. Students submit their answers in DP-LMS.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('teacher.assignments.store') }}" enctype="multipart/form-data" class="tp-card cq-card" id="cqForm" novalidate>
        @csrf

        @if($pairs->isEmpty())
            <div class="cq-warn" role="alert">You have no class yet. The admin assigns classes under Class Schedules.</div>
        @endif

        <div class="cq-field">
            <label class="cq-label" for="pairSelect">Class</label>
            <select name="subject_section" class="cq-input @error('subject_id') is-error @enderror" id="pairSelect" required
                    @error('subject_id') aria-invalid="true" aria-describedby="err-class" @enderror>
                <option value="">Choose a class</option>
                @foreach($pairs as $p)
                    <option value="{{ $p['subject_id'] }}|{{ $p['section_id'] }}"
                        {{ old('subject_id').'|'.old('section_id') == $p['subject_id'].'|'.$p['section_id'] ? 'selected' : '' }}>
                        {{ $p['subject_name'] }}, {{ $p['section_name'] }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="subject_id" id="subjectId" value="{{ old('subject_id') }}">
            <input type="hidden" name="section_id" id="sectionId" value="{{ old('section_id') }}">
            @error('subject_id') <p class="cq-err" id="err-class">{{ $message }}</p> @enderror
        </div>

        <div class="cq-field">
            <label class="cq-label" for="cqTitle">Title</label>
            <input type="text" id="cqTitle" name="title" class="cq-input @error('title') is-error @enderror" value="{{ old('title') }}"
                   placeholder="Quiz 1: Fractions" maxlength="255" required @error('title') aria-invalid="true" aria-describedby="err-title" @enderror>
            @error('title') <p class="cq-err" id="err-title">{{ $message }}</p> @enderror
        </div>

        <div class="cq-field">
            <label class="cq-label" for="cqInstr">Instructions <span class="cq-opt">(optional)</span></label>
            <textarea id="cqInstr" name="instructions" class="cq-input" maxlength="20000" placeholder="What students should do and how they submit">{{ old('instructions') }}</textarea>
            @error('instructions') <p class="cq-err">{{ $message }}</p> @enderror
        </div>

        <div class="cq-grid2">
            <div class="cq-field">
                <label class="cq-label" for="cqDue">Deadline <span class="cq-opt">(optional)</span></label>
                <input type="datetime-local" id="cqDue" name="due_date" class="cq-input @error('due_date') is-error @enderror" value="{{ old('due_date') }}"
                       @error('due_date') aria-invalid="true" aria-describedby="err-due" @enderror>
                @error('due_date') <p class="cq-err" id="err-due">{{ $message }}</p> @else <p class="cq-hint">After it passes, students can no longer submit.</p> @enderror
            </div>
            <div class="cq-field">
                <label class="cq-label" for="cqMax">Highest score</label>
                <input type="number" id="cqMax" name="max_score" class="cq-input @error('max_score') is-error @enderror" value="{{ old('max_score', 100) }}"
                       min="1" max="1000" inputmode="numeric" @error('max_score') aria-invalid="true" aria-describedby="err-max" @enderror>
                @error('max_score') <p class="cq-err" id="err-max">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="cq-field">
            <span class="cq-label" id="cqFileLabel">Quiz file <span class="cq-opt">(optional)</span></span>
            <div class="cq-file" id="cqFile">
                <input type="file" id="cqFileInput" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.ppt,.pptx,.xls,.xlsx"
                       aria-labelledby="cqFileLabel" aria-describedby="cqFileHint">
                <label for="cqFileInput" class="tp-btn ghost sm">Choose file</label>
                <span class="cq-file-name" id="cqFileName">No file chosen</span>
                <button type="button" class="tp-btn ghost sm cq-file-clear" id="cqFileClear" aria-label="Remove the file">Remove</button>
            </div>
            <p class="cq-hint" id="cqFileHint">PDF, Word, PowerPoint, Excel or an image, up to 10 MB.</p>
            @error('file') <p class="cq-err">{{ $message }}</p> @enderror
        </div>

        <label class="cq-publish" for="cqPublish">
            <input type="checkbox" id="cqPublish" name="is_published" value="1" {{ (old('_token') ? old('is_published') : true) ? 'checked' : '' }}>
            <span><b>Publish now</b>Students in the class see it right away and get a notification. Leave it off to save a draft.</span>
        </label>

        <div class="cq-actions">
            <button type="submit" class="tp-btn" id="cqSubmit" {{ $pairs->isEmpty() ? 'disabled' : '' }}>Create quiz</button>
            <a href="{{ route('teacher.assignments.index') }}" class="tp-btn ghost">Cancel</a>
        </div>
    </form>
</div>

<script>
    (function () {
        var pair = document.getElementById('pairSelect');
        function syncPair() {
            var parts = pair.value.split('|');
            document.getElementById('subjectId').value = parts[0] || '';
            document.getElementById('sectionId').value = parts[1] || '';
        }
        pair.addEventListener('change', syncPair);
        syncPair();

        // No past deadlines (the server refuses them too)
        var due = document.getElementById('cqDue');
        var now = new Date(Date.now() - new Date().getTimezoneOffset() * 60000);
        due.min = now.toISOString().slice(0, 16);

        var box = document.getElementById('cqFile'), input = document.getElementById('cqFileInput'), name = document.getElementById('cqFileName');
        function showFile() {
            var f = input.files && input.files[0];
            box.classList.toggle('has-file', !!f);
            name.textContent = f ? f.name + ' (' + (f.size / 1048576).toFixed(1) + ' MB)' : 'No file chosen';
        }
        input.addEventListener('change', showFile);
        document.getElementById('cqFileClear').addEventListener('click', function () { input.value = ''; showFile(); input.focus(); });

        var form = document.getElementById('cqForm'), submit = document.getElementById('cqSubmit');
        form.addEventListener('submit', function () { submit.disabled = true; submit.textContent = 'Creating…'; });
        window.addEventListener('pageshow', function (e) { if (e.persisted) { submit.disabled = false; submit.textContent = 'Create quiz'; } });
    })();
</script>
@endsection
