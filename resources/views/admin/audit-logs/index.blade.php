@extends('layouts.admin')
@section('title', 'Audit Logs')

@section('content')
@php
    $hasFilters = $search !== '' || $role !== '' || $module !== '' || $date !== '';
    $todayStr   = now()->toDateString();

    // Short, readable device label from the stored header (mobile app sends "X-Client")
    $device = function (?string $ua) {
        $ua = (string) $ua;
        if ($ua === '' || $ua === 'Unknown') return ['—', ''];
        if (stripos($ua, 'DP-LMS Mobile') !== false) return ['Mobile app', 'mobile'];
        $browser = match (true) {
            str_contains($ua, 'Edg/')     => 'Edge',
            str_contains($ua, 'OPR/')     => 'Opera',
            str_contains($ua, 'Chrome/')  => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/')  => 'Safari',
            default                       => 'Browser',
        };
        $os = match (true) {
            str_contains($ua, 'Android')                                  => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')      => 'iOS',
            str_contains($ua, 'Windows')                                  => 'Windows',
            str_contains($ua, 'Mac OS')                                   => 'macOS',
            str_contains($ua, 'Linux')                                    => 'Linux',
            default                                                       => '',
        };
        return [$browser . ($os ? ' · ' . $os : ''), str_contains($os, 'Android') || $os === 'iOS' ? 'mobile' : ''];
    };
    // Same rule as the controller's "failed" stat: fail / blocked / locked
    $isAlert = fn ($a) => (bool) preg_match('/fail|blocked|locked/i', (string) $a);
    $roleClass = ['student' => 'student', 'teacher' => 'teacher', 'parent' => 'parent', 'admin' => 'admin'];
