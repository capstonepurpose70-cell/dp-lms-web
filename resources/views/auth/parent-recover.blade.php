@extends('layouts.auth-card')

@section('title', 'I-recover ang Parent Account')
@section('heading', 'Bagong phone o nakalimutan ang password')
@section('desc', 'Humingi ng bagong code sa iyong anak (sa kanyang DP-LMS account, menu na "Magulang"), saka gumawa ng bagong password.')

@section('icon')
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
@endsection

@section('content')
<form method="POST" action="{{ route('parent.recover.submit') }}" novalidate>
    @csrf

    <div class="ac-group">
        <label for="code">Code mula sa anak</label>
        <input class="ac-input ac-code @error('code') is-error @enderror" type="text" id="code" name="code"
               value="{{ old('code', $code) }}" maxlength="9" autocomplete="off" autocapitalize="characters"
               spellcheck="false" placeholder="XXXX-XXXX" required>
    </div>

    <div class="ac-group">
        <label for="contact_number">Mobile number ng account mo</label>
        <input class="ac-input" type="tel" id="contact_number" name="contact_number"
               value="{{ old('contact_number') }}" inputmode="tel" autocomplete="tel" placeholder="09XXXXXXXXX" maxlength="16" required>
    </div>

    <div class="ac-group">
        <label for="password">Bagong password</label>
        <input class="ac-input @error('password') is-error @enderror" type="password" id="password" name="password"
               autocomplete="new-password" maxlength="100" required>
        <p class="ac-hint">8+ characters, may malaki at maliit na letra, numero, at simbolo.</p>
    </div>

    <div class="ac-group">
        <label for="password_confirmation">Ulitin ang bagong password</label>
        <input class="ac-input" type="password" id="password_confirmation" name="password_confirmation"
               autocomplete="new-password" maxlength="100" required>
    </div>

    <button type="submit" class="ac-btn">Palitan ang password</button>
</form>
@endsection

@section('footer')
    <div><a href="{{ route('login') }}">Bumalik sa login</a></div>
@endsection
