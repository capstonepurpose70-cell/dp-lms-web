/*
 * Admin dashboard — charts, counters and the live refresh.
 *
 * Reads everything from #dbRoot:
 *   data-charts  JSON { weekly, monthly, yearly } — each { labels, titles, prev, log, reg, apr }
 *   data-live    URL of GET /admin/dashboard/live
 *
 * The admin layout swaps pages without a reload and runs this file again on every visit, so all
 * state lives inside this function and every timer / observer stops once #dbRoot is gone.
 */
(function () {
    'use strict';

    const root = document.getElementById('dbRoot');
    if (!root) return;

    const POLL_MS = 20000;
    const REVEAL_MS = 900;
    const RANGE = {
        weekly:  { word: 'this week',  prev: 'last week' },
        monthly: { word: 'this month', prev: 'last month' },
        yearly:  { word: 'this year',  prev: 'last year' },
    };
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const $ = (id) => document.getElementById(id);
    const gone = () => !document.body.contains(root);
    const fmt = (n) => Number(n || 0).toLocaleString('en-US');
    const sum = (arr) => (arr || []).reduce((a, b) => a + (Number(b) || 0), 0);
    const ease = (t) => 1 - Math.pow(1 - t, 3);

    let data = parse(root.dataset.charts) || {};
    let period = 'weekly';

    function parse(text) {
        try { return JSON.parse(text); } catch (e) { return null; }
    }

    /* ─────────────────────────── Theme ─────────────────────────── */

    // Validated palette (colour-blind separation + contrast): light #2563EB / #0D9488, dark #3B82F6 / #0D9488
    function theme() {
        const dark = document.documentElement.getAttribute('data-theme') === 'dark';
        const css = getComputedStyle(root);
        const v = (name, fallback) => css.getPropertyValue(name).trim() || fallback;
        return {
            dark,
            blue: dark ? '#3B82F6' : '#2563EB',
            teal: '#0D9488',
            grid: dark ? 'rgba(255,255,255,.06)' : '#E9EEF5',
            tick: v('--slate-500', '#64748B'),
            surface: v('--surface-card', '#FFFFFF'),
        };
    }

    function rgba(hex, a) {
        const n = parseInt(hex.slice(1), 16);
        return `rgba(${(n >> 16) & 255}, ${(n >> 8) & 255}, ${n & 255}, ${a})`;
    }

    /* ───────────────────────── Numbers ─────────────────────────── */

    // Counts from the number on screen to the new one; tabular figures keep the width steady
    function countTo(el, to) {
        if (!el) return;
        const from = Number(String(el.textContent).replace(/[^\d-]/g, '')) || 0;
        to = Number(to) || 0;
        if (from === to) return;
        if (reduceMotion) { el.textContent = fmt(to); return; }
        const start = performance.now(), dur = 800;
        const step = (now) => {
            const t = Math.min(1, (now - start) / dur);
            el.textContent = fmt(Math.round(from + (to - from) * ease(t)));
            if (t < 1 && !gone()) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    }

    // "+18% vs last week" — an arrow and words, never colour alone
    function delta(el, now, before) {
        if (!el) return;
        let text, tone;
        if (before === 0 && now === 0) { text = 'No change'; tone = 'flat'; }
        else if (before === 0) { text = 'New ' + RANGE[period].word; tone = 'up'; }
        else {
            const pct = Math.round(((now - before) / before) * 100);
            tone = pct > 0 ? 'up' : (pct < 0 ? 'down' : 'flat');
            text = (pct > 0 ? '+' : '') + pct + '% vs ' + RANGE[period].prev;
        }
        el.dataset.tone = tone;
        el.querySelector('span').textContent = text;
        el.title = fmt(before) + ' ' + RANGE[period].prev;
    }

    /* ───────────────────────── Sparkline ───────────────────────── */

    // Small SVG trend for "Activity today" (sign-ins this week); a path, no chart library needed
    function sparkline(values) {
        const svg = $('dbSpark');
        if (!svg) return;
        const w = 120, h = 36, pad = 3, max = Math.max(1, ...values);
        const pts = values.map((v, i) => [pad + i * (w - pad * 2) / Math.max(1, values.length - 1), h - pad - (v / max) * (h - pad * 2)]);
        const line = pts.map((p, i) => (i ? 'L' : 'M') + p[0].toFixed(1) + ' ' + p[1].toFixed(1)).join(' ');
        const area = line + ` L${pts[pts.length - 1][0].toFixed(1)} ${h} L${pts[0][0].toFixed(1)} ${h} Z`;
        svg.innerHTML = `<path d="${area}" class="db-spark-area"/><path d="${line}" class="db-spark-line"/>`;
    }

    /* ─────────────────────── Screen-reader tables ──────────────── */

    function table(id, caption, labels, series) {
        const t = $(id);
        if (!t) return;
        const esc = (s) => String(s).replace(/[&<>]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]));
        t.innerHTML = `<caption>${esc(caption)}</caption><thead><tr><th scope="col">Period</th>`
            + series.map((s) => `<th scope="col">${esc(s.name)}</th>`).join('') + '</tr></thead><tbody>'
            + labels.map((l, i) => `<tr><th scope="row">${esc(l)}</th>` + series.map((s) => `<td>${s.values[i] || 0}</td>`).join('') + '</tr>').join('')
            + '</tbody>';
    }

    /* ─────────────────────────── Charts ────────────────────────── */

    let sign = null, regs = null;

    // One smooth left-to-right reveal of the whole line (instead of point-by-point delays)
    const reveal = {
        id: 'dbReveal',
        beforeDatasetsDraw(chart) {
            const p = chart.$reveal;
            if (p === undefined || p >= 1) return;
            const a = chart.chartArea;
            chart.ctx.save();
            chart.ctx.beginPath();
            chart.ctx.rect(a.left - 4, a.top - 10, (a.right - a.left + 8) * p, a.bottom - a.top + 20);
            chart.ctx.clip();
            chart.$clipped = true;
        },
        afterDatasetsDraw(chart) {
            if (chart.$clipped) { chart.ctx.restore(); chart.$clipped = false; }
        },
    };

    function playReveal(chart) {
        if (reduceMotion) { chart.$reveal = 1; chart.update('none'); return; }
        const start = performance.now();
        chart.$reveal = 0;
        const step = (now) => {
            if (gone()) return;
            chart.$reveal = ease(Math.min(1, (now - start) / REVEAL_MS));
            chart.draw();
            if (chart.$reveal < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    }

    // Thin vertical guide under the hovered point
    const crosshair = {
        id: 'dbCrosshair',
        afterDatasetsDraw(chart) {
            const active = chart.tooltip && chart.tooltip.getActiveElements();
            if (!active || !active.length) return;
            const x = active[0].element.x, a = chart.chartArea, ctx = chart.ctx;
            ctx.save();
            ctx.setLineDash([4, 4]);
            ctx.lineWidth = 1;
            ctx.strokeStyle = theme().tick;
            ctx.globalAlpha = .5;
            ctx.beginPath(); ctx.moveTo(x, a.top); ctx.lineTo(x, a.bottom); ctx.stroke();
            ctx.restore();
        },
    };

    // Our own tooltip card (HTML), positioned inside the chart box
    function htmlTooltip(boxId) {
        return (context) => {
            const box = $(boxId);
            if (!box) return;
            const tip = context.tooltip;
            if (tip.opacity === 0) { box.dataset.open = 'false'; return; }
            const i = tip.dataPoints && tip.dataPoints.length ? tip.dataPoints[0].dataIndex : 0;
            const d = data[period];
            const th = theme();
            const rows = tip.dataPoints.map((p) =>
                `<div class="db-tip-row"><i style="background:${p.dataset.label === 'Approvals' ? th.teal : th.blue}"></i>`
                + `<span>${p.dataset.label}</span><b>${fmt(p.parsed.y)}</b></div>`).join('');
            box.innerHTML = `<div class="db-tip-title">${(d.titles && d.titles[i]) || tip.title}</div>${rows}`;
            const canvas = context.chart.canvas;
            const left = Math.min(Math.max(tip.caretX, 80), canvas.offsetWidth - 80);
            box.style.transform = `translate(${left}px, ${Math.max(tip.caretY - 12, 0)}px) translate(-50%, -100%)`;
            box.dataset.open = 'true';
        };
    }

    function axes(t) {
        return {
            x: {
                grid: { display: false }, border: { display: false },
                ticks: { color: t.tick, maxRotation: 0, autoSkipPadding: 16, padding: 6, font: { size: 11.5, weight: '600' } },
            },
            y: {
                beginAtZero: true, grace: '8%',
                grid: { color: t.grid, drawTicks: false }, border: { display: false, dash: [4, 4] },
                ticks: { color: t.tick, precision: 0, maxTicksLimit: 5, padding: 10, font: { size: 11 } },
            },
        };
    }

    // Area fill fades from the line down to the baseline
    function areaFill(color) {
        return (context) => {
            const { chart } = context;
            const a = chart.chartArea;
            if (!a) return rgba(color, .12);
            const g = chart.ctx.createLinearGradient(0, a.top, 0, a.bottom);
            g.addColorStop(0, rgba(color, theme().dark ? .28 : .2));
            g.addColorStop(1, rgba(color, 0));
            return g;
        };
    }

    function buildCharts() {
        if (typeof Chart === 'undefined') {
            ['dbSignChart', 'dbRegChart'].forEach((id) => {
                const c = $(id);
                if (c) c.parentNode.innerHTML = '<div class="db-chart-note">The chart could not load (no internet connection). The numbers on this page are up to date.</div>';
            });
            return;
        }
        const t = theme();
        const d = data[period];
        Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";

        const common = {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            animation: reduceMotion ? false : { duration: 800, easing: 'easeOutCubic' },
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: axes(t),
        };

        sign = new Chart($('dbSignChart'), {
            type: 'line',
            data: {
                labels: d.labels,
                datasets: [{
                    label: 'Sign-ins', data: d.log,
                    borderColor: t.blue, backgroundColor: areaFill(t.blue), fill: 'origin',
                    borderWidth: 2.5, cubicInterpolationMode: 'monotone', borderCapStyle: 'round', borderJoinStyle: 'round',
                    pointRadius: 0, pointHitRadius: 18, pointHoverRadius: 6,
                    pointHoverBackgroundColor: t.blue, pointHoverBorderColor: t.surface, pointHoverBorderWidth: 3,
                }],
            },
            options: {
                ...common,
                transitions: { active: { animation: { duration: 150 } } },
                plugins: { ...common.plugins, tooltip: { enabled: false, external: htmlTooltip('dbSignTip') } },
            },
            plugins: [reveal, crosshair],
        });
        playReveal(sign);

        regs = new Chart($('dbRegChart'), {
            type: 'bar',
            data: {
                labels: d.labels,
                datasets: [
                    { label: 'Registrations', data: d.reg, backgroundColor: t.blue },
                    { label: 'Approvals', data: d.apr, backgroundColor: t.teal },
                ],
            },
            options: {
                ...common,
                datasets: { bar: { borderRadius: 6, borderSkipped: 'start', categoryPercentage: .64, barPercentage: .84, maxBarThickness: 16 } },
                plugins: { ...common.plugins, tooltip: { enabled: false, external: htmlTooltip('dbRegTip') } },
                // Fade the other days so the hovered one stands out
                onHover: (event, active, chart) => {
                    const idx = active.length ? active[0].index : -1;
                    if (chart.$hoverIndex === idx) return;
                    chart.$hoverIndex = idx;
                    const th = theme();
                    chart.data.datasets.forEach((ds, k) => {
                        const base = k === 0 ? th.blue : th.teal;
                        ds.backgroundColor = idx < 0 ? base : ds.data.map((_, j) => (j === idx ? base : rgba(base, .28)));
                    });
                    chart.update('none');
                },
            },
        });

        // Dark / light switch: recolour in place
        const watch = new MutationObserver(() => {
            if (gone()) { watch.disconnect(); return; }
            const th = theme();
            const s = sign.data.datasets[0];
            s.borderColor = th.blue; s.backgroundColor = areaFill(th.blue);
            s.pointHoverBackgroundColor = th.blue; s.pointHoverBorderColor = th.surface;
            regs.data.datasets[0].backgroundColor = th.blue;
            regs.data.datasets[1].backgroundColor = th.teal;
            [sign, regs].forEach((ch) => { ch.options.scales = axes(th); ch.update('none'); });
            legend(th);
        });
        watch.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
    }

    function legend(t) {
        root.querySelectorAll('[data-swatch="reg"]').forEach((el) => { el.style.background = t.blue; });
        root.querySelectorAll('[data-swatch="apr"]').forEach((el) => { el.style.background = t.teal; });
    }

    /* ───────────────────────── Period view ─────────────────────── */

    function renderText() {
        const d = data[period];
        if (!d) return;
        const prev = d.prev || { log: 0, reg: 0, apr: 0 };

        countTo($('dbSignTotal'), sum(d.log));
        countTo($('dbRegTotal'), sum(d.reg));
        $('dbSignRange').textContent = RANGE[period].word;
        delta($('dbSignDelta'), sum(d.log), prev.log);
        delta($('dbRegDelta'), sum(d.reg), prev.reg);
        table('dbSignTable', 'Sign-ins ' + RANGE[period].word, d.labels, [{ name: 'Sign-ins', values: d.log }]);
        table('dbRegTable', 'Registrations and approvals ' + RANGE[period].word, d.labels,
            [{ name: 'Registrations', values: d.reg }, { name: 'Approvals', values: d.apr }]);
        if (data.weekly) sparkline(data.weekly.log);
    }

    function render(rangeChanged) {
        const d = data[period];
        if (!d) return;
        renderText();
        if (!sign) return;
        if (rangeChanged && regs.$hoverIndex !== undefined && regs.$hoverIndex !== -1) {
            const th = theme();
            regs.$hoverIndex = -1;
            regs.data.datasets[0].backgroundColor = th.blue;
            regs.data.datasets[1].backgroundColor = th.teal;
        }
        sign.data.labels = d.labels; sign.data.datasets[0].data = d.log;
        regs.data.labels = d.labels; regs.data.datasets[0].data = d.reg; regs.data.datasets[1].data = d.apr;
        if (rangeChanged) {
            // A new range draws in again; bars grow from the baseline
            sign.update('none'); playReveal(sign);
            regs.reset(); regs.update();
        } else {
            // Same range, new numbers: values glide to their new height
            sign.update(); regs.update();
        }
    }

    root.querySelector('.db-tabs').addEventListener('click', (e) => {
        const b = e.target.closest('button[data-period]');
        if (!b || b.dataset.period === period) return;
        root.querySelectorAll('.db-tabs button').forEach((x) => x.setAttribute('aria-pressed', x === b ? 'true' : 'false'));
        period = b.dataset.period;
        render(true);
    });

    /* ───────────────────────── Live refresh ────────────────────── */

    const live = $('dbLive');
    let timer = null, busy = false;

    function setText(el, text) { if (el && el.textContent !== text) el.textContent = text; }

    function swapList(id, html) {
        const el = $(id);
        if (!el || typeof html !== 'string' || el.innerHTML.trim() === html.trim()) return;
        // Never move a button out from under the admin's pointer or keyboard focus
        if (el.contains(document.activeElement) || el.matches(':hover')) return;
        el.innerHTML = html;
        el.classList.remove('db-flash'); void el.offsetWidth; el.classList.add('db-flash');
    }

    function apply(json) {
        const c = json.counts || {};
        root.querySelectorAll('[data-count]').forEach((el) => {
            if (c[el.dataset.count] !== undefined) countTo(el, c[el.dataset.count]);
        });
        root.querySelectorAll('[data-note]').forEach((el) => {
            const n = Number(c[el.dataset.note] || 0);
            setText(el, n + el.dataset.text);
            if (el.dataset.warn !== undefined) el.classList.toggle('warn', n > 0);
        });
        root.querySelectorAll('[data-att]').forEach((a) => {
            const n = Number(c[a.dataset.att] || 0);
            if (a.dataset.one === undefined) return;
            a.classList.toggle('warn', n > 0); a.classList.toggle('ok', n === 0);
            setText(a.querySelector('[data-att-text]'), n === 0 ? a.dataset.none : (n === 1 ? a.dataset.one : a.dataset.many));
        });
        if (json.charts) { data = json.charts; render(false); }
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
            .catch(() => { live.classList.add('paused'); setText($('dbLiveLabel'), 'Reconnecting'); })
            .finally(() => { busy = false; live.classList.remove('updating'); });
    }

    function onVisible() {
        if (gone()) { document.removeEventListener('visibilitychange', onVisible); return; }
        if (!document.hidden) refresh();
    }

    /* ─────────────────────────── Start ─────────────────────────── */

    legend(theme());
    // Numbers count up from zero on the first paint
    root.querySelectorAll('.db-kpi-num[data-count]').forEach((el) => {
        const to = Number(el.textContent.replace(/[^\d-]/g, '')) || 0;
        el.textContent = '0';
        countTo(el, to);
    });
    buildCharts();
    renderText();
    timer = setInterval(refresh, POLL_MS);
    document.addEventListener('visibilitychange', onVisible);
})();
