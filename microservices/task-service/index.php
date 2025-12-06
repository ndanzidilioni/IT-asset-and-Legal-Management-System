<?php
header('Content-Type: application/json');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/health') {
    http_response_code(200);
    echo json_encode([
        'status' => 'healthy',
        'service' => 'task-service',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

if ($uri === '/api/tasks') {
    http_response_code(200);
    echo json_encode([
        'service' => 'task-service',
        'message' => 'Task service is running',
        'tasks' => []
    ]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
