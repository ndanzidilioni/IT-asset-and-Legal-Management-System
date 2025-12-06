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
        if (!Schema::hasTable('demand_note_deletion_requests')) {
            Schema::create('demand_note_deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('demand_note_id');
            $table->unsignedBigInteger('requested_by'); // User who requested deletion
            $table->string('requester_name');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable(); // User who approved/rejected
            $table->string('reviewer_name')->nullable();
            $table->text('review_comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Add indexes
            $table->index('demand_note_id');
            $table->index('requested_by');
            $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demand_note_deletion_requests');
    }
};
