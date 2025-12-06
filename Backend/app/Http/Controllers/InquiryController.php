<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class InquiryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Inquiry::query();

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by client name or company
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $inquiries = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $inquiries
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'client_name' => 'required|string|max:255',
                'company' => 'required|string|max:255',
                'description' => 'required|string',
                'status' => 'nullable|in:open,closed'
            ]);

            $inquiry = Inquiry::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Inquiry created successfully',
                'data' => $inquiry
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
     * Display the specified resource.
     */
    public function show(Inquiry $inquiry): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $inquiry
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inquiry $inquiry): JsonResponse
    {
        try {
            $validated = $request->validate([
                'client_name' => 'sometimes|required|string|max:255',
                'company' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'status' => 'sometimes|required|in:open,closed'
            ]);

            $inquiry->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Inquiry updated successfully',
                'data' => $inquiry
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
     * Remove the specified resource from storage.
     */
    public function destroy(Inquiry $inquiry): JsonResponse
    {
        $inquiry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inquiry deleted successfully'
        ]);
    }

    /**
     * Update inquiry status to closed.
     */
    public function close(Inquiry $inquiry): JsonResponse
    {
        $inquiry->update(['status' => 'closed']);

        return response()->json([
            'success' => true,
            'message' => 'Inquiry closed successfully',
            'data' => $inquiry
        ]);
    }

    /**
     * Update inquiry status to open.
     */
    public function open(Inquiry $inquiry): JsonResponse
    {
        $inquiry->update(['status' => 'open']);

        return response()->json([
            'success' => true,
            'message' => 'Inquiry opened successfully',
            'data' => $inquiry
        ]);
    }

    /**
     * Get inquiry report with statistics.
     */
    public function report(Request $request): JsonResponse
    {
        $query = Inquiry::query();

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by client name or company
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $inquiries = $query->orderBy('created_at', 'desc')->get();

        // Get statistics
        $totalInquiries = Inquiry::count();
        $openInquiries = Inquiry::where('status', 'open')->count();
        $closedInquiries = Inquiry::where('status', 'closed')->count();

        return response()->json([
            'success' => true,
            'data' => $inquiries,
            'statistics' => [
                'total' => $totalInquiries,
                'open' => $openInquiries,
                'closed' => $closedInquiries
            ]
        ]);
    }
}
