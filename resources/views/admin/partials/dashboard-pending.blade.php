{{-- Pending registrations list — used by the dashboard page and its live refresh --}}
@forelse($pendingUsers as $user)
    <div class="pending-row">
        <div class="pending-avatar" aria-hidden="true">
            {{-- preg_split: a double space ("Juan  Cruz") must not leave an empty first word --}}
            {{ collect(preg_split('/\s+/', trim($user->name), -1, PREG_SPLIT_NO_EMPTY))->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->join('') ?: '?' }}
        </div>
        <div class="pending-info">
            <div class="pending-name">{{ $user->name }}</div>
            <div class="pending-email">{{ $user->email ?? $user->contact_number }}</div>
        </div>
        <div class="pending-actions">
            <span class="role-pill {{ $user->role }}">{{ ucfirst($user->role) }}</span>
            <form method="POST" action="{{ route('admin.users.approve', $user) }}"
                  data-msg="Approve {{ $user->name }}? They will be able to log in." onsubmit="return confirm(this.dataset.msg);">
                @csrf @method('PATCH')
                <button type="submit" class="action-btn approve" aria-label="Approve {{ $user->name }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Approve
                </button>
            </form>
            <form method="POST" action="{{ route('admin.users.reject', $user) }}"
                  data-msg="Reject {{ $user->name }}? They will not be able to log in." onsubmit="return confirm(this.dataset.msg);">
                @csrf @method('PATCH')
                <button type="submit" class="action-btn reject" aria-label="Reject {{ $user->name }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reject
                </button>
            </form>
        </div>
    </div>
@empty
    <div class="panel-empty">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <b>All caught up</b>
        No accounts are waiting for approval.
    </div>
@endforelse
