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
 
    protected $fillable = ['gps_marker_id', 'name', 'description', 'lat', 'lng', 'radius', 'icon', 'active'];
    protected $casts    = ['active' => 'boolean', 'lat' => 'float', 'lng' => 'float', 'radius' => 'float'];
}
 