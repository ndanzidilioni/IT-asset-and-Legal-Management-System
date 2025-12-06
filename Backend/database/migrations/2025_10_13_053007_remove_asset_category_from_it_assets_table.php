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
        Schema::table('it_assets', function (Blueprint $table) {
            $table->dropColumn('asset_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('it_assets', function (Blueprint $table) {
            $table->string('asset_category')->default('general')->after('room');
        });
    }
};
