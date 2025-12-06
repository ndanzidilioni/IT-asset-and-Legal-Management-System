<?php

namespace App\Http\Controllers;

use App\Models\CourtSchedule;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CourtScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = CourtSchedule::with(['case', 'assignedLawyer', 'creator']);

        if ($request->has('status')) $query->where('status', $request->status);
        if ($request->has('event_type')) $query->where('event_type', $request->event_type);
        if ($request->has('from_date')) $query->where('event_date', '>=', $request->from_date);
        if ($request->has('to_date')) $query->where('event_date', '<=', $request->to_date);

        $perPage = $request->input('per_page', 15);
        return response()->json($query->orderBy('event_date', 'asc')->paginate($perPage));
    }

    public function show($id)
    {
        $schedule = CourtSchedule::with(['case', 'assignedLawyer', 'creator'])->findOrFail($id);
        return response()->json($schedule);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'event_type' => 'required|in:Hearing,Filing Deadline,Court Appearance,Mediation,Arbitration,Meeting,Other',
            'event_date' => 'required|date',
            'case_id' => 'nullable|exists:legal_cases,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $schedule = CourtSchedule::create(array_merge($request->all(), ['created_by' => Auth::id()]));

        AuditLog::log(
            'create',
            "Created court schedule: {$schedule->title}",
            ['schedule_id' => $schedule->id, 'schedule_data' => $schedule->toArray()]
        );

        return response()->json(['message' => 'Court schedule created successfully', 'schedule' => $schedule], 201);
    }

    public function update(Request $request, $id)
    {
        $schedule = CourtSchedule::findOrFail($id);
        $oldData = $schedule->toArray();
        $schedule->update($request->all());

        AuditLog::log(
            'update',
            "Updated court schedule: {$schedule->title}",
            ['schedule_id' => $schedule->id, 'changes' => ['old' => $oldData, 'new' => $schedule->toArray()]]
        );

        return response()->json(['message' => 'Court schedule updated successfully', 'schedule' => $schedule]);
    }

    public function destroy(Request $request, $id)
    {
        $schedule = CourtSchedule::findOrFail($id);
        $scheduleData = $schedule->toArray();
        $schedule->delete();

        $scheduleTitle = $scheduleData['title'] ?? 'Unknown';
        AuditLog::log(
            'delete',
            "Deleted court schedule: {$scheduleTitle}",
            ['schedule_id' => $id, 'deleted_data' => $scheduleData]
        );

        return response()->json(['message' => 'Court schedule deleted successfully']);
    }

    public function getHearings()
    {
        $hearings = CourtSchedule::with(['case', 'assignedLawyer'])
            ->where('event_type', 'Hearing')
            ->where('event_date', '>=', now())
            ->whereIn('status', ['Scheduled', 'Confirmed'])
            ->orderBy('event_date')
            ->get();

        return response()->json($hearings);
    }

    public function getDeadlines()
    {
        $deadlines = CourtSchedule::with(['case', 'assignedLawyer'])
            ->where('event_type', 'Filing Deadline')
            ->where('event_date', '>=', now())
            ->whereIn('status', ['Scheduled', 'Confirmed'])
            ->orderBy('event_date')
            ->get();

        return response()->json($deadlines);
    }

    public function getToday()
    {
        $today = CourtSchedule::with(['case', 'assignedLawyer'])
            ->whereDate('event_date', today())
            ->orderBy('event_time')
            ->get();

        return response()->json($today);
    }
}
