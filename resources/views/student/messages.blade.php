@extends('layouts.app')
@section('title', 'Messages')

@section('content')
<style>
    .msg-wrap { max-width: 760px; margin: 0 auto; padding: 8px 16px 56px; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    .msg-h1 { font-size: 26px; font-weight: 800; color: #0F172A; letter-spacing: -0.3px; }
    .msg-sub { font-size: 13.5px; color: #64748B; margin-top: 3px; }

    .msg-flash { display:flex; align-items:center; gap:10px; background:#ECFDF5; border:1px solid #A7F3D0; color:#047857;
                 padding:13px 16px; border-radius:14px; margin-bottom:18px; font-size:14px; font-weight:600; animation:pop .35s ease; }
    @keyframes pop { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:none} }

    .card { background:#fff; border:1px solid #EEF1F5; border-radius:22px; padding:22px;
            box-shadow:0 4px 24px rgba(15,23,42,.05); margin-bottom:20px; }
    .card-title { font-size:15px; font-weight:700; color:#0F172A; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
    .card-title .dot { width:8px; height:8px; border-radius:50%; background:#2563EB; }

    .lbl { display:block; font-size:12.5px; font-weight:600; color:#475569; margin-bottom:7px; }
    .sel-wrap { position:relative; }
    .sel-wrap svg { position:absolute; right:14px; top:50%; transform:translateY(-50%); pointer-events:none; color:#94A3B8; }
    select.inp, textarea.inp {
        width:100%; border:1.6px solid #E5E9F0; border-radius:14px; padding:12px 14px; font-size:14px; color:#0F172A;
        font-family:inherit; background:#F8FAFC; transition:.18s; box-sizing:border-box; appearance:none; -webkit-appearance:none;
    }
    select.inp { padding-right:38px; cursor:pointer; }
    textarea.inp { min-height:92px; resize:vertical; }
    .inp:focus { outline:none; border-color:#2563EB; background:#fff; box-shadow:0 0 0 4px rgba(37,99,235,.12); }
    .inp.err { border-color:#FCA5A5; }
    .err-txt { color:#DC2626; font-size:12px; margin-top:5px; }
    .warn-txt { color:#B45309; font-size:12px; margin-top:6px; }

    .send-btn { display:inline-flex; align-items:center; gap:9px; border:none; cursor:pointer;
                background:linear-gradient(135deg,#2563EB,#1E88E5); color:#fff; font-weight:700; font-size:14px;
                padding:12px 22px; border-radius:14px; box-shadow:0 8px 18px rgba(37,99,235,.32); transition:.2s; }
    .send-btn:hover { transform:translateY(-1px); box-shadow:0 11px 22px rgba(37,99,235,.4); }
    .send-btn:active { transform:translateY(0); }

    .row { display:flex; gap:10px; margin-bottom:16px; align-items:flex-end; }
    .row.mine { flex-direction:row-reverse; }
    .avatar { width:36px; height:36px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center;
              font-size:14px; font-weight:700; color:#fff; }
    .bubble { max-width:74%; padding:11px 15px; border-radius:18px; position:relative; }
    .bubble .nm { font-size:11px; font-weight:700; margin-bottom:3px; opacity:.85; }
    .bubble .bd { font-size:14px; line-height:1.45; white-space:pre-wrap; word-break:break-word; }
    .bubble .tm { font-size:10px; margin-top:5px; opacity:.7; }
    .b-mine { background:linear-gradient(135deg,#2563EB,#1E88E5); color:#fff; border-bottom-right-radius:5px; }
    .b-them { background:#F1F5F9; color:#0F172A; border-bottom-left-radius:5px; }

    .empty { text-align:center; padding:48px 20px; color:#94A3B8; }
    .empty svg { width:48px; height:48px; margin-bottom:12px; opacity:.45; }
</style>

<div class="msg-wrap">

    <div style="margin-bottom:22px;">
        <div class="msg-h1">Messages</div>
        <div class="msg-sub">Chat with your teachers — send a question and view their replies.</div>
    </div>

    @if(session('success'))
        <div class="msg-flash">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Compose --}}
    <div class="card">
        <div class="card-title"><span class="dot"></span> New message</div>

        <form action="{{ route('student.messages.store') }}" method="POST" novalidate>
            @csrf
            <div style="margin-bottom:16px;">
                <label for="teacher_id" class="lbl">To</label>
                <div class="sel-wrap">
                    <select name="teacher_id" id="teacher_id" required class="inp @error('teacher_id') err @enderror">
                        <option value="">Select a teacher…</option>
                        @foreach(($teachers ?? []) as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
                @error('teacher_id')<p class="err-txt">{{ $message }}</p>@enderror
                @if(($teachers ?? collect())->isEmpty())
                    <p class="warn-txt">No teachers yet — make sure you're enrolled in a section.</p>
                @endif
            </div>

            <div style="margin-bottom:18px;">
                <label for="body" class="lbl">Message</label>
                <textarea name="body" id="body" required class="inp @error('body') err @enderror" placeholder="Type your message…">{{ old('body') }}</textarea>
                @error('body')<p class="err-txt">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="send-btn">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                Send Message
            </button>
        </form>
    </div>

    {{-- Conversation --}}
    <div class="card">
        <div class="card-title"><span class="dot" style="background:#16A34A;"></span> Conversation</div>

        @forelse(($messages ?? []) as $m)
            @php
                $mine = $m->sender_id === auth()->id();
                $who  = $mine ? 'You' : ($m->sender->name ?? 'Teacher');
                $ini  = strtoupper(mb_substr($who === 'You' ? (auth()->user()->name ?? 'Y') : $who, 0, 1));
                $palette = ['#2563EB','#16A34A','#9333EA','#EA580C','#0891B2','#DB2777'];
                $av = $mine ? '#1E88E5' : $palette[($m->sender_id ?? 0) % count($palette)];
            @endphp
            <div class="row {{ $mine ? 'mine' : '' }}">
                <div class="avatar" style="background:{{ $av }};">{{ $ini }}</div>
                <div class="bubble {{ $mine ? 'b-mine' : 'b-them' }}">
                    <div class="nm">{{ $who }}</div>
                    <div class="bd">{{ $m->body }}</div>
                    <div class="tm">{{ $m->created_at?->diffForHumans() }}</div>
                </div>
            </div>
        @empty
            <div class="empty">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l.8-4A7.94 7.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <div style="font-size:14px;font-weight:600;color:#475569;">No messages yet</div>
                <div style="font-size:12.5px;margin-top:3px;">Send one above to start the conversation.</div>
            </div>
        @endforelse
    </div>

</div>
@endsection