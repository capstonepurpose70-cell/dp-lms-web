@extends('layouts.app')
@section('title', 'Register Face')

@section('content')
<style>
    /* Plain CSS (the Tailwind build is stale). Only used when FACE_SELF_UPLOAD=true. */
    .fr-wrap { max-width: 680px; margin: 0 auto; }
    .fr-wrap .hidden { display: none !important; }
    .fr-title { font-size: 24px; font-weight: 800; color: var(--text-1); }
    .fr-sub { font-size: 13.5px; color: var(--text-2); margin: 4px 0 18px; line-height: 1.55; }
    .fr-sub b.ok { color: #15803D; }

    .fr-alert { border-radius: 12px; padding: 12px 16px; margin-bottom: 14px; font-size: 13.5px; line-height: 1.5; border: 1px solid; }
    .fr-alert b { display: block; margin-bottom: 2px; }
    .fr-alert.bad  { background: #FEF2F2; border-color: #FECACA; color: #991B1B; }
    .fr-alert.warn { background: #FFF7ED; border-color: #FED7AA; color: #9A3412; }
    .fr-alert.wait { background: #FEFCE8; border-color: #FDE68A; color: #854D0E; }
    .fr-alert.ok   { background: #F0FDF4; border-color: #BBF7D0; color: #166534; }

    .fr-label { display: block; font-size: 12.5px; font-weight: 700; color: var(--text-2); margin-bottom: 5px; }
    .fr-select { width: 100%; min-height: 44px; border: 1.5px solid var(--border); border-radius: 10px; padding: 8px 12px; font-size: 14px; font-family: inherit; background: #fff; margin-bottom: 12px; }
    .fr-status { font-size: 13px; font-weight: 600; margin-bottom: 10px; }

    .fr-stage { position: relative; border-radius: 18px; overflow: hidden; background: #000; box-shadow: var(--shadow-md); aspect-ratio: 4 / 3; }
    .fr-stage video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); display: block; }
    .fr-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; pointer-events: none; }
    .fr-oval { width: 55%; height: 78%; border-radius: 50%; border: 4px solid; }
    .ring { transition: border-color .15s ease, box-shadow .15s ease; }
    .ring-idle  { border-color: rgba(255,255,255,.65); box-shadow: 0 0 0 2000px rgba(0,0,0,.25); }
    .ring-warn  { border-color: #f59e0b; box-shadow: 0 0 0 2000px rgba(0,0,0,.25), 0 0 18px rgba(245,158,11,.6); }
    .ring-ready { border-color: #3b82f6; box-shadow: 0 0 0 2000px rgba(0,0,0,.25), 0 0 18px rgba(59,130,246,.7); animation: frPulse 1s ease-in-out infinite; }
    .ring-ok    { border-color: #22c55e; box-shadow: 0 0 0 2000px rgba(0,0,0,.25), 0 0 22px rgba(34,197,94,.85); }
    @keyframes frPulse { 0%,100% { opacity: 1 } 50% { opacity: .55 } }
    .fr-phase { position: absolute; top: 12px; left: 0; right: 0; text-align: center; color: #fff; font-size: 13px; font-weight: 700; text-shadow: 0 1px 4px rgba(0,0,0,.7); }

    .fr-progress-row { display: flex; justify-content: space-between; gap: 10px; font-size: 13px; color: var(--text-2); margin: 14px 0 6px; }
    .fr-progress { height: 8px; background: #E2E8F0; border-radius: 99px; overflow: hidden; }
    .fr-bar { height: 100%; width: 0%; background: #16A34A; transition: width .2s ease; }

    .fr-btn { display: block; width: 100%; min-height: 48px; margin-top: 12px; border-radius: 12px; font-weight: 700; font-size: 15px; font-family: inherit; cursor: pointer; border: none; }
    .fr-btn:disabled { opacity: .5; cursor: not-allowed; }
    .fr-btn.primary { background: var(--accent); color: #fff; }
    .fr-btn.go { background: #15803D; color: #fff; }
    .fr-btn.ghost { background: #fff; color: var(--text-1); border: 1.5px solid var(--border); }
    .fr-tip { font-size: 12.5px; color: var(--text-3); margin-top: 14px; text-align: center; line-height: 1.55; }
</style>

<div class="fr-wrap">
    <h1 class="fr-title">Face Registration</h1>
    <p class="fr-sub">
        Enable the camera, choose which camera to use, then tap <b>Start</b>.
        Blink when asked — the circle turns <b class="ok">green</b> once verified and captures automatically.
    </p>

    @if ($blocked)
        <div class="fr-alert bad" role="alert">
            <b>Face registration blocked</b>
            Due to repeated violations, you can no longer register your face. Please visit the admin office.
        </div>
    @else
        @if ($warnings > 0)
            <div class="fr-alert warn"><b>Warning {{ $warnings }}/3</b>An inappropriate image was rejected. Capture your face only.</div>
        @endif
        @if ($registration && $registration->status === 'pending')
            <div class="fr-alert wait"><b>Submitted — waiting for admin verification</b>You can capture again below to replace your photos.</div>
        @elseif ($registration && $registration->status === 'approved')
            <div class="fr-alert ok"><b>Your face is approved ✓</b>You can register again below to update your photos.</div>
        @elseif ($registration && $registration->status === 'rejected')
            <div class="fr-alert bad"><b>Your last photos were not accepted</b>{{ $registration->reject_reason ?: 'Please capture clear photos of your face again.' }}</div>
        @endif

        <div id="camRow" class="hidden">
            <label class="fr-label" for="camSelect">Choose camera</label>
            <select id="camSelect" class="fr-select"></select>
        </div>

        <p id="status" class="fr-status hidden" role="status"></p>

        <div class="fr-stage">
            <video id="cam" autoplay playsinline muted></video>
            <div class="fr-overlay"><div id="oval" class="fr-oval ring ring-idle"></div></div>
            <div id="phase" class="fr-phase" aria-live="polite">Tap “Enable camera” to begin</div>
        </div>

        <div class="fr-progress-row">
            <span id="msg" aria-live="polite">Position your face inside the circle.</span>
            <b id="countTxt">0 / 20</b>
        </div>
        <div class="fr-progress" role="progressbar" aria-label="Photos captured"><div id="bar" class="fr-bar"></div></div>

        <button type="button" id="enableBtn" class="fr-btn primary">Enable camera</button>
        <button type="button" id="startBtn" class="fr-btn go hidden" disabled>Start</button>
        <button type="button" id="retryBtn" class="fr-btn ghost hidden">Try again</button>

        <p class="fr-tip">Tips: face the light, keep only your face inside the circle, then blink naturally when asked.</p>
    @endif
</div>

@unless ($blocked)
<script src="{{ asset('face/face-api.min.js') }}" onerror="window.__localFaceFailed=true"></script>
<script>
(function () {
    'use strict';

    const CSRF       = '{{ csrf_token() }}';
    const POST_URL   = '{{ route('student.face.store') }}';
    const LOCAL_LIB  = '{{ asset('face/face-api.min.js') }}';
    const LOCAL_MODELS = '{{ asset('face/models') }}';
    const TARGET     = 20;

    // fallback sources (used only if self-hosted files are missing)
    const LIB_FALLBACKS = [
        'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js',
        'https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js'
    ];
    const MODEL_FALLBACKS = [
        'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@0.22.2/weights',
        'https://justadudewhohacks.github.io/face-api.js/models'
    ];

    // tuning — lenient: adaptive blink + auto-verify once the face is steady
    const EAR_CLOSED = 0.25, EAR_OPEN = 0.27, BLINKS_REQUIRED = 1;
    const HOLD_VERIFY_MS = 3200;   // steady face for ~3.2s = verified (even if the blink is missed)
    const MIN_FACE_RATIO = 0.26, CENTER_TOL = 0.24, MIN_SCORE = 0.45;

    const video    = document.getElementById('cam');
    const oval     = document.getElementById('oval');
    const phaseEl  = document.getElementById('phase');
    const msgEl    = document.getElementById('msg');
    const countTx  = document.getElementById('countTxt');
    const bar      = document.getElementById('bar');
    const statusEl = document.getElementById('status');
    const enableBt = document.getElementById('enableBtn');
    const startBt  = document.getElementById('startBtn');
    const retryBt  = document.getElementById('retryBtn');
    const camRow   = document.getElementById('camRow');
    const camSel   = document.getElementById('camSelect');

    let stream = null, modelsReady = false;
    let running = false, finished = false, liveness = false, eyeClosed = false;
    let blinkCount = 0, captures = [];
    let earBase = 0, goodSince = 0; // adaptive blink baseline + steady-face timer

    function msg(t)   { msgEl.textContent = t; }
    function phase(t) { phaseEl.textContent = t; }
    function ring(s)  { oval.className = 'fr-oval ring ring-' + s; }
    function status(t, color) {
        statusEl.textContent = t;
        statusEl.style.color = color;
        statusEl.classList.remove('hidden');
    }

    function dist(a, b) { return Math.hypot(a.x - b.x, a.y - b.y); }
    function ear(e) { return (dist(e[1], e[5]) + dist(e[2], e[4])) / (2 * dist(e[0], e[3])); }

    function dataURLtoBlob(d) {
        const p = d.split(','), mime = p[0].match(/:(.*?);/)[1], bin = atob(p[1]);
        let n = bin.length; const u8 = new Uint8Array(n);
        while (n--) u8[n] = bin.charCodeAt(n);
        return new Blob([u8], { type: mime });
    }

    function loadScript(src) {
        return new Promise(function (res, rej) {
            const s = document.createElement('script');
            s.src = src; s.onload = res; s.onerror = function () { rej(new Error('fail ' + src)); };
            document.head.appendChild(s);
        });
    }

    async function ensureLib() {
        if (typeof faceapi !== 'undefined') return;
        const tries = (window.__localFaceFailed ? [] : [LOCAL_LIB]).concat(LIB_FALLBACKS);
        for (let i = 0; i < tries.length; i++) {
            try { await loadScript(tries[i]); if (typeof faceapi !== 'undefined') return; } catch (e) {}
        }
        throw new Error('lib unavailable');
    }

    async function loadModels() {
        const sources = [LOCAL_MODELS].concat(MODEL_FALLBACKS);
        for (let i = 0; i < sources.length; i++) {
            try {
                await faceapi.nets.tinyFaceDetector.loadFromUri(sources[i]);
                await faceapi.nets.faceLandmark68Net.loadFromUri(sources[i]);
                modelsReady = true; return;
            } catch (e) {}
        }
        throw new Error('models unavailable');
    }

    function grabFrame(b, vw, vh) {
        if (captures.length >= TARGET) return;
        const side = Math.min(Math.max(b.width, b.height) * 1.8, vw, vh);
        let sx = (b.x + b.width / 2) - side / 2;
        let sy = (b.y + b.height / 2) - side / 2;
        sx = Math.max(0, Math.min(sx, vw - side));
        sy = Math.max(0, Math.min(sy, vh - side));
        const c = document.createElement('canvas');
        c.width = 250; c.height = 250;
        c.getContext('2d').drawImage(video, sx, sy, side, side, 0, 0, 250, 250);
        captures.push(dataURLtoBlob(c.toDataURL('image/jpeg', 0.9)));
    }

    function process(det) {
        if (finished) return;

        // HARD GATE: no face -> never capture
        if (!det) {
            ring('idle'); msg('Position your face inside the circle');
            if (!liveness) { blinkCount = 0; eyeClosed = false; goodSince = 0; }
            return;
        }

        const b = det.detection.box, vw = video.videoWidth, vh = video.videoHeight;
        if (!vw || !vh) return;

        const ratio = b.width / vw;
        const cx = (b.x + b.width / 2) / vw, cy = (b.y + b.height / 2) / vh;
        const centered = Math.abs(cx - 0.5) < CENTER_TOL && Math.abs(cy - 0.5) < CENTER_TOL;
        const good = ratio >= MIN_FACE_RATIO && centered && det.detection.score >= MIN_SCORE;

        if (!good) {
            ring('warn');
            if (ratio < MIN_FACE_RATIO)  msg('Move a bit closer to the camera');
            else if (!centered)          msg('Center your face inside the circle');
            else                         msg('Hold still so your face is clear');
            if (!liveness) { blinkCount = 0; eyeClosed = false; goodSince = 0; }
            return;
        }

        if (!liveness) {
            const lm = det.landmarks;
            const e = (ear(lm.getLeftEye()) + ear(lm.getRightEye())) / 2;
            // Adaptive: baseline = the widest "open eyes" seen for THIS person —
            // so it works with small eyes, glasses, or from a distance.
            if (e > earBase) earBase = e;
            const closeTh = Math.max(earBase * 0.80, EAR_CLOSED);
            if (e < closeTh) eyeClosed = true;
            else if (eyeClosed && e > closeTh + 0.015) { eyeClosed = false; blinkCount++; }
            // Fallback: a steady, clear face for ~3s passes —
            // it never gets stuck on "please blink".
            if (!goodSince) goodSince = Date.now();
            if (blinkCount >= BLINKS_REQUIRED || (Date.now() - goodSince) >= HOLD_VERIFY_MS) {
                liveness = true;
            } else {
                ring('ready');
                msg('Face detected — blink once, or hold steady to verify');
                return;
            }
        }

        ring('ok');
        grabFrame(b, vw, vh);
        countTx.textContent = captures.length + ' / ' + TARGET;
        bar.style.width = (captures.length / TARGET * 100) + '%';
        msg('Verified! Capturing… keep looking (' + captures.length + '/' + TARGET + ')');
        if (captures.length >= TARGET) { finished = true; running = false; upload(); }
    }

    async function tick() {
        if (!running) return;
        try {
            const det = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.4 }))
                .withFaceLandmarks();
            process(det);
        } catch (e) {}
        if (running) setTimeout(tick, 100);
    }

    function startDetect() {
        if (!modelsReady) { msg('Face detection is not ready.'); return; }
        finished = false; liveness = false; eyeClosed = false; blinkCount = 0; captures = [];
        earBase = 0; goodSince = 0;
        countTx.textContent = '0 / ' + TARGET; bar.style.width = '0%';
        retryBt.classList.add('hidden'); startBt.classList.add('hidden');
        ring('idle'); phase('Look at the camera and blink');
        running = true; tick();
    }

    async function startStream(deviceId) {
        if (stream) stream.getTracks().forEach(function (t) { t.stop(); });
        const constraints = {
            video: deviceId ? { deviceId: { exact: deviceId } }
                            : { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
            audio: false
        };
        stream = await navigator.mediaDevices.getUserMedia(constraints);
        video.srcObject = stream; await video.play();
    }

    async function populateCams() {
        try {
            const devs = await navigator.mediaDevices.enumerateDevices();
            const cams = devs.filter(function (d) { return d.kind === 'videoinput'; });
            camSel.innerHTML = '';
            cams.forEach(function (c, i) {
                const o = document.createElement('option');
                o.value = c.deviceId; o.textContent = c.label || ('Camera ' + (i + 1));
                camSel.appendChild(o);
            });
            if (cams.length > 0) camRow.classList.remove('hidden');
        } catch (e) {}
    }

    async function enable() {
        enableBt.disabled = true;
        phase('Starting…'); status('Loading face detection…', '#6b7280');
        try { await ensureLib(); }
        catch (e) { enableBt.disabled = false; status('✗ Face detection unavailable — capture disabled.', '#dc2626'); phase('Load failed'); return; }
        try { await loadModels(); }
        catch (e) { enableBt.disabled = false; status('✗ Could not load face models — capture disabled.', '#dc2626'); phase('Load failed'); return; }
        try { await startStream(null); }
        catch (e) { enableBt.disabled = false; status('✗ Camera blocked. Allow permission and retry.', '#dc2626'); phase('Camera blocked'); return; }

        await populateCams();
        status('✓ Face detection ready. Choose your camera, then tap Start.', '#16a34a');
        phase('Choose camera, then tap Start');
        msg('Pick your camera below, then tap Start.');
        enableBt.classList.add('hidden');
        startBt.classList.remove('hidden');
        startBt.disabled = false;     // only enabled because models truly loaded
    }

    async function upload() {
        phase('Uploading your photos…'); msg('Please wait…'); ring('ok');
        const fd = new FormData();
        captures.forEach(function (blob, i) { fd.append('images[]', blob, 'img_' + (i + 1) + '.jpg'); });
        try {
            // Ask for JSON: otherwise a validation error comes back as an HTML redirect
            const res = await fetch(POST_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: fd });
            const data = await res.json().catch(function () { return {}; });
            if (data.ok) {
                phase('Done! Waiting for admin verification.');
                msg(data.message || 'Submitted successfully.');
                if (stream) stream.getTracks().forEach(function (t) { t.stop(); });
                setTimeout(function () { location.reload(); }, 2500);
            } else {
                msg(data.message || 'Upload failed. Please try again.');
                retryBt.classList.remove('hidden');
            }
        } catch (e) {
            msg('Network error — please try again.');
            retryBt.classList.remove('hidden');
        }
    }

    enableBt.addEventListener('click', enable);
    startBt.addEventListener('click', startDetect);
    retryBt.addEventListener('click', function () {
        if (modelsReady && stream) startDetect(); else enable();
    });
    camSel.addEventListener('change', async function () {
        try { await startStream(camSel.value); } catch (e) {}
    });
})();
</script>
@endunless
@endsection