@extends('layouts.app')
@section('title', 'My Grades')

@section('sidebar')
    <a href="{{ route('student.dashboard') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">Dashboard</a>
    <a href="{{ route('student.modules') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">Learning Modules</a>
    <a href="{{ route('student.quizzes') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">Quizzes</a>
    <a href="{{ route('student.grades') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm bg-blue-50 text-blue-700 font-medium">My Grades</a>
    <a href="{{ route('student.messages') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">Messages</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">My Grades</h1>
        <p class="text-sm text-gray-500 mt-1">Academic performance overview</p>
    </div>

    @forelse($grades->groupBy('subject_id') as $subjectId => $subjectGrades)
    @php $subject = $subjectGrades->first()->subject; @endphp
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
        <h2 class="text-sm font-semibold text-gray-800 mb-3">
            {{ $subject->name }}
            <span class="text-xs text-gray-400 font-normal ml-2">{{ $subject->code }}</span>
        </h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="text-left pb-2">Quarter</th>
                        <th class="text-left pb-2">Written Works</th>
                        <th class="text-left pb-2">Performance</th>
                        <th class="text-left pb-2">Assessment</th>
                        <th class="text-left pb-2">Final Grade</th>
                        <th class="text-left pb-2">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($subjectGrades as $grade)
                    <tr>
                        <td class="py-2 font-medium text-gray-700">{{ $grade->quarter }}</td>
                        <td class="py-2 text-gray-600">{{ $grade->written_works ?? '—' }}</td>
                        <td class="py-2 text-gray-600">{{ $grade->performance_tasks ?? '—' }}</td>
                        <td class="py-2 text-gray-600">{{ $grade->quarterly_assessment ?? '—' }}</td>
                        <td class="py-2 font-bold
                            {{ $grade->final_grade >= 75 ? 'text-green-600' : 'text-red-500' }}">
                            {{ $grade->final_grade ?? '—' }}
                        </td>
                        <td class="py-2">
                            @if($grade->remarks)
                            <span class="px-2 py-0.5 rounded-full text-xs
                                {{ $grade->isPassed() ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $grade->remarks }}
                            </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
        <p class="text-gray-400 text-sm">No grades recorded yet.</p>
    </div>
    @endforelse
</div>
@endsection