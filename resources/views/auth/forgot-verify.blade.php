@extends('layouts.auth-card')

@section('title', 'Enter Reset Code')

@section('steps')
    <li class="ac-step is-done"><span class="ac-step-dot">✓</span> Email</li>
    <li class="ac-step-line" aria-hidden="true"></li>
    <li class="ac-step is-active" aria-current="step"><span class="ac-step-dot">2</span> Code</li>
    <li class="ac-step-line" aria-hidden="true"></li>
    <li class="ac-step"><span class="ac-step-dot">3</span> New password</li>
@endsection

@section('heading', 'Enter the reset code')
@section('desc')
    Enter the 6-digit code sent to <strong>{{ session('reset_email') }}</strong>.
@endsection

@section('content')
<form method="POST" action="{{ route('password.verify.submit') }}" data-once>
    @csrf
    <div class="ac-group">
        <label for="code">Reset code</label>
        <input class="ac-input ac-code @error('code') is-error @enderror" type="text" id="code" name="code"
               inputmode="numeric" pattern="\d{6}" maxlength="6" data-digits autocomplete="one-time-code"
               placeholder="••••••" aria-describedby="code-timer" @error('code') aria-invalid="true" @enderror required autofocus>
    </div>

    {{-- Client-side only: the server's record isn't read here, so the page looks the same whether or not the email has an account --}}
    <p class="ac-timer" id="code-timer">
        Code expires in <span data-countdown="{{ \App\Services\PasswordResetService::CODE_MINUTES * 60 }}">{{ \App\Services\PasswordResetService::CODE_MINUTES }}:00</span>
    </p>

    <button type="submit" class="ac-btn" data-busy="Checking…">Verify code</button>
</form>

{{-- Real resend (same email, same rate limit) --}}
<form method="POST" action="{{ route('password.email') }}" data-once style="text-align:center;margin-top:8px;">
    @csrf
    <input type="hidden" name="email" value="{{ session('reset_email') }}">
    <span style="font-size:13.5px;color:var(--text-2);">Didn't get the code? Check your spam folder, or</span>
    <button type="submit" class="ac-btn-link" data-busy="Sending…">Send a new code</button>
</form>
@endsection

@section('footer')
    <span>Wrong email? <a href="{{ route('password.request') }}">Start over</a></span>
    <span><a href="{{ route('login') }}">Back to sign in</a></span>
@endsection
