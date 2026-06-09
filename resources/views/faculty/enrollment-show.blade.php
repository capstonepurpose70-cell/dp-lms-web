@extends('layouts.faculty')
@section('title', 'Review Enrollment')

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
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;1,9..144,300;1,9..144,400&display=swap" rel="stylesheet">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --ink:         #0c0a14;
    --ink-2:       #2a2638;
    --ink-3:       #5c5672;
    --ink-4:       #9893aa;
    --ink-5:       #cbc7d9;
    --ink-6:       #eceaf3;
    --page-bg:     #f6f5f9;
    --white:       #ffffff;

    --vi:          #4f32c5;
    --vi-2:        #6548d4;
    --vi-3:        #8a6ee8;
    --vi-4:        #bfaefc;
    --vi-5:        #ede8ff;
    --vi-6:        #f7f5ff;

    --em:          #0d9e8a;
    --em-2:        #12b89f;
    --em-bg:       #e7faf7;
    --em-border:   #9de8df;

    --ok:          #0b8c5c;
    --ok-2:        #0ea86e;
    --ok-bg:       #e8f9f2;
    --ok-border:   #8adcb8;

    --wa:          #b36200;
    --wa-bg:       #fef5e7;
    --wa-border:   #f5cc7a;

    --er:          #b82c1e;
    --er-2:        #d03a2a;
    --er-bg:       #fdecea;
    --er-border:   #f4a89f;

    --bl:          #1d57d4;
    --bl-bg:       #eef3fd;
    --bl-border:   #b3cafc;

    --r-xs:  5px;
    --r-sm:  9px;
    --r-md:  14px;
    --r-lg:  20px;
    --r-xl:  28px;
}

.page {
    font-family: 'Sora', sans-serif;
    color: var(--ink);
    max-width: none;
    width: 100%;
    margin: 0;
    padding: 1.75rem 2rem 4rem;
    animation: fadein 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
}
@keyframes fadein {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}

.back-link {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 600;
    color: var(--ink-3); text-decoration: none;
    padding: 6px 12px 6px 8px;
    border: 1px solid var(--ink-6);
    border-radius: 99px;
    background: var(--white);
    transition: color 0.15s, border-color 0.15s, background 0.15s;
    margin-bottom: 2rem;
}
.back-link:hover { color: var(--vi); border-color: var(--vi-4); background: var(--vi-6); }
.back-link svg { width: 14px; height: 14px; }

.flash {
    display: flex; align-items: center; gap: 10px;
    padding: 13px 18px;
    background: var(--ok-bg);
    border: 1px solid var(--ok-border);
    border-radius: var(--r-md);
    font-size: 13px; font-weight: 500; color: var(--ok);
    margin-bottom: 1.75rem;
}

/* Header */
.header-block {
    background: var(--white);
    border: 1px solid var(--ink-6);
    border-radius: var(--r-xl);
    overflow: hidden;
    margin-bottom: 1.25rem;
}
.header-accent {
    height: 4px;
    background: linear-gradient(90deg, var(--vi) 0%, var(--em) 55%, #38bdf8 100%);
}
.header-inner {
    padding: 2rem 2.5rem;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.header-left { flex: 1; min-width: 0; }

/* Request ID sits quiet, not as an "eyebrow" proclamation */
.request-id {
    font-size: 11px; font-weight: 600; letter-spacing: 0.06em;
    color: var(--ink-4); margin-bottom: 0.5rem;
}
.student-name {
    font-family: 'Fraunces', serif;
    font-size: 2.4rem; font-weight: 300;
    color: var(--ink); line-height: 1.1;
    letter-spacing: -0.03em;
    margin-bottom: 1rem;
}
.student-name em { font-style: italic; color: var(--vi-2); }
.tag-row { display: flex; flex-wrap: wrap; gap: 8px; }

/* Badges */
.pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase;
    padding: 4px 12px; border-radius: 99px; border: 1px solid transparent;
    flex-shrink: 0;
}
.pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }

