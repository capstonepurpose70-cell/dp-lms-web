@extends('layouts.admin')
@section('title', 'Add Teacher')

@section('content')
@php $byGrade = $sections->groupBy(fn ($s) => (string) $s->grade_level); @endphp
<style>
    /* Uses admin layout tokens so dark mode works */
    .ct-page { animation: ct-fade .22s ease both; max-width:1040px; }
    @keyframes ct-fade { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }

    .ct-back { display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:var(--slate-500); text-decoration:none; margin-bottom:10px; }
    .ct-back:hover { color:#2563eb; }
    .ct-back svg { width:15px; height:15px; }
    .ct-title { font-size:22px; font-weight:800; color:var(--slate-900); margin:0; letter-spacing:-0.01em; }
    .ct-sub { font-size:13px; color:var(--slate-500); margin:4px 0 18px; max-width:700px; line-height:1.55; }

    .ct-grid { display:grid; grid-template-columns:minmax(0,1.5fr) minmax(0,1fr); gap:18px; align-items:start; }
    .ct-card { background:var(--white); border:1px solid var(--border-default); border-radius:16px; padding:20px; box-shadow:var(--shadow-sm); }
    .ct-card h3 { margin:0 0 4px; font-size:15px; font-weight:800; color:var(--slate-900); }
    .ct-muted { font-size:12.5px; color:var(--slate-500); margin:0 0 6px; line-height:1.55; }

    .ct-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .ct-field { margin-top:14px; }
    .ct-label { display:block; font-size:12.5px; font-weight:700; color:var(--slate-700); margin-bottom:6px; }
    .ct-label .opt { font-weight:500; color:var(--slate-400); }
    .ct-label .req { color:#dc2626; }
    .ct-in { width:100%; height:42px; padding:0 12px; border:1.5px solid var(--border-default); border-radius:10px; box-sizing:border-box;
        font-size:14px; color:var(--slate-900); background:var(--white); transition:border-color .15s, box-shadow .15s; }
    select.ct-in { cursor:pointer; }
    .ct-in:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
    .ct-in.err { border-color:#f87171; }
    .ct-help { font-size:12px; color:var(--slate-500); margin-top:5px; line-height:1.5; }
    .ct-err { font-size:12.5px; color:#dc2626; margin-top:5px; font-weight:600; }

    .ct-actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:20px; padding-top:16px; border-top:1px solid var(--border-default); }
    .ct-btn { display:inline-flex; align-items:center; gap:8px; height:42px; padding:0 18px; border:0; border-radius:10px; background:#1d4ed8;
        color:#fff; font-size:14px; font-weight:700; cursor:pointer; text-decoration:none; transition:background .15s; }
    .ct-btn:hover { background:#1e40af; }
    .ct-btn:disabled { opacity:.7; cursor:wait; }
    .ct-btn svg { width:16px; height:16px; }
    .ct-btn.ghost { background:transparent; color:var(--slate-700); border:1px solid var(--border-default); }
    .ct-btn.ghost:hover { border-color:#2563eb; color:#2563eb; }

    .ct-alert { border-radius:12px; padding:12px 14px; font-size:13px; margin-bottom:14px; background:rgba(220,38,38,.08); color:#b91c1c; border:1px solid rgba(220,38,38,.3); }
    [data-theme="dark"] .ct-alert { color:#f87171; }

    .ct-steps { list-style:none; padding:0; margin:12px 0 0; }
    .ct-steps li { display:flex; gap:10px; margin-bottom:12px; font-size:13px; color:var(--slate-700); line-height:1.5; }
    .ct-steps i { font-style:normal; width:24px; height:24px; border-radius:999px; background:rgba(37,99,235,.12); color:#1d4ed8;
        display:grid; place-items:center; font-weight:800; font-size:12px; flex-shrink:0; }
    [data-theme="dark"] .ct-steps i { color:#93c5fd; }
    .ct-tip { margin-top:14px; padding:12px 14px; border-radius:12px; background:var(--slate-50); border:1px solid var(--border-default); font-size:12.5px; color:var(--slate-700); line-height:1.55; }
    .ct-tip a { color:#2563eb; font-weight:700; }

    @media (max-width: 900px) { .ct-grid, .ct-row { grid-template-columns:1fr; } }
</style>

<div class="ct-page">
    <a href="{{ route('admin.users.index', ['tab' => 'teachers']) }}" class="ct-back">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Back to Teachers
    </a>
    <h1 class="ct-title">Add Teacher</h1>
    <p class="ct-sub">Create one teacher account. The teacher receives a personal activation link and sets their own password —
        no temporary password is shown or shared.</p>

    <div class="ct-grid">
        <div class="ct-card">
            @if ($errors->any())
                <div class="ct-alert" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.users.store-teacher') }}" id="ctForm" novalidate>
                @csrf
                <h3>Teacher details</h3>
                <p class="ct-muted">Only the name is required. Fields marked optional can be filled in later.</p>

                <div class="ct-field">
                    <label class="ct-label" for="ctName">Full name <span class="req">*</span></label>
                    <input id="ctName" name="name" type="text" class="ct-in @error('name') err @enderror" value="{{ old('name') }}"
                           maxlength="255" placeholder="e.g. Jonalyn L. Gutierrez" autocomplete="off" required>
                    @error('name')<div class="ct-err">{{ $message }}</div>@enderror
                </div>

                <div class="ct-row">
                    <div class="ct-field">
                        <label class="ct-label" for="ctEmail">Email <span class="opt">(optional)</span></label>
                        <input id="ctEmail" name="email" type="email" class="ct-in @error('email') err @enderror" value="{{ old('email') }}"
                               maxlength="255" placeholder="name@deped.gov.ph" autocomplete="off">
                        @error('email')<div class="ct-err">{{ $message }}</div>@else<div class="ct-help">If given, the activation link is also emailed. If blank, the teacher enters their own email when activating.</div>@enderror
                    </div>
                    <div class="ct-field">
                        <label class="ct-label" for="ctEmp">Employee ID <span class="opt">(optional)</span></label>
                        <input id="ctEmp" name="employee_id" type="text" class="ct-in @error('employee_id') err @enderror" value="{{ old('employee_id') }}"
                               maxlength="50" placeholder="e.g. 4567890" autocomplete="off">
                        @error('employee_id')<div class="ct-err">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="ct-row">
                    <div class="ct-field">
                        <label class="ct-label" for="ctMobile">Mobile number <span class="opt">(optional)</span></label>
                        <input id="ctMobile" name="contact_number" type="text" inputmode="numeric" class="ct-in @error('contact_number') err @enderror"
                               value="{{ old('contact_number') }}" maxlength="13" placeholder="09XXXXXXXXX" autocomplete="off">
                        @error('contact_number')<div class="ct-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="ct-field">
                        <label class="ct-label" for="ctAdv">Advisory section <span class="opt">(optional)</span></label>
                        <select id="ctAdv" name="adviser_section_id" class="ct-in @error('adviser_section_id') err @enderror">
                            <option value="">— Not an adviser —</option>
                            @foreach ($byGrade as $g => $group)
                                <optgroup label="Grade {{ $g }}">
                                    @foreach ($group as $s)
                                        <option value="{{ $s->id }}" @selected((string) old('adviser_section_id') === (string) $s->id) @disabled($s->adviser_id)>
                                            {{ $s->name }}{{ $s->adviser ? ' — adviser: ' . $s->adviser->name : '' }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('adviser_section_id')<div class="ct-err">{{ $message }}</div>@else<div class="ct-help">Advisers review enrollment for their section. Sections that already have one are greyed out.</div>@enderror
                    </div>
                </div>

                <div class="ct-actions">
                    <button type="submit" class="ct-btn" id="ctSubmit">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Create account &amp; link
                    </button>
                    <a href="{{ route('admin.users.index', ['tab' => 'teachers']) }}" class="ct-btn ghost">Cancel</a>
                </div>
            </form>
        </div>

        <div class="ct-card">
            <h3>What happens next</h3>
            <ol class="ct-steps">
                <li><i>1</i><span>The account is created as <b>awaiting activation</b> — it cannot be used yet.</span></li>
                <li><i>2</i><span>You get a personal <b>activation link</b> (valid 14 days) to copy, print or send. If you entered an email, it is emailed too.</span></li>
                <li><i>3</i><span>The teacher opens the link, sets their email and password, and logs in. A verification code is sent to their email on first login.</span></li>
            </ol>
            <div class="ct-tip">
                Adding the whole faculty? Use <a href="{{ route('admin.users.import-teachers', [], false) }}">Import Teachers</a> —
                paste the list once and get every activation link at the same time.
            </div>
            <div class="ct-tip">
                Subjects and class schedules are assigned in <b>Class Schedules</b> after the account is created.
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var f = document.getElementById('ctForm');
    if (!f) return;
    // Keep only digits in the mobile field (server also validates 09XXXXXXXXX)
    var m = document.getElementById('ctMobile');
    if (m) m.addEventListener('input', function () { var v = m.value.replace(/[^\d]/g, '').slice(0, 11); if (v !== m.value) m.value = v; });
    f.addEventListener('submit', function (e) {
        var n = document.getElementById('ctName');
        if (!n.value.trim()) { e.preventDefault(); n.classList.add('err'); n.focus(); return; }
        var b = document.getElementById('ctSubmit'); b.disabled = true; b.lastChild.textContent = ' Creating…';
    });
})();
</script>
@endsection
