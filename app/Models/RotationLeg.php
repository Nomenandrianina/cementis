<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RotationLeg extends Model
{
    protected $fillable = [
        'rotation_id', 'circuit_leg_id', 'occurred_at',
        'lat', 'lng', 'duration_since_previous_seconds', 'raw_event','skipped_by_parent',
    ];
 
    protected $casts = [
        'occurred_at' => 'datetime',
        'raw_event'   => 'array',
        'lat'         => 'float',
        'lng'         => 'float',
    ];
 
    public function rotation(): BelongsTo   { return $this->belongsTo(Rotation::class); }
    public function circuitLeg(): BelongsTo { return $this->belongsTo(CircuitLeg::class); }
}