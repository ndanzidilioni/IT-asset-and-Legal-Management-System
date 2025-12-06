<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ITAsset;
use App\Models\User;

class ITAssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some test users if they don't exist (conform to updated schema)
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'fname' => 'Admin',
                'mname' => null,
                'lname' => 'User',
                'username' => 'admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'status' => 'active',
                'must_change_password' => true,
                'password_changed_at' => now(),
                'privileges' => []
            ]
        );

        $user = User::firstOrCreate(
            ['email' => 'user@test.com'],
            [
                'fname' => 'Test',
                'mname' => null,
                'lname' => 'User',
                'username' => 'testuser2',
                'password' => bcrypt('password'),
                'role' => 'client',
                'status' => 'active',
                'must_change_password' => true,
                'password_changed_at' => now(),
                'privileges' => ['client_access', 'view_reports']
            ]
        );

        // Create sample IT assets
        $assets = [
            [
                'asset_number' => 'IT2025010001',
                'asset_description' => 'Dell Laptop XPS 13 - Development Machine',
                'building' => 'Main Building',
                'floor' => '2nd Floor',
                'department' => 'IT Department',
                'room' => 'Room 201',
                'condition' => 'excellent',
                'status' => 'active',
                'notes' => 'Brand new laptop for software development',
                'created_by' => $admin->id
            ],
            [
                'asset_number' => 'IT2025010002',
                'asset_description' => 'HP LaserJet Pro Printer',
                'building' => 'Main Building',
                'floor' => '1st Floor',
                'department' => 'HR Department',
                'room' => 'Room 101',
                'condition' => 'good',
                'status' => 'active',
                'notes' => 'Network printer for HR documents',
                'created_by' => $admin->id
            ],
            [
                'asset_number' => 'IT2025010003',
                'asset_description' => 'Cisco Catalyst 2960 Switch',
                'building' => 'Main Building',
                'floor' => 'Basement',
                'department' => 'IT Department',
                'room' => 'Server Room',
                'condition' => 'excellent',
                'status' => 'active',
                'notes' => 'Core network switch for main building',
                'created_by' => $admin->id
            ],
            [
                'asset_number' => 'IT2025010004',
                'asset_description' => 'Samsung 24" LED Monitor',
                'building' => 'Annex Building',
                'floor' => '1st Floor',
                'department' => 'Finance Department',
                'room' => 'Room 105',
                'condition' => 'good',
                'status' => 'active',
                'notes' => 'Dual monitor setup for accounting',
                'created_by' => $user->id
            ],
            [
                'asset_number' => 'IT2025010005',
                'asset_description' => 'Apple MacBook Pro 16"',
                'building' => 'Main Building',
                'floor' => '3rd Floor',
                'department' => 'Marketing Department',
                'room' => 'Room 301',
                'condition' => 'excellent',
                'status' => 'active',
                'notes' => 'Creative workstation for design work',
                'created_by' => $user->id
            ],
            [
                'asset_number' => 'IT2025010006',
                'asset_description' => 'Dell OptiPlex Desktop',
                'building' => 'Main Building',
                'floor' => '2nd Floor',
                'department' => 'IT Department',
                'room' => 'Room 202',
                'condition' => 'fair',
                'status' => 'maintenance',
                'notes' => 'Requires RAM upgrade - currently in maintenance',
                'created_by' => $admin->id
            ],
            [
                'asset_number' => 'IT2025010007',
                'asset_description' => 'Epson Inkjet Printer',
                'building' => 'Warehouse Building',
                'floor' => 'Ground Floor',
                'department' => 'Operations Department',
                'room' => 'Office',
                'condition' => 'poor',
                'status' => 'inactive',
                'notes' => 'Printer not working properly, needs replacement',
                'created_by' => $user->id
            ],
            [
                'asset_number' => 'IT2025010008',
                'asset_description' => 'Lenovo ThinkPad Laptop',
                'building' => 'Main Building',
                'floor' => '1st Floor',
                'department' => 'HR Department',
                'room' => 'Room 102',
                'condition' => 'good',
                'status' => 'active',
                'notes' => 'HR manager workstation',
                'created_by' => $admin->id
            ],
            [
                'asset_number' => 'IT2025010009',
                'asset_description' => 'Canon ImageRunner Printer',
                'building' => 'Main Building',
                'floor' => '2nd Floor',
                'department' => 'Finance Department',
                'room' => 'Room 201',
                'condition' => 'excellent',
                'status' => 'active',
                'notes' => 'High-volume printer for financial documents',
                'created_by' => $user->id
            ],
            [
                'asset_number' => 'IT2025010010',
                'asset_description' => 'Old Desktop Computer',
                'building' => 'Annex Building',
                'floor' => '2nd Floor',
                'department' => 'Operations Department',
                'room' => 'Storage Room',
                'condition' => 'damaged',
                'status' => 'disposed',
                'notes' => 'Obsolete computer, disposed of after 8 years of service',
                'created_by' => $admin->id
            ]
        ];

        foreach ($assets as $assetData) {
            ITAsset::firstOrCreate(
                ['asset_number' => $assetData['asset_number']],
                $assetData
            );
        }

        // Create additional random assets using factory
        ITAsset::factory()->count(20)->create([
            'created_by' => $admin->id
        ]);

        ITAsset::factory()->count(15)->create([
            'created_by' => $user->id
        ]);
    }
}
