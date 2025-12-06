<?php

/**
 * Update Admin Credentials Script
 * Updates the admin user with new credentials
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Updating Admin Credentials ===\n\n";

try {
    // Check if admin user exists
    $admin = User::where('email', 'admin@test.com')->first();
    
    if ($admin) {
        echo "1. Updating existing admin user...\n";
        $admin->update([
            'password' => bcrypt('admin123')
        ]);
        echo "   ✅ Admin user updated: admin@test.com / admin123\n";
    } else {
        echo "1. Creating new admin user...\n";
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@test.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin'
        ]);
        echo "   ✅ Admin user created: admin@test.com / admin123\n";
    }

    // Also create/update other users
    $developer = User::where('email', 'developer@test.com')->first();
    if (!$developer) {
        User::create([
            'name' => 'IT Developer',
            'email' => 'developer@test.com',
            'password' => bcrypt('dev123'),
            'role' => 'developer'
        ]);
        echo "   ✅ Developer user created: developer@test.com / dev123\n";
    }

    $client = User::where('email', 'client@test.com')->first();
    if (!$client) {
        User::create([
            'name' => 'System Client',
            'email' => 'client@test.com',
            'password' => bcrypt('client123'),
            'role' => 'client'
        ]);
        echo "   ✅ Client user created: client@test.com / client123\n";
    }

    echo "\n=== Login Credentials ===\n";
    echo "Admin:    admin@test.com / admin123\n";
    echo "Developer: developer@test.com / dev123\n";
    echo "Client:   client@test.com / client123\n\n";
    
    echo "✅ Credentials updated successfully!\n";
    echo "Try logging in with: admin@test.com / admin123\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Make sure the database is properly set up.\n";
}

