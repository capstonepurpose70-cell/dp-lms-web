{{--
    Section balance table for one grade level.
    Expects: $stats (SectionBalanceService::forGrade), optional $suggestedId, $band (student's band key),
             $mineIds (array: sections the viewer can place into — others are shown for reference).
    Self-contained styles with fallbacks so it works in both the admin and the teacher layouts.
--}}
@php
    $bands    = \App\Services\SectionBalanceService::BANDS;
    $colors   = ['o' => '#16a34a', 'vs' => '#2563eb', 's' => '#0891b2', 'fs' => '#d97706', 'dn' => '#dc2626'];
    $mineIds  = $mineIds ?? null;
    $band     = $band ?? null;
    $maxTotal = max(1, (int) $stats->max('total'));
@endphp
@once
<style>
    .sb-wrap { --sb-text: var(--slate-900, var(--text-1, #0f172a)); --sb-muted: var(--slate-500, var(--text-2, #64748b));
               --sb-line: var(--border-default, var(--border, #e2e8f0)); --sb-soft: var(--slate-50, var(--surface-2, #f8fafc)); }
    .sb-table { width:100%; border-collapse:collapse; font-size:13px; }
    .sb-table th { text-align:left; font-size:10.5px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--sb-muted);
        padding:8px 10px; border-bottom:1px solid var(--sb-line); background:var(--sb-soft); white-space:nowrap; }
    .sb-table td { padding:9px 10px; border-bottom:1px solid var(--sb-line); color:var(--sb-text); vertical-align:middle; }
    .sb-table tr:last-child td { border-bottom:0; }
    .sb-table tr.sb-best td { background:rgba(22,163,74,.07); }
    .sb-table tr.sb-other td { opacity:.7; }
    .sb-name { font-weight:700; }
    .sb-sub { font-size:11.5px; color:var(--sb-muted); }
    .sb-pill { display:inline-block; margin-left:6px; padding:1px 7px; border-radius:999px; font-size:10.5px; font-weight:800; background:#16a34a; color:#fff; vertical-align:middle; }
    .sb-size { display:flex; align-items:center; gap:8px; min-width:120px; }
    .sb-size-bar { flex:1; height:6px; border-radius:999px; background:var(--sb-line); overflow:hidden; }
    .sb-size-bar i { display:block; height:100%; background:#64748b; border-radius:999px; }
    .sb-bands { display:flex; height:16px; border-radius:6px; overflow:hidden; min-width:140px; background:var(--sb-line); }
    .sb-bands span { display:block; height:100%; }
    .sb-band-n { display:flex; gap:8px; flex-wrap:wrap; margin-top:3px; font-size:11px; color:var(--sb-muted); }
    .sb-band-n b { font-weight:800; }
    .sb-band-n .hl { outline:2px solid currentColor; outline-offset:1px; border-radius:4px; padding:0 3px; }
    .sb-legend { display:flex; gap:12px; flex-wrap:wrap; font-size:11.5px; color:var(--sb-muted); margin-top:8px; }
    .sb-legend i { display:inline-block; width:10px; height:10px; border-radius:3px; margin-right:4px; vertical-align:-1px; }
    .sb-scroll { overflow-x:auto; border:1px solid var(--sb-line); border-radius:10px; }
</style>
@endonce

<div class="sb-wrap">
    @if($stats->isEmpty())
        <div class="sb-sub">No active sections for this grade yet.</div>
    @else
        <div class="sb-scroll">
            <table class="sb-table">
                <thead>
                    <tr><th>Section</th><th>Students</th><th>Boys / Girls</th><th>Avg. grade</th><th>Performance mix</th></tr>
                </thead>
                <tbody>
                    @foreach($stats as $st)
                        @php
                            $sec   = $st['section'];
                            $best  = isset($suggestedId) && (int) $suggestedId === (int) $sec->id;
                            $other = is_array($mineIds) && !in_array($sec->id, $mineIds);
                            $sum   = array_sum($st['bands']);
                        @endphp
                        <tr class="{{ $best ? 'sb-best' : '' }} {{ $other ? 'sb-other' : '' }}">
                            <td>
                                <span class="sb-name">{{ $sec->name }}</span>@if($best)<span class="sb-pill">Suggested</span>@endif
                                <div class="sb-sub">{{ $sec->adviser ? 'Adviser: ' . $sec->adviser->name : 'No adviser yet' }}{{ $other ? ' · not your section' : '' }}</div>
                            </td>
                            <td>
                                <div class="sb-size"><b>{{ $st['total'] }}</b><div class="sb-size-bar"><i style="width:{{ round($st['total'] / $maxTotal * 100) }}%;"></i></div></div>
                            </td>
                            <td>{{ $st['male'] }} / {{ $st['female'] }}</td>
                            <td>{!! $st['avg'] !== null ? '<b>' . e(number_format($st['avg'], 1)) . '</b>' : '<span class="sb-sub">—</span>' !!}</td>
                            <td>
                                @if($sum)
                                    <div class="sb-bands" title="{{ $st['withGa'] }} of {{ $st['total'] }} students have a general average on file">
                                        @foreach($bands as $k => $b)
                                            @if($st['bands'][$k])<span style="width:{{ $st['bands'][$k] / $sum * 100 }}%; background:{{ $colors[$k] }};"></span>@endif
                                        @endforeach
                                    </div>
                                    <div class="sb-band-n">
                                        @foreach($bands as $k => $b)
                                            <span class="{{ $band === $k ? 'hl' : '' }}" style="color:{{ $colors[$k] }};"><b>{{ $st['bands'][$k] }}</b> {{ strtoupper($k) }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="sb-sub">No grades on file yet</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="sb-legend">
            @foreach($bands as $k => $b)
                <span><i style="background:{{ $colors[$k] }};"></i>{{ strtoupper($k) }} = {{ $b[0] }}</span>
            @endforeach
        </div>
    @endif
</div>
