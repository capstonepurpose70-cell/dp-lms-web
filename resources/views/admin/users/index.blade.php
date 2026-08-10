@extends('layouts.admin')
@section('title', 'User Management')

@section('content')
<style>

    .um-page {
        animation: um-fadein 0.32s cubic-bezier(0.22, 1, 0.36, 1) both;
    }
    @keyframes um-fadein {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .um-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    .um-header-meta { flex: 1; min-width: 0; }
    .um-header-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--slate-900);
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin: 0 0 0.2rem;
    }
    .um-header-sub {
        font-size: 13px;
        color: var(--slate-500);
        margin: 0;
    }

    .um-btn-add {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0 1.1rem;
        height: 36px;
        border-radius: var(--r-md);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        font-weight: 700;
        color: #fff;
        background: var(--um-green-500);
        border: 1px solid rgba(5, 150, 105, 0.3);
        box-shadow: 0 2px 12px var(--um-green-glow), 0 1px 0 rgba(255,255,255,0.12) inset;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
        position: relative;
        overflow: hidden;
        transition:
            background  0.22s cubic-bezier(0.22,1,0.36,1),
            box-shadow  0.22s cubic-bezier(0.22,1,0.36,1),
            transform   0.18s cubic-bezier(0.34,1.56,0.64,1);
    }
    .um-btn-add::after {
        content: '';
        position: absolute;
        top: 0; left: -80%;
        width: 60%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.18), transparent);
        transition: left 0.5s cubic-bezier(0.22,1,0.36,1);
    }
    .um-btn-add:hover {
        background: var(--um-green-600);
        box-shadow: 0 4px 22px rgba(5,150,105,0.42), 0 1px 0 rgba(255,255,255,0.12) inset;
        transform: translateY(-1px);
        color: #fff;
    }
    .um-btn-add:hover::after { left: 140%; }
    .um-btn-add:active       { transform: translateY(0); }
    .um-btn-add svg          { width: 14px; height: 14px; flex-shrink: 0; }



    .um-tabs {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        border-bottom: 1px solid var(--border-default);
        margin-bottom: 1.25rem;
        padding-bottom: 0;
        overflow-x: auto;
    }
    .um-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 1rem;
        border-radius: var(--r-md) var(--r-md) 0 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        font-weight: 600;
        color: var(--slate-500);
        text-decoration: none;
        border: 1px solid transparent;
        border-bottom: none;
        white-space: nowrap;
            transition:
        color      0.18s cubic-bezier(0.22,1,0.36,1),
        background 0.18s cubic-bezier(0.22,1,0.36,1),
        border-color 0.18s cubic-bezier(0.22,1,0.36,1);
        position: relative;
        bottom: -1px;
    }
    .um-tab:hover {
        color: var(--slate-700);
        background: var(--slate-100);
    }
    .um-tab.active {
        background: var(--white);
        color: var(--um-blue-600);
        font-weight: 700;
        border-color: var(--border-default);
        border-bottom-color: var(--white);
    }
    .um-tab-badge {
        font-size: 10.5px;
        font-weight: 700;
        padding: 1px 7px;
        border-radius: 999px;
        background: var(--slate-200);
        color: var(--slate-500);
        line-height: 18px;
        transition: all 0.18s;
    }
    .um-tab.active .um-tab-badge {
        background: var(--um-blue-500);
        color: #fff;
    }

    .um-filterbar {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
    }

    .um-search {
        position: relative;
        display: flex;
        align-items: center;
    }
    .um-search-icon {
        position: absolute;
        left: 0.7rem;
        top: 50%;
        transform: translateY(-50%);
        width: 14px; height: 14px;
        color: var(--slate-400);
        pointer-events: none;
        transition: color 0.18s;
        flex-shrink: 0;
    }
    .um-search:focus-within .um-search-icon { color: var(--um-blue-500); }

    .um-search-input {
        height: 34px;
        width: 200px;
        padding: 0 0.75rem 0 2.1rem;
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-full);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        color: var(--slate-700);
        outline: none;
        transition: all 0.2s cubic-bezier(0.22,1,0.36,1);
    }
    .um-search-input::placeholder { color: var(--slate-400); font-size: 12.5px; }
    .um-search-input:focus {
        border-color: #4d96f0;
        box-shadow: 0 0 0 3px rgba(36,120,228,0.12);
        width: 240px;
    }

    .um-select {
        height: 34px;
        padding: 0 2rem 0 0.75rem;
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-md);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        color: var(--slate-700);
        outline: none;
        cursor: pointer;
        -webkit-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2394a3b8'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.6rem center;
        background-size: 15px;
        transition: all 0.18s;
    }
    .um-select:focus {
        border-color: var(--um-blue-500);
        box-shadow: 0 0 0 3px rgba(36,120,228,0.12);
    }
    .um-select option { background: #fff; color: var(--slate-700); }

    .um-btn-filter {
        height: 34px;
        padding: 0 1rem;
        background: var(--slate-100);
        border: 1px solid var(--border-default);
        border-radius: var(--r-md);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        font-weight: 600;
        color: var(--slate-700);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.18s;
    }
    .um-btn-filter:hover { background: var(--slate-200); }

    .um-btn-reset {
        height: 34px;
        padding: 0 0.85rem;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        font-weight: 500;
        color: var(--slate-500);
        background: transparent;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        transition: color 0.18s;
    }
    .um-btn-reset:hover { color: var(--um-blue-600); }

    .um-card {
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .um-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .um-table th {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: var(--slate-500);
        padding: 0.875rem 1.25rem;
        background: var(--slate-50);
        border-bottom: 1px solid var(--border-default);
        text-align: left;
        white-space: nowrap;
    }
    .um-table td {
        padding: 0.9rem 1.25rem;
        font-size: 13.5px;
        color: var(--slate-700);
        border-bottom: 1px solid var(--slate-100);
        vertical-align: middle;
        transition: background 0.15s;
    }
    .um-table tr:last-child td { border-bottom: none; }
    .um-table tbody tr:hover td { background: var(--slate-50); }

    .um-avatar {
        width: 34px; height: 34px;
        border-radius: var(--r-md);
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
    .um-avatar svg { width: 19px; height: 19px; }

    .um-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.22rem 0.65rem;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 600;
        border: 1px solid transparent;
    }
    .um-badge::before {
        content: '';
        width: 5px; height: 5px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .um-badge-pending  { background: #fffbeb; color: #b45309; border-color: #fcd34d; }
    .um-badge-pending::before  { background: #f59e0b; }
    .um-badge-approved { background: #ecfdf5; color: #047857; border-color: #6ee7b7; }
    .um-badge-approved::before { background: #10b981; }
    .um-badge-rejected { background: #fef2f2; color: #b91c1c; border-color: #fca5a5; }
    .um-badge-rejected::before { background: #ef4444; }

    .um-chip {
        display: inline-block;
        padding: 0.15rem 0.55rem;
        background: var(--slate-100);
        border: 1px solid var(--border-default);
        border-radius: var(--r-sm);
        font-size: 11px;
        color: var(--slate-700);
        white-space: nowrap;
    }

    .um-action-btn {
        width: 30px; height: 30px;
        display: inline-flex;
        align-items: center; justify-content: center;
        border-radius: var(--r-sm);
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.18s;
        text-decoration: none;
        background: none;
    }
    .um-action-btn svg { width: 14px; height: 14px; }

    .um-btn-view    { color: var(--um-blue-600);  background: var(--um-blue-50); }
    .um-btn-view:hover { background: #dbeafe; transform: translateY(-1px); }

    .um-btn-approve { color: #047857; background: #ecfdf5; }
    .um-btn-approve:hover { background: #d1fae5; transform: translateY(-1px); }

    .um-btn-reject  { color: #b91c1c; background: #fef2f2; }
    .um-btn-reject:hover  { background: #fee2e2; transform: translateY(-1px); }

    .um-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        padding: 3rem 1.5rem;
        color: var(--slate-400);
        font-size: 13px;
    }
    .um-empty svg { width: 32px; height: 32px; opacity: 0.2; }

    .um-pagination {
        padding: 0.875rem 1.25rem;
        border-top: 1px solid var(--border-default);
        background: var(--slate-50);
    }

    {{-- ── Grade / Section filter strip (Students only) ── --}}
    .um-grade-strip {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: wrap;
        margin-bottom: 0.75rem;
    }
    .um-grade-strip-label {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: var(--slate-400);
        margin-right: 0.25rem;
    }
    .um-grade-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 30px;
        padding: 0 0.7rem;
        border-radius: var(--r-md);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        font-weight: 700;
        color: var(--slate-600);
        background: var(--white);
        border: 1px solid var(--border-default);
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        transition: all 0.18s cubic-bezier(0.22,1,0.36,1);
    }
    .um-grade-btn:hover {
        background: var(--um-blue-50);
        border-color: #93c5fd;
        color: var(--um-blue-600);
    }
    .um-grade-btn.active {
        background: var(--um-blue-500);
        border-color: var(--um-blue-600);
        color: #fff;
        box-shadow: 0 2px 8px rgba(36,120,228,0.22);
    }

    .um-section-strip {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: wrap;
        margin-bottom: 1.1rem;
        padding: 0.6rem 0.9rem;
        background: var(--um-blue-50);
        border: 1px solid var(--um-blue-100);
        border-radius: var(--r-md);
        animation: um-fadein 0.22s cubic-bezier(0.22,1,0.36,1) both;
    }
    .um-section-strip-label {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: var(--um-blue-600);
        margin-right: 0.25rem;
        white-space: nowrap;
    }
    .um-section-btn {
        display: inline-flex;
        align-items: center;
        height: 26px;
        padding: 0 0.7rem;
        border-radius: var(--r-sm);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        font-weight: 600;
        color: var(--um-blue-600);
        background: var(--white);
        border: 1px solid var(--um-blue-200);
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        transition: all 0.16s cubic-bezier(0.22,1,0.36,1);
    }
    .um-section-btn:hover {
        background: var(--um-blue-100);
        border-color: var(--um-blue-500);
        color: var(--um-blue-600);
    }
    .um-section-btn.active {
        background: var(--um-blue-500);
        border-color: var(--um-blue-600);
        color: #fff;
    }

    .um-active-filter-bar {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.9rem;
        flex-wrap: wrap;
    }
    .um-filter-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        height: 24px;
        padding: 0 0.6rem;
        background: var(--um-blue-100);
        border: 1px solid var(--um-blue-200);
        border-radius: 999px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 11.5px;
        font-weight: 600;
        color: var(--um-blue-600);
    }
    .um-filter-pill a {
        color: var(--um-blue-600);
        opacity: 0.6;
        text-decoration: none;
        font-size: 13px;
        line-height: 1;
        margin-left: 0.1rem;
    }
    .um-filter-pill a:hover { opacity: 1; }


    .um-skel {
    background: linear-gradient(90deg, var(--slate-100) 25%, var(--slate-200) 50%, var(--slate-100) 75%);
    background-size: 200% 100%;
    animation: um-shimmer 1.4s infinite;
}
@keyframes um-shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* ═══ DESIGN POLISH (additive — overrides above, no markup/logic change) ═══ */

/* Header icon badge */
.um-header-icon {
    width: 46px; height: 46px;
    flex-shrink: 0;
    border-radius: var(--r-lg);
    display: grid; place-items: center;
    color: #fff;
    background: linear-gradient(135deg, var(--um-blue-500), var(--blue-700));
    box-shadow: 0 6px 18px -4px rgba(36,120,228,0.55);
}
.um-header-icon svg { width: 23px; height: 23px; }

/* Card a touch more elevated + smoother corners */
.um-card {
    border-radius: var(--r-xl);
    box-shadow: var(--shadow-md);
}

/* Sticky table header so column labels stay visible while scrolling */
.um-table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: var(--slate-50);
    backdrop-filter: saturate(1.2);
}

/* Slightly larger, softer avatars with a subtle ring */
.um-avatar {
    width: 38px; height: 38px;
    border-radius: var(--r-md);
    box-shadow: 0 2px 8px rgba(0,0,0,0.10), 0 0 0 2px rgba(255,255,255,0.65) inset;
    letter-spacing: 0.02em;
}

/* Row hover: gentle tint instead of flat grey */
.um-table tbody tr { transition: background 0.16s var(--ease-out); }
.um-table tbody tr:hover td { background: var(--um-blue-50); }

/* Tabs: smoother active lift */
.um-tab { transition: color .18s var(--ease-out), background .18s var(--ease-out), border-color .18s var(--ease-out); }
.um-tab.active { box-shadow: 0 -2px 0 0 var(--um-blue-500) inset; }

/* Action buttons: a little bigger tap target + spring */
.um-action-btn {
    width: 32px; height: 32px;
    transition: background .18s var(--ease-out), transform .16s var(--ease-spring), color .18s var(--ease-out);
}

/* Filter / search controls: unified focus glow already set — round them a bit more */
.um-search-input, .um-select, .um-btn-filter { border-radius: var(--r-md); }

/* Empty state: give the icon a soft circular backdrop */
.um-empty { gap: 0.75rem; }
.um-empty svg {
    width: 30px; height: 30px;
    opacity: 1;
    color: var(--slate-400);
    background: var(--slate-100);
    box-sizing: content-box;
    padding: 13px;
    border-radius: var(--r-full);
}

/* Mobile niceties */
@media (max-width: 640px) {
    .um-header-icon { width: 40px; height: 40px; }
    .um-header-title { font-size: 1.2rem; }
    .um-search-input, .um-search-input:focus { width: 100%; }
    .um-search { flex: 1 1 100%; }
    .um-filterbar { gap: 0.5rem; }
}
</style>

{{-- Idagdag ito bago ang drawer HTML sa index.blade.php --}}
<style>
    .up-page { max-width: 860px; margin: 0 auto; }
    .up-back { display:none; } /* hide sa drawer */

    .up-hero {
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-xl);
        box-shadow: var(--shadow-md);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .up-hero-strip {
        height: 5px;
        background: linear-gradient(90deg, var(--blue-500), var(--blue-300));
    }
    .up-hero-body {
        padding: 1.75rem 2rem;
        display: flex; align-items: flex-start;
        justify-content: space-between; gap: 1.5rem;
        flex-wrap: wrap;
    }
    .up-hero-left { display: flex; align-items: center; gap: 1.25rem; }
    .up-avatar {
        width: 64px; height: 64px;
        border-radius: var(--r-lg);
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; font-weight: 700; color: #fff;
        flex-shrink: 0;
        box-shadow: 0 4px 16px rgba(36,120,228,0.3);
    }
    .up-avatar.student { background: linear-gradient(135deg, #0d9488, #0891b2); }
    .up-avatar.teacher { background: linear-gradient(135deg, var(--blue-500, #2478e4), #1e40af); }
    .up-avatar.parent  { background: linear-gradient(135deg, #d97706, #b45309); }
    .up-hero-name {
        font-size: 1.3rem; font-weight: 700;
        color: var(--slate-900); letter-spacing: -0.02em;
        margin: 0 0 0.2rem;
    }
    .up-hero-email { font-size: 13px; color: var(--slate-500); margin: 0 0 0.5rem; }
    .up-hero-meta { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
    .up-pill {
        display: inline-flex; align-items: center; gap: 0.3rem;
        padding: 0.2rem 0.7rem;
        border-radius: var(--r-full);
        font-size: 11.5px; font-weight: 700;
        border: 1px solid transparent; white-space: nowrap;
    }
    .up-pill::before { content: ''; width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; }
    .up-pill-role-student { background: #e0f2f1; color: #0f766e; border-color: #5eead4; }
    .up-pill-role-student::before { background: #0d9488; }
    .up-pill-role-teacher { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .up-pill-role-teacher::before { background: #2478e4; }
    .up-pill-role-parent  { background: #fffbeb; color: #b45309; border-color: #fcd34d; }
    .up-pill-role-parent::before  { background: #d97706; }
    .up-pill-approved { background: #ecfdf5; color: #047857; border-color: #6ee7b7; }
    .up-pill-approved::before { background: #10b981; }
    .up-pill-pending  { background: #fffbeb; color: #b45309; border-color: #fcd34d; }
    .up-pill-pending::before  { background: #f59e0b; }
    .up-pill-rejected { background: #fef2f2; color: #b91c1c; border-color: #fca5a5; }
    .up-pill-rejected::before { background: #ef4444; }
    .up-date-chip { font-size: 11.5px; color: var(--slate-400); display: flex; align-items: center; gap: 0.3rem; }
    .up-date-chip svg { width: 12px; height: 12px; }
    .up-hero-info {
        padding: 1.25rem 2rem 1.75rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
        border-top: 1px solid var(--border-default);
    }
    .up-info-label {
        font-size: 10.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.08em;
        color: var(--slate-400); margin-bottom: 3px;
    }
    .up-info-value { font-size: 13.5px; font-weight: 600; color: var(--slate-800); }
    .up-info-value.muted { color: var(--slate-400); font-weight: 400; font-style: italic; }
    .up-actions {
        padding: 1.25rem 2rem 1.75rem;
        border-top: 1px solid var(--border-default);
        display: flex; gap: 0.75rem; flex-wrap: wrap;
    }
    .up-btn {
        display: inline-flex; align-items: center; gap: 0.5rem;
        padding: 0.6rem 1.25rem;
        border-radius: var(--r-md);
        font-size: 13px; font-weight: 600;
        border: none; cursor: pointer;
        transition: all 0.18s;
    }
    .up-btn svg { width: 15px; height: 15px; }
    .up-btn-approve { background: #059669; color: #fff; box-shadow: 0 2px 10px rgba(5,150,105,0.25); }
    .up-btn-approve:hover { background: #047857; transform: translateY(-1px); }
    .up-btn-reject  { background: var(--white); color: #b91c1c; border: 1px solid #fca5a5; }
    .up-btn-reject:hover  { background: #fef2f2; transform: translateY(-1px); }
    .up-panel {
        background: var(--white);
        border: 1px solid var(--border-default);
        border-radius: var(--r-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .up-panel-header {
        display: flex; align-items: center; gap: 0.6rem;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border-default);
        background: var(--slate-50);
    }
    .up-panel-icon {
        width: 30px; height: 30px;
        border-radius: var(--r-sm);
        background: #eff6ff;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .up-panel-icon svg { width: 15px; height: 15px; color: #2478e4; }
    .up-panel-title { font-size: 13.5px; font-weight: 700; color: var(--slate-800); }
    .up-panel-body { padding: 1.25rem 1.5rem; }
    .up-subject-row {
        display: flex; align-items: center;
        justify-content: space-between; gap: 1rem;
        padding: 0.75rem 1rem;
        border-radius: var(--r-md);
        background: var(--slate-50);
        border: 1px solid var(--border-default);
        margin-bottom: 0.5rem;
        transition: background 0.15s;
    }
    .up-subject-row:hover { background: #eff6ff; }
    .up-subject-name { font-size: 13.5px; font-weight: 600; color: var(--slate-800); }
    .up-subject-meta { font-size: 12px; color: var(--slate-500); margin-top: 1px; }
    .up-sy-chip {
        font-size: 11px; font-weight: 600;
        color: #1d4ed8; background: #eff6ff;
        border: 1px solid #bfdbfe;
        padding: 2px 10px; border-radius: 999px; white-space: nowrap;
    }
    .up-student-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.6rem; }
    .up-student-card {
        display: flex; align-items: center; gap: 0.65rem;
        padding: 0.65rem 0.875rem;
        background: var(--slate-50);
        border: 1px solid var(--border-default);
        border-radius: var(--r-md);
        transition: background 0.15s, border-color 0.15s;
    }
    .up-student-card:hover { background: #eff6ff; border-color: #bfdbfe; }
    .up-student-avatar {
        width: 32px; height: 32px;
        border-radius: var(--r-sm);
        background: linear-gradient(135deg, #60a5fa, #2563eb);
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700; color: #fff; flex-shrink: 0;
    }
    .up-student-name { font-size: 12.5px; font-weight: 600; color: var(--slate-800); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .up-student-section { font-size: 11px; color: var(--slate-400); }

    /* ── Dark mode fixes for User Profile drawer (literal light colors) ── */
    html[data-theme="dark"] .up-subject-name,
    html[data-theme="dark"] .up-student-name,
    html[data-theme="dark"] .up-info-value,
    html[data-theme="dark"] .up-panel-title { color: var(--slate-900); }
    html[data-theme="dark"] .up-subject-row:hover  { background: var(--slate-100); }
    html[data-theme="dark"] .up-student-card:hover { background: var(--slate-100); border-color: var(--slate-200); }
    html[data-theme="dark"] .up-sy-chip    { background: rgba(36,120,228,0.18); color: var(--blue-300); border-color: rgba(36,120,228,0.35); }
    html[data-theme="dark"] .up-panel-icon { background: rgba(36,120,228,0.18); }
    html[data-theme="dark"] .up-panel-icon svg { color: var(--blue-300); }

    /* ── Dark mode: table row hover + action buttons (were light literals) ── */
    html[data-theme="dark"] .um-table tbody tr:hover td { background: var(--slate-100); }
    html[data-theme="dark"] .um-btn-view:hover    { background: rgba(36,120,228,0.22); }
    html[data-theme="dark"] .um-btn-approve:hover { background: rgba(5,150,105,0.22); }
    html[data-theme="dark"] .um-btn-reject:hover  { background: rgba(220,38,38,0.22); }
    html[data-theme="dark"] .um-confirm-cancel:hover { background: var(--slate-200); }
    .up-section-divider { border: none; border-top: 1px solid var(--border-default); margin: 1.25rem 0; }
    .up-section-sub {
        font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.08em;
        color: var(--slate-400); margin-bottom: 0.75rem;
        display: flex; align-items: center; gap: 0.4rem;
    } 
    .up-section-sub span {
        background: var(--slate-100); border: 1px solid var(--border-default);
        padding: 1px 8px; border-radius: 999px; font-size: 10px;
    }
    .up-log-row {
        display: flex; align-items: flex-start;
        gap: 0.875rem; padding: 0.875rem 0;
        border-bottom: 1px solid var(--slate-100);
    }
    .up-log-row:last-child { border-bottom: none; }
    .up-log-dot { width: 8px; height: 8px; border-radius: 50%; background: #60a5fa; flex-shrink: 0; margin-top: 5px; }
    .up-log-body { flex: 1; min-width: 0; }
    .up-log-action { font-size: 13.5px; font-weight: 500; color: var(--slate-800); line-height: 1.4; }
    .up-log-meta { font-size: 11.5px; color: var(--slate-400); margin-top: 2px; display: flex; align-items: center; gap: 0.4rem; }
    .up-log-module {
        display: inline-block;
        background: #eff6ff; color: #1d4ed8;
        font-size: 10px; font-weight: 700;
        letter-spacing: 0.04em; text-transform: uppercase;
        padding: 1px 7px; border-radius: var(--r-sm);
    }
    .up-log-time { font-size: 11.5px; color: var(--slate-400); white-space: nowrap; flex-shrink: 0; padding-top: 2px; }
    .up-empty { text-align: center; padding: 2.5rem 1rem; color: var(--slate-400); font-size: 13px; }
    .up-empty svg { width: 36px; height: 36px; opacity: 0.2; margin: 0 auto 0.5rem; display: block; }


    /* ── Confirm Action Modal ── */
.um-confirm-backdrop {
    position: fixed; inset: 0;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 1000;
    display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none;
    transition: opacity 0.22s ease;
}
.um-confirm-backdrop.open {
    opacity: 1; pointer-events: auto;
}
.um-confirm-box {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 24px 56px -8px rgba(0,0,0,0.18);
    width: 360px; max-width: calc(100vw - 2rem);
    padding: 1.75rem;
    transform: scale(0.94) translateY(12px);
    transition: transform 0.28s cubic-bezier(0.34,1.56,0.64,1);
}
.um-confirm-backdrop.open .um-confirm-box {
    transform: scale(1) translateY(0);
}
.um-confirm-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 1.1rem;
}
.um-confirm-icon.approve { background: #ecfdf5; }
.um-confirm-icon.approve svg { color: #059669; }
.um-confirm-icon.reject  { background: #fef2f2; }
.um-confirm-icon.reject  svg { color: #dc2626; }
.um-confirm-icon svg { width: 24px; height: 24px; }
.um-confirm-title {
    font-size: 15px; font-weight: 700;
    color: #0f172a; margin-bottom: 0.4rem;
}
.um-confirm-desc {
    font-size: 13px; color: #64748b;
    line-height: 1.6; margin-bottom: 1.5rem;
}
.um-confirm-actions {
    display: flex; gap: 0.65rem;
}
.um-confirm-cancel {
    flex: 1; padding: 0.65rem;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13px; font-weight: 600;
    color: #334155; cursor: pointer;
    transition: background 0.15s;
}
.um-confirm-cancel:hover { background: #e2e8f0; }
.um-confirm-proceed {
    flex: 1; padding: 0.65rem;
    border: none; border-radius: 10px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13px; font-weight: 700;
    color: #fff; cursor: pointer;
    transition: all 0.18s;
    display: flex; align-items: center; justify-content: center; gap: 0.4rem;
}
.um-confirm-proceed.approve { background: #059669; }
.um-confirm-proceed.approve:hover { background: #047857; transform: translateY(-1px); }
.um-confirm-proceed.reject  { background: #dc2626; }
.um-confirm-proceed.reject:hover  { background: #b91c1c; transform: translateY(-1px); }
</style>

<div class="um-page">

    {{-- ── Page header ─────────────────────────────────── --}}
<div class="um-header">
    <div class="um-header-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
    </div>
    <div class="um-header-meta">
        <h1 class="um-header-title">User Management</h1>
        <p class="um-header-sub">Manage students, teachers, and parents efficiently.</p>
    </div>
</div>{{-- /um-header --}}

    {{-- ── Tab navigation ─────────────────────────────── --}}
    <div class="um-tabs" role="tablist">
        <a href="{{ route('admin.users.index', array_merge(request()->query(), ['tab'=>'parents'])) }}"
           class="um-tab {{ $tab === 'parents' ? 'active' : '' }}" role="tab">
            Parents
            <span class="um-tab-badge">{{ $parents->total() }}</span>
        </a>
    </div>

    {{-- ── Filter bar (buttons + search + filter only — NO tables inside) ── --}}
    <form method="GET" class="um-filterbar">
        <input type="hidden" name="tab" value="{{ $tab }}">
        {{-- Preserve grade/section filters when searching/filtering --}}
        @if(request('grade'))
            <input type="hidden" name="grade" value="{{ request('grade') }}">
        @endif
        @if(request('section_id'))
            <input type="hidden" name="section_id" value="{{ request('section_id') }}">
        @endif


        <div class="um-search">
            <svg class="um-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search users…"
                   class="um-search-input"
                   aria-label="Search users">
        </div>

        <select name="status" class="um-select" aria-label="Filter by status">
            <option value="">All Status</option>
            <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>

        <button type="submit" class="um-btn-filter">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M7 10h10M11 16h2"/>
            </svg>
            Filter
        </button>
        <a href="{{ route('admin.users.index', ['tab' => $tab]) }}" class="um-btn-reset">Reset</a>
    </form>
    {{-- ═══ END FORM — all tables are OUTSIDE the form below ═══ --}}


<div id="um-ajax-content" style="transition:opacity .22s ease,transform .22s ease">

    {{-- ════════════════════════════════
         STUDENTS TAB
    ════════════════════════════════ --}}
    @if($tab === 'students')

    {{-- ── Grade buttons ─────────────────────────────── --}}
    @php
        $activeGrade     = request('grade');
        $activeSectionId = request('section_id');
        $gradeList       = [7, 8, 9, 10, 11, 12];
    @endphp

    <div class="um-grade-strip" role="group" aria-label="Filter by grade level">
        <span class="um-grade-strip-label">Grade</span>

        {{-- "All" button --}}
        <a href="{{ route('admin.users.index', array_merge(request()->except(['grade','section_id']), ['tab'=>'students'])) }}"
           class="um-grade-btn {{ !$activeGrade ? 'active' : '' }}">
            All
        </a>

        @foreach($gradeList as $g)
        <a href="{{ route('admin.users.index', array_merge(request()->except(['section_id']), ['tab'=>'students','grade'=>$g])) }}"
           class="um-grade-btn {{ (string)$activeGrade === (string)$g ? 'active' : '' }}">
            Gr. {{ $g }}
        </a>
        @endforeach
    </div>

    {{-- ── Section buttons (only shown when a grade is selected) ─── --}}
    @if($activeGrade && isset($sectionsByGrade[$activeGrade]) && $sectionsByGrade[$activeGrade]->count())
    <div class="um-section-strip" role="group" aria-label="Filter by section">
        <span class="um-section-strip-label">Section</span>

        {{-- "All sections" for this grade --}}
        <a href="{{ route('admin.users.index', array_merge(request()->except(['section_id']), ['tab'=>'students','grade'=>$activeGrade])) }}"
           class="um-section-btn {{ !$activeSectionId ? 'active' : '' }}">
            All Sections
        </a>

        @foreach($sectionsByGrade[$activeGrade] as $sec)
        <a href="{{ route('admin.users.index', array_merge(request()->all(), ['tab'=>'students','grade'=>$activeGrade,'section_id'=>$sec->id])) }}"
           class="um-section-btn {{ (string)$activeSectionId === (string)$sec->id ? 'active' : '' }}">
            {{ $sec->name }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- ── Active filter pills ────────────────────────── --}}
    @if($activeGrade || $activeSectionId)
    <div class="um-active-filter-bar">
        @if($activeGrade)
        <span class="um-filter-pill">
            Grade {{ $activeGrade }}
            <a href="{{ route('admin.users.index', array_merge(request()->except(['grade','section_id']), ['tab'=>'students'])) }}"
               title="Remove grade filter">×</a>
        </span>
        @endif
        @if($activeSectionId && isset($sectionsByGrade[$activeGrade]))
            @php $activeSection = $sectionsByGrade[$activeGrade]->firstWhere('id', $activeSectionId); @endphp
            @if($activeSection)
            <span class="um-filter-pill">
                Section: {{ $activeSection->name }}
                <a href="{{ route('admin.users.index', array_merge(request()->except(['section_id']), ['tab'=>'students','grade'=>$activeGrade])) }}"
                   title="Remove section filter">×</a>
            </span>
            @endif
        @endif
    </div>
    @endif

    <div class="um-card">
        <div style="overflow-x:auto;">
            <table class="um-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Type</th>
                        <th>Section</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th style="text-align:right; padding-right:1.5rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                <div class="um-avatar" style="background:#0d9488;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </div>
                                <div>
                                    <p style="font-weight:600; font-size:13px; color:var(--slate-900); margin:0;">{{ $student->name }}</p>
                                    <p style="font-size:11.5px; color:var(--slate-500); margin:0;">{{ $student->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($student->studentProfile)
                                <span class="um-chip">{{ ucfirst($student->studentProfile->student_type) }}</span>
                            @else
                                <span style="color:var(--slate-400); font-size:12px;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($student->section)
                                <div>
                                    <p style="font-size:13px; font-weight:500; color:var(--um-blue-600); margin:0;">{{ $student->section->name }}</p>
                                    <p style="font-size:10px; color:var(--slate-500); margin:0; text-transform:uppercase; letter-spacing:.04em;">Gr. {{ $student->grade_level }}</p>
                                </div>
                            @else
                                <span style="color:var(--slate-400); font-size:12px; font-style:italic;">Not enrolled</span>
                            @endif
                        </td>
                        <td>
                            <span class="um-badge um-badge-{{ $student->status }}">
                                {{ ucfirst($student->status) }}
                            </span>
                        </td>
                        <td style="font-size:12px; color:var(--slate-500); font-family:monospace;">
                            {{ $student->created_at->format('M d, Y') }}
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.4rem; padding-right:0.5rem;">
                                <a href="{{ route('admin.users.show', $student) }}" class="um-action-btn um-btn-view" title="View">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
@if($student->status === 'pending')
    <button type="button"
        class="um-action-btn um-btn-approve"
        title="Approve"
        data-action="approve"
        data-url="{{ route('admin.users.approve', $student) }}"
        data-name="{{ $student->name }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
    </button>
    <button type="button"
        class="um-action-btn um-btn-reject"
        title="Reject"
        data-action="reject"
        data-url="{{ route('admin.users.reject', $student) }}"
        data-name="{{ $student->name }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
@endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="um-empty">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"/>
                                </svg>
                                No students found.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="um-pagination">{{ $students->withQueryString()->links() }}</div>
    </div>
    @endif

    {{-- ════════════════════════════════
         TEACHERS TAB
    ════════════════════════════════ --}}
    @if($tab === 'teachers')
    <div style="margin-bottom:1rem;">
        <a href="{{ route('admin.users.create-teacher') }}" class="um-btn-add">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Add Teacher
        </a>
    </div>
    <div class="um-card">
        <div style="overflow-x:auto;">
            <table class="um-table">
                <thead>
                    <tr>
                        <th>Teacher</th>
                        <th>Employee ID</th>
                        <th>Assigned Sections</th>
                        <th>Subject Load</th>
                        <th>Status</th>
                        <th style="text-align:right; padding-right:1.5rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $teacher)
                    @php
                        $assignedSections = $teacher->teacherSubjects->unique('section_id');
                        $subjectCount = $teacher->teacherSubjects->pluck('subject_id')->unique()->count();
                    @endphp
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                <div class="um-avatar" style="background:#2563eb;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </div>
                                <div>
                                    <p style="font-weight:600; font-size:13px; color:var(--slate-900); margin:0;">{{ $teacher->name }}</p>
                                    <p style="font-size:11.5px; color:var(--slate-500); margin:0;">{{ $teacher->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:12px; font-family:monospace; color:var(--slate-700);">
                            {{ $teacher->employee_id ?? '—' }}
                        </td>
                        <td>
                            @if($assignedSections->count())
                                <div style="display:flex; flex-wrap:wrap; gap:0.3rem;">
                                    @foreach($assignedSections as $ts)
                                        @if($ts->section)
                                            <span class="um-chip">{{ $ts->section->name }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <span style="color:var(--slate-400); font-size:12px;">No sections</span>
                            @endif
                        </td>
                        <td>
                            <p style="font-size:14px; font-weight:700; color:var(--um-purple-500); margin:0;">{{ $subjectCount }}</p>
                            <p style="font-size:10px; text-transform:uppercase; letter-spacing:.05em; color:var(--slate-500); margin:0;">Subjects</p>
                        </td>
                        <td>
                            <span class="um-badge um-badge-{{ $teacher->status }}">
                                {{ ucfirst($teacher->status) }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.4rem; padding-right:0.5rem;">
                                <a href="{{ route('admin.users.show', $teacher) }}" class="um-action-btn um-btn-view" title="View Profile">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="um-empty">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                No teachers found.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="um-pagination">{{ $teachers->withQueryString()->links() }}</div>
    </div>
    @endif
    {{-- ════════════════════════════════
         PARENTS TAB
    ════════════════════════════════ --}}
    @if($tab === 'parents')
    <div class="um-card">
        <div style="overflow-x:auto;">
            <table class="um-table">
                <thead>
                    <tr>
                        <th>Parent</th>
                        <th>Contact</th>
                        <th>Children</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th style="text-align:right; padding-right:1.5rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parents as $parent)
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                <div class="um-avatar" style="background:#d97706;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </div>
                                <div>
                                    <p style="font-weight:600; font-size:13px; color:var(--slate-900); margin:0;">{{ $parent->name }}</p>
                                    <p style="font-size:11.5px; color:var(--slate-500); margin:0;">{{ $parent->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:12px; font-family:monospace; color:var(--slate-700);">
                            {{ $parent->contact_number ?? '—' }}
                        </td>
                        <td>
                            @if($parent->children->count())
                                <div style="display:flex; flex-wrap:wrap; gap:0.3rem;">
                                    @foreach($parent->children as $child)
                                        <span class="um-chip">{{ $child->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span style="color:var(--slate-400); font-size:12px; font-style:italic;">No children linked</span>
                            @endif
                        </td>
                        <td>
                            <span class="um-badge um-badge-{{ $parent->status }}">
                                {{ ucfirst($parent->status) }}
                            </span>
                        </td>
                        <td style="font-size:12px; color:var(--slate-500); font-family:monospace;">
                            {{ $parent->created_at->format('M d, Y') }}
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.4rem; padding-right:0.5rem;">
                                <a href="{{ route('admin.users.show', $parent) }}" class="um-action-btn um-btn-view" title="View Profile">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                             @if($parent->status === 'pending')
    <button type="button"
        class="um-action-btn um-btn-approve"
        title="Approve"
        data-action="approve"
        data-url="{{ route('admin.users.approve', $parent) }}"
        data-name="{{ $parent->name }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
    </button>
    <button type="button"
        class="um-action-btn um-btn-reject"
        title="Reject"
        data-action="reject"
        data-url="{{ route('admin.users.reject', $parent) }}"
        data-name="{{ $parent->name }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
@endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="um-empty">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                No parents found.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="um-pagination">{{ $parents->withQueryString()->links() }}</div>
    </div>
    @endif
    </div>{{-- /um-ajax-content --}}

</div>{{-- /um-page --}}

{{-- ── Approve/Reject Confirmation Modal ── --}}
<div class="um-confirm-backdrop" id="um-confirm-backdrop">
    <div class="um-confirm-box">
        <div class="um-confirm-icon" id="um-confirm-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"></svg>
        </div>
        <div class="um-confirm-title" id="um-confirm-title"></div>
        <p class="um-confirm-desc" id="um-confirm-desc"></p>
        <div class="um-confirm-actions">
            <button class="um-confirm-cancel" id="um-confirm-cancel">Cancel</button>
            <button class="um-confirm-proceed" id="um-confirm-proceed"></button>
        </div>
    </div>
</div>

{{-- Hidden forms para sa actual POST -- triggered ng modal --}}
<form method="POST" id="um-approve-form" style="display:none;">
    @csrf @method('PATCH')
</form>
<form method="POST" id="um-reject-form" style="display:none;">
    @csrf @method('PATCH')
</form>


<script>
(function () {
    'use strict';

    const AJAX_URL = '{{ route("admin.users.index") }}';
    const area = document.getElementById('um-ajax-content');
    let isFetching = false;

    let state = {
        tab:        '{{ $tab }}',
        grade:      '{{ request("grade") }}',
        section_id: '{{ request("section_id") }}',
        search:     '{{ request("search") }}',
        status:     '{{ request("status") }}',
    };

    async function navigate(patch) {
        // Huwag mag-double fetch
        if (isFetching) return;

        Object.assign(state, patch);

        const p = new URLSearchParams();
        p.set('tab', state.tab);
        if (state.grade)      p.set('grade',      state.grade);
        if (state.section_id) p.set('section_id', state.section_id);
        if (state.search)     p.set('search',     state.search);
        if (state.status)     p.set('status',     state.status);

        history.pushState({ ...state }, '', '?' + p);
        showSkeleton();
        isFetching = true;

        try {
            const res = await fetch(AJAX_URL + '?' + p, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept':           'application/json',
                }
            });

            if (!res.ok) {
                // Kung 401/403/500 — i-reload na lang para makita ang actual error
                console.error('AJAX error:', res.status);
                window.location.href = '?' + p;
                return;
            }

            const data = await res.json();

            // I-check na may laman ang data
            if (!data.html) {
                console.error('Empty HTML from server');
                window.location.href = '?' + p;
                return;
            }

            area.style.opacity   = '0';
            area.style.transform = 'translateY(6px)';
            area.innerHTML       = data.html;
            updateBadges(data.counts);
            bindContent();

            requestAnimationFrame(() => {
                area.style.opacity   = '1';
                area.style.transform = 'translateY(0)';
            });

        } catch (err) {
            // Network error o JSON parse error — fallback sa full reload
            console.error('Navigate error:', err);
            window.location.href = '?' + p;
        } finally {
            isFetching = false;
        }
    }

    function showSkeleton() {
        area.innerHTML = `<div class="um-card">` +
            [...Array(6)].map(() => `
            <div style="display:flex;gap:.75rem;padding:.9rem 1.25rem;border-bottom:1px solid var(--slate-100)">
                <div class="um-skel" style="width:34px;height:34px;border-radius:var(--r-md);flex-shrink:0"></div>
                <div style="flex:1">
                    <div class="um-skel" style="height:12px;width:140px;border-radius:4px;margin-bottom:6px"></div>
                    <div class="um-skel" style="height:10px;width:180px;border-radius:4px"></div>
                </div>
                <div class="um-skel" style="height:22px;width:60px;border-radius:999px"></div>
            </div>`).join('') + `</div>`;
    }

    function updateBadges(counts) {
        if (!counts) return;
        document.querySelectorAll('.um-tab').forEach(t => {
            const name  = new URL(t.href).searchParams.get('tab');
            const badge = t.querySelector('.um-tab-badge');
            if (badge && counts[name] !== undefined)
                badge.textContent = counts[name];
            t.classList.toggle('active', name === state.tab);
        });
    }

    function bindContent() {
        // Grade buttons
        area.querySelectorAll('.um-grade-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                const u = new URL(btn.href);
                navigate({
                    grade:      u.searchParams.get('grade') || '',
                    section_id: '',
                });
            });
        });

        // Section buttons
        area.querySelectorAll('.um-section-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                const u = new URL(btn.href);
                navigate({ section_id: u.searchParams.get('section_id') || '' });
            });
        });

        // Filter pills ×
        area.querySelectorAll('.um-filter-pill a').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                const u = new URL(a.href);
                navigate({
                    grade:      u.searchParams.get('grade')      || '',
                    section_id: u.searchParams.get('section_id') || '',
                });
            });
        });

        // Pagination
        area.querySelectorAll('.pagination a[href]').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                const u    = new URL(a.href);
                const page = u.searchParams.get('page');
                // I-pass lahat ng existing state + bagong page
                navigate({ page: page || 1 });
            });
        });
    }

    // Tab clicks
    document.querySelectorAll('.um-tab').forEach(t => {
        t.addEventListener('click', e => {
            e.preventDefault();
            const u = new URL(t.href);
            navigate({ tab: u.searchParams.get('tab'), grade: '', section_id: '' });
        });
    });

    // Filter form submit
    document.querySelector('.um-filterbar').addEventListener('submit', e => {
        e.preventDefault();
        const fd = new FormData(e.target);
        navigate({
            search: fd.get('search') || '',
            status: fd.get('status') || '',
        });
    });

    // Reset
    document.querySelector('.um-btn-reset')?.addEventListener('click', e => {
        e.preventDefault();
        navigate({ search: '', status: '', grade: '', section_id: '' });
    });

    // Browser back/forward
    window.addEventListener('popstate', e => {
        if (e.state) {
            Object.assign(state, e.state);
            navigate({});
        }
    });

    bindContent();
})();
</script>

{{-- ── Slide-over Drawer ── --}}
<div id="up-overlay" style="
    position:fixed; inset:0; background:rgba(0,0,0,0);
    z-index:998; pointer-events:none;
    transition:background 0.3s ease;
"></div>

<div id="up-drawer" style="
    position:fixed; top:0; right:0; height:100vh;
    width:min(680px, 100vw);
    background:var(--white);
    box-shadow:-8px 0 40px rgba(0,0,0,0.12);
    z-index:999;
    transform:translateX(100%);
    transition:transform 0.35s cubic-bezier(0.22,1,0.36,1);
    display:flex; flex-direction:column;
    overflow:hidden;
">
    {{-- Drawer header --}}
    <div style="
        display:flex; align-items:center; justify-content:space-between;
        padding:1rem 1.5rem;
        border-bottom:1px solid var(--border-default);
        background:var(--slate-50);
        flex-shrink:0;
    ">
        <span style="font-size:13.5px; font-weight:700; color:var(--slate-700);">User Profile</span>
        <button id="up-drawer-close" style="
            width:30px; height:30px;
            display:flex; align-items:center; justify-content:center;
            border-radius:var(--r-sm);
            border:1px solid var(--border-default);
            background:var(--white);
            cursor:pointer;
            color:var(--slate-500);
            transition:all 0.15s;
        ">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Drawer body (scrollable) --}}
    <div id="up-drawer-body" style="flex:1; overflow-y:auto; padding:1.5rem;">
        {{-- skeleton / content loads here --}}
    </div>

    {{-- Drawer footer --}}
    <div id="up-drawer-footer" style="
        padding:1rem 1.5rem;
        border-top:1px solid var(--border-default);
        background:var(--slate-50);
        flex-shrink:0;
        display:none;
    ">
        <a id="up-drawer-fullpage" href="#" style="
            display:inline-flex; align-items:center; gap:0.4rem;
            font-size:12.5px; font-weight:600;
            color:var(--blue-600); text-decoration:none;
        ">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            Open full page
        </a>
    </div>
</div>


<script>
// PALITAN NG:
(function() {
    const drawer   = document.getElementById('up-drawer');
    const overlay  = document.getElementById('up-overlay');
    const body     = document.getElementById('up-drawer-body');
    const footer   = document.getElementById('up-drawer-footer');
    const fullLink = document.getElementById('up-drawer-fullpage');
    const closeBtn = document.getElementById('up-drawer-close');

    let currentController = null;  // ← BAGO

    function openDrawer(url) {
        if (currentController) currentController.abort();
        currentController = new AbortController();  // ← BAGO

        overlay.style.pointerEvents = 'auto';
        overlay.style.background    = 'rgba(0,0,0,0.35)';
        drawer.style.transform      = 'translateX(0)';
        body.innerHTML              = skeletonHTML();
        footer.style.display        = 'none';
        fullLink.href               = url;

        fetch(url, {
            signal: currentController.signal,  // ← BAGO
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'text/html',
            }
        })
        .then(res => {
            if (!res.ok) throw new Error(res.status);
            return res.text();
        })
        .then(html => {
            const parser = new DOMParser();
            const doc    = parser.parseFromString(html, 'text/html');
            const page   = doc.querySelector('.up-page');
            if (page) {
                page.querySelector('.up-back')?.remove();
                page.style.maxWidth = '100%';
                page.style.margin   = '0';
                body.innerHTML = '';
                body.appendChild(page);
            } else {
                body.innerHTML = '<p style="color:var(--slate-400);font-size:13px;padding:1rem;">Could not load profile.</p>';
            }
            footer.style.display = 'block';
        })
        .catch(err => {
            if (err.name === 'AbortError') return;  // ← BAGO: huwag mag-error pag intentional abort
            body.innerHTML = `<p style="color:var(--slate-400);font-size:13px;padding:1rem;">Failed to load. <a href="${fullLink.href}">Open full page instead.</a></p>`;
        })
        .finally(() => {
            currentController = null;  // ← BAGO
        });
    }

    function closeDrawer() {
        if (currentController) {  // ← BAGO: i-abort din pag nagsara
            currentController.abort();
            currentController = null;
        }
        drawer.style.transform      = 'translateX(100%)';
        overlay.style.background    = 'rgba(0,0,0,0)';
        overlay.style.pointerEvents = 'none';
    }

function skeletonHTML() {
    return `
    <div style="
        display:flex; align-items:center; justify-content:center;
        height:200px; color:var(--slate-400);
        flex-direction:column; gap:0.75rem;
    ">
        <svg style="width:20px;height:20px;animation:spin 0.7s linear infinite;" 
             fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" 
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        <span style="font-size:13px;">Loading profile…</span>
    </div>`;
}

    document.addEventListener('click', e => {
        const btn = e.target.closest('.um-btn-view');
        if (!btn) return;
        e.preventDefault();
        openDrawer(btn.href);
    });

    closeBtn.addEventListener('click', closeDrawer);
    overlay.addEventListener('click',  closeDrawer);
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeDrawer();
    });
})();

// ── Approve / Reject Confirmation Modal ──────────────────
(function() {
    const backdrop   = document.getElementById('um-confirm-backdrop');
    const iconEl     = document.getElementById('um-confirm-icon');
    const titleEl    = document.getElementById('um-confirm-title');
    const descEl     = document.getElementById('um-confirm-desc');
    const cancelBtn  = document.getElementById('um-confirm-cancel');
    const proceedBtn = document.getElementById('um-confirm-proceed');
    const approveForm = document.getElementById('um-approve-form');
    const rejectForm  = document.getElementById('um-reject-form');

    let pendingUrl    = null;
    let pendingAction = null;

    const config = {
        approve: {
            iconClass: 'approve',
            iconPath:  'M5 13l4 4L19 7',
            title: name => `Approve ${name}?`,
            desc:  name => `This will grant ${name} full access to DP-LMS. You can reject them later if needed.`,
            btnText:  'Yes, Approve',
            btnClass: 'approve',
        },
        reject: {
            iconClass: 'reject',
            iconPath:  'M6 18L18 6M6 6l12 12',
            title: name => `Reject ${name}?`,
            desc:  name => `${name}'s account will be rejected and they won't be able to log in.`,
            btnText:  'Yes, Reject',
            btnClass: 'reject',
        }
    };

    function openConfirm(action, url, name) {
        const c = config[action];
        pendingUrl    = url;
        pendingAction = action;

        iconEl.className = `um-confirm-icon ${c.iconClass}`;
        iconEl.querySelector('svg').innerHTML =
            `<path stroke-linecap="round" stroke-linejoin="round" d="${c.iconPath}"/>`;
        titleEl.textContent    = c.title(name);
        descEl.textContent     = c.desc(name);
        proceedBtn.textContent = c.btnText;
        proceedBtn.className   = `um-confirm-proceed ${c.btnClass}`;

        backdrop.classList.add('open');
        cancelBtn.focus();
    }

    function closeConfirm() {
        backdrop.classList.remove('open');
        pendingUrl = pendingAction = null;
    }

    proceedBtn.addEventListener('click', function () {
        if (!pendingUrl || !pendingAction) return;
        const form = pendingAction === 'approve' ? approveForm : rejectForm;
        form.action   = pendingUrl;
        this.disabled = true;
        this.textContent = 'Processing…';
        form.submit();
    });

    cancelBtn.addEventListener('click', closeConfirm);
    backdrop.addEventListener('click', e => {
        if (e.target === backdrop) closeConfirm();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeConfirm();
    });

    // Delegated — gumagana kahit after AJAX reload ng table
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-action="approve"],[data-action="reject"]');
        if (!btn) return;
        openConfirm(btn.dataset.action, btn.dataset.url, btn.dataset.name);
    });
})();
</script>

@endsection