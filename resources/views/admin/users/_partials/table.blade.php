{{-- ════════════════════════════════
     STUDENTS TAB
════════════════════════════════ --}}
@if($tab === 'students')

@php
    $activeGrade     = request('grade');
    $activeSectionId = request('section_id');
    $gradeList       = [7, 8, 9, 10, 11, 12];
@endphp

{{-- grade strip --}}
<div class="um-grade-strip" role="group" aria-label="Filter by grade level">
    <span class="um-grade-strip-label">Grade</span>
    <a href="{{ route('admin.users.index', array_merge(request()->except(['grade','section_id']), ['tab'=>'students'])) }}"
       class="um-grade-btn {{ !$activeGrade ? 'active' : '' }}">
        All
    </a>
    @foreach($gradeList as $g)
    <a href="{{ route('admin.users.index', array_merge(request()->except(['section_id']), ['tab'=>'students','grade'=>$g])) }}"
       class="um-grade-btn {{ (string)$activeGrade === (string)$g ? 'active' : '' }}">
        Gr. {{ $g }}
    </a>
    @endforeach
</div>

{{-- section strip --}}
@if($activeGrade && isset($sectionsByGrade[$activeGrade]) && $sectionsByGrade[$activeGrade]->count())
<div class="um-section-strip" role="group" aria-label="Filter by section">
    <span class="um-section-strip-label">Section</span>
    <a href="{{ route('admin.users.index', array_merge(request()->except(['section_id']), ['tab'=>'students','grade'=>$activeGrade])) }}"
       class="um-section-btn {{ !$activeSectionId ? 'active' : '' }}">
        All Sections
    </a>
    @foreach($sectionsByGrade[$activeGrade] as $sec)
    <a href="{{ route('admin.users.index', array_merge(request()->all(), ['tab'=>'students','grade'=>$activeGrade,'section_id'=>$sec->id])) }}"
       class="um-section-btn {{ (string)$activeSectionId === (string)$sec->id ? 'active' : '' }}">
        {{ $sec->name }}
    </a>
    @endforeach
</div>
@endif

{{-- filter pills --}}
@if($activeGrade || $activeSectionId)
<div class="um-active-filter-bar">
    @if($activeGrade)
    <span class="um-filter-pill">
        Grade {{ $activeGrade }}
        <a href="{{ route('admin.users.index', array_merge(request()->except(['grade','section_id']), ['tab'=>'students'])) }}"
           title="Remove grade filter">×</a>
    </span>
    @endif
    @if($activeSectionId && isset($sectionsByGrade[$activeGrade]))
        @php $activeSection = $sectionsByGrade[$activeGrade]->firstWhere('id', $activeSectionId); @endphp
        @if($activeSection)
        <span class="um-filter-pill">
            Section: {{ $activeSection->name }}
            <a href="{{ route('admin.users.index', array_merge(request()->except(['section_id']), ['tab'=>'students','grade'=>$activeGrade])) }}"
               title="Remove section filter">×</a>
        </span>
        @endif
    @endif
</div>
@endif

