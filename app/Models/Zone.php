<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    use SoftDeletes;

    protected $fillable = ['gps_zone_id', 'name','option', 'type','parent_id', 'color', 'vertices', 'role', 'active'];
    protected $casts    = ['active' => 'boolean'];

    public const OPTIONS = [
        'obligatoire' => 'Obligatoire',
        'optionnel'   => 'Optionnel',
    ];

    public const OPTION_ICONS = [
        'obligatoire' => '🚦',
        'optionnel'   => '📍',
    ];


    // ── Relations ─────────────────────────────────────────────────────────────

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Zone::class, 'parent_id')->orderBy('name');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isOptional(): bool
    {
        return $this->option === 'optionnel';
    }

    public function isObligatoire(): bool
    {
        return $this->option === 'obligatoire';
    }

    public function getOptionLabel(): string
    {
        return self::OPTIONS[$this->option] ?? $this->option;
    }

    /** "Ilanivato › Garage" */
    public function getFullPath(): string
    {
        return $this->parent
            ? $this->parent->getFullPath() . ' › ' . $this->name
            : $this->name;
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Retourne les vertices sous forme de tableau [{lat, lng}]
     */
    public function getVerticesArrayAttribute(): array
    {
        if (!$this->vertices) {
            return [];
        }
        $pairs = explode(',', $this->vertices);
        $result = [];
        for ($i = 0; $i + 1 < count($pairs); $i += 2) {
            $result[] = ['lat' => (float) $pairs[$i], 'lng' => (float) $pairs[$i + 1]];
        }
        return $result;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /** Zones racines uniquement (sans parent) */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
}