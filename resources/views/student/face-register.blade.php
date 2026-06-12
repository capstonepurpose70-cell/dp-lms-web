@extends('layouts.app')
@section('title', 'Register Face')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">

    <h1 class="text-xl font-bold text-gray-800 mb-1">Face Registration</h1>
    <p class="text-sm text-gray-500 mb-6">
        This will be used by the attendance camera to recognize you. Only your face
        (up to the neck) is captured &mdash; automatically cropped.
    </p>

    @if ($blocked)
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 mb-6">
            <p class="font-bold text-red-700 text-sm">⛔ Face registration blocked</p>
            <p class="text-sm text-red-600 mt-1">
                Due to repeated violations (inappropriate images), you can no longer
                register your face. Please visit the admin office to resolve this.
            </p>
        </div>
    @elseif (!$enrolled)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
            <p class="font-bold text-amber-800 text-sm mb-1">📋 Enrollment required</p>
            <p class="text-sm text-amber-700">
                You need to be <b>enrolled in a section</b> before you can register your face.
                Please submit your enrollment and wait for faculty approval first. Once you have
                a section and teacher, come back here to register your face for attendance.
            </p>
            <a href="{{ route('student.enroll') }}"
               class="inline-block mt-3 px-4 py-2 rounded-lg text-white text-sm font-semibold"
               style="background:#d97706;">Go to Enrollment</a>
        </div>
    @else
        @if ($warnings > 0)
            <div class="rounded-xl border border-orange-200 bg-orange-50 p-4 mb-4">
                <p class="text-sm text-orange-700">
                    ⚠️ <b>Warning {{ $warnings }}/3:</b> An inappropriate image was rejected.
                    Capture your face only &mdash; on the third warning, your account will be
                    blocked from face registration and you may be called to the office.
                </p>
            </div>
        @endif

        @if ($registration && $registration->status === 'pending')
            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 mb-6">
                <p class="font-bold text-yellow-700 text-sm">🕒 Pending verification</p>
                <p class="text-sm text-yellow-600 mt-1">
                    {{ $registration->images_count }} images submitted. Waiting for admin
                    verification. You may capture again &mdash; it will replace the previous set.
                </p>
            </div>
        @elseif ($registration && $registration->status === 'approved')
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 mb-6">
                <p class="font-bold text-green-700 text-sm">✅ Your face is verified!</p>
                <p class="text-sm text-green-600 mt-1">
                    The attendance camera can now recognize you. If you want to update it
                    (e.g. new haircut), just capture again below.
                </p>
            </div>
        @elseif ($registration && $registration->status === 'rejected')
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 mb-6">
                <p class="font-bold text-red-700 text-sm">❌ Rejected</p>
                <p class="text-sm text-red-600 mt-1">
                    {{ $registration->reject_reason ?: 'Images were unclear or invalid.' }}
                    &mdash; Please capture again below.
                </p>
            </div>
        @endif

        <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 mb-5">
            <p class="text-sm font-semibold text-blue-800 mb-1">Before you start:</p>
            <ul class="text-sm text-blue-700 space-y-0.5" style="list-style:disc;padding-left:18px;">
                <li><b>Remove your eyeglasses</b> (and hats / face masks).</li>
                <li>Make sure your face is well-lit and clearly visible.</li>
                <li>Only one person should be in front of the camera.</li>
                <li>Look at the camera and slowly turn your head left / right / up.</li>
            </ul>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-5"
             style="box-shadow:0 2px 16px rgba(0,0,0,0.05);">

            <div id="camWrap" class="relative mx-auto rounded-xl overflow-hidden bg-black"
                 style="max-width:480px; aspect-ratio:4/3;">
                <video id="cam" autoplay playsinline muted
                       class="w-full h-full object-cover" style="transform:scaleX(-1);"></video>
                <div id="camMsg"
                     class="absolute bottom-0 left-0 right-0 text-center text-white text-sm py-2"
                     style="background:rgba(0,0,0,.55);">Preparing&hellip;</div>
            </div>

            <div id="camSelectWrap" class="hidden mt-3">
                <label class="block text-xs font-semibold text-gray-500 mb-1">📷 Choose camera</label>
                <select id="camSelect"
                        class="w-full text-sm rounded-lg border border-gray-200 px-3 py-2 bg-white text-gray-700">
                </select>
                <p class="text-[11px] text-gray-400 mt-1">Pumili kung built-in camera o USB webcam ang gagamitin.</p>
            </div>

            <div class="mt-4">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Capture progress</span><span id="countTxt">0 / 20</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2.5">
                    <div id="bar" class="h-2.5 rounded-full"
                         style="width:0%;background:#2563eb;transition:width .3s;"></div>
                </div>
            </div>

            <button id="enableBtn"
                class="hidden w-full mt-4 py-2.5 rounded-xl text-white font-semibold text-sm"
                style="background:#2563eb;">🎥 Enable Camera</button>

            <button id="retryBtn"
                class="hidden w-full mt-3 py-2.5 rounded-xl font-semibold text-sm border border-gray-200 text-gray-600">
                🔄 Try again</button>

            <p id="phase" class="text-center text-xs text-gray-400 mt-3">
                Capturing starts automatically once a clear face is detected.
            </p>
        </div>
    @endif
