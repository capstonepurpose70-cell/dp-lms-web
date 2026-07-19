@extends('layouts.admin')
@section('title', 'LRN Master List')

@section('content')
<style>
    .lrn-page { animation: lrn-fadein .3s ease both; }
    @keyframes lrn-fadein { from { opacity:0; transform:translateY(8px);} to { opacity:1; transform:none;} }

    .lrn-head { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:14px; }
    .lrn-title h1 { font-size:20px; font-weight:800; margin:0; }
    .lrn-title p  { margin:2px 0 0; font-size:12.5px; color:var(--muted,#6b7280); }

    .lrn-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:14px; }
    .lrn-stat  { background:var(--surface,#fff); border:1px solid var(--border,#e5e7eb); border-radius:12px; padding:12px 14px; }
    .lrn-stat b { font-size:20px; display:block; }
    .lrn-stat span { font-size:11.5px; color:var(--muted,#6b7280); }

    .lrn-grid { display:grid; grid-template-columns: 340px 1fr; gap:14px; align-items:start; }
    .lrn-card { background:var(--surface,#fff); border:1px solid var(--border,#e5e7eb); border-radius:14px; padding:16px; }
    .lrn-card h3 { margin:0 0 10px; font-size:14px; font-weight:800; }
    .lrn-card label { display:block; font-size:11.5px; font-weight:700; margin:8px 0 4px; color:var(--muted,#6b7280); }
    .lrn-card input[type="text"], .lrn-card textarea, .lrn-card select {
        width:100%; padding:9px 11px; border:1px solid var(--border,#e5e7eb); border-radius:9px;
        font-size:13px; background:var(--surface,#fff); color:inherit; box-sizing:border-box;
    }
    .lrn-card textarea { min-height:120px; font-family:monospace; font-size:12px; }
    .lrn-btn { margin-top:10px; width:100%; padding:10px; border:0; border-radius:9px; font-weight:800;
        font-size:13px; cursor:pointer; background:#1565C0; color:#fff; }
    .lrn-btn.gold { background:#B97B18; }
    .lrn-hint { font-size:11px; color:var(--muted,#6b7280); margin-top:6px; line-height:1.4; }

    .lrn-toolbar { display:flex; gap:8px; margin-bottom:10px; flex-wrap:wrap; }
    .lrn-toolbar input { flex:1; min-width:180px; padding:9px 11px; border:1px solid var(--border,#e5e7eb);
        border-radius:9px; font-size:13px; background:var(--surface,#fff); color:inherit; }
    .lrn-toolbar select { padding:9px 11px; border:1px solid var(--border,#e5e7eb); border-radius:9px;
        font-size:13px; background:var(--surface,#fff); color:inherit; }
    .lrn-toolbar button { padding:9px 14px; border:0; border-radius:9px; background:#1565C0; color:#fff;
        font-weight:700; font-size:13px; cursor:pointer; }

    table.lrn-table { width:100%; border-collapse:collapse; font-size:13px; }
    .lrn-table th { text-align:left; font-size:11px; letter-spacing:.4px; text-transform:uppercase;
        color:var(--muted,#6b7280); padding:8px 10px; border-bottom:1px solid var(--border,#e5e7eb); }
    .lrn-table td { padding:9px 10px; border-bottom:1px solid var(--border,#e5e7eb); }
    .lrn-badge { display:inline-block; padding:3px 9px; border-radius:999px; font-size:11px; font-weight:800; }
    .lrn-badge.avail   { background:rgba(22,163,74,.12); color:#16a34a; }
    .lrn-badge.claimed { background:rgba(37,99,235,.12); color:#2563eb; }
    .lrn-del { border:0; background:transparent; color:#dc2626; font-weight:700; font-size:12px; cursor:pointer; }
    .lrn-mono { font-family:monospace; font-weight:700; letter-spacing:.5px; }

    .lrn-alert { border-radius:10px; padding:10px 13px; font-size:13px; margin-bottom:12px; }
    .lrn-alert.ok  { background:rgba(22,163,74,.1); color:#15803d; border:1px solid rgba(22,163,74,.3); }
    .lrn-alert.err { background:rgba(220,38,38,.08); color:#b91c1c; border:1px solid rgba(220,38,38,.3); }

    @media (max-width: 900px) { .lrn-grid { grid-template-columns:1fr; } .lrn-stats { grid-template-columns:1fr; } }
</style>

<div class="lrn-page">
    <div class="lrn-head">
        <div class="lrn-title">
            <h1>LRN Master List</h1>
            <p>Opisyal na listahan ng Learner Reference Numbers — dito kukunin ang validation ng student registration.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="lrn-alert ok">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="lrn-alert err">{{ $errors->first() }}</div>
    @endif

    <div class="lrn-stats">
        <div class="lrn-stat"><b>{{ $total }}</b><span>Total LRNs</span></div>
        <div class="lrn-stat"><b>{{ $available }}</b><span>Available (hindi pa nagagamit)</span></div>
        <div class="lrn-stat"><b>{{ $claimed }}</b><span>Claimed (may account na)</span></div>
    </div>

    <div class="lrn-grid">
        {{-- LEFT: Add forms --}}
        <div>
            <div class="lrn-card" style="margin-bottom:14px;">
                <h3>➕ Add Single LRN <span style="font-weight:600;font-size:11px;color:var(--muted,#6b7280)">(para sa bagong student / transferee)</span></h3>
                <form method="POST" action="{{ route('admin.lrns.store') }}">
                    @csrf
                    <label>LRN (12 digits) *</label>
                    <input type="text" name="lrn" value="{{ old('lrn') }}" maxlength="12"
                           inputmode="numeric" pattern="\d{12}" placeholder="e.g. 123456789012" required>
                    <label>Student Name (optional)</label>
                    <input type="text" name="student_name" value="{{ old('student_name') }}" placeholder="Dela Cruz, Juan">
                    <label>Grade Level (optional)</label>
                    <input type="text" name="grade_level" value="{{ old('grade_level') }}" placeholder="e.g. 11">
                    <button class="lrn-btn" type="submit">Add LRN</button>
                </form>
                <p class="lrn-hint">Kapag naidagdag, pwede nang mag-register ang estudyante gamit ang LRN na ito.</p>
            </div>

            <div class="lrn-card">
                <h3>📋 Bulk Import</h3>
                <form method="POST" action="{{ route('admin.lrns.bulk') }}">
                    @csrf
                    <label>Isang LRN bawat linya</label>
                    <textarea name="bulk" placeholder="123456789012&#10;123456789013,Dela Cruz Juan&#10;123456789014,Santos Maria,11"></textarea>
                    <button class="lrn-btn gold" type="submit">Import All</button>
                </form>
                <p class="lrn-hint">
                    Formats: <b>LRN</b> lang · <b>LRN,Name</b> · <b>LRN,Name,Grade</b>.
                    Awtomatikong nilalaktawan ang duplicate at invalid.
                    (Pwedeng i-copy-paste diretso mula sa Excel column.)
                </p>
            </div>
        </div>

        {{-- RIGHT: List --}}
        <div class="lrn-card">
            <form method="GET" class="lrn-toolbar">
                <input type="text" name="q" value="{{ $q }}" placeholder="Search LRN or name…">
                <select name="status" onchange="this.form.submit()">
                    <option value="all"       {{ $status==='all' ? 'selected' : '' }}>All</option>
                    <option value="available" {{ $status==='available' ? 'selected' : '' }}>Available</option>
                    <option value="claimed"   {{ $status==='claimed' ? 'selected' : '' }}>Claimed</option>
                </select>
                <button type="submit">Search</button>
            </form>

            <table class="lrn-table">
                <thead>
                    <tr>
                        <th>LRN</th>
                        <th>Name (masterlist)</th>
                        <th>Grade</th>
                        <th>Status</th>
                        <th>Registered Account</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lrns as $row)
                        <tr>
                            <td class="lrn-mono">{{ $row->lrn }}</td>
                            <td>{{ $row->student_name ?? '—' }}</td>
                            <td>{{ $row->grade_level ?? '—' }}</td>
                            <td>
                                @if ($row->claimed_by)
                                    <span class="lrn-badge claimed">Claimed</span>
                                @else
                                    <span class="lrn-badge avail">Available</span>
                                @endif
                            </td>
                            <td>
                                @if ($row->claimedBy)
                                    {{ $row->claimedBy->name }}<br>
                                    <span style="font-size:11px;color:var(--muted,#6b7280)">{{ $row->claimedBy->email }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if (!$row->claimed_by)
                                    <form method="POST" action="{{ route('admin.lrns.destroy', $row->id) }}"
                                          onsubmit="return confirm('Remove LRN {{ $row->lrn }} from the list?');">
                                        @csrf @method('DELETE')
                                        <button class="lrn-del" type="submit">Remove</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:var(--muted,#6b7280);padding:22px;">
                            Walang LRN na tumutugma. Magdagdag gamit ang form sa kaliwa.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top:12px;">{{ $lrns->links() }}</div>
        </div>
    </div>
</div>
@endsection