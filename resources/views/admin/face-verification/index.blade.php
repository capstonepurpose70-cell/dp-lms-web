@extends('layouts.admin')
@section('title', 'Face Verification')

@section('content')
<style>
    /* Uses admin layout tokens so dark mode works */
    .fv-page { max-width:1100px; margin:0 auto; animation: fv-fade .22s ease both; }
    @keyframes fv-fade { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
    .fv-title { font-size:22px; font-weight:800; color:var(--slate-900); margin:0; letter-spacing:-0.01em; }
    .fv-sub { font-size:13px; color:var(--slate-500); margin:4px 0 18px; line-height:1.55; max-width:760px; }
    .fv-card { background:var(--white); border:1px solid var(--border-default); border-radius:16px; padding:18px; margin-bottom:16px; box-shadow:var(--shadow-sm); }
    .fv-h2 { font-size:15px; font-weight:800; color:var(--slate-900); margin:0; }
    .fv-muted { font-size:12.5px; color:var(--slate-500); margin:3px 0 0; line-height:1.5; }
    .fv-alert { border-radius:12px; padding:12px 14px; font-size:13.5px; margin-bottom:14px; font-weight:600; background:rgba(220,38,38,.08); color:#b91c1c; border:1px solid rgba(220,38,38,.3); }
    [data-theme="dark"] .fv-alert { color:#f87171; }
    .fv-select { height:40px; padding:0 12px; border:1.5px solid var(--border-default); border-radius:10px; font-size:13.5px; background:var(--white); color:var(--slate-900); min-width:240px; }
    .fv-select:focus { outline:none; border-color:#2563eb; }
    .fv-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:8px; }
    .fv-row { display:flex; justify-content:space-between; align-items:center; gap:8px; border:1px solid var(--border-default); border-radius:12px; padding:9px 12px; }
    .fv-name { font-weight:700; color:var(--slate-900); font-size:13.5px; }
    .fv-meta { font-size:12px; color:var(--slate-500); margin-top:2px; }
    .fv-chip { display:inline-block; margin-top:3px; padding:1px 8px; border-radius:999px; font-size:11px; font-weight:800; }
    .fv-btn { height:36px; padding:0 14px; border-radius:10px; font-weight:700; font-size:13px; cursor:pointer; border:1px solid var(--border-default); background:var(--white); color:var(--slate-700); white-space:nowrap; }
    .fv-btn:hover { border-color:#2563eb; color:#2563eb; }
    .fv-btn.primary { background:#1d4ed8; border-color:#1d4ed8; color:#fff; }
    .fv-btn.ok { background:#16a34a; border-color:#16a34a; color:#fff; }
    .fv-btn.ok:hover { background:#15803d; color:#fff; }
    .fv-btn.warn { color:#dc2626; border-color:rgba(220,38,38,.35); background:transparent; }
    .fv-btn.warn:hover { background:rgba(220,38,38,.08); color:#dc2626; }
    .fv-btn.sm { height:32px; padding:0 12px; font-size:12.5px; }
    .fv-imgs { display:grid; grid-template-columns:repeat(auto-fill, minmax(84px, 1fr)); gap:8px; margin:12px 0 14px; }
    .fv-imgs img { width:100%; aspect-ratio:1; object-fit:cover; border-radius:10px; border:1px solid var(--border-default); background:var(--slate-100); }
    .fv-warnchip { display:inline-block; margin-top:6px; background:rgba(234,88,12,.12); color:#c2410c; font-size:11.5px; font-weight:700; padding:2px 9px; border-radius:999px; }
    [data-theme="dark"] .fv-warnchip { color:#fdba74; }
    .fv-empty { text-align:center; padding:26px; color:var(--slate-500); font-size:13.5px; border:1.5px dashed var(--border-default); border-radius:14px; }
    .fv-stats { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:12px; margin-bottom:16px; }
    .fv-stat { background:var(--white); border:1px solid var(--border-default); border-radius:14px; padding:14px 16px; box-shadow:var(--shadow-sm); }
    .fv-stat b { display:block; font-size:22px; font-weight:800; color:var(--slate-900); }
    .fv-stat span { font-size:12.5px; color:var(--slate-500); font-weight:600; }
    .fv-list .fv-li { display:flex; justify-content:space-between; align-items:center; gap:10px; padding:11px 2px; border-bottom:1px solid var(--border-default); font-size:13px; flex-wrap:wrap; }
    .fv-list .fv-li:last-child { border-bottom:0; }
    .fv-ok { color:#15803d; font-weight:700; } .fv-bad { color:#dc2626; font-weight:700; }
    [data-theme="dark"] .fv-ok { color:#4ade80; } [data-theme="dark"] .fv-bad { color:#f87171; }
    .fv-modal { display:none; position:fixed; inset:0; background:rgba(15,23,42,.55); z-index:1100; align-items:center; justify-content:center; padding:16px; }
    .fv-modal.on { display:flex; }
    .fv-modal-box { background:var(--white); border-radius:18px; max-width:440px; width:100%; padding:22px; box-shadow:var(--shadow-xl); }
    .fv-textarea { width:100%; border:1.5px solid var(--border-default); border-radius:10px; padding:9px 11px; font-size:13.5px; margin:6px 0 16px; resize:vertical; min-height:64px; background:var(--white); color:var(--slate-900); box-sizing:border-box; }
    @media (max-width: 640px) { .fv-stats { grid-template-columns:1fr; } }
</style>

<div class="fv-page">
    <h1 class="fv-title">Face Verification</h1>
    <p class="fv-sub">Faces used by the Raspberry Pi gate camera to send "arrived at school" notifications to parents.
        Register faces at the kiosk with a one-time QR (saved and approved right away). Remove any face that belongs to the wrong person.</p>

    @if (session('error'))
        <div class="fv-alert" role="alert">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="fv-alert" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="fv-stats">
        <div class="fv-stat"><b>{{ number_format($registeredCount) }}</b><span>Students with a registered face</span></div>
        <div class="fv-stat"><b>{{ number_format(max(0, $studentCount - $registeredCount)) }}</b><span>Students without a face</span></div>
        <div class="fv-stat"><b>{{ $pending->count() }}</b><span>Uploads waiting for review</span></div>
    </div>

    {{-- ── Register at the kiosk (one-time QR) ── --}}
    <div class="fv-card">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:12px; flex-wrap:wrap;">
            <div>
                <h2 class="fv-h2">Register a face at the kiosk</h2>
                <p class="fv-muted">Choose a section, then show the student's QR to the Raspberry Pi camera. Advisers can also do this from their own portal.</p>
            </div>
            <select id="fkSection" class="fv-select" aria-label="Section">
                <option value="">— Choose a section —</option>
                @foreach ($sections as $s)
                    <option value="{{ $s->id }}">Grade {{ $s->grade_level }} – {{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div id="fkList" class="fv-muted" style="margin-top:12px;">Students appear here after you choose a section.</div>
    </div>

    {{-- ── Uploads waiting for review ── --}}
    <div class="fv-card">
        <h2 class="fv-h2">Uploads waiting for review ({{ $pending->count() }})</h2>
        @unless (\App\Services\FaceRegistrationService::selfUploadEnabled())
            <p class="fv-muted">Student self-upload is turned off (kiosk only), so only uploads made before the switch can appear here.
                To allow phone/web uploads again, set <code>FACE_SELF_UPLOAD=true</code> on the server.</p>
        @endunless

        @forelse ($pending as $r)
            <div style="border-top:1px solid var(--border-default); margin-top:14px; padding-top:14px;">
                <div class="fv-name">{{ $r->user->name }}</div>
                <div class="fv-meta">
                    {{ $r->user->email ?? 'No email' }}@if ($r->user->lrn) · LRN {{ $r->user->lrn }}@endif
                    · {{ $r->images_count }} photos · uploaded {{ $r->created_at->diffForHumans() }}
                </div>
                @if ($r->user->face_warnings > 0)
                    <span class="fv-warnchip">⚠ {{ $r->user->face_warnings }}/3 warnings</span>
                @endif

                <div class="fv-imgs">
                    @forelse ($r->image_urls as $url)
                        <img src="{{ $url }}" alt="Face photo of {{ $r->user->name }}" loading="lazy">
                    @empty
                        <span class="fv-meta">No photos found.</span>
                    @endforelse
                </div>

                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <form method="POST" action="{{ route('admin.face.approve', $r->id) }}" style="margin:0;">
                        @csrf @method('PATCH')
                        <button type="submit" class="fv-btn ok">✓ Approve</button>
                    </form>
                    <button type="button" class="fv-btn js-reject" data-id="{{ $r->id }}" data-mode="reject" data-name="{{ $r->user->name }}">✕ Reject (unclear)</button>
                    <button type="button" class="fv-btn warn js-reject" data-id="{{ $r->id }}" data-mode="flag" data-name="{{ $r->user->name }}">⚠ Flag inappropriate</button>
                </div>
            </div>
        @empty
            <div class="fv-empty" style="margin-top:12px;">No uploads waiting for review.</div>
        @endforelse
    </div>

    {{-- ── Recently registered / reviewed ── --}}
    @if ($reviewed->count())
        <div class="fv-card">
            <h2 class="fv-h2">Recently registered or reviewed</h2>
            <p class="fv-muted">Remove a face if it was registered for the wrong person — the gate camera stops using it and the student can register again.</p>
            <div class="fv-list" style="margin-top:8px;">
                @foreach ($reviewed as $r)
                    <div class="fv-li">
                        <div style="min-width:0;">
                            <span class="fv-name">{{ $r->user->name }}</span>
                            <span class="fv-meta" style="margin-left:6px;">{{ optional($r->reviewed_at)->diffForHumans() }}</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            @if ($r->status === 'approved')
                                <span class="fv-ok">✓ Registered</span>
                                <button type="button" class="fv-btn warn sm js-reject" data-id="{{ $r->id }}" data-mode="remove" data-name="{{ $r->user->name }}">Remove</button>
                            @else
                                <span class="fv-bad">✕ {{ $r->inappropriate ? 'Rejected · flagged' : 'Rejected' }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- ── Reject / flag / remove dialog ── --}}
<div class="fv-modal" id="rejectModal" role="dialog" aria-modal="true" aria-labelledby="rjTitle">
    <div class="fv-modal-box">
        <h3 id="rjTitle" style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0 0 6px;">Reject face</h3>
        <p id="rjNote" class="fv-muted" style="margin:0 0 14px;"></p>
        <form id="rejectForm" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="inappropriate" id="rjFlag" value="0">
            <label for="rjReason" style="font-size:12.5px; font-weight:700; color:var(--slate-700);">Reason <span style="font-weight:500; color:var(--slate-400);">(optional — shown to the student)</span></label>
            <textarea name="reason" id="rjReason" maxlength="255" class="fv-textarea" placeholder="e.g. Blurry photo / not just the face"></textarea>
            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <button type="button" class="fv-btn" id="rjCancel">Cancel</button>
                <button type="submit" id="rjSubmit" class="fv-btn" style="background:#dc2626; border-color:#dc2626; color:#fff;">Confirm</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var BASE = @json(url('admin/face'));
    var MODES = {
        reject: { title: 'Reject face', note: 'Reject only (no warning). The student can take new photos.', btn: 'Confirm reject', flag: '0', reason: '' },
        flag:   { title: '⚠ Flag inappropriate', note: "This adds a WARNING. On the 3rd warning the student's account is banned.", btn: 'Confirm & warn', flag: '1', reason: '' },
        remove: { title: 'Remove registered face', note: 'The gate camera stops recognizing this student until their face is registered again at the kiosk.', btn: 'Remove face', flag: '0', reason: 'Removed by admin — please register again at the kiosk.' }
    };
    var modal = document.getElementById('rejectModal');

    document.querySelectorAll('.js-reject').forEach(function (b) {
        b.addEventListener('click', function () {
            var m = MODES[b.dataset.mode] || MODES.reject;
            document.getElementById('rejectForm').action = BASE + '/' + encodeURIComponent(b.dataset.id) + '/reject';
            document.getElementById('rjFlag').value = m.flag;
            document.getElementById('rjTitle').textContent = m.title + ' — ' + (b.dataset.name || '');
            document.getElementById('rjNote').textContent = m.note;
            document.getElementById('rjReason').value = m.reason;
            document.getElementById('rjSubmit').textContent = m.btn;
            modal.classList.add('on');
        });
    });
    function close() { modal.classList.remove('on'); }
    document.getElementById('rjCancel').addEventListener('click', close);
    modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
    if (!window.__fvEscBound) { // SPA re-runs this script; bind once
        window.__fvEscBound = true;
        document.addEventListener('keydown', function (e) {
            var m = document.getElementById('rejectModal');
            if (e.key === 'Escape' && m) m.classList.remove('on');
        });
    }

    // Kiosk QR: load the chosen section's students
    var sel = document.getElementById('fkSection'), list = document.getElementById('fkList');
    var URL_STUDENTS = @json(route('admin.face.kiosk.students'));
    var URL_ISSUE    = @json(url('admin/face/kiosk'));
    var CHIP = {
        approved: ['#16a34a', '#fff', 'Registered'],
        pending:  ['#f59e0b', '#3f2d00', 'Waiting for review'],
        rejected: ['#dc2626', '#fff', 'Rejected']
    };
    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }

    sel.addEventListener('change', function () {
        if (!sel.value) { list.textContent = 'Students appear here after you choose a section.'; return; }
        list.textContent = 'Loading…';
        fetch(URL_STUDENTS + '?section_id=' + encodeURIComponent(sel.value), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (rows) {
                if (!Array.isArray(rows) || !rows.length) { list.textContent = 'No approved students in this section yet.'; return; }
                list.innerHTML = '<div class="fv-grid">' + rows.map(function (s) {
                    var c = CHIP[s.face_status] || ['var(--slate-100)', 'var(--slate-700)', 'No face yet'];
                    var btn = s.blocked
                        ? '<span class="fv-bad" style="font-size:12px;">Blocked (3 warnings)</span>'
                        : '<button type="button" class="fv-btn sm primary" data-kiosk-issue="' + esc(URL_ISSUE + '/' + s.id) + '" data-name="' + esc(s.name) + '">'
                          + (s.face_status === 'approved' ? 'Re-register' : 'Show QR') + '</button>';
                    return '<div class="fv-row"><div style="min-width:0;"><div class="fv-name">' + esc(s.name) + '</div>'
                        + '<span class="fv-chip" style="background:' + c[0] + ';color:' + c[1] + ';">' + c[2] + '</span></div>' + btn + '</div>';
                }).join('') + '</div>';
            })
            .catch(function () { list.textContent = 'Could not load students. Please try again.'; });
    });
})();
</script>

@include('partials.face-kiosk-modal')
@endsection
