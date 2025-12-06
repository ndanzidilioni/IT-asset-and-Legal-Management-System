<?php

namespace App\Http\Controllers;

use App\Models\ITAsset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Exports\ITAssetsExport;
use App\Exports\ITAssetTemplateExport;
use App\Imports\ITAssetsImport;
use Maatwebsite\Excel\Facades\Excel;

class ITAssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ITAsset::with('creator');

        // Apply filters
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('condition')) {
            $query->byCondition($request->condition);
        }

        if ($request->has('department')) {
            $query->byDepartment($request->department);
        }

        if ($request->has('building')) {
            $query->byBuilding($request->building);
        }


        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('brand')) {
            $query->byBrand($request->brand);
        }

        if ($request->has('assigned_to')) {
            $query->byAssignedTo($request->assigned_to);
        }

        if ($request->has('critical')) {
            $query->critical();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('asset_number', 'like', "%{$search}%")
                  ->orWhere('asset_description', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('assigned_to', 'like', "%{$search}%");
            });
        }

        $assets = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $assets
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'asset_number' => 'required|string|unique:it_assets,asset_number',
                'asset_description' => 'required|string|max:255',
                'building' => 'required|string|max:255',
                'floor' => 'required|string|max:255',
                'department' => 'required|string|max:255',
                'room' => 'required|string|max:255',
                'condition' => 'required|in:excellent,good,fair,poor,damaged',
                'status' => 'required|in:active,inactive,maintenance,disposed',
                'notes' => 'nullable|string',
                // ICT Asset Classification
                'asset_type' => 'nullable|string|max:255',
                'brand' => 'nullable|string|max:255',
                'model' => 'nullable|string|max:255',
                'serial_number' => 'nullable|string|max:255',
                // Technical Specifications
                'processor' => 'nullable|string|max:255',
                'memory' => 'nullable|string|max:255',
                'storage' => 'nullable|string|max:255',
                'operating_system' => 'nullable|string|max:255',
                'ip_address' => 'nullable|ip',
                'mac_address' => 'nullable|string|max:17',
                // Asset Lifecycle
                'deployment_date' => 'nullable|date',
                'last_maintenance_date' => 'nullable|date',
                'next_maintenance_date' => 'nullable|date',
                'assigned_to' => 'nullable|string|max:255',
                'location_details' => 'nullable|string|max:255',
                // Additional ICT Fields
                'network_zone' => 'nullable|string|max:255',
                'is_critical' => 'nullable|boolean',
                'backup_status' => 'nullable|string|max:255',
                'technical_notes' => 'nullable|string',
            ]);

            // Asset number is now required and provided by user

            $validated['created_by'] = Auth::id();
            $asset = ITAsset::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'IT Asset created successfully',
                'data' => $asset->load('creator')
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
    public function show(ITAsset $itAsset): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $itAsset->load('creator')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ITAsset $itAsset): JsonResponse
    {
        try {
            $validated = $request->validate([
                'asset_number' => 'sometimes|string|unique:it_assets,asset_number,' . $itAsset->id,
                'asset_description' => 'sometimes|required|string|max:255',
                'building' => 'sometimes|required|string|max:255',
                'floor' => 'sometimes|required|string|max:255',
                'department' => 'sometimes|required|string|max:255',
                'room' => 'sometimes|required|string|max:255',
                'condition' => 'sometimes|required|in:excellent,good,fair,poor,damaged',
                'status' => 'sometimes|required|in:active,inactive,maintenance,disposed',
                'notes' => 'nullable|string'
            ]);

            $itAsset->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'IT Asset updated successfully',
                'data' => $itAsset->load('creator')
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
    public function destroy(ITAsset $itAsset): JsonResponse
    {
        $itAsset->delete();

        return response()->json([
            'success' => true,
            'message' => 'IT Asset deleted successfully'
        ]);
    }


    /**
     * Get statistics for the dashboard.
     */
    public function statistics(): JsonResponse
    {
        try {
            $total = ITAsset::count();
            $active = ITAsset::where('status', 'active')->count();
            $maintenance = ITAsset::where('status', 'maintenance')->count();
            $disposed = ITAsset::where('status', 'disposed')->count();
            
            $conditions = ITAsset::selectRaw('`condition`, count(*) as count')
                               ->groupBy('condition')
                               ->get()
                               ->pluck('count', 'condition');

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'active' => $active,
                    'maintenance' => $maintenance,
                    'disposed' => $disposed,
                    'conditions' => $conditions
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Statistics error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export IT assets to CSV.
     */
    public function exportCsv(Request $request)
    {
        try {
            \Log::info('Starting CSV export');
            
            // Get assets with creator relationship
            $assets = ITAsset::with('creator')->get();
            \Log::info('Assets retrieved: ' . $assets->count());
            
            $filename = 'it_assets_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($assets) {
                $file = fopen('php://output', 'w');
                
                // CSV Headers (without Created At / Updated At)
                fputcsv($file, [
                    'Asset Number',
                    'Asset Description',
                    'Building',
                    'Floor',
                    'Department',
                    'Room',
                    'Condition',
                    'Status',
                    'Notes'
                ]);

                // CSV Data
                foreach ($assets as $asset) {
                    fputcsv($file, [
                        $asset->asset_number,
                        $asset->asset_description,
                        $asset->building,
                        $asset->floor,
                        $asset->department,
                        $asset->room,
                        ucfirst($asset->condition),
                        ucfirst($asset->status),
                        $asset->notes,
                    ]);
                }

                fclose($file);
            };

            return Response::stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            \Log::error('CSV Export failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export IT assets to Excel (CSV format for now).
     */
    public function exportExcel(Request $request)
    {
        try {
            \Log::info('Starting Excel export (CSV format)');
            
            // Get assets with creator relationship
            $assets = ITAsset::with('creator')->get();
            \Log::info('Assets retrieved for Excel: ' . $assets->count());
            
            $filename = 'it_assets_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($assets) {
                $file = fopen('php://output', 'w');
                
                // CSV Headers
                fputcsv($file, [
                    'ID',
                    'Asset Number',
                    'Asset Description',
                    'Building',
                    'Floor',
                    'Department',
                    'Room',
                    'Condition',
                    'Status',
                    'Assigned To',
                    'Notes',
                    'Created By',
                    'Created At',
                    'Updated At'
                ]);

                // CSV Data
                foreach ($assets as $asset) {
                    fputcsv($file, [
                        $asset->id,
                        $asset->asset_number,
                        $asset->asset_description,
                        $asset->building,
                        $asset->floor,
                        $asset->department,
                        $asset->room,
                        ucfirst($asset->condition),
                        ucfirst($asset->status),
                        $asset->assigned_to,
                        $asset->notes,
                        $asset->creator ? $asset->creator->fname . ' ' . $asset->creator->lname : 'Unknown',
                        $asset->created_at->format('Y-m-d H:i:s'),
                        $asset->updated_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            };

            return Response::stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            \Log::error('Excel Export failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import IT assets from CSV.
     */
    public function importCsv(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120'
        ]);

        $file = $request->file('file');

        $requiredHeaders = [
            'asset_number','asset_description','building','floor','department','room','condition','status'
        ];

        $imported = 0;
        $skipped = 0;
        $errors = [];

        try {
            // Read entire file content for encoding detection
            $fileContent = file_get_contents($file->getRealPath());
            if ($fileContent === false) {
                throw new \RuntimeException('Unable to read uploaded file');
            }

            // Detect and convert encoding to UTF-8
            $encoding = mb_detect_encoding($fileContent, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'], true);
            if ($encoding && $encoding !== 'UTF-8') {
                $fileContent = mb_convert_encoding($fileContent, 'UTF-8', $encoding);
            }

            // Strip UTF-8 BOM if present
            if (strncmp($fileContent, "\xEF\xBB\xBF", 3) === 0) {
                $fileContent = substr($fileContent, 3);
            }

            // Create a temporary stream from the converted content
            $handle = fopen('php://temp', 'r+');
            fwrite($handle, $fileContent);
            rewind($handle);

            // Detect delimiter from first non-empty line
            $probe = '';
            while (($probe = fgets($handle)) !== false && trim($probe) === '') {}
            if ($probe === '' || $probe === false) {
                throw new \RuntimeException('CSV is empty');
            }
            $candidates = [',',';','\t','|'];
            $chosen = ',';
            $maxHits = -1;
            foreach ($candidates as $cand) {
                $hits = substr_count($probe, $cand === "\t" ? "\t" : $cand);
                if ($hits > $maxHits) { $maxHits = $hits; $chosen = $cand === "\t" ? "\t" : $cand; }
            }
            // Use detected delimiter
            rewind($handle);
            // Read header row with detected delimiter
            $headerLine = fgets($handle);
            if ($headerLine === false) {
                throw new \RuntimeException('CSV is empty');
            }
            $headerRow = str_getcsv($headerLine, $chosen);
            if (!$headerRow) {
                throw new \RuntimeException('CSV is empty');
            }
            $headers = array_map(function($h){
                $h = trim($h);
                $h = strtolower($h);
                $h = str_replace([' ', '-'], '_', $h);
                return $h;
            }, $headerRow);

            // Header alias mapping to be more permissive with common CSVs
            $aliases = [
                'description' => 'asset_description',
                'asset_desc' => 'asset_description',
                'assetname' => 'asset_description',
                'asset_no' => 'asset_number',
                'assetid' => 'asset_number',
                'asset_id' => 'asset_number',
                'dept' => 'department',
                'room_no' => 'room',
            ];
            $headers = array_map(function($h) use ($aliases) {
                return $aliases[$h] ?? $h;
            }, $headers);

            foreach ($requiredHeaders as $req) {
                if (!in_array($req, $headers, true)) {
                    $seen = implode(', ', $headers);
                    throw new \RuntimeException("Missing required column: {$req}. Found headers: {$seen}");
                }
            }

            $index = array_flip($headers);

            $lineNo = 1; // already consumed header
            while (($line = fgets($handle)) !== false) {
                $lineNo++;
                $row = str_getcsv($line, $chosen);
                if (count(array_filter($row, fn($v)=>trim((string)$v) !== '')) === 0) {
                    $skipped++;
                    continue;
                }

                $data = [];
                foreach ($requiredHeaders as $key) {
                    $data[$key] = isset($row[$index[$key]]) ? trim((string)$row[$index[$key]]) : null;
                }
                $data['assigned_to'] = isset($index['assigned_to']) && isset($row[$index['assigned_to']]) ? trim((string)$row[$index['assigned_to']]) : null;
                $data['notes'] = isset($index['notes']) && isset($row[$index['notes']]) ? trim((string)$row[$index['notes']]) : null;

                $missing = [];
                foreach ($requiredHeaders as $key) {
                    if ($data[$key] === null || $data[$key] === '') {
                        $missing[] = $key;
                    }
                }
                if (!empty($missing)) {
                    $errors[] = ['row' => $row, 'errors' => ['Missing: '.implode(', ', $missing)]];
                    $skipped++;
                    continue;
                }

                // Normalize condition value with common variations
                $conditionRaw = strtolower(trim($data['condition']));
                $conditionMap = [
                    'new' => 'excellent',
                    'brand new' => 'excellent',
                    'very good' => 'excellent',
                    'very_good' => 'excellent',
                    'verygood' => 'excellent',
                    'v.good' => 'excellent',
                    'v. good' => 'excellent',
                    'v good' => 'excellent',
                    'vgood' => 'excellent',
                    'excellent' => 'excellent',
                    'good' => 'good',
                    'g' => 'good',
                    'fair' => 'fair',
                    'f' => 'fair',
                    'average' => 'fair',
                    'ok' => 'fair',
                    'okay' => 'fair',
                    'poor' => 'poor',
                    'p' => 'poor',
                    'bad' => 'poor',
                    'damaged' => 'damaged',
                    'broken' => 'damaged',
                    'd' => 'damaged',
                ];
                
                if (isset($conditionMap[$conditionRaw])) {
                    $conditionValue = $conditionMap[$conditionRaw];
                } else {
                    $errors[] = ['row' => $row, 'errors' => ["Invalid condition '{$data['condition']}' at line {$lineNo}. Accepted: excellent/very good/v.good, good, fair, poor, damaged (abbreviations: V.Good, G, F, P, D also work)"]];
                    $skipped++;
                    continue;
                }
                
                // Normalize status value
                $statusValue = strtolower(trim($data['status']));
                if (!in_array($statusValue, ['active','inactive','maintenance','disposed'], true)) {
                    $errors[] = ['row' => $row, 'errors' => ["Invalid status '{$data['status']}' at line {$lineNo}. Must be one of: active, inactive, maintenance, disposed"]];
                    $skipped++;
                    continue;
                }

                // Check for duplicate and update if exists
                $existingAsset = ITAsset::where('asset_number', $data['asset_number'])->first();
                
                if ($existingAsset) {
                    // Update existing asset
                    $existingAsset->update([
                        'asset_description' => $data['asset_description'],
                        'building' => $data['building'],
                        'floor' => $data['floor'],
                        'department' => $data['department'],
                        'room' => $data['room'],
                        'condition' => $conditionValue,
                        'status' => $statusValue,
                        'assigned_to' => $data['assigned_to'] ?: null,
                        'notes' => $data['notes'] ?: null,
                    ]);
                } else {
                    // Create new asset
                    ITAsset::create([
                        'asset_number' => $data['asset_number'],
                        'asset_description' => $data['asset_description'],
                        'building' => $data['building'],
                        'floor' => $data['floor'],
                        'department' => $data['department'],
                        'room' => $data['room'],
                        'condition' => $conditionValue,
                        'status' => $statusValue,
                        'assigned_to' => $data['assigned_to'] ?: null,
                        'notes' => $data['notes'] ?: null,
                        'created_by' => Auth::id(),
                    ]);
                }

                $imported++;
            }

            fclose($handle);

            return response()->json([
                'success' => true,
                'message' => 'Import completed',
                'data' => [
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'errors' => count($errors),
                    'error_details' => $errors,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import IT assets from Excel.
     */
    public function importExcel(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:2048'
        ]);

        try {
            $import = new ITAssetsImport();
            Excel::import($import, $request->file('file'));
            
            $results = $import->getResults();
            
            return response()->json([
                'success' => true,
                'message' => 'Import completed',
                'data' => [
                    'imported' => $results['success_count'],
                    'skipped' => $results['skip_count'],
                    'errors' => $results['error_count'],
                    'error_details' => $results['errors']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download sample import template.
     */
    public function downloadTemplate(Request $request)
    {
        $format = $request->query('format', 'csv'); // csv or excel
        
        // Sample data with acceptable value examples
        $sampleData = [
            [
                'asset_number' => 'LAP001',
                'asset_description' => 'Dell Laptop - Inspiron 15',
                'building' => 'Main Building',
                'floor' => '2nd Floor',
                'department' => 'IT Department',
                'room' => 'Room 201',
                'condition' => 'excellent',
                'status' => 'active',
                'assigned_to' => 'John Doe',
                'notes' => 'Condition: excellent/very good/good/fair/poor/damaged'
            ],
            [
                'asset_number' => 'DES002',
                'asset_description' => 'HP Desktop - EliteDesk 800',
                'building' => 'Annex Building',
                'floor' => '1st Floor',
                'department' => 'Finance',
                'room' => 'Room 105',
                'condition' => 'good',
                'status' => 'active',
                'assigned_to' => 'Jane Smith',
                'notes' => 'Status: active/inactive/maintenance/disposed'
            ]
        ];

        try {
            if ($format === 'excel') {
                return Excel::download(new ITAssetTemplateExport($sampleData), 'it_asset_import_template.xlsx');
            } else {
                return Excel::download(new ITAssetTemplateExport($sampleData), 'it_asset_import_template.csv');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template download failed: ' . $e->getMessage()
            ], 500);
        }
    }
}


