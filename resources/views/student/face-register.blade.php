@extends('layouts.app')
@section('title', 'Register Face')

@section('content')
<style>
    .ring { transition: border-color .15s ease, box-shadow .15s ease; }
    .ring-idle { border-color: rgba(255,255,255,.65); box-shadow: 0 0 0 2000px rgba(0,0,0,.25); }
    .ring-warn { border-color: #f59e0b; box-shadow: 0 0 0 2000px rgba(0,0,0,.25), 0 0 18px rgba(245,158,11,.6); }
    .ring-ok   { border-color: #22c55e; box-shadow: 0 0 0 2000px rgba(0,0,0,.25), 0 0 22px rgba(34,197,94,.85); }
</style>

<div class="max-w-2xl mx-auto px-4 py-8">

    <h1 class="text-xl font-bold text-gray-800 mb-1">Face Registration</h1>
    <p class="text-sm text-gray-500 mb-6">
        Keep your face inside the circle. It captures automatically and only keeps the
        <span class="text-green-600 font-semibold">clear</span> shots.
    </p>

    {{-- ── STATUS BANNERS ───────────────────────────────────────────── --}}
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
                    ⚠️ <b>Warning {{ $warnings }}/3:</b> An inappropriate image was rejected.
                    Capture your face only.
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

        {{-- camera picker (shown only when 2+ cameras) --}}
        <div id="camRow" class="hidden mb-3">
            <label class="block text-xs font-medium text-gray-500 mb-1">Choose camera</label>
            <select id="camSelect"
                    class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm"></select>
        </div>

        {{-- ── CAMERA CARD ──────────────────────────────────────────── --}}
        <div class="relative rounded-2xl overflow-hidden bg-black shadow-lg" style="aspect-ratio: 4 / 3;">
            <video id="cam" autoplay playsinline muted
                   class="w-full h-full object-cover" style="transform: scaleX(-1);"></video>
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div id="oval" class="ring ring-idle rounded-full border-4" style="width:55%; height:78%;"></div>
            </div>
            <div id="phase"
                 class="absolute top-3 left-0 right-0 text-center text-white text-xs font-semibold drop-shadow">
                Tap “Enable Camera” to begin
            </div>
        </div>

        {{-- progress --}}
        <div class="mt-4">
            <div class="flex justify-between text-xs text-gray-600 mb-1">
                <span id="msg">Position your face inside the circle.</span>
                <span id="countTxt" class="font-semibold">0 / 20</span>
            </div>
            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                <div id="bar" class="h-full bg-green-500" style="width:0%; transition:width .2s ease;"></div>
            </div>
        </div>

        <button id="enableBtn"
                class="w-full mt-5 py-3 rounded-xl bg-blue-600 text-white font-semibold text-sm">
            📷 Enable Camera
        </button>
        <button id="retryBtn"
                class="hidden w-full mt-3 py-2.5 rounded-xl font-semibold text-sm border border-gray-200 text-gray-600">
            🔄 Try again
        </button>

        <p class="text-[11px] text-gray-400 mt-4 text-center leading-relaxed">
            Tips: face the light, hold the phone steady, keep only your face in the circle.
        </p>
    @endif
</div>

@unless ($blocked)
<script>
(function () {
    'use strict';

    const CSRF     = '{{ csrf_token() }}';
    const POST_URL = '{{ route('student.face.store') }}';
    const TARGET   = 20;        // best (sharpest) photos to keep
    const DURATION = 6000;      // capture window in ms
    const STEP     = 150;       // ms between samples

    const video    = document.getElementById('cam');
    const oval     = document.getElementById('oval');
    const phaseEl  = document.getElementById('phase');
    const msgEl    = document.getElementById('msg');
    const countTx  = document.getElementById('countTxt');
    const bar      = document.getElementById('bar');
    const enableBt = document.getElementById('enableBtn');
    const retryBt  = document.getElementById('retryBtn');
    const camRow   = document.getElementById('camRow');
    const camSel   = document.getElementById('camSelect');
    const shCanvas = document.createElement('canvas');  // reused for sharpness

    let stream  = null;
    let running = false;
    let samples = [];   // { sh, blob }
    let peak    = 1;
    let startT  = 0;

    function msg(t)   { msgEl.textContent = t; }
    function phase(t) { phaseEl.textContent = t; }
    function ring(s)  { oval.className = 'ring ring-' + s + ' rounded-full border-4'; }

    function dataURLtoBlob(dataurl) {
        const parts = dataurl.split(',');
        const mime  = parts[0].match(/:(.*?);/)[1];
        const bin   = atob(parts[1]);
        let n = bin.length;
        const u8 = new Uint8Array(n);
        while (n--) u8[n] = bin.charCodeAt(n);
        return new Blob([u8], { type: mime });
    }

    // crop geometry: centered square (~80%) = the oval area
    function geom() {
        const vw = video.videoWidth, vh = video.videoHeight;
        const side = Math.min(vw, vh) * 0.8;
        return { vw: vw, vh: vh, side: side, sx: (vw - side) / 2, sy: (vh - side) / 2 };
    }

    // sharpness = variance of Laplacian (higher = clearer; low = blurry)
    function sharpnessOf(g) {
        const w = 120, h = 120;
        shCanvas.width = w; shCanvas.height = h;
        const ctx = shCanvas.getContext('2d');
        ctx.drawImage(video, g.sx, g.sy, g.side, g.side, 0, 0, w, h);
        const d = ctx.getImageData(0, 0, w, h).data;
        function lum(i) { return 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2]; }
        let sum = 0, sum2 = 0, cnt = 0;
        const row = w * 4;
        for (let y = 1; y < h - 1; y++) {
            for (let x = 1; x < w - 1; x++) {
                const i = (y * w + x) * 4;
                const lap = -4 * lum(i) + lum(i - 4) + lum(i + 4) + lum(i - row) + lum(i + row);
                sum += lap; sum2 += lap * lap; cnt++;
            }
        }
        const mean = sum / cnt;
        return (sum2 / cnt) - (mean * mean);
    }

    function tick() {
        if (!running) return;
        const g = geom();
        if (g.vw && g.vh) {
            const sh   = sharpnessOf(g);
            peak = Math.max(peak, sh);
            const good = sh >= peak * 0.55;

            ring(good ? 'ok' : 'warn');
            msg(good ? 'Looking good — hold steady' : 'Hold steady / move to better light');

            const c = document.createElement('canvas');
            c.width = 250; c.height = 250;
            c.getContext('2d').drawImage(video, g.sx, g.sy, g.side, g.side, 0, 0, 250, 250);
            samples.push({ sh: sh, blob: dataURLtoBlob(c.toDataURL('image/jpeg', 0.9)) });

            const elapsed = Date.now() - startT;
            bar.style.width = Math.min(100, elapsed / DURATION * 100) + '%';
            countTx.textContent = Math.min(samples.length, TARGET) + ' / ' + TARGET;

            if (elapsed >= DURATION) { running = false; finalize(); return; }
        }
        setTimeout(tick, STEP);
    }

    function finalize() {
        phase('Selecting your clearest photos…');
        samples.sort(function (a, b) { return b.sh - a.sh; });   // sharpest first
        const best = samples.slice(0, TARGET).map(function (s) { return s.blob; });
        if (best.length < 5) {
            ring('warn');
            msg('Too blurry — please try again in better light.');
            retryBt.classList.remove('hidden');
            return;
        }
        upload(best);
    }

    async function upload(blobs) {
        phase('Uploading your photos…'); msg('Please wait…'); ring('ok');
        const fd = new FormData();
        blobs.forEach(function (b, i) { fd.append('images[]', b, 'img_' + (i + 1) + '.jpg'); });
        try {
            const res  = await fetch(POST_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF }, body: fd });
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

    function startCapture() {
        samples = []; peak = 1; startT = Date.now();
        retryBt.classList.add('hidden');
        bar.style.width = '0%'; countTx.textContent = '0 / ' + TARGET;
        ring('idle'); phase('Keep your face in the circle');
        running = true;
        tick();
    }

    async function startStream(deviceId) {
        if (stream) stream.getTracks().forEach(function (t) { t.stop(); });
        const constraints = {
            video: deviceId ? { deviceId: { exact: deviceId } }
                            : { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
            audio: false,
        };
        stream = await navigator.mediaDevices.getUserMedia(constraints);
        video.srcObject = stream;
        await video.play();
    }

    async function populateCams() {
        try {
            const devs = await navigator.mediaDevices.enumerateDevices();
            const cams = devs.filter(function (d) { return d.kind === 'videoinput'; });
            camSel.innerHTML = '';
            cams.forEach(function (c, i) {
                const o = document.createElement('option');
                o.value = c.deviceId;
                o.textContent = c.label || ('Camera ' + (i + 1));
                camSel.appendChild(o);
            });
            camRow.classList.toggle('hidden', cams.length < 2);
        } catch (e) { /* ignore */ }
    }

    async function enable() {
        enableBt.disabled = true;
        phase('Starting camera…'); msg('Please wait…');
        try { await startStream(null); }
        catch (e) {
            enableBt.disabled = false; phase('Camera blocked');
            msg('Allow camera permission, then tap Enable Camera again.');
            return;
        }
        await populateCams();
        enableBt.classList.add('hidden');
        startCapture();
    }

    enableBt.addEventListener('click', enable);
    retryBt.addEventListener('click', function () { if (stream) startCapture(); else enable(); });
    camSel.addEventListener('change', async function () {
        running = false;
        try { await startStream(camSel.value); startCapture(); } catch (e) {}
    });
})();
</script>
@endunless
@endsection