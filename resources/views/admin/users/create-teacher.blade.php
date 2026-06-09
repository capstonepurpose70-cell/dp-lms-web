@extends('layouts.admin')
@section('title', 'Create Teacher')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

<style>
/* ═══════════════════════════════════════════════
   DESIGN TOKENS — scoped to .ct-page
   Prevents collision with admin layout :root vars
   (esp. --ease-out used by sidebar transition)
═══════════════════════════════════════════════ */
.ct-page {
    /* Brand — matches create-faculty purple */
    --ct-accent:          #7c3aed;
    --ct-accent-dim:      #6d28d9;
    --ct-accent-glow:     rgba(124, 58, 237, 0.22);
    --ct-accent-glow-sm:  rgba(124, 58, 237, 0.12);
    --ct-accent-glow-xs:  rgba(124, 58, 237, 0.06);
    --ct-accent-ring:     rgba(124, 58, 237, 0.20);
    --ct-accent-soft:     #ede9fe;
    --ct-accent-soft2:    #f5f3ff;

    /* Surface — LIGHT, same as create-faculty */
    --ct-bg-page:         #f8fafc;   /* var(--slate-50) */
    --ct-bg-card:         #ffffff;
    --ct-bg-header:       #f8fafc;   /* card header bg */
    --ct-bg-raised:       #f1f5f9;   /* var(--slate-100) */
    --ct-bg-hover:        #f1f5f9;
    --ct-bg-field:        #ffffff;

    /* Border — light */
    --ct-border:          #e2e8f0;   /* var(--slate-200) */
    --ct-border-lit:      #e2e8f0;
    --ct-border-accent:   rgba(124, 58, 237, 0.25);

    /* Text — dark on light */
    --ct-txt-hi:          #0f172a;   /* var(--slate-900) */
    --ct-txt-md:          #64748b;   /* var(--slate-500) */
    --ct-txt-lo:          #94a3b8;   /* var(--slate-400) */

    /* States */
    --ct-green:           #22c55e;
    --ct-green-glow:      rgba(34, 197, 94, 0.12);
    --ct-red:             #ef4444;
    --ct-red-glow:        rgba(239, 68, 68, 0.10);

    /* Easing — prefixed, won't override admin --ease-out */
    --ct-ease-out:        cubic-bezier(0.16, 1, 0.3, 1);
    --ct-ease-in-out:     cubic-bezier(0.83, 0, 0.17, 1);
    --ct-ease-spring:     cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* ── Page shell ── */
.ct-page {
    max-width: none;
    width: 100%;
    margin: 0;
    padding: 1.75rem 2rem 4rem;
    opacity: 0;
    font-family: 'DM Sans', sans-serif;
    color: var(--ct-txt-hi);  /* now #0f172a — dark text on light bg */
}

/* ── Back button — matches cf-back-btn (light) ── */
.ct-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.48rem 0.9rem 0.48rem 0.7rem;
    background: var(--ct-bg-card);
    border: 1px solid var(--ct-border);
    border-radius: 9px;
    font-family: 'Sora', sans-serif;
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--ct-txt-md);
    text-decoration: none;
    margin-bottom: 1.75rem;
    opacity: 0;
    transition:
        border-color 180ms var(--ct-ease-out),
        color        180ms var(--ct-ease-out),
        background   180ms var(--ct-ease-out),
        box-shadow   180ms var(--ct-ease-out);
}
@media (hover: hover) and (pointer: fine) {
    .ct-back-btn:hover {
        background: var(--ct-bg-raised);
        border-color: var(--ct-accent);
        color: var(--ct-txt-hi);
        box-shadow: 0 0 0 3px var(--ct-accent-glow-xs);
    }
}
.ct-back-btn svg { width: 15px; height: 15px; flex-shrink: 0; }

/* ── Card ── */
.ct-card {
    background: var(--ct-bg-card);
    border: 1px solid var(--ct-border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow:
        0 1px 2px rgba(15, 23, 42, 0.04),
        0 10px 30px rgba(15, 23, 42, 0.06);
    position: relative;
    opacity: 0;
    transform: translateY(18px);
}

/* Atmospheric glows — same structure as original */
.ct-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 300px; height: 300px;
    background: radial-gradient(circle at 0% 0%, var(--ct-accent-soft), transparent 65%);
    pointer-events: none;
    z-index: 0;
}
.ct-card::after {
    content: '';
    position: absolute;
    bottom: 0; right: 0;
    width: 200px; height: 200px;
    background: radial-gradient(circle at 100% 100%, rgba(124,58,237,0.04), transparent 65%);
    pointer-events: none;
    z-index: 0;
}

