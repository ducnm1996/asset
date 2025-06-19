<?php
// models/Auth.php
require_once '../config/database.php';

class Auth {
    private $conn;
    private $users_table = "users";
    private $sessions_table = "user_sessions";
    private $roles_table = "roles";
    private $permissions_table = "permissions";
    private $role_permissions_table = "role_permissions";
    private $activity_logs_table = "activity_logs";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Đăng nhập
    public function login($username, $password, $ip_address = null, $user_agent = null) {
        try {
            // Tìm user theo username hoặc email
            $query = "SELECT u.*, r.name as role_name, r.display_name as role_display_name 
                      FROM " . $this->users_table . " u
                      LEFT JOIN " . $this->roles_table . " r ON u.role_id = r.id
                      WHERE (u.username = :username OR u.email = :username) 
                      AND u.is_active = true";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return ['success' => false, 'message' => 'Tài khoản không tồn tại'];
            }
            
            // Kiểm tra account có bị khóa không
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                return ['success' => false, 'message' => 'Tài khoản đang bị khóa'];
            }
            
            // Kiểm tra password
            if (!password_verify($password, $user['password_hash'])) {
                // Tăng số lần đăng nhập sai
                $this->incrementFailedAttempts($user['id']);
                return ['success' => false, 'message' => 'Mật khẩu không đúng'];
            }
            
            // Reset failed attempts
            $this->resetFailedAttempts($user['id']);
            
            // Tạo session token
            $session_token = $this->generateToken();
            $refresh_token = $this->generateToken();
            $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Lưu session
            $sessionQuery = "INSERT INTO " . $this->sessions_table . "
                            (user_id, session_token, refresh_token, ip_address, user_agent, expires_at)
                            VALUES (:user_id, :session_token, :refresh_token, :ip_address, :user_agent, :expires_at)";
            
            $sessionStmt = $this->conn->prepare($sessionQuery);
            $sessionStmt->bindParam(":user_id", $user['id']);
            $sessionStmt->bindParam(":session_token", $session_token);
            $sessionStmt->bindParam(":refresh_token", $refresh_token);
            $sessionStmt->bindParam(":ip_address", $ip_address);
            $sessionStmt->bindParam(":user_agent", $user_agent);
            $sessionStmt->bindParam(":expires_at", $expires_at);
            $sessionStmt->execute();
            
