<?php
// api/index.php - Updated with authentication and authorization
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/database.php';
require_once '../models/Employee.php';
require_once '../models/Department.php';
require_once '../models/Branch.php';
require_once '../models/Asset.php';
require_once '../middleware/AuthMiddleware.php';

session_start();

$database = new Database();
$db = $database->getConnection();
$authMiddleware = new AuthMiddleware($db);

// Lấy method và endpoint
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$endpoint = $request[0] ?? '';
$id = $request[1] ?? null;

try {
    switch ($endpoint) {
        case 'employees':
            handleEmployeesWithAuth($db, $authMiddleware, $method, $id);
            break;
        case 'departments':
            handleDepartmentsWithAuth($db, $authMiddleware, $method, $id);
            break;
        case 'branches':
            handleBranchesWithAuth($db, $authMiddleware, $method, $id);
            break;
        case 'assets':
            handleAssetsWithAuth($db, $authMiddleware, $method, $id);
            break;
        case 'allocate':
            handleAssetAllocationWithAuth($db, $authMiddleware, $method);
            break;
        case 'recall':
            handleAssetRecallWithAuth($db, $authMiddleware, $method);
            break;
        case 'import':
            handleImportWithAuth($db, $authMiddleware, $method);
            break;
        case 'export':
            handleExportWithAuth($db, $authMiddleware, $method);
            break;
        default:
            jsonResponse(null, false, 'Endpoint không tồn tại');
    }
} catch (Exception $e) {
    jsonResponse(null, false, 'Lỗi server: ' . $e->getMessage());
}

// Xử lý API cho nhân viên với authentication
function handleEmployeesWithAuth($db, $authMiddleware, $method, $id) {
    $employee = new Employee($db);
    
    switch ($method) {
        case 'GET':
            if (!$authMiddleware->requirePermission('employees.read')) return;
            
            if ($id) {
                $result = $employee->getById($id);
                if ($result) {
                    jsonResponse($result);
                } else {
                    jsonResponse(null, false, 'Không tìm thấy nhân viên');
                }
            } else {
                $search = $_GET['search'] ?? '';
                if ($search) {
                    $stmt = $employee->search($search);
                } else {
                    $stmt = $employee->getAllWithDetails();
                }
                
                $employees = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $employees[] = $row;
                }
                jsonResponse($employees);
            }
            break;
            
        case 'POST':
            if (!$authMiddleware->requirePermission('employees.create')) return;
            
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Validate required fields
            if (empty($data['ho_ten']) || empty($data['ma_nhan_vien'])) {
                jsonResponse(null, false, 'Họ tên và mã nhân viên là bắt buộc');
                return;
            }
            
            // Check if employee code exists
            if ($employee->checkMaNhanVienExists($data['ma_nhan_vien'])) {
                jsonResponse(null, false, 'Mã nhân viên đã tồn tại');
                return;
            }
            
            // Validate email if provided
            if (!empty($data['email']) && !validateEmail($data['email'])) {
                jsonResponse(null, false, 'Email không hợp lệ');
                return;
            }
            
            // Set employee data
            $employee->ho_ten = $data['ho_ten'];
            $employee->ma_nhan_vien = $data['ma_nhan_vien'];
            $employee->ngay_sinh = $data['ngay_sinh'] ?? null;
            $employee->sdt = $data['sdt'] ?? null;
            $employee->email = $data['email'] ?? null;
            $employee->gioi_tinh = $data['gioi_tinh'] ?? 'Nam';
            $employee->phong_ban_id = $data['phong_ban_id'] ?? null;
            $employee->chi_nhanh_id = $data['chi_nhanh_id'] ?? null;
            $employee->ngay_vao_lam = $data['ngay_vao_lam'] ?? date('Y-m-d');
            $employee->trang_thai_lam_viec = $data['trang_thai_lam_viec'] ?? 'Đang làm việc';
            
            if ($employee->create()) {
                $newId = $db->lastInsertId();
                $authMiddleware->logActivity('create', 'employees', $newId, null, $data);
                jsonResponse(['id' => $newId], true, 'Tạo nhân viên thành công');
            } else {
                jsonResponse(null, false, 'Lỗi khi tạo nhân viên');
            }
            break;
            
        case 'PUT':
            if (!$authMiddleware->requirePermission('employees.update')) return;
            
            if (!$id) {
                jsonResponse(null, false, 'ID nhân viên là bắt buộc');
                return;
            }
            
            // Get old data for logging
            $oldData = $employee->getById($id);
            
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Validate required fields
            if (empty($data['ho_ten']) || empty($data['ma_nhan_vien'])) {
                jsonResponse(null, false, 'Họ tên và mã nhân viên là bắt buộc');
                return;
            }
            
            // Check if employee code exists (excluding current employee)
            if ($employee->checkMaNhanVienExists($data['ma_nhan_vien'], $id)) {
                jsonResponse(null, false, 'Mã nhân viên đã tồn tại');
                return;
            }
            
            // Validate email if provided
            if (!empty($data['email']) && !validateEmail($data['email'])) {
                jsonResponse(null, false, 'Email không hợp lệ');
                return;
            }
            
            $employee->id = $id;
            $employee->ho_ten = $data['ho_ten'];
            $employee->ma_nhan_vien = $data['ma_nhan_vien'];
            $employee->ngay_sinh = $data['ngay_sinh'] ?? null;
            $employee->sdt = $data['sdt'] ?? null;
            $employee->email = $data['email'] ?? null;
            $employee->gioi_tinh = $data['gioi_tinh'] ?? 'Nam';
            $employee->phong_ban_id = $data['phong_ban_id'] ?? null;
            $employee->chi_nhanh_id = $data['chi_nhanh_id'] ?? null;
            $employee->ngay_vao_lam = $data['ngay_vao_lam'] ?? date('Y-m-d');
            $employee->trang_thai_lam_viec = $data['trang_thai_lam_viec'] ?? 'Đang làm việc';
            
            if ($employee->update()) {
                $authMiddleware->logActivity('update', 'employees', $id, $oldData, $data);
                jsonResponse(null, true, 'Cập nhật nhân viên thành công');
            } else {
                jsonResponse(null, false, 'Lỗi khi cập nhật nhân viên');
            }
            break;
            
        case 'DELETE':
            if (!$authMiddleware->requirePermission('employees.delete')) return;
            
            if (!$id) {
                jsonResponse(null, false, 'ID nhân viên là bắt buộc');
                return;
            }
            
            $oldData = $employee->getById($id);
            $employee->id = $id;
            
            if ($employee->delete()) {
                $authMiddleware->logActivity('delete', 'employees', $id, $oldData);
                jsonResponse(null, true, 'Xóa nhân viên thành công');
            } else {
                jsonResponse(null, false, 'Lỗi khi xóa nhân viên');
            }
            break;
            
        default:
            jsonResponse(null, false, 'Method không được hỗ trợ');
    }
}

