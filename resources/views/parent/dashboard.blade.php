@extends('layouts.app')
@section('title', 'Parent Portal')

@section('sidebar')
    <a href="{{ route('parent.dashboard') }}"
        class="{{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>
    <a href="{{ route('parent.child-records') }}"
        class="{{ request()->routeIs('parent.child-records') ? 'active' : '' }}">
        Child's Records
    </a>
@endsection

@section('content')
<style>
    /* Warm, document-like parent view — calm and easy to read */
    .pr { max-width: 860px; margin: 0 auto; color: #2b2620; }
    .pr-serif { font-family: Georgia, 'Times New Roman', serif; }

    .reveal { animation: prRise .5s cubic-bezier(.16,1,.3,1) both; }
    .reveal-2 { animation-delay:.07s; } .reveal-3 { animation-delay:.14s; }
    @keyframes prRise { from { opacity:0; transform:translateY(10px);} to { opacity:1; transform:translateY(0);} }
    @media (prefers-reduced-motion: reduce){ .reveal{ animation:none; } }

    /* Greeting */
    .pr-greet { margin-bottom: 26px; }
    .pr-greet h1 { font-size: 26px; font-weight: 600; color:#2b2620; margin:0; letter-spacing:-.01em; }
    .pr-greet p  { font-size: 13.5px; color:#9a8f80; margin-top:4px; }

    /* Child sheet */
    .pr-sheet { background:#fff; border:1px solid #ece6db; border-radius:8px; box-shadow:0 1px 2px rgba(80,60,30,.04); overflow:hidden; margin-bottom:22px; }
    .pr-sheet-top { display:flex; justify-content:space-between; gap:20px; padding:22px 24px; border-left:3px solid #9a3412; flex-wrap:wrap; }
    .pr-name { font-size:21px; font-weight:600; color:#2b2620; margin:0; }
    .pr-meta { font-size:13px; color:#9a8f80; margin-top:3px; }
    .pr-tag  { display:inline-block; font-size:11px; font-weight:600; letter-spacing:.03em; padding:3px 9px; border-radius:4px; margin-top:9px; }
    .pr-tag.on  { background:#eef7ef; color:#2f7d4f; }
    .pr-tag.off { background:#fbf3e6; color:#a96a1b; }

    .pr-avg { text-align:right; min-width:130px; }
    .pr-avg-num { font-size:40px; font-weight:600; line-height:1; color:#2b2620; }
    .pr-avg-num.low { color:#a23b1d; }
    .pr-avg-lbl { font-size:11px; letter-spacing:.08em; text-transform:uppercase; color:#b3a896; margin-top:4px; }
    .pr-note { font-size:12.5px; color:#7c7264; margin-top:6px; max-width:200px; margin-left:auto; }

    /* Grades list */
    .pr-body { padding: 4px 24px 18px; }
    .pr-subhead { font-size:12px; letter-spacing:.06em; text-transform:uppercase; color:#b3a896; padding:14px 0 6px; border-top:1px solid #f0ebe2; }
    .pr-row { display:flex; justify-content:space-between; align-items:center; padding:11px 0; border-bottom:1px solid #f4f0e8; }
    .pr-row:last-child { border-bottom:none; }
    .pr-subj { font-size:14.5px; color:#2b2620; font-weight:500; }
    .pr-q    { font-size:12px; color:#a99e8d; margin-left:8px; }
    .pr-mark { font-size:17px; font-weight:600; font-family:Georgia,serif; }
    .pr-mark.ok  { color:#2f7d4f; }
    .pr-mark.att { color:#a23b1d; }
    .pr-stat { font-size:11px; margin-left:10px; color:#a99e8d; }

    .pr-more { display:inline-block; margin-top:14px; font-size:13px; font-weight:600; color:#9a3412; text-decoration:none; }
    .pr-more:hover { text-decoration:underline; }
    .pr-empty-grades { padding:18px 0; color:#a99e8d; font-size:13.5px; }

    /* Announcements */
    .pr-ann { background:#fff; border:1px solid #ece6db; border-radius:8px; padding:20px 24px; }
    .pr-ann h2 { font-size:16px; font-weight:600; color:#2b2620; margin:0 0 4px; }
    .pr-ann-sub { font-size:12px; color:#b3a896; margin-bottom:8px; }
    .pr-ann-item { padding:14px 0; border-bottom:1px solid #f4f0e8; }
    .pr-ann-item:last-child { border-bottom:none; }
    .pr-ann-title { font-size:14.5px; font-weight:600; color:#2b2620; }
    .pr-ann-body { font-size:13px; color:#7c7264; margin-top:3px; line-height:1.5; }
    .pr-ann-meta { font-size:11.5px; color:#b3a896; margin-top:6px; }

    /* Empty / no children */
    .pr-none { background:#fff; border:1px dashed #ddd3c4; border-radius:8px; padding:46px 24px; text-align:center; }
    .pr-none h2 { font-size:17px; font-weight:600; color:#2b2620; margin:0; }
    .pr-none p  { font-size:13.5px; color:#9a8f80; margin-top:8px; max-width:380px; margin-inline:auto; line-height:1.55; }
</style>

<div class="pr">

    {{-- Greeting --}}
    @php
        $hour  = (int) now()->format('G');
        $part  = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $first = explode(' ', auth()->user()->name)[0];
    @endphp
    <div class="pr-greet reveal">
        <h1 class="pr-serif">{{ $part }}, {{ $first }}.</h1>
        <p>{{ now()->format('l, j F Y') }} — here’s how your {{ count($children) === 1 ? 'child is' : 'children are' }} doing.</p>
    </div>

    @forelse($children as $child)
    @php
        $avg     = $child->grades->whereNotNull('final_grade')->avg('final_grade');
        $graded  = $child->grades->whereNotNull('final_grade');
        $attention = $graded->filter(fn($g) => $g->final_grade < 75)->count();

        if (is_null($avg)) {
            $note = 'No grades have been posted yet. They’ll appear here once teachers submit them.';
        } elseif ($attention === 0) {
            $note = 'Passing every posted subject so far — well done.';
        } else {
            $note = $attention.' '.($attention === 1 ? 'subject needs' : 'subjects need').' a little attention.';
        }
    @endphp

    <div class="pr-sheet reveal reveal-2">

        {{-- Top: name + standing --}}
        <div class="pr-sheet-top">
            <div>
                <p class="pr-name pr-serif">{{ $child->name }}</p>
                <p class="pr-meta">
                    @if($child->grade_level) Grade {{ $child->grade_level }} @endif
                    @if($child->section) &middot; {{ $child->section->name }} @endif
                </p>
                @if($child->studentEnrollment)
                    <span class="pr-tag on">Enrolled · {{ $child->studentEnrollment->school_year }}</span>
                @else
                    <span class="pr-tag off">Not yet enrolled</span>
                @endif
            </div>
            <div class="pr-avg">
                <div class="pr-avg-num pr-serif {{ ($avg && $avg < 75) ? 'low' : '' }}">{{ $avg ? number_format($avg, 1) : '—' }}</div>
                <div class="pr-avg-lbl">Current Average</div>
                <p class="pr-note">{{ $note }}</p>
            </div>
        </div>

        {{-- Grades --}}
        <div class="pr-body">
            @if($child->grades->count())
                <div class="pr-subhead">Recent grades</div>
                @foreach($child->grades->whereNotNull('final_grade')->take(8) as $grade)
                <div class="pr-row">
                    <div>
                        <span class="pr-subj">{{ $grade->subject->name ?? 'Subject' }}</span>
                        <span class="pr-q">{{ $grade->quarter }}</span>
                    </div>
                    <div>
                        <span class="pr-mark {{ $grade->final_grade >= 75 ? 'ok' : 'att' }}">{{ $grade->final_grade }}</span>
                        <span class="pr-stat">{{ $grade->final_grade >= 75 ? 'Passed' : 'Needs work' }}</span>
                    </div>
                </div>
                @endforeach
                <a href="{{ route('parent.child-records') }}" class="pr-more">See full records &rarr;</a>
            @else
                <div class="pr-empty-grades">No grades have been posted for {{ explode(' ', $child->name)[0] }} yet.</div>
                <a href="{{ route('parent.child-records') }}" class="pr-more">View records &rarr;</a>
            @endif
        </div>

    </div>

    @empty
    <div class="pr-none reveal reveal-2">
        <h2 class="pr-serif">No child linked to your account yet</h2>
        <p>To see your child’s grades and records here, please ask the school administrator to link your child’s account to yours.</p>
    </div>
    @endforelse

    {{-- Announcements --}}
    <div class="pr-ann reveal reveal-3">
        <h2 class="pr-serif">From the school</h2>
        <p class="pr-ann-sub">Latest announcements</p>
        @forelse($announcements as $announcement)
        <div class="pr-ann-item">
            <div class="pr-ann-title">{{ $announcement->title }}</div>
            <div class="pr-ann-body">{{ $announcement->body }}</div>
            <div class="pr-ann-meta">
                {{ $announcement->author->name ?? 'School' }} &middot; {{ $announcement->created_at->diffForHumans() }}
            </div>
        </div>
        @empty
        <p class="pr-ann-sub" style="text-align:center;padding:18px 0;">No announcements right now.</p>
        @endforelse
    </div>

</div>
@endsection