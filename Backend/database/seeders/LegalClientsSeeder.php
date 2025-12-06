<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LegalClientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            // Government Entities
            [
                'name' => 'Ministry of Health, Community Development, Gender, Elderly and Children',
                'email' => 'legal@moh.go.tz',
                'phone' => '+255-22-2120261',
                'address' => 'Samora Avenue, P.O. Box 9083, Dar es Salaam, Tanzania',
                'company' => 'Ministry of Health',
                'client_type' => 'Government',
                'status' => 'VIP',
                'contact_person' => 'Permanent Secretary - Legal Affairs',
                'tax_id' => 'GOV-MOH-001',
                'registration_number' => 'MIN/HEALTH/2024',
                'notes' => 'Primary government client for healthcare legal matters. Handles medical negligence cases, pharmaceutical regulations, and health policy legal frameworks.',
                'created_by' => 1,
                'created_at' => Carbon::now()->subMonths(8),
                'updated_at' => Carbon::now()->subDays(15),
            ],
            [
                'name' => 'Tanzania Electric Supply Company Limited',
                'email' => 'legal@tanesco.co.tz',
                'phone' => '+255-22-2451174',
                'address' => 'Kijitonyama, P.O. Box 9024, Dar es Salaam, Tanzania',
                'company' => 'TANESCO',
                'client_type' => 'Government',
                'status' => 'VIP',
                'contact_person' => 'Head of Legal Department',
                'tax_id' => '101-234-567',
                'registration_number' => 'TANESCO/REG/1975',
                'notes' => 'National electricity utility company. Legal matters include power purchase agreements, infrastructure contracts, and regulatory compliance.',
                'created_by' => 1,
                'created_at' => Carbon::now()->subMonths(6),
                'updated_at' => Carbon::now()->subDays(8),
            ],
            [
                'name' => 'Tanzania Ports Authority',
                'email' => 'legal@ports.go.tz',
                'phone' => '+255-22-2117888',
                'address' => 'Sokoine Drive, P.O. Box 9184, Dar es Salaam, Tanzania',
                'company' => 'TPA',
                'client_type' => 'Government',
                'status' => 'Active',
                'contact_person' => 'Legal Counsel',
                'tax_id' => 'TPA-001-2024',
                'registration_number' => 'PORTS/AUTH/1977',
                'notes' => 'Manages all major ports in Tanzania. Legal services include maritime law, cargo disputes, and port operations contracts.',
                'created_by' => 1,
                'created_at' => Carbon::now()->subMonths(5),
                'updated_at' => Carbon::now()->subDays(12),
            ],

            // Major Corporations
            [
                'name' => 'Vodacom Tanzania PLC',
                'email' => 'legal.affairs@vodacom.co.tz',
                'phone' => '+255-754-282828',
                'address' => 'Haile Selassie Road, P.O. Box 2369, Dar es Salaam, Tanzania',
                'company' => 'Vodacom Tanzania',
                'client_type' => 'Corporate',
                'status' => 'VIP',
                'contact_person' => 'Chief Legal Officer',
                'tax_id' => '118-500-789',
                'registration_number' => 'VODACOM/TZ/2000',
                'notes' => 'Leading telecommunications company. Handles regulatory compliance, commercial contracts, intellectual property, and consumer protection matters.',
                'created_by' => 1,
                'created_at' => Carbon::now()->subMonths(10),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'name' => 'Barrick Gold Corporation - North Mara Mine',
                'email' => 'legal.tanzania@barrick.com',
                'phone' => '+255-28-2620001',
                'address' => 'North Mara Mine, P.O. Box 75, Tarime, Mara, Tanzania',
                'company' => 'Barrick Gold',
                'client_type' => 'Corporate',
                'status' => 'VIP',
                'contact_person' => 'Country Legal Manager',
                'tax_id' => '125-890-456',
                'registration_number' => 'BARRICK/NM/2006',
                'notes' => 'International mining corporation. Legal matters include environmental compliance, mining rights, community relations, and regulatory affairs.',
                'created_by' => 1,
                'created_at' => Carbon::now()->subMonths(7),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'name' => 'Precision Air Services PLC',
                'email' => 'legal@precisionairtz.com',
                'phone' => '+255-22-2860701',
                'address' => 'Terminal Two, Julius K. Nyerere International Airport, P.O. Box 70770, Dar es Salaam',
                'company' => 'Precision Air',
                'client_type' => 'Corporate',
                'status' => 'Active',
                'contact_person' => 'Head of Legal and Compliance',
                'tax_id' => '110-567-234',
                'registration_number' => 'PAL/REG/1993',
                'notes' => 'National airline carrier. Legal services include aviation law, insurance claims, passenger rights, and aircraft lease agreements.',
                'created_by' => 1,
                'created_at' => Carbon::now()->subMonths(4),
                'updated_at' => Carbon::now()->subDays(18),
            ],

            // International Organizations
            [
                'name' => 'World Health Organization - Tanzania',
                'email' => 'legal@who.int',
                'phone' => '+255-22-2650043',
                'address' => 'Jakaya Kikwete Road, P.O. Box 9292, Dar es Salaam, Tanzania',
                'company' => 'WHO Tanzania',
                'client_type' => 'NGO',
                'status' => 'VIP',
                'contact_person' => 'Legal Affairs Officer',
                'tax_id' => 'EXEMPT-WHO-001',
                'registration_number' => 'WHO/TZ/1961',
                'notes' => 'UN specialized agency. Legal support for health programs, international agreements, diplomatic immunity issues, and partnership agreements.',
                'created_by' => 1,
                'created_at' => Carbon::now()->subMonths(12),
                'updated_at' => Carbon::now()->subDays(25),
            ],
            [
                'name' => 'United Nations Development Programme - Tanzania',
                'email' => 'legal.undp@undp.org',
                'phone' => '+255-22-2112670',
                'address' => 'UN House, Ali Hassan Mwinyi Road, P.O. Box 9182, Dar es Salaam',
                'company' => 'UNDP Tanzania',
                'client_type' => 'NGO',
                'status' => 'VIP',
                'contact_person' => 'Country Legal Advisor',
                'tax_id' => 'EXEMPT-UNDP-001',
                'registration_number' => 'UNDP/TZ/1965',
                'notes' => 'UN development agency. Legal assistance for development projects, government partnerships, procurement compliance, and program implementation.',
                'created_by' => 1,
                'created_at' => Carbon::now()->subMonths(9),
                'updated_at' => Carbon::now()->subDays(7),
            ],

            // Individual High-Profile Clients
            [
                'name' => 'Prof. Florens Luoga',
                'email' => 'prof.luoga@udsm.ac.tz',
                'phone' => '+255-754-123789',
                'address' => 'University of Dar es Salaam, P.O. Box 35091, Dar es Salaam',
                'company' => 'Academic Professional',
                'client_type' => 'Individual',
                'status' => 'VIP',
                'contact_person' => 'Prof. Florens Luoga',
                'tax_id' => 'IND-FL-2024-001',
                'registration_number' => 'N/A',
                'notes' => 'Prominent academic and former Bank of Tanzania Governor. Legal services include intellectual property, academic publications, and personal legal matters.',
                'created_by' => 1,
                'created_at' => Carbon::now()->subMonths(3),
                'updated_at' => Carbon::now()->subDays(14),
            ],
            [
                'name' => 'Reginald Abraham Mengi Estate',
                'email' => 'estate@mengi.co.tz',
                'phone' => '+255-22-2775148',
                'address' => 'Mikocheni Light Industrial Area, P.O. Box 2271, Dar es Salaam',
                'company' => 'Mengi Holdings',
                'client_type' => 'Individual',
                'status' => 'VIP',
                'contact_person' => 'Estate Administrator',
                'tax_id' => 'EST-RAM-2019',
                'registration_number' => 'ESTATE/MENGI/2019',
                'notes' => 'Estate of late business magnate. Ongoing legal matters include estate administration, business succession, and philanthropic foundation management.',
                'created_by' => 1,
                'created_at' => Carbon::now()->subMonths(14),
                'updated_at' => Carbon::now()->subDays(6),
            ],

            // Local Businesses
            [
                'name' => 'East African Breweries Limited - Tanzania',
                'email' => 'legal@eabl.co.tz',
                'phone' => '+255-22-2862547',
                'address' => 'Plot No. 52, Nyerere Road, Dar es Salaam, Tanzania',
                'company' => 'EABL Tanzania',
                'client_type' => 'Corporate',
                'status' => 'Active',
                'contact_person' => 'Legal Manager',
                'tax_id' => '108-456-789',
                'registration_number' => 'EABL/TZ/1988',
                'notes' => 'Leading beverage manufacturer. Legal matters include distribution agreements, licensing, regulatory compliance, and commercial litigation.',
                'created_by' => 1,
                'created_at' => Carbon::now()->subMonths(6),
                'updated_at' => Carbon::now()->subDays(21),
            ],
            [
                'name' => 'Kilimanjaro Coffee Growers Cooperative Union',
                'email' => 'legal@kncu.co.tz',
                'phone' => '+255-27-2754284',
                'address' => 'Moshi, Kilimanjaro, P.O. Box 893, Moshi, Tanzania',
                'company' => 'KNCU',
                'client_type' => 'Corporate',
                'status' => 'Active',
                'contact_person' => 'General Manager - Legal',
                'tax_id' => '115-789-321',
                'registration_number' => 'KNCU/REG/1933',
                'notes' => 'Historic coffee cooperative. Legal services include farmer contracts, export agreements, certification compliance, and land disputes.',
                'created_by' => 1,
                'created_at' => Carbon::now()->subMonths(11),
                'updated_at' => Carbon::now()->subDays(9),
            ]
        ];

        foreach ($clients as $client) {
            // Check if client already exists by email
            $exists = DB::table('clients')->where('email', $client['email'])->exists();
            if (!$exists) {
                DB::table('clients')->insert($client);
            }
        }

        $this->command->info('✅ Legal Clients Database Enhanced:');
        $this->command->info('   - 12 New High-Profile Clients Added');
        $this->command->info('   - Government Entities: MOH, TANESCO, TPA');
        $this->command->info('   - Major Corporations: Vodacom, Barrick, Precision Air');
        $this->command->info('   - International Organizations: WHO, UNDP');
        $this->command->info('   - VIP Individuals: Prof. Luoga, Mengi Estate');
        $this->command->info('   - Local Businesses: EABL, Coffee Cooperative');
        $this->command->info('   - All clients have realistic Tanzanian legal context');
        $this->command->info('   - Client types: Government, Corporate, NGO, Individual');
        $this->command->info('   - Status levels: VIP, Active for proper prioritization');
    }
}
