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

$invoices = [
    [
        'id' => 101,
        'invoice_number' => 'INV-2024-101',
        'client_name' => 'John Smith',
        'amount' => 5250.00,
        'status' => 'Pending',
        'due_date' => '2024-11-01',
        'created_date' => '2024-10-15',
        'billable_hours' => 21.0
    ],
    [
        'id' => 100,
        'invoice_number' => 'INV-2024-100',
        'client_name' => 'Jane Doe',
        'amount' => 3800.00,
        'status' => 'Paid',
        'due_date' => '2024-10-20',
        'created_date' => '2024-10-01',
        'billable_hours' => 15.2
    ]
];

switch ($uri) {
    case '/':
    case '/health':
        echo json_encode([
            'status' => 'healthy',
            'service' => 'Billing & Finance Service',
            'port' => 8016,
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
        break;

    case '/api/invoices':
        echo json_encode([
            'success' => true,
            'data' => $invoices,
            'total' => count($invoices)
        ]);
        break;

    case '/api/billing/summary':
        echo json_encode([
            'success' => true,
            'data' => [
                'monthly_revenue' => 125000.00,
                'outstanding_balance' => 45000.00,
                'collected_this_month' => 80000.00,
                'total_billable_hours' => 320.5,
                'average_hourly_rate' => 250.00,
                'invoices_sent' => 48,
                'invoices_paid' => 35,
                'invoices_overdue' => 8
            ]
        ]);
        break;

    case '/api/billing/time-entries':
        echo json_encode([
            'success' => true,
            'data' => [
                ['lawyer' => 'Sarah Johnson', 'case' => 'CASE-001', 'hours' => 3.5, 'description' => 'Legal research', 'date' => '2024-10-24'],
                ['lawyer' => 'Mike Davis', 'case' => 'CASE-002', 'hours' => 2.0, 'description' => 'Client meeting', 'date' => '2024-10-24'],
                ['lawyer' => 'Lisa Chen', 'case' => 'CASE-003', 'hours' => 1.5, 'description' => 'Document review', 'date' => '2024-10-24']
            ],
            'total_today' => 7.0
        ]);
        break;

    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found',
            'available_endpoints' => [
                'GET /health',
                'GET /api/invoices',
                'POST /api/invoices',
                'GET /api/billing/summary',
                'GET /api/billing/time-entries',
                'POST /api/expenses'
            ]
        ]);
}
