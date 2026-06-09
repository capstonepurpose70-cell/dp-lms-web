@extends('layouts.admin')
@section('title', 'Add Faculty')

@section('content')
<style>
    .cf-page {
        animation: cf-fadein 0.32s cubic-bezier(0.22, 1, 0.36, 1) both;
    }
    @keyframes cf-fadein {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .cf-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.75rem;
    }
    .cf-back-btn {
        width: 34px; height: 34px;
        display: inline-flex;
        align-items: center; justify-content: center;
        border-radius: var(--r-md);
        border: 1px solid var(--border-default);
        background: var(--white);
        color: var(--slate-500);
        text-decoration: none;
        flex-shrink: 0;
        transition: all 0.18s cubic-bezier(0.22,1,0.36,1);
    }
    .cf-back-btn:hover {
        background: var(--slate-100);
        color: var(--slate-700);
        transform: translateX(-2px);
    }
    .cf-back-btn svg { width: 16px; height: 16px; }

    .cf-header-meta { flex: 1; }
    .cf-header-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--slate-900);
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin: 0 0 0.2rem;
    }
    .cf-header-sub {
        font-size: 13px;
        color: var(--slate-500);
        margin: 0;
    }

    /* ── Layout ── */
    .cf-layout {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 900px) {
        .cf-layout { grid-template-columns: 1fr; }
    }

    /* ── Card ── */
    .cf-card {
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }
    .cf-card-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1.1rem 1.5rem;
        border-bottom: 1px solid var(--border-default);
        background: var(--slate-50);
    }
    .cf-card-icon {
        width: 32px; height: 32px;
        border-radius: var(--r-md);
        display: flex; align-items: center; justify-content: center;
        background: #ede9fe;
        color: #7c3aed;
        flex-shrink: 0;
    }
    .cf-card-icon svg { width: 16px; height: 16px; }
    .cf-card-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13.5px;
        font-weight: 700;
        color: var(--slate-900);
    }
    .cf-card-body { padding: 1.5rem; }

    /* ── Form fields ── */
    .cf-field { margin-bottom: 1.25rem; }
    .cf-field:last-child { margin-bottom: 0; }

    .cf-label {
        display: block;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--slate-500);
        margin-bottom: 0.4rem;
    }
    .cf-label span.req {
        color: #ef4444;
        margin-left: 2px;
    }

    .cf-input {
        width: 100%;
        height: 40px;
        padding: 0 0.875rem;
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-md);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13.5px;
        color: var(--slate-700);
        outline: none;
        transition: all 0.2s cubic-bezier(0.22,1,0.36,1);
    }
    .cf-input::placeholder { color: var(--slate-400); font-size: 13px; }
    .cf-input:focus {
        border-color: #7c3aed;
        box-shadow: 0 0 0 3px rgba(124,58,237,0.12);
    }
    .cf-input.is-error {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239,68,68,0.10);
    }

    .cf-hint {
        font-size: 11.5px;
        color: var(--slate-400);
        margin-top: 0.35rem;
    }
    .cf-error {
        font-size: 11.5px;
        color: #dc2626;
        margin-top: 0.35rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }
    .cf-error svg { width: 12px; height: 12px; flex-shrink: 0; }

    /* Password wrapper with toggle */
    .cf-pw-wrap {
        position: relative;
    }
    .cf-pw-wrap .cf-input {
        padding-right: 2.5rem;
    }
    .cf-pw-toggle {
        position: absolute;
        right: 0.7rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: var(--slate-400);
        padding: 0;
        display: flex;
        align-items: center;
        transition: color 0.15s;
    }
    .cf-pw-toggle:hover { color: var(--slate-600); }
    .cf-pw-toggle svg { width: 16px; height: 16px; }

    /* Password strength bar */
    .cf-strength-bar {
        display: flex;
        gap: 4px;
        margin-top: 0.5rem;
    }
    .cf-strength-seg {
        flex: 1;
        height: 3px;
        border-radius: 999px;
        background: var(--slate-200);
        transition: background 0.25s;
    }
    .cf-strength-label {
        font-size: 11px;
        font-weight: 600;
        margin-top: 0.25rem;
        color: var(--slate-400);
        transition: color 0.25s;
    }

    /* ── Submit button ── */
    .cf-btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0 1.5rem;
        height: 42px;
        border-radius: var(--r-md);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13.5px;
        font-weight: 700;
        color: #fff;
        background: #7c3aed;
        border: 1px solid rgba(124,58,237,0.3);
        box-shadow: 0 2px 12px rgba(124,58,237,0.22), 0 1px 0 rgba(255,255,255,0.1) inset;
        cursor: pointer;
        width: 100%;
        justify-content: center;
        position: relative;
        overflow: hidden;
        transition:
            background  0.22s cubic-bezier(0.22,1,0.36,1),
            box-shadow  0.22s cubic-bezier(0.22,1,0.36,1),
            transform   0.18s cubic-bezier(0.34,1.56,0.64,1);
    }
    .cf-btn-submit::after {
        content: '';
        position: absolute;
        top: 0; left: -80%;
        width: 60%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
        transition: left 0.5s cubic-bezier(0.22,1,0.36,1);
    }
    .cf-btn-submit:hover {
        background: #6d28d9;
        box-shadow: 0 4px 20px rgba(124,58,237,0.38), 0 1px 0 rgba(255,255,255,0.1) inset;
        transform: translateY(-1px);
    }
    .cf-btn-submit:hover::after { left: 140%; }
    .cf-btn-submit:active { transform: translateY(0); }
    .cf-btn-submit svg { width: 15px; height: 15px; flex-shrink: 0; }

    .cf-btn-cancel {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 1.5rem;
        height: 40px;
        border-radius: var(--r-md);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        font-weight: 600;
        color: var(--slate-600);
        background: var(--slate-100);
        border: 1px solid var(--border-default);
        text-decoration: none;
        width: 100%;
        margin-top: 0.6rem;
        transition: all 0.18s;
    }
    .cf-btn-cancel:hover {
        background: var(--slate-200);
        color: var(--slate-700);
    }

    /* ── Side info card ── */
    .cf-info-card {
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }
    .cf-info-header {
        padding: 1rem 1.25rem 0.75rem;
        border-bottom: 1px solid var(--border-default);
        background: var(--slate-50);
    }
    .cf-info-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12.5px;
        font-weight: 700;
        color: var(--slate-700);
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .cf-info-body { padding: 1.25rem; }

    .cf-info-item {
        display: flex;
        gap: 0.65rem;
        margin-bottom: 1rem;
    }
    .cf-info-item:last-child { margin-bottom: 0; }
    .cf-info-dot {
        width: 20px; height: 20px;
        border-radius: 50%;
        background: #ede9fe;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .cf-info-dot svg { width: 11px; height: 11px; color: #7c3aed; }
    .cf-info-text {
        font-size: 12.5px;
        color: var(--slate-600);
        line-height: 1.5;
    }
    .cf-info-text strong {
        display: block;
        font-weight: 700;
        color: var(--slate-800);
        font-size: 12px;
        margin-bottom: 1px;
    }

    /* ── Avatar preview ── */
    .cf-avatar-preview {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.25rem;
        background: #faf5ff;
        border: 1px solid #e9d5ff;
        border-radius: var(--r-md);
        margin-bottom: 1.25rem;
    }
    .cf-avatar-circle {
        width: 44px; height: 44px;
        border-radius: var(--r-md);
        background: #7c3aed;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; font-weight: 700; color: #fff;
        flex-shrink: 0;
        transition: all 0.2s;
    }
    .cf-avatar-meta { flex: 1; min-width: 0; }
    .cf-avatar-name {
        font-size: 13.5px; font-weight: 700;
        color: var(--slate-900);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .cf-avatar-role {
        font-size: 11px; font-weight: 600;
        color: #7c3aed;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    /* ── Divider ── */
    .cf-divider {
        height: 1px;
        background: var(--border-default);
        margin: 1.25rem 0;
    }

    .cf-section-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--slate-400);
        margin-bottom: 1rem;
    }
</style>

<div class="cf-page">

    {{-- ── Header ── --}}
    <div class="cf-header">
        <a href="{{ route('admin.users.index', ['tab' => 'faculty']) }}" class="cf-back-btn" title="Back">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="cf-header-meta">
            <h1 class="cf-header-title">Add Faculty Member</h1>
            <p class="cf-header-sub">Create a new faculty account with immediate access.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.users.store-faculty') }}" id="cf-form">
        @csrf
        <div class="cf-layout">

            {{-- ── Main form card ── --}}
            <div class="cf-card">
                <div class="cf-card-header">
                    <div class="cf-card-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <span class="cf-card-title">Account Information</span>
                </div>

                <div class="cf-card-body">

                    {{-- Avatar preview --}}
                    <div class="cf-avatar-preview" id="cf-avatar-preview">
                        <div class="cf-avatar-circle" id="cf-avatar-circle">?</div>
                        <div class="cf-avatar-meta">
                            <div class="cf-avatar-name" id="cf-avatar-name">Faculty Name</div>
                            <div class="cf-avatar-role">Faculty · Approved</div>
                        </div>
                    </div>

                    {{-- Name --}}
                    <div class="cf-field">
                        <label class="cf-label" for="name">Full Name <span class="req">*</span></label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="cf-input {{ $errors->has('name') ? 'is-error' : '' }}"
                            value="{{ old('name') }}"
                            placeholder="e.g. Maria Santos"
                            autocomplete="off"
                            required
                        >
                        @error('name')
                            <div class="cf-error">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="cf-field">
                        <label class="cf-label" for="email">Email Address <span class="req">*</span></label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="cf-input {{ $errors->has('email') ? 'is-error' : '' }}"
                            value="{{ old('email') }}"
                            placeholder="e.g. m.santos@school.edu.ph"
                            autocomplete="off"
                            required
                        >
                        @error('email')
                            <div class="cf-error">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="cf-divider"></div>
                    <div class="cf-section-label">Security</div>

                    {{-- Password --}}
                    <div class="cf-field">
                        <label class="cf-label" for="password">Password <span class="req">*</span></label>
                        <div class="cf-pw-wrap">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="cf-input {{ $errors->has('password') ? 'is-error' : '' }}"
                                placeholder="Min. 8 characters"
                                autocomplete="new-password"
                                required
                            >
                            <button type="button" class="cf-pw-toggle" onclick="togglePw('password', this)" tabindex="-1">
                                <svg id="pw-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        {{-- Strength bar --}}
                        <div class="cf-strength-bar" id="strength-bar">
                            <div class="cf-strength-seg" id="seg1"></div>
                            <div class="cf-strength-seg" id="seg2"></div>
                            <div class="cf-strength-seg" id="seg3"></div>
                            <div class="cf-strength-seg" id="seg4"></div>
                        </div>
                        <div class="cf-strength-label" id="strength-label">Enter a password</div>
                        @error('password')
                            <div class="cf-error">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="cf-field">
                        <label class="cf-label" for="password_confirmation">Confirm Password <span class="req">*</span></label>
                        <div class="cf-pw-wrap">
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="cf-input"
                                placeholder="Re-enter password"
                                autocomplete="new-password"
                                required
                            >
                            <button type="button" class="cf-pw-toggle" onclick="togglePw('password_confirmation', this)" tabindex="-1">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="cf-hint" id="pw-match-hint"></div>
                    </div>

                </div>{{-- /card-body --}}
            </div>{{-- /cf-card --}}

            {{-- ── Side panel ── --}}
            <div>
                {{-- Submit card --}}
                <div class="cf-card" style="margin-bottom:1rem;">
                    <div class="cf-card-body">
                        <button type="submit" class="cf-btn-submit">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                            Create Faculty Account
                        </button>
                        <a href="{{ route('admin.users.index', ['tab' => 'faculty']) }}" class="cf-btn-cancel">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Info card --}}
                <div class="cf-info-card">
                    <div class="cf-info-header">
                        <div class="cf-info-title">About Faculty Accounts</div>
                    </div>
                    <div class="cf-info-body">
                        <div class="cf-info-item">
                            <div class="cf-info-dot">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div class="cf-info-text">
                                <strong>Instant access</strong>
                                Account is auto-approved — faculty can log in immediately after creation.
                            </div>
                        </div>
                        <div class="cf-info-item">
                            <div class="cf-info-dot">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="cf-info-text">
                                <strong>Enrollment management</strong>
                                Faculty can view and process student enrollment requests.
                            </div>
                        </div>
                        <div class="cf-info-item">
                            <div class="cf-info-dot">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div class="cf-info-text">
                                <strong>Name format</strong>
                                Only letters, spaces, and periods are allowed in the name field.
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /side --}}
        </div>{{-- /cf-layout --}}
    </form>

