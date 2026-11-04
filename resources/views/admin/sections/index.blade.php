@extends('layouts.admin')
@section('title', 'Sections & Advisers')

@section('content')
@include('admin.partials.list-ui')
@php
    // Which sections each teacher already advises (shown in the adviser lists)
    $advisedBy   = $sections->whereNotNull('adviser_id')->groupBy('adviser_id');
    $teacherIds  = $teachers->pluck('id')->map(fn ($id) => (int) $id)->all();
    $active      = $sections->where('is_active', true);
    $noAdviser   = $active->whereNull('adviser_id')->count();
    $inactive    = $sections->count() - $active->count();
    $withAdviser = $active->count() - $noAdviser;
    $pct         = $active->count() ? (int) round($withAdviser / $active->count() * 100) : 0;
    $byGrade     = $sections->groupBy(fn ($s) => (string) $s->grade_level);
    $advisesText = fn ($teacherId, $exceptId = null) => ($advisedBy[$teacherId] ?? collect())
        ->where('id', '!=', $exceptId)->map(fn ($x) => $x->grade_level . '-' . $x->name)->implode(', ');
    $openNew     = $errors->any() && old('_form') === 'new';
@endphp
<style>
    .lm-tabs button { display: inline-flex; align-items: center; gap: 7px; min-height: 34px; padding: 0 12px; border: 0; border-radius: 8px; background: transparent;
                      font: inherit; font-size: 13px; font-weight: 700; color: var(--slate-600); cursor: pointer; white-space: nowrap; transition: background-color .15s ease, color .15s ease; }
    .lm-tabs button:hover { color: var(--slate-900); }
    .lm-tabs button span { font-size: 12px; color: var(--slate-500); font-variant-numeric: tabular-nums; }
    .lm-tabs button[aria-pressed="true"] { background: var(--surface-card); color: var(--slate-900); box-shadow: 0 1px 2px rgba(15,23,42,.1), 0 0 0 1px rgba(15,23,42,.04); }
    .lm-tabs button:focus-visible { outline: 2px solid #2563EB; outline-offset: 2px; }
    .lm-table tr[hidden] { display: none !important; }
    .sx-grade td { padding: 9px 16px; background: var(--slate-50); font-size: 13px; font-weight: 800; color: var(--slate-800); border-bottom: 1px solid var(--lm-line); }
    .sx-grade small { margin-left: 8px; font-size: 12.5px; font-weight: 600; color: var(--slate-500); }
    .sx-name { font-weight: 700; color: var(--slate-900); }
    .sx-off .sx-name, .sx-off .lm-count { color: var(--slate-400); }
    .sx-adv { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin: 0; }
    .sx-adv select { min-height: 36px; max-width: 260px; padding: 0 10px; border-radius: 9px; border: 1px solid var(--lm-line); background: var(--surface-card); color: var(--slate-900); font: inherit; font-size: 13.5px; cursor: pointer; }
    .sx-adv select:focus { outline: none; border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,.16); }
    .sx-adv .lm-btn { min-height: 36px; padding: 0 12px; font-size: 13px; }
    .sx-adv .lm-btn[hidden] { display: none; }
    .sx-need { display: inline-flex; padding: 2px 9px; border-radius: 99px; background: #FEF3C7; color: #92400E; font-size: 12px; font-weight: 700; white-space: nowrap; }
    [data-theme="dark"] .sx-need { background: rgba(251,191,36,.14); color: #FBBF24; }
    .lm-count { display: inline-grid; place-items: center; min-width: 30px; height: 24px; padding: 0 8px; border-radius: 7px; background: var(--slate-100); color: var(--slate-700); font-size: 12.5px; font-weight: 700; font-variant-numeric: tabular-nums; }
    .sx-edit { min-height: 36px; padding: 0 12px; font-size: 13px; }
    .sx-danger { margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--lm-line); }
    .sx-danger .lm-btn.danger { background: transparent; color: #B91C1C; border-color: #FECACA; }
    .sx-danger .lm-btn.danger:hover:not(:disabled) { background: #FEF2F2; }
    [data-theme="dark"] .sx-danger .lm-btn.danger { color: #FCA5A5; border-color: rgba(248,113,113,.35); }
    .sx-danger p { font-size: 12.5px; color: var(--slate-500); line-height: 1.5; margin-top: 6px; }
    .sx-toggle { display: flex; align-items: center; gap: 10px; margin-top: 16px; font-size: 13.5px; font-weight: 600; color: var(--slate-700); cursor: pointer; }
    .sx-toggle input { width: 18px; height: 18px; accent-color: #16A34A; }
    .sx-help { margin-top: 4px; font-size: 12.5px; color: var(--slate-500); }
    @media (max-width: 760px) {
        .sx-grade { display: block; }
        .sx-grade td { display: block; }
        .sx-grade td::before { display: none; }
        .sx-adv { flex: 1 1 auto; min-width: 0; }
        .sx-adv select { max-width: none; flex: 1 1 auto; min-width: 0; width: 100%; }
    }
</style>

<div class="lm" id="sxRoot">
    <header class="lm-head">
        <div>
            <h1 class="lm-title">Sections &amp; Advisers</h1>
            <p class="lm-sub">Every active section needs an adviser — the adviser reviews the enrollment forms for that section.</p>
        </div>
        <div class="lm-actions">
            <button type="button" class="lm-btn primary" data-open="sxNew">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                New section
            </button>
        </div>
    </header>

    @if ($errors->any() && !$openNew)
        <div class="lm-alert" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <section class="lm-card lm-summary" aria-label="Summary">
        <div class="lm-figures">
            <div><span>Active sections</span><b>{{ number_format($active->count()) }}</b></div>
            <div><span>Need an adviser</span><b style="{{ $noAdviser ? 'color:#B45309' : '' }}">{{ number_format($noAdviser) }}</b></div>
            <div><span>Students placed</span><b>{{ number_format($sections->sum('students_count')) }}</b></div>
        </div>
        <div class="lm-progress">
            <div class="lm-bar" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Active sections with an adviser"><i style="width: {{ $pct }}%"></i></div>
            <span>{{ $withAdviser }} of {{ $active->count() }} active sections have an adviser</span>
        </div>
    </section>

    <section class="lm-card lm-list" aria-label="Sections">
        <div class="lm-toolbar">
            <div class="lm-tabs" role="group" aria-label="Show">
                <button type="button" class="sx-tab" data-show="all" aria-pressed="true">All <span>{{ $sections->count() }}</span></button>
                <button type="button" class="sx-tab" data-show="need" aria-pressed="false">Need an adviser <span>{{ $noAdviser }}</span></button>
                <button type="button" class="sx-tab" data-show="off" aria-pressed="false">Inactive <span>{{ $inactive }}</span></button>
            </div>
            <div class="lm-filters">
                <select id="sxGrade" aria-label="Grade">
                    <option value="">All grades</option>
                    @foreach ($grades as $g)<option value="{{ $g }}">Grade {{ $g }}</option>@endforeach
                </select>
                <label class="lm-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M21 21l-4.3-4.3M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                    <input type="search" id="sxSearch" placeholder="Section or adviser" aria-label="Search sections or advisers" autocomplete="off">
                </label>
            </div>
        </div>

        <div class="lm-table-wrap">
            <table class="lm-table">
                <thead>
                    <tr>
                        <th scope="col">Section</th>
                        <th scope="col">Students</th>
                        <th scope="col">Adviser</th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="lm-sr">Actions</span></th>
                    </tr>
                </thead>
                <tbody id="sxBody">
                    @forelse ($byGrade as $grade => $rows)
                        <tr class="sx-grade" data-grade="{{ $grade }}"><td colspan="5">Grade {{ $grade }}<small>{{ (int) $grade >= 11 ? 'Senior High' : 'Junior High' }} · {{ $rows->count() }} {{ Str::plural('section', $rows->count()) }}</small></td></tr>
                        @foreach ($rows as $s)
                            @php $need = $s->is_active && !$s->adviser_id; @endphp
                            <tr class="sx-row {{ $s->is_active ? '' : 'sx-off' }}" data-grade="{{ $s->grade_level }}"
                                data-state="{{ !$s->is_active ? 'off' : ($need ? 'need' : 'ok') }}"
                                data-search="{{ mb_strtolower($s->name . ' ' . ($s->adviser->name ?? '')) }}">
                                <td data-label="Section"><span class="sx-name">{{ $s->name }}</span></td>
                                <td data-label="Students"><span class="lm-count">{{ $s->students_count }}</span></td>
                                <td data-label="Adviser">
                                    {{-- Quick assign: the other fields are sent unchanged; Save shows only after a change --}}
                                    <form class="sx-adv" method="POST" action="{{ route('admin.sections.update', $s->id) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="name" value="{{ $s->name }}">
                                        <input type="hidden" name="grade_level" value="{{ $s->grade_level }}">
                                        <input type="hidden" name="is_active" value="{{ $s->is_active ? 1 : 0 }}">
                                        <select name="adviser_id" aria-label="Adviser for Grade {{ $s->grade_level }} {{ $s->name }}" data-initial="{{ $s->adviser_id }}">
                                            <option value="">No adviser</option>
                                            {{-- Keep a current adviser whose account is no longer approved, or saving would drop them --}}
                                            @if ($s->adviser && !in_array((int) $s->adviser_id, $teacherIds, true))
                                                <option value="{{ $s->adviser_id }}" selected>{{ $s->adviser->name }} (account {{ $s->adviser->status ?? 'inactive' }})</option>
                                            @endif
                                            @foreach ($teachers as $t)
                                                @php $also = $advisesText($t->id, $s->id); @endphp
                                                <option value="{{ $t->id }}" @selected((int) $s->adviser_id === (int) $t->id)>{{ $t->name }}{{ $also ? ' — also advises ' . $also : '' }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="lm-btn primary" hidden>Save</button>
                                    </form>
                                </td>
                                <td data-label="Status">
                                    @if (!$s->is_active)
                                        <span class="lm-status">Inactive</span>
                                    @elseif ($need)
                                        <span class="sx-need" title="Enrollment forms for this section can't be reviewed until it has an adviser">Needs an adviser</span>
                                    @else
                                        <span class="lm-status on">Active</span>
                                    @endif
                                </td>
                                <td class="lm-act">
                                    <button type="button" class="lm-btn sx-edit"
                                            data-edit="{{ route('admin.sections.update', $s->id) }}"
                                            data-delete="{{ route('admin.sections.destroy', $s->id) }}"
                                            data-name="{{ $s->name }}" data-grade="{{ $s->grade_level }}"
                                            data-adviser="{{ $s->adviser_id }}" data-active="{{ $s->is_active ? 1 : 0 }}"
                                            data-students="{{ $s->students_count }}"
                                            aria-label="Edit Grade {{ $s->grade_level }} {{ $s->name }}">Edit</button>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="5"><div class="lm-empty">
                            <b>No sections yet</b>
                            <span>Create the sections for this school year, then assign each one an adviser.</span>
                            <button type="button" class="lm-btn primary" data-open="sxNew">New section</button>
                        </div></td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="lm-empty" id="sxNone" hidden><b>No sections match</b><span>Try another search, tab or grade.</span></div>
        </div>
    </section>

    {{-- New section --}}
    <dialog class="lm-dialog small" id="sxNew" aria-labelledby="sxNewTitle" @if ($openNew) data-autoopen @endif>
        <form method="POST" action="{{ route('admin.sections.store') }}" class="lm-dialog-box" data-busy="Creating…">
            @csrf
            <input type="hidden" name="_form" value="new">
            <div class="lm-dialog-head">
                <h2 id="sxNewTitle">New section</h2>
                <button type="button" class="lm-close" data-close aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg></button>
            </div>
            <div class="lm-dialog-body">
                @if ($openNew)<div class="lm-alert" role="alert"><span>{{ $errors->first() }}</span></div>@endif
                <label class="lm-field" for="sxNewName">Section name</label>
                <input id="sxNewName" class="lm-input" type="text" name="name" value="{{ $openNew ? old('name') : '' }}" maxlength="100" placeholder="e.g. GUTIERREZ" autocomplete="off" required autofocus>
                <label class="lm-field" for="sxNewGrade">Grade</label>
                <select id="sxNewGrade" class="lm-input" name="grade_level" required>
                    <option value="">Choose a grade</option>
                    @foreach ($grades as $g)<option value="{{ $g }}" @selected($openNew && (string) old('grade_level') === (string) $g)>Grade {{ $g }}</option>@endforeach
                </select>
                <label class="lm-field" for="sxNewAdviser">Adviser <span>optional</span></label>
                <select id="sxNewAdviser" class="lm-input" name="adviser_id">
                    <option value="">No adviser yet</option>
                    @foreach ($teachers as $t)
                        @php $also = $advisesText($t->id); @endphp
                        <option value="{{ $t->id }}" @selected($openNew && (string) old('adviser_id') === (string) $t->id)>{{ $t->name }}{{ $also ? ' — advises ' . $also : '' }}</option>
                    @endforeach
                </select>
                <p class="lm-help">Only approved teacher accounts are listed.</p>
            </div>
            <div class="lm-dialog-foot end">
                <button type="button" class="lm-btn" data-close>Cancel</button>
                <button type="submit" class="lm-btn primary">Create section</button>
            </div>
        </form>
    </dialog>

    {{-- Edit section (one dialog, filled from the row) --}}
    <dialog class="lm-dialog small" id="sxEdit" aria-labelledby="sxEditTitle">
        <form method="POST" action="" class="lm-dialog-box" id="sxEditForm" data-busy="Saving…">
            @csrf @method('PUT')
            <input type="hidden" name="grade_level" id="sxEditGrade">
            <div class="lm-dialog-head">
                <h2 id="sxEditTitle">Edit section</h2>
                <button type="button" class="lm-close" data-close aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg></button>
            </div>
            <div class="lm-dialog-body">
                <label class="lm-field" for="sxEditName">Section name</label>
                <input id="sxEditName" class="lm-input" type="text" name="name" maxlength="100" autocomplete="off" required>
                <label class="lm-field" for="sxEditAdviser">Adviser</label>
                <select id="sxEditAdviser" class="lm-input" name="adviser_id">
                    <option value="">No adviser</option>
                    @foreach ($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                </select>
                <label class="sx-toggle"><input type="checkbox" name="is_active" value="1" id="sxEditActive"> Active this school year</label>
                <p class="sx-help">Turn off instead of deleting a section that already has students or records.</p>
                <div class="sx-danger">
                    <button type="button" class="lm-btn danger" id="sxDeleteBtn">Delete section…</button>
                    <p id="sxDeleteNote"></p>
                </div>
            </div>
            <div class="lm-dialog-foot end">
                <button type="button" class="lm-btn" data-close>Cancel</button>
                <button type="submit" class="lm-btn primary">Save changes</button>
            </div>
        </form>
    </dialog>

    {{-- Delete confirmation --}}
    <dialog class="lm-dialog small" id="sxDelete" aria-labelledby="sxDeleteTitle">
        <form method="POST" action="" class="lm-dialog-box" id="sxDeleteForm" data-busy="Deleting…">
            @csrf @method('DELETE')
            <div class="lm-dialog-body">
                <h2 class="lm-confirm-title" id="sxDeleteTitle">Delete this section?</h2>
                <p class="lm-help first"><b id="sxDeleteName"></b> will be removed. This can't be undone. A section that still has students, enrollments, quizzes or schedules can't be deleted — turn it off instead.</p>
            </div>
            <div class="lm-dialog-foot end">
                <button type="button" class="lm-btn" data-close autofocus>Keep it</button>
                <button type="submit" class="lm-btn danger">Delete</button>
            </div>
        </form>
    </dialog>
</div>

<script>
(function () {
    var root = document.getElementById('sxRoot');
    if (!root) return;
    var $ = function (id) { return document.getElementById(id); };

    function open(d) { if (d && !d.open) d.showModal(); }
    function close(d) {
        if (!d || !d.open || d.classList.contains('closing')) return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { d.close(); return; }
        d.classList.add('closing');
        d.addEventListener('animationend', function done() {
            d.removeEventListener('animationend', done);
            if (!d.classList.contains('closing')) return;   // already closed by the browser
            d.classList.remove('closing');
            d.close();
        });
    }
    root.querySelectorAll('dialog').forEach(function (d) {
        d.addEventListener('cancel', function (e) { if (e.cancelable) { e.preventDefault(); close(d); } });
        d.addEventListener('close', function () { d.classList.remove('closing'); });
        if (d.hasAttribute('data-autoopen')) d.showModal();
    });

    var editBtn = null;
    root.addEventListener('click', function (e) {
        var t = e.target;
        var opener = t.closest('[data-open]');
        if (opener) { open($(opener.dataset.open)); return; }

        var edit = t.closest('[data-edit]');
        if (edit) {
            editBtn = edit;
            $('sxEditForm').action = edit.dataset.edit;
            $('sxEditTitle').textContent = 'Edit Grade ' + edit.dataset.grade + ' – ' + edit.dataset.name;
            $('sxEditName').value = edit.dataset.name;
            $('sxEditGrade').value = edit.dataset.grade;
            var sel = $('sxEditAdviser'), want = edit.dataset.adviser || '';
            var extra = sel.querySelector('option[data-extra]');
            if (extra) extra.remove();
            sel.value = want;
            if (sel.value !== want) {
                // Current adviser's account is no longer approved, so not in the list: keep them, or saving drops them
                var cur = edit.closest('tr').querySelector('.sx-adv select option:checked');
                var o = new Option(cur ? cur.textContent : 'Current adviser', want, true, true);
                o.dataset.extra = '1';
                sel.add(o, 1);
            }
            $('sxEditActive').checked = edit.dataset.active === '1';
            var n = Number(edit.dataset.students || 0);
            $('sxDeleteNote').textContent = n ? 'This section has ' + n + ' ' + (n === 1 ? 'student' : 'students') + ', so it can only be turned off.' : 'Only sections with no students or records can be deleted.';
            $('sxDeleteBtn').disabled = n > 0;
            open($('sxEdit'));
            return;
        }
        if (t.closest('#sxDeleteBtn') && editBtn) {
            $('sxDeleteForm').action = editBtn.dataset.delete;
            $('sxDeleteName').textContent = 'Grade ' + editBtn.dataset.grade + ' – ' + editBtn.dataset.name;
            close($('sxEdit'));
            open($('sxDelete'));
            return;
        }
        if (t.closest('[data-close]')) { close(t.closest('dialog')); return; }
        if (t.tagName === 'DIALOG') close(t);
    });

    // Quick assign: Save appears only when the adviser was changed
    root.querySelectorAll('.sx-adv select').forEach(function (sel) {
        var btn = sel.form.querySelector('button[type="submit"]');
        sel.addEventListener('change', function () { btn.hidden = sel.value === (sel.dataset.initial || ''); });
    });

    // One click per submit
    root.querySelectorAll('form').forEach(function (f) {
        f.addEventListener('submit', function () {
            var b = f.querySelector('button[type="submit"]');
            if (b) { b.disabled = true; if (f.dataset.busy) b.textContent = f.dataset.busy; }
        });
    });

    // Filters: tab + grade + search, all on the rows already on the page
    var show = 'all';
    function filter() {
        var q = $('sxSearch').value.trim().toLowerCase(), g = $('sxGrade').value, any = false, seen = {};
        root.querySelectorAll('.sx-row').forEach(function (tr) {
            var ok = (show === 'all' || tr.dataset.state === show) && (!g || tr.dataset.grade === g)
                && (!q || tr.dataset.search.indexOf(q) !== -1);
            tr.hidden = !ok;
            if (ok) { any = true; seen[tr.dataset.grade] = true; }
        });
        root.querySelectorAll('.sx-grade').forEach(function (tr) { tr.hidden = !seen[tr.dataset.grade]; });
        if ($('sxNone')) $('sxNone').hidden = any || !root.querySelector('.sx-row');
    }
    root.querySelectorAll('.sx-tab').forEach(function (b) {
        b.addEventListener('click', function () {
            show = b.dataset.show;
            root.querySelectorAll('.sx-tab').forEach(function (x) { x.setAttribute('aria-pressed', String(x === b)); });
            filter();
        });
    });
    $('sxGrade').addEventListener('change', filter);
    $('sxSearch').addEventListener('input', filter);
})();
</script>
@endsection
