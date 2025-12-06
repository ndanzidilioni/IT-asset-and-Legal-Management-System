<?php

namespace App\Http\Controllers;

use App\Models\LegalContract;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LegalContractController extends Controller
{
    public function test()
    {
        $contracts = LegalContract::orderBy('created_at', 'desc')->limit(5)->get();
        return response()->json([
            'message' => 'Legal Contracts API is working',
            'total_contracts' => LegalContract::count(),
            'sample_contracts' => $contracts,
            'database' => config('database.default'),
            'table' => (new LegalContract)->getTable(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    public function index(Request $request)
    {
        $query = LegalContract::with(['client', 'manager', 'creator']);

        if ($request->has('status')) $query->where('status', $request->status);
        if ($request->has('contract_type')) $query->where('contract_type', $request->contract_type);
        if ($request->has('year')) {
            $year = $request->year;
            $query->where(function($q) use ($year) {
                $q->whereYear('start_date', $year)
                  ->orWhere(function($q2) use ($year) {
                      $q2->whereNull('start_date')
                         ->whereYear('created_at', $year);
                  });
            });
        }
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 15);
        return response()->json($query->orderBy('created_at', 'desc')->paginate($perPage));
    }

    public function show($id)
    {
        $contract = LegalContract::with(['client', 'manager', 'creator', 'documents'])->findOrFail($id);
        return response()->json($contract);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'contract_type' => 'nullable|string|max:100',
            'contract_value' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:Draft,Active,Under Review,Signed,Completed,Expired,Terminated',
            'description' => 'nullable|string',
            'currency' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Generate contract number if not provided
        $contractData = $request->all();
        if (empty($contractData['contract_number'])) {
            $year = date('Y');
            $count = LegalContract::whereYear('created_at', $year)->count() + 1;
            $contractData['contract_number'] = "CTR/{$year}/" . str_pad($count, 3, '0', STR_PAD_LEFT);
        }
        
        // Set default status if not provided
        if (empty($contractData['status'])) {
            $contractData['status'] = 'Draft';
        }
        
        // Set default currency if not provided
        if (empty($contractData['currency'])) {
            $contractData['currency'] = 'TSH';
        }

        $contract = LegalContract::create(array_merge($contractData, ['created_by' => Auth::id()]));

        AuditLog::log(
            'create',
            "Created contract: {$contract->title} ({$contract->contract_number})",
            ['contract_id' => $contract->id, 'contract_data' => $contract->toArray()]
        );

        return response()->json(['success' => true, 'message' => 'Contract created successfully', 'data' => $contract], 201);
    }

    public function update(Request $request, $id)
    {
        $contract = LegalContract::findOrFail($id);
        $oldData = $contract->toArray();
        $contract->update($request->all());

        AuditLog::log(
            'update',
            "Updated contract: {$contract->title} ({$contract->contract_number})",
            ['contract_id' => $contract->id, 'changes' => ['old' => $oldData, 'new' => $contract->toArray()]]
        );

        return response()->json(['message' => 'Contract updated successfully', 'contract' => $contract]);
    }

    public function destroy(Request $request, $id)
    {
        $contract = LegalContract::findOrFail($id);
        $contractData = $contract->toArray();
        $contract->delete();

        $contractTitle = $contractData['title'] ?? 'Unknown';
        AuditLog::log(
            'delete',
            "Deleted contract: {$contractTitle}",
            ['contract_id' => $id, 'deleted_data' => $contractData]
        );

        return response()->json(['message' => 'Contract deleted successfully']);
    }

    public function statistics(Request $request)
    {
        $year = $request->input('year', date('Y'));
        
        try {
            $stats = [
                'total_contracts' => LegalContract::count(),
                'active_contracts' => LegalContract::where('status', 'Active')->count(),
                'expiring_soon' => LegalContract::where('status', 'Active')
                    ->where('end_date', '<=', now()->addDays(30))
                    ->where('end_date', '>=', now())->count(),
                'total_value' => LegalContract::where('status', 'Active')->sum('contract_value'),
                'contracts_by_type' => LegalContract::selectRaw('contract_type, COUNT(*) as count')
                    ->whereNotNull('contract_type')
                    ->groupBy('contract_type')->get(),
                'contracts_by_status' => LegalContract::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')->get(),
                'contracts_by_year' => LegalContract::selectRaw('YEAR(start_date) as year, COUNT(*) as count')
                    ->whereNotNull('start_date')
                    ->groupBy('year')
                    ->orderBy('year', 'desc')
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'year' => $year
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching contract statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch contract statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getYears()
    {
        $years = LegalContract::selectRaw('DISTINCT YEAR(start_date) as year')
            ->whereNotNull('start_date')
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        return response()->json($years);
    }

    public function getDocuments($id)
    {
        $contract = LegalContract::findOrFail($id);
        $documents = $contract->documents()->with('uploader')->get();
        
        return response()->json([
            'success' => true,
            'data' => $documents,
            'contract' => [
                'id' => $contract->id,
                'title' => $contract->title,
                'contract_number' => $contract->contract_number
            ]
        ]);
    }
}
