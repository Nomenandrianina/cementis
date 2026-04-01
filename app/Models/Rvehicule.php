<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
 
// ============================================================
// Vehicule de rotation (Rvehicle)
// ============================================================

class Rvehicule extends Model
{
    protected $table = 'r_vehicules';
    protected $fillable = ['imei', 'name', 'plate_number', 'model'];
 
    public function rotations(): HasMany { return $this->hasMany(Rotation::class); }
 
    public function circuits(): BelongsToMany
    {
        return $this->belongsToMany(Circuit::class, 'circuit_vehicles')
                    ->withPivot('assigned_from', 'assigned_until')
                    ->withTimestamps();
    }
}