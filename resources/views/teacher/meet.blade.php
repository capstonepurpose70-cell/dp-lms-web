@extends('layouts.teacher')
@section('title', 'Google Meet')

@section('content')
<style>
    .mt-list { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); }
    .mt-row { display: grid; grid-template-columns: minmax(170px, 1fr) minmax(0, 1.5fr) auto; gap: 12px 16px; align-items: start; padding: 16px 18px; }
    .mt-row + .mt-row { border-top: 1px solid var(--border); }
    .mt-subject { font-size: 14.5px; font-weight: 700; color: var(--text-1); line-height: 1.35; }
    .mt-section { font-size: 13px; color: var(--text-2); margin-top: 2px; }
    .mt-in { width: 100%; height: 42px; padding: 0 12px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 14px; background: var(--surface); color: var(--text-1); }
    .mt-in:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .15); }
    .mt-in[aria-invalid="true"] { border-color: #DC2626; }
    .mt-err { font-size: 12.5px; color: #B91C1C; margin-top: 6px; line-height: 1.45; }
    .mt-actions { display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; }
    .mt-actions .tp-btn { min-height: 42px; }
    .mt-row [hidden] { display: none; }
    .mt-empty { padding: 40px 20px; text-align: center; font-size: 14px; color: var(--text-2); line-height: 1.55; }
    @media (max-width: 760px) {
        .mt-row { grid-template-columns: 1fr; padding: 14px 16px; }
        .mt-actions { justify-content: flex-start; }
        .mt-actions .tp-btn { flex: 1 1 auto; justify-content: center; }
    }
</style>

<div class="tp-page">
    <div class="tp-head">
        <div>
            <h1 class="tp-title">Google Meet</h1>
            <p class="tp-sub">Paste the Meet link of each class. Students of that section see it on their dashboard and get a notification.</p>
        </div>
    </div>

    @if ($classes->isEmpty())
        <div class="mt-list"><div class="mt-empty">You have no classes yet. The admin assigns your classes in <b>Class Schedules</b>.</div></div>
    @else
        <div class="mt-list" id="mtList">
            @foreach ($classes as $class)
                @php $label = "{$class['subject']}, Grade {$class['grade_level']} {$class['section']}"; @endphp
                <form class="mt-row" method="POST" action="{{ route('teacher.meet.store') }}" data-remove="{{ route('teacher.meet.destroy') }}" data-label="{{ $label }}" novalidate>
                    @csrf
                    <input type="hidden" name="class" value="{{ $class['class'] }}">
                    <div>
                        <p class="mt-subject">{{ $class['subject'] }}</p>
                        <p class="mt-section">Grade {{ $class['grade_level'] }} {{ $class['section'] }}</p>
                    </div>
                    <div>
                        <input class="mt-in" type="url" name="url" value="{{ $class['url'] }}" maxlength="255" autocomplete="off" spellcheck="false"
                               placeholder="Paste the Meet link" aria-label="Google Meet link for {{ $label }}">
                        <p class="mt-err" role="alert" hidden></p>
                    </div>
                    <div class="mt-actions">
                        <button type="submit" class="tp-btn">Publish</button>
                        <a class="tp-btn ghost js-open" href="{{ $class['url'] }}" target="_blank" rel="noopener noreferrer" @if (!$class['url']) hidden @endif>Open</a>
                        <button type="button" class="tp-btn danger js-remove" @if (!$class['url']) hidden @endif>Remove</button>
                    </div>
                </form>
            @endforeach
        </div>
    @endif
</div>

<script>
(function () {
    var list = document.getElementById('mtList');
    if (!list) return;

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        }).then(function (res) {
            return res.json().then(function (data) { return { ok: res.ok, data: data }; });
        });
    }

    function parts(form) {
        return {
            input: form.querySelector('.mt-in'),
            error: form.querySelector('.mt-err'),
            open: form.querySelector('.js-open'),
            remove: form.querySelector('.js-remove'),
            submit: form.querySelector('[type=submit]')
        };
    }

    function showError(p, text) {
        p.error.textContent = text || '';
        p.error.hidden = !text;
        if (text) p.input.setAttribute('aria-invalid', 'true');
        else p.input.removeAttribute('aria-invalid');
    }

    function showLink(p, url) {
        p.input.value = url || '';
        p.open.hidden = !url;
        p.remove.hidden = !url;
        if (url) p.open.href = url;
        else p.open.removeAttribute('href');
    }

    var OFFLINE = 'Could not reach the server. Check your connection and try again.';

    list.addEventListener('submit', function (e) {
        e.preventDefault();
        var form = e.target, p = parts(form);
        showError(p, '');
        p.submit.disabled = true;
        post(form.action, new FormData(form)).then(function (res) {
            if (res.ok) {
                showLink(p, res.data.url);
                window.dpToast(res.data.message, 'success');
            } else {
                showError(p, (res.data.errors && res.data.errors.url && res.data.errors.url[0]) || res.data.message);
            }
        }).catch(function () { showError(p, OFFLINE); }).finally(function () { p.submit.disabled = false; });
    });

    list.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-remove');
        if (!btn) return;
        var form = btn.closest('form'), p = parts(form);
        window.dpConfirm({
            title: 'Remove this link?',
            text: form.dataset.label + ' will no longer see a Join button.',
            ok: 'Remove link',
            danger: true
        }).then(function (yes) {
            if (!yes) return;
            var body = new FormData();
            body.append('_token', form.querySelector('[name=_token]').value);
            body.append('_method', 'DELETE');
            body.append('class', form.querySelector('[name=class]').value);
            showError(p, '');
            post(form.dataset.remove, body).then(function (res) {
                if (res.ok) {
                    showLink(p, '');
                    window.dpToast(res.data.message, 'success');
                } else {
                    showError(p, res.data.message);
                }
            }).catch(function () { showError(p, OFFLINE); });
        });
    });
})();
</script>
@endsection
