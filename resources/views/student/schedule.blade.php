@extends('layouts.app')
@section('title', 'My Schedule')

@section('content')
<style>
    /* Gumagamit ng design tokens ng layouts/app.blade.php — walang bagong
       Tailwind class, kaya hindi apektado ng estado ng CSS build. */

    .sch-wrap { max-width: 1080px; margin: 0 auto; }

    .sch-head { margin-bottom: 22px; }
    .sch-title { font-size: 24px; font-weight: 700; letter-spacing: -.02em; color: var(--text-1); }
    .sch-sub   { font-size: 13.5px; color: var(--text-2); margin-top: 4px; }

    .sch-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--r-lg); box-shadow: var(--shadow-sm);
    }

    /* ─── Weekly grid ─────────────────────────────────────────── */
    .sch-week { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; }

    .sch-day-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 11px 14px; border-bottom: 1px solid var(--border);
    }
    .sch-day-name {
        font-size: 12px; font-weight: 700; letter-spacing: .05em;
        text-transform: uppercase; color: var(--text-1);
    }
    .sch-day-count { font-size: 11.5px; color: var(--text-3); }
    .sch-day-body { padding: 10px; display: flex; flex-direction: column; gap: 8px; }

    .sch-today .sch-day-head { background: var(--accent-lt); }
    .sch-today .sch-day-name { color: var(--accent); }

    /* ─── Period card ─────────────────────────────────────────── */
    .sch-item {
        border: 1px solid var(--border); border-left: 3px solid var(--accent);
        border-radius: var(--r-sm); padding: 10px 11px; background: var(--surface);
    }
    .sch-time {
        font-size: 11.5px; font-weight: 700; color: var(--accent);
        letter-spacing: .01em;
    }
    .sch-subject {
        font-size: 13px; font-weight: 600; color: var(--text-1);
        margin-top: 3px; line-height: 1.35;
    }
    .sch-meta {
        font-size: 11.5px; color: var(--text-2); margin-top: 5px;
        display: flex; align-items: center; gap: 5px;
    }
    .sch-room {
        display: inline-block; margin-top: 6px; padding: 2px 7px;
        border-radius: 999px; background: var(--bg); color: var(--text-2);
        font-size: 11px; font-weight: 600;
    }

    .sch-free { font-size: 12px; color: var(--text-3); text-align: center; padding: 18px 6px; }

    /* ─── Empty / not enrolled ────────────────────────────────── */
    .sch-empty { text-align: center; padding: 48px 20px; }
    .sch-empty-ico {
        width: 46px; height: 46px; border-radius: 50%; margin: 0 auto 14px;
        background: var(--bg); color: var(--text-3);
        display: flex; align-items: center; justify-content: center;
    }
    .sch-empty-title { font-size: 15px; font-weight: 600; color: var(--text-1); }
    .sch-empty-text  { font-size: 13px; color: var(--text-2); margin-top: 5px; line-height: 1.55; }

    /* ─── Info strip ──────────────────────────────────────────── */
    .sch-info { display: flex; flex-wrap: wrap; gap: 26px; padding: 16px 20px; margin-bottom: 18px; }
    .sch-info-label {
        font-size: 11px; font-weight: 600; letter-spacing: .06em;
        text-transform: uppercase; color: var(--text-3);
    }
    .sch-info-value { font-size: 16px; font-weight: 700; color: var(--text-1); margin-top: 3px; }

    .sch-in { animation: schIn .35s cubic-bezier(.22,.61,.36,1) both; }
    @keyframes schIn { from { opacity:0; transform: translateY(10px); } to { opacity:1; transform:none; } }

    /* ─── Responsive: naging listahan sa maliit na screen ─────── */
    @media (max-width: 1000px) { .sch-week { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 620px)  {
        .sch-week { grid-template-columns: 1fr; }
        .sch-title { font-size: 21px; }
        .sch-info { gap: 18px; }
    }
    @media (prefers-reduced-motion: reduce) { .sch-in { animation: none; } }
</style>

@php
    // Lunes = 1 ... Biyernes = 5. Sabado/Linggo -> walang naka-highlight.
    $todayNum = (int) now()->dayOfWeekIso;
@endphp

<div class="sch-wrap">

    <header class="sch-head sch-in">
        <h1 class="sch-title">My Class Schedule</h1>
        <p class="sch-sub">Monday to Friday</p>
    </header>

    @if(!$section)
        {{-- Hindi pa naka-enroll o walang section --}}
        <div class="sch-card sch-in">
            <div class="sch-empty">
                <div class="sch-empty-ico" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                </div>
                <p class="sch-empty-title">No schedule yet</p>
                <p class="sch-empty-text">
                    Your class schedule will appear here once you are enrolled<br>
                    and the administrator assigns you to a section.
                </p>
            </div>
        </div>
    @else
        {{-- Section info --}}
        <section class="sch-card sch-info sch-in" aria-label="Section details">
            <div>
                <p class="sch-info-label">Grade &amp; Section</p>
                <p class="sch-info-value">
                    Grade {{ $section->grade_level }} – {{ $section->name }}
                </p>
            </div>
            @if($section->adviser)
            <div>
                <p class="sch-info-label">Adviser</p>
                <p class="sch-info-value">{{ $section->adviser->name }}</p>
            </div>
            @endif
            <div>
                <p class="sch-info-label">School Year</p>
                <p class="sch-info-value">{{ $section->school_year ?? '—' }}</p>
            </div>
        </section>

        @if($schedules->isEmpty())
            <div class="sch-card sch-in">
                <div class="sch-empty">
                    <div class="sch-empty-ico" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
                        </svg>
                    </div>
                    <p class="sch-empty-title">Schedule not yet posted</p>
                    <p class="sch-empty-text">
                        Your section has no class schedule yet.<br>
                        Please check back later.
                    </p>
                </div>
            </div>
        @else
            <div class="sch-week sch-in">
                @foreach($days as $num => $label)
                    @php $periods = $schedules[$num] ?? collect(); @endphp
                    <div class="sch-card {{ $num === $todayNum ? 'sch-today' : '' }}">
                        <div class="sch-day-head">
                            <span class="sch-day-name">{{ $label }}</span>
                            <span class="sch-day-count">
                                {{ $periods->count() }}
                                {{ \Illuminate\Support\Str::plural('class', $periods->count()) }}
                            </span>
                        </div>
                        <div class="sch-day-body">
                            @forelse($periods as $p)
                                <article class="sch-item">
                                    <p class="sch-time">{{ $p->time_range }}</p>
                                    <p class="sch-subject">{{ $p->subject?->name ?? 'Subject removed' }}</p>
                                    <p class="sch-meta">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                                             stroke-linejoin="round" aria-hidden="true">
                                            <circle cx="12" cy="8" r="3.5"/>
                                            <path d="M5 20a7 7 0 0 1 14 0"/>
                                        </svg>
                                        {{ $p->teacher?->name ?? 'Teacher to be assigned' }}
                                    </p>
                                    @if($p->room)
                                        <span class="sch-room">Room {{ $p->room }}</span>
                                    @endif
                                </article>
                            @empty
                                <p class="sch-free">No classes</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

</div>
@endsection