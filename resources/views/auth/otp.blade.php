@extends('layouts.auth-card')

@section('title', 'Verify Sign-in')

@section('steps')
    <li class="ac-step is-done"><span class="ac-step-dot">✓</span> Password</li>
    <li class="ac-step-line" aria-hidden="true"></li>
    <li class="ac-step is-active" aria-current="step"><span class="ac-step-dot">2</span> Email code</li>
@endsection

@section('heading', 'Check your email')
@section('desc')
    We sent a 6-digit sign-in code to <strong>{{ session('otp_email') }}</strong>.
@endsection

@section('content')
<form method="POST" action="{{ route('otp.verify') }}" data-once>
    @csrf
    <div class="ac-group">
        <label for="otp">Sign-in code</label>
        <input class="ac-input ac-code @error('otp') is-error @enderror" type="text" id="otp" name="otp"
               inputmode="numeric" pattern="\d{6}" maxlength="6" data-digits autocomplete="one-time-code"
               placeholder="••••••" aria-describedby="otp-timer" @error('otp') aria-invalid="true" @enderror required autofocus>
    </div>

    <p class="ac-timer" id="otp-timer">
        @if($secondsLeft > 0)
            Code expires in <span data-countdown="{{ $secondsLeft }}">{{ intdiv($secondsLeft, 60) }}:{{ str_pad($secondsLeft % 60, 2, '0', STR_PAD_LEFT) }}</span>
        @else
            This code has expired — send a new one below.
        @endif
    </p>

    <label class="ac-check">
        <input type="checkbox" name="trust_device" value="1">
        <span>Remember this device for 30 days (no code needed when signing in). <strong>Don't check</strong> on shared or internet-café computers.</span>
    </label>

    <button type="submit" class="ac-btn" data-busy="Verifying…">Verify and sign in</button>
</form>

<form method="POST" action="{{ route('otp.resend') }}" data-once style="text-align:center;margin-top:8px;">
    @csrf
    <span style="font-size:13.5px;color:var(--text-2);">Didn't get the code? Check your spam folder, or</span>
    <button type="submit" class="ac-btn-link" data-busy="Sending…">Send a new code</button>
</form>
@endsection

@section('footer')
    <span>Not you? <a href="{{ route('login') }}">Back to sign in</a></span>
@endsection
