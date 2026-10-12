@extends('layouts.auth-card')

@section('title', 'I-verify ang Email')
@section('heading', 'Ilagay ang code')
@section('desc')
    May 6-digit code na ipinadala sa <strong>{{ $email }}</strong>. Valid ito nang {{ $minutes }} minuto.
@endsection

@section('icon')
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
@endsection

@section('content')
<form method="POST" action="{{ route('activate.verify.submit') }}">
    @csrf
    <div class="ac-group">
        <label for="code">Verification code</label>
        <input class="ac-input ac-code @error('code') is-error @enderror" type="text" id="code" name="code"
               inputmode="numeric" pattern="\d{6}" maxlength="6" autocomplete="one-time-code"
               placeholder="••••••" required autofocus>
    </div>

    <label class="ac-check">
        <input type="checkbox" name="trust_device" value="1">
        <span>Tandaan ang device na ito nang 30 araw (hindi na hihingan ng code sa login). <strong>Huwag i-check</strong> kung shared o computer shop ito.</span>
    </label>

    <button type="submit" class="ac-btn">I-activate ang account</button>
</form>

<form method="POST" action="{{ route('activate.resend') }}" style="text-align:center;margin-top:10px;">
    @csrf
    <span style="font-size:13px;color:var(--text-2);">Walang dumating?</span>
    <button type="submit" class="ac-btn-link">Ipadala ulit</button>
</form>
@endsection

@section('footer')
    <div>Maling email? <a href="{{ route('activate') }}">Magsimula ulit</a></div>
@endsection
