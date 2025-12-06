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
            // ICT Asset Classification
            $table->string('asset_category')->default('general'); // hardware, software, network, peripheral, mobile
            $table->string('asset_type')->nullable(); // laptop, desktop, printer, router, etc.
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            
            // Technical Specifications
            $table->string('processor')->nullable();
            $table->string('memory')->nullable();
            $table->string('storage')->nullable();
            $table->string('operating_system')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            
            
            // Asset Lifecycle
            $table->date('deployment_date')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->string('assigned_to')->nullable(); // User or department
            $table->string('location_details')->nullable(); // More specific location info
            
            // Additional ICT Fields
            $table->string('network_zone')->nullable(); // DMZ, Internal, External
            $table->boolean('is_critical')->default(false);
            $table->string('backup_status')->nullable();
            $table->text('technical_notes')->nullable();
            
            // Indexes for better performance
            $table->index(['asset_category', 'asset_type']);
            $table->index(['brand', 'model']);
            $table->index('serial_number');
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('it_assets', function (Blueprint $table) {
            $table->dropIndex(['asset_category', 'asset_type']);
            $table->dropIndex(['brand', 'model']);
            $table->dropIndex('serial_number');
            $table->dropIndex('assigned_to');
            
            $table->dropColumn([
                'asset_category', 'asset_type', 'brand', 'model', 'serial_number',
                'processor', 'memory', 'storage', 'operating_system', 'ip_address', 'mac_address',
                'deployment_date', 'last_maintenance_date', 'next_maintenance_date', 'assigned_to', 'location_details',
                'network_zone', 'is_critical', 'backup_status', 'technical_notes'
            ]);
        });
    }
};
