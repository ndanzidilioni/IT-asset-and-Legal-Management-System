<?php
/**
 * Court Schedules API Endpoint
 * Proxy to court scheduling microservice
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

error_log("court-schedules-api.php accessed with method: " . $_SERVER['REQUEST_METHOD']);

// Extract the path from query parameters or REQUEST_URI
$path = $_GET['path'] ?? '';

// If no path in query, try to extract from REQUEST_URI
if (empty($path)) {
    $requestUri = $_SERVER['REQUEST_URI'];
    $basePath = '/court-schedules-api.php';
    
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

// Route to court scheduling microservice
$courtServiceUrl = 'http://localhost:8011'; // Court scheduling service port

function forwardToCourtService($path, $method, $data = null) {
    global $courtServiceUrl;
    
    // Map paths
    $servicePath = '';
    
    if ($path === '/court-schedules/today') {
        $servicePath = '/api/court-schedules/today';
    } elseif ($path === '/court-schedules/hearings') {
        $servicePath = '/api/hearings';
    } elseif ($path === '/court-schedules/deadlines') {
        $servicePath = '/api/deadlines';
    } elseif (preg_match('/^\/court-schedules\/(\d+)$/', $path, $matches)) {
        $servicePath = '/api/court-schedules/' . $matches[1];
    } elseif ($path === '/' || $path === '' || $path === '/court-schedules') {
        $servicePath = '/api/court-schedules';
    } else {
        $servicePath = '/api/court-schedules';
    }
    
    $url = $courtServiceUrl . $servicePath;
    
    // Handle URL parameters for PUT/DELETE requests
    if (in_array($method, ['PUT', 'DELETE']) && preg_match('/^\/court-schedules\/(\d+)$/', $path, $matches)) {
        $url .= '?id=' . $matches[1];
    }
    
    error_log("Forwarding to: $url (Method: $method)");
    
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
        error_log("Court service error: $curlError");
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Service unavailable: ' . $curlError,
            'details' => 'Could not connect to court scheduling service'
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
    error_log("Request data: " . $requestData);
}

// Forward the request
forwardToCourtService($path, $method, $requestData);
?>
