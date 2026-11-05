@extends('layouts.teacher')
@section('title', 'Gradebook')

@section('content')
<style>
    .gb-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--r-lg); box-shadow:var(--shadow-sm); padding:18px; margin-bottom:16px; }
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
    .gb-pct summary { display:flex; align-items:center; justify-content:space-between; gap:12px; cursor:pointer; list-style:none; }
    .gb-pct summary::-webkit-details-marker { display:none; }
    .gb-pct summary:focus-visible { outline:2px solid #2563eb; outline-offset:4px; border-radius:6px; }
    .gb-pct-title { display:block; font-size:15px; font-weight:800; color:var(--text-1); }
    .gb-pct-sub { display:block; margin:4px 0 0; }
    .gb-pct-chev { display:flex; flex-shrink:0; color:var(--text-2); transition:transform .15s ease; }
    .gb-pct[open] .gb-pct-chev { transform:rotate(90deg); }
    .gb-pct-body { margin-top:16px; padding-top:16px; border-top:1px solid var(--border); }
    .gb-grid.gb-grid-2 { grid-template-columns: 1fr 160px; }
    .gb-check { display:flex; align-items:center; gap:10px; min-height:40px; margin-top:10px; font-size:13.5px; color:var(--text-1); cursor:pointer; }
    .gb-check input { width:18px; height:18px; flex-shrink:0; accent-color:#2563eb; }
    .gb-pct-note { font-size:13px; color:var(--text-2); margin:6px 0 0; line-height:1.5; }
    .gb-pct-note:empty { display:none; }
    .gb-pct-note.locked { color:#92400E; font-weight:600; }
    #p_save { margin-top:12px; }
    @media (max-width: 760px) { .gb-grid.gb-grid-2 { grid-template-columns: 1fr; } }
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

    {{-- ── Percentages of one class and quarter ── --}}
    @if ($classList->isNotEmpty())
        <details class="gb-card gb-pct" id="pctCard">
            <summary>
                <span>
                    <span class="gb-pct-title">Grade percentages</span>
                    <span class="gb-muted gb-pct-sub">Each class uses {{ $w['written_works'] }}, {{ $w['performance_tasks'] }} and {{ $w['quarterly_assessment'] }} unless you change it. Set this before saving the first grade of the quarter.</span>
                </span>
                <span class="gb-pct-chev" aria-hidden="true"><x-ui-icon name="chevron-right" size="18" /></span>
            </summary>
            <form method="POST" action="{{ route('teacher.gradebook.weights') }}" id="pctForm" class="gb-pct-body">
                @csrf
                <div class="gb-grid gb-grid-2">
                    <div>
                        <label class="gb-label" for="p_class">Class</label>
                        <select class="gb-in" name="class_key" id="p_class" required>
                            <option value="">Choose a class</option>
                            @foreach ($classList as $c)
                                <option value="{{ $c['key'] }}">{{ $c['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="gb-label" for="p_quarter">Quarter</label>
                        <select class="gb-in" name="quarter" id="p_quarter" required>
                            @foreach (\App\Services\GradeService::QUARTERS as $q)
                                <option value="{{ $q }}">{{ $q }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="gb-grid3">
                    <div>
                        <label class="gb-label" for="p_ww">Written works (%)</label>
                        <input class="gb-in js-pct" type="number" step="1" min="0" max="100" inputmode="numeric" name="written_works" id="p_ww" required>
                    </div>
                    <div>
                        <label class="gb-label" for="p_pt">Performance tasks (%)</label>
                        <input class="gb-in js-pct" type="number" step="1" min="0" max="100" inputmode="numeric" name="performance_tasks" id="p_pt" required>
                    </div>
                    <div>
                        <label class="gb-label" for="p_qa">Quarterly assessment (%)</label>
                        <input class="gb-in js-pct" type="number" step="1" min="0" max="100" inputmode="numeric" name="quarterly_assessment" id="p_qa" required>
                    </div>
                    <div class="gb-final" id="p_total" aria-live="polite">Total: 100%</div>
                </div>
                <label class="gb-check">
                    <input type="checkbox" name="all_quarters" value="1" id="p_all">
                    Use these for every quarter that has no grades yet
                </label>
                <p class="gb-pct-note" id="p_note" role="status"></p>
                <button type="submit" class="tp-btn" id="p_save">Save percentages</button>
            </form>
        </details>
    @endif

    {{-- ── Encode / edit ── --}}
    <div class="gb-card" id="encodeCard">
        <h2>Encode grade</h2>
        @if ($classList->isEmpty())
            <div class="gb-empty">You have no classes yet. The admin assigns your classes in <b>Class Schedules</b>.</div>
        @else
            <p class="gb-muted">Choose the class first; the student list then shows only that section. Blank scores count as 0.</p>
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
                        <label class="gb-label" for="f_ww">Written works (<span data-weight="written_works">{{ $w['written_works'] }}</span>)</label>
                        <input class="gb-in js-score" type="number" step="0.01" min="0" max="100" inputmode="decimal" name="written_works" id="f_ww" value="{{ old('written_works') }}" placeholder="0–100">
                    </div>
                    <div>
                        <label class="gb-label" for="f_pt">Performance tasks (<span data-weight="performance_tasks">{{ $w['performance_tasks'] }}</span>)</label>
                        <input class="gb-in js-score" type="number" step="0.01" min="0" max="100" inputmode="decimal" name="performance_tasks" id="f_pt" value="{{ old('performance_tasks') }}" placeholder="0–100">
                    </div>
                    <div>
                        <label class="gb-label" for="f_qa">Quarterly assessment (<span data-weight="quarterly_assessment">{{ $w['quarterly_assessment'] }}</span>)</label>
                        <input class="gb-in js-score" type="number" step="0.01" min="0" max="100" inputmode="decimal" name="quarterly_assessment" id="f_qa" value="{{ old('quarterly_assessment') }}" placeholder="0–100">
                    </div>
                    <div style="display:flex; gap:10px; align-items:end;">
                        <div class="gb-final" id="f_final" aria-live="polite">Final: —</div>
                        <button type="submit" class="tp-btn">Save grade</button>
                    </div>
                </div>
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

    function weightsNow() {
        return (WEIGHTS[cls.value] && WEIGHTS[cls.value][quarter.value]) || DEFAULTS;
    }

    function showWeights() {
        var w = weightsNow();
        document.querySelectorAll('[data-weight]').forEach(function (el) { el.textContent = w[el.dataset.weight] + '%'; });
        updateFinal();
    }

    function updateFinal() {
        var w = weightsNow();
        var v = scores.map(function (el) { var n = parseFloat(el.value); return isNaN(n) ? 0 : n; });
        var any = scores.some(function (el) { return el.value !== ''; });
        // same formula as Grade::computeFinal() on the server
        var f = Math.round(v[0] * w.written_works + v[1] * w.performance_tasks + v[2] * w.quarterly_assessment) / 100;
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

    // ── Percentages of one class and quarter ──
    var pForm = document.getElementById('pctForm');
    var pClass = document.getElementById('p_class'), pQuarter = document.getElementById('p_quarter');
    var pInputs = ['p_ww', 'p_pt', 'p_qa'].map(function (id) { return document.getElementById(id); });
    var pTotal = document.getElementById('p_total'), pNote = document.getElementById('p_note');
    var pSave = document.getElementById('p_save'), pAll = document.getElementById('p_all');

    function pctRow() {
        return WEIGHTS[pClass.value] && WEIGHTS[pClass.value][pQuarter.value];
    }

    function checkTotal() {
        var row = pctRow();
        var sum = pInputs.reduce(function (t, el) { return t + (parseInt(el.value, 10) || 0); }, 0);
        var locked = row && row.locked && !pAll.checked;
        pTotal.textContent = 'Total: ' + sum + '%';
        pTotal.className = 'gb-final' + (sum === 100 ? ' pass' : ' fail');
        pSave.disabled = !row || locked || sum !== 100;
    }

    function fillPct() {
        var row = pctRow();
        pInputs.forEach(function (el, i) {
            el.value = (row || DEFAULTS)[PARTS[i]];
            el.disabled = !row || (row.locked && !pAll.checked);
        });
        pNote.className = 'gb-pct-note' + (row && row.locked ? ' locked' : '');
        pNote.textContent = !row ? 'Choose a class to see its percentages.'
            : row.locked ? (pAll.checked ? pQuarter.value + ' stays as it is because it already has grades. The other open quarters will change.'
                                         : 'Locked for ' + pQuarter.value + ' because this class already has grades in that quarter.')
            : '';
        checkTotal();
    }

    pClass.addEventListener('change', fillPct);
    pQuarter.addEventListener('change', fillPct);
    pAll.addEventListener('change', fillPct);
    pInputs.forEach(function (el) { el.addEventListener('input', checkTotal); });

    pForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var key = pClass.value, values = pInputs.map(function (el) { return parseInt(el.value, 10); });
        pSave.disabled = true;
        fetch(pForm.action, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(pForm)
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
        }).finally(fillPct);
    });

    // Restore after a validation error
    fillStudents(@json(old('student_id')));
    showWeights();
    fillPct();
})();
</script>
@endsection
