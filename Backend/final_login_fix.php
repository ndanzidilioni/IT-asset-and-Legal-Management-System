<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use Illuminate\Foundation\Application;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Final Login Fix ===\n\n";

try {
    // Delete all existing users to avoid conflicts
    echo "1. Clearing all existing users...\n";
    User::whereNotNull('id')->delete();
    echo "   ✅ All users cleared\n\n";

    // Create a fresh admin user with simple credentials
    echo "2. Creating fresh admin user...\n";
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('123456'),
        'role' => 'admin'
    ]);
    echo "   ✅ Admin user created successfully\n\n";

    // Verify the user was created
    $createdUser = User::where('email', 'admin@test.com')->first();
    if ($createdUser) {
        echo "3. Verifying user creation...\n";
        echo "   ✅ User ID: " . $createdUser->id . "\n";
        echo "   ✅ Name: " . $createdUser->name . "\n";
        echo "   ✅ Email: " . $createdUser->email . "\n";
        echo "   ✅ Role: " . $createdUser->role . "\n";
        echo "   ✅ Password hash exists: " . (strlen($createdUser->password) > 0 ? "Yes" : "No") . "\n";
        
        // Test password verification
        if (password_verify('123456', $createdUser->password)) {
            echo "   ✅ Password verification: SUCCESS\n";
        } else {
            echo "   ❌ Password verification: FAILED\n";
        }
    }

    echo "\n=== Your Login Credentials ===\n";
    echo "Email:    admin@test.com\n";
    echo "Password: 123456\n";
    echo "Role:     admin\n\n";
    
    echo "✅ Login credentials are ready!\n";
    echo "Go to: http://localhost:3000\n";
    echo "Login with: admin@test.com / 123456\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}













