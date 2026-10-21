@extends('layouts.teacher')
@section('title', 'Face Registration')

@section('content')
@php
    $chip = ['approved' => ['#16a34a', '#fff', 'Registered'], 'pending' => ['#f59e0b', '#3f2d00', 'Waiting for review'], 'rejected' => ['#dc2626', '#fff', 'Rejected']];
    $done = $students->where('face_status', 'approved')->count();
@endphp
<style>
    .fr-page { max-width:960px; animation: fr-fade .22s ease both; }
    @keyframes fr-fade { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
    .fr-title { font-size:22px; font-weight:800; color:var(--text-1); margin:0; }
    .fr-sub { font-size:13px; color:var(--text-2); margin:4px 0 16px; line-height:1.55; max-width:720px; }
    .fr-card { background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:18px; margin-bottom:16px; }
    .fr-steps { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:10px; }
    .fr-step { display:flex; gap:10px; font-size:13px; color:var(--text-2); line-height:1.5; }
    .fr-step i { font-style:normal; width:26px; height:26px; border-radius:999px; background:rgba(37,99,235,.12); color:#1d4ed8; display:grid; place-items:center; font-weight:800; font-size:12px; flex-shrink:0; }
    .fr-step b { color:var(--text-1); }
    .fr-progress { display:flex; align-items:center; gap:10px; font-size:13px; color:var(--text-2); margin-bottom:10px; }
    .fr-bar { flex:1; height:8px; border-radius:999px; background:rgba(100,116,139,.15); overflow:hidden; }
    .fr-bar i { display:block; height:100%; background:#16a34a; }
    .fr-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(270px, 1fr)); gap:8px; }
    .fr-row { display:flex; justify-content:space-between; align-items:center; gap:8px; border:1px solid var(--border); border-radius:12px; padding:10px 12px; }
    .fr-name { font-weight:700; color:var(--text-1); font-size:13.5px; }
    .fr-chip { display:inline-block; margin-top:3px; padding:1px 8px; border-radius:999px; font-size:11px; font-weight:800; }
    .fr-btn { height:34px; padding:0 13px; border-radius:9px; border:1px solid #1d4ed8; background:#1d4ed8; color:#fff; font-weight:700; font-size:12.5px; cursor:pointer; white-space:nowrap; }
    .fr-btn.ghost { background:transparent; color:#1d4ed8; }
    .fr-empty { text-align:center; padding:28px 12px; color:var(--text-2); font-size:13.5px; }
    @media (max-width: 760px) { .fr-steps { grid-template-columns:1fr; } }
</style>

<div class="fr-page">
    <h1 class="fr-title">Face Registration</h1>
    <p class="fr-sub">Register your advisory students' faces at the <b>Raspberry Pi kiosk</b>. The gate camera then recognizes them
        and their parents get an "arrived at school" notification. Each QR is for one student only, works once, and expires in 10 minutes.</p>

    <div class="fr-card">
        <div class="fr-steps">
            <div class="fr-step"><i>1</i><span>Bring the student to the kiosk and tap <b>Show QR</b> beside their name.</span></div>
            <div class="fr-step"><i>2</i><span>Hold the QR up to the Pi camera. It greets the student by name and takes about 10 photos.</span></div>
            <div class="fr-step"><i>3</i><span>The window turns green when it is saved — no admin approval needed.</span></div>
        </div>
    </div>

    <div class="fr-card">
        @if ($students->isEmpty())
            <div class="fr-empty">You don't have an advisory section with approved students yet. Only a section's adviser can register its students here.</div>
        @else
            <div class="fr-progress">
                <span><b style="color:var(--text-1);">{{ $done }}</b> of {{ $students->count() }} registered</span>
                <div class="fr-bar"><i style="width:{{ round($done / max(1, $students->count()) * 100) }}%;"></i></div>
            </div>
            <div class="fr-grid">
                @foreach ($students as $s)
                    @php [$bg, $fg, $lbl] = $chip[$s->face_status] ?? ['rgba(100,116,139,.15)', 'var(--text-2)', 'No face yet']; @endphp
                    <div class="fr-row">
                        <div style="min-width:0;">
                            <div class="fr-name">{{ $s->name }}</div>
                            <span class="fr-chip" style="background:{{ $bg }}; color:{{ $fg }};">{{ $lbl }}</span>
                        </div>
                        @if ((int) $s->face_warnings >= 3)
                            <span style="font-size:12px; color:#b91c1c; font-weight:700;">Blocked — see admin</span>
                        @else
                            <button type="button" class="fr-btn {{ $s->face_status === 'approved' ? 'ghost' : '' }}"
                                    data-kiosk-issue="{{ route('teacher.face-kiosk.issue', $s->id) }}" data-name="{{ $s->name }}">
                                {{ $s->face_status === 'approved' ? 'Re-register' : 'Show QR' }}
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@include('partials.face-kiosk-modal')
@endsection