// Xử lý API cho phòng ban với authentication
function handleDepartmentsWithAuth($db, $authMiddleware, $method, $id) {
    $department = new Department($db);
    
    switch ($method) {
        case 'GET':
            if (!$authMiddleware->requirePermission('departments.read')) return;
            
            if ($id) {
                $result = $department->getById($id);
                if ($result) {
                    jsonResponse($result);
                } else {
                    jsonResponse(null, false, 'Không tìm thấy phòng ban');
                }
            } else {
                $search = $_GET['search'] ?? '';
                if ($search) {
                    $stmt = $department->search($search);
                } else {
                    $stmt = $department->getAllWithEmployeeCount();
                }
                
                $departments = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $departments[] = $row;
                }
                jsonResponse($departments);
            }
            break;
            
        case 'POST':
            if (!$authMiddleware->requirePermission('departments.create')) return;
            
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (empty($data['ten_phong']) || empty($data['ma_phong'])) {
                jsonResponse(null, false, 'Tên phòng và mã phòng là bắt buộc');
                return;
            }
            
            if ($department->checkMaPhongExists($data['ma_phong'])) {
                jsonResponse(null, false, 'Mã phòng đã tồn tại');
                return;
            }
            
            $department->ten_phong = $data['ten_phong'];
            $department->ma_phong = $data['ma_phong'];
            $department->ghi_chu = $data['ghi_chu'] ?? '';
            
            if ($department->create()) {
                $newId = $db->lastInsertId();
                $authMiddleware->logActivity('create', 'departments', $newId, null, $data);
                jsonResponse(['id' => $newId], true, 'Tạo phòng ban thành công');
            } else {
                jsonResponse(null, false, 'Lỗi khi tạo phòng ban');
            }
            break;
            
        case 'PUT':
            if (!$authMiddleware->requirePermission('departments.update')) return;
            
            if (!$id) {
                jsonResponse(null, false, 'ID phòng ban là bắt buộc');
                return;
            }
            
            $oldData = $department->getById($id);
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (empty($data['ten_phong']) || empty($data['ma_phong'])) {
                jsonResponse(null, false, 'Tên phòng và mã phòng là bắt buộc');
                return;
            }
            
            if ($department->checkMaPhongExists($data['ma_phong'], $id)) {
                jsonResponse(null, false, 'Mã phòng đã tồn tại');
                return;
            }
            
            $department->id = $id;
            $department->ten_phong = $data['ten_phong'];
            $department->ma_phong = $data['ma_phong'];
            $department->ghi_chu = $data['ghi_chu'] ?? '';
            
            if ($department->update()) {
                $authMiddleware->logActivity('update', 'departments', $id, $oldData, $data);
                jsonResponse(null, true, 'Cập nhật phòng ban thành công');
            } else {
                jsonResponse(null, false, 'Lỗi khi cập nhật phòng ban');
            }
            break;
            
        case 'DELETE':
            if (!$authMiddleware->requirePermission('departments.delete')) return;
            
            if (!$id) {
                jsonResponse(null, false, 'ID phòng ban là bắt buộc');
                return;
            }
            
            $oldData = $department->getById($id);
            $department->id = $id;
            
            if ($department->delete()) {
                $authMiddleware->logActivity('delete', 'departments', $id, $oldData);
                jsonResponse(null, true, 'Xóa phòng ban thành công');
            } else {
                jsonResponse(null, false, 'Không thể xóa phòng ban vì còn nhân viên thuộc phòng ban này');
            }
            break;
            
        default:
            jsonResponse(null, false, 'Method không được hỗ trợ');
    }
}

