@extends('layouts.teacher')
@section('title', 'Gradebook')

@section('content')
<style>
    .gb-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--r-lg); box-shadow:var(--shadow-sm); padding:18px; margin-bottom:16px; }
    .gb-card h2 { font-size:15px; font-weight:800; color:var(--text-1); margin:0 0 4px; }
    .gb-muted { font-size:12.5px; color:var(--text-2); margin:0 0 14px; line-height:1.5; }
    .gb-grid { display:grid; grid-template-columns: 1.6fr 1.4fr .7fr; gap:12px; }
    .gb-grid3 { display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; align-items:end; margin-top:12px; }
    .gb-actions { grid-column:1 / -1; display:flex; justify-content:flex-end; align-items:center; gap:10px; }
    .gb-label { display:block; font-size:12.5px; font-weight:700; color:var(--text-1); margin-bottom:6px; }
    .gb-in { width:100%; height:44px; padding:0 12px; border:1.5px solid var(--border); border-radius:10px; font-size:14px; background:var(--surface); color:var(--text-1); box-sizing:border-box; }
    .gb-in:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
    .gb-in:disabled { opacity:.6; }
    .gb-final { height:44px; display:flex; align-items:center; gap:8px; padding:0 14px; border-radius:10px; font-weight:800; font-size:14px; white-space:nowrap;
        background:var(--accent-lt); color:var(--text-1); border:1px solid var(--border); }
    .gb-part { display:flex; align-items:center; justify-content:space-between; gap:8px; min-height:36px; margin-bottom:6px; }
    .gb-part .gb-label { margin:0; }
    .gb-pct { display:inline-flex; align-items:center; gap:4px; font-size:13px; font-weight:700; color:var(--text-2); }
    .gb-pct input { width:56px; height:36px; padding:0 4px; border:1.5px solid var(--border); border-radius:8px; background:var(--surface);
        color:var(--text-1); font-size:14px; font-weight:700; text-align:center; font-variant-numeric:tabular-nums; -moz-appearance:textfield; appearance:textfield; }
    .gb-pct input::-webkit-outer-spin-button, .gb-pct input::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
    .gb-pct input:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
    .gb-pct input:disabled { background:#F1F5F9; color:var(--text-2); border-color:transparent; }
    .gb-pctbar { display:flex; flex-wrap:wrap; align-items:center; gap:8px 16px; margin-top:12px; padding-top:12px; border-top:1px solid var(--border); }
    .gb-pctbar[hidden] { display:none; }
    .gb-pcttotal { font-size:13.5px; font-weight:800; color:#15803d; font-variant-numeric:tabular-nums; }
    .gb-pcttotal.fail { color:#b91c1c; }
    .gb-check { display:flex; align-items:center; gap:10px; min-height:40px; font-size:13.5px; color:var(--text-1); cursor:pointer; }
    .gb-check input { width:18px; height:18px; flex-shrink:0; accent-color:#2563eb; }
    .gb-pctbar .tp-btn { margin-left:auto; }
    .gb-pct-note { font-size:13px; color:var(--text-2); margin:10px 0 0; line-height:1.5; }
    .gb-pct-note:empty { display:none; }
    .gb-pct-note.locked { color:#92400E; font-weight:600; }
    @media (max-width: 760px) {
        .gb-pctbar .tp-btn { margin-left:0; width:100%; justify-content:center; }
        .gb-actions { justify-content:space-between; }
    }
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
    .gb-table th { text-align:left; font-size:12.5px; font-weight:700; color:var(--text-2); padding:9px 10px; border-bottom:1px solid var(--border); white-space:nowrap; }
    .gb-table td { padding:10px; border-bottom:1px solid var(--border); color:var(--text-1); vertical-align:middle; }
    .gb-table tr:last-child td { border-bottom:0; }
    .gb-table td.num { text-align:right; font-variant-numeric:tabular-nums; }
    .gb-table th.num { text-align:right; }
    .gb-name { font-weight:700; }
    .gb-small { display:flex; flex-wrap:wrap; gap:0 10px; font-size:12px; color:var(--text-3); margin-top:2px; }
    .gb-table td.gb-final-cell { font-weight:800; }
    .gb-table td.gb-final-cell.pass { color:#15803d; }
    .gb-table td.gb-final-cell.fail { color:#b91c1c; }
    .gb-mobile { display:none; }
    .gb-pager { margin-top:14px; }
    @media (max-width: 860px) {
        .gb-grid, .gb-grid3, .gb-filter { grid-template-columns: 1fr; }
    }

    /* Phones: each grade becomes a compact block instead of a 9-column table that hid the scores and Edit
       off-screen. Selectors start with .main-content to beat the layout's table rules. */
    @media (max-width: 760px) {
        .main-content .gb-scroll { overflow: visible; }
        .main-content .gb-table { display: block; min-width: 0; overflow: visible; }
        .main-content .gb-table thead { display: none; }
        .main-content .gb-table tbody { display: block; }
        .main-content .gb-table tr {
            display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 4px 12px;
            grid-template-areas: "who final" "subj remark" "scores edit";
            padding: 12px 0; border-bottom: 1px solid var(--border);
        }
        .main-content .gb-table tr:last-child { border-bottom: 0; }
        .main-content .gb-table td { padding: 0; border: 0; white-space: normal; }
        .main-content .gb-table .gb-c-ww, .main-content .gb-table .gb-c-pt,
        .main-content .gb-table .gb-c-qa, .main-content .gb-table .gb-c-q { display: none; }
        .gb-c-who { grid-area: who; } .gb-c-subj { grid-area: subj; color: var(--text-2); }
        .main-content .gb-table td.gb-final-cell { grid-area: final; text-align: right; font-size: 16px; }
        .gb-c-remark { grid-area: remark; text-align: right; }
        .gb-c-scores { grid-area: scores; font-size: 12.5px; color: var(--text-2); }
        .main-content .gb-table td.gb-c-edit { grid-area: edit; text-align: right; }
        .gb-mobile { display: inline; }
    }
</style>

@php
    $oldClass = old('class_key');
    $w = collect(\App\Models\Grade::DEFAULT_WEIGHTS)->map(fn ($v) => $v . '%');
@endphp

<div class="tp-page">
    <div class="tp-head">
        <div>
            <h1 class="tp-title">Gradebook</h1>
            <p class="tp-sub">Quarterly grades for your classes, school year {{ $schoolYear }}. Passing is {{ \App\Models\Grade::PASSING }}.
                Saving the same student, subject and quarter again updates the grade.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="gb-err" role="alert">{{ $errors->first() }}</div>
    @endif

    {{-- ── Encode / edit ── --}}
    <div class="gb-card" id="encodeCard">
        <h2>Encode grade</h2>
        @if ($classList->isEmpty())
            <div class="gb-empty">You have no classes yet. The admin assigns your classes in <b>Class Schedules</b>.</div>
        @else
            <p class="gb-muted">Choose the class first; the student list then shows only that section. Blank scores count as 0.
                Each part counts {{ $w['written_works'] }}, {{ $w['performance_tasks'] }} and {{ $w['quarterly_assessment'] }} unless you change the percentage beside it before the first grade of the quarter.</p>
            <form method="POST" action="{{ route('teacher.gradebook.store') }}" id="gbForm">
                @csrf
                <input type="hidden" name="subject_id" id="f_subject" value="{{ old('subject_id') }}">
                <div class="gb-grid">
                    <div>
                        <label class="gb-label" for="f_class">Class</label>
                        <select class="gb-in" name="class_key" id="f_class" required>
                            <option value="">Choose a class</option>
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
                        <div class="gb-part">
                            <label class="gb-label" for="f_ww">Written works</label>
                            <span class="gb-pct"><input class="js-pct" type="number" step="1" min="0" max="100" inputmode="numeric" name="percent[written_works]" id="p_ww" value="{{ \App\Models\Grade::DEFAULT_WEIGHTS['written_works'] }}" aria-label="Written works percentage" disabled>%</span>
                        </div>
                        <input class="gb-in js-score" type="number" step="0.01" min="0" max="100" inputmode="decimal" name="written_works" id="f_ww" value="{{ old('written_works') }}" placeholder="0–100">
                    </div>
                    <div>
                        <div class="gb-part">
                            <label class="gb-label" for="f_pt">Performance tasks</label>
                            <span class="gb-pct"><input class="js-pct" type="number" step="1" min="0" max="100" inputmode="numeric" name="percent[performance_tasks]" id="p_pt" value="{{ \App\Models\Grade::DEFAULT_WEIGHTS['performance_tasks'] }}" aria-label="Performance tasks percentage" disabled>%</span>
                        </div>
                        <input class="gb-in js-score" type="number" step="0.01" min="0" max="100" inputmode="decimal" name="performance_tasks" id="f_pt" value="{{ old('performance_tasks') }}" placeholder="0–100">
                    </div>
                    <div>
                        <div class="gb-part">
                            <label class="gb-label" for="f_qa">Quarterly assessment</label>
                            <span class="gb-pct"><input class="js-pct" type="number" step="1" min="0" max="100" inputmode="numeric" name="percent[quarterly_assessment]" id="p_qa" value="{{ \App\Models\Grade::DEFAULT_WEIGHTS['quarterly_assessment'] }}" aria-label="Quarterly assessment percentage" disabled>%</span>
                        </div>
                        <input class="gb-in js-score" type="number" step="0.01" min="0" max="100" inputmode="decimal" name="quarterly_assessment" id="f_qa" value="{{ old('quarterly_assessment') }}" placeholder="0–100">
                    </div>
                    <div class="gb-actions">
                        <div class="gb-final" id="f_final" aria-live="polite">Final: —</div>
                        <button type="submit" class="tp-btn" id="f_save">Save grade</button>
                    </div>
                </div>
                <div class="gb-pctbar" id="pctBar" hidden>
                    <span class="gb-pcttotal" id="p_total" aria-live="polite">Total 100%</span>
                    <label class="gb-check">
                        <input type="checkbox" name="all_quarters" value="1" id="p_all">
                        Use for every quarter with no grades yet
                    </label>
                    <button type="button" class="tp-btn ghost" id="p_save" data-url="{{ route('teacher.gradebook.weights') }}">Save percentages only</button>
                </div>
                <p class="gb-pct-note" id="p_note" role="status"></p>
            </form>
        @endif
    </div>

    {{-- ── Grades ── --}}
    <div class="gb-card">
        <h2>Grades for school year {{ $schoolYear }}</h2>
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
            <button type="submit" class="tp-btn">Filter</button>
            @if (array_filter($filters))
                <a href="{{ route('teacher.gradebook.index') }}" class="tp-btn ghost">Clear</a>
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
                                <td class="gb-c-who">
                                    <div class="gb-name">{{ $grade->student->name ?? 'Student removed' }}</div>
                                    <div class="gb-small">
                                        @if ($grade->student?->lrn)<span>LRN {{ $grade->student->lrn }}</span>@endif
                                        @if ($grade->student?->section)<span>{{ $grade->student->section->name }}</span>@endif
                                    </div>
                                </td>
                                <td class="gb-c-subj">{{ $grade->subject->name ?? 'Subject removed' }}<span class="gb-mobile">, {{ $grade->quarter }}</span></td>
                                <td class="gb-c-q">{{ $grade->quarter }}</td>
                                <td class="num gb-c-ww">{{ $grade->written_works ?? '—' }}</td>
                                <td class="num gb-c-pt">{{ $grade->performance_tasks ?? '—' }}</td>
                                <td class="num gb-c-qa">{{ $grade->quarterly_assessment ?? '—' }}</td>
                                <td class="gb-c-scores gb-mobile">Written {{ $grade->written_works ?? '—' }}, performance {{ $grade->performance_tasks ?? '—' }}, quarterly {{ $grade->quarterly_assessment ?? '—' }}</td>
                                <td class="num gb-final-cell {{ $grade->final_grade === null ? '' : ($grade->isPassed() ? 'pass' : 'fail') }}">{{ $grade->final_grade ?? '—' }}</td>
                                <td class="gb-c-remark">
                                    @if ($grade->final_grade !== null)
                                        <span class="tp-pill {{ $grade->isPassed() ? 'ok' : 'bad' }}">{{ $grade->isPassed() ? 'Passed' : 'Failed' }}</span>
                                    @endif
                                </td>
                                <td class="gb-c-edit" style="text-align:right;">
                                    @if ($editable)
                                        <button type="button" class="tp-btn ghost sm js-edit"
                                            data-class="{{ $key }}" data-student="{{ $grade->user_id }}" data-quarter="{{ $grade->quarter }}"
                                            data-ww="{{ $grade->written_works }}" data-pt="{{ $grade->performance_tasks }}" data-qa="{{ $grade->quarterly_assessment }}"
                                            aria-label="Edit {{ $grade->quarter }} {{ $grade->subject->name ?? '' }} grade of {{ $grade->student->name ?? 'student' }}">Edit</button>
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
    var WEIGHTS  = @json($weights);   // per class and quarter, as GradeService::classWeights() gives them
    var DEFAULTS = @json(\App\Models\Grade::DEFAULT_WEIGHTS);
    var PARTS = ['written_works', 'performance_tasks', 'quarterly_assessment'];
    var cls = document.getElementById('f_class'), stu = document.getElementById('f_student'), subj = document.getElementById('f_subject');
    var quarter = document.getElementById('f_quarter');
    var finalBox = document.getElementById('f_final');
    var scores = ['f_ww', 'f_pt', 'f_qa'].map(function (id) { return document.getElementById(id); });
    var pInputs = ['p_ww', 'p_pt', 'p_qa'].map(function (id) { return document.getElementById(id); });
    var pBar = document.getElementById('pctBar'), pTotal = document.getElementById('p_total'), pNote = document.getElementById('p_note');
    var pSave = document.getElementById('p_save'), pAll = document.getElementById('p_all'), gradeSave = document.getElementById('f_save');

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
        stu.add(new Option(list.length ? 'Choose a student' : 'No students in this section yet', ''));
        list.forEach(function (s) { stu.add(new Option(s.name + (s.lrn ? ' (LRN ' + s.lrn + ')' : ''), s.id)); });
        if (selected) stu.value = String(selected);
    }

    function savedRow() {
        return WEIGHTS[cls.value] && WEIGHTS[cls.value][quarter.value];
    }

    function typed() {
        return pInputs.map(function (el) { return parseInt(el.value, 10) || 0; });
    }

    function total() {
        return typed().reduce(function (sum, n) { return sum + n; }, 0);
    }

    // The saved percentages of the chosen class and quarter
    function showWeights() {
        var row = savedRow();
        pInputs.forEach(function (el, i) {
            el.value = (row || DEFAULTS)[PARTS[i]];
            el.disabled = !row || row.locked;
        });
        pAll.checked = false;
        pNote.className = 'gb-pct-note' + (row && row.locked ? ' locked' : '');
        pNote.textContent = !row ? 'Choose a class to change the percentages.'
            : row.locked ? 'Percentages are locked for ' + quarter.value + ' because this class already has grades in that quarter.'
            : '';
        checkPercent();
    }

    function checkPercent() {
        var row = savedRow(), now = typed(), sum = total();
        var changed = !!row && PARTS.some(function (part, i) { return row[part] !== now[i]; });
        pBar.hidden = !changed;
        pTotal.textContent = sum === 100 ? 'Total 100%' : 'Total ' + sum + '%, must be 100%';
        pTotal.className = 'gb-pcttotal' + (sum === 100 ? '' : ' fail');
        pSave.disabled = sum !== 100;
        gradeSave.disabled = sum !== 100;
        updateFinal();
    }

    function updateFinal() {
        var w = typed();
        var v = scores.map(function (el) { var n = parseFloat(el.value); return isNaN(n) ? 0 : n; });
        var any = scores.some(function (el) { return el.value !== ''; }) && total() === 100;
        // same formula as Grade::computeFinal() on the server
        var f = Math.round(v[0] * w[0] + v[1] * w[1] + v[2] * w[2]) / 100;
        finalBox.className = 'gb-final' + (any ? (f >= PASSING ? ' pass' : ' fail') : '');
        finalBox.textContent = any ? 'Final ' + f.toFixed(2) + (f >= PASSING ? ', passed' : ', failed') : 'Final: —';
    }

    cls.addEventListener('change', function () { fillStudents(null); showWeights(); });
    quarter.addEventListener('change', showWeights);
    scores.forEach(function (el) { el.addEventListener('input', updateFinal); });

    document.querySelectorAll('.js-edit').forEach(function (b) {
        b.addEventListener('click', function () {
            var d = b.dataset;
            cls.value = d.class;
            fillStudents(d.student);
            quarter.value = d.quarter || 'Q1';
            scores[0].value = d.ww || ''; scores[1].value = d.pt || ''; scores[2].value = d.qa || '';
            showWeights();
            document.getElementById('encodeCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    pInputs.forEach(function (el) { el.addEventListener('input', checkPercent); });

    // Set the percentages without saving a grade. Save grade also saves them.
    pSave.addEventListener('click', function () {
        var key = cls.value, values = typed();
        var body = new FormData();
        body.append('_token', form.querySelector('[name=_token]').value);
        body.append('class_key', key);
        body.append('quarter', quarter.value);
        PARTS.forEach(function (part, i) { body.append(part, values[i]); });
        if (pAll.checked) body.append('all_quarters', '1');

        pSave.disabled = true;
        fetch(pSave.dataset.url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        }).then(function (res) {
            return res.json().then(function (data) {
                if (!res.ok) { window.dpToast(data.message, 'error'); return; }
                data.saved.forEach(function (q) {
                    PARTS.forEach(function (part, i) { WEIGHTS[key][q][part] = values[i]; });
                });
                window.dpToast(data.message, 'success');
                showWeights();
            });
        }).catch(function () {
            window.dpToast('Could not reach the server. Check your connection and try again.', 'error');
        }).finally(checkPercent);
    });

    // Restore after a validation error
    fillStudents(@json(old('student_id')));
    showWeights();
})();
</script>
@endsection
