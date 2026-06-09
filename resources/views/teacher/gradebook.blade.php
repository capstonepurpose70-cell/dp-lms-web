@extends('layouts.teacher')
@section('title', 'Gradebook')

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
        overflow: hidden;
        animation: fadeUp 0.4s cubic-bezier(0.16,1,0.3,1) both;
    }

    @keyframes fadeUp {
        from { opacity:0; transform:translateY(12px); }
        to   { opacity:1; transform:translateY(0); }
    }

    .grade-row { transition: background 0.15s ease; }
    .grade-row:hover { background: #f8faff !important; }

    /* Encode form */
    .gb-field label {
        display:block; font-size:12px; font-weight:600;
        color:#64748b; margin-bottom:4px;
    }
    .gb-field select, .gb-field input {
        width:100%; padding:9px 11px; font-size:14px;
        border:1.5px solid #e2e8f0; border-radius:10px;
        background:#fff; color:#1e293b; outline:none;
        transition:border-color .15s ease, box-shadow .15s ease;
    }
    .gb-field select:focus, .gb-field input:focus {
        border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12);
    }
    .gb-btn {
        background:#2563eb; color:#fff; font-weight:600; font-size:14px;
        padding:10px 22px; border:none; border-radius:10px; cursor:pointer;
        transition:background .15s ease, transform .15s ease;
    }
    .gb-btn:hover { background:#1d4ed8; transform:translateY(-1px); }
    .gb-edit {
        font-size:12px; font-weight:600; color:#2563eb; background:#eff6ff;
        border:none; padding:5px 12px; border-radius:8px; cursor:pointer;
        transition:background .15s ease;
    }
    .gb-edit:hover { background:#dbeafe; }
    .gb-alert {
        padding:11px 16px; border-radius:12px; font-size:14px;
        font-weight:500; margin-bottom:16px;
    }
</style>

<div class="max-w-5xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gradebook</h1>
        <p class="text-sm text-gray-400 mt-1">Manage and encode student grades.</p>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="gb-alert" style="background:#dcfce7;color:#166534;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="gb-alert" style="background:#fee2e2;color:#991b1b;">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="gb-alert" style="background:#fee2e2;color:#991b1b;">{{ $errors->first() }}</div>
    @endif

    {{-- ── Encode / Edit Grade form ─────────────────────────────────── --}}
    <div class="page-card mb-6" id="encodeCard" style="padding:22px;">
        <h2 class="text-base font-bold text-gray-800 mb-1">Encode Grade</h2>
        <p class="text-xs text-gray-400 mb-4">
            Final grade auto-computes: Written Works 25% + Performance Tasks 50% + Quarterly Assessment 25%.
            Saving the same student, subject &amp; quarter updates the existing grade.
        </p>

        <form method="POST" action="{{ route('teacher.gradebook.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="gb-field">
                    <label>Student</label>
                    <select name="student_id" id="f_student" required>
                        <option value="">— Select student —</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}"
                                {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->name }}@if($student->section) — {{ $student->section->name }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="gb-field">
                    <label>Subject</label>
                    <select name="subject_id" id="f_subject" required>
                        <option value="">— Select subject —</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}"
                                {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="gb-field">
                    <label>Quarter</label>
                    <select name="quarter" id="f_quarter" required>
                        @foreach(['Q1','Q2','Q3','Q4'] as $q)
                            <option value="{{ $q }}" {{ old('quarter') == $q ? 'selected' : '' }}>{{ $q }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="gb-field">
                    <label>Written Works (0–100)</label>
                    <input type="number" step="0.01" min="0" max="100"
                        name="written_works" id="f_ww" value="{{ old('written_works') }}" placeholder="0">
                </div>

                <div class="gb-field">
                    <label>Performance Tasks (0–100)</label>
                    <input type="number" step="0.01" min="0" max="100"
                        name="performance_tasks" id="f_pt" value="{{ old('performance_tasks') }}" placeholder="0">
                </div>

                <div class="gb-field">
                    <label>Quarterly Assessment (0–100)</label>
                    <input type="number" step="0.01" min="0" max="100"
                        name="quarterly_assessment" id="f_qa" value="{{ old('quarterly_assessment') }}" placeholder="0">
                </div>
            </div>

            <div class="mt-5 flex justify-end">
                <button type="submit" class="gb-btn">Save Grade</button>
            </div>
        </form>
    </div>

    {{-- ── Grades table (existing, with an Edit action) ─────────────── --}}
    <div class="page-card">
        @if($grades->count())
        <table class="min-w-full text-sm">
            <thead style="background:#f8faff;">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Student</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Subject</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Quarter</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Written Works</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Performance</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Assessment</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Final Grade</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Remarks</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($grades as $grade)
                <tr class="grade-row">
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $grade->student->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $grade->subject->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $grade->quarter }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $grade->written_works ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $grade->performance_tasks ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $grade->quarterly_assessment ?? '—' }}</td>
                    <td class="px-5 py-3 font-bold {{ ($grade->final_grade ?? 0) >= 75 ? 'text-green-600' : 'text-red-500' }}">
                        {{ $grade->final_grade ?? '—' }}
                    </td>
                    <td class="px-5 py-3">
                        @if($grade->remarks)
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $grade->isPassed() ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $grade->remarks }}
                        </span>
                        @else
                        <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <button type="button" class="gb-edit"
                            data-student="{{ $grade->user_id }}"
                            data-subject="{{ $grade->subject_id }}"
                            data-quarter="{{ $grade->quarter }}"
                            data-ww="{{ $grade->written_works }}"
                            data-pt="{{ $grade->performance_tasks }}"
                            data-qa="{{ $grade->quarterly_assessment }}"
                            onclick="fillGradeForm(this)">Edit</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $grades->links() }}
        </div>
        @else
        <div class="py-16 text-center">
            <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4"
                style="display:flex;align-items:center;justify-content:center;">
                <svg width="24" height="24" fill="none" stroke="#d1d5db"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0
                           002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0
                           012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2
                           2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <p class="text-gray-400 text-sm font-medium">No grades recorded yet.</p>
            <p class="text-gray-300 text-xs mt-1">Use the form above to encode a student's grade.</p>
        </div>
        @endif
    </div>
</div>

<script>
    // Pre-fill the encode form when "Edit" is clicked (degrades gracefully).
    function fillGradeForm(btn) {
        var d = btn.dataset;
        document.getElementById('f_student').value = d.student || '';
        document.getElementById('f_subject').value = d.subject || '';
        document.getElementById('f_quarter').value = d.quarter || 'Q1';
        document.getElementById('f_ww').value = d.ww || '';
        document.getElementById('f_pt').value = d.pt || '';
        document.getElementById('f_qa').value = d.qa || '';
        document.getElementById('encodeCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
</script>
@endsection