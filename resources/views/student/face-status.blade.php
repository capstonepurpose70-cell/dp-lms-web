@extends('layouts.app')
@section('title', 'Face Registration')

@section('content')
@php
    $status = $registration->status ?? null;   // approved | pending | rejected | null
@endphp
<style>
    .fs-wrap { max-width:640px; margin:0 auto; padding:8px 16px 56px; font-family:'Plus Jakarta Sans', system-ui, sans-serif; }
    .fs-h1 { font-size:26px; font-weight:800; color:#0F172A; }
    .fs-sub { font-size:13.5px; color:#64748B; margin:3px 0 20px; line-height:1.5; }
    .fs-card { background:#fff; border:1px solid #EEF1F5; border-radius:22px; padding:24px; box-shadow:0 4px 24px rgba(15,23,42,.05); margin-bottom:16px; text-align:center; }
    .fs-ic { width:84px; height:84px; border-radius:999px; display:grid; place-items:center; margin:0 auto 12px; }
    .fs-ic svg { width:44px; height:44px; }
    .fs-ok { background:rgba(22,163,74,.12); color:#16A34A; }
    .fs-wait { background:rgba(37,99,235,.10); color:#1D4ED8; }
    .fs-bad { background:rgba(220,38,38,.10); color:#DC2626; }
    .fs-title { font-size:19px; font-weight:800; color:#0F172A; }
    .fs-text { font-size:14px; color:#475569; margin-top:6px; line-height:1.55; }
    .fs-steps { text-align:left; margin:0; padding:0; list-style:none; }
    .fs-steps li { display:flex; gap:12px; padding:10px 0; border-bottom:1px solid #F1F5F9; font-size:14px; color:#334155; line-height:1.5; }
    .fs-steps li:last-child { border-bottom:0; }
    .fs-steps i { font-style:normal; width:26px; height:26px; border-radius:999px; background:rgba(37,99,235,.12); color:#1D4ED8; display:grid; place-items:center; font-weight:800; font-size:12px; flex-shrink:0; }
</style>

<div class="fs-wrap">
    <div class="fs-h1">Face Registration</div>
    <div class="fs-sub">Your face lets the school-gate camera recognize you, so your parent gets an "arrived at school" notification.</div>

    <div class="fs-card">
        @if ($blocked)
            <div class="fs-ic fs-bad"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></div>
            <div class="fs-title">Face registration is blocked</div>
            <div class="fs-text">Please see the admin office.</div>
        @elseif ($status === 'approved')
            <div class="fs-ic fs-ok"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
            <div class="fs-title">Your face is registered ✓</div>
            <div class="fs-text">Registered {{ $registration->reviewed_at?->format('M j, Y') ?? $registration->updated_at?->format('M j, Y') }}.
                If the gate camera doesn't recognize you, ask your adviser to register you again.</div>
        @elseif (!$enrolled)
            <div class="fs-ic fs-wait"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div class="fs-title">Finish your enrollment first</div>
            <div class="fs-text">Your face can be registered once you are placed in a section.</div>
        @else
            <div class="fs-ic fs-wait"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M9 10h.01M15 10h.01M9.5 15a3.5 3.5 0 005 0"/></svg></div>
            <div class="fs-title">Not registered yet</div>
            <div class="fs-text">
                @if ($status === 'pending')
                    Your earlier photo upload is waiting for review. You can also register at the school kiosk with your adviser.
                @elseif ($status === 'rejected')
                    Your earlier photos were not accepted{{ $registration->reject_reason ? ': ' . $registration->reject_reason : '' }}. Please register at the school kiosk.
                @else
                    Your adviser{{ $adviser ? ', ' . $adviser->name . ',' : '' }} will register your face at the school kiosk.
                @endif
            </div>
        @endif
    </div>

    @if (!$blocked && $status !== 'approved' && $enrolled)
        <div class="fs-card" style="text-align:left;">
            <div style="font-size:15px; font-weight:800; color:#0F172A; margin-bottom:4px;">How it works</div>
            <ol class="fs-steps">
                <li><i>1</i><span>Go to the face registration kiosk (Raspberry Pi camera) with your adviser.</span></li>
                <li><i>2</i><span>Your adviser shows a QR code made just for you. Hold it up to the camera.</span></li>
                <li><i>3</i><span>Look straight at the camera, alone in the frame, until it finishes (about 10 photos).</span></li>
                <li><i>4</i><span>Done — this page will show <b>Registered ✓</b>.</span></li>
            </ol>
        </div>
    @endif
</div>
@endsection
