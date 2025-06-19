<?php
// middleware/AuthMiddleware.php
require_once '../models/Auth.php';

class AuthMiddleware {
    private $auth;
    
    public function __construct($db) {
        $this->auth = new Auth($db);
    }
    
    // Xác thực người dùng
    public function authenticate() {
        $headers = $this->getAuthHeaders();
        
        if (!$headers || !isset($headers['Authorization'])) {
            $this->respondUnauthorized('Token không được cung cấp');
            return false;
        }
        
        $token = $this->extractToken($headers['Authorization']);
        if (!$token) {
            $this->respondUnauthorized('Token không hợp lệ');
            return false;
        }
        
        $result = $this->auth->authenticate($token);
        if (!$result['success']) {
            $this->respondUnauthorized($result['message']);
            return false;
        }
        
        // Lưu thông tin user vào $_SESSION hoặc global variable
        $_SESSION['user'] = $result['user'];
        return $result['user'];
    }
    
    // Kiểm tra quyền
    public function authorize($permission) {
        if (!isset($_SESSION['user'])) {
            $this->respondForbidden('Chưa đăng nhập');
            return false;
        }
        
        $user_id = $_SESSION['user']['id'];
        
        if (!$this->auth->hasPermission($user_id, $permission)) {
            $this->respondForbidden('Không có quyền thực hiện hành động này');
            return false;
        }
        
        return true;
    }
    
    // Middleware combo: xác thực + phân quyền
    public function requirePermission($permission) {
        $user = $this->authenticate();
        if (!$user) {
            return false;
        }
        
        return $this->authorize($permission);
    }
    
    // Lấy thông tin user hiện tại
    public function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }
    
    // Lấy ID user hiện tại
    public function getCurrentUserId() {
        return $_SESSION['user']['id'] ?? null;
    }
    
    // Log hoạt động
    public function logActivity($action, $module, $record_id = null, $old_data = null, $new_data = null) {
        $user_id = $this->getCurrentUserId();
        if ($user_id) {
            $ip_address = $this->getClientIP();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            $this->auth->logActivity(
                $user_id, $action, $module, $record_id, 
                $old_data, $new_data, $ip_address, $user_agent
            );
        }
    }
    
    // Helper methods
    private function getAuthHeaders() {
        $headers = array();
        
        if (isset($_SERVER['Authorization'])) {
            $headers['Authorization'] = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers['Authorization'] = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers['Authorization'] = trim($requestHeaders['Authorization']);
            }
        }
        
        return $headers;
    }
    
    private function extractToken($authHeader) {
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    private function getClientIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    private function respondUnauthorized($message) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'error_code' => 'UNAUTHORIZED'
        ]);
        exit;
    }
    
    private function respondForbidden($message) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'error_code' => 'FORBIDDEN'
        ]);
        exit;
    }
}

// Hàm helper để sử dụng middleware dễ dàng hơn
function requireAuth($db) {
    session_start();
    $middleware = new AuthMiddleware($db);
    return $middleware->authenticate();
}

function requirePermission($db, $permission) {
    session_start();
    $middleware = new AuthMiddleware($db);
    return $middleware->requirePermission($permission);
}

function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

function getCurrentUserId() {
    return $_SESSION['user']['id'] ?? null;
}

function logUserActivity($db, $action, $module, $record_id = null, $old_data = null, $new_data = null) {
    session_start();
    $middleware = new AuthMiddleware($db);
    $middleware->logActivity($action, $module, $record_id, $old_data, $new_data);
}
?>