{{-- students table --}}
<div class="um-card">
    <div style="overflow-x:auto;">
        <table class="um-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Type</th>
                    <th>Section</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th style="text-align:right; padding-right:1.5rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <div class="um-avatar" style="background:#0d9488;">
                                {{ strtoupper(substr($student->name, 0, 1)) }}
                            </div>
                            <div>
                                <p style="font-weight:600; font-size:13px; color:var(--slate-900); margin:0;">{{ $student->name }}</p>
                                <p style="font-size:11.5px; color:var(--slate-500); margin:0;">{{ $student->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($student->studentProfile)
                            <span class="um-chip">{{ ucfirst($student->studentProfile->student_type) }}</span>
                        @else
                            <span style="color:var(--slate-400); font-size:12px;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($student->section)
                            <div>
                                <p style="font-size:13px; font-weight:500; color:var(--um-blue-600); margin:0;">{{ $student->section->name }}</p>
                                <p style="font-size:10px; color:var(--slate-500); margin:0; text-transform:uppercase; letter-spacing:.04em;">Gr. {{ $student->grade_level }}</p>
                            </div>
                        @else
                            <span style="color:var(--slate-400); font-size:12px; font-style:italic;">Not enrolled</span>
                        @endif
                    </td>
                    <td>
                        <span class="um-badge um-badge-{{ $student->status }}">
                            {{ ucfirst($student->status) }}
                        </span>
                    </td>
                    <td style="font-size:12px; color:var(--slate-500); font-family:monospace;">
                        {{ $student->created_at->format('M d, Y') }}
                    </td>
                    <td>
                        <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.4rem; padding-right:0.5rem;">
                            <a href="{{ route('admin.users.show', $student) }}" class="um-action-btn um-btn-view" title="View">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @if($student->status === 'pending')
                                <form method="POST" action="{{ route('admin.users.approve', $student) }}" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="um-action-btn um-btn-approve" title="Approve">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.reject', $student) }}" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="um-action-btn um-btn-reject" title="Reject">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="um-empty">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"/>
                            </svg>
                            No students found.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="um-pagination">{{ $students->withQueryString()->links() }}</div>
</div>
@endif

{{-- ════════════════════════════════
     TEACHERS TAB
════════════════════════════════ --}}
@if($tab === 'teachers')
<div style="margin-bottom:1rem;">
    <a href="{{ route('admin.users.create-teacher') }}" class="um-btn-add">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Add Teacher
    </a>
</div>
<div class="um-card">
    <div style="overflow-x:auto;">
        <table class="um-table">
            <thead>
                <tr>
                    <th>Teacher</th>
                    <th>Employee ID</th>
                    <th>Assigned Sections</th>
                    <th>Subject Load</th>
                    <th>Status</th>
                    <th style="text-align:right; padding-right:1.5rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teachers as $teacher)
                @php
                    $assignedSections = $teacher->teacherSubjects->unique('section_id');
                    $subjectCount = $teacher->teacherSubjects->pluck('subject_id')->unique()->count();
                @endphp
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <div class="um-avatar" style="background:#2563eb;">
                                {{ strtoupper(substr($teacher->name, 0, 1)) }}
                            </div>
                            <div>
                                <p style="font-weight:600; font-size:13px; color:var(--slate-900); margin:0;">{{ $teacher->name }}</p>
                                <p style="font-size:11.5px; color:var(--slate-500); margin:0;">{{ $teacher->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:12px; font-family:monospace; color:var(--slate-700);">
                        {{ $teacher->employee_id ?? '—' }}
                    </td>
                    <td>
                        @if($assignedSections->count())
                            <div style="display:flex; flex-wrap:wrap; gap:0.3rem;">
                                @foreach($assignedSections as $ts)
                                    @if($ts->section)
                                        <span class="um-chip">{{ $ts->section->name }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <span style="color:var(--slate-400); font-size:12px;">No sections</span>
                        @endif
                    </td>
                    <td>
                        <p style="font-size:14px; font-weight:700; color:var(--um-purple-500); margin:0;">{{ $subjectCount }}</p>
                        <p style="font-size:10px; text-transform:uppercase; letter-spacing:.05em; color:var(--slate-500); margin:0;">Subjects</p>
                    </td>
                    <td>
                        <span class="um-badge um-badge-{{ $teacher->status }}">
                            {{ ucfirst($teacher->status) }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.4rem; padding-right:0.5rem;">
                            <a href="{{ route('admin.users.show', $teacher) }}" class="um-action-btn um-btn-view" title="View Profile">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="um-empty">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            No teachers found.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="um-pagination">{{ $teachers->withQueryString()->links() }}</div>
</div>
@endif



{{-- ════════════════════════════════
     PARENTS TAB
════════════════════════════════ --}}
@if($tab === 'parents')
<div class="um-card">
    <div style="overflow-x:auto;">
        <table class="um-table">
            <thead>
                <tr>
                    <th>Parent</th>
                    <th>Contact</th>
                    <th>Children</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th style="text-align:right; padding-right:1.5rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($parents as $parent)
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <div class="um-avatar" style="background:#d97706;">
                                {{ strtoupper(substr($parent->name, 0, 1)) }}
                            </div>
                            <div>
                                <p style="font-weight:600; font-size:13px; color:var(--slate-900); margin:0;">{{ $parent->name }}</p>
                                <p style="font-size:11.5px; color:var(--slate-500); margin:0;">{{ $parent->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:12px; font-family:monospace; color:var(--slate-700);">
                        {{ $parent->contact_number ?? '—' }}
                    </td>
                    <td>
                        @if($parent->children->count())
                            <div style="display:flex; flex-wrap:wrap; gap:0.3rem;">
                                @foreach($parent->children as $child)
                                    <span class="um-chip">{{ $child->name }}</span>
                                @endforeach
                            </div>
                        @else
                            <span style="color:var(--slate-400); font-size:12px; font-style:italic;">No children linked</span>
                        @endif
                    </td>
                    <td>
                        <span class="um-badge um-badge-{{ $parent->status }}">
                            {{ ucfirst($parent->status) }}
                        </span>
                    </td>
                    <td style="font-size:12px; color:var(--slate-500); font-family:monospace;">
                        {{ $parent->created_at->format('M d, Y') }}
                    </td>
                    <td>
                        <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.4rem; padding-right:0.5rem;">
                            <a href="{{ route('admin.users.show', $parent) }}" class="um-action-btn um-btn-view" title="View Profile">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @if($parent->status === 'pending')
                                <form method="POST" action="{{ route('admin.users.approve', $parent) }}" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="um-action-btn um-btn-approve" title="Approve">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.reject', $parent) }}" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="um-action-btn um-btn-reject" title="Reject">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="um-empty">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            No parents found.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="um-pagination">{{ $parents->withQueryString()->links() }}</div>
</div>
@endif