// Xử lý API cho chi nhánh với authentication
function handleBranchesWithAuth($db, $authMiddleware, $method, $id) {
    $branch = new Branch($db);
    
    switch ($method) {
        case 'GET':
            if (!$authMiddleware->requirePermission('branches.read')) return;
            
            if ($id) {
                $result = $branch->getById($id);
                if ($result) {
                    jsonResponse($result);
                } else {
                    jsonResponse(null, false, 'Không tìm thấy chi nhánh');
                }
            } else {
                $search = $_GET['search'] ?? '';
                if ($search) {
                    $stmt = $branch->search($search);
                } else {
                    $stmt = $branch->getAllWithEmployeeCount();
                }
                
                $branches = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $branches[] = $row;
                }
                jsonResponse($branches);
            }
            break;
            
        case 'POST':
            if (!$authMiddleware->requirePermission('branches.create')) return;
            
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (empty($data['ten_chi_nhanh'])) {
                jsonResponse(null, false, 'Tên chi nhánh là bắt buộc');
                return;
            }
            
            $branch->ten_chi_nhanh = $data['ten_chi_nhanh'];
            $branch->dia_chi = $data['dia_chi'] ?? '';
            $branch->sdt = $data['sdt'] ?? '';
            $branch->email = $data['email'] ?? '';
            $branch->truong_chi_nhanh = $data['truong_chi_nhanh'] ?? '';
            
            if ($branch->create()) {
                $newId = $db->lastInsertId();
                $authMiddleware->logActivity('create', 'branches', $newId, null, $data);
                jsonResponse(['id' => $newId], true, 'Tạo chi nhánh thành công');
            } else {
                jsonResponse(null, false, 'Lỗi khi tạo chi nhánh');
            }
            break;
            
        case 'PUT':
            if (!$authMiddleware->requirePermission('branches.update')) return;
            
            if (!$id) {
                jsonResponse(null, false, 'ID chi nhánh là bắt buộc');
                return;
            }
            
            $oldData = $branch->getById($id);
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (empty($data['ten_chi_nhanh'])) {
                jsonResponse(null, false, 'Tên chi nhánh là bắt buộc');
                return;
            }
            
            $branch->id = $id;
            $branch->ten_chi_nhanh = $data['ten_chi_nhanh'];
            $branch->dia_chi = $data['dia_chi'] ?? '';
            $branch->sdt = $data['sdt'] ?? '';
            $branch->email = $data['email'] ?? '';
            $branch->truong_chi_nhanh = $data['truong_chi_nhanh'] ?? '';
            
            if ($branch->update()) {
                $authMiddleware->logActivity('update', 'branches', $id, $oldData, $data);
                jsonResponse(null, true, 'Cập nhật chi nhánh thành công');
            } else {
                jsonResponse(null, false, 'Lỗi khi cập nhật chi nhánh');
            }
            break;
            
        case 'DELETE':
            if (!$authMiddleware->requirePermission('branches.delete')) return;
            
            if (!$id) {
                jsonResponse(null, false, 'ID chi nhánh là bắt buộc');
                return;
            }
            
            $oldData = $branch->getById($id);
            $branch->id = $id;
            
            if ($branch->delete()) {
                $authMiddleware->logActivity('delete', 'branches', $id, $oldData);
                jsonResponse(null, true, 'Xóa chi nhánh thành công');
            } else {
                jsonResponse(null, false, 'Không thể xóa chi nhánh vì còn nhân viên thuộc chi nhánh này');
            }
            break;
            
        default:
            jsonResponse(null, false, 'Method không được hỗ trợ');
    }
}

