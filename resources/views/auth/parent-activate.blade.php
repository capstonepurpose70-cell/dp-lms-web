@extends('layouts.auth-card')

@section('title', 'Parent Account')
@section('heading', 'Gumawa ng Parent Account')
@section('desc', 'Ilagay ang code mula sa DP-LMS account ng iyong anak (menu na "Magulang").')

@section('icon')
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-4a3 3 0 11-6 0"/></svg>
@endsection

@section('content')
<div class="ac-alert ac-alert-info">
    Gamitin ang <strong>sarili mong phone</strong>. Kung sa phone ng anak mo, mag-logout muna siya.
</div>

<form method="POST" action="{{ route('parent.activate.submit') }}" novalidate>
    @csrf

    <div class="ac-group">
        <label for="code">Code mula sa anak</label>
        <input class="ac-input ac-code @error('code') is-error @enderror" type="text" id="code" name="code"
               value="{{ old('code', $code) }}" maxlength="9" autocomplete="off" autocapitalize="characters"
               spellcheck="false" placeholder="XXXX-XXXX" required>
    </div>

    <div class="ac-group">
        <label for="name">Buong pangalan mo</label>
        <input class="ac-input @error('name') is-error @enderror" type="text" id="name" name="name"
               value="{{ old('name') }}" autocomplete="name" maxlength="255" placeholder="Hal. Maria Santos Dela Cruz" required>
        <p class="ac-hint">Gaya ng nakasulat sa enrollment form ng anak mo (nanay, tatay, o guardian). Kapag tugma, makikita mo agad ang records niya.</p>
    </div>

    <div class="ac-group">
        <label for="contact_number">Mobile number</label>
        <input class="ac-input @error('contact_number') is-error @enderror" type="tel" id="contact_number" name="contact_number"
               value="{{ old('contact_number') }}" inputmode="tel" autocomplete="tel" placeholder="09XXXXXXXXX" maxlength="16" required>
        <p class="ac-hint">Ito ang gagamitin mo sa pag-login.</p>
    </div>

    <div class="ac-group">
        <label for="email">Email <span class="ac-opt">(optional)</span></label>
        <input class="ac-input @error('email') is-error @enderror" type="email" id="email" name="email"
               value="{{ old('email') }}" autocomplete="email" autocapitalize="none" maxlength="254">
        <p class="ac-hint">Kung wala, puwede pa rin. Sa bagong phone, hihingi ka lang ng bagong code sa anak mo.</p>
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

    <button type="submit" class="ac-btn">Gumawa ng account</button>
</form>
@endsection

@section('footer')
    <div>May account na? <a href="{{ route('login') }}">Mag-login</a></div>
    <div>Bagong phone o nakalimutan ang password? <a href="{{ route('parent.recover') }}">I-recover</a></div>
@endsection
