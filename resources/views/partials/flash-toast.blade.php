{{--
    Toasts: a short message that slides in at the top, stays about 3 seconds and slides away.
    Include once per layout, right before </body>. Messages come from partials/flash (session flash)
    or from scripts: window.dpToast('Saved.', 'success').
    Pages swapped in without a reload (admin) are watched too, so their flash shows the same way.
--}}
<div class="dpt-stack" id="dpToasts"></div>

<style>
    .dpt-stack { position: fixed; top: 16px; left: 50%; z-index: 10000; display: grid; gap: 8px; width: min(420px, calc(100vw - 32px));
                 transform: translateX(-50%); pointer-events: none; }
    .dpt { --dpt-c: #2563EB; position: relative; display: flex; align-items: flex-start; gap: 10px; padding: 12px 10px 12px 14px; overflow: hidden;
           border-radius: 12px; border: 1px solid #E2E8F0; background: #fff; color: #0F172A; pointer-events: auto;
           box-shadow: 0 12px 32px -8px rgba(15, 23, 42, .22), 0 2px 6px rgba(15, 23, 42, .06);
           font-family: inherit; font-size: 13.5px; font-weight: 600; line-height: 1.45;
           opacity: 0; transform: translateY(-14px) scale(.98);
           transition: opacity .22s ease, transform .32s cubic-bezier(.16, 1, .3, 1); }
    .dpt.in { opacity: 1; transform: none; }
    .dpt.out { opacity: 0; transform: translateY(-10px); transition-duration: .16s, .18s; }
    .dpt-success { --dpt-c: #16A34A; }
    .dpt-error   { --dpt-c: #DC2626; }
    .dpt-warning { --dpt-c: #D97706; }
    .dpt-ic { flex: none; width: 20px; height: 20px; color: var(--dpt-c); }
    .dpt p { flex: 1; margin: 0; padding-top: 1px; text-wrap: pretty; overflow-wrap: anywhere; }
    .dpt-x { flex: none; display: grid; place-items: center; width: 28px; height: 28px; margin: -4px -2px -4px 0; border: 0; border-radius: 8px;
             background: none; color: #94A3B8; cursor: pointer; }
    .dpt-x:hover { background: #F1F5F9; color: #475569; }
    .dpt-x:focus-visible { outline: 2px solid var(--dpt-c); outline-offset: 1px; }
    .dpt-x svg { width: 14px; height: 14px; }
    .dpt-bar { position: absolute; left: 0; bottom: 0; height: 2px; width: 100%; background: var(--dpt-c); opacity: .55; transform-origin: left;
               animation: dpt-time linear forwards; }
    .dpt:hover .dpt-bar, .dpt:focus-within .dpt-bar { animation-play-state: paused; }
    @keyframes dpt-time { to { transform: scaleX(0); } }

    [data-theme="dark"] .dpt { background: #1E293B; border-color: #334155; color: #F1F5F9; }
    [data-theme="dark"] .dpt-x:hover { background: #334155; color: #E2E8F0; }

    @media (prefers-reduced-motion: reduce) {
        .dpt, .dpt.out { transform: none; transition: opacity .15s ease; }
        .dpt-bar { display: none; }
    }
</style>

<script>
(function () {
    if (window.dpToast) return;
    var stack = document.getElementById('dpToasts');
    var icons = {
        success: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        error:   '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        warning: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>',
        info:    '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
    };

    // About 3 seconds; a long message gets a little more time to be read. Hover or focus pauses it.
    function duration(text) {
        return Math.min(7000, 3000 + Math.max(0, text.length - 90) * 35);
    }

    window.dpToast = function (message, type) {
        var text = String(message || '').trim();
        if (!text || !stack) return;
        type = icons[type] ? type : 'info';

        var t = document.createElement('div');
        t.className = 'dpt dpt-' + type;
        t.setAttribute('role', type === 'error' ? 'alert' : 'status');
        t.innerHTML = '<svg class="dpt-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">' + icons[type] + '</svg>'
            + '<p></p><button type="button" class="dpt-x" aria-label="Dismiss"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg></button><i class="dpt-bar" aria-hidden="true"></i>';
        t.querySelector('p').textContent = text;   // text only: a message can never inject markup

        // A JS timer, not the bar's animation: "reduce motion" settings shorten every animation to nothing
        var left = duration(text), started = 0, timer = null, gone = false;
        t.querySelector('.dpt-bar').style.animationDuration = left + 'ms';
        function hide() {
            if (gone) return;
            gone = true;
            clearTimeout(timer);
            t.classList.remove('in');
            t.classList.add('out');
            setTimeout(function () { t.remove(); }, 200);
        }
        function run() { if (gone || timer) return; started = Date.now(); timer = setTimeout(hide, left); }
        function pause() { if (!timer) return; clearTimeout(timer); timer = null; left -= Date.now() - started; }
        t.addEventListener('mouseenter', pause);
        t.addEventListener('mouseleave', function () { if (!t.contains(document.activeElement)) run(); });
        t.addEventListener('focusin', pause);
        t.addEventListener('focusout', function () { if (!t.matches(':hover')) run(); });
        t.querySelector('.dpt-x').addEventListener('click', hide);
        run();

        stack.appendChild(t);
        while (stack.children.length > 3) stack.firstElementChild.remove();
        requestAnimationFrame(function () { requestAnimationFrame(function () { t.classList.add('in'); }); });
    };

    function show(root) {
        var items = root.matches && root.matches('.dp-flash') ? [root] : root.querySelectorAll('.dp-flash');
        Array.prototype.forEach.call(items, function (el) {
            window.dpToast(el.textContent, el.getAttribute('data-type'));
            el.remove();
        });
    }

    show(document);
    new MutationObserver(function (changes) {
        changes.forEach(function (c) {
            c.addedNodes.forEach(function (n) { if (n.nodeType === 1) show(n); });
        });
    }).observe(document.body, { childList: true, subtree: true });
})();
</script>
