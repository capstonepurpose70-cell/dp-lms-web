@extends('layouts.app')
@section('title', 'Register Face')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">

    <h1 class="text-xl font-bold text-gray-800 mb-1">Face Registration</h1>
    <p class="text-sm text-gray-500 mb-6">
        Ito ang gagamitin ng attendance camera para makilala ka. Mukha mo lang
        (hanggang leeg) ang kukunan — awtomatikong naka-crop.
    </p>

    {{-- ── STATUS BANNERS ─────────────────────────────────────────── --}}
    @if ($blocked)
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 mb-6">
            <p class="font-bold text-red-700 text-sm">⛔ Face registration blocked</p>
            <p class="text-sm text-red-600 mt-1">
                Dahil sa paulit-ulit na paglabag (hindi wastong larawan), hindi ka na
                makakapag-register ng mukha. Pumunta sa admin office para maayos ito.
            </p>
        </div>
    @else
        @if ($warnings > 0)
            <div class="rounded-xl border border-orange-200 bg-orange-50 p-4 mb-4">
                <p class="text-sm text-orange-700">
                    ⚠️ <b>Babala {{ $warnings }}/3:</b> May na-reject na hindi wastong larawan.
                    Mukha mo lamang ang kunan — sa ikatlong babala, maba-block ang account mo
                    sa face registration at maipapatawag sa opisina.
                </p>
            </div>
        @endif

        @if ($registration && $registration->status === 'pending')
            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 mb-6">
                <p class="font-bold text-yellow-700 text-sm">🕒 Pending verification</p>
                <p class="text-sm text-yellow-600 mt-1">
                    Naipasa na ang {{ $registration->images_count }} larawan. Hinihintay ang
                    pag-verify ng admin. Pwede kang kumuha ulit — papalitan nito ang nauna.
                </p>
            </div>
        @elseif ($registration && $registration->status === 'approved')
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 mb-6">
                <p class="font-bold text-green-700 text-sm">✅ Verified na ang mukha mo!</p>
                <p class="text-sm text-green-600 mt-1">
                    Makikilala ka na ng attendance camera. Kung gusto mong palitan
                    (hal. bagong salamin/gupit), kumuha lang ulit sa ibaba.
                </p>
            </div>
        @elseif ($registration && $registration->status === 'rejected')
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 mb-6">
                <p class="font-bold text-red-700 text-sm">❌ Rejected</p>
                <p class="text-sm text-red-600 mt-1">
                    {{ $registration->reject_reason ?: 'Hindi malinaw o hindi wasto ang mga larawan.' }}
                    — Kumuha ulit sa ibaba.
                </p>
            </div>
        @endif

        {{-- ── CAMERA CARD ────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5"
             style="box-shadow:0 2px 16px rgba(0,0,0,0.05);">

            <div id="camWrap" class="relative mx-auto rounded-xl overflow-hidden bg-black"
                 style="max-width:480px; aspect-ratio:4/3;">
                <video id="cam" autoplay playsinline muted
                       class="w-full h-full object-cover" style="transform:scaleX(-1);"></video>
                {{-- oval guide --}}
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div id="oval" style="width:55%;height:78%;border:3px dashed rgba(255,255,255,.8);
                         border-radius:50%/60%;"></div>
                </div>
                <div id="camMsg"
                     class="absolute bottom-0 left-0 right-0 text-center text-white text-sm py-2"
                     style="background:rgba(0,0,0,.55);">Loading camera…</div>
            </div>

            {{-- progress --}}
            <div class="mt-4">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Progress</span><span id="countTxt">0 / 20</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2.5">
                    <div id="bar" class="h-2.5 rounded-full"
                         style="width:0%;background:#2563eb;transition:width .3s;"></div>
                </div>
            </div>

            <div class="flex gap-3 mt-4">
                <button id="startBtn"
                    class="flex-1 py-2.5 rounded-xl text-white font-semibold text-sm"
                    style="background:#2563eb;">📸 Start Capture</button>
                <button id="retryBtn" class="hidden flex-1 py-2.5 rounded-xl font-semibold text-sm border border-gray-200 text-gray-600">
                    🔄 Ulitin</button>
            </div>

            <p id="phase" class="text-center text-xs text-gray-400 mt-3">
                Pindutin ang Start, tumingin sa camera, at bahagyang igalaw ang ulo
                (kaliwa / kanan / taas) habang kumukuha.
            </p>
        </div>
    @endif
</div>

@if (!$blocked)
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const CSRF      = '{{ csrf_token() }}';
const POST_URL  = '{{ route('student.face.store') }}';
const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@0.22.2/weights';
const TARGET    = 20;

const video   = document.getElementById('cam');
const camMsg  = document.getElementById('camMsg');
const bar     = document.getElementById('bar');
const countTx = document.getElementById('countTxt');
const startBt = document.getElementById('startBtn');
const retryBt = document.getElementById('retryBtn');
const phase   = document.getElementById('phase');

let captures = [], running = false, modelReady = false;

async function init() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { width: 640, height: 480, facingMode: 'user' }, audio: false
        });
        video.srcObject = stream;
        camMsg.textContent = 'Nilo-load ang face detector…';
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        modelReady = true;
        camMsg.textContent = 'Handa na — pindutin ang Start Capture';
    } catch (e) {
        camMsg.textContent = '❌ Hindi ma-access ang camera. I-allow ang camera permission.';
    }
}
init();

