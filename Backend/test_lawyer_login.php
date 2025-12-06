<?php

// Test script to verify lawyer login with role-based response

$baseUrl = 'http://localhost:8000/api';

// Test lawyer login
$loginData = [
    'login' => 'lawyer',
    'password' => 'password'
];

echo "Testing lawyer login...\n";
echo "======================\n\n";

$ch = curl_init($baseUrl . '/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$data = json_decode($response, true);

curl_close($ch);

echo "HTTP Status: $httpCode\n\n";
echo "Response:\n";
print_r($data);

if (isset($data['user'])) {
    echo "\n✅ SUCCESS: Login response includes user data\n";
    echo "User Role: " . $data['user']['role'] . "\n";
    echo "Expected Redirect: ";
    
    switch($data['user']['role']) {
        case 'lawyer':
            echo "/legal (Legal Management System)\n";
            break;
        case 'admin':
            echo "/dashboard (Admin Dashboard)\n";
            break;
        default:
            echo "/dashboard (Default)\n";
    }
} else {
    echo "\n❌ ERROR: Login response does not include user data\n";
}
