@extends('layouts.app')
@section('title', 'Quizzes')

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
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Quizzes</h1>
        <p class="text-sm text-gray-500 mt-1">Take quizzes assigned by your teachers.</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
        <div class="w-16 h-16 bg-purple-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0
                       00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2
                       2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-700 mb-2">No quizzes available yet</h2>
        <p class="text-sm text-gray-400">
            Your teacher has not published any quizzes yet.<br>
            Check back later.
        </p>
    </div>
</div>
@endsection