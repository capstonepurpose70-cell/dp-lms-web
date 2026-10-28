@extends('layouts.teacher')
@section('title', 'Face Registration')

@section('content')
@php
    $pills = ['approved' => ['ok', 'Registered'], 'pending' => ['warn', 'Waiting for review'], 'rejected' => ['bad', 'Rejected']];
    $done = $students->where('face_status', 'approved')->count();
    $total = $students->count();
@endphp
<style>
    .fr-steps { list-style:none; margin:0; padding:0; display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:12px 20px; counter-reset:step; }
    .fr-steps li { display:flex; gap:10px; font-size:13.5px; color:var(--text-2); line-height:1.5; counter-increment:step; }
    .fr-steps li::before { content:counter(step); width:26px; height:26px; border-radius:50%; background:var(--accent-lt); color:var(--accent-dark);
                           display:grid; place-items:center; font-weight:800; font-size:12.5px; flex-shrink:0; }
    .fr-steps b { color:var(--text-1); }

    .fr-progress { display:flex; align-items:center; gap:12px; font-size:13.5px; color:var(--text-2); padding:16px 18px; border-bottom:1px solid var(--border); }
    .fr-progress b { color:var(--text-1); }
    .fr-bar { flex:1; max-width:280px; height:8px; border-radius:999px; background:rgba(100,116,139,.15); overflow:hidden; }
    .fr-bar i { display:block; height:100%; background:#16a34a; }

    .fr-list { list-style:none; margin:0; padding:0; }
    .fr-row { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:12px 18px; border-bottom:1px solid var(--border); }
    .fr-row:last-child { border-bottom:0; }
    .fr-who { display:flex; align-items:center; gap:10px; min-width:0; flex-wrap:wrap; }
    .fr-name { font-weight:700; color:var(--text-1); font-size:14px; }
    .fr-blocked { font-size:12.5px; color:#B91C1C; font-weight:700; text-align:right; }

    @media (max-width: 760px) { .fr-steps { grid-template-columns:1fr; } }
    @media (max-width: 480px) {
        .fr-row { padding:12px 14px; }
        .fr-who { flex-direction:column; align-items:flex-start; gap:4px; }
    }
</style>

<div class="tp-page">
    <div class="tp-head">
        <div>
            <h1 class="tp-title">Face registration</h1>
            <p class="tp-sub">Register your advisory students at the Raspberry Pi kiosk. The gate camera then recognizes them and
                their parents get an arrival notification. Each QR code is for one student, works once, and expires in 10 minutes.</p>
        </div>
    </div>

    <div class="tp-card" style="margin-bottom:16px;">
        <h2 class="sr-only">How to register a student</h2>
        <ol class="fr-steps">
            <li><span>Bring the student to the kiosk and press <b>Show QR</b> next to their name.</span></li>
            <li><span>Hold the QR code up to the kiosk camera. It greets the student by name and takes their photos.</span></li>
            <li><span>The kiosk screen turns green once the face is saved. No admin approval is needed.</span></li>
        </ol>
    </div>

    <section class="tp-card" style="padding:0;" aria-labelledby="frList">
        <h2 id="frList" class="sr-only">Students</h2>
        @if ($students->isEmpty())
            <div class="tp-empty">
                <b>No students to register</b>
                Only a section's adviser registers faces here, and your advisory section has no approved students yet.
            </div>
        @else
            <div class="fr-progress">
                <span><b>{{ $done }}</b> of {{ $total }} registered</span>
                <div class="fr-bar" aria-hidden="true"><i style="width:{{ round($done / max(1, $total) * 100) }}%;"></i></div>
            </div>
            <ul class="fr-list">
                @foreach ($students as $s)
                    @php [$cls, $lbl] = $pills[$s->face_status] ?? ['muted', 'No face yet']; @endphp
                    <li class="fr-row">
                        <div class="fr-who">
                            <span class="fr-name">{{ $s->name }}</span>
                            <span class="tp-pill {{ $cls }}">{{ $lbl }}</span>
                        </div>
                        @if ((int) $s->face_warnings >= 3)
                            <span class="fr-blocked">Blocked, ask the admin</span>
                        @else
                            @php $again = $s->face_status === 'approved'; @endphp
                            <button type="button" class="tp-btn sm {{ $again ? 'ghost' : '' }}"
                                    data-kiosk-issue="{{ route('teacher.face-kiosk.issue', $s->id) }}" data-name="{{ $s->name }}"
                                    aria-label="{{ $again ? 'Register again' : 'Show QR code' }} for {{ $s->name }}">
                                {{ $again ? 'Register again' : 'Show QR' }}
                            </button>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>

@include('partials.face-kiosk-modal')
@endsection
