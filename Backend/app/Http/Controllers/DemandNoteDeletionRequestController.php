<?php

namespace App\Http\Controllers;

use App\Models\DemandNoteDeletionRequest;
use App\Models\DemandNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DemandNoteDeletionRequestController extends Controller
{
    /**
     * Get all deletion requests
     */
    public function index(Request $request)
    {
        try {
            // Use leftJoin to handle orphaned records gracefully
            $query = DemandNoteDeletionRequest::query()
                ->leftJoin('demand_notes', 'demand_note_deletion_requests.demand_note_id', '=', 'demand_notes.id')
                ->select(
                    'demand_note_deletion_requests.*',
                    'demand_notes.demand_note_number',
                    'demand_notes.client_name as demand_note_client_name',
                    'demand_notes.amount_claimed'
                );

            // Filter by status
            if ($request->has('status')) {
                $query->where('demand_note_deletion_requests.status', $request->status);
            }

            $requests = $query->orderBy('demand_note_deletion_requests.requested_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $requests,
                'total' => $requests->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching demand note deletion requests: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch deletion requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending deletion requests (for approval)
     */
    public function pending()
    {
        try {
            $user = Auth::user();
            
            // Get pending requests not created by current user, with left join to handle orphaned records
            $requests = DemandNoteDeletionRequest::query()
                ->leftJoin('demand_notes', 'demand_note_deletion_requests.demand_note_id', '=', 'demand_notes.id')
                ->select(
                    'demand_note_deletion_requests.*',
                    'demand_notes.demand_note_number',
                    'demand_notes.client_name as demand_note_client_name',
                    'demand_notes.amount_claimed'
                )
                ->where('demand_note_deletion_requests.status', 'pending')
                ->where('demand_note_deletion_requests.requested_by', '!=', $user->id)
                ->orderBy('demand_note_deletion_requests.requested_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $requests,
                'total' => $requests->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching pending deletion requests: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending deletion requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a deletion request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'demand_note_id' => 'required|exists:demand_notes,id',
            'reason' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Check if there's already a pending request for this demand note
        $existingRequest = DemandNoteDeletionRequest::where('demand_note_id', $request->demand_note_id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'A deletion request for this demand note is already pending approval'
            ], 400);
        }

        $deletionRequest = DemandNoteDeletionRequest::create([
            'demand_note_id' => $request->demand_note_id,
            'requested_by' => $user->id,
            'requester_name' => $user->full_name,
            'reason' => $request->reason,
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Deletion request submitted successfully. Waiting for approval from another lawyer.',
            'data' => $deletionRequest
        ], 201);
    }

    /**
     * Approve a deletion request
     */
    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $deletionRequest = DemandNoteDeletionRequest::findOrFail($id);
        $user = Auth::user();

        // Check if user is trying to approve their own request
        if ($deletionRequest->requested_by === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot approve your own deletion request'
            ], 403);
        }

        // Check if already reviewed
        if ($deletionRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This request has already been reviewed'
            ], 400);
        }

        // Update request status
        $deletionRequest->update([
            'status' => 'approved',
            'reviewed_by' => $user->id,
            'reviewer_name' => $user->full_name,
            'review_comment' => $request->comment,
            'reviewed_at' => now()
        ]);

        // Delete the demand note
        $demandNote = DemandNote::find($deletionRequest->demand_note_id);
        if ($demandNote) {
            $demandNote->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Deletion request approved and demand note deleted successfully'
        ]);
    }

    /**
     * Reject a deletion request
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $deletionRequest = DemandNoteDeletionRequest::findOrFail($id);
        $user = Auth::user();

        // Check if user is trying to reject their own request
        if ($deletionRequest->requested_by === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot reject your own deletion request'
            ], 403);
        }

        // Check if already reviewed
        if ($deletionRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This request has already been reviewed'
            ], 400);
        }

        // Update request status
        $deletionRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $user->id,
            'reviewer_name' => $user->full_name,
            'review_comment' => $request->comment,
            'reviewed_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Deletion request rejected successfully'
        ]);
    }

    /**
     * Delete/Cancel a deletion request (only by requester)
     */
    public function destroy($id)
    {
        $deletionRequest = DemandNoteDeletionRequest::findOrFail($id);
        $user = Auth::user();

        // Only the requester can cancel their own pending request
        if ($deletionRequest->requested_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only cancel your own deletion requests'
            ], 403);
        }

        if ($deletionRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending requests can be cancelled'
            ], 400);
        }

        $deletionRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deletion request cancelled successfully'
        ]);
    }
}
