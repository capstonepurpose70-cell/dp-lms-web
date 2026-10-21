{{-- View / Approve / Reject buttons for one user row. Expects $u (User). --}}
<div class="um-row-actions">
    <a href="{{ route('admin.users.show', $u) }}" class="um-icon-btn um-btn-view" title="View profile" aria-label="View {{ $u->name }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
    </a>
    @if($u->status === 'pending')
        <button type="button" class="um-icon-btn um-btn-approve" title="Approve" aria-label="Approve {{ $u->name }}"
                data-action="approve" data-url="{{ route('admin.users.approve', $u) }}" data-name="{{ $u->name }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </button>
        <button type="button" class="um-icon-btn um-btn-reject" title="Reject" aria-label="Reject {{ $u->name }}"
                data-action="reject" data-url="{{ route('admin.users.reject', $u) }}" data-name="{{ $u->name }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    @endif
</div>
