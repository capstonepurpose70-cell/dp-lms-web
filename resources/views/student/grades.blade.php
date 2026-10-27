@extends('layouts.app')
@section('title', 'My Grades')

@section('content')
<style>
    .sg-wrap { max-width: 900px; margin: 0 auto; }
    .sg-title { font-size: 24px; font-weight: 800; color: var(--text-1); letter-spacing: -.01em; }
    .sg-sub { font-size: 13.5px; color: var(--text-2); margin-top: 4px; margin-bottom: 20px; }

    .sg-year { margin-bottom: 26px; }
    .sg-year-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
    .sg-year-title { font-size: 12.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--text-2); }
    .sg-general { display: inline-flex; align-items: baseline; gap: 8px; padding: 8px 14px; border-radius: 12px; background: var(--surface); border: 1px solid var(--border); }
    .sg-general span { font-size: 12.5px; color: var(--text-2); font-weight: 600; }
    .sg-general b { font-size: 20px; color: var(--text-1); }

    .sg-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); overflow: hidden; }
    .sg-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .sg-table th { background: #F8FAFF; text-align: left; padding: 11px 14px; font-size: 11.5px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--text-2); border-bottom: 1px solid var(--border); white-space: nowrap; }
    .sg-table td { padding: 12px 14px; border-bottom: 1px solid var(--border); color: var(--text-1); }
    .sg-table tr:last-child td { border-bottom: none; }
    .sg-table .num { text-align: center; font-variant-numeric: tabular-nums; }
    .sg-subject { font-weight: 700; }
    .sg-code { display: block; font-size: 11.5px; color: var(--text-3); font-weight: 500; }
    /* qualified with the element so they beat ".sg-table td" / ".sg-general b" */
    td.sg-pass, b.sg-pass { color: #15803D; font-weight: 700; }
    td.sg-fail, b.sg-fail { color: #B91C1C; font-weight: 700; }
    td.sg-none, span.sg-none { color: var(--text-3); }
    .sg-pill { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
    .sg-pill.pass { background: #DCFCE7; color: #166534; }
    .sg-pill.fail { background: #FEE2E2; color: #991B1B; }
    .sg-pill.wip  { background: #F1F5F9; color: #475569; }

    .sg-note { font-size: 12px; color: var(--text-3); margin-top: 8px; line-height: 1.5; }
    .sg-empty { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); padding: 44px 20px; text-align: center; }
    .sg-empty b { display: block; font-size: 15px; color: var(--text-1); }
    .sg-empty p { font-size: 13px; color: var(--text-2); margin-top: 4px; }
</style>

<div class="sg-wrap">
    <h1 class="sg-title">My Grades</h1>
    <p class="sg-sub">Quarterly grades from your teachers. Passing grade: {{ $passing }}.</p>

    @forelse($years as $schoolYear => $year)
        <section class="sg-year" aria-labelledby="sy-{{ $loop->index }}">
            <div class="sg-year-head">
                <h2 class="sg-year-title" id="sy-{{ $loop->index }}">School Year {{ $schoolYear }}</h2>
                @if($year['general'] !== null)
                    <div class="sg-general">
                        <span>General average{{ $year['complete'] ? '' : ' (so far)' }}</span>
                        <b class="{{ $year['general'] >= $passing ? 'sg-pass' : 'sg-fail' }}">{{ number_format($year['general'], 2) }}</b>
                    </div>
                @endif
            </div>

            <div class="sg-card">
                <table class="sg-table">
                    <thead>
                        <tr>
                            <th scope="col">Subject</th>
                            <th scope="col" class="num">Q1</th>
                            <th scope="col" class="num">Q2</th>
                            <th scope="col" class="num">Q3</th>
                            <th scope="col" class="num">Q4</th>
                            <th scope="col" class="num">Average</th>
                            <th scope="col">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($year['subjects'] as $s)
                            <tr>
                                <td>
                                    <span class="sg-subject">{{ $s['subject']?->name ?? 'Subject removed' }}</span>
                                    @if($s['subject']?->code) <span class="sg-code">{{ $s['subject']->code }}</span> @endif
                                </td>
                                @foreach($s['quarters'] as $q => $g)
                                    @if($g && $g->final_grade !== null)
                                        <td class="num {{ $g->isPassed() ? 'sg-pass' : 'sg-fail' }}"
                                            title="Written works {{ $g->written_works }} · Performance tasks {{ $g->performance_tasks }} · Quarterly assessment {{ $g->quarterly_assessment }}">
                                            {{ number_format((float) $g->final_grade, 2) }}
                                        </td>
                                    @else
                                        <td class="num sg-none" aria-label="No grade yet">—</td>
                                    @endif
                                @endforeach
                                <td class="num {{ $s['average'] === null ? 'sg-none' : ($s['passed'] ? 'sg-pass' : 'sg-fail') }}">
                                    {{ $s['average'] !== null ? number_format($s['average'], 2) : '—' }}
                                </td>
                                <td>
                                    @if($s['average'] === null)
                                        <span class="sg-none">—</span>
                                    @elseif(!$s['complete'])
                                        <span class="sg-pill wip">In progress</span>
                                    @else
                                        <span class="sg-pill {{ $s['passed'] ? 'pass' : 'fail' }}">{{ $s['passed'] ? 'Passed' : 'Failed' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="sg-note">Average = mean of the quarters recorded so far. Hover a quarter to see its written works, performance tasks, and quarterly assessment.</p>
        </section>
    @empty
        <div class="sg-empty">
            <b>No grades yet</b>
            <p>Your grades appear here once your teachers record them.</p>
        </div>
    @endforelse
</div>
@endsection
