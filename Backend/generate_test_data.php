<?php

/**
 * Test Data Generator for IT Asset Register
 * 
 * This script generates realistic test data for the IT Asset Register system.
 * Run this script to populate your database with sample assets for testing.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\ITAsset;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

class TestDataGenerator
{
    private $buildings = [
        'Main Building',
        'Annex Building', 
        'Warehouse Building',
        'Office Building',
        'Research Building',
        'Administration Building'
    ];

    private $departments = [
        'IT Department',
        'HR Department',
        'Finance Department',
        'Marketing Department',
        'Operations Department',
        'Sales Department',
        'Customer Service',
        'Research & Development',
        'Quality Assurance',
        'Legal Department'
    ];

    private $assetTypes = [
        'Desktop Computer' => ['Dell OptiPlex', 'HP EliteDesk', 'Lenovo ThinkCentre', 'Apple iMac'],
        'Laptop Computer' => ['Dell XPS', 'HP EliteBook', 'Lenovo ThinkPad', 'Apple MacBook Pro', 'Microsoft Surface'],
        'Monitor' => ['Dell UltraSharp', 'Samsung LED', 'LG UltraWide', 'ASUS ProArt', 'BenQ DesignVue'],
        'Printer' => ['HP LaserJet', 'Canon ImageRunner', 'Epson WorkForce', 'Brother MFC', 'Xerox WorkCentre'],
        'Network Equipment' => ['Cisco Catalyst', 'Netgear ProSAFE', 'Ubiquiti UniFi', 'TP-Link Business'],
        'Server' => ['Dell PowerEdge', 'HP ProLiant', 'IBM System x', 'Supermicro Server'],
        'Tablet' => ['iPad Pro', 'Samsung Galaxy Tab', 'Microsoft Surface Pro', 'Lenovo Yoga Tab'],
        'Phone' => ['iPhone', 'Samsung Galaxy', 'Google Pixel', 'OnePlus'],
        'Projector' => ['Epson PowerLite', 'BenQ Business', 'Optoma Professional', 'ViewSonic Business'],
        'Scanner' => ['Canon CanoScan', 'Epson Perfection', 'HP ScanJet', 'Fujitsu ScanSnap']
    ];

    private $conditions = ['excellent', 'good', 'fair', 'poor', 'damaged'];
    private $Status = ['active', 'inactive', 'maintenance', 'disposed'];

    public function generate($count = 100)
    {
        echo "Generating {$count} test assets...\n\n";

        // Create test users if they don't exist
        $users = $this->createTestUsers();

        $generated = 0;
        $errors = 0;

        for ($i = 0; $i < $count; $i++) {
            try {
                $asset = $this->generateRandomAsset($users->random());
                $generated++;
                
                if ($generated % 10 === 0) {
                    echo "Generated {$generated} assets...\n";
                }
            } catch (Exception $e) {
                $errors++;
                echo "Error generating asset {$i}: " . $e->getMessage() . "\n";
            }
        }

        echo "\n=== Generation Complete ===\n";
        echo "Successfully generated: {$generated} assets\n";
        echo "Errors: {$errors}\n";
        
        $this->generateStatistics();
    }

    private function createTestUsers()
    {
        $users = collect();

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@company.com'],
            [
                'name' => 'System Administrator',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]
        );
        $users->push($admin);

        // Create department managers
        $managers = [
            ['name' => 'John Smith', 'email' => 'john.smith@company.com', 'role' => 'admin'],
            ['name' => 'Sarah Johnson', 'email' => 'sarah.johnson@company.com', 'role' => 'admin'],
            ['name' => 'Mike Wilson', 'email' => 'mike.wilson@company.com', 'role' => 'user'],
            ['name' => 'Lisa Brown', 'email' => 'lisa.brown@company.com', 'role' => 'user'],
            ['name' => 'David Lee', 'email' => 'david.lee@company.com', 'role' => 'user']
        ];

        foreach ($managers as $managerData) {
            $user = User::firstOrCreate(
                ['email' => $managerData['email']],
                [
                    'name' => $managerData['name'],
                    'password' => bcrypt('password'),
                    'role' => $managerData['role']
                ]
            );
            $users->push($user);
        }

        return $users;
    }

    private function generateRandomAsset($user)
    {
        $assetType = array_rand($this->assetTypes);
        $brands = $this->assetTypes[$assetType];
        $brand = $brands[array_rand($brands)];
        
        $building = $this->buildings[array_rand($this->buildings)];
        $department = $this->departments[array_rand($this->departments)];
        
        // Generate realistic floor based on building type
        $floor = $this->generateFloor($building);
        $room = $this->generateRoom($floor);
        
        $condition = $this->conditions[array_rand($this->conditions)];
        $status = $this->Status[array_rand($this->Status)];
        
        // Generate asset description
        $description = $this->generateAssetDescription($brand, $assetType);
        
        // Generate notes based on condition and status
        $notes = $this->generateNotes($condition, $status, $assetType);

        return ITAsset::create([
            'asset_description' => $description,
            'building' => $building,
            'floor' => $floor,
            'department' => $department,
            'room' => $room,
            'condition' => $condition,
            'status' => $status,
            'notes' => $notes,
            'created_by' => $user->id
        ]);
    }

    private function generateFloor($building)
    {
        $floors = ['Ground Floor', '1st Floor', '2nd Floor', '3rd Floor', '4th Floor', '5th Floor'];
        
        // Some buildings have different floor structures
        if (strpos($building, 'Warehouse') !== false) {
            $floors = ['Ground Floor', 'Mezzanine', 'Upper Level'];
        } elseif (strpos($building, 'Research') !== false) {
            $floors = ['Ground Floor', '1st Floor', '2nd Floor', '3rd Floor', 'Basement'];
        }
        
        return $floors[array_rand($floors)];
    }

    private function generateRoom($floor)
    {
        $roomTypes = ['Room', 'Office', 'Lab', 'Conference Room', 'Meeting Room', 'Storage Room'];
        $roomType = $roomTypes[array_rand($roomTypes)];
        $number = rand(100, 999);
        
        return "{$roomType} {$number}";
    }

    private function generateAssetDescription($brand, $type)
    {
        $models = [
            'Dell' => ['OptiPlex 7090', 'XPS 13', 'PowerEdge R740', 'UltraSharp U2720Q'],
            'HP' => ['EliteDesk 800', 'EliteBook 850', 'ProLiant DL380', 'LaserJet Pro'],
            'Lenovo' => ['ThinkCentre M920', 'ThinkPad X1', 'ThinkSystem SR650', 'ThinkVision P27h'],
            'Apple' => ['iMac 24"', 'MacBook Pro 16"', 'Mac Pro', 'Studio Display'],
            'Samsung' => ['Galaxy Tab S8', 'Galaxy S22', 'LED Monitor 24"', 'QLED 4K Monitor'],
            'Cisco' => ['Catalyst 2960', 'ISR 4331', 'ASA 5525', 'Aironet 2800'],
            'Canon' => ['ImageRunner 2625i', 'CanoScan 9000F', 'PowerShot G7X', 'EOS R5']
        ];

        $brandModels = $models[$brand] ?? ['Standard Model'];
        $model = $brandModels[array_rand($brandModels)];
        
        return "{$brand} {$model} - {$type}";
    }

    private function generateNotes($condition, $status, $type)
    {
        $notes = [];

        if ($condition === 'excellent') {
            $notes[] = 'Brand new equipment in perfect condition';
            $notes[] = 'Recently purchased, under warranty';
            $notes[] = 'Excellent performance, no issues reported';
        } elseif ($condition === 'good') {
            $notes[] = 'Good working condition, minor wear';
            $notes[] = 'Regular maintenance performed';
            $notes[] = 'Reliable performance, well maintained';
        } elseif ($condition === 'fair') {
            $notes[] = 'Functional but showing signs of age';
            $notes[] = 'Requires occasional maintenance';
            $notes[] = 'Still operational but may need replacement soon';
        } elseif ($condition === 'poor') {
            $notes[] = 'Multiple issues reported, needs repair';
            $notes[] = 'Performance degraded, frequent problems';
            $notes[] = 'Consider for replacement or disposal';
        } elseif ($condition === 'damaged') {
            $notes[] = 'Physical damage reported, not functional';
            $notes[] = 'Requires major repair or replacement';
            $notes[] = 'Damaged beyond economical repair';
        }

        if ($status === 'maintenance') {
            $notes[] = 'Currently under maintenance';
            $notes[] = 'Scheduled for repair';
            $notes[] = 'Maintenance in progress';
        } elseif ($status === 'disposed') {
            $notes[] = 'Disposed of due to end of life';
            $notes[] = 'Replaced with newer model';
            $notes[] = 'Disposed following company policy';
        }

        return implode('. ', array_slice($notes, 0, rand(1, 2))) . '.';
    }

    private function generateStatistics()
    {
        echo "\n=== Generated Data Statistics ===\n";
        
        $total = ITAsset::count();
        $active = ITAsset::where('status', 'active')->count();
        $maintenance = ITAsset::where('status', 'maintenance')->count();
        $disposed = ITAsset::where('status', 'disposed')->count();
        
        echo "Total Assets: {$total}\n";
        echo "Active: {$active}\n";
        echo "Maintenance: {$maintenance}\n";
        echo "Disposed: {$disposed}\n";
        
        echo "\nCondition Breakdown:\n";
        $conditions = ITAsset::selectRaw('condition, count(*) as count')
            ->groupBy('condition')
            ->get();
            
        foreach ($conditions as $condition) {
            echo "  {$condition->condition}: {$condition->count}\n";
        }
        
        echo "\nDepartment Distribution:\n";
        $departments = ITAsset::selectRaw('department, count(*) as count')
            ->groupBy('department')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
            
        foreach ($departments as $department) {
            echo "  {$department->department}: {$department->count}\n";
        }
        
        echo "\nBuilding Distribution:\n";
        $buildings = ITAsset::selectRaw('building, count(*) as count')
            ->groupBy('building')
            ->orderBy('count', 'desc')
            ->get();
            
        foreach ($buildings as $building) {
            echo "  {$building->building}: {$building->count}\n";
        }
    }
}

// Run the generator
try {
    $generator = new TestDataGenerator();
    
    // Get count from command line argument or use default
    $count = isset($argv[1]) ? (int)$argv[1] : 100;
    
    if ($count < 1 || $count > 1000) {
        echo "Please specify a count between 1 and 1000\n";
        echo "Usage: php generate_test_data.php [count]\n";
        exit(1);
    }
    
    $generator->generate($count);
    
    echo "\n✅ Test data generation completed successfully!\n";
    echo "You can now test your IT Asset Register with realistic data.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
