@extends('layouts.teacher')
@section('title', 'Gradebook')

@section('content')
<style>
    .gb-page { max-width:1100px; animation: gb-fade .22s ease both; }
    @keyframes gb-fade { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
    .gb-title { font-size:22px; font-weight:800; color:var(--text-1); margin:0; }
    .gb-sub { font-size:13px; color:var(--text-2); margin:4px 0 18px; line-height:1.55; max-width:760px; }
    .gb-card { background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:18px; margin-bottom:16px; }
    .gb-card h2 { font-size:15px; font-weight:800; color:var(--text-1); margin:0 0 4px; }
    .gb-muted { font-size:12.5px; color:var(--text-2); margin:0 0 14px; line-height:1.5; }
    .gb-grid { display:grid; grid-template-columns: 1.6fr 1.4fr .7fr; gap:12px; }
    .gb-grid3 { display:grid; grid-template-columns: repeat(3, 1fr) auto; gap:12px; align-items:end; margin-top:12px; }
    .gb-label { display:block; font-size:12.5px; font-weight:700; color:var(--text-1); margin-bottom:6px; }
    .gb-in { width:100%; height:44px; padding:0 12px; border:1.5px solid var(--border); border-radius:10px; font-size:14px; background:var(--surface); color:var(--text-1); box-sizing:border-box; }
    .gb-in:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
    .gb-in:disabled { opacity:.6; }
    .gb-final { height:44px; display:flex; align-items:center; gap:8px; padding:0 14px; border-radius:10px; font-weight:800; font-size:14px; white-space:nowrap;
        background:var(--accent-lt); color:var(--text-1); border:1px solid var(--border); }
    .gb-final.pass { background:rgba(22,163,74,.10); color:#15803d; border-color:rgba(22,163,74,.35); }
    .gb-final.fail { background:rgba(220,38,38,.08); color:#b91c1c; border-color:rgba(220,38,38,.3); }
    .gb-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; height:44px; padding:0 20px; border:0; border-radius:11px;
        background:#1d4ed8; color:#fff; font-size:14px; font-weight:800; cursor:pointer; text-decoration:none; white-space:nowrap; }
    .gb-btn:hover { background:#1e40af; }
    .gb-btn.ghost { background:transparent; color:var(--text-1); border:1px solid var(--border); }
    .gb-btn.ghost:hover { border-color:#2563eb; color:#2563eb; }
    .gb-btn.sm { height:32px; padding:0 12px; font-size:12.5px; font-weight:700; }
    .gb-err { background:rgba(220,38,38,.08); border:1px solid rgba(220,38,38,.3); color:#b91c1c; padding:11px 14px; border-radius:12px; font-size:13px; margin-bottom:14px; }
    .gb-empty { text-align:center; padding:28px 12px; color:var(--text-2); font-size:13.5px; }
    .gb-filter { display:grid; grid-template-columns: 1.6fr .8fr 1.2fr auto auto; gap:10px; align-items:end; margin-bottom:14px; }
    .gb-scroll { overflow-x:auto; }
    .gb-table { width:100%; border-collapse:collapse; font-size:13px; min-width:760px; }
    .gb-table th { text-align:left; font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--text-3); padding:9px 10px; border-bottom:1px solid var(--border); white-space:nowrap; }
    .gb-table td { padding:10px; border-bottom:1px solid var(--border); color:var(--text-1); vertical-align:middle; }
    .gb-table tr:last-child td { border-bottom:0; }
    .gb-table td.num { text-align:right; font-variant-numeric:tabular-nums; }
    .gb-table th.num { text-align:right; }
    .gb-name { font-weight:700; }
    .gb-small { font-size:11.5px; color:var(--text-3); margin-top:2px; }
    .gb-pill { display:inline-block; padding:2px 10px; border-radius:999px; font-size:11.5px; font-weight:800; }
    .gb-pill.pass { background:rgba(22,163,74,.12); color:#15803d; }
    .gb-pill.fail { background:rgba(220,38,38,.10); color:#b91c1c; }
    .gb-pager { margin-top:14px; }
    @media (max-width: 860px) {
        .gb-grid, .gb-grid3, .gb-filter { grid-template-columns: 1fr; }
    }
</style>

@php
    $oldClass = old('class_key');
@endphp

<div class="gb-page">
    <h1 class="gb-title">Gradebook</h1>
    <p class="gb-sub">Encode quarterly grades for your classes (School Year {{ $schoolYear }}).
        Final grade = Written Works 25% + Performance Tasks 50% + Quarterly Assessment 25%. Passing is
        {{ \App\Models\Grade::PASSING }}. Saving the same student, subject and quarter again updates the grade.</p>

    @if ($errors->any())
        <div class="gb-err" role="alert">{{ $errors->first() }}</div>
    @endif

    {{-- ── Encode / edit ── --}}
    <div class="gb-card" id="encodeCard">
        <h2>Encode grade</h2>
        @if ($classList->isEmpty())
            <div class="gb-empty">You have no classes yet. The admin assigns your classes in <b>Class Schedules</b>.</div>
        @else
            <p class="gb-muted">Choose the class first — the student list shows only that section. Blank scores count as 0.</p>
            <form method="POST" action="{{ route('teacher.gradebook.store') }}" id="gbForm">
                @csrf
                <input type="hidden" name="subject_id" id="f_subject" value="{{ old('subject_id') }}">
                <div class="gb-grid">
                    <div>
                        <label class="gb-label" for="f_class">Class</label>
                        <select class="gb-in" name="class_key" id="f_class" required>
                            <option value="">— Choose a class —</option>
                            @foreach ($classList as $c)
                                <option value="{{ $c['key'] }}" data-section="{{ $c['section_id'] }}" data-subject="{{ $c['subject_id'] }}" @selected($oldClass === $c['key'])>{{ $c['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="gb-label" for="f_student">Student</label>
                        <select class="gb-in" name="student_id" id="f_student" required>
                            <option value="">Choose a class first</option>
                        </select>
                    </div>
                    <div>
                        <label class="gb-label" for="f_quarter">Quarter</label>
                        <select class="gb-in" name="quarter" id="f_quarter" required>
                            @foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $q)
                                <option value="{{ $q }}" @selected(old('quarter') === $q)>{{ $q }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="gb-grid3">
                    <div>
                        <label class="gb-label" for="f_ww">Written Works (25%)</label>
                        <input class="gb-in js-score" type="number" step="0.01" min="0" max="100" inputmode="decimal" name="written_works" id="f_ww" value="{{ old('written_works') }}" placeholder="0–100">
                    </div>
                    <div>
                        <label class="gb-label" for="f_pt">Performance Tasks (50%)</label>
                        <input class="gb-in js-score" type="number" step="0.01" min="0" max="100" inputmode="decimal" name="performance_tasks" id="f_pt" value="{{ old('performance_tasks') }}" placeholder="0–100">
                    </div>
                    <div>
                        <label class="gb-label" for="f_qa">Quarterly Assessment (25%)</label>
                        <input class="gb-in js-score" type="number" step="0.01" min="0" max="100" inputmode="decimal" name="quarterly_assessment" id="f_qa" value="{{ old('quarterly_assessment') }}" placeholder="0–100">
                    </div>
                    <div style="display:flex; gap:10px; align-items:end;">
                        <div class="gb-final" id="f_final" aria-live="polite">Final: —</div>
                        <button type="submit" class="gb-btn">Save grade</button>
                    </div>
                </div>
            </form>
        @endif
    </div>

    {{-- ── Grades ── --}}
    <div class="gb-card">
        <h2>Grades · S.Y. {{ $schoolYear }}</h2>
        <p class="gb-muted">{{ number_format($grades->total()) }} {{ \Illuminate\Support\Str::plural('grade', $grades->total()) }}{{ array_filter($filters) ? ' match your filters' : '' }}.</p>

        <form method="GET" action="{{ route('teacher.gradebook.index') }}" class="gb-filter">
            <div>
                <label class="gb-label" for="flt_class">Class</label>
                <select class="gb-in" name="class" id="flt_class">
                    <option value="">All my classes</option>
                    @foreach ($classList as $c)
                        <option value="{{ $c['key'] }}" @selected(($filters['class'] ?? '') === $c['key'])>{{ $c['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="gb-label" for="flt_quarter">Quarter</label>
                <select class="gb-in" name="quarter" id="flt_quarter">
                    <option value="">All</option>
                    @foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $q)
                        <option value="{{ $q }}" @selected(($filters['quarter'] ?? '') === $q)>{{ $q }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="gb-label" for="flt_q">Student</label>
                <input class="gb-in" type="search" name="q" id="flt_q" value="{{ $filters['q'] ?? '' }}" placeholder="Name or LRN" maxlength="100">
            </div>
            <button type="submit" class="gb-btn">Filter</button>
            @if (array_filter($filters))
                <a href="{{ route('teacher.gradebook.index') }}" class="gb-btn ghost">Clear</a>
            @endif
        </form>

        @if ($grades->count())
            <div class="gb-scroll">
                <table class="gb-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>Quarter</th>
                            <th class="num">WW</th>
                            <th class="num">PT</th>
                            <th class="num">QA</th>
                            <th class="num">Final</th>
                            <th>Remarks</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($grades as $grade)
                            @php
                                $secId = $grade->student?->studentSectionId();
                                $key   = $secId ? $secId . '-' . $grade->subject_id : null;
                                $editable = $key && $classList->contains('key', $key);
                            @endphp
                            <tr>
                                <td>
                                    <div class="gb-name">{{ $grade->student->name ?? '—' }}</div>
                                    <div class="gb-small">
                                        @if ($grade->student?->lrn) LRN {{ $grade->student->lrn }} @endif
                                        @if ($grade->student?->section) · {{ $grade->student->section->name }} @endif
                                    </div>
                                </td>
                                <td>{{ $grade->subject->name ?? '—' }}</td>
                                <td>{{ $grade->quarter }}</td>
                                <td class="num">{{ $grade->written_works ?? '—' }}</td>
                                <td class="num">{{ $grade->performance_tasks ?? '—' }}</td>
                                <td class="num">{{ $grade->quarterly_assessment ?? '—' }}</td>
                                <td class="num" style="font-weight:800; color:{{ $grade->isPassed() ? '#15803d' : '#b91c1c' }};">{{ $grade->final_grade ?? '—' }}</td>
                                <td>
                                    @if ($grade->final_grade !== null)
                                        <span class="gb-pill {{ $grade->isPassed() ? 'pass' : 'fail' }}">{{ $grade->isPassed() ? 'Passed' : 'Failed' }}</span>
                                    @else
                                        <span style="color:var(--text-3);">—</span>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    @if ($editable)
                                        <button type="button" class="gb-btn ghost sm js-edit"
                                            data-class="{{ $key }}" data-student="{{ $grade->user_id }}" data-quarter="{{ $grade->quarter }}"
                                            data-ww="{{ $grade->written_works }}" data-pt="{{ $grade->performance_tasks }}" data-qa="{{ $grade->quarterly_assessment }}">Edit</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="gb-pager">{{ $grades->links() }}</div>
        @else
            <div class="gb-empty">{{ array_filter($filters) ? 'No grades match your filters.' : 'No grades encoded yet for this school year.' }}</div>
        @endif
    </div>
</div>

<script>
(function () {
    var form = document.getElementById('gbForm');
    if (!form) return;
    var ROSTER  = @json($roster);
    var PASSING = {{ \App\Models\Grade::PASSING }};
    var cls = document.getElementById('f_class'), stu = document.getElementById('f_student'), subj = document.getElementById('f_subject');
    var finalBox = document.getElementById('f_final');
    var scores = ['f_ww', 'f_pt', 'f_qa'].map(function (id) { return document.getElementById(id); });

    function fillStudents(selected) {
        var opt = cls.options[cls.selectedIndex];
        stu.innerHTML = '';
        if (!cls.value) {
            subj.value = '';
            stu.add(new Option('Choose a class first', ''));
            return;
        }
        subj.value = opt.dataset.subject;
        var list = ROSTER[opt.dataset.section] || [];
        stu.add(new Option(list.length ? '— Choose a student —' : 'No students in this section yet', ''));
        list.forEach(function (s) { stu.add(new Option(s.name + (s.lrn ? ' · ' + s.lrn : ''), s.id)); });
        if (selected) stu.value = String(selected);
    }

    function updateFinal() {
        var v = scores.map(function (el) { var n = parseFloat(el.value); return isNaN(n) ? 0 : n; });
        var any = scores.some(function (el) { return el.value !== ''; });
        var f = Math.round((v[0] * 0.25 + v[1] * 0.50 + v[2] * 0.25) * 100) / 100;
        finalBox.className = 'gb-final' + (any ? (f >= PASSING ? ' pass' : ' fail') : '');
        finalBox.textContent = any ? 'Final: ' + f.toFixed(2) + (f >= PASSING ? ' · Passed' : ' · Failed') : 'Final: —';
    }

    cls.addEventListener('change', function () { fillStudents(null); });
    scores.forEach(function (el) { el.addEventListener('input', updateFinal); });

    document.querySelectorAll('.js-edit').forEach(function (b) {
        b.addEventListener('click', function () {
            var d = b.dataset;
            cls.value = d.class;
            fillStudents(d.student);
            document.getElementById('f_quarter').value = d.quarter || 'Q1';
            scores[0].value = d.ww || ''; scores[1].value = d.pt || ''; scores[2].value = d.qa || '';
            updateFinal();
            document.getElementById('encodeCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Restore after a validation error
    fillStudents(@json(old('student_id')));
    updateFinal();
})();
</script>
@endsection
