@extends('layouts.teacher')
@section('title', 'Upload Material')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap');

    :root {
        --ease-out:   cubic-bezier(0.23, 1, 0.32, 1);
        --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
        --blue:       #2563EB;
        --blue-dk:    #1D4ED8;
        --blue-lt:    #EFF6FF;
        --blue-glow:  rgba(37,99,235,.15);
        --green:      #16A34A;
        --green-lt:   #F0FDF4;
        --green-bd:   #86EFAC;
        --red:        #EF4444;
        --red-lt:     #FEF2F2;
        --red-bd:     #FCA5A5;
        --text-1:     #0F172A;
        --text-2:     #475569;
        --text-3:     #94A3B8;
        --border:     #E2E8F0;
        --surface:    #FFFFFF;
        --bg:         #F8FAFC;
        --r-sm: 8px;
        --r-md: 12px;
        --r-lg: 18px;
        --shadow-sm: 0 1px 3px rgba(15,23,42,.06), 0 1px 2px rgba(15,23,42,.04);
        --shadow-md: 0 4px 16px rgba(15,23,42,.08), 0 2px 4px rgba(15,23,42,.04);
        --shadow-lg: 0 16px 40px rgba(15,23,42,.12), 0 4px 8px rgba(15,23,42,.04);
    }

    .upload-page * { font-family: 'DM Sans', system-ui, sans-serif; }

    /* ── Page layout ── */
    .upload-page {
        max-width: 780px;
        margin: 0 auto;
        padding: 8px 0 48px;
    }

    /* ── Back link ── */
    .back-link {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 13px; font-weight: 500; color: var(--text-3);
        text-decoration: none; margin-bottom: 28px;
        transition: color 180ms var(--ease-out), gap 180ms var(--ease-out);
    }
    .back-link:hover { color: var(--blue); gap: 10px; }
    .back-link svg { transition: transform 180ms var(--ease-out); }
    .back-link:hover svg { transform: translateX(-3px); }

    /* ── Header ── */
    .page-header {
        display: flex; align-items: flex-start; justify-content: space-between;
        margin-bottom: 28px;
        animation: slideUp .4s var(--ease-out) both;
    }
    .page-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--blue-lt); color: var(--blue);
        border: 1px solid rgba(37,99,235,.2);
        border-radius: 20px; padding: 4px 12px;
        font-size: 11px; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; margin-bottom: 8px;
    }
    .page-title {
        font-size: 24px; font-weight: 700; color: var(--text-1);
        letter-spacing: -.02em; line-height: 1.2;
    }
    .page-sub {
        font-size: 13px; color: var(--text-3); margin-top: 4px; line-height: 1.6;
    }

    /* ── Card ── */
    .upload-card {
        background: var(--surface);
        border: 1.5px solid var(--border);
        border-radius: var(--r-lg);
        box-shadow: var(--shadow-md);
        overflow: hidden;
        animation: slideUp .4s var(--ease-out) .05s both;
    }

    .card-section {
        padding: 24px 28px;
        border-bottom: 1px solid #F1F5F9;
    }
    .card-section:last-child { border-bottom: none; }

    .section-label {
        font-size: 10px; font-weight: 700; letter-spacing: .12em;
        text-transform: uppercase; color: var(--text-3);
        margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
    }
    .section-label::after {
        content: ''; flex: 1; height: 1px; background: #F1F5F9;
    }

    /* ── Fields ── */
    .field { margin-bottom: 0; }
    .field-row { display: grid; gap: 16px; }
    .field-row.cols-3 { grid-template-columns: 2fr 1fr 1fr; }
    .field-row.cols-2 { grid-template-columns: 1fr 1fr; }

    .field-label {
        display: block; font-size: 12px; font-weight: 600;
        color: var(--text-2); margin-bottom: 6px;
        letter-spacing: .01em;
    }
    .field-label span { color: var(--red); margin-left: 2px; }

    .field-input {
        width: 100%; border: 1.5px solid var(--border);
        border-radius: var(--r-md); padding: 10px 14px;
        font-size: 14px; font-weight: 400; color: var(--text-1);
        background: var(--bg); outline: none;
        font-family: 'DM Sans', system-ui, sans-serif;
        transition: border-color 160ms var(--ease-out),
                    box-shadow 160ms var(--ease-out),
                    background 160ms var(--ease-out);
        -webkit-appearance: none; appearance: none;
    }
    .field-input:focus {
        border-color: var(--blue);
        box-shadow: 0 0 0 3px var(--blue-glow);
        background: var(--surface);
    }
    .field-input.is-error {
        border-color: #FCA5A5 !important;
        background: var(--red-lt) !important;
    }
    .field-input::placeholder { color: var(--text-3); }

    textarea.field-input { resize: none; min-height: 80px; line-height: 1.6; }

    select.field-input {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 14px;
        padding-right: 36px;
        cursor: pointer;
    }

    .field-error {
        font-size: 11.5px; color: var(--red); margin-top: 5px;
        font-weight: 500; display: flex; align-items: center; gap: 4px;
    }

    /* ── Drop zone ── */
    .drop-zone {
        border: 2px dashed var(--border);
        border-radius: var(--r-lg);
        padding: 36px 24px;
        text-align: center;
        cursor: pointer;
        position: relative;
        background: var(--bg);
        transition: border-color 200ms var(--ease-out),
                    background 200ms var(--ease-out),
                    transform 200ms var(--ease-spring);
    }
    .drop-zone:hover {
        border-color: var(--blue);
        background: var(--blue-lt);
        transform: translateY(-2px);
    }
    .drop-zone.dragover {
        border-color: var(--blue);
        background: var(--blue-lt);
        transform: scale(1.01);
    }
    .drop-zone input[type="file"] {
        position: absolute; inset: 0; opacity: 0;
        cursor: pointer; width: 100%; height: 100%;
    }

    .drop-icon {
        width: 52px; height: 52px;
        background: var(--surface); border: 1.5px solid var(--border);
        border-radius: var(--r-md);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 14px;
        box-shadow: var(--shadow-sm);
        transition: transform 280ms var(--ease-spring),
                    box-shadow 280ms var(--ease-out),
                    border-color 200ms;
    }
    .drop-zone:hover .drop-icon {
        transform: translateY(-4px) scale(1.06);
        box-shadow: 0 8px 24px var(--blue-glow);
        border-color: rgba(37,99,235,.3);
    }

    .drop-title {
        font-size: 14px; font-weight: 600; color: var(--text-1); margin-bottom: 4px;
    }
    .drop-sub {
        font-size: 12px; color: var(--text-3); line-height: 1.5;
    }
    .drop-sub strong { color: var(--blue); font-weight: 600; }

    /* ── File types ── */
    .file-types {
        display: flex; flex-wrap: wrap; gap: 6px;
        justify-content: center; margin-top: 14px;
    }
    .file-type-tag {
        font-size: 10px; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; padding: 3px 8px; border-radius: 4px;
        background: var(--surface); border: 1px solid var(--border);
        color: var(--text-3); font-family: 'DM Mono', monospace;
    }

    /* ── File preview ── */
    .file-preview {
        display: none; align-items: center; gap: 12px;
        background: var(--green-lt); border: 1.5px solid var(--green-bd);
        border-radius: var(--r-md); padding: 12px 16px; margin-top: 10px;
        animation: slideUp .25s var(--ease-out) both;
    }
    .file-preview-icon {
        width: 38px; height: 38px; border-radius: var(--r-sm);
        background: var(--surface); border: 1px solid var(--green-bd);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .file-preview-name {
        font-size: 13px; font-weight: 600; color: var(--green);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        max-width: 320px;
    }
    .file-preview-size { font-size: 11px; color: var(--text-3); margin-top: 1px; }
    .file-clear-btn {
        margin-left: auto; background: none; border: none; cursor: pointer;
        color: var(--text-3); font-size: 20px; line-height: 1; padding: 2px 6px;
        border-radius: 4px; flex-shrink: 0;
        transition: color 150ms, background 150ms;
    }
    .file-clear-btn:hover { color: var(--red); background: var(--red-lt); }

    /* ── Toggle ── */
    .toggle-row {
        display: flex; align-items: center; justify-content: space-between;
        gap: 16px; padding: 14px 16px;
        background: var(--bg); border: 1.5px solid var(--border);
        border-radius: var(--r-md);
        transition: border-color 200ms, background 200ms;
    }
    .toggle-row:has(input:checked) {
        background: var(--green-lt); border-color: var(--green-bd);
    }
    .toggle-info .toggle-title {
        font-size: 13px; font-weight: 600; color: var(--text-1);
    }
    .toggle-info .toggle-desc {
        font-size: 11px; color: var(--text-3); margin-top: 2px;
    }

    .toggle-switch {
        position: relative; width: 44px; height: 24px; flex-shrink: 0;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; position: absolute; }
    .toggle-track {
        position: absolute; inset: 0; border-radius: 24px;
        background: #CBD5E1; cursor: pointer;
        transition: background 220ms var(--ease-out);
    }
    .toggle-switch input:checked + .toggle-track { background: var(--green); }
    .toggle-thumb {
        position: absolute; top: 3px; left: 3px;
        width: 18px; height: 18px; border-radius: 50%;
        background: white; pointer-events: none;
        box-shadow: 0 1px 4px rgba(0,0,0,.2);
        transition: transform 220ms var(--ease-spring);
    }
    .toggle-switch input:checked ~ .toggle-thumb { transform: translateX(20px); }

    /* ── Error alert ── */
    .error-alert {
        background: var(--red-lt); border: 1.5px solid var(--red-bd);
        border-radius: var(--r-md); padding: 14px 16px; margin-bottom: 4px;
    }
    .error-alert-title {
        font-size: 13px; font-weight: 700; color: #991B1B;
        display: flex; align-items: center; gap: 6px; margin-bottom: 6px;
    }
    .error-alert ul { list-style: none; padding: 0; margin: 0; }
    .error-alert ul li {
        font-size: 12px; color: #B91C1C; padding: 2px 0;
        display: flex; align-items: flex-start; gap: 6px;
    }
    .error-alert ul li::before { content: '·'; font-weight: 900; flex-shrink: 0; }

    /* ── Footer buttons ── */
    .card-footer {
        padding: 20px 28px;
        background: #FAFBFC; border-top: 1px solid #F1F5F9;
        display: flex; align-items: center; gap: 10px;
    }

    .btn-primary {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--blue); color: white; border: none;
        border-radius: var(--r-md); padding: 11px 22px;
        font-size: 13px; font-weight: 700; cursor: pointer;
        font-family: 'DM Sans', system-ui, sans-serif;
        box-shadow: 0 4px 14px rgba(37,99,235,.3);
        transition: background 180ms var(--ease-out),
                    transform 180ms var(--ease-spring),
                    box-shadow 180ms var(--ease-out);
        position: relative; overflow: hidden;
    }
    .btn-primary::before {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,.12), transparent);
        pointer-events: none;
    }
    @media (hover: hover) and (pointer: fine) {
        .btn-primary:hover {
            background: var(--blue-dk);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(37,99,235,.4);
        }
    }
    .btn-primary:active { transform: scale(.97); }
    .btn-primary:disabled { opacity: .65; cursor: not-allowed; transform: none !important; }

    .btn-spinner {
        width: 16px; height: 16px;
        border: 2px solid rgba(255,255,255,.3);
        border-top-color: white; border-radius: 50%;
        animation: spin .6s linear infinite; display: none;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .btn-cancel {
        display: inline-flex; align-items: center;
        background: var(--surface); color: var(--text-2);
        border: 1.5px solid var(--border);
        border-radius: var(--r-md); padding: 11px 18px;
        font-size: 13px; font-weight: 600; cursor: pointer;
        text-decoration: none;
        font-family: 'DM Sans', system-ui, sans-serif;
        transition: background 160ms, border-color 160ms, color 160ms;
    }
    .btn-cancel:hover { background: var(--bg); border-color: #CBD5E1; color: var(--text-1); }

    /* ── Progress bar (upload feedback) ── */
    .upload-progress {
        display: none; margin-top: 10px;
    }
    .progress-bar-track {
        height: 4px; background: var(--border); border-radius: 4px; overflow: hidden;
    }
    .progress-bar-fill {
        height: 100%; background: var(--blue); border-radius: 4px;
        width: 0%; transition: width .3s var(--ease-out);
    }
    .progress-label {
        font-size: 11px; color: var(--text-3); margin-top: 5px; font-weight: 500;
    }

    /* ── Animations ── */
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    @media (prefers-reduced-motion: reduce) {
        .upload-card, .page-header, .file-preview { animation: none; }
        .drop-zone:hover { transform: none; }
    }

    @media (max-width: 600px) {
        .field-row.cols-3 { grid-template-columns: 1fr; }
        .field-row.cols-2 { grid-template-columns: 1fr; }
        .card-section { padding: 20px; }
        .card-footer { padding: 16px 20px; flex-wrap: wrap; }
    }
</style>

<div class="upload-page">

    {{-- Back --}}
    <a href="{{ route('teacher.materials.index') }}" class="back-link">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Materials
    </a>

    {{-- Header --}}
    <div class="page-header">
        <div>
            <div class="page-badge">
                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                New Upload
            </div>
            <h1 class="page-title">Upload Learning Material</h1>
            <p class="page-sub">Share PDFs, documents, videos, and other content with your students.</p>
        </div>
    </div>

    {{-- Error alert --}}
    @if($errors->any())
    <div class="error-alert" style="margin-bottom: 20px; animation: slideUp .3s var(--ease-out) both;">
        <div class="error-alert-title">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
            Please fix the following errors
        </div>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Main form card --}}
    <form method="POST" action="{{ route('teacher.materials.store') }}"
          enctype="multipart/form-data" id="uploadForm">
        @csrf

        <div class="upload-card">

            {{-- Section 1: Basic info --}}
            <div class="card-section">
                <div class="section-label">Material Details</div>

                {{-- Title --}}
                <div class="field" style="margin-bottom: 16px;">
                    <label class="field-label">Title <span>*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           placeholder="e.g. Chapter 1 — Introduction to Algebra"
                           required autocomplete="off"
                           class="field-input {{ $errors->has('title') ? 'is-error' : '' }}">
                    @error('title')
                        <p class="field-error">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Subject + Quarter + Week --}}
                <div class="field-row cols-3">
                    <div class="field">
                        <label class="field-label">Subject <span>*</span></label>
                        <select name="subject_id" required
                                class="field-input {{ $errors->has('subject_id') ? 'is-error' : '' }}">
                            <option value="">— Select subject —</option>
                            @foreach($subjects->groupBy('grade_level') as $grade => $gradeSubjects)
                                <optgroup label="Grade {{ $grade }}">
                                    @foreach($gradeSubjects as $subject)
                                        <option value="{{ $subject->id }}"
                                            {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <p class="field-error"><svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008z"/></svg> {{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="field-label">Quarter <span>*</span></label>
                        <select name="quarter" required
                                class="field-input {{ $errors->has('quarter') ? 'is-error' : '' }}">
                            <option value="">—</option>
                            <option value="Q1" {{ old('quarter') == 'Q1' ? 'selected' : '' }}>Q1 — First</option>
                            <option value="Q2" {{ old('quarter') == 'Q2' ? 'selected' : '' }}>Q2 — Second</option>
                            <option value="Q3" {{ old('quarter') == 'Q3' ? 'selected' : '' }}>Q3 — Third</option>
                            <option value="Q4" {{ old('quarter') == 'Q4' ? 'selected' : '' }}>Q4 — Fourth</option>
                        </select>
                        @error('quarter')
                            <p class="field-error"><svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008z"/></svg> {{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="field-label">Week</label>
                        <input type="number" name="week" min="1" max="20"
                               value="{{ old('week') }}" placeholder="1–20"
                               class="field-input">
                    </div>
                </div>
            </div>

            {{-- Section 2: Description --}}
            <div class="card-section">
                <div class="section-label">Description</div>
                <textarea name="description" rows="3"
                          placeholder="Brief description of what this material covers…"
                          class="field-input" style="margin-bottom:0;">{{ old('description') }}</textarea>
            </div>

            {{-- Section 3: File upload --}}
            <div class="card-section">
                <div class="section-label">File</div>

                <div class="drop-zone" id="dropZone">
                    <input type="file" name="file" id="fileInput"
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.png,.jpg,.jpeg,.mp4,.zip"
                           onchange="handleFile(this)">

                    <div class="drop-icon" id="dropIcon">
                        <svg width="22" height="22" fill="none" stroke="#3B82F6"
                             stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>
                    <p class="drop-title">Drop your file here, or <strong style="color:var(--blue)">browse</strong></p>
                    <p class="drop-sub">Max <strong>20 MB</strong> per file</p>

                    <div class="file-types">
                        @foreach(['PDF','DOC','PPT','XLS','PNG','JPG','MP4','ZIP'] as $ext)
                            <span class="file-type-tag">{{ $ext }}</span>
                        @endforeach
                    </div>
                </div>

                {{-- Preview --}}
                <div class="file-preview" id="filePreview">
                    <div class="file-preview-icon" id="filePreviewIcon">
                        <svg width="18" height="18" fill="none" stroke="#16A34A"
                             stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <p class="file-preview-name" id="fileName">—</p>
                        <p class="file-preview-size" id="fileSize">—</p>
                    </div>
                    <button type="button" class="file-clear-btn" onclick="clearFile()" title="Remove file">×</button>
                </div>

                {{-- Upload progress (shown during submit) --}}
                <div class="upload-progress" id="uploadProgress">
                    <div class="progress-bar-track">
                        <div class="progress-bar-fill" id="progressFill"></div>
                    </div>
                    <p class="progress-label" id="progressLabel">Preparing upload…</p>
                </div>

                @error('file')
                    <p class="field-error" style="margin-top:8px;">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008z"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Section 4: Visibility --}}
            <div class="card-section">
                <div class="section-label">Visibility</div>
                <div class="toggle-row">
                    <div class="toggle-info">
                        <p class="toggle-title">Publish immediately</p>
                        <p class="toggle-desc">Students can view this material right away after upload.</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_published" value="1"
                               id="publishToggle"
                               {{ old('is_published') ? 'checked' : '' }}>
                        <div class="toggle-track"></div>
                        <div class="toggle-thumb"></div>
                    </label>
                </div>
            </div>

            {{-- Footer --}}
            <div class="card-footer">
                <button type="submit" class="btn-primary" id="submitBtn">
                    <svg id="btnIcon" width="16" height="16" fill="none" stroke="currentColor"
                         stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <div class="btn-spinner" id="btnSpinner"></div>
                    <span id="btnText">Upload Material</span>
                </button>
                <a href="{{ route('teacher.materials.index') }}" class="btn-cancel">Cancel</a>
            </div>

        </div>
    </form>
</div>

<script>
// ── File handling
const EXT_ICONS = {
    pdf:  { color: '#EF4444', bg: '#FEF2F2' },
    doc:  { color: '#2563EB', bg: '#EFF6FF' },
    docx: { color: '#2563EB', bg: '#EFF6FF' },
    ppt:  { color: '#F97316', bg: '#FFF7ED' },
    pptx: { color: '#F97316', bg: '#FFF7ED' },
    xls:  { color: '#16A34A', bg: '#F0FDF4' },
    xlsx: { color: '#16A34A', bg: '#F0FDF4' },
    mp4:  { color: '#8B5CF6', bg: '#F5F3FF' },
    zip:  { color: '#6B7280', bg: '#F9FAFB' },
};

function handleFile(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const ext  = file.name.split('.').pop().toLowerCase();
    const ic   = EXT_ICONS[ext] || { color: '#3B82F6', bg: '#EFF6FF' };
    const size = file.size > 1048576
        ? (file.size / 1048576).toFixed(2) + ' MB'
        : (file.size / 1024).toFixed(1) + ' KB';

    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = size + ' · .' + ext.toUpperCase();

    const icon = document.getElementById('filePreviewIcon');
    icon.style.background = ic.bg;
    icon.style.borderColor = ic.color + '44';
    icon.innerHTML = `<span style="font-size:11px;font-weight:800;color:${ic.color};font-family:'DM Mono',monospace;">.${ext.toUpperCase()}</span>`;

    document.getElementById('filePreview').style.display = 'flex';
}

function clearFile() {
    document.getElementById('fileInput').value = '';
    document.getElementById('filePreview').style.display = 'none';
}

// ── Drag and drop
const dropZone = document.getElementById('dropZone');
['dragover', 'dragenter'].forEach(ev =>
    dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.add('dragover'); })
);
['dragleave', 'drop'].forEach(ev =>
    dropZone.addEventListener(ev, () => dropZone.classList.remove('dragover'))
);

// ── Submit — loading state + simulated progress
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    const btn     = document.getElementById('submitBtn');
    const icon    = document.getElementById('btnIcon');
    const spinner = document.getElementById('btnSpinner');
    const txt     = document.getElementById('btnText');
    const prog    = document.getElementById('uploadProgress');
    const fill    = document.getElementById('progressFill');
    const lbl     = document.getElementById('progressLabel');

    btn.disabled        = true;
    icon.style.display  = 'none';
    spinner.style.display = 'block';
    txt.textContent     = 'Uploading…';
    prog.style.display  = 'block';

    // Simulate progress (real progress needs XHR — this gives feedback)
    let pct = 0;
    const iv = setInterval(() => {
        pct = Math.min(pct + Math.random() * 18, 90);
        fill.style.width = pct + '%';
        lbl.textContent = pct < 40 ? 'Uploading file…'
                        : pct < 75 ? 'Processing…'
                        : 'Almost done…';
    }, 300);

    // Store interval id so it stops on page unload
    window._uploadInterval = iv;
});
</script>

@endsection