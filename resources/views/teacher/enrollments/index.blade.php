@extends('layouts.teacher')
@section('title', 'Enrollments')

@section('content')
<style>
    .en-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
    .en-tab { display:inline-flex; align-items:center; min-height:40px; padding:0 16px; border-radius:999px; border:1px solid var(--border);
              background:var(--surface); color:var(--text-2); font-size:13px; font-weight:700;
              text-decoration:none; }
    .en-tab.active { background:#2563EB; border-color:#2563EB; color:#fff; }

    .en-note { background:#FFFBEB; border:1px solid #FDE68A; color:#92400E;
               padding:11px 14px; border-radius:10px; font-size:12.5px; margin-bottom:14px; line-height:1.5; }
    .en-ok { background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46;
             padding:11px 14px; border-radius:10px; font-size:13px; margin-bottom:14px; }

    .en-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--r-lg); box-shadow:var(--shadow-sm); overflow:hidden; }
    table.en { width:100%; border-collapse:collapse; font-size:13.5px; }
    .en th { text-align:left; font-size:12.5px; font-weight:700; color:var(--text-2); padding:11px 14px; border-bottom:1px solid var(--border); background:#F8FAFF; }
    .en td { padding:12px 14px; border-bottom:1px solid var(--border); color:var(--text-1); }
    .en tr:last-child td { border-bottom:0; }
    .en .muted { font-size:12.5px; color:var(--text-2); }

    @media (max-width: 768px) { .en-card { overflow-x:auto; } table.en { min-width:640px; } }
</style>

<div class="tp-page">
    <div class="tp-head">
        <div>
            <h1 class="tp-title">Enrollments</h1>
            <p class="tp-sub">Review enrollment forms and place each student in your advisory section.</p>
        </div>
    </div>

    @unless ($hasAdvisory)
        <div class="en-note">
            ⚠️ Only section <b>advisers</b> review enrollment requests, and you are not assigned to a section yet.
            Please ask the admin to assign you as a section adviser.
        </div>
    @endunless

    <nav class="en-tabs" aria-label="Enrollment status">
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Returned'] as $key => $label)
            <a class="en-tab {{ $tab === $key ? 'active' : '' }}" @if ($tab === $key) aria-current="page" @endif
               href="{{ route('teacher.enrollments.index', ['tab' => $key]) }}">
                {{ $label }} ({{ $counts[$key] }})
            </a>
        @endforeach
    </nav>

    @php $types = ['new' => ['info', 'New'], 'old' => ['muted', 'Returning'], 'transfer' => ['warn', 'Transferee']]; @endphp
    <div class="en-card">
        @if ($enrollments->isEmpty())
            <div class="tp-empty">
                @if ($tab === 'pending')
                    <b>Nothing to review</b>
                    New enrollment forms from students in your grade level appear here.
                @else
                    <b>No {{ $tab === 'approved' ? 'approved' : 'returned' }} forms yet</b>
                @endif
            </div>
        @else
            <table class="en">
                <thead>
                    <tr>
                        <th scope="col">Student</th>
                        <th scope="col">LRN</th>
                        <th scope="col">Grade</th>
                        <th scope="col">Type</th>
                        <th scope="col">Submitted</th>
                        <th scope="col"><span class="sr-only">Action</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($enrollments as $e)
                        @php [$tCls, $tLbl] = $types[$e->student_type] ?? ['muted', ucfirst((string) $e->student_type)]; @endphp
                        <tr>
                            <td>
                                <b>{{ $e->full_name ?? $e->student->name }}</b><br>
                                <span class="muted">{{ $e->student->email ?? '' }}</span>
                            </td>
                            <td class="muted">{{ $e->student->lrn ?? '—' }}</td>
                            <td>Grade {{ $e->grade_level }}</td>
                            <td><span class="tp-pill {{ $tCls }}">{{ $tLbl }}</span></td>
                            <td class="muted">{{ $e->created_at->format('M j, Y') }}</td>
                            <td style="text-align:right;">
                                <a class="tp-btn sm {{ $tab === 'pending' ? '' : 'ghost' }}" href="{{ route('teacher.enrollments.show', $e->id) }}"
                                   aria-label="{{ $tab === 'pending' ? 'Review' : 'View' }} the form of {{ $e->full_name ?? $e->student->name }}">
                                    {{ $tab === 'pending' ? 'Review' : 'View' }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if ($enrollments->hasPages())
        <div style="margin-top:14px;">{{ $enrollments->links() }}</div>
    @endif
</div>
@endsection