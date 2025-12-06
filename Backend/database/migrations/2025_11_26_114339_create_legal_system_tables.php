<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create legal_contracts table
        Schema::create('legal_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number', 100)->unique();
            $table->string('title', 255);
            $table->string('contract_type', 100)->default('Service Agreement');
            $table->string('client_name', 255)->nullable();
            $table->decimal('contract_value', 15, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('signing_date')->nullable();
            $table->string('status', 50)->default('Draft');
            $table->text('description')->nullable();
            $table->string('tender_number', 100)->nullable();
            $table->string('supplier_contractor', 255)->nullable();
            $table->string('category', 100)->nullable();
            $table->unsignedBigInteger('assigned_lawyer_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->index(['status']);
            $table->index(['start_date']);
            $table->index(['contract_number']);
        });

        // Create legal_clients table
        Schema::create('legal_clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_number', 50)->unique();
            $table->string('client_type', 50)->default('Individual');
            $table->string('full_name', 255);
            $table->string('short_name', 100)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('status', 50)->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // Create contract_deletion_requests table
        Schema::create('contract_deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->unsignedBigInteger('requested_by');
            $table->text('reason');
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->text('review_comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_deletion_requests');
        Schema::dropIfExists('legal_contracts');
        Schema::dropIfExists('legal_clients');
    }
};
