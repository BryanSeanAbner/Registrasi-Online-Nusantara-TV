<style>
/* Kolom Form Answers — adaptive light/dark */
.rf-fieldlist {
  display: grid;
  grid-template-columns: 140px 1fr;
  gap: 6px 12px;
  margin: 0;
}

/* default (light) */
.rf-fieldlist dt { font-size: 12px; color: #6b7280; line-height: 1.25; }   /* gray-500 */
.rf-fieldlist dd {
  margin: 0; font-size: 14px; font-weight: 600; color: #111827;           /* gray-900 */
  line-height: 1.35; word-break: break-word;
}

/* link di dalam value mewarisi warna & rapi saat hover */
.rf-fieldlist dd a { color: inherit; text-decoration: none; }
.rf-fieldlist dd a:hover { text-decoration: underline; }

/* dark mode */
@media (prefers-color-scheme: dark) {
  .rf-fieldlist dt { color: #9ca3af; }    /* gray-400 agar terbaca */
  .rf-fieldlist dd { color: #e5e7eb; }    /* gray-200 terang */
}

/* badge kosong */
.rf-badge{
  display:inline-block; padding:2px 6px; border-radius:6px;
  background:#f3f4f6; color:#374151; font-size:12px;
}
@media (prefers-color-scheme: dark){
  .rf-badge{ background:#374151; color:#e5e7eb; }
}

</style>