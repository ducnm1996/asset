<?php
// api/auth.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/database.php';
require_once '../models/Auth.php';
require_once '../middleware/AuthMiddleware.php';

session_start();

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);
$middleware = new AuthMiddleware($db);

// Lấy method và endpoint
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$endpoint = $request[0] ?? '';

try {
    switch ($endpoint) {
        case 'login':
            handleLogin($auth, $method);
            break;
        case 'logout':
            handleLogout($auth, $middleware, $method);
            break;
        case 'refresh':
            handleRefreshToken($auth, $method);
            break;
        case 'me':
            handleGetCurrentUser($middleware, $method);
            break;
        case 'change-password':
            handleChangePassword($auth, $middleware, $method);
            break;
        case 'users':
            handleUsers($auth, $middleware, $method, $request[1] ?? null);
            break;
        case 'roles':
            handleRoles($auth, $middleware, $method);
            break;
        case 'permissions':
            handlePermissions($auth, $middleware, $method);
            break;
        case 'activity-logs':
            handleActivityLogs($auth, $middleware, $method);
            break;
        default:
            jsonResponse(null, false, 'Endpoint không tồn tại');
    }
} catch (Exception $e) {
    jsonResponse(null, false, 'Lỗi server: ' . $e->getMessage());
}

// Xử lý đăng nhập
function handleLogin($auth, $method) {
    if ($method !== 'POST') {
        jsonResponse(null, false, 'Chỉ hỗ trợ POST method');
        return;
    }
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['username']) || empty($data['password'])) {
        jsonResponse(null, false, 'Username và password là bắt buộc');
        return;
    }
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $result = $auth->login($data['username'], $data['password'], $ip_address, $user_agent);
    
    if ($result['success']) {
        // Lưu thông tin user vào session
        $_SESSION['user'] = $result['data']['user'];
        jsonResponse($result['data'], true, $result['message']);
    } else {
        jsonResponse(null, false, $result['message']);
    }
}

// Xử lý đăng xuất
function handleLogout($auth, $middleware, $method) {
    if ($method !== 'POST') {
        jsonResponse(null, false, 'Chỉ hỗ trợ POST method');
        return;
    }
    
    $headers = getallheaders();
    $token = null;
    
    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            $token = $matches[1];
        }
    }
    
    if ($token) {
        $result = $auth->logout($token);
        jsonResponse(null, $result['success'], $result['message']);
    } else {
        jsonResponse(null, true, 'Đăng xuất thành công');
    }
    
    // Xóa session
    session_destroy();
}

// Xử lý refresh token
function handleRefreshToken($auth, $method) {
    if ($method !== 'POST') {
        jsonResponse(null, false, 'Chỉ hỗ trợ POST method');
        return;
    }
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['refresh_token'])) {
        jsonResponse(null, false, 'Refresh token là bắt buộc');
        return;
    }
    
    $result = $auth->refreshToken($data['refresh_token']);
    
    if ($result['success']) {
        $_SESSION['user'] = $result['data']['user'];
        jsonResponse($result['data'], true, 'Refresh token thành công');
    } else {
        jsonResponse(null, false, $result['message']);
    }
}

// Lấy thông tin user hiện tại
function handleGetCurrentUser($middleware, $method) {
    if ($method !== 'GET') {
        jsonResponse(null, false, 'Chỉ hỗ trợ GET method');
        return;
    }
    
    $user = $middleware->authenticate();
    if ($user) {
        jsonResponse($user, true, 'Thành công');
    }
}

// Đổi mật khẩu
function handleChangePassword($auth, $middleware, $method) {
    if ($method !== 'POST') {
        jsonResponse(null, false, 'Chỉ hỗ trợ POST method');
        return;
    }
    
    $user = $middleware->authenticate();
    if (!$user) return;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['old_password']) || empty($data['new_password'])) {
        jsonResponse(null, false, 'Mật khẩu cũ và mật khẩu mới là bắt buộc');
        return;
    }
    
    if (strlen($data['new_password']) < 6) {
        jsonResponse(null, false, 'Mật khẩu mới phải có ít nhất 6 ký tự');
        return;
    }
    
    $result = $auth->changePassword($user['id'], $data['old_password'], $data['new_password']);
    jsonResponse(null, $result['success'], $result['message']);
}

