<?php

namespace App\Http\Controllers;

use App\Models\LocationHierarchy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LocationHierarchyController extends Controller
{
    /**
     * Get all buildings
     */
    public function getBuildings(): JsonResponse
    {
        try {
            $buildings = LocationHierarchy::getBuildings();
            
            return response()->json([
                'success' => true,
                'data' => $buildings->map(function ($building) {
                    return [
                        'value' => $building,
                        'label' => $building
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch buildings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get floors for a specific building
     */
    public function getFloors(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'building' => 'required|string|max:255'
            ]);

            $floors = LocationHierarchy::getFloorsForBuilding($validated['building']);
            
            return response()->json([
                'success' => true,
                'data' => $floors->map(function ($floor) {
                    return [
                        'value' => $floor,
                        'label' => $floor
                    ];
                })
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch floors: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get departments for a specific building and floor
     */
    public function getDepartments(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'building' => 'required|string|max:255',
                'floor' => 'required|string|max:255'
            ]);

            $departments = LocationHierarchy::getDepartmentsForLocation(
                $validated['building'], 
                $validated['floor']
            );
            
            return response()->json([
                'success' => true,
                'data' => $departments->map(function ($department) {
                    return [
                        'value' => $department,
                        'label' => $department
                    ];
                })
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch departments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rooms for a specific building, floor, and department
     */
    public function getRooms(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'building' => 'required|string|max:255',
                'floor' => 'required|string|max:255',
                'department' => 'required|string|max:255'
            ]);

            $rooms = LocationHierarchy::getRoomsForLocation(
                $validated['building'], 
                $validated['floor'], 
                $validated['department']
            );
            
            return response()->json([
                'success' => true,
                'data' => $rooms->map(function ($room) {
                    return [
                        'value' => $room,
                        'label' => $room
                    ];
                })
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rooms: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get hierarchical options with filters
     */
    public function getHierarchicalOptions(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:building,floor,department,room',
                'building' => 'nullable|string|max:255',
                'floor' => 'nullable|string|max:255',
                'department' => 'nullable|string|max:255'
            ]);

            $filters = array_filter([
                'building' => $validated['building'] ?? null,
                'floor' => $validated['floor'] ?? null,
                'department' => $validated['department'] ?? null
            ]);

            $options = LocationHierarchy::getHierarchicalOptions($validated['type'], $filters);
            
            return response()->json([
                'success' => true,
                'data' => $options->map(function ($option) {
                    return [
                        'value' => $option,
                        'label' => $option
                    ];
                })
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch options: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate a location combination
     */
    public function validateCombination(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'building' => 'required|string|max:255',
                'floor' => 'required|string|max:255',
                'department' => 'required|string|max:255',
                'room' => 'required|string|max:255'
            ]);

            $isValid = LocationHierarchy::validateCombination(
                $validated['building'],
                $validated['floor'],
                $validated['department'],
                $validated['room']
            );
            
            return response()->json([
                'success' => true,
                'data' => [
                    'is_valid' => $isValid,
                    'message' => $isValid ? 'Valid combination' : 'Invalid combination'
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate combination: ' . $e->getMessage()
            ], 500);
        }
    }
}
