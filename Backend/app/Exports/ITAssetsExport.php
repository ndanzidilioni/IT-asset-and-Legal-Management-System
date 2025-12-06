<?php

namespace App\Exports;

use App\Models\ITAsset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ITAssetsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ITAsset::with('creator')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
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
        ];
    }

    /**
     * @param ITAsset $asset
     */
    public function map($asset): array
    {
        return [
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
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
