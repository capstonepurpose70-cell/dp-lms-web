@extends('layouts.teacher')
@section('title', 'Announcements')

@section('content')
{{-- Plain CSS + layout tokens (the Tailwind build is stale: sm:col-span-2 / py-14 / w-14 were missing). --}}
<style>
    .ta-wrap { max-width: 880px; margin: 0 auto; }
    .ta-head { margin-bottom: 20px; }
    .ta-title { font-size: 24px; font-weight: 800; color: var(--text-1); letter-spacing: -.01em; }
    .ta-sub { font-size: 13px; color: var(--text-3); margin-top: 4px; }

    .ta-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); }
    .ta-form { padding: 20px; margin-bottom: 20px; }
    .ta-form-title { font-size: 15px; font-weight: 700; color: var(--text-1); margin-bottom: 14px; }

    .ta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .ta-span { grid-column: 1 / -1; }
    .ta-field label { display: block; font-size: 12.5px; font-weight: 700; color: var(--text-2); margin-bottom: 6px; }
    .ta-input {
        width: 100%; min-height: 42px; border: 1.5px solid var(--border); border-radius: 10px;
        padding: 9px 12px; font-size: 14px; color: var(--text-1); background: #fbfcff;
        font-family: inherit; outline: none; transition: border-color .15s, box-shadow .15s;
    }
    .ta-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-glow); background: #fff; }
    .ta-input.is-error { border-color: #f87171; }
    textarea.ta-input { min-height: 110px; resize: vertical; line-height: 1.55; }
    .ta-hint { font-size: 12px; color: var(--text-3); margin-top: 5px; line-height: 1.5; }

    .ta-errors { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 10px; padding: 10px 14px; font-size: 13px; margin-bottom: 14px; }
    .ta-errors ul { padding-left: 18px; }
    .ta-warn { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 10px; padding: 10px 14px; font-size: 13px; margin-bottom: 14px; line-height: 1.5; }

    .ta-actions { display: flex; justify-content: flex-end; margin-top: 16px; }
    .ta-btn {
        min-height: 42px; padding: 0 20px; border: none; border-radius: 10px; cursor: pointer;
        background: var(--accent); color: #fff; font-size: 14px; font-weight: 700; font-family: inherit;
        box-shadow: 0 4px 12px var(--accent-glow); transition: background .15s;
    }
    .ta-btn:hover { background: var(--accent-dark); }
    .ta-btn[disabled] { opacity: .55; cursor: not-allowed; }

    .ta-list-head { display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-bottom: 1px solid var(--border); }
    .ta-list-title { font-size: 14px; font-weight: 700; color: var(--text-1); }
    .ta-count { font-size: 12px; color: var(--text-3); }

    .ta-item { display: flex; gap: 14px; align-items: flex-start; padding: 16px 20px; border-bottom: 1px solid var(--border); }
    .ta-item:last-child { border-bottom: none; }
    .ta-item-main { flex: 1; min-width: 0; }
    .ta-item-title { font-size: 14px; font-weight: 700; color: var(--text-1); overflow-wrap: anywhere; }
    .ta-item-body {
        font-size: 13px; color: var(--text-2); margin-top: 4px; line-height: 1.55; white-space: pre-line; overflow-wrap: anywhere;
        display: -webkit-box; -webkit-line-clamp: 3; line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
    }
    .ta-meta { display: flex; flex-wrap: wrap; gap: 6px 10px; align-items: center; margin-top: 8px; font-size: 12px; color: var(--text-3); }
    .ta-chip { padding: 2px 9px; border-radius: 999px; font-size: 11.5px; font-weight: 700; background: var(--accent-lt); color: var(--accent-dark); }

    .ta-del {
        width: 40px; height: 40px; flex-shrink: 0; border: none; border-radius: 10px; cursor: pointer;
        display: grid; place-items: center; background: #fef2f2; color: #dc2626; transition: background .15s;
    }
    .ta-del:hover { background: #fee2e2; }

    .ta-empty { padding: 48px 20px; text-align: center; color: var(--text-3); font-size: 13px; line-height: 1.6; }
    .ta-pages { margin-top: 16px; }

    @media (max-width: 600px) { .ta-grid { grid-template-columns: 1fr; } .ta-actions .ta-btn { width: 100%; } }
</style>

<div class="ta-wrap">

    <div class="ta-head">
        <h1 class="ta-title">Announcements</h1>
        <p class="ta-sub">Post updates for the students and parents of your sections.</p>
    </div>

    {{-- Post form --}}
    <section class="ta-card ta-form" aria-labelledby="ta-form-title">
        <h2 class="ta-form-title" id="ta-form-title">New announcement</h2>

        {{-- Validation errors, so an invalid form does not come back silently --}}
        @if($errors->any())
            <div class="ta-errors" role="alert">
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @if($sections->isEmpty())
            <div class="ta-warn">
                You don't have any sections yet, so an announcement would not reach anyone.
                The school admin assigns your classes in Class Schedules.
            </div>
        @endif

        <form method="POST" action="{{ route('teacher.announcements.store') }}" id="taForm">
            @csrf
            <div class="ta-grid">
                <div class="ta-field ta-span">
                    <label for="ta-title-input">Title</label>
                    <input type="text" id="ta-title-input" name="title" value="{{ old('title') }}" maxlength="255" required
                           class="ta-input @error('title') is-error @enderror" placeholder="e.g. No classes on Friday"
                           @error('title') aria-invalid="true" @enderror>
                </div>

                <div class="ta-field">
                    <label for="ta-section">Send to</label>
                    <select id="ta-section" name="section_id" class="ta-input @error('section_id') is-error @enderror">
                        <option value="">All my sections</option>
                        @foreach($sections as $sec)
                            <option value="{{ $sec->id }}" @selected((string) old('section_id') === (string) $sec->id)>
                                Grade {{ $sec->grade_level }} – {{ $sec->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ta-field">
                    <label for="ta-audience">Who can see it</label>
                    <select id="ta-audience" name="audience" class="ta-input" aria-describedby="ta-audience-hint">
                        <option value="all" @selected(old('audience', 'all') === 'all')>Students and parents</option>
                        <option value="students" @selected(old('audience') === 'students')>Students only</option>
                        <option value="parents" @selected(old('audience') === 'parents')>Parents only</option>
                    </select>
                </div>

                <div class="ta-field ta-span">
                    <label for="ta-body">Message</label>
                    <textarea id="ta-body" name="body" maxlength="20000" required
                              class="ta-input @error('body') is-error @enderror" placeholder="Write your announcement…"
                              @error('body') aria-invalid="true" @enderror>{{ old('body') }}</textarea>
                    <p class="ta-hint" id="ta-audience-hint">Everyone you choose gets a phone notification in the DP-LMS app.</p>
                </div>
            </div>

            <div class="ta-actions">
                <button type="submit" class="ta-btn" @disabled($sections->isEmpty())>Post announcement</button>
            </div>
        </form>
    </section>

    {{-- Posted list --}}
    <section class="ta-card" aria-labelledby="ta-list-title">
        <div class="ta-list-head">
            <h2 class="ta-list-title" id="ta-list-title">Posted announcements</h2>
            <span class="ta-count">{{ $announcements->total() }} total</span>
        </div>

        @forelse($announcements as $announcement)
            <article class="ta-item">
                <div class="ta-item-main">
                    <h3 class="ta-item-title">{{ $announcement->title }}</h3>
                    <p class="ta-item-body">{{ $announcement->body }}</p>
                    <div class="ta-meta">
                        <span class="ta-chip">{{ $announcement->target_label }}</span>
                        <span>{{ ['all' => 'Students and parents', 'students' => 'Students only', 'parents' => 'Parents only'][$announcement->audience] ?? ucfirst($announcement->audience) }}</span>
                        <time datetime="{{ $announcement->created_at->toIso8601String() }}" title="{{ $announcement->created_at->format('M j, Y g:i A') }}">
                            {{ $announcement->created_at->diffForHumans() }}
                        </time>
                    </div>
                </div>

                <form method="POST" action="{{ route('teacher.announcements.destroy', $announcement->id) }}"
                      onsubmit="return confirm('Delete this announcement? Students and parents will no longer see it.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ta-del" aria-label="Delete “{{ $announcement->title }}”" title="Delete">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </article>
        @empty
            <div class="ta-empty">No announcements yet.<br>Post your first one above.</div>
        @endforelse
    </section>

    @if($announcements->hasPages())
        <div class="ta-pages">{{ $announcements->links() }}</div>
    @endif
</div>

<script>
    // Prevent a double post on a slow connection; the Back button restores the page with it re-enabled
    (function () {
        const form = document.getElementById('taForm');
        const btn  = form.querySelector('button[type="submit"]');
        const canPost = !btn.disabled;
        form.addEventListener('submit', function () {
            btn.disabled = true;
            btn.textContent = 'Posting…';
        });
        window.addEventListener('pageshow', function (e) {
            if (e.persisted && canPost) { btn.disabled = false; btn.textContent = 'Post announcement'; }
        });
    })();
</script>
@endsection
