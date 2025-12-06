<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use Illuminate\Foundation\Application;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Quick Password Reset ===\n\n";

// Reset password for admin@system.com (from setup script)
$admin = User::where('email', 'admin@system.com')->first();
if ($admin) {
    $admin->update(['password' => bcrypt('123456')]);
    echo "✅ admin@system.com password reset to: 123456\n";
} else {
    echo "❌ admin@system.com not found\n";
}

// Reset password for admin@test.com (from our previous work)
$admin2 = User::where('email', 'admin@test.com')->first();
if ($admin2) {
    $admin2->update(['password' => bcrypt('123456')]);
    echo "✅ admin@test.com password reset to: 123456\n";
} else {
    echo "❌ admin@test.com not found\n";
}

echo "\n=== Try These Login Credentials ===\n";
echo "Email: admin@system.com\n";
echo "Password: 123456\n\n";
echo "OR\n\n";
echo "Email: admin@test.com\n";
echo "Password: 123456\n\n";

echo "✅ Password reset complete!\n";
echo "Go to: http://localhost:3000\n";
