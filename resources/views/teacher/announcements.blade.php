@extends('layouts.teacher') 
@section('title', 'Announcements')

@section('sidebar')
    <a href="{{ route('teacher.dashboard') }}"
        class="{{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>
    <a href="{{ route('teacher.gradebook.index') }}"
        class="{{ request()->routeIs('teacher.gradebook.*') ? 'active' : '' }}">
        Gradebook
    </a>
    <a href="{{ route('teacher.materials.index') }}"
        class="{{ request()->routeIs('teacher.materials.*') ? 'active' : '' }}">
        Learning Materials
    </a>
    <a href="{{ route('teacher.announcements.index') }}"
        class="{{ request()->routeIs('teacher.announcements.*') ? 'active' : '' }}">
        Announcements
    </a>
@endsection

@section('content')
<style>
    .page-card {
        background: #fff;
        border-radius: 16px;
        border: 1.5px solid #f1f5f9;
        box-shadow: 0 2px 16px rgba(0,0,0,0.04);
        animation: fadeUp 0.4s cubic-bezier(0.16,1,0.3,1) both;
    }

    @keyframes fadeUp {
        from { opacity:0; transform:translateY(12px); }
        to   { opacity:1; transform:translateY(0); }
    }

    .form-card {
        background: #fff;
        border-radius: 16px;
        border: 1.5px solid #f1f5f9;
        box-shadow: 0 2px 16px rgba(0,0,0,0.04);
        padding: 20px;
        margin-bottom: 20px;
        animation: fadeUp 0.35s cubic-bezier(0.16,1,0.3,1) both;
    }

    .field-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .field-input {
        width: 100%;
        border: 1.5px solid #e5e7eb;
        border-radius: 9px;
        padding: 9px 12px;
        font-size: 13px;
        color: #111827;
        background: #fafafa;
        outline: none;
        font-family: inherit;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .field-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        background: #fff;
    }

    textarea.field-input {
        resize: none;
        min-height: 80px;
    }

    .post-btn {
        background: #2563eb;
        color: white;
        border: none;
        border-radius: 9px;
        padding: 9px 20px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
        box-shadow: 0 3px 10px rgba(37,99,235,0.25);
    }

    .post-btn:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(37,99,235,0.35);
    }

    .post-btn:active { transform: scale(0.97); }

    .announce-item {
        padding: 16px 20px;
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.15s ease;
    }

    .announce-item:last-child { border-bottom: none; }
    .announce-item:hover { background: #f8faff; }
</style>

<div class="max-w-4xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Announcements</h1>
        <p class="text-sm text-gray-400 mt-1">Post announcements for your students and parents.</p>
    </div>

    {{-- Post Form --}}
    <div class="form-card">
        <h2 class="text-sm font-bold text-gray-700 mb-4">Post New Announcement</h2>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-xs
                        rounded-lg px-3 py-2 mb-4 font-medium">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('teacher.announcements.store') }}">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                <div class="sm:col-span-2">
                    <label class="field-label">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}"
                        placeholder="Announcement title" required
                        class="field-input">
                </div>
                <div>
                    <label class="field-label">Audience</label>
                    <select name="audience" class="field-input">
                        <option value="all">All</option>
                        <option value="students">Students only</option>
                        <option value="parents">Parents only</option>
                      
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="field-label">Message</label>
                <textarea name="body" placeholder="Write your announcement..."
                    required class="field-input">{{ old('body') }}</textarea>
            </div>

            <button type="submit" class="post-btn">
                Post Announcement
            </button>
        </form>
    </div>

    {{-- Announcements List --}}
    <div class="page-card">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-sm font-bold text-gray-700">Posted Announcements</h2>
            <span class="text-xs text-gray-400">{{ $announcements->total() }} total</span>
        </div>

        @forelse($announcements as $announcement)
        <div class="announce-item">
            <div class="flex justify-between items-start gap-4">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800">
                        {{ $announcement->title }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                        {{ $announcement->body }}
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                        {{ ucfirst($announcement->audience) }}
                    </span>
                    <span class="text-xs text-gray-400">
                        {{ $announcement->created_at->diffForHumans() }}
                    </span>
                </div>
            </div>
        </div>
        @empty
        <div class="py-14 text-center">
            <div class="w-14 h-14 bg-gray-50 rounded-full mx-auto mb-4"
                style="display:flex;align-items:center;justify-content:center;">
                <svg width="24" height="24" fill="none" stroke="#d1d5db"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18
                           16h2a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11
                           5.882A2 2 0 0112.83 4h.842a2 2 0 011.995
                           1.858L17 16H8.343M11 5.882L8.343 16"/>
                </svg>
            </div>
            <p class="text-gray-400 text-sm font-medium">No announcements yet.</p>
            <p class="text-gray-300 text-xs mt-1">Post your first announcement above.</p>
        </div>
        @endforelse
    </div>

    @if($announcements->count())
    <div class="mt-4">{{ $announcements->links() }}</div>
    @endif
</div>
@endsection