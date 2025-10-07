<div id="seat-picker"
     data-event-id="{{ $eventId }}"
     data-registration-id="{{ $registrationId }}"
     class="sp-space-y-3">
  <div id="sp-loading" class="sp-text-sm sp-text-muted">Memuat peta kursi…</div>

  <div class="sp-legend sp-mb-2">
    <span class="sp-badge" style="background: var(--sp-green);"></span><span class="label">Tersedia</span>
    <span class="sp-badge" style="background: var(--sp-gray);"></span><span class="label">Terisi</span>
    <span class="sp-badge" style="background: var(--sp-amber);"></span><span class="label">Diblok</span>
  </div>

  <div id="sp-error" class="sp-text-sm" style="color:#dc2626; display:none;"></div>
  <div id="sp-content" class="sp-space-y-3" style="display:none;"></div>
</div>