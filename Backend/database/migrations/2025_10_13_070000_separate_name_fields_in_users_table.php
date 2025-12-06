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
        Schema::table('users', function (Blueprint $table) {
            // Add new name fields
            $table->string('fname')->after('id');
            $table->string('mname')->nullable()->after('fname');
            $table->string('lname')->after('mname');
            
            // Update role enum to only allow 'admin' and 'user'
            $table->dropColumn('role');
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Allow all roles used across the app and seeders
            $table->enum('role', ['admin', 'developer', 'client', 'user'])->default('user')->after('lname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop new name fields
            $table->dropColumn(['fname', 'mname', 'lname']);
            
            // Restore original role enum
            $table->dropColumn('role');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->enum('role', ['admin', 'developer', 'client'])->default('client')->after('name');
        });
    }
};