// Xử lý API cho tài sản với authentication
function handleAssetsWithAuth($db, $authMiddleware, $method, $id) {
    $asset = new Asset($db);
    
    switch ($method) {
        case 'GET':
            if (!$authMiddleware->requirePermission('assets.read')) return;
            
            if ($id) {
                $result = $asset->getById($id);
                if ($result) {
                    jsonResponse($result);
                } else {
                    jsonResponse(null, false, 'Không tìm thấy tài sản');
                }
            } else {
                $search = $_GET['search'] ?? '';
                $status = $_GET['status'] ?? '';
                
                if ($search) {
                    $stmt = $asset->search($search);
                } elseif ($status) {
                    $stmt = $asset->getByStatus($status);
                } else {
                    $stmt = $asset->getAllWithDetails();
                }
                
                $assets = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $assets[] = $row;
                }
                jsonResponse($assets);
            }
            break;
            
        case 'POST':
            if (!$authMiddleware->requirePermission('assets.create')) return;
            
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (empty($data['ten_tai_san']) || empty($data['ma_tai_san'])) {
                jsonResponse(null, false, 'Tên tài sản và mã tài sản là bắt buộc');
                return;
            }
            
            if ($asset->checkMaTaiSanExists($data['ma_tai_san'])) {
                jsonResponse(null, false, 'Mã tài sản đã tồn tại');
                return;
            }
            
            $asset->ten_tai_san = $data['ten_tai_san'];
            $asset->ma_tai_san = $data['ma_tai_san'];
            $asset->loai_tai_san = $data['loai_tai_san'] ?? '';
            $asset->ngay_mua = $data['ngay_mua'] ?? date('Y-m-d');
            $asset->tinh_trang = $data['tinh_trang'] ?? 'Mới';
            $asset->nhan_vien_id = $data['nhan_vien_id'] ?? null;
            $asset->vi_tri_phong = $data['vi_tri_phong'] ?? '';
            $asset->vi_tri_chi_nhanh_id = $data['vi_tri_chi_nhanh_id'] ?? null;
            $asset->so_luong_ton_kho = $data['so_luong_ton_kho'] ?? 1;
            $asset->gia_tri = $data['gia_tri'] ?? null;
            $asset->ghi_chu = $data['ghi_chu'] ?? '';
            
            if ($asset->create()) {
                $newId = $db->lastInsertId();
                $authMiddleware->logActivity('create', 'assets', $newId, null, $data);
                jsonResponse(['id' => $newId], true, 'Tạo tài sản thành công');
            } else {
                jsonResponse(null, false, 'Lỗi khi tạo tài sản');
            }
            break;
            
        case 'PUT':
            if (!$authMiddleware->requirePermission('assets.update')) return;
            
            if (!$id) {
                jsonResponse(null, false, 'ID tài sản là bắt buộc');
                return;
            }
            
            $oldData = $asset->getById($id);
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (empty($data['ten_tai_san']) || empty($data['ma_tai_san'])) {
                jsonResponse(null, false, 'Tên tài sản và mã tài sản là bắt buộc');
                return;
            }
            
            if ($asset->checkMaTaiSanExists($data['ma_tai_san'], $id)) {
                jsonResponse(null, false, 'Mã tài sản đã tồn tại');
                return;
            }
            
            $asset->id = $id;
            $asset->ten_tai_san = $data['ten_tai_san'];
            $asset->ma_tai_san = $data['ma_tai_san'];
            $asset->loai_tai_san = $data['loai_tai_san'] ?? '';
            $asset->ngay_mua = $data['ngay_mua'] ?? date('Y-m-d');
            $asset->tinh_trang = $data['tinh_trang'] ?? 'Mới';
            $asset->nhan_vien_id = $data['nhan_vien_id'] ?? null;
            $asset->vi_tri_phong = $data['vi_tri_phong'] ?? '';
            $asset->vi_tri_chi_nhanh_id = $data['vi_tri_chi_nhanh_id'] ?? null;
            $asset->so_luong_ton_kho = $data['so_luong_ton_kho'] ?? 1;
            $asset->gia_tri = $data['gia_tri'] ?? null;
            $asset->ghi_chu = $data['ghi_chu'] ?? '';
            
            if ($asset->update()) {
                $authMiddleware->logActivity('update', 'assets', $id, $oldData, $data);
                jsonResponse(null, true, 'Cập nhật tài sản thành công');
            } else {
                jsonResponse(null, false, 'Lỗi khi cập nhật tài sản');
            }
            break;
            
        case 'DELETE':
            if (!$authMiddleware->requirePermission('assets.delete')) return;
            
            if (!$id) {
                jsonResponse(null, false, 'ID tài sản là bắt buộc');
                return;
            }
            
            $oldData = $asset->getById($id);
            $asset->id = $id;
            
            if ($asset->delete()) {
                $authMiddleware->logActivity('delete', 'assets', $id, $oldData);
                jsonResponse(null, true, 'Xóa tài sản thành công');
            } else {
                jsonResponse(null, false, 'Lỗi khi xóa tài sản');
            }
            break;
            
        default:
            jsonResponse(null, false, 'Method không được hỗ trợ');
    }
}

