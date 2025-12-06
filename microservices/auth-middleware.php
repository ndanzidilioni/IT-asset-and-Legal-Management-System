<?php
/**
 * Authentication & Authorization Middleware for Legal Management Services
 * Handles JWT token validation and role-based access control
 */

class AuthMiddleware {
    private static $secret = 'your-secret-key-change-in-production'; // Change this!
    private static $db;
    
    // Role hierarchy
    const ROLES = [
        'admin' => ['admin', 'lawyer', 'legal_assistant', 'client'],
        'lawyer' => ['lawyer', 'legal_assistant'],
        'legal_assistant' => ['legal_assistant'],
        'client' => ['client'],
        'developer' => ['developer', 'lawyer', 'legal_assistant']
    ];
    
    /**
     * Initialize with database connection
     */
    public static function init($mainDbConnection) {
        self::$db = $mainDbConnection;
    }
    
    /**
     * Validate JWT token and return user data
     */
    public static function authenticate() {
        $headers = getallheaders();
        $token = null;
        
        // Check Authorization header
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }
        
        // Check token in query string (fallback)
        if (!$token && isset($_GET['token'])) {
            $token = $_GET['token'];
        }
        
        if (!$token) {
            self::unauthorized('No token provided');
            return null;
        }
        
        // Validate token
        $decoded = self::decodeToken($token);
        if (!$decoded) {
            self::unauthorized('Invalid or expired token');
            return null;
        }
        
        return $decoded;
    }
    
    /**
     * Check if user has required role
     */
    public static function authorize($user, $requiredRole) {
        if (!$user || !isset($user->role)) {
            self::forbidden('User role not found');
            return false;
        }
        
        $userRole = $user->role;
        
        // Check if user's role has access
        if (isset(self::ROLES[$userRole]) && in_array($requiredRole, self::ROLES[$userRole])) {
            return true;
        }
        
        // Check exact match
        if ($userRole === $requiredRole) {
            return true;
        }
        
        self::forbidden("Access denied. Required role: $requiredRole");
        return false;
    }
    
    /**
     * Middleware for routes requiring authentication
     */
    public static function requireAuth($requiredRole = null) {
        $user = self::authenticate();
        
        if (!$user) {
            exit;
        }
        
        // If specific role required, check authorization
        if ($requiredRole && !self::authorize($user, $requiredRole)) {
            exit;
        }
        
        return $user;
    }
    
    /**
     * Decode JWT token
     */
    private static function decodeToken($token) {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }
            
            list($header, $payload, $signature) = $parts;
            
            // Verify signature
            $validSignature = hash_hmac(
                'sha256',
                "$header.$payload",
                self::$secret,
                true
            );
            
            if (!hash_equals(
                self::base64UrlDecode($signature),
                $validSignature
            )) {
                return null;
            }
            
            // Decode payload
            $decoded = json_decode(self::base64UrlDecode($payload));
            
            // Check expiration
            if (isset($decoded->exp) && $decoded->exp < time()) {
                return null;
            }
            
            return $decoded;
            
        } catch (Exception $e) {
            error_log("Token decode error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate JWT token
     */
    public static function generateToken($userId, $email, $role, $name = '') {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        $payload = json_encode([
            'user_id' => $userId,
            'email' => $email,
            'role' => $role,
            'name' => $name,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24 * 7) // 7 days
        ]);
        
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);
        
        $signature = hash_hmac(
            'sha256',
            "$base64UrlHeader.$base64UrlPayload",
            self::$secret,
            true
        );
        
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }
    
    /**
     * Log authentication attempt
     */
    public static function logAccess($user, $action, $resource) {
        if (!self::$db) return;
        
        try {
            $stmt = self::$db->prepare(
                "INSERT INTO audit_logs (log_time, user_name, action, resource, ip_address) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                date('H:i:s'),
                $user->name ?? $user->email,
                $action,
                $resource,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            error_log("Audit log error: " . $e->getMessage());
        }
    }
    
    /**
     * Check if user can access specific resource
     */
    public static function canAccessResource($user, $resourceType, $resourceId = null) {
        $role = $user->role ?? 'client';
        
        // Admins can access everything
        if ($role === 'admin') {
            return true;
        }
        
        // Lawyers can access most resources
        if ($role === 'lawyer' || $role === 'developer') {
            return true;
        }
        
        // Legal assistants have limited access
        if ($role === 'legal_assistant') {
            $allowed = ['cases', 'documents', 'court-schedules', 'clients'];
            return in_array($resourceType, $allowed);
        }
        
        // Clients can only access their own resources
        if ($role === 'client') {
            // Would need to check if resource belongs to this client
            return in_array($resourceType, ['cases', 'documents', 'invoices']);
        }
        
        return false;
    }
    
    /**
     * Response helpers
     */
    private static function unauthorized($message) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized',
            'message' => $message
        ]);
    }
    
    private static function forbidden($message) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Forbidden',
            'message' => $message
        ]);
    }
    
    /**
     * Base64 URL encoding/decoding
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
?>
