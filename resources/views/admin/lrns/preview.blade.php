@extends('layouts.admin')
@section('title', 'Check the import')

@section('content')
@include('admin.lrns.partials.styles')

@php
    $c = $import['counts'];
    $groups = [
        'fix'    => ['title' => 'Need fixing',         'empty' => 'Every row can be read.'],
        'ready'  => ['title' => 'Ready to add',        'empty' => 'No new students in this file.'],
        'exists' => ['title' => 'Already on the list', 'empty' => 'None of these LRNs are on the list yet.'],
    ];
    $first = $c['fix'] ? 'fix' : ($c['ready'] ? 'ready' : 'exists');
    $byStatus = collect($import['rows'])->groupBy('status');
    $shown = 500;   // rows drawn per group; the counts above are always complete
    $readFrom = 'Read ' . collect($import['columns'])->map(fn ($where, $what) => "{$what} from {$where}")->implode(', ')
        . ($import['grade'] ? "; grade {$import['grade']} for rows without one." : '.');
@endphp

<div class="lm lmp" id="lmpRoot">
    <a href="{{ route('admin.lrns.index', [], false) }}" class="lm-back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6"/></svg>
        LRN Master List
    </a>
    <header class="lm-head">
        <div>
            <h1 class="lm-title">Check the import</h1>
            <p class="lm-sub">
                <b>{{ $import['file'] }}</b> — sheet “{{ $import['sheet'] }}”, header on row {{ $import['header'] }}.
                {{ $readFrom }}
            </p>
        </div>
    </header>

    <div class="lmp-tiles" role="tablist" aria-label="Rows by result">
        @foreach ($groups as $key => $g)
            <button type="button" role="tab" class="lm-card lmp-tile {{ $key }}" id="lmpTab-{{ $key }}" aria-controls="lmpPanel-{{ $key }}"
                    aria-selected="{{ $key === $first ? 'true' : 'false' }}" tabindex="{{ $key === $first ? '0' : '-1' }}" data-tab="{{ $key }}">
                <span class="lmp-tile-label"><i aria-hidden="true"></i>{{ $g['title'] }}</span>
                <b>{{ number_format($c[$key]) }}</b>
            </button>
        @endforeach
    </div>
    @if ($c['ignored'])
        <p class="lmp-note">{{ number_format($c['ignored']) }} {{ Str::plural('row', $c['ignored']) }} with no LRN and no student name (totals, notes, blank lines) {{ $c['ignored'] === 1 ? 'was' : 'were' }} left out.</p>
    @endif

    <section class="lm-card lm-list">
        @foreach ($groups as $key => $g)
            @php $rows = $byStatus->get($key, collect()); @endphp
            <div role="tabpanel" id="lmpPanel-{{ $key }}" aria-labelledby="lmpTab-{{ $key }}" @if ($key !== $first) hidden @endif>
                @if ($rows->isEmpty())
                    <div class="lm-empty"><b>{{ $g['empty'] }}</b></div>
                @else
                    <div class="lm-table-wrap">
                        <table class="lm-table">
                            <thead>
                                <tr>
                                    <th scope="col">Row</th>
                                    <th scope="col">LRN</th>
                                    <th scope="col">Student name</th>
                                    <th scope="col">Grade</th>
                                    @if ($key !== 'ready')<th scope="col">{{ $key === 'fix' ? 'What to fix' : 'Note' }}</th>@endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows->take($shown) as $r)
                                    <tr>
                                        <td data-label="Row" class="lmp-rowno">{{ $r['row'] }}</td>
                                        <td data-label="LRN" class="lm-lrn">{{ $r['lrn'] !== '' ? $r['lrn'] : '—' }}</td>
                                        <td data-label="Name" class="lm-name">{{ $r['name'] !== '' ? $r['name'] : '—' }}</td>
                                        <td data-label="Grade">@if ($r['grade'])<span class="lm-grade">{{ $r['grade'] }}</span>@else<span class="lm-muted">—</span>@endif</td>
                                        @if ($key !== 'ready')<td data-label="{{ $key === 'fix' ? 'Fix' : 'Note' }}" class="lmp-why">{{ $r['note'] }}</td>@endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($rows->count() > $shown)
                        <p class="lmp-more">Showing the first {{ $shown }} of {{ number_format($rows->count()) }} rows.@if ($key === 'fix') The download has all of them.@endif</p>
                    @endif
                @endif
            </div>
        @endforeach
    </section>

    <div class="lmp-bar">
        <p>
            @if ($c['ready'])
                <b>{{ number_format($c['ready']) }} {{ Str::plural('student', $c['ready']) }}</b> will be added.
                @if ($c['fix']) Rows that need fixing are left out — fix them and upload them again later. @endif
            @else
                <b>Nothing to add.</b> {{ $c['fix'] ? 'Fix the rows and upload the file again.' : 'Every LRN in this file is already on the list.' }}
            @endif
        </p>
        <div class="lmp-bar-actions">
            @if ($c['fix'])
                <a href="{{ route('admin.lrns.import.problems', $token) }}" class="lm-btn" download data-no-spa>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v11m0 0l-4-4m4 4l4-4M4 17v1a2 2 0 002 2h12a2 2 0 002-2v-1"/></svg>
                    Rows to fix (.xlsx)
                </a>
            @endif
            <form method="POST" action="{{ route('admin.lrns.import.cancel', $token) }}">
                @csrf @method('DELETE')
                <button type="submit" class="lm-btn">Cancel</button>
            </form>
            <form method="POST" action="{{ route('admin.lrns.import.confirm', $token) }}" id="lmpConfirm">
                @csrf
                <button type="submit" class="lm-btn primary" @disabled(!$c['ready'])>
                    Add {{ number_format($c['ready']) }} {{ Str::plural('student', $c['ready']) }}
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .lmp-tiles { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 10px; }
    .lmp-tile { display: flex; flex-direction: column; align-items: flex-start; gap: 6px; padding: 16px 18px; font: inherit; text-align: left; cursor: pointer;
                transition: border-color .15s ease, box-shadow .15s ease; }
    .lmp-tile:hover { border-color: var(--slate-300); }
    .lmp-tile[aria-selected="true"] { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,.14); }
    .lmp-tile:focus-visible { outline: 2px solid #2563EB; outline-offset: 2px; }
    .lmp-tile-label { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: var(--slate-600); }
    .lmp-tile-label i { width: 9px; height: 9px; border-radius: 50%; background: var(--slate-400); }
    .lmp-tile.ready i { background: #16A34A; }
    .lmp-tile.fix i { background: #D97706; }
    .lmp-tile b { font-size: 28px; font-weight: 800; color: var(--slate-900); letter-spacing: -.02em; line-height: 1.1; font-variant-numeric: tabular-nums; }
    .lmp-note { margin: 0 0 12px; font-size: 12.5px; color: var(--slate-500); }
    .lmp .lm-list { margin: 12px 0 14px; }
    .lmp-rowno { color: var(--slate-500) !important; font-variant-numeric: tabular-nums; width: 1%; white-space: nowrap; }
    .lmp-why { color: var(--slate-600); min-width: 240px; }
    #lmpPanel-fix .lmp-why { color: #B45309; font-weight: 600; }
    [data-theme="dark"] #lmpPanel-fix .lmp-why { color: #FBBF24; }
    .lmp-more { padding: 12px 16px; border-top: 1px solid var(--lm-line); font-size: 12.5px; color: var(--slate-500); }

    /* Actions stay in reach at the bottom of the screen */
    .lmp-bar { position: sticky; bottom: 16px; z-index: 5; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px 20px;
               padding: 12px 14px 12px 20px; border-radius: 14px; border: 1px solid var(--lm-line); background: var(--surface-card);
               box-shadow: 0 16px 40px -12px rgba(15,23,42,.28), 0 2px 6px rgba(15,23,42,.06); }
    .lmp-bar p { font-size: 13.5px; color: var(--slate-600); max-width: 60ch; }
    .lmp-bar b { color: var(--slate-900); }
    .lmp-bar-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .lmp-bar-actions form { margin: 0; }

    @media (max-width: 760px) {
        .lmp-tiles { gap: 8px; }
        .lmp-tile { padding: 12px; }
        .lmp-tile b { font-size: 22px; }
        .lmp-tile-label { font-size: 12px; }
        .lmp-bar { bottom: 8px; }
        .lmp-bar-actions, .lmp-bar-actions form, .lmp-bar-actions .lm-btn { flex: 1 1 auto; }
        .lmp-bar-actions form .lm-btn { width: 100%; }
    }
</style>

<script>
(function () {
    var root = document.getElementById('lmpRoot');
    if (!root) return;
    var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));

    function select(tab) {
        tabs.forEach(function (t) {
            var on = t === tab;
            t.setAttribute('aria-selected', on ? 'true' : 'false');
            t.tabIndex = on ? 0 : -1;
            document.getElementById(t.getAttribute('aria-controls')).hidden = !on;
        });
    }
    tabs.forEach(function (t, i) {
        t.addEventListener('click', function () { select(t); });
        t.addEventListener('keydown', function (e) {
            var step = e.key === 'ArrowRight' ? 1 : (e.key === 'ArrowLeft' ? -1 : 0);
            if (!step) return;
            var next = tabs[(i + step + tabs.length) % tabs.length];
            next.focus();
            select(next);
        });
    });

    // One click only: the second would find the preview already used
    var confirm = document.getElementById('lmpConfirm');
    if (confirm) confirm.addEventListener('submit', function () {
        var b = confirm.querySelector('button');
        b.disabled = true;
        b.textContent = 'Adding students…';
    });
})();
</script>
@endsection