</div>

@if (!$blocked && $enrolled)
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const CSRF      = '{{ csrf_token() }}';
const POST_URL  = '{{ route('student.face.store') }}';
const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@0.22.2/weights';
const TARGET    = 21;
const FRONT_END = 7, LEFT_END = 14;  // pose phases
const GLASSES_EDGE_THRESHOLD = 26;

const video   = document.getElementById('cam');
const camMsg  = document.getElementById('camMsg');
const bar     = document.getElementById('bar');
const countTx = document.getElementById('countTxt');
const enableBt= document.getElementById('enableBtn');
const retryBt = document.getElementById('retryBtn');
const phase   = document.getElementById('phase');

let captures = [], modelsReady = false, camReady = false, finished = false;
let currentStream = null, loopStarted = false;
const camSelectWrap = document.getElementById('camSelectWrap');
const camSelect     = document.getElementById('camSelect');
let lastShot = 0;

function setMsg(t, good) {
    camMsg.textContent = t;
    camMsg.style.background = good ? 'rgba(22,163,74,.85)' : 'rgba(0,0,0,.55)';
}

async function boot() {
    if (!window.isSecureContext) {
        setMsg('HTTPS (or localhost) is required for the camera.'); return;
    }
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        setMsg('This browser does not support the camera. Use Chrome / Brave / Edge.'); return;
    }
    setMsg('Loading face detector...');
    try {
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        ]);
        modelsReady = true;
    } catch (e) {
        setMsg('Could not load the face detector (internet issue). Refresh to retry.'); return;
    }
    startCamera();
}

async function startCamera(deviceId) {
    setMsg('Requesting camera... please tap ALLOW.');
    // Stop any previous stream before switching cameras
    if (currentStream) { currentStream.getTracks().forEach(t => t.stop()); currentStream = null; }

    const videoConstraints = deviceId
        ? { deviceId: { exact: deviceId } }
        : { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' };

    let stream;
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: videoConstraints, audio: false });
    } catch (e1) {
        if (e1.name === 'OverconstrainedError' || e1.name === 'ConstraintNotSatisfiedError') {
            try { stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false }); }
            catch (e2) { return camFail(e2); }
        } else { return camFail(e1); }
    }
    currentStream = stream;
    video.srcObject = stream;
    camReady = true;
    enableBt.classList.add('hidden');
    retryBt.classList.add('hidden');
    await populateCameras();
    video.onloadedmetadata = () => { if (!loopStarted) { loopStarted = true; loop(); } };
}

// List available cameras (webcam + built-in) and show a picker if there are 2+
async function populateCameras() {
    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        const cams = devices.filter(d => d.kind === 'videoinput');
        if (cams.length <= 1) { camSelectWrap.classList.add('hidden'); return; }
        const activeId = currentStream && currentStream.getVideoTracks()[0]
            ? currentStream.getVideoTracks()[0].getSettings().deviceId : null;
        camSelect.innerHTML = '';
        cams.forEach((c, i) => {
            const opt = document.createElement('option');
            opt.value = c.deviceId;
            opt.textContent = c.label || ('Camera ' + (i + 1));
            if (c.deviceId === activeId) opt.selected = true;
            camSelect.appendChild(opt);
        });
        camSelectWrap.classList.remove('hidden');
    } catch (e) { /* enumerate not allowed yet — ignore */ }
}

// Switch camera when the user picks a different one
camSelect.addEventListener('change', () => { startCamera(camSelect.value); });

function camFail(e) {
    camReady = false;
    if (e.name === 'NotAllowedError' || e.name === 'PermissionDeniedError') {
        camMsg.innerHTML = 'Camera is BLOCKED. Click the <b>lock / camera icon</b> in the address bar '
            + 'then set Camera to <b>Allow</b>, then tap the button below.';
        enableBt.classList.remove('hidden');
    } else if (e.name === 'NotFoundError' || e.name === 'DevicesNotFoundError') {
        setMsg('No camera found on this device.');
    } else if (e.name === 'NotReadableError' || e.name === 'TrackStartError') {
        camMsg.innerHTML = 'The camera is in use by another app, or blocked by Windows privacy. '
            + 'Close other camera apps, allow desktop apps in Camera privacy settings, then tap below.';
        enableBt.classList.remove('hidden');
    } else {
        setMsg('Camera error: ' + (e.name || e.message || 'unknown'));
        enableBt.classList.remove('hidden');
    }
}

