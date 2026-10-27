@extends('layouts.auth-card')

@section('title', 'Activate Account')
@section('wrap-class', 'is-wide')

@section('steps')
    <li class="ac-step is-active" aria-current="step"><span class="ac-step-dot">1</span> Your details</li>
    <li class="ac-step-line" aria-hidden="true"></li>
    <li class="ac-step"><span class="ac-step-dot">2</span> Verify email</li>
@endsection

@section('heading', 'Activate your student account')
@section('desc', 'First time here? Enter your LRN and full name exactly as they appear on your school class list.')

@section('content')
<form method="POST" action="{{ route('activate.start') }}" data-once novalidate>
    @csrf

    {{-- Honeypot (hidden from people, visible to bots) --}}
    <div class="ac-hp" aria-hidden="true">
        <label for="website">Website</label>
        <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
    </div>

    <p class="ac-section">School record</p>
    <div class="ac-grid">
        <div class="ac-group">
            <label for="lrn">LRN</label>
            <input class="ac-input @error('lrn') is-error @enderror" type="text" id="lrn" name="lrn"
                   value="{{ old('lrn') }}" inputmode="numeric" pattern="\d{12}" maxlength="12" data-digits
                   autocomplete="off" placeholder="12-digit LRN" aria-describedby="lrn-hint"
                   @error('lrn') aria-invalid="true" @enderror required autofocus>
            <p class="ac-hint" id="lrn-hint">Found on your report card or school ID.</p>
        </div>

        <div class="ac-group">
            <label for="birthdate">Birthdate</label>
            <input class="ac-input @error('birthdate') is-error @enderror" type="date" id="birthdate" name="birthdate"
                   value="{{ old('birthdate') }}" autocomplete="bday" max="{{ now()->subYears(8)->toDateString() }}"
                   aria-describedby="birthdate-hint" @error('birthdate') aria-invalid="true" @enderror required>
            <p class="ac-hint" id="birthdate-hint">Your adviser will compare this with your enrollment form.</p>
        </div>

        <div class="ac-group ac-span">
            <label for="name">Full name</label>
            <input class="ac-input @error('name') is-error @enderror" type="text" id="name" name="name"
                   value="{{ old('name') }}" autocomplete="name" placeholder="e.g. Juan Santos Dela Cruz" maxlength="255"
                   @error('name') aria-invalid="true" @enderror required>
        </div>
    </div>

    <p class="ac-section">Your account</p>
    <div class="ac-grid">
        <div class="ac-group ac-span">
            <label for="email">Email address</label>
            <input class="ac-input @error('email') is-error @enderror" type="email" id="email" name="email"
                   value="{{ old('email') }}" autocomplete="email" autocapitalize="none" spellcheck="false"
                   placeholder="you@gmail.com" maxlength="254" aria-describedby="email-hint"
                   @error('email') aria-invalid="true" @enderror required>
            <p class="ac-hint" id="email-hint">We'll send a 6-digit code here. Use an email you can open — it's also used for password resets.</p>
        </div>

        <div class="ac-group">
            <label for="password">Password</label>
            <div class="ac-pw">
                <input class="ac-input @error('password') is-error @enderror" type="password" id="password" name="password"
                       autocomplete="new-password" maxlength="100" aria-describedby="pw-rules"
                       @error('password') aria-invalid="true" @enderror required>
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

    <ul class="ac-rules" id="pw-rules" data-pw-rules="password" data-pw-confirm="password_confirmation" aria-label="Password requirements">
        <li data-rule="length">At least 8 characters</li>
        <li data-rule="mixed">Upper and lowercase letters</li>
        <li data-rule="number">At least one number</li>
        <li data-rule="symbol">At least one symbol</li>
        <li data-rule="match">Passwords match</li>
    </ul>

    <button type="submit" class="ac-btn" data-busy="Sending code…">Continue</button>
</form>
@endsection

@section('footer')
    <span>Already activated? <a href="{{ route('login') }}">Sign in</a></span>
    <span>Parent? <a href="{{ route('parent.activate') }}">Link with your child's code</a></span>
@endsection
