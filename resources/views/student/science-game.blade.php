@extends('layouts.app')
@section('title', 'Strata Rush — 3D Science Game')

@section('content')
<style>
    #sr-shell { position: relative; width: 100%; height: calc(100vh - 110px); min-height: 560px;
        border-radius: 18px; overflow: hidden; background: #04060f; box-shadow: 0 12px 44px rgba(0,0,0,.35); font-family: 'Plus Jakarta Sans', sans-serif; }
    #sr-canvas { display: block; width: 100%; height: 100%; touch-action: none; cursor: grab; }
    #sr-canvas:active { cursor: grabbing; }
    .sr-ui { position: absolute; inset: 0; pointer-events: none; }
    .sr-glass { background: rgba(10,16,34,.74); backdrop-filter: blur(10px); border: 1px solid rgba(120,150,220,.22); }

    .sr-hint-bar { position: absolute; top: 14px; left: 50%; transform: translateX(-50%);
        color: #dbe6fb; font-size: 13.5px; font-weight: 600; padding: 10px 20px; border-radius: 999px; max-width: 72%; text-align: center; }

    /* HP bars (G11 battle) */
    .sr-hp { position: absolute; top: 56px; left: 16px; right: 16px; display: none; align-items: center; gap: 14px; }
    .sr-hp.show { display: flex; }
    .sr-hpbar { flex: 1; border-radius: 14px; padding: 9px 13px; color: #fff; }
    .sr-hpbar span { font-size: 12px; font-weight: 800; display: block; margin-bottom: 6px; }
    .sr-hpbar .bar { height: 12px; border-radius: 999px; background: rgba(255,255,255,.14); overflow: hidden; }
    .sr-hpbar .bar > i { display: block; height: 100%; width: 100%; border-radius: 999px; transition: width .35s; }
    .sr-hpbar.you  .bar > i { background: linear-gradient(90deg,#34d399,#22c55e); }
    .sr-hpbar.rival { text-align: right; } .sr-hpbar.rival .bar { transform: scaleX(-1); }
    .sr-hpbar.rival .bar > i { background: linear-gradient(90deg,#f87171,#ef4444); }
    .sr-vs { font-family:'Outfit',sans-serif; font-weight:800; font-size:20px; color:# facc15; color:#fbbf24; }

    /* score/time/progress (lower right) */
    .sr-progress { position: absolute; bottom: 86px; right: 16px; width: 280px; max-width: 42%; border-radius: 16px; padding: 14px 16px; color: #fff; pointer-events: auto; }
    .sr-progress .pt { font-weight: 800; font-size: 14px; margin-bottom: 8px; }
    .sr-bar { height: 8px; border-radius: 999px; background: rgba(255,255,255,.12); overflow: hidden; }
    .sr-bar > i { display: block; height: 100%; width: 0%; border-radius: 999px; background: linear-gradient(90deg,#34d399,#22c55e); transition: width .4s; }
    .sr-prow { display: flex; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #93a7cc; }
    .sr-prow b { display: block; color: #fff; font-size: 17px; font-family: 'Outfit', sans-serif; }

    /* Quiz panel (bottom-center) */
    .sr-quiz { position: absolute; bottom: 16px; left: 50%; transform: translateX(-50%); width: 560px; max-width: 92%;
        border-radius: 18px; padding: 18px 20px; color: #fff; pointer-events: auto; display: none; }
    .sr-quiz.show { display: block; animation: srIn .22s ease; }
    @keyframes srIn { from { opacity: 0; transform: translate(-50%,12px); } to { opacity: 1; transform: translate(-50%,0); } }
    .sr-quiz .qtop { display:flex; justify-content:space-between; align-items:center; margin-bottom: 10px; }
    .sr-quiz h3 { font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 800; margin: 0; }
    .sr-quiz .qtopic { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: #c4b5fd;
        background: rgba(124,58,237,.2); padding: 3px 10px; border-radius: 999px; }
    .sr-quiz .qtext { font-size: 15.5px; font-weight: 700; line-height: 1.4; margin-bottom: 14px; }
    .sr-opts { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; }
    @media (max-width: 560px){ .sr-opts { grid-template-columns: 1fr; } }
    .sr-opt { display: flex; align-items: center; gap: 10px; text-align: left; background: rgba(255,255,255,.05);
        border: 1.5px solid rgba(255,255,255,.12); color: #e7edf7; border-radius: 12px; padding: 11px 13px; font-size: 13.5px; font-weight: 600; cursor: pointer; transition: all .15s; }
    .sr-opt .lt { width: 24px; height: 24px; border-radius: 7px; background: rgba(255,255,255,.1); display:grid; place-items:center; font-weight:800; font-size:12px; flex-shrink:0; }
    .sr-opt:hover:not(:disabled) { border-color: #5b8def; background: rgba(91,141,239,.14); }
    .sr-opt:disabled { cursor: default; }
    .sr-opt.correct { border-color: #34d399; background: #15803d; color: #fff; }
    .sr-opt.wrong { border-color: #f87171; background: rgba(239,68,68,.22); color: #fca5a5; }

    .sr-controls { position: absolute; bottom: 16px; left: 16px; display: flex; gap: 10px; pointer-events: auto; }
    .sr-ctrl { width: 58px; height: 56px; border-radius: 13px; color: #cfe; display: flex; flex-direction: column; align-items: center;
        justify-content: center; gap: 4px; cursor: pointer; font-size: 10px; font-weight: 700; border: 1px solid rgba(120,150,220,.22); }
    .sr-ctrl svg { width: 18px; height: 18px; }
    .sr-ctrl:hover { background: rgba(40,60,110,.6); }

    .sr-stats { position: absolute; top: 14px; right: 16px; border-radius: 14px; padding: 10px 13px; pointer-events: auto; color: #fff; display:flex; gap:16px; }
    .sr-stats .k { font-size: 10px; color: #93a7cc; font-weight: 600; }
    .sr-stats .v { font-size: 16px; font-weight: 800; font-family: 'Outfit', sans-serif; }

    .sr-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
        background: rgba(3,5,15,.86); backdrop-filter: blur(5px); pointer-events: auto; padding: 18px; z-index: 10; }
    .sr-overlay[hidden] { display: none; }
    .sr-panel { width: 100%; max-width: 440px; border-radius: 22px; padding: 28px 26px; color: #e7edf7; text-align: center;
        background: linear-gradient(180deg,#101a36,#0a1124); border: 1px solid rgba(91,141,239,.4); box-shadow: 0 24px 70px rgba(0,0,0,.6); }
    .sr-panel h2 { font-family: 'Outfit', sans-serif; font-size: 25px; font-weight: 800; margin: 0 0 6px; letter-spacing: -.02em; }
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
    @media (max-width: 820px) { .sr-progress { display:none; } .sr-stats { top:auto; bottom:80px; right:16px; } }
</style>

<div id="sr-shell">
    <canvas id="sr-canvas"></canvas>
    <div class="sr-ui">
        <div class="sr-hint-bar sr-glass" id="srHint">Loading…</div>

        <div class="sr-hp" id="srHpWrap">
            <div class="sr-hpbar you sr-glass"><span id="srYouName">🧑‍🔬 YOU</span><div class="bar"><i id="srHpYou"></i></div></div>
            <div class="sr-vs">VS</div>
            <div class="sr-hpbar rival sr-glass"><span>RIVAL 🧪</span><div class="bar"><i id="srHpRival"></i></div></div>
        </div>

        <div class="sr-progress sr-glass" id="srProgress">
            <div class="pt" id="srProgLabel">Progress</div>
            <div class="sr-bar"><i id="srBar"></i></div>
            <div class="sr-prow">
                <div>Score<b id="srScore">0</b></div>
                <div style="text-align:right;">Time<b id="srTime">05:00</b></div>
            </div>
        </div>

        <div class="sr-quiz sr-glass" id="srQuiz">
            <div class="qtop"><h3 id="srQuizTitle">Quiz</h3><span class="qtopic" id="srQTopic">SCIENCE</span></div>
            <div class="qtext" id="srQuizText">—</div>
            <div class="sr-opts" id="srQuizOpts"></div>
        </div>

        <div class="sr-controls">
            <div class="sr-ctrl sr-glass" id="srCtrlZoomIn"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m-3-3h6"/></svg>Zoom+</div>
            <div class="sr-ctrl sr-glass" id="srCtrlZoomOut"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM7 10h6"/></svg>Zoom−</div>
            <div class="sr-ctrl sr-glass" id="srCtrlReset"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9"/></svg>Reset</div>
        </div>

        <div class="sr-stats sr-glass">
            <div><div class="k">⭐ Points</div><div class="v" id="srTotalPts">{{ $myBest }}</div></div>
            <div><div class="k">🏅 Badges</div><div class="v" id="srBadges">0</div></div>
        </div>
    </div>

    <div class="sr-overlay" id="srStart">
        <div class="sr-panel">
            <h2>STRATA <span class="accent">RUSH</span></h2>
            <p id="srStartDesc"><b>Grade {{ $grade }} — {{ $world }}</b></p>
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
                <div><div class="v" id="srSumGoal">0</div><div class="k" id="srSumGoalK">Cleared</div></div>
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
    const isG11 = CFG.grade === '11';   // Formula Clash (battle) vs Field Researcher (collect)

    // ---------------- questions ----------------
    let QBANK = [], questionsReady = false, qPtr = 0;
    const FALLBACK = isG11 ? [
        {topic:'Physics', question:'Ano ang SI unit ng force?', options:['Joule','Watt','Newton','Pascal'], correct_index:2},
        {topic:'Chemistry', question:'Aling particle ang may negatibong charge?', options:['Proton','Neutron','Electron','Nucleus'], correct_index:2},
        {topic:'Physics', question:'F = ma. Kung m=2kg, a=3m/s², ano ang F?', options:['5 N','6 N','1.5 N','8 N'], correct_index:1},
    ] : [
        {topic:'Earth Science', question:'Anong uri ng bato ang nabubuo mula sa magma/lava?', options:['Sedimentary','Metamorphic','Igneous','Fossil'], correct_index:2},
        {topic:'Biology', question:'Ano ang "powerhouse of the cell"?', options:['Nucleus','Ribosome','Mitochondria','Vacuole'], correct_index:2},
        {topic:'Earth Science', question:'Aling layer ng Earth ang tinitirhan natin?', options:['Mantle','Outer core','Crust','Inner core'], correct_index:2},
    ];
    function nextQ() { if (!QBANK.length) QBANK = FALLBACK.slice(); const q = Object.assign({}, QBANK[qPtr % QBANK.length]); qPtr++; return q; }
    fetch(CFG.urlQuestions, { headers:{Accept:'application/json'} }).then(r=>r.json()).then(d=>{
        const qs = (d.questions && d.questions.length) ? d.questions : FALLBACK;
        QBANK = qs.map(q=>Object.assign({}, q)).sort(()=>Math.random()-0.5);
        questionsReady = true; document.getElementById('srLoadMsg').textContent = QBANK.length + ' questions loaded ✓';
    }).catch(()=>{ QBANK = FALLBACK.slice(); questionsReady = true; document.getElementById('srLoadMsg').textContent = 'Offline mode'; });

    // ================= THREE.JS =================
    const canvas = document.getElementById('sr-canvas'), shell = document.getElementById('sr-shell');
    let renderer, scene, camera, raycaster, mouseNDC = new THREE.Vector2(), clock;
    const samples = [];   // G12 collectibles
    let player, rival, beam;
    const cam = { az: 0.0, pol: 1.15, rad: 18, target: new THREE.Vector3(0, 1.6, 0) };
    const DEF = Object.assign({}, cam);

    const M = (c, opt) => new THREE.MeshStandardMaterial(Object.assign({ color:c, roughness:0.7 }, opt||{}));

    // ---- REALISTIC character builder (head, face, hair, clothes, accessories, mood) ----
    function buildCharacter(pal) {
        const p = Object.assign({ mood:'happy', outfit:'casual', coat:'#3b4356',
            glasses:false, facialHair:false, acc:'none' }, pal);
        const g = new THREE.Group();
        const skinMat = M(p.skin,{roughness:0.6});
        const shirtMat = M(p.shirt,{roughness:0.85});
        const pantsMat = M(p.pants,{roughness:0.9});
        const hairMat = M(p.hair,{roughness:0.92});

        // torso + shoulders + hips
        const torso = new THREE.Mesh(new THREE.CylinderGeometry(0.4,0.5,1.1,16), shirtMat); torso.position.y=1.05; g.add(torso);
        const shoulders = new THREE.Mesh(new THREE.CylinderGeometry(0.44,0.44,0.18,16), shirtMat); shoulders.rotation.z=Math.PI/2; shoulders.position.y=1.5; g.add(shoulders);
        const hip = new THREE.Mesh(new THREE.CylinderGeometry(0.48,0.42,0.4,16), pantsMat); hip.position.y=0.5; g.add(hip);
        // legs + shoes
        [-0.2,0.2].forEach(sx=>{
            const leg=new THREE.Mesh(new THREE.CylinderGeometry(0.16,0.14,0.7,10), pantsMat); leg.position.set(sx,0.15,0); g.add(leg);
            const foot=new THREE.Mesh(new THREE.BoxGeometry(0.22,0.14,0.4), M('#1a1d26',{roughness:0.5})); foot.position.set(sx,-0.12,0.1); g.add(foot);
        });
        // arms (forearm uses coat sleeve color if labcoat)
        [-1,1].forEach(side=>{ const sx=side*0.55;
            const up=new THREE.Mesh(new THREE.CylinderGeometry(0.13,0.12,0.6,10), shirtMat); up.position.set(sx,1.25,0); up.rotation.z=side*0.16; g.add(up);
            const fore=new THREE.Mesh(new THREE.CylinderGeometry(0.11,0.1,0.5,10), p.outfit==='labcoat'?M(p.coat,{roughness:0.85}):skinMat); fore.position.set(sx*1.04,0.82,0); fore.rotation.z=side*0.16; g.add(fore);
            const hand=new THREE.Mesh(new THREE.SphereGeometry(0.12,12,12), skinMat); hand.position.set(sx*1.08,0.56,0); g.add(hand);
        });
        // lab coat overlay
        if (p.outfit==='labcoat'){
            const coatMat=M(p.coat,{roughness:0.85});
            const coat=new THREE.Mesh(new THREE.CylinderGeometry(0.46,0.54,1.2,18,1,true), coatMat); coat.position.y=0.95; g.add(coat);
            const collar=new THREE.Mesh(new THREE.CylinderGeometry(0.3,0.42,0.2,16,1,true), coatMat); collar.position.y=1.5; g.add(collar);
            const badge=new THREE.Mesh(new THREE.BoxGeometry(0.12,0.16,0.02), M(p.accent||'#2563eb',{roughness:0.4})); badge.position.set(0.2,1.2,0.46); g.add(badge);
        }
        // neck + head (slightly elongated)
        const neck=new THREE.Mesh(new THREE.CylinderGeometry(0.13,0.15,0.2,10), skinMat); neck.position.y=1.66; g.add(neck);
        const head=new THREE.Mesh(new THREE.SphereGeometry(0.4,26,26), skinMat); head.position.y=1.98; head.scale.set(1,1.08,1.02); g.add(head);
        const FY=1.98, FZ=0.37;
        // ears
        [-1,1].forEach(side=>{ const ear=new THREE.Mesh(new THREE.SphereGeometry(0.08,10,10), skinMat); ear.position.set(side*0.39,FY,0); ear.scale.set(0.6,1,0.7); g.add(ear); });
        // eyes (white + iris + pupil)
        [-0.15,0.15].forEach(sx=>{
            const w=new THREE.Mesh(new THREE.SphereGeometry(0.078,14,14), M('#ffffff',{roughness:0.3})); w.position.set(sx,FY+0.05,FZ-0.02); w.scale.set(1,0.72,0.5); g.add(w);
            const iris=new THREE.Mesh(new THREE.SphereGeometry(0.04,12,12), M('#3a2f25')); iris.position.set(sx,FY+0.05,FZ+0.02); iris.scale.set(1,1,0.5); g.add(iris);
            const pup=new THREE.Mesh(new THREE.SphereGeometry(0.02,8,8), M('#0e0e16')); pup.position.set(sx,FY+0.05,FZ+0.04); g.add(pup);
        });
        // eyebrows (angle reflects mood)
        [-0.15,0.15].forEach((sx,i)=>{ const br=new THREE.Mesh(new THREE.BoxGeometry(0.14,0.03,0.04), hairMat); br.position.set(sx,FY+0.18,FZ);
            br.rotation.z = p.mood==='smirk' ? (i?0.3:-0.05) : (i?0.09:-0.09); g.add(br); });
        // nose
        const nose=new THREE.Mesh(new THREE.ConeGeometry(0.055,0.15,8), skinMat); nose.position.set(0,FY-0.04,FZ+0.03); nose.rotation.x=Math.PI/2; g.add(nose);
        // mouth (smile or smirk)
        if (p.mood==='smirk'){ const m=new THREE.Mesh(new THREE.BoxGeometry(0.15,0.03,0.04), M('#9c4744')); m.position.set(0.02,FY-0.16,FZ); m.rotation.z=0.24; g.add(m); }
        else { const m=new THREE.Mesh(new THREE.TorusGeometry(0.1,0.022,8,16,Math.PI), M('#b5524f')); m.position.set(0,FY-0.14,FZ); m.rotation.z=Math.PI; g.add(m); }
        // facial hair
        if (p.facialHair){ const gt=new THREE.Mesh(new THREE.SphereGeometry(0.1,12,12), hairMat); gt.position.set(0,FY-0.23,FZ-0.05); gt.scale.set(1.1,0.7,0.6); g.add(gt); }
        // hair (cap + back)
        const hairCap=new THREE.Mesh(new THREE.SphereGeometry(0.435,22,22,0,Math.PI*2,0,Math.PI*0.58), hairMat); hairCap.position.y=FY+0.05; hairCap.scale.set(1,1.05,1.04); g.add(hairCap);
        const hairBack=new THREE.Mesh(new THREE.SphereGeometry(0.41,18,18,0,Math.PI*2,Math.PI*0.5,Math.PI*0.4), hairMat); hairBack.position.set(0,FY,-0.05); g.add(hairBack);
        // accessories
        if (p.acc==='goggles'){
            [-0.15,0.15].forEach(sx=>{ const gg=new THREE.Mesh(new THREE.TorusGeometry(0.1,0.035,8,16), M(p.accent,{metalness:0.5,roughness:0.3})); gg.position.set(sx,FY+0.07,FZ-0.02); g.add(gg); });
            const st=new THREE.Mesh(new THREE.TorusGeometry(0.42,0.03,8,24), M(p.accent)); st.rotation.y=Math.PI/2; st.position.y=FY+0.07; g.add(st);
        } else if (p.acc==='hat'){
            const brim=new THREE.Mesh(new THREE.CylinderGeometry(0.56,0.56,0.05,20), M(p.accent)); brim.position.y=FY+0.32; g.add(brim);
            const top=new THREE.Mesh(new THREE.CylinderGeometry(0.34,0.37,0.32,20), M(p.accent)); top.position.y=FY+0.52; g.add(top);
        } else if (p.glasses){
            [-0.15,0.15].forEach(sx=>{ const l=new THREE.Mesh(new THREE.TorusGeometry(0.09,0.016,8,18), M('#1c2230',{metalness:0.3})); l.position.set(sx,FY+0.05,FZ); g.add(l); });
            const brg=new THREE.Mesh(new THREE.BoxGeometry(0.08,0.016,0.016), M('#1c2230')); brg.position.set(0,FY+0.05,FZ); g.add(brg);
        }
        g.userData.head = head;
        return g;
    }

    function initScene() {
        renderer = new THREE.WebGLRenderer({ canvas, antialias:true });
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        resize();
        scene = new THREE.Scene();
        camera = new THREE.PerspectiveCamera(55, shell.clientWidth/shell.clientHeight, 0.1, 400);
        raycaster = new THREE.Raycaster();
        scene.add(new THREE.AmbientLight(0xffffff, isG11 ? 0.55 : 0.85));
        const dir = new THREE.DirectionalLight(0xffffff, isG11 ? 0.7 : 1.0); dir.position.set(6,14,8); scene.add(dir);

        if (isG11) buildArena(); else buildField();

        clock = new THREE.Clock();
        window.addEventListener('resize', resize);
        bindControls(); applyCamera(); animate();
    }

    // ---------------- G11: FORMULA CLASH (lab battle) ----------------
    function buildArena() {
        scene.background = new THREE.Color(0x070b1c);
        scene.fog = new THREE.Fog(0x070b1c, 22, 48);
        const floor = new THREE.Mesh(new THREE.PlaneGeometry(60,60), M(0x0d1430,{metalness:0.3,roughness:0.7}));
        floor.rotation.x = -Math.PI/2; scene.add(floor);
        const grid = new THREE.GridHelper(60, 30, 0x2a3a7a, 0x1b2750); scene.add(grid);
        const pl = new THREE.PointLight(0x22d3ee, 1.4, 40); pl.position.set(0,8,4); scene.add(pl);
        // back lab consoles
        for (let i=-2;i<=2;i++){ const c=new THREE.Mesh(new THREE.BoxGeometry(2.2,2.6,0.6), M(0x16204a,{emissive:0x12306a,emissiveIntensity:0.4})); c.position.set(i*3.2,1.3,-9); scene.add(c); }
        // beakers
        [-5,-3.5,4,5.5].forEach(x=>{ const b=new THREE.Mesh(new THREE.CylinderGeometry(0.25,0.3,0.7,12), M(0x7cf,{transparent:true,opacity:0.6,emissive:0x2266aa,emissiveIntensity:0.5})); b.position.set(x,0.6,-6.5); scene.add(b); });

        player = buildCharacter({ skin:'#f0c08a', hair:'#3a2a18', shirt:'#dfe8f5', pants:'#33406a', accent:'#2563eb', acc:'goggles', outfit:'labcoat', coat:'#f4f6fa', mood:'happy' });
        player.position.set(-3.4, 0, 2.5); player.rotation.y = Math.PI*0.5; scene.add(player);
        rival = buildCharacter({ skin:'#e6b27e', hair:'#3a1414', shirt:'#241c2e', pants:'#2a2230', accent:'#ef4444', acc:'goggles', outfit:'labcoat', coat:'#3a2330', facialHair:true, mood:'smirk' });
        rival.position.set(3.4, 0, 2.5); rival.rotation.y = -Math.PI*0.5; scene.add(rival);

        // attack beam (reused)
        beam = new THREE.Mesh(new THREE.CylinderGeometry(0.08,0.08,1,8), new THREE.MeshBasicMaterial({ color:0x6ee7ff, transparent:true, opacity:0 }));
        beam.rotation.z = Math.PI/2; scene.add(beam);

        cam.target.set(0,1.7,2.5); cam.rad = 11; cam.pol = 1.25; Object.assign(DEF, cam);
    }

    // ---------------- G12: FIELD RESEARCHER (collect samples) ----------------
    function buildField() {
        scene.background = new THREE.Color(0x9fc6e8);
        scene.fog = new THREE.Fog(0xaecbe6, 36, 70);
        const ground = new THREE.Mesh(new THREE.PlaneGeometry(120,120,1,1), M(0x4f7a3a,{roughness:1}));
        ground.rotation.x = -Math.PI/2; scene.add(ground);
        // hills
        for (let i=0;i<10;i++){ const h=new THREE.Mesh(new THREE.SphereGeometry(4+Math.random()*4,16,12), M(0x3f6a30,{roughness:1})); h.position.set((Math.random()-0.5)*70,-2,(Math.random()-0.5)*70-10); h.scale.y=0.4; scene.add(h); }
        // trees
        for (let i=0;i<16;i++){ const t=new THREE.Group(); const tr=new THREE.Mesh(new THREE.CylinderGeometry(0.18,0.24,1.2,8), M(0x6b4423)); tr.position.y=0.6; t.add(tr); const lv=new THREE.Mesh(new THREE.ConeGeometry(0.9,1.8,8), M(0x2f7d3f)); lv.position.y=1.8; t.add(lv); const a=Math.random()*Math.PI*2,r=10+Math.random()*22; t.position.set(Math.cos(a)*r,0,Math.sin(a)*r); scene.add(t); }
        // rocks
        for (let i=0;i<10;i++){ const rk=new THREE.Mesh(new THREE.DodecahedronGeometry(0.5+Math.random()*0.8), M(0x8a8a90,{roughness:1})); const a=Math.random()*Math.PI*2,r=6+Math.random()*20; rk.position.set(Math.cos(a)*r,0.2,Math.sin(a)*r); scene.add(rk); }
        const sun = new THREE.DirectionalLight(0xfff4d6, 0.6); sun.position.set(-8,12,6); scene.add(sun);

        player = buildCharacter({ skin:'#f0c08a', hair:'#2a1a10', shirt:'#b89a5a', pants:'#566b39', accent:'#7a5a2a', acc:'hat' });
        player.position.set(0,0,9); scene.add(player);

        // collectible samples (glowing specimens)
        const COLORS = [0x34d399,0xf59e0b,0x60a5fa,0xa78bfa,0xf472b6,0xfbbf24,0x22d3ee,0xef4444];
        const N = 8;
        for (let i=0;i<N;i++){
            const a=(i/N)*Math.PI*2 + 0.3, r=8+Math.random()*9;
            const g=new THREE.Group();
            const core=new THREE.Mesh(new THREE.OctahedronGeometry(0.7), new THREE.MeshStandardMaterial({color:COLORS[i],emissive:COLORS[i],emissiveIntensity:0.7,roughness:0.3,metalness:0.4}));
            core.position.y=1; g.add(core);
            const pad=new THREE.Mesh(new THREE.CylinderGeometry(1,1,0.1,20), new THREE.MeshBasicMaterial({color:COLORS[i],transparent:true,opacity:0.4})); pad.position.y=0.06; g.add(pad);
            g.position.set(Math.cos(a)*r,0,Math.sin(a)*r);
            g.userData = { core, collected:false, hit:core };
            scene.add(g); samples.push(g);
        }
        cam.target.set(0,1.6,0); cam.rad = 22; cam.pol = 1.1; Object.assign(DEF, cam);
    }

    function resize() { renderer.setSize(shell.clientWidth, shell.clientHeight, false); if (camera){ camera.aspect=shell.clientWidth/shell.clientHeight; camera.updateProjectionMatrix(); } }
    function applyCamera() {
        const x=cam.target.x+cam.rad*Math.sin(cam.pol)*Math.sin(cam.az), y=cam.target.y+cam.rad*Math.cos(cam.pol), z=cam.target.z+cam.rad*Math.sin(cam.pol)*Math.cos(cam.az);
        camera.position.set(x,y,z); camera.lookAt(cam.target);
    }
    function bindControls() {
        let dragging=false, lx=0, ly=0, moved=0;
        const down=e=>{ dragging=true; moved=0; const t=e.touches?e.touches[0]:e; lx=t.clientX; ly=t.clientY; };
        const move=e=>{ const t=e.touches?e.touches[0]:e; if(dragging){ const dx=t.clientX-lx,dy=t.clientY-ly; lx=t.clientX; ly=t.clientY; moved+=Math.abs(dx)+Math.abs(dy); cam.az-=dx*0.005; cam.pol=Math.max(0.4,Math.min(1.45,cam.pol-dy*0.004)); applyCamera(); } };
        const up=e=>{ if(dragging && moved<6 && !isG11){ const t=(e.changedTouches?e.changedTouches[0]:e); pickSample(t.clientX,t.clientY); } dragging=false; };
        canvas.addEventListener('mousedown',down); window.addEventListener('mousemove',move); window.addEventListener('mouseup',up);
        canvas.addEventListener('touchstart',down,{passive:true}); canvas.addEventListener('touchmove',e=>{ move(e); if(dragging) e.preventDefault(); },{passive:false}); canvas.addEventListener('touchend',up);
        canvas.addEventListener('wheel',e=>{ cam.rad=Math.max(7,Math.min(40,cam.rad+Math.sign(e.deltaY)*1.8)); applyCamera(); e.preventDefault(); },{passive:false});
        document.getElementById('srCtrlZoomIn').onclick=()=>{ cam.rad=Math.max(7,cam.rad-3); applyCamera(); };
        document.getElementById('srCtrlZoomOut').onclick=()=>{ cam.rad=Math.min(40,cam.rad+3); applyCamera(); };
        document.getElementById('srCtrlReset').onclick=()=>{ Object.assign(cam,{az:DEF.az,pol:DEF.pol,rad:DEF.rad}); cam.target.copy(DEF.target); applyCamera(); };
    }
    function pickSample(cx, cy) {
        if (!G.running || G.quizOpen) return;
        const r=canvas.getBoundingClientRect();
        mouseNDC.x=((cx-r.left)/r.width)*2-1; mouseNDC.y=-((cy-r.top)/r.height)*2+1;
        raycaster.setFromCamera(mouseNDC,camera);
        const hits=raycaster.intersectObjects(samples.map(s=>s.userData.hit),false);
        if (hits.length){ const s=samples.find(x=>x.userData.hit===hits[0].object); if(s && !s.userData.collected) openQuiz(s); }
    }

    // ================= GAME =================
    const G = { running:false, quizOpen:false, score:0, correct:0, wrong:0, timeLeft:300, responseTimes:[], qStart:0,
        youHP:100, rivalHP:100, collected:0, total:8, curSample:null };
    let timerId=null;
    const el=id=>document.getElementById(id);

    function startGame() {
        if (!questionsReady) { el('srHint').textContent='Loading questions…'; return; }
        Object.assign(G,{ running:true, quizOpen:false, score:0, correct:0, wrong:0, timeLeft:300, responseTimes:[],
            youHP:100, rivalHP:100, collected:0, total: isG11?5:samples.length, curSample:null });
        qPtr=0;
        el('srStart').hidden=true; el('srSummary').hidden=true;
        if (isG11) {
            el('srHpWrap').classList.add('show');
            el('srProgLabel').textContent='Battle';
            el('srHint').textContent='⚔️ Formula Clash! Sagutin nang tama at mabilis para atakihin ang kalaban!';
            updateHUD(); askBattleQuestion();
        } else {
            samples.forEach(s=>{ s.userData.collected=false; s.visible=true; });
            el('srProgLabel').textContent='Samples Collected';
            el('srHint').textContent='🔬 Mag-click ng glowing SAMPLE para mag-research at i-collect!';
            updateHUD();
        }
        clearInterval(timerId);
        timerId=setInterval(()=>{ if(!G.running) return; G.timeLeft--; updateHUD(); if(G.timeLeft<=0) endGame(); },1000);
    }

    function updateHUD() {
        el('srScore').textContent=G.score;
        const m=Math.floor(G.timeLeft/60), s=G.timeLeft%60; el('srTime').textContent=(m<10?'0':'')+m+':'+(s<10?'0':'')+s;
        el('srTotalPts').textContent=CFG.myBest+G.score; el('srBadges').textContent=Math.min(12,Math.floor((CFG.myBest+G.score)/100));
        if (isG11){ el('srHpYou').style.width=Math.max(0,G.youHP)+'%'; el('srHpRival').style.width=Math.max(0,G.rivalHP)+'%'; el('srBar').style.width=(100-Math.max(0,G.rivalHP))+'%'; }
        else { el('srBar').style.width=Math.round(G.collected/G.total*100)+'%'; }
    }

    function renderQuiz(q, onAnswer) {
        G.qStart=performance.now(); q._answered=false;
        el('srQTopic').textContent=q.topic||'Science';
        el('srQuizText').textContent=q.question;
        const wrap=el('srQuizOpts'); wrap.innerHTML=''; const L=['A','B','C','D','E']; q._btns=[];
        q.options.forEach((opt,i)=>{ const b=document.createElement('button'); b.className='sr-opt'; b.innerHTML='<span class="lt">'+L[i]+'</span> '+opt; b.onclick=()=>{ if(q._answered) return; q._answered=true; G.responseTimes.push(performance.now()-G.qStart); q._btns.forEach(x=>x.disabled=true); const ok=(i===q.correct_index); if(ok){b.classList.add('correct');}else{b.classList.add('wrong'); q._btns[q.correct_index].classList.add('correct');} onAnswer(ok); }; wrap.appendChild(b); q._btns.push(b); });
        el('srQuiz').classList.add('show');
    }

    // ---- G11 battle flow ----
    function askBattleQuestion() {
        if (!G.running) return;
        el('srQuizTitle').textContent='Formula Clash';
        const q=nextQ();
        renderQuiz(q, (ok)=>{
            if (ok){ G.correct++; G.score+=80; flashBeam(player,rival,0x6ee7ff); G.rivalHP-=20; }
            else  { G.wrong++; flashBeam(rival,player,0xff6b6b); G.youHP-=20; }
            updateHUD();
            setTimeout(()=>{
                el('srQuiz').classList.remove('show');
                if (G.rivalHP<=0){ G.battleWon=true; endGame(); return; }
                if (G.youHP<=0){ G.battleWon=false; endGame(); return; }
                setTimeout(askBattleQuestion, 350);
            }, ok?700:1000);
        });
    }
    function flashBeam(from, to, color) {
        if (!beam) return;
        const a=from.position, b=to.position;
        beam.material.color.setHex(color); beam.material.opacity=0.9;
        beam.position.set((a.x+b.x)/2, 1.6, (a.z+b.z)/2);
        beam.scale.y = Math.abs(b.x-a.x);
        to.position.y = 0.15; // little hit hop
        setTimeout(()=>{ if(beam) beam.material.opacity=0; if(to) to.position.y=0; }, 220);
    }

    // ---- G12 collect flow ----
    function openQuiz(sample) {
        G.quizOpen=true; G.curSample=sample;
        cam.target.copy(sample.position); cam.target.y=1.4; applyCamera();
        el('srQuizTitle').textContent='Field Sample';
        const q=nextQ();
        renderQuiz(q, (ok)=>{
            if (ok){ G.correct++; G.score+=70; }
            else { G.wrong++; }
            updateHUD();
            setTimeout(()=>{
                el('srQuiz').classList.remove('show'); G.quizOpen=false;
                if (ok){ sample.userData.collected=true; sample.visible=false; G.collected++; el('srHint').textContent='✓ Sample collected! ('+G.collected+'/'+G.total+')'; }
                else { el('srHint').textContent='❌ Mali — subukan ulit ang ibang sample.'; }
                cam.target.set(0,1.6,0); applyCamera(); updateHUD();
                if (G.collected>=G.total){ G.fieldWon=true; endGame(); }
            }, ok?700:1000);
        });
    }

    function endGame() {
        G.running=false; clearInterval(timerId); el('srQuiz').classList.remove('show');
        const total=G.correct+G.wrong, acc=total?Math.round(G.correct/total*100):0;
        const avg=G.responseTimes.length?Math.round(G.responseTimes.reduce((a,b)=>a+b,0)/G.responseTimes.length):0;
        let won, goalVal, goalK;
        if (isG11){ won=G.rivalHP<=0; goalVal=won?'WIN':'LOSE'; goalK='Battle'; el('srSumTitle').textContent=won?'🏆 Rival Defeated!':'💥 You Lost the Clash'; }
        else { won=G.collected>=G.total; goalVal=G.collected+'/'+G.total; goalK='Samples'; el('srSumTitle').textContent=won?'🏆 Crisis Solved!':'⏱ Time\'s Up!'; }
        el('srSumScore').textContent=G.score; el('srSumAcc').textContent=acc+'%';
        el('srSumGoal').textContent=goalVal; el('srSumGoalK').textContent=goalK;
        el('srSumCorrect').textContent=G.correct; el('srSumWrong').textContent=G.wrong;
        el('srSummary').hidden=false; el('srSaveMsg').textContent='Saving to leaderboard…';
        fetch(CFG.urlScore,{ method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CFG.csrf,'Accept':'application/json'},
            body: JSON.stringify({ score:G.score, accuracy:acc, correct:G.correct, incorrect:G.wrong, max_combo:G.correct, avg_response_ms:avg }) })
          .then(r=>r.json()).then(d=>{ el('srSaveMsg').textContent=d.ok?'✓ Saved! Personal best: '+(d.personalBest ?? G.score):'Saved locally.'; })
          .catch(()=>{ el('srSaveMsg').textContent='Could not save (check connection).'; });
    }

    el('srStartBtn').onclick=startGame; el('srReplayBtn').onclick=startGame;

    // start overlay description
    el('srStartDesc').innerHTML = isG11
        ? '<b>Grade 11 — Formula Clash ⚔️</b><br>Labanan ang kalabang scientist! Sagutin ang Physics at Chemistry nang tama at mabilis para atakihin siya. Ubusin ang HP niya para manalo!'
        : '<b>Grade 12 — Field Researcher 🔬</b><br>Galugarin ang field at mag-click ng mga glowing SAMPLE. Sagutin ang Earth Science / Biology nang tama para i-collect ang bawat sample at lutasin ang crisis!';

    function animate() {
        requestAnimationFrame(animate);
        const dt=Math.min(clock.getDelta(),0.05), t=clock.elapsedTime;
        samples.forEach(s=>{ if(s.visible){ s.userData.core.rotation.y+=dt*1.3; s.userData.core.position.y=1+Math.sin(t*2+s.position.x)*0.15; } });
        if (player) player.position.y = Math.sin(t*1.5)*0.03;
        if (rival)  rival.position.y  = Math.sin(t*1.5+1)*0.03;
        renderer.render(scene, camera);
    }

    function boot(){ if(typeof THREE==='undefined'){ setTimeout(boot,120); return; } try{ initScene(); el('srHint').textContent = isG11?'⚔️ Formula Clash — handa ka na ba?':'🔬 Field Researcher — handa ka na ba?'; }catch(e){ console.error('Strata Rush',e); } }
    if (document.readyState==='loading') document.addEventListener('DOMContentLoaded',boot); else boot();
})();
</script>
@endsection