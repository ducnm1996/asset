<?php
// models/Employee.php
require_once '../config/database.php';

class Employee {
    private $conn;
    private $table_name = "nhan_vien";

    public $id;
    public $ho_ten;
    public $ma_nhan_vien;
    public $ngay_sinh;
    public $sdt;
    public $email;
    public $gioi_tinh;
    public $phong_ban_id;
    public $chi_nhanh_id;
    public $ngay_vao_lam;
    public $trang_thai_lam_viec;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy tất cả nhân viên với thông tin phòng ban và chi nhánh
    public function getAllWithDetails() {
        $query = "SELECT nv.*, pb.ten_phong, cn.ten_chi_nhanh 
                  FROM " . $this->table_name . " nv
                  LEFT JOIN phong_ban pb ON nv.phong_ban_id = pb.id
                  LEFT JOIN chi_nhanh cn ON nv.chi_nhanh_id = cn.id
                  ORDER BY nv.id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Tìm kiếm nhân viên
    public function search($keyword) {
        $query = "SELECT nv.*, pb.ten_phong, cn.ten_chi_nhanh 
                  FROM " . $this->table_name . " nv
                  LEFT JOIN phong_ban pb ON nv.phong_ban_id = pb.id
                  LEFT JOIN chi_nhanh cn ON nv.chi_nhanh_id = cn.id
                  WHERE nv.ho_ten ILIKE :keyword 
                     OR nv.ma_nhan_vien ILIKE :keyword
                     OR nv.email ILIKE :keyword
                     OR nv.sdt ILIKE :keyword
                  ORDER BY nv.id DESC";
        
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();
        return $stmt;
    }

    // Lấy thông tin nhân viên theo ID
    public function getById($id) {
        $query = "SELECT nv.*, pb.ten_phong, cn.ten_chi_nhanh 
                  FROM " . $this->table_name . " nv
                  LEFT JOIN phong_ban pb ON nv.phong_ban_id = pb.id
                  LEFT JOIN chi_nhanh cn ON nv.chi_nhanh_id = cn.id
                  WHERE nv.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo nhân viên mới
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  (ho_ten, ma_nhan_vien, ngay_sinh, sdt, email, gioi_tinh, 
                   phong_ban_id, chi_nhanh_id, ngay_vao_lam, trang_thai_lam_viec)
                  VALUES
                  (:ho_ten, :ma_nhan_vien, :ngay_sinh, :sdt, :email, :gioi_tinh,
                   :phong_ban_id, :chi_nhanh_id, :ngay_vao_lam, :trang_thai_lam_viec)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->ho_ten = htmlspecialchars(strip_tags($this->ho_ten));
        $this->ma_nhan_vien = htmlspecialchars(strip_tags($this->ma_nhan_vien));
        $this->sdt = htmlspecialchars(strip_tags($this->sdt));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->gioi_tinh = htmlspecialchars(strip_tags($this->gioi_tinh));
        $this->trang_thai_lam_viec = htmlspecialchars(strip_tags($this->trang_thai_lam_viec));

        // Bind values
        $stmt->bindParam(":ho_ten", $this->ho_ten);
        $stmt->bindParam(":ma_nhan_vien", $this->ma_nhan_vien);
        $stmt->bindParam(":ngay_sinh", $this->ngay_sinh);
        $stmt->bindParam(":sdt", $this->sdt);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":gioi_tinh", $this->gioi_tinh);
        $stmt->bindParam(":phong_ban_id", $this->phong_ban_id);
        $stmt->bindParam(":chi_nhanh_id", $this->chi_nhanh_id);
        $stmt->bindParam(":ngay_vao_lam", $this->ngay_vao_lam);
        $stmt->bindParam(":trang_thai_lam_viec", $this->trang_thai_lam_viec);

        return $stmt->execute();
    }

