<?php

/**
 * ICT Asset Register Setup Script with Login Users
 * 
 * This script sets up the ICT Asset Register system and creates default users for login
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ICT Asset Register Setup with Login Users ===\n\n";

try {
    // Step 1: Run migrations
    echo "1. Running database migrations...\n";
    Artisan::call('migrate:fresh');
    echo "   ✅ Database migrations completed\n\n";

    // Step 2: Create default users
    echo "2. Creating default users...\n";
    
    // Create Admin User
    $admin = User::create([
        'name' => 'System Administrator',
        'email' => 'admin@system.com',
        'password' => bcrypt('admin123'),
        'role' => 'admin'
    ]);
    echo "   ✅ Admin user created: admin@system.com / admin123\n";
    
    // Create Developer User
    $developer = User::create([
        'name' => 'IT Developer',
        'email' => 'developer@system.com',
        'password' => bcrypt('dev123'),
        'role' => 'developer'
    ]);
    echo "   ✅ Developer user created: developer@system.com / dev123\n";
    
    // Create Client User
    $client = User::create([
        'name' => 'System Client',
        'email' => 'client@system.com',
        'password' => bcrypt('client123'),
        'role' => 'client'
    ]);
    echo "   ✅ Client user created: client@system.com / client123\n\n";

    // Step 3: Seed dropdown options
    echo "3. Seeding dropdown options...\n";
    Artisan::call('db:seed', ['--class' => 'DropdownOptionSeeder']);
    echo "   ✅ Dropdown options seeded\n\n";

    // Step 4: Seed sample ICT assets
    echo "4. Creating sample ICT assets...\n";
    Artisan::call('db:seed', ['--class' => 'ITAssetSeeder']);
    echo "   ✅ Sample ICT assets created\n\n";

    echo "=== Setup Complete ===\n";
    echo "🎉 ICT Asset Register is now ready with login users!\n\n";
    
    echo "🔐 Login Credentials:\n";
    echo "👑 Admin: admin@system.com / admin123\n";
    echo "👨‍💻 Developer: developer@system.com / dev123\n";
    echo "👤 Client: client@system.com / client123\n\n";
    
    echo "🚀 Next Steps:\n";
    echo "1. Start Laravel server: php artisan serve\n";
    echo "2. Start React frontend: cd frontend && npm start\n";
    echo "3. Go to: http://localhost:3000/login\n";
    echo "4. Login with any of the credentials above\n";
    echo "5. Access the ICT Dashboard and Asset Register\n\n";
    
    echo "📋 Available Features After Login:\n";
    echo "✅ ICT Asset Registration and Management\n";
    echo "✅ Comprehensive Dashboard with Analytics\n";
    echo "✅ Advanced Filtering and Search\n";
    echo "✅ Import/Export Functionality\n";
    echo "✅ Dropdown Options Management\n";
    echo "✅ Maintenance Scheduling\n";
    echo "✅ Network Asset Tracking\n";
    echo "✅ Critical Asset Monitoring\n";
    echo "✅ Department and Category Analytics\n";
    echo "✅ Status and Condition Tracking\n\n";

} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Check database connection in .env file\n";
    echo "2. Ensure all dependencies are installed (composer install)\n";
    echo "3. Verify file permissions for storage and cache directories\n";
    echo "4. Check Laravel logs in storage/logs/laravel.log\n";
    exit(1);
}
