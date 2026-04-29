<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
 
// ============================================================
// Checkpoint
// ============================================================
class Checkpoint extends Model
{
    use SoftDeletes;
 
    protected $fillable = ['gps_marker_id', 'name', 'type', 'description', 'lat', 'lng', 'radius', 'icon', 'active'];
    protected $casts    = ['active' => 'boolean', 'lat' => 'float', 'lng' => 'float', 'radius' => 'float'];

    public const TYPES = [
        'obligatoire' => 'Obligatoire',
        'optionnel'  => 'Optionnel',
    ];

    public const TYPE_ICONS = [
        'obligatoire' => '🚦', // Obligatoire
        'optionnel'  => '🏭', // Optionnel
    ];

    public function isOptional(): bool
    {
        return $this->type === 'optionnel';
    }

    public function isObligatoire(): bool
    {
        return $this->type === 'obligatoire';
    }

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
 