            // Cập nhật last_login
            $updateQuery = "UPDATE " . $this->users_table . " 
                           SET last_login = CURRENT_TIMESTAMP 
                           WHERE id = :user_id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":user_id", $user['id']);
            $updateStmt->execute();
            
            // Log activity
            $this->logActivity($user['id'], 'login', 'auth', null, null, null, $ip_address, $user_agent);
            
            return [
                'success' => true,
                'message' => 'Đăng nhập thành công',
                'data' => [
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'full_name' => $user['full_name'],
                        'avatar' => $user['avatar'],
                        'role' => $user['role_name'],
                        'role_display' => $user['role_display_name']
                    ],
                    'session_token' => $session_token,
                    'refresh_token' => $refresh_token,
                    'expires_at' => $expires_at
                ]
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
        }
    }

    // Xác thực token
    public function authenticate($token) {
        try {
            $query = "SELECT u.*, r.name as role_name, r.display_name as role_display_name,
                             s.expires_at
                      FROM " . $this->users_table . " u
                      LEFT JOIN " . $this->roles_table . " r ON u.role_id = r.id
                      JOIN " . $this->sessions_table . " s ON u.id = s.user_id
                      WHERE s.session_token = :token 
                      AND u.is_active = true
                      AND s.expires_at > CURRENT_TIMESTAMP";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":token", $token);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return ['success' => false, 'message' => 'Token không hợp lệ hoặc đã hết hạn'];
            }
            
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'full_name' => $user['full_name'],
                    'avatar' => $user['avatar'],
                    'role' => $user['role_name'],
                    'role_display' => $user['role_display_name']
                ]
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi xác thực: ' . $e->getMessage()];
        }
    }

    // Đăng xuất
    public function logout($token) {
        try {
            // Xóa session
            $query = "DELETE FROM " . $this->sessions_table . " WHERE session_token = :token";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":token", $token);
            $stmt->execute();
            
            return ['success' => true, 'message' => 'Đăng xuất thành công'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi đăng xuất: ' . $e->getMessage()];
        }
    }

    // Kiểm tra quyền
    public function hasPermission($user_id, $permission) {
        try {
            $query = "SELECT user_has_permission(:user_id, :permission) as has_permission";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":permission", $permission);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['has_permission'] === 't' || $result['has_permission'] === true;
            
        } catch (Exception $e) {
            return false;
        }
    }

    // Lấy tất cả quyền của user
    public function getUserPermissions($user_id) {
        try {
            $query = "SELECT p.name, p.display_name, p.module, p.action
                      FROM " . $this->users_table . " u
                      JOIN " . $this->roles_table . " r ON u.role_id = r.id
                      JOIN " . $this->role_permissions_table . " rp ON r.id = rp.role_id
                      JOIN " . $this->permissions_table . " p ON rp.permission_id = p.id
                      WHERE u.id = :user_id AND u.is_active = true";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }

    // Tạo user mới
    public function createUser($data) {
        try {
            $query = "INSERT INTO " . $this->users_table . "
                      (username, email, password_hash, full_name, role_id, nhan_vien_id, is_active)
                      VALUES (:username, :email, :password_hash, :full_name, :role_id, :nhan_vien_id, :is_active)";
            
            $stmt = $this->conn->prepare($query);
            
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt->bindParam(":username", $data['username']);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":password_hash", $password_hash);
            $stmt->bindParam(":full_name", $data['full_name']);
            $stmt->bindParam(":role_id", $data['role_id']);
            $stmt->bindParam(":nhan_vien_id", $data['nhan_vien_id']);
            $stmt->bindParam(":is_active", $data['is_active']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'id' => $this->conn->lastInsertId()];
            }
            
            return ['success' => false, 'message' => 'Lỗi tạo tài khoản'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi tạo tài khoản: ' . $e->getMessage()];
        }
    }

    // Đổi mật khẩu
    public function changePassword($user_id, $old_password, $new_password) {
        try {
            // Kiểm tra mật khẩu cũ
            $query = "SELECT password_hash FROM " . $this->users_table . " WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($old_password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Mật khẩu cũ không đúng'];
            }
            
            // Cập nhật mật khẩu mới
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $updateQuery = "UPDATE " . $this->users_table . " 
                           SET password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP
                           WHERE id = :user_id";
            
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":password_hash", $new_password_hash);
            $updateStmt->bindParam(":user_id", $user_id);
            
            if ($updateStmt->execute()) {
                // Log activity
                $this->logActivity($user_id, 'change_password', 'auth');
                return ['success' => true, 'message' => 'Đổi mật khẩu thành công'];
            }
            
            return ['success' => false, 'message' => 'Lỗi cập nhật mật khẩu'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi đổi mật khẩu: ' . $e->getMessage()];
        }
    }

    // Lấy danh sách users
    public function getUsers() {
        try {
            $query = "SELECT u.id, u.username, u.email, u.full_name, u.avatar,
                             u.is_active, u.last_login, u.created_at,
                             r.name as role_name, r.display_name as role_display_name,
                             nv.ho_ten as employee_name, nv.ma_nhan_vien as employee_code
                      FROM " . $this->users_table . " u
                      LEFT JOIN " . $this->roles_table . " r ON u.role_id = r.id
                      LEFT JOIN nhan_vien nv ON u.nhan_vien_id = nv.id
                      ORDER BY u.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }

    // Lấy danh sách roles
    public function getRoles() {
        try {
            $query = "SELECT * FROM " . $this->roles_table . " ORDER BY id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }

    // Private helper methods
    private function generateToken() {
        return bin2hex(random_bytes(32));
    }

    private function incrementFailedAttempts($user_id) {
        $query = "UPDATE " . $this->users_table . " 
                  SET failed_login_attempts = failed_login_attempts + 1,
                      locked_until = CASE 
                        WHEN failed_login_attempts >= 4 THEN CURRENT_TIMESTAMP + INTERVAL '30 minutes'
                        ELSE locked_until
                      END
                  WHERE id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
    }

    private function resetFailedAttempts($user_id) {
        $query = "UPDATE " . $this->users_table . " 
                  SET failed_login_attempts = 0, locked_until = NULL
                  WHERE id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
    }

    public function logActivity($user_id, $action, $module, $record_id = null, $old_data = null, $new_data = null, $ip_address = null, $user_agent = null) {
        try {
            $query = "SELECT log_activity(:user_id, :action, :module, :record_id, :old_data, :new_data, :ip_address, :user_agent)";
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":action", $action);
            $stmt->bindParam(":module", $module);
            $stmt->bindParam(":record_id", $record_id);
            $stmt->bindParam(":old_data", $old_data ? json_encode($old_data) : null);
            $stmt->bindParam(":new_data", $new_data ? json_encode($new_data) : null);
            $stmt->bindParam(":ip_address", $ip_address);
            $stmt->bindParam(":user_agent", $user_agent);
            
            $stmt->execute();
        } catch (Exception $e) {
            // Log error but don't throw exception to avoid breaking main functionality
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }

    // Clean expired sessions
    public function cleanExpiredSessions() {
        try {
            $query = "DELETE FROM " . $this->sessions_table . " WHERE expires_at < CURRENT_TIMESTAMP";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->rowCount();
        } catch (Exception $e) {
            return 0;
        }
    }

    // Get activity logs
    public function getActivityLogs($limit = 100, $offset = 0, $user_id = null, $module = null) {
        try {
            $whereClause = "";
            $params = [];
            
            if ($user_id) {
                $whereClause .= " WHERE al.user_id = :user_id";
                $params[':user_id'] = $user_id;
            }
            
            if ($module) {
                $whereClause .= ($whereClause ? " AND" : " WHERE") . " al.module = :module";
                $params[':module'] = $module;
            }
            
            $query = "SELECT al.*, u.username, u.full_name
                      FROM " . $this->activity_logs_table . " al
                      LEFT JOIN " . $this->users_table . " u ON al.user_id = u.id
                      " . $whereClause . "
                      ORDER BY al.created_at DESC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }

    // Update user profile
    public function updateProfile($user_id, $data) {
        try {
            $query = "UPDATE " . $this->users_table . " 
                      SET full_name = :full_name, 
                          email = :email,
                          avatar = :avatar,
                          updated_at = CURRENT_TIMESTAMP
                      WHERE id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":full_name", $data['full_name']);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":avatar", $data['avatar']);
            $stmt->bindParam(":user_id", $user_id);
            
            if ($stmt->execute()) {
                $this->logActivity($user_id, 'update_profile', 'auth');
                return ['success' => true, 'message' => 'Cập nhật thông tin thành công'];
            }
            
            return ['success' => false, 'message' => 'Lỗi cập nhật thông tin'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi cập nhật thông tin: ' . $e->getMessage()];
        }
    }

    // Reset password (for admin)
    public function resetPassword($user_id, $new_password) {
        try {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $query = "UPDATE " . $this->users_table . " 
                      SET password_hash = :password_hash,
                          failed_login_attempts = 0,
                          locked_until = NULL,
                          updated_at = CURRENT_TIMESTAMP
                      WHERE id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":password_hash", $password_hash);
            $stmt->bindParam(":user_id", $user_id);
            
            if ($stmt->execute()) {
                $this->logActivity(null, 'reset_password', 'auth', $user_id);
                return ['success' => true, 'message' => 'Reset mật khẩu thành công'];
            }
            
            return ['success' => false, 'message' => 'Lỗi reset mật khẩu'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi reset mật khẩu: ' . $e->getMessage()];
        }
    }

    // Toggle user active status
    public function toggleUserStatus($user_id, $is_active) {
        try {
            $query = "UPDATE " . $this->users_table . " 
                      SET is_active = :is_active, updated_at = CURRENT_TIMESTAMP
                      WHERE id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":is_active", $is_active, PDO::PARAM_BOOL);
            $stmt->bindParam(":user_id", $user_id);
            
            if ($stmt->execute()) {
                $action = $is_active ? 'activate_user' : 'deactivate_user';
                $this->logActivity(null, $action, 'auth', $user_id);
                
                $message = $is_active ? 'Kích hoạt tài khoản thành công' : 'Vô hiệu hóa tài khoản thành công';
                return ['success' => true, 'message' => $message];
            }
            
            return ['success' => false, 'message' => 'Lỗi cập nhật trạng thái tài khoản'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi cập nhật trạng thái: ' . $e->getMessage()];
        }
    }

    // Check if username/email exists
    public function checkUserExists($username, $email, $exclude_id = null) {
        try {
            $query = "SELECT id FROM " . $this->users_table . " 
                      WHERE (username = :username OR email = :email)";
            
            if ($exclude_id) {
                $query .= " AND id != :exclude_id";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":email", $email);
            
            if ($exclude_id) {
                $stmt->bindParam(":exclude_id", $exclude_id);
            }
            
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            return false;
        }
    }

    // Update user role
    public function updateUserRole($user_id, $role_id) {
        try {
            $query = "UPDATE " . $this->users_table . " 
                      SET role_id = :role_id, updated_at = CURRENT_TIMESTAMP
                      WHERE id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":role_id", $role_id);
            $stmt->bindParam(":user_id", $user_id);
            
            if ($stmt->execute()) {
                $this->logActivity(null, 'update_user_role', 'auth', $user_id);
                return ['success' => true, 'message' => 'Cập nhật vai trò thành công'];
            }
            
            return ['success' => false, 'message' => 'Lỗi cập nhật vai trò'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi cập nhật vai trò: ' . $e->getMessage()];
        }
    }

    // Get user by ID
    public function getUserById($user_id) {
        try {
            $query = "SELECT u.*, r.name as role_name, r.display_name as role_display_name,
                             nv.ho_ten as employee_name, nv.ma_nhan_vien as employee_code
                      FROM " . $this->users_table . " u
                      LEFT JOIN " . $this->roles_table . " r ON u.role_id = r.id
                      LEFT JOIN nhan_vien nv ON u.nhan_vien_id = nv.id
                      WHERE u.id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return null;
        }
    }

    // Refresh token
    public function refreshToken($refresh_token) {
        try {
            // Kiểm tra refresh token
            $query = "SELECT s.*, u.id as user_id, u.username, u.email, u.full_name, u.avatar,
                             r.name as role_name, r.display_name as role_display_name
                      FROM " . $this->sessions_table . " s
                      JOIN " . $this->users_table . " u ON s.user_id = u.id
                      LEFT JOIN " . $this->roles_table . " r ON u.role_id = r.id
                      WHERE s.refresh_token = :refresh_token 
                      AND u.is_active = true
                      AND s.expires_at > CURRENT_TIMESTAMP";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":refresh_token", $refresh_token);
            $stmt->execute();
            
            $session = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$session) {
                return ['success' => false, 'message' => 'Refresh token không hợp lệ'];
            }
            
            // Tạo token mới
            $new_session_token = $this->generateToken();
            $new_refresh_token = $this->generateToken();
            $new_expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Cập nhật session
            $updateQuery = "UPDATE " . $this->sessions_table . " 
                           SET session_token = :new_session_token,
                               refresh_token = :new_refresh_token,
                               expires_at = :new_expires_at
                           WHERE id = :session_id";
            
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":new_session_token", $new_session_token);
            $updateStmt->bindParam(":new_refresh_token", $new_refresh_token);
            $updateStmt->bindParam(":new_expires_at", $new_expires_at);
            $updateStmt->bindParam(":session_id", $session['id']);
            $updateStmt->execute();
            
            return [
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $session['user_id'],
                        'username' => $session['username'],
                        'email' => $session['email'],
                        'full_name' => $session['full_name'],
                        'avatar' => $session['avatar'],
                        'role' => $session['role_name'],
                        'role_display' => $session['role_display_name']
                    ],
                    'session_token' => $new_session_token,
                    'refresh_token' => $new_refresh_token,
                    'expires_at' => $new_expires_at
                ]
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi refresh token: ' . $e->getMessage()];
        }
    }
}
?>