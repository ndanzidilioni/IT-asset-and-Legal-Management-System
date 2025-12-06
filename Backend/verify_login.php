<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use Illuminate\Foundation\Application;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Verifying Login Credentials ===\n\n";

$user = User::where('email', 'admin@test.com')->first();
if ($user) {
    echo "✅ User found: " . $user->name . " (" . $user->email . ")\n";
    echo "✅ Role: " . $user->role . "\n";
    echo "✅ Password hash exists: " . (strlen($user->password) > 0 ? "Yes" : "No") . "\n";
    
    // Test password verification
    if (password_verify('admin123', $user->password)) {
        echo "✅ Password verification: SUCCESS\n";
    } else {
        echo "❌ Password verification: FAILED\n";
    }
} else {
    echo "❌ User not found!\n";
}

echo "\n=== All Users in Database ===\n";
$users = User::all(['name', 'email', 'role']);
foreach ($users as $user) {
    echo "- " . $user->name . " (" . $user->email . ") - " . $user->role . "\n";
}






