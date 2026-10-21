@extends('layouts.teacher')
@section('title', 'Attendance — ' . ($session->subject?->name ?? 'Class'))

@section('content')
@php
    $isOpen = $session->isOpen();
    $chip = ['present' => ['g', 'Present'], 'late' => ['y', 'Late'], 'absent' => ['r', 'Absent']];
@endphp
<style>
    .lv-page { max-width:1040px; animation: lv-fade .22s ease both; }
    @keyframes lv-fade { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
    .lv-back { display:inline-block; font-size:13px; font-weight:600; color:var(--text-2); text-decoration:none; margin-bottom:10px; }
    .lv-back:hover { color:#2563eb; }
    .lv-head { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap; margin-bottom:14px; }
    .lv-title { font-size:21px; font-weight:800; color:var(--text-1); margin:0; }
    .lv-sub { font-size:13px; color:var(--text-2); margin-top:3px; }
    .lv-badge { display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:999px; font-size:12.5px; font-weight:800; }
    .lv-badge.g { background:rgba(22,163,74,.12); color:#15803d; }
    .lv-badge.y { background:rgba(234,179,8,.18); color:#a16207; }
    .lv-badge.x { background:rgba(100,116,139,.15); color:var(--text-2); }

    .lv-top { display:grid; grid-template-columns: 1.1fr 1fr; gap:14px; margin-bottom:14px; }
    .lv-card { background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:18px; }
    .lv-code-lbl { font-size:12px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:var(--text-3); }
    .lv-code { font-size:64px; font-weight:900; letter-spacing:14px; color:var(--text-1); font-family:ui-monospace, Consolas, monospace; line-height:1.1; margin:6px 0 10px; }
    .lv-code.closed { font-size:22px; letter-spacing:0; color:var(--text-3); font-family:inherit; }
    .lv-actions { display:flex; gap:8px; flex-wrap:wrap; }
    .lv-btn { display:inline-flex; align-items:center; justify-content:center; gap:6px; height:38px; padding:0 14px; border-radius:10px; font-size:13px; font-weight:700;
        cursor:pointer; border:1px solid var(--border); background:var(--surface); color:var(--text-1); }
    .lv-btn:hover { border-color:#2563eb; color:#2563eb; }
    .lv-btn.danger { color:#b91c1c; border-color:rgba(220,38,38,.35); }
    .lv-btn.danger:hover { background:rgba(220,38,38,.07); }
    .lv-timer { font-size:30px; font-weight:900; color:var(--text-1); font-variant-numeric:tabular-nums; }
    .lv-timer-lbl { font-size:12.5px; color:var(--text-2); margin-top:2px; }
    .lv-counts { display:grid; grid-template-columns:repeat(4, 1fr); gap:8px; margin-top:14px; }
    .lv-counts div { border-radius:12px; padding:10px; text-align:center; }
    .lv-counts b { display:block; font-size:22px; font-weight:900; }
    .lv-counts span { font-size:11.5px; font-weight:700; }
    .c-g { background:rgba(22,163,74,.10); color:#15803d; } .c-y { background:rgba(234,179,8,.16); color:#a16207; }
    .c-r { background:rgba(220,38,38,.09); color:#b91c1c; } .c-w { background:rgba(100,116,139,.12); color:var(--text-2); }

    .lv-table { width:100%; border-collapse:collapse; font-size:13.5px; }
    .lv-table th { text-align:left; font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--text-3); padding:9px 10px; border-bottom:1px solid var(--border); }
    .lv-table td { padding:9px 10px; border-bottom:1px solid var(--border); color:var(--text-1); vertical-align:middle; }
    .lv-table tr:last-child td { border-bottom:0; }
    .st { display:inline-block; min-width:74px; text-align:center; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:800; }
    .st.g { background:#16a34a; color:#fff; } .st.y { background:#eab308; color:#3f2d00; } .st.r { background:#dc2626; color:#fff; }
    .st.w { background:rgba(100,116,139,.15); color:var(--text-2); }
    .mk { display:inline-flex; gap:4px; }
    .mk button { width:34px; height:30px; border-radius:8px; border:1px solid var(--border); background:var(--surface); font-weight:800; font-size:12px; cursor:pointer; color:var(--text-2); }
    .mk button.g:hover, .mk button.g.on { background:#16a34a; color:#fff; border-color:#16a34a; }
    .mk button.y:hover, .mk button.y.on { background:#eab308; color:#3f2d00; border-color:#eab308; }
    .mk button.r:hover, .mk button.r.on { background:#dc2626; color:#fff; border-color:#dc2626; }
    .lv-small { font-size:12px; color:var(--text-3); }
    .lv-err { background:rgba(220,38,38,.08); border:1px solid rgba(220,38,38,.3); color:#b91c1c; padding:11px 14px; border-radius:12px; font-size:13px; margin-bottom:14px; }
    .lv-scroll { overflow-x:auto; }

    /* Full-screen code for the projector */
    .lv-full { position:fixed; inset:0; z-index:2000; background:#0b1220; color:#fff; display:none; flex-direction:column; align-items:center; justify-content:center; gap:14px; text-align:center; padding:20px; }
    .lv-full.on { display:flex; }
    .lv-full .fc { font-size:min(28vw, 260px); font-weight:900; letter-spacing:.12em; font-family:ui-monospace, Consolas, monospace; line-height:1; }
    .lv-full .fs { font-size:clamp(16px, 3vw, 28px); opacity:.85; }
    .lv-full .ft { font-size:clamp(22px, 5vw, 48px); font-weight:800; font-variant-numeric:tabular-nums; }
    .lv-full button { margin-top:10px; background:transparent; color:#fff; border:1px solid rgba(255,255,255,.4); border-radius:10px; padding:8px 16px; cursor:pointer; }
    @media (max-width: 760px) { .lv-top { grid-template-columns:1fr; } .lv-code { font-size:48px; letter-spacing:10px; } }
</style>

<div class="lv-page">
    <a href="{{ route('teacher.attendance.index') }}" class="lv-back">&larr; Back to Attendance</a>

    @if ($errors->any())
        <div class="lv-err" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="lv-head">
        <div>
            <h1 class="lv-title">{{ $session->subject?->name }} · Grade {{ $session->section?->grade_level }} {{ $session->section?->name }}</h1>
            <div class="lv-sub">Opened {{ $session->opened_at->format('M j, Y g:i A') }} · present until {{ $session->on_time_until->format('g:i A') }} · closes {{ $session->closes_at->format('g:i A') }}</div>
        </div>
        <span class="lv-badge x" id="lvPhase">{{ $isOpen ? '…' : 'Closed' }}</span>
    </div>

    <div class="lv-top">
        <div class="lv-card">
            <div class="lv-code-lbl">Class code — show this to the class</div>
            <div class="lv-code {{ $isOpen ? '' : 'closed' }}" id="lvCode">{{ $isOpen ? $session->code : 'Attendance closed' }}</div>
            @if ($isOpen)
                <div class="lv-actions" id="lvOpenActions">
                    <button type="button" class="lv-btn" id="lvFullBtn">Full screen (projector)</button>
                    <button type="button" class="lv-btn" id="lvNewCode">New code</button>
                    <form method="POST" action="{{ route('teacher.attendance.close', $session) }}" style="margin:0;"
                          onsubmit="return confirm('Close attendance now? Students who have not checked in will be marked absent and their parents notified.');">
                        @csrf
                        <button type="submit" class="lv-btn danger">Close now</button>
                    </form>
                </div>
            @else
                <div class="lv-small">Unmarked students were set to absent. You can still correct anyone below.</div>
            @endif
        </div>
        <div class="lv-card">
            <div class="lv-timer" id="lvTimer">{{ $isOpen ? '--:--' : '—' }}</div>
            <div class="lv-timer-lbl" id="lvTimerLbl">{{ $isOpen ? '' : 'Closed ' . $session->closed_at?->format('g:i A') }}</div>
            <div class="lv-counts">
                <div class="c-g"><b id="cPresent">{{ $counts['present'] }}</b><span>Present</span></div>
                <div class="c-y"><b id="cLate">{{ $counts['late'] }}</b><span>Late</span></div>
                <div class="c-r"><b id="cAbsent">{{ $counts['absent'] }}</b><span>Absent</span></div>
                <div class="c-w"><b id="cWaiting">{{ $counts['waiting'] }}</b><span>Not yet</span></div>
            </div>
        </div>
    </div>

    <div class="lv-card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:8px;">
            <b style="color:var(--text-1);">Students ({{ $counts['total'] }})</b>
            <span class="lv-small">P / L / A to mark a student yourself (e.g. no phone). Updates every few seconds.</span>
        </div>
        @if ($rows->isEmpty())
            <div class="lv-small" style="padding:18px 0; text-align:center;">No approved students in this section yet.</div>
        @else
            <div class="lv-scroll">
                <table class="lv-table">
                    <thead><tr><th>Student</th><th>Status</th><th>Time</th><th style="text-align:right;">Mark</th></tr></thead>
                    <tbody>
                        @foreach ($rows as $r)
                            @php [$cls, $lbl] = $chip[$r['status']] ?? ['w', 'Not yet']; @endphp
                            <tr data-id="{{ $r['student']->id }}">
                                <td>{{ $r['student']->name }}@if ($r['student']->lrn)<div class="lv-small">LRN {{ $r['student']->lrn }}</div>@endif</td>
                                <td><span class="st {{ $cls }}">{{ $lbl }}</span></td>
                                <td class="lv-small t">{{ $r['record']?->marked_at?->format('g:i A') ?? '' }}@if ($r['record']?->marked_by === 'teacher') · by you @endif</td>
                                <td style="text-align:right;">
                                    <span class="mk">
                                        <button type="button" class="g {{ $r['status'] === 'present' ? 'on' : '' }}" data-s="present" title="Present">P</button>
                                        <button type="button" class="y {{ $r['status'] === 'late' ? 'on' : '' }}" data-s="late" title="Late">L</button>
                                        <button type="button" class="r {{ $r['status'] === 'absent' ? 'on' : '' }}" data-s="absent" title="Absent">A</button>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="lv-full" id="lvFull" role="dialog" aria-label="Class code">
    <div class="fs">{{ $session->subject?->name }} · Grade {{ $session->section?->grade_level }} {{ $session->section?->name }} — enter this code in DP-LMS</div>
    <div class="fc" id="lvFullCode">{{ $isOpen ? $session->code : '' }}</div>
    <div class="ft" id="lvFullTimer"></div>
    <button type="button" id="lvFullClose">Exit full screen (Esc)</button>
</div>

<script>
(function () {
    var CSRF   = @json(csrf_token());
    var LIVE   = @json(route('teacher.attendance.live', $session));
    var MARK   = @json(route('teacher.attendance.mark', $session));
    var CODE   = @json(route('teacher.attendance.code', $session));
    var OPEN   = @json($isOpen);
    var tOn    = Date.parse(@json($session->on_time_until->toIso8601String()));
    var tClose = Date.parse(@json($session->closes_at->toIso8601String()));
    var skew   = Date.now() - Date.parse(@json(now()->toIso8601String())); // phone/PC clock vs server
    var LABEL  = { present: ['g', 'Present'], late: ['y', 'Late'], absent: ['r', 'Absent'] };
    var $ = function (id) { return document.getElementById(id); };

    function mmss(ms) { ms = Math.max(0, ms); var s = Math.floor(ms / 1000); return String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0'); }

    function tick() {
        if (!OPEN) return;
        var now = Date.now() - skew, phase = $('lvPhase');
        if (now < tOn) {
            phase.className = 'lv-badge g'; phase.textContent = 'Present window';
            $('lvTimer').textContent = mmss(tOn - now); $('lvTimerLbl').textContent = 'left to be marked present';
        } else if (now < tClose) {
            phase.className = 'lv-badge y'; phase.textContent = 'Late window';
            $('lvTimer').textContent = mmss(tClose - now); $('lvTimerLbl').textContent = 'left before it closes (late)';
        } else {
            phase.className = 'lv-badge x'; phase.textContent = 'Closing…';
            $('lvTimer').textContent = '00:00'; $('lvTimerLbl').textContent = 'Marking absents…';
        }
        $('lvFullTimer').textContent = $('lvTimer').textContent + ' · ' + phase.textContent;
    }

    function paint(id, status, time, byTeacher) {
        var tr = document.querySelector('tr[data-id="' + id + '"]'); if (!tr) return;
        var l = LABEL[status] || ['w', 'Not yet'];
        var chip = tr.querySelector('.st'); chip.className = 'st ' + l[0]; chip.textContent = l[1];
        tr.querySelector('.t').textContent = (time || '') + (byTeacher ? ' · by you' : '');
        tr.querySelectorAll('.mk button').forEach(function (b) { b.classList.toggle('on', b.dataset.s === status); });
    }

    function poll() {
        fetch(LIVE, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) {
                if (!d) return;
                $('cPresent').textContent = d.counts.present; $('cLate').textContent = d.counts.late;
                $('cAbsent').textContent = d.counts.absent;   $('cWaiting').textContent = d.counts.waiting;
                d.rows.forEach(function (r) { paint(r.id, r.status, r.time, r.by === 'teacher'); });
                if (OPEN && !d.open) { window.location.reload(); }
            }).catch(function () {});
    }

    document.querySelectorAll('.mk button').forEach(function (b) {
        b.addEventListener('click', function () {
            var tr = b.closest('tr'), body = new FormData();
            body.append('student_id', tr.dataset.id); body.append('status', b.dataset.s);
            fetch(MARK, { method: 'POST', body: body, headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (d) { if (d.ok) { paint(tr.dataset.id, d.status, d.time, true); poll(); } else { alert(d.message || 'Could not update.'); } })
                .catch(function () { alert('Network error — please try again.'); });
        });
    });

    if ($('lvNewCode')) $('lvNewCode').addEventListener('click', function () {
        if (!confirm('Make a new code? The current code will stop working.')) return;
        fetch(CODE, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (d) { if (d.ok) { $('lvCode').textContent = d.code; $('lvFullCode').textContent = d.code; } else { alert(d.message); } });
    });

    if ($('lvFullBtn')) $('lvFullBtn').addEventListener('click', function () {
        $('lvFull').classList.add('on');
        if (document.documentElement.requestFullscreen) document.documentElement.requestFullscreen().catch(function () {});
    });
    function exitFull() { $('lvFull').classList.remove('on'); if (document.fullscreenElement && document.exitFullscreen) document.exitFullscreen().catch(function () {}); }
    $('lvFullClose').addEventListener('click', exitFull);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && $('lvFull')) exitFull(); });

    tick(); setInterval(tick, 1000);
    if (OPEN) setInterval(poll, 5000);
})();
</script>
@endsection
