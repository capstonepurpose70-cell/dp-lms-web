@extends('layouts.admin')
@section('title', 'Sections & Advisers')

@section('content')
@php
    // Which sections each teacher already advises (shown in the dropdowns)
    $advisedBy   = $sections->whereNotNull('adviser_id')->groupBy('adviser_id');
    $teacherIds  = $teachers->pluck('id')->map(fn ($id) => (int) $id)->all();
    $activeCount = $sections->where('is_active', true)->count();
    $noAdviser   = $sections->where('is_active', true)->whereNull('adviser_id')->count();
    $studentSum  = $sections->sum('students_count');
    $byGrade     = $sections->groupBy(fn ($s) => (string) $s->grade_level);
@endphp
<style>
    /* Uses admin layout tokens (--white, --slate-*, --border-default) so dark mode works */
    .sec-page { animation: sec-fade .22s ease both; }
    @keyframes sec-fade { from { opacity:0; transform:translateY(6px);} to { opacity:1; transform:none;} }

    .sec-head { margin-bottom:18px; }
    .sec-head h1 { font-size:22px; font-weight:800; margin:0; color:var(--slate-900); letter-spacing:-0.01em; }
    .sec-head p  { margin:4px 0 0; font-size:13px; color:var(--slate-500); max-width:640px; line-height:1.5; }

    .sec-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:18px; }
    .sec-stat { background:var(--white); border:1px solid var(--border-default); border-radius:14px; padding:14px 16px; box-shadow:var(--shadow-sm); }
    .sec-stat b { font-size:22px; font-weight:800; display:block; color:var(--slate-900); line-height:1.1; }
    .sec-stat span { font-size:12px; color:var(--slate-500); font-weight:500; }
    .sec-stat.warn b { color:#b45309; }
    [data-theme="dark"] .sec-stat.warn b { color:#fbbf24; }

    .sec-grid { display:grid; grid-template-columns: 320px minmax(0,1fr); gap:16px; align-items:start; }
    .sec-card { background:var(--white); border:1px solid var(--border-default); border-radius:16px; padding:18px; box-shadow:var(--shadow-sm); }
    .sec-card h3 { margin:0 0 4px; font-size:14.5px; font-weight:800; color:var(--slate-900); display:flex; align-items:center; gap:8px; }
    .sec-card h3 svg { width:17px; height:17px; color:#2563eb; }
    .sec-sub { font-size:12px; color:var(--slate-500); margin:0 0 6px; }
    .sec-card label.fl { display:block; font-size:12px; font-weight:700; margin:12px 0 5px; color:var(--slate-700); }
    .sec-card label.fl .opt { font-weight:500; color:var(--slate-400); }
    .sec-in {
        width:100%; padding:10px 12px; border:1.5px solid var(--border-default); border-radius:10px;
        font-size:13.5px; background:var(--white); color:var(--slate-900); box-sizing:border-box;
        transition:border-color .15s, box-shadow .15s;
    }
    .sec-in:focus, .sec-row-in:focus, .sec-filter:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
    .sec-btn { margin-top:14px; width:100%; padding:11px; border:0; border-radius:10px; font-weight:700; font-size:13.5px;
        cursor:pointer; background:#1d4ed8; color:#fff; transition:background .15s; }
    .sec-btn:hover { background:#1e40af; }
    .sec-tip { font-size:12px; color:var(--slate-500); margin-top:12px; line-height:1.55; }

    .sec-list-head { display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
    .sec-filter { padding:9px 12px; border:1.5px solid var(--border-default); border-radius:10px; font-size:13px;
        background:var(--white); color:var(--slate-900); min-width:220px; }

    .sec-table-wrap { overflow-x:auto; border:1px solid var(--border-default); border-radius:12px; }
    table.sec { width:100%; border-collapse:collapse; font-size:13.5px; min-width:760px; }
    .sec th { text-align:left; font-size:11px; letter-spacing:.06em; text-transform:uppercase; font-weight:700;
        color:var(--slate-500); padding:11px 12px; background:var(--slate-50); border-bottom:1px solid var(--border-default); white-space:nowrap; }
    .sec td { padding:10px 12px; border-bottom:1px solid var(--border-default); vertical-align:middle; color:var(--slate-900); }
    .sec tr.grp td { background:var(--slate-50); font-size:11.5px; font-weight:800; letter-spacing:.06em; text-transform:uppercase;
        color:var(--slate-700); padding:8px 12px; }
    .sec tr.grp td small { font-weight:600; color:var(--slate-500); text-transform:none; letter-spacing:0; margin-left:6px; }
    .sec tr.row:hover td { background:var(--slate-50); }
    .sec tr.row.off td { opacity:.6; }

    .sec-row-in { width:100%; min-width:130px; padding:7px 10px; border:1.5px solid transparent; border-radius:8px; font-size:13.5px;
        font-weight:700; background:transparent; color:var(--slate-900); box-sizing:border-box; }
    .sec-row-in:hover { border-color:var(--border-default); }
    select.sec-row-in { font-weight:500; border-color:var(--border-default); background:var(--white); cursor:pointer; max-width:240px; }
    .sec-count { display:inline-block; min-width:30px; text-align:center; padding:2px 8px; border-radius:7px;
        background:var(--slate-100); color:var(--slate-700); font-weight:700; font-size:12.5px; }
    .sec-noadv { display:inline-block; margin-top:4px; font-size:11px; font-weight:700; color:#b45309; }
    [data-theme="dark"] .sec-noadv { color:#fbbf24; }

    .sec-toggle { display:inline-flex; align-items:center; gap:7px; cursor:pointer; font-size:12.5px; font-weight:600; color:var(--slate-700); user-select:none; }
    .sec-toggle input { width:16px; height:16px; accent-color:#16a34a; cursor:pointer; }

    .sec-actions { display:flex; gap:6px; justify-content:flex-end; align-items:center; }
    .sec-save { padding:7px 13px; border:0; border-radius:8px; background:#15803d; color:#fff; font-size:12.5px; font-weight:700; cursor:pointer; transition:background .15s; }
    .sec-save:hover { background:#166534; }
    .sec-save.dirty { box-shadow:0 0 0 3px rgba(22,163,74,.25); }
    .sec-del { padding:6px 11px; border:1px solid rgba(220,38,38,.25); background:transparent; color:#dc2626; font-size:12px; font-weight:700;
        cursor:pointer; border-radius:8px; transition:background .15s; }
    .sec-del:hover { background:rgba(220,38,38,.08); }

    .sec-empty { text-align:center; padding:40px 16px !important; color:var(--slate-500); }
    .sec-empty b { display:block; color:var(--slate-700); font-size:14px; margin-bottom:3px; }

    .sec-alert { border-radius:12px; padding:12px 14px; font-size:13.5px; margin-bottom:14px; font-weight:500; }
    .sec-alert.ok  { background:rgba(22,163,74,.1); color:#15803d; border:1px solid rgba(22,163,74,.3); }
    .sec-alert.err { background:rgba(220,38,38,.08); color:#b91c1c; border:1px solid rgba(220,38,38,.3); }
    [data-theme="dark"] .sec-alert.ok  { color:#4ade80; }
    [data-theme="dark"] .sec-alert.err { color:#f87171; }

    @media (max-width: 1100px) { .sec-grid { grid-template-columns:1fr; } }
    @media (max-width: 700px)  { .sec-stats { grid-template-columns:repeat(2,1fr); } }
</style>

<div class="sec-page">
    <div class="sec-head">
        <h1>Sections &amp; Advisers</h1>
        <p>Create sections and assign each one an adviser. The adviser reviews and approves enrollment requests for their section.</p>
    </div>

    {{-- Success message: shown by layouts/admin (toast) --}}
    @if ($errors->any())
        <div class="sec-alert err" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="sec-stats">
        <div class="sec-stat"><b>{{ $sections->count() }}</b><span>Total sections</span></div>
        <div class="sec-stat"><b>{{ $activeCount }}</b><span>Active sections</span></div>
        <div class="sec-stat {{ $noAdviser ? 'warn' : '' }}"><b>{{ $noAdviser }}</b><span>Active, no adviser yet</span></div>
        <div class="sec-stat"><b>{{ number_format($studentSum) }}</b><span>Students placed</span></div>
    </div>

    <div class="sec-grid">
        {{-- Create --}}
        <div class="sec-card">
            <h3><svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg> New Section</h3>
            <p class="sec-sub">Add a section for the current school year.</p>
            <form method="POST" action="{{ route('admin.sections.store') }}">
                @csrf
                <label class="fl" for="secName">Section Name</label>
                <input id="secName" class="sec-in" type="text" name="name" value="{{ old('name') }}" maxlength="100" placeholder="e.g. GUTIERREZ, ABM INNOVATOR" required>

                <label class="fl" for="secGrade">Grade Level</label>
                <select id="secGrade" class="sec-in" name="grade_level" required>
                    <option value="">— Select grade —</option>
                    @foreach ($grades as $g)
                        <option value="{{ $g }}" {{ (string) old('grade_level') === (string) $g ? 'selected' : '' }}>Grade {{ $g }}</option>
                    @endforeach
                </select>

                <label class="fl" for="secAdviser">Adviser <span class="opt">(optional)</span></label>
                <select id="secAdviser" class="sec-in" name="adviser_id">
                    <option value="">— No adviser yet —</option>
                    @foreach ($teachers as $t)
                        @php $adv = ($advisedBy[$t->id] ?? collect())->map(fn ($x) => $x->grade_level.'-'.$x->name)->implode(', '); @endphp
                        <option value="{{ $t->id }}" {{ (string) old('adviser_id') === (string) $t->id ? 'selected' : '' }}>
                            {{ $t->name }}{{ $adv ? ' (advises '.$adv.')' : '' }}
                        </option>
                    @endforeach
                </select>

                <button class="sec-btn" type="submit">Create Section</button>
            </form>
            <p class="sec-tip">
                Only approved teacher accounts appear as advisers.
                To retire a section, uncheck <b>Active</b> instead of deleting it — deleting is only allowed when the section has no students or records.
            </p>
        </div>

        {{-- List + edit --}}
        <div class="sec-card">
            <div class="sec-list-head">
                <h3 style="margin:0;">All Sections</h3>
                <input type="search" id="secFilter" class="sec-filter" placeholder="Filter by section or adviser…" aria-label="Filter sections">
            </div>

            <div class="sec-table-wrap">
                <table class="sec">
                    <thead>
                        <tr>
                            <th style="width:28%;">Section name</th>
                            <th>Students</th>
                            <th style="width:32%;">Adviser</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="secBody">
                        @forelse ($byGrade as $grade => $rows)
                            <tr class="grp"><td colspan="5">Grade {{ $grade }}<small>{{ (int) $grade >= 11 ? 'Senior High' : 'Junior High' }} · {{ $rows->count() }} {{ Str::plural('section', $rows->count()) }}</small></td></tr>
                            @foreach ($rows as $s)
                                @php $fid = 'secUpd'.$s->id; @endphp
                                <tr class="row {{ $s->is_active ? '' : 'off' }}"
                                    data-search="{{ mb_strtolower($s->grade_level.' '.$s->name.' '.($s->adviser->name ?? '')) }}">
                                    <td>
                                        <input class="sec-row-in" type="text" name="name" form="{{ $fid }}" value="{{ $s->name }}"
                                               maxlength="100" required aria-label="Section name" title="Click to rename">
                                    </td>
                                    <td><span class="sec-count">{{ $s->students_count }}</span></td>
                                    <td>
                                        <select class="sec-row-in" name="adviser_id" form="{{ $fid }}" aria-label="Adviser for {{ $s->name }}">
                                            <option value="">— No adviser —</option>
                                            {{-- Keep the current adviser selectable even if their account is no longer approved,
                                                 otherwise saving this row would silently remove them --}}
                                            @if ($s->adviser && !in_array((int) $s->adviser_id, $teacherIds, true))
                                                <option value="{{ $s->adviser_id }}" selected>{{ $s->adviser->name }} (account {{ $s->adviser->status ?? 'inactive' }})</option>
                                            @endif
                                            @foreach ($teachers as $t)
                                                @php
                                                    $adv = ($advisedBy[$t->id] ?? collect())->where('id', '!=', $s->id)
                                                        ->map(fn ($x) => $x->grade_level.'-'.$x->name)->implode(', ');
                                                @endphp
                                                <option value="{{ $t->id }}" {{ (int) $s->adviser_id === (int) $t->id ? 'selected' : '' }}>
                                                    {{ $t->name }}{{ $adv ? ' (also advises '.$adv.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if (!$s->adviser_id && $s->is_active)
                                            <span class="sec-noadv">No adviser — enrollment can't be reviewed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <label class="sec-toggle">
                                            <input type="checkbox" name="is_active" value="1" form="{{ $fid }}" {{ $s->is_active ? 'checked' : '' }}>
                                            Active
                                        </label>
                                    </td>
                                    <td>
                                        <div class="sec-actions">
                                            <form id="{{ $fid }}" method="POST" action="{{ route('admin.sections.update', $s->id) }}">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="grade_level" value="{{ $s->grade_level }}">
                                                <button class="sec-save" type="submit">Save</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.sections.destroy', $s->id) }}"
                                                  onsubmit="return confirm(this.dataset.msg);"
                                                  data-msg="Delete section Grade {{ $s->grade_level }} - {{ $s->name }}? This cannot be undone.">
                                                @csrf @method('DELETE')
                                                <button class="sec-del" type="submit">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="5" class="sec-empty">
                                <b>No sections yet</b>
                                Create one with the form on the left.
                            </td></tr>
                        @endforelse
                        <tr id="secNoMatch" style="display:none;"><td colspan="5" class="sec-empty"><b>No matching sections</b>Try a different name.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    // Client-side filter — hides rows and empty grade groups
    var input = document.getElementById('secFilter'), body = document.getElementById('secBody');
    if (input && body) {
        input.addEventListener('input', function () {
            var q = input.value.trim().toLowerCase(), any = false, grp = null, grpHas = false;
            var rows = body.querySelectorAll('tr.grp, tr.row');
            var finishGroup = function () { if (grp) grp.style.display = grpHas ? '' : 'none'; };
            rows.forEach(function (tr) {
                if (tr.classList.contains('grp')) { finishGroup(); grp = tr; grpHas = false; return; }
                var show = q === '' || (tr.dataset.search || '').indexOf(q) !== -1;
                tr.style.display = show ? '' : 'none';
                if (show) { grpHas = true; any = true; }
            });
            finishGroup();
            var nm = document.getElementById('secNoMatch');
            if (nm) nm.style.display = (rows.length && !any) ? '' : 'none';
        });
    }

    // Highlight Save when a row has unsaved changes
    document.querySelectorAll('form[id^="secUpd"]').forEach(function (form) {
        var btn = form.querySelector('.sec-save');
        document.querySelectorAll('[form="' + form.id + '"]').forEach(function (el) {
            el.addEventListener('change', function () { btn.classList.add('dirty'); btn.textContent = 'Save changes'; });
        });
    });
})();
</script>
@endsection
