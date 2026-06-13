@extends('layouts.teacher')
@section('title', 'Quizzes & Assignments')

@section('content')
<style>
    .qz-wrap { max-width: 980px; margin: 0 auto; padding: 4px 0 48px; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    .qz-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; gap:12px; flex-wrap:wrap; }
    .qz-title { font-size:24px; font-weight:800; color:#0F172A; }
    .qz-sub { font-size:13px; color:#64748B; margin-top:2px; }
    .qz-btn { display:inline-flex; align-items:center; gap:8px; background:#2563EB; color:#fff; font-weight:600; font-size:14px;
              padding:11px 18px; border-radius:12px; text-decoration:none; box-shadow:0 6px 16px rgba(37,99,235,.28); transition:.2s; }
    .qz-btn:hover { background:#1D4ED8; transform:translateY(-1px); }
    .qz-flash { background:#F0FDF4; border:1px solid #86EFAC; color:#166534; padding:12px 16px; border-radius:12px; margin-bottom:18px; font-size:14px; font-weight:600; }
    .qz-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(290px,1fr)); gap:16px; }
    .qz-card { background:#fff; border:1px solid #E2E8F0; border-radius:18px; padding:18px; box-shadow:0 1px 3px rgba(15,23,42,.05); transition:.2s; }
    .qz-card:hover { box-shadow:0 10px 28px rgba(15,23,42,.10); transform:translateY(-2px); }
    .qz-card h3 { font-size:16px; font-weight:700; color:#0F172A; margin-bottom:6px; }
    .qz-meta { font-size:12.5px; color:#64748B; margin-bottom:12px; }
    .qz-pill { display:inline-block; font-size:11px; font-weight:700; padding:3px 9px; border-radius:99px; }
    .qz-pub { background:#EFF6FF; color:#1D4ED8; }
    .qz-draft { background:#F1F5F9; color:#64748B; }
    .qz-row { display:flex; align-items:center; justify-content:space-between; margin-top:14px; padding-top:14px; border-top:1px solid #F1F5F9; }
    .qz-count { font-size:13px; color:#475569; font-weight:600; }
    .qz-view { font-size:13px; font-weight:700; color:#2563EB; text-decoration:none; }
    .qz-empty { text-align:center; padding:60px 20px; color:#94A3B8; }
    .qz-empty svg { width:54px; height:54px; margin-bottom:14px; opacity:.5; }
</style>

<div class="qz-wrap">
    <div class="qz-head">
        <div>
            <div class="qz-title">Quizzes &amp; Assignments</div>
            <div class="qz-sub">Upload a quiz/activity file. Students will submit their answers here.</div>
        </div>
        <a href="{{ route('teacher.assignments.create') }}" class="qz-btn">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Create Quiz
        </a>
    </div>

    @if(session('success'))
        <div class="qz-flash">{{ session('success') }}</div>
    @endif

    @if($assignments->count())
        <div class="qz-grid">
            @foreach($assignments as $a)
                <div class="qz-card">
                    <span class="qz-pill {{ $a->is_published ? 'qz-pub' : 'qz-draft' }}">{{ $a->is_published ? 'Published' : 'Draft' }}</span>
                    <h3 style="margin-top:10px">{{ $a->title }}</h3>
                    <div class="qz-meta">
                        {{ $a->subject?->name }} • {{ $a->section?->name }}<br>
                        @if($a->due_date) Due: {{ $a->due_date->format('M d, Y g:i A') }} @else No deadline @endif
                        • {{ $a->max_score }} pts
                    </div>
                    <div class="qz-row">
                        <span class="qz-count">{{ $a->submissions_count }} submission{{ $a->submissions_count == 1 ? '' : 's' }}</span>
                        <a href="{{ route('teacher.assignments.show', $a) }}" class="qz-view">View &amp; Grade →</a>
                    </div>
                </div>
            @endforeach
        </div>
        <div style="margin-top:24px">{{ $assignments->links() }}</div>
    @else
        <div class="qz-empty">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <div style="font-size:15px; font-weight:600; color:#475569">No quizzes yet</div>
            <div style="font-size:13px; margin-top:4px">Click "Create Quiz" to upload your first one.</div>
        </div>
    @endif
</div>
@endsection