@extends('layouts.faculty')
@section('title', 'Manage Teachers')


@section('content')
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
:root {
    --vi:        #4f32c5;
    --vi-2:      #6548d4;
    --vi-4:      #bfaefc;
    --vi-5:      #ede8ff;
    --vi-6:      #f7f5ff;
    --ink:       #0c0a14;
    --ink-2:     #2a2638;
    --ink-3:     #5c5672;
    --ink-4:     #9893aa;
    --ink-5:     #cbc7d9;
    --ink-6:     #eceaf3;
    --page-bg:   #f6f5f9;
    --white:     #ffffff;
    --ok:        #0b8c5c;
    --ok-bg:     #e8f9f2;
    --ok-border: #8adcb8;
    --wa:        #b36200;
    --wa-bg:     #fef5e7;
    --wa-border: #f5cc7a;
    --r-sm: 9px; --r-md: 14px; --r-lg: 20px;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.page {
    font-family: 'DM Sans', sans-serif;
    color: var(--ink);
    max-width: 1100px;
    margin: 0 auto;
    padding: 2rem 1.5rem 4rem;
    animation: fadein 0.4s cubic-bezier(0.16,1,0.3,1) both;
}
@keyframes fadein {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Header ── */
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.75rem;
}
.page-title {
    font-family: 'Sora', sans-serif;
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--ink);
    letter-spacing: -.025em;
}
.page-sub {
    font-size: .83rem;
    color: var(--ink-4);
    margin-top: .25rem;
}

/* ── Flash ── */
.flash {
    display: flex; align-items: center; gap: 10px;
    padding: 13px 18px;
    background: var(--ok-bg);
    border: 1px solid var(--ok-border);
    border-radius: var(--r-md);
    font-size: 13px; font-weight: 500; color: var(--ok);
    margin-bottom: 1.5rem;
}
.flash svg { width: 16px; height: 16px; flex-shrink: 0; }

/* ── Search bar ── */
.search-bar {
    display: flex;
    gap: .75rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}
.search-wrap {
    position: relative;
    flex: 1;
    min-width: 220px;
}
.search-wrap svg {
    position: absolute;
    left: 14px; top: 50%;
    transform: translateY(-50%);
    width: 16px; height: 16px;
    color: var(--ink-4);
    pointer-events: none;
}
.search-input {
    width: 100%;
    padding: 10px 14px 10px 40px;
    border: 1px solid var(--ink-5);
    border-radius: var(--r-sm);
    background: var(--white);
    font-size: 14px;
    font-family: 'DM Sans', sans-serif;
    color: var(--ink);
    outline: none;
    transition: border-color .18s, box-shadow .18s;
}
.search-input:focus {
    border-color: var(--vi);
    box-shadow: 0 0 0 3px rgba(79,50,197,.1);
}
.search-btn {
    padding: 10px 20px;
    background: var(--vi);
    color: #fff;
    border: none;
    border-radius: var(--r-sm);
    font-size: 13px;
    font-weight: 600;
    font-family: 'Sora', sans-serif;
    cursor: pointer;
    transition: background .18s;
    white-space: nowrap;
}
.search-btn:hover { background: var(--vi-2); }

/* ── Table card ── */
.tcard {
    background: var(--white);
    border: 1px solid var(--ink-6);
    border-radius: var(--r-lg);
    overflow: hidden;
}
.tcard-head {
    display: grid;
    grid-template-columns: 2fr 2fr 1.2fr 1.2fr 100px;
    gap: 1rem;
    padding: 12px 24px;
    background: var(--page-bg);
    border-bottom: 1px solid var(--ink-6);
    font-family: 'Sora', sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--ink-4);
}
.trow {
    display: grid;
    grid-template-columns: 2fr 2fr 1.2fr 1.2fr 100px;
    gap: 1rem;
    padding: 16px 24px;
    border-bottom: 1px solid var(--ink-6);
    align-items: center;
    transition: background .15s;
}
.trow:last-child { border-bottom: none; }
.trow:hover { background: var(--vi-6); }

.teacher-name {
    font-weight: 600;
    font-size: 14px;
    color: var(--ink);
    line-height: 1.3;
}
.teacher-email {
    font-size: 12px;
    color: var(--ink-4);
    margin-top: 2px;
}

.tcell { font-size: 13px; color: var(--ink-3); }

