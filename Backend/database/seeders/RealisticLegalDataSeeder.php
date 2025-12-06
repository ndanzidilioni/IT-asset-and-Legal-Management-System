<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RealisticLegalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, create admin user
        DB::table('users')->insert([
            'fname' => 'Admin',
            'lname' => 'User',
            'email' => 'admin@legal.gov.tz',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create lawyers
        $lawyers = [
            ['fname' => 'Advocate', 'lname' => 'Mwalimu', 'email' => 'mwalimu@legal.gov.tz', 'username' => 'mwalimu'],
            ['fname' => 'Advocate', 'lname' => 'Msomi', 'email' => 'msomi@legal.gov.tz', 'username' => 'msomi'],
            ['fname' => 'Advocate', 'lname' => 'Sheria', 'email' => 'sheria@legal.gov.tz', 'username' => 'sheria'],
        ];

        foreach ($lawyers as $lawyer) {
            DB::table('users')->insert([
                'fname' => $lawyer['fname'],
                'lname' => $lawyer['lname'],
                'email' => $lawyer['email'],
                'username' => $lawyer['username'],
                'password' => bcrypt('password'),
                'role' => 'lawyer',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Realistic Legal Clients
        $clients = [
            [
                'client_number' => 'CL/2024/001',
                'client_type' => 'Government',
                'full_name' => 'Ministry of Infrastructure Development',
                'short_name' => 'MID',
                'email' => 'legal@mid.go.tz',
                'phone' => '+255-22-2666781',
                'address' => 'Kivukoni Front, P.O. Box 9144, Dar es Salaam',
                'status' => 'Active',
                'created_at' => Carbon::now()->subMonths(6),
                'updated_at' => Carbon::now()->subMonths(6),
            ],
            [
                'client_number' => 'CL/2024/002',
                'client_type' => 'Corporate',
                'full_name' => 'Tanzania Building Agency',
                'short_name' => 'TBA',
                'email' => 'contracts@tba.go.tz',
                'phone' => '+255-22-2180166',
                'address' => 'Samora Avenue, P.O. Box 1106, Dar es Salaam',
                'status' => 'Active',
                'created_at' => Carbon::now()->subMonths(4),
                'updated_at' => Carbon::now()->subMonths(4),
            ],
            [
                'client_number' => 'CL/2024/003',
                'client_type' => 'Individual',
                'full_name' => 'Mhindi Kassim Mhindi',
                'short_name' => 'M.K. Mhindi',
                'email' => 'mhindi@gmail.com',
                'phone' => '+255-784-123456',
                'address' => 'Upanga, P.O. Box 2456, Dar es Salaam',
                'status' => 'Active',
                'created_at' => Carbon::now()->subMonths(3),
                'updated_at' => Carbon::now()->subMonths(3),
            ],
        ];

        foreach ($clients as $client) {
            DB::table('legal_clients')->insert($client);
        }

        // Realistic Legal Contracts
        $contracts = [
            [
                'contract_number' => 'MID/CTR/2024/001',
                'title' => 'Construction of Dodoma-Mwanza Highway Phase II',
                'contract_type' => 'Construction',
                'client_name' => 'China Railway Jianchang Engineering Co. Ltd',
                'contract_value' => 850000000000.00, // 850 Billion TSh
                'start_date' => '2024-03-15',
                'end_date' => '2026-03-14',
                'signing_date' => '2024-02-28',
                'status' => 'Active',
                'description' => 'Construction of 285km highway section from Dodoma to Mwanza including bridges, drainage systems and toll stations. Contract includes 24-month construction period with performance guarantees.',
                'tender_number' => 'MID/HQ/TEND/2023/007',
                'supplier_contractor' => 'China Railway Jianchang Engineering Co. Ltd',
                'category' => 'Infrastructure',
                'assigned_lawyer_id' => 2,
                'created_by' => 1,
                'created_at' => Carbon::create(2024, 2, 28),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'contract_number' => 'TBA/CTR/2024/015',
                'title' => 'Supply and Installation of Government Office Furniture',
                'contract_type' => 'Supply',
                'client_name' => 'Modern Office Solutions Ltd',
                'contract_value' => 45000000000.00, // 45 Billion TSh
                'start_date' => '2024-06-01',
                'end_date' => '2024-12-31',
                'signing_date' => '2024-05-15',
                'status' => 'Active',
                'description' => 'Supply and installation of office furniture for 25 government buildings across Dar es Salaam region. Includes executive desks, chairs, filing cabinets and conference room furniture.',
                'tender_number' => 'TBA/FURN/2024/003',
                'supplier_contractor' => 'Modern Office Solutions Ltd',
                'category' => 'Procurement',
                'assigned_lawyer_id' => 3,
                'created_by' => 1,
                'created_at' => Carbon::create(2024, 5, 15),
                'updated_at' => Carbon::now()->subDays(12),
            ],
            [
                'contract_number' => 'MID/CTR/2025/001',
                'title' => 'Maintenance of National Road Network - Northern Zone',
                'contract_type' => 'Maintenance',
                'client_name' => 'Tanzania Road Maintenance Consortium',
                'contract_value' => 125000000000.00, // 125 Billion TSh
                'start_date' => '2025-01-01',
                'end_date' => '2027-12-31',
                'signing_date' => '2024-12-15',
                'status' => 'Signed',
                'description' => 'Three-year maintenance contract for 1,200km of roads in Arusha, Kilimanjaro and Manyara regions. Includes routine and periodic maintenance, emergency repairs.',
                'tender_number' => 'MID/MAINT/2024/009',
                'supplier_contractor' => 'Tanzania Road Maintenance Consortium',
                'category' => 'Infrastructure',
                'assigned_lawyer_id' => 2,
                'created_by' => 1,
                'created_at' => Carbon::create(2024, 12, 15),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'contract_number' => 'TBA/CTR/2025/002',
                'title' => 'Legal Advisory Services for Public Procurement',
                'contract_type' => 'Consultancy',
                'client_name' => 'Mkono & Associates Advocates',
                'contract_value' => 18000000000.00, // 18 Billion TSh
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'signing_date' => '2024-12-20',
                'status' => 'Under Review',
                'description' => 'Legal advisory services for complex procurement processes, contract reviews, dispute resolution and regulatory compliance. Retainer-based engagement.',
                'tender_number' => 'TBA/LEG/2024/001',
                'supplier_contractor' => 'Mkono & Associates Advocates',
                'category' => 'Legal Services',
                'assigned_lawyer_id' => 4,
                'created_by' => 1,
                'created_at' => Carbon::create(2024, 12, 20),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'contract_number' => 'MID/CTR/2023/045',
                'title' => 'Construction of Mwanza Port Expansion',
                'contract_type' => 'Construction',
                'client_name' => 'East African Marine Construction Ltd',
                'contract_value' => 240000000000.00, // 240 Billion TSh
                'start_date' => '2023-07-01',
                'end_date' => '2024-12-31',
                'signing_date' => '2023-06-15',
                'status' => 'Completed',
                'description' => 'Expansion of Mwanza Port facilities including new container terminal, cargo handling equipment and warehouse facilities. Project completed ahead of schedule.',
                'tender_number' => 'MID/PORT/2022/003',
                'supplier_contractor' => 'East African Marine Construction Ltd',
                'category' => 'Infrastructure',
                'assigned_lawyer_id' => 3,
                'created_by' => 1,
                'created_at' => Carbon::create(2023, 6, 15),
                'updated_at' => Carbon::create(2024, 11, 30),
            ]
        ];

        foreach ($contracts as $contract) {
            DB::table('legal_contracts')->insert($contract);
        }

        $this->command->info('✅ Created realistic legal data:');
        $this->command->info('   - 4 Users (1 Admin + 3 Lawyers)');
        $this->command->info('   - 3 Legal Clients');
        $this->command->info('   - 5 Major Contracts (Total value: 1.3 Trillion TSh)');
        $this->command->info('   - Contracts span 2023-2027 with various statuses');
    }
}
