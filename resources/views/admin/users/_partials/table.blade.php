{{--
    User tables — the ONLY copy. Rendered on first load (included by index.blade.php)
    and returned as HTML for AJAX tab/filter/page changes (UserManagementController@index).
--}}
@php
    $activeGrade     = request('grade');
    $activeSectionId = request('section_id');
    $gradeList       = [7, 8, 9, 10, 11, 12];
    $palette         = ['#2563eb', '#0d9488', '#9333ea', '#ea580c', '#0891b2', '#db2777', '#4f46e5', '#16a34a'];
    $avatarColor     = fn ($id) => $palette[((int) $id) % count($palette)];
    $initials        = function ($name) {
        $parts = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);
        $first = mb_substr($parts[0] ?? '?', 0, 1);
        $last  = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
        return mb_strtoupper($first . $last);
    };
    $typeLabel   = ['new' => 'New', 'old' => 'Returning', 'transfer' => 'Transferee'];
    $hasFilters  = request('search') || request('status');
@endphp

{{-- ════════════════════ STUDENTS ════════════════════ --}}
@if($tab === 'students')

    <div class="um-chips" role="group" aria-label="Filter by grade level">
        <span class="um-chips-label">Grade</span>
        <a href="{{ route('admin.users.index', array_merge(request()->except(['grade','section_id','student_page']), ['tab'=>'students'])) }}"
           class="um-chip-btn js-grade {{ !$activeGrade ? 'on' : '' }}">All</a>
        @foreach($gradeList as $g)
            <a href="{{ route('admin.users.index', array_merge(request()->except(['section_id','student_page']), ['tab'=>'students','grade'=>$g])) }}"
               class="um-chip-btn js-grade {{ (string) $activeGrade === (string) $g ? 'on' : '' }}">Grade {{ $g }}</a>
        @endforeach
    </div>

    @if($activeGrade && isset($sectionsByGrade[$activeGrade]) && $sectionsByGrade[$activeGrade]->count())
        <div class="um-chips" role="group" aria-label="Filter by section">
            <span class="um-chips-label">Section</span>
            <a href="{{ route('admin.users.index', array_merge(request()->except(['section_id','student_page']), ['tab'=>'students','grade'=>$activeGrade])) }}"
               class="um-chip-btn sm js-section {{ !$activeSectionId ? 'on' : '' }}">All sections</a>
            @foreach($sectionsByGrade[$activeGrade] as $sec)
                <a href="{{ route('admin.users.index', array_merge(request()->except(['student_page']), ['tab'=>'students','grade'=>$activeGrade,'section_id'=>$sec->id])) }}"
                   class="um-chip-btn sm js-section {{ (string) $activeSectionId === (string) $sec->id ? 'on' : '' }}">{{ $sec->name }}</a>
            @endforeach
        </div>
    @endif

    <div class="um-card">
        <div class="um-scroll">
            <table class="um-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>LRN</th>
                        <th>Grade &amp; Section</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="r">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td>
                                <div class="um-person">
                                    <div class="um-avatar" style="background:{{ $avatarColor($student->id) }};">{{ $initials($student->name) }}</div>
                                    <div class="um-person-txt">
                                        <div class="um-name">{{ $student->name }}</div>
                                        <div class="um-email">{{ $student->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($student->lrn)
                                    <span class="um-mono">{{ $student->lrn }}</span>
                                @else
                                    <span class="um-dim">—</span>
                                @endif
                            </td>
                            <td>
                                @if($student->section)
                                    <div class="um-strong">{{ $student->section->name }}</div>
                                    <div class="um-sub">Grade {{ $student->grade_level }}</div>
                                @elseif($student->grade_level)
                                    <div class="um-strong">Grade {{ $student->grade_level }}</div>
                                    <div class="um-sub um-warn-txt">Not enrolled yet</div>
                                @else
                                    <span class="um-dim">Not enrolled yet</span>
                                @endif
                            </td>
                            <td>
                                @if($student->studentProfile?->student_type)
                                    <span class="um-tag">{{ $typeLabel[$student->studentProfile->student_type] ?? ucfirst($student->studentProfile->student_type) }}</span>
                                @else
                                    <span class="um-dim">—</span>
                                @endif
                            </td>
                            <td><span class="um-badge um-badge-{{ $student->status }}">{{ ucfirst($student->status) }}</span></td>
                            <td class="um-date">{{ $student->created_at?->format('M j, Y') }}</td>
                            <td class="r">
                                @include('admin.users._partials.row-actions', ['u' => $student])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">
                            <div class="um-empty">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m-6-9.5V16c3 2 9 2 12 0v-5.5"/></svg>
                                <b>{{ ($hasFilters || $activeGrade) ? 'No students match your filters' : 'No students yet' }}</b>
                                <span>{{ ($hasFilters || $activeGrade) ? 'Try a different search, status, or grade.' : 'Students appear here once they activate their account with their LRN.' }}</span>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admin.users._partials.pager', ['p' => $students])
    </div>
@endif

{{-- ════════════════════ TEACHERS ════════════════════ --}}
@if($tab === 'teachers')
    <div class="um-card">
        <div class="um-scroll">
            <table class="um-table">
                <thead>
                    <tr>
                        <th>Teacher</th>
                        <th>Employee ID</th>
                        <th>Advisory</th>
                        <th>Teaching</th>
                        <th>Status</th>
                        <th class="r">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $teacher)
                        @php
                            $assigned     = $teacher->teacherSubjects->filter(fn ($ts) => $ts->section)->unique('section_id');
                            $subjectCount = $teacher->teacherSubjects->pluck('subject_id')->unique()->count();
                            $advisory     = $advisories[$teacher->id] ?? collect();
                        @endphp
                        <tr>
                            <td>
                                <div class="um-person">
                                    <div class="um-avatar" style="background:{{ $avatarColor($teacher->id) }};">{{ $initials($teacher->name) }}</div>
                                    <div class="um-person-txt">
                                        <div class="um-name">{{ $teacher->name }}</div>
                                        <div class="um-email">{{ $teacher->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($teacher->employee_id)
                                    <span class="um-mono">{{ $teacher->employee_id }}</span>
                                @else
                                    <span class="um-dim">—</span>
                                @endif
                            </td>
                            <td>
                                @forelse($advisory as $sec)
                                    <span class="um-tag um-tag-blue">Grade {{ $sec->grade_level }} – {{ $sec->name }}</span>
                                @empty
                                    <span class="um-dim">None</span>
                                @endforelse
                            </td>
                            <td>
                                @if($subjectCount)
                                    <div class="um-strong">{{ $subjectCount }} {{ Str::plural('subject', $subjectCount) }}</div>
                                    <div class="um-sub">
                                        {{ $assigned->take(3)->map(fn ($ts) => $ts->section->name)->implode(', ') }}{{ $assigned->count() > 3 ? ' +' . ($assigned->count() - 3) . ' more' : '' }}
                                    </div>
                                @else
                                    <span class="um-dim">No classes yet</span>
                                @endif
                            </td>
                            <td><span class="um-badge um-badge-{{ $teacher->status }}">{{ ucfirst($teacher->status) }}</span></td>
                            <td class="r">
                                @include('admin.users._partials.row-actions', ['u' => $teacher])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">
                            <div class="um-empty">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <b>{{ $hasFilters ? 'No teachers match your filters' : 'No teachers yet' }}</b>
                                <span>{{ $hasFilters ? 'Try a different search or status.' : 'Use “Add Teacher” to create the first teacher account.' }}</span>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admin.users._partials.pager', ['p' => $teachers])
    </div>
@endif

{{-- ════════════════════ PARENTS ════════════════════ --}}
@if($tab === 'parents')
    <div class="um-card">
        <div class="um-scroll">
            <table class="um-table">
                <thead>
                    <tr>
                        <th>Parent</th>
                        <th>Contact</th>
                        <th>Linked child</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="r">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parents as $parent)
                        <tr>
                            <td>
                                <div class="um-person">
                                    <div class="um-avatar" style="background:{{ $avatarColor($parent->id) }};">{{ $initials($parent->name) }}</div>
                                    <div class="um-person-txt">
                                        <div class="um-name">{{ $parent->name }}</div>
                                        <div class="um-email">{{ $parent->email ?: 'No email' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($parent->contact_number)
                                    <span class="um-mono">{{ $parent->contact_number }}</span>
                                @else
                                    <span class="um-dim">—</span>
                                @endif
                            </td>
                            <td>
                                @forelse($parent->children as $child)
                                    <div class="um-strong">{{ $child->name }}</div>
                                    <div class="um-sub">{{ $child->section ? 'Grade ' . $child->grade_level . ' – ' . $child->section->name : 'Not enrolled yet' }}</div>
                                @empty
                                    <span class="um-dim um-warn-txt">No child linked</span>
                                @endforelse
                            </td>
                            <td><span class="um-badge um-badge-{{ $parent->status }}">{{ ucfirst($parent->status) }}</span></td>
                            <td class="um-date">{{ $parent->created_at?->format('M j, Y') }}</td>
                            <td class="r">
                                @include('admin.users._partials.row-actions', ['u' => $parent])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">
                            <div class="um-empty">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <b>{{ $hasFilters ? 'No parents match your filters' : 'No parents yet' }}</b>
                                <span>{{ $hasFilters ? 'Try a different search or status.' : 'Parents appear here after linking with their child’s one-time code.' }}</span>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admin.users._partials.pager', ['p' => $parents])
    </div>
@endif
