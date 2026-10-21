{{--
    Offline fallback for GSAP (loaded from a CDN). Include it RIGHT AFTER the GSAP <script src>.
    Several pages start with body { opacity:0 } and rely on GSAP to reveal them — without internet
    they would stay blank (including the login pages). If GSAP is missing, this tiny stand-in jumps
    every animation to its end state and still runs onComplete / delayedCall callbacks
    (the login pages redirect from onComplete), so the page works, just without animation.
--}}
<script>
(function (install) {
    // GSAP loaded with "defer" runs after this inline script — check once the DOM is parsed
    // (our listener is registered first, so it runs before the page's own DOMContentLoaded code).
    @if (!empty($deferred))
        document.addEventListener('DOMContentLoaded', function () { window.gsap || install(); });
    @else
        window.gsap || install();
    @endif
})(function () {
    function list(t) {
        if (!t) return [];
        if (typeof t === 'string') {
            try { return Array.prototype.slice.call(document.querySelectorAll(t)); } catch (e) { return []; }
        }
        if (t.nodeType || t === window) return [t];
        if (typeof t.length === 'number') {
            var out = [];
            for (var i = 0; i < t.length; i++) out = out.concat(list(t[i]));
            return out;
        }
        return [t];
    }
    function px(v) { return typeof v === 'number' ? v + 'px' : String(v); }
    function apply(t, v) {
        v = v || {};
        list(t).forEach(function (el) {
            if (!el || !el.style) return;
            if (v.opacity !== undefined) el.style.opacity = v.opacity;
            if (v.autoAlpha !== undefined) {
                el.style.opacity = v.autoAlpha;
                el.style.visibility = Number(v.autoAlpha) ? 'visible' : 'hidden';
            }
            var tr = [];
            if (v.x !== undefined) tr.push('translateX(' + px(v.x) + ')');
            if (v.y !== undefined) tr.push('translateY(' + px(v.y) + ')');
            if (v.scale !== undefined) tr.push('scale(' + v.scale + ')');
            if (v.rotation !== undefined) tr.push('rotate(' + v.rotation + 'deg)');
            if (tr.length) el.style.transform = tr.join(' ');
            if (v.display !== undefined) el.style.display = v.display;
        });
    }
    function later(fn, args, ms) {
        if (typeof fn !== 'function') return null;
        return setTimeout(function () {
            try { fn.apply(null, args || []); } catch (e) { if (window.console) console.error(e); }
        }, ms || 0);
    }
    function done(v) { if (v) later(v.onComplete, v.onCompleteParams); }

    function stub() {
        var s = {};
        ['play', 'pause', 'resume', 'reverse', 'restart', 'seek', 'progress', 'timeScale', 'kill', 'invalidate', 'eventCallback']
            .forEach(function (m) { s[m] = function () { return s; }; });
        s.then = function (fn) { later(fn); return s; };
        s.isActive = function () { return false; };
        return s;
    }

    function timeline(opts) {
        var tl = stub();
        tl.to     = function (t, v) { apply(t, v); done(v); return tl; };
        tl.set    = tl.to;
        tl.from   = function (t, v) { done(v); return tl; };            // natural state = end state
        tl.fromTo = function (t, a, b) { apply(t, b); done(b); return tl; };
        tl.add    = function (x) { if (typeof x === 'function') later(x); return tl; };
        tl.call   = function (fn, args) { later(fn, args); return tl; };
        tl.addLabel = function () { return tl; };
        done(opts);
        return tl;
    }

    window.gsap = {
        to:       function (t, v) { apply(t, v); done(v); return stub(); },
        set:      function (t, v) { apply(t, v); return stub(); },
        from:     function (t, v) { done(v); return stub(); },
        fromTo:   function (t, a, b) { apply(t, b); done(b); return stub(); },
        timeline: timeline,
        delayedCall: function (sec, fn, args) {
            var id = later(fn, args, (Number(sec) || 0) * 1000);
            var s = stub(); s.kill = function () { clearTimeout(id); return s; }; return s;
        },
        killTweensOf:   function () {},
        registerPlugin: function () {},
        config:         function () {},
        defaults:       function () {},
        isTweening:     function () { return false; },
        utils:          { toArray: list }
    };
});
</script>
