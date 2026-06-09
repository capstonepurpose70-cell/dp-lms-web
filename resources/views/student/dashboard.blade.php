@extends('layouts.app')
@section('title', 'Student Dashboard')

@section('content')
<style>
    .dash-card {
        background: #fff;
        border-radius: 16px;
        border: 1.5px solid #f1f5f9;
        padding: 24px;
        transition: transform 0.22s cubic-bezier(0.34,1.56,0.64,1),
                    box-shadow 0.22s ease,
                    border-color 0.2s ease;
        cursor: default;
        position: relative;
        overflow: hidden;
    }

    .dash-card:hover {
        transform: translateY(-4px) scale(1.01);
        box-shadow: 0 12px 32px rgba(0,0,0,0.09);
        border-color: #e2e8f0;
    }

    .dash-card:active {
        transform: translateY(-1px) scale(0.99);
        box-shadow: 0 4px 12px rgba(0,0,0,0.07);
    }

    .dash-card .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
        transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1);
    }

    .dash-card:hover .card-icon {
        transform: scale(1.12) rotate(-4deg);
    }

    .dash-card .card-number {
        font-size: 32px;
        font-weight: 800;
        color: #111827;
        line-height: 1;
        margin-bottom: 4px;
    }

    .dash-card .card-label {
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
    }

    .dash-card .card-sub {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 2px;
    }

    .nav-card {
        background: #fff;
        border-radius: 16px;
        border: 1.5px solid #f1f5f9;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        text-decoration: none;
        transition: transform 0.22s cubic-bezier(0.34,1.56,0.64,1),
                    box-shadow 0.22s ease,
                    border-color 0.2s ease,
                    background 0.2s ease;
        cursor: pointer;
    }

    .nav-card:hover {
        transform: translateY(-3px) scale(1.01);
        box-shadow: 0 10px 28px rgba(0,0,0,0.08);
        border-color: #dbeafe;
        background: #f8faff;
    }

    .nav-card:active {
        transform: translateY(0px) scale(0.98);
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    .nav-card .nav-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1);
    }

    .nav-card:hover .nav-icon {
        transform: scale(1.15) rotate(-5deg);
    }

    .nav-card .nav-title {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
    }

    .nav-card .nav-desc {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 2px;
    }

    .nav-card .nav-arrow {
        margin-left: auto;
        color: #d1d5db;
        transition: transform 0.2s ease, color 0.2s ease;
        flex-shrink: 0;
    }

    .nav-card:hover .nav-arrow {
        transform: translateX(4px);
        color: #3b82f6;
    }

    .announce-item {
        padding: 14px 0;
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.18s ease;
        border-radius: 8px;
        cursor: default;
    }

    .announce-item:last-child { border-bottom: none; }

    .announce-item:hover {
        background: #f8faff;
        padding-left: 8px;
        padding-right: 8px;
    }

    .fade-in {
        animation: fadeInUp 0.4s cubic-bezier(0.16,1,0.3,1) both;
    }

    .fade-in-1 { animation-delay: 0.05s; }
    .fade-in-2 { animation-delay: 0.1s; }
    .fade-in-3 { animation-delay: 0.15s; }
    .fade-in-4 { animation-delay: 0.2s; }
    .fade-in-5 { animation-delay: 0.25s; }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="max-w-5xl mx-auto">

    {{-- Welcome header --}}
    <div class="mb-8 fade-in fade-in-1">
        <h1 class="text-2xl font-bold text-gray-800">
            Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
            {{ explode(' ', auth()->user()->name)[0] }}!
        </h1>
        <p class="text-sm text-gray-400 mt-1">
            {{ now()->format('l, F d, Y') }} &nbsp;·&nbsp;
            Welcome to your learning dashboard.
        </p>
        @if(!$enrollment)
        <div class="bg-amber-50 rounded-xl p-4 mt-3">
            <p class="text-sm font-semibold text-amber-700">Not yet enrolled</p>
            <p class="text-xs text-amber-600 mt-1">
                Please wait for the administrator to assign your section and grade level.
            </p>
        </div>
        @endif
    </div>

    {{-- Enrollment info --}}
    @if($enrollment)
    <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-6 fade-in fade-in-1"
        style="box-shadow: 0 2px 16px rgba(0,0,0,0.04);">
        <div class="flex flex-wrap items-center gap-4">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide">Grade Level</p>
                <p class="text-lg font-bold text-gray-800 mt-0.5">Grade {{ $grade }}</p>
            </div>
            <div style="width:1px;height:40px;background:#f1f5f9;"></div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide">Section</p>
                <p class="text-lg font-bold text-gray-800 mt-0.5">{{ $section->name }}</p>
            </div>
            <div style="width:1px;height:40px;background:#f1f5f9;"></div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide">School Year</p>
                <p class="text-lg font-bold text-gray-800 mt-0.5">{{ $enrollment->school_year }}</p>
            </div>
            <div style="width:1px;height:40px;background:#f1f5f9;"></div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide">Total Subjects</p>
                <p class="text-lg font-bold text-gray-800 mt-0.5">{{ $subjectCount }}</p>
            </div>
            <span class="ml-auto px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                Enrolled
            </span>
        </div>
    </div>

    {{-- Subjects list --}}
    @if($subjects->count())
    <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-6 fade-in fade-in-2"
        style="box-shadow: 0 2px 16px rgba(0,0,0,0.04);">
        <h2 class="text-sm font-bold text-gray-700 mb-4">My Subjects</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($subjects as $ts)
            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100
                        hover:border-blue-200 hover:bg-blue-50 transition-all duration-200">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex-shrink-0"
                    style="display:flex;align-items:center;justify-content:center;">
                    <svg width="16" height="16" fill="none" stroke="#3b82f6"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168
                               5.477 3 6.253v13C4.168 18.477 5.754 18 7.5
                               18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754
                               5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832
                               18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">
                        {{ $ts->subject->name }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1">
                        <svg width="10" height="10" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ $ts->teacher->name ?? 'TBA' }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endif

    {{-- Stats row --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

        <div class="dash-card fade-in fade-in-1">
            <div class="card-icon" style="background:#eff6ff;">
                <svg width="22" height="22" fill="none" stroke="#3b82f6"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168
                           5.477 3 6.253v13C4.168 18.477 5.754 18 7.5
                           18s3.332.477 4.5 1.253m0-13C13.168 5.477
                           14.754 5 16.5 5c1.747 0 3.332.477 4.5
                           1.253v13C19.832 18.477 18.247 18 16.5
                           18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <p class="card-number">{{ $subjectCount }}</p>
            <p class="card-label">Subjects</p>
            <p class="card-sub">Enrolled this term</p>
        </div>

        <div class="dash-card fade-in fade-in-2">
            <div class="card-icon" style="background:#fff7ed;">
                <svg width="22" height="22" fill="none" stroke="#f97316"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2
                           0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002
                           2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0
                           012 2"/>
                </svg>
            </div>
            <p class="card-number">{{ $pendingAssignments }}</p>
            <p class="card-label">Assignments</p>
            <p class="card-sub">Pending submission</p>
        </div>

        <div class="dash-card fade-in fade-in-3">
            <div class="card-icon" style="background:#f0fdf4;">
                <svg width="22" height="22" fill="none" stroke="#22c55e"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03
                           8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512
                           15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <p class="card-number">{{ $unreadMessages }}</p>
            <p class="card-label">Messages</p>
            <p class="card-sub">Unread</p>
        </div>
    </div>

    {{-- Quick navigation cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">

        <a href="{{ route('student.modules') }}" class="nav-card fade-in fade-in-2">
            <div class="nav-icon" style="background:#eff6ff;">
                <svg width="20" height="20" fill="none" stroke="#3b82f6"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5
                           5S4.168 5.477 3 6.253v13C4.168 18.477 5.754
                           18 7.5 18s3.332.477 4.5 1.253m0-13C13.168
                           5.477 14.754 5 16.5 5c1.747 0 3.332.477
                           4.5 1.253v13C19.832 18.477 18.247 18 16.5
                           18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <p class="nav-title">Learning Modules</p>
                <p class="nav-desc">View and download your materials</p>
            </div>
            <svg class="nav-arrow" width="18" height="18" fill="none"
                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <a href="{{ route('student.quizzes') }}" class="nav-card fade-in fade-in-3">
            <div class="nav-icon" style="background:#faf5ff;">
                <svg width="20" height="20" fill="none" stroke="#a855f7"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2
                           0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002
                           2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0
                           012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div>
                <p class="nav-title">Quizzes</p>
                <p class="nav-desc">Take quizzes assigned by teachers</p>
            </div>
            <svg class="nav-arrow" width="18" height="18" fill="none"
                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <a href="{{ route('student.grades') }}" class="nav-card fade-in fade-in-4">
            <div class="nav-icon" style="background:#fff7ed;">
                <svg width="20" height="20" fill="none" stroke="#f97316"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2
                           2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0
                           012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002
                           2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2
                           2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0
                           01-2-2z"/>
                </svg>
            </div>
            <div>
                <p class="nav-title">My Grades</p>
                <p class="nav-desc">View your academic performance</p>
            </div>
            <svg class="nav-arrow" width="18" height="18" fill="none"
                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <a href="{{ route('student.messages') }}" class="nav-card fade-in fade-in-5">
            <div class="nav-icon" style="background:#f0fdf4;">
                <svg width="20" height="20" fill="none" stroke="#22c55e"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0
                           4.418-4.03 8-9 8a9.863 9.863 0
                           01-4.255-.949L3 20l1.395-3.72C3.512
                           15.042 3 13.574 3 12c0-4.418 4.03-8
                           9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <div>
                <p class="nav-title">Messages</p>
                <p class="nav-desc">Chat with your teachers</p>
            </div>
            <svg class="nav-arrow" width="18" height="18" fill="none"
                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- Announcements --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-6 fade-in fade-in-3"
        style="box-shadow: 0 2px 16px rgba(0,0,0,0.05);">
        <div class="flex justify-between items-center mb-2">
            <h2 class="text-sm font-bold text-gray-700">Recent Announcements</h2>
            <span class="text-xs text-gray-400">Latest updates</span>
        </div>

        @forelse($announcements as $announcement)
        <div class="announce-item">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-semibold text-gray-800">
                        {{ $announcement->title }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">
                        {{ $announcement->body }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $announcement->author->name ?? 'School' }}
                    </p>
                </div>
                <span class="text-xs text-gray-400 whitespace-nowrap ml-4 mt-0.5">
                    {{ $announcement->created_at->diffForHumans() }}
                </span>
            </div>
        </div>
        @empty
        <div class="text-center py-8">
            <div class="w-12 h-12 bg-gray-50 rounded-full mx-auto mb-3"
                style="display:flex;align-items:center;justify-content:center;">
                <svg width="20" height="20" fill="none" stroke="#d1d5db"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118
                           14.158V11a6.002 6.002 0 00-4-5.659V5a2
                           2 0 10-4 0v.341C7.67 6.165 6 8.388 6
                           11v3.159c0 .538-.214 1.055-.595
                           1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <p class="text-sm text-gray-400">No announcements yet.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection