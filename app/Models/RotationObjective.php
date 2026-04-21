<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class RotationObjective extends Model
{
    protected $fillable = [
        'circuit_id', 'target_rotations_per_month', 'target_duration_seconds',
        'leg_objectives', 'effective_from', 'effective_until', 'notes',
    ];
 
    protected $casts = [
        'leg_objectives'  => 'array',
        'effective_from'  => 'date',
        'effective_until' => 'date',
    ];
 
    public function circuit(): BelongsTo { return $this->belongsTo(Circuit::class); }
}