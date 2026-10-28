@extends('layouts.teacher')
@section('title', 'Material Details')

@section('content')
<style>
    .msd-head { padding: 22px; }
    .msd-desc { font-size: 14px; color: var(--text-2); line-height: 1.6; margin-top: 12px; white-space: pre-line; overflow-wrap: anywhere; }
    .msd-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 12px; margin-top: 6px; font-size: 13px; color: var(--text-3); }

    .msd-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 14px; }
    .msd-stat { padding: 16px 18px; }
    .msd-num { font-size: 26px; font-weight: 800; color: var(--text-1); line-height: 1; font-variant-numeric: tabular-nums; }
    .msd-lbl { font-size: 13px; color: var(--text-2); margin-top: 6px; }

    .msd-sec { padding: 20px; margin-top: 14px; }
    .msd-h2 { font-size: 15px; font-weight: 800; color: var(--text-1); margin-bottom: 6px; }
    .msd-row { display: flex; align-items: flex-start; gap: 12px; padding: 11px 0; border-bottom: 1px solid var(--border); }
    .msd-row:last-child { border-bottom: none; }
    .msd-av { width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0; display: grid; place-items: center; font-weight: 800; font-size: 14px; color: var(--accent); background: var(--accent-lt); }
    .msd-who { flex: 1; min-width: 0; }
    .msd-name { font-size: 14px; font-weight: 700; color: var(--text-1); }
    .msd-time { font-size: 12px; color: var(--text-3); white-space: nowrap; }
    .msd-body { font-size: 14px; color: var(--text-2); margin-top: 2px; line-height: 1.55; white-space: pre-line; overflow-wrap: anywhere; }
    .msd-none { font-size: 14px; color: var(--text-2); padding: 8px 0 2px; }

    @media (max-width: 480px) {
        .msd-stats { gap: 8px; }
        .msd-stat { padding: 12px; }
        .msd-num { font-size: 22px; }
    }
</style>

<div class="tp-page">
    <a href="{{ route('teacher.materials.index') }}" class="tp-back">
        <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Learning materials
    </a>

    <section class="tp-card msd-head">
        <h1 class="tp-title">{{ $material->title }}</h1>
        <p class="msd-meta">
            <span class="tp-pill {{ $material->is_published ? 'ok' : 'muted' }}">{{ $material->is_published ? 'Published' : 'Draft' }}</span>
            <span>{{ $material->subject->name ?? 'Subject removed' }}</span>
            @if($material->quarter)<span>{{ $material->quarter }}</span>@endif
            @if($material->week)<span>Week {{ $material->week }}</span>@endif
        </p>
        @if($material->description)
            <p class="msd-desc">{{ $material->description }}</p>
        @endif
    </section>

    <div class="msd-stats">
        <div class="tp-card msd-stat">
            <div class="msd-num">{{ $material->views_count }}</div>
            <div class="msd-lbl">{{ Str::plural('Student', $material->views_count) }} opened it</div>
        </div>
        <div class="tp-card msd-stat">
            <div class="msd-num">{{ $material->likes_count }}</div>
            <div class="msd-lbl">{{ Str::plural('Heart', $material->likes_count) }}</div>
        </div>
        <div class="tp-card msd-stat">
            <div class="msd-num">{{ $material->comments_count }}</div>
            <div class="msd-lbl">{{ Str::plural('Comment', $material->comments_count) }}</div>
        </div>
    </div>

    <section class="tp-card msd-sec" aria-labelledby="msd-viewers">
        <h2 class="msd-h2" id="msd-viewers">Who opened it</h2>
        @forelse($viewers as $v)
            <div class="msd-row">
                <span class="msd-av" aria-hidden="true">{{ mb_strtoupper(mb_substr($v->user->name ?? 'S', 0, 1)) }}</span>
                <div class="msd-who"><span class="msd-name">{{ $v->user->name ?? 'Student' }}</span></div>
                <time class="msd-time" datetime="{{ $v->created_at->toIso8601String() }}">{{ $v->created_at->diffForHumans() }}</time>
            </div>
        @empty
            <p class="msd-none">No student has opened it yet.</p>
        @endforelse
    </section>

    <section class="tp-card msd-sec" aria-labelledby="msd-comments">
        <h2 class="msd-h2" id="msd-comments">Comments</h2>
        @forelse($comments as $c)
            <div class="msd-row">
                <span class="msd-av" aria-hidden="true">{{ mb_strtoupper(mb_substr($c->user->name ?? 'S', 0, 1)) }}</span>
                <div class="msd-who">
                    <span class="msd-name">{{ $c->user->name ?? 'Student' }}</span>
                    <p class="msd-body">{{ $c->body }}</p>
                </div>
                <time class="msd-time" datetime="{{ $c->created_at->toIso8601String() }}">{{ $c->created_at->diffForHumans() }}</time>
            </div>
        @empty
            <p class="msd-none">No comments yet.</p>
        @endforelse
    </section>
</div>
@endsection