/* ── Card header ── */
.ct-header {
    padding: 2.2rem 2.6rem 1.75rem;
    border-bottom: 1px solid var(--ct-border);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1.5rem;
    position: relative;
    z-index: 1;
}
.ct-header-kicker {
    font-family: 'Sora', sans-serif;
    font-size: 0.58rem;
    font-weight: 700;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--ct-accent);
    margin-bottom: 0.6rem;
    display: flex;
    align-items: center;
    gap: 0.45rem;
}
/* Pulse dot — Emil: purposeful, not decorative noise */
.kicker-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--ct-accent);
    box-shadow: 0 0 8px var(--ct-accent);
    animation: dot-pulse 2.4s ease-in-out infinite;
}
@keyframes dot-pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.38; transform: scale(0.72); }
}
.ct-header h1 {
    font-family: 'Sora', sans-serif;
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--ct-txt-hi);
    letter-spacing: -0.025em;
    line-height: 1.1;
    margin-bottom: 0.3rem;
}
.ct-header p {
    font-size: 0.82rem;
    color: var(--ct-txt-md);
    line-height: 1.6;
}
.ct-header-badge {
    flex-shrink: 0;
    width: 52px; height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--ct-accent-soft2), var(--ct-accent-glow-xs));
    border: 1px solid var(--ct-border-accent);
    display: flex; align-items: center; justify-content: center;
    color: var(--ct-accent);
    box-shadow: 0 0 24px var(--ct-accent-glow-xs);
}
.ct-header-badge svg { width: 22px; height: 22px; }

/* ── Progress steps ── */
.ct-progress {
    display: flex;
    align-items: center;
    padding: 1rem 2.6rem;
    border-bottom: 1px solid var(--ct-border);
    position: relative;
    z-index: 1;
    overflow-x: auto;
    gap: 0;
}
.ct-step {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    white-space: nowrap;
    font-family: 'Sora', sans-serif;
    font-size: 0.64rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    color: var(--ct-txt-lo);
    transition:
        color 250ms var(--ct-ease-out);
}
.ct-step.active { color: var(--ct-txt-hi); }
.ct-step.done   { color: var(--ct-green); }
.ct-step-num {
    width: 24px; height: 24px;
    border-radius: 50%;
    border: 1.5px solid var(--ct-border-lit);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.66rem; font-weight: 700;
    flex-shrink: 0;
    /* Emil: specify exact props */
    transition:
        background   250ms var(--ct-ease-out),
        border-color 250ms var(--ct-ease-out),
        color        250ms var(--ct-ease-out),
        box-shadow   250ms var(--ct-ease-out);
}
.ct-step.active .ct-step-num {
    background: var(--ct-accent);
    border-color: var(--ct-accent);
    color: #fff;
    box-shadow: 0 0 16px var(--ct-accent-glow);
}
.ct-step.done .ct-step-num {
    background: var(--ct-green-glow);
    border-color: var(--ct-green);
    color: var(--ct-green);
}
.ct-step-line {
    flex: 1;
    min-width: 24px;
    height: 1px;
    background: var(--ct-border);
    margin: 0 0.75rem;
}

/* ── Body ── */
.ct-body {
    padding: 2.2rem 2.6rem;
    position: relative;
    z-index: 1;
}
@media (max-width: 640px) {
    .ct-body    { padding: 1.5rem 1.25rem; }
    .ct-header  { padding: 1.5rem 1.25rem 1.25rem; flex-wrap: wrap; }
    .ct-progress{ padding: 0.9rem 1.25rem; }
}

/* ── Section label ── */
.ct-section-lbl {
    font-family: 'Sora', sans-serif;
    font-size: 0.58rem;
    font-weight: 700;
    letter-spacing: 0.20em;
    text-transform: uppercase;
    color: var(--ct-txt-lo);
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin-bottom: 1.35rem;
}
.ct-section-lbl::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--ct-border);
}

/* ── Grid ── */
.g2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
.g3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.1rem; }
@media (max-width: 640px) { .g2, .g3 { grid-template-columns: 1fr; } }
@media (max-width: 860px) { .g3 { grid-template-columns: 1fr 1fr; } }

.ct-field { margin-bottom: 1.15rem; }

/* ── Label ── */
.ct-label {
    display: block;
    font-family: 'Sora', sans-serif;
    font-size: 0.66rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--ct-txt-md);
    margin-bottom: 0.45rem;
}
.ct-label .req { color: var(--ct-accent); margin-left: 2px; }