.pill-approved { background: var(--ok-bg);  color: var(--ok);  border-color: var(--ok-border); }
.pill-approved::before { background: var(--ok-2); }
.pill-pending  { background: var(--wa-bg);  color: var(--wa);  border-color: var(--wa-border); }
.pill-pending::before  { background: #f0a500; }
.pill-rejected { background: var(--er-bg);  color: var(--er);  border-color: var(--er-border); }
.pill-rejected::before { background: var(--er-2); }

.tag-new      { background: var(--bl-bg);  color: var(--bl);  border-color: var(--bl-border); }
.tag-new::before      { background: var(--bl); }
.tag-transfer { background: var(--vi-5);   color: var(--vi);  border-color: var(--vi-4); }
.tag-transfer::before { background: var(--vi-3); }
.tag-old      { background: var(--ink-6);  color: var(--ink-3); border-color: var(--ink-5); }
.tag-old::before      { background: var(--ink-4); }
.tag-grade    { background: var(--em-bg);  color: var(--em);  border-color: var(--em-border); }
.tag-grade::before { background: var(--em-2); }
.tag-sy       { background: var(--ink-6);  color: var(--ink-2); border-color: var(--ink-5); font-weight: 600; }
.tag-sy::before { background: var(--ink-4); }

/* Cards */
.card {
    background: var(--white);
    border: 1px solid var(--ink-6);
    border-radius: var(--r-lg);
    overflow: hidden;
    margin-bottom: 1rem;
}
.card-head {
    display: flex; align-items: center; gap: 12px;
    padding: 16px 24px;
    border-bottom: 1px solid var(--ink-6);
    background: var(--page-bg);
}
.card-icon {
    width: 34px; height: 34px; border-radius: var(--r-sm);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.card-icon svg { width: 16px; height: 16px; }
.card-title {
    font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;
    color: var(--ink-3);
}
.card-body { padding: 24px; }

/* Field grid */
.fgrid   { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 20px 28px; }
.fgrid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px 28px; }
@media (max-width: 760px) {
    .fgrid   { grid-template-columns: 1fr 1fr; }
    .fgrid-3 { grid-template-columns: 1fr 1fr; }
}

.field-label {
    display: block;
    font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;
    color: var(--ink-4); margin-bottom: 6px;
}
.field-value {
    font-size: 14.5px; font-weight: 600; color: var(--ink);
    line-height: 1.4;
}
.field-value.dim { color: var(--ink-4); font-weight: 400; font-style: italic; }

.field-divider {
    border: none; border-top: 1px dashed var(--ink-6); margin: 22px 0;
}

/* Decision area */
.decision-label {
    font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase;
    color: var(--ink-4); margin-bottom: 12px; padding-left: 2px;
}
/* Review layout — details left, sticky decision panel right */
.review-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 1.25rem;
    align-items: start;
}
.review-main { min-width: 0; }
.review-side { position: sticky; top: 1.25rem; }
@media (max-width: 1024px) {
    .review-grid { grid-template-columns: 1fr; }
    .review-side { position: static; }
}

.action-zone {
    display: grid; grid-template-columns: 1fr; gap: 1rem; margin-bottom: 1rem;
}
@media (max-width: 660px) { .action-zone { grid-template-columns: 1fr; } }

.action-card {
    background: var(--white);
    border-radius: var(--r-lg);
    overflow: hidden;
    border: 1.5px solid var(--ink-6);
    transition: border-color 0.2s, box-shadow 0.2s;
}
.action-card.approve:hover { border-color: var(--ok-border); box-shadow: 0 0 0 4px rgba(11,140,92,0.08); }
.action-card.reject:hover  { border-color: var(--er-border); box-shadow: 0 0 0 4px rgba(184,44,30,0.07); }

.action-head {
    padding: 16px 22px;
    border-bottom: 1px solid var(--ink-6);
    display: flex; align-items: center; gap: 10px;
}
.action-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.approve .action-dot { background: var(--ok-2); }
.reject  .action-dot { background: var(--er-2); }

.action-label { font-size: 13px; font-weight: 700; }
.approve .action-label { color: var(--ok); }
.reject  .action-label { color: var(--er); }

/* Small note under action heading — plain, not a "description" blurb */
.action-note { font-size: 11px; color: var(--ink-4); margin-top: 2px; }

.action-body { padding: 20px 22px; }

/* Form */
.f-label {
    display: block;
    font-size: 10.5px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;
    color: var(--ink-3); margin-bottom: 7px;
}
.f-label .opt { font-weight: 400; color: var(--ink-5); text-transform: none; letter-spacing: 0; }
.f-required { color: var(--er-2); margin-left: 2px; }

