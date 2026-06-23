@extends('layouts.teacher')
@section('title', 'Messages')

@section('content')
<style>
    .msg-wrap { max-width: 64rem; margin: 0 auto; }
    .msg-card { background:#fff; border:1px solid #eef2f7; border-radius:18px;
                box-shadow:0 2px 14px rgba(15,23,42,.04); overflow:hidden; }
    .msg-grid { display:grid; grid-template-columns: 320px 1fr; min-height: 560px; }

    /* conversation list */
    .msg-list { border-right:1px solid #f1f5f9; display:flex; flex-direction:column; }
    .msg-list-head { padding:16px 18px; border-bottom:1px solid #f1f5f9; }
    .msg-conv { display:flex; gap:11px; align-items:center; padding:12px 16px;
                border-bottom:1px solid #f6f8fb; text-decoration:none; transition:background .15s; }
    .msg-conv:hover { background:#f8fbff; }
    .msg-conv.active { background:#eff6ff; border-left:3px solid #2563eb; padding-left:13px; }
    .msg-avatar { width:40px; height:40px; border-radius:12px; flex-shrink:0;
                  display:flex; align-items:center; justify-content:center;
                  font-size:15px; font-weight:700; color:#fff;
                  background:linear-gradient(135deg,#60a5fa,#2563eb); }
    .msg-name { font-size:13.5px; font-weight:600; color:#0f172a; }
    .msg-prev { font-size:12px; color:#94a3b8; max-width:170px; white-space:nowrap;
                overflow:hidden; text-overflow:ellipsis; }
    .msg-unread { min-width:18px; height:18px; padding:0 5px; border-radius:9px;
                  background:#2563eb; color:#fff; font-size:10px; font-weight:700;
                  display:flex; align-items:center; justify-content:center; }

    /* thread */
    .msg-thread { display:flex; flex-direction:column; }
    .msg-thread-head { padding:14px 18px; border-bottom:1px solid #f1f5f9;
                       display:flex; align-items:center; gap:11px; }
    .msg-body-area { flex:1; padding:18px; overflow-y:auto; max-height:430px; background:#fcfdff; }
    .bubble { max-width:74%; margin-bottom:10px; padding:9px 13px; border-radius:14px;
              font-size:14px; line-height:1.4; }
    .bubble-row { display:flex; }
    .bubble-mine  { margin-left:auto; background:#2563eb; color:#fff; border-bottom-right-radius:4px; }
    .bubble-their { margin-right:auto; background:#fff; color:#0f172a; border:1px solid #eef2f7;
                    border-bottom-left-radius:4px; }
    .bubble-time { font-size:10px; margin-top:3px; opacity:.7; }

    .msg-composer { padding:12px 14px; border-top:1px solid #f1f5f9; display:flex; gap:9px; }
    .msg-composer textarea { flex:1; resize:none; border:1px solid #e7edf3; border-radius:12px;
                             padding:10px 14px; font-size:14px; color:#0f172a; outline:none;
                             max-height:120px; }
    .msg-composer textarea:focus { border-color:#bfdbfe; box-shadow:0 0 0 3px rgba(59,130,246,.1); }
    .msg-send { width:46px; height:46px; border:none; border-radius:50%; background:#2563eb;
                color:#fff; cursor:pointer; flex-shrink:0; display:flex; align-items:center;
                justify-content:center; align-self:flex-end; }
    .msg-send:hover { background:#1d4ed8; }

    .msg-empty { flex:1; display:flex; flex-direction:column; align-items:center;
                 justify-content:center; padding:40px; text-align:center; color:#94a3b8; }
    .msg-empty-icon { width:60px; height:60px; border-radius:16px; background:#f1f5f9;
                      display:flex; align-items:center; justify-content:center; margin-bottom:14px; }

    @media (max-width:768px){
        .msg-grid { grid-template-columns: 1fr; }
        .msg-list { border-right:none; display: var(--list-display, flex); }
        .msg-thread { display: var(--thread-display, none); }
    }
</style>

<div class="msg-wrap"
     style="@if($activeUser)--list-display:none;--thread-display:flex;@else--list-display:flex;--thread-display:none;@endif">

    <div class="mb-6">
        <p style="font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#94a3b8;">Communication</p>
        <h1 style="font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-.02em;">Messages</h1>
        <p style="font-size:13px;color:#94a3b8;margin-top:2px;">Chat with students in your sections.</p>
    </div>

    @if(session('success'))
        <div style="background:#dcfce7;border:1px solid #bbf7d0;color:#15803d;padding:10px 14px;border-radius:12px;font-size:13px;margin-bottom:14px;">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div class="msg-card">
        <div class="msg-grid">

            {{-- ── LEFT: conversations ──────────────────────────────── --}}
            <div class="msg-list">
                <div class="msg-list-head">
                    <p style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:8px;">Conversations</p>
                    @if($contacts->count())
                    <select onchange="if(this.value) window.location='{{ route('teacher.messages') }}?with='+this.value"
                            style="width:100%;border:1px solid #e7edf3;border-radius:10px;padding:7px 10px;font-size:12.5px;color:#334155;background:#fbfcfe;">
                        <option value="">+ New message to a student…</option>
                        @foreach($contacts as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @endif
                </div>

                <div style="overflow-y:auto;flex:1;max-height:480px;">
                    @forelse($conversations as $conv)
                        <a class="msg-conv {{ $activeUser && $activeUser->id == $conv['id'] ? 'active' : '' }}"
                           href="{{ route('teacher.messages', ['with' => $conv['id']]) }}">
                            <div class="msg-avatar">{{ strtoupper(substr($conv['name'] ?? '?', 0, 1)) }}</div>
                            <div style="flex:1;min-width:0;">
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:6px;">
                                    <span class="msg-name">{{ $conv['name'] }}</span>
                                    <span style="font-size:10px;color:#cbd5e1;flex-shrink:0;">{{ $conv['last_at'] }}</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:6px;">
                                    <span class="msg-prev">{{ $conv['last_mine'] ? 'You: ' : '' }}{{ $conv['last_message'] }}</span>
                                    @if($conv['unread'] > 0)
                                        <span class="msg-unread">{{ $conv['unread'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div style="padding:36px 20px;text-align:center;color:#94a3b8;font-size:13px;">
                            No conversations yet.<br>Use “New message” above to start one.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ── RIGHT: thread ────────────────────────────────────── --}}
            <div class="msg-thread">
                @if($activeUser)
                    <div class="msg-thread-head">
                        <a href="{{ route('teacher.messages') }}"
                           style="display:none;color:#64748b;" class="msg-back">←</a>
                        <div class="msg-avatar">{{ strtoupper(substr($activeUser->name ?? '?', 0, 1)) }}</div>
                        <div>
                            <p style="font-size:14px;font-weight:700;color:#0f172a;">{{ $activeUser->name }}</p>
                            @if($activeSection)
                                <p style="font-size:11px;color:#94a3b8;">{{ $activeSection }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="msg-body-area" id="msgBody">
                        @forelse($messages as $m)
                            @php $mine = $m->sender_id === $me->id; @endphp
                            <div class="bubble-row">
                                <div class="bubble {{ $mine ? 'bubble-mine' : 'bubble-their' }}">
                                    {{ $m->body }}
                                    <div class="bubble-time">{{ $m->created_at?->format('M d, h:i A') }}</div>
                                </div>
                            </div>
                        @empty
                            <div style="text-align:center;color:#94a3b8;font-size:13px;padding:30px;">
                                No messages yet. Say hello 👋
                            </div>
                        @endforelse
                    </div>

                    @error('body')
                        <div style="background:#fee2e2;color:#b91c1c;font-size:12px;padding:8px 14px;">{{ $message }}</div>
                    @enderror

                    <form method="POST" action="{{ route('teacher.messages.store') }}" class="msg-composer">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ $activeUser->id }}">
                        <textarea name="body" rows="1" placeholder="Type a message…"
                                  required maxlength="2000"
                                  onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();this.form.submit();}"></textarea>
                        <button type="submit" class="msg-send" aria-label="Send">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                        </button>
                    </form>
                @else
                    <div class="msg-empty">
                        <div class="msg-empty-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/></svg>
                        </div>
                        <p style="font-size:15px;font-weight:700;color:#334155;">Select a conversation</p>
                        <p style="font-size:13px;margin-top:4px;">Pick a student on the left, or start a new message.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

<script>
    // auto-scroll thread to newest
    var b = document.getElementById('msgBody');
    if (b) b.scrollTop = b.scrollHeight;
    // show back arrow on mobile
    if (window.matchMedia('(max-width:768px)').matches) {
        var back = document.querySelector('.msg-back');
        if (back) back.style.display = 'inline';
    }
</script>
@endsection