<?php
// models/Department.php
require_once '../config/database.php';

class Department {
    private $conn;
    private $table_name = "phong_ban";

    public $id;
    public $ten_phong;
    public $ma_phong;
    public $ghi_chu;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy tất cả phòng ban
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Tìm kiếm phòng ban
    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE ten_phong ILIKE :keyword 
                     OR ma_phong ILIKE :keyword
                  ORDER BY id DESC";
        
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();
        return $stmt;
    }

    // Lấy thông tin phòng ban theo ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo phòng ban mới
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  (ten_phong, ma_phong, ghi_chu)
                  VALUES
                  (:ten_phong, :ma_phong, :ghi_chu)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->ten_phong = htmlspecialchars(strip_tags($this->ten_phong));
        $this->ma_phong = htmlspecialchars(strip_tags($this->ma_phong));
        $this->ghi_chu = htmlspecialchars(strip_tags($this->ghi_chu));

        // Bind values
        $stmt->bindParam(":ten_phong", $this->ten_phong);
        $stmt->bindParam(":ma_phong", $this->ma_phong);
        $stmt->bindParam(":ghi_chu", $this->ghi_chu);

        return $stmt->execute();
    }

    // Cập nhật phòng ban
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET ten_phong = :ten_phong,
                      ma_phong = :ma_phong,
                      ghi_chu = :ghi_chu,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->ten_phong = htmlspecialchars(strip_tags($this->ten_phong));
        $this->ma_phong = htmlspecialchars(strip_tags($this->ma_phong));
        $this->ghi_chu = htmlspecialchars(strip_tags($this->ghi_chu));

        // Bind values
        $stmt->bindParam(":ten_phong", $this->ten_phong);
        $stmt->bindParam(":ma_phong", $this->ma_phong);
        $stmt->bindParam(":ghi_chu", $this->ghi_chu);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Xóa phòng ban
    public function delete() {
        // Kiểm tra xem có nhân viên nào đang thuộc phòng ban này không
        $checkQuery = "SELECT COUNT(*) as count FROM nhan_vien WHERE phong_ban_id = :id";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":id", $this->id);
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            return false; // Không thể xóa vì còn nhân viên
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    // Kiểm tra mã phòng đã tồn tại
    public function checkMaPhongExists($ma_phong, $exclude_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE ma_phong = :ma_phong";
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ma_phong", $ma_phong);
        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Lấy số lượng nhân viên trong phòng ban
    public function getEmployeeCount($id) {
        $query = "SELECT COUNT(*) as count FROM nhan_vien WHERE phong_ban_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    // Lấy danh sách phòng ban với số lượng nhân viên
    public function getAllWithEmployeeCount() {
        $query = "SELECT pb.*, COUNT(nv.id) as so_nhan_vien
                  FROM " . $this->table_name . " pb
                  LEFT JOIN nhan_vien nv ON pb.id = nv.phong_ban_id
                  GROUP BY pb.id, pb.ten_phong, pb.ma_phong, pb.ghi_chu, pb.created_at, pb.updated_at
                  ORDER BY pb.id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>