.f-select, .f-textarea {
    width: 100%;
    background: var(--page-bg);
    border: 1px solid var(--ink-5);
    border-radius: var(--r-sm);
    padding: 11px 15px;
    font-size: 14px; font-family: 'Sora', sans-serif;
    color: var(--ink);
    outline: none;
    transition: border-color 0.18s, box-shadow 0.18s;
    appearance: none;
}
.f-select {
    background-image: url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%239893aa' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 13px center;
    padding-right: 34px;
    cursor: pointer;
}
.f-select:focus, .f-textarea:focus {
    border-color: var(--vi-3);
    box-shadow: 0 0 0 3px rgba(79,50,197,0.1);
    background: var(--white);
}
.f-select.reject-focus:focus {
    border-color: var(--er-2);
    box-shadow: 0 0 0 3px rgba(184,44,30,0.09);
}
.f-textarea { resize: vertical; min-height: 96px; line-height: 1.6; }

/* Hint under section select — factual, not promotional */
.select-hint {
    font-size: 11px; color: var(--ink-4); margin-top: 8px;
}

/* Buttons */
.btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 12px 18px;
    border: none; border-radius: var(--r-sm);
    font-size: 13.5px; font-weight: 700; font-family: 'Sora', sans-serif;
    cursor: pointer;
    margin-top: 14px;
    transition: transform 0.14s, box-shadow 0.14s, background 0.14s;
}
.btn svg { width: 15px; height: 15px; flex-shrink: 0; }
.btn:active { transform: scale(0.98); }

.btn-approve {
    background: var(--ok); color: #fff;
    box-shadow: 0 2px 10px rgba(11,140,92,0.28);
}
.btn-approve:hover {
    background: var(--ok-2);
    box-shadow: 0 4px 16px rgba(11,140,92,0.36);
    transform: translateY(-1px);
}
.btn-reject {
    background: var(--er); color: #fff;
    box-shadow: 0 2px 10px rgba(184,44,30,0.22);
}
.btn-reject:hover {
    background: var(--er-2);
    box-shadow: 0 4px 16px rgba(184,44,30,0.28);
    transform: translateY(-1px);
}

/* Reviewed state */
.reviewed-card {
    background: var(--white);
    border: 1px solid var(--ink-6);
    border-radius: var(--r-lg);
    padding: 2rem 1.75rem;
    display: flex; flex-direction: column; align-items: center; gap: 12px;
    text-align: center;
    margin-bottom: 1rem;
}
.reviewed-icon {
    width: 52px; height: 52px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
}
.reviewed-icon svg { width: 22px; height: 22px; }

.reviewed-text {
    font-size: 13.5px; color: var(--ink-2); line-height: 1.7;
}
.reviewed-text strong { color: var(--ink); font-weight: 700; }

.rejection-reason {
    font-size: 12.5px; font-weight: 500; color: var(--er);
    background: var(--er-bg);
    border: 1px solid var(--er-border);
    border-radius: var(--r-sm);
    padding: 9px 16px;
    max-width: 480px;
    line-height: 1.5;
    text-align: left;
}
.rejection-reason-label {
    display: block;
    font-size: 10px; font-weight: 700; letter-spacing: 0.08em;
    text-transform: uppercase; color: var(--er);
    margin-bottom: 4px;
}

.no-reason {
    font-size: 11.5px; color: var(--ink-5); font-style: italic;
}

.section-badge {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 600;
    background: var(--vi-5); color: var(--vi-2);
    border: 1px solid var(--vi-4);
    border-radius: var(--r-sm); padding: 5px 13px;
}
.section-badge svg { width: 12px; height: 12px; }

.date-note {
    font-size: 11px; color: var(--ink-4);
    border: 1px solid var(--ink-6);
    border-radius: 99px; padding: 4px 12px;
    background: var(--page-bg);
}
</style>

