@extends('layouts.teacher')
@section('title', 'Enrollments')

@section('content')
<style>
    .en-page { animation: en-fade .3s ease both; }
    @keyframes en-fade { from { opacity:0; transform:translateY(8px);} to { opacity:1; transform:none;} }

    .en-head h1 { font-size:22px; font-weight:800; margin:0 0 2px; color:var(--text-1); }
    .en-head p  { margin:0 0 16px; font-size:13px; color:var(--text-2); }

    .en-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
    .en-tab { padding:8px 16px; border-radius:999px; border:1px solid var(--border);
              background:var(--surface); color:var(--text-2); font-size:12.5px; font-weight:700;
              text-decoration:none; }
    .en-tab.active { background:#2563EB; border-color:#2563EB; color:#fff; }

    .en-note { background:#FFFBEB; border:1px solid #FDE68A; color:#92400E;
               padding:11px 14px; border-radius:10px; font-size:12.5px; margin-bottom:14px; line-height:1.5; }
    .en-ok { background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46;
             padding:11px 14px; border-radius:10px; font-size:13px; margin-bottom:14px; }

    .en-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; overflow:hidden; }
    table.en { width:100%; border-collapse:collapse; font-size:13px; }
    .en th { text-align:left; font-size:10.5px; letter-spacing:.5px; text-transform:uppercase;
             color:var(--text-3); padding:11px 14px; border-bottom:1px solid var(--border); background:var(--bg,#F8FAFC); }
    .en td { padding:12px 14px; border-bottom:1px solid var(--border); color:var(--text-1); }
    .en tr:last-child td { border-bottom:0; }
    .en .muted { font-size:11.5px; color:var(--text-2); }

    .badge { display:inline-block; padding:3px 10px; border-radius:999px; font-size:10.5px; font-weight:800; }
    .badge.new { background:#DBEAFE; color:#1E40AF; }
    .badge.old { background:#F3F4F6; color:#374151; }
    .badge.transfer { background:#FEF3C7; color:#92400E; }

    .en-btn { display:inline-block; padding:7px 14px; border-radius:8px; background:#2563EB; color:#fff;
              font-size:12px; font-weight:700; text-decoration:none; }
    .en-empty { text-align:center; padding:40px 14px; color:var(--text-2); font-size:13px; }

    @media (max-width: 768px) { .en-card { overflow-x:auto; } table.en { min-width:640px; } }
</style>

<div class="en-page">
    <div class="en-head">
        <h1>Enrollments</h1>
        <p>Review student enrollment requests and assign them to your advisory section.</p>
    </div>

    @if (session('success'))
        <div class="en-ok">{{ session('success') }}</div>
    @endif

    @unless ($hasAdvisory)
        <div class="en-note">
            ⚠️ You are not yet assigned as an <b>adviser</b> to any section. You can view pending requests,
            but to approve a student you must first have an advisory section. Please ask the admin to
            assign you as a section adviser.
        </div>
    @endunless

    <div class="en-tabs">
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label)
            <a class="en-tab {{ $tab === $key ? 'active' : '' }}"
               href="{{ route('teacher.enrollments.index', ['tab' => $key]) }}">
                {{ $label }} ({{ $counts[$key] }})
            </a>
        @endforeach
    </div>

    <div class="en-card">
        <table class="en">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>LRN</th>
                    <th>Grade</th>
                    <th>Type</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($enrollments as $e)
                    <tr>
                        <td>
                            <b>{{ $e->full_name ?? $e->student->name }}</b><br>
                            <span class="muted">{{ $e->student->email ?? '' }}</span>
                        </td>
                        <td class="muted">{{ $e->student->lrn ?? '—' }}</td>
                        <td>Grade {{ $e->grade_level }}</td>
                        <td>
                            <span class="badge {{ $e->student_type }}">{{ ucfirst($e->student_type) }}</span>
                        </td>
                        <td class="muted">{{ $e->created_at->format('M j, Y') }}</td>
                        <td style="text-align:right;">
                            <a class="en-btn" href="{{ route('teacher.enrollments.show', $e->id) }}">
                                {{ $tab === 'pending' ? 'Review' : 'View' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="en-empty">No {{ $tab }} enrollment requests.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $enrollments->links() }}</div>
</div>
@endsection