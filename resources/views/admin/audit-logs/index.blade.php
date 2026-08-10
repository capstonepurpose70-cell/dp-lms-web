@extends('layouts.admin')
@section('title', 'Audit Logs')

@section('content')
<style>
    :root {
        --al-blue-500: #2478e4;
        --al-blue-600: #1a62be;
        --al-blue-50:  #f0f6ff;
        --al-blue-100: #ddeafa;
        --al-blue-200: #bcd6fc;
    }

    .al-page {
        animation: al-fadein 0.32s cubic-bezier(0.22,1,0.36,1) both;
    }
    @keyframes al-fadein {
        from { opacity:0; transform:translateY(8px); }
        to   { opacity:1; transform:translateY(0); }
    }

    /* ── Header ── */
    .al-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    .al-header-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--slate-900);
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin: 0 0 0.2rem;
    }
    .al-header-sub {
        font-size: 13px;
        color: var(--slate-500);
        margin: 0;
    }

    /* ── Filter bar ── */
    .al-filterbar {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-lg);
        box-shadow: var(--shadow-sm);
        padding: 0.875rem 1.25rem;
    }

    .al-search {
        position: relative;
        display: flex;
        align-items: center;
    }
    .al-search-icon {
        position: absolute;
        left: 0.7rem;
        top: 50%;
        transform: translateY(-50%);
        width: 14px; height: 14px;
        color: var(--slate-400);
        pointer-events: none;
    }
    .al-search-input {
        height: 34px;
        width: 220px;
        padding: 0 0.75rem 0 2.1rem;
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-full);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        color: var(--slate-700);
        outline: none;
        transition: all 0.2s cubic-bezier(0.22,1,0.36,1);
    }
    .al-search-input::placeholder { color: var(--slate-400); font-size: 12.5px; }
    .al-search-input:focus {
        border-color: #4d96f0;
        box-shadow: 0 0 0 3px rgba(36,120,228,0.12);
        width: 260px;
    }

    .al-select {
        height: 34px;
        padding: 0 2rem 0 0.75rem;
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-md);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        color: var(--slate-700);
        outline: none;
        cursor: pointer;
        -webkit-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2394a3b8'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.6rem center;
        background-size: 15px;
        transition: all 0.18s;
    }
    .al-select:focus {
        border-color: var(--al-blue-500);
        box-shadow: 0 0 0 3px rgba(36,120,228,0.12);
    }

    .al-date-input {
        height: 34px;
        padding: 0 0.75rem;
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-md);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        color: var(--slate-700);
        outline: none;
        transition: all 0.18s;
    }
    .al-date-input:focus {
        border-color: var(--al-blue-500);
        box-shadow: 0 0 0 3px rgba(36,120,228,0.12);
    }

    .al-btn-filter {
        height: 34px;
        padding: 0 1rem;
        background: var(--al-blue-500);
        border: 1px solid var(--al-blue-600);
        border-radius: var(--r-md);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        font-weight: 600;
        color: #fff;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.18s;
        box-shadow: 0 2px 8px rgba(36,120,228,0.2);
    }
    .al-btn-filter:hover {
        background: var(--al-blue-600);
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(36,120,228,0.3);
    }

    .al-btn-reset {
        height: 34px;
        padding: 0 0.85rem;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        font-weight: 500;
        color: var(--slate-500);
        background: transparent;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        transition: color 0.18s;
    }
    .al-btn-reset:hover { color: var(--al-blue-600); }

    /* ── Stats strip ── */
    .al-stats {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
    }
    .al-stat-card {
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-lg);
        box-shadow: var(--shadow-sm);
        padding: 0.875rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex: 1;
        min-width: 140px;
    }
    .al-stat-icon {
        width: 36px; height: 36px;
        border-radius: var(--r-md);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .al-stat-icon svg { width: 16px; height: 16px; }
    .al-stat-value {
        font-family: 'Outfit', sans-serif;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--slate-900);
        line-height: 1;
        margin-bottom: 2px;
    }
    .al-stat-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--slate-400);
    }

    /* ── Table card ── */
    .al-card {
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }
    .al-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border-default);
        background: var(--slate-50);
    }
    .al-card-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        font-weight: 700;
        color: var(--slate-700);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .al-card-title svg { width: 15px; height: 15px; color: var(--al-blue-500); }
    .al-total-pill {
        font-size: 11.5px;
        font-weight: 700;
        padding: 2px 10px;
        border-radius: 999px;
        background: var(--al-blue-50);
        border: 1px solid var(--al-blue-200);
        color: var(--al-blue-600);
    }

    /* ── Table ── */
    .al-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .al-table th {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: var(--slate-500);
        padding: 0.75rem 1.25rem;
        background: var(--slate-50);
        border-bottom: 1px solid var(--border-default);
        text-align: left;
        white-space: nowrap;
    }
    .al-table td {
        padding: 0.875rem 1.25rem;
        font-size: 13px;
        color: var(--slate-700);
        border-bottom: 1px solid var(--slate-100);
        vertical-align: middle;
        transition: background 0.15s;
    }
    .al-table tr:last-child td { border-bottom: none; }
    .al-table tbody tr:hover td { background: var(--slate-50); }

    /* ── Role badges ── */
    .al-role {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.18rem 0.6rem;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .al-role::before {
        content: '';
        width: 5px; height: 5px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .al-role-teacher { background: var(--al-blue-50);  color: #1a62be; border-color: var(--al-blue-200); }
    .al-role-teacher::before { background: var(--al-blue-500); }
    .al-role-student { background: #e0f2f1; color: #0f766e; border-color: #5eead4; }
    .al-role-student::before { background: #0d9488; }
    .al-role-admin   { background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }
    .al-role-admin::before   { background: #7c3aed; }
    .al-role-parent  { background: #fffbeb; color: #b45309; border-color: #fcd34d; }
    .al-role-parent::before  { background: #d97706; }

    /* ── Module chip ── */
    .al-module {
        display: inline-block;
        padding: 0.15rem 0.6rem;
        background: var(--slate-100);
        border: 1px solid var(--border-default);
        border-radius: var(--r-sm);
        font-size: 11px;
        font-weight: 600;
        color: var(--slate-600);
        white-space: nowrap;
    }
    .al-module-auth       { background: #fff7ed; border-color: #fed7aa; color: #c2410c; }
    .al-module-enrollment { background: var(--al-blue-50); border-color: var(--al-blue-200); color: var(--al-blue-600); }
    .al-module-grades     { background: #f0fdf4; border-color: #86efac; color: #15803d; }
    .al-module-messaging  { background: #fdf4ff; border-color: #f0abfc; color: #86198f; }

    /* ── IP chip ── */
    .al-ip {
        font-family: monospace;
        font-size: 11.5px;
        color: var(--slate-500);
        background: var(--slate-50);
        border: 1px solid var(--border-default);
        padding: 2px 8px;
        border-radius: var(--r-sm);
    }

    /* ── Time ── */
    .al-time {
        font-size: 12px;
        color: var(--slate-500);
        white-space: nowrap;
    }
    .al-time-date { font-weight: 500; color: var(--slate-700); }

    /* ── User cell ── */
    .al-user-avatar {
        width: 30px; height: 30px;
        border-radius: var(--r-sm);
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700;
        color: #fff;
        flex-shrink: 0;
        background: linear-gradient(135deg, var(--al-blue-500), var(--al-blue-700));
    }

    /* ── Empty ── */
    .al-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        padding: 3.5rem 1.5rem;
        color: var(--slate-400);
        font-size: 13px;
    }
    .al-empty svg { width: 36px; height: 36px; opacity: 0.2; }

    /* ── Pagination ── */
    .al-pagination {
        padding: 0.875rem 1.25rem;
        border-top: 1px solid var(--border-default);
        background: var(--slate-50);
    }
</style>

<div class="al-page">

    {{-- Header --}}
    <div class="al-header">
        <div>
            <h1 class="al-header-title">Audit Logs</h1>
            <p class="al-header-sub">Monitor all system activity and track user actions.</p>
        </div>
    </div>

    {{-- Stats strip --}}
    <div class="al-stats">
        <div class="al-stat-card">
            <div class="al-stat-icon" style="background:#eff6ff;">
                <svg fill="none" stroke="#2478e4" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <div class="al-stat-value">{{ $logs->total() }}</div>
                <div class="al-stat-label">Total Logs</div>
            </div>
        </div>
        <div class="al-stat-card">
            <div class="al-stat-icon" style="background:#fff7ed;">
                <svg fill="none" stroke="#c2410c" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <div class="al-stat-value">{{ $logs->where('module', 'Auth')->count() }}</div>
                <div class="al-stat-label">Auth Events</div>
            </div>
        </div>
        <div class="al-stat-card">
            <div class="al-stat-icon" style="background:#ecfdf5;">
                <svg fill="none" stroke="#059669" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="al-stat-value">{{ $logs->where('module', 'Enrollment')->count() }}</div>
                <div class="al-stat-label">Enrollments</div>
            </div>
        </div>
        <div class="al-stat-card">
            <div class="al-stat-icon" style="background:#f5f3ff;">
                <svg fill="none" stroke="#7c3aed" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <div class="al-stat-value">{{ $logs->pluck('user_name')->unique()->filter()->count() }}</div>
                <div class="al-stat-label">Unique Users</div>
            </div>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" class="al-filterbar">
        <div class="al-search">
            <svg class="al-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search action, user, module…"
                   class="al-search-input">
        </div>

        <select name="role" class="al-select">
            <option value="">All Roles</option>
            <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Student</option>
            <option value="teacher" {{ request('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
            <option value="parent"  {{ request('role') == 'parent'  ? 'selected' : '' }}>Parent</option>
            <option value="admin"   {{ request('role') == 'admin'   ? 'selected' : '' }}>Admin</option>
        </select>

        <select name="module" class="al-select">
            <option value="">All Modules</option>
            <option value="Auth"            {{ request('module') == 'Auth'            ? 'selected' : '' }}>Auth</option>
            <option value="Enrollment"      {{ request('module') == 'Enrollment'      ? 'selected' : '' }}>Enrollment</option>
            <option value="Grades"          {{ request('module') == 'Grades'          ? 'selected' : '' }}>Grades</option>
            <option value="Messaging"       {{ request('module') == 'Messaging'       ? 'selected' : '' }}>Messaging</option>
            <option value="User Management" {{ request('module') == 'User Management' ? 'selected' : '' }}>User Management</option>
        </select>

        <input type="date" name="date" value="{{ request('date') }}" class="al-date-input">

        <button type="submit" class="al-btn-filter">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M7 10h10M11 16h2"/>
            </svg>
            Filter
        </button>
        <a href="{{ route('admin.audit-logs.index') }}" class="al-btn-reset">Reset</a>
    </form>

    {{-- Table --}}
    <div class="al-card">
        <div class="al-card-header">
            <div class="al-card-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Activity Records
            </div>
            <span class="al-total-pill">{{ number_format($logs->total()) }} entries</span>
        </div>

        <div style="overflow-x:auto;">
            <table class="al-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        {{-- Date/Time --}}
                        <td class="al-time">
                            <span class="al-time-date">{{ $log->created_at->format('M d, Y') }}</span><br>
                            {{ $log->created_at->format('H:i:s') }}
                        </td>

                        {{-- User --}}
                        <td>
                            <div style="display:flex; align-items:center; gap:0.6rem;">
                                <div class="al-user-avatar">
                                    {{ strtoupper(substr($log->user_name ?? 'S', 0, 1)) }}
                                </div>
                                <span style="font-size:13px; font-weight:600; color:var(--slate-800);">
                                    {{ $log->user_name ?? 'System' }}
                                </span>
                            </div>
                        </td>

                        {{-- Role --}}
                        <td>
                            @if($log->role)
                                <span class="al-role al-role-{{ $log->role }}">
                                    {{ ucfirst($log->role) }}
                                </span>
                            @else
                                <span style="color:var(--slate-400); font-size:12px;">—</span>
                            @endif
                        </td>

                        {{-- Module --}}
                        <td>
                            @if($log->module)
                                @php
                                    $modClass = match(strtolower($log->module)) {
                                        'auth'            => 'al-module-auth',
                                        'enrollment'      => 'al-module-enrollment',
                                        'grades'          => 'al-module-grades',
                                        'messaging'       => 'al-module-messaging',
                                        default           => '',
                                    };
                                @endphp
                                <span class="al-module {{ $modClass }}">{{ $log->module }}</span>
                            @else
                                <span style="color:var(--slate-400); font-size:12px;">—</span>
                            @endif
                        </td>

                        {{-- Action --}}
                        <td style="max-width:280px;">
                            <span style="font-size:13px; font-weight:500; color:var(--slate-800);">
                                {{ $log->action }}
                            </span>
                        </td>

                        {{-- IP --}}
                        <td>
                            <span class="al-ip">{{ $log->ip_address ?? '—' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="al-empty">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                No audit logs found.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="al-pagination">
            {{ $logs->withQueryString()->links() }}
        </div>
    </div>

</div>
@endsection