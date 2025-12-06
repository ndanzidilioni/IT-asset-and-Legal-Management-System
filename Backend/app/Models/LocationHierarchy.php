<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class LocationHierarchy extends Model
{
    protected $table = 'location_hierarchy';
    
    protected $fillable = [
        'building',
        'floor',
        'department',
        'room',
        'is_active'
    ];

    /**
     * Get all buildings
     */
    public static function getBuildings(): Collection
    {
        return ITAsset::select('building')
            ->distinct()
            ->whereNotNull('building')
            ->where('building', '!=', '')
            ->orderBy('building')
            ->pluck('building');
    }

    /**
     * Get floors for a specific building
     */
    public static function getFloorsForBuilding(string $building): Collection
    {
        return ITAsset::select('floor')
            ->distinct()
            ->where('building', $building)
            ->whereNotNull('floor')
            ->where('floor', '!=', '')
            ->orderBy('floor')
            ->pluck('floor');
    }

    /**
     * Get departments for a specific building and floor
     */
    public static function getDepartmentsForLocation(string $building, string $floor): Collection
    {
        return ITAsset::select('department')
            ->distinct()
            ->where('building', $building)
            ->where('floor', $floor)
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->orderBy('department')
            ->pluck('department');
    }

    /**
     * Get rooms for a specific building, floor, and department
     */
    public static function getRoomsForLocation(string $building, string $floor, string $department): Collection
    {
        return ITAsset::select('room')
            ->distinct()
            ->where('building', $building)
            ->where('floor', $floor)
            ->where('department', $department)
            ->whereNotNull('room')
            ->where('room', '!=', '')
            ->orderBy('room')
            ->pluck('room');
    }

    /**
     * Get hierarchical options for cascading dropdowns
     */
    public static function getHierarchicalOptions(string $type, array $filters = []): Collection
    {
        $query = ITAsset::query();

        // Apply filters based on parent selections
        if (isset($filters['building'])) {
            $query->where('building', $filters['building']);
        }
        if (isset($filters['floor'])) {
            $query->where('floor', $filters['floor']);
        }
        if (isset($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        // Get the appropriate column based on type
        $column = match($type) {
            'building' => 'building',
            'floor' => 'floor',
            'department' => 'department',
            'room' => 'room',
            default => null
        };

        if (!$column) {
            return collect();
        }

        return $query->select($column)
            ->distinct()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->orderBy($column)
            ->pluck($column);
    }

    /**
     * Get all possible combinations for validation
     */
    public static function getAllCombinations(): Collection
    {
        return ITAsset::select('building', 'floor', 'department', 'room')
            ->distinct()
            ->orderBy('building')
            ->orderBy('floor')
            ->orderBy('department')
            ->orderBy('room')
            ->get();
    }

    /**
     * Validate if a combination exists
     */
    public static function validateCombination(string $building, string $floor, string $department, string $room): bool
    {
        return ITAsset::where('building', $building)
            ->where('floor', $floor)
            ->where('department', $department)
            ->where('room', $room)
            ->exists();
    }
}
