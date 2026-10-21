{{--
    One-time QR window for registering a face at the Raspberry Pi kiosk (admin + teacher pages).
    Any button with data-kiosk-issue="{POST url}" (and data-name) opens it.
    The QR SVG comes from our own server (BaconQrCode), so inserting it as HTML is safe.
--}}
<style>
    .fk-back { position:fixed; inset:0; z-index:1200; background:rgba(15,23,42,.55); display:none; align-items:center; justify-content:center; padding:16px; }
    .fk-back.on { display:flex; }
    .fk-box { width:min(420px, 100%); background:#fff; border-radius:20px; padding:22px; text-align:center; box-shadow:0 24px 60px rgba(0,0,0,.25); color:#0f172a; }
    .fk-title { font-size:17px; font-weight:800; margin:0 0 2px; }
    .fk-sub { font-size:13px; color:#64748b; margin:0 0 14px; line-height:1.5; }
    .fk-qr { width:260px; height:260px; margin:0 auto; display:grid; place-items:center; background:#fff; border:1px solid #e2e8f0; border-radius:14px; }
    .fk-qr svg { width:240px; height:240px; display:block; }
    .fk-state { margin-top:12px; font-size:14px; font-weight:700; min-height:22px; }
    .fk-state.wait { color:#1d4ed8; } .fk-state.ok { color:#15803d; } .fk-state.bad { color:#b91c1c; }
    .fk-timer { font-size:13px; color:#64748b; margin-top:4px; font-variant-numeric:tabular-nums; }
    .fk-steps { text-align:left; font-size:12.5px; color:#475569; margin:14px 0 0; padding-left:18px; line-height:1.6; }
    .fk-btns { display:flex; gap:8px; justify-content:center; margin-top:16px; flex-wrap:wrap; }
    .fk-btn { height:40px; padding:0 16px; border-radius:10px; font-size:13.5px; font-weight:700; cursor:pointer; border:1px solid #e2e8f0; background:#fff; color:#0f172a; }
    .fk-btn.primary { background:#1d4ed8; border-color:#1d4ed8; color:#fff; }
    .fk-check { width:120px; height:120px; border-radius:999px; background:rgba(22,163,74,.12); color:#16a34a; display:grid; place-items:center; margin:40px auto; }
    .fk-check svg { width:64px; height:64px; }
</style>

<div class="fk-back" id="fkBack" role="dialog" aria-modal="true" aria-labelledby="fkTitle">
    <div class="fk-box">
        <h3 class="fk-title" id="fkTitle">Register face at the kiosk</h3>
        <p class="fk-sub" id="fkSub">Loading…</p>
        <div class="fk-qr" id="fkQr"></div>
        <div class="fk-state wait" id="fkState"></div>
        <div class="fk-timer" id="fkTimer"></div>
        <ol class="fk-steps">
            <li>The student holds this screen up to the Raspberry Pi camera.</li>
            <li>The Pi greets them by name, then takes about 10 photos — look straight at the camera.</li>
            <li>This window turns green when the face is saved. It works once and expires in 10 minutes.</li>
        </ol>
        <div class="fk-btns">
            <button type="button" class="fk-btn" id="fkClose">Close</button>
            <button type="button" class="fk-btn primary" id="fkAgain" style="display:none;">Make a new QR</button>
        </div>
    </div>
</div>

<script>
(function () {
    // Bind once per page load (admin layout re-runs page scripts on SPA navigation)
    if (window.__dpFaceKioskBound) return;
    window.__dpFaceKioskBound = true;

    var poll = null, tick = null, lastUrl = null, lastName = '', changed = false;
    var $ = function (id) { return document.getElementById(id); };
    var csrf = function () { var m = document.querySelector('meta[name="csrf-token"]'); return m ? m.content : ''; };

    function stop() { clearInterval(poll); clearInterval(tick); poll = tick = null; }
    function setState(cls, text) { var s = $('fkState'); s.className = 'fk-state ' + cls; s.textContent = text; }

    function issue(url, name) {
        stop(); lastUrl = url; lastName = name || '';
        $('fkBack').classList.add('on');
        $('fkSub').textContent = 'Creating a QR for ' + lastName + '…';
        $('fkQr').innerHTML = ''; $('fkTimer').textContent = ''; $('fkAgain').style.display = 'none';
        setState('wait', '');

        fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
            .then(function (res) {
                var d = res.d;
                if (!res.ok || !d.ok) { $('fkSub').textContent = ''; setState('bad', d.message || 'Could not create the QR.'); return; }
                $('fkSub').textContent = 'For ' + d.student + ' only.';
                $('fkQr').innerHTML = d.qr_svg;
                setState('wait', 'Waiting for the kiosk…');
                var skew = Date.now() - Date.parse(d.server_time), exp = Date.parse(d.expires_at);
                tick = setInterval(function () {
                    var left = Math.max(0, exp - (Date.now() - skew)), s = Math.floor(left / 1000);
                    $('fkTimer').textContent = 'Expires in ' + Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0');
                }, 1000);
                poll = setInterval(function () {
                    fetch(d.status_url, { headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (st) {
                            if (st.status === 'registered') {
                                stop(); changed = true;
                                $('fkQr').innerHTML = '<div class="fk-check"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>';
                                $('fkTimer').textContent = ''; setState('ok', '✓ Face registered for ' + d.student + '.');
                            } else if (st.status === 'expired' || st.status === 'cancelled') {
                                stop(); $('fkQr').innerHTML = ''; $('fkTimer').textContent = '';
                                setState('bad', st.status === 'expired' ? 'This QR expired.' : 'A newer QR replaced this one.');
                                $('fkAgain').style.display = '';
                            }
                        }).catch(function () {});
                }, 3000);
            })
            .catch(function () { setState('bad', 'Network error — please try again.'); });
    }

    function close() {
        stop(); var b = $('fkBack'); if (b) b.classList.remove('on');
        if (changed) { changed = false; window.location.reload(); }  // refresh the face statuses
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-kiosk-issue]');
        if (btn) { e.preventDefault(); issue(btn.getAttribute('data-kiosk-issue'), btn.getAttribute('data-name')); return; }
        if (e.target.id === 'fkClose' || e.target.id === 'fkBack') { close(); return; }
        if (e.target.id === 'fkAgain' && lastUrl) { issue(lastUrl, lastName); }
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && $('fkBack') && $('fkBack').classList.contains('on')) close(); });
})();
</script>