startBt.addEventListener('click', () => {
    if (!modelReady || running) return;
    captures = []; running = true;
    startBt.disabled = true; startBt.style.opacity = .5;
    phase.textContent = 'Kumukuha… tumingin sa camera, bahagyang igalaw ang ulo.';
    loop();
});

retryBt.addEventListener('click', () => location.reload());

async function loop() {
    if (!running) return;
    const det = await faceapi.detectAllFaces(
        video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 })
    );

    if (det.length === 1) {
        const b = det[0].box;
        if (b.width >= video.videoWidth * 0.16) {
            grabFace(b);
            camMsg.textContent = `📸 Nakuha: ${captures.length}/${TARGET}`;
        } else {
            camMsg.textContent = 'Lumapit pa nang konti…';
        }
    } else if (det.length === 0) {
        camMsg.textContent = 'Walang mukhang nakikita…';
    } else {
        camMsg.textContent = 'Isang tao lang dapat sa camera!';
    }

    countTx.textContent = `${captures.length} / ${TARGET}`;
    bar.style.width = (captures.length / TARGET * 100) + '%';

    if (captures.length >= TARGET) { running = false; upload(); return; }
    setTimeout(loop, 350);
}

/** Crop face only — up to the neck (box + margins), 250×250 jpeg */
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
    camMsg.textContent = '⬆️ Ina-upload…'; phase.textContent = 'Ina-upload ang mga larawan…';
    // wait for any pending toBlob callbacks
    await new Promise(r => setTimeout(r, 600));

    const fd = new FormData();
    captures.forEach((blob, i) => fd.append('images[]', blob, `img_${i + 1}.jpg`));

    try {
        const res  = await fetch(POST_URL, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF }, body: fd
        });
        const data = await res.json();
        if (data.ok) {
            camMsg.textContent = '✅ ' + data.message;
            phase.textContent  = 'Tapos! Hinihintay na ang admin verification.';
            (video.srcObject?.getTracks() || []).forEach(t => t.stop());
            setTimeout(() => location.reload(), 2500);
        } else {
            camMsg.textContent = '❌ ' + (data.message || 'Upload failed');
            retryBt.classList.remove('hidden');
        }
    } catch (e) {
        camMsg.textContent = '❌ Network error — subukan ulit.';
        retryBt.classList.remove('hidden');
    }
}
</script>
@endif
@endsection