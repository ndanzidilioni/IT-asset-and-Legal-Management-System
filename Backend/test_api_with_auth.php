<?php

// Test script to verify API works with authentication

$baseUrl = 'http://localhost:8000/api';

// First, login to get a token
$loginData = [
    'login' => 'admin',
    'password' => 'password'
];

$ch = curl_init($baseUrl . '/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$loginResponse = curl_exec($ch);
$loginData = json_decode($loginResponse, true);

echo "Login Response:\n";
print_r($loginData);

if (isset($loginData['access_token'])) {
    $token = $loginData['access_token'];
    echo "\nToken obtained: " . substr($token, 0, 20) . "...\n\n";
    
    // Now try to access the statistics endpoint
    $ch2 = curl_init($baseUrl . '/it-assets/statistics');
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    
    $statsResponse = curl_exec($ch2);
    $httpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    
    echo "Statistics Response (HTTP $httpCode):\n";
    echo $statsResponse . "\n";
    
    curl_close($ch2);
} else {
    echo "\nFailed to obtain token\n";
}

curl_close($ch);
