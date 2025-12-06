<?php

// Simple test to check if the API endpoint is accessible
echo "=== API Endpoint Test ===\n\n";

// Test the login endpoint directly
$url = 'http://127.0.0.1:8000/api/login';
$data = [
    'email' => 'admin@test.com',
    'password' => '123456'
];

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    echo "❌ API endpoint not accessible\n";
    echo "Make sure the Laravel server is running on http://127.0.0.1:8000\n";
} else {
    echo "✅ API endpoint is accessible\n";
    $response = json_decode($result, true);
    if (isset($response['access_token'])) {
        echo "✅ Login successful\n";
        echo "✅ Token received: " . substr($response['access_token'], 0, 20) . "...\n";
    } else {
        echo "❌ Login failed\n";
        echo "Response: " . $result . "\n";
    }
}

echo "\n=== Test Complete ===\n";



















