<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LegalDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = LegalDocument::with(['case', 'client', 'contract', 'uploader']);

        if ($request->has('case_id')) $query->where('case_id', $request->case_id);
        if ($request->has('client_id')) $query->where('client_id', $request->client_id);
        if ($request->has('contract_id')) $query->where('contract_id', $request->contract_id);
        if ($request->has('document_type')) $query->where('document_type', $request->document_type);

        $perPage = $request->input('per_page', 15);
        return response()->json($query->orderBy('created_at', 'desc')->paginate($perPage));
    }

    public function show($id)
    {
        $document = LegalDocument::with(['case', 'client', 'contract', 'uploader'])->findOrFail($id);
        return response()->json($document);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_name' => 'required|string|max:255',
            'document_type' => 'required|in:Contract,Court Filing,Evidence,Letter,Agreement,Report,Other',
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('legal_documents', $fileName, 'public');

        $document = LegalDocument::create([
            'document_name' => $request->document_name,
            'document_type' => $request->document_type,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'case_id' => $request->case_id,
            'client_id' => $request->client_id,
            'contract_id' => $request->contract_id,
            'status' => $request->input('status', 'Draft'),
            'uploaded_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Document uploaded successfully', 'document' => $document], 201);
    }

    public function destroy($id)
    {
        $document = LegalDocument::findOrFail($id);
        
        // Delete file from storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }

    public function download($id)
    {
        $document = LegalDocument::findOrFail($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }
}
