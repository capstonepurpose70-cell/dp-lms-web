@extends('layouts.admin')
@section('title', 'Face Verification')

@section('content')
<div style="max-width:1100px;margin:0 auto;padding:24px 8px;">

    <div style="margin-bottom:20px;">
        <h1 style="font-size:20px;font-weight:800;color:var(--slate-900,#0f172a);margin:0;">Face Verification</h1>
        <p style="font-size:13px;color:var(--slate-500,#64748b);margin:4px 0 0;">
            Faces used by the Raspberry Pi gate camera. Register a face at the kiosk with a one-time QR (saved right away),
            or review photos students uploaded themselves. Photos must show the face down to the neck only — flag anything inappropriate.
        </p>
    </div>

    {{-- ── REGISTER AT THE KIOSK (one-time QR) ─────────────────── --}}
    <div style="background:var(--white,#fff);border:1px solid var(--border-default,#e2e8f0);border-radius:16px;padding:18px;margin-bottom:22px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;">
            <div>
                <h2 style="font-size:15px;font-weight:800;color:var(--slate-900,#0f172a);margin:0;">Register a face at the kiosk</h2>
                <p style="font-size:12.5px;color:var(--slate-500,#64748b);margin:3px 0 0;">Choose a section, then show the student's QR to the Raspberry Pi camera. Advisers can also do this from their own portal.</p>
            </div>
            <select id="fkSection" style="height:40px;padding:0 12px;border:1.5px solid var(--border-default,#e2e8f0);border-radius:10px;font-size:13.5px;background:var(--white,#fff);color:var(--slate-900,#0f172a);min-width:240px;">
                <option value="">— Choose a section —</option>
                @foreach ($sections as $s)
                    <option value="{{ $s->id }}">Grade {{ $s->grade_level }} – {{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div id="fkList" style="margin-top:12px;font-size:13px;color:var(--slate-500,#64748b);">Students appear here after you choose a section.</div>
    </div>

    {{-- ── PENDING ─────────────────────────────────────────────── --}}
    <h2 style="font-size:14px;font-weight:700;color:#334155;margin:0 0 12px;">
        Pending ({{ $pending->count() }})
    </h2>

    @forelse ($pending as $r)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;
                    padding:18px;margin-bottom:16px;box-shadow:0 1px 8px rgba(0,0,0,.04);">
            <div style="display:flex;justify-content:space-between;align-items:center;
                        flex-wrap:wrap;gap:8px;margin-bottom:12px;">
                <div>
                    <p style="font-weight:700;color:#0f172a;font-size:15px;margin:0;">
                        {{ $r->user->name }}
                    </p>
                    <p style="font-size:12px;color:#64748b;margin:2px 0 0;">
                        {{ $r->user->email }}
                        @if ($r->user->employee_id || $r->user->student_id)
                            · ID: {{ $r->user->student_id ?? $r->user->employee_id }}
                        @endif
                        · {{ $r->images_count }} images · {{ $r->created_at->diffForHumans() }}
                    </p>
                    @if ($r->user->face_warnings > 0)
                        <span style="display:inline-block;margin-top:6px;background:#fff7ed;color:#c2410c;
                                     border:1px solid #fed7aa;font-size:11px;font-weight:600;
                                     padding:2px 8px;border-radius:999px;">
                            ⚠️ {{ $r->user->face_warnings }}/3 warnings
                        </span>
                    @endif
                </div>
            </div>

            {{-- image grid --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(80px,1fr));
                        gap:8px;margin-bottom:14px;">
                @forelse ($r->image_urls as $url)
                    <img src="{{ $url }}" alt="face"
                         style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:10px;
                                border:1px solid #e2e8f0;">
                @empty
                    <p style="font-size:12px;color:#94a3b8;">No photos found.</p>
                @endforelse
            </div>

            {{-- actions --}}
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <form method="POST" action="{{ route('admin.face.approve', $r->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                        style="background:#16a34a;color:#fff;border:none;font-weight:600;font-size:13px;
                               padding:9px 18px;border-radius:10px;cursor:pointer;">
                        ✓ Approve
                    </button>
                </form>

                <button type="button" onclick="openReject({{ $r->id }}, false)"
                    style="background:#fff;color:#475569;border:1px solid #cbd5e1;font-weight:600;
                           font-size:13px;padding:9px 18px;border-radius:10px;cursor:pointer;">
                    ✕ Reject (unclear)
                </button>

                <button type="button" onclick="openReject({{ $r->id }}, true)"
                    style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;font-weight:600;
                           font-size:13px;padding:9px 18px;border-radius:10px;cursor:pointer;">
                    ⚠️ Flag inappropriate (warning)
                </button>
            </div>
        </div>
    @empty
        <div style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:14px;
                    padding:28px;text-align:center;color:#94a3b8;font-size:13px;margin-bottom:24px;">
            No face registrations waiting for review. 🎉
        </div>
    @endforelse

    {{-- ── REVIEWED ────────────────────────────────────────────── --}}
    @if ($reviewed->count())
        <h2 style="font-size:14px;font-weight:700;color:#334155;margin:28px 0 12px;">
            Recently reviewed
        </h2>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">
            @foreach ($reviewed as $r)
                <div style="display:flex;justify-content:space-between;align-items:center;
                            padding:12px 16px;border-bottom:1px solid #f1f5f9;font-size:13px;">
                    <span style="color:#0f172a;font-weight:600;">{{ $r->user->name }}</span>
                    <span>
                        @if ($r->status === 'approved')
                            <span style="color:#16a34a;font-weight:600;">✓ Approved</span>
                        @else
                            <span style="color:#dc2626;font-weight:600;">✕ Rejected</span>
                            @if ($r->inappropriate)
                                <span style="color:#c2410c;">· flagged</span>
                            @endif
                        @endif
                        <span style="color:#94a3b8;margin-left:8px;">
                            {{ optional($r->reviewed_at)->diffForHumans() }}
                        </span>
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- ── REJECT MODAL ───────────────────────────────────────────── --}}
<div id="rejectModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
     z-index:9999;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:16px;max-width:420px;width:100%;padding:22px;">
        <h3 id="rjTitle" style="font-size:16px;font-weight:700;color:#0f172a;margin:0 0 6px;">Reject face</h3>
        <p id="rjNote" style="font-size:12px;color:#64748b;margin:0 0 14px;"></p>
        <form id="rejectForm" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="inappropriate" id="rjFlag" value="0">
            <label style="font-size:12px;font-weight:600;color:#334155;">Reason (optional)</label>
            <textarea name="reason" rows="2"
                style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:8px 10px;
                       font-size:13px;margin:6px 0 16px;resize:none;"
                placeholder="e.g. Blurry photo / not just the face"></textarea>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="closeReject()"
                    style="background:#f1f5f9;color:#475569;border:none;font-weight:600;font-size:13px;
                           padding:9px 16px;border-radius:10px;cursor:pointer;">Cancel</button>
                <button type="submit" id="rjSubmit"
                    style="background:#dc2626;color:#fff;border:none;font-weight:600;font-size:13px;
                           padding:9px 18px;border-radius:10px;cursor:pointer;">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

<script>
function openReject(id, inappropriate) {
    var form = document.getElementById('rejectForm');
    form.action = '{{ url('admin/face') }}/' + id + '/reject';
    document.getElementById('rjFlag').value = inappropriate ? '1' : '0';
    document.getElementById('rjTitle').textContent = inappropriate ? '⚠️ Flag inappropriate' : 'Reject face';
    document.getElementById('rjNote').textContent  = inappropriate
        ? 'This adds a WARNING. On the 3rd warning the student\'s account is banned.'
        : 'Reject only (no warning). The student can take new photos.';
    document.getElementById('rjSubmit').textContent = inappropriate ? 'Confirm & Warn' : 'Confirm Reject';
    document.getElementById('rejectModal').style.display = 'flex';
}
function closeReject() { document.getElementById('rejectModal').style.display = 'none'; }

// Kiosk QR: load the chosen section's students
(function () {
    var sel = document.getElementById('fkSection'), list = document.getElementById('fkList');
    if (!sel) return;
    var URL_STUDENTS = @json(route('admin.face.kiosk.students'));
    var URL_ISSUE    = @json(url('admin/face/kiosk'));
    var CHIP = {
        approved: ['#16a34a', '#fff', 'Registered'],
        pending:  ['#f59e0b', '#3f2d00', 'Waiting for review'],
        rejected: ['#dc2626', '#fff', 'Rejected']
    };
    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }

    sel.addEventListener('change', function () {
        if (!sel.value) { list.textContent = 'Students appear here after you choose a section.'; return; }
        list.textContent = 'Loading…';
        fetch(URL_STUDENTS + '?section_id=' + encodeURIComponent(sel.value), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (rows) {
                if (!Array.isArray(rows) || !rows.length) { list.textContent = 'No approved students in this section yet.'; return; }
                list.innerHTML = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:8px;">' + rows.map(function (s) {
                    var c = CHIP[s.face_status] || ['#e2e8f0', '#475569', 'No face yet'];
                    var btn = s.blocked
                        ? '<span style="font-size:12px;color:#b91c1c;font-weight:700;">Blocked (3 warnings)</span>'
                        : '<button type="button" data-kiosk-issue="' + esc(URL_ISSUE + '/' + s.id) + '" data-name="' + esc(s.name) + '" style="height:32px;padding:0 12px;border-radius:9px;border:1px solid #1d4ed8;background:#1d4ed8;color:#fff;font-weight:700;font-size:12.5px;cursor:pointer;">' + (s.face_status === 'approved' ? 'Re-register' : 'Show QR') + '</button>';
                    return '<div style="display:flex;justify-content:space-between;align-items:center;gap:8px;border:1px solid var(--border-default,#e2e8f0);border-radius:12px;padding:9px 12px;">'
                        + '<div style="min-width:0;"><div style="font-weight:700;color:var(--slate-900,#0f172a);font-size:13px;">' + esc(s.name) + '</div>'
                        + '<span style="display:inline-block;margin-top:3px;padding:1px 8px;border-radius:999px;font-size:11px;font-weight:800;background:' + c[0] + ';color:' + c[1] + ';">' + c[2] + '</span></div>'
                        + btn + '</div>';
                }).join('') + '</div>';
            })
            .catch(function () { list.textContent = 'Could not load students. Please try again.'; });
    });
})();
</script>

@include('partials.face-kiosk-modal')
@endsection