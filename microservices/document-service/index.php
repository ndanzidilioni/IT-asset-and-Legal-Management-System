<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$documents = [
    [
        'id' => 1,
        'name' => 'Motion to Dismiss',
        'case_number' => 'CASE-2024-001',
        'type' => 'PDF',
        'size' => '2.4 MB',
        'created_by' => 'Sarah Johnson',
        'created_at' => '2024-10-24',
        'status' => 'Signed',
        'version' => '2.1',
        'category' => 'Pleadings'
    ],
    [
        'id' => 2,
        'name' => 'Client Agreement',
        'case_number' => 'CASE-2024-001',
        'type' => 'PDF',
        'size' => '1.8 MB',
        'created_by' => 'Mike Davis',
        'created_at' => '2024-10-20',
        'status' => 'Signed',
        'version' => '1.0',
        'category' => 'Contracts'
    ],
    [
        'id' => 3,
        'name' => 'Evidence Photo',
        'case_number' => 'CASE-2024-002',
        'type' => 'JPG',
        'size' => '3.2 MB',
        'created_by' => 'Lisa Chen',
        'created_at' => '2024-10-18',
        'status' => 'Approved',
        'version' => '1.0',
        'category' => 'Evidence'
    ]
];

switch ($uri) {
    case '/':
    case '/health':
        echo json_encode([
            'status' => 'healthy',
            'service' => 'Document Management Service',
            'port' => 8010,
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'features' => ['upload', 'download', 'e-signature', 'version-control', 'OCR']
        ]);
        break;

    case '/api/documents':
        echo json_encode([
            'success' => true,
            'data' => $documents,
            'total' => count($documents),
            'message' => 'Documents retrieved successfully'
        ]);
        break;

    case '/api/documents/statistics':
        echo json_encode([
            'success' => true,
            'data' => [
                'total_documents' => 1247,
                'pending_signature' => 18,
                'signed_today' => 12,
                'uploaded_this_week' => 45,
                'storage_used' => '24.5 GB',
                'by_category' => [
                    'Contracts' => 342,
                    'Pleadings' => 289,
                    'Evidence' => 456,
                    'Correspondence' => 160
                ]
            ]
        ]);
        break;

    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found',
            'available_endpoints' => [
                'GET /health',
                'GET /api/documents',
                'POST /api/documents',
                'GET /api/documents/{id}',
                'POST /api/documents/{id}/sign',
                'GET /api/documents/statistics'
            ]
        ]);
}
