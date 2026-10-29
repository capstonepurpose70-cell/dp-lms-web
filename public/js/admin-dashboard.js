/*
 * Admin dashboard — activity chart, counters and the live refresh.
 *
 * Reads from #dbRoot:
 *   data-charts  JSON { weekly, monthly, yearly }, each { labels, titles, current, prev, log, reg, apr,
 *                before: { log, reg, apr } } — "before" is the previous period at the same position
 *   data-live    URL of GET /admin/dashboard/live
 *
 * The admin layout swaps pages without a reload and runs this file again on every visit, so all state
 * lives inside this function and every timer / observer stops once #dbRoot is gone.
 */
(function () {
    'use strict';

    const root = document.getElementById('dbRoot');
    if (!root) return;

    const POLL_MS = 20000;
    const RANGE = {
        weekly:  { now: 'This week',  then: 'Last week',  unit: 'day' },
        monthly: { now: 'This month', then: 'Last month', unit: 'day' },
        yearly:  { now: 'This year',  then: 'Last year',  unit: 'month' },
    };
    const METRIC = { log: 'Sign-ins', reg: 'Registrations', apr: 'Approvals' };
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const $ = (id) => document.getElementById(id);
    const gone = () => !document.body.contains(root);
    const fmt = (n) => Number(n || 0).toLocaleString('en-US');
    const num = (text) => Number(String(text).replace(/[^0-9-]/g, '')) || 0;
    const sum = (arr) => (arr || []).reduce((a, b) => a + (Number(b) || 0), 0);
    const easeOut = (t) => 1 - Math.pow(1 - t, 3);

    let data = parseJson(root.dataset.charts) || {};
    let period = 'weekly';
    let metric = 'log';

    function parseJson(text) {
        try { return JSON.parse(text); } catch (e) { return null; }
    }

    /* ─────────────────────────── Numbers ─────────────────────────── */

    const running = new WeakMap();

    function countTo(el, to) {
        if (!el) return;
        to = Number(to) || 0;
        cancelAnimationFrame(running.get(el));
        const from = num(el.textContent);
        if (from === to) { el.textContent = fmt(to); return; }
        if (reduceMotion) { el.textContent = fmt(to); return; }
        const start = performance.now(), dur = 700;
        const step = (now) => {
            const k = Math.min(1, (now - start) / dur);
            el.textContent = fmt(Math.round(from + (to - from) * easeOut(k)));
            if (k < 1 && !gone()) running.set(el, requestAnimationFrame(step));
        };
        running.set(el, requestAnimationFrame(step));
    }

    // Change vs the previous period as words ("+18%"), never colour alone
    function change(now, before) {
        if (!now && !before) return { tone: 'flat', text: 'No change' };
        if (!before) return { tone: 'up', text: 'New' };
        const pct = Math.round(((now - before) / before) * 100);
        return { tone: pct > 0 ? 'up' : (pct < 0 ? 'down' : 'flat'), text: (pct > 0 ? '+' : '') + pct + '%' };
    }

    function setDelta(el, now, before, suffix) {
        if (!el) return;
        const c = change(now, before);
        el.dataset.tone = c.tone;
        el.querySelector('span').textContent = c.text + (suffix ? ' ' + suffix : '');
    }

    /* ─────────────────────────── Theme ──────────────────────────── */

    function theme() {
        const dark = document.documentElement.getAttribute('data-theme') === 'dark';
        return {
            accent: dark ? '#3B82F6' : '#2563EB',            // today, or the bar under the pointer
            bar:    dark ? '#2B4A80' : '#BFD3FB',            // every other bar
            track:  dark ? 'rgba(148,163,184,.07)' : '#F1F4F9',
            stripe: dark ? 'rgba(148,163,184,.16)' : '#DCE3EE',
            marker: dark ? '#E2E8F0' : '#0F172A',            // previous period
            grid:   dark ? 'rgba(148,163,184,.10)' : '#E8EDF4',
            tick:   getComputedStyle(root).getPropertyValue('--slate-500').trim() || '#64748B',
        };
    }

    // Slots that have not happened yet get a diagonal hatch instead of an empty track
    function hatch(t) {
        const c = document.createElement('canvas');
        c.width = c.height = 8;
        const x = c.getContext('2d');
        x.fillStyle = t.track;
        x.fillRect(0, 0, 8, 8);
        x.strokeStyle = t.stripe;
        x.lineWidth = 1.5;
        x.beginPath();
        x.moveTo(-2, 10); x.lineTo(10, -2);
        x.moveTo(-2, 2);  x.lineTo(2, -2);
        x.moveTo(6, 10);  x.lineTo(10, 6);
        x.stroke();
        return x.createPattern(c, 'repeat');
    }

    /* ─────────────────────────── Chart ──────────────────────────── */

    let chart = null;
    let hover = -1;

    function view() {
        const d = data[period] || { labels: [], titles: [], current: -1, before: {} };
        const now = d[metric] || [];
        const before = (d.before && d.before[metric]) || [];
        const scale = niceScale(now.concat(before));
        return { d, now, before, max: scale.max, step: scale.step };
    }

    // Top of the y axis and its tick step: 5 → 1, 10 → 2, 20 → 5, 50 → 10, 100 → 20 ...
    function niceScale(values) {
        const m = Math.max(5, ...values.map(Number));
        const p = Math.pow(10, Math.floor(Math.log10(m)));
        const n = m / p;
        const max = (n <= 1 ? 1 : n <= 2 ? 2 : n <= 5 ? 5 : 10) * p;
        return { max, step: max / (String(max)[0] === '2' ? 4 : 5) };
    }

    function fills(t, d) {
        const pattern = hatch(t);
        const bars = [], tracks = [];
        const lit = hover >= 0 ? hover : d.current;
        d.labels.forEach((_, i) => {
            bars.push(i === lit ? t.accent : t.bar);
            tracks.push(d.current >= 0 && i > d.current ? pattern : t.track);
        });
        return { bars, tracks };
    }

    // Previous period: a short dash across each slot, drawn after the bars
    const lastPeriodMarks = {
        id: 'dbLastPeriod',
        afterDatasetsDraw(c) {
            const bars = c.getDatasetMeta(0).data;
            const before = c.data.datasets[0].before || [];
            const y = c.scales.y;
            const ctx = c.ctx;
            ctx.save();
            ctx.strokeStyle = theme().marker;
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            bars.forEach((bar, i) => {
                if (!before[i]) return;
                const half = Math.max(3, bar.width / 2 - 1);
                const py = Math.round(y.getPixelForValue(before[i])) + 0.5;
                ctx.beginPath();
                ctx.moveTo(bar.x - half, py);
                ctx.lineTo(bar.x + half, py);
                ctx.stroke();
            });
            ctx.restore();
        },
    };

    function tooltip(context) {
        const box = $('dbTip');
        if (!box) return;
        const tip = context.tooltip;
        if (tip.opacity === 0 || !tip.dataPoints || !tip.dataPoints.length) { box.dataset.open = 'false'; return; }
        const i = tip.dataPoints[0].dataIndex;
        const { d, now, before } = view();
        const r = RANGE[period];
        const t = theme();
        const future = d.current >= 0 && i > d.current;
        const c = change(now[i] || 0, before[i] || 0);
        box.innerHTML =
            `<div class="db-tip-title">${(d.titles && d.titles[i]) || d.labels[i]}</div>`
            + (future
                ? `<div class="db-tip-row"><span>Not here yet</span></div>`
                : `<div class="db-tip-row"><i style="background:${t.accent}"></i><span>${r.now}</span><b>${fmt(now[i])}</b></div>`)
            + `<div class="db-tip-row"><i class="dash" style="background:${t.marker}"></i><span>${r.then}</span><b>${fmt(before[i])}</b></div>`
            + (future ? '' : `<div class="db-tip-chip" data-tone="${c.tone}">${c.text} vs ${r.then.toLowerCase()}</div>`);
        const area = context.chart.chartArea;
        const bar = context.chart.getDatasetMeta(0).data[i];
        const x = Math.min(Math.max(bar ? bar.x : tip.caretX, 84), context.chart.width - 84);
        box.style.transform = `translate(${Math.round(x)}px, ${Math.round(area.top)}px) translate(-50%, calc(-100% - 6px))`;
        box.dataset.open = 'true';
    }

    function setHover(i) {
        if (!chart || hover === i) return;
        hover = i;
        chart.data.datasets[0].backgroundColor = fills(theme(), view().d).bars;
        chart.update('none');
    }

    function build() {
        const canvas = $('dbChart');
        if (!canvas) return;
        if (typeof Chart === 'undefined') {
            canvas.parentNode.innerHTML = '<p class="db-chart-note">The chart could not load. Check the internet connection, then reload the page. The numbers above are still live.</p>';
            return;
        }
        const t = theme();
        const { d, now, before, max, step } = view();
        const f = fills(t, d);
        // Hover colour is ours (setHover); Chart.js would otherwise darken the bar and tint the track
        const sameColour = (ctx) => {
            const bg = ctx.dataset.backgroundColor;
            return Array.isArray(bg) ? bg[ctx.dataIndex] : bg;
        };
        const bar = { borderRadius: 6, borderSkipped: false, grouped: false, categoryPercentage: .78, barPercentage: .82, maxBarThickness: 36, hoverBackgroundColor: sameColour };
        Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;

        chart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: d.labels,
                datasets: [
                    Object.assign({ label: 'now', data: now, before, order: 1, backgroundColor: f.bars }, bar),
                    Object.assign({ label: 'track', data: d.labels.map(() => max), order: 2, backgroundColor: f.tracks, animation: false }, bar),
                ],
            },
            plugins: [lastPeriodMarks],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                animation: reduceMotion ? false : {
                    duration: 650,
                    easing: 'easeOutQuart',
                    delay: (ctx) => (ctx.type === 'data' && ctx.mode === 'default' ? ctx.dataIndex * Math.min(40, 280 / ctx.chart.data.labels.length) : 0),
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false, external: tooltip, filter: (item) => item.datasetIndex === 0 },
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false },
                         ticks: { color: t.tick, maxRotation: 0, autoSkipPadding: 12, padding: 6, font: { size: 11.5, weight: '600' } } },
                    y: { min: 0, max, grid: { color: t.grid, drawTicks: false }, border: { display: false },
                         ticks: { color: t.tick, stepSize: step, padding: 10, font: { size: 11 } } },
                },
                onHover: (e, active) => setHover(active.length ? active[0].index : -1),
            },
        });

        canvas.addEventListener('mouseleave', () => setHover(-1));

        const watch = new MutationObserver(() => {
            if (gone()) { watch.disconnect(); return; }
            refreshChart('theme');
        });
        watch.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
    }

    // 'grow': the range or metric changed, bars grow again · 'theme': recolour only · 'data': live numbers
    function refreshChart(mode) {
        if (!chart) return;
        const t = theme();
        hover = -1;
        const { d, now, before, max, step } = view();
        const f = fills(t, d);
        const [bars, tracks] = chart.data.datasets;
        chart.data.labels = d.labels;
        bars.data = now;
        bars.before = before;
        bars.backgroundColor = f.bars;
        tracks.data = d.labels.map(() => max);
        tracks.backgroundColor = f.tracks;
        const s = chart.options.scales;
        s.y.max = max;
        s.y.ticks.stepSize = step;
        s.y.grid.color = t.grid;
        s.x.ticks.color = s.y.ticks.color = t.tick;
        if (mode === 'theme') { chart.update('none'); return; }
        if (mode === 'grow') chart.reset();
        chart.update();
    }

    /* ─────────────────────── Text around the chart ─────────────── */

    function renderText() {
        const { d, now, before } = view();
        const r = RANGE[period];
        const word = METRIC[metric].toLowerCase();
        countTo($('dbTotal'), sum(now));
        $('dbTotalWord').textContent = word + ' ' + r.now.toLowerCase();
        setDelta($('dbDelta'), sum(now), sum(before), 'vs ' + r.then.toLowerCase());
        $('dbNowLabel').textContent = r.now;
        $('dbThenLabel').textContent = r.then;
        $('dbThenValue').textContent = fmt(sum(before));
        $('dbChart').setAttribute('aria-label', `${METRIC[metric]} per ${r.unit}, ${r.now.toLowerCase()} compared with ${r.then.toLowerCase()}`);

        const table = $('dbTable');
        table.innerHTML = `<caption>${METRIC[metric]} per ${r.unit}</caption>`
            + `<thead><tr><th scope="col">${r.unit === 'day' ? 'Day' : 'Month'}</th><th scope="col">${r.now}</th><th scope="col">${r.then}</th></tr></thead><tbody>`
            + d.labels.map((l, i) => `<tr><th scope="row">${(d.titles && d.titles[i]) || l}</th><td>${now[i] || 0}</td><td>${before[i] || 0}</td></tr>`).join('')
            + '</tbody>';

        // Hero card: sign-ins today, this week, this month
        const w = data.weekly, m = data.monthly;
        if (w) {
            const today = w.current >= 0 ? (w.log[w.current] || 0) : 0;
            const yesterday = w.current > 0 ? (w.log[w.current - 1] || 0) : ((w.before && w.before.log[6]) || 0);
            countTo($('dbSignToday'), today);
            setDelta($('dbSignDelta'), today, yesterday, 'vs yesterday');
            countTo($('dbSignWeek'), sum(w.log));
        }
        if (m) countTo($('dbSignMonth'), sum(m.log));
    }

    function bindTabs(id, pick) {
        const group = $(id);
        if (!group) return;
        group.addEventListener('click', (e) => {
            const b = e.target.closest('button[data-value]');
            if (!b || b.getAttribute('aria-pressed') === 'true') return;
            group.querySelectorAll('button').forEach((x) => x.setAttribute('aria-pressed', String(x === b)));
            pick(b.dataset.value);
            renderText();
            refreshChart('grow');
        });
    }

    /* ───────────────────────── Live refresh ────────────────────── */

    const live = $('dbLive');
    let timer = null, busy = false;

    function setText(el, text) { if (el && el.textContent !== text) el.textContent = text; }

    function swapList(id, html) {
        const el = $(id);
        if (!el || typeof html !== 'string' || el.innerHTML.trim() === html.trim()) return;
        // Never pull a button out from under the admin's pointer or keyboard focus
        if (el.contains(document.activeElement) || el.matches(':hover')) return;
        el.innerHTML = html;
        el.classList.remove('db-flash');
        void el.offsetWidth;
        el.classList.add('db-flash');
    }

    function apply(json) {
        const c = json.counts || {};
        root.querySelectorAll('[data-count]').forEach((el) => {
            if (c[el.dataset.count] !== undefined) countTo(el, c[el.dataset.count]);
        });
        root.querySelectorAll('[data-diff]').forEach((el) => {
            const [a, b] = el.dataset.diff.split('-');
            if (c[a] !== undefined && c[b] !== undefined) countTo(el, Math.max(0, c[a] - c[b]));
        });
        root.querySelectorAll('[data-warn]').forEach((el) => {
            el.classList.toggle('warn', Number(c[el.dataset.warn] || 0) > 0);
        });
        root.querySelectorAll('[data-att]').forEach((a) => {
            if (a.dataset.one === undefined) return;
            const n = Number(c[a.dataset.att] || 0);
            a.classList.toggle('warn', n > 0);
            a.classList.toggle('ok', n === 0);
            setText(a.querySelector('[data-att-text]'), n === 0 ? a.dataset.none : (n === 1 ? a.dataset.one : a.dataset.many));
        });
        if (json.charts) {
            data = json.charts;
            renderText();
            refreshChart('data');
        }
        swapList('dbPending', json.pendingHtml);
        swapList('dbActivity', json.activityHtml);
        setText($('dbUpdated'), 'Updated ' + new Date(json.updatedAt || Date.now()).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }));
    }

    function refresh() {
        if (gone()) { clearInterval(timer); return; }
        if (document.hidden || busy) return;
        busy = true;
        live.classList.add('updating');
        fetch(root.dataset.live, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then((r) => {
                // Session ended in another tab: the endpoint redirects to the login page
                if (r.status === 401 || r.status === 419 || (r.redirected && r.url.indexOf('/admin/dashboard') === -1)) {
                    window.location.href = '/admin/login';
                    throw new Error('signed out');
                }
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then((json) => {
                if (gone()) return;
                apply(json);
                live.classList.remove('paused');
                setText($('dbLiveLabel'), 'Live');
            })
            .catch(() => {
                live.classList.add('paused');
                setText($('dbLiveLabel'), 'Reconnecting');
            })
            .finally(() => {
                busy = false;
                live.classList.remove('updating');
            });
    }

    function onVisible() {
        if (gone()) { document.removeEventListener('visibilitychange', onVisible); return; }
        if (!document.hidden) refresh();
    }

    /* ─────────────────────────── Start ─────────────────────────── */

    root.querySelectorAll('.db-num').forEach((el) => {
        const to = num(el.textContent);
        el.textContent = '0';
        countTo(el, to);
    });
    bindTabs('dbMetric', (v) => { metric = v; });
    bindTabs('dbRange', (v) => { period = v; });
    build();
    renderText();
    timer = setInterval(refresh, POLL_MS);
    document.addEventListener('visibilitychange', onVisible);
})();
