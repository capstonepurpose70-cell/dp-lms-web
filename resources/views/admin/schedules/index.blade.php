@extends('layouts.admin')
@section('title', 'Class Schedules')

@section('content')
@include('admin.partials.list-ui')
@php
    $days = \App\Models\ClassSchedule::DAYS;

    // Sto. Domingo NHS bell schedule (from the school timetable) — quick-fill buttons
    $periods = [
        ['07:30', '08:20'], ['08:20', '09:10'], ['09:30', '10:20'], ['10:20', '11:10'],
        ['11:10', '12:00'], ['13:00', '13:50'], ['13:50', '14:40'], ['14:40', '15:30'],
    ];

    $all         = $schedules->flatten();
    $classCount  = $all->count();
    $weekMinutes = $all->sum(function ($p) {
        $s = strtotime((string) $p->start_time); $e = strtotime((string) $p->end_time);
        return ($s !== false && $e !== false && $e > $s) ? intdiv($e - $s, 60) : 0;
    });
    $scheduledSubjectIds = $all->pluck('subject_id')->unique()->all();
    $unscheduled = $subjects->reject(fn ($sub) => in_array($sub->id, $scheduledSubjectIds));
    $perSubject  = $all->countBy('subject_id');   // classes a week per subject

    // Previous / next section (same order as the list)
    $pos         = $section ? $sections->search(fn ($s) => $s->id === $section->id) : false;
    $prevSection = $pos !== false && $pos > 0 ? $sections[$pos - 1] : null;
    $nextSection = $pos !== false && $pos < $sections->count() - 1 ? $sections[$pos + 1] : null;
    $sectionUrl  = fn ($s) => route('admin.schedules.index', ['section_id' => $s->id], false);

    // Restore edit mode after a failed update (validation error)
    $editingId = (int) old('_editing');
    $editing   = $editingId ? $all->firstWhere('id', $editingId) : null;

    // Add mode: the days and times to start from. After a save it continues where the last class ended.
    $last = session('sch_last');
    $pickedDays = array_map('intval', (array) old('days', $last['days'] ?? array_keys($days)));
    $startDefault = '07:30'; $endDefault = '08:20';
    if (is_array($last) && preg_match('/^\d{2}:\d{2}$/', $last['end'] ?? '')) {
        $next = collect($periods)->first(fn ($p) => $p[0] >= $last['end']);
        [$startDefault, $endDefault] = $next ?? [$last['end'], date('H:i', strtotime($last['end']) + 50 * 60)];
    }

    $palette = ['#2563eb', '#16a34a', '#9333ea', '#ea580c', '#0891b2', '#db2777', '#ca8a04', '#4f46e5', '#0d9488', '#dc2626'];
    $colorOf = fn ($subjectId) => $palette[((int) $subjectId) % count($palette)];
