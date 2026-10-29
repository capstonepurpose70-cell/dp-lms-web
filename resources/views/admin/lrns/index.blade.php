@extends('layouts.admin')
@section('title', 'LRN Master List')

@section('content')
@include('admin.lrns.partials.styles')

@php
    $filters = array_filter(['q' => $q ?: null, 'grade' => $grade]);
    $tab = fn (string $s) => route('admin.lrns.index', $filters + ($s === 'all' ? [] : ['status' => $s]), false);
    $pct = $total ? (int) round($claimed / $total * 100) : 0;
    $openImport = $errors->import->any();
    $openSingle = $errors->single->any();
@endphp

<div class="lm" id="lmRoot">
    <header class="lm-head">
        <div>
            <h1 class="lm-title">LRN Master List</h1>
            <p class="lm-sub">Students can activate an account only when their LRN and name match an entry here.</p>
        </div>
        <div class="lm-actions">
            <button type="button" class="lm-btn" data-open="lmSingle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                Add one LRN
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
            <div class="lm-bar" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Students who activated their account">
                <i style="width: {{ $pct }}%"></i>
            </div>
            <span>{{ $pct }}% of students on the list have activated their account</span>
        </div>
    </section>

    <section class="lm-card lm-list" aria-label="LRNs">
        <div class="lm-toolbar">
            <nav class="lm-tabs" aria-label="Filter by status">
                <a href="{{ $tab('all') }}" @if ($status === 'all') aria-current="page" @endif>All <span>{{ number_format($total) }}</span></a>
                <a href="{{ $tab('available') }}" @if ($status === 'available') aria-current="page" @endif>Not activated <span>{{ number_format($available) }}</span></a>
                <a href="{{ $tab('claimed') }}" @if ($status === 'claimed') aria-current="page" @endif>Activated <span>{{ number_format($claimed) }}</span></a>
            </nav>
            <form method="GET" action="{{ route('admin.lrns.index') }}" class="lm-filters" role="search">
                @if ($status !== 'all')<input type="hidden" name="status" value="{{ $status }}">@endif
                <select name="grade" aria-label="Grade" data-autosubmit>
                    <option value="">All grades</option>
                    @foreach ($grades as $g)
                        <option value="{{ $g }}" @selected($grade === $g)>Grade {{ $g }}</option>
                    @endforeach
                </select>
                <label class="lm-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M21 21l-4.3-4.3M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                    <input type="search" name="q" value="{{ $q }}" placeholder="LRN or name" aria-label="Search LRN or name" autocomplete="off">
                </label>
            </form>
        </div>

        @if ($lrns->count())
            <div class="lm-table-wrap">
                <table class="lm-table">
                    <thead>
                        <tr>
                            <th scope="col">LRN</th>
                            <th scope="col">Student name</th>
                            <th scope="col">Grade</th>
                            <th scope="col">Status</th>
                            <th scope="col">Account</th>
                            <th scope="col"><span class="lm-sr">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lrns as $row)
                            <tr>
                                <td class="lm-lrn" data-label="LRN">{{ $row->lrn }}</td>
                                <td class="lm-name" data-label="Name">{{ $row->student_name ?: '—' }}</td>
                                <td data-label="Grade">@if ($row->grade_level)<span class="lm-grade">{{ $row->grade_level }}</span>@else<span class="lm-muted">—</span>@endif</td>
                                <td data-label="Status">
                                    @if ($row->claimed_by)
                                        <span class="lm-status on">Activated</span>
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
                                    @unless ($row->claimed_by)
                                        <button type="button" class="lm-icon-btn" data-remove="{{ route('admin.lrns.destroy', $row->id) }}" data-lrn="{{ $row->lrn }}" aria-label="Remove LRN {{ $row->lrn }}">
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
                <span>Showing {{ number_format($lrns->firstItem()) }}–{{ number_format($lrns->lastItem()) }} of {{ number_format($lrns->total()) }}</span>
                {{ $lrns->links('pagination::default') }}
            </div>
        @else
            <div class="lm-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.6a1 1 0 01.7.3l5.4 5.4a1 1 0 01.3.7V19a2 2 0 01-2 2z"/></svg>
                @if ($q !== '' || $status !== 'all' || $grade)
                    <b>No LRNs match these filters</b>
                    <span>Try another search, or <a href="{{ route('admin.lrns.index', [], false) }}">show all LRNs</a>.</span>
                @else
                    <b>The list is empty</b>
                    <span>Import the school's list (SF1 from LIS works as is) to let students activate their accounts.</span>
                    <button type="button" class="lm-btn primary" data-open="lmImport">Import list</button>
                @endif
            </div>
        @endif
    </section>

    {{-- Import: choose a file; the next page shows every row before anything is saved --}}
    <dialog class="lm-dialog" id="lmImport" aria-labelledby="lmImportTitle" @if ($openImport) data-autoopen @endif>
        <form method="POST" action="{{ route('admin.lrns.import') }}" enctype="multipart/form-data" class="lm-dialog-box" data-busy="Checking the file…">
            @csrf
            <div class="lm-dialog-head">
                <h2 id="lmImportTitle">Import LRN list</h2>
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
                        <span>Excel (.xlsx) or CSV, up to 2 MB. SF1 from LIS works as is.</span>
                    </span>
                </label>

                <label class="lm-field" for="lmGradeAll">Grade for these students <span>optional</span></label>
                <select id="lmGradeAll" name="grade" class="lm-input">
                    <option value="">Use the grade column, or leave blank</option>
                    @foreach ($grades as $g)
                        <option value="{{ $g }}" @selected(old('grade') === $g)>Grade {{ $g }}</option>
                    @endforeach
                </select>
                <p class="lm-help">For a file with no grade column, such as one section's SF1.</p>

                <ul class="lm-steps">
                    <li>The file needs a header row with <b>LRN</b> and the <b>student name</b> (one name column, or last / first / middle).</li>
                    <li>You will see every row on the next page. Nothing is saved until you confirm.</li>
                    <li>LRNs already on the list are reported and left unchanged.</li>
                </ul>
            </div>
            <div class="lm-dialog-foot">
                <a href="{{ route('admin.lrns.template') }}" class="lm-link" download data-no-spa>Download the template</a>
                <div>
                    <button type="button" class="lm-btn" data-close>Cancel</button>
                    <button type="submit" class="lm-btn primary">Check file</button>
                </div>
            </div>
        </form>
    </dialog>

    {{-- One LRN, for a transferee --}}
    <dialog class="lm-dialog small" id="lmSingle" aria-labelledby="lmSingleTitle" @if ($openSingle) data-autoopen @endif>
        <form method="POST" action="{{ route('admin.lrns.store') }}" class="lm-dialog-box" data-busy="Adding…">
            @csrf
            <div class="lm-dialog-head">
                <h2 id="lmSingleTitle">Add one LRN</h2>
                <button type="button" class="lm-close" data-close aria-label="Close">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
            <div class="lm-dialog-body">
                <p class="lm-help first">For a new student or a transferee who is not on the imported list.</p>
                <label class="lm-field" for="lmLrn">LRN</label>
                <input id="lmLrn" class="lm-input lm-lrn-input @error('lrn', 'single') bad @enderror" type="text" name="lrn" value="{{ $openSingle ? old('lrn') : '' }}"
                       inputmode="numeric" maxlength="12" pattern="\d{12}" placeholder="12 digits" autocomplete="off" required autofocus
                       @error('lrn', 'single') aria-invalid="true" aria-describedby="lmLrnErr" @enderror>
                <div class="lm-hint-row">
                    @error('lrn', 'single')<span class="lm-err" id="lmLrnErr">{{ $message }}</span>@else<span>Numbers only</span>@enderror
                    <span id="lmLrnCount">0 / 12</span>
                </div>

                <label class="lm-field" for="lmName">Student name</label>
                <input id="lmName" class="lm-input @error('student_name', 'single') bad @enderror" type="text" name="student_name" value="{{ $openSingle ? old('student_name') : '' }}"
                       placeholder="Dela Cruz, Juan Santos" autocomplete="off" maxlength="255" required
                       @error('student_name', 'single') aria-invalid="true" aria-describedby="lmNameErr" @enderror>
                @error('student_name', 'single')<span class="lm-err" id="lmNameErr">{{ $message }}</span>@enderror

                <label class="lm-field" for="lmGrade">Grade <span>optional</span></label>
                <select id="lmGrade" name="grade_level" class="lm-input">
                    <option value="">Not set</option>
                    @foreach ($grades as $g)
                        <option value="{{ $g }}" @selected($openSingle && old('grade_level') === $g)>Grade {{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lm-dialog-foot end">
                <button type="button" class="lm-btn" data-close>Cancel</button>
                <button type="submit" class="lm-btn primary">Add LRN</button>
            </div>
        </form>
    </dialog>

    {{-- Remove confirmation --}}
    <dialog class="lm-dialog small" id="lmRemove" aria-labelledby="lmRemoveTitle">
        <form method="POST" action="" class="lm-dialog-box" id="lmRemoveForm" data-busy="Removing…">
            @csrf @method('DELETE')
            <div class="lm-dialog-body">
                <h2 class="lm-confirm-title" id="lmRemoveTitle">Remove this LRN?</h2>
                <p class="lm-help first"><b id="lmRemoveLrn"></b> will be taken off the master list. The student will not be able to activate an account with it until it is added again.</p>
            </div>
            <div class="lm-dialog-foot end">
                <button type="button" class="lm-btn" data-close autofocus>Keep it</button>
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
        return d;
    }
    function close(d) {
        if (!d || !d.open || d.classList.contains('closing')) return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { d.close(); return; }
        d.classList.add('closing');
        d.addEventListener('animationend', function done() {
            d.removeEventListener('animationend', done);
            // Already closed another way (the browser can close it on Esc without waiting): leave it closed
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
            document.getElementById('lmRemoveLrn').textContent = 'LRN ' + remove.dataset.lrn;
            open('lmRemove');
            return;
        }

        if (e.target.closest('[data-close]')) { close(e.target.closest('dialog')); return; }
        // A click on the dimmed area outside the box closes the dialog
        if (e.target.tagName === 'DIALOG') close(e.target);
    });

    root.querySelectorAll('dialog').forEach(function (d) {
        // Esc: animate out when the browser lets us; otherwise it closes the dialog itself
        d.addEventListener('cancel', function (e) { if (e.cancelable) { e.preventDefault(); close(d); } });
        d.addEventListener('close', function () { d.classList.remove('closing'); });
        if (d.hasAttribute('data-autoopen')) d.showModal();
    });

    // Submitting: lock the button so a double click cannot send the form twice
    root.querySelectorAll('form[data-busy]').forEach(function (f) {
        f.addEventListener('submit', function () {
            var b = f.querySelector('button[type="submit"]');
            if (b) { b.disabled = true; b.textContent = f.dataset.busy; }
        });
    });

    root.querySelectorAll('[data-autosubmit]').forEach(function (s) {
        s.addEventListener('change', function () { s.form.requestSubmit(); });
    });
    // The search box's clear (×) button: show the full list again
    var search = root.querySelector('.lm-search input');
    if (search) search.addEventListener('search', function () { if (search.value === '' && search.defaultValue !== '') search.form.requestSubmit(); });

    // LRN field: digits only, with a counter
    var lrn = document.getElementById('lmLrn'), count = document.getElementById('lmLrnCount');
    function digits() {
        var v = lrn.value.replace(/\D/g, '').slice(0, 12);
        if (v !== lrn.value) lrn.value = v;
        count.textContent = v.length + ' / 12';
        count.classList.toggle('ok', v.length === 12);
    }
    if (lrn && count) { lrn.addEventListener('input', digits); digits(); }

    // File picker: show the chosen file, accept a dropped file, refuse other types right away
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
        else if (f.size > 2097152) s.textContent = size + ' — bigger than 2 MB. Split it into smaller files.';
        else s.textContent = size + ' · click to choose another file';
        text.replaceChildren(b, s);
        drop.classList.add('has');
        if (!ok || f.size > 2097152) { drop.classList.add('bad'); file.setCustomValidity(s.textContent); }
    }
    if (file && drop && text) {
        file.addEventListener('change', showFile);
        ['dragenter', 'dragover'].forEach(function (t) {
            drop.addEventListener(t, function (e) { e.preventDefault(); drop.classList.add('over'); });
        });
        ['dragleave', 'drop'].forEach(function (t) {
            drop.addEventListener(t, function () { drop.classList.remove('over'); });
        });
        drop.addEventListener('drop', function (e) {
            e.preventDefault();
            if (e.dataTransfer && e.dataTransfer.files.length) { file.files = e.dataTransfer.files; showFile(); }
        });
    }
})();
</script>
@endsection
