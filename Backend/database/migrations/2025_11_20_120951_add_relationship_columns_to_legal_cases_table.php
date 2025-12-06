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
        Schema::table('legal_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('legal_cases', 'client_id')) {
                $table->unsignedBigInteger('client_id')->nullable()->after('id');
                $table->index('client_id');
            }

            if (!Schema::hasColumn('legal_cases', 'assigned_lawyer_id')) {
                $table->unsignedBigInteger('assigned_lawyer_id')->nullable()->after('assigned_lawyer');
                $table->index('assigned_lawyer_id');
            }

            if (!Schema::hasColumn('legal_cases', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('assigned_lawyer_id');
                $table->index('created_by');
            }

            if (!Schema::hasColumn('legal_cases', 'filing_date')) {
                $table->date('filing_date')->nullable()->after('court_name');
            }

            if (!Schema::hasColumn('legal_cases', 'hearing_date')) {
                $table->date('hearing_date')->nullable()->after('filing_date');
            }

            if (!Schema::hasColumn('legal_cases', 'notes')) {
                $table->text('notes')->nullable()->after('any_appeal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legal_cases', function (Blueprint $table) {
            if (Schema::hasColumn('legal_cases', 'client_id')) {
                $table->dropIndex(['client_id']);
                $table->dropColumn('client_id');
            }

            if (Schema::hasColumn('legal_cases', 'assigned_lawyer_id')) {
                $table->dropIndex(['assigned_lawyer_id']);
                $table->dropColumn('assigned_lawyer_id');
            }

            if (Schema::hasColumn('legal_cases', 'created_by')) {
                $table->dropIndex(['created_by']);
                $table->dropColumn('created_by');
            }

            if (Schema::hasColumn('legal_cases', 'filing_date')) {
                $table->dropColumn('filing_date');
            }

            if (Schema::hasColumn('legal_cases', 'hearing_date')) {
                $table->dropColumn('hearing_date');
            }

            if (Schema::hasColumn('legal_cases', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