    // Cập nhật nhân viên
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET ho_ten = :ho_ten,
                      ma_nhan_vien = :ma_nhan_vien,
                      ngay_sinh = :ngay_sinh,
                      sdt = :sdt,
                      email = :email,
                      gioi_tinh = :gioi_tinh,
                      phong_ban_id = :phong_ban_id,
                      chi_nhanh_id = :chi_nhanh_id,
                      ngay_vao_lam = :ngay_vao_lam,
                      trang_thai_lam_viec = :trang_thai_lam_viec,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->ho_ten = htmlspecialchars(strip_tags($this->ho_ten));
        $this->ma_nhan_vien = htmlspecialchars(strip_tags($this->ma_nhan_vien));
        $this->sdt = htmlspecialchars(strip_tags($this->sdt));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->gioi_tinh = htmlspecialchars(strip_tags($this->gioi_tinh));
        $this->trang_thai_lam_viec = htmlspecialchars(strip_tags($this->trang_thai_lam_viec));

        // Bind values
        $stmt->bindParam(":ho_ten", $this->ho_ten);
        $stmt->bindParam(":ma_nhan_vien", $this->ma_nhan_vien);
        $stmt->bindParam(":ngay_sinh", $this->ngay_sinh);
        $stmt->bindParam(":sdt", $this->sdt);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":gioi_tinh", $this->gioi_tinh);
        $stmt->bindParam(":phong_ban_id", $this->phong_ban_id);
        $stmt->bindParam(":chi_nhanh_id", $this->chi_nhanh_id);
        $stmt->bindParam(":ngay_vao_lam", $this->ngay_vao_lam);
        $stmt->bindParam(":trang_thai_lam_viec", $this->trang_thai_lam_viec);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Xóa nhân viên
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    // Kiểm tra mã nhân viên đã tồn tại
    public function checkMaNhanVienExists($ma_nhan_vien, $exclude_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE ma_nhan_vien = :ma_nhan_vien";
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ma_nhan_vien", $ma_nhan_vien);
        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Import từ Excel data
    public function importFromArray($data) {
        $this->conn->beginTransaction();
        try {
            $successful = 0;
            $errors = [];
            
            foreach ($data as $index => $row) {
                // Validate required fields
                if (empty($row['ho_ten']) || empty($row['ma_nhan_vien'])) {
                    $errors[] = "Dòng " . ($index + 1) . ": Thiếu họ tên hoặc mã nhân viên";
                    continue;
                }
                
                // Check if employee code already exists
                if ($this->checkMaNhanVienExists($row['ma_nhan_vien'])) {
                    $errors[] = "Dòng " . ($index + 1) . ": Mã nhân viên '{$row['ma_nhan_vien']}' đã tồn tại";
                    continue;
                }
                
                $this->ho_ten = $row['ho_ten'];
                $this->ma_nhan_vien = $row['ma_nhan_vien'];
                $this->ngay_sinh = !empty($row['ngay_sinh']) ? $row['ngay_sinh'] : null;
                $this->sdt = $row['sdt'] ?? null;
                $this->email = $row['email'] ?? null;
                $this->gioi_tinh = $row['gioi_tinh'] ?? 'Nam';
                $this->phong_ban_id = !empty($row['phong_ban_id']) ? $row['phong_ban_id'] : null;
                $this->chi_nhanh_id = !empty($row['chi_nhanh_id']) ? $row['chi_nhanh_id'] : null;
                $this->ngay_vao_lam = !empty($row['ngay_vao_lam']) ? $row['ngay_vao_lam'] : date('Y-m-d');
                $this->trang_thai_lam_viec = $row['trang_thai_lam_viec'] ?? 'Đang làm việc';
                
                if ($this->create()) {
                    $successful++;
                } else {
                    $errors[] = "Dòng " . ($index + 1) . ": Lỗi khi tạo nhân viên";
                }
            }
            
            $this->conn->commit();
            return [
                'success' => true,
                'imported' => $successful,
                'errors' => $errors
            ];
        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                'success' => false,
                'message' => 'Lỗi import: ' . $e->getMessage()
            ];
        }
    }
}
?>