@extends('layouts.app')
@section('title', 'Messages')

@section('content')
{{-- B6-1/B6-2/B6-3: one conversation per teacher, oldest → newest, marked read when opened,
     attachments shown. Dati: iisang halo-halong listahan para sa lahat ng teacher. --}}
<style>
    .ms-wrap { max-width: 1040px; margin: 0 auto; }
    .ms-title { font-size: 24px; font-weight: 800; color: var(--text-1); letter-spacing: -.01em; }
    .ms-sub { font-size: 13.5px; color: var(--text-2); margin: 4px 0 18px; }

    .ms-grid { display: grid; grid-template-columns: 280px 1fr; gap: 16px; align-items: start; }
    .ms-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); overflow: hidden; }

    /* Contacts */
    .ms-contacts { list-style: none; }
    .ms-contact { display: flex; align-items: center; gap: 11px; padding: 12px 14px; border-bottom: 1px solid var(--border); text-decoration: none; color: inherit; }
    .ms-contacts li:last-child .ms-contact { border-bottom: none; }
    .ms-contact:hover { background: #F8FAFF; }
    .ms-contact[aria-current="true"] { background: var(--accent-lt); box-shadow: inset 3px 0 0 var(--accent); }
    .ms-av { width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0; display: grid; place-items: center; background: var(--accent-lt); color: var(--accent); font-weight: 800; font-size: 14px; }
    .ms-contact[aria-current="true"] .ms-av { background: var(--accent); color: #fff; }
    .ms-c-main { flex: 1; min-width: 0; }
    .ms-c-name, .ms-c-last { display: block; }
    .ms-c-name { font-size: 13.5px; font-weight: 700; color: var(--text-1); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ms-c-last { font-size: 12px; color: var(--text-3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px; }
    .ms-badge { min-width: 20px; height: 20px; padding: 0 6px; border-radius: 10px; background: var(--danger); color: #fff; font-size: 11px; font-weight: 800; display: grid; place-items: center; flex-shrink: 0; }

    /* Thread */
    .ms-head { display: flex; align-items: center; gap: 11px; padding: 14px 18px; border-bottom: 1px solid var(--border); }
    .ms-head b { font-size: 15px; color: var(--text-1); }
    .ms-head-role { display: block; font-size: 12px; color: var(--text-3); }
    .ms-thread { padding: 16px 18px; max-height: 56vh; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; background: #FBFCFF; }
    .ms-day { align-self: center; font-size: 11.5px; font-weight: 700; color: var(--text-3); padding: 2px 10px; border-radius: 99px; background: var(--surface); border: 1px solid var(--border); margin: 6px 0; }
    .ms-msg { max-width: 76%; display: flex; flex-direction: column; }
    .ms-msg.mine { align-self: flex-end; align-items: flex-end; }
    .ms-bubble { padding: 10px 14px; border-radius: 16px; font-size: 14px; line-height: 1.5; overflow-wrap: anywhere; }
    .ms-text { white-space: pre-wrap; }   /* only the message keeps its line breaks, not the template's indentation */
    .ms-text + .ms-file { margin-top: 6px; }
    .ms-msg.mine .ms-bubble { background: var(--accent); color: #fff; border-bottom-right-radius: 5px; }
    .ms-msg.them .ms-bubble { background: var(--surface); color: var(--text-1); border: 1px solid var(--border); border-bottom-left-radius: 5px; }
    .ms-file { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; text-decoration: underline; color: inherit; }
    .ms-time { font-size: 11px; color: var(--text-3); margin-top: 3px; }
    .ms-empty { padding: 40px 20px; text-align: center; color: var(--text-2); font-size: 13.5px; line-height: 1.6; }

    /* Compose */
    .ms-compose { display: flex; gap: 10px; align-items: flex-end; padding: 12px 14px; border-top: 1px solid var(--border); }
    .ms-input { flex: 1; min-height: 46px; max-height: 160px; border: 1.5px solid var(--border); border-radius: 12px; padding: 11px 13px; font-size: 14px; font-family: inherit; color: var(--text-1); background: #F8FAFC; resize: vertical; }
    .ms-input:focus { outline: none; border-color: var(--accent); background: #fff; box-shadow: 0 0 0 3px var(--accent-glow); }
    .ms-send { min-height: 46px; padding: 0 18px; border: none; border-radius: 12px; background: var(--accent); color: #fff; font-size: 14px; font-weight: 700; font-family: inherit; cursor: pointer; }
    .ms-send:hover { background: var(--accent-dark); }
    .ms-send[disabled] { opacity: .65; cursor: progress; }
    .ms-err { color: #B91C1C; font-size: 12.5px; padding: 0 14px 10px; }
    .ms-note { padding: 12px 16px; border-top: 1px solid var(--border); font-size: 13px; color: var(--text-2); background: #F8FAFC; }

    @media (max-width: 760px) {
        .ms-grid { grid-template-columns: 1fr; }
        /* contacts become a scrollable row of chips */
        .ms-contacts { display: flex; overflow-x: auto; gap: 8px; padding: 10px; }
        .ms-contact { border: 1px solid var(--border); border-radius: 999px; padding: 6px 12px 6px 6px; white-space: nowrap; }
        .ms-contacts li:last-child .ms-contact { border-bottom: 1px solid var(--border); }
        .ms-contact[aria-current="true"] { box-shadow: none; border-color: var(--accent); }
        .ms-c-last { display: none; }
        .ms-av { width: 30px; height: 30px; font-size: 12px; }
        .ms-msg { max-width: 88%; }
        .ms-thread { max-height: 60vh; }
    }
</style>

<div class="ms-wrap">
    <h1 class="ms-title">Messages</h1>
    <p class="ms-sub">Chat with your teachers. Pick a teacher to see your conversation.</p>

    @if($contacts->isEmpty())
        <div class="ms-card ms-empty">
            <b style="display:block;color:var(--text-1);">No teachers yet</b>
            Your teachers appear here once you are enrolled in a section.
        </div>
    @else
        <div class="ms-grid">
            <nav class="ms-card" aria-label="Teachers">
                <ul class="ms-contacts">
                    @foreach($contacts as $c)
                        @php $isActive = $active && $active->id === $c->id; @endphp
                        <li>
                            <a class="ms-contact" href="{{ route('student.messages', ['with' => $c->id]) }}" @if($isActive) aria-current="true" @endif>
                                <span class="ms-av" aria-hidden="true">{{ mb_strtoupper(mb_substr($c->name, 0, 1)) }}</span>
                                <span class="ms-c-main">
                                    <span class="ms-c-name">{{ $c->name }}</span>
                                    <span class="ms-c-last">
                                        @if($c->last)
                                            {{ (int) $c->last->sender_id === (int) auth()->id() ? 'You: ' : '' }}{{ Str::limit($c->last->body !== '' ? $c->last->body : ($c->last->file_name ?? 'Attachment'), 40) }}
                                        @else
                                            No messages yet
                                        @endif
                                    </span>
                                </span>
                                @if($c->unread > 0)
                                    <span class="ms-badge" aria-label="{{ $c->unread }} unread">{{ $c->unread }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <section class="ms-card" aria-label="Conversation with {{ $active->name }}">
                <div class="ms-head">
                    <span class="ms-av" aria-hidden="true">{{ mb_strtoupper(mb_substr($active->name, 0, 1)) }}</span>
                    <div><b>{{ $active->name }}</b><span class="ms-head-role">Teacher</span></div>
                </div>

                <div class="ms-thread" id="msThread" tabindex="0" aria-live="polite">
                    @php $lastDay = null; @endphp
                    @forelse($thread as $m)
                        @php
                            $mine = (int) $m->sender_id === (int) auth()->id();
                            $day  = $m->created_at?->isToday() ? 'Today' : ($m->created_at?->isYesterday() ? 'Yesterday' : $m->created_at?->format('M j, Y'));
                        @endphp
                        @if($day !== $lastDay)
                            <span class="ms-day">{{ $day }}</span>
                            @php $lastDay = $day; @endphp
                        @endif
                        <div class="ms-msg {{ $mine ? 'mine' : 'them' }}">
                            <div class="ms-bubble">
                                @if($m->body !== '' && $m->body !== null)<span class="ms-text">{{ $m->body }}</span>@endif
                                @if($m->file_path)
                                    <a class="ms-file" href="{{ asset('storage/' . ltrim($m->file_path, '/')) }}" target="_blank" rel="noopener"><span aria-hidden="true">📎</span>{{ $m->file_name ?? 'Attachment' }}</a>
                                @endif
                            </div>
                            <span class="ms-time">
                                {{ $mine ? 'You' : 'Teacher' }} · {{ $m->created_at?->format('g:i A') }}{{ $m->edited_at ? ' · edited' : '' }}{{ $mine && $m->is_read ? ' · seen' : '' }}
                            </span>
                        </div>
                    @empty
                        <p class="ms-empty">No messages yet. Say hello or ask a question below.</p>
                    @endforelse
                </div>

                @if($active->canMessage)
                    <form method="POST" action="{{ route('student.messages.store') }}" id="msForm">
                        @csrf
                        <input type="hidden" name="teacher_id" value="{{ $active->id }}">
                        @if($errors->any())
                            <p class="ms-err" role="alert">{{ $errors->first() }}</p>
                        @endif
                        <div class="ms-compose">
                            <label for="msBody" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);">Message to {{ $active->name }}</label>
                            <textarea id="msBody" name="body" class="ms-input" maxlength="2000" rows="1" required
                                      placeholder="Write a message…">{{ old('body') }}</textarea>
                            <button type="submit" class="ms-send">Send</button>
                        </div>
                    </form>
                @else
                    <p class="ms-note">This teacher no longer handles your section, so you can read the conversation but not reply.</p>
                @endif
            </section>
        </div>
    @endif
</div>

<script>
    (function () {
        // Newest at the bottom, so start scrolled to the end
        const t = document.getElementById('msThread');
        if (t) t.scrollTop = t.scrollHeight;

        const form = document.getElementById('msForm');
        if (!form) return;
        const btn = form.querySelector('.ms-send');
        const box = document.getElementById('msBody');
        // Enter sends, Shift+Enter = new line
        box.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) { e.preventDefault(); form.requestSubmit(); }
        });
        form.addEventListener('submit', function (e) {
            if (!box.value.trim()) { e.preventDefault(); return; }
            btn.disabled = true; btn.textContent = 'Sending…';
        });
        window.addEventListener('pageshow', function (e) { if (e.persisted) { btn.disabled = false; btn.textContent = 'Send'; } });
    })();
</script>
@endsection
