    @extends('layouts.faculty')
    @section('title', 'Assign Teacher — ' . $teacher->name)


    @section('content')
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
    :root {
        --vi:        #4f32c5;
        --vi-2:      #6548d4;
        --vi-3:      #8a6ee8;
        --vi-4:      #bfaefc;
        --vi-5:      #ede8ff;
        --vi-6:      #f7f5ff;
        --ink:       #0c0a14;
        --ink-2:     #2a2638;
        --ink-3:     #5c5672;
        --ink-4:     #9893aa;
        --ink-5:     #cbc7d9;
        --ink-6:     #eceaf3;
        --page-bg:   #f6f5f9;
        --white:     #ffffff;
        --ok:        #0b8c5c;
        --ok-2:      #0ea86e;
        --ok-bg:     #e8f9f2;
        --ok-border: #8adcb8;
        --er:        #b82c1e;
        --er-bg:     #fdecea;
        --er-border: #f4a89f;
        --r-sm: 9px; --r-md: 14px; --r-lg: 20px; --r-xl: 28px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    .page {
        font-family: 'DM Sans', sans-serif;
        color: var(--ink);
        max-width: 860px;
        margin: 0 auto;
        padding: 2rem 1.5rem 4rem;
        animation: fadein 0.4s cubic-bezier(0.16,1,0.3,1) both;
    }
    @keyframes fadein {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Back link ── */
    .back-link {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 600; letter-spacing: .03em;
        color: var(--ink-3); text-decoration: none;
        padding: 6px 12px 6px 8px;
        border: 1px solid var(--ink-6);
        border-radius: 99px;
        background: var(--white);
        transition: color .15s, border-color .15s, background .15s;
        margin-bottom: 1.75rem;
        width: fit-content;
        display: flex;
    }
    .back-link:hover { color: var(--vi); border-color: var(--vi-4); background: var(--vi-6); }
    .back-link svg { width: 14px; height: 14px; }

    /* ── Teacher hero card ── */
    .hero-card {
        background: var(--white);
        border: 1px solid var(--ink-6);
        border-radius: var(--r-xl);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .hero-stripe {
        height: 4px;
        background: linear-gradient(90deg, var(--vi) 0%, #38bdf8 100%);
    }
    .hero-body {
        padding: 1.75rem 2rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        flex-wrap: wrap;
    }
    .hero-avatar {
        width: 52px; height: 52px;
        border-radius: 14px;
        background: var(--vi-5);
        border: 1px solid var(--vi-4);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .hero-avatar svg { width: 24px; height: 24px; color: var(--vi); }
    .hero-name {
        font-family: 'Sora', sans-serif;
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--ink);
        letter-spacing: -.02em;
    }
    .hero-meta {
        font-size: 13px;
        color: var(--ink-4);
        margin-top: 3px;
    }
    .hero-badges {
        margin-left: auto;
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .badge {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 11px; font-weight: 700; letter-spacing: .04em;
        padding: 5px 12px; border-radius: 999px; border: 1px solid transparent;
    }
    .badge::before { content:''; width:5px; height:5px; border-radius:50%; flex-shrink:0; }
    .badge-ok  { background:var(--ok-bg);  color:var(--ok);  border-color:var(--ok-border); }
    .badge-ok::before  { background:var(--ok); }
    .badge-wa  { background:#fef5e7; color:#b36200; border-color:#f5cc7a; }
    .badge-wa::before  { background:#f0a500; }
    .badge-vi  { background:var(--vi-5); color:var(--vi); border-color:var(--vi-4); }
    .badge-vi::before  { background:var(--vi); }

    /* ── Form card ── */
    .form-card {
        background: var(--white);
        border: 1px solid var(--ink-6);
        border-radius: var(--r-lg);
        overflow: hidden;
        margin-bottom: 1rem;
    }
    .form-card-head {
        display: flex; align-items: center; gap: 12px;
        padding: 16px 24px;
        border-bottom: 1px solid var(--ink-6);
        background: var(--page-bg);
    }
    .card-icon {
        width: 36px; height: 36px; border-radius: var(--r-sm);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .card-icon svg { width: 17px; height: 17px; }
    .card-title {
        font-family: 'Sora', sans-serif;
        font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
        color: var(--ink-3);
    }
    .form-card-body { padding: 24px; }

    /* ── Section select ── */
    .f-label {
        display: block;
        font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
        color: var(--ink-3); margin-bottom: 8px;
    }
    .f-select {
        width: 100%;
        background: var(--page-bg);
        border: 1px solid var(--ink-5);
        border-radius: var(--r-sm);
        padding: 11px 36px 11px 14px;
        font-size: 14px; font-family: 'DM Sans', sans-serif;
        color: var(--ink); outline: none; appearance: none; cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%239893aa' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        transition: border-color .18s, box-shadow .18s;
    }
    .f-select:focus {
        border-color: var(--vi-3);
        box-shadow: 0 0 0 3px rgba(79,50,197,.1);
        background-color: var(--white);
    }
    .f-hint { font-size: 12px; color: var(--ink-4); margin-top: 8px; line-height: 1.5; }

    /* ── Subject grid ── */
    .grade-block { margin-bottom: 1.75rem; }
    .grade-block:last-child { margin-bottom: 0; }

    .grade-label {
        font-family: 'Sora', sans-serif;
        font-size: 10px; font-weight: 800; letter-spacing: .2em; text-transform: uppercase;
        color: var(--vi-3);
        display: flex; align-items: center; gap: .6rem;
        margin-bottom: .9rem;
    }
    .grade-label::after { content:''; flex:1; height:1px; background:var(--ink-6); }

    .subj-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        gap: .5rem;
    }
    .subj-item {
        display: flex; align-items: center; gap: .6rem;
        padding: .6rem .9rem;
        background: var(--page-bg);
        border: 1px solid var(--ink-6);
        border-radius: 9px;
        cursor: pointer;
        transition: border-color .18s, background .18s, box-shadow .18s;
        user-select: none;
    }
    .subj-item:hover { background: var(--vi-6); border-color: var(--vi-4); }
    .subj-item:has(input:checked) {
        background: var(--vi-5);
        border-color: var(--vi-3);
        box-shadow: 0 0 0 2px rgba(79,50,197,.12);
    }
    .subj-item input[type="checkbox"] {
        width: 14px; height: 14px;
        accent-color: var(--vi);
        cursor: pointer; flex-shrink: 0;
    }
    .subj-item span { font-size: 13px; color: var(--ink-3); line-height: 1.3; }
    .subj-item:has(input:checked) span { color: var(--ink); font-weight: 600; }

    /* ── Counter pill ── */
    .counter-pill {
        font-family: 'Sora', sans-serif;
        font-size: 11px; font-weight: 700; letter-spacing: .04em;
        background: var(--vi-5); border: 1px solid var(--vi-4); color: var(--vi);
        padding: 4px 12px; border-radius: 999px;
    }

    /* ── Actions ── */
    .form-actions {
        display: flex; gap: 1rem; flex-wrap: wrap;
        padding-top: 1.5rem;
        border-top: 1px solid var(--ink-6);
        margin-top: 1.5rem;
    }
    .btn-save {
        flex: 2; min-width: 200px;
        display: flex; align-items: center; justify-content: center; gap: 7px;
        padding: 12px 24px;
        background: var(--vi);
        color: #fff;
        border: none; border-radius: var(--r-sm);
        font-size: 14px; font-weight: 700; font-family: 'Sora', sans-serif;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(79,50,197,.35);
        transition: background .2s, transform .15s, box-shadow .2s;
    }
    .btn-save:hover { background: var(--vi-2); transform: translateY(-1px); box-shadow: 0 6px 28px rgba(79,50,197,.45); }
    .btn-save:active { transform: translateY(0); }
    .btn-save svg { width: 16px; height: 16px; }

    .btn-cancel {
        flex: 1; min-width: 120px;
        display: flex; align-items: center; justify-content: center;
        padding: 12px 24px;
        background: transparent;
        color: var(--ink-3);
        border: 1px solid var(--ink-5);
        border-radius: var(--r-sm);
        font-size: 14px; font-weight: 600; font-family: 'DM Sans', sans-serif;
        text-decoration: none;
        transition: background .18s, color .18s;
    }
    .btn-cancel:hover { background: var(--page-bg); color: var(--ink); }

    /* ── Alert ── */
    .alert-err {
        background: var(--er-bg); border: 1px solid var(--er-border);
        border-radius: var(--r-md); padding: 14px 18px;
        display: flex; align-items: flex-start; gap: 10px;
        margin-bottom: 1.5rem; font-size: 13px; color: var(--er);
    }
    .alert-err svg { width: 17px; height: 17px; flex-shrink: 0; margin-top: 1px; }
    .alert-err ul { list-style: none; display: flex; flex-direction: column; gap: 4px; }
    .alert-err li::before { content: '✕ '; font-weight: 700; }

    @media (max-width: 540px) {
        .form-actions { flex-direction: column; }
        .btn-save, .btn-cancel { flex: unset; }
    }
    </style>

    <div class="page">

        {{-- Back --}}
        <a href="{{ route('faculty.teachers.index') }}" class="back-link">
            <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6"/>
            </svg>
            Back to Teachers
        </a>

        {{-- Validation errors --}}
        @if($errors->any())
        <div class="alert-err">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/>
            </svg>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Teacher info hero --}}
        <div class="hero-card">
            <div class="hero-stripe"></div>
            <div class="hero-body">
                <div class="hero-avatar">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="hero-name">{{ $teacher->name }}</div>
                    <div class="hero-meta">{{ $teacher->email }}
                        @if($teacher->employee_id)
                            &nbsp;·&nbsp; {{ $teacher->employee_id }}
                        @endif
                    </div>
                </div>
                <div class="hero-badges">
                    @if($teacher->section)
                        <span class="badge badge-ok">{{ $teacher->section->name }}</span>
                    @else
                        <span class="badge badge-wa">No section yet</span>
                    @endif
                    @php $sc = $teacher->teacherSubjects->count(); @endphp
                    @if($sc > 0)
                        <span class="badge badge-vi">{{ $sc }} subject{{ $sc > 1 ? 's' : '' }}</span>
                    @else
                        <span class="badge badge-wa">No subjects yet</span>
                    @endif
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('faculty.teachers.assign.save', $teacher) }}">
            @csrf

            {{-- Section Assignment --}}
            <div class="form-card">
                <div class="form-card-head">
                    <div class="card-icon" style="background:var(--vi-5);">
                        <svg fill="none" stroke="var(--vi)" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <span class="card-title">Section Assignment</span>
                </div>
                <div class="form-card-body">
                    <label class="f-label" for="section_id">Assign to Section</label>
                    <select name="section_id" id="section_id" class="f-select">
                        <option value="">— No section assigned —</option>
                        @foreach($sections->groupBy('grade_level') as $grade => $gradeSections)
                            <optgroup label="Grade {{ $grade }}">
                                @foreach($gradeSections as $section)
                                    <option value="{{ $section->id }}"
                                        {{ $teacher->section_id == $section->id ? 'selected' : '' }}>
                                        {{ $section->name }} — Grade {{ $section->grade_level }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <p class="f-hint">
                        The selected section will be this teacher's primary class assignment.
                    </p>
                </div>
            </div>

            {{-- Subject Assignment --}}
            <div class="form-card">
                <div class="form-card-head">
                    <div class="card-icon" style="background:#e8f9f2;">
                        <svg fill="none" stroke="#0b8c5c" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                        </svg>
                    </div>
                    <span class="card-title">Subject Assignment</span>
                    <span class="counter-pill" id="subjCounter" style="margin-left:auto;">
                        {{ count($assignedSubjectIds) }} selected
                    </span>
                </div>
                <div class="form-card-body">

                    @foreach($subjects->groupBy('grade_level') as $grade => $gradeSubjects)
                    <div class="grade-block">
                        <div class="grade-label">Grade {{ $grade }}</div>
                        <div class="subj-grid">
                            @foreach($gradeSubjects as $subject)
                            <label class="subj-item">
                                <input type="checkbox"
                                    name="subjects[]"
                                    value="{{ $subject->id }}"
                                    class="subj-chk"
                                    {{ in_array($subject->id, $assignedSubjectIds) ? 'checked' : '' }}>
                                <span>{{ $subject->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                    {{-- Actions --}}
                    <div class="form-actions">
                        <button type="submit" class="btn-save" id="saveBtn">
                            <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Save Assignment
                        </button>
                        <a href="{{ route('faculty.teachers.index') }}" class="btn-cancel">Cancel</a>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <script>
    // Subject counter
    function updateCount() {
        const n = document.querySelectorAll('.subj-chk:checked').length;
        document.getElementById('subjCounter').textContent =
            n + (n === 1 ? ' selected' : ' selected');
    }
    document.querySelectorAll('.subj-chk').forEach(c =>
        c.addEventListener('change', updateCount)
    );

    // Loading state on submit
    document.getElementById('saveBtn').closest('form').addEventListener('submit', function() {
        const btn = document.getElementById('saveBtn');
        btn.disabled = true;
        btn.style.opacity = '.75';
        btn.innerHTML = `
            <svg style="width:16px;height:16px;animation:spin .7s linear infinite" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
            </svg>
            Saving…
        `;
    });
    </script>
    <style>
    @keyframes spin { to { transform: rotate(360deg); } }
    </style>
    @endsection