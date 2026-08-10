@extends('layouts.admin')
@section('title', 'Sections & Advisers')

@section('content')
<style>
    .sec-page { animation: sec-fade .3s ease both; }
    @keyframes sec-fade { from { opacity:0; transform:translateY(8px);} to { opacity:1; transform:none;} }

    .sec-head h1 { font-size:20px; font-weight:800; margin:0 0 2px; }
    .sec-head p  { margin:0 0 16px; font-size:12.5px; color:var(--muted,#6b7280); }

    .sec-grid { display:grid; grid-template-columns: 320px 1fr; gap:16px; align-items:start; }

    .sec-card { background:var(--surface,#fff); border:1px solid var(--border,#e5e7eb);
                border-radius:14px; padding:16px; }
    .sec-card h3 { margin:0 0 12px; font-size:14px; font-weight:800; }
    .sec-card label { display:block; font-size:11.5px; font-weight:700; margin:9px 0 4px; color:var(--muted,#6b7280); }
    .sec-card input, .sec-card select {
        width:100%; padding:9px 11px; border:1px solid var(--border,#e5e7eb); border-radius:9px;
        font-size:13px; background:var(--surface,#fff); color:inherit; box-sizing:border-box;
    }
    .sec-btn { margin-top:12px; width:100%; padding:10px; border:0; border-radius:9px;
               font-weight:800; font-size:13px; cursor:pointer; background:#1565C0; color:#fff; }

    table.sec { width:100%; border-collapse:collapse; font-size:13px; }
    .sec th { text-align:left; font-size:10.5px; letter-spacing:.4px; text-transform:uppercase;
              color:var(--muted,#9ca3af); padding:9px 12px; border-bottom:1px solid var(--border,#e5e7eb); }
    .sec td { padding:10px 12px; border-bottom:1px solid var(--border,#e5e7eb); vertical-align:middle; }
    .sec tr:last-child td { border-bottom:0; }

    .sec-inline { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
    .sec-inline select { padding:6px 9px; border:1px solid var(--border,#e5e7eb); border-radius:7px;
                         font-size:12px; background:var(--surface,#fff); color:inherit; max-width:190px; }
    .sec-save { padding:6px 11px; border:0; border-radius:7px; background:#15803D; color:#fff;
                font-size:11.5px; font-weight:700; cursor:pointer; }
    .sec-del { border:0; background:transparent; color:#dc2626; font-size:11.5px; font-weight:700; cursor:pointer; }
    .adviser-none { color:#b45309; font-size:11.5px; font-weight:700; }
    .adviser-set  { color:#065f46; font-size:12px; font-weight:700; }

    .sec-alert { border-radius:10px; padding:10px 13px; font-size:13px; margin-bottom:12px; }
    .sec-alert.ok  { background:rgba(22,163,74,.1); color:#15803d; border:1px solid rgba(22,163,74,.3); }
    .sec-alert.err { background:rgba(220,38,38,.08); color:#b91c1c; border:1px solid rgba(220,38,38,.3); }

    @media (max-width: 900px) { .sec-grid { grid-template-columns:1fr; } }
</style>

<div class="sec-page">
    <div class="sec-head">
        <h1>Sections &amp; Advisers</h1>
        <p>Create sections and assign an adviser (teacher). The adviser reviews enrollment for their section.</p>
    </div>

    @if (session('success'))
        <div class="sec-alert ok">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="sec-alert err">{{ $errors->first() }}</div>
    @endif

    <div class="sec-grid">
        {{-- Create --}}
        <div class="sec-card">
            <h3>➕ New Section</h3>
            <form method="POST" action="{{ route('admin.sections.store') }}">
                @csrf
                <label>Section Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Rizal, Section A" required>

                <label>Grade Level *</label>
                <select name="grade_level" required>
                    <option value="">— Select —</option>
                    @foreach ($grades as $g)
                        <option value="{{ $g }}" {{ old('grade_level') == $g ? 'selected' : '' }}>Grade {{ $g }}</option>
                    @endforeach
                </select>

                <label>Adviser (optional)</label>
                <select name="adviser_id">
                    <option value="">— No adviser yet —</option>
                    @foreach ($teachers as $t)
                        <option value="{{ $t->id }}" {{ old('adviser_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>

                <button class="sec-btn" type="submit">Create Section</button>
            </form>
        </div>

        {{-- List + assign --}}
        <div class="sec-card">
            <h3>All Sections</h3>
            <table class="sec">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>Grade</th>
                        <th>Adviser</th>
                        <th>Assign / Update</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sections as $s)
                        <tr>
                            <td><b>{{ $s->name }}</b></td>
                            <td>Grade {{ $s->grade_level }}</td>
                            <td>
                                @if ($s->adviser)
                                    <span class="adviser-set">{{ $s->adviser->name }}</span>
                                @else
                                    <span class="adviser-none">No adviser</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.sections.update', $s->id) }}" class="sec-inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="name" value="{{ $s->name }}">
                                    <input type="hidden" name="grade_level" value="{{ $s->grade_level }}">
                                    <input type="hidden" name="is_active" value="1">
                                    <select name="adviser_id">
                                        <option value="">— No adviser —</option>
                                        @foreach ($teachers as $t)
                                            <option value="{{ $t->id }}" {{ $s->adviser_id == $t->id ? 'selected' : '' }}>
                                                {{ $t->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button class="sec-save" type="submit">Save</button>
                                </form>
                            </td>
                            <td style="text-align:right;">
                                <form method="POST" action="{{ route('admin.sections.destroy', $s->id) }}"
                                      onsubmit="return confirm('Delete section {{ $s->name }}?');">
                                    @csrf @method('DELETE')
                                    <button class="sec-del" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--muted,#6b7280);padding:22px;">
                            No sections yet. Create one on the left.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection