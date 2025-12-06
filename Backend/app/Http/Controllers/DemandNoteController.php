<?php

namespace App\Http\Controllers;

use App\Models\DemandNote;
use App\Models\DemandNotePayment;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DemandNoteController extends Controller
{
    /**
     * Get all demand notes with filtering and pagination
     */
    public function index(Request $request): JsonResponse
    {
        // Temporarily disable auth check for testing
        // $user = $request->user();
        
        // if (!$user || !in_array($user->role, ['admin', 'lawyer'])) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $query = DemandNote::with(['creator:id,username,fname,lname', 'payments']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('current_status', $request->status);
        }

        // Filter by client name
        if ($request->has('client_name') && $request->client_name) {
            $query->where('client_name', 'like', '%' . $request->client_name . '%');
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('due_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('due_date', '<=', $request->end_date);
        }

        // Filter by nature of claim
        if ($request->has('nature_of_claim') && $request->nature_of_claim) {
            $query->where('nature_of_claim', $request->nature_of_claim);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('demand_note_number', 'like', '%' . $request->search . '%')
                  ->orWhere('client_name', 'like', '%' . $request->search . '%')
                  ->orWhere('claim_reference', 'like', '%' . $request->search . '%');
            });
        }

        // Order by
        $query->orderBy('created_at', 'desc');

        // Paginate
        $perPage = $request->get('per_page', 20);
        $demandNotes = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $demandNotes->items(),
            'pagination' => [
                'total' => $demandNotes->total(),
                'per_page' => $demandNotes->perPage(),
                'current_page' => $demandNotes->currentPage(),
                'last_page' => $demandNotes->lastPage(),
            ]
        ]);
    }

    /**
     * Get single demand note
     */
    public function show(DemandNote $demandNote): JsonResponse
    {
        // Temporarily disable auth check for testing
        // $user = request()->user();
        
        // if (!$user || !in_array($user->role, ['admin', 'lawyer'])) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $demandNote->load(['creator:id,username,fname,lname', 'payments.recorder:id,username,fname,lname']);

        // Temporarily disable logging for testing
        // AuditLog::log('view_demand_note', "Viewed demand note: {$demandNote->demand_note_number}");

        return response()->json([
            'success' => true,
            'data' => $demandNote
        ]);
    }

    /**
     * Create new demand note
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user || !in_array($user->role, ['admin', 'lawyer'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_number' => 'nullable|string|max:255',
            'claim_reference' => 'nullable|string|max:255',
            'amount_claimed' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'nature_of_claim' => 'required|in:Works,Supply of Goods,Services,Consultancy,Other',
            'agency_of_claim' => 'nullable|string|max:255',
            'notice_to_institute_suit' => 'nullable|string',
            'time_given_to_settle' => 'nullable|string|max:255',
            'settlement_action_taken' => 'nullable|string',
            'remarks' => 'nullable|string',
            'late_fee' => 'nullable|numeric|min:0',
        ]);

        $validated['created_by'] = $user->id;
        $validated['current_status'] = 'pending';

        $demandNote = DemandNote::create($validated);

        // Log creation
        AuditLog::log(
            'create_demand_note',
            "Created demand note: {$demandNote->demand_note_number} for {$demandNote->client_name}",
            ['demand_note_id' => $demandNote->id, 'amount' => $demandNote->amount_claimed]
        );

        return response()->json([
            'success' => true,
            'message' => 'Demand note created successfully',
            'data' => $demandNote
        ], 201);
    }

    /**
     * Update demand note
     */
    public function update(Request $request, DemandNote $demandNote): JsonResponse
    {
        $user = $request->user();
        
        if (!$user || !in_array($user->role, ['admin', 'lawyer'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'client_name' => 'sometimes|required|string|max:255',
            'client_number' => 'sometimes|nullable|string|max:255',
            'claim_reference' => 'sometimes|nullable|string|max:255',
            'amount_claimed' => 'sometimes|required|numeric|min:0',
            'due_date' => 'sometimes|required|date',
            'nature_of_claim' => 'sometimes|required|in:Works,Supply of Goods,Services,Consultancy,Other',
            'agency_of_claim' => 'sometimes|nullable|string|max:255',
            'notice_to_institute_suit' => 'sometimes|nullable|string',
            'time_given_to_settle' => 'sometimes|nullable|string|max:255',
            'settlement_action_taken' => 'sometimes|nullable|string',
            'current_status' => 'sometimes|required|in:pending,paid,partially_paid,overdue,cancelled',
            'remarks' => 'sometimes|nullable|string',
            'late_fee' => 'sometimes|nullable|numeric|min:0',
        ]);

        $demandNote->update($validated);

        // Log update
        AuditLog::log(
            'update_demand_note',
            "Updated demand note: {$demandNote->demand_note_number}",
            ['demand_note_id' => $demandNote->id]
        );

        return response()->json([
            'success' => true,
            'message' => 'Demand note updated successfully',
            'data' => $demandNote
        ]);
    }

    /**
     * Delete demand note
     */
    public function destroy(DemandNote $demandNote): JsonResponse
    {
        $user = request()->user();
        
        if (!$user || !in_array($user->role, ['admin', 'lawyer'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $demandNoteNumber = $demandNote->demand_note_number;
        $demandNote->delete();

        // Log deletion
        AuditLog::log(
            'delete_demand_note',
            "Deleted demand note: {$demandNoteNumber}"
        );

        return response()->json([
            'success' => true,
            'message' => 'Demand note deleted successfully'
        ]);
    }

    /**
     * Add payment to demand note
     */
    public function addPayment(Request $request, DemandNote $demandNote): JsonResponse
    {
        $user = $request->user();
        
        if (!$user || !in_array($user->role, ['admin', 'lawyer'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string|max:100',
            'receipt_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['demand_note_id'] = $demandNote->id;
        $validated['recorded_by'] = $user->id;

        $payment = DemandNotePayment::create($validated);

        // Log payment
        AuditLog::log(
            'add_payment_demand_note',
            "Added payment of {$payment->payment_amount} to demand note: {$demandNote->demand_note_number}",
            ['demand_note_id' => $demandNote->id, 'payment_amount' => $payment->payment_amount]
        );

        $demandNote->load('payments');

        return response()->json([
            'success' => true,
            'message' => 'Payment added successfully',
            'data' => $demandNote
        ]);
    }

    /**
     * Get statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        // Temporarily disable auth check for testing
        // $user = $request->user();
        
        // if (!$user || !in_array($user->role, ['admin', 'lawyer'])) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $stats = [
            'total_demand_notes' => DemandNote::count(),
            'pending' => DemandNote::where('current_status', 'pending')->count(),
            'paid' => DemandNote::where('current_status', 'paid')->count(),
            'overdue' => DemandNote::where('current_status', 'overdue')->count(),
            'partially_paid' => DemandNote::where('current_status', 'partially_paid')->count(),
            'total_claimed' => DemandNote::sum('amount_claimed'),
            'total_collected' => DemandNote::sum('amount_paid'),
            'total_outstanding' => DemandNote::sum('balance_due'),
            'by_nature' => DemandNote::select('nature_of_claim', DB::raw('COUNT(*) as count'))
                ->groupBy('nature_of_claim')
                ->get(),
            'recent_notes' => DemandNote::with('creator:id,username,fname,lname')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Export to CSV
     */
    public function exportCsv(Request $request)
    {
        $user = $request->user();
        
        if (!$user || !in_array($user->role, ['admin', 'lawyer'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = DemandNote::with('creator:id,username');

        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('current_status', $request->status);
        }

        if ($request->has('start_date') && $request->start_date) {
            $query->where('due_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('due_date', '<=', $request->end_date);
        }

        $demandNotes = $query->orderBy('created_at', 'desc')->get();

        $filename = 'demand_notes_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($demandNotes) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Demand Note Number',
                'Client Name',
                'Client Number',
                'Claim Reference',
                'Amount Claimed',
                'Amount Paid',
                'Balance Due',
                'Due Date',
                'Nature of Claim',
                'Status',
                'Issued Date',
                'Created By'
            ]);
            
            foreach ($demandNotes as $note) {
                fputcsv($file, [
                    $note->demand_note_number,
                    $note->client_name,
                    $note->client_number,
                    $note->claim_reference,
                    $note->amount_claimed,
                    $note->amount_paid,
                    $note->balance_due,
                    $note->due_date->format('Y-m-d'),
                    $note->nature_of_claim,
                    $note->current_status,
                    $note->issued_date ? $note->issued_date->format('Y-m-d') : '',
                    $note->creator ? $note->creator->username : ''
                ]);
            }
            
            fclose($file);
        };

        // Log export
        AuditLog::log('export_demand_notes', 'Exported demand notes to CSV');

        return response()->stream($callback, 200, $headers);
    }
}
