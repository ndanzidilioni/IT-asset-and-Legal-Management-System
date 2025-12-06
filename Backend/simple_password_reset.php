<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use Illuminate\Foundation\Application;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Simple Password Reset ===\n\n";

try {
    // Find or create admin user
    $admin = User::where('email', 'admin@test.com')->first();
    
    if (!$admin) {
        echo "1. Creating new admin user...\n";
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('123456'),
            'role' => 'admin'
        ]);
        echo "   ✅ New admin user created\n";
    } else {
        echo "1. Updating existing admin user...\n";
        $admin->update([
            'name' => 'Admin',
            'password' => bcrypt('123456'),
            'role' => 'admin'
        ]);
        echo "   ✅ Admin user updated\n";
    }

    // Test the credentials
    echo "\n2. Testing credentials...\n";
    $testUser = User::where('email', 'admin@test.com')->first();
    if ($testUser && password_verify('123456', $testUser->password)) {
        echo "   ✅ Password verification: SUCCESS\n";
        echo "   ✅ User ID: " . $testUser->id . "\n";
        echo "   ✅ Role: " . $testUser->role . "\n";
    } else {
        echo "   ❌ Password verification: FAILED\n";
    }

    echo "\n=== Login Credentials ===\n";
    echo "Email:    admin@test.com\n";
    echo "Password: 123456\n";
    echo "Role:     admin\n\n";
    
    echo "✅ Ready to login!\n";
    echo "Go to: http://localhost:3000\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}



















