@extends('layouts.admin')
@section('title', 'Faculty Master List')

@section('content')
@include('admin.partials.list-ui')

@php
    $filters = array_filter(['q' => $q ?: null]);
    $tab = fn (string $s) => route('admin.faculty.index', $filters + ($s === 'all' ? [] : ['status' => $s]), false);
    $pct = $total ? (int) round($claimed / $total * 100) : 0;
    $openImport = $errors->import->any();
    $openSingle = $errors->single->any();
@endphp

<div class="lm" id="lmRoot">
    <header class="lm-head">
        <div>
            <h1 class="lm-title">Faculty Master List</h1>
            <p class="lm-sub">Teachers activate their own account at <b>/activate/teacher</b> when their employee ID and name match an entry here.</p>
        </div>
        <div class="lm-actions">
            <button type="button" class="lm-btn" data-open="lmSingle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                Add one teacher
            </button>
            <button type="button" class="lm-btn primary" data-open="lmImport">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15V3m0 0L7.5 7.5M12 3l4.5 4.5M4 15v3a3 3 0 003 3h10a3 3 0 003-3v-3"/></svg>
                Import list
            </button>
        </div>
    </header>

    @if ($errors->has('general'))
        <div class="lm-alert" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ $errors->first('general') }}</span>
        </div>
    @endif

    <section class="lm-card lm-summary" aria-label="Summary">
        <div class="lm-figures">
            <div><span>On the list</span><b>{{ number_format($total) }}</b></div>
            <div><span>Not activated</span><b>{{ number_format($available) }}</b></div>
            <div><span>Activated</span><b>{{ number_format($claimed) }}</b></div>
        </div>
        <div class="lm-progress">
            <div class="lm-bar" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Teachers who activated their account">
                <i style="width: {{ $pct }}%"></i>
            </div>
            <span>{{ $pct }}% of teachers on the list have activated their account</span>
        </div>
    </section>

    <section class="lm-card lm-list" aria-label="Teachers">
        <div class="lm-toolbar">
            <nav class="lm-tabs" aria-label="Filter by status">
                <a href="{{ $tab('all') }}" @if ($status === 'all') aria-current="page" @endif>All <span>{{ number_format($total) }}</span></a>
                <a href="{{ $tab('available') }}" @if ($status === 'available') aria-current="page" @endif>Not activated <span>{{ number_format($available) }}</span></a>
                <a href="{{ $tab('claimed') }}" @if ($status === 'claimed') aria-current="page" @endif>Activated <span>{{ number_format($claimed) }}</span></a>
            </nav>
            <form method="GET" action="{{ route('admin.faculty.index') }}" class="lm-filters" role="search">
                @if ($status !== 'all')<input type="hidden" name="status" value="{{ $status }}">@endif
                <label class="lm-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M21 21l-4.3-4.3M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                    <input type="search" name="q" value="{{ $q }}" placeholder="Employee ID or name" aria-label="Search employee ID or name" autocomplete="off">
                </label>
            </form>
        </div>

        @if ($records->count())
            <div class="lm-table-wrap">
                <table class="lm-table">
                    <thead>
                        <tr>
                            <th scope="col">Employee ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Advisory</th>
                            <th scope="col">Status</th>
                            <th scope="col">Account</th>
                            <th scope="col"><span class="lm-sr">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $row)
                            @php $gone = $row->claimed_by && !$row->claimedBy; @endphp
                            <tr>
                                <td class="lm-lrn" data-label="Employee ID">{{ $row->employee_id }}</td>
                                <td class="lm-name" data-label="Name">{{ $row->name }}</td>
                                <td data-label="Advisory">
                                    @if ($row->section)
                                        {{ $row->section->grade_level }}-{{ $row->section->name }}
                                    @else
                                        <span class="lm-muted">—</span>
                                    @endif
                                </td>
                                <td data-label="Status">
                                    @if ($row->claimedBy)
                                        <span class="lm-status on">Activated</span>
                                    @elseif ($gone)
                                        <span class="lm-status">Account removed</span>
                                    @else
                                        <span class="lm-status">Not activated</span>
                                    @endif
                                </td>
                                <td data-label="Account">
                                    @if ($row->claimedBy)
                                        <span class="lm-acct">{{ $row->claimedBy->name }}</span>
                                        <span class="lm-email">{{ $row->claimedBy->email }}</span>
                                    @else
                                        <span class="lm-muted">—</span>
                                    @endif
                                </td>
                                <td class="lm-act">
                                    @unless ($row->claimedBy)
                                        <button type="button" class="lm-icon-btn" data-remove="{{ route('admin.faculty.destroy', $row->id) }}" data-label="{{ $row->name }} ({{ $row->employee_id }})" aria-label="Remove {{ $row->name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12M9 7V4h6v3"/></svg>
                                        </button>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="lm-pages">
                <span>Showing {{ number_format($records->firstItem()) }}–{{ number_format($records->lastItem()) }} of {{ number_format($records->total()) }}</span>
                {{ $records->links('pagination::default') }}
            </div>
        @else
            <div class="lm-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8z"/></svg>
                @if ($q !== '' || $status !== 'all')
                    <b>No teachers match these filters</b>
                    <span>Try another search, or <a href="{{ route('admin.faculty.index', [], false) }}">show all teachers</a>.</span>
                @else
                    <b>The list is empty</b>
                    <span>Import the school's faculty list so teachers can activate their own accounts.</span>
                    <button type="button" class="lm-btn primary" data-open="lmImport">Import list</button>
                @endif
            </div>
        @endif
    </section>

    {{-- Import: choose a file; the next page shows every row before anything is saved --}}
    <dialog class="lm-dialog" id="lmImport" aria-labelledby="lmImportTitle" @if ($openImport) data-autoopen @endif>
        <form method="POST" action="{{ route('admin.faculty.import') }}" enctype="multipart/form-data" class="lm-dialog-box" data-busy="Checking the file…">
            @csrf
            <div class="lm-dialog-head">
                <h2 id="lmImportTitle">Import faculty list</h2>
                <button type="button" class="lm-close" data-close aria-label="Close">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
            <div class="lm-dialog-body">
                @if ($openImport)
                    <div class="lm-alert" role="alert">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $errors->import->first() }}</span>
                    </div>
                @endif

                <label class="lm-drop" id="lmDrop">
                    <input type="file" name="file" id="lmFile" accept=".xlsx,.csv" required autofocus>
                    <span class="lm-drop-ic" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14 3v4a1 1 0 001 1h4M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5zM9 13l2 2 4-4"/></svg>
                    </span>
                    <span class="lm-drop-text" id="lmDropText">
                        <b>Choose a file or drop it here</b>
                        <span>Excel (.xlsx) or CSV, up to 2 MB.</span>
                    </span>
                </label>

                <ul class="lm-steps">
                    <li>The file needs a header row with <b>Employee ID</b> and the <b>name</b> (one name column, or last / first / middle).</li>
                    <li>An <b>Advisory</b> column is optional, written as grade-section (7-Gutierrez). The teacher becomes its adviser on activation.</li>
                    <li>You will see every row on the next page. Nothing is saved until you confirm.</li>
                </ul>
            </div>
            <div class="lm-dialog-foot">
                <a href="{{ route('admin.faculty.template') }}" class="lm-link" download data-no-spa>Download the template</a>
                <div>
                    <button type="button" class="lm-btn" data-close>Cancel</button>
                    <button type="submit" class="lm-btn primary">Check file</button>
                </div>
            </div>
        </form>
    </dialog>

    {{-- One teacher --}}
    <dialog class="lm-dialog small" id="lmSingle" aria-labelledby="lmSingleTitle" @if ($openSingle) data-autoopen @endif>
        <form method="POST" action="{{ route('admin.faculty.store') }}" class="lm-dialog-box" data-busy="Adding…">
            @csrf
            <div class="lm-dialog-head">
                <h2 id="lmSingleTitle">Add one teacher</h2>
                <button type="button" class="lm-close" data-close aria-label="Close">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
            <div class="lm-dialog-body">
                <p class="lm-help first">For a new hire or a transferee who is not on the imported list.</p>
                <label class="lm-field" for="lmEmp">Employee ID</label>
                <input id="lmEmp" class="lm-input lm-lrn-input @error('employee_id', 'single') bad @enderror" type="text" name="employee_id" value="{{ $openSingle ? old('employee_id') : '' }}"
                       maxlength="50" placeholder="e.g. 4512345" autocomplete="off" autocapitalize="characters" spellcheck="false" required autofocus
                       @error('employee_id', 'single') aria-invalid="true" aria-describedby="lmEmpErr" @enderror>
                @error('employee_id', 'single')<span class="lm-err" id="lmEmpErr">{{ $message }}</span>@enderror

                <label class="lm-field" for="lmName">Name</label>
                <input id="lmName" class="lm-input @error('name', 'single') bad @enderror" type="text" name="name" value="{{ $openSingle ? old('name') : '' }}"
                       placeholder="Santos, Maria Lopez" autocomplete="off" maxlength="255" required
                       @error('name', 'single') aria-invalid="true" aria-describedby="lmNameErr" @enderror>
                @error('name', 'single')<span class="lm-err" id="lmNameErr">{{ $message }}</span>@enderror

                <label class="lm-field" for="lmAdv">Advisory <span>optional</span></label>
                <select id="lmAdv" name="section_id" class="lm-input @error('section_id', 'single') bad @enderror">
                    <option value="">None</option>
                    @foreach ($sections as $s)
                        <option value="{{ $s['id'] }}" @selected($openSingle && (string) old('section_id') === (string) $s['id']) @disabled($s['taken'])>
                            {{ $s['label'] }}{{ $s['taken'] ? ' — has an adviser' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('section_id', 'single')<span class="lm-err">{{ $message }}</span>@else<p class="lm-help">The teacher becomes the adviser of this section on activation.</p>@enderror
            </div>
            <div class="lm-dialog-foot end">
                <button type="button" class="lm-btn" data-close>Cancel</button>
                <button type="submit" class="lm-btn primary">Add teacher</button>
            </div>
        </form>
    </dialog>

    {{-- Remove confirmation --}}
    <dialog class="lm-dialog small" id="lmRemove" aria-labelledby="lmRemoveTitle">
        <form method="POST" action="" class="lm-dialog-box" id="lmRemoveForm" data-busy="Removing…">
            @csrf @method('DELETE')
            <div class="lm-dialog-body">
                <h2 class="lm-confirm-title" id="lmRemoveTitle">Remove this teacher?</h2>
                <p class="lm-help first"><b id="lmRemoveName"></b> will be taken off the faculty list and will not be able to activate an account until added again.</p>
            </div>
            <div class="lm-dialog-foot end">
                <button type="button" class="lm-btn" data-close autofocus>Keep</button>
                <button type="submit" class="lm-btn danger">Remove</button>
            </div>
        </form>
    </dialog>
</div>

<script>
(function () {
    var root = document.getElementById('lmRoot');
    if (!root) return;

    function open(id) {
        var d = document.getElementById(id);
        if (d && !d.open) d.showModal();
    }
    function close(d) {
        if (!d || !d.open || d.classList.contains('closing')) return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { d.close(); return; }
        d.classList.add('closing');
        d.addEventListener('animationend', function done() {
            d.removeEventListener('animationend', done);
            if (!d.classList.contains('closing')) return;
            d.classList.remove('closing');
            d.close();
        });
    }

    root.addEventListener('click', function (e) {
        var opener = e.target.closest('[data-open]');
        if (opener) { open(opener.dataset.open); return; }

        var remove = e.target.closest('[data-remove]');
        if (remove) {
            document.getElementById('lmRemoveForm').action = remove.dataset.remove;
            document.getElementById('lmRemoveName').textContent = remove.dataset.label;
            open('lmRemove');
            return;
        }

        if (e.target.closest('[data-close]')) { close(e.target.closest('dialog')); return; }
        if (e.target.tagName === 'DIALOG') close(e.target);
    });

    root.querySelectorAll('dialog').forEach(function (d) {
        d.addEventListener('cancel', function (e) { if (e.cancelable) { e.preventDefault(); close(d); } });
        d.addEventListener('close', function () { d.classList.remove('closing'); });
        if (d.hasAttribute('data-autoopen')) d.showModal();
    });

    root.querySelectorAll('form[data-busy]').forEach(function (f) {
        f.addEventListener('submit', function () {
            var b = f.querySelector('button[type="submit"]');
            if (b) { b.disabled = true; b.textContent = f.dataset.busy; }
        });
    });

    var search = root.querySelector('.lm-search input');
    if (search) search.addEventListener('search', function () { if (search.value === '' && search.defaultValue !== '') search.form.requestSubmit(); });

    // Employee ID: capitals, digits and dashes only
    var emp = document.getElementById('lmEmp');
    if (emp) emp.addEventListener('input', function () {
        var v = emp.value.toUpperCase().replace(/[^A-Z0-9\-]/g, '');
        if (v !== emp.value) emp.value = v;
    });

    var file = document.getElementById('lmFile'), drop = document.getElementById('lmDrop'), text = document.getElementById('lmDropText');
    var blank = text ? text.innerHTML : '';
    function showFile() {
        var f = file.files && file.files[0];
        drop.classList.remove('bad');
        file.setCustomValidity('');
        if (!f) { text.innerHTML = blank; drop.classList.remove('has'); return; }
        var ok = /\.(xlsx|csv)$/i.test(f.name);
        var size = f.size < 1048576 ? Math.max(1, Math.round(f.size / 1024)) + ' KB' : (f.size / 1048576).toFixed(1) + ' MB';
        var b = document.createElement('b'), s = document.createElement('span');
        b.textContent = f.name;
        if (!ok) s.textContent = 'This is not an .xlsx or .csv file. For an old .xls file, save it as .xlsx in Excel first.';
        else if (f.size > 2097152) s.textContent = size + ' — bigger than 2 MB.';
        else s.textContent = size + ' · click to choose another file';
        text.replaceChildren(b, s);
        drop.classList.add('has');
        if (!ok || f.size > 2097152) { drop.classList.add('bad'); file.setCustomValidity(s.textContent); }
    }
    if (file && drop && text) {
        file.addEventListener('change', showFile);
        ['dragenter', 'dragover'].forEach(function (t) { drop.addEventListener(t, function (e) { e.preventDefault(); drop.classList.add('over'); }); });
        ['dragleave', 'drop'].forEach(function (t) { drop.addEventListener(t, function () { drop.classList.remove('over'); }); });
        drop.addEventListener('drop', function (e) {
            e.preventDefault();
            if (e.dataTransfer && e.dataTransfer.files.length) { file.files = e.dataTransfer.files; showFile(); }
        });
    }
})();
</script>
@endsection
