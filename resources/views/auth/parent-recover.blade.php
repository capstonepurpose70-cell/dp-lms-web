@extends('layouts.auth-card')

@section('title', 'Recover Parent Account')

@section('heading', 'Recover your parent account')
@section('desc', "New phone or forgot your password? Ask your child for a new code (DP-LMS → \"Parent\"), then set a new password.")

@section('content')
<form method="POST" action="{{ route('parent.recover.submit') }}" data-once novalidate>
    @csrf

    <div class="ac-group">
        <label for="code">Code from your child</label>
        <input class="ac-input ac-code @error('code') is-error @enderror" type="text" id="code" name="code"
               value="{{ old('code', $code) }}" maxlength="9" autocomplete="off" autocapitalize="characters"
               spellcheck="false" placeholder="XXXX-XXXX" required>
    </div>

    <div class="ac-group">
        <label for="contact_number">Your mobile number</label>
        <input class="ac-input" type="tel" id="contact_number" name="contact_number"
               value="{{ old('contact_number') }}" inputmode="tel" autocomplete="tel" placeholder="09XXXXXXXXX" maxlength="16" required>
    </div>

    <div class="ac-group">
        <label for="password">New password</label>
        <div class="ac-pw">
            <input class="ac-input @error('password') is-error @enderror" type="password" id="password" name="password"
                   autocomplete="new-password" maxlength="100" required>
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

    <ul class="ac-rules" data-pw-rules="password" data-pw-confirm="password_confirmation" aria-label="Password requirements">
        <li data-rule="length">At least 8 characters</li>
        <li data-rule="mixed">Upper and lowercase letters</li>
        <li data-rule="number">At least one number</li>
        <li data-rule="symbol">At least one symbol</li>
        <li data-rule="match">Passwords match</li>
    </ul>

    <button type="submit" class="ac-btn" data-busy="Saving…">Change password</button>
</form>
@endsection

@section('footer')
    <span><a href="{{ route('login') }}">Back to sign in</a></span>
@endsection
