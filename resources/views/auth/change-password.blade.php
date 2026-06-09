<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password — DP-LMS</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<style>
:root {
  --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
  --red:      #e63946;
  --red-dim:  #c1121f;
  --red-glow: rgba(230,57,70,.18);
}

*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }

body {
  min-height:100vh;
  font-family:'Segoe UI',system-ui,sans-serif;
  display:flex; align-items:center; justify-content:center;
  padding:24px 16px;
  background:url('/images/bg.jpg') center/cover no-repeat;
  position:relative; overflow:hidden; opacity:0;
}

body::before {
  content:''; position:fixed; inset:0;
  background:inherit; transform:scale(1.05); z-index:0;
}
body::after {
  content:''; position:fixed; inset:0;
  background:rgba(0,0,0,.65); z-index:0;
}

.wrapper { position:relative; z-index:1; width:100%; max-width:420px; }

/* ── Card ── */
.card {
  background:rgba(248,248,255,.97);
  border-radius:22px;
  box-shadow:0 4px 6px rgba(0,0,0,.06), 0 24px 64px rgba(0,0,0,.28),
             0 0 0 1px rgba(255,255,255,.1);
  padding:36px 32px 30px;
  backdrop-filter:blur(10px);
  opacity:0; transform:translateY(28px) scale(.98);
}

/* ── Header ── */
.card-kicker {
  display:flex; align-items:center; gap:.5rem;
  font-size:11px; font-weight:700; letter-spacing:.18em;
  text-transform:uppercase; color:var(--red);
  margin-bottom:.6rem;
}
.kicker-dot {
  width:6px; height:6px; border-radius:50%;
  background:var(--red); box-shadow:0 0 8px var(--red);
  animation:blink 2.2s ease-in-out infinite;
}
@keyframes blink{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.4;transform:scale(.75)}}