/* ── Inputs ── */
.ct-input {
    width: 100%;
    background: var(--ct-bg-field);
    border: 1px solid var(--ct-border);
    color: var(--ct-txt-hi);
    border-radius: 10px;
    padding: 0.75rem 0.9rem;
    font-size: 0.875rem;
    font-family: 'DM Sans', sans-serif;
    outline: none;
    -webkit-appearance: none;
    appearance: none;
    /* Emil: specify exact props */
    transition:
        border-color 180ms var(--ct-ease-out),
        box-shadow   180ms var(--ct-ease-out),
        background   180ms var(--ct-ease-out);
}
.ct-input::placeholder { color: var(--ct-txt-lo); }
.ct-input:focus {
    border-color: var(--ct-accent);
    background: var(--ct-bg-hover);
    box-shadow: 0 0 0 3px var(--ct-accent-glow-sm);
}
.ct-input.is-valid {
    border-color: var(--ct-green);
    box-shadow: 0 0 0 3px var(--ct-green-glow);
}
.ct-input.is-error {
    border-color: var(--ct-red);
    box-shadow: 0 0 0 3px var(--ct-red-glow);
}

/* Select custom arrow */
select.ct-input {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%233a4a65'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.8rem center;
    background-size: 16px;
    padding-right: 2.3rem;
    cursor: pointer;
}
select.ct-input option      { background: var(--ct-bg-field); }
select.ct-input optgroup    { color: var(--ct-txt-lo); }

/* ── Field hints ── */
.field-hint {
    font-size: 0.7rem;
    color: var(--ct-txt-lo);
    margin-top: 0.38rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    min-height: 16px;
    line-height: 1.4;
}
.field-hint svg { width: 11px; height: 11px; flex-shrink: 0; }
.hint-ok  { color: var(--ct-green) !important; }
.hint-err { color: var(--ct-red)   !important; }

/* ── Error alert ── */
.ct-alert {
    background: rgba(239, 68, 68, 0.08);
    border: 1px solid rgba(239, 68, 68, 0.28);
    border-radius: 12px;
    padding: 0.9rem 1.1rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
}
.ct-alert svg { width: 16px; height: 16px; color: var(--ct-red); flex-shrink: 0; margin-top: 2px; }
.ct-alert ul  { list-style: none; display: flex; flex-direction: column; gap: 0.25rem; }
.ct-alert li  {
    font-size: 0.8rem;
    color: #f87171;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.ct-alert li::before { content: '✕'; font-size: 0.58rem; color: var(--ct-red); font-weight: 700; }

/* ── Divider ── */
.ct-hr { border: none; border-top: 1px solid var(--ct-border); margin: 1.85rem 0; }

/* ── Specialization tags ── */
.spec-tags { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.65rem; }
.spec-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.32rem;
    background: var(--ct-accent-soft);
    border: 1px solid var(--ct-accent-ring);
    color: var(--ct-txt-hi);
    padding: 0.26rem 0.65rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 500;
    /* Emil: CSS transition for interruptibility */
    transition:
        background   150ms var(--ct-ease-out),
        border-color 150ms var(--ct-ease-out);
}
.spec-tag:hover { background: var(--ct-accent-soft2); }
.spec-tag button {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--ct-txt-lo);
    padding: 0;
    display: flex;
    transition: color 150ms var(--ct-ease-out);
}
.spec-tag button:hover { color: var(--ct-red); }
.spec-tag button svg   { width: 11px; height: 11px; }

.spec-input-row          { display: flex; gap: 0.5rem; }
.spec-input-row .ct-input{ flex: 1; }
.spec-add-btn {
    flex-shrink: 0;
    background: var(--ct-bg-raised);
    border: 1px solid var(--ct-border-lit);
    color: var(--ct-txt-md);
    border-radius: 10px;
    padding: 0.75rem 0.85rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.32rem;
    font-size: 0.8rem;
    font-family: 'DM Sans', sans-serif;
    white-space: nowrap;
    transition:
        background   180ms var(--ct-ease-out),
        border-color 180ms var(--ct-ease-out),
        color        180ms var(--ct-ease-out);
}
@media (hover: hover) and (pointer: fine) {
    .spec-add-btn:hover {
        background: var(--ct-accent-soft);
        border-color: var(--ct-accent);
        color: var(--ct-txt-hi);
    }
}
.spec-add-btn svg { width: 14px; height: 14px; }

