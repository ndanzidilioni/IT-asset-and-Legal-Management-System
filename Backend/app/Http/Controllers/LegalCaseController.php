<?php

namespace App\Http\Controllers;

use App\Models\LegalCase;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LegalCaseController extends Controller
{
    public function test()
    {
        $cases = LegalCase::orderBy('created_at', 'desc')->limit(5)->get();
        return response()->json([
            'message' => 'Legal Cases API is working',
            'total_cases' => LegalCase::count(),
            'sample_cases' => $cases,
            'database' => config('database.default'),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    public function index(Request $request)
    {
        $query = LegalCase::with(['client', 'assignedLawyer', 'creator']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('case_type')) {
            $query->where('case_type', $request->case_type);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('case_number', 'like', "%{$search}%")
                  ->orWhere('parties', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 15);
        $cases = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($cases);
    }

    public function show($id)
    {
        // Only load relationships where tables exist
        $relationships = ['client', 'assignedLawyer', 'creator', 'courtSchedules', 'documents'];
        
        // Check if optional tables exist before loading
        if (\Schema::hasTable('legal_invoices')) {
            $relationships[] = 'invoices';
        }
        if (\Schema::hasTable('legal_time_entries')) {
            $relationships[] = 'timeEntries';
        }
        
        $case = LegalCase::with($relationships)->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $case
        ]);
    }

    public function store(Request $request)
    {
        // Log immediately when method is called
        \Log::info('=== CASE CREATE ENDPOINT HIT ===', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => Auth::id(),
            'user' => Auth::user() ? Auth::user()->email : 'null',
            'has_token' => $request->bearerToken() ? 'yes' : 'no',
            'data_keys' => array_keys($request->all()),
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);
        
        try {
            
            $validator = Validator::make($request->all(), [
                'case_number' => 'nullable|string|max:100|unique:legal_cases,case_number',
                'case_year' => 'nullable|string|max:20',
                'parties' => 'nullable|string|max:500',
                'case_type' => 'required|in:Civil,Criminal,Corporate,Labor,Family,Constitutional,Administrative,Other',
                'amount_in_claim' => 'nullable|numeric|min:0',
                'filing_date' => 'nullable|date',
                'hearing_date' => 'nullable|date',
                'client_id' => 'nullable|exists:legal_clients,id',
                'status' => 'nullable|in:Active,Pending,Completed,On Hold,Closed,Dismissed',
                'priority' => 'nullable|in:Low,Medium,High,Urgent',
                'any_appeal' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                \Log::warning('Case creation validation failed', ['errors' => $validator->errors()]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            \Log::info('Creating case in database...', ['data_count' => count($request->all())]);
            
            // Test database connection first
            try {
                \DB::connection()->getPdo();
            } catch (\Exception $dbConnEx) {
                \Log::error('Database connection failed before case creation', [
                    'error' => $dbConnEx->getMessage()
                ]);
                return response()->json([
                    'error' => 'Database connection failed',
                    'message' => 'Unable to connect to database. Please check if MySQL is running.',
                    'details' => config('app.debug') ? $dbConnEx->getMessage() : null
                ], 503);
            }
            
            // Set a timeout for the database operation
            $startTime = microtime(true);
            try {
                $caseData = $request->all();
                
                if (empty($caseData['title'])) {
                    $caseData['title'] = $caseData['case_number']
                        ?? ($caseData['parties'] ?? 'Untitled Case');
                }

                $case = LegalCase::create(array_merge(
                    $caseData,
                    ['created_by' => Auth::id()]
                ));
                $elapsed = round((microtime(true) - $startTime) * 1000, 2);
                \Log::info('Case created successfully', [
                    'case_id' => $case->id, 
                    'case_number' => $case->case_number,
                    'elapsed_ms' => $elapsed
                ]);
            } catch (\Illuminate\Database\QueryException $dbEx) {
                $elapsed = round((microtime(true) - $startTime) * 1000, 2);
                \Log::error('Database query failed', [
                    'error' => $dbEx->getMessage(),
                    'elapsed_ms' => $elapsed,
                    'sql_state' => $dbEx->getCode()
                ]);
                throw $dbEx;
            }

            // Log audit trail asynchronously to prevent blocking
            try {
                AuditLog::log(
                    'create',
                    "Created case: {$case->case_number}",
                    ['case_id' => $case->id, 'case_data' => $case->toArray()]
                );
            } catch (\Exception $auditException) {
                // Don't fail the request if audit logging fails
                \Log::warning('Audit log failed for case creation', [
                    'case_id' => $case->id,
                    'error' => $auditException->getMessage()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Case created successfully',
                'case' => $case
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error creating case: ' . $e->getMessage());
            return response()->json([
                'error' => 'Database error',
                'message' => 'Failed to create case due to database error. Please check the database connection and table structure.',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Error creating case: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Failed to create case',
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $case = LegalCase::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'case_number' => 'nullable|string|max:100|unique:legal_cases,case_number,'.$id,
            'case_year' => 'nullable|string|max:20',
            'parties' => 'nullable|string|max:500',
            'case_type' => 'sometimes|required|in:Civil,Criminal,Corporate,Labor,Family,Constitutional,Administrative,Other',
            'amount_in_claim' => 'nullable|numeric|min:0',
            'filing_date' => 'nullable|date',
            'hearing_date' => 'nullable|date',
            'status' => 'nullable|in:Active,Pending,Completed,On Hold,Closed,Dismissed',
            'priority' => 'nullable|in:Low,Medium,High,Urgent',
            'any_appeal' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldData = $case->toArray();
        $case->update($request->all());

        AuditLog::log(
            'update',
            "Updated case: {$case->case_number}",
            ['case_id' => $case->id, 'changes' => ['old' => $oldData, 'new' => $case->toArray()]]
        );

        return response()->json([
            'message' => 'Case updated successfully',
            'case' => $case
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $case = LegalCase::findOrFail($id);
        $caseData = $case->toArray();
        
        $case->delete();

        $caseNumber = $caseData['case_number'] ?? 'Unknown';
        AuditLog::log(
            'delete',
            "Deleted case: {$caseNumber}",
            ['case_id' => $id, 'deleted_data' => $caseData]
        );

        return response()->json(['message' => 'Case deleted successfully']);
    }

    public function statistics()
    {
        $stats = [
            'total_cases' => LegalCase::count(),
            'active_cases' => LegalCase::where('status', 'Active')->count(),
            'pending_cases' => LegalCase::where('status', 'Pending')->count(),
            'completed_cases' => LegalCase::where('status', 'Completed')->count(),
            'cases_by_type' => LegalCase::selectRaw('case_type, COUNT(*) as count')
                ->groupBy('case_type')->get(),
            'cases_by_status' => LegalCase::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')->get(),
            'cases_by_priority' => LegalCase::selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')->get(),
        ];

        return response()->json($stats);
    }

    public function dashboard()
    {
        $stats = [
            'total_cases' => LegalCase::count(),
            'active_cases' => LegalCase::where('status', 'Active')->count(),
            'high_priority_cases' => LegalCase::whereIn('priority', ['High', 'Urgent'])->count(),
            'recent_cases' => LegalCase::with(['client', 'assignedLawyer'])
                ->orderBy('created_at', 'desc')->limit(5)->get(),
        ];

        return response()->json($stats);
    }
}
