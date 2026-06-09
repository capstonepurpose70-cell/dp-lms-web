@extends('layouts.app')
@section('title', "Child's Records")

@section('content')
<style>
    .fade-up { animation: fadeUp 0.4s cubic-bezier(0.16,1,0.3,1) both; }
    @keyframes fadeUp { from { opacity:0; transform:translateY(12px);} to { opacity:1; transform:translateY(0);} }

    .info-card {
        background:#fff; border-radius:14px; border:1.5px solid #f1f5f9;
        box-shadow:0 2px 12px rgba(0,0,0,0.04);
    }
    .cr-avatar {
        width:52px; height:52px; border-radius:14px; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
        font-size:20px; font-weight:800; color:#fff;
        background:linear-gradient(135deg,#d97706,#b45309);
    }
    .cr-badge {
        display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:600;
        padding:4px 10px; border-radius:999px;
    }
    .cr-badge.enrolled { background:#dcfce7; color:#15803d; }
    .cr-badge.pending  { background:#fef3c7; color:#b45309; }
    .cr-badge.none     { background:#f1f5f9; color:#64748b; }

    .subj-card { border:1px solid #f1f5f9; border-radius:12px; overflow:hidden; }
    .subj-head {
        background:#f8fafc; padding:10px 14px; font-weight:700; font-size:14px; color:#1e293b;
        display:flex; justify-content:space-between; align-items:center;
    }
    .cr-table { width:100%; border-collapse:collapse; font-size:13px; }
    .cr-table th {
        text-align:left; padding:8px 14px; color:#94a3b8; font-weight:600;
        font-size:11px; text-transform:uppercase; letter-spacing:.03em; border-bottom:1px solid #f1f5f9;
    }
    .cr-table td { padding:9px 14px; border-bottom:1px solid #f6f7f9; color:#334155; }
    .cr-table tr:last-child td { border-bottom:none; }
    .grade-final { font-weight:800; }
    .pass { color:#16a34a; } .fail { color:#dc2626; }
</style>

<div class="max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="mb-6 fade-up flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Child's Records</h1>
            <p class="text-sm text-gray-400 mt-1">Grades and enrollment details of your linked children</p>
        </div>
        <a href="{{ route('parent.dashboard') }}"
           class="text-sm font-semibold text-amber-700 hover:text-amber-800">&larr; Back to Dashboard</a>
    </div>

    @forelse($children as $child)
    @php
        $enroll = $child->studentEnrollment;
        $status = $enroll->status ?? null;
        $allGrades = $child->grades->whereNotNull('final_grade');
        $avg = $allGrades->avg('final_grade');
    @endphp

    <div class="info-card p-6 mb-6 fade-up">

        {{-- Child header --}}
        <div class="flex items-center gap-4 mb-5 flex-wrap">
            <div class="cr-avatar">{{ strtoupper(substr($child->name, 0, 1)) }}</div>
            <div class="flex-1 min-w-0">
                <div class="text-lg font-bold text-gray-800">{{ $child->name }}</div>
                <div class="text-sm text-gray-400">
                    @if($child->grade_level) Grade {{ $child->grade_level }} @endif
                    @if($child->section) &middot; {{ $child->section->name }} @endif
                </div>
            </div>
            @if($status === 'enrolled')
                <span class="cr-badge enrolled">Enrolled</span>
            @elseif($status)
                <span class="cr-badge pending">{{ ucfirst($status) }}</span>
            @else
                <span class="cr-badge none">Not enrolled</span>
            @endif
            @if(!is_null($avg))
                <span class="cr-badge {{ $avg >= 75 ? 'enrolled' : 'pending' }}">
                    Average: {{ number_format($avg, 2) }}
                </span>
            @endif
        </div>

        {{-- Grades grouped by subject --}}
        @if($child->grades->count())
            <div class="space-y-4">
                @foreach($child->grades->groupBy(fn($g) => $g->subject->name ?? 'Unknown subject') as $subjectName => $subjectGrades)
                <div class="subj-card">
                    <div class="subj-head">
                        <span>{{ $subjectName }}</span>
                    </div>
                    <table class="cr-table">
                        <thead>
                            <tr>
                                <th>Quarter</th>
                                <th>Written (25%)</th>
                                <th>Perf. (50%)</th>
                                <th>Quarterly (25%)</th>
                                <th>Final</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjectGrades->sortBy('quarter') as $g)
                            <tr>
                                <td>{{ $g->quarter ?? '—' }}</td>
                                <td>{{ !is_null($g->written_works) ? number_format($g->written_works, 2) : '—' }}</td>
                                <td>{{ !is_null($g->performance_tasks) ? number_format($g->performance_tasks, 2) : '—' }}</td>
                                <td>{{ !is_null($g->quarterly_assessment) ? number_format($g->quarterly_assessment, 2) : '—' }}</td>
                                <td class="grade-final {{ !is_null($g->final_grade) ? ($g->final_grade >= 75 ? 'pass' : 'fail') : '' }}">
                                    {{ !is_null($g->final_grade) ? number_format($g->final_grade, 2) : '—' }}
                                </td>
                                <td>
                                    @if(is_null($g->final_grade))
                                        <span class="text-gray-400">Pending</span>
                                    @elseif($g->final_grade >= 75)
                                        <span class="pass font-semibold">Passed</span>
                                    @else
                                        <span class="fail font-semibold">Failed</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-400 text-sm">
                No grades recorded yet for this child.
            </div>
        @endif

    </div>
    @empty
    <div class="info-card p-10 text-center fade-up">
        <div class="text-gray-800 font-semibold text-lg mb-1">No children linked to your account yet.</div>
        <p class="text-sm text-gray-400">
            Please contact the school administrator to link your child's account.
        </p>
    </div>
    @endforelse

</div>
@endsection