@extends('layouts.teacher') 
@section('title', 'Learning Materials')

@section('sidebar')
    <a href="{{ route('teacher.dashboard') }}"
        class="{{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>
    <a href="{{ route('teacher.gradebook.index') }}"
        class="{{ request()->routeIs('teacher.gradebook.*') ? 'active' : '' }}">
        Gradebook
    </a>
    <a href="{{ route('teacher.materials.index') }}"
        class="{{ request()->routeIs('teacher.materials.*') ? 'active' : '' }}">
        Learning Materials
    </a>
    <a href="{{ route('teacher.announcements.index') }}"
        class="{{ request()->routeIs('teacher.announcements.*') ? 'active' : '' }}">
        Announcements
    </a>
@endsection

@section('content')
<style>
    /* ── Page card ── */
    .page-card {
        background: #fff;
        border-radius: 16px;
        border: 1.5px solid #f1f5f9;
        box-shadow: 0 2px 16px rgba(0,0,0,0.04);
        animation: fadeUp 0.4s cubic-bezier(0.16,1,0.3,1) both;
    }
    @keyframes fadeUp {
        from { opacity:0; transform:translateY(12px); }
        to   { opacity:1; transform:translateY(0); }
    }

    .material-item {
        padding: 16px 20px;
        border-bottom: 1px solid #f3f4f6;
        display: flex; align-items: center; gap: 14px;
        transition: background 0.15s ease;
    }
    .material-item:last-child { border-bottom: none; }
    .material-item:hover { background: #f8faff; }

    .material-icon {
        width: 40px; height: 40px; border-radius: 10px; background: #eff6ff;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        transition: transform 0.2s cubic-bezier(0.34,1.56,0.64,1);
    }
    .material-item:hover .material-icon { transform: scale(1.1) rotate(-4deg); }

    .upload-btn {
        display: inline-flex; align-items: center; gap: 6px;
        background: #2563eb; color: white; border: none;
        border-radius: 10px; padding: 9px 18px;
        font-size: 13px; font-weight: 600; cursor: pointer;
        transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
        box-shadow: 0 3px 10px rgba(37,99,235,0.25);
        font-family: inherit;
    }
    @media (hover: hover) and (pointer: fine) {
        .upload-btn:hover {
            background: #1d4ed8; transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37,99,235,0.35);
        }
    }
    .upload-btn:active { transform: scale(0.97); }

    /* ══════════════════════════════════════════
       UPLOAD MODAL — scoped so layout styles
       cannot bleed in
    ══════════════════════════════════════════ */

    /* Override layout's .modal-overlay for OUR overlay only */
    #uploadModal.upload-modal-overlay {
        position: fixed !important;
        inset: 0 !important;
        z-index: 9999 !important;
        background: rgba(15,23,42,0) !important;
        backdrop-filter: none !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 20px !important;
        pointer-events: none !important;
        opacity: 1 !important;               /* override layout opacity:0 */
        transition: background 220ms cubic-bezier(0.23,1,0.32,1) !important;
    }
    #uploadModal.upload-modal-overlay.is-open {
        background: rgba(15,23,42,0.58) !important;
        pointer-events: all !important;
    }
    #uploadModal.upload-modal-overlay.is-closing {
        background: rgba(15,23,42,0) !important;
        transition: background 160ms cubic-bezier(0.23,1,0.32,1) !important;
    }

    /* Panel */
    #uploadModal .um-panel {
        --ease-out:    cubic-bezier(0.23, 1, 0.32, 1);
        --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
        --blue:      #2563EB;
        --blue-dk:   #1D4ED8;
        --blue-lt:   #EFF6FF;
        --blue-glow: rgba(37,99,235,.15);
        --green:     #16A34A;
        --green-lt:  #F0FDF4;
        --green-bd:  #86EFAC;
        --red:       #EF4444;
        --red-lt:    #FEF2F2;
        --red-bd:    #FCA5A5;
        --um-text-1: #0F172A;
        --um-text-2: #475569;
        --um-text-3: #94A3B8;
        --um-border: #E2E8F0;
        --um-surface:#FFFFFF;
        --um-bg:     #F8FAFC;

        background: var(--um-surface);
        border-radius: 20px;
        box-shadow: 0 24px 64px rgba(15,23,42,.18), 0 8px 24px rgba(15,23,42,.08);
        width: 100%; max-width: 740px;
        max-height: 92dvh;
        overflow: hidden;
        display: flex; flex-direction: column;
        opacity: 0;
        transform: scale(0.96) translateY(8px);
        transition: opacity 220ms var(--ease-out), transform 280ms var(--ease-out);
        will-change: transform, opacity;
        border: 1.5px solid rgba(255,255,255,.08);
    }
    #uploadModal.is-open .um-panel {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
    #uploadModal.is-closing .um-panel {
        opacity: 0;
        transform: scale(0.97) translateY(4px);
        transition: opacity 160ms cubic-bezier(0.23,1,0.32,1),
                    transform 160ms cubic-bezier(0.23,1,0.32,1);
    }

    /* ── Inside panel styles (all scoped to #uploadModal) ── */
    #uploadModal .um-header {
        padding: 22px 28px 0;
        display: flex; align-items: flex-start; justify-content: space-between;
        flex-shrink: 0;
    }
    #uploadModal .um-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--blue-lt); color: var(--blue);
        border: 1px solid rgba(37,99,235,.2);
        border-radius: 20px; padding: 4px 12px;
        font-size: 11px; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; margin-bottom: 6px;
    }
    #uploadModal .um-title {
        font-size: 20px; font-weight: 700; color: var(--um-text-1);
        letter-spacing: -.02em; line-height: 1.25;
    }
    #uploadModal .um-sub { font-size: 12.5px; color: var(--um-text-3); margin-top: 3px; }

    #uploadModal .um-close {
        width: 34px; height: 34px; border-radius: 10px;
        border: 1.5px solid var(--um-border); background: var(--um-bg);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; flex-shrink: 0; color: var(--um-text-3);
        transition: background 150ms, border-color 150ms, color 150ms,
                    transform 150ms cubic-bezier(0.34,1.56,0.64,1);
        margin-top: 2px;
    }
    @media (hover: hover) and (pointer: fine) {
        #uploadModal .um-close:hover {
            background: var(--red-lt); border-color: var(--red-bd); color: var(--red);
            transform: scale(1.08) rotate(8deg);
        }
    }
    #uploadModal .um-close:active { transform: scale(0.94); }

    #uploadModal .um-body {
        overflow-y: auto; overflow-x: hidden;
        padding: 20px 28px; flex: 1;
        scrollbar-width: thin; scrollbar-color: #CBD5E1 transparent;
    }
    #uploadModal .um-body::-webkit-scrollbar { width: 5px; }
    #uploadModal .um-body::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 99px; }

    #uploadModal .um-section {
        padding: 20px 0; border-bottom: 1px solid #F1F5F9;
    }
    #uploadModal .um-section:last-child { border-bottom: none; padding-bottom: 0; }

    #uploadModal .um-section-label {
        font-size: 10px; font-weight: 700; letter-spacing: .12em;
        text-transform: uppercase; color: var(--um-text-3);
        margin-bottom: 14px; display: flex; align-items: center; gap: 8px;
    }
    #uploadModal .um-section-label::after { content:''; flex:1; height:1px; background:#F1F5F9; }

    #uploadModal .um-row { display: grid; gap: 14px; }
    #uploadModal .um-row.c3 { grid-template-columns: 2fr 1fr 1fr; }

    #uploadModal .um-label {
        display: block; font-size: 12px; font-weight: 600;
        color: var(--um-text-2); margin-bottom: 5px;
    }
    #uploadModal .um-label span { color: var(--red); margin-left: 2px; }

    #uploadModal .um-input {
        width: 100%; border: 1.5px solid var(--um-border);
        border-radius: 12px; padding: 10px 14px;
        font-size: 14px; color: var(--um-text-1);
        background: var(--um-bg); outline: none; font-family: inherit;
        transition: border-color 160ms, box-shadow 160ms, background 160ms;
        -webkit-appearance: none; appearance: none; box-sizing: border-box;
    }
    #uploadModal .um-input:focus {
        border-color: var(--blue);
        box-shadow: 0 0 0 3px var(--blue-glow);
        background: var(--um-surface);
    }
    #uploadModal .um-input.err { border-color: #FCA5A5 !important; background: var(--red-lt) !important; }
    #uploadModal .um-input::placeholder { color: var(--um-text-3); }
    #uploadModal textarea.um-input { resize: none; min-height: 76px; line-height: 1.6; }
    #uploadModal select.um-input {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 12px center;
        background-size: 14px; padding-right: 36px; cursor: pointer;
    }
    #uploadModal .um-field-err {
        font-size: 11.5px; color: var(--red); margin-top: 5px;
        font-weight: 500; display: flex; align-items: center; gap: 4px;
    }

    /* Drop zone */
    #uploadModal .um-drop {
        border: 2px dashed var(--um-border); border-radius: 18px;
        padding: 32px 24px; text-align: center; cursor: pointer;
        position: relative; background: var(--um-bg);
        transition: border-color 200ms, background 200ms, transform 200ms cubic-bezier(0.34,1.56,0.64,1);
    }
    @media (hover: hover) and (pointer: fine) {
        #uploadModal .um-drop:hover {
            border-color: var(--blue); background: var(--blue-lt); transform: translateY(-2px);
        }
    }
    #uploadModal .um-drop.dragover {
        border-color: var(--blue); background: var(--blue-lt); transform: scale(1.01);
    }
    #uploadModal .um-drop input[type="file"] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }
    #uploadModal .um-drop-icon {
        width: 48px; height: 48px; background: var(--um-surface);
        border: 1.5px solid var(--um-border); border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 12px;
        box-shadow: 0 1px 3px rgba(15,23,42,.06);
        transition: transform 280ms cubic-bezier(0.34,1.56,0.64,1), box-shadow 280ms, border-color 200ms;
    }
    @media (hover: hover) and (pointer: fine) {
        #uploadModal .um-drop:hover .um-drop-icon {
            transform: translateY(-4px) scale(1.06);
            box-shadow: 0 8px 24px var(--blue-glow);
            border-color: rgba(37,99,235,.3);
        }
    }
    #uploadModal .um-drop-title { font-size: 14px; font-weight: 600; color: var(--um-text-1); margin-bottom: 4px; }
    #uploadModal .um-drop-sub   { font-size: 12px; color: var(--um-text-3); }
    #uploadModal .um-drop-sub strong { color: var(--blue); font-weight: 600; }
    #uploadModal .um-tags {
        display: flex; flex-wrap: wrap; gap: 5px; justify-content: center; margin-top: 12px;
    }
    #uploadModal .um-tag {
        font-size: 10px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
        padding: 3px 7px; border-radius: 4px;
        background: var(--um-surface); border: 1px solid var(--um-border); color: var(--um-text-3);
        font-family: 'Courier New', monospace;
    }

    /* File preview */
    #uploadModal .um-file-preview {
        display: none; align-items: center; gap: 12px;
        background: var(--green-lt); border: 1.5px solid var(--green-bd);
        border-radius: 12px; padding: 12px 16px; margin-top: 10px;
    }
    #uploadModal .um-file-icon {
        width: 36px; height: 36px; border-radius: 8px;
        background: var(--um-surface); border: 1px solid var(--green-bd);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    #uploadModal .um-file-name {
        font-size: 13px; font-weight: 600; color: var(--green);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px;
    }
    #uploadModal .um-file-size { font-size: 11px; color: var(--um-text-3); margin-top: 1px; }
    #uploadModal .um-file-clear {
        margin-left: auto; background: none; border: none; cursor: pointer;
        color: var(--um-text-3); font-size: 20px; line-height: 1; padding: 2px 6px;
        border-radius: 4px; transition: color 150ms, background 150ms;
    }
    #uploadModal .um-file-clear:hover { color: var(--red); background: var(--red-lt); }

    /* Progress */
    #uploadModal .um-progress { display: none; margin-top: 10px; }
    #uploadModal .um-progress-track { height: 4px; background: var(--um-border); border-radius: 4px; overflow: hidden; }
    #uploadModal .um-progress-fill {
        height: 100%; background: var(--blue); border-radius: 4px;
        width: 0%; transition: width .3s cubic-bezier(0.23,1,0.32,1);
    }
    #uploadModal .um-progress-label { font-size: 11px; color: var(--um-text-3); margin-top: 5px; font-weight: 500; }

    /* Toggle */
    #uploadModal .um-toggle-row {
        display: flex; align-items: center; justify-content: space-between; gap: 16px;
        padding: 13px 15px; background: var(--um-bg); border: 1.5px solid var(--um-border);
        border-radius: 12px; transition: border-color 200ms, background 200ms;
    }
    #uploadModal .um-toggle-row:has(input:checked) { background: var(--green-lt); border-color: var(--green-bd); }
    #uploadModal .um-toggle-title { font-size: 13px; font-weight: 600; color: var(--um-text-1); }
    #uploadModal .um-toggle-desc  { font-size: 11px; color: var(--um-text-3); margin-top: 2px; }
    #uploadModal .um-switch { position: relative; width: 44px; height: 24px; flex-shrink: 0; }
    #uploadModal .um-switch input { opacity: 0; width: 0; height: 0; position: absolute; }
    #uploadModal .um-track {
        position: absolute; inset: 0; border-radius: 24px; background: #CBD5E1;
        cursor: pointer; transition: background 220ms cubic-bezier(0.34,1.56,0.64,1);
    }
    #uploadModal .um-switch input:checked + .um-track { background: var(--green); }
    #uploadModal .um-thumb {
        position: absolute; top: 3px; left: 3px; width: 18px; height: 18px;
        border-radius: 50%; background: white; pointer-events: none;
        box-shadow: 0 1px 4px rgba(0,0,0,.2);
        transition: transform 220ms cubic-bezier(0.34,1.56,0.64,1);
    }
    #uploadModal .um-switch input:checked ~ .um-thumb { transform: translateX(20px); }

    /* Error alert */
    #uploadModal .um-alert {
        background: var(--red-lt); border: 1.5px solid var(--red-bd);
        border-radius: 12px; padding: 13px 15px; margin-bottom: 16px;
    }
    #uploadModal .um-alert-title {
        font-size: 13px; font-weight: 700; color: #991B1B;
        display: flex; align-items: center; gap: 6px; margin-bottom: 6px;
    }
    #uploadModal .um-alert ul { list-style: none; padding: 0; margin: 0; }
    #uploadModal .um-alert ul li {
        font-size: 12px; color: #B91C1C; padding: 2px 0;
        display: flex; align-items: flex-start; gap: 6px;
    }
    #uploadModal .um-alert ul li::before { content: '·'; font-weight: 900; flex-shrink: 0; }

    /* Footer */
    #uploadModal .um-footer {
        padding: 16px 28px; background: #FAFBFC; border-top: 1px solid #F1F5F9;
        display: flex; align-items: center; gap: 10px; flex-shrink: 0;
    }
    #uploadModal .um-btn-primary {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--blue); color: white; border: none;
        border-radius: 12px; padding: 11px 22px;
        font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit;
        box-shadow: 0 4px 14px rgba(37,99,235,.3);
        transition: background 180ms, transform 180ms cubic-bezier(0.34,1.56,0.64,1), box-shadow 180ms;
        position: relative; overflow: hidden;
    }
    #uploadModal .um-btn-primary::before {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,.12), transparent);
        pointer-events: none;
    }
    @media (hover: hover) and (pointer: fine) {
        #uploadModal .um-btn-primary:hover {
            background: var(--blue-dk); transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(37,99,235,.4);
        }
    }
    #uploadModal .um-btn-primary:active { transform: scale(.97); }
    #uploadModal .um-btn-primary:disabled { opacity: .65; cursor: not-allowed; transform: none !important; }

    #uploadModal .um-spinner {
        width: 16px; height: 16px;
        border: 2px solid rgba(255,255,255,.3); border-top-color: white;
        border-radius: 50%; animation: umSpin .6s linear infinite; display: none;
    }
    @keyframes umSpin { to { transform: rotate(360deg); } }

    #uploadModal .um-btn-cancel {
        display: inline-flex; align-items: center;
        background: var(--um-surface); color: var(--um-text-2);
        border: 1.5px solid var(--um-border); border-radius: 12px; padding: 11px 18px;
        font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit;
        transition: background 160ms, border-color 160ms, color 160ms;
    }
    #uploadModal .um-btn-cancel:hover { background: var(--um-bg); border-color: #CBD5E1; color: var(--um-text-1); }

    @media (prefers-reduced-motion: reduce) {
        #uploadModal .um-panel { transition: opacity 80ms linear !important; transform: none !important; }
        #uploadModal.upload-modal-overlay { transition: background 80ms linear !important; }
    }
    @media (max-width: 600px) {
        #uploadModal .um-row.c3 { grid-template-columns: 1fr; }
        #uploadModal .um-body   { padding: 16px 20px; }
        #uploadModal .um-header { padding: 18px 20px 0; }
        #uploadModal .um-footer { padding: 14px 20px; flex-wrap: wrap; }
    }
        /* Details modal (smooth) */
        .dm-trigger:hover { background:#dbeafe !important; transform:translateY(-1px); }
        .dm-overlay {
            position: fixed; inset: 0; z-index: 200;
            background: rgba(15,23,42,.45);
            display: flex; align-items: center; justify-content: center; padding: 20px;
            opacity: 0; visibility: hidden;
            transition: opacity .24s ease, visibility .24s ease;
        }
        .dm-overlay.is-open { opacity: 1; visibility: visible; }
        .dm-card {
            background: #fff; border-radius: 18px; width: 100%; max-width: 560px;
            max-height: 86vh; display: flex; flex-direction: column; overflow: hidden;
            box-shadow: 0 24px 70px rgba(0,0,0,.28);
            transform: translateY(18px) scale(.96); opacity: 0;
            transition: transform .3s cubic-bezier(.16,1,.3,1), opacity .26s ease;
        }
        .dm-overlay.is-open .dm-card { transform: translateY(0) scale(1); opacity: 1; }
        .dm-head { padding: 20px 22px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: flex-start; gap: 12px; }
        .dm-close {
            margin-left: auto; width: 32px; height: 32px; border-radius: 9px; border: none;
            background: #f1f5f9; color: #64748b; cursor: pointer; font-size: 18px; line-height: 1;
            display: flex; align-items: center; justify-content: center;
            transition: background .18s, transform .22s;
        }
        .dm-close:hover { background: #e2e8f0; transform: rotate(90deg); }
        .dm-stats { display: flex; gap: 10px; padding: 16px 22px 4px; }
        .dm-stat { flex: 1; text-align: center; background: #f8fafc; border-radius: 12px; padding: 12px 6px; }
        .dm-stat b { display: block; font-size: 20px; color: #1e293b; }
        .dm-stat span { font-size: 11px; color: #94a3b8; }
        .dm-body { overflow-y: auto; padding: 8px 22px; }
        .dm-sec-title { font-size: 13px; font-weight: 700; color: #1e293b; margin: 14px 0 6px; }
        .dm-row { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid #f5f7fa; }
        .dm-row:last-child { border-bottom: none; }
        .dm-av {
            width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 13px; color: #2563eb; background: #eff6ff;
        }
        .dm-empty { color: #94a3b8; font-size: 13px; padding: 10px 0; }
        .dm-foot { padding: 14px 22px; border-top: 1px solid #f1f5f9; }
        .dm-del {
            width: 100%; padding: 11px; border: none; border-radius: 11px; cursor: pointer;
            background: #fee2e2; color: #dc2626; font-weight: 700; font-size: 14px;
            transition: background .18s, transform .18s, box-shadow .2s;
        }
        .dm-del:hover { background: #fecaca; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(220,38,38,.18); }
        .dm-loading { padding: 40px; text-align: center; color: #94a3b8; }
</style>

{{-- ── Page ── --}}
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Learning Materials</h1>
            <p class="text-sm text-gray-400 mt-1">Upload and manage content for your students.</p>
        </div>
        <button type="button" class="upload-btn" onclick="umOpen()">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            Upload Material
        </button>
    </div>

    <div class="page-card">
        @forelse($materials as $material)
        <div class="material-item">
            <div class="material-icon">
                <svg width="18" height="18" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1
                           1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ $material->title }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $material->subject->name ?? '—' }}
                    @if($material->quarter) · {{ $material->quarter }} @endif
                    @if($material->week) · Week {{ $material->week }} @endif
                </p>
                <p class="text-xs text-gray-500 mt-1" style="display:flex;gap:14px;">
                    <span title="Viewed by">👁 {{ $material->views_count ?? 0 }}</span>
                    <span title="Hearts">❤️ {{ $material->likes_count ?? 0 }}</span>
                    <span title="Comments">💬 {{ $material->comments_count ?? 0 }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                    {{ $material->is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $material->is_published ? 'Published' : 'Draft' }}
                </span>
                <span class="text-xs text-gray-400">{{ $material->created_at->diffForHumans() }}</span>
                <button type="button"
                   onclick="dmOpen('{{ route('teacher.materials.show', $material) }}', '{{ route('teacher.materials.destroy', $material) }}')"
                   class="dm-trigger px-2.5 py-1 rounded-lg text-xs font-semibold"
                   style="background:#eff6ff;color:#2563eb;border:none;cursor:pointer;transition:background .18s,transform .18s;">Details</button>
                <form method="POST" action="{{ route('teacher.materials.destroy', $material) }}"
                      onsubmit="return confirm('Delete this material? This cannot be undone.');"
                      style="margin:0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-2.5 py-1 rounded-lg text-xs font-semibold"
                        style="background:#fee2e2;color:#dc2626;border:none;cursor:pointer;">Delete</button>
                </form>
            </div>
        </div>
        @empty
        <div class="py-16 text-center">
            <div class="w-14 h-14 bg-blue-50 rounded-full mx-auto mb-4"
                style="display:flex;align-items:center;justify-content:center;">
                <svg width="24" height="24" fill="none" stroke="#93c5fd" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5
                           5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
            </div>
            <p class="text-gray-400 text-sm font-medium">No materials uploaded yet.</p>
            <p class="text-gray-300 text-xs mt-1">Click "Upload Material" to add your first material.</p>
        </div>
        @endforelse
    </div>

    @if($materials->count())
    <div class="mt-4">{{ $materials->links() }}</div>
    @endif
</div>

{{-- ══════════════════════════════════════
     UPLOAD MODAL
     Moved OUTSIDE .main-content so the
     AJAX innerHTML swap never destroys it,
     and layout's modal CSS cannot interfere.
══════════════════════════════════════ --}}
<div id="uploadModal" class="upload-modal-overlay"
     aria-modal="true" role="dialog" aria-hidden="true"
     onclick="umOverlayClick(event)">

    <div class="um-panel">

        <div class="um-header">
            <div>
                <div class="um-badge">
                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    New Upload
                </div>
                <h2 class="um-title">Upload Learning Material</h2>
                <p class="um-sub">Share PDFs, documents, videos, and other content with your students.</p>
            </div>
            <button type="button" class="um-close" onclick="umClose()" aria-label="Close">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="um-body">

            @if($errors->any())
            <div class="um-alert">
                <div class="um-alert-title">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
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

            <form method="POST" action="{{ route('teacher.materials.store') }}"
                  enctype="multipart/form-data" id="umForm">
                @csrf

                {{-- Details --}}
                <div class="um-section">
                    <div class="um-section-label">Material Details</div>
                    <div style="margin-bottom:14px;">
                        <label class="um-label">Title <span>*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}"
                               placeholder="e.g. Chapter 1 — Introduction to Algebra"
                               required autocomplete="off"
                               class="um-input {{ $errors->has('title') ? 'err' : '' }}">
                        @error('title')
                            <p class="um-field-err">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="um-row c3">
                        <div>
                            <label class="um-label">Subject <span>*</span></label>
                            <select name="subject_id" required
                                    class="um-input {{ $errors->has('subject_id') ? 'err' : '' }}">
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
                                <p class="um-field-err">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="um-label">Quarter <span>*</span></label>
                            <select name="quarter" required
                                    class="um-input {{ $errors->has('quarter') ? 'err' : '' }}">
                                <option value="">—</option>
                                <option value="Q1" {{ old('quarter')=='Q1'?'selected':'' }}>Q1 — First</option>
                                <option value="Q2" {{ old('quarter')=='Q2'?'selected':'' }}>Q2 — Second</option>
                                <option value="Q3" {{ old('quarter')=='Q3'?'selected':'' }}>Q3 — Third</option>
                                <option value="Q4" {{ old('quarter')=='Q4'?'selected':'' }}>Q4 — Fourth</option>
                            </select>
                            @error('quarter')
                                <p class="um-field-err">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="um-label">Week</label>
                            <input type="number" name="week" min="1" max="20"
                                   value="{{ old('week') }}" placeholder="1–20" class="um-input">
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                <div class="um-section">
                    <div class="um-section-label">Description</div>
                    <textarea name="description" rows="3"
                              placeholder="Brief description of what this material covers…"
                              class="um-input">{{ old('description') }}</textarea>
                </div>

                {{-- File --}}
                <div class="um-section">
                    <div class="um-section-label">File</div>
                    <div class="um-drop" id="umDrop">
                        <input type="file" name="file" id="umFileInput"
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.png,.jpg,.jpeg,.mp4,.zip"
                               onchange="umHandleFile(this)">
                        <div class="um-drop-icon">
                            <svg width="20" height="20" fill="none" stroke="#3B82F6" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <p class="um-drop-title">Drop your file here, or <strong style="color:#2563EB">browse</strong></p>
                        <p class="um-drop-sub">Max <strong>20 MB</strong> per file</p>
                        <div class="um-tags">
                            @foreach(['PDF','DOC','PPT','XLS','PNG','JPG','MP4','ZIP'] as $ext)
                                <span class="um-tag">{{ $ext }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="um-file-preview" id="umFilePreview">
                        <div class="um-file-icon" id="umFileIcon"></div>
                        <div style="flex:1;min-width:0;">
                            <p class="um-file-name" id="umFileName">—</p>
                            <p class="um-file-size" id="umFileSize">—</p>
                        </div>
                        <button type="button" class="um-file-clear" onclick="umClearFile()">×</button>
                    </div>
                    <div class="um-progress" id="umProgress">
                        <div class="um-progress-track"><div class="um-progress-fill" id="umProgressFill"></div></div>
                        <p class="um-progress-label" id="umProgressLabel">Preparing…</p>
                    </div>
                    @error('file')
                        <p class="um-field-err" style="margin-top:8px;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Visibility --}}
                <div class="um-section">
                    <div class="um-section-label">Visibility</div>
                    <div class="um-toggle-row">
                        <div>
                            <p class="um-toggle-title">Publish immediately</p>
                            <p class="um-toggle-desc">Students can view this material right away.</p>
                        </div>
                        <label class="um-switch">
                            <input type="checkbox" name="is_published" value="1"
                                   {{ old('is_published') ? 'checked' : '' }}>
                            <div class="um-track"></div>
                            <div class="um-thumb"></div>
                        </label>
                    </div>
                </div>

            </form>
        </div>

        <div class="um-footer">
            <button type="submit" form="umForm" class="um-btn-primary" id="umSubmitBtn">
                <svg id="umBtnIcon" width="16" height="16" fill="none" stroke="currentColor"
                     stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <div class="um-spinner" id="umSpinner"></div>
                <span id="umBtnTxt">Upload Material</span>
            </button>
            <button type="button" class="um-btn-cancel" onclick="umClose()">Cancel</button>
        </div>

    </div>
</div>

{{-- ══════════ DETAILS MODAL ══════════ --}}
<div class="dm-overlay" id="detailsModal" onclick="dmOverlayClick(event)" aria-hidden="true">
    <div class="dm-card" role="dialog" aria-modal="true">
        <div class="dm-head">
            <div>
                <h3 id="dmTitle" style="font-size:17px;font-weight:800;color:#1e293b;margin:0;">Material</h3>
                <p id="dmMeta" style="font-size:12px;color:#94a3b8;margin:3px 0 0;"></p>
            </div>
            <button type="button" class="dm-close" onclick="dmClose()" aria-label="Close">&times;</button>
        </div>
        <div class="dm-stats">
            <div class="dm-stat"><b id="dmViews">0</b><span>👁 Viewed</span></div>
            <div class="dm-stat"><b id="dmLikes">0</b><span>❤️ Hearts</span></div>
            <div class="dm-stat"><b id="dmComments">0</b><span>💬 Comments</span></div>
        </div>
        <div class="dm-body" id="dmBody">
            <div class="dm-loading">Loading…</div>
        </div>
        <div class="dm-foot">
            <form id="dmDeleteForm" method="POST" onsubmit="return confirm('Delete this material? This cannot be undone.');">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="dm-del">Delete material</button>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const ov = document.getElementById('detailsModal');

    window.dmOpen = function (showUrl, destroyUrl) {
        document.getElementById('dmDeleteForm').action = destroyUrl;
        document.getElementById('dmBody').innerHTML = '<div class="dm-loading">Loading…</div>';
        document.getElementById('dmTitle').textContent = 'Material';
        document.getElementById('dmMeta').textContent = '';
        document.getElementById('dmViews').textContent = '0';
        document.getElementById('dmLikes').textContent = '0';
        document.getElementById('dmComments').textContent = '0';
        document.body.style.overflow = 'hidden';
        ov.classList.add('is-open');

        fetch(showUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => dmRender(d))
            .catch(() => {
                document.getElementById('dmBody').innerHTML = '<div class="dm-loading">Failed to load.</div>';
            });
    };

    window.dmClose = function () {
        ov.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    window.dmOverlayClick = function (e) {
        if (e.target === ov) dmClose();
    };

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && ov.classList.contains('is-open')) dmClose();
    });

    function esc(s) {
        return (s == null ? '' : String(s)).replace(/[&<>"']/g, c =>
            ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }
    function initial(name) {
        const n = (name || 'S').trim();
        return n ? n[0].toUpperCase() : 'S';
    }

    function dmRender(d) {
        document.getElementById('dmTitle').textContent = d.title || 'Material';
        const meta = [d.subject, d.quarter, d.week ? ('Week ' + d.week) : null,
                      d.is_published ? 'Published' : 'Draft'].filter(Boolean).join(' · ');
        document.getElementById('dmMeta').textContent = meta;
        document.getElementById('dmViews').textContent = d.view_count ?? 0;
        document.getElementById('dmLikes').textContent = d.like_count ?? 0;
        document.getElementById('dmComments').textContent = d.comment_count ?? 0;

        let html = '';
        if (d.description) {
            html += '<p style="font-size:13px;color:#475569;line-height:1.5;margin:10px 0 0;">' + esc(d.description) + '</p>';
        }

        html += '<div class="dm-sec-title">Who viewed (' + (d.view_count ?? 0) + ')</div>';
        if (d.viewers && d.viewers.length) {
            d.viewers.forEach(v => {
                html += '<div class="dm-row"><div class="dm-av">' + esc(initial(v.name)) + '</div>' +
                        '<div style="flex:1;min-width:0;font-size:13px;font-weight:600;color:#334155;">' + esc(v.name) + '</div>' +
                        '<span style="font-size:11px;color:#94a3b8;">' + esc(v.at) + '</span></div>';
            });
        } else {
            html += '<div class="dm-empty">No students have viewed this yet.</div>';
        }

        html += '<div class="dm-sec-title">Comments (' + (d.comment_count ?? 0) + ')</div>';
        if (d.comments && d.comments.length) {
            d.comments.forEach(c => {
                html += '<div class="dm-row" style="align-items:flex-start;"><div class="dm-av" style="color:#16a34a;background:#dcfce7;">' + esc(initial(c.name)) + '</div>' +
                        '<div style="flex:1;min-width:0;"><div style="font-size:12.5px;font-weight:700;color:#334155;">' + esc(c.name) +
                        ' <span style="font-weight:400;color:#94a3b8;font-size:11px;">' + esc(c.at) + '</span></div>' +
                        '<div style="font-size:13px;color:#475569;margin-top:2px;">' + esc(c.body) + '</div></div></div>';
            });
        } else {
            html += '<div class="dm-empty">No comments yet.</div>';
        }

        document.getElementById('dmBody').innerHTML = html;
    }
})();
</script>

<script>
(function () {
    const EXT_COLORS = {
        pdf:  ['#EF4444','#FEF2F2'], doc:  ['#2563EB','#EFF6FF'],
        docx: ['#2563EB','#EFF6FF'], ppt:  ['#F97316','#FFF7ED'],
        pptx: ['#F97316','#FFF7ED'], xls:  ['#16A34A','#F0FDF4'],
        xlsx: ['#16A34A','#F0FDF4'], mp4:  ['#8B5CF6','#F5F3FF'],
        zip:  ['#6B7280','#F9FAFB'],
    };

    const overlay = document.getElementById('uploadModal');
    let closing = false;

    window.umOpen = function () {
        document.body.style.overflow = 'hidden';
        overlay.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(() => overlay.classList.add('is-open'));
    };

    window.umClose = function () {
        if (closing) return;
        closing = true;
        overlay.classList.add('is-closing');
        overlay.classList.remove('is-open');
        setTimeout(() => {
            overlay.classList.remove('is-closing');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            closing = false;
        }, 160);
    };

    window.umOverlayClick = function (e) {
        if (e.target === overlay) umClose();
    };

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) umClose();
    });

    @if($errors->any())
    // Auto-open on validation error redirect
    requestAnimationFrame(() => umOpen());
    @endif

    window.umHandleFile = function (input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const ext  = file.name.split('.').pop().toLowerCase();
        const [color, bg] = EXT_COLORS[ext] || ['#3B82F6','#EFF6FF'];
        const size = file.size > 1048576
            ? (file.size/1048576).toFixed(2)+' MB'
            : (file.size/1024).toFixed(1)+' KB';

        document.getElementById('umFileName').textContent = file.name;
        document.getElementById('umFileSize').textContent = size+' · .'+ext.toUpperCase();
        const icon = document.getElementById('umFileIcon');
        icon.style.background  = bg;
        icon.style.borderColor = color+'44';
        icon.innerHTML = `<span style="font-size:10px;font-weight:800;color:${color};font-family:monospace;">.${ext.toUpperCase()}</span>`;
        document.getElementById('umFilePreview').style.display = 'flex';
    };

    window.umClearFile = function () {
        document.getElementById('umFileInput').value = '';
        document.getElementById('umFilePreview').style.display = 'none';
    };

    // Drag & drop
    const drop = document.getElementById('umDrop');
    ['dragover','dragenter'].forEach(ev =>
        drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('dragover'); })
    );
    ['dragleave','drop'].forEach(ev =>
        drop.addEventListener(ev, () => drop.classList.remove('dragover'))
    );

    // Submit loading state
    document.getElementById('umForm').addEventListener('submit', function () {
        const btn  = document.getElementById('umSubmitBtn');
        const icon = document.getElementById('umBtnIcon');
        const spin = document.getElementById('umSpinner');
        const txt  = document.getElementById('umBtnTxt');
        const prog = document.getElementById('umProgress');
        const fill = document.getElementById('umProgressFill');
        const lbl  = document.getElementById('umProgressLabel');

        btn.disabled = true;
        icon.style.display = 'none';
        spin.style.display = 'block';
        txt.textContent    = 'Uploading…';
        prog.style.display = 'block';

        let pct = 0;
        setInterval(() => {
            pct = Math.min(pct + Math.random() * 18, 90);
            fill.style.width = pct + '%';
            lbl.textContent  = pct < 40 ? 'Uploading file…' : pct < 75 ? 'Processing…' : 'Almost done…';
        }, 300);
    });
})();
</script>
@endsection