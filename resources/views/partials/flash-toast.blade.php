{{--
    Turns Laravel session flash banners into smooth auto-dismissing toasts.
    Self-contained (own CSS + JS). Touches no existing markup.

    Usage: add this ONE line right before </body> in each layout you use
           (layouts/teacher.blade.php, layouts/app.blade.php, layouts/admin.blade.php):
           @include('partials.flash-toast')

    It targets the session flash banners (.flash / .alert-success / .alert-error)
    that sit at the top level of the page. Inline alerts inside forms, tables,
    cards, or modals are left untouched.
--}}

<style>
    .dptoast-box {
        position: fixed;
        top: 18px; right: 18px;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: min(92vw, 380px);
        pointer-events: none;
    }
    .dptoast-box .dptoast {
        pointer-events: auto;
        margin: 0 !important;
        width: 100%;
        border-radius: 12px !important;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.16);
        opacity: 0;
        transform: translateX(24px);
        transition: opacity 0.3s ease, transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .dptoast-box .dptoast.dptoast-in  { opacity: 1; transform: translateX(0); }
    .dptoast-box .dptoast.dptoast-out { opacity: 0; transform: translateX(24px); }

    @media (prefers-reduced-motion: reduce) {
        .dptoast-box .dptoast { transition: opacity 0.2s ease; transform: none; }
        .dptoast-box .dptoast.dptoast-out { transform: none; }
    }
</style>

<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        // Only the top-level session flash banners — never inline alerts
        // that live inside a form, table, card, or modal.
        var candidates = document.querySelectorAll('.flash, .alert-success, .alert-error');
        var flashes = [];
        candidates.forEach(function (el) {
            if (!el.closest('form, table, .card, .modal, .modal-box, .info-box')) {
                flashes.push(el);
            }
        });
        if (!flashes.length) return;

        var box = document.createElement('div');
        box.className = 'dptoast-box';
        document.body.appendChild(box);

        var VISIBLE_MS = 3000;   // stays for 3 seconds
        var ANIM_MS    = 400;    // fade/slide duration

        flashes.forEach(function (el, i) {
            el.classList.add('dptoast');
            box.appendChild(el);   // move into the fixed toast stack (keeps its colors)

            // Entrance
            requestAnimationFrame(function () {
                requestAnimationFrame(function () { el.classList.add('dptoast-in'); });
            });

            // Auto-dismiss after 3s (tiny stagger if several)
            var hide = function () {
                el.classList.remove('dptoast-in');
                el.classList.add('dptoast-out');
                setTimeout(function () {
                    el.remove();
                    if (!box.children.length) box.remove();
                }, ANIM_MS);
            };
            setTimeout(hide, VISIBLE_MS + (i * 150));

            // Let the user dismiss early by clicking the toast
            el.style.cursor = 'pointer';
            el.addEventListener('click', hide);
        });
    });
})();
</script>