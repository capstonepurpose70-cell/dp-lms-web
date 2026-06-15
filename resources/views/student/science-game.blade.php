@extends('layouts.app')
@section('title', 'Strata Rush — 3D Science Game')

@section('content')
<style>
    #sr-shell { position: relative; width: 100%; height: calc(100vh - 110px); min-height: 560px;
        border-radius: 18px; overflow: hidden; background: radial-gradient(ellipse at 50% 30%, #0a1330 0%, #03050f 70%);
        box-shadow: 0 12px 44px rgba(0,0,0,.35); font-family: 'Plus Jakarta Sans', sans-serif; }
    #sr-canvas { display: block; width: 100%; height: 100%; touch-action: none; cursor: grab; }
    #sr-canvas:active { cursor: grabbing; }
    .sr-ui { position: absolute; inset: 0; pointer-events: none; }
    .sr-glass { background: rgba(10,16,34,.74); backdrop-filter: blur(10px); border: 1px solid rgba(120,150,220,.22); }

    .sr-hint-bar { position: absolute; top: 16px; left: 50%; transform: translateX(-50%);
        color: #dbe6fb; font-size: 14px; font-weight: 600; padding: 11px 22px; border-radius: 999px; max-width: 70%; text-align: center; }

    .sr-quiz { position: absolute; top: 16px; right: 16px; width: 340px; max-width: 42%; border-radius: 18px;
        padding: 20px; color: #fff; pointer-events: auto; display: none; }
    .sr-quiz.show { display: block; animation: srIn .25s ease; }
    @keyframes srIn { from { opacity: 0; transform: translateX(14px); } to { opacity: 1; transform: none; } }
    .sr-quiz h3 { font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 800; margin: 0; }
    .sr-quiz .qcount { color: #93a7cc; font-size: 12.5px; margin: 2px 0 16px; }
    .sr-quiz .qtext { font-size: 15.5px; font-weight: 700; line-height: 1.45; margin-bottom: 16px; }
    .sr-opt { display: flex; align-items: center; gap: 12px; width: 100%; text-align: left; background: rgba(255,255,255,.05);
        border: 1.5px solid rgba(255,255,255,.12); color: #e7edf7; border-radius: 13px; padding: 13px 14px; margin-bottom: 10px;
        font-size: 14px; font-weight: 600; cursor: pointer; transition: all .15s; }
    .sr-opt .lt { width: 26px; height: 26px; border-radius: 8px; background: rgba(255,255,255,.1); display: grid; place-items: center;
        font-weight: 800; font-size: 13px; flex-shrink: 0; }
    .sr-opt:hover:not(:disabled) { border-color: #5b8def; background: rgba(91,141,239,.14); }
    .sr-opt:disabled { cursor: default; }
    .sr-opt.correct { border-color: #34d399; background: #15803d; color: #fff; }
    .sr-opt.correct .lt { background: rgba(255,255,255,.25); }
    .sr-opt.wrong { border-color: #f87171; background: rgba(239,68,68,.22); color: #fca5a5; }
    .sr-opt.dim { opacity: .4; }
    .sr-next { width: 100%; background: linear-gradient(135deg,#2563eb,#1d4ed8); color: #fff; border: none; border-radius: 13px;
        padding: 13px; font-weight: 800; font-size: 14.5px; cursor: pointer; margin-top: 4px; }
    .sr-next:disabled { opacity: .45; cursor: not-allowed; }

    .sr-progress { position: absolute; bottom: 16px; right: 16px; width: 340px; max-width: 42%; border-radius: 16px; padding: 16px 18px; color: #fff; pointer-events: auto; }
    .sr-progress .pt { font-weight: 800; font-size: 15px; margin-bottom: 10px; }
    .sr-bar { height: 9px; border-radius: 999px; background: rgba(255,255,255,.12); overflow: hidden; }
    .sr-bar > i { display: block; height: 100%; width: 0%; border-radius: 999px; background: linear-gradient(90deg,#34d399,#22c55e); transition: width .4s; }
    .sr-prow { display: flex; justify-content: space-between; margin-top: 12px; font-size: 12px; color: #93a7cc; }
    .sr-prow b { display: block; color: #fff; font-size: 18px; font-family: 'Outfit', sans-serif; }

    .sr-controls { position: absolute; bottom: 96px; left: 50%; transform: translateX(-50%); display: flex; gap: 12px; pointer-events: auto; }
    .sr-ctrl { width: 70px; height: 66px; border-radius: 14px; color: #cfe; display: flex; flex-direction: column; align-items: center;
        justify-content: center; gap: 5px; cursor: pointer; font-size: 11px; font-weight: 700; border: 1px solid rgba(120,150,220,.22); }
    .sr-ctrl svg { width: 20px; height: 20px; }
    .sr-ctrl:hover { background: rgba(40,60,110,.6); }

    .sr-dock { position: absolute; bottom: 14px; left: 50%; transform: translateX(-50%); display: flex; gap: 6px;
        padding: 8px; border-radius: 16px; pointer-events: auto; max-width: 94%; overflow-x: auto; }
    .sr-planet { min-width: 78px; padding: 8px 6px; border-radius: 12px; text-align: center; cursor: pointer; border: 2px solid transparent; transition: all .15s; }
    .sr-planet:hover { background: rgba(60,90,160,.3); }
    .sr-planet.active { border-color: #5b8def; background: rgba(91,141,239,.2); }
    .sr-planet .dot { width: 40px; height: 40px; border-radius: 50%; margin: 0 auto 6px; box-shadow: inset -6px -4px 10px rgba(0,0,0,.5); position: relative; }
    .sr-planet .dot .chk { position: absolute; right: -2px; top: -2px; width: 16px; height: 16px; background: #22c55e; border-radius: 50%;
        color: #fff; font-size: 11px; display: none; align-items: center; justify-content: center; }
    .sr-planet.cleared .dot .chk { display: flex; }
    .sr-planet span { font-size: 11px; font-weight: 700; color: #cdd9ef; }

    .sr-stats { position: absolute; bottom: 14px; left: 14px; border-radius: 14px; padding: 12px 14px; pointer-events: auto; color: #fff; min-width: 150px; }
    .sr-stats .row { display: flex; align-items: center; gap: 10px; margin: 6px 0; }
    .sr-stats .ic { font-size: 20px; }
    .sr-stats .k { font-size: 10.5px; color: #93a7cc; font-weight: 600; }
    .sr-stats .v { font-size: 17px; font-weight: 800; font-family: 'Outfit', sans-serif; }

    .sr-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
        background: rgba(3,5,15,.86); backdrop-filter: blur(5px); pointer-events: auto; padding: 18px; z-index: 10; }
    .sr-overlay[hidden] { display: none; }
    .sr-panel { width: 100%; max-width: 440px; border-radius: 22px; padding: 28px 26px; color: #e7edf7; text-align: center;
        background: linear-gradient(180deg,#101a36,#0a1124); border: 1px solid rgba(91,141,239,.4); box-shadow: 0 24px 70px rgba(0,0,0,.6); }
    .sr-panel h2 { font-family: 'Outfit', sans-serif; font-size: 26px; font-weight: 800; margin: 0 0 6px; letter-spacing: -.02em; }
    .sr-panel .accent { color: #7cb0ff; }
    .sr-panel p { color: #9fb3d4; font-size: 13.5px; line-height: 1.6; margin: 0 0 16px; }
    .sr-btn { display: inline-block; background: linear-gradient(135deg,#2563eb,#1d4ed8); color: #fff; border: none; border-radius: 14px;
        padding: 13px 26px; font-weight: 800; font-size: 14.5px; cursor: pointer; text-decoration: none; box-shadow: 0 8px 24px rgba(37,99,235,.45); }
    .sr-btn.ghost { background: transparent; border: 1px solid rgba(255,255,255,.2); box-shadow: none; }
    .sr-btnrow { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 8px; }
    .sr-final { font-family: 'Outfit', sans-serif; font-size: 44px; font-weight: 800; color: #7cb0ff; line-height: 1; }
    .sr-sumgrid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 14px 0; }
    .sr-sumgrid div { background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); border-radius: 12px; padding: 11px; }
    .sr-sumgrid .v { font-size: 22px; font-weight: 800; font-family: 'Outfit', sans-serif; color: #fff; }
    .sr-sumgrid .k { font-size: 10.5px; color: #93a7cc; text-transform: uppercase; letter-spacing: .04em; margin-top: 4px; }

    @media (max-width: 820px) {
        .sr-quiz, .sr-progress { width: 88%; max-width: 88%; left: 50%; right: auto; transform: translateX(-50%); }
        .sr-quiz { top: 56px; } .sr-progress { display: none; }
        .sr-controls { bottom: 110px; } .sr-stats { display: none; }
    }
</style>

<div id="sr-shell">
    <canvas id="sr-canvas"></canvas>
    <div class="sr-ui">
        <div class="sr-hint-bar sr-glass" id="srHint">Explore the planets and answer the quiz to earn points!</div>

        <div class="sr-quiz sr-glass" id="srQuiz">
            <h3 id="srQuizTitle">Earth Quiz</h3>
            <div class="qcount" id="srQuizCount">Question 1 of 3</div>
            <div class="qtext" id="srQuizText">—</div>
            <div id="srQuizOpts"></div>
            <button class="sr-next" id="srNextBtn" disabled>Next</button>
        </div>

        <div class="sr-progress sr-glass" id="srProgress">
            <div class="pt">Your Progress</div>
            <div class="sr-bar"><i id="srBar"></i></div>
            <div class="sr-prow">
                <div>Score<b id="srScore">0</b></div>
                <div style="text-align:right;">Time<b id="srTime">05:00</b></div>
            </div>
        </div>

        <div class="sr-controls">
            <div class="sr-ctrl sr-glass" id="srCtrlRotate"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Auto</div>
            <div class="sr-ctrl sr-glass" id="srCtrlZoomIn"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m-3-3h6"/></svg>Zoom +</div>
            <div class="sr-ctrl sr-glass" id="srCtrlZoomOut"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM7 10h6"/></svg>Zoom −</div>
            <div class="sr-ctrl sr-glass" id="srCtrlReset"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9"/></svg>Reset</div>
        </div>

        <div class="sr-dock sr-glass" id="srDock"></div>

        <div class="sr-stats sr-glass">
            <div class="row"><span class="ic">⭐</span><div><div class="k">Total Points</div><div class="v" id="srTotalPts">{{ $myBest }}</div></div></div>
            <div class="row"><span class="ic">🏅</span><div><div class="k">Badges</div><div class="v" id="srBadges">0</div></div></div>
        </div>
    </div>

    <div class="sr-overlay" id="srStart">
        <div class="sr-panel">
            <h2>STRATA <span class="accent">RUSH</span></h2>
            <p><b>Grade {{ $grade }} — {{ $world }}</b><br>
            Galugarin ang solar system! Mag-click ng <b>planeta</b> sa ibaba (o sa screen) para magbukas ng quiz.
            Sagutin lahat para makakuha ng points at i-clear ang lahat ng planeta bago maubos ang oras! 🚀</p>
            <p style="font-size:12.5px;color:#7c8db0;">🖱️ I-drag para tumingin · scroll para mag-zoom</p>
            <div class="sr-btnrow">
                <button class="sr-btn" id="srStartBtn">▶ Start Mission</button>
                <a class="sr-btn ghost" href="{{ route('student.science-game.leaderboard') }}">🏆 Leaderboard</a>
            </div>
            <p id="srLoadMsg" style="margin-top:14px;font-size:12.5px;color:#93a7cc;">Loading questions…</p>
        </div>
    </div>

    <div class="sr-overlay" id="srSummary" hidden>
        <div class="sr-panel">
            <h2 id="srSumTitle">🏆 Mission Complete!</h2>
            <div class="sr-final" id="srSumScore">0</div>
            <p>Final Score</p>
            <div class="sr-sumgrid">
                <div><div class="v" id="srSumAcc">0%</div><div class="k">Accuracy</div></div>
                <div><div class="v" id="srSumPlanets">0/8</div><div class="k">Planets</div></div>
                <div><div class="v" id="srSumCorrect">0</div><div class="k">Correct</div></div>
                <div><div class="v" id="srSumWrong">0</div><div class="k">Incorrect</div></div>
            </div>
            <p id="srSaveMsg" style="font-size:12.5px;">Saving…</p>
            <div class="sr-btnrow">
                <button class="sr-btn" id="srReplayBtn">🔁 Play Again</button>
                <a class="sr-btn ghost" href="{{ route('student.science-game.leaderboard') }}">🏆 Leaderboard</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
(function () {
    const CFG = {
        grade: @json($grade),
        world: @json($world),
        myBest: {{ (int) $myBest }},
        urlQuestions: @json(route('student.science-game.questions')),
        urlScore:     @json(route('student.science-game.score')),
        csrf: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    };

    const PLANETS = [
        { name:'Mercury', size:0.7, orbit:7,  speed:0.9, kind:'rocky', col:'#9c9189' },
        { name:'Venus',   size:1.0, orbit:9.5, speed:0.7, kind:'rocky', col:'#caa46a' },
        { name:'Earth',   size:1.1, orbit:12.5,speed:0.6, kind:'earth', col:'#3b7dd8' },
        { name:'Mars',    size:0.85,orbit:15.5,speed:0.5, kind:'rocky', col:'#c1502e' },
        { name:'Jupiter', size:2.4, orbit:20,  speed:0.34,kind:'gas',   col:'#c9a36b' },
        { name:'Saturn',  size:2.1, orbit:25,  speed:0.28,kind:'gas',   col:'#d8c08a', ring:true },
        { name:'Uranus',  size:1.5, orbit:29,  speed:0.22,kind:'ice',   col:'#7fd0d8' },
        { name:'Neptune', size:1.45,orbit:33,  speed:0.18,kind:'ice',   col:'#3f63d8' },
    ];

    let QBANK = [], questionsReady = false, qPtr = 0;
    const FALLBACK = (CFG.grade === '11') ? [
        {topic:'Physics', question:'Ano ang SI unit ng force?', options:['Joule','Watt','Newton','Pascal'], correct_index:2},
        {topic:'Chemistry', question:'Aling particle ang may negatibong charge?', options:['Proton','Neutron','Electron','Nucleus'], correct_index:2},
        {topic:'Physics', question:'F = ma. Kung m=2kg, a=3m/s², ano ang F?', options:['5 N','6 N','1.5 N','8 N'], correct_index:1},
    ] : [
        {topic:'Astronomy', question:'Ano ang tawag sa pag-ikot ng Earth sa Araw?', options:['Rotation','Revolution','Eclipse','Gravity'], correct_index:1},
        {topic:'Earth Science', question:'Aling layer ng Earth ang tinitirhan natin?', options:['Mantle','Outer core','Crust','Inner core'], correct_index:2},
        {topic:'Biology', question:'Ano ang "powerhouse of the cell"?', options:['Nucleus','Ribosome','Mitochondria','Vacuole'], correct_index:2},
    ];
    function drawQuestions(n) {
        if (!QBANK.length) QBANK = FALLBACK.slice();
        const out = [];
        for (let i = 0; i < n; i++) { out.push(Object.assign({}, QBANK[qPtr % QBANK.length])); qPtr++; }
        return out;
    }
    fetch(CFG.urlQuestions, { headers:{Accept:'application/json'} }).then(r=>r.json()).then(d=>{
        const qs = (d.questions && d.questions.length) ? d.questions : FALLBACK;
        QBANK = qs.map(q=>Object.assign({}, q)).sort(()=>Math.random()-0.5);
        questionsReady = true;
        document.getElementById('srLoadMsg').textContent = QBANK.length + ' questions loaded ✓';
    }).catch(()=>{ QBANK = FALLBACK.slice(); questionsReady = true;
        document.getElementById('srLoadMsg').textContent = 'Offline mode (sample questions)'; });

    // ================= THREE.JS =================
    const canvas = document.getElementById('sr-canvas'), shell = document.getElementById('sr-shell');
    let renderer, scene, camera, raycaster, mouseNDC = new THREE.Vector2();
    const planetMeshes = [];
    let sun, astronaut, clock;
    const cam = { az: 0.0, pol: 1.15, rad: 40, target: new THREE.Vector3(0, 2, 0) };
    const DEF = { az: 0.0, pol: 1.15, rad: 40 };

    function texCanvas(draw) {
        const c = document.createElement('canvas'); c.width = 512; c.height = 256;
        draw(c.getContext('2d'), c.width, c.height);
        return new THREE.CanvasTexture(c);
    }
    function shadeColor(hex, f) {
        const r = Math.min(255, Math.round(parseInt(hex.slice(1,3),16)*f));
        const g = Math.min(255, Math.round(parseInt(hex.slice(3,5),16)*f));
        const b = Math.min(255, Math.round(parseInt(hex.slice(5,7),16)*f));
        return 'rgb('+r+','+g+','+b+')';
    }
    function planetTexture(p) {
        return texCanvas((x, w, h) => {
            if (p.kind === 'gas' || p.kind === 'ice') {
                for (let y = 0; y < h; y++) {
                    const t = y / h, shade = 0.78 + 0.22 * Math.sin(t * (p.kind === 'gas' ? 26 : 12) + 1);
                    x.fillStyle = shadeColor(p.col, shade); x.fillRect(0, y, w, 1);
                }
            } else if (p.kind === 'earth') {
                x.fillStyle = '#1c5fb0'; x.fillRect(0, 0, w, h);
                x.fillStyle = '#2e8b4f';
                for (let i = 0; i < 26; i++) {
                    const cx = Math.random()*w, cy = 40 + Math.random()*(h-80), r = 12 + Math.random()*34;
                    x.beginPath(); x.ellipse(cx, cy, r, r*0.7, Math.random()*6, 0, 7); x.fill();
                }
                x.fillStyle = '#e8f0ff'; x.fillRect(0, 0, w, 14); x.fillRect(0, h-14, w, 14);
            } else {
                x.fillStyle = p.col; x.fillRect(0, 0, w, h);
                for (let i = 0; i < 120; i++) {
                    const cx = Math.random()*w, cy = Math.random()*h, r = 2 + Math.random()*9;
                    x.fillStyle = Math.random() > 0.5 ? shadeColor(p.col, 0.78) : shadeColor(p.col, 1.16);
                    x.beginPath(); x.arc(cx, cy, r, 0, 7); x.fill();
                }
            }
        });
    }

    function initScene() {
        renderer = new THREE.WebGLRenderer({ canvas, antialias:true });
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        resize();
        scene = new THREE.Scene();
        camera = new THREE.PerspectiveCamera(55, shell.clientWidth/shell.clientHeight, 0.1, 400);
        raycaster = new THREE.Raycaster();

        scene.add(new THREE.AmbientLight(0xffffff, 0.35));
        const sunLight = new THREE.PointLight(0xffffff, 2.2, 300); scene.add(sunLight);

        const starGeo = new THREE.BufferGeometry(), sv = [];
        for (let i = 0; i < 1400; i++) {
            const r = 120 + Math.random()*160, t = Math.random()*Math.PI*2, ph = Math.acos(2*Math.random()-1);
            sv.push(r*Math.sin(ph)*Math.cos(t), r*Math.cos(ph), r*Math.sin(ph)*Math.sin(t));
        }
        starGeo.setAttribute('position', new THREE.Float32BufferAttribute(sv, 3));
        scene.add(new THREE.Points(starGeo, new THREE.PointsMaterial({ color:0xffffff, size:0.7 })));

        sun = new THREE.Mesh(new THREE.SphereGeometry(3.4, 32, 32), new THREE.MeshBasicMaterial({ color:0xffd34d }));
        scene.add(sun);
        scene.add(new THREE.Mesh(new THREE.SphereGeometry(4.6, 32, 32),
            new THREE.MeshBasicMaterial({ color:0xffae34, transparent:true, opacity:0.25 })));

        PLANETS.forEach((p, i) => {
            const m = new THREE.Mesh(new THREE.SphereGeometry(p.size, 36, 36),
                new THREE.MeshStandardMaterial({ map: planetTexture(p), roughness: p.kind==='gas'?0.9:1, metalness:0 }));
            const a0 = (i/PLANETS.length)*Math.PI*2;
            m.position.set(Math.cos(a0)*p.orbit, 0, Math.sin(a0)*p.orbit);
            m.userData = { idx:i, angle:a0, orbit:p.orbit, speed:p.speed, cleared:false };
            scene.add(m); planetMeshes.push(m);
            if (p.ring) {
                const ring = new THREE.Mesh(new THREE.RingGeometry(p.size*1.4, p.size*2.2, 48),
                    new THREE.MeshBasicMaterial({ color:0xd8c08a, side:THREE.DoubleSide, transparent:true, opacity:0.7 }));
                ring.rotation.x = Math.PI/2.2; m.add(ring);
            }
            const og = new THREE.BufferGeometry(), ov = [];
            for (let s=0; s<=64; s++){ const a=s/64*Math.PI*2; ov.push(Math.cos(a)*p.orbit,0,Math.sin(a)*p.orbit); }
            og.setAttribute('position', new THREE.Float32BufferAttribute(ov,3));
            scene.add(new THREE.LineLoop(og, new THREE.LineBasicMaterial({ color:0x3a4a78, transparent:true, opacity:0.4 })));
        });

        const moon = new THREE.Mesh(new THREE.SphereGeometry(10, 36, 36),
            new THREE.MeshStandardMaterial({ map: planetTexture({kind:'rocky', col:'#8a8a90'}), roughness:1 }));
        moon.position.set(0, -11, 8); scene.add(moon);
        astronaut = buildAstronaut(); astronaut.position.set(0, 0.4, 8); scene.add(astronaut);
        const flag = buildFlag(); flag.position.set(3.4, 0.4, 7.4); scene.add(flag);

        clock = new THREE.Clock();
        window.addEventListener('resize', resize);
        bindControls(); buildDock(); applyCamera(); animate();
    }

    function buildAstronaut() {
        const g = new THREE.Group();
        const white = new THREE.MeshStandardMaterial({ color:0xf2f4f8, roughness:0.6 });
        const blue  = new THREE.MeshStandardMaterial({ color:0x3b6fd1, roughness:0.5 });
        const body = new THREE.Mesh(new THREE.CylinderGeometry(0.42,0.52,1.3,14), white); body.position.y = 1.15; g.add(body);
        const head = new THREE.Mesh(new THREE.SphereGeometry(0.42,20,20), white); head.position.y = 2.0; g.add(head);
        const visor = new THREE.Mesh(new THREE.SphereGeometry(0.31,16,16),
            new THREE.MeshStandardMaterial({ color:0x16243f, roughness:0.2, metalness:0.5 }));
        visor.position.set(0,2.0,0.2); visor.scale.set(1,0.9,0.6); g.add(visor);
        const pack = new THREE.Mesh(new THREE.BoxGeometry(0.55,0.7,0.3), blue); pack.position.set(0,1.2,-0.42); g.add(pack);
        [-0.52,0.52].forEach(sx=>{ const arm=new THREE.Mesh(new THREE.CylinderGeometry(0.15,0.15,0.85,8),white); arm.position.set(sx,1.05,0); g.add(arm); });
        [-0.22,0.22].forEach(sx=>{ const leg=new THREE.Mesh(new THREE.CylinderGeometry(0.17,0.17,0.9,8),white); leg.position.set(sx,0.4,0); g.add(leg); });
        return g;
    }
    function buildFlag() {
        const g = new THREE.Group();
        const pole = new THREE.Mesh(new THREE.CylinderGeometry(0.05,0.05,3,8), new THREE.MeshStandardMaterial({color:0xcccccc, metalness:0.6}));
        pole.position.y = 1.5; g.add(pole);
        const cloth = new THREE.Mesh(new THREE.PlaneGeometry(1.3,0.85), new THREE.MeshStandardMaterial({color:0x2848a8, side:THREE.DoubleSide}));
        cloth.position.set(0.7,2.4,0); g.add(cloth);
        return g;
    }

    function resize() {
        renderer.setSize(shell.clientWidth, shell.clientHeight, false);
        if (camera) { camera.aspect = shell.clientWidth/shell.clientHeight; camera.updateProjectionMatrix(); }
    }
    function applyCamera() {
        const x = cam.target.x + cam.rad * Math.sin(cam.pol) * Math.sin(cam.az);
        const y = cam.target.y + cam.rad * Math.cos(cam.pol);
        const z = cam.target.z + cam.rad * Math.sin(cam.pol) * Math.cos(cam.az);
        camera.position.set(x, y, z); camera.lookAt(cam.target);
    }

    function bindControls() {
        let dragging = false, lx = 0, ly = 0, moved = 0;
        const down = e => { dragging = true; moved = 0; const t = e.touches?e.touches[0]:e; lx = t.clientX; ly = t.clientY; };
        const move = e => {
            const t = e.touches?e.touches[0]:e;
            if (dragging) {
                const dx = t.clientX - lx, dy = t.clientY - ly; lx = t.clientX; ly = t.clientY; moved += Math.abs(dx)+Math.abs(dy);
                cam.az -= dx * 0.005; cam.pol = Math.max(0.35, Math.min(1.45, cam.pol - dy * 0.004)); applyCamera();
            }
        };
        const up = e => {
            if (dragging && moved < 6) { const t = (e.changedTouches?e.changedTouches[0]:e); pickPlanet(t.clientX, t.clientY); }
            dragging = false;
        };
        canvas.addEventListener('mousedown', down); window.addEventListener('mousemove', move); window.addEventListener('mouseup', up);
        canvas.addEventListener('touchstart', down, {passive:true});
        canvas.addEventListener('touchmove', e=>{ move(e); if(dragging) e.preventDefault(); }, {passive:false});
        canvas.addEventListener('touchend', up);
        canvas.addEventListener('wheel', e=>{ cam.rad = Math.max(14, Math.min(70, cam.rad + Math.sign(e.deltaY)*2.5)); applyCamera(); e.preventDefault(); }, {passive:false});

        document.getElementById('srCtrlZoomIn').onclick  = ()=>{ cam.rad = Math.max(14, cam.rad-4); applyCamera(); };
        document.getElementById('srCtrlZoomOut').onclick = ()=>{ cam.rad = Math.min(70, cam.rad+4); applyCamera(); };
        document.getElementById('srCtrlReset').onclick   = ()=>{ cam.target.set(0,2,0); cam.az=DEF.az; cam.pol=DEF.pol; cam.rad=DEF.rad; applyCamera(); };
        document.getElementById('srCtrlRotate').onclick  = ()=>{ G.autoRotate = !G.autoRotate; };
    }
    function pickPlanet(clientX, clientY) {
        const r = canvas.getBoundingClientRect();
        mouseNDC.x = ((clientX - r.left)/r.width)*2 - 1;
        mouseNDC.y = -((clientY - r.top)/r.height)*2 + 1;
        raycaster.setFromCamera(mouseNDC, camera);
        const hit = raycaster.intersectObjects(planetMeshes, false)[0];
        if (hit) selectPlanet(hit.object.userData.idx);
    }

    // ================= GAME =================
    const G = { running:false, quizOpen:false, autoRotate:true, score:0, correct:0, wrong:0,
        cleared:0, timeLeft:300, responseTimes:[], curIdx:-1, quiz:[], qi:0, qStart:0 };
    let timerId = null;
    const el = id => document.getElementById(id);

    function buildDock() {
        const dock = el('srDock'); dock.innerHTML = '';
        PLANETS.forEach((p, i) => {
            const d = document.createElement('div'); d.className = 'sr-planet'; d.dataset.idx = i;
            d.innerHTML = '<div class="dot" style="background:radial-gradient(circle at 35% 30%, '+shadeColor(p.col,1.25)+', '+p.col+' 70%, '+shadeColor(p.col,0.6)+');"><span class="chk">✓</span></div><span>'+p.name+'</span>';
            d.onclick = () => selectPlanet(i);
            dock.appendChild(d);
        });
    }

    function startGame() {
        if (!questionsReady) { el('srHint').textContent = 'Loading questions…'; return; }
        Object.assign(G, { running:true, quizOpen:false, autoRotate:true, score:0, correct:0, wrong:0,
            cleared:0, timeLeft:300, responseTimes:[], curIdx:-1, quiz:[], qi:0 });
        qPtr = 0;
        planetMeshes.forEach(m => m.userData.cleared = false);
        document.querySelectorAll('.sr-planet').forEach(d => d.classList.remove('cleared','active'));
        el('srStart').hidden = true; el('srSummary').hidden = true; el('srQuiz').classList.remove('show');
        el('srHint').textContent = 'Mag-click ng planeta para magsimula! 🪐';
        updateHUD();
        clearInterval(timerId);
        timerId = setInterval(()=>{ if(!G.running) return; G.timeLeft--; updateHUD(); if(G.timeLeft<=0) endGame(); }, 1000);
    }

    function updateHUD() {
        el('srScore').textContent = G.score;
        const m = Math.floor(G.timeLeft/60), s = G.timeLeft%60;
        el('srTime').textContent = (m<10?'0':'')+m+':'+(s<10?'0':'')+s;
        el('srBar').style.width = Math.round(G.cleared/PLANETS.length*100) + '%';
        el('srTotalPts').textContent = CFG.myBest + G.score;
        el('srBadges').textContent = Math.min(12, Math.floor((CFG.myBest + G.score)/100));
    }

    function selectPlanet(idx) {
        if (!G.running || G.quizOpen) return;
        if (planetMeshes[idx].userData.cleared) { el('srHint').textContent = PLANETS[idx].name + ' ✓ na-clear na! Pumili ng iba.'; return; }
        G.curIdx = idx; G.quizOpen = true;
        document.querySelectorAll('.sr-planet').forEach(d=>d.classList.remove('active'));
        document.querySelector('.sr-planet[data-idx="'+idx+'"]').classList.add('active');
        cam.target.copy(planetMeshes[idx].position); cam.rad = 18; applyCamera();
        G.quiz = drawQuestions(3); G.qi = 0;
        renderQuestion(); el('srQuiz').classList.add('show');
    }

    function renderQuestion() {
        const q = G.quiz[G.qi]; G.qStart = performance.now(); q._answered = false;
        el('srQuizTitle').textContent = PLANETS[G.curIdx].name + ' Quiz';
        el('srQuizCount').textContent = 'Question ' + (G.qi+1) + ' of ' + G.quiz.length;
        el('srQuizText').textContent = q.question;
        const wrap = el('srQuizOpts'); wrap.innerHTML = '';
        const letters = ['A','B','C','D','E'];
        q._btns = [];
        q.options.forEach((opt,i)=>{
            const b = document.createElement('button'); b.className='sr-opt';
            b.innerHTML = '<span class="lt">'+letters[i]+'</span> '+opt;
            b.onclick = ()=>answer(i,q,b); wrap.appendChild(b); q._btns.push(b);
        });
        const nb = el('srNextBtn'); nb.disabled = true;
        nb.textContent = (G.qi < G.quiz.length-1) ? 'Next' : 'Finish Planet';
        nb.onclick = nextQuestion;
    }

    function answer(i,q,btn) {
        if (q._answered) return; q._answered = true;
        G.responseTimes.push(performance.now() - G.qStart);
        q._btns.forEach(b=>b.disabled=true);
        if (i === q.correct_index) { btn.classList.add('correct'); G.correct++; G.score += 50; }
        else { btn.classList.add('wrong'); q._btns[q.correct_index].classList.add('correct'); G.wrong++; }
        updateHUD(); el('srNextBtn').disabled = false;
    }

    function nextQuestion() {
        if (G.qi < G.quiz.length-1) { G.qi++; renderQuestion(); return; }
        planetMeshes[G.curIdx].userData.cleared = true; G.cleared++;
        document.querySelector('.sr-planet[data-idx="'+G.curIdx+'"]').classList.add('cleared');
        el('srQuiz').classList.remove('show'); G.quizOpen = false;
        cam.target.set(0,2,0); cam.rad = 40; applyCamera();
        el('srHint').textContent = PLANETS[G.curIdx].name + ' cleared! ✓ Pumili ng susunod na planeta.';
        updateHUD();
        if (G.cleared >= PLANETS.length) endGame();
    }

    function endGame() {
        G.running = false; clearInterval(timerId);
        const total = G.correct + G.wrong;
        const acc = total ? Math.round(G.correct/total*100) : 0;
        const avg = G.responseTimes.length ? Math.round(G.responseTimes.reduce((a,b)=>a+b,0)/G.responseTimes.length) : 0;
        el('srSumTitle').textContent = (G.cleared>=PLANETS.length) ? '🏆 Mission Complete!' : '⏱ Time\'s Up!';
        el('srSumScore').textContent = G.score;
        el('srSumAcc').textContent = acc+'%';
        el('srSumPlanets').textContent = G.cleared+'/'+PLANETS.length;
        el('srSumCorrect').textContent = G.correct;
        el('srSumWrong').textContent = G.wrong;
        el('srSummary').hidden = false;
        el('srSaveMsg').textContent = 'Saving to leaderboard…';
        fetch(CFG.urlScore, { method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CFG.csrf,'Accept':'application/json'},
            body: JSON.stringify({ score:G.score, accuracy:acc, correct:G.correct, incorrect:G.wrong, max_combo:G.correct, avg_response_ms:avg })
        }).then(r=>r.json()).then(d=>{ el('srSaveMsg').textContent = d.ok ? '✓ Saved! Personal best: '+(d.personalBest ?? G.score) : 'Saved locally.'; })
          .catch(()=>{ el('srSaveMsg').textContent = 'Could not save (check connection).'; });
    }

    el('srStartBtn').onclick = startGame;
    el('srReplayBtn').onclick = startGame;

    function animate() {
        requestAnimationFrame(animate);
        const dt = Math.min(clock.getDelta(), 0.05), t = clock.elapsedTime;
        if (sun) sun.rotation.y += dt*0.1;
        planetMeshes.forEach(m=>{
            if (G.running && !G.quizOpen && G.autoRotate) {
                m.userData.angle += dt * m.userData.speed * 0.25;
                m.position.set(Math.cos(m.userData.angle)*m.userData.orbit, 0, Math.sin(m.userData.angle)*m.userData.orbit);
            }
            m.rotation.y += dt * 0.4;
        });
        if (astronaut) astronaut.position.y = 0.4 + Math.sin(t*1.5)*0.05;
        renderer.render(scene, camera);
    }

    function boot(){ if (typeof THREE === 'undefined'){ setTimeout(boot,120); return; } try{ initScene(); }catch(e){ console.error('Strata Rush', e); } }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();
</script>
@endsection