.card-title {
  font-size:20px; font-weight:800; color:#111827;
  letter-spacing:-.025em; margin-bottom:.3rem;
}
.card-sub  { font-size:13px; color:#6b7280; margin-bottom:24px; line-height:1.5; }

/* ── Labels & Inputs ── */
label {
  display:block; font-size:11px; font-weight:700;
  text-transform:uppercase; letter-spacing:.06em;
  color:#374151; margin-bottom:6px;
}
.form-group { margin-bottom:18px; opacity:0; transform:translateY(8px); }

.input-wrapper { position:relative; }
.input-icon {
  position:absolute; left:14px; top:50%;
  transform:translateY(-50%); color:#9ca3af;
  pointer-events:none;
  transition:color 200ms var(--ease-out);
}
.input-icon svg { width:18px; height:18px; display:block; }
.input-wrapper:focus-within .input-icon { color:var(--red); }

input[type="password"] {
  width:100%; border:1.5px solid #e5e7eb; border-radius:10px;
  padding:11px 44px 11px 42px; font-size:14px; font-weight:500;
  color:#111827; background:#f9fafb; outline:none;
  font-family:inherit;
  transition:border-color 180ms var(--ease-out),
             box-shadow 180ms var(--ease-out),
             background 180ms var(--ease-out);
}
input[type="password"]:focus {
  border-color:var(--red);
  box-shadow:0 0 0 3px var(--red-glow);
  background:#fff;
}
input.is-error { border-color:#fca5a5 !important; background:#fff5f5 !important; }
input.is-valid { border-color:#86efac !important; background:#f0fdf4 !important; }

.error-msg { font-size:11px; color:#ef4444; margin-top:4px; font-weight:500; }

/* ── Password toggle ── */
.pw-toggle {
  position:absolute; right:12px; top:50%;
  transform:translateY(-50%);
  background:none; border:none; cursor:pointer;
  color:#9ca3af; padding:4px; display:flex;
  transition:color 180ms var(--ease-out); z-index:2;
}
@media(hover:hover) and (pointer:fine){.pw-toggle:hover{color:var(--red);}}
.pw-toggle svg { width:18px; height:18px; }
.eye-off { display:none; }

/* ── Strength ── */
.pw-strength { margin-top:.5rem; }
.pw-segs { display:flex; gap:4px; margin-bottom:.35rem; }
.pw-seg {
  height:3px; flex:1; border-radius:2px;
  background:#e5e7eb;
  transition:background .3s;
}
.pw-strength-lbl { font-size:.7rem; height:14px; transition:color .3s; }

/* ── Match hint ── */
.match-hint {
  font-size:.72rem; color:#9ca3af; margin-top:.45rem;
  display:flex; align-items:center; gap:.35rem; min-height:16px;
}
.match-hint svg { width:12px; height:12px; flex-shrink:0; }
.hint-ok  { color:#15803d !important; }
.hint-err { color:#ef4444 !important; }

/* ── Submit ── */
.btn-submit {
  width:100%;
  background:linear-gradient(135deg,var(--red),var(--red-dim));
  color:#fff; border:none; border-radius:11px;
  padding:13px; font-size:15px; font-weight:700;
  cursor:pointer; margin-top:4px;
  position:relative; overflow:hidden;
  display:flex; align-items:center; justify-content:center; gap:8px;
  transition:background 220ms var(--ease-out), box-shadow 220ms var(--ease-out);
  animation:neon-red 2.5s ease-in-out infinite;
  letter-spacing:.02em;
  font-family:inherit;
}
@keyframes neon-red {
  0%,100%{box-shadow:0 0 8px rgba(230,57,70,.6),0 0 20px rgba(230,57,70,.35),0 4px 16px rgba(230,57,70,.3);}
  50%{box-shadow:0 0 14px rgba(230,57,70,.8),0 0 35px rgba(230,57,70,.45),0 4px 16px rgba(230,57,70,.3);}
}
.btn-submit::before {
  content:''; position:absolute; top:0; left:-100%;
  width:60%; height:100%;
  background:linear-gradient(120deg,transparent,rgba(255,255,255,.16),transparent);
  transition:left 480ms var(--ease-out);
}
@media(hover:hover) and (pointer:fine){
  .btn-submit:hover::before{left:150%;}
}
.btn-submit:disabled { opacity:.7; cursor:not-allowed; animation:none; }

/* ── Spinner ── */
@keyframes spin{to{transform:rotate(360deg)}}
.spinner {
  width:18px; height:18px;
  border:2.5px solid rgba(255,255,255,.35);
  border-top-color:#fff; border-radius:50%;
  animation:spin .65s linear infinite; flex-shrink:0; display:none;
}

/* ── Alert ── */
.alert-err {
  background:#fef2f2; border:1.5px solid #fecaca;
  border-radius:10px; padding:11px 14px;
  font-size:13px; color:#b91c1c; font-weight:500;
  margin-bottom:18px; line-height:1.6;
}
.alert-info {
  background:#eff6ff; border:1.5px solid #bfdbfe;
  border-radius:10px; padding:11px 14px;
  font-size:13px; color:#1d4ed8; font-weight:500;
  margin-bottom:18px; line-height:1.6;
}

/* ── Copyright ── */
.copyright {
  text-align:center; font-size:11px;
  color:rgba(255,255,255,.45); margin-top:18px; opacity:0;
}

@media(max-width:480px){
  .card{padding:28px 22px 24px; border-radius:18px;}
}
@media(prefers-reduced-motion:reduce){
  .btn-submit{animation:none;box-shadow:0 4px 16px rgba(230,57,70,.3);}
  .kicker-dot{animation:none;}
}
</style>
</head>
<body>
<div class="wrapper">

  <div class="card" id="changeCard">

    @if(session('error'))
      <div class="alert-err">{{ session('error') }}</div>
    @endif

    @if(session('info'))
      <div class="alert-info">{{ session('info') }}</div>
    @endif

    <div class="card-kicker" id="kicker">
      <span class="kicker-dot"></span>
      Security Required
    </div>
    <h1 class="card-title">Change Your Password</h1>
    <p class="card-sub">
      Welcome, <strong>{{ auth()->user()->name }}</strong>! You must set a new password before
      accessing your DP-LMS account.
    </p>

    <form method="POST" action="{{ route('password.change.submit') }}" id="changeForm" novalidate>
      @csrf

      {{-- New Password ── --}}
      <div class="form-group" id="fg1">
        <label>New Password <span style="color:var(--red)">*</span></label>
        <div class="input-wrapper">
          <span class="input-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
            </svg>
          </span>
          <input type="password" name="password" id="pwInput"
                 placeholder="Min. 8 characters"
                 class="{{ $errors->has('password') ? 'is-error' : '' }}"
                 oninput="updateStrength(this.value)" required>
          <button type="button" class="pw-toggle" onclick="togglePw('pwInput',this)" tabindex="-1">
            <svg class="eye-on" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <svg class="eye-off" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
            </svg>
          </button>
        </div>
        @error('password')
          <p class="error-msg">{{ $message }}</p>
        @enderror
        <div class="pw-strength">
          <div class="pw-segs">
            <div class="pw-seg" id="seg1"></div>
            <div class="pw-seg" id="seg2"></div>
            <div class="pw-seg" id="seg3"></div>
            <div class="pw-seg" id="seg4"></div>
          </div>
          <p class="pw-strength-lbl" id="strengthLbl"></p>
        </div>
      </div>

      {{-- Confirm ── --}}
      <div class="form-group" id="fg2">
        <label>Confirm Password <span style="color:var(--red)">*</span></label>
        <div class="input-wrapper">
          <span class="input-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
            </svg>
          </span>
          <input type="password" name="password_confirmation" id="pwConfirm"
                 placeholder="Re-enter password"
                 oninput="checkMatch()" required>
          <button type="button" class="pw-toggle" onclick="togglePw('pwConfirm',this)" tabindex="-1">
            <svg class="eye-on" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <svg class="eye-off" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
            </svg>
          </button>
        </div>
        <p class="match-hint" id="matchHint"></p>
      </div>

      <button type="submit" class="btn-submit" id="submitBtn">
        <svg id="btnIcon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
             style="width:18px;height:18px;flex-shrink:0;">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="spinner" id="btnSpinner"></div>
        <span id="btnText">Save New Password</span>
      </button>
    </form>

  </div>

  <p class="copyright" id="copyright">
    &copy; {{ date('Y') }} Sto. Domingo National High School &middot; DP-LMS
  </p>
</div>

<script>
function togglePw(id, btn) {
  const inp  = document.getElementById(id);
  const on   = btn.querySelector('.eye-on');
  const off  = btn.querySelector('.eye-off');
  const show = inp.type === 'password';
  inp.type          = show ? 'text'     : 'password';
  on.style.display  = show ? 'none'     : '';
  off.style.display = show ? ''         : 'none';
}

function updateStrength(val) {
  let s = 0;
  if (val.length >= 8)           s++;
  if (/[A-Z]/.test(val))         s++;
  if (/[0-9]/.test(val))         s++;
  if (/[^A-Za-z0-9]/.test(val)) s++;
  const colors = ['#e63946','#f97316','#eab308','#22c55e'];
  const labels = ['Weak','Fair','Good','Strong'];
  [1,2,3,4].forEach(i => {
    document.getElementById('seg'+i).style.background =
      i <= s ? colors[s-1] : '#e5e7eb';
  });
  const lbl = document.getElementById('strengthLbl');
  lbl.textContent  = val ? (labels[s-1] || 'Too short') : '';
  lbl.style.color  = s > 0 ? colors[s-1] : '#e63946';
  checkMatch();
}

function checkMatch() {
  const pw  = document.getElementById('pwInput').value;
  const cpw = document.getElementById('pwConfirm').value;
  const lbl = document.getElementById('matchHint');
  const inp = document.getElementById('pwConfirm');
  if (!cpw) { lbl.innerHTML=''; inp.classList.remove('is-error','is-valid'); return; }
  if (pw === cpw) {
    lbl.innerHTML = '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg> Passwords match';
    lbl.className = 'match-hint hint-ok';
    inp.classList.remove('is-error'); inp.classList.add('is-valid');
  } else {
    lbl.innerHTML = '✕ Passwords do not match';
    lbl.className = 'match-hint hint-err';
    inp.classList.remove('is-valid'); inp.classList.add('is-error');
  }
}

document.getElementById('changeForm').addEventListener('submit', function(e) {
  const pw  = document.getElementById('pwInput').value;
  const cpw = document.getElementById('pwConfirm').value;
  if (pw !== cpw) {
    e.preventDefault();
    document.getElementById('matchHint').innerHTML = '✕ Passwords do not match.';
    document.getElementById('matchHint').className = 'match-hint hint-err';
    document.getElementById('pwConfirm').scrollIntoView({behavior:'smooth',block:'center'});
    return;
  }
  const btn  = document.getElementById('submitBtn');
  const icon = document.getElementById('btnIcon');
  const spin = document.getElementById('btnSpinner');
  const txt  = document.getElementById('btnText');
  btn.disabled       = true;
  icon.style.display = 'none';
  spin.style.display = 'block';
  txt.textContent    = 'Saving…';
});

document.addEventListener('DOMContentLoaded', () => {
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (reduced) {
    gsap.set('body', { opacity:1 });
    gsap.to(['#changeCard','#copyright'], { opacity:1, duration:.3, stagger:.06 });
    gsap.to('.form-group', { opacity:1, y:0, duration:.3, stagger:.07 });
  } else {
    const tl = gsap.timeline({ defaults:{ ease:'power3.out' } });
    tl.to('body', { opacity:1, duration:.35 })
      .to('#changeCard', { opacity:1, y:0, scale:1, duration:.5, ease:'power3.out' }, '-=.2')
      .to('.form-group', { opacity:1, y:0, stagger:.08, duration:.38 }, '-=.25')
      .to('#copyright',  { opacity:1, duration:.5 }, '-=.1');

    const btn = document.getElementById('submitBtn');
    const isP = window.matchMedia('(hover:hover) and (pointer:fine)').matches;
    if (isP) {
      btn.addEventListener('mouseenter', () => gsap.to(btn, { y:-2, duration:.22, ease:'power2.out' }));
      btn.addEventListener('mouseleave', () => gsap.to(btn, { y:0,  duration:.22, ease:'power2.out' }));
    }
    btn.addEventListener('mousedown',  () => gsap.to(btn, { scale:.97, duration:.1 }));
    btn.addEventListener('mouseup',    () => gsap.to(btn, { scale:1,   duration:.12 }));
  }
});
</script>
</body>
</html>