@extends('layouts.teacher')
@section('title', 'Upload Material')

@section('content')
<style>
    .um-card { padding: 24px; }
    .um-field { margin-bottom: 18px; }
    .um-label { display: block; font-size: 13.5px; font-weight: 700; color: var(--text-1); margin-bottom: 7px; }
    .um-opt { font-weight: 500; color: var(--text-3); }
    .um-input {
        width: 100%; min-height: 44px; border: 1.5px solid var(--border); border-radius: var(--r-md); padding: 10px 13px;
        font-size: 14px; color: var(--text-1); font-family: inherit; background: #F8FAFC;
    }
    .um-input:focus { outline: none; border-color: var(--accent); background: #fff; box-shadow: 0 0 0 3px var(--accent-glow); }
    .um-input.is-error { border-color: #DC2626; }
    textarea.um-input { min-height: 96px; resize: vertical; line-height: 1.5; }
    .um-grid3 { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 16px; }
    .um-err { color: #B91C1C; font-size: 12.5px; margin-top: 5px; }
    .um-hint { font-size: 12.5px; color: var(--text-3); margin-top: 5px; }

    /* File picker: the real input stays in the form (keyboard + screen readers), styled through its label */
    .um-file { position: relative; display: flex; align-items: center; gap: 12px; padding: 12px; border: 1.5px dashed #CBD5E1; border-radius: var(--r-md); background: #F8FAFC; }
    .um-file input { position: absolute; width: 1px; height: 1px; opacity: 0; }
    .um-file input:focus-visible + label { outline: 3px solid var(--accent); outline-offset: 2px; }
    .um-file.dragover { border-color: var(--accent); background: var(--accent-lt); }
    .um-file-name { flex: 1; min-width: 0; font-size: 13.5px; color: var(--text-2); overflow-wrap: anywhere; }
    .um-file-clear { display: none; }
    .um-file.has-file { border-style: solid; border-color: var(--accent); background: var(--accent-lt); }
    .um-file.has-file .um-file-name { color: var(--text-1); font-weight: 600; }
    .um-file.has-file .um-file-clear { display: inline-flex; }

    .um-publish { display: flex; align-items: flex-start; gap: 12px; padding: 14px; border: 1.5px solid var(--border); border-radius: var(--r-md); cursor: pointer; }
    .um-publish input { width: 20px; height: 20px; margin-top: 1px; accent-color: var(--accent); flex-shrink: 0; }
    .um-publish b { display: block; font-size: 14px; color: var(--text-1); }
    .um-publish span { font-size: 13px; color: var(--text-2); }
    .um-actions { display: flex; gap: 10px; margin-top: 22px; flex-wrap: wrap; align-items: center; }
    .um-status { font-size: 13px; color: var(--text-2); }
    .um-alert { background: #FEF2F2; border: 1px solid #FECACA; color: #B91C1C; border-radius: var(--r-md); padding: 12px 14px; font-size: 13.5px; margin-bottom: 18px; }
    .um-alert ul { margin: 6px 0 0; padding-left: 18px; }

    @media (max-width: 600px) {
        .um-card { padding: 18px; }
        .um-grid3 { grid-template-columns: 1fr 1fr; gap: 0 12px; }
        .um-grid3 > :first-child { grid-column: 1 / -1; }
        .um-actions .tp-btn { flex: 1; }
    }
</style>

<div class="tp-page tp-narrow">
    <a href="{{ route('teacher.materials.index') }}" class="tp-back">
        <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Learning materials
    </a>
    <div class="tp-head">
        <div>
            <h1 class="tp-title">Upload material</h1>
            <p class="tp-sub">Share a handout, slides, a video or other files with the students taking one of your subjects.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('teacher.materials.store') }}" enctype="multipart/form-data" class="tp-card um-card" id="umForm">
        @csrf

        @if ($errors->any())
            <div class="um-alert" role="alert">
                <b>The material was not uploaded.</b>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($subjects->isEmpty())
            <div class="um-alert" role="alert">You have no subjects yet. The admin assigns subjects under Class Schedules.</div>
        @endif

        <div class="um-field">
            <label class="um-label" for="umTitle">Title</label>
            <input type="text" id="umTitle" name="title" value="{{ old('title') }}" maxlength="255" required autocomplete="off"
                   placeholder="Chapter 1: Introduction to algebra"
                   class="um-input @error('title') is-error @enderror" @error('title') aria-invalid="true" aria-describedby="err-title" @enderror>
            @error('title') <p class="um-err" id="err-title">{{ $message }}</p> @enderror
        </div>

        <div class="um-grid3">
            <div class="um-field">
                <label class="um-label" for="umSubject">Subject</label>
                <select id="umSubject" name="subject_id" required class="um-input @error('subject_id') is-error @enderror"
                        @error('subject_id') aria-invalid="true" aria-describedby="err-subject" @enderror>
                    <option value="">Choose a subject</option>
                    @foreach ($subjects->groupBy('grade_level') as $grade => $gradeSubjects)
                        <optgroup label="Grade {{ $grade }}">
                            @foreach ($gradeSubjects as $subject)
                                <option value="{{ $subject->id }}" @selected((string) old('subject_id') === (string) $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('subject_id') <p class="um-err" id="err-subject">{{ $message }}</p> @enderror
            </div>

            <div class="um-field">
                <label class="um-label" for="umQuarter">Quarter</label>
                <select id="umQuarter" name="quarter" required class="um-input @error('quarter') is-error @enderror"
                        @error('quarter') aria-invalid="true" aria-describedby="err-quarter" @enderror>
                    <option value="">Choose</option>
                    @foreach (['Q1' => 'First', 'Q2' => 'Second', 'Q3' => 'Third', 'Q4' => 'Fourth'] as $q => $label)
                        <option value="{{ $q }}" @selected(old('quarter') === $q)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('quarter') <p class="um-err" id="err-quarter">{{ $message }}</p> @enderror
            </div>

            <div class="um-field">
                <label class="um-label" for="umWeek">Week <span class="um-opt">(optional)</span></label>
                <input type="number" id="umWeek" name="week" min="1" max="20" inputmode="numeric" value="{{ old('week') }}"
                       class="um-input @error('week') is-error @enderror" @error('week') aria-invalid="true" aria-describedby="err-week" @enderror>
                @error('week') <p class="um-err" id="err-week">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="um-field">
            <label class="um-label" for="umDesc">Description <span class="um-opt">(optional)</span></label>
            <textarea id="umDesc" name="description" maxlength="1000" class="um-input @error('description') is-error @enderror"
                      placeholder="What this material covers and what students should do with it">{{ old('description') }}</textarea>
            @error('description') <p class="um-err">{{ $message }}</p> @enderror
        </div>

        <div class="um-field">
            <span class="um-label" id="umFileLabel">File <span class="um-opt">(optional)</span></span>
            <div class="um-file" id="umFile">
                <input type="file" id="umFileInput" name="file"
                       accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.png,.jpg,.jpeg,.mp4,.zip"
                       aria-labelledby="umFileLabel" aria-describedby="umFileHint">
                <label for="umFileInput" class="tp-btn ghost sm">Choose file</label>
                <span class="um-file-name" id="umFileName">No file chosen, or drop one here</span>
                <button type="button" class="tp-btn ghost sm um-file-clear" id="umFileClear" aria-label="Remove the file">Remove</button>
            </div>
            <p class="um-hint" id="umFileHint">PDF, Word, PowerPoint, Excel, an image, an MP4 video or a ZIP, up to 20 MB.</p>
            <p class="um-err" id="umFileErr" role="alert" hidden></p>
            @error('file') <p class="um-err">{{ $message }}</p> @enderror
        </div>

        <label class="um-publish" for="umPublish">
            <input type="checkbox" id="umPublish" name="is_published" value="1" {{ (old('_token') ? old('is_published') : true) ? 'checked' : '' }}>
            <span><b>Publish now</b>Students see it right away and get a notification. Leave it off to save a draft you can publish later.</span>
        </label>

        <div class="um-actions">
            <button type="submit" class="tp-btn" id="umSubmit" {{ $subjects->isEmpty() ? 'disabled' : '' }}>Upload material</button>
            <a href="{{ route('teacher.materials.index') }}" class="tp-btn ghost">Cancel</a>
            <span class="um-status" id="umStatus" role="status"></span>
        </div>
    </form>
</div>

<script>
    (function () {
        var MAX = 20 * 1024 * 1024;
        var box = document.getElementById('umFile'), input = document.getElementById('umFileInput');
        var name = document.getElementById('umFileName'), err = document.getElementById('umFileErr');

        function showFile() {
            var f = input.files && input.files[0];
            var tooBig = !!f && f.size > MAX;
            box.classList.toggle('has-file', !!f);
            name.textContent = f ? f.name + ' (' + (f.size / 1048576).toFixed(1) + ' MB)' : 'No file chosen, or drop one here';
            err.hidden = !tooBig;
            err.textContent = tooBig ? 'This file is larger than 20 MB. Choose a smaller file.' : '';
            return !tooBig;
        }
        input.addEventListener('change', showFile);
        document.getElementById('umFileClear').addEventListener('click', function () { input.value = ''; showFile(); input.focus(); });

        // Dropping a file onto the box fills the real input, so the normal form post sends it
        ['dragenter', 'dragover'].forEach(function (ev) {
            box.addEventListener(ev, function (e) { e.preventDefault(); box.classList.add('dragover'); });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            box.addEventListener(ev, function () { box.classList.remove('dragover'); });
        });
        box.addEventListener('drop', function (e) {
            e.preventDefault();
            if (e.dataTransfer && e.dataTransfer.files.length) { input.files = e.dataTransfer.files; showFile(); }
        });

        var form = document.getElementById('umForm'), submit = document.getElementById('umSubmit'), status = document.getElementById('umStatus');
        form.addEventListener('submit', function (e) {
            if (!showFile()) { e.preventDefault(); input.focus(); return; }
            submit.disabled = true;
            submit.textContent = 'Uploading…';
            if (input.files && input.files.length) status.textContent = 'Large files can take a minute. Keep this page open.';
        });
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) { submit.disabled = false; submit.textContent = 'Upload material'; status.textContent = ''; }
        });
    })();
</script>
@endsection
