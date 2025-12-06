<?php

namespace App\Http\Controllers;

use App\Models\LegalInvoice;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LegalInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = LegalInvoice::with(['client', 'case', 'creator']);

        if ($request->has('status')) $query->where('status', $request->status);
        if ($request->has('client_id')) $query->where('client_id', $request->client_id);

        $perPage = $request->input('per_page', 15);
        return response()->json($query->orderBy('created_at', 'desc')->paginate($perPage));
    }

    public function show($id)
    {
        $invoice = LegalInvoice::with(['client', 'case', 'creator'])->findOrFail($id);
        return response()->json($invoice);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_name' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $invoiceData = $request->all();
        $invoiceData['balance_due'] = $invoiceData['total_amount'] - ($invoiceData['amount_paid'] ?? 0);
        $invoiceData['created_by'] = Auth::id();

        $invoice = LegalInvoice::create($invoiceData);

        AuditLog::log(
            'create',
            "Created invoice: {$invoice->invoice_number} for {$invoice->client_name}",
            ['invoice_id' => $invoice->id, 'invoice_data' => $invoice->toArray()]
        );

        return response()->json(['message' => 'Invoice created successfully', 'invoice' => $invoice], 201);
    }

    public function update(Request $request, $id)
    {
        $invoice = LegalInvoice::findOrFail($id);
        $oldData = $invoice->toArray();
        $invoice->update($request->all());

        AuditLog::log(
            'update',
            "Updated invoice: {$invoice->invoice_number}",
            ['invoice_id' => $invoice->id, 'changes' => ['old' => $oldData, 'new' => $invoice->toArray()]]
        );

        return response()->json(['message' => 'Invoice updated successfully', 'invoice' => $invoice]);
    }

    public function destroy(Request $request, $id)
    {
        $invoice = LegalInvoice::findOrFail($id);
        $invoiceData = $invoice->toArray();
        $invoice->delete();

        $invoiceNumber = $invoiceData['invoice_number'] ?? 'Unknown';
        AuditLog::log(
            'delete',
            "Deleted invoice: {$invoiceNumber}",
            ['invoice_id' => $id, 'deleted_data' => $invoiceData]
        );

        return response()->json(['message' => 'Invoice deleted successfully']);
    }

    public function getSummary()
    {
        $summary = [
            'total_invoiced' => LegalInvoice::sum('total_amount'),
            'total_paid' => LegalInvoice::sum('amount_paid'),
            'total_outstanding' => LegalInvoice::sum('balance_due'),
            'overdue_amount' => LegalInvoice::where('due_date', '<', now())
                ->where('balance_due', '>', 0)->sum('balance_due'),
            'invoice_count' => LegalInvoice::count(),
            'paid_count' => LegalInvoice::where('status', 'Paid')->count(),
            'overdue_count' => LegalInvoice::where('due_date', '<', now())
                ->where('balance_due', '>', 0)->count(),
        ];

        return response()->json($summary);
    }
}
