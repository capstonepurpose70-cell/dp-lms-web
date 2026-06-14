@extends('layouts.app')
@section('title', 'Messages')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6" style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Messages</h1>
        <p class="text-sm text-gray-400 mt-1">Send a message to your teachers and view replies.</p>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl bg-green-50 border border-green-200 text-green-700 px-4 py-3 text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    {{-- Send a message --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6" style="box-shadow:0 2px 16px rgba(0,0,0,0.05);">
        <h2 class="text-base font-semibold text-gray-700 mb-4">Send a message</h2>

        <form action="{{ route('student.messages.store') }}" method="POST" novalidate>
            @csrf
            <div class="mb-4">
                <label for="teacher_id" class="block text-sm font-medium text-gray-600 mb-1.5">To</label>
                <select name="teacher_id" id="teacher_id" required
                    class="w-full rounded-xl border @error('teacher_id') border-red-300 @else border-gray-200 @enderror bg-white px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition">
                    <option value="">Select a teacher…</option>
                    @foreach(($teachers ?? []) as $teacher)
                        <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
                @error('teacher_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                @if(($teachers ?? collect())->isEmpty())
                    <p class="text-amber-600 text-xs mt-1">No teachers yet. Make sure you are enrolled in a section.</p>
                @endif
            </div>

            <div class="mb-4">
                <label for="body" class="block text-sm font-medium text-gray-600 mb-1.5">Message</label>
                <textarea name="body" id="body" rows="3" required
                    class="w-full rounded-xl border @error('body') border-red-300 @else border-gray-200 @enderror bg-white px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition"
                    placeholder="Type your message...">{{ old('body') }}</textarea>
                @error('body')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition">
                Send Message
            </button>
        </form>
    </div>

    {{-- Conversation history --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-6" style="box-shadow:0 2px 16px rgba(0,0,0,0.05);">
        <h2 class="text-base font-semibold text-gray-700 mb-4">Conversation</h2>

        @forelse(($messages ?? []) as $m)
            @php $mine = $m->sender_id === auth()->id(); @endphp
            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }} mb-3">
                <div class="max-w-[75%] rounded-2xl px-4 py-2.5 {{ $mine ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                    <p class="text-xs font-semibold mb-0.5 {{ $mine ? 'text-blue-100' : 'text-gray-500' }}">
                        {{ $mine ? 'You' : ($m->sender->name ?? 'Teacher') }}
                    </p>
                    <p class="text-sm" style="white-space:pre-wrap;">{{ $m->body }}</p>
                    <p class="text-[10px] mt-1 {{ $mine ? 'text-blue-100' : 'text-gray-400' }}">
                        {{ $m->created_at?->diffForHumans() }}
                    </p>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400 text-center py-8">No messages yet. Send one above to start.</p>
        @endforelse
    </div>

</div>
@endsection