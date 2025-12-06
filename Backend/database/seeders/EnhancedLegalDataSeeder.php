<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EnhancedLegalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add more realistic Tanzanian legal cases
        $legalCases = [
            [
                'case_number' => 'HC/MISC/CIVIL/APP/123/2024',
                'case_year' => '2024',
                'parties' => 'Ministry of Infrastructure Development vs. China Railway Construction Corp',
                'title' => 'Contract Breach - Dodoma Highway Construction Delay',
                'description' => 'Dispute over delayed completion of highway construction project. Ministry seeking damages for delays affecting project timeline and budget overruns.',
                'client_name' => 'Ministry of Infrastructure Development',
                'case_type' => 'Contract Dispute',
                'status' => 'Active',
                'priority' => 'High',
                'assigned_lawyer' => 'Advocate Mwalimu',
                'assigned_lawyer_id' => 4,
                'created_by' => 1,
                'court_name' => 'High Court of Tanzania - Commercial Division',
                'filing_date' => '2024-08-15',
                'hearing_date' => '2024-12-10',
                'next_hearing_date' => '2025-01-15',
                'amount_in_claim' => 2500000000.00,
                'any_appeal' => 'No',
                'notes' => 'High-value infrastructure dispute requiring expert testimony on construction delays',
                'created_at' => Carbon::create(2024, 8, 15),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'case_number' => 'HC/LAND/CASE/456/2024',
                'case_year' => '2024',
                'parties' => 'Kilimanjaro Coffee Estates Ltd vs. Local Village Council',
                'title' => 'Land Rights Dispute - Coffee Plantation Boundary',
                'description' => 'Land boundary dispute involving 500 hectares of coffee plantation. Conflicting claims between commercial estate and traditional village land rights.',
                'client_name' => 'Kilimanjaro Coffee Estates Ltd',
                'case_type' => 'Land Dispute',
                'status' => 'Active',
                'priority' => 'High',
                'assigned_lawyer' => 'Advocate Msomi',
                'assigned_lawyer_id' => 5,
                'created_by' => 1,
                'court_name' => 'High Court of Tanzania - Land Division',
                'filing_date' => '2024-09-20',
                'hearing_date' => '2024-11-25',
                'next_hearing_date' => '2025-02-10',
                'amount_in_claim' => 800000000.00,
                'any_appeal' => 'No',
                'notes' => 'Complex land rights case involving traditional and modern land tenure systems',
                'created_at' => Carbon::create(2024, 9, 20),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'case_number' => 'HC/COMM/CASE/789/2025',
                'case_year' => '2025',
                'parties' => 'Dar Port Authority vs. Maersk Tanzania Ltd',
                'title' => 'Port Services Contract Termination Dispute',
                'description' => 'Dispute over early termination of port management services contract. Issues include performance standards, equipment handover, and compensation claims.',
                'client_name' => 'Dar es Salaam Port Authority',
                'case_type' => 'Commercial Dispute',
                'status' => 'Under Review',
                'priority' => 'High',
                'assigned_lawyer' => 'Advocate Sheria',
                'assigned_lawyer_id' => 6,
                'created_by' => 1,
                'court_name' => 'High Court of Tanzania - Commercial Division',
                'filing_date' => '2025-01-10',
                'hearing_date' => '2025-03-15',
                'next_hearing_date' => '2025-03-15',
                'amount_in_claim' => 1200000000.00,
                'any_appeal' => 'No',
                'notes' => 'International commercial arbitration clause may apply',
                'created_at' => Carbon::create(2025, 1, 10),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'case_number' => 'HC/TAX/APP/234/2024',
                'case_year' => '2024',
                'parties' => 'Tanzania Revenue Authority vs. Twiga Cement Company',
                'title' => 'Tax Assessment Appeal - Import Duty Calculation',
                'description' => 'Appeal against TRA tax assessment on imported cement manufacturing equipment. Dispute over classification and applicable duty rates.',
                'client_name' => 'Twiga Cement Company Ltd',
                'case_type' => 'Tax Appeal',
                'status' => 'Active',
                'priority' => 'Medium',
                'assigned_lawyer' => 'Advocate Mwalimu',
                'assigned_lawyer_id' => 4,
                'created_by' => 1,
                'court_name' => 'Tax Revenue Appeals Board',
                'filing_date' => '2024-10-05',
                'hearing_date' => '2024-12-20',
                'next_hearing_date' => '2025-01-25',
                'amount_in_claim' => 450000000.00,
                'any_appeal' => 'Under consideration',
                'notes' => 'Technical expertise required for equipment classification',
                'created_at' => Carbon::create(2024, 10, 5),
                'updated_at' => Carbon::now()->subDays(7),
            ],
            [
                'case_number' => 'HC/ENV/CASE/567/2024',
                'case_year' => '2024',
                'parties' => 'National Environmental Management Council vs. Goldmining Corp',
                'title' => 'Environmental Impact Violation - Mining Operations',
                'description' => 'Environmental compliance enforcement action against gold mining operations. Allegations of water pollution and inadequate waste management.',
                'client_name' => 'National Environmental Management Council',
                'case_type' => 'Environmental Law',
                'status' => 'Active',
                'priority' => 'High',
                'assigned_lawyer' => 'Advocate Sheria',
                'assigned_lawyer_id' => 6,
                'created_by' => 1,
                'court_name' => 'High Court of Tanzania - Environmental Division',
                'filing_date' => '2024-07-30',
                'hearing_date' => '2024-12-05',
                'next_hearing_date' => '2025-02-20',
                'amount_in_claim' => 300000000.00,
                'any_appeal' => 'No',
                'notes' => 'Environmental impact assessment and remediation costs involved',
                'created_at' => Carbon::create(2024, 7, 30),
                'updated_at' => Carbon::now()->subDays(4),
            ]
        ];

        foreach ($legalCases as $case) {
            DB::table('legal_cases')->insert($case);
        }

        // Add more realistic contracts
        $contracts = [
            [
                'contract_number' => 'TANROADS/CTR/2025/001',
                'title' => 'Rehabilitation of Dar es Salaam - Morogoro Highway',
                'client_name' => 'Tanzania National Roads Agency',
                'client_id' => 1,
                'contract_type' => 'Service Agreement',
                'status' => 'Active',
                'start_date' => '2025-02-01',
                'end_date' => '2027-01-31',
                'contract_value' => 95000000000.00,
                'currency' => 'TSH',
                'description' => 'Complete rehabilitation of 180km highway section including asphalt overlay, bridge repairs, and drainage improvements. Project includes traffic management during construction.',
                'terms_and_conditions' => 'Standard FIDIC conditions with local amendments. Performance guarantee required.',
                'payment_terms' => 'Monthly progress payments based on certified work completion',
                'renewal_terms' => 'No automatic renewal. New tender required for extensions.',
                'termination_clause' => '90 days written notice required from either party',
                'governing_law' => 'Laws of Tanzania',
                'jurisdiction' => 'High Court of Tanzania - Commercial Division',
                'signatory_client' => 'Chief Executive - TANROADS',
                'signatory_company' => 'Managing Director',
                'created_by' => 1,
                'created_at' => Carbon::create(2025, 1, 15),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'contract_number' => 'TCRA/CTR/2024/018',
                'title' => 'Telecommunications Infrastructure Regulatory Compliance',
                'client_name' => 'Tanzania Communications Regulatory Authority',
                'client_id' => 2,
                'contract_type' => 'Service Agreement',
                'status' => 'Active',
                'start_date' => '2024-04-01',
                'end_date' => '2025-03-31',
                'contract_value' => 18000000000.00,
                'currency' => 'TSH',
                'description' => 'Legal advisory services for telecommunications sector regulation, license compliance monitoring, and policy development support.',
                'terms_and_conditions' => 'Professional services agreement with confidentiality provisions',
                'payment_terms' => 'Quarterly retainer payments plus hourly rates for additional work',
                'renewal_terms' => 'Automatic renewal for one year unless 60 days notice given',
                'termination_clause' => '30 days written notice for convenience, immediate for cause',
                'governing_law' => 'Laws of Tanzania',
                'jurisdiction' => 'Arbitration under Tanzania Arbitration Act',
                'signatory_client' => 'Director General - TCRA',
                'signatory_company' => 'Senior Partner',
                'created_by' => 1,
                'created_at' => Carbon::create(2024, 3, 15),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'contract_number' => 'BOT/CTR/2025/003',
                'title' => 'Central Bank Legal Advisory - Banking Regulation',
                'client_name' => 'Bank of Tanzania',
                'client_id' => 3,
                'contract_type' => 'Service Agreement',
                'status' => 'Under Review',
                'start_date' => '2025-03-01',
                'end_date' => '2026-02-28',
                'contract_value' => 35000000000.00,
                'currency' => 'TSH',
                'description' => 'Comprehensive legal advisory services for banking sector regulation, financial crimes investigation support, and monetary policy legal framework development.',
                'terms_and_conditions' => 'High-level confidentiality agreement. Security clearance required for assigned lawyers.',
                'payment_terms' => 'Monthly retainer plus project-based payments',
                'renewal_terms' => 'Subject to annual budget approval and performance review',
                'termination_clause' => '60 days notice required. Immediate termination for security breaches',
                'governing_law' => 'Laws of Tanzania and applicable international standards',
                'jurisdiction' => 'High Court of Tanzania - Commercial Division',
                'signatory_client' => 'Governor - Bank of Tanzania',
                'signatory_company' => 'Managing Partner',
                'created_by' => 1,
                'created_at' => Carbon::create(2025, 1, 20),
                'updated_at' => Carbon::now()->subDays(1),
            ]
        ];

        foreach ($contracts as $contract) {
            DB::table('contracts')->insert($contract);
        }

        // Add more clients if needed
        $clients = [
            [
                'name' => 'Tanzania National Roads Agency',
                'email' => 'legal@tanroads.go.tz',
                'phone' => '+255-22-2862760',
                'address' => 'TANROADS Headquarters, Samora Avenue, Dar es Salaam',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Tanzania Communications Regulatory Authority',
                'email' => 'info@tcra.go.tz',
                'phone' => '+255-22-2199760',
                'address' => 'Mawasiliano Towers, Dar es Salaam',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Bank of Tanzania',
                'email' => 'info@bot.go.tz',
                'phone' => '+255-22-2113639',
                'address' => '10 Mirambo Street, Dar es Salaam',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        foreach ($clients as $client) {
            // Check if client already exists
            $exists = DB::table('clients')->where('email', $client['email'])->exists();
            if (!$exists) {
                DB::table('clients')->insert($client);
            }
        }

        $this->command->info('✅ Enhanced legal data added to existing scheduling database:');
        $this->command->info('   - 5 New Major Legal Cases (Land, Tax, Environmental, Commercial)');
        $this->command->info('   - 3 New High-Value Contracts (Roads, Telecom, Banking)');
        $this->command->info('   - 3 New Government Agency Clients');
        $this->command->info('   - All data includes realistic Tanzanian legal context');
        $this->command->info('   - Contract values: 148 Billion TSh total');
        $this->command->info('   - Cases span multiple legal domains with scheduled hearings');
    }
}
