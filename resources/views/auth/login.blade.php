@extends('layouts.auth-card')

@section('title', 'Sign in')
@section('heading', 'Welcome back')
@section('desc', 'Sign in with your email, LRN or mobile number.')

@section('content')
<style>
    .lg-status { min-height: 18px; margin-top: 5px; font-size: 12.5px; color: var(--text-3); }
    .lg-status.is-ok { color: var(--ok-dk); }
    .lg-status.is-bad { color: var(--danger); }
    .ac-input.is-valid { border-color: #86efac; }
    .lg-error { display: none; margin-top: 5px; font-size: 12.5px; color: var(--danger); }
    .lg-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin: 2px 0 18px; font-size: 13.5px; }
    .lg-row label { display: flex; align-items: center; gap: 8px; color: var(--text-2); cursor: pointer; }
    .lg-row input { width: 17px; height: 17px; accent-color: var(--accent); }
    .lg-row a { color: var(--accent); font-weight: 600; text-decoration: none; }
    .lg-row a:hover { text-decoration: underline; }
    .ac-btn.loading { pointer-events: none; }
    .lg-links { display: grid; gap: 6px; }
</style>

@if(session('signed_out'))
    <div class="ac-alert ac-alert-ok" role="status">You have been signed out.</div>
@endif

<form method="POST" action="{{ route('login.submit') }}" id="loginForm" novalidate @if(session('signed_out')) data-signed-out @endif>
    @csrf

    {{-- Honeypot: people never see or fill this; the server rejects it when filled --}}
    <div class="ac-hp" aria-hidden="true">
        <label for="company">Company</label>
        <input type="text" name="company" id="company" tabindex="-1" autocomplete="off">
    </div>

    <div class="ac-group">
        <label for="emailInput">Email, LRN or mobile number</label>
        <input type="text" name="email" id="emailInput" value="{{ old('email') }}"
               class="ac-input {{ $errors->has('email') ? 'is-error' : '' }}"
               placeholder="you@gmail.com · 12-digit LRN · 09XXXXXXXXX"
               autocomplete="username" autocapitalize="none" spellcheck="false" maxlength="254"
               aria-describedby="emailStatus" @error('email') aria-invalid="true" @enderror required data-autofocus>
        <p class="lg-status" id="emailStatus" aria-live="polite"></p>
    </div>

    <div class="ac-group">
        <label for="passwordInput">Password</label>
        <div class="ac-pw">
            <input type="password" name="password" id="passwordInput"
                   class="ac-input {{ $errors->has('password') ? 'is-error' : '' }}"
                   autocomplete="current-password" maxlength="100" aria-describedby="passwordError"
                   @error('password') aria-invalid="true" @enderror required>
            <button type="button" class="ac-pw-toggle" data-pw-toggle="passwordInput" aria-pressed="false">Show</button>
        </div>
        <p class="lg-error" id="passwordError" role="alert"></p>
    </div>

    <div class="lg-row">
        <label><input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}> Remember me</label>
        <a href="{{ route('password.request') }}">Forgot your password?</a>
    </div>

    <button type="submit" class="ac-btn" id="submitBtn">Sign in</button>
</form>
@endsection

@section('footer')
    <div class="lg-links">
        <span>Student, first time here? <a href="{{ route('activate') }}">Activate your account</a></span>
        <span>Parent? <a href="{{ route('parent.activate') }}">Link with your child's code</a></span>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('loginForm');
    const email = document.getElementById('emailInput');
    const password = document.getElementById('passwordInput');
    const status = document.getElementById('emailStatus');
    const passError = document.getElementById('passwordError');
    const btn = document.getElementById('submitBtn');
    const label = btn.textContent;

    const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const LRN_RE = /^\d{12}$/;
    // PH mobile: 09XXXXXXXXX or +63 9XX… (spaces/dashes allowed) — same rule as App\Support\PhoneNumber
    const isMobile = (v) => /^[\d\s\-+()]+$/.test(v) && /^(0|\+?63)9\d{9}$/.test(v.replace(/[\s\-()]/g, ''));
    const valid = (v) => EMAIL_RE.test(v) || LRN_RE.test(v) || isMobile(v);

    function setStatus(text, ok) {
        status.textContent = text;
        status.classList.toggle('is-ok', ok === true);
        status.classList.toggle('is-bad', ok === false);
    }

    // Live hint — the server is the real check
    email.addEventListener('input', function () {
        const v = email.value.trim();
        email.classList.remove('is-error', 'is-valid');
        if (!v) { setStatus('', null); return; }
        if (valid(v)) {
            setStatus(LRN_RE.test(v) ? 'Valid LRN' : (isMobile(v) ? 'Valid mobile number' : 'Valid email'), true);
            email.classList.add('is-valid');
            email.removeAttribute('aria-invalid');
        } else {
            setStatus(/^\d+$/.test(v) ? 'An LRN has 12 digits; a mobile number starts with 09.' : 'Enter an email, a 12-digit LRN or a mobile number.', false);
        }
    });

    password.addEventListener('input', function () {
        password.classList.remove('is-error');
        password.removeAttribute('aria-invalid');
        passError.style.display = 'none';
    });

    form.addEventListener('submit', function (e) {
        if (document.getElementById('company').value.trim() !== '') { e.preventDefault(); return; }   // bot trap

        let first = null;
        const v = email.value.trim();
        if (!v || !valid(v)) {
            email.classList.add('is-error');
            email.setAttribute('aria-invalid', 'true');
            setStatus(v ? 'Enter an email, a 12-digit LRN or a mobile number.' : 'Enter your email, LRN or mobile number.', false);
            first = email;
        }
        if (password.value === '') {
            password.classList.add('is-error');
            password.setAttribute('aria-invalid', 'true');
            passError.textContent = 'Enter your password.';
            passError.style.display = 'block';
            first = first || password;
        }
        if (first) { e.preventDefault(); first.focus(); return; }

        btn.classList.add('loading');
        btn.disabled = true;
        btn.textContent = 'Signing in…';
    });

    // After logout, and whenever Back/Forward brings this page back, start from an empty form with the
    // password hidden — browsers otherwise put the typed LRN and password back. A page restored from the
    // back/forward cache also gets its button back.
    window.addEventListener('pageshow', function (e) {
        const nav = performance.getEntriesByType('navigation')[0];
        if (e.persisted || (nav && nav.type === 'back_forward') || form.hasAttribute('data-signed-out')) {
            form.reset();
            password.value = '';
            const toggle = document.querySelector('[data-pw-toggle="passwordInput"]');
            if (password.type !== 'password' && toggle) toggle.click();
            email.classList.remove('is-valid');
            setStatus('', null);
            passError.style.display = 'none';
        }
        if (e.persisted) {
            btn.classList.remove('loading');
            btn.disabled = false;
            btn.textContent = label;
        }
    });
})();
</script>
@endpush
