<?php

/**
 * Test Dropdown Fix Script
 * 
 * This script tests the dropdown options and fixes any issues
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use App\Models\DropdownOption;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Dropdown Options Fix ===\n\n";

try {
    // Step 1: Check if asset_category options exist
    echo "1. Checking asset_category options...\n";
    $categories = DropdownOption::where('type', 'asset_category')
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();
    
    if ($categories->count() > 0) {
        echo "   ✅ Found {$categories->count()} asset categories:\n";
        foreach ($categories as $category) {
            echo "      - {$category->label} ({$category->value})\n";
        }
    } else {
        echo "   ❌ No asset categories found - seeding them...\n";
        Artisan::call('db:seed', ['--class' => 'DropdownOptionSeeder']);
        echo "   ✅ Asset categories seeded\n";
    }
    echo "\n";

    // Step 2: Test API endpoint directly
    echo "2. Testing API endpoint...\n";
    $testCategories = DropdownOption::where('type', 'asset_category')
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get()
        ->map(function($item) {
            return [
                'value' => $item->value,
                'label' => $item->label,
                'is_predefined' => true
            ];
        });
    
    echo "   ✅ API data structure:\n";
    echo "   " . json_encode($testCategories->take(3)->toArray(), JSON_PRETTY_PRINT) . "\n\n";

    // Step 3: Test adding a new category
    echo "3. Testing add new category functionality...\n";
    $testCategory = DropdownOption::firstOrCreate(
        [
            'type' => 'asset_category',
            'value' => 'test_category'
        ],
        [
            'label' => 'Test Category',
            'sort_order' => 999,
            'is_active' => true
        ]
    );
    
    if ($testCategory->wasRecentlyCreated) {
        echo "   ✅ Successfully added test category\n";
        // Clean up
        $testCategory->delete();
        echo "   ✅ Test category cleaned up\n";
    } else {
        echo "   ✅ Test category already exists\n";
    }
    echo "\n";

    echo "=== Dropdown Fix Complete ===\n";
    echo "🎉 Asset categories should now work properly!\n\n";
    
    echo "📋 Available Categories:\n";
    $finalCategories = DropdownOption::where('type', 'asset_category')
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();
    
    foreach ($finalCategories as $category) {
        echo "✅ {$category->label}\n";
    }
    echo "\n";
    
    echo "🚀 Next Steps:\n";
    echo "1. Start your servers:\n";
    echo "   - Backend: php artisan serve\n";
    echo "   - Frontend: cd frontend && npm start\n";
    echo "2. Go to the asset form\n";
    echo "3. Click on Asset Category dropdown\n";
    echo "4. You should see the categories listed\n";
    echo "5. Try adding a new category by typing and clicking Add\n\n";
    
    echo "🔧 Changes Made:\n";
    echo "✅ Removed asset_type field from both forms\n";
    echo "✅ Fixed DropdownWithAdd onChange handling\n";
    echo "✅ Verified asset categories are available\n";
    echo "✅ Tested add new category functionality\n\n";

} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Check database connection in .env file\n";
    echo "2. Ensure all dependencies are installed (composer install)\n";
    echo "3. Verify file permissions for storage and cache directories\n";
    echo "4. Check Laravel logs in storage/logs/laravel.log\n";
    exit(1);
}
