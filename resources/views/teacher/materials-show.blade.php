@extends('layouts.teacher')
@section('title', 'Material Details')

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
    .ms-card {
        background:#fff; border-radius:16px; border:1.5px solid #f1f5f9;
        box-shadow:0 2px 16px rgba(0,0,0,.04);
    }
    .ms-stat-num { font-size:26px; font-weight:800; color:#1e293b; line-height:1; }
    .ms-stat-lbl { font-size:11px; color:#94a3b8; margin-top:5px; text-transform:uppercase; letter-spacing:.04em; }
    .ms-row { display:flex; align-items:center; gap:11px; padding:11px 0; border-bottom:1px solid #f1f5f9; }
    .ms-row:last-child { border-bottom:none; }
    .ms-avatar {
        width:36px; height:36px; border-radius:50%; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
        font-weight:700; font-size:14px; color:#2563eb; background:#eff6ff;
    }
</style>

<div class="max-w-4xl mx-auto">

    <a href="{{ route('teacher.materials.index') }}"
       style="color:#2563eb;font-size:13px;text-decoration:none;font-weight:600;">&larr; Back to materials</a>

    {{-- Material header --}}
    <div class="ms-card mt-3" style="padding:22px;">
        <h1 class="text-xl font-bold text-gray-800">{{ $material->title }}</h1>
        <p class="text-sm text-gray-400 mt-1">
            {{ $material->subject->name ?? '—' }}
            @if($material->quarter) · {{ $material->quarter }} @endif
            @if($material->week) · Week {{ $material->week }} @endif
            · {{ $material->is_published ? 'Published' : 'Draft' }}
        </p>
        @if($material->description)
            <p class="text-sm text-gray-600 mt-3" style="line-height:1.5;">{{ $material->description }}</p>
        @endif
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-3 mt-4">
        <div class="ms-card" style="padding:18px;text-align:center;">
            <div class="ms-stat-num">{{ $material->views_count }}</div>
            <div class="ms-stat-lbl">👁 Viewed</div>
        </div>
        <div class="ms-card" style="padding:18px;text-align:center;">
            <div class="ms-stat-num">{{ $material->likes_count }}</div>
            <div class="ms-stat-lbl">❤️ Hearts</div>
        </div>
        <div class="ms-card" style="padding:18px;text-align:center;">
            <div class="ms-stat-num">{{ $material->comments_count }}</div>
            <div class="ms-stat-lbl">💬 Comments</div>
        </div>
    </div>

    {{-- Who viewed --}}
    <div class="ms-card mt-4" style="padding:20px;">
        <h2 class="text-base font-bold text-gray-800 mb-2">Who viewed this ({{ $material->views_count }})</h2>
        @forelse($viewers as $v)
            <div class="ms-row">
                <div class="ms-avatar">{{ strtoupper(substr($v->user->name ?? 'S', 0, 1)) }}</div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $v->user->name ?? 'Student' }}</p>
                </div>
                <span class="text-xs text-gray-400">{{ $v->created_at->diffForHumans() }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-400 py-3">No students have viewed this yet.</p>
        @endforelse
    </div>

    {{-- Comments --}}
    <div class="ms-card mt-4 mb-8" style="padding:20px;">
        <h2 class="text-base font-bold text-gray-800 mb-2">Comments ({{ $material->comments_count }})</h2>
        @forelse($comments as $c)
            <div class="ms-row" style="align-items:flex-start;">
                <div class="ms-avatar" style="color:#16a34a;background:#dcfce7;">
                    {{ strtoupper(substr($c->user->name ?? 'S', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <p class="text-sm font-semibold text-gray-800">{{ $c->user->name ?? 'Student' }}</p>
                        <span class="text-xs text-gray-400">{{ $c->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-gray-600 mt-0.5">{{ $c->body }}</p>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400 py-3">No comments yet.</p>
        @endforelse
    </div>

</div>
@endsection