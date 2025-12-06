<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../db-config.php';
require_once __DIR__ . '/../auth-middleware.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Only set JSON content type for non-file routes
if (!preg_match('/\/(upload|download)/', $uri)) {
    header('Content-Type: application/json');
}

// Get database connection
$db = DatabaseConfig::getConnection(DatabaseConfig::CASE_DB);

if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Initialize auth with compliance database for audit logging
$complianceDb = DatabaseConfig::getConnection(DatabaseConfig::COMPLIANCE_DB);
AuthMiddleware::init($complianceDb);

// Handle file download FIRST (before other routes)
if (preg_match('/\/api\/cases\/(\d+)\/download/', $uri, $matches)) {
    $caseId = $matches[1];
    try {
        $stmt = $db->prepare("SELECT document_path, document_name FROM cases WHERE id = ?");
        $stmt->execute([$caseId]);
        $case = $stmt->fetch();
        
        if ($case && $case['document_path']) {
            $filePath = __DIR__ . '/' . $case['document_path'];
            if (file_exists($filePath)) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $case['document_name'] . '"');
                header('Content-Length: ' . filesize($filePath));
                readfile($filePath);
                exit;
            }
        }
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Document not found']);
    } catch (Exception $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle file upload
if (preg_match('/\/api\/cases\/(\d+)\/upload/', $uri, $matches)) {
    header('Content-Type: application/json');
    $caseId = $matches[1];
    
    if ($method === 'POST') {
        try {
            if (!isset($_FILES['document'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No file uploaded']);
                exit;
            }
            
            $file = $_FILES['document'];
            
            // Check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Upload error: ' . $file['error']]);
                exit;
            }
            
            $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
                           'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            
            if (!in_array($file['type'], $allowedTypes)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid file type: ' . $file['type']]);
                exit;
            }
            
            if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'File too large (max 10MB)']);
                exit;
            }
            
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'case_' . $caseId . '_' . time() . '.' . $ext;
            $uploadPath = 'uploads/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], __DIR__ . '/' . $uploadPath)) {
                $stmt = $db->prepare("UPDATE cases SET document_path = ?, document_name = ? WHERE id = ?");
                $stmt->execute([$uploadPath, $file['name'], $caseId]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Document uploaded successfully',
                    'data' => ['document_name' => $file['name'], 'document_path' => $uploadPath]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to save file']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed. Use POST.']);
    }
    exit;
}

