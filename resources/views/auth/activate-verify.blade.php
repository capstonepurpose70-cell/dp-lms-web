@extends('layouts.auth-card')

@section('title', 'Verify Email')

@section('steps')
    <li class="ac-step is-done"><span class="ac-step-dot">✓</span> Your details</li>
    <li class="ac-step-line" aria-hidden="true"></li>
    <li class="ac-step is-active" aria-current="step"><span class="ac-step-dot">2</span> Verify email</li>
@endsection

@section('heading', 'Check your email')
@section('desc')
    We sent a 6-digit code to <strong>{{ $email }}</strong>. It expires in {{ $minutes }} minutes.
@endsection

@section('content')
<form method="POST" action="{{ route('activate.verify.submit') }}" data-once>
    @csrf
    <div class="ac-group">
        <label for="code">Verification code</label>
        <input class="ac-input ac-code @error('code') is-error @enderror" type="text" id="code" name="code"
               inputmode="numeric" pattern="\d{6}" maxlength="6" data-digits autocomplete="one-time-code"
               placeholder="••••••" @error('code') aria-invalid="true" @enderror required autofocus>
    </div>

    <label class="ac-check">
        <input type="checkbox" name="trust_device" value="1">
        <span>Remember this device for 30 days (no code needed when signing in). <strong>Don't check</strong> on shared or internet-café computers.</span>
    </label>

    <button type="submit" class="ac-btn" data-busy="Activating…">Activate account</button>
</form>

<form method="POST" action="{{ route('activate.resend') }}" data-once style="text-align:center;margin-top:8px;">
    @csrf
    <span style="font-size:13.5px;color:var(--text-2);">Didn't get the code? Check your spam folder, or</span>
    <button type="submit" class="ac-btn-link" data-busy="Sending…">Resend code</button>
</form>
@endsection

@section('footer')
    <span>Wrong email? <a href="{{ route('activate') }}">Start over</a></span>
    <span><a href="{{ route('login') }}">Back to sign in</a></span>
@endsection