@endphp
<style>
    /* Uses admin layout tokens (--white, --slate-*, --border-default) so dark mode works */
    .sch-page { animation: sch-fade .22s ease both; }
    .sch-page.sch-swapped { animation: none; }
    .sch-page[aria-busy="true"] .sch-week, .sch-page[aria-busy="true"] .sch-blank,
    .sch-page[aria-busy="true"] .sch-plan-list { opacity: .55; transition: opacity .15s ease; pointer-events: none; }
    @keyframes sch-fade { from { opacity:0; transform:translateY(6px);} to { opacity:1; transform:none;} }

    .sch-head { display:flex; justify-content:space-between; align-items:flex-end; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
    .sch-head h1 { font-size:26px; font-weight:800; margin:0; color:var(--slate-900); letter-spacing:-0.01em; }
    .sch-head p  { margin:4px 0 0; font-size:14px; color:var(--slate-600); line-height:1.5; }

    .sch-card { background:var(--white); border:1px solid var(--border-default); border-radius:16px; padding:20px; box-shadow:var(--shadow-sm); }
    .sch-card h3 { margin:0 0 4px; font-size:14.5px; font-weight:800; color:var(--slate-900); }
    .sch-label { display:block; font-size:12px; font-weight:700; margin:12px 0 5px; color:var(--slate-700); }
    .sch-label .opt { font-weight:500; color:var(--slate-400); }
    .sch-in {
        width:100%; min-height:44px; padding:10px 12px; border:1.5px solid var(--border-default); border-radius:10px;
        font-size:13.5px; background:var(--white); color:var(--slate-900); box-sizing:border-box; transition:border-color .15s, box-shadow .15s;
    }
    .sch-in:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
    .sch-row2 { display:grid; grid-template-columns:1fr 1fr; gap:10px; }

    /* Section picker bar */
    .sch-picker { display:flex; gap:14px; align-items:flex-end; flex-wrap:wrap; margin-bottom:16px; }
    .sch-picker .pick { flex:1; min-width:240px; }
    .sch-picker .pick .sch-label { margin-top:0; }
    .sch-sum { display:flex; gap:10px; flex-wrap:wrap; }
    .sch-term { flex-basis:100%; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px 16px;
        padding-top:12px; border-top:1px solid var(--border-default); }
    .sch-term-tabs { display:inline-flex; padding:3px; border-radius:10px; background:var(--slate-100); }
    .sch-term-tabs a { padding:7px 14px; border-radius:8px; font-size:13px; font-weight:700; color:var(--slate-600); text-decoration:none; }
    .sch-term-tabs a[aria-current="page"] { background:var(--white); color:var(--slate-900); box-shadow:var(--shadow-sm); }
    .sch-term-now { display:flex; flex-wrap:wrap; align-items:center; gap:10px; margin:0; font-size:13px; color:var(--slate-600); }
    .sch-term-now b { color:var(--slate-900); }
    .sch-term-switch { border:1px solid #2563eb; background:transparent; color:#2563eb; border-radius:9px; padding:7px 12px; font-size:12.5px; font-weight:700; cursor:pointer; }
    .sch-term-switch:hover { background:rgba(37,99,235,.08); }
    .sch-pill { display:inline-flex; align-items:center; gap:4px; min-height:44px; background:var(--slate-100); color:var(--slate-700);
                border-radius:10px; padding:0 14px; font-size:12.5px; font-weight:600; white-space:nowrap; }
    .sch-pill b { color:var(--slate-900); font-size:14px; margin-right:3px; }

    .sch-grid { display:grid; grid-template-columns: 330px minmax(0,1fr); gap:16px; align-items:start; }

    .sch-presets { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:6px; margin-top:8px; }
    .sch-preset { border:1px solid var(--border-default); background:var(--white); color:var(--slate-700); border-radius:8px;
        min-height:34px; padding:4px 6px; font-size:11.5px; font-weight:600; text-align:center; cursor:pointer; transition:border-color .15s, color .15s; }
    .sch-preset:hover { border-color:#2563eb; color:#2563eb; }

    .sch-btn { margin-top:16px; width:100%; min-height:44px; padding:11px; border:0; border-radius:10px; font-weight:700; font-size:13.5px;
        cursor:pointer; background:#1d4ed8; color:#fff; transition:background .15s; }
    .sch-btn:hover { background:#1e40af; }
    .sch-btn:disabled { background:#94a3b8; cursor:not-allowed; }
    .sch-btn.edit { background:#15803d; }
    .sch-btn.edit:hover { background:#166534; }
    .sch-cancel { margin-top:8px; width:100%; min-height:40px; padding:9px; border:1px solid var(--border-default); border-radius:10px; background:transparent;
        color:var(--slate-700); font-weight:600; font-size:13px; cursor:pointer; }
    .sch-cancel:hover { background:var(--slate-50); }
    .sch-mode { display:inline-block; font-size:11px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; padding:3px 8px;
        border-radius:6px; background:rgba(22,163,74,.12); color:#15803d; margin-left:6px; vertical-align:middle; }
    .sch-days { display:grid; grid-template-columns:repeat(5, minmax(0, 1fr)); gap:6px; }
    .sch-days-foot { display:flex; justify-content:flex-end; margin-top:6px; }
    .sch-daychip { position:relative; }
    .sch-daychip input { position:absolute; opacity:0; inset:0; margin:0; cursor:pointer; }
    .sch-daychip span { display:grid; place-items:center; width:100%; height:40px; padding:0 4px; border-radius:9px; border:1.5px solid var(--border-default);
        font-size:12.5px; font-weight:700; color:var(--slate-600); background:var(--white); transition:background-color .15s, border-color .15s, color .15s; }
    .sch-daychip input:checked + span { background:#1d4ed8; border-color:#1d4ed8; color:#fff; }
    .sch-daychip input:focus-visible + span { outline:2px solid #2563eb; outline-offset:2px; }
    .sch-daychip:hover span { border-color:#2563eb; }
    .sch-alldays { border:0; background:none; padding:0 4px; font-size:12px; font-weight:700; color:#2563eb; cursor:pointer; }
    .sch-alldays:hover { text-decoration:underline; }
    .sch-preset[aria-pressed="true"] { border-color:#2563eb; background:rgba(37,99,235,.1); color:#1d4ed8; }
    [data-theme="dark"] .sch-preset[aria-pressed="true"] { color:#93c5fd; }
    .sch-hint { font-size:12px; color:var(--slate-500); margin-top:12px; line-height:1.55; }

    .sch-pickrow { display:flex; gap:8px; align-items:stretch; }
    .sch-pickrow select { flex:1; min-width:0; }
    .sch-step { display:inline-grid; place-items:center; width:42px; border-radius:10px; border:1px solid var(--border-default);
                background:var(--white); color:var(--slate-700); text-decoration:none; flex-shrink:0; }
    .sch-step:hover { border-color:#2563eb; color:#1d4ed8; }
    .sch-step[aria-disabled="true"] { opacity:.35; pointer-events:none; }
    .sch-step svg { width:18px; height:18px; }

    .sch-plan { margin-bottom:16px; }
    .sch-plan-h { display:flex; flex-wrap:wrap; align-items:baseline; gap:4px 12px; margin-bottom:12px; }
    .sch-plan-h h2 { margin:0; font-size:15px; font-weight:800; color:var(--slate-900); }
    .sch-plan-h p { margin:0; font-size:13px; color:var(--slate-500); }
    .sch-plan-list { display:grid; grid-template-columns:repeat(auto-fill, minmax(210px, 1fr)); gap:8px; }
    .sch-subj { display:inline-flex; align-items:center; gap:8px; min-height:40px; padding:6px 12px 6px 10px; border-radius:10px;
                border:1px solid var(--border-default); background:var(--white); color:var(--slate-900); font:inherit; font-size:13px;
                font-weight:700; cursor:pointer; text-align:left; transition:border-color .15s ease, background-color .15s ease; }
    .sch-subj:hover { border-color:var(--c); }
    .sch-subj:focus-visible { outline:2px solid #2563eb; outline-offset:2px; }
    .sch-subj i { width:9px; height:9px; border-radius:50%; background:var(--c); flex-shrink:0; }
    .sch-subj span { margin-left:auto; padding-left:8px; font-size:12px; font-weight:600; color:var(--slate-500); white-space:nowrap; }
    .sch-subj.todo { border-style:dashed; }
    .sch-subj.todo span { color:#b45309; }
    .sch-subj.done span { color:#15803d; }
    .sch-subj.picked { border-color:var(--c); background:color-mix(in srgb, var(--c) 9%, transparent); }
    [data-theme="dark"] .sch-subj.todo span { color:#fbbf24; }
    [data-theme="dark"] .sch-subj.done span { color:#4ade80; }

    .sch-blank { grid-column:1 / -1; text-align:center; padding:48px 20px; border:1.5px dashed var(--border-default); border-radius:14px;
                 color:var(--slate-500); font-size:13.5px; line-height:1.6; background:var(--white); }
    .sch-blank b { display:block; font-size:15px; color:var(--slate-900); margin-bottom:4px; }
    .sch-todo { margin-top:16px; border-top:1px solid var(--border-default); padding-top:14px; }
    .sch-todo h4 { margin:0 0 8px; font-size:12.5px; font-weight:800; color:var(--slate-700); }
    .sch-chip { display:inline-block; margin:0 6px 6px 0; padding:4px 9px; border-radius:7px; font-size:12px; font-weight:600;
        background:rgba(217,119,6,.12); color:#b45309; }
    [data-theme="dark"] .sch-chip { color:#fbbf24; }
    .sch-alldone { font-size:12.5px; color:#15803d; font-weight:600; }
    [data-theme="dark"] .sch-alldone { color:#4ade80; }

    /* Weekly grid */
    .sch-week { display:grid; grid-template-columns:repeat(5, minmax(0,1fr)); gap:12px; }
    .sch-day { background:var(--white); border:1px solid var(--border-default); border-radius:14px; overflow:hidden; box-shadow:var(--shadow-sm); }
    .sch-day-h { padding:10px 12px; border-bottom:1px solid var(--border-default); display:flex; justify-content:space-between; align-items:center;
        font-size:11.5px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; color:var(--slate-700); background:var(--slate-50); }
    .sch-day-h span { font-size:11px; font-weight:700; color:var(--slate-500); text-transform:none; letter-spacing:0; }
    .sch-day.today .sch-day-h { color:#1d4ed8; }
    .sch-day-b { padding:10px; display:flex; flex-direction:column; gap:8px; }

    .sch-item { border:1px solid var(--border-default); border-left:4px solid var(--c, #2563eb); border-radius:10px; padding:10px 11px;
        background:var(--white); cursor:pointer; transition:box-shadow .15s, border-color .15s; }
    .sch-item:focus-visible { outline:2px solid #2563eb; outline-offset:2px; }
    .sch-item:hover { box-shadow:var(--shadow-md); }
    .sch-item.editing { box-shadow:0 0 0 3px rgba(22,163,74,.35); }
    .sch-item-time { font-size:11.5px; font-weight:800; color:var(--c, #2563eb); }
    .sch-item-subj { font-size:13px; font-weight:700; margin-top:2px; color:var(--slate-900); line-height:1.3; }
    .sch-item-meta { font-size:11.5px; color:var(--slate-500); margin-top:3px; line-height:1.4; }
    .sch-item-meta .nt { color:#b45309; font-weight:600; }
    [data-theme="dark"] .sch-item-meta .nt { color:#fbbf24; }
    .sch-item-act { display:flex; gap:10px; margin-top:6px; }
    .sch-link { border:0; background:transparent; font-size:11.5px; font-weight:700; cursor:pointer; padding:2px 0; }
    .sch-link.edit { color:#2563eb; }
    .sch-link.del  { color:#dc2626; }
    .sch-link:hover { text-decoration:underline; }
    .sch-none { font-size:12px; color:var(--slate-400); text-align:center; padding:16px 4px; border:1.5px dashed var(--border-default); border-radius:10px; }

    .sch-alert { border-radius:12px; padding:12px 14px; font-size:13.5px; margin-bottom:14px; font-weight:500; }
    .sch-ok  { background:rgba(22,163,74,.1); color:#15803d; border:1px solid rgba(22,163,74,.3); }
    .sch-err { background:rgba(220,38,38,.08); color:#b91c1c; border:1px solid rgba(220,38,38,.3); }
    [data-theme="dark"] .sch-ok  { color:#4ade80; }
    [data-theme="dark"] .sch-err { color:#f87171; }
    .sch-err ul { margin:6px 0 0; padding-left:18px; }

    .sch-empty-state { text-align:center; padding:40px 16px; color:var(--slate-500); font-size:13.5px; }
    .sch-empty-state a { color:#2563eb; font-weight:700; }

    @media (min-width:1101px) { #schFormCard { position:sticky; top:16px; } }
    @media (max-width:1280px) { .sch-week { grid-template-columns:repeat(3, minmax(0,1fr)); } }
    @media (max-width:1100px) { .sch-grid { grid-template-columns:1fr; } }
    @media (max-width:700px)  { .sch-week { grid-template-columns:1fr; } }
</style>

<div class="sch-page">

    <div class="sch-head">
        <div>
            <h1>Class Schedules</h1>
            <p>Set the weekly timetable (Monday to Friday) for each section. Students see it once they are enrolled.</p>
        </div>
    </div>

    {{-- Success message: shown by layouts/admin (toast) --}}

    @if($errors->any())
        <div class="sch-alert sch-err" role="alert">
            <strong>Could not save this class:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Section picker + summary --}}
    <div class="sch-card sch-picker">
        <form method="GET" action="{{ route('admin.schedules.index') }}" class="pick">
            <label class="sch-label" for="sectionPicker">Section</label>
            <div class="sch-pickrow">
            <a class="sch-step js-swap" data-no-spa href="{{ $prevSection ? $sectionUrl($prevSection) : '#' }}" @if(!$prevSection) aria-disabled="true" tabindex="-1" @endif
               aria-label="Previous section{{ $prevSection ? ': Grade '.$prevSection->grade_level.' – '.$prevSection->name : '' }}" title="Previous section">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m14.5 6-6 6 6 6"/></svg>
            </a>
            <select name="section_id" id="sectionPicker" class="sch-in">
                @forelse($sections->groupBy('grade_level') as $grade => $group)
                    <optgroup label="Grade {{ $grade }}">
                        @foreach($group as $s)
                            <option value="{{ $s->id }}" @selected($section && $section->id === $s->id)>
                                Grade {{ $s->grade_level }} – {{ $s->name }}
                            </option>
                        @endforeach
                    </optgroup>
                @empty
                    <option value="">No active sections</option>
                @endforelse
            </select>
            <a class="sch-step js-swap" data-no-spa href="{{ $nextSection ? $sectionUrl($nextSection) : '#' }}" @if(!$nextSection) aria-disabled="true" tabindex="-1" @endif
               aria-label="Next section{{ $nextSection ? ': Grade '.$nextSection->grade_level.' – '.$nextSection->name : '' }}" title="Next section">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9.5 6 6 6-6 6"/></svg>
            </a>
            </div>
            <noscript><button type="submit" class="sch-btn" style="width:auto;padding:9px 16px;margin-top:8px;">Show</button></noscript>
        </form>
        @if($section)
            <div class="sch-sum">
                <div class="sch-pill"><b>{{ $classCount }}</b>{{ Str::plural('class', $classCount) }} / week</div>
                <div class="sch-pill"><b>{{ intdiv($weekMinutes, 60) }}h{{ $weekMinutes % 60 ? ' '.($weekMinutes % 60).'m' : '' }}</b>total time</div>
            </div>
            @if($isShs)
                {{-- Senior High: each semester has its own timetable --}}
                <div class="sch-term">
                    <nav class="sch-term-tabs" aria-label="Semester">
                        @foreach(\App\Models\ClassSchedule::SEMESTERS as $num => $label)
                            <a class="js-swap" data-no-spa href="{{ route('admin.schedules.index', ['section_id' => $section->id, 'semester' => $num], false) }}" @if($semester === $num) aria-current="page" @endif>{{ $label }}</a>
                        @endforeach
                    </nav>
                    <form method="POST" action="{{ route('admin.schedules.semester') }}" class="sch-term-now">
                        @csrf
                        <span>Students now see the <b>{{ \App\Models\ClassSchedule::SEMESTERS[$currentSemester] }}</b>.</span>
                        @if($semester !== $currentSemester)
                            <input type="hidden" name="semester" value="{{ $semester }}">
                            <button type="submit" class="sch-term-switch">Switch students to the {{ \App\Models\ClassSchedule::SEMESTERS[$semester] }}</button>
                        @endif
                    </form>
                </div>
            @endif
        @endif
    </div>

    @if(!$section)
        <div class="sch-card">
            <div class="sch-empty-state">
                No active sections yet. Create one first under <a href="{{ route('admin.sections.index') }}">Sections &amp; Advisers</a>.
            </div>
        </div>
    @else
    @if($subjects->isNotEmpty())
        <div class="sch-card sch-plan">
            <div class="sch-plan-h">
                <h2>Grade {{ $section->grade_level }} subjects</h2>
                <p>
                    @if($unscheduled->isEmpty())
                        Every subject has a class. Pick one to add more.
                    @else
                        {{ $subjects->count() - $unscheduled->count() }} of {{ $subjects->count() }} have a class. Pick a subject to add it.
                    @endif
                </p>
            </div>
            <div class="sch-plan-list">
                @foreach($subjects as $sub)
                    @php $n = (int) ($perSubject[$sub->id] ?? 0); @endphp
                    <button type="button" class="sch-subj {{ $n ? 'done' : 'todo' }}" data-subject="{{ $sub->id }}" style="--c: {{ $colorOf($sub->id) }};">
                        <i aria-hidden="true"></i>{{ $sub->name }}
                        <span>{{ $n ? $n . ' a week' : 'Not yet' }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif
    <div class="sch-grid">

        {{-- ── Add / Edit form ── --}}
        <div class="sch-card" id="schFormCard">
            <h3 id="schFormTitle">
                {{ $editing ? 'Edit class' : 'Add a class' }}
                <span class="sch-mode" id="schModeTag" @if(!$editing) style="display:none" @endif>Editing</span>
            </h3>
            <div class="sch-alert sch-err" id="schFormErr" role="alert" hidden style="margin:12px 0 0;"></div>
            <form method="POST" id="schForm"
                  action="{{ $editing ? route('admin.schedules.update', $editing) : route('admin.schedules.store') }}"
                  data-store-url="{{ route('admin.schedules.store') }}">
                @csrf
                <input type="hidden" name="_method" value="PUT" id="schMethod" @disabled(!$editing)>
                <input type="hidden" name="_editing" value="{{ $editing?->id }}" id="schEditing">
                <input type="hidden" name="section_id" value="{{ $section->id }}">
                @if($isShs)<input type="hidden" name="semester" value="{{ $semester }}">@endif

                <label class="sch-label" for="subject_id">Subject</label>
                <select name="subject_id" id="subject_id" class="sch-in" required>
                    <option value="">— Select subject —</option>
                    @forelse($subjects as $sub)
                        <option value="{{ $sub->id }}" @selected((string) old('subject_id') === (string) $sub->id)>
                            {{ $sub->name }}
                        </option>
                    @empty
                        <option value="" disabled>No Grade {{ $section->grade_level }} subjects yet</option>
                    @endforelse
                </select>

                <label class="sch-label" for="teacher_id">Teacher <span class="opt">(optional)</span></label>
                <select name="teacher_id" id="teacher_id" class="sch-in">
                    <option value="">— To be assigned —</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" @selected((string) old('teacher_id') === (string) $t->id)>
                            {{ $t->name }}{{ ($t->status && $t->status !== 'approved') ? ' ('.$t->status.')' : '' }}
                        </option>
                    @endforeach
                </select>

                {{-- Add: several days at once. Edit: the one day of that class. --}}
                <div id="schDaysWrap" @if($editing) hidden @endif>
                    <span class="sch-label" id="schDaysLabel">Days <span class="opt">(tap a day to leave it out)</span></span>
                    <div class="sch-days" role="group" aria-labelledby="schDaysLabel">
                        @foreach($days as $num => $label)
                            <label class="sch-daychip" title="{{ $label }}">
                                <input type="checkbox" name="days[]" value="{{ $num }}" @checked(in_array($num, $pickedDays, true)) @disabled($editing)>
                                <span>{{ substr($label, 0, 3) }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="sch-days-foot"><button type="button" class="sch-alldays" id="schAllDays">Every day</button></div>
                </div>
                <div id="schDayWrap" @unless($editing) hidden @endunless>
                    <label class="sch-label" for="day_of_week">Day</label>
                    <select name="day_of_week" id="day_of_week" class="sch-in" required @disabled(!$editing)>
                        @foreach($days as $num => $label)
                            <option value="{{ $num }}" @selected((string) old('day_of_week') === (string) $num)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sch-row2">
                    <div>
                        <label class="sch-label" for="start_time">Start</label>
                        <input type="time" name="start_time" id="start_time" class="sch-in"
                               value="{{ old('start_time', $startDefault) }}" required>
                    </div>
                    <div>
                        <label class="sch-label" for="end_time">End</label>
                        <input type="time" name="end_time" id="end_time" class="sch-in"
                               value="{{ old('end_time', $endDefault) }}" required>
                    </div>
                </div>
                <div class="sch-presets" aria-label="Bell schedule periods">
                    @foreach($periods as [$ps, $pe])
                        <button type="button" class="sch-preset" data-start="{{ $ps }}" data-end="{{ $pe }}" aria-pressed="false">
                            {{ \App\Models\ClassSchedule::formatTime($ps) }}–{{ \App\Models\ClassSchedule::formatTime($pe) }}
                        </button>
                    @endforeach
                </div>

                <label class="sch-label" for="room">Room <span class="opt">(optional)</span></label>
                <input type="text" name="room" id="room" class="sch-in" maxlength="60"
                       value="{{ old('room') }}" placeholder="e.g. Room 5, Science Lab">

                <button type="submit" id="schSubmit" class="sch-btn {{ $editing ? 'edit' : '' }}" @disabled($subjects->isEmpty())>
                    {{ $editing ? 'Save changes' : 'Add to timetable' }}
                </button>
                <button type="button" id="schCancel" class="sch-cancel" @if(!$editing) style="display:none" @endif>Cancel editing</button>

                <p class="sch-hint">
                    @if($subjects->isEmpty())
                        There are no active Grade {{ $section->grade_level }} subjects yet — add subjects first.
                    @else
                        Overlapping classes are blocked automatically for the same section, the same teacher, and the same room.
                        Choosing a teacher also gives them this subject in their portal (gradebook, materials, quizzes).
                    @endif
                </p>
            </form>

        </div>

        {{-- ── Weekly grid ── --}}
        <div>
            @if($classCount === 0)
                <div class="sch-blank">
                    <b>No classes yet for Grade {{ $section->grade_level }} – {{ $section->name }}</b>
                    Pick a subject above, choose its teacher, the days and the period, then press Add.
                </div>
            @else
            <div class="sch-week">
                @foreach($days as $num => $label)
                    @php $dayItems = $schedules[$num] ?? collect(); @endphp
                    <div class="sch-day {{ now()->dayOfWeekIso === $num ? 'today' : '' }}">
                        <div class="sch-day-h">{{ $label }} <span>{{ $dayItems->count() ?: '' }}</span></div>
                        <div class="sch-day-b">
                            @forelse($dayItems as $p)
                                <div class="sch-item {{ $editing && $editing->id === $p->id ? 'editing' : '' }}" style="--c: {{ $colorOf($p->subject_id) }};" tabindex="0"
                                     aria-label="Edit {{ $p->subject?->name ?? 'class' }}, {{ $p->day_name }} {{ $p->time_range }}"
                                     data-id="{{ $p->id }}"
                                     data-url="{{ route('admin.schedules.update', $p) }}"
                                     data-subject="{{ $p->subject_id }}"
                                     data-teacher="{{ $p->teacher_id }}"
                                     data-day="{{ $p->day_of_week }}"
                                     data-start="{{ substr((string) $p->start_time, 0, 5) }}"
                                     data-end="{{ substr((string) $p->end_time, 0, 5) }}"
                                     data-room="{{ $p->room }}">
                                    <div class="sch-item-time">{{ str_replace([' AM', ' PM'], ["\u{00A0}AM", "\u{00A0}PM"], $p->time_range) }}</div>
                                    <div class="sch-item-subj">{{ $p->subject?->name ?? 'Subject removed' }}</div>
                                    <div class="sch-item-meta">
                                        @if($p->teacher) {{ $p->teacher->name }} @else <span class="nt">No teacher yet</span> @endif
                                        @if($p->room) · {{ $p->room }} @endif
                                    </div>
                                    <div class="sch-item-act">
                                        <button type="button" class="sch-link edit js-edit">Edit</button>
                                        <button type="button" class="sch-link del js-remove" data-url="{{ route('admin.schedules.destroy', $p) }}"
                                                data-label="{{ $p->subject?->name ?? 'This class' }}, {{ $p->day_name }} {{ $p->time_range }}">Remove</button>
                                    </div>
                                </div>
                            @empty
                                <div class="sch-none">No classes</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>
    @endif

    <dialog class="lm-dialog small" id="schRemove" aria-labelledby="schRemoveTitle">
        <form method="POST" action="" class="lm-dialog-box" id="schRemoveForm">
            @csrf @method('DELETE')
            <div class="lm-dialog-body">
                <h2 class="lm-confirm-title" id="schRemoveTitle">Remove this class?</h2>
                <p class="lm-help first"><b id="schRemoveName"></b> will be taken off the timetable. Students and the teacher stop seeing it.</p>
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
    var INDEX = @json(route('admin.schedules.index', [], false));
    var CSRF  = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var swapping = 0;

    function toast(msg, type) { if (window.dpToast) window.dpToast(msg, type); }
    function page() { return document.querySelector('.sch-page'); }
    function here() { return location.pathname + location.search; }

    // Put a fresh copy of the timetable area in place of the current one (no full reload, no skeleton)
    function swap(url, push) {
        var cur = page();
        if (!cur) { location.href = url; return; }
        var id = ++swapping;
        cur.setAttribute('aria-busy', 'true');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', cache: 'no-store' })
            .then(function (r) {
                if (r.status === 401 || r.status === 419) { location.href = url; throw new Error('signed out'); }
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.text();
            })
            .then(function (html) {
                if (id !== swapping) return;
                var fresh = new DOMParser().parseFromString(html, 'text/html').querySelector('.sch-page');
                if (!fresh) { location.href = url; return; }
                fresh.classList.add('sch-swapped');
                page().replaceWith(document.importNode(fresh, true));
                if (push && url !== here()) history.pushState(null, '', url);
                init();
            })
            .catch(function (err) {
                if (id !== swapping || (err && err.message === 'signed out')) return;
                var p = page(); if (p) p.removeAttribute('aria-busy');
                toast('Could not load the timetable. Check the connection and try again.', 'error');
            });
    }

    // Send a form in the background; the server answers JSON
    function send(f) {
        return fetch(f.action, { method: 'POST', body: new FormData(f), credentials: 'same-origin',
                                 headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF } })
            .then(function (r) {
                return r.json().catch(function () { return {}; })
                    .then(function (d) { d = d || {}; return { ok: r.ok && !!d.ok, status: r.status, d: d }; });
            });
    }
    function problems(res) {
        if (res.status === 419) return ['This page expired. Reload the page and try again.'];
        if (res.status === 401) return ['You were signed out. Sign in again to continue.'];
        var out = [];
        if (res.d.errors) Object.keys(res.d.errors).forEach(function (k) { out = out.concat(res.d.errors[k]); });
        if (!out.length) out.push(res.d.message || 'Could not save. Please try again.');
        return out;
    }

    function init() {
        var root = page();
        if (!root) return;
        var $ = function (id) { return document.getElementById(id); };

        // Section and semester: switch in place
        var picker = $('sectionPicker');
        if (picker) picker.addEventListener('change', function () {
            swap(INDEX + '?section_id=' + encodeURIComponent(picker.value), true);
        });
        root.querySelectorAll('.js-swap').forEach(function (a) {
            a.addEventListener('click', function (e) {
                if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
                e.preventDefault();
                if (a.getAttribute('aria-disabled') === 'true') return;
                swap(a.getAttribute('href'), true);
            });
        });
        var term = root.querySelector('form.sch-term-now');
        if (term) term.addEventListener('submit', function (e) {
            e.preventDefault();
            var b = term.querySelector('button[type="submit"]');
            if (b) b.disabled = true;
            send(term).then(function (res) {
                if (!res.ok) { toast(problems(res)[0], 'error'); if (b) b.disabled = false; return; }
                toast(res.d.message, 'success');
                swap(here(), false);
            }).catch(function () { toast('No connection. Nothing was changed.', 'error'); if (b) b.disabled = false; });
        });

        var form = $('schForm');
        if (!form) return;
        var method = $('schMethod'), editing = $('schEditing'), title = $('schFormTitle'), tag = $('schModeTag'),
            submit = $('schSubmit'), cancel = $('schCancel'), errBox = $('schFormErr');
        var dayBoxes = root.querySelectorAll('#schDaysWrap input[type="checkbox"]');
        var presets  = root.querySelectorAll('.sch-preset');
        var subjButtons = root.querySelectorAll('.sch-subj');

        // Bell schedule quick-fill; the period that matches the times is shown as chosen
        function markPreset() {
            presets.forEach(function (b) {
                b.setAttribute('aria-pressed', b.dataset.start === $('start_time').value && b.dataset.end === $('end_time').value ? 'true' : 'false');
            });
        }
        function markSubject() {
            subjButtons.forEach(function (b) { b.classList.toggle('picked', b.dataset.subject === $('subject_id').value); });
        }
        presets.forEach(function (b) {
            b.addEventListener('click', function () {
                $('start_time').value = b.dataset.start;
                $('end_time').value   = b.dataset.end;
                markPreset();
            });
        });
        $('start_time').addEventListener('input', markPreset);
        $('end_time').addEventListener('input', markPreset);

        function isEditing() { return !method.disabled; }
        function addLabel() {
            var n = Array.prototype.filter.call(dayBoxes, function (c) { return c.checked; }).length;
            submit.textContent = n > 1 ? 'Add on ' + n + ' days' : 'Add to timetable';
        }
        dayBoxes.forEach(function (c) { c.addEventListener('change', addLabel); });
        $('schAllDays').addEventListener('click', function () {
            dayBoxes.forEach(function (c) { c.checked = true; });
            addLabel();
        });

        function showErrors(list) {
            var strong = document.createElement('strong'), ul = document.createElement('ul');
            strong.textContent = 'Could not save this class:';
            list.forEach(function (m) { var li = document.createElement('li'); li.textContent = m; ul.appendChild(li); });
            errBox.replaceChildren(strong, ul);
            errBox.hidden = false;
            errBox.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
        function clearErrors() { errBox.hidden = true; errBox.replaceChildren(); }

        // Save: stays on the page; on success the timetable refreshes in place
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!isEditing() && !Array.prototype.some.call(dayBoxes, function (c) { return c.checked; })) {
                toast('Choose at least one day.', 'warning');
                return;
            }
            clearErrors();
            var label = submit.textContent;
            submit.disabled = true;
            submit.textContent = 'Saving…';
            send(form).then(function (res) {
                if (!res.ok) { showErrors(problems(res)); submit.disabled = false; submit.textContent = label; return; }
                toast(res.d.message, 'success');
                swap(here(), false);
            }).catch(function () {
                showErrors(['No connection. The class was not saved.']);
                submit.disabled = false; submit.textContent = label;
            });
        });

        function setMode(edit) {
            $('schDaysWrap').hidden = edit;
            $('schDayWrap').hidden = !edit;
            dayBoxes.forEach(function (c) { c.disabled = edit; });
            $('day_of_week').disabled = !edit;
        }
        function setTitle(text) { title.firstChild.nodeValue = text + ' '; }
        function clearHighlight() { root.querySelectorAll('.sch-item.editing').forEach(function (el) { el.classList.remove('editing'); }); }

        // Edit: the whole class card opens it in the form (update route, PUT)
        function startEdit(card) {
            clearErrors();
            form.action = card.dataset.url;
            method.disabled = false;
            editing.value = card.dataset.id;
            $('subject_id').value  = card.dataset.subject || '';
            $('teacher_id').value  = card.dataset.teacher || '';
            setMode(true);
            $('day_of_week').value = card.dataset.day;
            $('start_time').value  = card.dataset.start;
            $('end_time').value    = card.dataset.end;
            $('room').value        = card.dataset.room || '';
            markPreset();
            markSubject();
            setTitle('Edit class'); tag.style.display = '';
            submit.textContent = 'Save changes'; submit.classList.add('edit');
            cancel.style.display = '';
            clearHighlight(); card.classList.add('editing');
            $('schFormCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        root.querySelectorAll('.sch-item').forEach(function (card) {
            card.addEventListener('click', function (e) {
                if (e.target.closest('.js-remove')) return;
                startEdit(card);
            });
            card.addEventListener('keydown', function (e) {
                if (e.target === card && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); startEdit(card); }
            });
        });

        // Subject checklist: pick a subject to add it (leaves edit mode first)
        subjButtons.forEach(function (b) {
            b.addEventListener('click', function () {
                if (isEditing()) cancel.click();
                $('subject_id').value = b.dataset.subject;
                markSubject();
                if (window.matchMedia('(max-width: 1100px)').matches) $('schFormCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
                $('teacher_id').focus({ preventScroll: true });
            });
        });
        $('subject_id').addEventListener('change', markSubject);

        // Cancel: back to "Add a class"
        cancel.addEventListener('click', function () {
            clearErrors();
            form.action = form.dataset.storeUrl;
            method.disabled = true;
            editing.value = '';
            form.reset();
            $('subject_id').value = ''; $('teacher_id').value = ''; $('room').value = '';
            $('day_of_week').selectedIndex = 0;
            $('start_time').value = '07:30'; $('end_time').value = '08:20';
            setMode(false);
            dayBoxes.forEach(function (c) { c.checked = true; });
            setTitle('Add a class'); tag.style.display = 'none';
            submit.classList.remove('edit');
            addLabel();
            markPreset();
            cancel.style.display = 'none';
            clearHighlight();
            markSubject();
        });

        // Remove: confirm in the dialog, then take it off in place
        var dlg = $('schRemove'), rform = $('schRemoveForm');
        root.querySelectorAll('.js-remove').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                rform.action = btn.dataset.url;
                $('schRemoveName').textContent = btn.dataset.label;
                dlg.showModal();
            });
        });
        dlg.addEventListener('click', function (e) { if (e.target === dlg || e.target.closest('[data-close]')) dlg.close(); });
        rform.addEventListener('submit', function (e) {
            e.preventDefault();
            var b = rform.querySelector('button[type="submit"]');
            b.disabled = true;
            send(rform).then(function (res) {
                dlg.close();
                b.disabled = false;
                if (!res.ok) { toast(problems(res)[0], 'error'); return; }
                toast(res.d.message, 'success');
                swap(here(), false);
            }).catch(function () { b.disabled = false; toast('No connection. The class was not removed.', 'error'); });
        });

        markPreset();
        markSubject();
        if (!isEditing()) addLabel();
    }

    init();
})();
</script>
@endsection
