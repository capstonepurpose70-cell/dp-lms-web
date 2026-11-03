@extends('layouts.app')
@section('title', 'My Subjects')

@section('content')
{{-- One accent color; each card links to that subject's modules. --}}
<style>
    .ss-wrap { max-width: 1040px; margin: 0 auto; }
    .ss-title { font-size: 24px; font-weight: 800; color: var(--text-1); letter-spacing: -.01em; }
    .ss-sub { font-size: 14px; color: var(--text-2); margin: 4px 0 22px; }

    .ss-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px; }
    .ss-card {
        display: flex; flex-direction: column; background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--r-lg); box-shadow: var(--shadow-sm); text-decoration: none; color: inherit;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .ss-card:hover { border-color: #C7D4EE; box-shadow: var(--shadow-md); }
    .ss-card:focus-visible { outline: 3px solid var(--accent); outline-offset: 2px; }
    .ss-top { display: flex; align-items: center; gap: 12px; padding: 16px 18px 12px; }
    .ss-abbr { width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0; display: grid; place-items: center;
               background: var(--accent-lt); color: var(--accent-dark); font-size: 14px; font-weight: 800; letter-spacing: .02em; }
    .ss-name { font-size: 15px; font-weight: 800; color: var(--text-1); line-height: 1.3; }
    .ss-code { font-size: 12.5px; color: var(--text-3); margin-top: 1px; font-variant-numeric: tabular-nums; }
    .ss-teacher { display: flex; align-items: center; gap: 8px; padding: 0 18px 14px; font-size: 13.5px; color: var(--text-2); }
    .ss-teacher svg { width: 16px; height: 16px; color: var(--text-3); flex-shrink: 0; }
    .ss-link { display: flex; align-items: center; justify-content: space-between; margin-top: auto; padding: 12px 18px;
               border-top: 1px solid var(--border); font-size: 13.5px; font-weight: 700; color: var(--accent-dark); }
    .ss-link svg { width: 16px; height: 16px; transition: transform .15s ease; }
    .ss-card:hover .ss-link svg { transform: translateX(2px); }
    @media (prefers-reduced-motion: reduce) { .ss-card, .ss-link svg { transition: none; } }

    .ss-empty { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); padding: 48px 20px; text-align: center; }
    .ss-empty b { display: block; font-size: 15.5px; color: var(--text-1); margin-bottom: 6px; }
    .ss-empty p { font-size: 13.5px; color: var(--text-2); line-height: 1.55; }
    .ss-btn { display: inline-flex; align-items: center; min-height: 44px; margin-top: 16px; padding: 0 20px; border-radius: 10px; background: var(--accent); color: #fff; font-size: 13.5px; font-weight: 700; text-decoration: none; }
    .ss-btn:hover { background: var(--accent-dark); }
</style>

<div class="ss-wrap">
    <h1 class="ss-title">My Subjects</h1>
    <p class="ss-sub">
        @if($enrollment)
            {{ $subjects->count() }} {{ Str::plural('subject', $subjects->count()) }}
            · Grade {{ $enrollment->grade_level }} – {{ $section->name ?? '' }} · SY {{ $enrollment->school_year }}
        @else
            Your subjects appear here once you are enrolled.
        @endif
    </p>

    @if(!$enrollment)
        <div class="ss-empty">
            <b>No subjects yet</b>
            <p>Your subjects are assigned when your enrollment is approved and you are placed in a section.</p>
            <a href="{{ route('student.dashboard') }}" class="ss-btn">See what to do next</a>
        </div>
    @elseif($subjects->isEmpty())
        <div class="ss-empty">
            <b>No subjects assigned yet</b>
            <p>The school hasn't assigned teachers to your section's subjects yet. Please check back later.</p>
        </div>
    @else
        <div class="ss-grid">
            @foreach($subjects->sortBy(fn ($ts) => mb_strtolower($ts->subject?->name ?? '')) as $ts)
                @php
                    $name = $ts->subject?->name ?? 'Subject removed';
                    $abbr = collect(preg_split('/\s+/', $name))->filter(fn ($w) => mb_strlen($w) > 2)->take(2)
                                ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: mb_strtoupper(mb_substr($name, 0, 3));
                @endphp
                <a class="ss-card" href="{{ route('student.modules') }}#subj-{{ $ts->subject_id }}"
                   aria-label="{{ $name }} — {{ $ts->teacher->name ?? 'teacher to be assigned' }}. Open learning modules">
                    <div class="ss-top">
                        <span class="ss-abbr" aria-hidden="true">{{ $abbr }}</span>
                        <div style="min-width:0;">
                            <p class="ss-name">{{ $name }}</p>
                            @if($ts->subject?->code)<p class="ss-code">{{ $ts->subject->code }}</p>@endif
                        </div>
                    </div>
                    <p class="ss-teacher">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 21a8 8 0 0 1 16 0"/></svg>
                        {{ $ts->teacher->name ?? 'Teacher to be assigned' }}
                    </p>
                    <span class="ss-link">Learning modules
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                    </span>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
