{{-- Shared by the LRN Master List and the import preview. Layout tokens only, so dark mode follows. --}}
<style>
    .lm { max-width: 1180px; margin: 0 auto; --lm-line: var(--border-default); --lm-blue: #1D4ED8; }
    .lm-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
    .lm-card { background: var(--surface-card); border: 1px solid var(--lm-line); border-radius: 16px; }

    /* Header */
    .lm-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 14px 20px; margin-bottom: 20px; }
    .lm-title { font-size: 26px; font-weight: 800; color: var(--slate-900); letter-spacing: -.02em; line-height: 1.2; }
    .lm-sub { margin-top: 4px; font-size: 14px; color: var(--slate-600); max-width: 60ch; text-wrap: pretty; }
    .lm-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .lm-back { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 10px; font-size: 13px; font-weight: 700; color: var(--slate-500); text-decoration: none; }
    .lm-back:hover { color: var(--slate-900); }
    .lm-back svg { width: 15px; height: 15px; }

    /* Buttons */
    .lm-btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 40px; padding: 0 16px; border-radius: 10px;
              border: 1px solid var(--lm-line); background: var(--surface-card); color: var(--slate-800); font: inherit; font-size: 13.5px; font-weight: 700;
              cursor: pointer; text-decoration: none; white-space: nowrap; transition: background-color .15s ease, border-color .15s ease, transform .1s ease; }
    .lm-btn:hover { background: var(--slate-50); border-color: var(--slate-300); }
    .lm-btn:active { transform: scale(.97); }
    .lm-btn svg { width: 16px; height: 16px; flex-shrink: 0; }
    .lm-btn.primary { background: var(--lm-blue); border-color: var(--lm-blue); color: #fff; }
    .lm-btn.primary:hover { background: #1E40AF; border-color: #1E40AF; }
    .lm-btn.danger { background: #DC2626; border-color: #DC2626; color: #fff; }
    .lm-btn.danger:hover { background: #B91C1C; border-color: #B91C1C; }
    .lm-btn:disabled { opacity: .55; cursor: not-allowed; transform: none; }
    .lm-btn:focus-visible, .lm-icon-btn:focus-visible, .lm-tabs a:focus-visible, .lm-link:focus-visible { outline: 2px solid #2563EB; outline-offset: 2px; }
    .lm-link { font-size: 13px; font-weight: 700; color: #2563EB; text-decoration: none; }
    .lm-link:hover { text-decoration: underline; text-underline-offset: 3px; }
    [data-theme="dark"] .lm-link { color: #93C5FD; }

    .lm-alert { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 14px; padding: 11px 14px; border-radius: 12px; font-size: 13.5px; font-weight: 600; line-height: 1.45;
                background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; }
    .lm-alert svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
    [data-theme="dark"] .lm-alert { background: rgba(220,38,38,.12); border-color: rgba(248,113,113,.3); color: #FCA5A5; }

    /* Summary */
    .lm-summary { padding: 18px 22px; margin-bottom: 14px; }
    .lm-figures { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .lm-figures div { padding: 0 20px; }
    .lm-figures div:first-child { padding-left: 0; }
    .lm-figures div + div { border-left: 1px solid var(--lm-line); }
    .lm-figures span { display: block; font-size: 13px; font-weight: 600; color: var(--slate-500); }
    .lm-figures b { display: block; margin-top: 4px; font-size: 28px; font-weight: 800; color: var(--slate-900); letter-spacing: -.02em; line-height: 1.1; font-variant-numeric: tabular-nums; }
    .lm-progress { display: flex; align-items: center; gap: 12px; margin-top: 16px; font-size: 12.5px; color: var(--slate-500); }
    .lm-bar { flex: 0 0 220px; height: 8px; border-radius: 99px; background: var(--slate-100); overflow: hidden; }
    .lm-bar i { display: block; height: 100%; border-radius: 99px; background: #16A34A; }

    /* List */
    .lm-list { overflow: hidden; }
    .lm-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--lm-line); }
    .lm-tabs { display: inline-flex; padding: 3px; border-radius: 11px; background: var(--slate-100); }
    .lm-tabs a { display: inline-flex; align-items: center; gap: 7px; min-height: 34px; padding: 0 12px; border-radius: 8px; font-size: 13px; font-weight: 700;
                 color: var(--slate-600); text-decoration: none; white-space: nowrap; transition: background-color .15s ease, color .15s ease; }
    .lm-tabs a:hover { color: var(--slate-900); }
    .lm-tabs a span { font-size: 12px; font-weight: 700; color: var(--slate-500); font-variant-numeric: tabular-nums; }
    .lm-tabs a[aria-current="page"] { background: var(--surface-card); color: var(--slate-900); box-shadow: 0 1px 2px rgba(15,23,42,.1), 0 0 0 1px rgba(15,23,42,.04); }
    .lm-filters { display: flex; flex-wrap: wrap; gap: 8px; flex: 1 1 340px; justify-content: flex-end; }
    .lm-filters select, .lm-input { min-height: 40px; padding: 0 12px; border-radius: 10px; border: 1px solid var(--lm-line); background: var(--surface-card);
                                    color: var(--slate-900); font: inherit; font-size: 13.5px; transition: border-color .15s ease, box-shadow .15s ease; }
    .lm-filters select { width: auto; flex: 0 0 auto; cursor: pointer; }
    .lm-search { position: relative; display: block; flex: 1 1 220px; max-width: 320px; }
    .lm-search svg { position: absolute; left: 12px; top: 50%; width: 16px; height: 16px; transform: translateY(-50%); color: var(--slate-400); pointer-events: none; }
    .lm-search input { width: 100%; min-height: 40px; padding: 0 12px 0 36px; border-radius: 10px; border: 1px solid var(--lm-line); background: var(--surface-card);
                       color: var(--slate-900); font: inherit; font-size: 13.5px; transition: border-color .15s ease, box-shadow .15s ease; }
    .lm-filters select:focus, .lm-search input:focus, .lm-input:focus { outline: none; border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,.16); }

    .lm-table-wrap { overflow-x: auto; }
    .lm-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .lm-table th { padding: 10px 16px; text-align: left; font-size: 12px; font-weight: 700; color: var(--slate-500); background: var(--slate-50);
                   border-bottom: 1px solid var(--lm-line); white-space: nowrap; }
    .lm-table td { padding: 12px 16px; border-bottom: 1px solid var(--lm-line); color: var(--slate-700); vertical-align: middle; }
    .lm-table tbody tr:last-child td { border-bottom: 0; }
    .lm-table tbody tr { transition: background-color .12s ease; }
    .lm-table tbody tr:hover { background: var(--slate-50); }
    .lm-lrn { font-weight: 700; color: var(--slate-900) !important; font-variant-numeric: tabular-nums; letter-spacing: .03em; white-space: nowrap; }
    .lm-name { font-weight: 600; color: var(--slate-900) !important; }
    .lm-muted { color: var(--slate-400); }
    .lm-grade { display: inline-grid; place-items: center; min-width: 30px; height: 24px; padding: 0 7px; border-radius: 7px; background: var(--slate-100);
                color: var(--slate-700); font-size: 12.5px; font-weight: 700; }
    .lm-status { display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px 3px 8px; border-radius: 99px; font-size: 12px; font-weight: 700; white-space: nowrap;
                 background: var(--slate-100); color: var(--slate-600); }
    .lm-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; opacity: .8; }
    .lm-status.on { background: #DCFCE7; color: #166534; }
    [data-theme="dark"] .lm-status.on { background: rgba(22,163,74,.16); color: #86EFAC; }
    .lm-acct { display: block; font-weight: 600; color: var(--slate-900); }
    .lm-email { display: block; margin-top: 1px; font-size: 12.5px; color: var(--slate-500); }
    .lm-act { width: 1%; text-align: right; }
    .lm-icon-btn { display: inline-grid; place-items: center; width: 36px; height: 36px; border-radius: 9px; border: 1px solid transparent; background: transparent;
                   color: var(--slate-400); cursor: pointer; transition: background-color .15s ease, color .15s ease, border-color .15s ease; }
    .lm-icon-btn svg { width: 17px; height: 17px; }
    .lm-icon-btn:hover { color: #DC2626; background: #FEF2F2; border-color: #FECACA; }
    [data-theme="dark"] .lm-icon-btn:hover { color: #FCA5A5; background: rgba(220,38,38,.12); border-color: rgba(248,113,113,.3); }

    .lm-pages { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 16px; border-top: 1px solid var(--lm-line);
                font-size: 13px; color: var(--slate-500); }
    .lm-pages nav { margin: 0; }
    .lm-empty { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 48px 20px; text-align: center; font-size: 13.5px; color: var(--slate-500); }
    .lm-empty svg { width: 40px; height: 40px; color: var(--slate-300); margin-bottom: 4px; }
    .lm-empty b { font-size: 15px; color: var(--slate-900); }
    .lm-empty a { color: #2563EB; font-weight: 700; }
    .lm-empty .lm-btn { margin-top: 10px; }

    /* Dialogs (native <dialog>: focus stays inside, Esc closes) */
    /* inset + margin:auto centre it; the layout's global reset removes the browser's own margin:auto */
    .lm-dialog { position: fixed; inset: 0; margin: auto; width: min(560px, calc(100vw - 32px)); height: fit-content; max-height: calc(100dvh - 48px); padding: 0; border: 1px solid var(--lm-line); border-radius: 18px;
                 background: var(--surface-card); color: var(--slate-900); box-shadow: 0 24px 60px -12px rgba(15,23,42,.35), 0 4px 12px rgba(15,23,42,.08); overflow: hidden; }
    .lm-dialog.small { width: min(440px, calc(100vw - 32px)); }
    .lm-dialog::backdrop { background: rgba(15,23,42,.45); backdrop-filter: blur(3px); }
    .lm-dialog[open] { animation: lm-in .24s cubic-bezier(.2,.8,.2,1); }
    .lm-dialog[open]::backdrop { animation: lm-fade .2s ease; }
    .lm-dialog.closing { animation: lm-out .14s ease forwards; }
    .lm-dialog.closing::backdrop { animation: lm-fade .14s ease reverse forwards; }
    @keyframes lm-in { from { opacity: 0; transform: translateY(8px) scale(.97); } }
    @keyframes lm-out { to { opacity: 0; transform: translateY(4px) scale(.98); } }
    @keyframes lm-fade { from { opacity: 0; } }
    html:has(.lm-dialog[open]) { overflow: hidden; }
    .lm-dialog-box { display: flex; flex-direction: column; max-height: inherit; }
    .lm-dialog-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 16px 14px 22px; border-bottom: 1px solid var(--lm-line); }
    .lm-dialog-head h2 { font-size: 16px; font-weight: 800; }
    .lm-close { display: grid; place-items: center; width: 36px; height: 36px; border-radius: 10px; border: 1px solid var(--lm-line); background: transparent; color: var(--slate-500); cursor: pointer; }
    .lm-close:hover { background: var(--slate-50); color: var(--slate-900); }
    .lm-close svg { width: 16px; height: 16px; }
    .lm-dialog-body { flex: 1; min-height: 0; overflow-y: auto; overscroll-behavior: contain; padding: 18px 22px 20px; }
    .lm-dialog-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 22px; border-top: 1px solid var(--lm-line); background: var(--slate-50); }
    .lm-dialog-foot.end { justify-content: flex-end; }
    .lm-dialog-foot > div { display: flex; gap: 8px; margin-left: auto; }
    .lm-confirm-title { font-size: 17px; font-weight: 800; margin-bottom: 6px; }

    .lm-field { display: flex; align-items: baseline; gap: 6px; margin: 16px 0 6px; font-size: 13px; font-weight: 700; color: var(--slate-700); }
    .lm-field span { font-weight: 500; color: var(--slate-400); }
    .lm-input { width: 100%; }
    .lm-input.bad { border-color: #DC2626; }
    .lm-lrn-input { font-variant-numeric: tabular-nums; letter-spacing: .06em; font-weight: 700; }
    .lm-hint-row { display: flex; justify-content: space-between; gap: 10px; margin-top: 6px; font-size: 12px; color: var(--slate-500); }
    .lm-hint-row .ok { color: #15803D; font-weight: 700; }
    .lm-err { display: block; margin-top: 6px; font-size: 12.5px; font-weight: 600; color: #B91C1C; }
    [data-theme="dark"] .lm-err { color: #FCA5A5; }
    .lm-help { margin-top: 6px; font-size: 12.5px; color: var(--slate-500); line-height: 1.5; text-wrap: pretty; }
    .lm-help.first { margin-top: 0; font-size: 13.5px; color: var(--slate-600); }

    .lm-drop { position: relative; display: flex; align-items: center; gap: 14px; padding: 18px; border: 1.5px dashed var(--slate-300); border-radius: 14px;
               background: var(--slate-50); cursor: pointer; transition: border-color .15s ease, background-color .15s ease; }
    .lm-drop:hover, .lm-drop.over { border-color: #2563EB; background: rgba(37,99,235,.05); }
    .lm-drop.has { border-style: solid; border-color: #2563EB; }
    .lm-drop.bad { border-color: #DC2626; background: rgba(220,38,38,.05); }
    .lm-drop input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .lm-drop:focus-within { box-shadow: 0 0 0 3px rgba(37,99,235,.18); }
    .lm-drop-ic { display: grid; place-items: center; width: 44px; height: 44px; border-radius: 12px; background: var(--surface-card); border: 1px solid var(--lm-line); color: #2563EB; flex-shrink: 0; }
    .lm-drop-ic svg { width: 22px; height: 22px; }
    .lm-drop.bad .lm-drop-ic { color: #DC2626; }
    .lm-drop-text { min-width: 0; font-size: 12.5px; color: var(--slate-500); line-height: 1.45; }
    .lm-drop-text b { display: block; font-size: 14px; color: var(--slate-900); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .lm-drop.bad .lm-drop-text span { color: #B91C1C; font-weight: 600; }
    .lm-steps { margin: 18px 0 0; padding: 14px 16px 14px 34px; border-radius: 12px; background: var(--slate-50); font-size: 12.5px; color: var(--slate-600); line-height: 1.55; }
    .lm-steps li + li { margin-top: 4px; }
    .lm-steps b { color: var(--slate-900); }

    @media (max-width: 760px) {
        .lm-figures { grid-template-columns: 1fr 1fr 1fr; }
        .lm-figures div { padding: 0 10px; }
        .lm-figures b { font-size: 22px; }
        .lm-progress { flex-direction: column; align-items: stretch; gap: 8px; }
        .lm-bar { flex-basis: auto; }
        .lm-filters { justify-content: stretch; }
        .lm-search { max-width: none; flex: 1 1 140px; min-width: 0; }
        .lm-tabs { width: 100%; }
        .lm-tabs a { flex: 1; justify-content: center; padding: 0 6px; }

        /* Rows become cards */
        .lm-table thead { display: none; }
        .lm-table, .lm-table tbody, .lm-table tr, .lm-table td { display: block; }
        .lm-table tr { position: relative; padding: 12px 16px; border-bottom: 1px solid var(--lm-line); }
        .lm-table tbody tr:last-child { border-bottom: 0; }
        .lm-table td { display: flex; gap: 12px; padding: 3px 0; border: 0; }
        .lm-table td[data-label]::before { content: attr(data-label); flex: 0 0 74px; font-size: 12px; font-weight: 600; color: var(--slate-500); }
        .lm-table td.lm-act { position: absolute; top: 8px; right: 10px; width: auto; padding: 0; }
    }
    @media (max-width: 560px) {
        .lm-actions, .lm-actions .lm-btn { flex: 1; }
        .lm-dialog { width: 100vw; max-width: 100vw; max-height: 92dvh; margin: auto 0 0; border-radius: 18px 18px 0 0; }
        .lm-dialog.small { width: 100vw; }
    }
    @media (prefers-reduced-motion: reduce) {
        .lm-dialog[open], .lm-dialog[open]::backdrop, .lm-dialog.closing { animation: none; }
        .lm-btn:active { transform: none; }
    }
</style>