/* ── Invite notice ── */
.ct-invite-notice {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    background: var(--ct-accent-glow-xs);
    border: 1px solid var(--ct-accent-ring);
    border-radius: 16px;
    padding: 1.4rem 1.6rem;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
}
.ct-invite-notice::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, var(--ct-accent-soft), transparent 60%);
    pointer-events: none;
    opacity: 0.5;
}
.ct-invite-icon {
    flex-shrink: 0;
    width: 44px; height: 44px;
    border-radius: 12px;
    background: var(--ct-accent-soft2);
    border: 1px solid var(--ct-accent-ring);
    display: flex; align-items: center; justify-content: center;
    color: var(--ct-accent);
    position: relative;
    /* subtle breathing pulse — purposeful: indicates async action */
    animation: icon-breathe 3.5s ease-in-out infinite;
}
@keyframes icon-breathe {
    0%, 100% { box-shadow: 0 0 0 0 var(--ct-accent-glow); }
    50%       { box-shadow: 0 0 0 6px transparent; }
}
.ct-invite-icon svg { width: 21px; height: 21px; }
.ct-invite-body     { position: relative; }
.ct-invite-body strong {
    display: block;
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--ct-txt-hi);
    margin-bottom: 0.3rem;
}
.ct-invite-body p {
    font-size: 0.8rem;
    color: var(--ct-txt-md);
    line-height: 1.6;
    margin-bottom: 0.75rem;
}
.ct-invite-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}
.ct-invite-meta span {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.68rem;
    font-weight: 600;
    color: var(--ct-accent);
    letter-spacing: 0.02em;
}
.ct-invite-meta svg { width: 12px; height: 12px; flex-shrink: 0; }

/* ── Actions ── */
.ct-actions {
    display: flex;
    gap: 0.85rem;
    padding-top: 1.85rem;
    border-top: 1px solid var(--ct-border);
    flex-wrap: wrap;
}

.ct-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    border-radius: 11px;
    font-family: 'Sora', sans-serif;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    padding: 0.82rem 1.5rem;
    cursor: pointer;
    text-decoration: none;
    border: none;
    outline: none;
    position: relative;
    overflow: hidden;
    /* Emil: specify exact props */
    transition:
        background   200ms var(--ct-ease-out),
        box-shadow   200ms var(--ct-ease-out),
        border-color 200ms var(--ct-ease-out),
        color        200ms var(--ct-ease-out),
        transform    160ms var(--ct-ease-spring);
}
.ct-btn svg { width: 15px; height: 15px; flex-shrink: 0; }

/* Emil: scale(0.97) on :active — buttons must feel responsive */
.ct-btn:active { transform: scale(0.97); }

/* Primary */
.ct-btn-primary {
    flex: 2;
    min-width: 200px;
    background: var(--ct-accent);
    color: #fff;
    box-shadow:
        0 4px 28px var(--ct-accent-glow),
        0 1px 0 rgba(255,255,255,0.10) inset;
}

/* Shimmer sweep — Emil: purposeful, matches create-faculty pattern */
.ct-btn-primary::before {
    content: '';
    position: absolute;
    top: 0; left: -100%;
    width: 60%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.13), transparent);
    transition: left 480ms var(--ct-ease-out);
}
@media (hover: hover) and (pointer: fine) {
    .ct-btn-primary:hover {
        background: var(--ct-accent-dim);
        box-shadow:
            0 6px 36px rgba(124,58,237,0.48),
            0 1px 0 rgba(255,255,255,0.10) inset;
        transform: translateY(-1px);
    }
    .ct-btn-primary:hover::before { left: 160%; }
}
/* Emil: :active resets transform, scale applied via .ct-btn:active above */
.ct-btn-primary:active { transform: scale(0.97) !important; }

/* Loading state */
.ct-btn-primary.loading .btn-txt  { opacity: 0; }
.ct-btn-primary.loading .btn-spin { display: flex; }

