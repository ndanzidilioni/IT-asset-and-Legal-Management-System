<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../db-config.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Get database connection
$db = DatabaseConfig::getConnection(DatabaseConfig::COURT_DB);

$hearings = [
    [
        'id' => 1,
        'case_number' => 'CASE-2024-001',
        'type' => 'Court Hearing',
        'date' => '2024-11-03',
        'time' => '10:00 AM',
        'court' => 'Downtown District Court',
        'judge' => 'Hon. Maria Rodriguez',
        'room' => 'Courtroom 3A',
        'status' => 'Scheduled',
        'assigned_lawyer' => 'Sarah Johnson'
    ],
    [
        'id' => 2,
        'case_number' => 'CASE-2024-002',
        'type' => 'Pre-Trial Conference',
        'date' => '2024-10-26',
        'time' => '2:00 PM',
        'court' => 'Family Court',
        'judge' => 'Hon. James Anderson',
        'room' => 'Courtroom 1B',
        'status' => 'Urgent',
        'assigned_lawyer' => 'Mike Davis'
    ]
];

$deadlines = [
    ['case' => 'CASE-2024-001', 'type' => 'Motion Due', 'date' => '2024-10-25', 'priority' => 'High'],
    ['case' => 'CASE-2024-002', 'type' => 'Document Filing', 'date' => '2024-10-28', 'priority' => 'Urgent'],
    ['case' => 'CASE-2024-003', 'type' => 'Discovery Deadline', 'date' => '2024-11-15', 'priority' => 'Medium']
];

switch ($uri) {
    case '/':
    case '/health':
        echo json_encode([
            'status' => 'healthy',
            'service' => 'Court Scheduling Service',
            'port' => 8011,
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
        break;

    case '/api/hearings':
        echo json_encode([
            'success' => true,
            'data' => $hearings,
            'total' => count($hearings),
            'message' => 'Hearings retrieved successfully'
        ]);
        break;

    case '/api/deadlines':
        echo json_encode([
            'success' => true,
            'data' => $deadlines,
            'total' => count($deadlines)
        ]);
        break;

    case '/api/court-schedules':
        if ($method === 'GET') {
            try {
                if ($db) {
                    $stmt = $db->query("SELECT * FROM court_schedules ORDER BY event_date DESC, event_time DESC");
                    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode([
                        'success' => true,
                        'data' => $schedules,
                        'total' => count($schedules),
                        'message' => 'Court schedules retrieved successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'data' => $hearings,
                        'total' => count($hearings),
                        'message' => 'Court schedules retrieved successfully (sample data)'
                    ]);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        } elseif ($method === 'POST') {
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Validate required fields
                if (empty($data['title']) || empty($data['event_date'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Title and event date are required']);
                    exit;
                }
                
                if ($db) {
                    $stmt = $db->prepare(
                        "INSERT INTO court_schedules (title, event_date, event_time, duration_minutes, location, 
                         description, notes, status, case_id, client_name, attendees, created_by)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                    );
                    
                    $stmt->execute([
                        $data['title'],
                        $data['event_date'],
                        $data['event_time'] ?? null,
                        $data['duration_minutes'] ?? 60,
                        $data['location'] ?? null,
                        $data['description'] ?? null,
                        $data['notes'] ?? null,
                        $data['status'] ?? 'Scheduled',
                        $data['case_id'] ?? null,
                        $data['client_name'] ?? null,
                        isset($data['attendees']) ? json_encode($data['attendees']) : null,
                        $data['created_by'] ?? 1
                    ]);
                    
                    $scheduleId = $db->lastInsertId();
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Court schedule created successfully',
                        'data' => ['id' => $scheduleId]
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Court schedule created successfully (test mode)',
                        'data' => ['id' => rand(100, 999)]
                    ]);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        break;

    case '/api/court-schedules/today':
        try {
            if ($db) {
                $today = date('Y-m-d');
                $stmt = $db->prepare("SELECT * FROM court_schedules WHERE event_date = ? ORDER BY event_time");
                $stmt->execute([$today]);
                $todaySchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'date' => $today,
                        'hearings_count' => count($todaySchedules),
                        'hearings' => $todaySchedules
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'date' => date('Y-m-d'),
                        'hearings_count' => 3,
                        'deadlines_count' => 2,
                        'hearings' => array_slice($hearings, 0, 2)
                    ]
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        if (preg_match('/\/api\/court-schedules\/(\d+)/', $uri, $matches)) {
            $id = (int)$matches[1];
            
            if ($method === 'GET') {
                try {
                    if ($db) {
                        $stmt = $db->prepare("SELECT * FROM court_schedules WHERE id = ?");
                        $stmt->execute([$id]);
                        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($schedule) {
                            echo json_encode(['success' => true, 'data' => $schedule]);
                        } else {
                            http_response_code(404);
                            echo json_encode(['success' => false, 'message' => 'Schedule not found']);
                        }
                    } else {
                        $schedule = array_filter($hearings, fn($h) => $h['id'] === $id);
                        if ($schedule) {
                            echo json_encode(['success' => true, 'data' => array_values($schedule)[0]]);
                        } else {
                            http_response_code(404);
                            echo json_encode(['success' => false, 'message' => 'Schedule not found']);
                        }
                    }
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
            } elseif ($method === 'PUT') {
                try {
                    $data = json_decode(file_get_contents('php://input'), true);
                    
                    if ($db) {
                        $stmt = $db->prepare(
                            "UPDATE court_schedules SET title = ?, event_date = ?, event_time = ?, 
                             duration_minutes = ?, location = ?, description = ?, notes = ?, status = ?, 
                             case_id = ?, client_name = ?, attendees = ?
                             WHERE id = ?"
                        );
                        
                        $stmt->execute([
                            $data['title'],
                            $data['event_date'],
                            $data['event_time'] ?? null,
                            $data['duration_minutes'] ?? 60,
                            $data['location'] ?? null,
                            $data['description'] ?? null,
                            $data['notes'] ?? null,
                            $data['status'] ?? 'Scheduled',
                            $data['case_id'] ?? null,
                            $data['client_name'] ?? null,
                            isset($data['attendees']) ? json_encode($data['attendees']) : null,
                            $id
                        ]);
                        
                        echo json_encode(['success' => true, 'message' => 'Schedule updated successfully']);
                    } else {
                        echo json_encode(['success' => true, 'message' => 'Schedule updated (test mode)']);
                    }
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
            } elseif ($method === 'DELETE') {
                try {
                    if ($db) {
                        $stmt = $db->prepare("DELETE FROM court_schedules WHERE id = ?");
                        $stmt->execute([$id]);
                        
                        if ($stmt->rowCount() > 0) {
                            echo json_encode(['success' => true, 'message' => 'Schedule deleted successfully']);
                        } else {
                            http_response_code(404);
                            echo json_encode(['success' => false, 'message' => 'Schedule not found']);
                        }
                    } else {
                        echo json_encode(['success' => true, 'message' => 'Schedule deleted (test mode)']);
                    }
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
            }
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Endpoint not found',
                'available_endpoints' => [
                    'GET /health',
                    'GET /api/hearings',
                    'POST /api/hearings',
                    'GET /api/deadlines',
                    'GET /api/court-schedules',
                    'POST /api/court-schedules',
                    'GET /api/court-schedules/{id}',
                    'PUT /api/court-schedules/{id}',
                    'DELETE /api/court-schedules/{id}',
                    'GET /api/court-schedules/today'
                ]
            ]);
        }
        break;
}
