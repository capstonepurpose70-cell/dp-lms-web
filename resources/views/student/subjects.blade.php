@extends('layouts.app')
@section('title', 'My Subjects')

@section('content')
{{-- One accent color; each card links to that subject's modules. --}}
<style>
    .ss-wrap { max-width: 1040px; margin: 0 auto; }
    .ss-title { font-size: 24px; font-weight: 800; color: var(--text-1); letter-spacing: -.01em; }
    .ss-sub { font-size: 13.5px; color: var(--text-2); margin: 4px 0 22px; }

    .ss-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
    .ss-card {
        display: flex; flex-direction: column; background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--r-lg); box-shadow: var(--shadow-sm); overflow: hidden; text-decoration: none; color: inherit;
        transition: border-color .18s, box-shadow .18s, transform .18s;
    }
    .ss-card:hover { border-color: var(--accent); box-shadow: 0 8px 22px var(--accent-glow); transform: translateY(-2px); }
    .ss-cover { height: 84px; display: grid; place-items: center; background: linear-gradient(135deg, var(--accent-dark), var(--accent)); }
    .ss-abbr { font-size: 24px; font-weight: 900; color: #fff; letter-spacing: .02em; }
    .ss-body { padding: 14px 16px 16px; flex: 1; display: flex; flex-direction: column; }
    .ss-name { font-size: 14.5px; font-weight: 700; color: var(--text-1); line-height: 1.35; }
    .ss-meta { font-size: 12.5px; color: var(--text-2); margin-top: 6px; line-height: 1.5; }
    .ss-link { margin-top: auto; padding-top: 10px; font-size: 12.5px; font-weight: 700; color: var(--accent-dark); }

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
                    <div class="ss-cover" aria-hidden="true"><span class="ss-abbr">{{ $abbr }}</span></div>
                    <div class="ss-body">
                        <p class="ss-name">{{ $name }}</p>
                        <p class="ss-meta">
                            {{ $ts->teacher->name ?? 'Teacher to be assigned' }}
                            @if($ts->subject?->code)<br>{{ $ts->subject->code }}@endif
                        </p>
                        <span class="ss-link">Learning modules →</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
