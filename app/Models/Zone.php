<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    use SoftDeletes;

    protected $fillable = ['gps_zone_id', 'name', 'type', 'color', 'vertices', 'role', 'active'];
    protected $casts    = ['active' => 'boolean'];

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
}