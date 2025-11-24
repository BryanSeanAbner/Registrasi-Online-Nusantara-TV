<style>
:root {
  --sp-green: #16a34a;
  --sp-green-hover: #15803d;
  --sp-gray: #9ca3af;
  --sp-amber: #f59e0b;
  --sp-text: #ffffff;
  --sp-muted: #6b7280;
  --sp-border: #e5e7eb;
  --sp-bg: #ffffff;
}

.sp-hidden { display: none !important; }
.sp-text-sm { font-size: 0.875rem; line-height: 1.25rem; }
.sp-text-xs { font-size: 0.75rem; line-height: 1rem; }
.sp-text-muted { color: var(--sp-muted); }
.sp-text { color: var(--sp-text); }
.sp-rounded { border-radius: .5rem; }
.sp-border { border: 1px solid var(--sp-border); }
.sp-card { background: var(--sp-bg); border: 1px solid var(--sp-border); border-radius: .5rem; padding: .75rem; }
.sp-mb-2 { margin-bottom: .5rem; }
.sp-mb-3 { margin-bottom: .75rem; }
.sp-mt-4 { margin-top: 1rem; }
.sp-mt-6 { margin-top: 1.5rem; }
.sp-py-1 { padding-top: .25rem; padding-bottom: .25rem; }
.sp-p-2 { padding: .5rem; }
.sp-gap-2 { display: inline-flex; gap: .5rem; align-items: center; }
.sp-badge { display:inline-block; width:.75rem; height:.75rem; border-radius:.125rem; }

.sp-legend { display:flex; gap:.75rem; align-items:center; flex-wrap:wrap; }
.sp-legend span.label { font-size:.75rem; color:var(--sp-muted); }

.sp-grid { display:grid; gap:6px; }

.sp-seat {
  font-size:.75rem;
  line-height:1rem;
  padding:.25rem .25rem;
  border:none;
  border-radius:.375rem;
  color:#fff;
  cursor:pointer;
  transition:background-color .2s ease, opacity .2s ease;
  min-height:28px;
}
.sp-seat.is-available { background: var(--sp-green); }
.sp-seat.is-available:hover { background: var(--sp-green-hover); }
.sp-seat.is-taken { background: var(--sp-gray); cursor:not-allowed; }
.sp-seat.is-blocked { background: var(--sp-amber); cursor:not-allowed; }

.sp-section { border:1px solid var(--sp-border); border-radius:.5rem; padding:.75rem; }
.sp-section-title { font-weight:600; margin-bottom:.5rem; color:var(--sp-text); }

.sp-space-y-4 > * + * { margin-top: 1rem; }
.fi-modal-content .sp-space-y-3 > * + * { margin-top:.75rem; }

.sp-kvlist{ display:flex; flex-wrap:wrap; gap:.375rem; }
.sp-kv{
  display:inline-flex; align-items:baseline; gap:.35rem;
  padding:.25rem .5rem; border:1px solid var(--sp-border); border-radius:.5rem;
  background: var(--sp-bg);
}
.sp-kv .label{ font-size:.75rem; color:#6b7280; }
.sp-kv .value{ font-weight:600; font-size:.875rem; color:#111827; max-width:18rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

.dark .sp-kv{ border-color:#374151; background:#0b0f19; }
.dark .sp-kv .label{ color:#9ca3af; }
.dark .sp-kv .value{ color:#e5e7eb; }

/* ====== Registration detail components ====== */
.rf-modal-stack > * + * { margin-top: 1.5rem; }
.rf-action-bar {
  display:flex; flex-wrap:wrap; gap:.5rem;
  border-bottom:1px solid rgba(15,23,42,.1);
  padding:.35rem .25rem .75rem;
}
.rf-panel {
  border:1px solid rgba(15,23,42,.12);
  border-radius:1rem;
  padding:1rem;
  background:rgba(255,255,255,.9);
}
.rf-panel-title {
  font-size:.72rem;
  letter-spacing:.12em;
  font-weight:600;
  text-transform:uppercase;
  color:#6b7280;
  margin-bottom:.85rem;
}
.rf-summary-stack > * + * { margin-top:1rem; }
.rf-summary-grid {
  display:grid;
  gap:.75rem;
  grid-template-columns:repeat(auto-fit,minmax(210px,1fr));
}
.rf-summary-card {
  border:1px solid rgba(15,23,42,.08);
  border-radius:.65rem;
  padding:.75rem;
  background:#fff;
}
.rf-summary-card dt {
  font-size:.78rem;
  color:#6b7280;
  margin-bottom:.3rem;
}
.rf-summary-card dd {
  margin:0;
  font-size:.95rem;
  font-weight:600;
  color:#0f172a;
}
.rf-status-badge {
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-height:1.7rem;
  padding:0 .9rem;
  border-radius:999px;
  font-size:.78rem;
  font-weight:600;
}
.rf-status-success { background:#dcfce7; color:#15803d; }
.rf-status-danger { background:#fee2e2; color:#b91c1c; }
.rf-status-warning { background:#fef3c7; color:#b45309; }
.rf-status-muted { background:#e5e7eb; color:#374151; }
.rf-fieldlist {
  display:grid;
  grid-template-columns:140px 1fr;
  gap:6px 12px;
  margin:0;
}
.rf-fieldlist dt {
  font-size:12px;
  color:#6b7280;
  line-height:1.25;
}
.rf-fieldlist dd {
  margin:0;
  font-size:14px;
  font-weight:600;
  color:#111827;
  line-height:1.35;
  word-break:break-word;
}
.rf-fieldlist dd a { color:inherit; text-decoration:none; }
.rf-fieldlist dd a:hover { text-decoration:underline; }
.rf-badge {
  display:inline-block;
  padding:2px 6px;
  border-radius:6px;
  background:#f3f4f6;
  color:#374151;
  font-size:12px;
}
.rf-empty-text { font-size:.85rem; color:#6b7280; margin:0; }

.dark .rf-panel {
  border-color:rgba(255,255,255,.12);
  background:rgba(26,27,28,.7);
}
.dark .rf-panel-title { color:#d1d5db; }
.dark .rf-summary-card {
  border-color:rgba(255,255,255,.14);
  background:rgba(26,27,28,.7);
}
.dark .rf-summary-card dt { color:#9ca3af; }
.dark .rf-summary-card dd { color:#f3f4f6; }
.dark .rf-action-bar { border-color:rgba(255,255,255,.12); }
.dark .rf-fieldlist dt { color:#9ca3af; }
.dark .rf-fieldlist dd { color:#e5e7eb; }
.dark .rf-badge { background:#374151; color:#e5e7eb; }
.dark .rf-status-success { background:rgba(34,197,94,.18); color:#4ade80; }
.dark .rf-status-danger { background:rgba(248,113,113,.18); color:#fca5a5; }
.dark .rf-status-warning { background:rgba(251,191,36,.18); color:#fcd34d; }
.dark .rf-status-muted { background:rgba(148,163,184,.24); color:#f3f4f6; }
</style>
