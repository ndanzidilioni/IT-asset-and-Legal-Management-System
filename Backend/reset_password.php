<?php

/**
 * Reset Password Script
 * Resets the admin password to a simple one
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use Illuminate\Foundation\Application;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Reset Admin Password ===\n\n";

try {
    // Find the admin user
    $admin = User::where('email', 'admin@test.com')->first();
    
    if (!$admin) {
        echo "❌ Admin user not found!\n";
        echo "Creating new admin user...\n";
        
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);
        echo "✅ New admin user created!\n";
    } else {
        // Reset password to something simple
        $admin->update([
            'password' => bcrypt('password123')
        ]);
        echo "✅ Admin password reset successfully!\n";
    }

    echo "\n=== Your New Login Credentials ===\n";
    echo "Email:    admin@test.com\n";
    echo "Password: password123\n";
    echo "Role:     Admin\n\n";
    
    echo "✅ You can now login with these credentials!\n";
    echo "Go to: http://localhost:3000\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}













