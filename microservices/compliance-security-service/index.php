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

$auditLogs = [
    ['time' => '10:45 AM', 'user' => 'Sarah Johnson', 'action' => 'Viewed', 'resource' => 'CASE-001', 'ip' => '192.168.1.100'],
    ['time' => '10:30 AM', 'user' => 'Mike Davis', 'action' => 'Updated', 'resource' => 'CASE-002', 'ip' => '192.168.1.101'],
    ['time' => '10:15 AM', 'user' => 'John Smith', 'action' => 'Signed Document', 'resource' => 'DOC-456', 'ip' => '192.168.1.102'],
    ['time' => '09:50 AM', 'user' => 'Lisa Chen', 'action' => 'Created Case', 'resource' => 'CASE-099', 'ip' => '192.168.1.103']
];

switch ($uri) {
    case '/':
    case '/health':
        echo json_encode([
            'status' => 'healthy',
            'service' => 'Compliance & Security Service',
            'port' => 8013,
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'security_status' => 'All systems secure'
        ]);
        break;

    case '/api/audit-logs':
        echo json_encode([
            'success' => true,
            'data' => $auditLogs,
            'total' => count($auditLogs)
        ]);
        break;

    case '/api/compliance/status':
        echo json_encode([
            'success' => true,
            'data' => [
                'gdpr_compliant' => true,
                'data_encryption_active' => true,
                'backup_status' => 'Completed 2 hours ago',
                'security_incidents' => 0,
                'unauthorized_access_attempts' => 0,
                'last_security_audit' => '2024-10-20',
                'compliance_checks' => [
                    'GDPR Compliance' => 'Pass',
                    'Attorney-Client Privilege' => 'Protected',
                    'Data Retention Policy' => 'Active',
                    'Access Controls' => 'Configured'
                ]
            ]
        ]);
        break;

    case '/api/security/alerts':
        echo json_encode([
            'success' => true,
            'data' => [],
            'message' => 'No security alerts. All systems secure.'
        ]);
        break;

    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found',
            'available_endpoints' => [
                'GET /health',
                'GET /api/audit-logs',
                'GET /api/compliance/status',
                'POST /api/compliance/check',
                'GET /api/security/alerts'
            ]
        ]);
}
