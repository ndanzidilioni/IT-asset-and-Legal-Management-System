<?php

namespace App\Http\Controllers;

use App\Models\LegalClient;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LegalClientController extends Controller
{
    /**
     * Get all clients with optional filters
     */
    public function index(Request $request)
    {
        $query = LegalClient::with(['assignedLawyer', 'creator']);

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('client_type')) {
            $query->where('client_type', $request->client_type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('client_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $clients = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($clients);
    }

    /**
     * Get single client by ID
     */
    public function show($id)
    {
        $client = LegalClient::with([
            'assignedLawyer',
            'creator',
            'cases',
            'contracts',
            'invoices',
            'documents'
        ])->findOrFail($id);

        return response()->json($client);
    }

    /**
     * Create new client
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'client_type' => 'required|in:Individual,Corporate,Government,NGO,Other',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'nullable|in:Active,Inactive,Prospective,Blacklisted',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $client = LegalClient::create(array_merge(
            $request->all(),
            ['created_by' => Auth::id()]
        ));

        // Audit log
        AuditLog::log(
            'create',
            "Created client: {$client->full_name} ({$client->client_number})",
            ['client_id' => $client->id, 'client_data' => $client->toArray()]
        );

        return response()->json([
            'message' => 'Client created successfully',
            'client' => $client
        ], 201);
    }

    /**
     * Update existing client
     */
    public function update(Request $request, $id)
    {
        $client = LegalClient::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|required|string|max:255',
            'client_type' => 'sometimes|required|in:Individual,Corporate,Government,NGO,Other',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'nullable|in:Active,Inactive,Prospective,Blacklisted',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldData = $client->toArray();
        $client->update($request->all());

        // Audit log
        AuditLog::log(
            'update',
            "Updated client: {$client->full_name} ({$client->client_number})",
            ['client_id' => $client->id, 'changes' => ['old' => $oldData, 'new' => $client->toArray()]]
        );

        return response()->json([
            'message' => 'Client updated successfully',
            'client' => $client
        ]);
    }

    /**
     * Delete client
     */
    public function destroy(Request $request, $id)
    {
        $client = LegalClient::findOrFail($id);
        $clientData = $client->toArray();
        $clientName = $client->full_name;
        
        $client->delete();

        // Audit log
        AuditLog::log(
            'delete',
            "Deleted client: {$clientName}",
            ['client_id' => $id, 'deleted_data' => $clientData]
        );

        return response()->json([
            'message' => 'Client deleted successfully'
        ]);
    }

    /**
     * Get client statistics
     */
    public function statistics()
    {
        $stats = [
            'total_clients' => LegalClient::count(),
            'active_clients' => LegalClient::where('status', 'Active')->count(),
            'corporate_clients' => LegalClient::where('client_type', 'Corporate')->count(),
            'individual_clients' => LegalClient::where('client_type', 'Individual')->count(),
            'prospective_clients' => LegalClient::where('status', 'Prospective')->count(),
            'clients_by_type' => LegalClient::selectRaw('client_type, COUNT(*) as count')
                ->groupBy('client_type')
                ->get(),
            'clients_by_status' => LegalClient::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get(),
        ];

        return response()->json($stats);
    }
}
