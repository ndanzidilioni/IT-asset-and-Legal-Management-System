<?php

/**
 * Setup Dropdown Options Script
 * 
 * This script sets up the dropdown options system for the IT Asset Register.
 * Run this after creating the migrations to populate the dropdown options.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Setting up Dropdown Options for IT Asset Register ===\n\n";

try {
    // Run migrations
    echo "1. Running migrations...\n";
    Artisan::call('migrate');
    echo "   ✅ Migrations completed\n\n";

    // Seed dropdown options
    echo "2. Seeding dropdown options...\n";
    Artisan::call('db:seed', ['--class' => 'DropdownOptionSeeder']);
    echo "   ✅ Dropdown options seeded\n\n";

    // Test the setup
    echo "3. Testing dropdown options setup...\n";
    $output = shell_exec('php test_dropdown_options.php 2>&1');
    echo $output;

    echo "\n=== Setup Complete ===\n";
    echo "✅ Dropdown options system is now ready!\n\n";
    
    echo "Available API endpoints:\n";
    echo "- GET /api/dropdown-options - Get all dropdown options\n";
    echo "- GET /api/dropdown-options/{type} - Get options by type (floor, department, room, condition, status)\n";
    echo "- POST /api/dropdown-options - Add new option\n";
    echo "- PUT /api/dropdown-options/{id} - Update option\n";
    echo "- DELETE /api/dropdown-options/{id} - Delete option\n";
    echo "- POST /api/dropdown-options/{id}/toggle - Toggle option active status\n\n";
    
    echo "Frontend components updated:\n";
    echo "- ITAssetForm now uses DropdownWithAdd components\n";
    echo "- Users can select from existing options or add new ones\n";
    echo "- Options are automatically loaded from the database\n\n";
    
    echo "Next steps:\n";
    echo "1. Start your Laravel server: php artisan serve\n";
    echo "2. Start your React frontend: cd frontend && npm start\n";
    echo "3. Test the asset form with the new dropdown functionality\n";

} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