// Xử lý cấp phát tài sản với authentication
function handleAssetAllocationWithAuth($db, $authMiddleware, $method) {
    if ($method !== 'POST') {
        jsonResponse(null, false, 'Chỉ hỗ trợ POST method');
        return;
    }
    
    if (!$authMiddleware->requirePermission('assets.allocate')) return;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['asset_id']) || empty($data['employee_id'])) {
        jsonResponse(null, false, 'ID tài sản và ID nhân viên là bắt buộc');
        return;
    }
    
    $asset = new Asset($db);
    $reason = $data['reason'] ?? '';
    
    if ($asset->allocateToEmployee($data['asset_id'], $data['employee_id'], $reason)) {
        $authMiddleware->logActivity('allocate', 'assets', $data['asset_id'], null, $data);
        jsonResponse(null, true, 'Cấp phát tài sản thành công');
    } else {
        jsonResponse(null, false, 'Lỗi khi cấp phát tài sản');
    }
}

// Xử lý thu hồi tài sản với authentication
function handleAssetRecallWithAuth($db, $authMiddleware, $method) {
    if ($method !== 'POST') {
        jsonResponse(null, false, 'Chỉ hỗ trợ POST method');
        return;
    }
    
    if (!$authMiddleware->requirePermission('assets.recall')) return;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['asset_id'])) {
        jsonResponse(null, false, 'ID tài sản là bắt buộc');
        return;
    }
    
    $asset = new Asset($db);
    $reason = $data['reason'] ?? '';
    
    if ($asset->recallFromEmployee($data['asset_id'], $reason)) {
        $authMiddleware->logActivity('recall', 'assets', $data['asset_id'], null, $data);
        jsonResponse(null, true, 'Thu hồi tài sản thành công');
    } else {
        jsonResponse(null, false, 'Lỗi khi thu hồi tài sản');
    }
}

