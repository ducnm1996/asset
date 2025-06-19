<?php
// models/Branch.php
require_once '../config/database.php';

class Branch {
    private $conn;
    private $table_name = "chi_nhanh";

    public $id;
    public $ten_chi_nhanh;
    public $dia_chi;
    public $sdt;
    public $email;
    public $truong_chi_nhanh;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy tất cả chi nhánh
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Tìm kiếm chi nhánh
    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE ten_chi_nhanh ILIKE :keyword 
                     OR dia_chi ILIKE :keyword
                     OR truong_chi_nhanh ILIKE :keyword
                  ORDER BY id DESC";
        
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();
        return $stmt;
    }

    // Lấy thông tin chi nhánh theo ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo chi nhánh mới
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  (ten_chi_nhanh, dia_chi, sdt, email, truong_chi_nhanh)
                  VALUES
                  (:ten_chi_nhanh, :dia_chi, :sdt, :email, :truong_chi_nhanh)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->ten_chi_nhanh = htmlspecialchars(strip_tags($this->ten_chi_nhanh));
        $this->dia_chi = htmlspecialchars(strip_tags($this->dia_chi));
        $this->sdt = htmlspecialchars(strip_tags($this->sdt));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->truong_chi_nhanh = htmlspecialchars(strip_tags($this->truong_chi_nhanh));

        // Bind values
        $stmt->bindParam(":ten_chi_nhanh", $this->ten_chi_nhanh);
        $stmt->bindParam(":dia_chi", $this->dia_chi);
        $stmt->bindParam(":sdt", $this->sdt);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":truong_chi_nhanh", $this->truong_chi_nhanh);

        return $stmt->execute();
    }

    // Cập nhật chi nhánh
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET ten_chi_nhanh = :ten_chi_nhanh,
                      dia_chi = :dia_chi,
                      sdt = :sdt,
                      email = :email,
                      truong_chi_nhanh = :truong_chi_nhanh,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->ten_chi_nhanh = htmlspecialchars(strip_tags($this->ten_chi_nhanh));
        $this->dia_chi = htmlspecialchars(strip_tags($this->dia_chi));
        $this->sdt = htmlspecialchars(strip_tags($this->sdt));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->truong_chi_nhanh = htmlspecialchars(strip_tags($this->truong_chi_nhanh));

        // Bind values
        $stmt->bindParam(":ten_chi_nhanh", $this->ten_chi_nhanh);
        $stmt->bindParam(":dia_chi", $this->dia_chi);
        $stmt->bindParam(":sdt", $this->sdt);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":truong_chi_nhanh", $this->truong_chi_nhanh);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Xóa chi nhánh
    public function delete() {
        // Kiểm tra xem có nhân viên nào đang thuộc chi nhánh này không
        $checkQuery = "SELECT COUNT(*) as count FROM nhan_vien WHERE chi_nhanh_id = :id";
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

    // Lấy số lượng nhân viên trong chi nhánh
    public function getEmployeeCount($id) {
        $query = "SELECT COUNT(*) as count FROM nhan_vien WHERE chi_nhanh_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    // Lấy danh sách chi nhánh với số lượng nhân viên
    public function getAllWithEmployeeCount() {
        $query = "SELECT cn.*, COUNT(nv.id) as so_nhan_vien
                  FROM " . $this->table_name . " cn
                  LEFT JOIN nhan_vien nv ON cn.id = nv.chi_nhanh_id
                  GROUP BY cn.id, cn.ten_chi_nhanh, cn.dia_chi, cn.sdt, cn.email, cn.truong_chi_nhanh, cn.created_at, cn.updated_at
                  ORDER BY cn.id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}