@extends('layouts.app')
@section('title', 'Parent Portal')

@section('content')
<style>
    .pd-wrap { max-width: 900px; margin: 0 auto; }
    .pd-title { font-size: 24px; font-weight: 800; color: var(--text-1); letter-spacing: -.01em; }
    .pd-sub { font-size: 13.5px; color: var(--text-2); margin: 4px 0 20px; }

    .pd-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); margin-bottom: 18px; overflow: hidden; }

    /* Child header */
    .pd-child { display: flex; flex-wrap: wrap; align-items: center; gap: 14px 18px; padding: 18px 20px; border-bottom: 1px solid var(--border); }
    .pd-av { width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0; display: grid; place-items: center; background: var(--accent-lt); color: var(--accent); font-size: 18px; font-weight: 800; }
    .pd-who { flex: 1; min-width: 180px; }
    .pd-name { font-size: 17px; font-weight: 800; color: var(--text-1); }
    .pd-meta { font-size: 13px; color: var(--text-2); margin-top: 2px; }
    .pd-badge { display: inline-block; margin-top: 7px; padding: 3px 10px; border-radius: 99px; font-size: 11.5px; font-weight: 700; }
    .pd-badge.enrolled { background: #DCFCE7; color: #166534; }
    .pd-badge.pending  { background: #FEF3C7; color: #92400E; }
    .pd-badge.returned { background: #FEE2E2; color: #991B1B; }
    .pd-badge.none     { background: #F1F5F9; color: #475569; }

    .pd-avg { text-align: right; }
    .pd-avg-num { font-size: 30px; font-weight: 800; line-height: 1; color: var(--text-1); font-variant-numeric: tabular-nums; }
    .pd-avg-num.pass { color: #15803D; }
    .pd-avg-num.fail { color: #B91C1C; }
    .pd-avg-lbl { font-size: 12px; font-weight: 600; color: var(--text-3); margin-top: 4px; }

    /* Subjects */
    .pd-body { padding: 6px 20px 16px; }
    .pd-note { font-size: 13px; color: var(--text-2); line-height: 1.55; padding: 10px 0 4px; }
    .pd-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 11px 0; border-bottom: 1px solid var(--border); }
    .pd-row:last-of-type { border-bottom: none; }
    .pd-subj { font-size: 14px; font-weight: 700; color: var(--text-1); }
    .pd-qs { display: block; font-size: 12px; font-weight: 500; color: var(--text-3); margin-top: 1px; }
    .pd-mark { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
    .pd-mark b { font-size: 16px; font-variant-numeric: tabular-nums; color: var(--text-1); }
    .pd-pill { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
    .pd-pill.pass { background: #DCFCE7; color: #166534; }
    .pd-pill.fail { background: #FEE2E2; color: #991B1B; }
    .pd-more { display: inline-flex; align-items: center; min-height: 44px; margin-top: 4px; font-size: 13.5px; font-weight: 700; color: var(--accent-dark); text-decoration: none; }
    .pd-more:hover { text-decoration: underline; }

    /* Announcements */
    .pd-section { font-size: 12.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--text-2); margin: 26px 0 10px; }
    .pd-ann { padding: 16px 20px; border-bottom: 1px solid var(--border); }
    .pd-ann:last-child { border-bottom: none; }
    .pd-ann-title { font-size: 14.5px; font-weight: 700; color: var(--text-1); }
    .pd-ann-body { font-size: 13.5px; color: var(--text-2); line-height: 1.55; margin-top: 4px; white-space: pre-line; overflow-wrap: anywhere; }
    .pd-ann-meta { font-size: 12px; color: var(--text-3); margin-top: 6px; }

    .pd-empty { padding: 36px 20px; text-align: center; }
    .pd-empty b { display: block; font-size: 15px; color: var(--text-1); }
    .pd-empty p { font-size: 13.5px; color: var(--text-2); margin: 6px auto 0; max-width: 420px; line-height: 1.55; }
    .pd-foot { font-size: 12.5px; color: var(--text-2); margin-top: 8px; }   /* sits on --bg: text-3 is only 4.4:1 there */

    @media (max-width: 560px) {
        .pd-avg { text-align: left; width: 100%; display: flex; align-items: baseline; gap: 8px; }
        .pd-avg-lbl { margin-top: 0; }
    }
</style>

@php
    $hour     = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $badges   = [
        'enrolled' => 'Enrolled · SY ' . $schoolYear,
        'pending'  => 'Enrollment form under review',
        'returned' => 'Enrollment form returned',
        'none'     => 'Not enrolled for SY ' . $schoolYear,
    ];
@endphp

<div class="pd-wrap">
    <h1 class="pd-title">{{ $greeting }}, {{ Str::of(auth()->user()->name)->explode(' ')->first() }}</h1>
    <p class="pd-sub">
        {{ now()->format('l, F j, Y') }}
        @if($children->isNotEmpty())
            · How your {{ $children->count() === 1 ? 'child is' : 'children are' }} doing this school year
        @endif
    </p>

    @forelse($children as $child)
        @php
            $card    = $cards[$child->id];
            $state   = $states[$child->id];
            $general = $card['general'] ?? null;
            $section = $child->studentEnrollment?->section ?? $child->section;
            $failing = $card ? $card['subjects']->filter(fn ($s) => $s['average'] !== null && !$s['passed'])->count() : 0;
        @endphp
        <section class="pd-card" aria-label="{{ $child->name }}">
            <div class="pd-child">
                <span class="pd-av" aria-hidden="true">{{ mb_strtoupper(mb_substr($child->name, 0, 1)) }}</span>
                <div class="pd-who">
                    <p class="pd-name">{{ $child->name }}</p>
                    <p class="pd-meta">
                        Grade {{ $child->studentEnrollment?->grade_level ?? $child->grade_level ?? '—' }}@if($section) · {{ $section->name }}@endif
                    </p>
                    <span class="pd-badge {{ $state }}">{{ $badges[$state] }}</span>
                </div>
                <div class="pd-avg">
                    @if($general !== null)
                        <div class="pd-avg-num {{ $general >= $passing ? 'pass' : 'fail' }}">{{ number_format($general, 2) }}</div>
                        <div class="pd-avg-lbl">General average{{ $card['complete'] ? '' : ' so far' }}</div>
                    @else
                        <div class="pd-avg-num" aria-hidden="true">—</div>
                        <div class="pd-avg-lbl">No grades yet</div>
                    @endif
                </div>
            </div>

            <div class="pd-body">
                @if(!$card)
                    <p class="pd-note">No grades have been posted for SY {{ $schoolYear }} yet. They appear here as soon as teachers record them.</p>
                @else
                    <p class="pd-note">
                        @if($failing === 0)
                            Passing every subject with a recorded grade so far.
                        @else
                            {{ $failing }} {{ Str::plural('subject', $failing) }} below the passing grade of {{ $passing }} — worth a talk with the teacher.
                        @endif
                    </p>
                    @foreach($card['subjects'] as $s)
                        @php $recorded = $s['quarters']->filter(fn ($g) => $g && $g->final_grade !== null)->keys(); @endphp
                        <div class="pd-row">
                            <span class="pd-subj">
                                {{ $s['subject']?->name ?? 'Subject removed' }}
                                <span class="pd-qs">{{ $recorded->isNotEmpty() ? $recorded->implode(', ') . ' recorded' : 'No quarter grade yet' }}</span>
                            </span>
                            @if($s['average'] !== null)
                                <span class="pd-mark">
                                    <b>{{ number_format($s['average'], 2) }}</b>
                                    <span class="pd-pill {{ $s['passed'] ? 'pass' : 'fail' }}">{{ $s['passed'] ? 'Passing' : 'Below passing' }}</span>
                                </span>
                            @endif
                        </div>
                    @endforeach
                @endif
                <a class="pd-more" href="{{ route('parent.child-records') }}#child-{{ $child->id }}">Full records and attendance →</a>
            </div>
        </section>
    @empty
        <div class="pd-card pd-empty">
            <b>No child is linked to your account</b>
            <p>Please contact the school office so they can link your child's account to yours.</p>
        </div>
    @endforelse

    @if($children->isNotEmpty())
        <p class="pd-foot">Have another child at this school? Ask the school office to link them to this account.</p>
    @endif

    <h2 class="pd-section">From the school</h2>
    <div class="pd-card">
        @forelse($announcements as $a)
            <article class="pd-ann">
                <h3 class="pd-ann-title">{{ $a->title }}</h3>
                <p class="pd-ann-body">{{ $a->body }}</p>
                <p class="pd-ann-meta">
                    {{ $a->author->name ?? 'School' }} ·
                    <time datetime="{{ $a->created_at?->toIso8601String() }}">{{ $a->created_at?->diffForHumans() }}</time>
                </p>
            </article>
        @empty
            <div class="pd-empty"><p>No announcements right now.</p></div>
        @endforelse
    </div>
</div>
@endsection
