<?php

namespace App\Http\Controllers;

use App\Models\DropdownOption;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DropdownOptionController extends Controller
{
    /**
     * Get dropdown options by type.
     */
    public function getOptions(Request $request, $type): JsonResponse
    {
        $validTypes = ['building', 'floor', 'department', 'room', 'condition', 'status'];
        
        if (!in_array($type, $validTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid option type'
            ], 400);
        }

        $options = DropdownOption::getCombinedOptions($type);

        return response()->json([
            'success' => true,
            'data' => $options
        ]);
    }

    /**
     * Get all dropdown options.
     */
    public function getAllOptions(): JsonResponse
    {
        $types = ['building', 'floor', 'department', 'room', 'condition', 'status'];
        $allOptions = [];

        foreach ($types as $type) {
            $allOptions[$type] = DropdownOption::getCombinedOptions($type);
        }

        return response()->json([
            'success' => true,
            'data' => $allOptions
        ]);
    }

    /**
     * Add a new dropdown option.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:category,building,floor,department,room,condition,status',
                'value' => 'required|string|max:255',
                'label' => 'nullable|string|max:255',
                'sort_order' => 'nullable|integer|min:0'
            ]);

            $option = DropdownOption::addOption(
                $validated['type'],
                $validated['value'],
                $validated['label'] ?? $validated['value'],
                $validated['sort_order'] ?? 0
            );

            return response()->json([
                'success' => true,
                'message' => 'Option added successfully',
                'data' => $option
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Update a dropdown option.
     */
    public function update(Request $request, DropdownOption $dropdownOption): JsonResponse
    {
        try {
            $validated = $request->validate([
                'value' => 'sometimes|required|string|max:255',
                'label' => 'nullable|string|max:255',
                'is_active' => 'sometimes|boolean',
                'sort_order' => 'nullable|integer|min:0'
            ]);

            $dropdownOption->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Option updated successfully',
                'data' => $dropdownOption
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Delete a dropdown option.
     */
    public function destroy(Request $request, DropdownOption $dropdownOption): JsonResponse
    {
        $user = $request->user();
        if(!$user || $user->role !== 'admin'){
            return response()->json(['message'=>'Unauthorized. Only admin users can delete dropdown options.'],403);
        }

        $dropdownOption->delete();

        return response()->json([
            'success' => true,
            'message' => 'Option deleted successfully'
        ]);
    }

    /**
     * Toggle option active status.
     */
    public function toggle(DropdownOption $dropdownOption): JsonResponse
    {
        $dropdownOption->update(['is_active' => !$dropdownOption->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Option status updated successfully',
            'data' => $dropdownOption
        ]);
    }

    /**
     * Toggle existing value status.
     */
    public function toggleExistingValue(Request $request): JsonResponse
    {
        $user = $request->user();
        if(!$user || $user->role !== 'admin'){
            return response()->json(['message'=>'Unauthorized. Only admin users can toggle existing value status.'],403);
        }
        try {
            $validated = $request->validate([
                'type' => 'required|in:building,floor,department,room,condition,status',
                'value' => 'required|string|max:255',
                'is_active' => 'required|boolean'
            ]);
            $option = DropdownOption::toggleExistingValue(
                $validated['type'],
                $validated['value'],
                $validated['is_active']
            );
            return response()->json([
                'success' => true,
                'message' => 'Existing value status updated successfully',
                'data' => $option
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }
}
