/*
 * Sideways-scroll cue for wide tables and tab rows on small screens.
 *
 * A box that scrolls sideways gives no hint that columns are hidden. When there is more to the right
 * (or left), that edge fades out; at the end of the scroll the fade goes away. Works for any element
 * with overflow-x auto/scroll; pages that are swapped in without a reload are picked up as well.
 */
(function () {
    'use strict';
    if (window.__scrollCue) return;
    window.__scrollCue = true;

    const FADE = 28;
    const style = document.createElement('style');
    style.textContent =
        '.x-cue-l, .x-cue-r { --cue-l: 0px; --cue-r: 0px;' +
        ' -webkit-mask-image: linear-gradient(to right, transparent, #000 var(--cue-l), #000 calc(100% - var(--cue-r)), transparent);' +
        ' mask-image: linear-gradient(to right, transparent, #000 var(--cue-l), #000 calc(100% - var(--cue-r)), transparent); }' +
        '.x-cue-l { --cue-l: ' + FADE + 'px; } .x-cue-r { --cue-r: ' + FADE + 'px; }';
    document.head.appendChild(style);

    const tracked = new WeakSet();

    function update(el) {
        const max = el.scrollWidth - el.clientWidth;
        el.classList.toggle('x-cue-l', max > 2 && el.scrollLeft > 2);
        el.classList.toggle('x-cue-r', max > 2 && el.scrollLeft < max - 2);
    }

    function scan() {
        // Cheap candidate set: the containers that wrap tables and tab rows
        document.querySelectorAll('table, [class*="scroll"], [class*="tabs"], [class*="table"]').forEach(function (node) {
            const el = node.tagName === 'TABLE' && getComputedStyle(node).overflowX === 'visible' ? node.parentElement : node;
            if (!el) return;
            if (tracked.has(el)) { update(el); return; }
            const ox = getComputedStyle(el).overflowX;
            if (ox !== 'auto' && ox !== 'scroll') return;
            tracked.add(el);
            el.addEventListener('scroll', function () { update(el); }, { passive: true });
            update(el);
        });
    }

    // Debounced: live pages (counters, chat) change the DOM many times a second
    let timer = 0;
    function schedule() {
        clearTimeout(timer);
        timer = setTimeout(scan, 150);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', scan); else scan();
    window.addEventListener('resize', schedule, { passive: true });
    window.addEventListener('load', schedule);
    // Admin and teacher layouts swap the page content without a reload
    new MutationObserver(schedule).observe(document.body, { childList: true, subtree: true });
})();
