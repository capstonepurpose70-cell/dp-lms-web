@extends('layouts.app')
@section('title', 'My Subjects')

@section('content')
<style>
    .subjects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
    }

    .subject-card {
        background: #fff;
        border-radius: 16px;
        border: 1.5px solid #f1f5f9;
        overflow: hidden;
        text-decoration: none;
        display: block;
        transition: transform 0.22s cubic-bezier(0.34,1.56,0.64,1),
                    box-shadow 0.22s ease,
                    border-color 0.2s ease;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    }

    .subject-card:hover {
        transform: translateY(-4px) scale(1.01);
        box-shadow: 0 14px 36px rgba(0,0,0,0.10);
        border-color: #e2e8f0;
    }

    .subject-card:active {
        transform: translateY(-1px) scale(0.99);
    }

    .subject-cover {
        height: 110px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .subject-cover-text {
        font-size: 22px;
        font-weight: 900;
        color: rgba(255,255,255,0.92);
        letter-spacing: -0.5px;
        text-align: center;
        padding: 0 12px;
        line-height: 1.2;
        word-break: break-word;
        text-shadow: 0 2px 8px rgba(0,0,0,0.18);
        z-index: 1;
    }

    .subject-cover::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(160deg, rgba(255,255,255,0.08) 0%, rgba(0,0,0,0.12) 100%);
    }

    .subject-body {
        padding: 14px 16px 16px;
    }

    .subject-name {
        font-size: 13px;
        font-weight: 700;
        color: #111827;
        line-height: 1.35;
        margin-bottom: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .subject-meta {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .subject-meta-row {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        color: #9ca3af;
    }

    .subject-meta-row svg {
        flex-shrink: 0;
        opacity: 0.7;
    }

    .empty-state {
        text-align: center;
        padding: 64px 24px;
        background: #fff;
        border-radius: 20px;
        border: 1.5px solid #f1f5f9;
        box-shadow: 0 2px 16px rgba(0,0,0,0.04);
    }

    .empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
    }

    .fade-in {
        animation: fadeInUp 0.35s cubic-bezier(0.16,1,0.3,1) both;
    }

    @for($i = 1; $i <= 20; $i++)
    .fade-in-{{ $i }} { animation-delay: {{ ($i * 0.04) }}s; }
    @endfor

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>

{{-- Color palette for covers --}}
@php
$coverColors = [
    ['#1e3a5f', '#2563eb'],
    ['#134e2a', '#16a34a'],
    ['#4c1d95', '#7c3aed'],
    ['#7c2d12', '#ea580c'],
    ['#0f4c75', '#0284c7'],
    ['#3b0764', '#a21caf'],
    ['#1a1a2e', '#4f46e5'],
    ['#052e16', '#15803d'],
    ['#450a0a', '#dc2626'],
    ['#0c1445', '#1d4ed8'],
];
@endphp

<div class="max-w-5xl mx-auto">

    {{-- Header --}}
    <div class="mb-8 fade-in fade-in-1">
        <h1 class="text-2xl font-bold text-gray-800">My Subjects</h1>
        <p class="text-sm text-gray-400 mt-1">
            @if($enrollment)
                {{ $subjects->count() }} {{ Str::plural('subject', $subjects->count()) }}
                &nbsp;·&nbsp; Grade {{ $enrollment->grade_level }}
                &nbsp;·&nbsp; {{ $section->name ?? '' }}
                &nbsp;·&nbsp; {{ $enrollment->school_year }}
            @else
                Your enrolled subjects will appear here.
            @endif
        </p>
    </div>

    @if(!$enrollment)
    {{-- Not enrolled --}}
    <div class="empty-state fade-in fade-in-2">
        <div class="empty-icon">
            <svg width="28" height="28" fill="none" stroke="#94a3b8"
                stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0
                       01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2
                       2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-gray-700 mb-2">No subjects yet</h3>
        <p class="text-sm text-gray-400 mb-6">
            You need to be enrolled first before subjects are assigned to you.
        </p>
        <a href="{{ route('student.enroll') }}"
            style="display:inline-flex; align-items:center; gap:8px;
                   background:#2563eb; color:#fff; padding:10px 22px;
                   border-radius:10px; font-size:13px; font-weight:700;
                   text-decoration:none; transition:background .2s, transform .15s;
                   box-shadow: 0 4px 14px rgba(37,99,235,0.3);"
            onmouseover="this.style.background='#1d4ed8';this.style.transform='translateY(-1px)'"
            onmouseout="this.style.background='#2563eb';this.style.transform='translateY(0)'">
            <svg width="15" height="15" fill="none" stroke="currentColor"
                stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                       a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Go to Enrollment Form
        </a>
    </div>

    @elseif($subjects->isEmpty())
    {{-- Enrolled but no subjects assigned yet --}}
    <div class="empty-state fade-in fade-in-2">
        <div class="empty-icon">
            <svg width="28" height="28" fill="none" stroke="#94a3b8"
                stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168
                       5.477 3 6.253v13C4.168 18.477 5.754 18 7.5
                       18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754
                       5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832
                       18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-gray-700 mb-2">No subjects assigned yet</h3>
        <p class="text-sm text-gray-400">
            Your teacher hasn't assigned any subjects to your section yet.
            Please check back later.
        </p>
    </div>

    @else
    {{-- Subjects grid --}}
    <div class="subjects-grid">
        @foreach($subjects as $index => $ts)
        @php
            $color = $coverColors[$index % count($coverColors)];
            $abbr  = collect(explode(' ', $ts->subject->name))
                        ->filter(fn($w) => strlen($w) > 2)
                        ->take(2)
                        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                        ->implode('');
            $abbr  = $abbr ?: strtoupper(substr($ts->subject->name, 0, 3));
        @endphp
        <div class="subject-card fade-in fade-in-{{ $index + 1 }}">
            {{-- Cover --}}
            <div class="subject-cover"
                style="background: linear-gradient(135deg, {{ $color[0] }}, {{ $color[1] }});">
                <span class="subject-cover-text">{{ $abbr }}</span>
            </div>

            {{-- Body --}}
            <div class="subject-body">
                <p class="subject-name">{{ $ts->subject->name }}</p>
                <div class="subject-meta">
                    <div class="subject-meta-row">
                        <svg width="11" height="11" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>{{ $ts->teacher->name ?? 'TBA' }}</span>
                    </div>
                    <div class="subject-meta-row">
                        <svg width="11" height="11" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $enrollment->school_year }}</span>
                    </div>
                    <div class="subject-meta-row">
                        <svg width="11" height="11" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2
                                   2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0
                                   012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span>Grade {{ $ts->grade_level ?? $enrollment->grade_level }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection