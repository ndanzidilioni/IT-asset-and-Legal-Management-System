<?php
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

// Only set JSON content type for non-file routes
if (!preg_match('/\/(upload|download)/', $uri)) {
    header('Content-Type: application/json');
}

// Log for debugging
error_log("Contract Service - URI: $uri, Method: $method");

// Get database connection
$db = DatabaseConfig::getConnection(DatabaseConfig::CONTRACT_DB);

// Handle file download FIRST (before dynamic route check)

if (preg_match('/\/api\/contracts\/(\d+)\/download/', $uri, $matches)) {
    $contractId = $matches[1];
    try {
        $stmt = $db->prepare("SELECT document_path, document_name FROM contracts WHERE id = ?");
        $stmt->execute([$contractId]);
        $contract = $stmt->fetch();
        
        if ($contract && $contract['document_path']) {
            $filePath = __DIR__ . '/' . $contract['document_path'];
            if (file_exists($filePath)) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $contract['document_name'] . '"');
                header('Content-Length: ' . filesize($filePath));
                readfile($filePath);
                exit;
            }
        }
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Document not found']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle file upload
if (preg_match('/\/api\/contracts\/(\d+)\/upload/', $uri, $matches)) {
    header('Content-Type: application/json');
    $contractId = $matches[1];
    
    error_log("Upload endpoint hit - Method: $method, Contract ID: $contractId");
    error_log("Request Method from SERVER: " . $_SERVER['REQUEST_METHOD']);
    
    if ($method === 'POST') {
        try {
            error_log("Upload request for contract $contractId");
            error_log("FILES: " . print_r($_FILES, true));
            
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
            $filename = 'contract_' . $contractId . '_' . time() . '.' . $ext;
            $uploadPath = 'uploads/' . $filename;
            
            error_log("Attempting to save file to: " . __DIR__ . '/' . $uploadPath);
            
            if (move_uploaded_file($file['tmp_name'], __DIR__ . '/' . $uploadPath)) {
                $stmt = $db->prepare("UPDATE contracts SET document_path = ?, document_name = ? WHERE id = ?");
                $stmt->execute([$uploadPath, $file['name'], $contractId]);
                
                error_log("File uploaded successfully: $uploadPath");
                
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

// Check for dynamic routes (e.g., /api/contracts/123) - AFTER upload/download
preg_match('/\/api\/contracts\/(\d+)$/', $uri, $matches);
$isDynamicRoute = !empty($matches);

if ($isDynamicRoute) {
    $contractId = $matches[1];
    
    if ($method === 'GET') {
        try {
            $stmt = $db->prepare("SELECT * FROM contracts WHERE id = ?");
            $stmt->execute([$contractId]);
            $contract = $stmt->fetch();
            
            if ($contract) {
                echo json_encode(['success' => true, 'data' => $contract]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Contract not found']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } elseif ($method === 'DELETE') {
        try {
            $stmt = $db->prepare("DELETE FROM contracts WHERE id = ?");
            $stmt->execute([$contractId]);
            
            echo json_encode(['success' => true, 'message' => 'Contract deleted successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    exit;
}

switch ($uri) {
    case '/':
    case '/health':
        echo json_encode([
            'status' => 'healthy',
            'service' => 'Contract Register Service',
            'port' => 8015,
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
        break;

    case '/api/contracts':
        if ($method === 'GET') {
            try {
                $year = $_GET['year'] ?? date('Y');
                $status = $_GET['status'] ?? null;
                
                $sql = "SELECT * FROM contracts WHERE contract_year = ?";
                $params = [$year];
                
                if ($status) {
                    $sql .= " AND status = ?";
                    $params[] = $status;
                }
                
                $sql .= " ORDER BY created_at DESC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $contracts = $stmt->fetchAll();
                
                echo json_encode([
                    'success' => true,
                    'data' => $contracts,
                    'year' => (int)$year,
                    'total' => count($contracts)
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        } elseif ($method === 'POST') {
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Validate required fields
                if (empty($data['tender_number']) || empty($data['project_name']) || 
                    empty($data['supplier_contractor'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Tender number, project name, and supplier/contractor are required']);
                    exit;
                }
                
                $stmt = $db->prepare(
                    "INSERT INTO contracts (tender_number, project_name, supplier_contractor, contract_amount, 
                     contract_year, date_received, date_vetted, date_signed, commencement_date, completion_date, 
                     status, notes, created_by) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                
                $stmt->execute([
                    $data['tender_number'],
                    $data['project_name'],
                    $data['supplier_contractor'],
                    $data['contract_amount'] ?? null,
                    $data['contract_year'] ?? date('Y'),
                    $data['date_received'] ?? null,
                    $data['date_vetted'] ?? null,
                    $data['date_signed'] ?? null,
                    $data['commencement_date'] ?? null,
                    $data['completion_date'] ?? null,
                    $data['status'] ?? 'Draft',
                    $data['notes'] ?? null,
                    $data['created_by'] ?? 'System'
                ]);
                
                $contractId = $db->lastInsertId();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Contract created successfully',
                    'data' => ['id' => $contractId, 'tender_number' => $data['tender_number']]
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        } elseif ($method === 'PUT') {
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $id = $_GET['id'] ?? null;
                
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Contract ID required']);
                    exit;
                }
                
                $stmt = $db->prepare(
                    "UPDATE contracts SET tender_number = ?, project_name = ?, supplier_contractor = ?, 
                     contract_amount = ?, contract_year = ?, date_received = ?, date_vetted = ?, 
                     date_signed = ?, commencement_date = ?, completion_date = ?, status = ?, notes = ?, 
                     updated_by = ? WHERE id = ?"
                );
                
                $stmt->execute([
                    $data['tender_number'],
                    $data['project_name'],
                    $data['supplier_contractor'],
                    $data['contract_amount'] ?? null,
                    $data['contract_year'] ?? date('Y'),
                    $data['date_received'] ?? null,
                    $data['date_vetted'] ?? null,
                    $data['date_signed'] ?? null,
                    $data['commencement_date'] ?? null,
                    $data['completion_date'] ?? null,
                    $data['status'] ?? 'Draft',
                    $data['notes'] ?? null,
                    $data['updated_by'] ?? 'System',
                    $id
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Contract updated successfully',
                    'data' => ['id' => $id]
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        break;

    case '/api/contracts/statistics':
        try {
            $year = $_GET['year'] ?? date('Y');
            
            $totalStmt = $db->prepare("SELECT COUNT(*) as count, SUM(contract_amount) as total_value FROM contracts WHERE contract_year = ?");
            $totalStmt->execute([$year]);
            $totals = $totalStmt->fetch();
            
            $statusStmt = $db->prepare("SELECT status, COUNT(*) as count FROM contracts WHERE contract_year = ? GROUP BY status");
            $statusStmt->execute([$year]);
            $statusCounts = [];
            while ($row = $statusStmt->fetch()) {
                $statusCounts[$row['status']] = (int)$row['count'];
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'year' => (int)$year,
                    'total_contracts' => (int)$totals['count'],
                    'total_value' => (float)$totals['total_value'],
                    'by_status' => $statusCounts,
                    'active_contracts' => $statusCounts['Active'] ?? 0,
                    'completed_contracts' => $statusCounts['Completed'] ?? 0,
                    'pending_review' => $statusCounts['Under Review'] ?? 0
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case '/api/contracts/years':
        try {
            $stmt = $db->query("SELECT DISTINCT contract_year FROM contracts ORDER BY contract_year DESC");
            $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo json_encode([
                'success' => true,
                'data' => array_map('intval', $years)
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
        break;
}
