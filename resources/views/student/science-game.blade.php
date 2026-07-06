@extends('layouts.app')
@section('title', 'Strata Rush — 3D Science Game')

@section('content')
<style>
    #sr-shell { position: relative; width: 100%; height: calc(100vh - 110px); min-height: 560px;
        border-radius: 18px; overflow: hidden; background: radial-gradient(120% 90% at 50% 0%, #0a1836 0%, #05070f 55%, #03050c 100%); box-shadow: 0 12px 44px rgba(0,0,0,.35); font-family: 'Plus Jakarta Sans', sans-serif; }
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
    .sr-vs { font-family:'Outfit',sans-serif; font-weight:800; font-size:20px; color:#fbbf24; }

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
    .sr-panel { position: relative; overflow: hidden; width: 100%; max-width: 440px; border-radius: 22px; padding: 30px 26px; color: #e7edf7; text-align: center;
        background: linear-gradient(180deg,#131f42,#0a1124); border: 1px solid rgba(91,141,239,.45);
        box-shadow: 0 24px 70px rgba(0,0,0,.6), inset 0 1px 0 rgba(255,255,255,.06); }
    .sr-panel::before { content:''; position:absolute; top:0; left:50%; transform:translateX(-50%);
        width:62%; height:3px; border-radius:0 0 6px 6px;
        background:linear-gradient(90deg,transparent,#5b8def,#7cb0ff,#5b8def,transparent);
        box-shadow:0 0 18px rgba(124,176,255,.7); }
    .sr-panel h2 { font-family: 'Outfit', sans-serif; font-size: 30px; font-weight: 800; margin: 2px 0 6px; letter-spacing: -.02em; text-shadow: 0 2px 20px rgba(124,176,255,.25); }
    .sr-panel .accent { color: #7cb0ff; text-shadow: 0 0 22px rgba(124,176,255,.85), 0 0 8px rgba(124,176,255,.6); }
    .sr-panel p { color: #9fb3d4; font-size: 13.5px; line-height: 1.6; margin: 0 0 16px; }
    .sr-btn { display: inline-block; background: linear-gradient(135deg,#3b82f6,#1d4ed8); color: #fff; border: none; border-radius: 14px;
        padding: 13px 26px; font-weight: 800; font-size: 14.5px; cursor: pointer; text-decoration: none; box-shadow: 0 8px 24px rgba(37,99,235,.45);
        transition: transform .16s cubic-bezier(.34,1.56,.64,1), box-shadow .2s, filter .2s; }
    .sr-btn:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 12px 32px rgba(37,99,235,.62); filter: brightness(1.08); }
    .sr-btn:active { transform: translateY(0) scale(.98); }
    .sr-btn.ghost:hover { background: rgba(124,176,255,.10); border-color: rgba(124,176,255,.5); box-shadow: none; filter: none; }
    .sr-btn.ghost { background: transparent; border: 1px solid rgba(255,255,255,.2); box-shadow: none; }
    .sr-btnrow { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 8px; }
    .sr-final { font-family: 'Outfit', sans-serif; font-size: 48px; font-weight: 800; color: #7cb0ff; line-height: 1; text-shadow: 0 0 30px rgba(124,176,255,.6); }
    .sr-sumgrid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 14px 0; }
    .sr-sumgrid div { background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); border-radius: 12px; padding: 11px; }
    .sr-sumgrid .v { font-size: 22px; font-weight: 800; font-family: 'Outfit', sans-serif; color: #fff; }
    .sr-sumgrid .k { font-size: 10.5px; color: #93a7cc; text-transform: uppercase; letter-spacing: .04em; margin-top: 4px; }
    @media (max-width: 820px) { .sr-progress { display:none; } .sr-stats { top:auto; bottom:80px; right:16px; } }

    /* --- Step 5: gameplay systems UI --- */
    .sr-extra { display:flex; align-items:center; gap:10px; margin-top:10px; min-height:22px; }
    .sr-lives { font-size:16px; letter-spacing:2px; }
    .sr-combo-pill { background:linear-gradient(135deg,#f59e0b,#ef4444); color:#fff; font-weight:800;
        font-size:12.5px; padding:3px 11px; border-radius:999px; box-shadow:0 2px 10px rgba(245,158,11,.4); }
    .sr-diff { font-size:10px; font-weight:800; letter-spacing:.06em; padding:3px 9px; border-radius:999px;
        background:rgba(124,176,255,.18); color:#9ec5ff; text-transform:uppercase; }
    .sr-hint-btn { width:100%; margin-top:10px; background:rgba(124,176,255,.12); color:#bcd6ff;
        border:1px solid rgba(124,176,255,.3); border-radius:12px; padding:10px; font-weight:800; font-size:13px; cursor:pointer; transition:all .15s; }
    .sr-hint-btn:hover:not(:disabled) { background:rgba(124,176,255,.22); }
    .sr-hint-btn:disabled { opacity:.4; cursor:not-allowed; }
    .sr-opt.dim { opacity:.32; text-decoration:line-through; }

    /* --- Premium polish: attention pulse on the start button --- */
    #srStartBtn { animation: srPulse 2.2s ease-in-out infinite; }
    @keyframes srPulse {
        0%, 100% { box-shadow: 0 8px 24px rgba(37,99,235,.45); }
        50%      { box-shadow: 0 8px 34px rgba(37,99,235,.75), 0 0 0 4px rgba(37,99,235,.14); }
    }
    @media (prefers-reduced-motion: reduce) { #srStartBtn { animation: none; } }
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
            <div class="sr-extra">
                <span class="sr-lives" id="srLives"></span>
                <span class="sr-combo-pill" id="srComboPill" style="display:none;">🔥 x<span id="srCombo">1</span></span>
            </div>
            <div class="sr-prow">
                <div>Score<b id="srScore">0</b></div>
                <div style="text-align:right;">Time<b id="srTime">05:00</b></div>
            </div>
        </div>

        <div class="sr-quiz sr-glass" id="srQuiz">
            <div class="qtop"><h3 id="srQuizTitle">Quiz</h3><div style="display:flex;gap:6px;align-items:center;"><span class="sr-diff" id="srDiff">EASY</span><span class="qtopic" id="srQTopic">SCIENCE</span></div></div>
            <div class="qtext" id="srQuizText">—</div>
            <div class="sr-opts" id="srQuizOpts"></div>
            <button class="sr-hint-btn" id="srHintBtn">💡 Hint (<span id="srHints">3</span>)</button>
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
    let QBANK = [], QBUCK = {easy:[],medium:[],hard:[]}, questionsReady = false, qPtr = 0;
    const FALLBACK = isG11 ? [
        {topic:'Physics', difficulty:'easy', question:'What is the SI unit of force?', options:['Joule','Watt','Newton','Pascal'], correct_index:2},
        {topic:'Chemistry', difficulty:'easy', question:'Which particle carries a negative charge?', options:['Proton','Neutron','Electron','Nucleus'], correct_index:2},
        {topic:'Physics', difficulty:'medium', question:'F = ma. If m = 2 kg and a = 3 m/s², what is F?', options:['5 N','6 N','1.5 N','8 N'], correct_index:1},
        {topic:'Chemistry', difficulty:'medium', question:'What is the chemical formula of water?', options:['CO2','H2O','O2','NaCl'], correct_index:1},
        {topic:'Physics', difficulty:'hard', question:'Which quantity is measured in watts?', options:['Energy','Power','Momentum','Force'], correct_index:1},
    ] : [
        {topic:'Earth Science', difficulty:'easy', question:'Which rock type forms from cooled magma or lava?', options:['Sedimentary','Metamorphic','Igneous','Fossil'], correct_index:2},
        {topic:'Biology', difficulty:'easy', question:'Which organelle is the "powerhouse of the cell"?', options:['Nucleus','Ribosome','Mitochondria','Vacuole'], correct_index:2},
        {topic:'Earth Science', difficulty:'medium', question:'Which layer of the Earth do we live on?', options:['Mantle','Outer core','Crust','Inner core'], correct_index:2},
        {topic:'Biology', difficulty:'medium', question:'Which process do plants use to make food?', options:['Respiration','Photosynthesis','Digestion','Fermentation'], correct_index:1},
        {topic:'Earth Science', difficulty:'hard', question:'What is the primary gas driving the greenhouse effect?', options:['Oxygen','Nitrogen','Carbon dioxide','Hydrogen'], correct_index:2},
    ];
    function bucketize() {
        QBUCK = { easy:[], medium:[], hard:[] };
        QBANK.forEach(q=>{ const d=(q.difficulty||'easy').toLowerCase(); (QBUCK[d]||QBUCK.easy).push(q); });
        ['easy','medium','hard'].forEach(d=>{ if(!QBUCK[d].length) QBUCK[d]=QBANK.slice(); });
    }
    function pickDifficulty() {
        const r=G.recentAcc.slice(-4); const a=r.length?r.reduce((x,y)=>x+y,0)/r.length:0.5;
        return a>=0.75?'hard':(a>=0.45?'medium':'easy');
    }
    function comboMult(){ return 1 + Math.min(Math.max(G.combo-1,0),5)*0.2; }   // up to 2x at a 6-streak
    function nextQ() {
        if (!QBANK.length){ QBANK=FALLBACK.slice(); bucketize(); }
        const d=pickDifficulty(); const pool=(QBUCK[d]&&QBUCK[d].length)?QBUCK[d]:QBANK;
        const q=Object.assign({}, pool[Math.floor(Math.random()*pool.length)]); q._diff=d; return q;
    }
    fetch(CFG.urlQuestions, { headers:{Accept:'application/json'} }).then(r=>r.json()).then(d=>{
        const qs = (d.questions && d.questions.length) ? d.questions : FALLBACK;
        QBANK = qs.map(q=>Object.assign({}, q)).sort(()=>Math.random()-0.5); bucketize();
        questionsReady = true; document.getElementById('srLoadMsg').textContent = QBANK.length + ' questions loaded ✓';
    }).catch(()=>{ QBANK = FALLBACK.slice(); bucketize(); questionsReady = true; document.getElementById('srLoadMsg').textContent = 'Offline mode'; });

    // ================= THREE.JS =================
    const canvas = document.getElementById('sr-canvas'), shell = document.getElementById('sr-shell');
    let renderer, scene, camera, raycaster, mouseNDC = new THREE.Vector2(), clock;
    const samples = [];   // G12 collectibles
    let player, rival, beam;
    const actors = [];    // animated characters (idle breathing + attack/hurt)
    const birds = [];     // flying birds (ambient life)
    const clouds = [];    const butterflies = [];    const swayTrees = [];
    const sparks = [];    // transient hit effects (rings + particles)

    function registerActor(ch, lungeX){
        ch.userData.baseX=ch.position.x; ch.userData.baseY=ch.position.y; ch.userData.baseZ=ch.position.z;
        ch.userData.lungeX=lungeX||0; ch.userData.phase=Math.random()*Math.PI*2;
        ch.userData.atk=null; ch.userData.hurt=null;
        ch.traverse(o=>{ if(o.isMesh) o.castShadow=true; });
        actors.push(ch);
    }
    function playAttack(ch){ if(ch&&ch.userData) ch.userData.atk={t:0}; }
    function playHurt(ch){ if(ch&&ch.userData) ch.userData.hurt={t:0}; }
    function hitSpark(x,y,z,color){
        if(!scene) return;
        const ring=new THREE.Mesh(new THREE.RingGeometry(0.1,0.2,20), new THREE.MeshBasicMaterial({color,transparent:true,opacity:0.9,side:THREE.DoubleSide}));
        ring.position.set(x,y,z); if(camera) ring.lookAt(camera.position); scene.add(ring); sparks.push({m:ring,t:0,dur:0.4,ring:true});
        for(let i=0;i<7;i++){ const pc=new THREE.Mesh(new THREE.SphereGeometry(0.05,6,6), new THREE.MeshBasicMaterial({color,transparent:true}));
            const a=Math.random()*Math.PI*2, sp=1.5+Math.random()*2.2; pc.position.set(x,y,z); scene.add(pc);
            sparks.push({m:pc,t:0,dur:0.5,vx:Math.cos(a)*sp,vy:1.4+Math.random()*2.2,vz:Math.sin(a)*sp}); }
    }
    const cam = { az: 0.0, pol: 1.15, rad: 18, target: new THREE.Vector3(0, 1.6, 0) };
    const DEF = Object.assign({}, cam);

    const M = (c, opt) => new THREE.MeshStandardMaterial(Object.assign({ color:c, roughness:0.7 }, opt||{}));

    // ---- REALISTIC character builder (head, face, hair, clothes, accessories, mood) ----
    function buildCharacter(pal) {
        const p = Object.assign({ mood:'happy', outfit:'casual', coat:'#3b4356',
            glasses:false, facialHair:false, acc:'none' }, pal);
        const g = new THREE.Group();
        const skinMat=M(p.skin,{roughness:0.62}), shirtMat=M(p.shirt,{roughness:0.82}),
              pantsMat=M(p.pants,{roughness:0.9}), hairMat=M(p.hair,{roughness:0.92});
        const foreMat = p.outfit==='labcoat'?M(p.coat,{roughness:0.85}):skinMat;
        // slim legs + shoes
        [-0.13,0.13].forEach(sx=>{
            const up=new THREE.Mesh(new THREE.CylinderGeometry(0.12,0.1,0.52,10), pantsMat); up.position.set(sx,0.68,0); g.add(up);
            const lo=new THREE.Mesh(new THREE.CylinderGeometry(0.1,0.085,0.5,10), pantsMat); lo.position.set(sx,0.2,0); g.add(lo);
            const foot=new THREE.Mesh(new THREE.BoxGeometry(0.17,0.12,0.36), M('#1a1d26',{roughness:0.5})); foot.position.set(sx,-0.12,0.08); g.add(foot);
        });
        // hips + slim tapered torso + chest (athletic, NOT barrel)
        const hip=new THREE.Mesh(new THREE.CylinderGeometry(0.25,0.22,0.32,14), pantsMat); hip.position.y=1.0; g.add(hip);
        const torso=new THREE.Mesh(new THREE.CylinderGeometry(0.29,0.23,1.0,16), shirtMat); torso.position.y=1.52; g.add(torso);
        const chest=new THREE.Mesh(new THREE.CylinderGeometry(0.3,0.29,0.28,16), shirtMat); chest.position.y=1.95; g.add(chest);
        if (p.outfit==='labcoat'){
            const coatMat=M(p.coat,{roughness:0.85});
            const coat=new THREE.Mesh(new THREE.CylinderGeometry(0.32,0.4,1.15,18,1,true), coatMat); coat.position.y=1.42; g.add(coat);
            const collar=new THREE.Mesh(new THREE.CylinderGeometry(0.2,0.3,0.18,16,1,true), coatMat); collar.position.y=2.0; g.add(collar);
            const badge=new THREE.Mesh(new THREE.BoxGeometry(0.1,0.13,0.02), M(p.accent||'#2563eb',{roughness:0.4})); badge.position.set(0.15,1.7,0.32); g.add(badge);
        }
        // animatable arms (pivot at shoulder)
        function makeArm(){ const arm=new THREE.Group();
            const u=new THREE.Mesh(new THREE.CylinderGeometry(0.08,0.07,0.44,10), shirtMat); u.position.y=-0.22; arm.add(u);
            const fo=new THREE.Mesh(new THREE.CylinderGeometry(0.07,0.06,0.42,10), foreMat); fo.position.y=-0.62; arm.add(fo);
            const h=new THREE.Mesh(new THREE.SphereGeometry(0.085,12,12), skinMat); h.position.y=-0.85; arm.add(h);
            return arm; }
        const armL=makeArm(); armL.position.set(-0.34,1.92,0); g.add(armL);
        const armR=makeArm(); armR.position.set(0.34,1.92,0); g.add(armR);
        // neck + head GROUP (smaller head -> realistic, not chibi)
        const neck=new THREE.Mesh(new THREE.CylinderGeometry(0.09,0.11,0.2,10), skinMat); neck.position.y=2.12; g.add(neck);
        const head=new THREE.Group(); head.position.y=2.42; g.add(head);
        const skull=new THREE.Mesh(new THREE.SphereGeometry(0.33,26,26), skinMat); skull.scale.set(1,1.12,1.02); head.add(skull);
        const FZ=0.31;
        [-1,1].forEach(side=>{ const ear=new THREE.Mesh(new THREE.SphereGeometry(0.065,10,10), skinMat); ear.position.set(side*0.32,0,0); ear.scale.set(0.6,1,0.7); head.add(ear); });
        [-0.12,0.12].forEach(sx=>{
            const w=new THREE.Mesh(new THREE.SphereGeometry(0.062,14,14), M('#ffffff',{roughness:0.3})); w.position.set(sx,0.04,FZ-0.02); w.scale.set(1,0.72,0.5); head.add(w);
            const iris=new THREE.Mesh(new THREE.SphereGeometry(0.032,12,12), M('#3a2f25')); iris.position.set(sx,0.04,FZ+0.01); iris.scale.set(1,1,0.5); head.add(iris);
            const pup=new THREE.Mesh(new THREE.SphereGeometry(0.016,8,8), M('#0e0e16')); pup.position.set(sx,0.04,FZ+0.03); head.add(pup);
        });
        [-0.12,0.12].forEach((sx,i)=>{ const br=new THREE.Mesh(new THREE.BoxGeometry(0.12,0.025,0.035), hairMat); br.position.set(sx,0.15,FZ);
            br.rotation.z = p.mood==='smirk' ? (i?0.3:-0.05) : (i?0.09:-0.09); head.add(br); });
        const nose=new THREE.Mesh(new THREE.ConeGeometry(0.045,0.13,8), skinMat); nose.position.set(0,-0.03,FZ+0.03); nose.rotation.x=Math.PI/2; head.add(nose);
        if (p.mood==='smirk'){ const m=new THREE.Mesh(new THREE.BoxGeometry(0.12,0.025,0.035), M('#9c4744')); m.position.set(0.02,-0.14,FZ); m.rotation.z=0.24; head.add(m); }
        else { const m=new THREE.Mesh(new THREE.TorusGeometry(0.08,0.018,8,16,Math.PI), M('#b5524f')); m.position.set(0,-0.12,FZ); m.rotation.z=Math.PI; head.add(m); }
        if (p.facialHair){ const gt=new THREE.Mesh(new THREE.SphereGeometry(0.08,12,12), hairMat); gt.position.set(0,-0.2,FZ-0.04); gt.scale.set(1.1,0.7,0.6); head.add(gt); }
        const hairCap=new THREE.Mesh(new THREE.SphereGeometry(0.355,22,22,0,Math.PI*2,0,Math.PI*0.6), hairMat); hairCap.position.y=0.04; hairCap.scale.set(1,1.05,1.05); head.add(hairCap);
        const hairBack=new THREE.Mesh(new THREE.SphereGeometry(0.34,18,18,0,Math.PI*2,Math.PI*0.5,Math.PI*0.4), hairMat); hairBack.position.set(0,0,-0.04); head.add(hairBack);
        if (p.acc==='goggles'){
            [-0.12,0.12].forEach(sx=>{ const gg=new THREE.Mesh(new THREE.TorusGeometry(0.08,0.028,8,16), M(p.accent,{metalness:0.5,roughness:0.3})); gg.position.set(sx,0.06,FZ-0.02); head.add(gg); });
            const st=new THREE.Mesh(new THREE.TorusGeometry(0.34,0.025,8,24), M(p.accent)); st.rotation.y=Math.PI/2; st.position.y=0.06; head.add(st);
        } else if (p.acc==='hat'){
            const brim=new THREE.Mesh(new THREE.CylinderGeometry(0.46,0.46,0.04,20), M(p.accent)); brim.position.y=0.28; head.add(brim);
            const top=new THREE.Mesh(new THREE.CylinderGeometry(0.28,0.31,0.28,20), M(p.accent)); top.position.y=0.45; head.add(top);
        } else if (p.glasses){
            [-0.12,0.12].forEach(sx=>{ const l=new THREE.Mesh(new THREE.TorusGeometry(0.072,0.014,8,18), M('#1c2230',{metalness:0.3})); l.position.set(sx,0.04,FZ); head.add(l); });
            const brg=new THREE.Mesh(new THREE.BoxGeometry(0.07,0.014,0.014), M('#1c2230')); brg.position.set(0,0.04,FZ); head.add(brg);
        }
        g.userData.head = head; g.userData.armL = armL; g.userData.armR = armR;
        return g;
    }

    function initScene() {
        renderer = new THREE.WebGLRenderer({ canvas, antialias:true });
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.shadowMap.enabled = true; renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        renderer.outputEncoding = THREE.sRGBEncoding;
        resize();
        scene = new THREE.Scene();
        camera = new THREE.PerspectiveCamera(55, shell.clientWidth/shell.clientHeight, 0.1, 400);
        raycaster = new THREE.Raycaster();
        scene.add(new THREE.AmbientLight(0xffffff, isG11 ? 0.5 : 0.8));
        const dir = new THREE.DirectionalLight(0xffffff, isG11 ? 0.8 : 1.05); dir.position.set(6,16,8);
        dir.castShadow = true; dir.shadow.mapSize.set(1024,1024);
        dir.shadow.camera.near=1; dir.shadow.camera.far=70;
        dir.shadow.camera.left=-24; dir.shadow.camera.right=24; dir.shadow.camera.top=24; dir.shadow.camera.bottom=-24;
        dir.shadow.bias=-0.0004; scene.add(dir);

        if (isG11) buildArena(); else buildField();

        clock = new THREE.Clock();
        window.addEventListener('resize', resize);
        bindControls(); applyCamera(); animate();
    }

    // ---- shared helpers: fake contact shadow + lab screen texture ----
    let _shadowTex=null;
    function shadowTexture(){ if(_shadowTex) return _shadowTex;
        const c=document.createElement('canvas'); c.width=c.height=64; const x=c.getContext('2d');
        const g=x.createRadialGradient(32,32,2,32,32,30); g.addColorStop(0,'rgba(0,0,0,0.4)'); g.addColorStop(1,'rgba(0,0,0,0)');
        x.fillStyle=g; x.fillRect(0,0,64,64); _shadowTex=new THREE.CanvasTexture(c); return _shadowTex; }
    function addShadow(x,z,r){ const sh=new THREE.Mesh(new THREE.PlaneGeometry(r*3.4,r*3.4),
        new THREE.MeshBasicMaterial({map:shadowTexture(),transparent:true,depthWrite:false}));
        sh.rotation.x=-Math.PI/2; sh.position.set(x,0.02,z); scene.add(sh); }
    function labScreenTexture(){
        const c=document.createElement('canvas'); c.width=256; c.height=192; const x=c.getContext('2d');
        x.fillStyle='#0a1838'; x.fillRect(0,0,256,192);
        x.strokeStyle='rgba(57,208,255,.18)'; for(let i=0;i<192;i+=14){ x.beginPath(); x.moveTo(0,i); x.lineTo(256,i); x.stroke(); }
        x.fillStyle='#46d3ff'; x.font='bold 26px monospace';
        const L=['H2O','E=mc^2','PV=nRT','F = m a','CO2','1/2mv^2']; 
        L.forEach((t,i)=>{ x.globalAlpha=0.55+Math.random()*0.45; x.fillText(t, 16, 34+i*28); });
        x.globalAlpha=1; return new THREE.CanvasTexture(c);
    }

    // ---------------- G11: FORMULA CLASH (realistic science lab) ----------------
    // ── Rich world: sky, sun, clouds, hills, flora, butterflies ───────────
    function skyTex() {
        const c=document.createElement('canvas'); c.width=16; c.height=256; const g=c.getContext('2d');
        const grd=g.createLinearGradient(0,0,0,256);
        grd.addColorStop(0,'#3f83c9'); grd.addColorStop(0.55,'#8fc0ea'); grd.addColorStop(1,'#e4f2ff');
        g.fillStyle=grd; g.fillRect(0,0,16,256);
        return new THREE.CanvasTexture(c);
    }
    function buildSky() {
        const sky=new THREE.Mesh(new THREE.SphereGeometry(170,32,20),
            new THREE.MeshBasicMaterial({map:skyTex(), side:THREE.BackSide, fog:false}));
        scene.add(sky);
        const sun=new THREE.Mesh(new THREE.SphereGeometry(5,20,20), new THREE.MeshBasicMaterial({color:0xfff6cf, fog:false}));
        sun.position.set(-48,66,-88); scene.add(sun);
        [10,16].forEach((r,i)=>{ const gl=new THREE.Mesh(new THREE.SphereGeometry(r,20,20),
            new THREE.MeshBasicMaterial({color:0xfff0b0, transparent:true, opacity:i?0.10:0.26, fog:false}));
            gl.position.copy(sun.position); scene.add(gl); });
    }
    function makeCloud() {
        const g=new THREE.Group();
        const m=new THREE.MeshStandardMaterial({color:0xffffff, roughness:1, transparent:true, opacity:0.92, fog:true});
        const n=3+Math.floor(Math.random()*3);
        for(let i=0;i<n;i++){ const s=2+Math.random()*3.2;
            const puff=new THREE.Mesh(new THREE.SphereGeometry(s,8,7), m);
            puff.position.set((Math.random()-0.5)*9,(Math.random()-0.5)*1.6,(Math.random()-0.5)*4.5); puff.scale.y=0.68; g.add(puff); }
        return g;
    }
    function buildClouds() {
        for(let i=0;i<8;i++){ const c=makeCloud();
            c.position.set((Math.random()-0.5)*150, 30+Math.random()*22, (Math.random()-0.5)*150);
            c.userData.sp=0.5+Math.random()*0.9; scene.add(c); clouds.push(c); }
    }
    function buildScenery() {
        // distant rolling hills (depth)
        for(let i=0;i<11;i++){ const a=(i/11)*Math.PI*2, r=98;
            const h=new THREE.Mesh(new THREE.ConeGeometry(26+Math.random()*12, 15+Math.random()*12, 6),
                M(i%2?0x4a7742:0x56824c,{roughness:1}));
            h.position.set(Math.cos(a)*r, 1, Math.sin(a)*r); h.rotation.y=Math.random(); scene.add(h); }
        // rocks
        for(let i=0;i<12;i++){ const a=Math.random()*Math.PI*2, r=8+Math.random()*20;
            const rk=new THREE.Mesh(new THREE.DodecahedronGeometry(0.4+Math.random()*0.8), M(0x8a8f96,{roughness:1}));
            rk.position.set(Math.cos(a)*r,0.2,Math.sin(a)*r-2); rk.rotation.set(Math.random(),Math.random(),Math.random()); rk.castShadow=true; scene.add(rk); }
        // bushes
        for(let i=0;i<14;i++){ const a=Math.random()*Math.PI*2, r=9+Math.random()*18;
            const bg=new THREE.Group(), bm=M(i%2?0x3f7d34:0x4c8f3e,{roughness:1});
            for(let j=0;j<3;j++){ const b=new THREE.Mesh(new THREE.SphereGeometry(0.5+Math.random()*0.3,8,8), bm);
                b.position.set((Math.random()-0.5)*0.8,0.4,(Math.random()-0.5)*0.8); b.castShadow=true; bg.add(b); }
            bg.position.set(Math.cos(a)*r,0,Math.sin(a)*r-2); scene.add(bg); }
        // flowers
        const fcols=[0xff6b8a,0xffd24c,0xa78bfa,0xffffff,0xff9f4c];
        for(let i=0;i<28;i++){ const a=Math.random()*Math.PI*2, r=6+Math.random()*16;
            const fg=new THREE.Group();
            const stem=new THREE.Mesh(new THREE.CylinderGeometry(0.02,0.02,0.4,5), M(0x3f7d34)); stem.position.y=0.2; fg.add(stem);
            const head=new THREE.Mesh(new THREE.SphereGeometry(0.09,7,7),
                new THREE.MeshStandardMaterial({color:fcols[i%fcols.length], roughness:0.85})); head.position.y=0.42; fg.add(head);
            fg.position.set(Math.cos(a)*r,0,Math.sin(a)*r-2); scene.add(fg); }
        // grass tufts
        const gm=M(0x4c8f3e,{roughness:1});
        for(let i=0;i<55;i++){ const a=Math.random()*Math.PI*2, r=5+Math.random()*20;
            const tuft=new THREE.Mesh(new THREE.ConeGeometry(0.12,0.5,4), gm);
            tuft.position.set(Math.cos(a)*r,0.25,Math.sin(a)*r-2); scene.add(tuft); }
        // small pond
        const pond=new THREE.Mesh(new THREE.CircleGeometry(4,32),
            new THREE.MeshStandardMaterial({color:0x3b82c4, roughness:0.15, metalness:0.35, transparent:true, opacity:0.86}));
        pond.rotation.x=-Math.PI/2; pond.position.set(-16,0.02,11); scene.add(pond);
    }
    function makeButterfly() {
        const g=new THREE.Group(); const col=[0xff6b8a,0xffd24c,0xa78bfa,0x6ee7ff][Math.floor(Math.random()*4)];
        const wm=new THREE.MeshStandardMaterial({color:col, roughness:0.6, side:THREE.DoubleSide, transparent:true, opacity:0.95});
        const wL=new THREE.Mesh(new THREE.CircleGeometry(0.14,8), wm); wL.position.x=-0.1; g.add(wL);
        const wR=new THREE.Mesh(new THREE.CircleGeometry(0.14,8), wm); wR.position.x=0.1; g.add(wR);
        g.userData={wL,wR}; return g;
    }
    function buildButterflies() {
        for(let i=0;i<6;i++){ const b=makeButterfly();
            b.userData.cx=(Math.random()-0.5)*18; b.userData.cz=(Math.random()-0.5)*18-2;
            b.userData.r=1.5+Math.random()*3; b.userData.y=0.8+Math.random()*1.3;
            b.userData.a=Math.random()*6.28; b.userData.sp=0.8+Math.random()*0.8; b.userData.ph=Math.random()*6.28;
            scene.add(b); butterflies.push(b); }
    }

    // ── Nature: trees + birds (outdoor world) ──────────────────────────────
    function buildTree(x, z, sc) {
        const g = new THREE.Group();
        const trunk = new THREE.Mesh(new THREE.CylinderGeometry(0.16*sc,0.28*sc,1.7*sc,8), M(0x6d4a28,{roughness:1}));
        trunk.position.y=0.85*sc; trunk.castShadow=true; g.add(trunk);
        const greens=[0x3f7d34,0x4c8f3e,0x357029];
        [[0,2.1,0,1.15],[0.55,2.55,0.2,0.85],[-0.5,2.45,-0.2,0.8],[0,2.95,0,0.7]].forEach((b,i)=>{
            const f=new THREE.Mesh(new THREE.SphereGeometry(b[3]*sc,9,9), M(greens[i%3],{roughness:1}));
            f.position.set(b[0]*sc,b[1]*sc,b[2]*sc); f.castShadow=true; g.add(f);
        });
        g.position.set(x,0,z); g.rotation.y=Math.random()*Math.PI; return g;
    }
    function buildPine(x, z, sc) {
        const g = new THREE.Group();
        const trunk = new THREE.Mesh(new THREE.CylinderGeometry(0.14*sc,0.2*sc,1.2*sc,8), M(0x6d4a28,{roughness:1}));
        trunk.position.y=0.6*sc; trunk.castShadow=true; g.add(trunk);
        [[1.5,1.3],[2.3,1.0],[3.0,0.68]].forEach(([y,r])=>{
            const c=new THREE.Mesh(new THREE.ConeGeometry(r*sc,1.25*sc,9), M(0x2f6b2c,{roughness:1}));
            c.position.y=y*sc; c.castShadow=true; g.add(c);
        });
        g.position.set(x,0,z); return g;
    }
    function buildTrees() {
        const N=18;
        for(let i=0;i<N;i++){
            const a=(i/N)*Math.PI*2 + (Math.random()-0.5)*0.25;
            const r=24 + Math.random()*14;
            const x=Math.cos(a)*r, z=Math.sin(a)*r - 2;
            const sc=1.3 + Math.random()*1.3;
            const tr=(Math.random()<0.35 ? buildPine(x,z,sc) : buildTree(x,z,sc));
            tr.userData.ph=Math.random()*6.28; swayTrees.push(tr); scene.add(tr);
        }
        [[-14,-13,1.6],[15,-14,1.8],[-18,-6,1.5],[18,-4,1.5]].forEach(([x,z,sc])=>{ const tr=buildTree(x,z,sc); tr.userData.ph=Math.random()*6.28; swayTrees.push(tr); scene.add(tr); });
    }
    function buildBird() {
        const g=new THREE.Group();
        const body=new THREE.Mesh(new THREE.SphereGeometry(0.13,8,8), M(0x2b3038)); body.scale.set(1.7,0.8,0.9); g.add(body);
        const head=new THREE.Mesh(new THREE.SphereGeometry(0.08,8,8), M(0x2b3038)); head.position.set(0.2,0.05,0); g.add(head);
        const wm=M(0x3a4049);
        const wL=new THREE.Mesh(new THREE.BoxGeometry(0.55,0.04,0.24), wm); wL.position.z=-0.28; g.add(wL);
        const wR=new THREE.Mesh(new THREE.BoxGeometry(0.55,0.04,0.24), wm); wR.position.z=0.28; g.add(wR);
        g.userData={wL,wR}; return g;
    }
    function buildBirds() {
        for(let i=0;i<7;i++){
            const b=buildBird();
            b.userData.r=14+Math.random()*16;
            b.userData.y=10+Math.random()*7;
            b.userData.a=Math.random()*Math.PI*2;
            b.userData.sp=0.22+Math.random()*0.28;
            b.userData.ph=Math.random()*Math.PI*2;
            scene.add(b); birds.push(b);
        }
    }

    function buildArena() {
        // ── Outdoor world: daytime sky + grassy clearing ──
        scene.background = new THREE.Color(0x8fc0ea);
        scene.fog = new THREE.Fog(0xcfe3f5, 42, 108);
        scene.add(new THREE.HemisphereLight(0xdcecff, 0x4a7a38, 1.05));
        // grassy ground + dirt clearing where they battle
        const grass = new THREE.Mesh(new THREE.PlaneGeometry(240,240), M(0x5c9440,{roughness:1}));
        grass.rotation.x=-Math.PI/2; grass.receiveShadow=true; scene.add(grass);
        const patch = new THREE.Mesh(new THREE.CircleGeometry(12,48), M(0x836a44,{roughness:1}));
        patch.rotation.x=-Math.PI/2; patch.position.y=0.01; patch.receiveShadow=true; scene.add(patch);
        // battle rings (player / rival)
        [[-3.4,0x22d3ee],[3.4,0xef4444]].forEach(([x,col])=>{
            const ring=new THREE.Mesh(new THREE.RingGeometry(1.3,1.62,40), new THREE.MeshBasicMaterial({color:col,transparent:true,opacity:0.8,side:THREE.DoubleSide}));
            ring.rotation.x=-Math.PI/2; ring.position.set(x,0.03,2.5); scene.add(ring);
        });
        // rich outdoor world
        buildSky();
        buildClouds();
        buildScenery();
        buildTrees();
        buildBirds();
        buildButterflies();

        player = buildCharacter({ skin:'#f0c08a', hair:'#3a2a18', shirt:'#dfe8f5', pants:'#33406a', accent:'#2563eb', acc:'goggles', outfit:'labcoat', coat:'#f4f6fa', mood:'happy' });
        player.position.set(-3.4, 0, 2.5); player.rotation.y = Math.PI*0.5; scene.add(player); registerActor(player, 1);
        rival = buildCharacter({ skin:'#e6b27e', hair:'#3a1414', shirt:'#241c2e', pants:'#2a2230', accent:'#ef4444', acc:'goggles', outfit:'labcoat', coat:'#3a2330', facialHair:true, mood:'smirk' });
        rival.position.set(3.4, 0, 2.5); rival.rotation.y = -Math.PI*0.5; scene.add(rival); registerActor(rival, -1);
        // lab supervisor NPC (watching the clash)
        const npc = buildCharacter({ skin:'#e8c4a0', hair:'#2a2a2a', shirt:'#1f6f5c', pants:'#26303f', accent:'#2563eb', outfit:'labcoat', coat:'#eef2f7', glasses:true, mood:'happy' });
        npc.position.set(0,0,-7.5); npc.scale.setScalar(0.92); scene.add(npc); registerActor(npc, 0);

        beam = new THREE.Mesh(new THREE.CylinderGeometry(0.08,0.08,1,8), new THREE.MeshBasicMaterial({ color:0x6ee7ff, transparent:true, opacity:0 }));
        beam.rotation.z = Math.PI/2; scene.add(beam);
        cam.target.set(0,1.95,2.5); cam.rad = 11; cam.pol = 1.25; Object.assign(DEF, cam);
    }

    // ---------------- G12: FIELD RESEARCHER (realistic research field) ----------------
    function buildField() {
        // gradient sky
        const skyC=document.createElement('canvas'); skyC.width=16; skyC.height=256; const sc=skyC.getContext('2d');
        const grd=sc.createLinearGradient(0,0,0,256); grd.addColorStop(0,'#3b7fd4'); grd.addColorStop(0.55,'#8cbbe8'); grd.addColorStop(1,'#dcecf6');
        sc.fillStyle=grd; sc.fillRect(0,0,16,256); scene.background=new THREE.CanvasTexture(skyC);
        scene.fog=new THREE.Fog(0xc6def0, 44, 96);
        scene.add(new THREE.HemisphereLight(0xbfdcf6, 0x4a6b35, 0.95));
        const sun=new THREE.DirectionalLight(0xfff2d0, 0.9); sun.position.set(-14,20,10); scene.add(sun);

        const ground=new THREE.Mesh(new THREE.PlaneGeometry(220,220), M(0x5d8c40,{roughness:1})); ground.rotation.x=-Math.PI/2; ground.receiveShadow=true; scene.add(ground);
        // rolling hills
        for(let i=0;i<14;i++){ const h=new THREE.Mesh(new THREE.SphereGeometry(5+Math.random()*6,16,12), M(i%2?0x4f7d36:0x3f6a30,{roughness:1})); h.position.set((Math.random()-0.5)*130,-3,(Math.random()-0.5)*130-22); h.scale.y=0.32; scene.add(h); }
        // distant mountains
        for(let i=0;i<8;i++){ const a=Math.PI+(i/8)*Math.PI; const m=new THREE.Mesh(new THREE.ConeGeometry(8+Math.random()*6,13+Math.random()*8,6), M(0x6e89a3,{roughness:1})); m.position.set(Math.cos(a)*64,3,Math.sin(a)*64-22); scene.add(m); }
        // trees (varied)
        function tree(x,z,sc){ const t=new THREE.Group();
            const tr=new THREE.Mesh(new THREE.CylinderGeometry(0.2*sc,0.3*sc,1.4*sc,8), M(0x6b4423,{roughness:1})); tr.position.y=0.7*sc; t.add(tr);
            const c1=new THREE.Mesh(new THREE.SphereGeometry(1.1*sc,10,8), M(0x2f7d3f,{roughness:1})); c1.position.y=2*sc; t.add(c1);
            const c2=new THREE.Mesh(new THREE.SphereGeometry(0.8*sc,10,8), M(0x37913f,{roughness:1})); c2.position.set(0.5*sc,1.7*sc,0.3*sc); t.add(c2);
            t.position.set(x,0,z); scene.add(t); addShadow(x,z,1.1*sc); }
        for(let i=0;i<22;i++){ const a=Math.random()*Math.PI*2,r=12+Math.random()*32; tree(Math.cos(a)*r,Math.sin(a)*r,0.7+Math.random()*0.8); }
        // bushes, flowers, rocks, grass
        for(let i=0;i<18;i++){ const b=new THREE.Mesh(new THREE.SphereGeometry(0.5+Math.random()*0.5,8,6), M(0x3f8a44,{roughness:1})); b.scale.y=0.7; const a=Math.random()*Math.PI*2,r=6+Math.random()*28; b.position.set(Math.cos(a)*r,0.3,Math.sin(a)*r); scene.add(b); }
        for(let i=0;i<30;i++){ const col=[0xff6b9d,0xfde047,0xffffff,0xa78bfa][i%4]; const fl=new THREE.Mesh(new THREE.SphereGeometry(0.12,6,5), M(col)); const a=Math.random()*Math.PI*2,r=4+Math.random()*26; fl.position.set(Math.cos(a)*r,0.22,Math.sin(a)*r); scene.add(fl); }
        for(let i=0;i<12;i++){ const rk=new THREE.Mesh(new THREE.DodecahedronGeometry(0.4+Math.random()*0.9), M(0x8a8a90,{roughness:1})); const a=Math.random()*Math.PI*2,r=6+Math.random()*24; rk.position.set(Math.cos(a)*r,0.2,Math.sin(a)*r); rk.rotation.set(Math.random(),Math.random(),Math.random()); scene.add(rk); }
        // pond
        const pond=new THREE.Mesh(new THREE.CircleGeometry(4,32), new THREE.MeshStandardMaterial({color:0x3b82c4,transparent:true,opacity:0.82,roughness:0.2,metalness:0.3})); pond.rotation.x=-Math.PI/2; pond.position.set(-13,0.04,-9); scene.add(pond);
        // research camp: tent, table, crates, flag
        const tent=new THREE.Mesh(new THREE.ConeGeometry(2,2.3,4), M(0xd98c4a,{roughness:0.9})); tent.position.set(12,1.15,6); tent.rotation.y=Math.PI/4; scene.add(tent); addShadow(12,6,1.4);
        const tbl=new THREE.Mesh(new THREE.BoxGeometry(1.8,0.15,1), M(0x8a6a4a)); tbl.position.set(9,0.9,7.5); scene.add(tbl);
        [-0.8,0.8].forEach(lx=>[-0.4,0.4].forEach(lz=>{ const l=new THREE.Mesh(new THREE.BoxGeometry(0.1,0.9,0.1),M(0x6b4423)); l.position.set(9+lx,0.45,7.5+lz); scene.add(l); }));
        for(let i=0;i<3;i++){ const cr=new THREE.Mesh(new THREE.BoxGeometry(0.7,0.7,0.7), M(0xb08850,{roughness:0.9})); cr.position.set(10+i*0.85,0.35,9); scene.add(cr); }
        const pole=new THREE.Mesh(new THREE.CylinderGeometry(0.05,0.05,3,8),M(0xcccccc,{metalness:0.5})); pole.position.set(14,1.5,4); scene.add(pole);
        const flag=new THREE.Mesh(new THREE.PlaneGeometry(1.2,0.8), M(0x2848a8,{side:THREE.DoubleSide})); flag.position.set(14.6,2.5,4); scene.add(flag);
        // crisis site (geological): scorched cracked ground + steam
        const crisis=new THREE.Mesh(new THREE.CircleGeometry(5,24), M(0x4a3528,{roughness:1})); crisis.rotation.x=-Math.PI/2; crisis.position.set(15,0.03,-15); scene.add(crisis);
        for(let i=0;i<6;i++){ const a=Math.random()*Math.PI*2; const ck=new THREE.Mesh(new THREE.BoxGeometry(0.16,0.02,1.6+Math.random()*2), M(0x190d07)); ck.position.set(15+Math.cos(a)*1.4,0.05,-15+Math.sin(a)*1.4); ck.rotation.y=a; scene.add(ck); }
        for(let i=0;i<4;i++){ const sm=new THREE.Mesh(new THREE.SphereGeometry(0.8+Math.random()*0.6,8,8), new THREE.MeshBasicMaterial({color:0x9a8a7a,transparent:true,opacity:0.22})); sm.position.set(15+(Math.random()-0.5)*2,1.4+i*0.85,-15+(Math.random()-0.5)*2); scene.add(sm); }
        // clouds
        for(let i=0;i<8;i++){ const cl=new THREE.Group(); for(let j=0;j<3;j++){ const pp=new THREE.Mesh(new THREE.SphereGeometry(1.5+Math.random(),8,6), new THREE.MeshBasicMaterial({color:0xffffff,transparent:true,opacity:0.85})); pp.position.set(j*1.6-1.6,0,Math.random()); pp.scale.y=0.6; cl.add(pp); } cl.position.set((Math.random()-0.5)*90,17+Math.random()*7,(Math.random()-0.5)*90-22); scene.add(cl); }

        player = buildCharacter({ skin:'#f0c08a', hair:'#2a1a10', shirt:'#b89a5a', pants:'#566b39', accent:'#7a5a2a', acc:'hat' });
        player.position.set(0,0,9); scene.add(player); registerActor(player, 0);
        // field guide NPC near the camp
        const guide = buildCharacter({ skin:'#caa06f', hair:'#3a2a1a', shirt:'#3f7d4f', pants:'#4a5a35', accent:'#7a5a2a', acc:'hat', mood:'happy' });
        guide.position.set(9.5,0,5.5); guide.rotation.y=-Math.PI*0.6; scene.add(guide); registerActor(guide, 0);

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
        cam.target.set(0,1.85,0); cam.rad = 22; cam.pol = 1.1; Object.assign(DEF, cam);
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
        youHP:100, rivalHP:100, collected:0, total:8, curSample:null,
        combo:0, maxCombo:0, hints:3, lives:3, recentAcc:[], curQ:null };
    let timerId=null;
    const el=id=>document.getElementById(id);

    function startGame() {
        if (!questionsReady) { el('srHint').textContent='Loading questions…'; return; }
        Object.assign(G,{ running:true, quizOpen:false, score:0, correct:0, wrong:0, timeLeft:300, responseTimes:[],
            youHP:100, rivalHP:100, collected:0, total: isG11?5:samples.length, curSample:null,
            combo:0, maxCombo:0, hints:3, lives:3, recentAcc:[], curQ:null });
        qPtr=0;
        el('srStart').hidden=true; el('srSummary').hidden=true;
        el('srLives').style.display = isG11 ? 'none' : '';
        el('srHintBtn').disabled=false; el('srHints').textContent=G.hints; el('srComboPill').style.display='none';
        if (isG11) {
            el('srHpWrap').classList.add('show');
            el('srProgLabel').textContent='Battle';
            el('srHint').textContent='Formula Clash! Answer correctly and quickly to strike your rival.';
            updateHUD(); askBattleQuestion();
        } else {
            samples.forEach(s=>{ s.userData.collected=false; s.visible=true; });
            el('srProgLabel').textContent='Samples Collected';
            el('srHint').textContent='Click a glowing SAMPLE to research and collect it.';
            updateHUD();
        }
        clearInterval(timerId);
        timerId=setInterval(()=>{ if(!G.running) return; G.timeLeft--; updateHUD(); if(G.timeLeft<=0) endGame(); },1000);
    }

    function updateHUD() {
        el('srScore').textContent=G.score;
        const m=Math.floor(G.timeLeft/60), s=G.timeLeft%60; el('srTime').textContent=(m<10?'0':'')+m+':'+(s<10?'0':'')+s;
        el('srTotalPts').textContent=CFG.myBest+G.score; el('srBadges').textContent=Math.min(12,Math.floor((CFG.myBest+G.score)/100));
        const cp=el('srComboPill'); if(G.combo>=2){ cp.style.display=''; el('srCombo').textContent=G.combo; } else cp.style.display='none';
        el('srHints').textContent=G.hints;
        if(!isG11) el('srLives').textContent = G.lives>0 ? '\u2764\ufe0f'.repeat(G.lives) : '\ud83d\udc94';
        if (isG11){ el('srHpYou').style.width=Math.max(0,G.youHP)+'%'; el('srHpRival').style.width=Math.max(0,G.rivalHP)+'%'; el('srBar').style.width=(100-Math.max(0,G.rivalHP))+'%'; }
        else { el('srBar').style.width=Math.round(G.collected/G.total*100)+'%'; }
    }

    function renderQuiz(q, onAnswer) {
        G.qStart=performance.now(); q._answered=false; q._hintUsed=false; G.curQ=q;
        el('srHintBtn').disabled = (G.hints<=0);
        el('srDiff').textContent = (q._diff||q.difficulty||'easy').toUpperCase();
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
            if (ok){ G.correct++; G.combo++; G.maxCombo=Math.max(G.maxCombo,G.combo); G.recentAcc.push(1);
                     G.score += Math.round(80*comboMult());
                     playAttack(player); flashBeam(player,rival,0x6ee7ff);
                     setTimeout(()=>{ playHurt(rival); hitSpark(rival.position.x,1.8,rival.position.z,0x6ee7ff); },180);
                     G.rivalHP-=20; }
            else  { G.wrong++; G.combo=0; G.recentAcc.push(0);
                     playAttack(rival); flashBeam(rival,player,0xff6b6b);
                     setTimeout(()=>{ playHurt(player); hitSpark(player.position.x,1.8,player.position.z,0xff6b6b); },180);
                     G.youHP-=20; }
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
        beam.position.set((a.x+b.x)/2, 1.8, (a.z+b.z)/2);
        beam.scale.set(1, Math.max(0.2, Math.abs(b.x-a.x)), 1);
        setTimeout(()=>{ if(beam) beam.material.opacity=0; }, 240);
    }

    // ---- G12 collect flow ----
    function openQuiz(sample) {
        G.quizOpen=true; G.curSample=sample;
        cam.target.copy(sample.position); cam.target.y=1.4; applyCamera();
        el('srQuizTitle').textContent='Field Sample';
        const q=nextQ();
        renderQuiz(q, (ok)=>{
            if (ok){ G.correct++; G.combo++; G.maxCombo=Math.max(G.maxCombo,G.combo); G.recentAcc.push(1); G.score += Math.round(70*comboMult()); }
            else { G.wrong++; G.combo=0; G.recentAcc.push(0); G.lives--; }
            updateHUD();
            setTimeout(()=>{
                el('srQuiz').classList.remove('show'); G.quizOpen=false;
                if (ok){ sample.userData.collected=true; sample.visible=false; G.collected++; el('srHint').textContent='Sample collected! ('+G.collected+'/'+G.total+')'; }
                else { el('srHint').textContent='Wrong! You lost a life. Pick another sample.'; }
                cam.target.set(0,1.85,0); applyCamera(); updateHUD();
                if (G.lives<=0){ endGame(); return; }
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
        else { won=G.collected>=G.total; goalVal=G.collected+'/'+G.total; goalK='Samples'; el('srSumTitle').textContent = won?'🏆 Crisis Solved!':(G.lives<=0?'💔 Out of Lives!':'⏱ Time\'s Up!'); }
        el('srSumScore').textContent=G.score; el('srSumAcc').textContent=acc+'%';
        el('srSumGoal').textContent=goalVal; el('srSumGoalK').textContent=goalK;
        el('srSumCorrect').textContent=G.correct; el('srSumWrong').textContent=G.wrong;
        el('srSummary').hidden=false; el('srSaveMsg').textContent='Saving to leaderboard…';
        fetch(CFG.urlScore,{ method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CFG.csrf,'Accept':'application/json'},
            body: JSON.stringify({ score:G.score, accuracy:acc, correct:G.correct, incorrect:G.wrong, max_combo:G.maxCombo, avg_response_ms:avg }) })
          .then(r=>r.json()).then(d=>{ el('srSaveMsg').textContent=d.ok?'✓ Saved! Personal best: '+(d.personalBest ?? G.score):'Saved locally.'; })
          .catch(()=>{ el('srSaveMsg').textContent='Could not save (check connection).'; });
    }

    el('srStartBtn').onclick=startGame; el('srReplayBtn').onclick=startGame;
    el('srHintBtn').onclick = ()=>{
        const q=G.curQ; if(!q || G.hints<=0 || q._hintUsed || q._answered) return;
        q._hintUsed=true; G.hints--; let dimmed=0;
        q._btns.forEach((b,i)=>{ if(i!==q.correct_index && dimmed<2 && !b.disabled){ b.classList.add('dim'); b.disabled=true; dimmed++; } });
        updateHUD();
    };

    // start overlay description
    el('srStartDesc').innerHTML = isG11
        ? '<b>Grade 11 — Formula Clash ⚔️</b><br>Face the rival scientist! Answer Physics and Chemistry questions correctly and quickly to strike. Drain the rival\'s HP to win!'
        : '<b>Grade 12 — Field Researcher 🔬</b><br>Explore the field and click the glowing SAMPLES. Answer Earth Science / Biology questions correctly to collect each sample and solve the crisis!';

    function animate() {
        requestAnimationFrame(animate);
        const dt=Math.min(clock.getDelta(),0.05), t=clock.elapsedTime;
        // floating/spinning samples
        samples.forEach(s=>{ if(s.visible){ s.userData.core.rotation.y+=dt*1.3; s.userData.core.position.y=1+Math.sin(t*2+s.position.x)*0.15; } });
        // characters: idle breathing/sway + attack lunge + hurt knockback
        actors.forEach(ch=>{
            const u=ch.userData; if(u.baseX===undefined) return;
            const breathe = Math.sin(t*1.8 + u.phase)*0.02;
            const sway = Math.sin(t*1.4 + u.phase)*0.09;
            if (u.head) u.head.rotation.x = Math.sin(t*0.9 + u.phase)*0.04;
            if (u.armL) u.armL.rotation.x = sway;
            if (u.armR) u.armR.rotation.x = -sway;
            let lunge=0, hop=0, knock=0, flinch=0;
            if (u.atk){ u.atk.t+=dt; const p=Math.min(u.atk.t/0.45,1), k=Math.sin(p*Math.PI);
                lunge=u.lungeX*k*0.95; hop=k*0.07;
                if (u.armR) u.armR.rotation.x=-k*1.7;
                if (u.armL) u.armL.rotation.x=-k*0.5;
                if (p>=1) u.atk=null;
            }
            if (u.hurt){ u.hurt.t+=dt; const p=Math.min(u.hurt.t/0.4,1), k=Math.sin(p*Math.PI);
                knock=-u.lungeX*k*0.55; flinch=-k*0.08;
                if (p>=1) u.hurt=null;
            }
            ch.position.x = u.baseX + lunge + knock;
            ch.position.y = u.baseY + breathe + hop + flinch;
        });
        // hit sparks (rings expand+fade, particles fly+fall)
        for(let i=sparks.length-1;i>=0;i--){ const s=sparks[i]; s.t+=dt; const p=s.t/s.dur;
            if(p>=1){ scene.remove(s.m); sparks.splice(i,1); continue; }
            if(s.ring){ const sc=1+p*3.2; s.m.scale.set(sc,sc,sc); s.m.material.opacity=0.9*(1-p); }
            else { s.m.position.x+=s.vx*dt; s.m.position.z+=s.vz*dt; s.vy-=9*dt; s.m.position.y+=s.vy*dt; s.m.material.opacity=1-p; }
        }
        // clouds drift slowly across the sky
        for(const c of clouds){ c.position.x += c.userData.sp*dt; if(c.position.x>95) c.position.x=-95; }
        // butterflies flutter in little loops near the ground
        for(const bf of butterflies){
            bf.userData.a += bf.userData.sp*dt; const a=bf.userData.a, r=bf.userData.r;
            bf.position.set(bf.userData.cx+Math.cos(a)*r, bf.userData.y+Math.sin(a*2+bf.userData.ph)*0.3, bf.userData.cz+Math.sin(a)*r);
            bf.rotation.y=-a;
            const fl=Math.sin(t*16+bf.userData.ph)*0.9;
            if(bf.userData.wL) bf.userData.wL.rotation.y=fl; if(bf.userData.wR) bf.userData.wR.rotation.y=-fl;
        }
        // gentle wind sway on the trees
        for(const tr of swayTrees){ tr.rotation.z = Math.sin(t*0.8 + (tr.userData.ph||0))*0.02; }
        // birds circling the sky, wings flapping
        for(const b of birds){
            b.userData.a += b.userData.sp*dt;
            const a=b.userData.a, r=b.userData.r;
            b.position.set(Math.cos(a)*r, b.userData.y + Math.sin(t*0.8+b.userData.ph)*0.7, Math.sin(a)*r - 2);
            b.rotation.y = -a;
            const flap=Math.sin(t*11 + b.userData.ph)*0.7;
            if(b.userData.wL) b.userData.wL.rotation.x=flap;
            if(b.userData.wR) b.userData.wR.rotation.x=-flap;
        }
        renderer.render(scene, camera);
    }

    function boot(){ if(typeof THREE==='undefined'){ setTimeout(boot,120); return; } try{ initScene(); el('srHint').textContent = isG11?'Formula Clash — ready to begin?':'Field Researcher — ready to begin?'; }catch(e){ console.error('Strata Rush',e); } }
    if (document.readyState==='loading') document.addEventListener('DOMContentLoaded',boot); else boot();
})();
</script>
@endsection