<?php

/**
 * Dropdown Options Test Script
 * 
 * This script tests the dropdown options functionality for the IT Asset Register.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\DropdownOption;
use App\Models\ITAsset;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

class DropdownOptionsTester
{
    public function runAllTests()
    {
        echo "=== Dropdown Options Test Suite ===\n\n";
        
        $this->testDropdownOptionsModel();
        $this->testCombinedOptions();
        $this->testExistingValues();
        $this->testAddOption();
        $this->displayAllOptions();
        
        echo "\n=== All Tests Completed ===\n";
    }

    private function testDropdownOptionsModel()
    {
        echo "1. Testing DropdownOptions Model...\n";
        
        // Test getting options by type
        $floorOptions = DropdownOption::getByType('floor');
        $this->assert($floorOptions->count() > 0, "Floor options should exist");
        
        $departmentOptions = DropdownOption::getByType('department');
        $this->assert($departmentOptions->count() > 0, "Department options should exist");
        
        echo "   ✓ DropdownOptions model working correctly\n";
    }

    private function testCombinedOptions()
    {
        echo "2. Testing Combined Options...\n";
        
        $combinedOptions = DropdownOption::getCombinedOptions('floor');
        $this->assert($combinedOptions->count() > 0, "Combined options should exist");
        
        // Check if options have the correct structure
        $firstOption = $combinedOptions->first();
        $this->assert(isset($firstOption['value']), "Option should have value");
        $this->assert(isset($firstOption['label']), "Option should have label");
        $this->assert(isset($firstOption['is_predefined']), "Option should have is_predefined flag");
        
        echo "   ✓ Combined options working correctly\n";
    }

    private function testExistingValues()
    {
        echo "3. Testing Existing Values from Assets...\n";
        
        // Create a test asset with unique values
        $user = User::first();
        if (!$user) {
            echo "   ⚠️  No users found, creating test user\n";
            $user = User::factory()->create();
        }
        
        $testAsset = ITAsset::create([
            'asset_description' => 'Test Asset for Dropdown',
            'building' => 'Test Building',
            'floor' => 'Unique Test Floor',
            'department' => 'Unique Test Department',
            'room' => 'Unique Test Room',
            'condition' => 'good',
            'status' => 'active',
            'created_by' => $user->id
        ]);
        
        $existingFloors = DropdownOption::getExistingValues('floor');
        $this->assert($existingFloors->contains('Unique Test Floor'), "Should find existing floor values");
        
        $existingDepartments = DropdownOption::getExistingValues('department');
        $this->assert($existingDepartments->contains('Unique Test Department'), "Should find existing department values");
        
        // Clean up test asset
        $testAsset->delete();
        
        echo "   ✓ Existing values detection working correctly\n";
    }

    private function testAddOption()
    {
        echo "4. Testing Add Option Functionality...\n";
        
        $newOption = DropdownOption::addOption(
            'floor',
            'Test Floor ' . time(),
            'Test Floor Label',
            999
        );
        
        $this->assert($newOption->exists, "New option should be created");
        $this->assert($newOption->type === 'floor', "Option type should be correct");
        $this->assert($newOption->is_active === true, "New option should be active");
        
        // Clean up
        $newOption->delete();
        
        echo "   ✓ Add option functionality working correctly\n";
    }

    private function displayAllOptions()
    {
        echo "5. Displaying All Available Options...\n";
        
        $types = ['floor', 'department', 'room', 'condition', 'status'];
        
        foreach ($types as $type) {
            echo "\n   {$type} Options:\n";
            $options = DropdownOption::getCombinedOptions($type);
            
            foreach ($options as $option) {
                $predefined = $option['is_predefined'] ? '[Predefined]' : '[Existing]';
                echo "     - {$option['label']} {$predefined}\n";
            }
        }
    }

    private function assert($condition, $message)
    {
        if (!$condition) {
            throw new Exception("Assertion failed: " . $message);
        }
    }
}

// Run the tests
try {
    $tester = new DropdownOptionsTester();
    $tester->runAllTests();
    echo "\n✅ All dropdown options tests passed successfully!\n";
} catch (Exception $e) {
    echo "\n❌ Test failed: " . $e->getMessage() . "\n";
    exit(1);
}