<div class="page">

    <a href="{{ route('faculty.enrollments') }}" class="back-link">
        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6"/>
        </svg>
        Back to Enrollments
    </a>

    @if(session('success'))
    <div class="flash">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="header-block">
        <div class="header-accent"></div>
        <div class="header-inner">
            <div class="header-left">
                <div class="request-id">#{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div class="student-name"><em>{{ $request->full_name }}</em></div>
                <div class="tag-row">
                    @php $type = $request->student_type; @endphp
                    <span class="pill tag-grade">Grade {{ $request->grade_level }}</span>
                    <span class="pill {{ $type === 'new' ? 'tag-new' : ($type === 'transfer' ? 'tag-transfer' : 'tag-old') }}">
                        {{ ucfirst($type) }}
                    </span>
                    <span class="pill tag-sy">SY {{ $request->school_year }}</span>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;flex-shrink:0;">
                <span class="pill {{ $request->status === 'approved' ? 'pill-approved' : ($request->status === 'pending' ? 'pill-pending' : 'pill-rejected') }}">
                    {{ ucfirst($request->status) }}
                </span>
                @if($request->reviewed_at)
                <span class="date-note">{{ $request->reviewed_at->format('M d, Y') }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="review-grid">
    <div class="review-main">

    {{-- Enrollment Details --}}
    <div class="card">
        <div class="card-head">
            <div class="card-icon" style="background:var(--vi-5);">
                <svg fill="none" stroke="var(--vi)" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <span class="card-title">Enrollment Details</span>
        </div>
        <div class="card-body">
            <div class="fgrid-3">
                <div>
                    <span class="field-label">Grade Level</span>
                    <span class="field-value">Grade {{ $request->grade_level }}</span>
                </div>
                <div>
                    <span class="field-label">Student Type</span>
                    <span class="field-value">{{ ucfirst($request->student_type) }}</span>
                </div>
                <div>
                    <span class="field-label">School Year</span>
                    <span class="field-value">{{ $request->school_year }}</span>
                </div>
            </div>

            @if($request->last_school)
            <hr class="field-divider">
            <div class="fgrid">
                <div>
                    <span class="field-label">Last School Attended</span>
                    <span class="field-value">{{ $request->last_school }}</span>
                </div>
                <div>
                    <span class="field-label">Last Grade Completed</span>
                    <span class="field-value {{ !$request->last_grade_completed ? 'dim' : '' }}">
                        {{ $request->last_grade_completed ?? '—' }}
                    </span>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Student Profile --}}
    <div class="card">
        <div class="card-head">
            <div class="card-icon" style="background:var(--bl-bg);">
                <svg fill="none" stroke="var(--bl)" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <span class="card-title">Student</span>
        </div>
        <div class="card-body">
            <div class="fgrid" style="margin-bottom:20px;">
                <div>
                    <span class="field-label">Full Name</span>
                    <span class="field-value">{{ $request->full_name }}</span>
                </div>
                <div>
                    <span class="field-label">Address</span>
                    <span class="field-value {{ !$request->address ? 'dim' : '' }}">
                        {{ $request->address ?? '—' }}
                    </span>
                </div>
            </div>
            <hr class="field-divider" style="margin:0 0 20px;">
            <div class="fgrid-3">
                <div>
                    <span class="field-label">Age</span>
                    <span class="field-value {{ !$request->age ? 'dim' : '' }}">
                        {{ $request->age ? $request->age . ' yrs' : '—' }}
                    </span>
                </div>
                <div>
                    <span class="field-label">Date of Birth</span>
                    <span class="field-value {{ !$request->birthdate ? 'dim' : '' }}">
                        {{ $request->birthdate?->format('M d, Y') ?? '—' }}
                    </span>
                </div>
                <div>
                    <span class="field-label">Gender</span>
                    <span class="field-value {{ !$request->gender ? 'dim' : '' }}">
                        {{ $request->gender ?? '—' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Family --}}
    <div class="card">
        <div class="card-head">
            <div class="card-icon" style="background:var(--em-bg);">
                <svg fill="none" stroke="var(--em)" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87a4 4 0 100-8 4 4 0 000 8z"/>
                </svg>
            </div>
            <span class="card-title">Family</span>
        </div>
        <div class="card-body">
            <div class="fgrid" style="margin-bottom:20px;">
                <div>
                    <span class="field-label">Mother</span>
                    <span class="field-value {{ !$request->mother_name ? 'dim' : '' }}">
                        {{ $request->mother_name ?? '—' }}
                    </span>
                </div>
                <div>
                    <span class="field-label">Father</span>
                    <span class="field-value {{ !$request->father_name ? 'dim' : '' }}">
                        {{ $request->father_name ?? '—' }}
                    </span>
                </div>
            </div>
            <hr class="field-divider" style="margin:0 0 20px;">
            <div class="fgrid">
                <div>
                    <span class="field-label">Guardian</span>
                    <span class="field-value {{ !$request->guardian_name ? 'dim' : '' }}">
                        {{ $request->guardian_name ?? '—' }}
                    </span>
                </div>
                <div>
                    <span class="field-label">Guardian Contact</span>
                    <span class="field-value {{ !$request->guardian_contact ? 'dim' : '' }}">
                        {{ $request->guardian_contact ?? '—' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    </div>{{-- /review-main --}}

    <div class="review-side">
    {{-- Decision --}}
    @if($request->status === 'pending')

    <div class="decision-label">Decision</div>

    <div class="action-zone">

        <div class="action-card approve">
            <div class="action-head">
                <div class="action-dot"></div>
                <div>
                    <div class="action-label">Approve</div>
                    <div class="action-note">Assign the student to a section.</div>
                </div>
            </div>
            <div class="action-body">
                <form method="POST" action="{{ route('faculty.enrollments.approve', $request) }}">
                    @csrf
                    <label class="f-label" for="section_id">
                        Section <span class="f-required">*</span>
                    </label>
                    <select name="section_id" id="section_id" required class="f-select">
                        <option value="">— Choose section —</option>
                        @foreach($sections->groupBy('grade_level') as $grade => $gradeSections)
                            <optgroup label="Grade {{ $grade }}">
                                @foreach($gradeSections as $section)
                                    <option value="{{ $section->id }}"
                                        {{ $section->grade_level == $request->grade_level ? 'selected' : '' }}>
                                        {{ $section->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <p class="select-hint">Grade {{ $request->grade_level }} sections are pre-selected.</p>
                    <button type="submit" class="btn btn-approve">
                        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Approve &amp; Assign
                    </button>
                </form>
            </div>
        </div>

        <div class="action-card reject">
            <div class="action-head">
                <div class="action-dot"></div>
                <div>
                    <div class="action-label">Reject</div>
                    <div class="action-note">The applicant will be notified.</div>
                </div>
            </div>
            <div class="action-body">
                <form method="POST" action="{{ route('faculty.enrollments.reject', $request) }}">
                    @csrf
                    <label class="f-label" for="remarks">
                        Reason <span class="opt">(optional)</span>
                    </label>
                    <textarea
                        name="remarks"
                        id="remarks"
                        rows="4"
                        class="f-textarea reject-focus"
                        placeholder="Why is this enrollment being rejected?"></textarea>
                    <button type="submit" class="btn btn-reject">
                        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Reject
                    </button>
                </form>
            </div>
        </div>

    </div>

    @else

    <div class="reviewed-card">

        @if($request->status === 'approved')
        <div class="reviewed-icon" style="background:var(--ok-bg); border: 1.5px solid var(--ok-border);">
            <svg fill="none" stroke="var(--ok)" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        @else
        <div class="reviewed-icon" style="background:var(--er-bg); border: 1.5px solid var(--er-border);">
            <svg fill="none" stroke="var(--er)" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        @endif

        <div class="reviewed-text">
            <strong style="color:{{ $request->status === 'approved' ? 'var(--ok)' : 'var(--er)' }}">
                {{ ucfirst($request->status) }}
            </strong>
            @if($request->reviewer)
                by <strong>{{ $request->reviewer->name }}</strong>
            @endif
            @if($request->reviewed_at)
                &mdash; {{ $request->reviewed_at->format('F d, Y \a\t g:i A') }}
            @endif
        </div>

        @if($request->status === 'approved' && $request->section)
        <span class="section-badge">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            {{ $request->section->name }}
        </span>
        @endif

        @if($request->remarks)
        <div class="rejection-reason">
            <span class="rejection-reason-label">Reason</span>
            {{ $request->remarks }}
        </div>
        @elseif($request->status === 'rejected')
        <span class="no-reason">No reason provided.</span>
        @endif

    </div>

    @endif

    </div>{{-- /review-side --}}
    </div>{{-- /review-grid --}}

</div>
@endsection