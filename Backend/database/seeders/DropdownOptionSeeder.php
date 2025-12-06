<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DropdownOption;

class DropdownOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = [
            // Floor options
            'floor' => [
                ['value' => 'Ground Floor', 'label' => 'Ground Floor', 'sort_order' => 1],
                ['value' => '1st Floor', 'label' => '1st Floor', 'sort_order' => 2],
                ['value' => '2nd Floor', 'label' => '2nd Floor', 'sort_order' => 3],
                ['value' => '3rd Floor', 'label' => '3rd Floor', 'sort_order' => 4],
                ['value' => '4th Floor', 'label' => '4th Floor', 'sort_order' => 5],
                ['value' => '5th Floor', 'label' => '5th Floor', 'sort_order' => 6],
                ['value' => 'Basement', 'label' => 'Basement', 'sort_order' => 7],
                ['value' => 'Mezzanine', 'label' => 'Mezzanine', 'sort_order' => 8],
                ['value' => 'Upper Level', 'label' => 'Upper Level', 'sort_order' => 9],
            ],

            // Department options
            'department' => [
                ['value' => 'IT Department', 'label' => 'IT Department', 'sort_order' => 1],
                ['value' => 'HR Department', 'label' => 'HR Department', 'sort_order' => 2],
                ['value' => 'Finance Department', 'label' => 'Finance Department', 'sort_order' => 3],
                ['value' => 'Marketing Department', 'label' => 'Marketing Department', 'sort_order' => 4],
                ['value' => 'Operations Department', 'label' => 'Operations Department', 'sort_order' => 5],
                ['value' => 'Sales Department', 'label' => 'Sales Department', 'sort_order' => 6],
                ['value' => 'Customer Service', 'label' => 'Customer Service', 'sort_order' => 7],
                ['value' => 'Research & Development', 'label' => 'Research & Development', 'sort_order' => 8],
                ['value' => 'Quality Assurance', 'label' => 'Quality Assurance', 'sort_order' => 9],
                ['value' => 'Legal Department', 'label' => 'Legal Department', 'sort_order' => 10],
                ['value' => 'Administration', 'label' => 'Administration', 'sort_order' => 11],
            ],

            // Room options
            'room' => [
                ['value' => 'Room 101', 'label' => 'Room 101', 'sort_order' => 1],
                ['value' => 'Room 102', 'label' => 'Room 102', 'sort_order' => 2],
                ['value' => 'Room 201', 'label' => 'Room 201', 'sort_order' => 3],
                ['value' => 'Room 202', 'label' => 'Room 202', 'sort_order' => 4],
                ['value' => 'Room 301', 'label' => 'Room 301', 'sort_order' => 5],
                ['value' => 'Room 302', 'label' => 'Room 302', 'sort_order' => 6],
                ['value' => 'Conference Room A', 'label' => 'Conference Room A', 'sort_order' => 7],
                ['value' => 'Conference Room B', 'label' => 'Conference Room B', 'sort_order' => 8],
                ['value' => 'Meeting Room 1', 'label' => 'Meeting Room 1', 'sort_order' => 9],
                ['value' => 'Meeting Room 2', 'label' => 'Meeting Room 2', 'sort_order' => 10],
                ['value' => 'Server Room', 'label' => 'Server Room', 'sort_order' => 11],
                ['value' => 'Storage Room', 'label' => 'Storage Room', 'sort_order' => 12],
                ['value' => 'Lab 1', 'label' => 'Lab 1', 'sort_order' => 13],
                ['value' => 'Lab 2', 'label' => 'Lab 2', 'sort_order' => 14],
                ['value' => 'Office 1', 'label' => 'Office 1', 'sort_order' => 15],
                ['value' => 'Office 2', 'label' => 'Office 2', 'sort_order' => 16],
            ],

            // Condition options
            'condition' => [
                ['value' => 'excellent', 'label' => 'Excellent', 'sort_order' => 1],
                ['value' => 'good', 'label' => 'Good', 'sort_order' => 2],
                ['value' => 'fair', 'label' => 'Fair', 'sort_order' => 3],
                ['value' => 'poor', 'label' => 'Poor', 'sort_order' => 4],
                ['value' => 'damaged', 'label' => 'Damaged', 'sort_order' => 5],
            ],

            // Status options
            'status' => [
                ['value' => 'active', 'label' => 'Active', 'sort_order' => 1],
                ['value' => 'inactive', 'label' => 'Inactive', 'sort_order' => 2],
                ['value' => 'maintenance', 'label' => 'Maintenance', 'sort_order' => 3],
                ['value' => 'disposed', 'label' => 'Disposed', 'sort_order' => 4],
            ],


            // Asset Type options
            'asset_type' => [
                // Compute types
                ['value' => 'laptop', 'label' => 'Laptop', 'sort_order' => 1],
                ['value' => 'desktop', 'label' => 'Desktop', 'sort_order' => 2],
                ['value' => 'server', 'label' => 'Server', 'sort_order' => 3],
                ['value' => 'workstation', 'label' => 'Workstation', 'sort_order' => 4],
                ['value' => 'tablet', 'label' => 'Tablet', 'sort_order' => 5],
                
                // Printer types
                ['value' => 'laser_printer', 'label' => 'Laser Printer', 'sort_order' => 6],
                ['value' => 'inkjet_printer', 'label' => 'Inkjet Printer', 'sort_order' => 7],
                ['value' => 'multifunction_printer', 'label' => 'Multifunction Printer', 'sort_order' => 8],
                ['value' => 'dot_matrix_printer', 'label' => 'Dot Matrix Printer', 'sort_order' => 9],
                
                // Scanner types
                ['value' => 'flatbed_scanner', 'label' => 'Flatbed Scanner', 'sort_order' => 10],
                ['value' => 'document_scanner', 'label' => 'Document Scanner', 'sort_order' => 11],
                ['value' => 'handheld_scanner', 'label' => 'Handheld Scanner', 'sort_order' => 12],
                ['value' => 'barcode_scanner', 'label' => 'Barcode Scanner', 'sort_order' => 13],
                
                // Switch types
                ['value' => 'managed_switch', 'label' => 'Managed Switch', 'sort_order' => 14],
                ['value' => 'unmanaged_switch', 'label' => 'Unmanaged Switch', 'sort_order' => 15],
                ['value' => 'poe_switch', 'label' => 'PoE Switch', 'sort_order' => 16],
                ['value' => 'gigabit_switch', 'label' => 'Gigabit Switch', 'sort_order' => 17],
                
                // Network types
                ['value' => 'router', 'label' => 'Router', 'sort_order' => 18],
                ['value' => 'access_point', 'label' => 'Access Point', 'sort_order' => 19],
                ['value' => 'firewall', 'label' => 'Firewall', 'sort_order' => 20],
                ['value' => 'modem', 'label' => 'Modem', 'sort_order' => 21],
                
                // Other types
                ['value' => 'monitor', 'label' => 'Monitor', 'sort_order' => 22],
                ['value' => 'keyboard', 'label' => 'Keyboard', 'sort_order' => 23],
                ['value' => 'mouse', 'label' => 'Mouse', 'sort_order' => 24],
                ['value' => 'webcam', 'label' => 'Webcam', 'sort_order' => 25],
                ['value' => 'speaker', 'label' => 'Speaker', 'sort_order' => 26],
                ['value' => 'headset', 'label' => 'Headset', 'sort_order' => 27],
            ],
        ];

        foreach ($options as $type => $typeOptions) {
            foreach ($typeOptions as $option) {
                DropdownOption::firstOrCreate(
                    [
                        'type' => $type,
                        'value' => $option['value']
                    ],
                    [
                        'label' => $option['label'],
                        'sort_order' => $option['sort_order'],
                        'is_active' => true
                    ]
                );
            }
        }
    }
}
