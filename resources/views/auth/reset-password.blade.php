@extends('layouts.auth-card')

@section('title', 'Set New Password')

@section('steps')
    <li class="ac-step is-done"><span class="ac-step-dot">✓</span> Email</li>
    <li class="ac-step-line" aria-hidden="true"></li>
    <li class="ac-step is-done"><span class="ac-step-dot">✓</span> Code</li>
    <li class="ac-step-line" aria-hidden="true"></li>
    <li class="ac-step is-active" aria-current="step"><span class="ac-step-dot">3</span> New password</li>
@endsection

@section('heading', 'Set a new password')
@section('desc')
    Choose a new password for <strong>{{ session('reset_email') }}</strong>.
@endsection

@section('content')
<form method="POST" action="{{ route('password.update') }}" data-once novalidate>
    @csrf
    <div class="ac-group">
        <label for="password">New password</label>
        <div class="ac-pw">
            <input class="ac-input @error('password') is-error @enderror" type="password" id="password" name="password"
                   autocomplete="new-password" maxlength="100" aria-describedby="pw-rules"
                   @error('password') aria-invalid="true" @enderror required autofocus>
            <button type="button" class="ac-pw-toggle" data-pw-toggle="password" aria-pressed="false">Show</button>
        </div>
    </div>

    <div class="ac-group">
        <label for="password_confirmation">Confirm new password</label>
        <div class="ac-pw">
            <input class="ac-input" type="password" id="password_confirmation" name="password_confirmation"
                   autocomplete="new-password" maxlength="100" required>
            <button type="button" class="ac-pw-toggle" data-pw-toggle="password_confirmation" aria-pressed="false">Show</button>
        </div>
    </div>

    {{-- Same rule as the server (PasswordResetService::passwordRules) --}}
    <ul class="ac-rules" id="pw-rules" data-pw-rules="password" data-pw-confirm="password_confirmation" aria-label="Password requirements">
        <li data-rule="length">At least 8 characters</li>
        <li data-rule="mixed">Upper and lowercase letters</li>
        <li data-rule="number">At least one number</li>
        <li data-rule="symbol">At least one symbol</li>
        <li data-rule="match">Passwords match</li>
    </ul>

    <button type="submit" class="ac-btn" data-busy="Saving…">Save new password</button>
</form>
@endsection

@section('footer')
    <span><a href="{{ route('login') }}">Back to sign in</a></span>
@endsection
