@extends('layouts.teacher')
@section('title', 'Messages')

@section('content')
<style>
    /* The chat fills the screen; only the conversation list and the message area scroll (like Messenger) */
    .msg-wrap {
        --msg-line: #E2E8F0;
        max-width: 1120px; margin: 0 auto;
        height: calc(100dvh - var(--nav-h) - 80px); min-height: 460px;
        display: flex; flex-direction: column;
    }
    .msg-wrap .tp-head { margin-bottom: 16px; }
    .msg-card {
        flex: 1; min-height: 0;
        display: grid; grid-template-columns: 340px minmax(0, 1fr);
        background: var(--surface); border: 1px solid #D9DFE8; border-radius: var(--r-lg); overflow: hidden;
    }

    /* ── Conversation list ── */
    .msg-list { display: flex; flex-direction: column; min-height: 0; border-right: 1px solid var(--msg-line); }
    .msg-list-head { padding: 16px 16px 12px; border-bottom: 1px solid var(--msg-line); display: grid; gap: 10px; }
    .msg-list-title { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .msg-list-title h2 { font-size: 16px; font-weight: 800; color: var(--text-1); }
    .msg-search { position: relative; }
    .msg-search svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-3); pointer-events: none; }
    .msg-input {
        width: 100%; height: 40px; border: 1px solid #D9DFE8; border-radius: 10px; background: #F8FAFC;
        font-family: inherit; font-size: 14px; color: var(--text-1);
    }
    .msg-input:focus { outline: none; border-color: var(--accent); background: #fff; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
    .msg-search .msg-input { padding: 0 12px 0 36px; }
    select.msg-input { padding: 0 12px; cursor: pointer; }

    .msg-convs { flex: 1; min-height: 0; overflow-y: auto; }
    .msg-conv {
        display: flex; gap: 12px; align-items: center; position: relative;
        padding: 12px 16px; border-bottom: 1px solid #EEF1F5; text-decoration: none;
        transition: background-color .12s ease;
    }
    .msg-conv[hidden] { display: none; }
    .msg-conv:hover { background: #F8FAFC; }
    .msg-conv.active { background: #EEF3FF; }
    .msg-conv.active::before { content: ''; position: absolute; left: 0; top: 10px; bottom: 10px; width: 3px; border-radius: 0 3px 3px 0; background: var(--accent-dark); }
    .msg-conv-main { flex: 1; min-width: 0; }
    .msg-conv-top, .msg-conv-bottom { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
    .msg-name { font-size: 14px; font-weight: 700; color: var(--text-1); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .msg-when { font-size: 12px; color: var(--text-3); flex-shrink: 0; }
    .msg-prev { font-size: 13px; color: var(--text-2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
    .msg-conv.unread .msg-name, .msg-conv.unread .msg-prev { color: var(--text-1); font-weight: 700; }
    .msg-unread {
        min-width: 20px; height: 20px; padding: 0 6px; border-radius: 10px; flex-shrink: 0;
        background: var(--accent); color: #fff; font-size: 11px; font-weight: 800;
        display: inline-flex; align-items: center; justify-content: center; font-variant-numeric: tabular-nums;
    }
    .msg-avatar {
        width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px; font-weight: 800; color: var(--accent-dark); background: var(--accent-lt);
    }
    .msg-conv.active .msg-avatar { background: #fff; box-shadow: inset 0 0 0 1px #C7D7FE; }
    .msg-list-empty { padding: 36px 20px; text-align: center; font-size: 14px; color: var(--text-2); line-height: 1.55; }
    .msg-list-empty b { display: block; color: var(--text-1); }

    /* ── Thread ── */
    .msg-thread { display: flex; flex-direction: column; min-height: 0; min-width: 0; }
    .msg-thread-head { display: flex; align-items: center; gap: 12px; padding: 12px 20px; min-height: 68px; border-bottom: 1px solid var(--msg-line); }
    .msg-thread-name { font-size: 15px; font-weight: 800; color: var(--text-1); }
    .msg-thread-sub { font-size: 12.5px; color: var(--text-2); }
    .msg-back { display: none; width: 40px; height: 40px; align-items: center; justify-content: center; margin-left: -8px; color: var(--text-2); border-radius: var(--r-sm); }
    .msg-back:hover { background: #F3F5F9; color: var(--text-1); }

    .msg-body { flex: 1; min-height: 0; overflow-y: auto; padding: 16px 20px 8px; background: #F8FAFC; }
    .msg-day { display: flex; align-items: center; gap: 12px; margin: 14px 0 12px; font-size: 12px; font-weight: 700; color: var(--text-3); }
    .msg-day::before, .msg-day::after { content: ''; flex: 1; height: 1px; background: var(--msg-line); }
    .bubble-row { display: flex; margin-bottom: 6px; }
    .bubble { max-width: min(72%, 560px); padding: 9px 13px; border-radius: 16px; font-size: 14px; line-height: 1.45; overflow-wrap: anywhere; }
    .bubble-text { white-space: pre-wrap; }
    .bubble-mine  { margin-left: auto; background: var(--accent); color: #fff; border-bottom-right-radius: 5px; }
    .bubble-their { margin-right: auto; background: #fff; color: var(--text-1); border: 1px solid var(--msg-line); border-bottom-left-radius: 5px; }
    .bubble-time { font-size: 11px; margin-top: 3px; opacity: .78; font-variant-numeric: tabular-nums; }
    .bubble-text + .bubble-file { margin-top: 6px; }
    .bubble-file { display: inline-flex; align-items: center; gap: 6px; font-size: 13.5px; font-weight: 700; color: inherit; text-decoration: underline; text-underline-offset: 2px; }
    .msg-thread-empty { text-align: center; color: var(--text-2); font-size: 14px; padding: 40px 20px; }

    .msg-error { background: #FEF2F2; color: #B91C1C; font-size: 13px; padding: 8px 20px; border-top: 1px solid #FECACA; }
    .msg-error:empty { display: none; }

    .msg-composer { display: flex; align-items: flex-end; gap: 10px; padding: 12px 16px; border-top: 1px solid var(--msg-line); background: #fff; }
    .msg-composer textarea {
        flex: 1; resize: none; min-height: 44px; max-height: 140px;
        border: 1px solid #D9DFE8; border-radius: 12px; padding: 11px 14px;
        font-family: inherit; font-size: 14px; line-height: 1.4; color: var(--text-1); background: #F8FAFC;
    }
    .msg-composer textarea:focus { outline: none; border-color: var(--accent); background: #fff; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
    .msg-send {
        width: 44px; height: 44px; border: none; border-radius: 12px; background: var(--accent); color: #fff;
        cursor: pointer; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
        transition: background-color .15s ease, transform .1s ease;
    }
    .msg-send:hover { background: var(--accent-dark); }
    .msg-send:active { transform: scale(.96); }
    .msg-send:disabled { opacity: .55; cursor: progress; }

    .msg-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px; text-align: center; background: #F8FAFC; }
    .msg-empty-icon { width: 56px; height: 56px; border-radius: 16px; border: 1px solid var(--msg-line); background: #fff; color: var(--text-3); display: flex; align-items: center; justify-content: center; margin-bottom: 14px; }
    .msg-empty b { font-size: 15px; color: var(--text-1); }
    .msg-empty p { font-size: 13.5px; color: var(--text-2); margin-top: 4px; }

    @media (max-width: 768px) {
        .msg-wrap { height: calc(100dvh - var(--nav-h) - 36px); min-height: 420px; }
        .msg-wrap .tp-head { display: none; }
        .msg-card { grid-template-columns: 1fr; }
        .msg-list { border-right: none; display: var(--list-display, flex); }
        .msg-thread { display: var(--thread-display, none); }
        .msg-back { display: inline-flex; }
        .msg-body { padding: 12px 14px 6px; }
        .bubble { max-width: 84%; }
    }
    @media (max-width: 480px) {
        .msg-wrap { height: calc(100dvh - var(--nav-h) - 28px); }
        .main-content .msg-composer textarea { font-size: 16px !important; }
    }
</style>

@php
    $initial = fn ($name) => mb_strtoupper(mb_substr($name ?? '?', 0, 1));
@endphp

<div class="msg-wrap" id="msgApp" data-base="{{ route('teacher.messages') }}"
     style="@if($activeUser)--list-display:none;--thread-display:flex;@else--list-display:flex;--thread-display:none;@endif">

    <div class="tp-head">
        <div>
            <h1 class="tp-title">Messages</h1>
            <p class="tp-sub">Chat with students in your sections.</p>
        </div>
    </div>

    <div class="msg-card">

        {{-- ── Conversations ── --}}
        <aside class="msg-list" aria-label="Conversations">
            <div class="msg-list-head">
                <div class="msg-list-title"><h2>Chats</h2></div>
                <label class="msg-search">
                    <span class="sr-only">Search conversations</span>
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-3.5-3.5"/></svg>
                    <input type="search" class="msg-input" id="msgSearch" placeholder="Search by name" autocomplete="off">
                </label>
                @if($contacts->count())
                    <label>
                        <span class="sr-only">Start a new message</span>
                        <select class="msg-input" id="msgNew">
                            <option value="">New message to a student…</option>
                            @foreach($contacts as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
            </div>

            <div class="msg-convs" id="msgList">
                @forelse($conversations as $conv)
                    @php $isActive = $activeUser && (int) $activeUser->id === (int) $conv['id']; @endphp
                    <a class="msg-conv {{ $isActive ? 'active' : '' }} {{ $conv['unread'] > 0 && ! $isActive ? 'unread' : '' }}"
                       href="{{ route('teacher.messages', ['with' => $conv['id']]) }}" data-id="{{ $conv['id'] }}" data-name="{{ mb_strtolower($conv['name']) }}"
                       @if($isActive) aria-current="true" @endif>
                        <span class="msg-avatar" aria-hidden="true">{{ $initial($conv['name']) }}</span>
                        <span class="msg-conv-main">
                            <span class="msg-conv-top">
                                <span class="msg-name">{{ $conv['name'] }}</span>
                                <span class="msg-when">{{ $conv['last_at'] }}</span>
                            </span>
                            <span class="msg-conv-bottom">
                                <span class="msg-prev">{{ $conv['last_mine'] ? 'You: ' : '' }}{{ $conv['last_message'] }}</span>
                                @if($conv['unread'] > 0 && ! $isActive)
                                    <span class="msg-unread" aria-label="{{ $conv['unread'] }} unread">{{ $conv['unread'] }}</span>
                                @endif
                            </span>
                        </span>
                    </a>
                @empty
                    <div class="msg-list-empty">
                        <b>No conversations yet</b>
                        Pick a student under "New message" to start one.
                    </div>
                @endforelse
            </div>
        </aside>

        {{-- ── Thread ── --}}
        <section class="msg-thread" id="msgThread" aria-label="Conversation">
            @if($activeUser)
                <div class="msg-thread-head">
                    <a href="{{ route('teacher.messages') }}" class="msg-back" id="msgBack" aria-label="Back to chats">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <span class="msg-avatar" aria-hidden="true">{{ $initial($activeUser->name) }}</span>
                    <div style="min-width:0;">
                        <p class="msg-thread-name">{{ $activeUser->name }}</p>
                        <p class="msg-thread-sub">{{ $activeSection ? 'Student, ' . $activeSection : 'Student' }}</p>
                    </div>
                </div>

                <div class="msg-body" id="msgBody" aria-live="polite">
                    @php $lastDay = null; @endphp
                    @forelse($messages as $m)
                        @php
                            $mine = (int) $m->sender_id === (int) $me->id;
                            $day  = $m->created_at?->toDateString();
                        @endphp
                        @if($day !== $lastDay)
                            <div class="msg-day">{{ $m->created_at->isToday() ? 'Today' : ($m->created_at->isYesterday() ? 'Yesterday' : $m->created_at->format('M j, Y')) }}</div>
                            @php $lastDay = $day; @endphp
                        @endif
                        <div class="bubble-row">
                            <div class="bubble {{ $mine ? 'bubble-mine' : 'bubble-their' }}">
                                @if($m->body !== null && $m->body !== '')<span class="bubble-text">{{ $m->body }}</span>@endif
                                @if($m->file_path)
                                    <a class="bubble-file" href="{{ asset('storage/' . ltrim($m->file_path, '/')) }}" target="_blank" rel="noopener">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        {{ $m->file_name ?? 'Attachment' }}
                                    </a>
                                @endif
                                <div class="bubble-time">{{ $m->created_at?->format('g:i A') }}{{ $m->edited_at ? ', edited' : '' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="msg-thread-empty">No messages yet. Say hello to {{ explode(' ', trim($activeUser->name))[0] }}.</div>
                    @endforelse
                </div>

                <div class="msg-error" id="msgError" role="alert">@error('body'){{ $message }}@enderror</div>

                <form method="POST" action="{{ route('teacher.messages.store') }}" class="msg-composer" id="msgForm">
                    @csrf
                    <input type="hidden" name="receiver_id" value="{{ $activeUser->id }}">
                    <label for="msgText" class="sr-only">Message to {{ $activeUser->name }}</label>
                    <textarea id="msgText" name="body" rows="1" placeholder="Write a message" required maxlength="2000">{{ old('body') }}</textarea>
                    <button type="submit" class="msg-send" id="msgSend" aria-label="Send">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12L3.3 4.6a.8.8 0 011.1-.9l16 7.5a.9.9 0 010 1.6l-16 7.5a.8.8 0 01-1.1-.9L5 12zm0 0h7"/></svg>
                    </button>
                </form>
            @else
                <div class="msg-empty">
                    <span class="msg-empty-icon" aria-hidden="true">
                        <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5m-9 6l2.6-2.6A2 2 0 018 17h11a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v14z"/></svg>
                    </span>
                    <b>Select a chat</b>
                    <p>Pick a student on the left, or start a new message.</p>
                </div>
            @endif
        </section>

    </div>
</div>

<script>
(function () {
    const app = document.getElementById('msgApp');
    if (!app) return;
    const BASE = app.dataset.base;
    const POLL_MS = 5000;
    let seq = 0, timer = null, busy = false;

    const $ = (id) => document.getElementById(id);
    const activeId = () => new URL(location.href).searchParams.get('with');
    const gone = () => !document.body.contains(app);   // the teacher layout swapped in another page

    function atBottom(el) { return el.scrollHeight - el.scrollTop - el.clientHeight < 60; }
    function toBottom() { const b = $('msgBody'); if (b) b.scrollTop = b.scrollHeight; }

    function fit(t) { t.style.height = 'auto'; t.style.height = Math.min(t.scrollHeight, 140) + 'px'; }

    // Same server page as a full load (same access rules, marks the chat read); only parts are swapped in.
    function fetchPage(url, options) {
        return fetch(url, Object.assign({ credentials: 'same-origin', headers: { 'Accept': 'text/html' } }, options || {}))
            .then(r => {
                if (r.redirected && new URL(r.url).pathname.indexOf('/teacher/messages') !== 0) {
                    window.location.href = r.url;   // signed out → login page
                    throw new Error('left');
                }
                if (!r.ok) throw new Error('http ' + r.status);
                return r.text();
            })
            .then(html => new DOMParser().parseFromString(html, 'text/html'));
    }

    function applySearch() {
        const q = ($('msgSearch')?.value || '').trim().toLowerCase();
        document.querySelectorAll('#msgList .msg-conv').forEach(a => { a.hidden = q !== '' && !a.dataset.name.includes(q); });
    }

    // Open a chat without reloading
    function openChat(url, push) {
        const my = ++seq;
        return fetchPage(url).then(doc => {
            if (my !== seq || gone()) return;
            const next = doc.getElementById('msgApp');
            if (!next) { window.location.href = url; return; }
            app.setAttribute('style', next.getAttribute('style') || '');
            $('msgList').innerHTML = next.querySelector('#msgList').innerHTML;
            $('msgThread').innerHTML = next.querySelector('#msgThread').innerHTML;
            if (push) history.pushState(null, '', url);
            applySearch();
            toBottom();
            const t = $('msgText');
            if (t && window.matchMedia('(min-width: 769px)').matches) t.focus();
        }).catch(e => { if (e.message !== 'left') window.location.href = url; });
    }

    // Live refresh: new messages in the open chat + the list (previews, unread counts)
    function refresh() {
        if (gone()) { clearInterval(timer); return; }
        if (document.hidden || busy) return;
        const my = seq;
        fetchPage(location.href).then(doc => {
            if (my !== seq || gone()) return;   // the teacher switched chats meanwhile
            const list = doc.getElementById('msgList');
            if (list && list.innerHTML !== $('msgList').innerHTML) { $('msgList').innerHTML = list.innerHTML; applySearch(); }
            const body = doc.getElementById('msgBody'), cur = $('msgBody');
            if (body && cur && body.innerHTML !== cur.innerHTML) {
                const stick = atBottom(cur);
                cur.innerHTML = body.innerHTML;
                if (stick) toBottom();
            }
        }).catch(() => {});
    }

    // Send without reloading (same form post: CSRF, bad-word filter and validation stay on the server)
    function send(form) {
        const t = $('msgText'), btn = $('msgSend');
        if (!t.value.trim() || busy) return;
        busy = true; btn.disabled = true;
        const my = ++seq;
        fetchPage(form.action, { method: 'POST', body: new FormData(form) }).then(doc => {
            if (gone()) return;
            const next = doc.getElementById('msgApp');
            if (!next) return;
            const err = next.querySelector('#msgError');
            $('msgError').textContent = err ? err.textContent.trim() : '';
            if (my === seq) {
                $('msgList').innerHTML = next.querySelector('#msgList').innerHTML;
                const body = next.querySelector('#msgBody');
                if (body) $('msgBody').innerHTML = body.innerHTML;
                applySearch();
            }
            if (!$('msgError').textContent) { t.value = ''; fit(t); }
            toBottom();
        }).catch(e => {
            if (e.message !== 'left') $('msgError').textContent = 'The message was not sent. Check your connection and try again.';
        }).finally(() => { busy = false; const b = $('msgSend'); if (b) b.disabled = false; $('msgText')?.focus(); });
    }

    // One set of listeners on the wrapper; the inner parts are swapped freely
    app.addEventListener('click', e => {
        const conv = e.target.closest('.msg-conv');
        const back = e.target.closest('#msgBack');
        if ((!conv && !back) || e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
        e.preventDefault();
        if (back) {
            app.style.setProperty('--list-display', 'flex');
            app.style.setProperty('--thread-display', 'none');
            history.pushState(null, '', BASE);
            seq++;
            return;
        }
        if (conv.getAttribute('href') !== location.href) openChat(conv.href, true);
    });
    app.addEventListener('submit', e => { if (e.target.id === 'msgForm') { e.preventDefault(); send(e.target); } });
    app.addEventListener('keydown', e => {
        if (e.target.id === 'msgText' && e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
            e.preventDefault();
            send($('msgForm'));
        }
    });
    app.addEventListener('input', e => {
        if (e.target.id === 'msgText') fit(e.target);
        if (e.target.id === 'msgSearch') applySearch();
    });
    app.addEventListener('change', e => {
        if (e.target.id === 'msgNew' && e.target.value) {
            openChat(BASE + '?with=' + encodeURIComponent(e.target.value), true);
            e.target.value = '';
        }
    });

    toBottom();
    if ($('msgText')) fit($('msgText'));
    timer = setInterval(refresh, POLL_MS);
})();
</script>
@endsection