/* ── Badges ── */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .04em;
    padding: 4px 10px;
    border-radius: 999px;
    border: 1px solid transparent;
    white-space: nowrap;
}
.badge::before {
    content: '';
    width: 5px; height: 5px;
    border-radius: 50%;
    flex-shrink: 0;
}
.badge-ok  { background: var(--ok-bg);  color: var(--ok);  border-color: var(--ok-border); }
.badge-ok::before  { background: var(--ok); }
.badge-wa  { background: var(--wa-bg);  color: var(--wa);  border-color: var(--wa-border); }
.badge-wa::before  { background: #f0a500; }
.badge-vi  { background: var(--vi-5);   color: var(--vi);  border-color: var(--vi-4); }
.badge-vi::before  { background: var(--vi); }

/* ── Assign button ── */
.btn-assign {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 14px;
    background: var(--vi-5);
    color: var(--vi);
    border: 1px solid var(--vi-4);
    border-radius: var(--r-sm);
    font-size: 12px;
    font-weight: 700;
    font-family: 'Sora', sans-serif;
    text-decoration: none;
    transition: background .18s, border-color .18s, color .18s;
    white-space: nowrap;
}
.btn-assign:hover {
    background: var(--vi);
    color: #fff;
    border-color: var(--vi);
}
.btn-assign svg { width: 13px; height: 13px; }

/* ── Empty state ── */
.empty-state {
    text-align: center;
    padding: 3.5rem 1.5rem;
    color: var(--ink-4);
}
.empty-state svg { width: 44px; height: 44px; margin-bottom: 1rem; opacity: .4; }
.empty-state p { font-size: 14px; }

/* ── Pagination ── */
.pagination-wrap {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--ink-6);
    display: flex;
    justify-content: flex-end;
}

@media (max-width: 760px) {
    .tcard-head,
    .trow { grid-template-columns: 1fr 1fr; }
    .tcard-head .col-section,
    .trow .col-section,
    .tcard-head .col-subjects,
    .trow .col-subjects { display: none; }
}
</style>

<div class="page">

    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Teachers</h1>
            <p class="page-sub">Assign sections and subjects to teachers in your school.</p>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="flash">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Search --}}
    <form method="GET" action="{{ route('faculty.teachers.index') }}" class="search-bar">
        <div class="search-wrap">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
            </svg>
            <input type="text" name="search" class="search-input"
                   placeholder="Search by name or email…"
                   value="{{ request('search') }}">
        </div>
        <button type="submit" class="search-btn">Search</button>
        @if(request('search'))
            <a href="{{ route('faculty.teachers.index') }}" class="search-btn"
               style="background:var(--ink-4);text-decoration:none;display:inline-flex;align-items:center;">
               Clear
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="tcard">
        <div class="tcard-head">
            <div>Teacher</div>
            <div>Email</div>
            <div class="col-section">Section</div>
            <div class="col-subjects">Subjects</div>
            <div></div>
        </div>

        @forelse($teachers as $teacher)
        <div class="trow">
            {{-- Name --}}
            <div>
                <div class="teacher-name">{{ $teacher->name }}</div>
                @if($teacher->employee_id)
                    <div class="teacher-email">{{ $teacher->employee_id }}</div>
                @endif
            </div>

            {{-- Email --}}
            <div class="tcell">{{ $teacher->email }}</div>

            {{-- Section --}}
            <div class="col-section">
                @if($teacher->section)
                    <span class="badge badge-ok">{{ $teacher->section->name }}</span>
                @else
                    <span class="badge badge-wa">No section</span>
                @endif
            </div>

            {{-- Subjects count --}}
            <div class="col-subjects">
                @php $subjCount = $teacher->teacherSubjects->count(); @endphp
                @if($subjCount > 0)
                    <span class="badge badge-vi">{{ $subjCount }} subject{{ $subjCount > 1 ? 's' : '' }}</span>
                @else
                    <span class="badge badge-wa">None assigned</span>
                @endif
            </div>

            {{-- Action --}}
            <div>
                <a href="{{ route('faculty.teachers.assign', $teacher) }}" class="btn-assign">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                    </svg>
                    Assign
                </a>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87a4 4 0 100-8 4 4 0 000 8z"/>
            </svg>
            <p>No approved teachers found.</p>
        </div>
        @endforelse

        {{-- Pagination --}}
        @if($teachers->hasPages())
        <div class="pagination-wrap">
            {{ $teachers->links() }}
        </div>
        @endif
    </div>

</div>
@endsection