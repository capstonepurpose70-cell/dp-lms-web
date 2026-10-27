@extends('layouts.auth-card')

@section('title', 'Forgot Password')

@section('steps')
    <li class="ac-step is-active" aria-current="step"><span class="ac-step-dot">1</span> Email</li>
    <li class="ac-step-line" aria-hidden="true"></li>
    <li class="ac-step"><span class="ac-step-dot">2</span> Code</li>
    <li class="ac-step-line" aria-hidden="true"></li>
    <li class="ac-step"><span class="ac-step-dot">3</span> New password</li>
@endsection

@section('heading', 'Forgot your password?')
@section('desc', "Enter the email on your account. We'll send a 6-digit code so you can set a new password.")

@section('content')
<form method="POST" action="{{ route('password.email') }}" data-once>
    @csrf
    <div class="ac-group">
        <label for="email">Email address</label>
        <input class="ac-input @error('email') is-error @enderror" type="email" id="email" name="email"
               value="{{ old('email') }}" autocomplete="email" autocapitalize="none" spellcheck="false"
               placeholder="you@gmail.com" maxlength="254" aria-describedby="email-hint"
               @error('email') aria-invalid="true" @enderror required autofocus>
        <p class="ac-hint" id="email-hint">
            Parent without an email? Ask your child for a new code and use
            <a href="{{ route('parent.recover') }}" style="color:var(--accent);font-weight:700;">parent recovery</a>.
        </p>
    </div>

    <button type="submit" class="ac-btn" data-busy="Sending code…">Send code</button>
</form>
@endsection

@section('footer')
    <span>Remember your password? <a href="{{ route('login') }}">Sign in</a></span>
@endsection
