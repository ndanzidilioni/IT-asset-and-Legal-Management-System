<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegalContractsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contracts = [
            [
                'contract_number' => 'CTR/2025/001',
                'title' => 'Road Construction Project - Phase 1',
                'contract_type' => 'Construction',
                'client_name' => 'ABC Construction Ltd',
                'contract_value' => 150000000,
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'signing_date' => '2025-01-01',
                'status' => 'Active',
                'description' => 'Category: PMU' . PHP_EOL . 'Tender Number: TEN/2024/PMU/001' . PHP_EOL . 'Construction of main road infrastructure',
                'tender_number' => 'TEN/2024/PMU/001',
                'supplier_contractor' => 'ABC Construction Ltd',
                'category' => 'PMU',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'contract_number' => 'CTR/2025/002',
                'title' => 'IT Equipment Supply',
                'contract_type' => 'Supply',
                'client_name' => 'TechSolutions Tanzania',
                'contract_value' => 25000000,
                'start_date' => '2025-02-01',
                'end_date' => '2025-06-30',
                'signing_date' => '2025-01-15',
                'status' => 'Active',
                'description' => 'Category: ICT' . PHP_EOL . 'Tender Number: TEN/2025/ICT/001' . PHP_EOL . 'Supply of computers and network equipment',
                'tender_number' => 'TEN/2025/ICT/001',
                'supplier_contractor' => 'TechSolutions Tanzania',
                'category' => 'ICT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'contract_number' => 'CTR/2024/015',
                'title' => 'Office Renovation Project',
                'contract_type' => 'Renovation',
                'client_name' => 'BuildCorp Ltd',
                'contract_value' => 45000000,
                'start_date' => '2024-10-01',
                'end_date' => '2024-12-31',
                'signing_date' => '2024-09-15',
                'status' => 'Completed',
                'description' => 'Category: PMU' . PHP_EOL . 'Tender Number: TEN/2024/PMU/015' . PHP_EOL . 'Complete renovation of office building',
                'tender_number' => 'TEN/2024/PMU/015',
                'supplier_contractor' => 'BuildCorp Ltd',
                'category' => 'PMU',
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(1),
            ],
            [
                'contract_number' => 'CTR/2025/003',
                'title' => 'Legal Consulting Services',
                'contract_type' => 'Service',
                'client_name' => 'Law Associates Tanzania',
                'contract_value' => 12000000,
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'signing_date' => '2024-12-20',
                'status' => 'Under Review',
                'description' => 'Category: Legal' . PHP_EOL . 'Tender Number: TEN/2024/LEG/001' . PHP_EOL . 'Legal advisory and consultation services',
                'tender_number' => 'TEN/2024/LEG/001',
                'supplier_contractor' => 'Law Associates Tanzania',
                'category' => 'Legal',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(5),
            ],
            [
                'contract_number' => 'CTR/2025/004',
                'title' => 'Vehicle Maintenance Contract',
                'contract_type' => 'Maintenance',
                'client_name' => 'AutoService Pro',
                'contract_value' => 8000000,
                'start_date' => '2025-03-01',
                'end_date' => '2026-02-28',
                'signing_date' => '2025-02-15',
                'status' => 'Signed',
                'description' => 'Category: Maintenance' . PHP_EOL . 'Tender Number: TEN/2025/MNT/001' . PHP_EOL . 'Annual vehicle maintenance services',
                'tender_number' => 'TEN/2025/MNT/001',
                'supplier_contractor' => 'AutoService Pro',
                'category' => 'Maintenance',
                'created_at' => now()->subDays(20),
                'updated_at' => now()->subDays(15),
            ]
        ];

        foreach ($contracts as $contract) {
            DB::table('legal_contracts')->insert($contract);
        }

        $this->command->info('Created ' . count($contracts) . ' sample contracts');
    }
}
