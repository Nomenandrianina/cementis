<?php
// ============================================================
// app/Models/Circuit.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Circuit extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'description', 'active'];
    protected $casts    = ['active' => 'boolean'];

    public function legs(): HasMany
    {
        return $this->hasMany(CircuitLeg::class)->orderBy('order');
    }

    public function rotations(): HasMany
    {
        return $this->hasMany(Rotation::class);
    }

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(
            Rvehicule::class,   // Le modèle lié
            'circuit_vehicles', // Le nom de la table pivot
            'circuit_id',       // La clé étrangère de ce modèle (Circuit) sur la table pivot
            'vehicule_id'      // La clé étrangère du modèle lié (RVehicule) sur la table pivot <--- C'est ici le correctif
        )->withPivot(['assigned_from', 'assigned_until'])
        ->withTimestamps();
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(RotationObjective::class);
    }

    public function currentObjective()
    {
        return $this->objectives()
                    ->where('effective_from', '<=', now())
                    ->where(fn($q) => $q->whereNull('effective_until')->orWhere('effective_until', '>=', now()))
                    ->latest('effective_from')
                    ->first();
    }

    public function completionRules(): HasMany
    {
        return $this->hasMany(CircuitCompletionRule::class)->orderBy('order');
    }
}