@endphp
<style>
    /* Uses admin layout tokens (--white, --slate-*, --border-default) so dark mode works */
    .al-page { animation: al-fade .22s ease both; }
    @keyframes al-fade { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }

    .al-head { display:flex; align-items:center; gap:14px; margin-bottom:18px; }
    .al-head-ic { width:46px; height:46px; border-radius:14px; display:grid; place-items:center; color:#fff; flex-shrink:0;
        background:linear-gradient(135deg, var(--blue-500), var(--blue-700)); box-shadow:0 6px 16px -4px rgba(36,120,228,.5); }
    .al-head-ic svg { width:22px; height:22px; }
    .al-title { font-size:22px; font-weight:800; color:var(--slate-900); letter-spacing:-0.01em; margin:0; }
    .al-sub { font-size:13px; color:var(--slate-500); margin:2px 0 0; }

    .al-stats { display:grid; grid-template-columns:repeat(5, minmax(0,1fr)); gap:12px; margin-bottom:16px; }
    .al-stat { background:var(--white); border:1px solid var(--border-default); border-radius:14px; padding:14px 16px; box-shadow:var(--shadow-sm); }
    .al-stat b { display:block; font-size:22px; font-weight:800; color:var(--slate-900); line-height:1.1; }
    .al-stat span { font-size:12px; color:var(--slate-500); font-weight:500; }
    .al-stat.alert b { color:#dc2626; }
    [data-theme="dark"] .al-stat.alert b { color:#f87171; }

    .al-filters { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:10px; }
    .al-search { position:relative; flex:1; min-width:220px; max-width:360px; }
    .al-search svg { position:absolute; left:12px; top:50%; transform:translateY(-50%); width:16px; height:16px; color:var(--slate-400); pointer-events:none; }
    .al-in { height:40px; padding:0 12px; border:1.5px solid var(--border-default); border-radius:10px; font-size:13.5px;
        color:var(--slate-900); background:var(--white); box-sizing:border-box; }
    .al-search .al-in { width:100%; padding-left:36px; }
    .al-in:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
    select.al-in { cursor:pointer; max-width:220px; }
    .al-go { height:40px; padding:0 18px; border:0; border-radius:10px; background:#1d4ed8; color:#fff; font-size:13.5px; font-weight:700; cursor:pointer; }
    .al-go:hover { background:#1e40af; }
    .al-clear { font-size:13px; font-weight:600; color:var(--slate-500); text-decoration:none; padding:0 6px; }
    .al-clear:hover { color:#2563eb; }
    .al-quick { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:14px; }
    .al-chip { display:inline-flex; align-items:center; height:30px; padding:0 12px; border-radius:999px; border:1px solid var(--border-default);
        background:var(--white); color:var(--slate-700); font-size:12.5px; font-weight:600; text-decoration:none; }
    .al-chip:hover { border-color:#2563eb; color:#2563eb; }
    .al-chip.on { background:#1d4ed8; border-color:#1d4ed8; color:#fff; }

    .al-card { background:var(--surface-card); border:1px solid var(--border-default); border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; }
    .al-scroll { overflow-x:auto; }
    .al-table { width:100%; border-collapse:collapse; min-width:900px; }
    .al-table th { text-align:left; padding:12px 16px; font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase;
        color:var(--slate-500); background:var(--slate-50); border-bottom:1px solid var(--border-default); white-space:nowrap; }
    .al-table td { padding:12px 16px; font-size:13.5px; color:var(--slate-700); border-bottom:1px solid var(--border-default); vertical-align:top; }
    .al-table tbody tr:last-child td { border-bottom:0; }
    .al-table tbody tr:hover td { background:var(--slate-50); }
    .al-table tr.alert td { background:rgba(220,38,38,.04); }
    .al-table tr.alert td:first-child { box-shadow:inset 3px 0 0 #dc2626; }

    .al-when b { display:block; font-weight:600; color:var(--slate-900); font-size:13px; white-space:nowrap; }
    .al-when span { font-size:12px; color:var(--slate-500); white-space:nowrap; }
    .al-user { display:flex; align-items:center; gap:10px; }
    .al-av { width:32px; height:32px; border-radius:10px; display:grid; place-items:center; color:#fff; font-size:12px; font-weight:800; flex-shrink:0; background:#64748b; }
    .al-av.student { background:#0d9488; } .al-av.teacher { background:#2563eb; } .al-av.parent { background:#d97706; } .al-av.admin { background:#7c3aed; }
    .al-uname { font-weight:700; color:var(--slate-900); font-size:13px; }
    .al-role { font-size:11.5px; font-weight:700; text-transform:capitalize; color:var(--slate-500); }
    .al-role.student { color:#0f766e; } .al-role.teacher { color:#1d4ed8; } .al-role.parent { color:#b45309; } .al-role.admin { color:#6d28d9; }
    [data-theme="dark"] .al-role.student { color:#5eead4; } [data-theme="dark"] .al-role.teacher { color:#93c5fd; }
    [data-theme="dark"] .al-role.parent { color:#fbbf24; } [data-theme="dark"] .al-role.admin { color:#c4b5fd; }

    .al-action { font-weight:600; color:var(--slate-900); line-height:1.4; }
    .al-table tr.alert .al-action { color:#b91c1c; }
    [data-theme="dark"] .al-table tr.alert .al-action { color:#f87171; }
    .al-desc { font-size:12.5px; color:var(--slate-500); margin-top:3px; line-height:1.45; word-break:break-word; }
    .al-mod { display:inline-block; padding:3px 9px; border-radius:7px; font-size:12px; font-weight:600; background:var(--slate-100); color:var(--slate-700); white-space:nowrap; }
    .al-mod.auth { background:rgba(234,88,12,.12); color:#c2410c; }
    [data-theme="dark"] .al-mod.auth { color:#fdba74; }
    .al-mono { font-family:ui-monospace, Consolas, monospace; font-size:12px; color:var(--slate-700); }
    .al-dev { font-size:12px; color:var(--slate-500); margin-top:2px; white-space:nowrap; }
    .al-dev.mobile { color:#0d9488; font-weight:600; }

    .al-empty { text-align:center; padding:52px 20px; color:var(--slate-500); }
    .al-empty svg { width:40px; height:40px; color:var(--slate-400); margin-bottom:10px; }
    .al-empty b { display:block; font-size:14.5px; color:var(--slate-700); margin-bottom:3px; }

    .al-pager { display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; padding:12px 16px;
        border-top:1px solid var(--border-default); background:var(--slate-50); }
    .al-pager-info { font-size:12.5px; color:var(--slate-500); }

    @media (max-width: 1100px) { .al-stats { grid-template-columns:repeat(3, minmax(0,1fr)); } }
    @media (max-width: 640px)  { .al-stats { grid-template-columns:repeat(2, minmax(0,1fr)); } }
</style>

<div class="al-page">

    <div class="al-head">
        <div class="al-head-ic" aria-hidden="true">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
        </div>
        <div>
            <h1 class="al-title">Audit Logs</h1>
            <p class="al-sub">Every sign-in and admin, teacher, student and parent action — who did it, when, and from which device.</p>
        </div>
    </div>

    {{-- Stats (for the current filters, across ALL pages) --}}
    <div class="al-stats">
        <div class="al-stat"><b>{{ number_format($stats['total']) }}</b><span>{{ $hasFilters ? 'Matching entries' : 'Total entries' }}</span></div>
        <div class="al-stat"><b>{{ number_format($stats['auth']) }}</b><span>Sign-in events</span></div>
        <div class="al-stat {{ $stats['failed'] ? 'alert' : '' }}"><b>{{ number_format($stats['failed']) }}</b><span>Failed / blocked attempts</span></div>
        <div class="al-stat"><b>{{ number_format($stats['enrollment']) }}</b><span>Enrollment actions</span></div>
        <div class="al-stat"><b>{{ number_format($stats['users']) }}</b><span>Different users</span></div>
    </div>

    <form method="GET" class="al-filters" role="search">
        <label class="al-search">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/></svg>
            <input type="text" name="search" value="{{ $search }}" class="al-in" placeholder="Search user, action, module or details…" aria-label="Search logs">
        </label>
        <select name="role" class="al-in" aria-label="Filter by role" onchange="this.form.submit()">
            <option value="">All roles</option>
            @foreach (['admin' => 'Admin', 'teacher' => 'Teacher', 'student' => 'Student', 'parent' => 'Parent', 'unknown' => 'Guest / not signed in'] as $val => $label)
                <option value="{{ $val }}" @selected($role === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="module" class="al-in" aria-label="Filter by module" onchange="this.form.submit()">
            <option value="">All modules</option>
            @foreach ($modules as $m)
                <option value="{{ $m }}" @selected($module === $m)>{{ $m }}</option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ $date }}" max="{{ $todayStr }}" class="al-in" aria-label="Filter by date" onchange="this.form.submit()">
        <button type="submit" class="al-go">Search</button>
        @if ($hasFilters)
            <a href="{{ route('admin.audit-logs.index') }}" class="al-clear">Clear filters</a>
        @endif
    </form>

    <div class="al-quick">
        <a href="{{ route('admin.audit-logs.index', array_filter(['search' => $search, 'role' => $role, 'module' => $module])) }}" class="al-chip {{ $date === '' ? 'on' : '' }}">All dates</a>
        <a href="{{ route('admin.audit-logs.index', array_filter(['search' => $search, 'role' => $role, 'module' => $module, 'date' => $todayStr])) }}" class="al-chip {{ $date === $todayStr ? 'on' : '' }}">Today</a>
        <a href="{{ route('admin.audit-logs.index', array_filter(['search' => $search, 'role' => $role, 'module' => $module, 'date' => now()->subDay()->toDateString()])) }}" class="al-chip {{ $date === now()->subDay()->toDateString() ? 'on' : '' }}">Yesterday</a>
        <a href="{{ route('admin.audit-logs.index', ['search' => 'fail']) }}" class="al-chip {{ $search === 'fail' ? 'on' : '' }}">Failed attempts</a>
        <a href="{{ route('admin.audit-logs.index', ['role' => 'admin']) }}" class="al-chip {{ $role === 'admin' && $search === '' && $module === '' ? 'on' : '' }}">Admin actions</a>
    </div>

    <div class="al-card">
        <div class="al-scroll">
            <table class="al-table">
                <thead>
                    <tr>
                        <th style="width:130px;">When</th>
                        <th>User</th>
                        <th>Module</th>
                        <th>Action &amp; details</th>
                        <th>IP / Device</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        @php
                            $r = $roleClass[$log->role] ?? '';
                            [$devLabel, $devClass] = $device($log->device);
                            $name = $log->user_name ?: 'Guest';
                        @endphp
                        <tr class="{{ $isAlert($log->action) ? 'alert' : '' }}">
                            <td class="al-when" title="{{ $log->created_at?->diffForHumans() }}">
                                <b>{{ $log->created_at?->format('M j, Y') }}</b>
                                <span>{{ $log->created_at?->format('g:i:s A') }}</span>
                            </td>
                            <td>
                                <div class="al-user">
                                    <div class="al-av {{ $r }}">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</div>
                                    <div>
                                        <div class="al-uname">{{ $name }}</div>
                                        <div class="al-role {{ $r }}">{{ $log->role && $log->role !== 'unknown' ? $log->role : 'Not signed in' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($log->module)
                                    <span class="al-mod {{ strtolower($log->module) === 'auth' ? 'auth' : '' }}">{{ $log->module }}</span>
                                @else
                                    <span class="al-dev">—</span>
                                @endif
                            </td>
                            <td style="max-width:380px;">
                                <div class="al-action">{{ $log->action }}</div>
                                @if ($log->description)
                                    <div class="al-desc">{{ \Illuminate\Support\Str::limit($log->description, 220) }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="al-mono">{{ $log->ip_address ?: '—' }}</div>
                                <div class="al-dev {{ $devClass }}" title="{{ $log->device }}">{{ $devLabel }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">
                            <div class="al-empty">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <b>{{ $hasFilters ? 'No log entries match your filters' : 'No activity recorded yet' }}</b>
                                <span>{{ $hasFilters ? 'Try a different date, role or module.' : 'Entries appear here as people sign in and use the system.' }}</span>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->total() > 0)
            <div class="al-pager">
                <span class="al-pager-info">Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ number_format($logs->total()) }}</span>
                {{ $logs->links('pagination::default') }}
            </div>
        @endif
    </div>
</div>
@endsection
