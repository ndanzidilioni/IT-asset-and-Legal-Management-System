<?php

namespace App\Http\Controllers;

use App\Models\CourtProceeding;
use App\Models\LegalCase;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CourtProceedingController extends Controller
{
    public function index(Request $request)
    {
        $query = CourtProceeding::with(['case', 'creator']);

        // Filter by case_id if provided
        if ($request->has('case_id')) {
            $query->where('case_id', $request->case_id);
        }

        // Filter by hearing date range
        if ($request->has('from_date')) {
            $query->where('hearing_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('hearing_date', '<=', $request->to_date);
        }

        $proceedings = $query->orderBy('hearing_date', 'desc')->get();
        return response()->json($proceedings);
    }

    public function show($id)
    {
        $proceeding = CourtProceeding::with(['case', 'creator'])->findOrFail($id);
        return response()->json($proceeding);
    }

    public function getByCaseId($caseId)
    {
        $proceedings = CourtProceeding::where('case_id', $caseId)
            ->with(['creator'])
            ->orderBy('hearing_date', 'desc')
            ->get();
        
        return response()->json($proceedings);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:legal_cases,id',
            'hearing_date' => 'required|date',
            'name_of_court' => 'nullable|string|max:255',
            'parties' => 'nullable|string|max:500',
            'case_number' => 'nullable|string|max:100',
            'court_judge' => 'nullable|string|max:255',
            'clerk_karani' => 'nullable|string|max:255',
            'advocate_for_opponent' => 'nullable|string|max:255',
            'advocate_for_moi' => 'nullable|string|max:255',
            'proceedings' => 'nullable|string',
            'court_order' => 'nullable|string',
            'next_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $proceeding = CourtProceeding::create(array_merge(
            $request->all(),
            ['created_by' => Auth::id()]
        ));

        // Update the case's next hearing date if provided
        if ($request->next_date) {
            $case = LegalCase::find($request->case_id);
            if ($case) {
                $case->hearing_date = $request->next_date;
                $case->save();
            }
        }

        AuditLog::log(
            'create',
            "Created court proceeding for case ID: {$proceeding->case_id}",
            ['proceeding_id' => $proceeding->id, 'proceeding_data' => $proceeding->toArray()]
        );

        return response()->json([
            'message' => 'Court proceeding created successfully',
            'proceeding' => $proceeding
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $proceeding = CourtProceeding::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'case_id' => 'sometimes|required|exists:legal_cases,id',
            'hearing_date' => 'sometimes|required|date',
            'name_of_court' => 'nullable|string|max:255',
            'parties' => 'nullable|string|max:500',
            'case_number' => 'nullable|string|max:100',
            'court_judge' => 'nullable|string|max:255',
            'clerk_karani' => 'nullable|string|max:255',
            'advocate_for_opponent' => 'nullable|string|max:255',
            'advocate_for_moi' => 'nullable|string|max:255',
            'proceedings' => 'nullable|string',
            'court_order' => 'nullable|string',
            'next_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldData = $proceeding->toArray();
        $proceeding->update($request->all());

        // Update the case's next hearing date if provided
        if ($request->has('next_date') && $request->next_date) {
            $case = LegalCase::find($proceeding->case_id);
            if ($case) {
                $case->hearing_date = $request->next_date;
                $case->save();
            }
        }

        AuditLog::log(
            'update',
            "Updated court proceeding ID: {$proceeding->id}",
            ['proceeding_id' => $proceeding->id, 'changes' => ['old' => $oldData, 'new' => $proceeding->toArray()]]
        );

        return response()->json([
            'message' => 'Court proceeding updated successfully',
            'proceeding' => $proceeding
        ]);
    }

    public function destroy($id)
    {
        $proceeding = CourtProceeding::findOrFail($id);
        $proceedingData = $proceeding->toArray();
        
        $proceeding->delete();

        AuditLog::log(
            'delete',
            "Deleted court proceeding ID: {$id}",
            ['proceeding_id' => $id, 'deleted_data' => $proceedingData]
        );

        return response()->json(['message' => 'Court proceeding deleted successfully']);
    }
}