switch ($uri) {
    case '/':
    case '/health':
        echo json_encode([
            'status' => 'healthy',
            'service' => 'Case Management Service',
            'port' => 8008,
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
        break;

    case '/api/cases':
        // Temporarily disable auth for testing - REMOVE IN PRODUCTION
        // $user = AuthMiddleware::requireAuth();
        // if (!$user) exit;
        
        // Mock user for now
        $user = (object)[
            'user_id' => 1,
            'email' => 'admin@test.com',
            'role' => 'admin',
            'name' => 'Admin'
        ];
        
        if ($method === 'GET') {
            try {
                // Log access
                AuthMiddleware::logAccess($user, 'VIEW', 'cases');
                
                // Get cases based on role
                if ($user->role === 'client') {
                    // Clients only see their own cases
                    $stmt = $db->prepare("SELECT * FROM cases WHERE client_name = ? ORDER BY created_at DESC");
                    $stmt->execute([$user->name]);
                } else {
                    // Lawyers, legal assistants, admins see all cases
                    $stmt = $db->query("SELECT * FROM cases ORDER BY created_at DESC");
                }
                
                $cases = $stmt->fetchAll();
                echo json_encode([
                    'success' => true,
                    'data' => $cases,
                    'total' => count($cases),
                    'message' => 'Cases retrieved successfully'
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        } elseif ($method === 'POST') {
            // Only lawyers and admins can create cases
            if (!AuthMiddleware::authorize($user, 'lawyer')) {
                exit;
            }
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Validate required fields
                if (empty($data['case_number']) || empty($data['parties']) || empty($data['nature_of_case'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Case number, parties, and nature of case are required']);
                    exit;
                }
                
                $stmt = $db->prepare(
                    "INSERT INTO cases (
                        case_number, parties, nature_of_case, amount_in_claim, date_filed, 
                        current_status, next_hearing_date, any_appeal, remarks, assigned_lawyer, created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                
                $stmt->execute([
                    $data['case_number'],
                    $data['parties'],
                    $data['nature_of_case'],
                    $data['amount_in_claim'] ?? null,
                    $data['date_filed'] ?? null,
                    $data['current_status'] ?? 'Pending Hearing',
                    $data['next_hearing_date'] ?? null,
                    $data['any_appeal'] ?? 'No',
                    $data['remarks'] ?? null,
                    $data['assigned_lawyer'] ?? null,
                    $user->name ?? $user->email
                ]);
                
                $caseId = $db->lastInsertId();
                
                // Log activity
                AuthMiddleware::logAccess($user, 'CREATE', "case-{$data['case_number']}");
                $activityStmt = $db->prepare(
                    "INSERT INTO case_activities (case_id, activity_type, description, created_by) VALUES (?, ?, ?, ?)"
                );
                $activityStmt->execute([
                    $caseId,
                    'Case Created',
                    "Case {$data['case_number']} created",
                    $user->name ?? $user->email
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Case created successfully',
                    'data' => [
                        'id' => $caseId,
                        'case_number' => $data['case_number']
                    ]
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        } elseif ($method === 'PUT') {
            // Only lawyers and admins can update cases
            if (!$user->role || !in_array($user->role, ['admin', 'lawyer', 'developer'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
                exit;
            }
            
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $id = $_GET['id'] ?? null;
                
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Case ID required']);
                    exit;
                }
                
                $stmt = $db->prepare(
                    "UPDATE cases SET 
                        case_number = ?, parties = ?, nature_of_case = ?, amount_in_claim = ?,
                        date_filed = ?, current_status = ?, next_hearing_date = ?,
                        any_appeal = ?, remarks = ?, assigned_lawyer = ?
                    WHERE id = ?"
                );
                
                $stmt->execute([
                    $data['case_number'],
                    $data['parties'],
                    $data['nature_of_case'],
                    $data['amount_in_claim'] ?? null,
                    $data['date_filed'] ?? null,
                    $data['current_status'] ?? 'Pending Hearing',
                    $data['next_hearing_date'] ?? null,
                    $data['any_appeal'] ?? 'No',
                    $data['remarks'] ?? null,
                    $data['assigned_lawyer'] ?? null,
                    $id
                ]);
                
                // Log activity
                $activityStmt = $db->prepare(
                    "INSERT INTO case_activities (case_id, activity_type, description, created_by) VALUES (?, ?, ?, ?)"
                );
                $activityStmt->execute([
                    $id,
                    'Case Updated',
                    "Case updated by {$user->name}",
                    $user->name ?? $user->email
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Case updated successfully',
                    'data' => ['id' => $id]
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        break;
        
    case (preg_match('/\/api\/cases\/(\d+)/', $uri, $matches) ? true : false):
        // Get single case by ID
        $caseId = $matches[1];
        
        if ($method === 'GET') {
            try {
                $stmt = $db->prepare("SELECT * FROM cases WHERE id = ?");
                $stmt->execute([$caseId]);
                $case = $stmt->fetch();
                
                if ($case) {
                    echo json_encode([
                        'success' => true,
                        'data' => $case
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Case not found']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        break;

    case '/api/cases/statistics':
        try {
            $totalCases = $db->query("SELECT COUNT(*) as count FROM cases")->fetch()['count'];
            $activeCases = $db->query("SELECT COUNT(*) as count FROM cases WHERE current_status = 'Active'")->fetch()['count'];
            $pendingCases = $db->query("SELECT COUNT(*) as count FROM cases WHERE current_status IN ('Pending Appeal', 'Pending Hearing')")->fetch()['count'];
            $urgentCases = $db->query("SELECT COUNT(*) as count FROM cases WHERE priority = 'Urgent'")->fetch()['count'];
            
            $casesByType = [];
            $stmt = $db->query("SELECT nature_of_case, COUNT(*) as count FROM cases GROUP BY nature_of_case");
            while ($row = $stmt->fetch()) {
                $casesByType[$row['nature_of_case']] = (int)$row['count'];
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'total_cases' => (int)$totalCases,
                    'active_cases' => (int)$activeCases,
                    'pending_cases' => (int)$pendingCases,
                    'urgent_cases' => (int)$urgentCases,
                    'cases_by_type' => $casesByType,
                    'upcoming_hearings' => 8,
                    'overdue_deadlines' => 2
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case '/api/cases/documents':
        // Handle document uploads for cases
        $user = AuthMiddleware::requireAuth();
        if (!$user) exit;
        
        if ($method === 'POST') {
            // Document upload endpoint
            try {
                $caseId = $_POST['case_id'] ?? null;
                if (!$caseId || !isset($_FILES['document'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Case ID and document file required']);
                    exit;
                }
                
                // In a real implementation, you would:
                // 1. Validate file type and size
                // 2. Move uploaded file to secure location
                // 3. Store file path in database
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Document uploaded successfully',
                    'data' => [
                        'case_id' => $caseId,
                        'filename' => $_FILES['document']['name']
                    ]
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        } elseif ($method === 'GET') {
            // Get documents for a case
            $caseId = $_GET['case_id'] ?? null;
            if (!$caseId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Case ID required']);
                exit;
            }
            
            try {
                $stmt = $db->prepare("SELECT * FROM case_documents WHERE case_id = ? ORDER BY upload_date DESC");
                $stmt->execute([$caseId]);
                $documents = $stmt->fetchAll();
                
                echo json_encode([
                    'success' => true,
                    'data' => $documents
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        break;
        
    case '/api/cases/dashboard':
        echo json_encode([
            'success' => true,
            'data' => [
                'recent_cases' => array_slice($cases, 0, 3),
                'statistics' => [
                    'total' => 45,
                    'active' => 28,
                    'urgent' => 5
                ],
                'upcoming_deadlines' => [
                    ['case' => 'CASE-2024-001', 'deadline' => 'Motion Due', 'date' => '2024-10-25'],
                    ['case' => 'CASE-2024-002', 'deadline' => 'Court Hearing', 'date' => '2024-10-26']
                ]
            ]
        ]);
        break;

    default:
        if (preg_match('/\/api\/cases\/(\d+)/', $uri, $matches)) {
            $id = (int)$matches[1];
            try {
                $stmt = $db->prepare("SELECT * FROM cases WHERE id = ?");
                $stmt->execute([$id]);
                $case = $stmt->fetch();
                if ($case) {
                    echo json_encode(['success' => true, 'data' => $case]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Case not found']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Endpoint not found',
                'available_endpoints' => [
                    'GET /health',
                    'GET /api/cases',
                    'POST /api/cases',
                    'GET /api/cases/{id}',
                    'GET /api/cases/statistics',
                    'GET /api/cases/dashboard'
                ]
            ]);
        }
}
