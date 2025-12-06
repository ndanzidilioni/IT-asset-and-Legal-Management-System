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

switch ($uri) {
    case '/':
    case '/health':
        echo json_encode([
            'status' => 'healthy',
            'service' => 'Legal Analytics Service',
            'port' => 8014,
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
        break;

    case '/api/legal-analytics':
    case '/api/legal-analytics/dashboard':
        echo json_encode([
            'success' => true,
            'data' => [
                'cases_by_status' => [
                    'Active' => 45,
                    'Pending' => 18,
                    'Completed' => 12,
                    'On Hold' => 5,
                    'Closed' => 87
                ],
                'cases_by_type' => [
                    'Civil' => 35,
                    'Criminal' => 18,
                    'Family' => 25,
                    'Corporate' => 22,
                    'Immigration' => 12,
                    'Other' => 8
                ],
                'revenue_trends' => [
                    'January' => 98000,
                    'February' => 105000,
                    'March' => 112000,
                    'April' => 108000,
                    'May' => 125000,
                    'June' => 118000
                ],
                'lawyer_performance' => [
                    ['name' => 'Sarah Johnson', 'hours' => 245, 'cases' => 15, 'revenue' => 98000],
                    ['name' => 'Mike Davis', 'hours' => 198, 'cases' => 12, 'revenue' => 79000],
                    ['name' => 'Lisa Chen', 'hours' => 167, 'cases' => 10, 'revenue' => 67000]
                ],
                'upcoming_hearings' => 12,
                'overdue_tasks' => 3,
                'client_satisfaction' => 4.7
            ]
        ]);
        break;

    case '/api/legal-analytics/reports':
        echo json_encode([
            'success' => true,
            'data' => [
                'monthly_summary' => [
                    'cases_opened' => 12,
                    'cases_closed' => 8,
                    'revenue' => 125000,
                    'billable_hours' => 520,
                    'new_clients' => 7
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
                'GET /api/legal-analytics',
                'GET /api/legal-analytics/dashboard',
                'POST /api/legal-analytics/report'
            ]
        ]);
}
