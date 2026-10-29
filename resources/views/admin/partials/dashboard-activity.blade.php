{{-- Recent audit log entries — used by the dashboard page and its live refresh --}}
@forelse($recentLogs as $log)
    @php
        $a = mb_strtolower((string) $log->action);
        $tone = str_contains($a, 'fail') || str_contains($a, 'blocked') || str_contains($a, 'reject') ? 'bad'
            : (str_contains($a, 'approve') || str_contains($a, 'activated') ? 'good' : 'neutral');
    @endphp
    <div class="log-row">
        <span class="log-dot {{ $tone }}" aria-hidden="true"></span>
        <div class="log-body">
            <div class="log-action">{{ $log->action }}</div>
            <div class="log-meta">{{ $log->user_name ?: 'System' }}@if($log->module)<span class="log-module">{{ $log->module }}</span>@endif</div>
        </div>
        <time class="log-time" datetime="{{ $log->created_at?->toIso8601String() }}">{{ $log->created_at?->diffForHumans(null, true) }}</time>
    </div>
@empty
    <div class="panel-empty">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
        <b>No activity yet</b>
        Sign-ins, approvals and changes will show here.
    </div>
@endforelse
