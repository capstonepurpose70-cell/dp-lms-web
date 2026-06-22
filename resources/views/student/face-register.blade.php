@extends('layouts.app')
@section('title', 'Register Face')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">

    <h1 class="text-xl font-bold text-gray-800 mb-1">Face Registration</h1>
    <p class="text-sm text-gray-500 mb-6">
        This will be used by the attendance camera to recognize you. Only your face
        (up to the neck) is captured &mdash; automatically cropped.
    </p>

    {{-- ── STATUS BANNERS ───────────────────────────────────────────── --}}
    @if ($blocked)
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 mb-6">
            <p class="font-bold text-red-700 text-sm">⛔ Face registration blocked</p>
            <p class="text-sm text-red-600 mt-1">
                Due to repeated violations (inappropriate images), you can no longer
                register your face. Please visit the admin office to resolve this.
            </p>
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
            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 mb-4">
                <p class="font-bold text-yellow-800 text-sm">⏳ Submitted — waiting for admin verification</p>
                <p class="text-sm text-yellow-700 mt-1">
                    You can re-capture below to replace your previous photos.
                </p>
            </div>
        @elseif ($registration && $registration->status === 'approved')
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 mb-4">
                <p class="font-bold text-green-800 text-sm">✅ Your face is approved</p>
                <p class="text-sm text-green-700 mt-1">
                    You can re-register below if you need to update your photos.
                </p>
            </div>
        @elseif ($registration && $registration->status === 'rejected')
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 mb-4">
                <p class="font-bold text-red-700 text-sm">❌ Your last submission was rejected</p>
                <p class="text-sm text-red-600 mt-1">Please capture clear photos of your face again.</p>
            </div>
        @endif

        {{-- ── CAMERA CARD ──────────────────────────────────────────── --}}
        <div class="relative rounded-2xl overflow-hidden bg-black shadow-lg" style="aspect-ratio: 4 / 3;">
            <video id="cam" autoplay playsinline muted
                   class="w-full h-full object-cover" style="transform: scaleX(-1);"></video>

            {{-- oval face guide --}}
            <div id="oval" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="rounded-full border-4 border-white/70"
                     style="width:55%; height:78%; box-shadow:0 0 0 2000px rgba(0,0,0,0.25);"></div>
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

        {{-- buttons --}}
        <button id="enableBtn"
                class="w-full mt-5 py-3 rounded-xl bg-blue-600 text-white font-semibold text-sm">
            📷 Enable Camera
        </button>
        <button id="retryBtn"
                class="hidden w-full mt-3 py-2.5 rounded-xl font-semibold text-sm border border-gray-200 text-gray-600">
            🔄 Try again
        </button>

        <p class="text-[11px] text-gray-400 mt-4 text-center leading-relaxed">
            Good lighting in front of you works best. Keep only your face in view.<br>
            The camera captures automatically — just follow the on-screen guide.
        </p>
    @endif
</div>

@unless ($blocked)
<script>
(function () {
    'use strict';

    const CSRF     = '{{ csrf_token() }}';
    const POST_URL = '{{ route('student.face.store') }}';
    const TARGET   = 20;          // number of photos to capture
    const INTERVAL = 280;         // ms between captures

    const video    = document.getElementById('cam');
    const phaseEl  = document.getElementById('phase');
    const msgEl    = document.getElementById('msg');
    const countTx  = document.getElementById('countTxt');
    const bar      = document.getElementById('bar');
    const enableBt = document.getElementById('enableBtn');
    const retryBt  = document.getElementById('retryBtn');

    let captures = [];
    let timer    = null;

    function setMsg(t)   { if (msgEl)  msgEl.textContent = t; }
    function setPhase(t) { if (phaseEl) phaseEl.textContent = t; }

    // Pose guidance: front third -> left third -> right third
    function poseFor(n) {
        if (n < TARGET / 3)        return 'Look STRAIGHT at the camera';
        if (n < (TARGET * 2) / 3)  return 'Slowly turn your head to your LEFT';
        return 'Slowly turn your head to your RIGHT';
    }

    // Convert a data URL to a Blob synchronously (so counting is exact).
    function dataURLtoBlob(dataurl) {
        const parts = dataurl.split(',');
        const mime  = parts[0].match(/:(.*?);/)[1];
        const bin   = atob(parts[1]);
        let n = bin.length;
        const u8 = new Uint8Array(n);
        while (n--) u8[n] = bin.charCodeAt(n);
        return new Blob([u8], { type: mime });
    }

    // Grab a centered square crop (250x250) of the current frame.
    function grabFrame() {
        const vw = video.videoWidth, vh = video.videoHeight;
        if (!vw || !vh) return;                 // camera not ready yet
        const side = Math.min(vw, vh) * 0.8;
        const sx = (vw - side) / 2, sy = (vh - side) / 2;
        const c = document.createElement('canvas');
        c.width = 250; c.height = 250;
        c.getContext('2d').drawImage(video, sx, sy, side, side, 0, 0, 250, 250);
        const blob = dataURLtoBlob(c.toDataURL('image/jpeg', 0.85));
        if (captures.length < TARGET) captures.push(blob);
    }

    function startCapture() {
        clearInterval(timer);
        captures = [];
        retryBt.classList.add('hidden');
        setPhase('Capturing — keep your face in the circle');

        timer = setInterval(function () {
            grabFrame();
            const n = captures.length;
            countTx.textContent = n + ' / ' + TARGET;
            bar.style.width = (n / TARGET * 100) + '%';
            setMsg(poseFor(n));
            if (n >= TARGET) {
                clearInterval(timer);
                upload();
            }
        }, INTERVAL);
    }

    async function upload() {
        setPhase('Uploading your photos…');
        setMsg('Please wait…');
        const fd = new FormData();
        captures.forEach(function (blob, i) {
            fd.append('images[]', blob, 'img_' + (i + 1) + '.jpg');
        });
        try {
            const res  = await fetch(POST_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF },
                body: fd,
            });
            const data = await res.json();
            if (data.ok) {
                setPhase('Done! Waiting for admin verification.');
                setMsg(data.message || 'Submitted successfully.');
                stopCamera();
                setTimeout(function () { location.reload(); }, 2500);
            } else {
                setMsg(data.message || 'Upload failed. Please try again.');
                retryBt.classList.remove('hidden');
            }
        } catch (e) {
            setMsg('Network error — please try again.');
            retryBt.classList.remove('hidden');
        }
    }

    function stopCamera() {
        const s = video.srcObject;
        if (s) s.getTracks().forEach(function (t) { t.stop(); });
    }

    async function enableCamera() {
        enableBt.disabled = true;
        setPhase('Starting camera…');
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                audio: false,
            });
            video.srcObject = stream;
            await video.play();
            enableBt.classList.add('hidden');
            startCapture();
        } catch (err) {
            enableBt.disabled = false;
            setPhase('Camera blocked');
            setMsg('Cannot access camera. Allow camera permission, then tap Enable Camera.');
        }
    }

    enableBt.addEventListener('click', enableCamera);
    retryBt.addEventListener('click', function () {
        if (video.srcObject) startCapture();
        else enableCamera();
    });
})();
</script>
@endunless
@endsection