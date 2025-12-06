<?php

echo "=== Testing Login with Token ===\n\n";

// Test login and get token
$loginData = [
    'email' => 'admin@test.com',
    'password' => '123456'
];

$loginUrl = 'http://127.0.0.1:8000/api/login';
$loginOptions = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($loginData)
    ]
];

echo "1. Testing login...\n";
$loginContext = stream_context_create($loginOptions);
$loginResult = file_get_contents($loginUrl, false, $loginContext);

if ($loginResult === FALSE) {
    echo "   ❌ Login failed\n";
    exit(1);
}

$loginResponse = json_decode($loginResult, true);
if (isset($loginResponse['access_token'])) {
    echo "   ✅ Login successful\n";
    $token = $loginResponse['access_token'];
    echo "   ✅ Token: " . substr($token, 0, 20) . "...\n";
} else {
    echo "   ❌ No token received\n";
    echo "   Response: " . $loginResult . "\n";
    exit(1);
}

// Test IT assets endpoint with token
echo "\n2. Testing IT assets endpoint with token...\n";
$assetsUrl = 'http://127.0.0.1:8000/api/it-assets';
$assetsOptions = [
    'http' => [
        'header' => "Authorization: Bearer $token\r\nContent-type: application/json\r\n",
        'method' => 'GET'
    ]
];

$assetsContext = stream_context_create($assetsOptions);
$assetsResult = file_get_contents($assetsUrl, false, $assetsContext);

if ($assetsResult === FALSE) {
    echo "   ❌ IT assets request failed\n";
} else {
    $assetsResponse = json_decode($assetsResult, true);
    if (isset($assetsResponse['success']) && $assetsResponse['success']) {
        echo "   ✅ IT assets request successful\n";
        echo "   ✅ Number of assets: " . count($assetsResponse['data']) . "\n";
    } else {
        echo "   ❌ IT assets request failed\n";
        echo "   Response: " . $assetsResult . "\n";
    }
}

echo "\n=== Test Complete ===\n";



















