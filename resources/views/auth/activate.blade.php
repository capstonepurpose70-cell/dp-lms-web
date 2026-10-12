@extends('layouts.auth-card')

@section('title', 'I-activate ang Account')
@section('heading', 'I-activate ang Student Account')
@section('desc', 'Para sa unang beses. Gamitin ang LRN at buong pangalan mo gaya ng nasa class list ng paaralan.')

@section('icon')
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l6.16-3.42A12 12 0 0112 20.06a12 12 0 01-6.16-9.48L12 14z"/></svg>
@endsection

@section('content')
<form method="POST" action="{{ route('activate.start') }}" novalidate>
    @csrf

    {{-- Honeypot --}}
    <div class="ac-hp" aria-hidden="true">
        <label for="website">Website</label>
        <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
    </div>

    <div class="ac-group">
        <label for="lrn">LRN</label>
        <input class="ac-input @error('lrn') is-error @enderror" type="text" id="lrn" name="lrn"
               value="{{ old('lrn') }}" inputmode="numeric" pattern="\d{12}" maxlength="12"
               autocomplete="off" placeholder="12-digit LRN" required>
        <p class="ac-hint">Nasa report card o school ID mo.</p>
    </div>

    <div class="ac-group">
        <label for="name">Buong pangalan</label>
        <input class="ac-input" type="text" id="name" name="name" value="{{ old('name') }}"
               autocomplete="name" placeholder="Hal. Juan Santos Dela Cruz" maxlength="255" required>
    </div>

    <div class="ac-group">
        <label for="birthdate">Birthdate</label>
        <input class="ac-input" type="date" id="birthdate" name="birthdate" value="{{ old('birthdate') }}"
               autocomplete="bday" max="{{ now()->subYears(8)->toDateString() }}" required>
        <p class="ac-hint">Ikukumpara ito ng adviser mo sa enrollment form.</p>
    </div>

    <div class="ac-group">
        <label for="email">Email</label>
        <input class="ac-input @error('email') is-error @enderror" type="email" id="email" name="email"
               value="{{ old('email') }}" autocomplete="email" autocapitalize="none" spellcheck="false"
               placeholder="Dito ipapadala ang code" maxlength="254" required>
        <p class="ac-hint">Gamitin ang email na nabubuksan mo — dito rin ang "Forgot password".</p>
    </div>

    <div class="ac-group">
        <label for="password">Password</label>
        <input class="ac-input @error('password') is-error @enderror" type="password" id="password" name="password"
               autocomplete="new-password" maxlength="100" required>
        <p class="ac-hint">8+ characters, may malaki at maliit na letra, numero, at simbolo.</p>
    </div>

    <div class="ac-group">
        <label for="password_confirmation">Ulitin ang password</label>
        <input class="ac-input" type="password" id="password_confirmation" name="password_confirmation"
               autocomplete="new-password" maxlength="100" required>
    </div>

    <button type="submit" class="ac-btn">Ipadala ang code sa email</button>
</form>
@endsection

@section('footer')
    <div>May account na? <a href="{{ route('login') }}">Mag-login</a></div>
    <div>Magulang ka? <a href="{{ route('parent.activate') }}">I-link gamit ang code ng anak</a></div>
@endsection
