@extends('layouts.admin')

@section('title', 'Enrollment Management')

@section('content')
@php
    $validGrades  = ['7', '8', '9', '10', '11', '12'];
    $sectionsByGr = $sections->groupBy(fn ($s) => (string) $s->grade_level);
    $typeLabel    = ['new' => 'New student', 'old' => 'Returning student', 'transfer' => 'Transferee'];
    $allCount     = $counts->sum();
    $tabUrl       = fn ($st) => route('admin.enrollment.index', array_filter(['status' => $st, 'search' => $search]));
@endphp
<style>
    .en-page { animation: en-fade .22s ease both; }
    @keyframes en-fade { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }

    .en-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:18px; flex-wrap:wrap; }
    .en-header-meta { display:flex; align-items:center; gap:14px; min-width:0; }
    .en-header-icon { width:46px; height:46px; border-radius:var(--r-lg); flex-shrink:0; display:grid; place-items:center; color:#fff;
        background:linear-gradient(135deg, var(--blue-500), var(--blue-700)); box-shadow:0 6px 16px -4px rgba(36,120,228,.5); }
    .en-header-icon svg { width:22px; height:22px; }
    .en-title { font-size:22px; font-weight:800; color:var(--slate-900); letter-spacing:-0.01em; }
    .en-sub   { font-size:13px; color:var(--slate-500); margin-top:2px; max-width:620px; line-height:1.5; }

    .en-flash { display:flex; align-items:center; gap:10px; padding:12px 14px; border-radius:12px; font-size:13.5px; font-weight:500; margin-bottom:16px; border:1px solid; }
    .en-flash svg { width:18px; height:18px; flex-shrink:0; }
    .en-flash-error { background:rgba(220,38,38,.08); color:#b91c1c; border-color:rgba(220,38,38,.3); }
    [data-theme="dark"] .en-flash-error { color:#f87171; }

    /* Tabs */
    .en-tabs { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:14px; }
    .en-tab { display:inline-flex; align-items:center; gap:8px; padding:8px 14px; border-radius:10px; border:1px solid var(--border-default);
        background:var(--white); color:var(--slate-700); font-size:13px; font-weight:600; text-decoration:none; transition:border-color .15s, color .15s; }
    .en-tab:hover { border-color:var(--blue-400); color:var(--blue-600); }
    .en-tab.on { background:#1d4ed8; border-color:#1d4ed8; color:#fff; }
    .en-tab .n { min-width:22px; text-align:center; padding:1px 7px; border-radius:999px; font-size:11.5px; font-weight:800; background:var(--slate-100); color:var(--slate-700); }
    .en-tab.on .n { background:rgba(255,255,255,.22); color:#fff; }
    .en-tab.pend .n { background:rgba(245,158,11,.18); color:#b45309; }
    .en-tab.pend.on .n { background:rgba(255,255,255,.22); color:#fff; }

    /* Filter */
    .en-filterbar { display:flex; align-items:center; gap:10px; margin-bottom:14px; flex-wrap:wrap; }
    .en-search { position:relative; display:flex; align-items:center; flex:1; max-width:380px; min-width:220px; }
    .en-search svg { position:absolute; left:12px; width:15px; height:15px; color:var(--slate-400); pointer-events:none; }
    .en-search input { height:40px; width:100%; padding:0 14px 0 36px; border:1.5px solid var(--border-default); border-radius:10px;
        font-size:13.5px; color:var(--slate-900); background:var(--white); transition:border-color .15s, box-shadow .15s; }
    .en-search input:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
    .en-btn-apply { height:40px; padding:0 18px; border:none; border-radius:10px; cursor:pointer; background:#1d4ed8; color:#fff; font-size:13px; font-weight:700; }
    .en-btn-apply:hover { background:#1e40af; }
    .en-btn-reset { height:40px; padding:0 8px; display:inline-flex; align-items:center; color:var(--slate-500); font-size:13px; font-weight:600; text-decoration:none; }
    .en-btn-reset:hover { color:#2563eb; }

    /* Table */
    .en-card { background:var(--surface-card); border:1px solid var(--border-default); border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; }
    .en-scroll { overflow-x:auto; }
    .en-table { width:100%; border-collapse:collapse; min-width:900px; }
    .en-table th { text-align:left; padding:12px 16px; font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase;
        color:var(--slate-500); background:var(--slate-50); border-bottom:1px solid var(--border-default); white-space:nowrap; }
    .en-table td { padding:14px 16px; font-size:13.5px; color:var(--slate-700); border-bottom:1px solid var(--border-default); vertical-align:middle; }
    .en-table tbody tr.main:hover td { background:var(--slate-50); }
    .en-table tr.det td { background:var(--slate-50); padding:0 16px 16px; }

    .en-student { display:flex; align-items:center; gap:12px; }
    .en-avatar { width:38px; height:38px; border-radius:999px; flex-shrink:0; display:grid; place-items:center; font-weight:800; font-size:14px;
        color:#1d4ed8; background:rgba(37,99,235,.12); }
    .en-name  { font-weight:700; color:var(--slate-900); }
    .en-email { font-size:12.5px; color:var(--slate-500); }
    .en-lrn   { font-family:ui-monospace, Consolas, monospace; font-size:12px; color:var(--slate-500); }
    .en-warn  { font-size:11.5px; font-weight:700; color:#b45309; margin-top:3px; }
    [data-theme="dark"] .en-warn { color:#fbbf24; }
    .en-grade { font-weight:700; color:var(--slate-900); white-space:nowrap; }
    .en-tbd   { display:inline-block; font-size:11.5px; font-weight:700; padding:3px 8px; border-radius:7px; background:rgba(245,158,11,.14); color:#b45309; }
    [data-theme="dark"] .en-tbd { color:#fbbf24; }
    .en-muted { color:var(--slate-400); }
    .en-small { font-size:12px; color:var(--slate-500); }

    .en-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 11px; border-radius:999px; font-size:12px; font-weight:700; border:1px solid; white-space:nowrap; }
    .en-badge::before { content:''; width:6px; height:6px; border-radius:50%; background:currentColor; }
    .en-badge-pending  { background:rgba(245,158,11,.12); color:#b45309; border-color:rgba(245,158,11,.35); }
    .en-badge-approved { background:rgba(16,185,129,.12); color:#047857; border-color:rgba(16,185,129,.35); }
    .en-badge-rejected { background:rgba(239,68,68,.10);  color:#b91c1c; border-color:rgba(239,68,68,.35); }
    [data-theme="dark"] .en-badge-pending  { color:#fbbf24; }
    [data-theme="dark"] .en-badge-approved { color:#34d399; }
    [data-theme="dark"] .en-badge-rejected { color:#f87171; }

    .en-actions { display:flex; align-items:center; gap:8px; justify-content:flex-end; flex-wrap:wrap; }
    .en-action-select { height:36px; padding:0 10px; border:1.5px solid var(--border-default); border-radius:9px; font-size:12.5px;
        color:var(--slate-900); background:var(--white); cursor:pointer; max-width:230px; }
    .en-action-select:focus { outline:none; border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.18); }
    .en-btn { height:36px; padding:0 14px; border:none; border-radius:9px; cursor:pointer; font-size:12.5px; font-weight:700;
        display:inline-flex; align-items:center; gap:5px; transition:background .15s, transform .1s; white-space:nowrap; }
    .en-btn svg { width:13px; height:13px; }
    .en-btn:active { transform:translateY(1px); }
    .en-btn-approve { background:#16a34a; color:#fff; }
    .en-btn-approve:hover { background:#15803d; }
    .en-btn-reject { background:transparent; color:#dc2626; border:1px solid rgba(220,38,38,.35); }
    .en-btn-reject:hover { background:rgba(220,38,38,.08); }
    .en-btn-more { background:transparent; color:var(--slate-700); border:1px solid var(--border-default); }
    .en-btn-more:hover { border-color:#2563eb; color:#2563eb; }
    .en-nosec { font-size:12px; font-weight:600; color:#b45309; text-align:right; }
    .en-nosec a { color:#2563eb; }

    /* Details */
    .en-det { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:12px 20px; padding:14px 16px; border:1px solid var(--border-default);
        border-radius:12px; background:var(--white); }
    .en-det div span { display:block; font-size:11px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:var(--slate-500); margin-bottom:2px; }
    .en-det div b { font-weight:600; color:var(--slate-900); font-size:13px; word-break:break-word; }
    .en-det .wide { grid-column:span 2; }
    .en-det .full { grid-column:1 / -1; }

    .en-empty { text-align:center; padding:56px 20px; }
    .en-empty-icon { width:56px; height:56px; margin:0 auto 14px; border-radius:999px; display:grid; place-items:center; background:var(--slate-100); color:var(--slate-400); }
    .en-empty-icon svg { width:26px; height:26px; }
    .en-empty-title { font-weight:700; font-size:15px; color:var(--slate-700); }
    .en-empty-sub { font-size:13px; color:var(--slate-500); margin-top:4px; }

    .en-pagination { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; border-top:1px solid var(--border-default); background:var(--slate-50); gap:10px; flex-wrap:wrap; }
    .en-page-info { font-size:12.5px; color:var(--slate-500); }
    .en-page-nav { display:flex; gap:8px; }
    .en-page-btn { height:34px; padding:0 14px; display:inline-flex; align-items:center; gap:5px; border:1px solid var(--border-default); border-radius:9px;
        background:var(--white); color:var(--slate-700); font-size:12.5px; font-weight:600; text-decoration:none; }
    .en-page-btn svg { width:14px; height:14px; }
    .en-page-btn:hover { border-color:#2563eb; color:#2563eb; }
    .en-page-btn.disabled { opacity:.45; pointer-events:none; }

    @media (max-width: 900px) { .en-det { grid-template-columns:repeat(2, minmax(0,1fr)); } }
</style>

<div class="en-page">

    <div class="en-header">
        <div class="en-header-meta">
            <div class="en-header-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
            </div>
            <div>
                <div class="en-title">Enrollment Management</div>
                <div class="en-sub">Review enrollment forms and place each student in a section. Section advisers can also approve requests from their own portal.</div>
            </div>
        </div>
    </div>

    @if(session('error') || $errors->any())
        <div class="en-flash en-flash-error" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            {{ session('error') ?? $errors->first() }}
        </div>
    @endif

    {{-- Status tabs --}}
    <nav class="en-tabs" aria-label="Filter by status">
        <a href="{{ $tabUrl(null) }}" class="en-tab {{ !$status ? 'on' : '' }}">All <span class="n">{{ $allCount }}</span></a>
        <a href="{{ $tabUrl('pending') }}" class="en-tab pend {{ $status === 'pending' ? 'on' : '' }}">Pending <span class="n">{{ $counts['pending'] ?? 0 }}</span></a>
        <a href="{{ $tabUrl('approved') }}" class="en-tab {{ $status === 'approved' ? 'on' : '' }}">Approved <span class="n">{{ $counts['approved'] ?? 0 }}</span></a>
        <a href="{{ $tabUrl('rejected') }}" class="en-tab {{ $status === 'rejected' ? 'on' : '' }}">Rejected <span class="n">{{ $counts['rejected'] ?? 0 }}</span></a>
    </nav>

    <form method="GET" class="en-filterbar" role="search">
        @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <label class="en-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by student name or LRN…" aria-label="Search by student name or LRN">
        </label>
        <button type="submit" class="en-btn-apply">Search</button>
        @if($search !== '' || $status)
            <a href="{{ route('admin.enrollment.index') }}" class="en-btn-reset">Clear filters</a>
        @endif
    </form>

    <div class="en-card">
        <div class="en-scroll">
        <table class="en-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Grade</th>
                    <th>Type</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $enrollment)
                    @php
                        $st        = $enrollment->status;
                        $stu       = $enrollment->student;
                        $grade     = in_array((string) $enrollment->grade_level, $validGrades, true) ? (string) $enrollment->grade_level : null;
                        // Grade unknown (no grade on the LRN master list) -> offer every active section
                        $options   = $grade ? ($sectionsByGr[$grade] ?? collect()) : $sections;
                        $bdMismatch = ($bd = $stu?->birthdate) && $enrollment->birthdate && !$bd->isSameDay($enrollment->birthdate);
                        $detId     = 'enDet' . $enrollment->id;
                    @endphp
                    <tr class="main">
                        <td>
                            <div class="en-student">
                                <div class="en-avatar">{{ mb_strtoupper(mb_substr($stu->name ?? '?', 0, 1)) }}</div>
                                <div>
                                    <div class="en-name">{{ $stu->name ?? 'Account removed' }}</div>
                                    <div class="en-email">{{ $stu->email ?? '—' }}</div>
                                    @if($stu?->lrn)<div class="en-lrn">LRN {{ $stu->lrn }}</div>@endif
                                    @if($bdMismatch)
                                        <div class="en-warn">⚠ Birthdate on form differs from account activation</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($grade)
                                <span class="en-grade">Grade {{ $grade }}</span>
                            @else
                                <span class="en-tbd">Grade not set</span>
                            @endif
                        </td>
                        <td>{{ $typeLabel[$enrollment->student_type] ?? ucfirst((string) $enrollment->student_type) }}</td>
                        <td>
                            <div>{{ $enrollment->created_at?->format('M j, Y') }}</div>
                            <div class="en-small">{{ $enrollment->created_at?->diffForHumans() }}</div>
                        </td>
                        <td>
                            <span class="en-badge en-badge-{{ $st }}">{{ ucfirst($st) }}</span>
                            @if($st === 'approved' && $stu?->section)
                                <div class="en-small" style="margin-top:4px;">{{ $stu->section->name }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="en-actions">
                                <button type="button" class="en-btn en-btn-more js-det" aria-expanded="false" aria-controls="{{ $detId }}">Details</button>
                                @if($st === 'pending' && $stu)
                                    @if($options->isEmpty())
                                        <div class="en-nosec">No active {{ $grade ? 'Grade '.$grade.' ' : '' }}section.
                                            <a href="{{ route('admin.sections.index') }}">Create one</a></div>
                                    @else
                                        <form method="POST" action="{{ route('admin.enrollment.approve', $enrollment) }}" class="en-actions js-approve"
                                              data-name="{{ $stu->name }}">
                                            @csrf
                                            @method('PATCH')
                                            <select name="section_id" class="en-action-select" required aria-label="Section for {{ $stu->name }}">
                                                <option value="">{{ $grade ? 'Select section…' : 'Select section (any grade)…' }}</option>
                                                @foreach($options->groupBy(fn ($s) => (string) $s->grade_level) as $g => $group)
                                                    <optgroup label="Grade {{ $g }}">
                                                        @foreach($group as $section)
                                                            <option value="{{ $section->id }}" @selected($options->count() === 1)>
                                                                {{ $section->name }}{{ $section->adviser ? ' — '.$section->adviser->name : ' — no adviser' }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="en-btn en-btn-approve">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                                Approve
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.enrollment.reject', $enrollment) }}" class="js-reject" data-name="{{ $stu->name }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="remarks" value="">
                                        <button type="submit" class="en-btn en-btn-reject">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                            Reject
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr class="det" id="{{ $detId }}" hidden>
                        <td colspan="6">
                            <div class="en-det">
                                <div class="wide"><span>Full name (form)</span><b>{{ $enrollment->full_name ?: '—' }}</b></div>
                                <div><span>Birthdate</span><b>{{ $enrollment->birthdate?->format('M j, Y') ?? '—' }}</b></div>
                                <div><span>Age / Gender</span><b>{{ $enrollment->age ?? '—' }} · {{ $enrollment->gender ?? '—' }}</b></div>
                                <div class="full"><span>Address</span><b>{{ $enrollment->address ?: '—' }}</b></div>
                                <div><span>Mother</span><b>{{ $enrollment->mother_name ?: '—' }}</b></div>
                                <div><span>Father</span><b>{{ $enrollment->father_name ?: '—' }}</b></div>
                                <div><span>Guardian</span><b>{{ $enrollment->guardian_name ?: '—' }}</b></div>
                                <div><span>Guardian contact</span><b>{{ $enrollment->guardian_contact ?: '—' }}</b></div>
                                <div class="wide"><span>Last school attended</span><b>{{ $enrollment->last_school ?: '—' }}</b></div>
                                <div><span>Last grade completed</span><b>{{ $enrollment->last_grade_completed ?: '—' }}</b></div>
                                <div><span>School year</span><b>{{ $enrollment->school_year ?: '—' }}</b></div>
                                @if($bdMismatch)
                                    <div class="full"><span>Birthdate check</span><b style="color:#b45309;">Form: {{ $enrollment->birthdate->format('M j, Y') }} · Activation: {{ $stu->birthdate->format('M j, Y') }} — verify with the student before approving.</b></div>
                                @endif
                                @if($st !== 'pending')
                                    <div class="wide"><span>Reviewed</span><b>{{ $enrollment->reviewed_at?->format('M j, Y g:i A') ?? '—' }}</b></div>
                                @endif
                                @if($st === 'rejected')
                                    <div class="wide"><span>Reason</span><b>{{ $enrollment->remarks ?: 'No reason given' }}</b></div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="en-empty">
                                <div class="en-empty-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
                                </div>
                                @if($search !== '' || $status)
                                    <div class="en-empty-title">No matching requests</div>
                                    <div class="en-empty-sub">Try a different search or clear the filters.</div>
                                @else
                                    <div class="en-empty-title">No enrollment requests yet</div>
                                    <div class="en-empty-sub">Requests appear here when students submit their enrollment form.</div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if($enrollments->hasPages())
            <div class="en-pagination">
                <span class="en-page-info">
                    Showing {{ $enrollments->firstItem() }}–{{ $enrollments->lastItem() }} of {{ $enrollments->total() }}
                </span>
                <div class="en-page-nav">
                    <a href="{{ $enrollments->previousPageUrl() ?? '#' }}" class="en-page-btn {{ $enrollments->onFirstPage() ? 'disabled' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                        Prev
                    </a>
                    <a href="{{ $enrollments->nextPageUrl() ?? '#' }}" class="en-page-btn {{ $enrollments->hasMorePages() ? '' : 'disabled' }}">
                        Next
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                </div>
            </div>
        @endif
    </div>

</div>

<script>
(function () {
    // Details toggle
    document.querySelectorAll('.js-det').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row = document.getElementById(btn.getAttribute('aria-controls'));
            if (!row) return;
            var open = row.hidden;
            row.hidden = !open;
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.textContent = open ? 'Hide' : 'Details';
        });
    });

    // Approve: confirm with the chosen section
    document.querySelectorAll('.js-approve').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var sel = form.querySelector('select[name="section_id"]');
            if (!sel.value) { e.preventDefault(); alert('Please select a section first.'); sel.focus(); return; }
            var label = sel.options[sel.selectedIndex].text.split(' — ')[0];
            if (!confirm('Enroll ' + form.dataset.name + ' in ' + label + '?')) e.preventDefault();
        });
    });

    // Reject: ask for an optional reason (sent to the student by email)
    document.querySelectorAll('.js-reject').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var reason = prompt('Reject the enrollment of ' + form.dataset.name + '?\n\nReason (optional — the student will see this):', '');
            if (reason === null) { e.preventDefault(); return; }
            form.querySelector('input[name="remarks"]').value = reason.trim().slice(0, 500);
        });
    });
})();
</script>
@endsection
