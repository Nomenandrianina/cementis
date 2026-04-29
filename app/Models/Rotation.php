<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;



// ============================================================
// Rotation
// ============================================================
class Rotation extends Model
{
     protected $fillable = [
        'rvehicule_id', 'circuit_id', 'started_at', 'completed_at',
        'duration_seconds', 'status', 'counted_month', 'is_valid',
        'invalidation_reason', 'raw_events',
    ];
 
    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'is_valid'     => 'boolean',
        'raw_events'   => 'array',
    ];
 
    public function rvehicule(): BelongsTo { return $this->belongsTo(Rvehicule::class); }
    public function circuit(): BelongsTo { return $this->belongsTo(Circuit::class); }
 
    public function rotationLegs(): HasMany
    {
        return $this->hasMany(RotationLeg::class)->orderBy('occurred_at');
    }

    public function getStartedAtLocalAttribute()
    {
        if (!$this->started_at) {
            return null;
        }

        // On s'assure que c'est une instance Carbon, puis on change la timezone
        return $this->started_at->copy()->timezone('Africa/Nairobi'); 
    }

    public function getCompletedAtLocalAttribute()
    {
        if (!$this->completed_at) {
            return null;
        }

        // On s'assure que c'est une instance Carbon, puis on change la timezone
        return $this->completed_at->copy()->timezone('Africa/Nairobi'); 
    }
}
