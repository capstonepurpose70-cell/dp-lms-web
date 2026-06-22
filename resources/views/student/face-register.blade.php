@extends('layouts.app')
@section('title', 'Register Face')

@section('content')
<style>
    .ring { transition: border-color .15s ease, box-shadow .15s ease; }
    .ring-idle  { border-color: rgba(255,255,255,.65); box-shadow: 0 0 0 2000px rgba(0,0,0,.25); }
    .ring-warn  { border-color: #f59e0b; box-shadow: 0 0 0 2000px rgba(0,0,0,.25), 0 0 18px rgba(245,158,11,.6); }
    .ring-ready { border-color: #3b82f6; box-shadow: 0 0 0 2000px rgba(0,0,0,.25), 0 0 18px rgba(59,130,246,.7);
                  animation: pulse 1s ease-in-out infinite; }
    .ring-ok    { border-color: #22c55e; box-shadow: 0 0 0 2000px rgba(0,0,0,.25), 0 0 22px rgba(34,197,94,.85); }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.55} }
    .btn { width:100%; padding:.75rem; border-radius:.75rem; font-weight:600; font-size:.875rem; }
    .btn:disabled { opacity:.5; cursor:not-allowed; }
</style>

<div class="max-w-2xl mx-auto px-4 py-8">

    <h1 class="text-xl font-bold text-gray-800 mb-1">Face Registration</h1>
    <p class="text-sm text-gray-500 mb-6">
        Enable the camera, choose which camera to use, then tap <b>Start</b>.
        Blink when asked &mdash; the circle turns
        <span class="text-green-600 font-semibold">green</span> once verified and captures automatically.
    </p>

    @if ($blocked)
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 mb-6">
            <p class="font-bold text-red-700 text-sm">⛔ Face registration blocked</p>
            <p class="text-sm text-red-600 mt-1">
                Due to repeated violations, you can no longer register your face.
                Please visit the admin office to resolve this.
            </p>
        </div>
    @else
        @if ($warnings > 0)
            <div class="rounded-xl border border-orange-200 bg-orange-50 p-4 mb-4">
                <p class="text-sm text-orange-700">
                    ⚠️ <b>Warning {{ $warnings }}/3:</b> An inappropriate image was rejected. Capture your face only.
                </p>
            </div>
        @endif
        @if ($registration && $registration->status === 'pending')
            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 mb-4">
                <p class="font-bold text-yellow-800 text-sm">⏳ Submitted — waiting for admin verification</p>
                <p class="text-sm text-yellow-700 mt-1">You can re-capture below to replace your previous photos.</p>
            </div>
        @elseif ($registration && $registration->status === 'approved')
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 mb-4">
                <p class="font-bold text-green-800 text-sm">✅ Your face is approved</p>
                <p class="text-sm text-green-700 mt-1">You can re-register below to update your photos.</p>
            </div>
        @elseif ($registration && $registration->status === 'rejected')
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 mb-4">
                <p class="font-bold text-red-700 text-sm">❌ Your last submission was rejected</p>
                <p class="text-sm text-red-600 mt-1">Please capture clear photos of your face again.</p>
            </div>
        @endif

        {{-- camera picker --}}
        <div id="camRow" class="hidden mb-3">
            <label class="block text-xs font-medium text-gray-500 mb-1">Choose camera</label>
            <select id="camSelect" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm"></select>
        </div>

        {{-- detection status --}}
        <p id="status" class="hidden text-xs mb-3"></p>

        <div class="relative rounded-2xl overflow-hidden bg-black shadow-lg" style="aspect-ratio: 4 / 3;">
            <video id="cam" autoplay playsinline muted class="w-full h-full object-cover" style="transform: scaleX(-1);"></video>
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div id="oval" class="ring ring-idle rounded-full border-4" style="width:55%; height:78%;"></div>
            </div>
            <div id="phase" class="absolute top-3 left-0 right-0 text-center text-white text-xs font-semibold drop-shadow">
                Tap “Enable Camera” to begin
            </div>
        </div>

        <div class="mt-4">
            <div class="flex justify-between text-xs text-gray-600 mb-1">
                <span id="msg">Position your face inside the circle.</span>
                <span id="countTxt" class="font-semibold">0 / 20</span>
            </div>
            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                <div id="bar" class="h-full bg-green-500" style="width:0%; transition:width .2s ease;"></div>
            </div>
        </div>

        <button id="enableBtn" class="btn bg-blue-600 text-white mt-5">📷 Enable Camera</button>
        <button id="startBtn"  class="btn bg-green-600 text-white mt-3 hidden" disabled>▶️ Start</button>
        <button id="retryBtn"  class="btn border border-gray-200 text-gray-600 mt-3 hidden">🔄 Try again</button>

        <p class="text-[11px] text-gray-400 mt-4 text-center leading-relaxed">
            Tips: face the light, keep only your face inside the circle, then blink naturally when asked.
        </p>
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

    // tuning
    const EAR_CLOSED = 0.21, EAR_OPEN = 0.26, BLINKS_REQUIRED = 1;
    const MIN_FACE_RATIO = 0.30, CENTER_TOL = 0.22, MIN_SCORE = 0.50;

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

    function msg(t)   { msgEl.textContent = t; }
    function phase(t) { phaseEl.textContent = t; }
    function ring(s)  { oval.className = 'ring ring-' + s + ' rounded-full border-4'; }
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
            if (!liveness) { blinkCount = 0; eyeClosed = false; }
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
            if (!liveness) { blinkCount = 0; eyeClosed = false; }
            return;
        }

        if (!liveness) {
            const lm = det.landmarks;
            const e = (ear(lm.getLeftEye()) + ear(lm.getRightEye())) / 2;
            if (e < EAR_CLOSED) eyeClosed = true;
            else if (eyeClosed && e > EAR_OPEN) { eyeClosed = false; blinkCount++; }
            if (blinkCount >= BLINKS_REQUIRED) { liveness = true; }
            else { ring('ready'); msg('Face detected — please BLINK to verify'); return; }
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
            const res = await fetch(POST_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF }, body: fd });
            const data = await res.json();
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