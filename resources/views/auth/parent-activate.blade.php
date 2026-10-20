@extends('layouts.auth-card')

@section('title', 'Parent Account')
@section('wrap-class', 'is-wide')

@section('heading', 'Create your parent account')
@section('desc', "Enter the code from your child's DP-LMS account (the \"Parent\" menu), or scan their QR code.")

@section('content')
<div class="ac-alert ac-alert-info">
    Use <strong>your own phone</strong>. If you're using your child's phone, ask them to sign out first.
</div>

<form method="POST" action="{{ route('parent.activate.submit') }}" data-once novalidate>
    @csrf

    <div class="ac-group">
        <label for="code">Code from your child</label>
        <input class="ac-input ac-code @error('code') is-error @enderror" type="text" id="code" name="code"
               value="{{ old('code', $code) }}" maxlength="9" autocomplete="off" autocapitalize="characters"
               spellcheck="false" placeholder="XXXX-XXXX" required>
    </div>

    <p class="ac-section">About you</p>
    <div class="ac-grid">
        <div class="ac-group ac-span">
            <label for="name">Full name</label>
            <input class="ac-input @error('name') is-error @enderror" type="text" id="name" name="name"
                   value="{{ old('name') }}" autocomplete="name" maxlength="255" placeholder="e.g. Maria Santos Dela Cruz" required>
            <p class="ac-hint">As written on your child's enrollment form (mother, father, or guardian). If it matches, you'll see their records right away.</p>
        </div>

        <div class="ac-group">
            <label for="contact_number">Mobile number</label>
            <input class="ac-input @error('contact_number') is-error @enderror" type="tel" id="contact_number" name="contact_number"
                   value="{{ old('contact_number') }}" inputmode="tel" autocomplete="tel" placeholder="09XXXXXXXXX" maxlength="16" required>
            <p class="ac-hint">You'll use this to sign in.</p>
        </div>

        <div class="ac-group">
            <label for="email">Email <span class="ac-opt">(optional)</span></label>
            <input class="ac-input @error('email') is-error @enderror" type="email" id="email" name="email"
                   value="{{ old('email') }}" autocomplete="email" autocapitalize="none" maxlength="254">
            <p class="ac-hint">No email? That's fine — on a new phone, just ask your child for a new code.</p>
        </div>

        <div class="ac-group">
            <label for="password">Password</label>
            <div class="ac-pw">
                <input class="ac-input @error('password') is-error @enderror" type="password" id="password" name="password"
                       autocomplete="new-password" maxlength="100" required>
                <button type="button" class="ac-pw-toggle" data-pw-toggle="password" aria-pressed="false">Show</button>
            </div>
        </div>

        <div class="ac-group">
            <label for="password_confirmation">Confirm password</label>
            <div class="ac-pw">
                <input class="ac-input" type="password" id="password_confirmation" name="password_confirmation"
                       autocomplete="new-password" maxlength="100" required>
                <button type="button" class="ac-pw-toggle" data-pw-toggle="password_confirmation" aria-pressed="false">Show</button>
            </div>
        </div>
    </div>

    <ul class="ac-rules" data-pw-rules="password" data-pw-confirm="password_confirmation" aria-label="Password requirements">
        <li data-rule="length">At least 8 characters</li>
        <li data-rule="mixed">Upper and lowercase letters</li>
        <li data-rule="number">At least one number</li>
        <li data-rule="symbol">At least one symbol</li>
        <li data-rule="match">Passwords match</li>
    </ul>

    <button type="submit" class="ac-btn" data-busy="Creating account…">Create account</button>
</form>
@endsection

@section('footer')
    <span>Have an account? <a href="{{ route('login') }}">Sign in</a></span>
    <span>New phone or forgot password? <a href="{{ route('parent.recover') }}">Recover</a></span>
@endsection
