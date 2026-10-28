@extends('layouts.teacher')
@section('title', 'Review Enrollment')

@section('content')
<style>
    .rv-head { display:flex; align-items:center; gap:14px; margin-bottom:20px; }
    .rv-av { width:52px; height:52px; border-radius:var(--r-md); background:var(--accent-lt); color:var(--accent-dark); flex-shrink:0;
             display:flex; align-items:center; justify-content:center; font-weight:800; font-size:20px; }
    .rv-meta { display:flex; flex-wrap:wrap; align-items:center; gap:4px 14px; margin-top:4px; font-size:13px; color:var(--text-2); }
    .rv-meta .tp-pill { margin-left:-2px; }

    .rv-card { margin-bottom:14px; }
    .rv-card h2 { font-size:15px; font-weight:800; margin:0 0 14px; color:var(--text-1); }

    .rv-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:14px 24px; margin:0; }
    .rv-grid .wide { grid-column:1 / -1; }
    .rv-grid dt { font-size:12.5px; font-weight:600; color:var(--text-2); margin-bottom:2px; }
    .rv-grid dd { margin:0; font-size:14px; color:var(--text-1); overflow-wrap:anywhere; }
    .rv-grid dd.none { color:var(--text-3); }

    .rv-label { display:block; font-size:13px; font-weight:700; color:var(--text-1); margin-bottom:6px; }
    .rv-hint { font-size:12.5px; color:var(--text-2); margin:-2px 0 8px; }
    .rv-sel, .rv-txt { width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:var(--r-sm);
                       font-size:14px; background:var(--surface); color:var(--text-1); box-sizing:border-box; }
    .rv-sel { height:44px; }
    .rv-txt { min-height:88px; resize:vertical; line-height:1.5; }
    .rv-sel:focus, .rv-txt:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 3px rgba(37,99,235,.15); }

    .rv-actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:16px; }
    .main-content .rv-actions .tp-btn { min-height:44px; }
    .rv-approve { background:#15803D; }
    .rv-approve:hover { background:#166534; }

    .rv-note { padding:11px 14px; border-radius:var(--r-sm); font-size:13px; line-height:1.55; margin-bottom:14px; }
    .rv-note.info { background:#EFF6FF; border:1px solid #BFDBFE; color:#1E40AF; }
    .rv-note.warn { background:#FFFBEB; border:1px solid #FDE68A; color:#92400E; }
    .rv-err { background:#FEF2F2; border:1px solid #FCA5A5; color:#B91C1C;
              padding:10px 14px; border-radius:var(--r-sm); font-size:13px; margin-bottom:14px; }

    @media (max-width: 640px) {
        .rv-grid { grid-template-columns:1fr; }
        .rv-actions .tp-btn { flex:1; }
    }
</style>

@php
    $st = $enrollment->student;
    $status = $enrollment->status;
    $name = $enrollment->full_name ?? $st->name;
    [$pillCls, $pillLbl] = [
        'pending'  => ['warn', 'Pending'],
        'approved' => ['ok', 'Approved'],
        'rejected' => ['bad', 'Returned'],
    ][$status] ?? ['muted', ucfirst((string) $status)];
    $types = ['new' => 'New student', 'old' => 'Returning', 'transfer' => 'Transferee'];
@endphp

<div class="tp-page tp-narrow">
    <a class="tp-back" href="{{ route('teacher.enrollments.index') }}">
        <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Enrollments
    </a>

    <div class="rv-head">
        <div class="rv-av" aria-hidden="true">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</div>
        <div style="min-width:0;">
            <h1 class="tp-title">{{ $name }}</h1>
            <div class="rv-meta">
                <span class="tp-pill {{ $pillCls }}">{{ $pillLbl }}</span>
                <span>Grade {{ $enrollment->grade_level }}</span>
                @if ($st->email)<span style="overflow-wrap:anywhere;">{{ $st->email }}</span>@endif
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="rv-err" role="alert">{{ $errors->first() }}</div>
    @endif

    {{-- Birthdate cross-check: the date given at account activation should match the form.
         A mismatch may mean someone else activated this student's account. --}}
    @php
        $declaredBday = $st->birthdate;
        $formBday     = $enrollment->birthdate;
    @endphp
    @if ($declaredBday && $formBday && !$declaredBday->isSameDay($formBday))
        <div class="rv-note warn" role="alert">
            <strong>Birthdates do not match.</strong>
            The account was activated with {{ $declaredBday->format('M j, Y') }}, but the form says {{ $formBday->format('M j, Y') }}.
            Someone else may have activated this account, so confirm with the student before approving.
        </div>
    @endif

    <section class="tp-card rv-card" aria-labelledby="rvProfile">
        <h2 id="rvProfile">Student profile</h2>
        @php
            $fields = [
                'Full name'            => $enrollment->full_name,
                'LRN'                  => $st->lrn,
                'Age'                  => $enrollment->age,
                'Gender'               => $enrollment->gender ? ucfirst($enrollment->gender) : null,
                'Birthdate'            => $enrollment->birthdate?->format('M j, Y'),
                'Student type'         => $enrollment->student_type ? ($types[$enrollment->student_type] ?? ucfirst($enrollment->student_type)) : null,
                'Address'              => $enrollment->address,
                "Mother's name"        => $enrollment->mother_name,
                "Father's name"        => $enrollment->father_name,
                'Guardian'             => $enrollment->guardian_name,
                'Guardian contact'     => $enrollment->guardian_contact,
                'Last school'          => $enrollment->last_school,
                'Last grade completed' => $enrollment->last_grade_completed,
                'School year'          => $enrollment->school_year,
                'General average (last school year)' => $enrollment->general_average !== null ? number_format($enrollment->general_average, 2) : null,
            ];
        @endphp
        <dl class="rv-grid">
            @foreach ($fields as $label => $value)
                <div class="{{ $label === 'Address' ? 'wide' : '' }}">
                    <dt>{{ $label }}</dt>
                    @if ($value === null || $value === '')
                        <dd class="none">Not given</dd>
                    @else
                        <dd>{{ $value }}</dd>
                    @endif
                </div>
            @endforeach
        </dl>
    </section>

    {{-- Section balancing: each section should get a mix of performance levels --}}
    @if ($status === 'pending' && $balanceStats->isNotEmpty())
        @php
            $bestOverallSec = $balanceStats->firstWhere('section.id', $bestOverall)['section'] ?? null;
            $bestIsMine     = $bestOverall && $mySections->contains('id', $bestOverall);
        @endphp
        <section class="tp-card rv-card" aria-labelledby="rvBalance">
            <h2 id="rvBalance">Grade {{ $grade }} sections</h2>
            @if ($bestOverallSec && !$bestIsMine)
                <div class="rv-note warn">
                    For a balanced mix, the best fit for this student is <b>{{ $bestOverallSec->name }}</b>
                    ({{ $bestOverallSec->adviser ? 'adviser: ' . $bestOverallSec->adviser->name : 'no adviser yet' }}).
                    You can still place them in your section, or leave this request for that adviser.
                </div>
            @elseif ($bestIsMine)
                <div class="rv-note info">Your section is the best fit to keep Grade {{ $grade }} balanced.</div>
            @endif
            @include('partials.section-balance', [
                'stats'       => $balanceStats,
                'suggestedId' => $bestOverall,
                'band'        => \App\Services\SectionBalanceService::band($enrollment->general_average),
                'mineIds'     => $mySections->pluck('id')->all(),
            ])
        </section>
    @endif

    @if ($status === 'pending')
        @if ($mySections->isEmpty())
            <div class="rv-note warn">
                You have no advisory section for Grade {{ $enrollment->grade_level }}. Ask the admin to assign
                you as an adviser first, or leave this request for the correct adviser.
            </div>
        @else
            <form method="POST" action="{{ route('teacher.enrollments.approve', $enrollment->id) }}" class="tp-card rv-card">
                @csrf
                <h2>Approve and place in a section</h2>
                <p class="rv-hint">The student is added to the section and gets an email with their section, grade and school year.</p>
                <label class="rv-label" for="rvSection">Section</label>
                <select id="rvSection" name="section_id" class="rv-sel" required>
                    <option value="">Choose a section</option>
                    @foreach ($mySections as $sec)
                        <option value="{{ $sec->id }}" @selected((int) $bestMine === (int) $sec->id || $mySections->count() === 1)>
                            {{ $sec->name }} (Grade {{ $sec->grade_level }}){{ (int) $bestMine === (int) $sec->id && $mySections->count() > 1 ? ', suggested' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="rv-actions">
                    <button type="submit" class="tp-btn rv-approve"
                            data-msg="Approve and enroll {{ $name }}?"
                            onclick="return confirm(this.dataset.msg);">
                        Approve and enroll
                    </button>
                </div>
            </form>
        @endif

        <form method="POST" action="{{ route('teacher.enrollments.reject', $enrollment->id) }}" class="tp-card rv-card">
            @csrf
            <h2>Return the form</h2>
            <label class="rv-label" for="rvRemarks">What needs to change</label>
            <p class="rv-hint">The student sees this message, so say exactly what to fix or submit.</p>
            <textarea id="rvRemarks" name="remarks" class="rv-txt" required maxlength="500"
                      placeholder="Example: Please upload your Form 137 from your last school.">{{ old('remarks') }}</textarea>
            <div class="rv-actions">
                <button type="submit" class="tp-btn danger"
                        onclick="return confirm('Return this form to the student?');">
                    Return form
                </button>
            </div>
        </form>
    @else
        <section class="tp-card rv-card" aria-labelledby="rvOutcome">
            <h2 id="rvOutcome">Review</h2>
            <dl class="rv-grid">
                <div>
                    <dt>Status</dt>
                    <dd><span class="tp-pill {{ $pillCls }}">{{ $pillLbl }}</span></dd>
                </div>
                <div>
                    <dt>Reviewed</dt>
                    @if ($enrollment->reviewed_at)
                        <dd>{{ $enrollment->reviewed_at->format('M j, Y g:i A') }}</dd>
                    @else
                        <dd class="none">Not recorded</dd>
                    @endif
                </div>
                @if ($enrollment->remarks)
                    <div class="wide">
                        <dt>Remarks</dt>
                        <dd style="white-space:pre-line;">{{ $enrollment->remarks }}</dd>
                    </div>
                @endif
            </dl>
        </section>
    @endif
</div>
@endsection
