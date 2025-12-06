<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Get schedules with filters
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Schedule::query();

        // Apply filters
        if ($request->has('developer_id')) {
            $query->where('developer_id', $request->developer_id);
        }

        if ($request->has('from')) {
            $query->where('end', '>=', Carbon::parse($request->from));
        }

        if ($request->has('to')) {
            $query->where('start', '<=', Carbon::parse($request->to));
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Role-based access control
        if ($user->role === 'admin') {
            $schedules = $query->with(['developer', 'task'])->get();
        } elseif ($user->role === 'developer') {
            $schedules = $query->where('developer_id', $user->id)
                             ->with(['developer', 'task'])
                             ->get();
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'schedules' => $schedules
        ]);
    }

    /**
     * Get specific schedule
     */
    public function show(Schedule $schedule): JsonResponse
    {
        $user = Auth::user();

        // Check authorization
        if ($user->role !== 'admin' && $schedule->developer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'schedule' => $schedule->load(['developer', 'task'])
        ]);
    }

    /**
     * Create new schedule
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Check authorization
        if (!in_array($user->role, ['admin', 'developer'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'developer_id' => 'required|exists:users,id',
            'task_id' => 'nullable|exists:tasks,id',
            'start' => 'required|date|after:now',
            'end' => 'required|date|after:start',
            'type' => 'nullable|string|in:availability,meeting,break,maintenance',
            'status' => 'nullable|string|in:free,booked,busy',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for conflicts
        $conflict = Schedule::where('developer_id', $request->developer_id)
            ->where(function ($query) use ($request) {
                $query->where('start', '<', $request->end)
                      ->where('end', '>', $request->start);
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'Schedule conflict detected'
            ], 409);
        }

        $schedule = Schedule::create([
            'developer_id' => $request->developer_id,
            'task_id' => $request->task_id,
            'start' => $request->start,
            'end' => $request->end,
            'type' => $request->type ?? 'availability',
            'status' => $request->status ?? 'free',
            'notes' => $request->notes
        ]);

        return response()->json([
            'message' => 'Schedule created successfully',
            'schedule' => $schedule->load(['developer', 'task'])
        ], 201);
    }

    /**
     * Update schedule
     */
    public function update(Request $request, Schedule $schedule): JsonResponse
    {
        $user = Auth::user();

        // Check authorization
        if ($user->role !== 'admin' && $schedule->developer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'developer_id' => 'sometimes|exists:users,id',
            'task_id' => 'nullable|exists:tasks,id',
            'start' => 'sometimes|date',
            'end' => 'sometimes|date|after:start',
            'type' => 'sometimes|string|in:availability,meeting,break,maintenance',
            'status' => 'sometimes|string|in:free,booked,busy',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for conflicts if time is being changed
        if ($request->has('start') || $request->has('end')) {
            $start = $request->get('start', $schedule->start);
            $end = $request->get('end', $schedule->end);
            $developerId = $request->get('developer_id', $schedule->developer_id);

            $conflict = Schedule::where('developer_id', $developerId)
                ->where('id', '!=', $schedule->id)
                ->where(function ($query) use ($start, $end) {
                    $query->where('start', '<', $end)
                          ->where('end', '>', $start);
                })
                ->exists();

            if ($conflict) {
                return response()->json([
                    'message' => 'Schedule conflict detected'
                ], 409);
            }
        }

        $schedule->update($request->only([
            'developer_id', 'task_id', 'start', 'end', 'type', 'status', 'notes'
        ]));

        return response()->json([
            'message' => 'Schedule updated successfully',
            'schedule' => $schedule->fresh()->load(['developer', 'task'])
        ]);
    }

    /**
     * Delete schedule
     */
    public function destroy(Schedule $schedule): JsonResponse
    {
        $user = Auth::user();

        // Check authorization
        if ($user->role !== 'admin' && $schedule->developer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $schedule->delete();

        return response()->json([
            'message' => 'Schedule deleted successfully'
        ]);
    }

    /**
     * Get free time slots for a developer
     */
    public function freeSlots(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'developer_id' => 'required|exists:users,id',
            'from' => 'required|date',
            'to' => 'required|date|after:from',
            'duration' => 'nullable|integer|min:1' // duration in hours
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $developer = User::findOrFail($request->developer_id);
        $freeSlots = $developer->getFreeSlots($request->from, $request->to);

        // Filter by duration if specified
        if ($request->has('duration')) {
            $duration = $request->duration;
            $freeSlots = array_filter($freeSlots, function ($slot) use ($duration) {
                $start = Carbon::parse($slot['start']);
                $end = Carbon::parse($slot['end']);
                return $start->diffInHours($end) >= $duration;
            });
        }

        return response()->json([
            'free_slots' => array_values($freeSlots)
        ]);
    }

    /**
     * Book a schedule slot
     */
    public function book(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'developer_id' => 'required|exists:users,id',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'task_id' => 'nullable|exists:tasks,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if slot is available
        $developer = User::findOrFail($request->developer_id);
        if (!$developer->isAvailableAt($request->start, $request->end)) {
            return response()->json([
                'message' => 'Time slot is not available'
            ], 409);
        }

        $schedule = Schedule::create([
            'developer_id' => $request->developer_id,
            'task_id' => $request->task_id,
            'start' => $request->start,
            'end' => $request->end,
            'type' => 'meeting',
            'status' => 'booked',
            'notes' => $request->notes
        ]);

        return response()->json([
            'message' => 'Schedule booked successfully',
            'schedule' => $schedule->load(['developer', 'task'])
        ], 201);
    }
}














