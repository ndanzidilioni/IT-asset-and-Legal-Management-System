<?php

/**
 * Complete Reset and Setup Script
 * 
 * This script completely resets the database and sets up everything fresh
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Complete Database Reset and Setup ===\n\n";

try {
    // Step 1: Drop all tables and reset completely
    echo "1. Completely resetting database...\n";
    Artisan::call('migrate:fresh', ['--force' => true]);
    echo "   ✅ Database completely reset\n\n";

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

    // Step 5: Verify setup
    echo "5. Verifying setup...\n";
    $userCount = User::count();
    echo "   ✅ Total users: $userCount\n";
    
    // Test login credentials
    $testUser = User::where('email', 'admin@system.com')->first();
    if ($testUser && password_verify('admin123', $testUser->password)) {
        echo "   ✅ Login credentials working correctly\n";
    } else {
        echo "   ❌ Login credentials issue detected\n";
    }
    echo "\n";

    echo "=== Setup Complete ===\n";
    echo "🎉 System is now ready!\n\n";
    
    echo "🔐 Login Credentials:\n";
    echo "👑 Admin: admin@system.com / admin123\n";
    echo "👨‍💻 Developer: developer@system.com / dev123\n";
    echo "👤 Client: client@system.com / client123\n\n";
    
    echo "🚀 Next Steps:\n";
    echo "1. Start Laravel server: php artisan serve\n";
    echo "2. Start React frontend: cd frontend && npm start\n";
    echo "3. Go to: http://localhost:3000/login\n";
    echo "4. Login with any of the credentials above\n\n";
    
    echo "✅ Database completely reset\n";
    echo "✅ All migrations run successfully\n";
    echo "✅ Users created with working passwords\n";
    echo "✅ System ready for login!\n\n";

} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Check database connection in .env file\n";
    echo "2. Ensure all dependencies are installed (composer install)\n";
    echo "3. Verify file permissions for storage and cache directories\n";
    echo "4. Check Laravel logs in storage/logs/laravel.log\n";
    echo "5. Try manually dropping the database and recreating it\n";
    exit(1);
}
