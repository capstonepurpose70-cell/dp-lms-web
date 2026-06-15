@extends('layouts.app')
@section('title', 'Strata Rush — Leaderboard')

@section('content')
<style>
    .lb-wrap { max-width: 820px; margin: 0 auto; padding: 8px 4px 40px; }
    .lb-head {
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px; flex-wrap: wrap; margin-bottom: 20px;
    }
    .lb-title { font-size: 22px; font-weight: 800; color: var(--ink, #0f172a); letter-spacing: -0.02em; }
    .lb-title span { color: #7c3aed; }
    .lb-sub { font-size: 13px; color: var(--muted, #64748b); margin-top: 2px; }
    .lb-back {
        display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700;
        color: #fff; text-decoration: none; padding: 9px 16px; border-radius: 12px;
        background: linear-gradient(135deg, #7c3aed, #2563eb);
    }
    .lb-mybest {
        background: linear-gradient(135deg, #faf5ff, #eef2ff);
        border: 1px solid #e9d5ff; border-radius: 14px; padding: 14px 18px; margin-bottom: 18px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .lb-mybest b { color: #7c3aed; font-size: 20px; }
    .lb-card { background: var(--surface, #fff); border: 1px solid var(--border, #e2e8f0); border-radius: 16px; overflow: hidden; }
    .lb-row {
        display: grid; grid-template-columns: 56px 1fr auto auto; align-items: center;
        gap: 12px; padding: 13px 18px; border-bottom: 1px solid var(--border, #eef2f7);
    }
    .lb-row:last-child { border-bottom: none; }
    .lb-rank { font-weight: 800; font-size: 16px; color: var(--muted, #94a3b8); text-align: center; }
    .lb-row.me { background: #faf5ff; }
    .lb-row.top1 .lb-rank { color: #f59e0b; }
    .lb-row.top2 .lb-rank { color: #94a3b8; }
    .lb-row.top3 .lb-rank { color: #b45309; }
    .lb-name { font-weight: 700; font-size: 14.5px; color: var(--ink, #0f172a); }
    .lb-acc { font-size: 12px; color: var(--muted, #64748b); }
    .lb-score { font-weight: 800; font-size: 17px; color: #7c3aed; }
    .lb-empty { padding: 50px 20px; text-align: center; color: var(--muted, #94a3b8); }
</style>

<div class="lb-wrap">
    <div class="lb-head">
        <div>
            <div class="lb-title">🏆 <span>Strata Rush</span> Leaderboard</div>
            <div class="lb-sub">Grade {{ $grade }} — {{ $world }}</div>
        </div>
        <a href="{{ route('student.science-game') }}" class="lb-back">▶ Play</a>
    </div>

    <div class="lb-mybest">
        <span style="font-weight:700;color:var(--ink,#0f172a);">Your Best Score</span>
        <b>{{ number_format($myBest) }}</b>
    </div>

    <div class="lb-card">
        @forelse($top as $i => $row)
            <div class="lb-row top{{ $i + 1 }} {{ $row->user_id === auth()->id() ? 'me' : '' }}">
                <div class="lb-rank">
                    @if($i === 0) 🥇 @elseif($i === 1) 🥈 @elseif($i === 2) 🥉 @else {{ $i + 1 }} @endif
                </div>
                <div>
                    <div class="lb-name">{{ $row->user->name ?? 'Student' }}{{ $row->user_id === auth()->id() ? ' (You)' : '' }}</div>
                    <div class="lb-acc">{{ number_format($row->accuracy, 0) }}% accuracy · combo x{{ $row->max_combo }}</div>
                </div>
                <div></div>
                <div class="lb-score">{{ number_format($row->score) }}</div>
            </div>
        @empty
            <div class="lb-empty">Wala pang scores. Ikaw ang mauuna! ▶</div>
        @endforelse
    </div>
</div>
@endsection