.btn-txt  { display: flex; align-items: center; gap: 0.42rem; }
.btn-spin {
    display: none;
    position: absolute;
    width: 18px; height: 18px;
    border: 2px solid rgba(255,255,255,0.28);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 0.65s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Ghost */
.ct-btn-ghost {
    flex: 1;
    min-width: 110px;
    background: transparent;
    color: var(--ct-txt-md);
    border: 1px solid var(--ct-border-lit);
}
@media (hover: hover) and (pointer: fine) {
    .ct-btn-ghost:hover {
        background: var(--ct-bg-raised);
        color: var(--ct-txt-hi);
        border-color: var(--ct-border-accent);
    }
}

@media (max-width: 480px) { .ct-actions { flex-direction: column; } }

/* ── Reduced motion — Emil: keep opacity, remove movement ── */
@media (prefers-reduced-motion: reduce) {
    .kicker-dot,
    .ct-invite-icon,
    .btn-spin  { animation: none; }
    .ct-input,
    .ct-btn,
    .ct-back-btn,
    .spec-tag,
    .spec-add-btn { transition: none; }
}
</style>

<div class="ct-page" id="ctPage">

    <a href="{{ route('admin.users.index') }}" class="ct-back-btn" id="ctBackBtn">
        <svg viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd"/>
        </svg>
        Back to Users
    </a>

    <div class="ct-card" id="ctCard">

        {{-- ── Header ── --}}
        <div class="ct-header">
            <div>
                <div class="ct-header-kicker">
                    <span class="kicker-dot"></span>
                    User Management
                </div>
                <h1>Create Teacher Account</h1>
                <p>An invite link will be sent to the teacher's email to set their own password.</p>
            </div>
            <div class="ct-header-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>

        {{-- ── Progress — 3 steps ── --}}
        <div class="ct-progress">
            <div class="ct-step active" id="ps1">
                <div class="ct-step-num">1</div>
                <span>Account Info</span>
            </div>
            <div class="ct-step-line"></div>
            <div class="ct-step" id="ps2">
                <div class="ct-step-num">2</div>
                <span>Professional Details</span>
            </div>
            <div class="ct-step-line"></div>
            <div class="ct-step" id="ps3">
                <div class="ct-step-num">3</div>
                <span>Invite</span>
            </div>
        </div>

        <div class="ct-body">

            @if($errors->any())
            <div class="ct-alert gsap-field">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST"
                  action="{{ route('admin.users.store-teacher') }}"
                  id="teacherForm"
                  novalidate>
                @csrf
                <input type="hidden" name="specializations_json" id="specializationsJson"
                       value="{{ old('specializations_json', '[]') }}">

                {{-- ══ SECTION 1 — ACCOUNT INFO ══ --}}
                <div class="ct-section-lbl gsap-section">01 · Account Information</div>

                <div class="g2 gsap-field">
                    <div class="ct-field">
                        <label class="ct-label">Full Name <span class="req">*</span></label>
                        <input type="text" name="name" id="nameInput"
                               value="{{ old('name') }}" required
                               placeholder="Maria Santos"
                               class="ct-input {{ $errors->has('name') ? 'is-error' : '' }}">
                        <p class="field-hint" id="nameHint"></p>
                    </div>
                    <div class="ct-field">
                        <label class="ct-label">Employee ID</label>
                        <input type="text" name="employee_id"
                               value="{{ old('employee_id') }}"
                               placeholder="EMP-2024-001"
                               class="ct-input {{ $errors->has('employee_id') ? 'is-error' : '' }}">
                        <p class="field-hint">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/>
                            </svg>
                            Used for official records
                        </p>
                    </div>
                </div>

                <div class="ct-field gsap-field">
                    <label class="ct-label">Email Address <span class="req">*</span></label>
                    <input type="email" name="email" id="emailInput"
                           value="{{ old('email') }}" required
                           placeholder="teacher@school.edu.ph"
                           class="ct-input {{ $errors->has('email') ? 'is-error' : '' }}">
                    <p class="field-hint" id="emailHint"></p>
                </div>

                <div class="g2 gsap-field">
                    <div class="ct-field">
                        <label class="ct-label">Contact Number</label>
                        <input type="text" name="contact_number"
                               value="{{ old('contact_number') }}"
                               placeholder="09XXXXXXXXX"
                               maxlength="11" inputmode="numeric"
                               class="ct-input">
                        <p class="field-hint">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/>
                            </svg>
                            11-digit PH mobile
                        </p>
                    </div>
                    <div class="ct-field">
                        <label class="ct-label">Civil Status</label>
                        <select name="civil_status" class="ct-input">
                            <option value="">— Select —</option>
                            <option value="single"    {{ old('civil_status') == 'single'    ? 'selected' : '' }}>Single</option>
                            <option value="married"   {{ old('civil_status') == 'married'   ? 'selected' : '' }}>Married</option>
                            <option value="widowed"   {{ old('civil_status') == 'widowed'   ? 'selected' : '' }}>Widowed</option>
                            <option value="separated" {{ old('civil_status') == 'separated' ? 'selected' : '' }}>Separated</option>
                        </select>
                    </div>
                </div>

                <hr class="ct-hr">

                {{-- ══ SECTION 2 — PROFESSIONAL DETAILS ══ --}}
                <div class="ct-section-lbl gsap-section">02 · Professional Details</div>

                <div class="g3 gsap-field">
                    <div class="ct-field">
                        <label class="ct-label">Department <span class="req">*</span></label>
                        <select name="department" id="deptSelect" class="ct-input">
                            <option value="">— Select —</option>
                            <option value="Science"        {{ old('department') == 'Science'        ? 'selected' : '' }}>Science</option>
                            <option value="Mathematics"    {{ old('department') == 'Mathematics'    ? 'selected' : '' }}>Mathematics</option>
                            <option value="English"        {{ old('department') == 'English'        ? 'selected' : '' }}>English</option>
                            <option value="Filipino"       {{ old('department') == 'Filipino'       ? 'selected' : '' }}>Filipino</option>
                            <option value="Social Studies" {{ old('department') == 'Social Studies' ? 'selected' : '' }}>Social Studies</option>
                            <option value="MAPEH"          {{ old('department') == 'MAPEH'          ? 'selected' : '' }}>MAPEH</option>
                            <option value="TLE"            {{ old('department') == 'TLE'            ? 'selected' : '' }}>TLE / TVL</option>
                            <option value="Values"         {{ old('department') == 'Values'         ? 'selected' : '' }}>Values Education</option>
                            <option value="ICT"            {{ old('department') == 'ICT'            ? 'selected' : '' }}>ICT</option>
                        </select>
                    </div>
                    <div class="ct-field">
                        <label class="ct-label">Position / Designation</label>
                        <select name="position" class="ct-input">
                            <option value="">— Select —</option>
                            <option value="Teacher I"         {{ old('position') == 'Teacher I'         ? 'selected' : '' }}>Teacher I</option>
                            <option value="Teacher II"        {{ old('position') == 'Teacher II'        ? 'selected' : '' }}>Teacher II</option>
                            <option value="Teacher III"       {{ old('position') == 'Teacher III'       ? 'selected' : '' }}>Teacher III</option>
                            <option value="Master Teacher I"  {{ old('position') == 'Master Teacher I'  ? 'selected' : '' }}>Master Teacher I</option>
                            <option value="Master Teacher II" {{ old('position') == 'Master Teacher II' ? 'selected' : '' }}>Master Teacher II</option>
                            <option value="Head Teacher"      {{ old('position') == 'Head Teacher'      ? 'selected' : '' }}>Head Teacher</option>
                        </select>
                    </div>
                    <div class="ct-field">
                        <label class="ct-label">Date Hired</label>
                        <input type="date" name="date_hired"
                               value="{{ old('date_hired') }}"
                               class="ct-input" style="color-scheme: dark;">
                    </div>
                </div>

                <div class="g2 gsap-field">
                    <div class="ct-field">
                        <label class="ct-label">Highest Educational Attainment</label>
                        <select name="education" class="ct-input">
                            <option value="">— Select —</option>
                            <option value="Bachelor's Degree" {{ old('education') == "Bachelor's Degree" ? 'selected' : '' }}>Bachelor's Degree</option>
                            <option value="With MA Units"     {{ old('education') == 'With MA Units'     ? 'selected' : '' }}>With MA Units</option>
                            <option value="Master's Degree"   {{ old('education') == "Master's Degree"   ? 'selected' : '' }}>Master's Degree</option>
                            <option value="With PhD Units"    {{ old('education') == 'With PhD Units'    ? 'selected' : '' }}>With PhD Units</option>
                            <option value="Doctorate"         {{ old('education') == 'Doctorate'         ? 'selected' : '' }}>Doctorate</option>
                        </select>
                    </div>
                    <div class="ct-field">
                        <label class="ct-label">Years of Experience</label>
                        <input type="number" name="years_experience"
                               value="{{ old('years_experience') }}"
                               placeholder="e.g. 5"
                               min="0" max="50"
                               class="ct-input">
                    </div>
                </div>

                <div class="ct-field gsap-field">
                    <label class="ct-label">Specializations / Expertise</label>
                    <div class="spec-input-row">
                        <input type="text" id="specInput"
                               placeholder="e.g. Algebra, Chemistry, Spoken English…"
                               class="ct-input">
                        <button type="button" class="spec-add-btn" onclick="addSpec()">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/>
                            </svg>
                            Add
                        </button>
                    </div>
                    <div class="spec-tags" id="specTags"></div>
                    <p class="field-hint" style="margin-top:.5rem;">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/>
                        </svg>
                        Press Add or Enter to add a specialization tag
                    </p>
                </div>

                <div class="ct-field gsap-field">
                    <label class="ct-label">
                        Certifications / Awards
                        <small style="color:var(--ct-txt-lo);font-weight:400;text-transform:none;letter-spacing:0">(optional)</small>
                    </label>
                    <input type="text" name="certifications"
                           value="{{ old('certifications') }}"
                           placeholder="e.g. LET Passer, CSE Professional, Best Teacher 2023"
                           class="ct-input">
                </div>

                <hr class="ct-hr">

                {{-- ══ SECTION 3 — INVITE ══ --}}
                <div class="ct-section-lbl gsap-section">03 · Account Invite</div>

                <div class="ct-invite-notice gsap-field">
                    <div class="ct-invite-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                        </svg>
                    </div>
                    <div class="ct-invite-body">
                        <strong>Invite link sent to teacher's email</strong>
                        <p>
                            After creating the account, the teacher will receive an email containing
                            a secure activation link. They will set their own password on first login.
                        </p>
                        <div class="ct-invite-meta">
                            <span>
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd"/>
                                </svg>
                                Expires in 72 hours
                            </span>
                            <span>
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/>
                                </svg>
                                Account activates on first login
                            </span>
                        </div>
                    </div>
                </div>

                {{-- ── Actions ── --}}
                <div class="ct-actions gsap-field">
                    <button type="submit" class="ct-btn ct-btn-primary" id="submitBtn">
                        <span class="btn-txt">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="15" height="15">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                            </svg>
                            Create &amp; Send Invite
                        </span>
                        <div class="btn-spin"></div>
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="ct-btn ct-btn-ghost">
                        Cancel
                    </a>
                </div>

            </form>
        </div>{{-- /ct-body --}}
    </div>{{-- /ct-card --}}
</div>{{-- /ct-page --}}

<script>
/* ═══════════════════════════════════════
   EMAIL VALIDATION
═══════════════════════════════════════ */
document.getElementById('emailInput').addEventListener('input', function () {
    const val  = this.value;
    const hint = document.getElementById('emailHint');
    if (!val) {
        hint.innerHTML = '';
        hint.className = 'field-hint';
        this.classList.remove('is-valid', 'is-error');
        updateSteps();
        return;
    }
    const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
    if (ok) {
        hint.textContent = '✓ Valid email';
        hint.className   = 'field-hint hint-ok';
        this.classList.add('is-valid');
        this.classList.remove('is-error');
    } else {
        hint.textContent = 'Enter a valid email address';
        hint.className   = 'field-hint hint-err';
        this.classList.remove('is-valid');
        this.classList.add('is-error');
    }
    updateSteps();
});

/* ═══════════════════════════════════════
   NAME VALIDATION
═══════════════════════════════════════ */
document.getElementById('nameInput').addEventListener('input', function () {
    const hint = document.getElementById('nameHint');
    if (this.value.trim().length >= 3) {
        hint.innerHTML   = '';
        hint.className   = 'field-hint';
        this.classList.add('is-valid');
        this.classList.remove('is-error');
    } else if (this.value.length > 0) {
        hint.textContent = 'Name too short';
        hint.className   = 'field-hint hint-err';
        this.classList.remove('is-valid');
        this.classList.add('is-error');
    } else {
        hint.innerHTML = '';
        hint.className = 'field-hint';
        this.classList.remove('is-valid', 'is-error');
    }
    updateSteps();
});

/* ═══════════════════════════════════════
   SPECIALIZATION TAGS
   Emil: CSS transitions for interruptibility
═══════════════════════════════════════ */
let specs = [];
try {
    specs = JSON.parse(document.getElementById('specializationsJson').value) || [];
} catch (e) {}

function renderSpecs() {
    const wrap = document.getElementById('specTags');
    wrap.innerHTML = specs.map((s, i) =>
        `<span class="spec-tag">${s}
            <button type="button" onclick="removeSpec(${i})" aria-label="Remove ${s}">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                </svg>
            </button>
        </span>`
    ).join('');
    document.getElementById('specializationsJson').value = JSON.stringify(specs);
}

function addSpec() {
    const inp = document.getElementById('specInput');
    const val = inp.value.trim();
    if (val && !specs.includes(val)) {
        specs.push(val);
        renderSpecs();
        /* Emil: scale from 0.82 not 0 — nothing appears from nothing */
        const last = document.querySelector('.spec-tag:last-child');
        if (last && typeof gsap !== 'undefined') {
            gsap.from(last, { scale: 0.82, opacity: 0, duration: 0.26, ease: 'back.out(2.2)' });
        }
    }
    inp.value = '';
    inp.focus();
}

function removeSpec(i) {
    specs.splice(i, 1);
    renderSpecs();
}

document.getElementById('specInput').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); addSpec(); }
});