// Xử lý import với authentication
function handleImportWithAuth($db, $authMiddleware, $method) {
    if ($method !== 'POST') {
        jsonResponse(null, false, 'Chỉ hỗ trợ POST method');
        return;
    }
    
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        jsonResponse(null, false, 'Lỗi upload file');
        return;
    }
    
    $type = $_POST['type'] ?? '';
    if (!in_array($type, ['employees', 'assets'])) {
        jsonResponse(null, false, 'Loại import không hợp lệ');
        return;
    }
    
    // Check permissions
    $permission = $type . '.import';
    if (!$authMiddleware->requirePermission($permission)) return;
    
    // Process Excel file
    require_once '../vendor/autoload.php'; // PhpSpreadsheet
    
    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES['file']['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = $worksheet->toArray();
        
        // Remove header row
        $headers = array_shift($data);
        
        // Convert to associative array
        $importData = [];
        foreach ($data as $row) {
            $rowData = [];
            foreach ($headers as $index => $header) {
                $rowData[trim($header)] = $row[$index] ?? '';
            }
            $importData[] = $rowData;
        }
        
        if ($type === 'employees') {
            $employee = new Employee($db);
            $result = $employee->importFromArray($importData);
        } else {
            $asset = new Asset($db);
            $result = $asset->importFromArray($importData);
        }
        
        // Log import activity
        $authMiddleware->logActivity('import', $type, null, null, [
            'file_name' => $_FILES['file']['name'],
            'imported_count' => $result['imported'] ?? 0,
            'error_count' => count($result['errors'] ?? [])
        ]);
        
        jsonResponse($result, $result['success'], $result['message'] ?? '');
        
    } catch (Exception $e) {
        jsonResponse(null, false, 'Lỗi xử lý file Excel: ' . $e->getMessage());
    }
}

