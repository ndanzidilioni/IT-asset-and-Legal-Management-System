<?php

namespace App\Http\Controllers;

use App\Models\Case;
use App\Models\CaseActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CaseController extends Controller
{
    /**
     * Get all cases with filters
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Case::query();

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('case_type')) {
            $query->where('case_type', $request->case_type);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('assigned_lawyer_id')) {
            $query->where('assigned_lawyer_id', $request->assigned_lawyer_id);
        }

        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('case_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Role-based access control
        if ($user->role === 'lawyer') {
            $query->where('assigned_lawyer_id', $user->id);
        } elseif ($user->role === 'legal_assistant') {
            $query->where('assigned_legal_assistant_id', $user->id);
        } elseif ($user->role === 'client') {
            $query->where('client_id', $user->id);
        }

        $cases = $query->with(['client', 'assignedLawyer', 'assignedLegalAssistant'])
                      ->orderBy('created_at', 'desc')
                      ->paginate($request->get('per_page', 15));

        return response()->json([
            'cases' => $cases->items(),
            'pagination' => [
                'current_page' => $cases->currentPage(),
                'last_page' => $cases->lastPage(),
                'per_page' => $cases->perPage(),
                'total' => $cases->total(),
            ]
        ]);
    }

    /**
     * Get specific case
     */
    public function show(Case $case): JsonResponse
    {
        $user = Auth::user();

        // Check authorization
        if (!$this->canAccessCase($user, $case)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $case->load([
            'client',
            'assignedLawyer',
            'assignedLegalAssistant',
            'documents',
            'activities.user',
            'deadlines',
            'hearings',
            'expenses',
            'timeEntries'
        ]);

        return response()->json([
            'case' => $case
        ]);
    }

    /**
     * Create new case
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Check authorization
        if (!in_array($user->role, ['admin', 'lawyer'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'case_type' => 'required|string|in:civil,criminal,corporate,family,immigration,personal_injury,real_estate,employment,other',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'client_id' => 'required|exists:clients,id',
            'assigned_lawyer_id' => 'nullable|exists:users,id',
            'assigned_legal_assistant_id' => 'nullable|exists:users,id',
            'court_name' => 'nullable|string|max:255',
            'court_location' => 'nullable|string|max:255',
            'filing_date' => 'nullable|date',
            'hearing_date' => 'nullable|date',
            'deadline_date' => 'nullable|date',
            'estimated_completion_date' => 'nullable|date',
            'case_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_confidential' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $caseData = $request->only([
            'title', 'description', 'case_type', 'priority', 'client_id',
            'assigned_lawyer_id', 'assigned_legal_assistant_id', 'court_name',
            'court_location', 'filing_date', 'hearing_date', 'deadline_date',
            'estimated_completion_date', 'case_value', 'notes', 'is_confidential'
        ]);

        $caseData['case_number'] = Case::generateCaseNumber();
        $caseData['status'] = 'open';
        $caseData['is_active'] = true;

        $case = Case::create($caseData);

        // Log activity
        CaseActivity::create([
            'case_id' => $case->id,
            'user_id' => $user->id,
            'activity_type' => 'case_created',
            'title' => 'Case Created',
            'description' => "Case {$case->case_number} was created",
            'activity_date' => now(),
            'is_public' => true
        ]);

        return response()->json([
            'message' => 'Case created successfully',
            'case' => $case->load(['client', 'assignedLawyer', 'assignedLegalAssistant'])
        ], 201);
    }

    /**
     * Update case
     */
    public function update(Request $request, Case $case): JsonResponse
    {
        $user = Auth::user();

        // Check authorization
        if (!$this->canModifyCase($user, $case)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'case_type' => 'sometimes|string|in:civil,criminal,corporate,family,immigration,personal_injury,real_estate,employment,other',
            'priority' => 'sometimes|string|in:low,medium,high,urgent',
            'status' => 'sometimes|string|in:open,in_progress,pending,completed,closed,cancelled',
            'client_id' => 'sometimes|exists:clients,id',
            'assigned_lawyer_id' => 'nullable|exists:users,id',
            'assigned_legal_assistant_id' => 'nullable|exists:users,id',
            'court_name' => 'nullable|string|max:255',
            'court_location' => 'nullable|string|max:255',
            'filing_date' => 'nullable|date',
            'hearing_date' => 'nullable|date',
            'deadline_date' => 'nullable|date',
            'estimated_completion_date' => 'nullable|date',
            'actual_completion_date' => 'nullable|date',
            'case_value' => 'nullable|numeric|min:0',
            'outcome' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_confidential' => 'boolean',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldStatus = $case->status;
        $case->update($request->only([
            'title', 'description', 'case_type', 'priority', 'status', 'client_id',
            'assigned_lawyer_id', 'assigned_legal_assistant_id', 'court_name',
            'court_location', 'filing_date', 'hearing_date', 'deadline_date',
            'estimated_completion_date', 'actual_completion_date', 'case_value',
            'outcome', 'notes', 'is_confidential', 'is_active'
        ]));

        // Log status change activity
        if ($oldStatus !== $case->status) {
            CaseActivity::create([
                'case_id' => $case->id,
                'user_id' => $user->id,
                'activity_type' => 'status_changed',
                'title' => 'Status Changed',
                'description' => "Case status changed from {$oldStatus} to {$case->status}",
                'activity_date' => now(),
                'is_public' => true,
                'metadata' => [
                    'old_status' => $oldStatus,
                    'new_status' => $case->status
                ]
            ]);
        }

        return response()->json([
            'message' => 'Case updated successfully',
            'case' => $case->fresh()->load(['client', 'assignedLawyer', 'assignedLegalAssistant'])
        ]);
    }

    /**
     * Delete case
     */
    public function destroy(Case $case): JsonResponse
    {
        $user = Auth::user();

        // Check authorization
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $case->delete();

        return response()->json([
            'message' => 'Case deleted successfully'
        ]);
    }

    /**
     * Get case activities/timeline
     */
    public function activities(Case $case): JsonResponse
    {
        $user = Auth::user();

        if (!$this->canAccessCase($user, $case)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $activities = $case->activities()
                          ->with('user')
                          ->orderBy('activity_date', 'desc')
                          ->get();

        return response()->json([
            'activities' => $activities
        ]);
    }

    /**
     * Get case statistics
     */
    public function statistics(Case $case): JsonResponse
    {
        $user = Auth::user();

        if (!$this->canAccessCase($user, $case)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $statistics = [
            'total_billable_hours' => $case->getTotalBillableHours(),
            'total_expenses' => $case->getTotalExpenses(),
            'duration_days' => $case->getDurationInDays(),
            'is_overdue' => $case->isOverdue(),
            'has_upcoming_deadline' => $case->hasUpcomingDeadline(),
            'total_documents' => $case->documents()->count(),
            'total_activities' => $case->activities()->count(),
            'total_hearings' => $case->hearings()->count()
        ];

        return response()->json([
            'statistics' => $statistics
        ]);
    }

    /**
     * Get dashboard data
     */
    public function dashboard(): JsonResponse
    {
        $user = Auth::user();
        $query = Case::query();

        // Apply role-based filtering
        if ($user->role === 'lawyer') {
            $query->where('assigned_lawyer_id', $user->id);
        } elseif ($user->role === 'legal_assistant') {
            $query->where('assigned_legal_assistant_id', $user->id);
        } elseif ($user->role === 'client') {
            $query->where('client_id', $user->id);
        }

        $dashboard = [
            'total_cases' => $query->count(),
            'active_cases' => $query->where('is_active', true)->count(),
            'overdue_cases' => $query->overdue()->count(),
            'upcoming_deadlines' => $query->upcomingDeadlines()->count(),
            'cases_by_status' => $query->selectRaw('status, count(*) as count')
                                     ->groupBy('status')
                                     ->pluck('count', 'status'),
            'cases_by_priority' => $query->selectRaw('priority, count(*) as count')
                                       ->groupBy('priority')
                                       ->pluck('count', 'priority'),
            'recent_activities' => CaseActivity::with(['case', 'user'])
                                            ->whereHas('case', function ($q) use ($user) {
                                                if ($user->role === 'lawyer') {
                                                    $q->where('assigned_lawyer_id', $user->id);
                                                } elseif ($user->role === 'legal_assistant') {
                                                    $q->where('assigned_legal_assistant_id', $user->id);
                                                } elseif ($user->role === 'client') {
                                                    $q->where('client_id', $user->id);
                                                }
                                            })
                                            ->orderBy('activity_date', 'desc')
                                            ->limit(10)
                                            ->get()
        ];

        return response()->json([
            'dashboard' => $dashboard
        ]);
    }

    /**
     * Check if user can access case
     */
    private function canAccessCase($user, $case): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'lawyer' && $case->assigned_lawyer_id === $user->id) {
            return true;
        }

        if ($user->role === 'legal_assistant' && $case->assigned_legal_assistant_id === $user->id) {
            return true;
        }

        if ($user->role === 'client' && $case->client_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can modify case
     */
    private function canModifyCase($user, $case): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'lawyer' && $case->assigned_lawyer_id === $user->id) {
            return true;
        }

        if ($user->role === 'legal_assistant' && $case->assigned_legal_assistant_id === $user->id) {
            return true;
        }

        return false;
    }
}