</div>{{-- /cf-page --}}

<script>
(function () {
    /* ── Live avatar preview ── */
    const nameInput  = document.getElementById('name');
    const avatarCirc = document.getElementById('cf-avatar-circle');
    const avatarName = document.getElementById('cf-avatar-name');

    nameInput.addEventListener('input', function () {
        const val = this.value.trim();
        if (val) {
            const initials = val.split(/\s+/).map(w => w[0]).join('').slice(0, 2).toUpperCase();
            avatarCirc.textContent = initials;
            avatarName.textContent = val;
        } else {
            avatarCirc.textContent = '?';
            avatarName.textContent = 'Faculty Name';
        }
    });

    /* ── Password visibility toggle ── */
    window.togglePw = function (id, btn) {
        const input = document.getElementById(id);
        const isText = input.type === 'text';
        input.type = isText ? 'password' : 'text';
        btn.querySelector('svg').style.opacity = isText ? '1' : '0.5';
    };

    /* ── Password strength ── */
    const pwInput      = document.getElementById('password');
    const segs         = [1,2,3,4].map(i => document.getElementById('seg'+i));
    const strengthLbl  = document.getElementById('strength-label');

    const levels = [
        { label: 'Too short',  color: '#ef4444', count: 1 },
        { label: 'Weak',       color: '#f97316', count: 2 },
        { label: 'Fair',       color: '#eab308', count: 3 },
        { label: 'Strong',     color: '#22c55e', count: 4 },
    ];

    function getStrength(pw) {
        if (pw.length < 8) return 0;
        let score = 1;
        if (/[A-Z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        return score;
    }

    pwInput.addEventListener('input', function () {
        const pw  = this.value;
        const lvl = pw.length === 0 ? -1 : getStrength(pw) - 1;

        segs.forEach((s, i) => {
            s.style.background = (lvl >= 0 && i <= lvl) ? levels[Math.min(lvl,3)].color : 'var(--slate-200)';
        });

        strengthLbl.textContent = lvl < 0 ? 'Enter a password' : levels[Math.min(lvl,3)].label;
        strengthLbl.style.color = lvl < 0 ? 'var(--slate-400)' : levels[Math.min(lvl,3)].color;
    });

    /* ── Password match indicator ── */
    const pwConf     = document.getElementById('password_confirmation');
    const matchHint  = document.getElementById('pw-match-hint');

    function checkMatch() {
        if (!pwConf.value) { matchHint.textContent = ''; return; }
        const match = pwInput.value === pwConf.value;
        matchHint.textContent = match ? '✓ Passwords match' : '✗ Passwords do not match';
        matchHint.style.color = match ? '#22c55e' : '#ef4444';
    }

    pwInput.addEventListener('input', checkMatch);
    pwConf.addEventListener('input', checkMatch);

})();
</script>
@endsection