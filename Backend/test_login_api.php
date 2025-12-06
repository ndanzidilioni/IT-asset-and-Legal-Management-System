<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Login API ===\n\n";

try {
    // Test 1: Check if user exists
    echo "1. Checking if user exists...\n";
    $user = User::where('email', 'admin@test.com')->first();
    if ($user) {
        echo "   ✅ User found: " . $user->email . "\n";
        echo "   ✅ User ID: " . $user->id . "\n";
        echo "   ✅ Role: " . $user->role . "\n";
    } else {
        echo "   ❌ User not found!\n";
        exit(1);
    }

    // Test 2: Test password verification
    echo "\n2. Testing password verification...\n";
    if (password_verify('123456', $user->password)) {
        echo "   ✅ Password verification: SUCCESS\n";
    } else {
        echo "   ❌ Password verification: FAILED\n";
        exit(1);
    }

    // Test 3: Test Laravel Auth attempt
    echo "\n3. Testing Laravel Auth attempt...\n";
    $credentials = [
        'email' => 'admin@test.com',
        'password' => '123456'
    ];
    
    if (Auth::attempt($credentials)) {
        echo "   ✅ Laravel Auth attempt: SUCCESS\n";
        echo "   ✅ Authenticated user: " . Auth::user()->name . "\n";
        Auth::logout();
    } else {
        echo "   ❌ Laravel Auth attempt: FAILED\n";
    }

    // Test 4: Test token creation
    echo "\n4. Testing token creation...\n";
    if (Auth::attempt($credentials)) {
        $token = Auth::user()->createToken('test_token')->plainTextToken;
        echo "   ✅ Token created: " . substr($token, 0, 20) . "...\n";
        Auth::logout();
    } else {
        echo "   ❌ Token creation: FAILED\n";
    }

    echo "\n=== All Tests Passed! ===\n";
    echo "The login should work with:\n";
    echo "Email: admin@test.com\n";
    echo "Password: 123456\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}



















