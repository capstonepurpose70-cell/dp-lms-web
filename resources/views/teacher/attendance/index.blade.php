@extends('layouts.teacher')
@section('title', 'Attendance')

@section('content')
<style>
    .att-wrap { max-width: 64rem; margin: 0 auto; }
    .att-num  { font-variant-numeric: tabular-nums; }

    /* ── Header ───────────────────────────────────────────── */
    .att-eyebrow {
        font-size: 11px; font-weight: 700; letter-spacing: .14em;
        text-transform: uppercase; color: #94a3b8;
    }
    .att-title { font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -.02em; }
    .att-sub   { font-size: 13px; color: #94a3b8; margin-top: 2px; }
    .att-live {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 7px 13px; border-radius: 999px;
        background: #f0f7ff; border: 1px solid #dbeafe;
        font-size: 12px; font-weight: 600; color: #1d4ed8;
    }
    .att-dot {
        width: 7px; height: 7px; border-radius: 50%; background: #2563eb;
        box-shadow: 0 0 0 0 rgba(37,99,235,.5); animation: attpulse 1.8s infinite;
    }
    @keyframes attpulse {
        0%   { box-shadow: 0 0 0 0 rgba(37,99,235,.45); }
        70%  { box-shadow: 0 0 0 7px rgba(37,99,235,0); }
        100% { box-shadow: 0 0 0 0 rgba(37,99,235,0); }
    }

    /* ── Summary strip (one cohesive register summary) ────── */
    .att-summary {
        display: flex; background: #fff; border: 1px solid #eef2f7;
        border-radius: 18px; box-shadow: 0 2px 14px rgba(15,23,42,.04);
        overflow: hidden; margin-bottom: 22px;
    }
    .att-seg {
        flex: 1; display: flex; align-items: center; gap: 13px;
        padding: 18px 20px; border-left: 1px solid #f1f5f9;
    }
    .att-seg:first-child { border-left: none; }
    .att-chip {
        width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
    }
    .att-seg-val { font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1; }
    .att-seg-lbl { font-size: 12px; color: #94a3b8; margin-top: 4px; }

    /* ── Log card ─────────────────────────────────────────── */
    .att-card { background:#fff; border:1px solid #eef2f7; border-radius:18px;
                box-shadow:0 2px 14px rgba(15,23,42,.04); overflow:hidden; }
    .att-toolbar { display:flex; align-items:center; gap:12px; justify-content:space-between;
                   padding:16px 18px; border-bottom:1px solid #f1f5f9; flex-wrap:wrap; }
    .att-toolbar h2 { font-size:14px; font-weight:700; color:#0f172a; }
    .att-search { position:relative; flex:1; min-width:180px; max-width:280px; }
    .att-search input {
        width:100%; padding:8px 12px 8px 34px; border:1px solid #e7edf3; border-radius:10px;
        font-size:13px; color:#334155; background:#fbfcfe; outline:none; transition:.15s;
    }
    .att-search input:focus { border-color:#bfdbfe; background:#fff; box-shadow:0 0 0 3px rgba(59,130,246,.1); }
    .att-search svg { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#94a3b8; }
    .att-count { font-size:12px; color:#94a3b8; }

    /* ── Register rows (status rail = the signature) ──────── */
    .att-row {
        display:flex; align-items:center; gap:13px;
        padding:13px 18px 13px 15px; border-bottom:1px solid #f6f8fb;
        border-left:3px solid transparent; transition:background .15s;
    }
    .att-row:last-of-type { border-bottom:none; }
    .att-row:hover { background:#f8fbff; }
    .att-avatar {
        width:38px; height:38px; border-radius:11px; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
        font-size:14px; font-weight:700; color:#fff;
        background:linear-gradient(135deg,#60a5fa,#2563eb);
    }
    .att-name { font-size:13.5px; font-weight:600; color:#0f172a; }
    .att-meta { font-size:11.5px; color:#94a3b8; display:flex; align-items:center; gap:5px; margin-top:2px; }
    .att-time { font-size:12px; color:#64748b; display:flex; align-items:center; gap:5px; justify-content:flex-end; }
    .att-badge { padding:3px 10px; border-radius:999px; font-size:10.5px; font-weight:700;
                 display:inline-flex; align-items:center; gap:4px; }
    .att-source { padding:3px 9px; border-radius:999px; font-size:10px; font-weight:600;
                  background:#eff6ff; color:#2563eb; display:inline-flex; align-items:center; gap:4px; }

    .att-empty { padding:54px 24px; text-align:center; }
    .att-empty-icon {
        width:58px; height:58px; border-radius:16px; margin:0 auto 16px;
        background:#f1f5f9; color:#94a3b8; display:flex; align-items:center; justify-content:center;
    }

    @media (max-width:640px){
        .att-summary { flex-direction:column; }
        .att-seg { border-left:none; border-top:1px solid #f1f5f9; }
        .att-seg:first-child { border-top:none; }
        .att-time-col { display:none; }
    }
</style>

<div class="att-wrap">

    {{-- ── Header ───────────────────────────────────────────── --}}
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <p class="att-eyebrow">Attendance Register</p>
            <h1 class="att-title">Attendance</h1>
            <p class="att-sub">{{ now()->format('l, F d, Y') }}</p>
        </div>
        <span class="att-live">
            <span class="att-dot"></span>
            Face&nbsp;ID device · Live
        </span>
    </div>

    {{-- ── Summary strip ────────────────────────────────────── --}}
    <div class="att-summary">
        <div class="att-seg">
            <span class="att-chip" style="background:#f1f5f9;color:#475569;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg>
            </span>
            <div>
                <p class="att-seg-val att-num">{{ $attendances->total() }}</p>
                <p class="att-seg-lbl">Total records</p>
            </div>
        </div>
        <div class="att-seg">
            <span class="att-chip" style="background:#dcfce7;color:#16a34a;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
            </span>
            <div>
                <p class="att-seg-val att-num" style="color:#16a34a;">{{ $attendances->where('status','present')->count() }}</p>
                <p class="att-seg-lbl">Present (this page)</p>
            </div>
        </div>
        <div class="att-seg">
            <span class="att-chip" style="background:#eff6ff;color:#2563eb;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/></svg>
            </span>
            <div>
                <p class="att-seg-val att-num" style="color:#2563eb;">{{ $attendances->where('source','iot')->count() }}</p>
                <p class="att-seg-lbl">Via Face ID</p>
            </div>
        </div>
    </div>

    {{-- ── Log card ─────────────────────────────────────────── --}}
    <div class="att-card">
        <div class="att-toolbar">
            <h2>Attendance log</h2>
            @if($attendances->count())
            <div class="att-search">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input id="attSearch" type="text" placeholder="Search this page…" autocomplete="off">
            </div>
            @endif
            <span class="att-count att-num">{{ $attendances->total() }} records</span>
        </div>

        <div id="attList">
        @forelse($attendances as $record)
            @php
                $s = strtolower($record->status ?? '');
                $map = [
                    'present' => ['#16a34a', '#dcfce7', '#15803d', 'Present'],
                    'late'    => ['#d97706', '#fef3c7', '#b45309', 'Late'],
                    'absent'  => ['#dc2626', '#fee2e2', '#b91c1c', 'Absent'],
                    'excused' => ['#7c3aed', '#ede9fe', '#6d28d9', 'Excused'],
                ];
                $st = $map[$s] ?? ['#6366f1', '#e0e7ff', '#4338ca', ucfirst($record->status ?? '—')];
                $dispName = $record->student_name ?? $record->student_id;
                $section  = $record->user->section->name ?? null;
                $isIot    = ($record->source ?? null) === 'iot';
            @endphp
            <div class="att-row"
                 style="border-left-color: {{ $st[0] }};"
                 data-search="{{ strtolower(($dispName ?? '').' '.($record->student_id ?? '').' '.($section ?? '')) }}">
                <div class="att-avatar">{{ strtoupper(substr($dispName ?? '?', 0, 1)) }}</div>

                <div class="flex-1 min-w-0">
                    <p class="att-name truncate">{{ $dispName }}</p>
                    <p class="att-meta">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg>
                        {{ $section ?? 'No section' }}
                    </p>
                </div>

                <div class="att-time-col text-right flex-shrink-0">
                    <p class="att-time att-num">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        {{ \Carbon\Carbon::parse($record->attended_at)->format('M d, h:i A') }}
                    </p>
                    <div class="flex gap-1.5 justify-end mt-1.5">
                        @if($isIot)
                        <span class="att-source">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M8 12h8"/></svg>
                            Face ID
                        </span>
                        @endif
                        <span class="att-badge" style="background: {{ $st[1] }}; color: {{ $st[2] }};">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            {{ $st[3] }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="att-empty">
                <div class="att-empty-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11Z"/></svg>
                </div>
                @if(!($hasSections ?? true))
                    <p style="font-size:15px;color:#334155;font-weight:700;">No sections assigned yet</p>
                    <p style="font-size:13px;color:#94a3b8;margin-top:6px;max-width:340px;margin-left:auto;margin-right:auto;">
                        Once a faculty assigns you to a section, your students' attendance will appear here automatically.
                    </p>
                @else
                    <p style="font-size:15px;color:#334155;font-weight:700;">No records yet</p>
                    <p style="font-size:13px;color:#94a3b8;margin-top:6px;max-width:360px;margin-left:auto;margin-right:auto;">
                        The Face ID device will log your students here as they check in. Keep the camera running.
                    </p>
                @endif
            </div>
        @endforelse
        </div>

        {{-- shown by JS when a search matches nothing on this page --}}
        <div id="attNoMatch" class="att-empty" style="display:none;">
            <div class="att-empty-icon">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            </div>
            <p style="font-size:14px;color:#334155;font-weight:600;">No match on this page</p>
            <p style="font-size:12.5px;color:#94a3b8;margin-top:4px;">Try a different name, or check other pages below.</p>
        </div>
    </div>

    {{-- ── Pagination ───────────────────────────────────────── --}}
    @if($attendances->hasPages())
    <div class="mt-4">
        {{ $attendances->links() }}
    </div>
    @endif

</div>

<script>
(function () {
    const input = document.getElementById('attSearch');
    if (!input) return;
    const list  = document.getElementById('attList');
    const noMatch = document.getElementById('attNoMatch');
    const rows = Array.from(list.querySelectorAll('.att-row'));
    input.addEventListener('input', function () {
        const q = input.value.trim().toLowerCase();
        let shown = 0;
        rows.forEach(function (r) {
            const hit = !q || (r.dataset.search || '').indexOf(q) !== -1;
            r.style.display = hit ? '' : 'none';
            if (hit) shown++;
        });
        noMatch.style.display = (rows.length && shown === 0) ? '' : 'none';
    });
})();
</script>
@endsection