// Quản lý users
function handleUsers($auth, $middleware, $method, $id) {
    switch ($method) {
        case 'GET':
            if (!$middleware->requirePermission('users.manage')) return;
            
            if ($id) {
                $user = $auth->getUserById($id);
                if ($user) {
                    // Loại bỏ password_hash khỏi response
                    unset($user['password_hash']);
                    jsonResponse($user, true, 'Thành công');
                } else {
                    jsonResponse(null, false, 'Không tìm thấy user');
                }
            } else {
                $users = $auth->getUsers();
                // Loại bỏ password_hash khỏi tất cả users
                foreach ($users as &$user) {
                    unset($user['password_hash']);
                }
                jsonResponse($users, true, 'Thành công');
            }
            break;
            
        case 'POST':
            if (!$middleware->requirePermission('users.manage')) return;
            
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Validate required fields
            $required_fields = ['username', 'email', 'password', 'full_name', 'role_id'];
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    jsonResponse(null, false, "Trường {$field} là bắt buộc");
                    return;
                }
            }
            
            // Validate email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                jsonResponse(null, false, 'Email không hợp lệ');
                return;
            }
            
            // Validate password length
            if (strlen($data['password']) < 6) {
                jsonResponse(null, false, 'Mật khẩu phải có ít nhất 6 ký tự');
                return;
            }
            
            // Check if username/email already exists
            if ($auth->checkUserExists($data['username'], $data['email'])) {
                jsonResponse(null, false, 'Username hoặc email đã tồn tại');
                return;
            }
            
            $data['is_active'] = $data['is_active'] ?? true;
            $data['nhan_vien_id'] = $data['nhan_vien_id'] ?? null;
            
            $result = $auth->createUser($data);
            
            if ($result['success']) {
                $middleware->logActivity('create_user', 'users', $result['id']);
                jsonResponse(['id' => $result['id']], true, 'Tạo tài khoản thành công');
            } else {
                jsonResponse(null, false, $result['message']);
            }
            break;
            
        case 'PUT':
            if (!$middleware->requirePermission('users.manage')) return;
            
            if (!$id) {
                jsonResponse(null, false, 'ID user là bắt buộc');
                return;
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (isset($data['role_id'])) {
                $result = $auth->updateUserRole($id, $data['role_id']);
                jsonResponse(null, $result['success'], $result['message']);
            } else {
                jsonResponse(null, false, 'Không có dữ liệu để cập nhật');
            }
            break;
            
        case 'DELETE':
            if (!$middleware->requirePermission('users.manage')) return;
            
            if (!$id) {
                jsonResponse(null, false, 'ID user là bắt buộc');
                return;
            }
            
            // Không cho phép xóa user hiện tại
            if ($id == $middleware->getCurrentUserId()) {
                jsonResponse(null, false, 'Không thể xóa tài khoản của chính mình');
                return;
            }
            
            $result = $auth->toggleUserStatus($id, false);
            jsonResponse(null, $result['success'], $result['message']);
            break;
            
        default:
            jsonResponse(null, false, 'Method không được hỗ trợ');
    }
}

// Lấy danh sách roles
function handleRoles($auth, $middleware, $method) {
    if ($method !== 'GET') {
        jsonResponse(null, false, 'Chỉ hỗ trợ GET method');
        return;
    }
    
    if (!$middleware->requirePermission('roles.manage')) return;
    
    $roles = $auth->getRoles();
    jsonResponse($roles, true, 'Thành công');
}

// Lấy quyền của user hiện tại
function handlePermissions($auth, $middleware, $method) {
    if ($method !== 'GET') {
        jsonResponse(null, false, 'Chỉ hỗ trợ GET method');
        return;
    }
    
    $user = $middleware->authenticate();
    if (!$user) return;
    
    $permissions = $auth->getUserPermissions($user['id']);
    
    // Chuyển đổi thành format dễ sử dụng cho frontend
    $formatted_permissions = [];
    foreach ($permissions as $permission) {
        $formatted_permissions[$permission['name']] = [
            'display_name' => $permission['display_name'],
            'module' => $permission['module'],
            'action' => $permission['action']
        ];
    }
    
    jsonResponse($formatted_permissions, true, 'Thành công');
}

// Lấy activity logs
function handleActivityLogs($auth, $middleware, $method) {
    if ($method !== 'GET') {
        jsonResponse(null, false, 'Chỉ hỗ trợ GET method');
        return;
    }
    
    if (!$middleware->requirePermission('logs.view')) return;
    
    $limit = $_GET['limit'] ?? 100;
    $offset = $_GET['offset'] ?? 0;
    $user_id = $_GET['user_id'] ?? null;
    $module = $_GET['module'] ?? null;
    
    $logs = $auth->getActivityLogs($limit, $offset, $user_id, $module);
    jsonResponse($logs, true, 'Thành công');
}

// Helper function để trả về JSON response
function jsonResponse($data, $success = true, $message = '') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}
?>