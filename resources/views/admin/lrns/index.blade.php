@extends('layouts.admin')
@section('title', 'LRN Master List')

@section('content')
<style>
    /* Uses admin layout tokens (--white, --slate-*, --border-default) so dark mode works */
    .lrn-page { animation: lrn-fadein .22s ease both; }
    @keyframes lrn-fadein { from { opacity:0; transform:translateY(6px);} to { opacity:1; transform:none;} }

    .lrn-head { display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:18px; }
    .lrn-title h1 { font-size:22px; font-weight:800; margin:0; color:var(--slate-900); letter-spacing:-0.01em; }
    .lrn-title p  { margin:4px 0 0; font-size:13px; color:var(--slate-500); max-width:620px; line-height:1.5; }

    .lrn-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:18px; }
    .lrn-stat  { background:var(--white); border:1px solid var(--border-default); border-radius:14px; padding:14px 16px;
                 display:flex; align-items:center; gap:12px; box-shadow:var(--shadow-sm); }
    .lrn-stat-ic { width:40px; height:40px; border-radius:11px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .lrn-stat-ic svg { width:20px; height:20px; }
    .lrn-stat b { font-size:22px; font-weight:800; display:block; color:var(--slate-900); line-height:1.1; }
    .lrn-stat span { font-size:12px; color:var(--slate-500); font-weight:500; }
    .ic-blue  { background:rgba(37,99,235,.12);  color:#2563eb; }
    .ic-green { background:rgba(22,163,74,.12);  color:#16a34a; }
    .ic-indigo{ background:rgba(79,70,229,.12);  color:#4f46e5; }

    .lrn-grid { display:grid; grid-template-columns: 340px minmax(0,1fr); gap:16px; align-items:start; }
    .lrn-card { background:var(--white); border:1px solid var(--border-default); border-radius:16px; padding:18px; box-shadow:var(--shadow-sm); }
    .lrn-card h3 { margin:0 0 4px; font-size:14.5px; font-weight:800; color:var(--slate-900); display:flex; align-items:center; gap:8px; }
    .lrn-card h3 svg { width:17px; height:17px; color:#2563eb; }
    .lrn-card .lrn-sub { font-size:12px; color:var(--slate-500); margin:0 0 10px; }
    .lrn-card label { display:block; font-size:12px; font-weight:700; margin:12px 0 5px; color:var(--slate-700); }
    .lrn-card label .opt { font-weight:500; color:var(--slate-400); }
    .lrn-input, .lrn-card textarea, .lrn-card select {
        width:100%; padding:10px 12px; border:1.5px solid var(--border-default); border-radius:10px;
        font-size:13.5px; background:var(--white); color:var(--slate-900); box-sizing:border-box;
        transition:border-color .15s, box-shadow .15s;
    }
    .lrn-input:focus, .lrn-card textarea:focus, .lrn-card select:focus, .lrn-search input:focus, .lrn-search select:focus {
        outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15);
    }
    .lrn-card textarea { min-height:130px; font-family:ui-monospace, Consolas, monospace; font-size:12.5px; resize:vertical; }
    .lrn-count { display:flex; justify-content:space-between; font-size:11.5px; color:var(--slate-500); margin-top:5px; }
    .lrn-count .ok { color:#16a34a; font-weight:700; }

    .lrn-btn { margin-top:14px; width:100%; padding:11px; border:0; border-radius:10px; font-weight:700;
        font-size:13.5px; cursor:pointer; background:#1d4ed8; color:#fff; display:flex; align-items:center; justify-content:center; gap:8px;
        transition:background .15s, transform .1s; }
    .lrn-btn:hover { background:#1e40af; }
    .lrn-btn:active { transform:scale(.98); }
    .lrn-btn.gold { background:#b45309; }
    .lrn-btn.gold:hover { background:#92400e; }
    .lrn-btn svg { width:16px; height:16px; }
    .lrn-hint { font-size:12px; color:var(--slate-500); margin-top:10px; line-height:1.55; }
    .lrn-hint code { background:var(--slate-100); color:var(--slate-700); padding:1px 6px; border-radius:5px; font-size:11.5px; }

    .lrn-list-head { display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
    .lrn-search { display:flex; gap:8px; flex-wrap:wrap; flex:1; }
    .lrn-search .box { position:relative; flex:1; min-width:200px; }
    .lrn-search .box svg { position:absolute; left:11px; top:50%; transform:translateY(-50%); width:16px; height:16px; color:var(--slate-400); pointer-events:none; }
    .lrn-search input { width:100%; padding:10px 12px 10px 34px; border:1.5px solid var(--border-default); border-radius:10px;
        font-size:13.5px; background:var(--white); color:var(--slate-900); box-sizing:border-box; }
    .lrn-search select { padding:10px 12px; border:1.5px solid var(--border-default); border-radius:10px;
        font-size:13.5px; background:var(--white); color:var(--slate-900); cursor:pointer; }
    .lrn-search button { padding:10px 16px; border:0; border-radius:10px; background:#1d4ed8; color:#fff;
        font-weight:700; font-size:13.5px; cursor:pointer; }
    .lrn-search button:hover { background:#1e40af; }
    .lrn-clear { font-size:12.5px; font-weight:600; color:var(--slate-500); text-decoration:none; align-self:center; }
    .lrn-clear:hover { color:#2563eb; }

    .lrn-table-wrap { overflow-x:auto; border:1px solid var(--border-default); border-radius:12px; }
    table.lrn-table { width:100%; border-collapse:collapse; font-size:13.5px; min-width:620px; }
    .lrn-table th { text-align:left; font-size:11px; letter-spacing:.06em; text-transform:uppercase; font-weight:700;
        color:var(--slate-500); padding:11px 14px; background:var(--slate-50); border-bottom:1px solid var(--border-default); white-space:nowrap; }
    .lrn-table td { padding:12px 14px; border-bottom:1px solid var(--border-default); color:var(--slate-900); vertical-align:middle; }
    .lrn-table tbody tr:last-child td { border-bottom:0; }
    .lrn-table tbody tr:hover td { background:var(--slate-50); }
    .lrn-name { font-weight:600; }
    .lrn-muted { color:var(--slate-400); }
    .lrn-email { font-size:12px; color:var(--slate-500); display:block; margin-top:2px; }
    .lrn-badge { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:999px; font-size:11.5px; font-weight:700; white-space:nowrap; }
    .lrn-badge::before { content:''; width:6px; height:6px; border-radius:50%; background:currentColor; }
    .lrn-badge.avail   { background:rgba(22,163,74,.12); color:#15803d; }
    .lrn-badge.claimed { background:rgba(37,99,235,.12); color:#1d4ed8; }
    .lrn-grade { display:inline-block; min-width:28px; text-align:center; padding:2px 8px; border-radius:7px;
        background:var(--slate-100); color:var(--slate-700); font-weight:700; font-size:12.5px; }
    .lrn-del { border:1px solid rgba(220,38,38,.25); background:transparent; color:#dc2626; font-weight:700; font-size:12px;
        cursor:pointer; padding:5px 11px; border-radius:8px; transition:background .15s; }
    .lrn-del:hover { background:rgba(220,38,38,.08); }
    .lrn-mono { font-family:ui-monospace, Consolas, monospace; font-weight:700; letter-spacing:.5px; color:var(--slate-900); }

    .lrn-empty { text-align:center; padding:40px 16px !important; }
    .lrn-empty svg { width:40px; height:40px; color:var(--slate-400); margin-bottom:8px; }
    .lrn-empty b { display:block; color:var(--slate-700); font-size:14px; margin-bottom:3px; }
    .lrn-empty span { color:var(--slate-500); font-size:12.5px; }

    .lrn-alert { border-radius:12px; padding:12px 14px; font-size:13.5px; margin-bottom:14px; display:flex; gap:10px; align-items:flex-start; font-weight:500; }
    .lrn-alert svg { width:18px; height:18px; flex-shrink:0; margin-top:1px; }
    .lrn-alert.ok  { background:rgba(22,163,74,.1); color:#15803d; border:1px solid rgba(22,163,74,.3); }
    .lrn-alert.err { background:rgba(220,38,38,.08); color:#b91c1c; border:1px solid rgba(220,38,38,.3); }
    [data-theme="dark"] .lrn-alert.ok  { color:#4ade80; }
    [data-theme="dark"] .lrn-alert.err { color:#f87171; }
    [data-theme="dark"] .lrn-badge.avail   { color:#4ade80; }
    [data-theme="dark"] .lrn-badge.claimed { color:#93c5fd; }

    .lrn-pages { margin-top:14px; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; }
    .lrn-pages .info { font-size:12.5px; color:var(--slate-500); }

    @media (max-width: 960px) { .lrn-grid { grid-template-columns:1fr; } }
    @media (max-width: 640px) { .lrn-stats { grid-template-columns:1fr; } }
</style>

<div class="lrn-page">
    <div class="lrn-head">
        <div class="lrn-title">
            <h1>LRN Master List</h1>
            <p>The official list of Learner Reference Numbers. Students can only activate an account when their LRN and name match an entry here.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="lrn-alert ok" role="status">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if ($errors->any())
        <div class="lrn-alert err" role="alert">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="lrn-stats">
        <div class="lrn-stat">
            <div class="lrn-stat-ic ic-blue"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
            <div><b>{{ number_format($total) }}</b><span>Total LRNs</span></div>
        </div>
        <div class="lrn-stat">
            <div class="lrn-stat-ic ic-green"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg></div>
            <div><b>{{ number_format($available) }}</b><span>Available — not yet activated</span></div>
        </div>
        <div class="lrn-stat">
            <div class="lrn-stat-ic ic-indigo"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg></div>
            <div><b>{{ number_format($claimed) }}</b><span>Claimed — account activated</span></div>
        </div>
    </div>

    <div class="lrn-grid">
        {{-- LEFT: Add forms --}}
        <div>
            <div class="lrn-card" style="margin-bottom:16px;">
                <h3><svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg> Add Single LRN</h3>
                <p class="lrn-sub">For a new student or a transferee.</p>
                <form method="POST" action="{{ route('admin.lrns.store') }}">
                    @csrf
                    <label for="lrnInput">LRN <span class="opt">(12 digits)</span></label>
                    <input id="lrnInput" class="lrn-input lrn-mono" type="text" name="lrn" value="{{ old('lrn') }}" maxlength="12"
                           inputmode="numeric" pattern="\d{12}" placeholder="123456789012" autocomplete="off" required>
                    <div class="lrn-count"><span>Numbers only</span><span id="lrnCount">0 / 12</span></div>

                    <label for="lrnName">Student Name</label>
                    <input id="lrnName" class="lrn-input" type="text" name="student_name" value="{{ old('student_name') }}" placeholder="Dela Cruz Juan" autocomplete="off" required>

                    <label for="lrnGrade">Grade Level <span class="opt">(optional)</span></label>
                    <select id="lrnGrade" name="grade_level">
                        <option value="">— Not set —</option>
                        @foreach (['7','8','9','10','11','12'] as $g)
                            <option value="{{ $g }}" {{ old('grade_level') === $g ? 'selected' : '' }}>Grade {{ $g }}</option>
                        @endforeach
                    </select>

                    <button class="lrn-btn" type="submit">
                        <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Add LRN
                    </button>
                </form>
                <p class="lrn-hint">Once added, the student can activate their account using this LRN and name.</p>
            </div>

            <div class="lrn-card">
                <h3><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg> Bulk Import</h3>
                <p class="lrn-sub">Paste many students at once — one per line.</p>
                <form method="POST" action="{{ route('admin.lrns.bulk') }}">
                    @csrf
                    <label for="lrnBulk">Student list</label>
                    <textarea id="lrnBulk" name="bulk" placeholder="123456789013,Dela Cruz Juan&#10;123456789014,Santos Maria,11" required>{{ old('bulk') }}</textarea>
                    <div class="lrn-count"><span>One student per line</span><span id="bulkCount">0 lines</span></div>
                    <button class="lrn-btn gold" type="submit">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import All
                    </button>
                </form>
                <p class="lrn-hint">
                    Formats: <code>LRN,Name</code> or <code>LRN,Name,Grade</code>. The name is required (it is matched during activation).
                    Don't put commas inside the name. Duplicates and invalid lines are skipped automatically.
                    You can copy and paste directly from an Excel column.
                </p>
            </div>
        </div>

        {{-- RIGHT: List --}}
        <div class="lrn-card">
            <div class="lrn-list-head">
                <form method="GET" class="lrn-search" role="search">
                    <div class="box">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                        <input type="text" name="q" value="{{ $q }}" placeholder="Search by LRN or name…" aria-label="Search by LRN or name">
                    </div>
                    <select name="status" onchange="this.form.submit()" aria-label="Filter by status">
                        <option value="all"       {{ $status==='all' ? 'selected' : '' }}>All statuses</option>
                        <option value="available" {{ $status==='available' ? 'selected' : '' }}>Available</option>
                        <option value="claimed"   {{ $status==='claimed' ? 'selected' : '' }}>Claimed</option>
                    </select>
                    <button type="submit">Search</button>
                    @if ($q !== '' || $status !== 'all')
                        <a class="lrn-clear" href="{{ route('admin.lrns.index') }}">Clear filters</a>
                    @endif
                </form>
            </div>

            <div class="lrn-table-wrap">
                <table class="lrn-table">
                    <thead>
                        <tr>
                            <th>LRN</th>
                            <th>Name (master list)</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th>Activated Account</th>
                            <th><span style="position:absolute;left:-9999px;">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lrns as $row)
                            <tr>
                                <td class="lrn-mono">{{ $row->lrn }}</td>
                                <td class="lrn-name">{!! $row->student_name ? e($row->student_name) : '<span class="lrn-muted">—</span>' !!}</td>
                                <td>{!! $row->grade_level ? '<span class="lrn-grade">'.e($row->grade_level).'</span>' : '<span class="lrn-muted">—</span>' !!}</td>
                                <td>
                                    @if ($row->claimed_by)
                                        <span class="lrn-badge claimed">Claimed</span>
                                    @else
                                        <span class="lrn-badge avail">Available</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($row->claimedBy)
                                        {{ $row->claimedBy->name }}
                                        <span class="lrn-email">{{ $row->claimedBy->email }}</span>
                                    @else
                                        <span class="lrn-muted">—</span>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    @if (!$row->claimed_by)
                                        <form method="POST" action="{{ route('admin.lrns.destroy', $row->id) }}"
                                              onsubmit="return confirm('Remove LRN {{ $row->lrn }} from the list?');">
                                            @csrf @method('DELETE')
                                            <button class="lrn-del" type="submit">Remove</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="lrn-empty">
                                <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                                @if ($q !== '' || $status !== 'all')
                                    <b>No matching LRNs</b>
                                    <span>Try a different search or clear the filters.</span>
                                @else
                                    <b>No LRNs yet</b>
                                    <span>Add one with the form on the left, or bulk import your class list.</span>
                                @endif
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($lrns->total() > 0)
                <div class="lrn-pages">
                    <span class="info">Showing {{ $lrns->firstItem() }}–{{ $lrns->lastItem() }} of {{ number_format($lrns->total()) }}</span>
                    {{ $lrns->withQueryString()->links('pagination::default') }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    // Live helpers only — validation still happens on the server
    var lrn = document.getElementById('lrnInput'), lc = document.getElementById('lrnCount');
    if (lrn && lc) {
        var upd = function () {
            var clean = lrn.value.replace(/\D/g, '').slice(0, 12);
            if (clean !== lrn.value) lrn.value = clean;
            lc.textContent = clean.length + ' / 12';
            lc.className = clean.length === 12 ? 'ok' : '';
        };
        lrn.addEventListener('input', upd); upd();
    }
    var bulk = document.getElementById('lrnBulk'), bc = document.getElementById('bulkCount');
    if (bulk && bc) {
        var updB = function () {
            var n = bulk.value.split(/\r?\n/).filter(function (l) { return l.trim() !== ''; }).length;
            bc.textContent = n + (n === 1 ? ' line' : ' lines');
        };
        bulk.addEventListener('input', updB); updB();
    }
})();
</script>
@endsection
