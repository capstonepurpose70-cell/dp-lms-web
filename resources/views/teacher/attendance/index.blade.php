@extends('layouts.teacher')
@section('title', 'Attendance')

@section('content')
<style>
    .ca-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--r-lg); box-shadow:var(--shadow-sm); padding:18px; margin-bottom:16px; }
    .ca-card h2 { font-size:15px; font-weight:800; color:var(--text-1); margin:0 0 4px; }
    .ca-muted { font-size:12.5px; color:var(--text-2); margin:0 0 12px; line-height:1.5; }

    .ca-live { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding:14px 16px; border-radius:14px;
        background:rgba(22,163,74,.08); border:1px solid rgba(22,163,74,.35); margin-bottom:10px; }
    .ca-live b { color:var(--text-1); font-size:14px; }
    .ca-live span { display:block; font-size:12.5px; color:var(--text-2); margin-top:2px; }
    .ca-dot { display:inline-block; width:9px; height:9px; border-radius:50%; background:#16a34a; margin-right:6px; animation: ca-pulse 1.4s infinite; }
    @keyframes ca-pulse { 50% { opacity:.3; } }

    .ca-grid { display:grid; grid-template-columns: 1.4fr 1fr 1fr; gap:12px; }
    .ca-label { display:block; font-size:12.5px; font-weight:700; color:var(--text-1); margin-bottom:6px; }
    .ca-in { width:100%; height:44px; padding:0 12px; border:1.5px solid var(--border); border-radius:10px; font-size:14px; background:var(--surface); color:var(--text-1); box-sizing:border-box; }
    .ca-in:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
    .ca-preview { display:flex; gap:8px; flex-wrap:wrap; margin:14px 0 4px; }
    .ca-chip { display:inline-flex; align-items:center; gap:6px; padding:6px 11px; border-radius:999px; font-size:12.5px; font-weight:700; }
    .ca-chip.g { background:rgba(22,163,74,.12); color:#15803d; }
    .ca-chip.y { background:rgba(234,179,8,.18); color:#a16207; }
    .ca-chip.r { background:rgba(220,38,38,.10); color:#b91c1c; }
    .ca-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; height:44px; padding:0 18px; border:0; border-radius:11px;
        background:#15803d; color:#fff; font-size:14px; font-weight:800; cursor:pointer; text-decoration:none; }
    .ca-btn:hover { background:#166534; }
    .ca-btn.ghost { background:transparent; color:var(--text-1); border:1px solid var(--border); height:36px; font-size:13px; font-weight:700; }
    .ca-btn.ghost:hover { border-color:#2563eb; color:#2563eb; }
    .ca-btn:disabled { opacity:.6; cursor:wait; }
    .ca-err { background:rgba(220,38,38,.08); border:1px solid rgba(220,38,38,.3); color:#b91c1c; padding:11px 14px; border-radius:12px; font-size:13px; margin-bottom:14px; }
    .ca-empty { text-align:center; padding:28px 12px; color:var(--text-2); font-size:13.5px; }

    .ca-table { width:100%; border-collapse:collapse; font-size:13px; }
    .ca-table th { text-align:left; font-size:12.5px; font-weight:700; color:var(--text-2); padding:9px 10px; border-bottom:1px solid var(--border); }
    .ca-table td { padding:10px; border-bottom:1px solid var(--border); color:var(--text-1); }
    .ca-table tr:last-child td { border-bottom:0; }
    .ca-n { font-weight:800; }
    .ca-n.g { color:#15803d; } .ca-n.y { color:#a16207; } .ca-n.r { color:#b91c1c; }
    .ca-scroll { overflow-x:auto; }
    @media (max-width: 760px) { .ca-grid { grid-template-columns:1fr; } }
</style>

<div class="tp-page">
    <div class="tp-head">
        <div>
            <h1 class="tp-title">Class attendance</h1>
            <p class="tp-sub">Open attendance for a class: students get a notification and enter the 4-digit code you show.
                Before the on-time limit they are present, after it late, and anyone who has not checked in when it closes
                is absent. Parents are notified of every result.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="ca-err" role="alert">{{ $errors->first() }}</div>
    @endif

    @foreach ($open as $s)
        <div class="ca-live">
            <div>
                <b><span class="ca-dot" aria-hidden="true"></span>{{ $s->subject?->name }}, Grade {{ $s->section?->grade_level }} {{ $s->section?->name }} is open</b>
                <span>Present until {{ $s->on_time_until->format('g:i A') }}, closes at {{ $s->closes_at->format('g:i A') }}</span>
            </div>
            <a href="{{ route('teacher.attendance.show', $s) }}" class="ca-btn" style="height:38px;">Show code &amp; live list</a>
        </div>
    @endforeach

    <div class="ca-card">
        <h2>Start attendance</h2>
        @if ($classes->isEmpty())
            <div class="ca-empty">You have no classes yet. The admin assigns your classes in <b>Class Schedules</b>.</div>
        @else
            <p class="ca-muted">Choose the class and how long students have. You can close it early or mark students yourself anytime.</p>
            <form method="POST" action="{{ route('teacher.attendance.store') }}" id="caForm">
                @csrf
                <div class="ca-grid">
                    <div>
                        <label class="ca-label" for="caClass">Class</label>
                        <select id="caClass" name="class" class="ca-in" required>
                            <option value="">Choose a class</option>
                            @foreach ($classes as $c)
                                @php $val = $c->section_id . '-' . $c->subject_id; @endphp
                                <option value="{{ $val }}" @selected(old('class') === $val)>{{ $c->subject->name }}, Grade {{ $c->section->grade_level }} {{ $c->section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="ca-label" for="caOn">Present if within</label>
                        <select id="caOn" name="on_time_minutes" class="ca-in">
                            @foreach ([5, 10, 15, 20, 30, 45, 60] as $m)
                                <option value="{{ $m }}" @selected((int) old('on_time_minutes', 15) === $m)>{{ $m }} minutes</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="ca-label" for="caLate">Then late for</label>
                        <select id="caLate" name="late_minutes" class="ca-in">
                            @foreach ([0 => 'No late window', 5 => '5 minutes', 10 => '10 minutes', 15 => '15 minutes', 30 => '30 minutes', 45 => '45 minutes', 60 => '60 minutes'] as $m => $label)
                                <option value="{{ $m }}" @selected((int) old('late_minutes', 15) === $m)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="ca-preview" aria-live="polite">
                    <span class="ca-chip g" id="pvPresent">Present</span>
                    <span class="ca-chip y" id="pvLate">Late</span>
                    <span class="ca-chip r">Absent after that</span>
                </div>
                <div style="margin-top:14px;">
                    <button type="submit" class="ca-btn" id="caStart">Start attendance &amp; notify students</button>
                </div>
            </form>
        @endif
    </div>

    <div class="ca-card">
        <h2>Recent attendance</h2>
        @if ($history->isEmpty())
            <div class="ca-empty">No attendance taken yet.</div>
        @else
            <div class="ca-scroll">
                <table class="ca-table">
                    <thead><tr><th scope="col">Date</th><th scope="col">Class</th><th scope="col">Present</th><th scope="col">Late</th><th scope="col">Absent</th><th scope="col"><span class="sr-only">Action</span></th></tr></thead>
                    <tbody>
                        @foreach ($history as $h)
                            <tr>
                                <td>{{ $h->opened_at->format('M j, Y') }}<div style="font-size:12px;color:var(--text-3);">{{ $h->opened_at->format('g:i A') }}</div></td>
                                <td>{{ $h->subject?->name }}<div style="font-size:12px;color:var(--text-3);">Grade {{ $h->section?->grade_level }} {{ $h->section?->name }}</div></td>
                                <td class="ca-n g">{{ $h->present_count }}</td>
                                <td class="ca-n y">{{ $h->late_count }}</td>
                                <td class="ca-n r">{{ $h->closed_at ? $h->absent_count : '—' }}</td>
                                <td style="text-align:right;">
                                    <a href="{{ route('teacher.attendance.show', $h) }}" class="ca-btn ghost">{{ $h->isOpen() ? 'Open live' : 'View' }}</a>
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
    var on = document.getElementById('caOn'), late = document.getElementById('caLate');
    if (!on) return;
    function fmt(d) { return d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }); }
    var timer;
    function upd() {
        // the teacher layout swaps pages without reloading: stop once this form is gone
        if (!document.body.contains(on)) { clearInterval(timer); return; }
        var now = new Date(), p = new Date(now.getTime() + on.value * 60000), l = new Date(p.getTime() + late.value * 60000);
        document.getElementById('pvPresent').textContent = 'Present until ' + fmt(p);
        var lt = document.getElementById('pvLate');
        lt.textContent = Number(late.value) ? 'Late until ' + fmt(l) : 'No late window';
    }
    on.addEventListener('change', upd); late.addEventListener('change', upd); upd(); timer = setInterval(upd, 30000);
    document.getElementById('caForm').addEventListener('submit', function (e) {
        if (!document.getElementById('caClass').value) { e.preventDefault(); document.getElementById('caClass').focus(); return; }
        var b = document.getElementById('caStart'); b.disabled = true; b.textContent = 'Starting…';
    });
})();
</script>
@endsection
