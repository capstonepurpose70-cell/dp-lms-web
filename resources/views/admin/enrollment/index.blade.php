@extends('layouts.admin')

@section('title', 'Enrollment Management')

@section('content')
<style>
    .en-page { animation: en-fade 0.32s cubic-bezier(0.22,1,0.36,1) both; }
    @keyframes en-fade { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

    /* Header */
    .en-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 22px; flex-wrap: wrap; }
    .en-header-meta { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .en-header-icon {
        width: 46px; height: 46px; border-radius: var(--r-lg); flex-shrink: 0;
        display: grid; place-items: center; color: #fff;
        background: linear-gradient(135deg, var(--blue-500), var(--blue-700));
        box-shadow: 0 6px 16px -4px rgba(36,120,228,0.5);
    }
    .en-header-icon svg { width: 22px; height: 22px; }
    .en-title { font-family: var(--font-display); font-size: 22px; font-weight: 700; color: var(--slate-900); letter-spacing: -0.02em; }
    .en-sub   { font-size: 13px; color: var(--slate-500); margin-top: 2px; }
    .en-count {
        display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px;
        background: var(--blue-50); color: var(--blue-700); border: 1px solid var(--blue-100);
        border-radius: var(--r-full); font-size: 13px; font-weight: 600;
    }

    /* Flash */
    .en-flash { display: flex; align-items: center; gap: 10px; padding: 13px 16px; border-radius: var(--r-md); font-size: 13.5px; font-weight: 500; margin-bottom: 18px; border: 1px solid; }
    .en-flash svg { width: 18px; height: 18px; flex-shrink: 0; }
    .en-flash-success { background: var(--success-light); color: #047857; border-color: #6ee7b7; }
    .en-flash-error   { background: var(--danger-light);  color: #b91c1c; border-color: #fca5a5; }

    /* Filter bar */
    .en-filterbar { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; flex-wrap: wrap; }
    .en-search { position: relative; display: flex; align-items: center; }
    .en-search svg { position: absolute; left: 12px; width: 15px; height: 15px; color: var(--slate-400); pointer-events: none; }
    .en-search input {
        height: 40px; width: 250px; max-width: 60vw; padding: 0 14px 0 36px;
        border: 1px solid var(--border-default); border-radius: var(--r-md);
        font-family: var(--font-ui); font-size: 13.5px; color: var(--slate-900);
        background: var(--white); transition: border-color .15s, box-shadow .15s;
    }
    .en-search input:focus { outline: none; border-color: var(--blue-400); box-shadow: 0 0 0 3px var(--blue-100); }
    .en-select {
        height: 40px; padding: 0 36px 0 14px; border: 1px solid var(--border-default);
        border-radius: var(--r-md); font-family: var(--font-ui); font-size: 13.5px; color: var(--slate-700);
        background: var(--white); cursor: pointer; appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2394a3b8'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 12px center;
    }
    .en-select:focus { outline: none; border-color: var(--blue-400); box-shadow: 0 0 0 3px var(--blue-100); }
    .en-btn-apply {
        height: 40px; padding: 0 18px; border: none; border-radius: var(--r-md); cursor: pointer;
        background: var(--blue-600); color: #fff; font-family: var(--font-ui); font-size: 13px; font-weight: 600;
        transition: background .15s, transform .1s;
    }
    .en-btn-apply:hover { background: var(--blue-700); }
    .en-btn-apply:active { transform: translateY(1px); }
    .en-btn-reset { height: 40px; padding: 0 12px; display: inline-flex; align-items: center; color: var(--slate-500); font-size: 13px; font-weight: 600; text-decoration: none; }
    .en-btn-reset:hover { color: var(--blue-600); }

    /* Card + table */
    .en-card { background: var(--surface-card); border: 1px solid var(--border-default); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); overflow: hidden; }
    .en-table { width: 100%; border-collapse: collapse; }
    .en-table th {
        text-align: left; padding: 13px 20px; font-size: 11px; font-weight: 700; letter-spacing: 0.04em;
        text-transform: uppercase; color: var(--slate-500); background: var(--slate-50);
        border-bottom: 1px solid var(--border-default); white-space: nowrap;
    }
    .en-table td { padding: 14px 20px; font-size: 13.5px; color: var(--slate-700); border-bottom: 1px solid var(--slate-100); vertical-align: middle; }
    .en-table tr:last-child td { border-bottom: none; }
    .en-table tbody tr { transition: background .12s; }
    .en-table tbody tr:hover td { background: var(--slate-50); }

    .en-student { display: flex; align-items: center; gap: 12px; }
    .en-avatar {
        width: 38px; height: 38px; border-radius: var(--r-full); flex-shrink: 0;
        display: grid; place-items: center; font-weight: 700; font-size: 14px; color: var(--blue-700);
        background: var(--blue-100);
    }
    .en-name  { font-weight: 600; color: var(--slate-900); }
    .en-email { font-size: 12.5px; color: var(--slate-500); }
    .en-grade { font-weight: 600; color: var(--slate-700); }
    .en-muted { color: var(--slate-400); font-style: italic; }
    .en-year  { display: inline-flex; padding: 3px 10px; border-radius: var(--r-sm); background: var(--slate-100); color: var(--slate-700); font-size: 12.5px; font-weight: 600; }

    /* Badges */
    .en-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 11px; border-radius: var(--r-full); font-size: 12px; font-weight: 600; border: 1px solid; }
    .en-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .en-badge-pending  { background: #fffbeb; color: #b45309; border-color: #fcd34d; }
    .en-badge-pending::before  { background: #f59e0b; }
    .en-badge-approved { background: #ecfdf5; color: #047857; border-color: #6ee7b7; }
    .en-badge-approved::before { background: #10b981; }
    .en-badge-rejected { background: #fef2f2; color: #b91c1c; border-color: #fca5a5; }
    .en-badge-rejected::before { background: #ef4444; }

    /* Actions */
    .en-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .en-action-select {
        height: 34px; padding: 0 30px 0 10px; border: 1px solid var(--border-default); border-radius: var(--r-sm);
        font-family: var(--font-ui); font-size: 12.5px; color: var(--slate-700); background: var(--white); cursor: pointer; appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2394a3b8'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 9px center; max-width: 180px;
    }
    .en-action-select:focus { outline: none; border-color: var(--um-green-500); box-shadow: 0 0 0 3px var(--um-green-glow); }
    .en-btn { height: 34px; padding: 0 14px; border: none; border-radius: var(--r-sm); cursor: pointer; font-family: var(--font-ui); font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; transition: background .15s, transform .1s; }
    .en-btn svg { width: 13px; height: 13px; }
    .en-btn:active { transform: translateY(1px); }
    .en-btn-approve { background: var(--um-green-500); color: #fff; }
    .en-btn-approve:hover { background: var(--um-green-600); }
    .en-btn-reject { background: #fff; color: var(--danger); border: 1px solid #fca5a5; }
    .en-btn-reject:hover { background: var(--danger-light); }
    .en-na { color: var(--slate-400); font-size: 12.5px; font-style: italic; }

    /* Empty */
    .en-empty { text-align: center; padding: 56px 20px; }
    .en-empty-icon { width: 56px; height: 56px; margin: 0 auto 14px; border-radius: var(--r-full); display: grid; place-items: center; background: var(--slate-100); color: var(--slate-400); }
    .en-empty-icon svg { width: 26px; height: 26px; }
    .en-empty-title { font-weight: 700; font-size: 15px; color: var(--slate-700); }
    .en-empty-sub { font-size: 13px; color: var(--slate-400); margin-top: 4px; }

    /* Pagination */
    .en-pagination { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-top: 1px solid var(--border-default); background: var(--slate-50); }
    .en-page-info { font-size: 12.5px; color: var(--slate-500); }
    .en-page-nav { display: flex; gap: 8px; }
    .en-page-btn { height: 34px; padding: 0 14px; display: inline-flex; align-items: center; gap: 5px; border: 1px solid var(--border-default); border-radius: var(--r-sm); background: var(--white); color: var(--slate-700); font-size: 12.5px; font-weight: 600; text-decoration: none; transition: border-color .15s, color .15s; }
    .en-page-btn:hover { border-color: var(--blue-400); color: var(--blue-600); }
    .en-page-btn.disabled { opacity: 0.45; pointer-events: none; }
</style>

<div class="en-page">

    {{-- Header --}}
    <div class="en-header">
        <div class="en-header-meta">
            <div class="en-header-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
            </div>
            <div>
                <div class="en-title">Enrollment Management</div>
                <div class="en-sub">Review and process student enrollment applications</div>
            </div>
        </div>
        <span class="en-count">
            {{ $enrollments->total() }} {{ Str::plural('request', $enrollments->total()) }}
        </span>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="en-flash en-flash-success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="en-flash en-flash-error">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter bar --}}
    <form method="GET" class="en-filterbar">
        <label class="en-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by student name…">
        </label>
        <select name="status" class="en-select">
            <option value="">All statuses</option>
            <option value="pending"  @selected(request('status')==='pending')>Pending</option>
            <option value="approved" @selected(request('status')==='approved')>Approved</option>
            <option value="rejected" @selected(request('status')==='rejected')>Rejected</option>
        </select>
        <button type="submit" class="en-btn-apply">Apply</button>
        @if(request('search') || request('status'))
            <a href="{{ route('admin.enrollment.index') }}" class="en-btn-reset">Reset</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="en-card">
        <table class="en-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Grade</th>
                    <th>Section</th>
                    <th>School Year</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $enrollment)
                    <tr>
                        {{-- Student --}}
                        <td>
                            <div class="en-student">
                                <div class="en-avatar">{{ strtoupper(substr($enrollment->student->name ?? '?', 0, 1)) }}</div>
                                <div>
                                    <div class="en-name">{{ $enrollment->student->name ?? 'Unknown' }}</div>
                                    <div class="en-email">{{ $enrollment->student->email ?? '—' }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Grade --}}
                        <td><span class="en-grade">Grade {{ $enrollment->grade_level }}</span></td>

                        {{-- Section --}}
                        <td>
                            @if($enrollment->student && $enrollment->student->section)
                                {{ $enrollment->student->section->name }}
                            @else
                                <span class="en-muted">Unassigned</span>
                            @endif
                        </td>

                        {{-- School Year --}}
                        <td><span class="en-year">{{ $enrollment->school_year }}</span></td>

                        {{-- Status --}}
                        <td>
                            @php $st = $enrollment->status; @endphp
                            <span class="en-badge en-badge-{{ $st }}">{{ ucfirst($st) }}</span>
                        </td>

                        {{-- Actions --}}
                        <td style="text-align:right;">
                            @if($st === 'pending')
                                <div class="en-actions" style="justify-content:flex-end;">
                                    <form method="POST" action="{{ route('admin.enrollment.approve', $enrollment) }}" class="en-actions" onsubmit="return this.section_id.value ? confirm('Approve and enroll this student?') : (alert('Please select a section first.'), false);">
                                        @csrf
                                        @method('PATCH')
                                        <select name="section_id" class="en-action-select" required>
                                            <option value="">Select section…</option>
                                            @foreach($sections as $section)
                                                @if((string) $section->grade_level === (string) $enrollment->grade_level)
                                                    <option value="{{ $section->id }}">Grade {{ $section->grade_level }} — {{ $section->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="submit" class="en-btn en-btn-approve">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                            Approve
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.enrollment.reject', $enrollment) }}" onsubmit="return confirm('Reject this enrollment request?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="en-btn en-btn-reject">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="en-na">No actions available</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="en-empty">
                                <div class="en-empty-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
                                </div>
                                <div class="en-empty-title">No enrollment requests found</div>
                                <div class="en-empty-sub">There are currently no records matching your filters.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($enrollments->hasPages())
            <div class="en-pagination">
                <span class="en-page-info">
                    Page {{ $enrollments->currentPage() }} of {{ $enrollments->lastPage() }}
                    · {{ $enrollments->total() }} total
                </span>
                <div class="en-page-nav">
                    <a href="{{ $enrollments->previousPageUrl() ?? '#' }}"
                       class="en-page-btn {{ $enrollments->onFirstPage() ? 'disabled' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                        Prev
                    </a>
                    <a href="{{ $enrollments->nextPageUrl() ?? '#' }}"
                       class="en-page-btn {{ $enrollments->hasMorePages() ? '' : 'disabled' }}">
                        Next
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection