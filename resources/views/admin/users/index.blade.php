@extends('layouts.admin')
@section('title', 'User Management')

@section('content')
@php
    $tabs = [
        'students' => ['label' => 'Students', 'count' => $students->total(), 'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0v6m-6-9.5V16c3 2 9 2 12 0v-5.5'],
        'teachers' => ['label' => 'Teachers', 'count' => $teachers->total(), 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        'parents'  => ['label' => 'Parents',  'count' => $parents->total(),  'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
    ];
    $roleOf = ['students' => 'student', 'teachers' => 'teacher', 'parents' => 'parent'];
@endphp
<style>
    /* Uses admin layout tokens (--white, --slate-*, --border-default, --shadow-*) so dark mode works */
    .um-page { animation: um-fade .22s ease both; }
    @keyframes um-fade { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }

    /* Header */
    .um-head { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:18px; }
    .um-head-l { display:flex; align-items:center; gap:14px; min-width:0; }
    .um-head-ic { width:46px; height:46px; border-radius:14px; display:grid; place-items:center; color:#fff; flex-shrink:0;
        background:linear-gradient(135deg, var(--blue-500), var(--blue-700)); box-shadow:0 6px 16px -4px rgba(36,120,228,.5); }
    .um-head-ic svg { width:22px; height:22px; }
    .um-title { font-size:22px; font-weight:800; color:var(--slate-900); letter-spacing:-0.01em; margin:0; }
    .um-subtitle { font-size:13px; color:var(--slate-500); margin:2px 0 0; }
    .um-add { display:inline-flex; align-items:center; gap:8px; height:40px; padding:0 16px; border-radius:10px; background:#1d4ed8; color:#fff;
        font-size:13.5px; font-weight:700; text-decoration:none; box-shadow:0 6px 14px -6px rgba(29,78,216,.6); transition:background .15s, transform .1s; }
    .um-add:hover { background:#1e40af; }
    .um-add:active { transform:translateY(1px); }
    .um-add svg { width:16px; height:16px; }

    /* Tabs */
    .um-tabs { display:flex; gap:6px; padding:5px; background:var(--slate-100); border-radius:14px; width:fit-content; max-width:100%; overflow-x:auto; margin-bottom:14px; }
    .um-tab { display:inline-flex; align-items:center; gap:8px; padding:9px 16px; border-radius:10px; font-size:13.5px; font-weight:600;
        color:var(--slate-500); text-decoration:none; white-space:nowrap; transition:background .15s, color .15s, box-shadow .15s; }
    .um-tab svg { width:16px; height:16px; }
    .um-tab:hover { color:var(--slate-900); }
    .um-tab.active { background:var(--white); color:var(--slate-900); box-shadow:var(--shadow-sm); }
    .um-tab-badge { min-width:22px; text-align:center; padding:1px 7px; border-radius:999px; font-size:11.5px; font-weight:800;
        background:var(--slate-200); color:var(--slate-700); }
    .um-tab.active .um-tab-badge { background:rgba(37,99,235,.12); color:#1d4ed8; }
    .um-tab-pend { padding:1px 7px; border-radius:999px; font-size:11px; font-weight:800; background:rgba(245,158,11,.18); color:#b45309; }
    [data-theme="dark"] .um-tab.active .um-tab-badge { color:#93c5fd; }
    [data-theme="dark"] .um-tab-pend { color:#fbbf24; }

    /* Toolbar */
    .um-toolbar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
    .um-search { position:relative; flex:1; min-width:220px; max-width:420px; }
    .um-search svg { position:absolute; left:12px; top:50%; transform:translateY(-50%); width:16px; height:16px; color:var(--slate-400); pointer-events:none; }
    .um-search input { width:100%; height:40px; padding:0 12px 0 36px; border:1.5px solid var(--border-default); border-radius:10px;
        font-size:13.5px; color:var(--slate-900); background:var(--white); box-sizing:border-box; transition:border-color .15s, box-shadow .15s; }
    .um-select { height:40px; padding:0 12px; border:1.5px solid var(--border-default); border-radius:10px; font-size:13.5px;
        color:var(--slate-900); background:var(--white); cursor:pointer; }
    .um-search input:focus, .um-select:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
    .um-go { height:40px; padding:0 18px; border:0; border-radius:10px; background:#1d4ed8; color:#fff; font-size:13.5px; font-weight:700; cursor:pointer; }
    .um-go:hover { background:#1e40af; }
    .um-reset { font-size:13px; font-weight:600; color:var(--slate-500); text-decoration:none; padding:0 6px; }
    .um-reset:hover { color:#2563eb; }

    /* Grade / section chips */
    .um-chips { display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-bottom:10px; }
    .um-chips-label { font-size:11px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; color:var(--slate-500); margin-right:4px; min-width:56px; }
    .um-chip-btn { display:inline-flex; align-items:center; height:32px; padding:0 13px; border-radius:999px; border:1px solid var(--border-default);
        background:var(--white); color:var(--slate-700); font-size:12.5px; font-weight:600; text-decoration:none; transition:border-color .15s, color .15s, background .15s; }
    .um-chip-btn.sm { height:28px; padding:0 11px; font-size:12px; }
    .um-chip-btn:hover { border-color:#2563eb; color:#2563eb; }
    .um-chip-btn.on { background:#1d4ed8; border-color:#1d4ed8; color:#fff; }

    /* Table */
    .um-card { background:var(--surface-card); border:1px solid var(--border-default); border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; margin-top:6px; }
    .um-scroll { overflow-x:auto; }
    .um-table { width:100%; border-collapse:collapse; min-width:860px; }
    .um-table th { text-align:left; padding:12px 16px; font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase;
        color:var(--slate-500); background:var(--slate-50); border-bottom:1px solid var(--border-default); white-space:nowrap; }
    .um-table td { padding:12px 16px; font-size:13.5px; color:var(--slate-700); border-bottom:1px solid var(--border-default); vertical-align:middle; }
    .um-table tbody tr:last-child td { border-bottom:0; }
    .um-table tbody tr:hover td { background:var(--slate-50); }
    .um-table .r { text-align:right; }

    .um-person { display:flex; align-items:center; gap:12px; min-width:0; }
    .um-avatar { width:38px; height:38px; border-radius:12px; flex-shrink:0; display:grid; place-items:center; color:#fff; font-size:13px; font-weight:800; letter-spacing:.02em; }
    .um-person-txt { min-width:0; }
    .um-name { font-weight:700; color:var(--slate-900); font-size:13.5px; line-height:1.3; }
    .um-email { font-size:12.5px; color:var(--slate-500); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:260px; }
    .um-strong { font-weight:600; color:var(--slate-900); font-size:13px; }
    .um-sub { font-size:12px; color:var(--slate-500); margin-top:1px; }
    .um-mono { font-family:ui-monospace, Consolas, monospace; font-size:12.5px; color:var(--slate-700); letter-spacing:.3px; }
    .um-dim { color:var(--slate-400); font-size:12.5px; }
    .um-warn-txt { color:#b45309; }
    [data-theme="dark"] .um-warn-txt { color:#fbbf24; }
    .um-date { font-size:12.5px; color:var(--slate-500); white-space:nowrap; }
    .um-tag { display:inline-block; padding:3px 9px; border-radius:7px; font-size:12px; font-weight:600; background:var(--slate-100); color:var(--slate-700); margin:1px 4px 1px 0; }
    .um-tag-blue { background:rgba(37,99,235,.10); color:#1d4ed8; }
    [data-theme="dark"] .um-tag-blue { color:#93c5fd; }

    .um-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:700; border:1px solid; white-space:nowrap; }
    .um-badge::before { content:''; width:6px; height:6px; border-radius:50%; background:currentColor; }
    .um-badge-pending  { background:rgba(245,158,11,.12); color:#b45309; border-color:rgba(245,158,11,.35); }
    .um-badge-approved { background:rgba(16,185,129,.12); color:#047857; border-color:rgba(16,185,129,.35); }
    .um-badge-rejected { background:rgba(239,68,68,.10); color:#b91c1c; border-color:rgba(239,68,68,.35); }
    [data-theme="dark"] .um-badge-pending  { color:#fbbf24; }
    [data-theme="dark"] .um-badge-approved { color:#34d399; }
    [data-theme="dark"] .um-badge-rejected { color:#f87171; }

    .um-row-actions { display:inline-flex; gap:6px; justify-content:flex-end; }
    .um-icon-btn { width:34px; height:34px; display:inline-grid; place-items:center; border-radius:9px; border:1px solid var(--border-default);
        background:var(--white); color:var(--slate-500); cursor:pointer; transition:border-color .15s, color .15s, background .15s; }
    .um-icon-btn svg { width:16px; height:16px; }
    .um-btn-view:hover, .um-btn-link:hover { border-color:#2563eb; color:#2563eb; }
    .um-btn-link { color:#b45309; border-color:rgba(217,119,6,.35); }
    .um-btn-approve { color:#16a34a; border-color:rgba(22,163,74,.35); }
    .um-btn-approve:hover { background:rgba(22,163,74,.10); }
    .um-btn-reject  { color:#dc2626; border-color:rgba(220,38,38,.35); }
    .um-btn-reject:hover  { background:rgba(220,38,38,.08); }

    .um-empty { text-align:center; padding:52px 20px; color:var(--slate-500); }
    .um-empty svg { width:40px; height:40px; color:var(--slate-400); margin-bottom:10px; }
    .um-empty b { display:block; font-size:14.5px; color:var(--slate-700); margin-bottom:3px; }
    .um-empty span { font-size:13px; }

    .um-pager { display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; padding:12px 16px;
        border-top:1px solid var(--border-default); background:var(--slate-50); }
    .um-pager-info { font-size:12.5px; color:var(--slate-500); }

    /* Loading skeleton (AJAX) */
    .um-skel { background:linear-gradient(90deg, var(--slate-200) 25%, var(--slate-100) 50%, var(--slate-200) 75%);
        background-size:200% 100%; animation:skeletonShimmer 1.2s infinite; border-radius:6px; }
    #um-ajax-content { transition:opacity .15s ease; }
    #um-ajax-content.loading { opacity:.55; pointer-events:none; }

    /* Confirm modal */
    .um-confirm-backdrop { position:fixed; inset:0; background:rgba(15,23,42,.45); display:flex; align-items:center; justify-content:center;
        z-index:1000; opacity:0; pointer-events:none; transition:opacity .18s ease; padding:16px; }
    .um-confirm-backdrop.open { opacity:1; pointer-events:auto; }
    .um-confirm-box { width:min(420px, 100%); background:var(--white); border-radius:18px; padding:24px; box-shadow:var(--shadow-xl);
        transform:translateY(8px) scale(.98); transition:transform .18s ease; text-align:center; }
    .um-confirm-backdrop.open .um-confirm-box { transform:none; }
    .um-confirm-icon { width:52px; height:52px; border-radius:999px; margin:0 auto 12px; display:grid; place-items:center; }
    .um-confirm-icon svg { width:24px; height:24px; }
    .um-confirm-icon.approve { background:rgba(22,163,74,.12); color:#16a34a; }
    .um-confirm-icon.reject  { background:rgba(220,38,38,.10); color:#dc2626; }
    .um-confirm-title { font-size:17px; font-weight:800; color:var(--slate-900); margin-bottom:6px; }
    .um-confirm-desc { font-size:13.5px; color:var(--slate-500); line-height:1.55; margin:0 0 18px; }
    .um-confirm-actions { display:flex; gap:10px; }
    .um-confirm-actions button { flex:1; height:42px; border-radius:10px; font-size:13.5px; font-weight:700; cursor:pointer; }
    .um-confirm-cancel { background:transparent; border:1px solid var(--border-default); color:var(--slate-700); }
    .um-confirm-cancel:hover { background:var(--slate-50); }
    .um-confirm-proceed { border:0; color:#fff; }
    .um-confirm-proceed.approve { background:#16a34a; }
    .um-confirm-proceed.reject  { background:#dc2626; }
    .um-confirm-proceed:disabled { opacity:.7; cursor:wait; }

    /* Profile drawer */
    .um-overlay { position:fixed; inset:0; background:rgba(15,23,42,0); z-index:998; pointer-events:none; transition:background .25s ease; }
    .um-overlay.open { background:rgba(15,23,42,.38); pointer-events:auto; }
    .um-drawer { position:fixed; top:0; right:0; height:100vh; width:min(680px, 100vw); background:var(--white); box-shadow:-12px 0 40px rgba(0,0,0,.14);
        z-index:999; transform:translateX(100%); transition:transform .3s cubic-bezier(.22,1,.36,1); display:flex; flex-direction:column; }
    .um-drawer.open { transform:none; }
    .um-drawer-head { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--border-default); background:var(--slate-50); }
    .um-drawer-head b { font-size:14px; color:var(--slate-900); }
    .um-drawer-close { width:32px; height:32px; display:grid; place-items:center; border-radius:9px; border:1px solid var(--border-default);
        background:var(--white); color:var(--slate-500); cursor:pointer; }
    .um-drawer-close:hover { color:var(--slate-900); }
    .um-drawer-close svg { width:14px; height:14px; }
    .um-drawer-body { flex:1; overflow-y:auto; padding:20px; }
    .um-drawer-foot { padding:12px 20px; border-top:1px solid var(--border-default); background:var(--slate-50); display:none; }
    .um-drawer-foot a { display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:700; color:#2563eb; text-decoration:none; }
    .um-drawer-foot svg { width:14px; height:14px; }
    .um-drawer-msg { color:var(--slate-500); font-size:13.5px; padding:20px 4px; }
    .um-drawer-msg a { color:#2563eb; font-weight:700; }

    @media (max-width: 640px) {
        .um-head { align-items:flex-start; }
        .um-search { max-width:none; }
    }
</style>

<div class="um-page">

    <div class="um-head">
        <div class="um-head-l">
            <div class="um-head-ic" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <h1 class="um-title">User Management</h1>
                <p class="um-subtitle">Students, teachers and parents — search, review pending accounts, and open any profile.</p>
            </div>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('admin.users.import-teachers', [], false) }}" class="um-add" style="background:transparent; color:var(--slate-700); border:1px solid var(--border-default); box-shadow:none;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Import Teachers
            </a>
            <a href="{{ route('admin.users.create-teacher') }}" class="um-add" data-no-spa>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Add Teacher
            </a>
        </div>
    </div>

    {{-- Tabs (Students / Teachers / Parents) --}}
    <nav class="um-tabs" role="tablist" aria-label="User type">
        @foreach($tabs as $key => $t)
            @php $pend = (int) ($pendingCounts[$roleOf[$key]] ?? 0); @endphp
            <a href="{{ route('admin.users.index', array_merge(request()->only(['search', 'status']), ['tab' => $key])) }}"
               class="um-tab {{ $tab === $key ? 'active' : '' }}" role="tab" aria-selected="{{ $tab === $key ? 'true' : 'false' }}" data-tab="{{ $key }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['icon'] }}"/></svg>
                {{ $t['label'] }}
                <span class="um-tab-badge">{{ number_format($t['count']) }}</span>
                @if($pend)<span class="um-tab-pend" title="Waiting for approval">{{ $pend }} pending</span>@endif
            </a>
        @endforeach
    </nav>

    {{-- Search + status --}}
    <form method="GET" class="um-toolbar" id="umFilter" role="search">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <label class="um-search">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, LRN or mobile…" aria-label="Search users">
        </label>
        <select name="status" class="um-select" aria-label="Filter by status">
            <option value="">All statuses</option>
            <option value="pending"  @selected(request('status') === 'pending')>Pending</option>
            <option value="approved" @selected(request('status') === 'approved')>Approved</option>
            <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
        </select>
        <button type="submit" class="um-go">Search</button>
        <a href="{{ route('admin.users.index', ['tab' => $tab]) }}" class="um-reset" id="umReset">Reset</a>
    </form>

    <div id="um-ajax-content">
        @include('admin.users._partials.table')
    </div>
</div>

{{-- Approve / Reject confirmation --}}
<div class="um-confirm-backdrop" id="um-confirm-backdrop" role="dialog" aria-modal="true" aria-labelledby="um-confirm-title">
    <div class="um-confirm-box">
        <div class="um-confirm-icon" id="um-confirm-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d=""/></svg>
        </div>
        <div class="um-confirm-title" id="um-confirm-title"></div>
        <p class="um-confirm-desc" id="um-confirm-desc"></p>
        <div class="um-confirm-actions">
            <button type="button" class="um-confirm-cancel" id="um-confirm-cancel">Cancel</button>
            <button type="button" class="um-confirm-proceed" id="um-confirm-proceed"></button>
        </div>
    </div>
</div>
<form method="POST" id="um-action-form" style="display:none;">
    @csrf @method('PATCH')
</form>

{{-- Profile drawer --}}
<div class="um-overlay" id="up-overlay"></div>
<aside class="um-drawer" id="up-drawer" aria-label="User profile" aria-hidden="true">
    <div class="um-drawer-head">
        <b>User Profile</b>
        <button type="button" class="um-drawer-close" id="up-drawer-close" aria-label="Close">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <div class="um-drawer-body" id="up-drawer-body"></div>
    <div class="um-drawer-foot" id="up-drawer-footer">
        <a id="up-drawer-fullpage" href="#" data-no-spa>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Open full page
        </a>
    </div>
</aside>

<script>
(function () {
    'use strict';

    var AJAX_URL = @json(route('admin.users.index'));
    var state = {
        tab:        @json($tab),
        grade:      @json((string) request('grade', '')),
        section_id: @json((string) request('section_id', '')),
        search:     @json((string) request('search', '')),
        status:     @json((string) request('status', '')),
        pageKey:    '',
        pageVal:    ''
    };
    var $ = function (id) { return document.getElementById(id); };
    var busy = false;

    function query() {
        var p = new URLSearchParams();
        p.set('tab', state.tab);
        if (state.grade)      p.set('grade', state.grade);
        if (state.section_id) p.set('section_id', state.section_id);
        if (state.search)     p.set('search', state.search);
        if (state.status)     p.set('status', state.status);
        if (state.pageKey && state.pageVal) p.set(state.pageKey, state.pageVal);
        return p.toString();
    }

    // Swap only the table area (tabs/filters/pages) via the controller's JSON response
    function navigate(patch) {
        if (busy) return;
        Object.assign(state, patch);
        if (!('pageVal' in patch)) { state.pageKey = ''; state.pageVal = ''; } // any filter change -> page 1

        var qs = query(), area = $('um-ajax-content');
        history.pushState(null, '', '?' + qs);
        busy = true;
        area.classList.add('loading');

        fetch(AJAX_URL + '?' + qs, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function (data) {
                if (!data || typeof data.html !== 'string') throw new Error('Bad response');
                area.innerHTML = data.html;
                updateTabs(data.counts);
                var f = $('umFilter'); if (f) f.elements.tab.value = state.tab;
            })
            .catch(function () { window.location.href = '?' + qs; })
            .finally(function () { busy = false; area.classList.remove('loading'); });
    }

    function updateTabs(counts) {
        document.querySelectorAll('.um-tab').forEach(function (t) {
            var key = t.dataset.tab, on = key === state.tab;
            t.classList.toggle('active', on);
            t.setAttribute('aria-selected', on ? 'true' : 'false');
            var b = t.querySelector('.um-tab-badge');
            if (b && counts && counts[key] !== undefined) b.textContent = Number(counts[key]).toLocaleString();
        });
    }

    function plainClick(e) { return e.button === 0 && !e.ctrlKey && !e.metaKey && !e.shiftKey && !e.altKey; }

    // Tabs
    document.querySelectorAll('.um-tab').forEach(function (t) {
        t.addEventListener('click', function (e) {
            if (!plainClick(e)) return;
            e.preventDefault();
            navigate({ tab: t.dataset.tab, grade: '', section_id: '' });
        });
    });

    // Search / status
    $('umFilter').addEventListener('submit', function (e) {
        e.preventDefault();
        var fd = new FormData(e.target);
        navigate({ search: (fd.get('search') || '').toString().trim(), status: (fd.get('status') || '').toString() });
    });
    $('umFilter').elements.status.addEventListener('change', function () { $('umFilter').requestSubmit(); });
    $('umReset').addEventListener('click', function (e) {
        if (!plainClick(e)) return;
        e.preventDefault();
        var f = $('umFilter'); f.elements.search.value = ''; f.elements.status.value = '';
        navigate({ search: '', status: '', grade: '', section_id: '' });
    });

    // Delegated clicks inside the table area (grade/section chips + pagination) — survive AJAX swaps
    $('um-ajax-content').addEventListener('click', function (e) {
        if (!plainClick(e)) return;
        var a = e.target.closest('a.js-grade, a.js-section, .um-pager a[href]');
        if (!a) return;
        e.preventDefault();
        var u = new URL(a.href, window.location.origin);
        if (a.classList.contains('js-grade'))   return navigate({ grade: u.searchParams.get('grade') || '', section_id: '' });
        if (a.classList.contains('js-section')) return navigate({ section_id: u.searchParams.get('section_id') || '' });
        // Pagination: each tab has its own page parameter (student_page / teacher_page / parent_page)
        var key = ['student_page', 'teacher_page', 'parent_page'].find(function (k) { return u.searchParams.has(k); });
        if (key) navigate({ pageKey: key, pageVal: u.searchParams.get(key) });
    });

    /* ── Document-level handlers: bind ONCE per page load. The admin layout re-runs page
       scripts on every SPA visit, so without this guard listeners would pile up. They
       look elements up at click time, so they always act on the current page's DOM. ── */
    if (window.__umUsersBound) return;
    window.__umUsersBound = true;

    // Profile drawer
    var drawerCtl = null;
    function openDrawer(url) {
        var drawer = $('up-drawer'), overlay = $('up-overlay'), body = $('up-drawer-body'), foot = $('up-drawer-footer');
        if (!drawer) return false;
        if (drawerCtl) drawerCtl.abort();
        drawerCtl = new AbortController();
        overlay.classList.add('open'); drawer.classList.add('open'); drawer.setAttribute('aria-hidden', 'false');
        body.innerHTML = '<div class="um-drawer-msg">Loading profile…</div>';
        foot.style.display = 'none';
        $('up-drawer-fullpage').href = url;

        fetch(url, { signal: drawerCtl.signal, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.text(); })
            .then(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var page = doc.querySelector('.up-page');
                if (!page) throw new Error('No profile');
                page.querySelector('.up-back') && page.querySelector('.up-back').remove();
                page.style.maxWidth = '100%'; page.style.margin = '0';
                body.innerHTML = '';
                // Bring the profile page's own styles along — first block only (scoped .up-*);
                // its second block is its own confirm modal, which this page already styles.
                var css = doc.querySelector('#mainContent style');
                if (css) body.appendChild(css.cloneNode(true));
                body.appendChild(page);
                foot.style.display = 'block';
            })
            .catch(function (err) {
                if (err.name === 'AbortError') return;
                body.innerHTML = '<div class="um-drawer-msg">Could not load this profile. <a href="' + url + '">Open the full page instead.</a></div>';
            })
            .finally(function () { drawerCtl = null; });
        return true;
    }
    function closeDrawer() {
        var drawer = $('up-drawer'), overlay = $('up-overlay');
        if (drawerCtl) { drawerCtl.abort(); drawerCtl = null; }
        if (drawer) { drawer.classList.remove('open'); drawer.setAttribute('aria-hidden', 'true'); }
        if (overlay) overlay.classList.remove('open');
    }

    // Approve / reject confirmation
    var pending = null;
    var CFG = {
        approve: { icon: 'M5 13l4 4L19 7', btn: 'Yes, approve', title: function (n) { return 'Approve ' + n + '?'; },
                   desc: function (n) { return n + ' will be able to log in to DP-LMS. An email notification is sent if they have an email address.'; } },
        reject:  { icon: 'M6 18L18 6M6 6l12 12', btn: 'Yes, reject', title: function (n) { return 'Reject ' + n + '?'; },
                   desc: function (n) { return n + "'s account will be rejected and they will not be able to log in."; } }
    };
    function openConfirm(action, url, name) {
        var c = CFG[action], box = $('um-confirm-backdrop');
        if (!c || !box) return;
        pending = { action: action, url: url };
        $('um-confirm-icon').className = 'um-confirm-icon ' + action;
        $('um-confirm-icon').querySelector('path').setAttribute('d', c.icon);
        $('um-confirm-title').textContent = c.title(name);
        $('um-confirm-desc').textContent  = c.desc(name);
        var go = $('um-confirm-proceed');
        go.textContent = c.btn; go.className = 'um-confirm-proceed ' + action; go.disabled = false;
        box.classList.add('open');
        $('um-confirm-cancel').focus();
    }
    function closeConfirm() {
        var box = $('um-confirm-backdrop');
        if (box) box.classList.remove('open');
        pending = null;
    }

    document.addEventListener('click', function (e) {
        if (!$('um-ajax-content')) return; // not on this page anymore (SPA)

        var view = e.target.closest('.um-btn-view');
        if (view && plainClick(e)) { if (openDrawer(view.href)) e.preventDefault(); return; }

        var act = e.target.closest('[data-action="approve"],[data-action="reject"]');
        if (act) { openConfirm(act.dataset.action, act.dataset.url, act.dataset.name); return; }

        if (e.target.closest('#up-drawer-close') || e.target.id === 'up-overlay') { closeDrawer(); return; }
        if (e.target.closest('#um-confirm-cancel') || e.target.id === 'um-confirm-backdrop') { closeConfirm(); return; }

        if (e.target.closest('#um-confirm-proceed') && pending) {
            var form = $('um-action-form'), go = $('um-confirm-proceed');
            form.action = pending.url;
            go.disabled = true; go.textContent = 'Processing…';
            form.submit();
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape' || !$('um-ajax-content')) return;
        var box = $('um-confirm-backdrop');
        if (box && box.classList.contains('open')) closeConfirm(); else closeDrawer();
    });
})();
</script>
@endsection
