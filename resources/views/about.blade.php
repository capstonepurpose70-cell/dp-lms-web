<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About — DP-LMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts (same family as Login / Register) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --green-500: #16a34a;
            --green-600: #15803d;
            --green-700: #166534;
            --green-50:  #f0fdf4;
            --border:    #e2e8f0;
            --text-dark: #0f172a;
            --text-mid:  #334155;
            --text-muted:#64748b;
            --surface:   #f4f7f9;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', system-ui, sans-serif;
            background: var(--surface);
            color: var(--text-dark);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        .about-wrap {
            max-width: 900px;
            margin: 0 auto;
            padding: 104px 24px 56px;   /* top clearance for the fixed navbar */
        }

        /* Hero */
        .about-hero { text-align: center; margin-bottom: 34px; }
        .about-hero img {
            width: 96px; height: 96px; object-fit: contain;
            border-radius: 50%; background: #fff; padding: 10px;
            box-shadow: 0 6px 22px rgba(0,0,0,0.10);
            margin-bottom: 16px;
        }
        .about-hero .eyebrow {
            font-size: 11px; font-weight: 600; letter-spacing: 3px;
            text-transform: uppercase; color: var(--green-600); margin-bottom: 8px;
        }
        .about-hero h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 42px; font-weight: 700; line-height: 1.1;
            color: var(--text-dark); margin-bottom: 8px;
        }
        .about-hero p {
            font-size: 14px; color: var(--text-muted); max-width: 560px; margin: 0 auto;
            line-height: 1.7;
        }

        /* Card */
        .about-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 12px 34px rgba(15, 23, 42, 0.06);
            padding: 30px 30px 26px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        .about-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, var(--green-500), var(--green-700));
        }
        .about-card h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 24px; font-weight: 700; color: var(--text-dark);
            margin-bottom: 14px;
        }
        .about-card p { font-size: 14px; color: var(--text-mid); line-height: 1.75; margin-bottom: 14px; }

        .feat { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
        .feat svg { width: 20px; height: 20px; color: var(--green-600); flex-shrink: 0; margin-top: 2px; }
        .feat span { font-size: 14px; color: var(--text-mid); line-height: 1.6; }

        /* Org chart */
        .org-thumb {
            display: block; width: 100%; padding: 0; margin-top: 6px;
            border: 1px solid var(--border); border-radius: 14px;
            cursor: zoom-in; overflow: hidden; background: var(--surface);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }
        .org-thumb:hover { box-shadow: 0 10px 26px rgba(15,23,42,0.10); transform: translateY(-2px); }
        .org-thumb img { display: block; width: 100%; height: auto; }
        .org-hint { font-size: 12px; color: var(--text-muted); text-align: center; margin-top: 10px; }

        /* Footer */
        .about-foot { text-align: center; margin-top: 8px; }
        .about-foot a {
            display: inline-flex; align-items: center; gap: 7px;
            color: var(--green-600); font-weight: 600; font-size: 14px; text-decoration: none;
        }
        .about-foot a:hover { text-decoration: underline; }
        .about-copy { text-align: center; font-size: 12px; color: #94a3b8; margin-top: 20px; }

        /* Fullscreen lightbox */
        .lightbox {
            position: fixed; inset: 0; z-index: 5000;
            display: flex; align-items: center; justify-content: center;
            padding: 24px;
            background: rgba(6, 12, 26, 0.92);
            opacity: 0; pointer-events: none;
            transition: opacity 0.25s ease;
        }
        .lightbox.show { opacity: 1; pointer-events: all; }
        .lightbox img {
            max-width: 96vw; max-height: 90vh;
            border-radius: 8px; box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            cursor: zoom-out;
        }
        .lightbox-close {
            position: absolute; top: 18px; right: 22px;
            width: 42px; height: 42px; border-radius: 50%;
            background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.25);
            color: #fff; font-size: 24px; line-height: 1; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }

        @media (max-width: 560px) {
            .about-wrap { padding: 92px 16px 40px; }
            .about-hero h1 { font-size: 32px; }
            .about-card { padding: 24px 20px; }
        }
    </style>
</head>
<body>
@include('partials.auth-navbar')

    <div class="about-wrap">

        {{-- Hero --}}
        <div class="about-hero">
            <img src="{{ asset('images/logo.png') }}" alt="School logo">
            <p class="eyebrow">Sto. Domingo National High School</p>
            <h1>About DP-LMS</h1>
            <p>The official Digital Portal &amp; Learning Management System — connecting
               students, teachers, and parents in one secure place.</p>
        </div>

        {{-- What is DP-LMS --}}
        <div class="about-card">
            <h2>What is DP-LMS?</h2>
            <p>DP-LMS is the digital learning hub of Sto. Domingo National High School.
               It brings classes, materials, activities, grades, and school attendance
               together in a single, easy-to-use platform for the whole school community.</p>

            <div class="feat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                <span>Learning materials, quizzes, and grades — anytime, anywhere.</span>
            </div>
            <div class="feat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>Face-recognition attendance with present, late, and absent tracking.</span>
            </div>
            <div class="feat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span>Instant notifications for announcements, activities, and results.</span>
            </div>
        </div>

        {{-- Organizational Chart --}}
        <div class="about-card">
            <h2>Organizational Chart</h2>
            <p>Meet the people who lead and support Sto. Domingo National High School.</p>

            <button type="button" class="org-thumb" id="orgOpen" aria-label="Open organizational chart">
                <img src="{{ asset('images/org-chart.png') }}" alt="Organizational Chart">
            </button>
            <p class="org-hint">Tap the chart to enlarge</p>
        </div>

        {{-- Footer --}}
        <div class="about-foot">
            <a href="{{ route('login') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Login
            </a>
        </div>

        <p class="about-copy">
            &copy; {{ date('Y') }} Sto. Domingo National High School. All rights reserved.
        </p>
    </div>

    {{-- Fullscreen org-chart viewer --}}
    <div class="lightbox" id="lightbox">
        <button type="button" class="lightbox-close" id="lightboxClose" aria-label="Close">&times;</button>
        <img src="{{ asset('images/org-chart.png') }}" alt="Organizational Chart (enlarged)">
    </div>

<script>
(function () {
    var lb      = document.getElementById('lightbox');
    var lbOpen  = document.getElementById('orgOpen');
    var lbClose = document.getElementById('lightboxClose');

    function openLb()  { if (lb) lb.classList.add('show'); }
    function closeLb() { if (lb) lb.classList.remove('show'); }

    if (lbOpen)  lbOpen.addEventListener('click', openLb);
    if (lbClose) lbClose.addEventListener('click', closeLb);
    if (lb) lb.addEventListener('click', function (e) {
        if (e.target === lb || e.target.tagName === 'IMG') closeLb();
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeLb(); });
})();
</script>
</body>
</html>