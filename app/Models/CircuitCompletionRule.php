<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CircuitCompletionRule extends Model
{
    protected $fillable = [
        'circuit_id',
        'rule_type',           // 'zone_stay'
        'reference_type',      // 'zone'
        'reference_id',        // ID BDD local
        'stay_hours',          // durée sans sortie (heures)
        'produces_valid_rotation',
        'label',
        'order',
    ];

    protected $casts = [
        'produces_valid_rotation' => 'boolean',
    ];

    public function circuit(): BelongsTo
    {
        return $this->belongsTo(Circuit::class);
    }

    public function reference(): Zone|Checkpoint|null
    {
        return match ($this->reference_type) {
            'zone'       => Zone::find($this->reference_id),
            'checkpoint' => Checkpoint::find($this->reference_id),
            default      => null,
        };
    }

    public function getLabel(): string
    {
        if ($this->label) return $this->label;

        $ref = $this->reference()?->name ?? '?';

        return match ($this->rule_type) {
            'zone_stay' => "Arrivée {$ref} sans sortie dans {$this->stay_hours}h",
            default     => "Règle {$this->rule_type}",
        };
    }
}