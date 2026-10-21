@extends('layouts.admin')
@section('title', 'Class Schedules')

@section('content')
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

    // Restore edit mode after a failed update (validation error)
    $editingId = (int) old('_editing');
    $editing   = $editingId ? $all->firstWhere('id', $editingId) : null;

    $palette = ['#2563eb', '#16a34a', '#9333ea', '#ea580c', '#0891b2', '#db2777', '#ca8a04', '#4f46e5', '#0d9488', '#dc2626'];
    $colorOf = fn ($subjectId) => $palette[((int) $subjectId) % count($palette)];
@endphp
<style>
    /* Uses admin layout tokens (--white, --slate-*, --border-default) so dark mode works */
    .sch-page { animation: sch-fade .22s ease both; }
    @keyframes sch-fade { from { opacity:0; transform:translateY(6px);} to { opacity:1; transform:none;} }

    .sch-head { display:flex; justify-content:space-between; align-items:flex-end; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
    .sch-head h1 { font-size:22px; font-weight:800; margin:0; color:var(--slate-900); letter-spacing:-0.01em; }
    .sch-head p  { margin:4px 0 0; font-size:13px; color:var(--slate-500); line-height:1.5; }

    .sch-card { background:var(--white); border:1px solid var(--border-default); border-radius:16px; padding:18px; box-shadow:var(--shadow-sm); }
    .sch-card h3 { margin:0 0 4px; font-size:14.5px; font-weight:800; color:var(--slate-900); }
    .sch-label { display:block; font-size:12px; font-weight:700; margin:12px 0 5px; color:var(--slate-700); }
    .sch-label .opt { font-weight:500; color:var(--slate-400); }
    .sch-in {
        width:100%; padding:10px 12px; border:1.5px solid var(--border-default); border-radius:10px;
        font-size:13.5px; background:var(--white); color:var(--slate-900); box-sizing:border-box; transition:border-color .15s, box-shadow .15s;
    }
    .sch-in:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
    .sch-row2 { display:grid; grid-template-columns:1fr 1fr; gap:10px; }

    /* Section picker bar */
    .sch-picker { display:flex; gap:14px; align-items:flex-end; flex-wrap:wrap; margin-bottom:16px; }
    .sch-picker .pick { flex:1; min-width:240px; }
    .sch-picker .pick .sch-label { margin-top:0; }
    .sch-sum { display:flex; gap:10px; flex-wrap:wrap; }
    .sch-pill { background:var(--slate-100); color:var(--slate-700); border-radius:10px; padding:8px 12px; font-size:12.5px; font-weight:600; }
    .sch-pill b { color:var(--slate-900); font-size:14px; margin-right:3px; }

    .sch-grid { display:grid; grid-template-columns: 330px minmax(0,1fr); gap:16px; align-items:start; }

    .sch-presets { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }
    .sch-preset { border:1px solid var(--border-default); background:var(--white); color:var(--slate-700); border-radius:8px;
        padding:5px 8px; font-size:11.5px; font-weight:600; cursor:pointer; transition:border-color .15s, color .15s; }
    .sch-preset:hover { border-color:#2563eb; color:#2563eb; }

    .sch-btn { margin-top:14px; width:100%; padding:11px; border:0; border-radius:10px; font-weight:700; font-size:13.5px;
        cursor:pointer; background:#1d4ed8; color:#fff; transition:background .15s; }
    .sch-btn:hover { background:#1e40af; }
    .sch-btn:disabled { background:#94a3b8; cursor:not-allowed; }
    .sch-btn.edit { background:#15803d; }
    .sch-btn.edit:hover { background:#166534; }
    .sch-cancel { margin-top:8px; width:100%; padding:9px; border:1px solid var(--border-default); border-radius:10px; background:transparent;
        color:var(--slate-700); font-weight:600; font-size:13px; cursor:pointer; }
    .sch-cancel:hover { background:var(--slate-50); }
    .sch-mode { display:inline-block; font-size:11px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; padding:3px 8px;
        border-radius:6px; background:rgba(22,163,74,.12); color:#15803d; margin-left:6px; vertical-align:middle; }
    .sch-hint { font-size:12px; color:var(--slate-500); margin-top:12px; line-height:1.55; }

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

    .sch-item { border:1px solid var(--border-default); border-left:4px solid var(--c, #2563eb); border-radius:10px; padding:9px 10px;
        background:var(--white); transition:box-shadow .15s, transform .15s; }
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
            <select name="section_id" id="sectionPicker" class="sch-in" onchange="this.form.submit()">
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
            <noscript><button type="submit" class="sch-btn" style="width:auto;padding:9px 16px;">Show</button></noscript>
        </form>
        @if($section)
            <div class="sch-sum">
                <div class="sch-pill"><b>{{ $classCount }}</b>{{ Str::plural('class', $classCount) }} / week</div>
                <div class="sch-pill"><b>{{ intdiv($weekMinutes, 60) }}h{{ $weekMinutes % 60 ? ' '.($weekMinutes % 60).'m' : '' }}</b>total time</div>
                <div class="sch-pill"><b>{{ $subjects->count() - $unscheduled->count() }}/{{ $subjects->count() }}</b>subjects scheduled</div>
            </div>
        @endif
    </div>

    @if(!$section)
        <div class="sch-card">
            <div class="sch-empty-state">
                No active sections yet. Create one first under <a href="{{ route('admin.sections.index') }}">Sections &amp; Advisers</a>.
            </div>
        </div>
    @else
    <div class="sch-grid">

        {{-- ── Add / Edit form ── --}}
        <div class="sch-card" id="schFormCard">
            <h3 id="schFormTitle">
                {{ $editing ? 'Edit Class' : 'Add a Class' }}
                <span class="sch-mode" id="schModeTag" @if(!$editing) style="display:none" @endif>Editing</span>
            </h3>
            <form method="POST" id="schForm"
                  action="{{ $editing ? route('admin.schedules.update', $editing) : route('admin.schedules.store') }}"
                  data-store-url="{{ route('admin.schedules.store') }}">
                @csrf
                <input type="hidden" name="_method" value="PUT" id="schMethod" @disabled(!$editing)>
                <input type="hidden" name="_editing" value="{{ $editing?->id }}" id="schEditing">
                <input type="hidden" name="section_id" value="{{ $section->id }}">

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

                <label class="sch-label" for="day_of_week">Day</label>
                <select name="day_of_week" id="day_of_week" class="sch-in" required>
                    @foreach($days as $num => $label)
                        <option value="{{ $num }}" @selected((string) old('day_of_week') === (string) $num)>{{ $label }}</option>
                    @endforeach
                </select>

                <div class="sch-row2">
                    <div>
                        <label class="sch-label" for="start_time">Start</label>
                        <input type="time" name="start_time" id="start_time" class="sch-in"
                               value="{{ old('start_time', '07:30') }}" required>
                    </div>
                    <div>
                        <label class="sch-label" for="end_time">End</label>
                        <input type="time" name="end_time" id="end_time" class="sch-in"
                               value="{{ old('end_time', '08:20') }}" required>
                    </div>
                </div>
                <div class="sch-presets" aria-label="Bell schedule periods">
                    @foreach($periods as [$ps, $pe])
                        <button type="button" class="sch-preset" data-start="{{ $ps }}" data-end="{{ $pe }}">
                            {{ \App\Models\ClassSchedule::formatTime($ps) }}–{{ \App\Models\ClassSchedule::formatTime($pe) }}
                        </button>
                    @endforeach
                </div>

                <label class="sch-label" for="room">Room <span class="opt">(optional)</span></label>
                <input type="text" name="room" id="room" class="sch-in" maxlength="60"
                       value="{{ old('room') }}" placeholder="e.g. Room 5, Science Lab">

                <button type="submit" id="schSubmit" class="sch-btn {{ $editing ? 'edit' : '' }}" @disabled($subjects->isEmpty())>
                    {{ $editing ? 'Save Changes' : 'Add to Schedule' }}
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

            @if($subjects->isNotEmpty())
                <div class="sch-todo">
                    @if($unscheduled->isEmpty())
                        <div class="sch-alldone">✓ Every Grade {{ $section->grade_level }} subject has at least one class.</div>
                    @else
                        <h4>Not scheduled yet ({{ $unscheduled->count() }})</h4>
                        @foreach($unscheduled as $sub)
                            <span class="sch-chip">{{ $sub->name }}</span>
                        @endforeach
                    @endif
                </div>
            @endif
        </div>

        {{-- ── Weekly grid ── --}}
        <div>
            <div class="sch-week">
                @foreach($days as $num => $label)
                    @php $dayItems = $schedules[$num] ?? collect(); @endphp
                    <div class="sch-day {{ now()->dayOfWeekIso === $num ? 'today' : '' }}">
                        <div class="sch-day-h">{{ $label }} <span>{{ $dayItems->count() ?: '' }}</span></div>
                        <div class="sch-day-b">
                            @forelse($dayItems as $p)
                                <div class="sch-item {{ $editing && $editing->id === $p->id ? 'editing' : '' }}" style="--c: {{ $colorOf($p->subject_id) }};"
                                     data-id="{{ $p->id }}"
                                     data-url="{{ route('admin.schedules.update', $p) }}"
                                     data-subject="{{ $p->subject_id }}"
                                     data-teacher="{{ $p->teacher_id }}"
                                     data-day="{{ $p->day_of_week }}"
                                     data-start="{{ substr((string) $p->start_time, 0, 5) }}"
                                     data-end="{{ substr((string) $p->end_time, 0, 5) }}"
                                     data-room="{{ $p->room }}">
                                    <div class="sch-item-time">{{ $p->time_range }}</div>
                                    <div class="sch-item-subj">{{ $p->subject?->name ?? 'Subject removed' }}</div>
                                    <div class="sch-item-meta">
                                        @if($p->teacher) {{ $p->teacher->name }} @else <span class="nt">No teacher yet</span> @endif
                                        @if($p->room) · {{ $p->room }} @endif
                                    </div>
                                    <div class="sch-item-act">
                                        <button type="button" class="sch-link edit js-edit">Edit</button>
                                        <form method="POST" action="{{ route('admin.schedules.destroy', $p) }}"
                                              onsubmit="return confirm('Remove this class from the schedule?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="sch-link del">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="sch-none">No classes</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
    @endif
</div>

<script>
(function () {
    var form = document.getElementById('schForm');
    if (!form) return;

    var $ = function (id) { return document.getElementById(id); };
    var method = $('schMethod'), editing = $('schEditing'), title = $('schFormTitle'), tag = $('schModeTag'),
        submit = $('schSubmit'), cancel = $('schCancel');

    // Bell schedule quick-fill
    document.querySelectorAll('.sch-preset').forEach(function (b) {
        b.addEventListener('click', function () {
            $('start_time').value = b.dataset.start;
            $('end_time').value   = b.dataset.end;
        });
    });

    function setTitle(text) { title.firstChild.nodeValue = text + ' '; }
    function clearHighlight() { document.querySelectorAll('.sch-item.editing').forEach(function (el) { el.classList.remove('editing'); }); }

    // Edit: load the class into the form and switch it to the update route (PUT)
    document.querySelectorAll('.js-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var card = btn.closest('.sch-item');
            form.action = card.dataset.url;
            method.disabled = false;
            editing.value = card.dataset.id;
            $('subject_id').value  = card.dataset.subject || '';
            $('teacher_id').value  = card.dataset.teacher || '';
            $('day_of_week').value = card.dataset.day;
            $('start_time').value  = card.dataset.start;
            $('end_time').value    = card.dataset.end;
            $('room').value        = card.dataset.room || '';
            setTitle('Edit Class'); tag.style.display = '';
            submit.textContent = 'Save Changes'; submit.classList.add('edit');
            cancel.style.display = '';
            clearHighlight(); card.classList.add('editing');
            $('schFormCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Cancel: back to "Add a Class"
    cancel.addEventListener('click', function () {
        form.action = form.dataset.storeUrl;
        method.disabled = true;
        editing.value = '';
        form.reset();
        $('subject_id').value = ''; $('teacher_id').value = ''; $('room').value = '';
        $('day_of_week').selectedIndex = 0;
        $('start_time').value = '07:30'; $('end_time').value = '08:20';
        setTitle('Add a Class'); tag.style.display = 'none';
        submit.textContent = 'Add to Schedule'; submit.classList.remove('edit');
        cancel.style.display = 'none';
        clearHighlight();
    });
})();
</script>
@endsection
