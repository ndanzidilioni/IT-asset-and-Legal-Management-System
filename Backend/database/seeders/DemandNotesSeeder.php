<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemandNotesSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Ensure we have a user to reference
        $user = DB::table('users')->first();
        if (!$user) {
            // Create a default user if none exists
            $userId = DB::table('users')->insertGetId([
                'fname' => 'System',
                'lname' => 'Administrator',
                'email' => 'admin@system.com',
                'username' => 'admin',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'status' => 'active',
                'must_change_password' => true,
                'password_changed_at' => Carbon::now(),
                'privileges' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        } else {
            $userId = $user->id;
        }

        // Sample demand notes data
        $demandNotes = [
            [
                'demand_note_number' => 'DN-2025-001',
                'client_name' => 'Tanzania Building Agency',
                'client_number' => 'TBA-2024-001',
                'claim_reference' => 'CLAIM-TBA-001',
                'amount_claimed' => 15000000.00,
                'amount_paid' => 5000000.00,
                'balance_due' => 10000000.00,
                'late_fee' => 500000.00,
                'due_date' => Carbon::now()->subDays(30),
                'issued_date' => Carbon::now()->subDays(60),
                'payment_date' => null,
                'nature_of_claim' => 'Works',
                'agency_of_claim' => 'Ministry of Infrastructure',
                'notice_to_institute_suit' => 'Legal notice served on 15th October 2024 demanding payment within 30 days',
                'time_given_to_settle' => '30 days',
                'settlement_action_taken' => 'Partial payment received, follow-up meetings scheduled',
                'current_status' => 'partially_paid',
                'remarks' => 'Road construction project - Phase 1 completed',
                'created_by' => $userId,
                'created_at' => Carbon::now()->subDays(60),
                'updated_at' => Carbon::now()->subDays(10)
            ],
            [
                'demand_note_number' => 'DN-2025-002',
                'client_name' => 'Dar es Salaam Water Corporation',
                'client_number' => 'DAWASCO-2024-002',
                'claim_reference' => 'CLAIM-DAWASCO-002',
                'amount_claimed' => 8500000.00,
                'amount_paid' => 0.00,
                'balance_due' => 8500000.00,
                'late_fee' => 850000.00,
                'due_date' => Carbon::now()->subDays(45),
                'issued_date' => Carbon::now()->subDays(75),
                'payment_date' => null,
                'nature_of_claim' => 'Supply of Goods',
                'agency_of_claim' => 'Dar es Salaam Water Corporation',
                'notice_to_institute_suit' => 'Final demand notice issued, legal action pending',
                'time_given_to_settle' => '21 days',
                'settlement_action_taken' => 'Multiple reminders sent, no response received',
                'current_status' => 'overdue',
                'remarks' => 'Water pipes and fittings supply contract',
                'created_by' => $userId,
                'created_at' => Carbon::now()->subDays(75),
                'updated_at' => Carbon::now()->subDays(5)
            ],
            [
                'demand_note_number' => 'DN-2025-003',
                'client_name' => 'Ministry of Health',
                'client_number' => 'MOH-2024-003',
                'claim_reference' => 'CLAIM-MOH-003',
                'amount_claimed' => 12000000.00,
                'amount_paid' => 12000000.00,
                'balance_due' => 0.00,
                'late_fee' => 0.00,
                'due_date' => Carbon::now()->subDays(20),
                'issued_date' => Carbon::now()->subDays(50),
                'payment_date' => Carbon::now()->subDays(15),
                'nature_of_claim' => 'Consultancy',
                'agency_of_claim' => 'Ministry of Health',
                'notice_to_institute_suit' => null,
                'time_given_to_settle' => '30 days',
                'settlement_action_taken' => 'Payment received in full',
                'current_status' => 'paid',
                'remarks' => 'Healthcare system consultancy services',
                'created_by' => $userId,
                'created_at' => Carbon::now()->subDays(50),
                'updated_at' => Carbon::now()->subDays(15)
            ],
            [
                'demand_note_number' => 'DN-2025-004',
                'client_name' => 'Tanzania Electric Supply Company',
                'client_number' => 'TANESCO-2024-004',
                'claim_reference' => 'CLAIM-TANESCO-004',
                'amount_claimed' => 25000000.00,
                'amount_paid' => 0.00,
                'balance_due' => 25000000.00,
                'late_fee' => 0.00,
                'due_date' => Carbon::now()->addDays(15),
                'issued_date' => Carbon::now()->subDays(15),
                'payment_date' => null,
                'nature_of_claim' => 'Works',
                'agency_of_claim' => 'Tanzania Electric Supply Company',
                'notice_to_institute_suit' => null,
                'time_given_to_settle' => '45 days',
                'settlement_action_taken' => 'Initial discussions ongoing',
                'current_status' => 'pending',
                'remarks' => 'Electrical infrastructure upgrade project',
                'created_by' => $userId,
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(1)
            ],
            [
                'demand_note_number' => 'DN-2025-005',
                'client_name' => 'National Housing Corporation',
                'client_number' => 'NHC-2024-005',
                'claim_reference' => 'CLAIM-NHC-005',
                'amount_claimed' => 18500000.00,
                'amount_paid' => 3000000.00,
                'balance_due' => 15500000.00,
                'late_fee' => 1200000.00,
                'due_date' => Carbon::now()->subDays(60),
                'issued_date' => Carbon::now()->subDays(90),
                'payment_date' => null,
                'nature_of_claim' => 'Services',
                'agency_of_claim' => 'National Housing Corporation',
                'notice_to_institute_suit' => 'Legal proceedings initiated due to non-payment',
                'time_given_to_settle' => '30 days',
                'settlement_action_taken' => 'Court case filed, awaiting hearing date',
                'current_status' => 'overdue',
                'remarks' => 'Housing development legal services',
                'created_by' => $userId,
                'created_at' => Carbon::now()->subDays(90),
                'updated_at' => Carbon::now()->subDays(2)
            ],
            [
                'demand_note_number' => 'DN-2025-006',
                'client_name' => 'Tanzania Ports Authority',
                'client_number' => 'TPA-2024-006',
                'claim_reference' => 'CLAIM-TPA-006',
                'amount_claimed' => 7200000.00,
                'amount_paid' => 7200000.00,
                'balance_due' => 0.00,
                'late_fee' => 0.00,
                'due_date' => Carbon::now()->subDays(10),
                'issued_date' => Carbon::now()->subDays(40),
                'payment_date' => Carbon::now()->subDays(8),
                'nature_of_claim' => 'Consultancy',
                'agency_of_claim' => 'Tanzania Ports Authority',
                'notice_to_institute_suit' => null,
                'time_given_to_settle' => '30 days',
                'settlement_action_taken' => 'Payment received within due date',
                'current_status' => 'paid',
                'remarks' => 'Port operations legal advisory services',
                'created_by' => $userId,
                'created_at' => Carbon::now()->subDays(40),
                'updated_at' => Carbon::now()->subDays(8)
            ]
        ];

        // Insert demand notes and collect their IDs
        $insertedIds = [];
        foreach ($demandNotes as $index => $note) {
            $id = DB::table('demand_notes')->insertGetId($note);
            $insertedIds[$index] = $id;
        }

        // Add some payment records for partially paid notes
        $payments = [
            [
                'demand_note_id' => $insertedIds[0], // DN-2025-001
                'payment_amount' => 5000000.00,
                'payment_date' => Carbon::now()->subDays(20),
                'payment_method' => 'Bank Transfer',
                'receipt_number' => 'RCP-001-2025',
                'notes' => 'Partial payment - first installment',
                'recorded_by' => $userId,
                'created_at' => Carbon::now()->subDays(20)
            ],
            [
                'demand_note_id' => $insertedIds[2], // DN-2025-003
                'payment_amount' => 12000000.00,
                'payment_date' => Carbon::now()->subDays(15),
                'payment_method' => 'Government Cheque',
                'receipt_number' => 'RCP-003-2025',
                'notes' => 'Full payment received',
                'recorded_by' => $userId,
                'created_at' => Carbon::now()->subDays(15)
            ],
            [
                'demand_note_id' => $insertedIds[4], // DN-2025-005
                'payment_amount' => 3000000.00,
                'payment_date' => Carbon::now()->subDays(30),
                'payment_method' => 'Bank Transfer',
                'receipt_number' => 'RCP-005-2025',
                'notes' => 'Partial payment under dispute',
                'recorded_by' => $userId,
                'created_at' => Carbon::now()->subDays(30)
            ],
            [
                'demand_note_id' => $insertedIds[5], // DN-2025-006
                'payment_amount' => 7200000.00,
                'payment_date' => Carbon::now()->subDays(8),
                'payment_method' => 'Electronic Transfer',
                'receipt_number' => 'RCP-006-2025',
                'notes' => 'Full payment - prompt settlement',
                'recorded_by' => $userId,
                'created_at' => Carbon::now()->subDays(8)
            ]
        ];

        // Insert payments
        foreach ($payments as $payment) {
            DB::table('demand_note_payments')->insert($payment);
        }

        $this->command->info('Demand notes seeded successfully with realistic data!');
    }
}
