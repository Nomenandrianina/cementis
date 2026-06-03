lade<div class="form-group">
    <label>Délai zone parent → sous-zone (heures)</label>
    <input type="number" name="parent_zone_delay_hours"
           value="{{ $circuit->parent_zone_delay_hours ?? 24 }}"
           min="1" max="720" style="width:120px;">
    <div style="font-size:11px;color:var(--muted);margin-top:3px;">
        Délai maximum entre entrée zone parent et sous-zone pour fin acceptable. Par défaut : 24h.
    </div>
</div>