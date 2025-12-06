<?php

namespace App\Imports;

use App\Models\ITAsset;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class ITAssetsImport implements ToCollection, WithHeadingRow
{
    private $errors = [];
    private $successCount = 0;
    private $skipCount = 0;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            try {
                // Skip empty rows
                if (empty($row['asset_number']) && empty($row['asset_description'])) {
                    $this->skipCount++;
                    continue;
                }

                // Validate required fields
                $validator = Validator::make($row->toArray(), [
                    'asset_number' => 'required|string|max:255|unique:it_assets,asset_number',
                    'asset_description' => 'required|string|max:255',
                    'building' => 'required|string|max:255',
                    'floor' => 'required|string|max:255',
                    'department' => 'required|string|max:255',
                    'room' => 'required|string|max:255',
                    'condition' => 'required|in:excellent,good,fair,poor,damaged',
                    'status' => 'required|in:active,inactive,maintenance,disposed',
                ]);

                if ($validator->fails()) {
                    $this->errors[] = [
                        'row' => $row,
                        'errors' => $validator->errors()->all()
                    ];
                    continue;
                }

                // Parse dates
                $purchaseDate = null;
                if (!empty($row['purchase_date'])) {
                    try {
                        $purchaseDate = Carbon::parse($row['purchase_date']);
                    } catch (\Exception $e) {
                        $this->errors[] = [
                            'row' => $row,
                            'errors' => ['Invalid purchase date format']
                        ];
                        continue;
                    }
                }

                $warrantyExpiry = null;
                if (!empty($row['warranty_expiry'])) {
                    try {
                        $warrantyExpiry = Carbon::parse($row['warranty_expiry']);
                    } catch (\Exception $e) {
                        $this->errors[] = [
                            'row' => $row,
                            'errors' => ['Invalid warranty expiry date format']
                        ];
                        continue;
                    }
                }

                // Create IT Asset
                ITAsset::create([
                    'asset_number' => $row['asset_number'],
                    'asset_description' => $row['asset_description'],
                    'building' => $row['building'],
                    'floor' => $row['floor'],
                    'department' => $row['department'],
                    'room' => $row['room'],
                    'condition' => $row['condition'],
                    'status' => $row['status'],
                    'assigned_to' => $row['assigned_to'] ?? null,
                    'notes' => $row['notes'] ?? null,
                    'created_by' => Auth::id(),
                ]);

                $this->successCount++;

            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $row,
                    'errors' => [$e->getMessage()]
                ];
            }
        }
    }

    /**
     * Get import results
     */
    public function getResults(): array
    {
        return [
            'success_count' => $this->successCount,
            'skip_count' => $this->skipCount,
            'error_count' => count($this->errors),
            'errors' => $this->errors
        ];
    }
}
