-- Thêm vào database schema cho authentication và authorization

-- Bảng roles (vai trò)
CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng permissions (quyền)
CREATE TABLE permissions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    module VARCHAR(50) NOT NULL, -- employees, departments, branches, assets
    action VARCHAR(50) NOT NULL, -- create, read, update, delete, export, import
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng role_permissions (phân quyền cho vai trò)
CREATE TABLE role_permissions (
    id SERIAL PRIMARY KEY,
    role_id INTEGER REFERENCES roles(id) ON DELETE CASCADE,
    permission_id INTEGER REFERENCES permissions(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(role_id, permission_id)
);

-- Bảng users (người dùng)
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    avatar VARCHAR(255),
    role_id INTEGER REFERENCES roles(id) ON DELETE SET NULL,
    nhan_vien_id INTEGER REFERENCES nhan_vien(id) ON DELETE SET NULL,
    is_active BOOLEAN DEFAULT true,
    last_login TIMESTAMP,
    failed_login_attempts INTEGER DEFAULT 0,
    locked_until TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng user_sessions (phiên đăng nhập)
CREATE TABLE user_sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    refresh_token VARCHAR(255) UNIQUE,
    ip_address INET,
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng activity_logs (nhật ký hoạt động)
CREATE TABLE activity_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(100) NOT NULL,
    module VARCHAR(50) NOT NULL,
    record_id INTEGER,
    old_data JSONB,
    new_data JSONB,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes để tối ưu hiệu suất
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role_id);
CREATE INDEX idx_user_sessions_token ON user_sessions(session_token);
CREATE INDEX idx_user_sessions_user ON user_sessions(user_id);
CREATE INDEX idx_activity_logs_user ON activity_logs(user_id);
CREATE INDEX idx_activity_logs_module ON activity_logs(module);
CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at);

-- Trigger để cập nhật updated_at cho users
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Thêm dữ liệu mẫu cho roles
INSERT INTO roles (name, display_name, description) VALUES
('super_admin', 'Super Admin', 'Quyền cao nhất, có thể làm tất cả'),
('admin', 'Administrator', 'Quản trị viên hệ thống'),
('hr_manager', 'HR Manager', 'Quản lý nhân sự'),
('department_manager', 'Department Manager', 'Trưởng phòng'),
('employee', 'Employee', 'Nhân viên thường'),
('viewer', 'Viewer', 'Chỉ xem, không chỉnh sửa');

-- Thêm dữ liệu mẫu cho permissions
INSERT INTO permissions (name, display_name, description, module, action) VALUES
-- Employee permissions
('employees.create', 'Tạo nhân viên', 'Có thể tạo nhân viên mới', 'employees', 'create'),
('employees.read', 'Xem nhân viên', 'Có thể xem danh sách nhân viên', 'employees', 'read'),
('employees.update', 'Sửa nhân viên', 'Có thể chỉnh sửa thông tin nhân viên', 'employees', 'update'),
('employees.delete', 'Xóa nhân viên', 'Có thể xóa nhân viên', 'employees', 'delete'),
('employees.export', 'Export nhân viên', 'Có thể export danh sách nhân viên', 'employees', 'export'),
('employees.import', 'Import nhân viên', 'Có thể import nhân viên từ Excel', 'employees', 'import'),

-- Department permissions
('departments.create', 'Tạo phòng ban', 'Có thể tạo phòng ban mới', 'departments', 'create'),
('departments.read', 'Xem phòng ban', 'Có thể xem danh sách phòng ban', 'departments', 'read'),
('departments.update', 'Sửa phòng ban', 'Có thể chỉnh sửa thông tin phòng ban', 'departments', 'update'),
('departments.delete', 'Xóa phòng ban', 'Có thể xóa phòng ban', 'departments', 'delete'),

-- Branch permissions
('branches.create', 'Tạo chi nhánh', 'Có thể tạo chi nhánh mới', 'branches', 'create'),
('branches.read', 'Xem chi nhánh', 'Có thể xem danh sách chi nhánh', 'branches', 'read'),
('branches.update', 'Sửa chi nhánh', 'Có thể chỉnh sửa thông tin chi nhánh', 'branches', 'update'),
('branches.delete', 'Xóa chi nhánh', 'Có thể xóa chi nhánh', 'branches', 'delete'),

