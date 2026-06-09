@extends('layouts.app')
@section('title', 'Messages')

@section('sidebar')
    <a href="{{ route('student.dashboard') }}"
        class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
        {{ request()->routeIs('student.dashboard') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
        Dashboard
    </a>
    <a href="{{ route('student.modules') }}"
        class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
        {{ request()->routeIs('student.modules') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
        Learning Modules
    </a>
    <a href="{{ route('student.quizzes') }}"
        class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
        {{ request()->routeIs('student.quizzes') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
        Quizzes
    </a>
    <a href="{{ route('student.grades') }}"
        class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
        {{ request()->routeIs('student.grades') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
        My Grades
    </a>
    <a href="{{ route('student.messages') }}"
        class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
        {{ request()->routeIs('student.messages') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
        Messages
    </a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Messages</h1>
            <p class="text-sm text-gray-400 mt-1">Communicate with your teachers.</p>
        </div>
        <span class="hidden sm:inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 bg-gray-50 border border-gray-100 px-3 py-1.5 rounded-full">
            <span class="w-2 h-2 rounded-full bg-green-500"></span> Online
        </span>
    </div>

    {{-- Flash: success --}}
    @if (session('status'))
        <div class="mb-4 flex items-start gap-3 bg-green-50 border border-green-100 text-green-700 text-sm rounded-xl px-4 py-3">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="mt-0.5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    {{-- Flash: error (e.g. ban warning) --}}
    @if (session('error'))
        <div class="mb-4 flex items-start gap-3 bg-red-50 border border-red-100 text-red-700 text-sm rounded-xl px-4 py-3">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="mt-0.5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Policy notice --}}
    <div class="mb-6 flex items-start gap-3 bg-amber-50 border border-amber-100 text-amber-800 text-xs rounded-xl px-4 py-3">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="mt-0.5 shrink-0">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"/>
        </svg>
        <span><strong>Reminder:</strong> Be respectful. Messages na may bad words / offensive language ay automatic <strong>babanned</strong> ang account mo.</span>
    </div>

    {{-- Compose card --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-8 mb-6"
        style="box-shadow: 0 2px 16px rgba(0,0,0,0.05);">

        <h2 class="text-base font-semibold text-gray-700 mb-4">Send a message</h2>

        <form action="{{ route('student.messages.store') }}" method="POST" id="messageForm" novalidate>
            @csrf

            {{-- Recipient --}}
            <div class="mb-4">
                <label for="teacher_id" class="block text-sm font-medium text-gray-600 mb-1.5">To</label>
                <select name="teacher_id" id="teacher_id" required
                    class="w-full rounded-xl border @error('teacher_id') border-red-300 @else border-gray-200 @enderror
                    bg-white px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition">
                    <option value="">Select a teacher…</option>
                    @foreach (($teachers ?? []) as $teacher)
                        <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
                @error('teacher_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Subject --}}
            <div class="mb-4">
                <label for="subject" class="block text-sm font-medium text-gray-600 mb-1.5">Subject</label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}"
                    maxlength="100" required placeholder="e.g. Question about Module 3"
                    class="w-full rounded-xl border @error('subject') border-red-300 @else border-gray-200 @enderror
                    px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition">
                @error('subject')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Body --}}
            <div class="mb-2">
                <label for="body" class="block text-sm font-medium text-gray-600 mb-1.5">Message</label>
                <textarea name="body" id="body" rows="5" required minlength="5" maxlength="1000"
                    placeholder="Type your message here…"
                    class="w-full rounded-xl border @error('body') border-red-300 @else border-gray-200 @enderror
                    px-3 py-2.5 text-sm text-gray-700 resize-none focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition">{{ old('body') }}</textarea>
                <div class="flex items-center justify-between mt-1">
                    @error('body')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @else
                        <span class="text-xs text-gray-400">Minimum 5 characters.</span>
                    @enderror
                    <span class="text-xs text-gray-400"><span id="charCount">0</span>/1000</span>
                </div>
            </div>

            <div class="flex justify-end mt-4">
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800
                    text-white text-sm font-medium px-5 py-2.5 rounded-xl transition disabled:opacity-50">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                    </svg>
                    Send Message
                </button>
            </div>
        </form>
    </div>

    {{-- Empty state (kept from original, slightly polished) --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center"
        style="box-shadow: 0 2px 16px rgba(0,0,0,0.05);">

        <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg width="28" height="28" fill="none" stroke="#22c55e" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </div>

        <h2 class="text-lg font-semibold text-gray-700 mb-2">No messages yet</h2>
        <p class="text-sm text-gray-400">
            You have no messages at the moment.<br>
            Messages from your teachers will appear here.
        </p>
    </div>
</div>

{{-- Client-side validations (live char count + basic guard) --}}
<script>
    (function () {
        const body = document.getElementById('body');
        const count = document.getElementById('charCount');
        const form = document.getElementById('messageForm');

        if (body && count) {
            const update = () => count.textContent = body.value.length;
            body.addEventListener('input', update);
            update();
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                const teacher = document.getElementById('teacher_id');
                const subject = document.getElementById('subject');
                if (!teacher.value) { e.preventDefault(); teacher.focus(); }
                else if (subject.value.trim().length < 3) { e.preventDefault(); subject.focus(); }
                else if (body.value.trim().length < 5) { e.preventDefault(); body.focus(); }
            });
        }
    })();
</script>
@endsection