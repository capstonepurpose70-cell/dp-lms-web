@extends('layouts.teacher')
@section('title', 'Review Enrollment')

@section('content')
<style>
    .rv-page { animation: rv-fade .3s ease both; max-width: 940px; }
    @keyframes rv-fade { from { opacity:0; transform:translateY(8px);} to { opacity:1; transform:none;} }

    .rv-back { display:inline-block; font-size:12.5px; color:var(--text-2); text-decoration:none; margin-bottom:12px; }
    .rv-head { display:flex; align-items:center; gap:14px; margin-bottom:18px; }
    .rv-av { width:54px; height:54px; border-radius:14px; background:#EFF6FF; color:#1D4ED8;
             display:flex; align-items:center; justify-content:center; font-weight:800; font-size:20px; }
    .rv-head h1 { font-size:21px; font-weight:800; margin:0; color:var(--text-1); }
    .rv-head p  { margin:2px 0 0; font-size:12.5px; color:var(--text-2); }

    .badge { display:inline-block; padding:4px 11px; border-radius:999px; font-size:11px; font-weight:800; }
    .badge.pending  { background:#FEF3C7; color:#B45309; }
    .badge.approved { background:#D1FAE5; color:#065F46; }
    .badge.rejected { background:#FEE2E2; color:#B91C1C; }

    .rv-card { background:var(--surface); border:1px solid var(--border); border-radius:14px;
               padding:18px; margin-bottom:14px; }
    .rv-card h2 { font-size:14px; font-weight:800; margin:0 0 14px; color:var(--text-1); }

    .rv-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:14px 20px; }
    .rv-field label { display:block; font-size:10.5px; font-weight:700; text-transform:uppercase;
                      letter-spacing:.4px; color:var(--text-3); margin-bottom:3px; }
    .rv-field .val { font-size:13.5px; color:var(--text-1); }

    .rv-sel { width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:9px;
              font-size:13.5px; background:var(--surface); color:var(--text-1); box-sizing:border-box; }
    .rv-txt { width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:9px;
              font-size:13px; background:var(--surface); color:var(--text-1); box-sizing:border-box;
              min-height:74px; resize:vertical; }

    .rv-actions { display:flex; gap:10px; flex-wrap:wrap; }
    .rv-btn { padding:11px 22px; border:0; border-radius:10px; font-weight:800; font-size:13.5px; cursor:pointer; }
    .rv-btn.approve { background:#15803D; color:#fff; }
    .rv-btn.reject  { background:#fff; color:#B91C1C; border:1.5px solid #FCA5A5; }

    .rv-note { padding:11px 14px; border-radius:10px; font-size:12.5px; line-height:1.5; margin-bottom:14px; }
    .rv-note.info { background:#EFF6FF; border:1px solid #BFDBFE; color:#1E40AF; }
    .rv-note.warn { background:#FFFBEB; border:1px solid #FDE68A; color:#92400E; }
    .rv-err { background:#FEF2F2; border:1px solid #FCA5A5; color:#B91C1C;
              padding:10px 14px; border-radius:10px; font-size:12.5px; margin-bottom:12px; }

    @media (max-width: 640px) { .rv-grid { grid-template-columns:1fr; } }
</style>

@php
    $st = $enrollment->student;
    $status = $enrollment->status;
@endphp

<div class="rv-page">
    <a class="rv-back" href="{{ route('teacher.enrollments.index') }}">&larr; Back to enrollments</a>

    <div class="rv-head">
        <div class="rv-av">{{ strtoupper(substr($enrollment->full_name ?? $st->name, 0, 1)) }}</div>
        <div>
            <h1>{{ $enrollment->full_name ?? $st->name }}</h1>
            <p>{{ $st->email }} &nbsp;·&nbsp; Grade {{ $enrollment->grade_level }} &nbsp;·&nbsp;
               <span class="badge {{ $status }}">{{ ucfirst($status) }}</span>
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="rv-err">{{ $errors->first() }}</div>
    @endif

    {{-- Student Profile Sheet (read-only) --}}
    <div class="rv-card">
        <h2>Student Profile Sheet</h2>
        <div class="rv-grid">
            <div class="rv-field"><label>Full Name</label><div class="val">{{ $enrollment->full_name ?? '—' }}</div></div>
            <div class="rv-field"><label>LRN</label><div class="val">{{ $st->lrn ?? '—' }}</div></div>
            <div class="rv-field"><label>Age</label><div class="val">{{ $enrollment->age ?? '—' }}</div></div>
            <div class="rv-field"><label>Gender</label><div class="val">{{ ucfirst($enrollment->gender ?? '—') }}</div></div>
            <div class="rv-field"><label>Birthdate</label><div class="val">{{ $enrollment->birthdate ?? '—' }}</div></div>
            <div class="rv-field"><label>Student Type</label><div class="val">{{ ucfirst($enrollment->student_type ?? '—') }}</div></div>
            <div class="rv-field" style="grid-column:1 / -1;"><label>Address</label><div class="val">{{ $enrollment->address ?? '—' }}</div></div>
            <div class="rv-field"><label>Mother's Name</label><div class="val">{{ $enrollment->mother_name ?? '—' }}</div></div>
            <div class="rv-field"><label>Father's Name</label><div class="val">{{ $enrollment->father_name ?? '—' }}</div></div>
            <div class="rv-field"><label>Guardian</label><div class="val">{{ $enrollment->guardian_name ?? '—' }}</div></div>
            <div class="rv-field"><label>Guardian Contact</label><div class="val">{{ $enrollment->guardian_contact ?? '—' }}</div></div>
            <div class="rv-field"><label>Last School</label><div class="val">{{ $enrollment->last_school ?? '—' }}</div></div>
            <div class="rv-field"><label>Last Grade Completed</label><div class="val">{{ $enrollment->last_grade_completed ?? '—' }}</div></div>
            <div class="rv-field"><label>School Year</label><div class="val">{{ $enrollment->school_year ?? '—' }}</div></div>
        </div>
    </div>

    @if ($status === 'pending')
        {{-- Approve: assign to adviser's section --}}
        @if ($mySections->isEmpty())
            <div class="rv-note warn">
                You have no advisory section for Grade {{ $enrollment->grade_level }}. Ask the admin to assign
                you as an adviser first, or forward this request to the correct adviser.
            </div>
        @else
            <form method="POST" action="{{ route('teacher.enrollments.approve', $enrollment->id) }}">
                @csrf
                <div class="rv-card">
                    <h2>Assign to section &amp; approve</h2>
                    <div class="rv-note info">
                        Approving places this student in your section and emails their details
                        (section, grade, school year). Assign carefully to keep sections balanced.
                    </div>
                    <div class="rv-field">
                        <label>Section *</label>
                        <select name="section_id" class="rv-sel" required>
                            <option value="">— Select your advisory section —</option>
                            @foreach ($mySections as $sec)
                                <option value="{{ $sec->id }}">
                                    {{ $sec->name }} (Grade {{ $sec->grade_level }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="rv-actions" style="margin-top:16px;">
                        <button type="submit" class="rv-btn approve"
                                onclick="return confirm('Approve and enroll {{ $enrollment->full_name ?? $st->name }}?');">
                            ✓ Approve &amp; Enroll
                        </button>
                    </div>
                </div>
            </form>
        @endif

        {{-- Reject --}}
        <form method="POST" action="{{ route('teacher.enrollments.reject', $enrollment->id) }}">
            @csrf
            <div class="rv-card">
                <h2>Reject enrollment</h2>
                <div class="rv-field">
                    <label>Reason for rejection *</label>
                    <textarea name="remarks" class="rv-txt" required
                              placeholder="e.g. Incomplete requirements. Please submit Form 137."></textarea>
                </div>
                <div class="rv-actions" style="margin-top:12px;">
                    <button type="submit" class="rv-btn reject"
                            onclick="return confirm('Reject this enrollment?');">
                        ✕ Reject
                    </button>
                </div>
            </div>
        </form>
    @else
        <div class="rv-card">
            <h2>Review outcome</h2>
            <div class="rv-field">
                <label>Status</label>
                <div class="val"><span class="badge {{ $status }}">{{ ucfirst($status) }}</span></div>
            </div>
            @if ($enrollment->remarks)
                <div class="rv-field" style="margin-top:10px;">
                    <label>Remarks</label>
                    <div class="val">{{ $enrollment->remarks }}</div>
                </div>
            @endif
            <div class="rv-field" style="margin-top:10px;">
                <label>Reviewed</label>
                <div class="val">{{ optional($enrollment->reviewed_at)->format('M j, Y g:i A') ?? '—' }}</div>
            </div>
        </div>
    @endif
</div>
@endsection