// Xử lý export với authentication
function handleExportWithAuth($db, $authMiddleware, $method) {
    if ($method !== 'GET') {
        jsonResponse(null, false, 'Chỉ hỗ trợ GET method');
        return;
    }
    
    $type = $_GET['type'] ?? '';
    if (!in_array($type, ['employees', 'departments', 'branches', 'assets'])) {
        jsonResponse(null, false, 'Loại export không hợp lệ');
        return;
    }
    
    // Check permissions
    $permission = $type . '.export';
    if ($type === 'employees' || $type === 'assets') {
        if (!$authMiddleware->requirePermission($permission)) return;
    } else {
        // For departments and branches, check read permission
        $permission = $type . '.read';
        if (!$authMiddleware->requirePermission($permission)) return;
    }
    
    require_once '../vendor/autoload.php'; // PhpSpreadsheet
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    try {
        switch ($type) {
            case 'employees':
                $employee = new Employee($db);
                $stmt = $employee->getAllWithDetails();
                
                // Set headers
                $sheet->setCellValue('A1', 'ID');
                $sheet->setCellValue('B1', 'Họ tên');
                $sheet->setCellValue('C1', 'Mã nhân viên');
                $sheet->setCellValue('D1', 'Ngày sinh');
                $sheet->setCellValue('E1', 'SĐT');
                $sheet->setCellValue('F1', 'Email');
                $sheet->setCellValue('G1', 'Giới tính');
                $sheet->setCellValue('H1', 'Phòng ban');
                $sheet->setCellValue('I1', 'Chi nhánh');
                $sheet->setCellValue('J1', 'Ngày vào làm');
                $sheet->setCellValue('K1', 'Trạng thái');
                
                $row = 2;
                while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $sheet->setCellValue('A' . $row, $data['id']);
                    $sheet->setCellValue('B' . $row, $data['ho_ten']);
                    $sheet->setCellValue('C' . $row, $data['ma_nhan_vien']);
                    $sheet->setCellValue('D' . $row, $data['ngay_sinh']);
                    $sheet->setCellValue('E' . $row, $data['sdt']);
                    $sheet->setCellValue('F' . $row, $data['email']);
                    $sheet->setCellValue('G' . $row, $data['gioi_tinh']);
                    $sheet->setCellValue('H' . $row, $data['ten_phong']);
                    $sheet->setCellValue('I' . $row, $data['ten_chi_nhanh']);
                    $sheet->setCellValue('J' . $row, $data['ngay_vao_lam']);
                    $sheet->setCellValue('K' . $row, $data['trang_thai_lam_viec']);
                    $row++;
                }
                $filename = 'danh_sach_nhan_vien.xlsx';
                break;
                
            case 'departments':
                $department = new Department($db);
                $stmt = $department->getAllWithEmployeeCount();
                
                $sheet->setCellValue('A1', 'ID');
                $sheet->setCellValue('B1', 'Tên phòng');
                $sheet->setCellValue('C1', 'Mã phòng');
                $sheet->setCellValue('D1', 'Số nhân viên');
                $sheet->setCellValue('E1', 'Ghi chú');
                
                $row = 2;
                while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $sheet->setCellValue('A' . $row, $data['id']);
                    $sheet->setCellValue('B' . $row, $data['ten_phong']);
                    $sheet->setCellValue('C' . $row, $data['ma_phong']);
                    $sheet->setCellValue('D' . $row, $data['so_nhan_vien']);
                    $sheet->setCellValue('E' . $row, $data['ghi_chu']);
                    $row++;
                }
                $filename = 'danh_sach_phong_ban.xlsx';
                break;
                
            case 'branches':
                $branch = new Branch($db);
                $stmt = $branch->getAllWithEmployeeCount();
                
                $sheet->setCellValue('A1', 'ID');
                $sheet->setCellValue('B1', 'Tên chi nhánh');
                $sheet->setCellValue('C1', 'Địa chỉ');
                $sheet->setCellValue('D1', 'SĐT');
                $sheet->setCellValue('E1', 'Email');
                $sheet->setCellValue('F1', 'Trưởng chi nhánh');
                $sheet->setCellValue('G1', 'Số nhân viên');
                
                $row = 2;
                while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $sheet->setCellValue('A' . $row, $data['id']);
                    $sheet->setCellValue('B' . $row, $data['ten_chi_nhanh']);
                    $sheet->setCellValue('C' . $row, $data['dia_chi']);
                    $sheet->setCellValue('D' . $row, $data['sdt']);
                    $sheet->setCellValue('E' . $row, $data['email']);
                    $sheet->setCellValue('F' . $row, $data['truong_chi_nhanh']);
                    $sheet->setCellValue('G' . $row, $data['so_nhan_vien']);
                    $row++;
                }
                $filename = 'danh_sach_chi_nhanh.xlsx';
                break;
                
            case 'assets':
                $asset = new Asset($db);
                $stmt = $asset->getAllWithDetails();
                
                $sheet->setCellValue('A1', 'ID');
                $sheet->setCellValue('B1', 'Tên tài sản');
                $sheet->setCellValue('C1', 'Mã tài sản');
                $sheet->setCellValue('D1', 'Loại tài sản');
                $sheet->setCellValue('E1', 'Ngày mua');
                $sheet->setCellValue('F1', 'Tình trạng');
                $sheet->setCellValue('G1', 'Nhân viên sử dụng');
                $sheet->setCellValue('H1', 'Vị trí phòng');
                $sheet->setCellValue('I1', 'Chi nhánh');
                $sheet->setCellValue('J1', 'Số lượng tồn');
                $sheet->setCellValue('K1', 'Giá trị');
                
                $row = 2;
                while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $sheet->setCellValue('A' . $row, $data['id']);
                    $sheet->setCellValue('B' . $row, $data['ten_tai_san']);
                    $sheet->setCellValue('C' . $row, $data['ma_tai_san']);
                    $sheet->setCellValue('D' . $row, $data['loai_tai_san']);
                    $sheet->setCellValue('E' . $row, $data['ngay_mua']);
                    $sheet->setCellValue('F' . $row, $data['tinh_trang']);
                    $sheet->setCellValue('G' . $row, $data['ten_nhan_vien']);
                    $sheet->setCellValue('H' . $row, $data['vi_tri_phong']);
                    $sheet->setCellValue('I' . $row, $data['ten_chi_nhanh']);
                    $sheet->setCellValue('J' . $row, $data['so_luong_ton_kho']);
                    $sheet->setCellValue('K' . $row, $data['gia_tri']);
                    $row++;
                }
                $filename = 'danh_sach_tai_san.xlsx';
                break;
        }
        
        // Log export activity
        $authMiddleware->logActivity('export', $type, null, null, ['filename' => $filename]);
        
        // Output file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        
    } catch (Exception $e) {
        jsonResponse(null, false, 'Lỗi tạo file Excel: ' . $e->getMessage());
    }
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

// Validation functions
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^[0-9+\-\(\)\s]{10,15}$/', $phone);
}
?>