<?php

namespace App\Http\Controllers;

use App\Models\LegalTimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LegalTimeEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = LegalTimeEntry::with(['case', 'client', 'lawyer']);

        if ($request->has('case_id')) $query->where('case_id', $request->case_id);
        if ($request->has('lawyer_id')) $query->where('lawyer_id', $request->lawyer_id);
        if ($request->has('billed')) $query->where('billed', $request->billed);

        $perPage = $request->input('per_page', 15);
        return response()->json($query->orderBy('entry_date', 'desc')->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entry_date' => 'required|date',
            'hours' => 'required|numeric|min:0',
            'description' => 'required|string',
            'hourly_rate' => 'required|numeric|min:0',
            'lawyer_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $timeEntry = LegalTimeEntry::create($request->all());

        return response()->json(['message' => 'Time entry created successfully', 'time_entry' => $timeEntry], 201);
    }

    public function update(Request $request, $id)
    {
        $timeEntry = LegalTimeEntry::findOrFail($id);
        $timeEntry->update($request->all());

        return response()->json(['message' => 'Time entry updated successfully', 'time_entry' => $timeEntry]);
    }

    public function destroy($id)
    {
        $timeEntry = LegalTimeEntry::findOrFail($id);
        $timeEntry->delete();

        return response()->json(['message' => 'Time entry deleted successfully']);
    }
}
