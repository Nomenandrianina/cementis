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
        'control' => 'Checkpoint de contrôle',
        'client'  => 'Dépôt client',
        'depot'   => 'Dépôt intermédiaire',
    ];

    public const TYPE_ICONS = [
        'control' => '🚦',
        'client'  => '🏭',
        'depot'   => '📦',
    ];

    public function isOptional(): bool
    {
        return in_array($this->type, ['client', 'depot']);
    }

    public function isControl(): bool
    {
        return $this->type === 'control';
    }

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
 