@extends('layouts.admin')
@section('title', 'Class Schedules')

@section('content')
<style>
    .sch-page { animation: sch-fade .3s ease both; }
    @keyframes sch-fade { from { opacity:0; transform:translateY(8px);} to { opacity:1; transform:none;} }

    .sch-head h1 { font-size:20px; font-weight:800; margin:0 0 2px; }
    .sch-head p  { margin:0 0 16px; font-size:12.5px; color:var(--muted,#6b7280); }

    .sch-grid { display:grid; grid-template-columns: 330px 1fr; gap:16px; align-items:start; }

    .sch-card { background:var(--surface,#fff); border:1px solid var(--border,#e5e7eb);
                border-radius:14px; padding:16px; }
    .sch-card h3 { margin:0 0 12px; font-size:14px; font-weight:800; }
    .sch-card label { display:block; font-size:11.5px; font-weight:700; margin:9px 0 4px;
                      color:var(--muted,#6b7280); }
    .sch-card input, .sch-card select {
        width:100%; padding:9px 11px; border:1px solid var(--border,#e5e7eb); border-radius:9px;
        font-size:13px; background:var(--surface,#fff); color:inherit; box-sizing:border-box;
    }
    .sch-row2 { display:grid; grid-template-columns:1fr 1fr; gap:9px; }
    .sch-btn  { margin-top:13px; width:100%; padding:10px; border:0; border-radius:9px;
                font-weight:800; font-size:13px; cursor:pointer; background:#1565C0; color:#fff; }
    .sch-btn:disabled { background:#94a3b8; cursor:not-allowed; }

    .sch-hint { font-size:11px; color:var(--muted,#9ca3af); margin-top:6px; line-height:1.5; }

    /* ─── Weekly grid ─── */
    .sch-week { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; }
    .sch-day { background:var(--surface,#fff); border:1px solid var(--border,#e5e7eb);
               border-radius:12px; overflow:hidden; }
    .sch-day-h { padding:9px 12px; border-bottom:1px solid var(--border,#e5e7eb);
                 font-size:11px; font-weight:800; letter-spacing:.4px; text-transform:uppercase; }
    .sch-day-b { padding:9px; display:flex; flex-direction:column; gap:7px; }

    .sch-item { border:1px solid var(--border,#e5e7eb); border-left:3px solid #1565C0;
                border-radius:8px; padding:8px 9px; }
    .sch-item-time { font-size:11px; font-weight:800; color:#1565C0; }
    .sch-item-subj { font-size:12.5px; font-weight:700; margin-top:2px; }
    .sch-item-meta { font-size:11px; color:var(--muted,#6b7280); margin-top:3px; }
    .sch-item-del  { border:0; background:transparent; color:#dc2626; font-size:11px;
                     font-weight:700; cursor:pointer; padding:3px 0 0; }
    .sch-none { font-size:11.5px; color:var(--muted,#9ca3af); text-align:center; padding:14px 4px; }

    .sch-alert { border-radius:10px; padding:10px 13px; font-size:13px; margin-bottom:12px; }
    .sch-ok  { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
    .sch-err { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
    .sch-err ul { margin:6px 0 0; padding-left:18px; }

    .sch-empty-state { text-align:center; padding:40px 16px; color:var(--muted,#6b7280); font-size:13px; }

    @media (max-width:1100px) { .sch-grid { grid-template-columns:1fr; } .sch-week { grid-template-columns:repeat(2,1fr);} }
    @media (max-width:640px)  { .sch-week { grid-template-columns:1fr; } }
</style>

@php
    $days = \App\Models\ClassSchedule::DAYS;
@endphp

<div class="sch-page">

    <div class="sch-head">
        <h1>Class Schedules</h1>
        <p>Set the weekly timetable for each section. Students see this once they are enrolled.</p>
    </div>

    @if(session('success'))
        <div class="sch-alert sch-ok">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="sch-alert sch-err">
            <strong>Could not save this schedule:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Section picker --}}
    <div class="sch-card" style="margin-bottom:16px;">
        <form method="GET" action="{{ route('admin.schedules.index') }}"
              style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
            <div style="flex:1; min-width:240px;">
                <label for="sectionPicker">Section</label>
                <select name="section_id" id="sectionPicker" onchange="this.form.submit()">
                    @forelse($sections as $s)
                        <option value="{{ $s->id }}" @selected($section && $section->id === $s->id)>
                            Grade {{ $s->grade_level }} – {{ $s->name }}
                        </option>
                    @empty
                        <option value="">No active sections</option>
                    @endforelse
                </select>
            </div>
        </form>
    </div>

    @if(!$section)
        <div class="sch-card">
            <div class="sch-empty-state">
                Create a section first under <strong>Sections &amp; Advisers</strong>.
            </div>
        </div>
    @else
    <div class="sch-grid">

        {{-- ── Add form ── --}}
        <div class="sch-card">
            <h3>Add a Class</h3>
            <form method="POST" action="{{ route('admin.schedules.store') }}">
                @csrf
                <input type="hidden" name="section_id" value="{{ $section->id }}">

                <label for="subject_id">Subject</label>
                <select name="subject_id" id="subject_id" required>
                    <option value="">— Select subject —</option>
                    @forelse($subjects as $sub)
                        <option value="{{ $sub->id }}" @selected(old('subject_id') == $sub->id)>
                            {{ $sub->name }}
                        </option>
                    @empty
                        <option value="" disabled>No Grade {{ $section->grade_level }} subjects</option>
                    @endforelse
                </select>

                <label for="teacher_id">Teacher</label>
                <select name="teacher_id" id="teacher_id">
                    <option value="">— To be assigned —</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" @selected(old('teacher_id') == $t->id)>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>

                <label for="day_of_week">Day</label>
                <select name="day_of_week" id="day_of_week" required>
                    @foreach($days as $num => $label)
                        <option value="{{ $num }}" @selected(old('day_of_week') == $num)>{{ $label }}</option>
                    @endforeach
                </select>

                <div class="sch-row2">
                    <div>
                        <label for="start_time">Start</label>
                        <input type="time" name="start_time" id="start_time"
                               value="{{ old('start_time', '08:00') }}" required>
                    </div>
                    <div>
                        <label for="end_time">End</label>
                        <input type="time" name="end_time" id="end_time"
                               value="{{ old('end_time', '09:00') }}" required>
                    </div>
                </div>

                <label for="room">Room</label>
                <input type="text" name="room" id="room" maxlength="60"
                       value="{{ old('room') }}" placeholder="e.g. 101, Science Lab">

                <button type="submit" class="sch-btn" @disabled($subjects->isEmpty())>
                    Add to Schedule
                </button>

                <p class="sch-hint">
                    The system blocks overlapping classes for the same section,
                    the same teacher, and the same room.
                </p>
            </form>
        </div>

        {{-- ── Weekly grid ── --}}
        <div>
            <div class="sch-week">
                @foreach($days as $num => $label)
                    @php $periods = $schedules[$num] ?? collect(); @endphp
                    <div class="sch-day">
                        <div class="sch-day-h">{{ $label }}</div>
                        <div class="sch-day-b">
                            @forelse($periods as $p)
                                <div class="sch-item">
                                    <div class="sch-item-time">{{ $p->time_range }}</div>
                                    <div class="sch-item-subj">{{ $p->subject?->name ?? '—' }}</div>
                                    <div class="sch-item-meta">
                                        {{ $p->teacher?->name ?? 'No teacher' }}
                                        @if($p->room) · Room {{ $p->room }} @endif
                                    </div>
                                    <form method="POST"
                                          action="{{ route('admin.schedules.destroy', $p) }}"
                                          onsubmit="return confirm('Remove this class from the schedule?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="sch-item-del">Remove</button>
                                    </form>
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
@endsection