enableBt.addEventListener('click', () => { enableBt.classList.add('hidden'); startCamera(); });
retryBt.addEventListener('click', () => location.reload());

async function loop() {
    if (finished || !camReady || !modelsReady) return;

    const res = await faceapi
        .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
        .withFaceLandmarks();

    if (res.length === 0) {
        setMsg('No face detected - face the camera.');
    } else if (res.length > 1) {
        setMsg('Only one person should be in front of the camera.');
    } else {
        const det = res[0];
        const b   = det.detection.box;
        if (b.width < video.videoWidth * 0.20) {
            setMsg('Move a little closer.');
        } else if (wearingGlasses(det.landmarks)) {
            setMsg('Please remove your eyeglasses to continue.');
        } else {
            const now = Date.now();
            if (now - lastShot > 300) { grabFace(b); lastShot = now; }
            // Guide the student through 3 poses: front -> left -> right
            let pose;
            if (captures.length < FRONT_END)      pose = 'Look straight at the camera';
            else if (captures.length < LEFT_END)  pose = 'Now slowly turn your head to your LEFT';
            else                                   pose = 'Now slowly turn your head to your RIGHT';
            setMsg(pose + '  (' + captures.length + '/' + TARGET + ')', true);
        }
    }

    countTx.textContent = captures.length + ' / ' + TARGET;
    bar.style.width = (captures.length / TARGET * 100) + '%';

    if (captures.length >= TARGET) { finished = true; upload(); return; }
    setTimeout(loop, 120);
}

function wearingGlasses(landmarks) {
    const pts = landmarks.positions;
    const le  = pts[36], re = pts[45];
    const top = Math.min(pts[37].y, pts[44].y);
    const bot = Math.max(pts[41].y, pts[46].y);
    const padY = (bot - top) * 1.1;

    const sx = Math.max(0, le.x - 6);
    const sy = Math.max(0, top - padY);
    const sw = Math.min(video.videoWidth  - sx, (re.x - le.x) + 12);
    const sh = Math.min(video.videoHeight - sy, (bot - top) + padY * 2);
    if (sw < 10 || sh < 6) return false;

    const W = 64, H = 24;
    const c = document.createElement('canvas'); c.width = W; c.height = H;
    const ctx = c.getContext('2d', { willReadFrequently: true });
    ctx.drawImage(video, sx, sy, sw, sh, 0, 0, W, H);
    const d = ctx.getImageData(0, 0, W, H).data;

    const g = new Float32Array(W * H);
    for (let i = 0; i < W * H; i++) {
        g[i] = 0.299 * d[i*4] + 0.587 * d[i*4+1] + 0.114 * d[i*4+2];
    }
    let sum = 0, n = 0;
    for (let y = 1; y < H; y++) {
        for (let x = 0; x < W; x++) {
            sum += Math.abs(g[y*W + x] - g[(y-1)*W + x]); n++;
        }
    }
    return (sum / n) > GLASSES_EDGE_THRESHOLD;
}

function grabFace(b) {
    const mx = b.width * 0.25, mTop = b.height * 0.35, mBot = b.height * 0.55;
    const sx = Math.max(0, b.x - mx),
          sy = Math.max(0, b.y - mTop),
          sw = Math.min(video.videoWidth  - sx, b.width  + mx * 2),
          sh = Math.min(video.videoHeight - sy, b.height + mTop + mBot);
    const c = document.createElement('canvas'); c.width = 250; c.height = 250;
    c.getContext('2d').drawImage(video, sx, sy, sw, sh, 0, 0, 250, 250);
    c.toBlob(blob => { if (blob && captures.length < TARGET) captures.push(blob); }, 'image/jpeg', 0.85);
}

async function upload() {
    setMsg('Uploading...'); phase.textContent = 'Uploading your images...';
    await new Promise(r => setTimeout(r, 600));

    const fd = new FormData();
    captures.forEach((blob, i) => fd.append('images[]', blob, 'img_' + (i + 1) + '.jpg'));

    try {
        const res  = await fetch(POST_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF }, body: fd });
        const data = await res.json();
        if (data.ok) {
            setMsg(data.message, true);
            phase.textContent = 'Done! Waiting for admin verification.';
            (video.srcObject?.getTracks() || []).forEach(t => t.stop());
            setTimeout(() => location.reload(), 2500);
        } else {
            setMsg(data.message || 'Upload failed');
            retryBt.classList.remove('hidden');
        }
    } catch (e) {
        setMsg('Network error - please try again.');
        retryBt.classList.remove('hidden');
    }
}

boot();
</script>
@endif
@endsection