renderSpecs();

/* ═══════════════════════════════════════
   PROGRESS STEPS — 3 steps
═══════════════════════════════════════ */
function updateSteps() {
    const name  = document.getElementById('nameInput').value.trim();
    const email = document.getElementById('emailInput').value.trim();
    const dept  = document.getElementById('deptSelect').value;

    const s1 = name.length >= 3 && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    const s2 = dept !== '';
    const s3 = s1 && s2;

    const steps = [
        { id: 'ps1', done: s1,      active: !s1 },
        { id: 'ps2', done: s2,      active: s1 && !s2 },
        { id: 'ps3', done: s3,      active: s1 && s2 },
    ];

    steps.forEach(({ id, done, active }) => {
        const el = document.getElementById(id);
        el.classList.remove('active', 'done');
        if (done)        el.classList.add('done');
        else if (active) el.classList.add('active');
    });
}

document.getElementById('deptSelect').addEventListener('change', updateSteps);

/* ═══════════════════════════════════════
   FORM SUBMIT — loading state
═══════════════════════════════════════ */
document.getElementById('teacherForm').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.classList.add('loading');
    btn.disabled = true;
});

/* ═══════════════════════════════════════
   GSAP ENTRANCE
   Emil: power4.out for card, stagger fields
   CSS-off-main-thread for predetermined
   GSAP for dynamic/interruptible JS parts
═══════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function () {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isPointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    if (reduced) {
        /* Reduced motion: opacity only, no movement */
        gsap.set('#ctPage', { opacity: 1 });
        gsap.to(['#ctBackBtn', '#ctCard'], { opacity: 1, duration: 0.25, stagger: 0.05 });
        gsap.to(['.gsap-section', '.gsap-field'], { opacity: 1, duration: 0.2, stagger: 0.03 });
    } else {
        const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

        tl.to('#ctPage',    { opacity: 1, duration: 0.01 })
          /* Emil: back btn translates in from slight up — asymmetric, feels fast */
          .to('#ctBackBtn', { opacity: 1, y: 0, duration: 0.42 }, 0.06)
          /* Emil: card with power4 = very snappy, settles cleanly */
          .to('#ctCard',    { opacity: 1, y: 0, duration: 0.65, ease: 'power4.out' }, 0.14)
          .from('.ct-header',    { opacity: 0, y: 12, duration: 0.45 }, 0.36)
          .from('.ct-progress',  { opacity: 0, duration: 0.32 },         0.50)
          /* Emil: stagger 60ms — feels alive, not mechanical */
          .from('.gsap-section', { opacity: 0, y: 9,  stagger: 0.065, duration: 0.38 }, 0.56)
          .from('.gsap-field',   { opacity: 0, y: 7,  stagger: 0.030, duration: 0.32 }, 0.62);

        /* Back btn — GSAP x-nudge (spring feel, pointer-gated) */
        if (isPointer) {
            const back = document.getElementById('ctBackBtn');
            back.addEventListener('mouseenter', () =>
                gsap.to(back, { x: -3, duration: 0.18, ease: 'power2.out' })
            );
            back.addEventListener('mouseleave', () =>
                gsap.to(back, { x: 0, duration: 0.18, ease: 'power2.out' })
            );
        }

        /* Submit btn — translateY hover + scale press */
        if (isPointer) {
            const sub = document.getElementById('submitBtn');
            sub.addEventListener('mouseenter', () => {
                if (!sub.classList.contains('loading'))
                    gsap.to(sub, { y: -2, duration: 0.18, ease: 'power2.out' });
            });
            sub.addEventListener('mouseleave', () =>
                gsap.to(sub, { y: 0, scale: 1, duration: 0.18, ease: 'power2.out' })
            );
            /* Emil: 0.97 scale on press — immediate tactile feedback */
            sub.addEventListener('mousedown', () =>
                gsap.to(sub, { scale: 0.97, duration: 0.10, ease: 'power2.out' })
            );
            sub.addEventListener('mouseup', () =>
                gsap.to(sub, { scale: 1, duration: 0.14, ease: 'power2.out' })
            );
        }
    }

    updateSteps();
});
</script>

@endsection