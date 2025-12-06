<?php
/**
 * Client API Endpoint - Simplified for Testing
 * Direct proxy to client microservice without authentication
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_log("clients-api.php accessed with method: " . $_SERVER['REQUEST_METHOD']);

// Extract the path from query parameters or REQUEST_URI
$path = $_GET['path'] ?? '';

// If no path in query, try to extract from REQUEST_URI
if (empty($path)) {
    $requestUri = $_SERVER['REQUEST_URI'];
    $basePath = '/clients-api.php';
    
    if (strpos($requestUri, $basePath) !== false) {
        $fullPath = substr($requestUri, strpos($requestUri, $basePath) + strlen($basePath));
        // Remove query string
        if (($pos = strpos($fullPath, '?')) !== false) {
            $path = substr($fullPath, 0, $pos);
        } else {
            $path = $fullPath;
        }
    }
}

error_log("Extracted path: " . $path);

$method = $_SERVER['REQUEST_METHOD'];

// Skip authentication for now to fix the 401 error
// TODO: Implement proper authentication later
$user = null;

// Route to appropriate microservice endpoint
$clientServiceUrl = 'http://localhost:8009'; // Client service port

function forwardToClientService($path, $method, $data = null) {
    global $clientServiceUrl;
    
    // Map paths
    $servicePath = '';
    
    if ($path === '/clients/statistics') {
        $servicePath = '/api/clients/statistics';
    } elseif (preg_match('/^\/clients\/(\d+)$/', $path, $matches)) {
        $servicePath = '/api/clients/' . $matches[1];
    } elseif ($path === '/' || $path === '') {
        $servicePath = '/api/clients';
    } else {
        $servicePath = '/api/clients';
    }
    
    $url = $clientServiceUrl . $servicePath;
    
    // Handle URL parameters for PUT requests
    if ($method === 'PUT' && preg_match('/^\/clients\/(\d+)$/', $path, $matches)) {
        $url .= '?id=' . $matches[1];
        $servicePath = '/api/clients';
        $url = $clientServiceUrl . $servicePath . '?id=' . $matches[1];
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($data && in_array($method, ['POST', 'PUT'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($data) ? $data : json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(is_string($data) ? $data : json_encode($data))
        ]);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Service unavailable: ' . $curlError,
            'details' => 'Could not connect to client service'
        ]);
        return;
    }
    
    http_response_code($httpCode);
    echo $response;
}

// Get request data for POST/PUT
$requestData = null;
if (in_array($method, ['POST', 'PUT'])) {
    $requestData = file_get_contents('php://input');
}

// Forward the request
forwardToClientService($path, $method, $requestData);
?>