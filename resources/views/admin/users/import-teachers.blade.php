@extends('layouts.admin')
@section('title', 'Import Teachers')

@section('content')
@php
    $result  = session('import_result');
    $okRows  = $result ? collect($result['results'])->where('ok', true)->values() : collect();
    $badRows = $result ? collect($result['results'])->where('ok', false)->values() : collect();
    $open    = $sections->whereNull('adviser_id');
@endphp
<style>
    /* Uses admin layout tokens so dark mode works */
    .it-page { animation: it-fade .22s ease both; max-width:1100px; }
    @keyframes it-fade { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }

    .it-back { display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:var(--slate-500); text-decoration:none; margin-bottom:10px; }
    .it-back:hover { color:#2563eb; }
    .it-back svg { width:15px; height:15px; }
    .it-title { font-size:22px; font-weight:800; color:var(--slate-900); margin:0; letter-spacing:-0.01em; }
    .it-sub { font-size:13px; color:var(--slate-500); margin:4px 0 18px; max-width:720px; line-height:1.55; }

    .it-steps { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:12px; margin-bottom:18px; }
    .it-step { background:var(--white); border:1px solid var(--border-default); border-radius:14px; padding:14px 16px; display:flex; gap:12px; }
    .it-step i { font-style:normal; width:28px; height:28px; border-radius:999px; background:rgba(37,99,235,.12); color:#1d4ed8; display:grid; place-items:center; font-weight:800; font-size:13px; flex-shrink:0; }
    [data-theme="dark"] .it-step i { color:#93c5fd; }
    .it-step b { display:block; font-size:13.5px; color:var(--slate-900); margin-bottom:2px; }
    .it-step span { font-size:12.5px; color:var(--slate-500); line-height:1.5; }

    .it-card { background:var(--white); border:1px solid var(--border-default); border-radius:16px; padding:18px; box-shadow:var(--shadow-sm); margin-bottom:18px; }
    .it-card h3 { margin:0 0 4px; font-size:15px; font-weight:800; color:var(--slate-900); }
    .it-card .muted { font-size:12.5px; color:var(--slate-500); margin:0 0 12px; line-height:1.55; }
    .it-grid { display:grid; grid-template-columns:minmax(0,1.4fr) minmax(0,1fr); gap:18px; align-items:start; }

    .it-ta { width:100%; min-height:220px; padding:12px; border:1.5px solid var(--border-default); border-radius:12px; box-sizing:border-box;
        font-family:ui-monospace, Consolas, monospace; font-size:13px; line-height:1.6; color:var(--slate-900); background:var(--white); resize:vertical; }
    .it-ta:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
    .it-count { display:flex; justify-content:space-between; font-size:12px; color:var(--slate-500); margin-top:6px; }
    .it-btn { display:inline-flex; align-items:center; justify-content:center; gap:7px; height:42px; padding:0 18px; border:0; border-radius:10px;
        background:#1d4ed8; color:#fff; font-size:13.5px; font-weight:700; cursor:pointer; transition:background .15s; text-decoration:none; }
    .it-btn:hover { background:#1e40af; }
    .it-btn svg { width:16px; height:16px; }
    .it-btn.ghost { background:transparent; color:var(--slate-700); border:1px solid var(--border-default); }
    .it-btn.ghost:hover { border-color:#2563eb; color:#2563eb; }
    .it-btn.sm { height:32px; padding:0 12px; font-size:12.5px; }
    .it-err { color:#dc2626; font-size:12.5px; margin-top:6px; font-weight:600; }

    .it-fmt { font-size:12.5px; color:var(--slate-700); line-height:1.7; }
    .it-fmt code { background:var(--slate-100); color:var(--slate-900); padding:1px 6px; border-radius:5px; font-size:12px; }
    .it-codes { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }
    .it-code { border:1px dashed var(--border-default); background:var(--slate-50); color:var(--slate-700); border-radius:8px; padding:4px 9px;
        font-family:ui-monospace, Consolas, monospace; font-size:12px; cursor:pointer; }
    .it-code:hover { border-color:#2563eb; color:#2563eb; }
    .it-code.taken { opacity:.55; text-decoration:line-through; cursor:default; }

    .it-alert { border-radius:12px; padding:12px 14px; font-size:13px; margin-bottom:12px; font-weight:500; line-height:1.5; }
    .it-alert.warn { background:rgba(245,158,11,.12); color:#92400e; border:1px solid rgba(245,158,11,.35); }
    [data-theme="dark"] .it-alert.warn { color:#fbbf24; }

    .it-table { width:100%; border-collapse:collapse; }
    .it-table th { text-align:left; font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--slate-500);
        padding:10px 12px; background:var(--slate-50); border-bottom:1px solid var(--border-default); }
    .it-table td { padding:10px 12px; border-bottom:1px solid var(--border-default); font-size:13px; color:var(--slate-700); vertical-align:middle; }
    .it-table tr:last-child td { border-bottom:0; }
    .it-name { font-weight:700; color:var(--slate-900); }
    .it-link { font-family:ui-monospace, Consolas, monospace; font-size:11.5px; color:var(--slate-700); word-break:break-all; }
    .it-note { font-size:12px; color:#b45309; margin-top:2px; }
    [data-theme="dark"] .it-note { color:#fbbf24; }
    .it-skip td { color:var(--slate-500); }
    .it-tag { display:inline-block; padding:2px 8px; border-radius:7px; font-size:12px; font-weight:600; background:rgba(37,99,235,.10); color:#1d4ed8; }
    [data-theme="dark"] .it-tag { color:#93c5fd; }
    .it-exp { font-size:12px; color:var(--slate-500); }
    .it-exp.expired { color:#dc2626; font-weight:700; }
    .it-scroll { overflow-x:auto; border:1px solid var(--border-default); border-radius:12px; }

    @media (max-width: 900px) { .it-grid, .it-steps { grid-template-columns:1fr; } }

    /* Print only the activation-link sheet */
    @media print {
        body * { visibility:hidden !important; }
        #itPrint, #itPrint * { visibility:visible !important; }
        #itPrint { position:absolute; left:0; top:0; width:100%; border:0; box-shadow:none; }
        #itPrint .no-print { display:none !important; }
        .it-link { color:#000; font-size:11px; }
    }
</style>

<div class="it-page">
    <a href="{{ route('admin.users.index', ['tab' => 'teachers']) }}" class="it-back">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Back to Teachers
    </a>
    <h1 class="it-title">Import Teachers</h1>
    <p class="it-sub">Create all teacher accounts at once — no email addresses needed. Each teacher gets a personal activation link
        where they enter their own email and password. Advisers are linked to their section right away.</p>

    <div class="it-steps">
        <div class="it-step"><i>1</i><div><b>Paste the faculty list</b><span>One teacher per line, with their advisory section if they have one.</span></div></div>
        <div class="it-step"><i>2</i><div><b>Hand out the links</b><span>Copy or print the activation links and give each teacher their own (e.g. at the faculty meeting).</span></div></div>
        <div class="it-step"><i>3</i><div><b>Teacher activates</b><span>They open the link, enter their email and a password, then log in. A code is emailed on first login.</span></div></div>
    </div>

    {{-- ── Results (links are shown only once) ── --}}
    @if($result)
        <div class="it-card" id="itPrint">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap; margin-bottom:10px;">
                <div>
                    <h3>Activation links</h3>
                    <p class="muted" style="margin:0;">Sto. Domingo National High School · DP-LMS · valid for {{ \App\Services\TeacherRosterService::LINK_DAYS }} days</p>
                </div>
                @if($okRows->isNotEmpty())
                    <div class="no-print" style="display:flex; gap:8px;">
                        <button type="button" class="it-btn ghost sm" id="itCopyAll">Copy all</button>
                        <button type="button" class="it-btn sm" onclick="window.print()">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Print
                        </button>
                    </div>
                @endif
            </div>

            @if($okRows->isNotEmpty())
                <div class="it-alert warn no-print">
                    <b>Save these links now.</b> For security they are shown only once. If a link is lost, use “New link” below to make another.
                </div>
                <div class="it-scroll">
                    <table class="it-table">
                        <thead><tr><th>Teacher</th><th>Advisory</th><th>Activation link</th><th class="no-print"></th></tr></thead>
                        <tbody>
                            @foreach($okRows as $r)
                                <tr>
                                    <td><div class="it-name">{{ $r['name'] }}</div>@if($r['note'])<div class="it-note">{{ $r['note'] }}</div>@endif</td>
                                    <td>@if($r['advisory'] ?? null)<span class="it-tag">{{ $r['advisory'] }}</span>@else<span class="it-exp">—</span>@endif</td>
                                    <td><span class="it-link js-link">{{ $r['link'] }}</span></td>
                                    <td class="no-print"><button type="button" class="it-btn ghost sm js-copy" data-link="{{ $r['link'] }}">Copy</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($badRows->isNotEmpty())
                <h3 class="no-print" style="margin-top:16px;">Skipped ({{ $badRows->count() }})</h3>
                <div class="it-scroll no-print" style="margin-top:8px;">
                    <table class="it-table">
                        <tbody>
                            @foreach($badRows as $r)
                                <tr class="it-skip"><td style="width:70px;">Line {{ $r['line'] }}</td><td class="it-name">{{ $r['name'] }}</td><td><div class="it-note">{{ $r['note'] }}</div></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    {{-- ── Import form ── --}}
    <div class="it-grid">
        <div class="it-card">
            <h3>Faculty list</h3>
            <p class="muted">One teacher per line. Lines with errors are skipped and listed — the rest are still created.</p>
            <form method="POST" action="{{ route('admin.users.import-teachers.store') }}" id="itForm">
                @csrf
                <textarea name="roster" id="itRoster" class="it-ta" required spellcheck="false"
                          placeholder="Jonalyn L. Gutierrez, 7-GUTIERREZ&#10;Ricardo C. Sison, Jr., 11-SMAW WORKER&#10;Joey G. Centino&#10;Maria Rose A. Secretario, 11-ABM INNOVATOR, 4567890">{{ old('roster') }}</textarea>
                @error('roster')<div class="it-err">{{ $message }}</div>@enderror
                <div class="it-count"><span>Duplicates and existing teachers are skipped automatically.</span><span id="itCount">0 teachers</span></div>
                <div style="margin-top:14px;">
                    <button type="submit" class="it-btn" id="itSubmit">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Create accounts &amp; links
                    </button>
                </div>
            </form>
        </div>

        <div class="it-card">
            <h3>Line format</h3>
            <div class="it-fmt">
                <code>Name</code> — no advisory class<br>
                <code>Name, Advisory</code><br>
                <code>Name, Advisory, Employee ID</code><br>
                Advisory is the grade and section, e.g. <code>7-GUTIERREZ</code> or <code>Grade 11 - SMAW WORKER</code>.
                A suffix like <code>, Jr.</code> right after the name is kept with the name.
            </div>
            <p class="muted" style="margin:14px 0 0;"><b style="color:var(--slate-700);">Sections without an adviser</b> — click to copy:</p>
            <div class="it-codes">
                @forelse($open as $s)
                    <button type="button" class="it-code js-copy" data-link="{{ $s->grade_level }}-{{ $s->name }}">{{ $s->grade_level }}-{{ $s->name }}</button>
                @empty
                    <span class="it-exp">Every active section already has an adviser.</span>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Teachers who have not activated yet ── --}}
    <div class="it-card">
        <h3>Waiting for activation ({{ $awaiting->count() }})</h3>
        <p class="muted">These teachers have an account but have not opened their link yet. Make a new link if theirs was lost or expired — the old one stops working.</p>
        @if($awaiting->isEmpty())
            <span class="it-exp">No teachers are waiting — everyone imported has activated.</span>
        @else
            <div class="it-scroll">
                <table class="it-table">
                    <thead><tr><th>Teacher</th><th>Advisory</th><th>Link expires</th><th></th></tr></thead>
                    <tbody>
                        @foreach($awaiting as $t)
                            @php $sec = $advisoryOf[$t->id] ?? null; $expired = $t->invite_expires_at && $t->invite_expires_at->isPast(); @endphp
                            <tr>
                                <td><div class="it-name">{{ $t->name }}</div>@if($t->employee_id)<div class="it-exp">ID {{ $t->employee_id }}</div>@endif</td>
                                <td>@if($sec)<span class="it-tag">Grade {{ $sec->grade_level }} – {{ $sec->name }}</span>@else<span class="it-exp">—</span>@endif</td>
                                <td><span class="it-exp {{ $expired ? 'expired' : '' }}">{{ $t->invite_expires_at ? ($expired ? 'Expired ' : '') . $t->invite_expires_at->format('M j, Y') : '—' }}</span></td>
                                <td style="text-align:right;">
                                    <form method="POST" action="{{ route('admin.users.activation-link', $t) }}" style="margin:0;"
                                          data-msg="Make a new activation link for {{ $t->name }}? Their old link will stop working."
                                          onsubmit="return confirm(this.dataset.msg);">
                                        @csrf
                                        <button type="submit" class="it-btn ghost sm">New link</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<script>
(function () {
    // Copy to clipboard, with a fallback for plain-http LAN addresses (no navigator.clipboard)
    function copy(text, btn) {
        function ok() { if (!btn) return; var t = btn.textContent; btn.textContent = 'Copied!'; setTimeout(function () { btn.textContent = t; }, 1200); }
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(ok, function () { legacy(text) && ok(); });
        } else if (legacy(text)) { ok(); }
    }
    function legacy(text) {
        var ta = document.createElement('textarea');
        ta.value = text; ta.setAttribute('readonly', ''); ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta); ta.select();
        var done = false; try { done = document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
        if (!done) window.prompt('Copy this:', text);
        return true;
    }

    document.querySelectorAll('.js-copy').forEach(function (b) {
        b.addEventListener('click', function () { copy(b.dataset.link, b); });
    });

    var all = document.getElementById('itCopyAll');
    if (all) all.addEventListener('click', function () {
        var lines = [];
        document.querySelectorAll('#itPrint tbody tr').forEach(function (tr) {
            var name = tr.querySelector('.it-name'), link = tr.querySelector('.js-link');
            if (name && link) lines.push(name.textContent.trim() + ' — ' + link.textContent.trim());
        });
        copy(lines.join('\n'), all);
    });

    // Live count of non-empty lines
    var ta = document.getElementById('itRoster'), cnt = document.getElementById('itCount');
    if (ta && cnt) {
        var upd = function () {
            var n = ta.value.split(/\r?\n/).filter(function (l) { return l.trim() !== ''; }).length;
            cnt.textContent = n + (n === 1 ? ' teacher' : ' teachers');
        };
        ta.addEventListener('input', upd); upd();
    }

    // Prevent double submit
    var f = document.getElementById('itForm');
    if (f) f.addEventListener('submit', function () {
        var b = document.getElementById('itSubmit'); b.disabled = true; b.textContent = 'Creating…';
    });
})();
</script>
@endsection