-- Asset permissions
('assets.create', 'Tạo tài sản', 'Có thể tạo tài sản mới', 'assets', 'create'),
('assets.read', 'Xem tài sản', 'Có thể xem danh sách tài sản', 'assets', 'read'),
('assets.update', 'Sửa tài sản', 'Có thể chỉnh sửa thông tin tài sản', 'assets', 'update'),
('assets.delete', 'Xóa tài sản', 'Có thể xóa tài sản', 'assets', 'delete'),
('assets.allocate', 'Cấp phát tài sản', 'Có thể cấp phát tài sản cho nhân viên', 'assets', 'allocate'),
('assets.recall', 'Thu hồi tài sản', 'Có thể thu hồi tài sản từ nhân viên', 'assets', 'recall'),
('assets.export', 'Export tài sản', 'Có thể export danh sách tài sản', 'assets', 'export'),
('assets.import', 'Import tài sản', 'Có thể import tài sản từ Excel', 'assets', 'import'),

-- System permissions
('users.manage', 'Quản lý người dùng', 'Có thể quản lý tài khoản người dùng', 'users', 'manage'),
('roles.manage', 'Quản lý vai trò', 'Có thể quản lý vai trò và phân quyền', 'roles', 'manage'),
('logs.view', 'Xem nhật ký', 'Có thể xem nhật ký hoạt động', 'logs', 'view'),
('dashboard.view', 'Xem dashboard', 'Có thể xem trang tổng quan', 'dashboard', 'view');

-- Phân quyền cho Super Admin (tất cả quyền)
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- Phân quyền cho Admin (hầu hết quyền, trừ quản lý super admin)
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE name != 'users.manage';

-- Phân quyền cho HR Manager
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE name IN (
    'employees.create', 'employees.read', 'employees.update', 'employees.export', 'employees.import',
    'departments.read', 'branches.read',
    'dashboard.view'
);

-- Phân quyền cho Department Manager
INSERT INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions WHERE name IN (
    'employees.read', 'employees.update',
    'departments.read', 'branches.read',
    'assets.read', 'assets.allocate', 'assets.recall',
    'dashboard.view'
);

-- Phân quyền cho Employee
INSERT INTO role_permissions (role_id, permission_id)
SELECT 5, id FROM permissions WHERE name IN (
    'employees.read', 'departments.read', 'branches.read', 'assets.read',
    'dashboard.view'
);

-- Phân quyền cho Viewer
INSERT INTO role_permissions (role_id, permission_id)
SELECT 6, id FROM permissions WHERE action = 'read' OR name = 'dashboard.view';

-- Tạo user admin mặc định
-- Password: admin123 (đã hash bằng password_hash function)
INSERT INTO users (username, email, password_hash, full_name, role_id, is_active) VALUES
('admin', 'admin@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 1, true),
('hr_manager', 'hr@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR Manager', 3, true);

-- Thêm cột user_id vào các bảng để track ai tạo/sửa
ALTER TABLE nhan_vien ADD COLUMN created_by INTEGER REFERENCES users(id);
ALTER TABLE nhan_vien ADD COLUMN updated_by INTEGER REFERENCES users(id);

ALTER TABLE phong_ban ADD COLUMN created_by INTEGER REFERENCES users(id);
ALTER TABLE phong_ban ADD COLUMN updated_by INTEGER REFERENCES users(id);

ALTER TABLE chi_nhanh ADD COLUMN created_by INTEGER REFERENCES users(id);
ALTER TABLE chi_nhanh ADD COLUMN updated_by INTEGER REFERENCES users(id);

ALTER TABLE tai_san ADD COLUMN created_by INTEGER REFERENCES users(id);
ALTER TABLE tai_san ADD COLUMN updated_by INTEGER REFERENCES users(id);

-- Function để check permission
CREATE OR REPLACE FUNCTION user_has_permission(user_id INTEGER, permission_name VARCHAR)
RETURNS BOOLEAN AS $$
DECLARE
    has_perm BOOLEAN := FALSE;
BEGIN
    SELECT COUNT(*) > 0 INTO has_perm
    FROM users u
    JOIN roles r ON u.role_id = r.id
    JOIN role_permissions rp ON r.id = rp.role_id
    JOIN permissions p ON rp.permission_id = p.id
    WHERE u.id = user_id 
      AND u.is_active = true
      AND p.name = permission_name;
    
    RETURN has_perm;
END;
$$ LANGUAGE plpgsql;

-- Function để log activity
CREATE OR REPLACE FUNCTION log_activity(
    p_user_id INTEGER,
    p_action VARCHAR,
    p_module VARCHAR,
    p_record_id INTEGER DEFAULT NULL,
    p_old_data JSONB DEFAULT NULL,
    p_new_data JSONB DEFAULT NULL,
    p_ip_address INET DEFAULT NULL,
    p_user_agent TEXT DEFAULT NULL
) RETURNS VOID AS $$
BEGIN
    INSERT INTO activity_logs (
        user_id, action, module, record_id, 
        old_data, new_data, ip_address, user_agent
    ) VALUES (
        p_user_id, p_action, p_module, p_record_id,
        p_old_data, p_new_data, p_ip_address, p_user_agent
    );
END;
$$ LANGUAGE plpgsql;