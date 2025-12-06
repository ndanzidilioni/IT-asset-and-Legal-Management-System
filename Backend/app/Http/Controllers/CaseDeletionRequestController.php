<?php

namespace App\Http\Controllers;

use App\Models\CaseDeletionRequest;
use App\Models\LegalCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CaseDeletionRequestController extends Controller
{
    /**
     * Get all deletion requests
     */
    public function index(Request $request)
    {
        $query = CaseDeletionRequest::with('case');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    /**
     * Get pending deletion requests (for approval)
     */
    public function pending()
    {
        $user = Auth::user();
        
        // Get pending requests not created by current user
        $requests = CaseDeletionRequest::with('case')
            ->where('status', 'pending')
            ->where('requested_by', '!=', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    /**
     * Create a deletion request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:legal_cases,id',
            'reason' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Check if there's already a pending request for this case
        $existingRequest = CaseDeletionRequest::where('case_id', $request->case_id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'A deletion request for this case is already pending approval'
            ], 400);
        }

        $deletionRequest = CaseDeletionRequest::create([
            'case_id' => $request->case_id,
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

        $deletionRequest = CaseDeletionRequest::findOrFail($id);
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

        // Delete the case
        $case = LegalCase::find($deletionRequest->case_id);
        if ($case) {
            $case->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Deletion request approved and case deleted successfully'
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

        $deletionRequest = CaseDeletionRequest::findOrFail($id);
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
        $deletionRequest = CaseDeletionRequest::findOrFail($id);
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
