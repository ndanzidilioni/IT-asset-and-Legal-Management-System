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
            if (!Schema::hasColumn('legal_cases', 'case_year')) {
                $table->string('case_year', 20)->nullable();
            }

            if (!Schema::hasColumn('legal_cases', 'parties')) {
                $table->text('parties')->nullable();
            }

            if (!Schema::hasColumn('legal_cases', 'amount_in_claim')) {
                $table->decimal('amount_in_claim', 15, 2)->nullable()->default(0);
            }

            if (!Schema::hasColumn('legal_cases', 'any_appeal')) {
                $table->string('any_appeal', 100)->nullable()->default('No');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legal_cases', function (Blueprint $table) {
            if (Schema::hasColumn('legal_cases', 'case_year')) {
                $table->dropColumn('case_year');
            }

            if (Schema::hasColumn('legal_cases', 'parties')) {
                $table->dropColumn('parties');
            }

            if (Schema::hasColumn('legal_cases', 'amount_in_claim')) {
                $table->dropColumn('amount_in_claim');
            }

            if (Schema::hasColumn('legal_cases', 'any_appeal')) {
                $table->dropColumn('any_appeal');
            }
        });
    }
};
