@extends('layouts.admin')
@section('title', 'Change Password')

@section('content')
<style>
    .prof-page { max-width: 600px; margin: 0 auto; }
    .prof-card {
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-xl);
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }
    .prof-card-header {
        padding: 1.25rem 1.75rem;
        border-bottom: 1px solid var(--border-default);
        background: var(--slate-50);
        display: flex; align-items: center; gap: 0.75rem;
    }
    .prof-card-icon {
        width: 34px; height: 34px;
        border-radius: var(--r-md);
        background: #fff7ed;
        display: flex; align-items: center; justify-content: center;
    }
    .prof-card-icon svg { width: 16px; height: 16px; color: #c2410c; }
    .prof-card-title { font-size: 14px; font-weight: 700; color: var(--slate-800); }
    .prof-card-body { padding: 1.75rem; }
    .prof-field { margin-bottom: 1.25rem; }
    .prof-label {
        display: block;
        font-size: 12px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.06em;
        color: var(--slate-500); margin-bottom: 0.5rem;
    }
    .prof-input-wrap { position: relative; }
    .prof-input {
        width: 100%; height: 40px;
        padding: 0 2.5rem 0 0.875rem;
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-md);
        font-family: var(--font-ui); font-size: 13.5px;
        color: var(--slate-800); outline: none;
        transition: all 0.18s;
    }
    .prof-input:focus {
        border-color: var(--blue-400);
        box-shadow: 0 0 0 3px rgba(36,120,228,0.12);
    }
    .prof-input.error { border-color: var(--danger); }
    .prof-toggle {
        position: absolute; right: 0.75rem; top: 50%;
        transform: translateY(-50%);
        background: none; border: none; cursor: pointer;
        color: var(--slate-400); padding: 0;
        display: flex; align-items: center;
    }
    .prof-toggle:hover { color: var(--slate-700); }
    .prof-toggle svg { width: 16px; height: 16px; }
    .prof-error { font-size: 12px; color: var(--danger); margin-top: 4px; }
    .prof-hint { font-size: 11.5px; color: var(--slate-400); margin-top: 4px; }
    .prof-btn {
        height: 40px; padding: 0 1.5rem;
        background: var(--blue-500); border: none;
        border-radius: var(--r-md);
        font-family: var(--font-ui); font-size: 13.5px; font-weight: 700;
        color: #fff; cursor: pointer;
        transition: all 0.18s;
        display: inline-flex; align-items: center; gap: 0.5rem;
    }
    .prof-btn:hover { background: var(--blue-600); transform: translateY(-1px); }
    .prof-btn svg { width: 15px; height: 15px; }
    .prof-flash {
        display: flex; align-items: center; gap: 0.75rem;
        background: #ecfdf5; border: 1px solid #6ee7b7;
        color: #047857; font-size: 13px; font-weight: 500;
        border-radius: var(--r-lg); padding: 0.75rem 1rem;
        margin-bottom: 1.25rem;
    }
    .prof-flash svg { width: 17px; height: 17px; flex-shrink: 0; }
</style>

<div class="prof-page">

    <div style="margin-bottom:1.25rem;">
        <a href="{{ route('admin.profile.edit') }}" style="font-size:13px;color:var(--slate-500);display:inline-flex;align-items:center;gap:0.4rem;text-decoration:none;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Profile
        </a>
    </div>

    @if(session('success'))
    <div class="prof-flash">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="prof-card">
        <div class="prof-card-header">
            <div class="prof-card-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <span class="prof-card-title">Change Password</span>
        </div>
        <div class="prof-card-body">
            <form method="POST" action="{{ route('admin.profile.update-password') }}">
                @csrf @method('PATCH')

                <div class="prof-field">
                    <label class="prof-label">Current Password</label>
                    <div class="prof-input-wrap">
                        <input type="password" name="current_password" id="cur_pw"
                               class="prof-input {{ $errors->has('current_password') ? 'error' : '' }}"
                               placeholder="Enter current password">
                        <button type="button" class="prof-toggle" onclick="togglePw('cur_pw', this)">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @error('current_password')<div class="prof-error">{{ $message }}</div>@enderror
                </div>

                <div class="prof-field">
                    <label class="prof-label">New Password</label>
                    <div class="prof-input-wrap">
                        <input type="password" name="password" id="new_pw"
                               class="prof-input {{ $errors->has('password') ? 'error' : '' }}"
                               placeholder="Min. 8 characters">
                        <button type="button" class="prof-toggle" onclick="togglePw('new_pw', this)">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="prof-hint">At least 8 characters</div>
                    @error('password')<div class="prof-error">{{ $message }}</div>@enderror
                </div>

                <div class="prof-field">
                    <label class="prof-label">Confirm New Password</label>
                    <div class="prof-input-wrap">
                        <input type="password" name="password_confirmation" id="conf_pw"
                               class="prof-input"
                               placeholder="Repeat new password">
                        <button type="button" class="prof-toggle" onclick="togglePw('conf_pw', this)">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="prof-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update Password
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
@endsection