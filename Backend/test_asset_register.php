<?php

/**
 * IT Asset Register Test Script
 * 
 * This script provides comprehensive testing for the IT Asset Register functionality.
 * Run this script to test all aspects of your asset management system.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ITAsset;
use App\Http\Controllers\ITAssetController;

class AssetRegisterTester
{
    private $app;
    private $user;
    private $controller;

    public function __construct()
    {
        $this->app = require_once __DIR__ . '/bootstrap/app.php';
        $this->app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        // Create a test user
        $this->user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]
        );
        
        $this->controller = new ITAssetController();
    }

    public function runAllTests()
    {
        echo "=== IT Asset Register Test Suite ===\n\n";
        
        $this->testModelFunctionality();
        $this->testAssetNumberGeneration();
        $this->testModelScopes();
        $this->testControllerMethods();
        $this->testValidation();
        $this->testStatistics();
        $this->testCSVExport();
        $this->testCSVImport();
        
        echo "\n=== All Tests Completed ===\n";
    }

    private function testModelFunctionality()
    {
        echo "1. Testing Model Functionality...\n";
        
        // Test asset creation
        $asset = ITAsset::create([
            'asset_description' => 'Test Laptop',
            'building' => 'Test Building',
            'floor' => '1st Floor',
            'department' => 'Test Department',
            'room' => 'Room 101',
            'condition' => 'good',
            'status' => 'active',
            'notes' => 'Test asset for functionality testing',
            'created_by' => $this->user->id
        ]);
        
        $this->assert($asset->exists, "Asset creation failed");
        $this->assert($asset->creator->id === $this->user->id, "Creator relationship failed");
        
        echo "   ✓ Asset creation and relationships working\n";
    }

    private function testAssetNumberGeneration()
    {
        echo "2. Testing Asset Number Generation...\n";
        
        $assetNumber1 = ITAsset::generateAssetNumber();
        $assetNumber2 = ITAsset::generateAssetNumber();
        
        $this->assert($assetNumber1 !== $assetNumber2, "Asset numbers should be unique");
        $this->assert(str_starts_with($assetNumber1, 'IT'), "Asset number should start with 'IT'");
        $this->assert(strlen($assetNumber1) === 12, "Asset number should be 12 characters long");
        
        echo "   ✓ Asset number generation working correctly\n";
    }

    private function testModelScopes()
    {
        echo "3. Testing Model Scopes...\n";
        
        // Create test assets with different Status and conditions
        ITAsset::create([
            'asset_description' => 'Active Asset',
            'building' => 'Test Building',
            'floor' => '1st Floor',
            'department' => 'Test Department',
            'room' => 'Room 101',
            'condition' => 'excellent',
            'status' => 'active',
            'created_by' => $this->user->id
        ]);
        
        ITAsset::create([
            'asset_description' => 'Maintenance Asset',
            'building' => 'Test Building',
            'floor' => '1st Floor',
            'department' => 'Test Department',
            'room' => 'Room 101',
            'condition' => 'fair',
            'status' => 'maintenance',
            'created_by' => $this->user->id
        ]);
        
        // Test scopes
        $activeAssets = ITAsset::active()->get();
        $excellentAssets = ITAsset::byCondition('excellent')->get();
        $maintenanceAssets = ITAsset::byStatus('maintenance')->get();
        $itAssets = ITAsset::byDepartment('Test')->get();
        
        $this->assert($activeAssets->count() >= 1, "Active scope not working");
        $this->assert($excellentAssets->count() >= 1, "Condition scope not working");
        $this->assert($maintenanceAssets->count() >= 1, "Status scope not working");
        $this->assert($itAssets->count() >= 1, "Department scope not working");
        
        echo "   ✓ All model scopes working correctly\n";
    }

    private function testControllerMethods()
    {
        echo "4. Testing Controller Methods...\n";
        
        // Test index method
        $request = new Request();
        $response = $this->controller->index($request);
        $data = json_decode($response->getContent(), true);
        
        $this->assert($data['success'] === true, "Index method failed");
        $this->assert(is_array($data['data']), "Index method should return array");
        
        // Test statistics method
        $response = $this->controller->statistics();
        $data = json_decode($response->getContent(), true);
        
        $this->assert($data['success'] === true, "Statistics method failed");
        $this->assert(isset($data['data']['total']), "Statistics should include total count");
        
        echo "   ✓ Controller methods working correctly\n";
    }

    private function testValidation()
    {
        echo "5. Testing Validation...\n";
        
        // Test required field validation
        $request = new Request([]);
        $response = $this->controller->store($request);
        $data = json_decode($response->getContent(), true);
        
        $this->assert($response->getStatusCode() === 422, "Validation should fail for empty data");
        $this->assert($data['success'] === false, "Validation response should indicate failure");
        
        // Test enum validation
        $request = new Request([
            'asset_description' => 'Test Asset',
            'building' => 'Test Building',
            'floor' => '1st Floor',
            'department' => 'Test Department',
            'room' => 'Room 101',
            'condition' => 'invalid_condition',
            'status' => 'invalid_status'
        ]);
        
        $response = $this->controller->store($request);
        $this->assert($response->getStatusCode() === 422, "Enum validation should fail");
        
        echo "   ✓ Validation working correctly\n";
    }

    private function testStatistics()
    {
        echo "6. Testing Statistics...\n";
        
        $response = $this->controller->statistics();
        $data = json_decode($response->getContent(), true);
        
        $this->assert($data['success'] === true, "Statistics method failed");
        $this->assert(isset($data['data']['total']), "Statistics should include total");
        $this->assert(isset($data['data']['active']), "Statistics should include active count");
        $this->assert(isset($data['data']['maintenance']), "Statistics should include maintenance count");
        $this->assert(isset($data['data']['disposed']), "Statistics should include disposed count");
        $this->assert(isset($data['data']['conditions']), "Statistics should include conditions breakdown");
        
        echo "   ✓ Statistics calculation working correctly\n";
    }

    private function testCSVExport()
    {
        echo "7. Testing CSV Export...\n";
        
        $request = new Request();
        $response = $this->controller->exportCsv($request);
        
        $this->assert($response->getStatusCode() === 200, "CSV export should succeed");
        $this->assert($response->headers->get('Content-Type') === 'text/csv; charset=UTF-8', "CSV export should have correct content type");
        
        echo "   ✓ CSV export working correctly\n";
    }

    private function testCSVImport()
    {
        echo "8. Testing CSV Import...\n";
        
        // Create a test CSV file
        $csvContent = "Asset Number,Asset Description,Building,Floor,Department,Room,Condition,Status,Notes\n";
        $csvContent .= "IT2025019999,Test Import Asset,Test Building,1st Floor,Test Department,Room 101,excellent,active,Test import\n";
        
        $tempFile = tempnam(sys_get_temp_dir(), 'test_assets');
        file_put_contents($tempFile, $csvContent);
        
        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempFile,
            'test_assets.csv',
            'text/csv',
            null,
            true
        );
        
        $request = new Request();
        $request->files->set('csv_file', $uploadedFile);
        
        $response = $this->controller->importCsv($request);
        $data = json_decode($response->getContent(), true);
        
        $this->assert($data['success'] === true, "CSV import should succeed");
        $this->assert($data['imported'] >= 1, "CSV import should import at least 1 asset");
        
        // Clean up
        unlink($tempFile);
        
        echo "   ✓ CSV import working correctly\n";
    }

    private function assert($condition, $message)
    {
        if (!$condition) {
            throw new Exception("Assertion failed: " . $message);
        }
    }

    public function generateTestReport()
    {
        echo "\n=== IT Asset Register Test Report ===\n";
        
        $totalAssets = ITAsset::count();
        $activeAssets = ITAsset::where('status', 'active')->count();
        $maintenanceAssets = ITAsset::where('status', 'maintenance')->count();
        $disposedAssets = ITAsset::where('status', 'disposed')->count();
        
        $conditions = ITAsset::selectRaw('condition, count(*) as count')
            ->groupBy('condition')
            ->get()
            ->pluck('count', 'condition');
        
        echo "Total Assets: {$totalAssets}\n";
        echo "Active Assets: {$activeAssets}\n";
        echo "Maintenance Assets: {$maintenanceAssets}\n";
        echo "Disposed Assets: {$disposedAssets}\n";
        echo "\nCondition Breakdown:\n";
        foreach ($conditions as $condition => $count) {
            echo "  {$condition}: {$count}\n";
        }
        
        echo "\nRecent Assets:\n";
        $recentAssets = ITAsset::with('creator')->latest()->take(5)->get();
        foreach ($recentAssets as $asset) {
            echo "  {$asset->asset_number} - {$asset->asset_description} ({$asset->status})\n";
        }
    }
}

// Run the tests
try {
    $tester = new AssetRegisterTester();
    $tester->runAllTests();
    $tester->generateTestReport();
    echo "\n✅ All tests passed successfully!\n";
} catch (Exception $e) {
    echo "\n❌ Test failed: " . $e->getMessage() . "\n";
    exit(1);
}
