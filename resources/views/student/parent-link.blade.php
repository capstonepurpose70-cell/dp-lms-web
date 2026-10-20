@extends('layouts.app')
@section('title', 'Parent')

@section('content')
<style>
    /* Design tokens ng layouts/app.blade.php — walang bagong Tailwind class. */
    .pl-wrap { max-width: 720px; margin: 0 auto; }
    .pl-title { font-size: 24px; font-weight: 700; letter-spacing: -.02em; color: var(--text-1); }
    .pl-sub   { font-size: 13.5px; color: var(--text-2); margin: 4px 0 22px; line-height: 1.6; }

    .pl-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--r-lg); box-shadow: var(--shadow-sm); padding: 22px;
        margin-bottom: 16px;
    }
    .pl-row { display: flex; align-items: center; gap: 14px; }
    .pl-avatar {
        width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0;
        background: var(--accent-lt); color: var(--accent);
        display: flex; align-items: center; justify-content: center; font-weight: 700;
    }
    .pl-name { font-size: 15px; font-weight: 700; color: var(--text-1); }
    .pl-meta { font-size: 13px; color: var(--text-2); margin-top: 2px; }

    .pl-pill { display: inline-block; font-size: 11.5px; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
    .pl-pill-ok   { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }
    .pl-pill-wait { background: #fffbeb; color: #b45309; border: 1px solid #fcd34d; }

    .pl-note { font-size: 13.5px; color: var(--text-2); line-height: 1.65; }
    .pl-note strong { color: var(--text-1); }

    .pl-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        min-height: 44px; padding: 10px 18px; border-radius: var(--r-md); border: none; cursor: pointer;
        background: var(--accent); color: #fff; font-size: 14px; font-weight: 700; font-family: inherit;
    }
    .pl-btn:hover { filter: brightness(.95); }
    .pl-btn:focus-visible { outline: 3px solid var(--accent); outline-offset: 2px; }

    .pl-err { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; border-radius: var(--r-md); padding: 11px 14px; font-size: 13.5px; margin-bottom: 16px; }

    /* Code + QR */
    .pl-code-grid { display: grid; grid-template-columns: auto 1fr; gap: 22px; align-items: center; }
    .pl-qr { background: #fff; border: 1px solid var(--border); border-radius: var(--r-md); padding: 10px; width: 220px; }
    .pl-qr svg { display: block; width: 100%; height: auto; }
    .pl-code-label { font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--text-3); }
    .pl-code {
        font-family: ui-monospace, 'Cascadia Mono', Consolas, monospace;
        font-size: 32px; font-weight: 800; letter-spacing: .12em; color: var(--text-1); margin: 4px 0 6px;
    }
    .pl-steps { margin: 12px 0 0 18px; font-size: 13.5px; color: var(--text-2); line-height: 1.8; }
    .pl-url { font-size: 12px; color: var(--text-3); word-break: break-all; margin-top: 10px; }

    @media (max-width: 640px) {
        .pl-code-grid { grid-template-columns: 1fr; justify-items: center; text-align: center; }
        .pl-steps { text-align: left; }
    }
</style>

<div class="pl-wrap">
    <h1 class="pl-title">Parent</h1>
    <p class="pl-sub">Link your parent or guardian's account so they can see your grades and records.</p>

    @if($errors->any())
        <div class="pl-err" role="alert">{{ $errors->first() }}</div>
    @endif

    @if(!$student->studentProfile)
        {{-- Not enrolled yet — no parent names to match against --}}
        <div class="pl-card">
            <p class="pl-note">
                Your <strong>enrollment must be approved</strong> before you can link a parent.
                We use the mother, father, or guardian names from your enrollment form to verify them.
            </p>
            <p style="margin-top:14px;"><a class="pl-btn" href="{{ route('student.enroll') }}">Go to enrollment form</a></p>
        </div>

    @elseif($link)
        {{-- New code — shown ONLY now, never stored in plain text --}}
        <div class="pl-card">
            <div class="pl-code-grid">
                <div class="pl-qr" role="img" aria-label="QR code for your parent">{!! $link['qr_svg'] !!}</div>
                <div>
                    <div class="pl-code-label">{{ $link['purpose'] === 'link' ? 'Code for your parent' : 'Recovery code' }}</div>
                    <div class="pl-code">{{ $link['code'] }}</div>
                    <div class="pl-meta">Valid until {{ $link['expires_at']->timezone(config('app.timezone'))->format('M j, g:i A') }} · can be used once</div>
                    <ol class="pl-steps">
                        @if($link['purpose'] === 'link')
                            <li>Your parent scans the QR code with <strong>their own phone</strong>.</li>
                            <li>They enter their name, mobile number, and a password.</li>
                            <li>If their name matches your enrollment form, they can see your records right away.</li>
                        @else
                            <li>Your parent scans the QR code, or opens "Recover" on the sign-in page.</li>
                            <li>They enter the code, their mobile number, and a new password.</li>
                        @endif
                    </ol>
                    <div class="pl-url">{{ $link['url'] }}</div>
                </div>
            </div>
        </div>
        <p class="pl-note">⚠️ Only give this code to your parent or guardian. Don't post or share it.</p>

    @elseif($parent)
        <div class="pl-card">
            <div class="pl-row">
                <div class="pl-avatar" aria-hidden="true">{{ strtoupper(mb_substr($parent->name, 0, 1)) }}</div>
                <div style="flex:1;min-width:0;">
                    <div class="pl-name">{{ $parent->name }}</div>
                    <div class="pl-meta">Linked parent</div>
                </div>
                @if($parent->status === 'approved')
                    <span class="pl-pill pl-pill-ok">Active</span>
                @else
                    <span class="pl-pill pl-pill-wait">Waiting for admin</span>
                @endif
            </div>
        </div>

        @if($parent->status === 'approved')
            <div class="pl-card">
                <p class="pl-note">Did your parent forget their password or get a new phone? Create a <strong>recovery code</strong>.</p>
                <form method="POST" action="{{ route('student.parent-link.store') }}" style="margin-top:14px;">
                    @csrf
                    <button type="submit" class="pl-btn">Create recovery code</button>
                </form>
            </div>
        @else
            <p class="pl-note">The name didn't match your enrollment form, so the school admin will verify it first.</p>
        @endif

        <p class="pl-note" style="margin-top:8px;">Don't recognize this person? Report it to your adviser or the school admin right away.</p>

    @else
        <div class="pl-card">
            <p class="pl-note">
                No parent is linked yet. Create a <strong>one-time code</strong> and let your parent scan it.
                It's valid for 24 hours and can be used once.
            </p>
            <form method="POST" action="{{ route('student.parent-link.store') }}" style="margin-top:14px;">
                @csrf
                <button type="submit" class="pl-btn">Create code for my parent</button>
            </form>
        </div>
    @endif
</div>
@endsection
