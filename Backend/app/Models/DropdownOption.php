<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DropdownOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'value',
        'label',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Get options by type.
     */
    public static function getByType($type)
    {
        return self::where('type', $type)
                   ->where('is_active', true)
                   ->orderBy('sort_order')
                   ->orderBy('label')
                   ->get();
    }

    /**
     * Get all unique values for a specific type from existing assets.
     */
    public static function getExistingValues($type)
    {
        $column = match($type) {
            'building' => 'building',
            'floor' => 'floor',
            'department' => 'department',
            'room' => 'room',
            'condition' => 'condition',
            'status' => 'status',
            default => null
        };

        if (!$column) {
            return collect();
        }

        return ITAsset::select($column)
                      ->distinct()
                      ->whereNotNull($column)
                      ->where($column, '!=', '')
                      ->orderBy($column)
                      ->pluck($column);
    }

    /**
     * Get combined options (from dropdown_options table + existing values from assets).
     */
    public static function getCombinedOptions($type)
    {
        $dropdownOptions = self::getByType($type);
        $existingValues = self::getExistingValues($type);

        $options = collect();

        // Add dropdown options first
        foreach ($dropdownOptions as $option) {
            $options->push([
                'id' => $option->id,
                'value' => $option->value,
                'label' => $option->label ?: $option->value,
                'is_active' => $option->is_active,
                'is_predefined' => true
            ]);
        }

        // Add existing values that are not already in dropdown options
        // Only show existing values if they don't have a corresponding dropdown option
        $existingValues->each(function ($value) use ($options) {
            // Check if there's already a dropdown option with this value
            $hasDropdownOption = $options->contains(function ($option) use ($value) {
                return $option['value'] === $value && !str_starts_with($option['id'], 'existing_');
            });
            
            if (!$hasDropdownOption) {
                $options->push([
                    'id' => 'existing_' . md5($value), // Generate a unique ID for existing values
                    'value' => $value,
                    'label' => $value,
                    'is_active' => true,
                    'is_predefined' => false
                ]);
            }
        });

        return $options->sortBy('label')->values();
    }

    /**
     * Add a new option.
     */
    public static function addOption($type, $value, $label = null, $sortOrder = 0)
    {
        return self::firstOrCreate(
            [
                'type' => $type,
                'value' => $value
            ],
            [
                'label' => $label ?: $value,
                'sort_order' => $sortOrder,
                'is_active' => true
            ]
        );
    }

    /**
     * Toggle existing value status by creating or updating a dropdown option.
     */
    public static function toggleExistingValue($type, $value, $isActive)
    {
        $option = self::firstOrCreate(
            [
                'type' => $type,
                'value' => $value
            ],
            [
                'label' => $value,
                'sort_order' => 0,
                'is_active' => $isActive
            ]
        );
        
        $option->update(['is_active' => $isActive]);
        return $option;
    }
}
