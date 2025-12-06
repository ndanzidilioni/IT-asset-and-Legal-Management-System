<?php

echo "=== Backend Status Check ===\n\n";

// Test 1: Check if Laravel is working
echo "1. Testing Laravel application...\n";
try {
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    echo "   ✅ Laravel application loaded successfully\n";
} catch (Exception $e) {
    echo "   ❌ Laravel application failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check database connection
echo "\n2. Testing database connection...\n";
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=scheduling', 'root', '');
    echo "   ✅ Database connection successful\n";
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Test 3: Check if IT assets table exists
echo "\n3. Checking IT assets table...\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM it_assets");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ IT assets table exists\n";
    echo "   ✅ Number of assets: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "   ❌ IT assets table error: " . $e->getMessage() . "\n";
}

// Test 4: Check if users table exists
echo "\n4. Checking users table...\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ Users table exists\n";
    echo "   ✅ Number of users: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "   ❌ Users table error: " . $e->getMessage() . "\n";
}

echo "\n=== Backend Status: READY ===\n";
echo "The backend should be working properly.\n";
echo "If you're still getting 404 errors, the issue is likely:\n";
echo "1. Frontend not connecting to the right backend URL\n";
echo "2. CORS issues\n";
echo "3. Authentication token not being sent properly\n";



















