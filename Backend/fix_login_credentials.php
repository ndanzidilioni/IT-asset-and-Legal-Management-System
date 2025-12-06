<?php

/**
 * Fix Login Credentials Script
 * Creates or updates user credentials for login
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Fixing Login Credentials ===\n\n";

try {
    // Delete existing users (handles foreign key constraints)
    echo "1. Removing existing users...\n";
    User::where('email', 'admin@system.com')->delete();
    User::where('email', 'admin@test.com')->delete();
    User::where('email', 'developer@system.com')->delete();
    User::where('email', 'developer@test.com')->delete();
    User::where('email', 'client@system.com')->delete();
    User::where('email', 'client@test.com')->delete();
    echo "   ✅ Existing users removed\n\n";

    // Create new users
    echo "2. Creating new users...\n";
    
    // Create Admin User
    $admin = User::create([
        'name' => 'System Administrator',
        'email' => 'admin@test.com',
        'password' => bcrypt('admin123'),
        'role' => 'admin'
    ]);
    echo "   ✅ Admin user created: admin@test.com / admin123\n";
    
    // Create Developer User
    $developer = User::create([
        'name' => 'IT Developer',
        'email' => 'developer@test.com',
        'password' => bcrypt('dev123'),
        'role' => 'developer'
    ]);
    echo "   ✅ Developer user created: developer@test.com / dev123\n";
    
    // Create Client User
    $client = User::create([
        'name' => 'System Client',
        'email' => 'client@test.com',
        'password' => bcrypt('client123'),
        'role' => 'client'
    ]);
    echo "   ✅ Client user created: client@test.com / client123\n\n";

    // Verify users were created
    echo "3. Verifying users...\n";
    $userCount = User::count();
    echo "   ✅ Total users in database: $userCount\n\n";

    echo "=== Login Credentials ===\n";
    echo "Admin:    admin@test.com / admin123\n";
    echo "Developer: developer@test.com / dev123\n";
    echo "Client:   client@test.com / client123\n\n";
    
    echo "✅ Login credentials fixed successfully!\n";
    echo "You can now login with admin@test.com / admin123\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Make sure the database is properly set up.\n";
}



















