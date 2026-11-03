@extends('layouts.auth-card')

@section('title', 'Activate Teacher Account')
@section('wrap-class', 'is-wide')

@section('steps')
    <li class="ac-step is-active" aria-current="step"><span class="ac-step-dot">1</span> Your details</li>
    <li class="ac-step-line" aria-hidden="true"></li>
    <li class="ac-step"><span class="ac-step-dot">2</span> Verify email</li>
@endsection

@section('heading', 'Activate your teacher account')
@section('desc', 'Use your employee ID and full name as they appear on the school\'s faculty list.')

@section('content')
<form method="POST" action="{{ route('teacher.activate.start') }}" data-once novalidate>
    @csrf
    <div class="ac-hp" aria-hidden="true">
        <label for="website">Website</label>
        <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
    </div>

    <div class="ac-grid">
        <div class="ac-group">
            <label for="employee_id">Employee ID</label>
            <input class="ac-input @error('employee_id') is-error @enderror" type="text" id="employee_id" name="employee_id"
                   value="{{ old('employee_id') }}" maxlength="50" autocomplete="off" autocapitalize="characters" spellcheck="false"
                   placeholder="e.g. 4567890" @error('employee_id') aria-invalid="true" @enderror required data-autofocus>
            <p class="ac-hint">From your school ID or appointment papers.</p>
        </div>
        <div class="ac-group">
            <label for="name">Full name</label>
            <input class="ac-input @error('name') is-error @enderror" type="text" id="name" name="name" value="{{ old('name') }}"
                   autocomplete="name" maxlength="255" placeholder="e.g. Maria Santos Reyes" @error('name') aria-invalid="true" @enderror required>
        </div>
        <div class="ac-group ac-span">
            <label for="email">Email address</label>
            <input class="ac-input @error('email') is-error @enderror" type="email" id="email" name="email" value="{{ old('email') }}"
                   autocomplete="email" autocapitalize="none" spellcheck="false" maxlength="254" placeholder="you@deped.gov.ph"
                   @error('email') aria-invalid="true" @enderror required>
            <p class="ac-hint">We'll send a 6-digit code here. Use one you can open.</p>
        </div>
        <div class="ac-group">
            <label for="password">Password</label>
            <div class="ac-pw">
                <input class="ac-input @error('password') is-error @enderror" type="password" id="password" name="password"
                       autocomplete="new-password" maxlength="100" aria-describedby="pw-rules" @error('password') aria-invalid="true" @enderror required>
                <button type="button" class="ac-pw-toggle" data-pw-toggle="password" aria-pressed="false">Show</button>
            </div>
        </div>
        <div class="ac-group">
            <label for="password_confirmation">Confirm password</label>
            <div class="ac-pw">
                <input class="ac-input" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" maxlength="100" required>
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

    <label class="ac-check">
        <input type="checkbox" name="terms" value="1" required @checked(old('terms')) @error('terms') aria-invalid="true" @enderror>
        <span>I agree to the <a href="{{ route('terms') }}" target="_blank" rel="noopener">Terms of Use</a> and the <a href="{{ route('privacy') }}" target="_blank" rel="noopener">Privacy Policy</a>.</span>
    </label>

    <button type="submit" class="ac-btn" data-busy="Sending code…">Continue</button>
</form>
@endsection

@section('footer')
    <span>Already activated? <a href="{{ route('login') }}">Sign in</a></span>
    <span>Not on the faculty list? Ask the school admin.</span>
@endsection
