@extends('layouts.app')
@section('title', 'Quizzes')

@section('content')
<style>
    .sq-wrap { max-width:920px; margin:0 auto; padding:4px 0 48px; font-family:'Plus Jakarta Sans', system-ui, sans-serif; }
    .sq-title { font-size:24px; font-weight:800; color:#0F172A; }
    .sq-sub { font-size:13px; color:#64748B; margin:2px 0 24px; }
    .sq-flash { background:#F0FDF4; border:1px solid #86EFAC; color:#166534; padding:12px 16px; border-radius:12px; margin-bottom:18px; font-size:14px; font-weight:600; }
    .sq-card { display:flex; align-items:center; justify-content:space-between; gap:14px; background:#fff; border:1px solid #E2E8F0; border-radius:16px; padding:18px 20px; margin-bottom:13px; box-shadow:0 1px 3px rgba(15,23,42,.05); transition:.2s; }
    .sq-card:hover { box-shadow:0 8px 24px rgba(15,23,42,.10); transform:translateY(-2px); }
    .sq-name { font-size:16px; font-weight:700; color:#0F172A; }
    .sq-meta { font-size:12.5px; color:#64748B; margin-top:3px; }
    .sq-right { display:flex; align-items:center; gap:14px; flex-shrink:0; }
    .sq-pill { font-size:11px; font-weight:700; padding:4px 11px; border-radius:99px; white-space:nowrap; }
    .sq-todo { background:#FEF3C7; color:#92400E; }
    .sq-done { background:#DBEAFE; color:#1D4ED8; }
    .sq-graded { background:#DCFCE7; color:#166534; }
    .sq-open { background:#2563EB; color:#fff; font-weight:700; font-size:13px; padding:9px 16px; border-radius:10px; text-decoration:none; white-space:nowrap; }
    .sq-open:hover { background:#1D4ED8; }
    .sq-empty { text-align:center; padding:60px 20px; color:#94A3B8; }
    .sq-empty svg { width:54px; height:54px; margin-bottom:14px; opacity:.5; }
</style>

<div class="sq-wrap">
    <div class="sq-title">Quizzes &amp; Assignments</div>
    <div class="sq-sub">Open a quiz to view it and submit your answer.</div>

    @if(session('success'))
        <div class="sq-flash">{{ session('success') }}</div>
    @endif

    @if($assignments->count())
        @foreach($assignments as $a)
            <div class="sq-card">
                <div>
                    <div class="sq-name">{{ $a->title }}</div>
                    <div class="sq-meta">
                        {{ $a->subject?->name }} •
                        @if($a->due_date)
                            Due {{ $a->due_date->format('M d, Y g:i A') }}
                            @if($a->isOverdue()) <span style="color:#DC2626;font-weight:700">(Overdue)</span> @endif
                        @else No deadline @endif
                        • {{ $a->max_score }} pts
                    </div>
                </div>
                <div class="sq-right">
                    @php $sub = $a->mySubmission; @endphp
                    @if($sub && $sub->status === 'graded')
                        <span class="sq-pill sq-graded">Graded: {{ $sub->score }}/{{ $a->max_score }}</span>
                    @elseif($sub)
                        <span class="sq-pill sq-done">Submitted</span>
                    @else
                        <span class="sq-pill sq-todo">Not submitted</span>
                    @endif
                    <a href="{{ route('student.quizzes.show', $a) }}" class="sq-open">Open</a>
                </div>
            </div>
        @endforeach
    @else
        <div class="sq-empty">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <div style="font-size:15px; font-weight:600; color:#475569">No quizzes available yet</div>
            <div style="font-size:13px; margin-top:4px">Quizzes from your teachers will show up here.</div>
        </div>
    @endif
</div>
@endsection