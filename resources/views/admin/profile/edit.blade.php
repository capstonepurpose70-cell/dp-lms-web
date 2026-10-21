@extends('layouts.admin')
@section('title', 'Edit Profile')

@section('content')
<style>
    .prof-page { max-width: 600px; margin: 0 auto; }
    .prof-card {
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-xl);
        box-shadow: var(--shadow-md);
        overflow: hidden;
        margin-bottom: 1.25rem;
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
        background: var(--blue-50);
        display: flex; align-items: center; justify-content: center;
    }
    .prof-card-icon svg { width: 16px; height: 16px; color: var(--blue-500); }
    .prof-card-title { font-size: 14px; font-weight: 700; color: var(--slate-800); }
    .prof-card-body { padding: 1.75rem; }
    .prof-field { margin-bottom: 1.25rem; }
    .prof-label {
        display: block;
        font-size: 12px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.06em;
        color: var(--slate-500);
        margin-bottom: 0.5rem;
    }
    .prof-input {
        width: 100%; height: 40px;
        padding: 0 0.875rem;
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-md);
        font-family: var(--font-ui); font-size: 13.5px;
        color: var(--slate-800);
        outline: none;
        transition: all 0.18s;
    }
    .prof-input:focus {
        border-color: var(--blue-400);
        box-shadow: 0 0 0 3px rgba(36,120,228,0.12);
    }
    .prof-input.error { border-color: var(--danger); }
    .prof-error { font-size: 12px; color: var(--danger); margin-top: 4px; }
    .prof-hint { font-size: 11.5px; color: var(--slate-500); margin-top: 4px; }
    .prof-field[hidden] { display: none; }
    .prof-btn {
        height: 40px; padding: 0 1.5rem;
        background: var(--blue-500);
        border: none; border-radius: var(--r-md);
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
    .prof-avatar-wrap {
        display: flex; align-items: center; gap: 1.25rem;
        margin-bottom: 1.75rem;
    }
    .prof-avatar-big {
        width: 72px; height: 72px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--blue-500), var(--blue-700));
        display: flex; align-items: center; justify-content: center;
        font-size: 26px; font-weight: 700; color: #fff;
        flex-shrink: 0;
        box-shadow: 0 4px 16px rgba(36,120,228,0.3);
    }
    .prof-avatar-info { line-height: 1.4; }
    .prof-avatar-name { font-size: 16px; font-weight: 700; color: var(--slate-900); }
    .prof-avatar-role {
        font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.06em;
        color: var(--blue-600);
    }
</style>

<div class="prof-page">

    {{-- Back link --}}
    <div style="margin-bottom:1.25rem;">
        <a href="{{ route('admin.dashboard') }}" style="font-size:13px;color:var(--slate-500);display:inline-flex;align-items:center;gap:0.4rem;text-decoration:none;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Dashboard
        </a>
    </div>

    {{-- Edit Profile Card --}}
    <div class="prof-card">
        <div class="prof-card-header">
            <div class="prof-card-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <span class="prof-card-title">Edit Profile</span>
        </div>
        <div class="prof-card-body">
            {{-- Avatar --}}
            <div class="prof-avatar-wrap">
                <div class="prof-avatar-big">
                    {{ mb_strtoupper(mb_substr($admin->name, 0, 1)) }}
                </div>
                <div class="prof-avatar-info">
                    <div class="prof-avatar-name">{{ $admin->name }}</div>
                    <div class="prof-avatar-role">Administrator</div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.profile.update') }}">
                @csrf @method('PATCH')

                <div class="prof-field">
                    <label class="prof-label">Full Name</label>
                    <input type="text" name="name"
                           value="{{ old('name', $admin->name) }}"
                           class="prof-input {{ $errors->has('name') ? 'error' : '' }}"
                           placeholder="Your full name">
                    @error('name')<div class="prof-error">{{ $message }}</div>@enderror
                </div>

                <div class="prof-field">
                    <label class="prof-label">Email Address</label>
                    <input type="email" name="email" id="profEmail"
                           value="{{ old('email', $admin->email) }}" data-original="{{ $admin->email }}"
                           class="prof-input {{ $errors->has('email') ? 'error' : '' }}"
                           placeholder="your@email.com" autocomplete="email">
                    <div class="prof-hint">This is the email you sign in with.</div>
                    @error('email')<div class="prof-error">{{ $message }}</div>@enderror
                </div>

                {{-- Needed only when the email changes (shown automatically) --}}
                <div class="prof-field" id="profPwField" @unless($errors->has('current_password') || old('email', $admin->email) !== $admin->email) hidden @endunless>
                    <label class="prof-label" for="profCurPw">Current Password</label>
                    <input type="password" name="current_password" id="profCurPw" autocomplete="current-password"
                           class="prof-input {{ $errors->has('current_password') ? 'error' : '' }}"
                           placeholder="Required to change your sign-in email">
                    @error('current_password')<div class="prof-error">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="prof-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
            </form>
        </div>
    </div>

    {{-- Security --}}
    <div class="prof-card" style="margin-top:1rem;">
        <div class="prof-card-body" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <div>
                <div style="font-weight:700;color:var(--slate-900);font-size:14px;">Password</div>
                <div style="font-size:12.5px;color:var(--slate-500);margin-top:2px;">Change it regularly. Changing it signs out every other device.</div>
            </div>
            <a href="{{ route('admin.profile.change-password') }}" class="prof-btn" style="width:auto;margin:0;text-decoration:none;">Change Password</a>
        </div>
    </div>

</div>

<script>
(function () {
    // Show the current-password field only when the sign-in email is being changed
    var email = document.getElementById('profEmail'), field = document.getElementById('profPwField');
    if (!email || !field) return;
    function upd() { field.hidden = email.value.trim().toLowerCase() === (email.dataset.original || '').toLowerCase() && !field.querySelector('.prof-error'); }
    email.addEventListener('input', upd);
})();
</script>
@endsection