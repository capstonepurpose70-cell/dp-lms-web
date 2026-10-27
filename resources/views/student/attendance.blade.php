@extends('layouts.app')
@section('title', 'Attendance')

@section('content')
@php $chip = ['present' => ['g', 'Present'], 'late' => ['y', 'Late'], 'absent' => ['r', 'Absent']]; @endphp
<style>
    /* B6-12: no extra side padding — the layout already pads the page (dati makitid ang code field sa phone) */
    .sa-wrap { max-width:760px; margin:0 auto; padding:0 0 40px; }
    .sa-h1 { font-size:26px; font-weight:800; color:#0F172A; }
    .sa-sub { font-size:13.5px; color:#64748B; margin:3px 0 20px; line-height:1.5; }
    .sa-card { background:#fff; border:1px solid #EEF1F5; border-radius:22px; padding:20px; box-shadow:0 4px 24px rgba(15,23,42,.05); margin-bottom:16px; }
    .sa-top { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; flex-wrap:wrap; }
    .sa-subj { font-size:18px; font-weight:800; color:#0F172A; }
    .sa-meta { font-size:13px; color:#64748B; margin-top:2px; }
    .sa-timer { text-align:right; }
    .sa-timer b { display:block; font-size:24px; font-weight:900; color:#0F172A; font-variant-numeric:tabular-nums; }
    .sa-timer span { font-size:12px; color:#64748B; }
    .sa-form { display:flex; gap:10px; margin-top:16px; flex-wrap:wrap; }
    .sa-code { flex:1; min-width:150px; height:58px; border:2px solid #E5E9F0; border-radius:16px; text-align:center; font-size:30px; font-weight:900;
        letter-spacing:12px; color:#0F172A; background:#F8FAFC; font-family:ui-monospace, Consolas, monospace; }
    .sa-code:focus { outline:none; border-color:#2563EB; background:#fff; box-shadow:0 0 0 4px rgba(37,99,235,.12); }
    .sa-btn { flex:1; min-width:170px; height:58px; border:0; border-radius:16px; font-size:17px; font-weight:900; cursor:pointer; color:#fff;
        transition:background .2s, transform .1s; }
    .sa-btn:active { transform:scale(.98); }
    .sa-btn.g { background:#16A34A; box-shadow:0 8px 18px rgba(22,163,74,.32); }
    .sa-btn.y { background:#EAB308; color:#3F2D00; box-shadow:0 8px 18px rgba(234,179,8,.32); }
    .sa-btn.x { background:#94A3B8; cursor:not-allowed; box-shadow:none; }
    .sa-btn:disabled { opacity:.75; }
    .sa-hint { font-size:12.5px; color:#64748B; margin-top:8px; }
    .sa-err { color:#DC2626; font-size:13px; font-weight:700; margin-top:8px; }
    .sa-done { margin-top:14px; display:flex; align-items:center; gap:10px; font-size:14px; font-weight:700; color:#0F172A; }
    .st { display:inline-block; padding:5px 13px; border-radius:999px; font-size:13px; font-weight:800; }
    .st.g { background:#16A34A; color:#fff; } .st.y { background:#EAB308; color:#3F2D00; } .st.r { background:#DC2626; color:#fff; }
    .sa-empty { text-align:center; padding:30px 12px; color:#64748B; }
    .sa-empty b { display:block; color:#475569; font-size:15px; margin-bottom:3px; }
    .sa-row { display:flex; justify-content:space-between; align-items:center; gap:10px; padding:11px 0; border-bottom:1px solid #F1F5F9; }
    .sa-row:last-child { border-bottom:0; }
    .sa-row b { color:#0F172A; font-size:14px; }
    .sa-row span.d { display:block; font-size:12.5px; color:#64748B; }
</style>

<div class="sa-wrap">
    <div class="sa-h1">Attendance</div>
    <div class="sa-sub">When your teacher opens attendance, enter the <b>4-digit code</b> shown in class.
        On time = <b style="color:#16A34A;">present</b>, after the time limit = <b style="color:#A16207;">late</b>,
        not checked in when it closes = <b style="color:#DC2626;">absent</b>. Your parent is notified.</div>

    @error('general')
        <div class="sa-card" role="alert" style="border-color:#FCA5A5; background:#FEF2F2; color:#B91C1C; font-weight:700; padding:14px 18px;">{{ $message }}</div>
    @enderror

    @forelse ($open as $s)
        @php $mine = $s->myRecord; @endphp
        <div class="sa-card" data-on="{{ $s->on_time_until->toIso8601String() }}" data-close="{{ $s->closes_at->toIso8601String() }}">
            <div class="sa-top">
                <div>
                    <div class="sa-subj">{{ $s->subject?->name }}</div>
                    <div class="sa-meta">{{ $s->teacher?->name }} · Grade {{ $s->section?->grade_level }} {{ $s->section?->name }}</div>
                </div>
                @unless ($mine)
                    <div class="sa-timer"><b class="js-timer">--:--</b><span class="js-timer-lbl"></span></div>
                @endunless
            </div>

            @if ($mine)
                @php [$cls, $lbl] = $chip[$mine->status] ?? ['r', ucfirst($mine->status)]; @endphp
                <div class="sa-done"><span class="st {{ $cls }}">{{ $lbl }}</span>
                    {{ $mine->marked_at ? 'at ' . $mine->marked_at->format('g:i A') : '' }}</div>
            @else
                <form method="POST" action="{{ route('student.attendance.mark', $s) }}" class="sa-form js-form">
                    @csrf
                    <input type="text" name="code" class="sa-code" inputmode="numeric" pattern="\d{4}" maxlength="4" autocomplete="off"
                           placeholder="••••" aria-label="Class code for {{ $s->subject?->name }}" required>
                    <button type="submit" class="sa-btn g js-btn">I'm present</button>
                </form>
                @error('code_' . $s->id)<div class="sa-err" role="alert">{{ $message }}</div>@enderror
                <div class="sa-hint js-hint">Present until {{ $s->on_time_until->format('g:i A') }}{{ $s->late_minutes ? ' · late until ' . $s->closes_at->format('g:i A') : '' }}</div>
            @endif
        </div>
    @empty
        <div class="sa-card">
            <div class="sa-empty"><b>No attendance is open right now</b>You'll get a notification when your teacher opens one.</div>
        </div>
    @endforelse

    <div class="sa-card">
        <div style="font-size:15px; font-weight:800; color:#0F172A; margin-bottom:6px;">Last 30 days</div>
        @forelse ($history as $h)
            @php [$cls, $lbl] = $chip[$h->status] ?? ['r', ucfirst($h->status)]; @endphp
            <div class="sa-row">
                <div>
                    <b>{{ $h->session?->subject?->name ?? 'Class' }}</b>
                    <span class="d">{{ $h->session?->opened_at?->format('D, M j') }}{{ $h->marked_at ? ' · ' . $h->marked_at->format('g:i A') : '' }}</span>
                </div>
                <span class="st {{ $cls }}">{{ $lbl }}</span>
            </div>
        @empty
            <div class="sa-empty" style="padding:16px;">No attendance records yet.</div>
        @endforelse
    </div>
</div>

<script>
(function () {
    var skew = Date.now() - Date.parse(@json(now()->toIso8601String()));
    function mmss(ms) { ms = Math.max(0, ms); var s = Math.floor(ms / 1000); return String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0'); }

    var cards = document.querySelectorAll('.sa-card[data-on]');
    function tick() {
        var now = Date.now() - skew;
        cards.forEach(function (c) {
            var on = Date.parse(c.dataset.on), close = Date.parse(c.dataset.close);
            var btn = c.querySelector('.js-btn'), t = c.querySelector('.js-timer'), l = c.querySelector('.js-timer-lbl');
            if (!btn) return;
            if (now < on)         { btn.className = 'sa-btn g js-btn'; btn.textContent = "I'm present"; t.textContent = mmss(on - now); l.textContent = 'to be on time'; }
            else if (now < close) { btn.className = 'sa-btn y js-btn'; btn.textContent = "I'm late";    t.textContent = mmss(close - now); l.textContent = 'before it closes'; }
            else                  { btn.className = 'sa-btn x js-btn'; btn.textContent = 'Closed'; btn.disabled = true; t.textContent = '00:00'; l.textContent = 'closed'; }
        });
    }
    tick(); setInterval(tick, 1000);

    document.querySelectorAll('.js-form').forEach(function (f) {
        var input = f.querySelector('.sa-code');
        input.addEventListener('input', function () { input.value = input.value.replace(/\D/g, '').slice(0, 4); });
        f.addEventListener('submit', function (e) {
            if (input.value.length !== 4) { e.preventDefault(); input.focus(); return; }
            var b = f.querySelector('.js-btn'); b.disabled = true; b.textContent = 'Checking…';
        });
    });

    // B6-12: the Back button restored the page with the button stuck on "Checking…"
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        document.querySelectorAll('.js-btn').forEach(function (b) { b.disabled = false; });
        tick();
    });
})();
</script>
@endsection
