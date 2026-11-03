@extends('layouts.auth-card')

@section('title', 'Terms and Privacy')
@section('heading', 'Before you continue')
@section('desc', 'DP-LMS now has a Terms of Use and a Privacy Policy. Please read them and agree to keep using your account.')

@section('content')
<style>
    .ta-list { list-style: none; display: grid; gap: 10px; margin-bottom: 18px; }
    .ta-list a { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; border: 1px solid var(--border); border-radius: var(--r-md);
                 text-decoration: none; color: var(--text-1); font-weight: 600; font-size: 14.5px; transition: border-color .15s, background-color .15s; }
    .ta-list a:hover { border-color: #86efac; background: var(--accent-lt); }
    .ta-list span { font-size: 12.5px; font-weight: 500; color: var(--text-3); }
    .ta-list svg { width: 18px; height: 18px; color: var(--accent); flex-shrink: 0; }
    .ta-out { margin-top: 10px; }
    .ta-out button { width: 100%; min-height: 44px; border: 1px solid var(--border); border-radius: var(--r-md); background: #fff; color: var(--text-2); font: inherit; font-weight: 600; cursor: pointer; }
    .ta-out button:hover { background: #f8fafc; }
</style>

<ul class="ta-list">
    <li><a href="{{ route('terms') }}" target="_blank" rel="noopener"><div>Terms of Use<br><span>How the portal may be used</span></div>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 01-1 1H5a1 1 0 01-1-1V7a1 1 0 011-1h5"/></svg></a></li>
    <li><a href="{{ route('privacy') }}" target="_blank" rel="noopener"><div>Privacy Policy<br><span>What we collect and how it is protected</span></div>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 01-1 1H5a1 1 0 01-1-1V7a1 1 0 011-1h5"/></svg></a></li>
</ul>

<form method="POST" action="{{ route('terms.accept.submit') }}" data-once>
    @csrf
    <label class="ac-check">
        <input type="checkbox" name="terms" value="1" required @error('terms') aria-invalid="true" @enderror>
        <span>I have read and agree to the Terms of Use and the Privacy Policy.</span>
    </label>
    <button type="submit" class="ac-btn" data-busy="Saving…">Agree and continue</button>
</form>

<form method="POST" action="{{ route('logout') }}" class="ta-out">
    @csrf
    <button type="submit">Not now — sign out</button>
</form>
@endsection
