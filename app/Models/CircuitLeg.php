<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

// ============================================================
// CircuitLeg — Étape d'un circuit
// ============================================================
class CircuitLeg extends Model
{
    protected $fillable = ['circuit_id', 'order', 'label', 'event_type', 'reference_type', 'reference_id', 'direction', 'optional'];
    protected $casts = [
        'optional' => 'boolean',
    ];

    public function circuit(): BelongsTo { return $this->belongsTo(Circuit::class); }
    public function rotationLegs(): HasMany { return $this->hasMany(RotationLeg::class); }

    public function reference(): Zone|Checkpoint|null
    {
        return match ($this->reference_type) {
            'zone'       => Zone::find($this->reference_id),
            'checkpoint' => Checkpoint::find($this->reference_id),
            default      => null,
        };
    }

    /**
     * Un leg est optionnel si :
     * - Il est marqué optional = true dans le circuit
     * - OU si le checkpoint associé est de type client/dépôt
     */
    public function isOptional(): bool
    {
        if (!empty($this->optional)) {
            return true;
        }

        if ($this->reference_type === 'checkpoint') {
            $cp = Checkpoint::find($this->reference_id);
            return $cp && $cp->isOptional();
        }

        if ($this->reference_type === 'zone' && !empty($this->reference_id)) {
            $zone = Zone::find($this->reference_id);
            return $zone && $zone->isOptional();
        }

        return false;
    }
}