<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create demand_notes table (only if it doesn't exist)
        if (!Schema::hasTable('demand_notes')) {
            Schema::create('demand_notes', function (Blueprint $table) {
            $table->id();
            $table->string('demand_note_number', 50)->unique()->comment('Unique demand note identifier (DN-YYYY-####)');
            
            // Client Information
            $table->string('client_name')->comment('Client full name or company name');
            $table->string('client_number')->nullable()->comment('Optional client reference number');
            $table->string('claim_reference')->nullable()->comment('Claim reference number');
            
            // Financial Information
            $table->decimal('amount_claimed', 15, 2)->comment('Total amount claimed in TZS');
            $table->decimal('amount_paid', 15, 2)->default(0)->comment('Total amount paid so far');
            $table->decimal('balance_due', 15, 2)->default(0)->comment('Remaining balance (auto-calculated)');
            $table->decimal('late_fee', 15, 2)->nullable()->default(0)->comment('Late payment penalty fee');
            
            // Dates
            $table->date('due_date')->comment('Payment due date');
            $table->date('issued_date')->nullable()->comment('Date demand note was issued');
            $table->date('payment_date')->nullable()->comment('Date when fully paid');
            
            // Claim Details
            $table->enum('nature_of_claim', ['Works', 'Supply of Goods', 'Services', 'Consultancy', 'Other'])
                  ->default('Works')
                  ->comment('Type of claim');
            $table->string('agency_of_claim')->nullable()->comment('Agency or organization name');
            
            // Legal Notice & Settlement
            $table->text('notice_to_institute_suit')->nullable()->comment('Legal notice details');
            $table->string('time_given_to_settle')->nullable()->comment('Settlement timeframe (e.g., 30 days)');
            $table->text('settlement_action_taken')->nullable()->comment('Actions taken or settlement details');
            
            // Status
            $table->enum('current_status', ['pending', 'paid', 'partially_paid', 'overdue', 'cancelled'])
                  ->default('pending')
                  ->comment('Current payment status');
            
            // Additional Information
            $table->text('remarks')->nullable()->comment('Additional notes or comments');
            
            // User Attribution
            $table->unsignedBigInteger('created_by')->nullable()->comment('User ID who created this note');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('demand_note_number');
            $table->index('client_name');
            $table->index('current_status');
            $table->index('due_date');
            $table->index('nature_of_claim');
            $table->index('created_by');
            $table->index(['current_status', 'due_date']);
            $table->index(['nature_of_claim', 'current_status']);
            
            // Foreign Keys
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            });
        }
        
        // Create demand_note_payments table (only if it doesn't exist)
        if (!Schema::hasTable('demand_note_payments')) {
            Schema::create('demand_note_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('demand_note_id')->comment('Foreign key to demand_notes table');
            
            // Payment Information
            $table->decimal('payment_amount', 15, 2)->comment('Amount paid in this transaction');
            $table->date('payment_date')->comment('Date of payment');
            $table->string('payment_method', 100)->nullable()->comment('Payment method (e.g., Bank Transfer, Cash)');
            $table->string('receipt_number')->nullable()->comment('Receipt or transaction number');
            $table->text('notes')->nullable()->comment('Additional payment notes');
            
            // User Attribution
            $table->unsignedBigInteger('recorded_by')->nullable()->comment('User ID who recorded this payment');
            
            // Timestamp
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index('demand_note_id');
            $table->index('payment_date');
            $table->index('recorded_by');
            
            // Foreign Keys
            $table->foreign('demand_note_id')
                  ->references('id')
                  ->on('demand_notes')
                  ->onDelete('cascade');
                  
            $table->foreign('recorded_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            });
        }
        
        // Create triggers for automatic updates (will drop and recreate if they exist)
        $this->createTriggers();
        
        // Create function for generating demand note numbers (will drop and recreate if it exists)
        $this->createFunction();
        
        // Create stored procedure for updating overdue notes (will drop and recreate if it exists)
        $this->createProcedure();
        
        // Create view for summary (will replace if exists)
        $this->createView();
    }
    
    /**
     * Create database triggers
     */
    protected function createTriggers(): void
    {
        // Drop triggers if they exist
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_payment_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_payment_delete');
        
        // Trigger after payment insert
        DB::unprepared('
            CREATE TRIGGER trg_after_payment_insert 
            AFTER INSERT ON demand_note_payments
            FOR EACH ROW
            BEGIN
              DECLARE total_paid DECIMAL(15, 2);
              DECLARE claimed_amount DECIMAL(15, 2);
              DECLARE late_fee_amount DECIMAL(15, 2);
              DECLARE new_balance DECIMAL(15, 2);
              DECLARE new_status VARCHAR(20);
              DECLARE due_date_val DATE;
              
              SELECT COALESCE(SUM(payment_amount), 0) INTO total_paid
              FROM demand_note_payments
              WHERE demand_note_id = NEW.demand_note_id;
              
              SELECT amount_claimed, COALESCE(late_fee, 0), due_date
              INTO claimed_amount, late_fee_amount, due_date_val
              FROM demand_notes
              WHERE id = NEW.demand_note_id;
              
              SET new_balance = claimed_amount + late_fee_amount - total_paid;
              
              IF new_balance <= 0 THEN
                SET new_status = "paid";
              ELSEIF total_paid > 0 THEN
                SET new_status = "partially_paid";
              ELSEIF due_date_val < CURDATE() THEN
                SET new_status = "overdue";
              ELSE
                SET new_status = "pending";
              END IF;
              
              UPDATE demand_notes
              SET 
                amount_paid = total_paid,
                balance_due = new_balance,
                current_status = new_status,
                payment_date = IF(new_balance <= 0, CURDATE(), payment_date)
              WHERE id = NEW.demand_note_id;
            END
        ');
        
        // Trigger after payment delete
        DB::unprepared('
            CREATE TRIGGER trg_after_payment_delete 
            AFTER DELETE ON demand_note_payments
            FOR EACH ROW
            BEGIN
              DECLARE total_paid DECIMAL(15, 2);
              DECLARE claimed_amount DECIMAL(15, 2);
              DECLARE late_fee_amount DECIMAL(15, 2);
              DECLARE new_balance DECIMAL(15, 2);
              DECLARE new_status VARCHAR(20);
              DECLARE due_date_val DATE;
              
              SELECT COALESCE(SUM(payment_amount), 0) INTO total_paid
              FROM demand_note_payments
              WHERE demand_note_id = OLD.demand_note_id;
              
              SELECT amount_claimed, COALESCE(late_fee, 0), due_date
              INTO claimed_amount, late_fee_amount, due_date_val
              FROM demand_notes
              WHERE id = OLD.demand_note_id;
              
              SET new_balance = claimed_amount + late_fee_amount - total_paid;
              
              IF new_balance <= 0 THEN
                SET new_status = "paid";
              ELSEIF total_paid > 0 THEN
                SET new_status = "partially_paid";
              ELSEIF due_date_val < CURDATE() THEN
                SET new_status = "overdue";
              ELSE
                SET new_status = "pending";
              END IF;
              
              UPDATE demand_notes
              SET 
                amount_paid = total_paid,
                balance_due = new_balance,
                current_status = new_status,
                payment_date = NULL
              WHERE id = OLD.demand_note_id;
            END
        ');
    }
    
    /**
     * Create function for generating demand note numbers
     */
    protected function createFunction(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS generate_demand_note_number');
        DB::unprepared('
            CREATE FUNCTION generate_demand_note_number()
            RETURNS VARCHAR(50)
            DETERMINISTIC
            BEGIN
              DECLARE current_year INT;
              DECLARE next_number INT;
              DECLARE note_number VARCHAR(50);
              
              SET current_year = YEAR(CURDATE());
              
              SELECT COALESCE(MAX(CAST(SUBSTRING(demand_note_number, 9) AS UNSIGNED)), 0) + 1
              INTO next_number
              FROM demand_notes
              WHERE demand_note_number LIKE CONCAT("DN-", current_year, "-%");
              
              SET note_number = CONCAT("DN-", current_year, "-", LPAD(next_number, 4, "0"));
              
              RETURN note_number;
            END
        ');
    }
    
    /**
     * Create stored procedure for updating overdue notes
     */
    protected function createProcedure(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS update_overdue_demand_notes');
        DB::unprepared('
            CREATE PROCEDURE update_overdue_demand_notes()
            BEGIN
              UPDATE demand_notes
              SET current_status = "overdue"
              WHERE due_date < CURDATE()
                AND current_status NOT IN ("paid", "cancelled")
                AND balance_due > 0;
                
              SELECT ROW_COUNT() AS overdue_notes_updated;
            END
        ');
    }
    
    /**
     * Create view for demand notes summary
     */
    protected function createView(): void
    {
        DB::statement('
            CREATE OR REPLACE VIEW vw_demand_notes_summary AS
            SELECT 
              dn.id,
              dn.demand_note_number,
              dn.client_name,
              dn.client_number,
              dn.amount_claimed,
              dn.amount_paid,
              dn.balance_due,
              dn.late_fee,
              dn.due_date,
              dn.issued_date,
              dn.nature_of_claim,
              dn.current_status,
              dn.created_at,
              u.username AS created_by_username,
              CONCAT(u.fname, " ", u.lname) AS created_by_name,
              COUNT(dnp.id) AS payment_count,
              DATEDIFF(CURDATE(), dn.due_date) AS days_overdue
            FROM demand_notes dn
            LEFT JOIN users u ON dn.created_by = u.id
            LEFT JOIN demand_note_payments dnp ON dn.id = dnp.demand_note_id
            GROUP BY dn.id, dn.demand_note_number, dn.client_name, dn.client_number, 
                     dn.amount_claimed, dn.amount_paid, dn.balance_due, dn.late_fee, 
                     dn.due_date, dn.issued_date, dn.nature_of_claim, dn.current_status, 
                     dn.created_at, u.username, u.fname, u.lname
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop view
        DB::statement('DROP VIEW IF EXISTS vw_demand_notes_summary');
        
        // Drop triggers
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_payment_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_payment_delete');
        
        // Drop function
        DB::unprepared('DROP FUNCTION IF EXISTS generate_demand_note_number');
        
        // Drop procedure
        DB::unprepared('DROP PROCEDURE IF EXISTS update_overdue_demand_notes');
        
        // Drop tables (order matters due to foreign keys)
        Schema::dropIfExists('demand_note_payments');
        Schema::dropIfExists('demand_notes');
    }
};
