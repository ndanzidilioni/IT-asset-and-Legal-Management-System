<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ITAsset extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'it_assets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'asset_number',
        'asset_description',
        'building',
        'floor',
        'department',
        'room',
        'condition',
        'status',
        'notes',
        'created_by',
        // ICT Asset Classification
        'asset_type',
        'brand',
        'model',
        'serial_number',
        // Technical Specifications
        'processor',
        'memory',
        'storage',
        'operating_system',
        'ip_address',
        'mac_address',
        // Asset Lifecycle
        'deployment_date',
        'last_maintenance_date',
        'next_maintenance_date',
        'assigned_to',
        'location_details',
        // Additional ICT Fields
        'network_zone',
        'is_critical',
        'backup_status',
        'technical_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deployment_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'is_critical' => 'boolean',
    ];

    /**
     * Get the user who created this asset.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include active assets.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include assets by condition.
     */
    public function scopeByCondition($query, $condition)
    {
        return $query->where('condition', $condition);
    }

    /**
     * Scope a query to only include assets by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by department.
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', 'like', "%{$department}%");
    }

    /**
     * Scope a query to filter by building.
     */
    public function scopeByBuilding($query, $building)
    {
        return $query->where('building', 'like', "%{$building}%");
    }


    /**
     * Scope a query to filter by asset type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('asset_type', $type);
    }

    /**
     * Scope a query to filter by brand.
     */
    public function scopeByBrand($query, $brand)
    {
        return $query->where('brand', 'like', "%{$brand}%");
    }

    /**
     * Scope a query to filter by assigned user.
     */
    public function scopeByAssignedTo($query, $assignedTo)
    {
        return $query->where('assigned_to', 'like', "%{$assignedTo}%");
    }

    /**
     * Scope a query to filter critical assets.
     */
    public function scopeCritical($query)
    {
        return $query->where('is_critical', true);
    }


    /**
     * Scope a query to filter assets needing maintenance.
     */
    public function scopeMaintenanceDue($query, $days = 30)
    {
        return $query->where('next_maintenance_date', '<=', now()->addDays($days))
                    ->where('next_maintenance_date', '>', now());
    }

    /**
     * Generate a unique asset number.
     */
    public static function generateAssetNumber()
    {
        $prefix = 'IT';
        $year = date('Y');
        $month = date('m');
        
        $lastAsset = self::where('asset_number', 'like', "{$prefix}{$year}{$month}%")
                         ->orderBy('asset_number', 'desc')
                         ->first();
        
        if ($lastAsset) {
            $lastNumber = intval(substr($lastAsset->asset_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}


