@extends('layouts.app')
@section('title', "Child's Records")

@section('content')
<style>
    .cr-wrap { max-width: 960px; margin: 0 auto; }
    .cr-title { font-size: 24px; font-weight: 800; color: var(--text-1); letter-spacing: -.01em; }
    .cr-sub { font-size: 13.5px; color: var(--text-2); margin: 4px 0 20px; }

    .cr-child { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); padding: 20px; margin-bottom: 22px; scroll-margin-top: calc(var(--nav-h) + 12px); }
    .cr-head { display: flex; flex-wrap: wrap; align-items: center; gap: 12px 16px; }
    .cr-av { width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0; display: grid; place-items: center; background: var(--accent-lt); color: var(--accent); font-size: 18px; font-weight: 800; }
    .cr-who { flex: 1; min-width: 180px; }
    .cr-name { font-size: 17px; font-weight: 800; color: var(--text-1); }
    .cr-meta { font-size: 13px; color: var(--text-2); margin-top: 2px; }
    .cr-badge { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 11.5px; font-weight: 700; }
    .cr-badge.enrolled { background: #DCFCE7; color: #166534; }
    .cr-badge.pending  { background: #FEF3C7; color: #92400E; }
    .cr-badge.returned { background: #FEE2E2; color: #991B1B; }
    .cr-badge.none     { background: #F1F5F9; color: #475569; }
    .cr-reason { flex-basis: 100%; font-size: 13px; color: var(--text-2); background: #FEF2F2; border-radius: var(--r-sm); padding: 9px 12px; }

    .cr-h2 { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 6px 12px; margin: 22px 0 10px; }
    .cr-h2 h3 { font-size: 12.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--text-2); }
    .cr-h2 span { font-size: 13px; color: var(--text-2); }
    .cr-h2 b { color: var(--text-1); }

    .cr-box { border: 1px solid var(--border); border-radius: var(--r-md); overflow: hidden; }
    .cr-scroll { overflow-x: auto; }
    .cr-scroll .cr-table { display: table; }   /* the box scrolls, so keep tables full width (layout makes them blocks on phones) */
    .cr-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .cr-table th { background: #F8FAFF; text-align: left; padding: 10px 12px; font-size: 11.5px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--text-2); border-bottom: 1px solid var(--border); white-space: nowrap; }
    .cr-table td { padding: 11px 12px; border-bottom: 1px solid var(--border); color: var(--text-1); white-space: nowrap; }
    .cr-table tr:last-child td { border-bottom: none; }
    .cr-table .num { text-align: center; font-variant-numeric: tabular-nums; }
    .cr-table td.cr-subj { font-weight: 700; white-space: normal; min-width: 140px; }
    .cr-table td.cr-pass { color: #15803D; font-weight: 700; }
    .cr-table td.cr-fail { color: #B91C1C; font-weight: 700; }
    .cr-table td.cr-dim { color: var(--text-3); }

    .cr-pill { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 11.5px; font-weight: 700; }
    .cr-pill.pass, .cr-pill.present { background: #DCFCE7; color: #166534; }
    .cr-pill.fail, .cr-pill.absent  { background: #FEE2E2; color: #991B1B; }
    .cr-pill.late                   { background: #FEF3C7; color: #92400E; }
    .cr-pill.wip                    { background: #F1F5F9; color: #475569; }

    .cr-details { margin-top: 10px; }
    .cr-details summary { display: inline-flex; align-items: center; min-height: 44px; cursor: pointer; font-size: 13.5px; font-weight: 700; color: var(--accent-dark); }
    .cr-details summary:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; border-radius: 4px; }
    .cr-details[open] summary { margin-bottom: 6px; }

    .cr-empty { padding: 18px 14px; text-align: center; font-size: 13.5px; color: var(--text-2); border: 1px dashed var(--border); border-radius: var(--r-md); }
    .cr-none { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); padding: 40px 20px; text-align: center; }
    .cr-none b { display: block; font-size: 15px; color: var(--text-1); }
    .cr-none p { font-size: 13.5px; color: var(--text-2); margin-top: 6px; }
</style>

@php
    $badges = [
        'enrolled' => 'Enrolled · SY ' . $schoolYear,
        'pending'  => 'Enrollment form under review',
        'returned' => 'Enrollment form returned',
        'none'     => 'Not enrolled for SY ' . $schoolYear,
    ];
    $statusLabel = ['present' => 'Present', 'late' => 'Late', 'absent' => 'Absent'];
    $pct = fn (string $key) => rtrim(rtrim(number_format($weights[$key] * 100, 1), '0'), '.') . '%';
@endphp

<div class="cr-wrap">
    <h1 class="cr-title">Child's Records</h1>
    <p class="cr-sub">Grades, school arrivals and class attendance. Passing grade: {{ $passing }}.</p>

    @forelse($children as $child)
        @php
            $state   = $states[$child->id];
            $section = $child->studentEnrollment?->section ?? $child->section;
            $years   = $reportCards[$child->id];
            $gate    = $arrivals[$child->id];
            $classes = $classAttendance[$child->id];
        @endphp
        <section class="cr-child" id="child-{{ $child->id }}" aria-labelledby="child-{{ $child->id }}-name">
            <div class="cr-head">
                <span class="cr-av" aria-hidden="true">{{ mb_strtoupper(mb_substr($child->name, 0, 1)) }}</span>
                <div class="cr-who">
                    <h2 class="cr-name" id="child-{{ $child->id }}-name">{{ $child->name }}</h2>
                    <p class="cr-meta">
                        Grade {{ $child->studentEnrollment?->grade_level ?? $child->grade_level ?? '—' }}@if($section) · {{ $section->name }}@endif
                        @if($child->lrn) · LRN {{ $child->lrn }}@endif
                    </p>
                </div>
                <span class="cr-badge {{ $state }}">{{ $badges[$state] }}</span>
                @if($state === 'returned' && $child->enrollmentRequest?->remarks)
                    <p class="cr-reason">Reason given by the school: {{ $child->enrollmentRequest->remarks }}</p>
                @endif
            </div>

            {{-- Grades, one block per school year --}}
            @forelse($years as $schoolYearLabel => $year)
                <div class="cr-h2">
                    <h3>Grades · SY {{ $schoolYearLabel }}</h3>
                    @if($year['general'] !== null)
                        <span>General average{{ $year['complete'] || !$year['current'] ? '' : ' so far' }}: <b>{{ number_format($year['general'], 2) }}</b></span>
                    @endif
                </div>
                <div class="cr-box cr-scroll">
                    <table class="cr-table">
                        <thead>
                            <tr>
                                <th scope="col">Subject</th>
                                @foreach(\App\Services\GradeService::QUARTERS as $q)<th scope="col" class="num">{{ $q }}</th>@endforeach
                                <th scope="col" class="num">Average</th>
                                <th scope="col">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($year['subjects'] as $s)
                                <tr>
                                    <td class="cr-subj">{{ $s['subject']?->name ?? 'Subject removed' }}</td>
                                    @foreach($s['quarters'] as $g)
                                        @if($g && $g->final_grade !== null)
                                            <td class="num {{ $g->final_grade >= $passing ? '' : 'cr-fail' }}">{{ number_format($g->final_grade, 2) }}</td>
                                        @else
                                            <td class="num cr-dim">—</td>
                                        @endif
                                    @endforeach
                                    @if($s['average'] !== null)
                                        <td class="num {{ $s['passed'] ? 'cr-pass' : 'cr-fail' }}">{{ number_format($s['average'], 2) }}</td>
                                        <td>
                                            @if(!$s['complete'])
                                                <span class="cr-pill wip">{{ $year['current'] ? 'In progress' : 'Incomplete' }}</span>
                                            @else
                                                <span class="cr-pill {{ $s['passed'] ? 'pass' : 'fail' }}">{{ $s['passed'] ? 'Passed' : 'Failed' }}</span>
                                            @endif
                                        </td>
                                    @else
                                        <td class="num cr-dim">—</td>
                                        <td class="cr-dim">No grade yet</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <details class="cr-details">
                    <summary>Score breakdown</summary>
                    <div class="cr-box cr-scroll">
                        <table class="cr-table">
                            <thead>
                                <tr>
                                    <th scope="col">Subject</th>
                                    <th scope="col" class="num">Quarter</th>
                                    <th scope="col" class="num">Written works ({{ $pct('written_works') }})</th>
                                    <th scope="col" class="num">Performance tasks ({{ $pct('performance_tasks') }})</th>
                                    <th scope="col" class="num">Quarterly assessment ({{ $pct('quarterly_assessment') }})</th>
                                    <th scope="col" class="num">Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($year['subjects'] as $s)
                                    @foreach($s['quarters']->filter() as $q => $g)
                                        <tr>
                                            <td class="cr-subj">{{ $s['subject']?->name ?? 'Subject removed' }}</td>
                                            <td class="num">{{ $q }}</td>
                                            @foreach(['written_works', 'performance_tasks', 'quarterly_assessment'] as $part)
                                                <td class="num">{{ $g->$part !== null ? number_format($g->$part, 2) : '—' }}</td>
                                            @endforeach
                                            <td class="num {{ $g->final_grade === null ? 'cr-dim' : ($g->final_grade >= $passing ? 'cr-pass' : 'cr-fail') }}">
                                                {{ $g->final_grade !== null ? number_format($g->final_grade, 2) : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @empty
                <div class="cr-h2"><h3>Grades</h3></div>
                <p class="cr-empty">No grades have been recorded yet.</p>
            @endforelse

            {{-- Gate arrivals (face kiosk) --}}
            <div class="cr-h2">
                <h3>School arrivals · last {{ $days }} days</h3>
                @if($gate->isNotEmpty())
                    <span><b>{{ $gate->where('status', 'present')->count() }}</b> on time · <b>{{ $gate->where('status', 'late')->count() }}</b> late</span>
                @endif
            </div>
            @if($gate->isEmpty())
                <p class="cr-empty">No arrivals recorded in the last {{ $days }} days.</p>
            @else
                <div class="cr-box cr-scroll">
                    <table class="cr-table">
                        <thead><tr><th scope="col">Date</th><th scope="col">Time</th><th scope="col">Status</th></tr></thead>
                        <tbody>
                            @foreach($gate as $a)
                                <tr>
                                    <td>{{ $a->attended_at?->format('D, M j') }}</td>
                                    <td>{{ $a->attended_at?->format('g:i A') }}</td>
                                    <td><span class="cr-pill {{ $a->status === 'late' ? 'late' : 'present' }}">{{ $a->status === 'late' ? 'Late' : 'On time' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Class attendance (taken by subject teachers) --}}
            <div class="cr-h2">
                <h3>Class attendance · last {{ $days }} days</h3>
                @if($classes->isNotEmpty())
                    <span>
                        <b>{{ $classes->where('status', 'present')->count() }}</b> present ·
                        <b>{{ $classes->where('status', 'late')->count() }}</b> late ·
                        <b>{{ $classes->where('status', 'absent')->count() }}</b> absent
                    </span>
                @endif
            </div>
            @if($classes->isEmpty())
                <p class="cr-empty">No class attendance recorded in the last {{ $days }} days.</p>
            @else
                <div class="cr-box cr-scroll">
                    <table class="cr-table">
                        <thead><tr><th scope="col">Date</th><th scope="col">Subject</th><th scope="col">Teacher</th><th scope="col">Marked</th><th scope="col">Status</th></tr></thead>
                        <tbody>
                            @foreach($classes as $r)
                                <tr>
                                    <td>{{ $r->session?->opened_at?->format('D, M j') ?? '—' }}</td>
                                    <td>{{ $r->session?->subject?->name ?? '—' }}</td>
                                    <td>{{ $r->session?->teacher?->name ?? '—' }}</td>
                                    <td>{{ $r->marked_at?->format('g:i A') ?? '—' }}</td>
                                    <td><span class="cr-pill {{ $r->status }}">{{ $statusLabel[$r->status] ?? ucfirst($r->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @empty
        <div class="cr-none">
            <b>No child is linked to your account</b>
            <p>Please contact the school office so they can link your child's account to yours.</p>
        </div>
    @endforelse
</div>
@endsection
