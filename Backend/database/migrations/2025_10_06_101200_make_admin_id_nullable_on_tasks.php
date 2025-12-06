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
        // Make admin_id nullable so clients can create requests without an admin assigned yet.
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                // change requires doctrine/dbal, but we'll attempt it; if not available, developer can run manual SQL.
                $table->unsignedBigInteger('admin_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->unsignedBigInteger('admin_id')->nullable(false)->change();
            });
        }
    }
};
