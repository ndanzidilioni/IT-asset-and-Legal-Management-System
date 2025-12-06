<?php
/**
 * Authentication Service for Legal Management System
 * Provides login, registration, and token management
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../auth-middleware.php';

// Connect to main application database (where users table is)
try {
    $db = new PDO('mysql:host=localhost;dbname=ict_asset_register', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    AuthMiddleware::init($db);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

switch ($uri) {
    case '/':
    case '/health':
        echo json_encode([
            'status' => 'healthy',
            'service' => 'Authentication Service',
            'port' => 8015,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        break;
        
    case '/api/auth/login':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['email']) || !isset($data['password'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Email and password required']);
                break;
            }
            
            try {
                $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$data['email']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($data['password'], $user['password'])) {
                    // Generate token
                    $token = AuthMiddleware::generateToken(
                        $user['id'],
                        $user['email'],
                        $user['role'],
                        $user['name']
                    );
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login successful',
                        'token' => $token,
                        'user' => [
                            'id' => $user['id'],
                            'name' => $user['name'],
                            'email' => $user['email'],
                            'role' => $user['role']
                        ]
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Login failed: ' . $e->getMessage()]);
            }
        }
        break;
        
    case '/api/auth/register':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $required = ['name', 'email', 'password'];
            foreach ($required as $field) {
                if (!isset($data[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "$field is required"]);
                    exit;
                }
            }
            
            try {
                // Check if email exists
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$data['email']]);
                if ($stmt->fetch()) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Email already exists']);
                    break;
                }
                
                // Create user
                $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $data['name'],
                    $data['email'],
                    password_hash($data['password'], PASSWORD_DEFAULT),
                    $data['role'] ?? 'client'
                ]);
                
                $userId = $db->lastInsertId();
                
                // Generate token
                $token = AuthMiddleware::generateToken(
                    $userId,
                    $data['email'],
                    $data['role'] ?? 'client',
                    $data['name']
                );
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Registration successful',
                    'token' => $token,
                    'user' => [
                        'id' => $userId,
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'role' => $data['role'] ?? 'client'
                    ]
                ]);
                
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
            }
        }
        break;
        
    case '/api/auth/verify':
        if ($method === 'POST') {
            $user = AuthMiddleware::authenticate();
            if ($user) {
                echo json_encode([
                    'success' => true,
                    'valid' => true,
                    'user' => [
                        'id' => $user->user_id,
                        'email' => $user->email,
                        'role' => $user->role,
                        'name' => $user->name ?? ''
                    ]
                ]);
            }
        }
        break;
        
    case '/api/auth/roles':
        echo json_encode([
            'success' => true,
            'roles' => [
                'admin' => 'Full system access',
                'lawyer' => 'Lawyer/Advocate - Full legal case management',
                'legal_assistant' => 'Legal Assistant - Case support',
                'client' => 'Client - View own cases and documents',
                'developer' => 'Developer - System development access'
            ]
        ]);
        break;
        
    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found',
            'available_endpoints' => [
                'POST /api/auth/login',
                'POST /api/auth/register',
                'POST /api/auth/verify',
                'GET /api/auth/roles'
            ]
        ]);
}
?>
