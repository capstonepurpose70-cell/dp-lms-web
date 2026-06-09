@extends('layouts.faculty')
@section('title', 'Enrollment Requests')

@section('sidebar')
    <a href="{{ route('faculty.dashboard') }}"
        class="{{ request()->routeIs('faculty.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>
    <a href="{{ route('faculty.enrollments') }}"
        class="{{ request()->routeIs('faculty.enrollments*') ? 'active' : '' }}">
        Enrollments
    </a>
@endsection

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;1,400;1,600&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    *, *::before, *::after { box-sizing: border-box; }

    :root {
        --ink:        #0f0d1a;
        --ink-soft:   #3d3750;
        --ink-muted:  #8b82a7;
        --ink-ghost:  #c5bfda;
        --surface:    #faf9fc;
        --surface-2:  #f3f0f9;
        --surface-3:  #ebe6f5;
        --border:     #e6e0f4;
        --border-2:   #d4cce8;
        --violet:     #6c3fc5;
        --violet-mid: #7e52d4;
        --violet-lt:  #a982f0;
        --violet-bg:  #f0eaff;
        --teal:       #0d9488;
        --teal-bg:    #e6faf8;
        --amber:      #c07a00;
        --amber-bg:   #fef7e6;
        --blue:       #2563eb;
        --blue-bg:    #eff4ff;
        --red:        #c0392b;
        --red-bg:     #fdecea;
        --radius-sm:  8px;
        --radius-md:  12px;
        --radius-lg:  18px;
        --shadow-sm:  0 1px 3px rgba(15,13,26,0.06), 0 1px 2px rgba(15,13,26,0.04);
        --shadow-md:  0 4px 16px rgba(108,63,197,0.08), 0 1px 4px rgba(15,13,26,0.05);
        --font-body:  'Source Sans 3', sans-serif;
        --font-serif: 'Lora', serif;
    }

    .er-page {
        font-family: var(--font-body);
        font-size: 15px;
        color: var(--ink);
        animation: er-in 0.38s cubic-bezier(0.22,1,0.36,1) both;
    }
    @keyframes er-in {
        from { opacity:0; transform:translateY(10px); }
        to   { opacity:1; transform:translateY(0); }
    }

    /* ── Page header ── */
    .er-header {
        display: flex; align-items: flex-end; justify-content: space-between;
        margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;
    }
    .er-eyebrow {
        font-size: 11px; font-weight: 600;
        letter-spacing: 0.12em; text-transform: uppercase;
        color: var(--ink-muted); margin-bottom: 5px;
        display: flex; align-items: center; gap: 6px;
        font-family: var(--font-body);
    }
    .er-eyebrow::before {
        content: '';
        display: inline-block; width: 16px; height: 2px;
        background: linear-gradient(90deg, var(--violet), var(--teal));
        border-radius: 2px;
    }
    .er-title {
        font-family: var(--font-serif);
        font-size: 2rem; font-weight: 400;
        color: var(--ink); line-height: 1.15;
        letter-spacing: -0.01em;
    }
    .er-title em { font-style: italic; color: var(--violet-mid); }
    .er-sub {
        font-size: 13.5px; color: var(--ink-muted);
        margin-top: 6px; font-weight: 400; line-height: 1.5;
        font-family: var(--font-body);
    }

    /* ── Filter bar ── */
    .er-filter-bar {
        display: flex; align-items: center; gap: 10px;
        flex-wrap: wrap;
    }
    .er-input-wrap {
        position: relative; flex: 1; min-width: 180px; max-width: 240px;
    }
    .er-input-icon {
        position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
        color: var(--ink-ghost); pointer-events: none;
        display: flex; align-items: center;
    }
    .er-input {
        width: 100%;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 9px 12px 9px 34px;
        font-size: 14px; font-family: var(--font-body);
        color: var(--ink);
        outline: none;
        box-shadow: var(--shadow-sm);
        transition: border-color 0.18s, box-shadow 0.18s;
    }
    .er-input::placeholder { color: var(--ink-ghost); }
    .er-input:focus {
        border-color: var(--violet-lt);
        box-shadow: 0 0 0 3px rgba(108,63,197,0.1);
    }

    .er-select {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 9px 32px 9px 12px;
        font-size: 14px; font-family: var(--font-body);
        color: var(--ink);
        outline: none;
        box-shadow: var(--shadow-sm);
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%238b82a7' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 11px center;
        transition: border-color 0.18s, box-shadow 0.18s;
    }
    .er-select:focus {
        border-color: var(--violet-lt);
        box-shadow: 0 0 0 3px rgba(108,63,197,0.1);
    }

    .er-btn-filter {
        background: var(--ink);
        color: #fff;
        border: none;
        border-radius: var(--radius-sm);
        padding: 9px 20px;
        font-size: 14px; font-weight: 600;
        font-family: var(--font-body);
        cursor: pointer;
        box-shadow: var(--shadow-sm);
        transition: background 0.18s, transform 0.15s;
        letter-spacing: 0.01em;
    }
    .er-btn-filter:hover {
        background: var(--violet);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(108,63,197,0.25);
    }
    .er-btn-filter:active { transform: translateY(0); }

    /* ── Table card ── */
    .er-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .er-table { width: 100%; border-collapse: collapse; }

    .er-thead th {
        padding: 12px 18px;
        text-align: left;
        font-size: 11px; font-weight: 700;
        letter-spacing: 0.08em; text-transform: uppercase;
        color: var(--ink-muted);
        background: var(--surface);
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
        font-family: var(--font-body);
    }
    .er-thead th:first-child { padding-left: 20px; }
    .er-thead th:last-child  { padding-right: 20px; text-align: right; }

    .er-tbody tr {
        border-bottom: 1px solid var(--surface-2);
        transition: background 0.14s;
    }
    .er-tbody tr:last-child { border-bottom: none; }
    .er-tbody tr:hover { background: #fdfcff; }

    .er-tbody td {
        padding: 14px 18px;
        vertical-align: middle;
    }
    .er-tbody td:first-child { padding-left: 20px; }
    .er-tbody td:last-child  { padding-right: 20px; text-align: right; }

    .er-student-name {
        font-size: 14px; font-weight: 600;
        color: var(--ink); letter-spacing: 0;
        white-space: nowrap;
        font-family: var(--font-body);
    }
    .er-student-email {
        font-size: 12.5px; color: var(--ink-muted);
        margin-top: 2px; font-weight: 400;
        font-family: var(--font-body);
    }

    .er-avatar {
        width: 36px; height: 36px; border-radius: var(--radius-sm);
        background: linear-gradient(135deg, var(--violet-mid), #5a32b0);
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700; color: #fff;
        flex-shrink: 0;
        font-family: var(--font-body);
    }
    .er-student-cell {
        display: flex; align-items: center; gap: 11px;
    }

    .er-grade {
        font-size: 14px; color: var(--ink-soft); font-weight: 500;
        font-family: var(--font-body);
    }

    .er-pill {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 11px; font-weight: 600;
        padding: 4px 10px; border-radius: 20px;
        border: 1px solid transparent;
        letter-spacing: 0.03em; white-space: nowrap;
        font-family: var(--font-body);
    }
    .er-pill::before {
        content: ''; width: 5px; height: 5px;
        border-radius: 50%; flex-shrink: 0;
    }
    .er-type-new      { background: var(--blue-bg);   color: var(--blue);   border-color: #bfcff8; }
    .er-type-new::before      { background: var(--blue); }
    .er-type-transfer { background: var(--violet-bg); color: var(--violet); border-color: #d4c4f8; }
    .er-type-transfer::before { background: var(--violet-lt); }
    .er-type-old      { background: var(--surface-2); color: var(--ink-soft); border-color: var(--border); }
    .er-type-old::before      { background: var(--ink-ghost); }
    .er-status-approved { background: var(--teal-bg);  color: var(--teal);   border-color: #99e6e1; }
    .er-status-approved::before { background: var(--teal); }
    .er-status-pending  { background: var(--amber-bg); color: var(--amber);  border-color: #f3d89a; }
    .er-status-pending::before  { background: #e8a800; }
    .er-status-rejected { background: var(--red-bg);   color: var(--red);    border-color: #f5b3ac; }
    .er-status-rejected::before { background: var(--red); }

    .er-date {
        font-size: 13px; color: var(--ink-muted); font-weight: 400;
        font-family: var(--font-body);
    }

    /* ── Preview button ── */
    .er-preview-btn {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 13px; font-weight: 600;
        color: var(--violet);
        background: var(--violet-bg);
        border: 1px solid #ddd4f8;
        border-radius: var(--radius-sm);
        padding: 6px 13px;
        cursor: pointer;
        transition: all 0.18s;
        letter-spacing: 0;
        font-family: var(--font-body);
    }
    .er-preview-btn:hover {
        background: #e4d9ff;
        color: #5a32b0;
        border-color: #c9b8f5;
        box-shadow: 0 2px 8px rgba(108,63,197,0.15);
        transform: translateY(-1px);
    }
    .er-preview-btn svg { transition: transform 0.18s; }
    .er-preview-btn:hover svg { transform: translateX(2px); }

    /* empty state */
    .er-empty-row td { padding: 56px 20px; text-align: center; }
    .er-empty-inner  { display: flex; flex-direction: column; align-items: center; gap: 8px; }
    .er-empty-icon {
        width: 48px; height: 48px; border-radius: 14px;
        background: var(--violet-bg);
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 4px;
    }
    .er-empty-icon svg { width: 22px; height: 22px; color: var(--violet-lt); }
    .er-empty-title { font-size: 14px; font-weight: 600; color: var(--ink-soft); font-family: var(--font-body); }
    .er-empty-sub   { font-size: 13px; color: var(--ink-muted); font-family: var(--font-body); }

    /* pagination */
    .er-pagination {
        padding: 14px 20px;
        border-top: 1px solid var(--surface-2);
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .er-pagination-label {
        font-size: 13px; color: var(--ink-muted); font-weight: 400;
        font-family: var(--font-body);
    }

    /* ════════════════════════════════════════
       MODAL
    ════════════════════════════════════════ */
    .enroll-overlay {
        position: fixed; inset: 0; z-index: 9000;
        display: flex; align-items: center; justify-content: center;
        padding: 1.5rem;
        pointer-events: none; opacity: 0;
        transition: opacity 0.32s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .enroll-overlay.is-open { pointer-events: all; opacity: 1; }

    .enroll-backdrop {
        position: absolute; inset: 0;
        background: rgba(15, 13, 26, 0.55);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        cursor: pointer;
    }

    .enroll-modal {
        position: relative; z-index: 1;
        background: #fff;
        border-radius: 22px;
        width: 100%; max-width: 740px; max-height: 88vh;
        overflow-y: auto; overflow-x: hidden;
        box-shadow:
            0 0 0 1px rgba(108,63,197,0.12),
            0 32px 80px rgba(15,13,26,0.28),
            0 8px 24px rgba(108,63,197,0.14);
        transform: translateY(28px) scale(0.96);
        opacity: 0;
        transition:
            transform 0.4s cubic-bezier(0.16, 1, 0.3, 1),
            opacity   0.3s cubic-bezier(0.16, 1, 0.3, 1);
        scrollbar-width: thin;
        scrollbar-color: var(--border) transparent;
        font-family: var(--font-body);
    }
    .enroll-overlay.is-open .enroll-modal { transform: translateY(0) scale(1); opacity: 1; }
    .enroll-modal::-webkit-scrollbar { width: 5px; }
    .enroll-modal::-webkit-scrollbar-track { background: transparent; }
    .enroll-modal::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }

    .modal-stripe {
        height: 4px;
        background: linear-gradient(90deg, var(--violet) 0%, #7e52d4 40%, var(--teal) 100%);
        border-radius: 22px 22px 0 0;
    }

    .modal-header {
        padding: 1.6rem 1.75rem 1.3rem;
        display: flex; align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        border-bottom: 1px solid var(--border);
    }
    .modal-eyebrow {
        font-size: 11px; font-weight: 600;
        letter-spacing: 0.12em; text-transform: uppercase;
        color: var(--violet-lt); margin-bottom: 5px;
        font-family: var(--font-body);
    }
    .modal-name {
        font-family: var(--font-serif);
        font-size: 1.7rem; font-weight: 400;
        color: var(--ink); line-height: 1.15;
        letter-spacing: -0.01em;
        margin-bottom: 0.7rem;
    }
    .modal-name em { font-style: italic; color: var(--violet-mid); }
    .modal-tags { display: flex; flex-wrap: wrap; gap: 6px; }

    .modal-close {
        width: 34px; height: 34px; border-radius: 8px;
        background: var(--surface-2); border: 1px solid var(--border);
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; margin-top: 2px;
        transition: background 0.15s, transform 0.15s;
        color: var(--ink-muted);
    }
    .modal-close:hover { background: var(--border); color: var(--ink); transform: scale(1.05); }
    .modal-close:active { transform: scale(0.95); }

    .modal-body { padding: 1.5rem 1.75rem; display: flex; flex-direction: column; gap: 1.25rem; }

    .modal-section {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        overflow: hidden;
    }
    .modal-section-head {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
        background: var(--surface-2);
    }
    .modal-section-icon {
        width: 30px; height: 30px; border-radius: 7px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .modal-section-icon svg { width: 15px; height: 15px; }
    .modal-section-title {
        font-size: 11px; font-weight: 700;
        letter-spacing: 0.08em; text-transform: uppercase;
        color: var(--ink-muted);
        font-family: var(--font-body);
    }
    .modal-section-body { padding: 18px; }

    .mfgrid   { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 28px; }
    .mfgrid-3 { display: grid; grid-template-columns: repeat(3,1fr); gap: 18px 28px; }
    @media (max-width: 560px) {
        .mfgrid, .mfgrid-3 { grid-template-columns: 1fr 1fr; }
    }

    .mfield-label {
        display: block;
        font-size: 10.5px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase;
        color: var(--ink-ghost); margin-bottom: 5px;
        font-family: var(--font-body);
    }
    .mfield-value {
        font-size: 14.5px; font-weight: 600; color: var(--ink);
        letter-spacing: 0; line-height: 1.4;
        font-family: var(--font-body);
    }
    .mfield-value.dim {
        color: var(--ink-ghost); font-weight: 400; font-style: italic;
        font-family: var(--font-serif);
    }
    .mfield-divider { border: none; border-top: 1px dashed var(--border); margin: 16px 0; }

    .modal-footer {
        padding: 1rem 1.75rem 1.5rem;
        display: flex; align-items: center; justify-content: flex-end;
        gap: 10px; border-top: 1px solid var(--border);
    }
    .modal-btn-close-soft {
        padding: 10px 22px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border);
        background: var(--surface);
        font-size: 14px; font-weight: 600;
        color: var(--ink-muted); cursor: pointer;
        font-family: var(--font-body);
        transition: all 0.15s; letter-spacing: 0;
    }
    .modal-btn-close-soft:hover { background: var(--border); color: var(--ink); }

    .modal-btn-review {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 10px 24px;
        border-radius: var(--radius-sm); border: none;
        background: var(--violet); color: #fff;
        font-size: 14px; font-weight: 700;
        font-family: var(--font-body);
        cursor: pointer; text-decoration: none;
        letter-spacing: 0.01em;
        box-shadow: 0 2px 10px rgba(108,63,197,0.3);
        transition: all 0.18s;
    }
    .modal-btn-review:hover {
        background: #5a32b0;
        box-shadow: 0 4px 18px rgba(108,63,197,0.4);
        transform: translateY(-1px);
    }
    .modal-btn-review svg { width: 15px; height: 15px; }

    .modal-status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 11.5px; font-weight: 700;
        padding: 4px 12px; border-radius: 99px;
        border: 1px solid transparent;
        letter-spacing: 0.04em; text-transform: uppercase;
        flex-shrink: 0; margin-top: 4px;
        font-family: var(--font-body);
    }
    .modal-status-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .msp-approved { background: var(--teal-bg); color: var(--teal); border-color: #99e6e1; }
    .msp-approved::before { background: var(--teal); }
    .msp-pending  { background: var(--amber-bg); color: var(--amber); border-color: #f3d89a; }
    .msp-pending::before  { background: #e8a800; }
    .msp-rejected { background: var(--red-bg); color: var(--red); border-color: #f5b3ac; }
    .msp-rejected::before { background: var(--red); }

    .modal-skeleton {
        padding: 2.5rem 1.75rem;
        display: flex; flex-direction: column; gap: 14px;
        align-items: center; justify-content: center;
        min-height: 240px;
    }
    .sk-spin {
        width: 38px; height: 38px; border-radius: 50%;
        border: 3px solid var(--border); border-top-color: var(--violet);
        animation: sk-rotate 0.7s linear infinite;
    }
    @keyframes sk-rotate { to { transform: rotate(360deg); } }
    .sk-label { font-size: 14px; color: var(--ink-muted); font-family: var(--font-body); }

    .modal-assigned-badge {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 12.5px; font-weight: 600;
        background: var(--violet-bg); color: var(--violet-mid);
        border: 1px solid #d4c4f8;
        border-radius: var(--radius-sm); padding: 5px 12px; margin-top: 4px;
        font-family: var(--font-body);
    }
    .modal-assigned-badge svg { width: 12px; height: 12px; }

    .modal-reviewed-notice {
        display: flex; flex-direction: column; align-items: center;
        gap: 10px; text-align: center; padding: 1.4rem;
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md);
    }
    .modal-reviewed-icon {
        width: 48px; height: 48px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .modal-reviewed-icon svg { width: 22px; height: 22px; }
    .modal-reviewed-text {
        font-size: 14px; color: var(--ink-soft); line-height: 1.7;
        font-family: var(--font-body);
    }
    .modal-reviewed-text strong { color: var(--ink); }
    .modal-rejection-reason {
        font-size: 13px; color: var(--red);
        background: var(--red-bg); border: 1px solid #f5b3ac;
        border-radius: var(--radius-sm); padding: 10px 16px;
        max-width: 440px; line-height: 1.6;
        font-family: var(--font-body);
    }
    .modal-rejection-reason strong {
        display: block; font-size: 10px; letter-spacing: 0.08em;
        text-transform: uppercase; margin-bottom: 4px; color: var(--red);
    }
</style>

<div class="er-page">

    {{-- ── Page Header ── --}}
    <div class="er-header">
        <div class="er-header-left">
            <div class="er-eyebrow">Faculty Portal</div>
            <div class="er-title">Enrollment <em>Requests</em></div>
            <div class="er-sub">Review and process student enrollment applications.</div>
        </div>
    </div>

    {{-- ── Filter Bar ── --}}
    <form method="GET" class="er-filter-bar" style="margin-bottom: 1.25rem;">
        <div class="er-input-wrap">
            <span class="er-input-icon">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
            </span>
            <input
                type="text" name="search"
                value="{{ request('search') }}"
                placeholder="Search student name…"
                class="er-input">
        </div>

        <select name="status" class="er-select">
            <option value="">All status</option>
            <option value="pending"  {{ request('status')=='pending'  ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Rejected</option>
        </select>

        <button type="submit" class="er-btn-filter">Filter</button>
    </form>

    {{-- ── Table Card ── --}}
    <div class="er-card">
        <table class="er-table">
            <thead class="er-thead">
                <tr>
                    <th>Student</th>
                    <th>Grade</th>
                    <th>Type</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody class="er-tbody">
                @forelse($enrollments as $enrollment)
                <tr>
                    <td>
                        <div class="er-student-cell">
                            <div class="er-avatar">
                                {{ strtoupper(substr($enrollment->full_name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <div class="er-student-name">{{ $enrollment->full_name }}</div>
                                <div class="er-student-email">{{ $enrollment->student->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="er-grade">Grade {{ $enrollment->grade_level }}</span>
                    </td>
                    <td>
                        @php $type = $enrollment->student_type; @endphp
                        <span class="er-pill
                            {{ $type === 'new'      ? 'er-type-new'      :
                               ($type === 'transfer' ? 'er-type-transfer' : 'er-type-old') }}">
                            {{ ucfirst($type) }}
                        </span>
                    </td>
                    <td>
                        <span class="er-date">
                            {{ $enrollment->created_at->format('M d, Y') }}
                        </span>
                    </td>
                    <td>
                        @php $status = $enrollment->status; @endphp
                        <span class="er-pill
                            {{ $status === 'approved' ? 'er-status-approved' :
                               ($status === 'pending'  ? 'er-status-pending'  : 'er-status-rejected') }}">
                            {{ ucfirst($status) }}
                        </span>
                    </td>
                    <td>
                        <button
                            type="button"
                            class="er-preview-btn"
                            onclick="openEnrollPreview({{ $enrollment->id }})"
                        >
                            Preview
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr class="er-empty-row">
                    <td colspan="6">
                        <div class="er-empty-inner">
                            <div class="er-empty-icon">
                                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="er-empty-title">No enrollment requests found.</div>
                            <div class="er-empty-sub">Try adjusting your search or filter.</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="er-pagination">
            <span class="er-pagination-label">
                Showing {{ $enrollments->firstItem() ?? 0 }}–{{ $enrollments->lastItem() ?? 0 }}
                of {{ $enrollments->total() }} results
            </span>
            <div>{{ $enrollments->withQueryString()->links() }}</div>
        </div>
    </div>

</div>

{{-- ════════════════════════════════════════
     ENROLLMENT PREVIEW MODAL
════════════════════════════════════════ --}}

<script id="enrollments-data" type="application/json">
    {!! json_encode($enrollments->map(function($e) {
        return [
            'id'                  => $e->id,
            'full_name'           => $e->full_name,
            'grade_level'         => $e->grade_level,
            'student_type'        => $e->student_type,
            'school_year'         => $e->school_year,
            'status'              => $e->status,
            'address'             => $e->address,
            'age'                 => $e->age,
            'birthdate'           => $e->birthdate?->format('M d, Y'),
            'gender'              => $e->gender,
            'mother_name'         => $e->mother_name,
            'father_name'         => $e->father_name,
            'guardian_name'       => $e->guardian_name,
            'guardian_contact'    => $e->guardian_contact,
            'last_school'         => $e->last_school,
            'last_grade_completed'=> $e->last_grade_completed,
            'email'               => $e->student->email ?? null,
            'reviewed_at'         => $e->reviewed_at?->format('F d, Y \a\t g:i A'),
            'reviewer_name'       => $e->reviewer?->name,
            'remarks'             => $e->remarks,
            'section_name'        => $e->section?->name,
            'review_url'          => route('faculty.enrollments.show', $e),
        ];
    })->keyBy('id')) !!}
</script>

<div class="enroll-overlay" id="enrollOverlay" role="dialog" aria-modal="true" aria-labelledby="modalStudentName">
    <div class="enroll-backdrop" onclick="closeEnrollPreview()"></div>
    <div class="enroll-modal" id="enrollModal">
        <div id="enrollModalContent">
            <div class="modal-skeleton">
                <div class="sk-spin"></div>
                <span class="sk-label">Loading details…</span>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const overlay = document.getElementById('enrollOverlay');
    const content = document.getElementById('enrollModalContent');
    let dataMap   = {};

    try {
        const raw = document.getElementById('enrollments-data');
        if (raw) dataMap = JSON.parse(raw.textContent);
    } catch(e) {}

    function esc(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;');
    }

    function val(v) {
        return (v && String(v).trim() !== '')
            ? `<span class="mfield-value">${esc(v)}</span>`
            : `<span class="mfield-value dim">Not provided</span>`;
    }

    function typePillClass(type) {
        if (type === 'new')      return 'er-type-new';
        if (type === 'transfer') return 'er-type-transfer';
        return 'er-type-old';
    }

    function statusPillClass(status) {
        if (status === 'approved') return 'msp-approved';
        if (status === 'rejected') return 'msp-rejected';
        return 'msp-pending';
    }

    function buildModal(d) {
        const typeLabel   = d.student_type ? (d.student_type.charAt(0).toUpperCase() + d.student_type.slice(1)) : '—';
        const statusLabel = d.status ? (d.status.charAt(0).toUpperCase() + d.status.slice(1)) : '—';
        const idPadded    = String(d.id).padStart(5,'0');

        /* ── Reviewed notice ── */
        let reviewedHTML = '';
        if (d.status !== 'pending') {
            const isApproved = d.status === 'approved';
            const iconColor  = isApproved ? '#0d9488' : '#c0392b';
            const iconBg     = isApproved ? '#e6faf8' : '#fdecea';
            const iconBorder = isApproved ? '#99e6e1' : '#f5b3ac';
            const iconPath   = isApproved
                ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                : 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z';

            let sectionBadge = '';
            if (isApproved && d.section_name) {
                sectionBadge = `
                <span class="modal-assigned-badge">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Assigned: ${esc(d.section_name)}
                </span>`;
            }

            let remarkBlock = '';
            if (d.remarks) {
                remarkBlock = `
                <div class="modal-rejection-reason">
                    <strong>Reason for Rejection</strong>
                    ${esc(d.remarks)}
                </div>`;
            } else if (!isApproved) {
                remarkBlock = `<span style="font-size:13px;color:#c5bfda;font-style:italic;font-family:'Lora',serif;">No reason was provided.</span>`;
            }

            const byText = d.reviewer_name ? `by <strong>${esc(d.reviewer_name)}</strong> ` : '';
            const atText = d.reviewed_at   ? `&mdash; ${esc(d.reviewed_at)}` : '';

            reviewedHTML = `
            <div class="modal-reviewed-notice">
                <div class="modal-reviewed-icon" style="background:${iconBg};border:1.5px solid ${iconBorder};">
                    <svg fill="none" stroke="${iconColor}" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="${iconPath}"/>
                    </svg>
                </div>
                <div class="modal-reviewed-text">
                    This enrollment has been
                    <strong style="color:${iconColor}">${statusLabel}</strong>
                    ${byText}${atText}
                </div>
                ${sectionBadge}
                ${remarkBlock}
            </div>`;
        }

        /* ── Last school ── */
        let lastSchoolHTML = '';
        if (d.last_school) {
            lastSchoolHTML = `
            <hr class="mfield-divider">
            <div class="mfgrid">
                <div>
                    <span class="mfield-label">Last School Attended</span>
                    ${val(d.last_school)}
                </div>
                <div>
                    <span class="mfield-label">Last Grade Completed</span>
                    ${val(d.last_grade_completed)}
                </div>
            </div>`;
        }

        return `
        <div class="modal-stripe"></div>

        <div class="modal-header">
            <div class="modal-header-meta">
                <div class="modal-eyebrow">Enrollment Request #${idPadded}</div>
                <div class="modal-name" id="modalStudentName"><em>${esc(d.full_name)}</em></div>
                <div class="modal-tags">
                    <span class="er-pill" style="background:#e7faf7;color:#0d9488;border-color:#9de8df;">Grade ${esc(d.grade_level)}</span>
                    <span class="er-pill ${typePillClass(d.student_type)}">${typeLabel} Student</span>
                    <span class="er-pill er-type-old">${esc(d.school_year)}</span>
                    <span class="modal-status-pill ${statusPillClass(d.status)}">${statusLabel}</span>
                </div>
            </div>
            <button class="modal-close" onclick="closeEnrollPreview()" aria-label="Close preview">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="modal-body">

            ${reviewedHTML}

            <div class="modal-section">
                <div class="modal-section-head">
                    <div class="modal-section-icon" style="background:#f0eaff;">
                        <svg fill="none" stroke="#6c3fc5" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <span class="modal-section-title">Enrollment Details</span>
                </div>
                <div class="modal-section-body">
                    <div class="mfgrid-3">
                        <div>
                            <span class="mfield-label">Grade Level</span>
                            ${val('Grade ' + d.grade_level)}
                        </div>
                        <div>
                            <span class="mfield-label">Student Type</span>
                            ${val(typeLabel)}
                        </div>
                        <div>
                            <span class="mfield-label">School Year</span>
                            ${val(d.school_year)}
                        </div>
                    </div>
                    ${lastSchoolHTML}
                </div>
            </div>

            <div class="modal-section">
                <div class="modal-section-head">
                    <div class="modal-section-icon" style="background:#eff4ff;">
                        <svg fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <span class="modal-section-title">Student Profile</span>
                </div>
                <div class="modal-section-body">
                    <div class="mfgrid" style="margin-bottom:16px;">
                        <div>
                            <span class="mfield-label">Full Name</span>
                            ${val(d.full_name)}
                        </div>
                        <div>
                            <span class="mfield-label">Address</span>
                            ${val(d.address)}
                        </div>
                    </div>
                    <hr class="mfield-divider" style="margin:0 0 16px;">
                    <div class="mfgrid-3">
                        <div>
                            <span class="mfield-label">Age</span>
                            ${d.age ? val(d.age + ' years old') : val(null)}
                        </div>
                        <div>
                            <span class="mfield-label">Date of Birth</span>
                            ${val(d.birthdate)}
                        </div>
                        <div>
                            <span class="mfield-label">Gender</span>
                            ${val(d.gender)}
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-section">
                <div class="modal-section-head">
                    <div class="modal-section-icon" style="background:#e6faf8;">
                        <svg fill="none" stroke="#0d9488" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87a4 4 0 100-8 4 4 0 000 8z"/>
                        </svg>
                    </div>
                    <span class="modal-section-title">Family Information</span>
                </div>
                <div class="modal-section-body">
                    <div class="mfgrid" style="margin-bottom:16px;">
                        <div>
                            <span class="mfield-label">Mother's Name</span>
                            ${val(d.mother_name)}
                        </div>
                        <div>
                            <span class="mfield-label">Father's Name</span>
                            ${val(d.father_name)}
                        </div>
                    </div>
                    <hr class="mfield-divider" style="margin:0 0 16px;">
                    <div class="mfgrid">
                        <div>
                            <span class="mfield-label">Guardian's Name</span>
                            ${val(d.guardian_name)}
                        </div>
                        <div>
                            <span class="mfield-label">Guardian's Contact</span>
                            ${val(d.guardian_contact)}
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="modal-footer">
            <button type="button" class="modal-btn-close-soft" onclick="closeEnrollPreview()">Close</button>
            <a href="${esc(d.review_url)}" class="modal-btn-review">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Open Full Review
            </a>
        </div>`;
    }

    window.openEnrollPreview = function(id) {
        const d = dataMap[id];
        if (!d) return;

        content.innerHTML = `
            <div class="modal-skeleton">
                <div class="sk-spin"></div>
                <span class="sk-label">Loading details…</span>
            </div>`;

        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';

        setTimeout(() => { content.innerHTML = buildModal(d); }, 180);
    };

    window.closeEnrollPreview = function() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) {
            closeEnrollPreview();
        }
    });
})();
</script>

@endsection