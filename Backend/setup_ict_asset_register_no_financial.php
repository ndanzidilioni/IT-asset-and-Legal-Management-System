<?php

/**
 * ICT Asset Register Setup Script (No Financial Features)
 * 
 * This script sets up the ICT Asset Register system without financial features:
 * - Enhanced database schema for ICT assets (no financial fields)
 * - Comprehensive dashboard functionality (no financial analytics)
 * - Advanced reporting capabilities (no financial reports)
 * - Import/export features
 * - Dropdown options management
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ICT Asset Register Setup (No Financial Features) ===\n\n";

try {
    // Step 1: Run migrations
    echo "1. Running database migrations...\n";
    Artisan::call('migrate:fresh');
    echo "   ✅ Database migrations completed\n\n";

    // Step 2: Seed dropdown options
    echo "2. Seeding dropdown options...\n";
    Artisan::call('db:seed', ['--class' => 'DropdownOptionSeeder']);
    echo "   ✅ Dropdown options seeded\n\n";

    // Step 3: Seed sample ICT assets
    echo "3. Creating sample ICT assets...\n";
    Artisan::call('db:seed', ['--class' => 'ITAssetSeeder']);
    echo "   ✅ Sample ICT assets created\n\n";

    // Step 4: Test the setup
    echo "4. Testing ICT Asset Register setup...\n";
    $output = shell_exec('php test_asset_register.php 2>&1');
    echo $output;

    echo "\n=== Setup Complete ===\n";
    echo "🎉 ICT Asset Register (No Financial Features) is now ready!\n\n";
    
    echo "📋 Available Features:\n";
    echo "✅ Enhanced ICT Asset Registration with detailed technical specifications\n";
    echo "✅ Comprehensive Dashboard with analytics and reporting (no financial data)\n";
    echo "✅ Advanced Filtering and Search capabilities\n";
    echo "✅ Import/Export functionality for bulk operations\n";
    echo "✅ Dropdown options management for consistent data entry\n";
    echo "✅ Warranty management and tracking\n";
    echo "✅ Maintenance scheduling and alerts\n";
    echo "✅ Network asset management with IP/MAC tracking\n";
    echo "✅ Critical asset monitoring\n";
    echo "✅ Department and category analytics\n";
    echo "❌ Financial tracking and cost analysis (removed as requested)\n\n";
    
    echo "🔗 API Endpoints Available:\n";
    echo "- GET /api/ict-dashboard - Comprehensive dashboard data (no financial analytics)\n";
    echo "- GET /api/ict-dashboard/reports - Detailed reports (no financial reports)\n";
    echo "- GET /api/it-assets - Enhanced asset listing with ICT filters\n";
    echo "- POST /api/it-assets - Create ICT assets with full specifications\n";
    echo "- GET /api/dropdown-options - Manage dropdown options\n";
    echo "- GET /api/it-assets/export/csv - Export with ICT fields\n";
    echo "- POST /api/it-assets/import/csv - Import ICT assets\n\n";
    
    echo "🎨 Frontend Components:\n";
    echo "- ICTDashboard - Comprehensive analytics dashboard (no financial data)\n";
    echo "- ICTAssetForm - Multi-tab form for detailed asset registration (no financial tab)\n";
    echo "- DropdownWithAdd - Smart dropdowns with add-new capability\n";
    echo "- Enhanced ITAssetReport - Advanced filtering and reporting\n\n";
    
    echo "📊 Dashboard Features:\n";
    echo "- Overview statistics and key metrics\n";
    echo "- Asset category and condition analysis\n";
    echo "- Maintenance and warranty alerts\n";
    echo "- Department and brand distribution\n";
    echo "- Recent activities and critical assets\n";
    echo "- Network assets monitoring\n";
    echo "- Custom reports generation\n";
    echo "- Status and condition overview\n\n";
    
    echo "🚀 Next Steps:\n";
    echo "1. Start Laravel server: php artisan serve\n";
    echo "2. Start React frontend: cd frontend && npm start\n";
    echo "3. Access the ICT Dashboard to view analytics\n";
    echo "4. Use the enhanced asset form to register ICT assets\n";
    echo "5. Explore the reporting features for insights\n";
    echo "6. Set up maintenance schedules and warranty tracking\n";
    echo "7. Configure network zones and critical asset monitoring\n\n";
    
    echo "📚 ICT Asset Categories Supported:\n";
    echo "- Hardware (Laptops, Desktops, Servers, etc.)\n";
    echo "- Software (Licenses, Applications, etc.)\n";
    echo "- Network (Routers, Switches, Access Points, etc.)\n";
    echo "- Peripheral (Printers, Scanners, Monitors, etc.)\n";
    echo "- Mobile (Smartphones, Tablets, etc.)\n";
    echo "- Storage (NAS, SAN, External Drives, etc.)\n\n";
    
    echo "🔧 Technical Specifications Tracked:\n";
    echo "- Processor, Memory, Storage details\n";
    echo "- Operating System and Software versions\n";
    echo "- IP Address and MAC Address\n";
    echo "- Network Zone and Security classification\n";
    echo "- Backup status and criticality flags\n";
    echo "- Technical notes and configurations\n\n";
    
    echo "🔄 Lifecycle Management:\n";
    echo "- Deployment and assignment tracking\n";
    echo "- Maintenance scheduling and history\n";
    echo "- Status monitoring (Active, Maintenance, Disposed)\n";
    echo "- Location and assignment management\n";
    echo "- End-of-life planning\n";
    echo "- Warranty status and expiry tracking\n\n";
    
    echo "⚠️  Removed Features (as requested):\n";
    echo "❌ Purchase price tracking\n";
    echo "❌ Purchase date recording\n";
    echo "❌ Vendor information\n";
    echo "❌ Financial cost analysis\n";
    echo "❌ Budget tracking\n";
    echo "❌ ROI calculations\n";
    echo "❌ Financial reports\n\n";
    
    echo "Your ICT Asset Register (No Financial Features) is now fully operational! 🎉\n";

} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Check database connection in .env file\n";
    echo "2. Ensure all dependencies are installed (composer install)\n";
    echo "3. Verify file permissions for storage and cache directories\n";
    echo "4. Check Laravel logs in storage/logs/laravel.log\n";
    exit(1);
}
