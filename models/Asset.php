<?php
// models/Asset.php
require_once '../config/database.php';

class Asset {
    private $conn;
    private $table_name = "tai_san";

    public $id;
    public $ten_tai_san;
    public $ma_tai_san;
    public $loai_tai_san;
    public $ngay_mua;
    public $tinh_trang;
    public $nhan_vien_id;
    public $vi_tri_phong;
    public $vi_tri_chi_nhanh_id;
    public $so_luong_ton_kho;
    public $gia_tri;
    public $ghi_chu;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy tất cả tài sản với thông tin chi tiết
    public function getAllWithDetails() {
        $query = "SELECT ts.*, nv.ho_ten as ten_nhan_vien, nv.ma_nhan_vien, 
                         cn.ten_chi_nhanh
                  FROM " . $this->table_name . " ts
                  LEFT JOIN nhan_vien nv ON ts.nhan_vien_id = nv.id
                  LEFT JOIN chi_nhanh cn ON ts.vi_tri_chi_nhanh_id = cn.id
                  ORDER BY ts.id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Tìm kiếm tài sản
    public function search($keyword) {
        $query = "SELECT ts.*, nv.ho_ten as ten_nhan_vien, nv.ma_nhan_vien, 
                         cn.ten_chi_nhanh
                  FROM " . $this->table_name . " ts
                  LEFT JOIN nhan_vien nv ON ts.nhan_vien_id = nv.id
                  LEFT JOIN chi_nhanh cn ON ts.vi_tri_chi_nhanh_id = cn.id
                  WHERE ts.ten_tai_san ILIKE :keyword 
                     OR ts.ma_tai_san ILIKE :keyword
                     OR ts.loai_tai_san ILIKE :keyword
                     OR nv.ho_ten ILIKE :keyword
                  ORDER BY ts.id DESC";
        
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();
        return $stmt;
    }

    // Lấy thông tin tài sản theo ID
    public function getById($id) {
        $query = "SELECT ts.*, nv.ho_ten as ten_nhan_vien, nv.ma_nhan_vien, 
                         cn.ten_chi_nhanh
                  FROM " . $this->table_name . " ts
                  LEFT JOIN nhan_vien nv ON ts.nhan_vien_id = nv.id
                  LEFT JOIN chi_nhanh cn ON ts.vi_tri_chi_nhanh_id = cn.id
                  WHERE ts.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo tài sản mới
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  (ten_tai_san, ma_tai_san, loai_tai_san, ngay_mua, tinh_trang, 
                   nhan_vien_id, vi_tri_phong, vi_tri_chi_nhanh_id, so_luong_ton_kho, 
                   gia_tri, ghi_chu)
                  VALUES
                  (:ten_tai_san, :ma_tai_san, :loai_tai_san, :ngay_mua, :tinh_trang,
                   :nhan_vien_id, :vi_tri_phong, :vi_tri_chi_nhanh_id, :so_luong_ton_kho,
                   :gia_tri, :ghi_chu)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->ten_tai_san = htmlspecialchars(strip_tags($this->ten_tai_san));
        $this->ma_tai_san = htmlspecialchars(strip_tags($this->ma_tai_san));
        $this->loai_tai_san = htmlspecialchars(strip_tags($this->loai_tai_san));
        $this->tinh_trang = htmlspecialchars(strip_tags($this->tinh_trang));
        $this->vi_tri_phong = htmlspecialchars(strip_tags($this->vi_tri_phong));
        $this->ghi_chu = htmlspecialchars(strip_tags($this->ghi_chu));

        // Bind values
        $stmt->bindParam(":ten_tai_san", $this->ten_tai_san);
        $stmt->bindParam(":ma_tai_san", $this->ma_tai_san);
        $stmt->bindParam(":loai_tai_san", $this->loai_tai_san);
        $stmt->bindParam(":ngay_mua", $this->ngay_mua);
        $stmt->bindParam(":tinh_trang", $this->tinh_trang);
        $stmt->bindParam(":nhan_vien_id", $this->nhan_vien_id);
        $stmt->bindParam(":vi_tri_phong", $this->vi_tri_phong);
        $stmt->bindParam(":vi_tri_chi_nhanh_id", $this->vi_tri_chi_nhanh_id);
        $stmt->bindParam(":so_luong_ton_kho", $this->so_luong_ton_kho);
        $stmt->bindParam(":gia_tri", $this->gia_tri);
        $stmt->bindParam(":ghi_chu", $this->ghi_chu);

        return $stmt->execute();
    }

    // Cập nhật tài sản
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET ten_tai_san = :ten_tai_san,
                      ma_tai_san = :ma_tai_san,
                      loai_tai_san = :loai_tai_san,
                      ngay_mua = :ngay_mua,
                      tinh_trang = :tinh_trang,
                      nhan_vien_id = :nhan_vien_id,
                      vi_tri_phong = :vi_tri_phong,
                      vi_tri_chi_nhanh_id = :vi_tri_chi_nhanh_id,
                      so_luong_ton_kho = :so_luong_ton_kho,
                      gia_tri = :gia_tri,
                      ghi_chu = :ghi_chu,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->ten_tai_san = htmlspecialchars(strip_tags($this->ten_tai_san));
        $this->ma_tai_san = htmlspecialchars(strip_tags($this->ma_tai_san));
        $this->loai_tai_san = htmlspecialchars(strip_tags($this->loai_tai_san));
        $this->tinh_trang = htmlspecialchars(strip_tags($this->tinh_trang));
        $this->vi_tri_phong = htmlspecialchars(strip_tags($this->vi_tri_phong));
        $this->ghi_chu = htmlspecialchars(strip_tags($this->ghi_chu));

        // Bind values
        $stmt->bindParam(":ten_tai_san", $this->ten_tai_san);
        $stmt->bindParam(":ma_tai_san", $this->ma_tai_san);
        $stmt->bindParam(":loai_tai_san", $this->loai_tai_san);
        $stmt->bindParam(":ngay_mua", $this->ngay_mua);
        $stmt->bindParam(":tinh_trang", $this->tinh_trang);
        $stmt->bindParam(":nhan_vien_id", $this->nhan_vien_id);
        $stmt->bindParam(":vi_tri_phong", $this->vi_tri_phong);
        $stmt->bindParam(":vi_tri_chi_nhanh_id", $this->vi_tri_chi_nhanh_id);
        $stmt->bindParam(":so_luong_ton_kho", $this->so_luong_ton_kho);
        $stmt->bindParam(":gia_tri", $this->gia_tri);
        $stmt->bindParam(":ghi_chu", $this->ghi_chu);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Xóa tài sản
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    // Kiểm tra mã tài sản đã tồn tại
    public function checkMaTaiSanExists($ma_tai_san, $exclude_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE ma_tai_san = :ma_tai_san";
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ma_tai_san", $ma_tai_san);
        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Cấp phát tài sản cho nhân viên
    public function allocateToEmployee($asset_id, $employee_id, $reason = '') {
        $this->conn->beginTransaction();
        try {
            // Cập nhật tài sản
            $updateQuery = "UPDATE " . $this->table_name . " 
                           SET nhan_vien_id = :employee_id, 
                               tinh_trang = 'Đang dùng',
                               updated_at = CURRENT_TIMESTAMP
                           WHERE id = :asset_id";
            
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":employee_id", $employee_id);
            $updateStmt->bindParam(":asset_id", $asset_id);
            $updateStmt->execute();

            // Ghi lịch sử cấp phát
            $historyQuery = "INSERT INTO lich_su_cap_phat 
                            (tai_san_id, nhan_vien_id, ngay_cap_phat, trang_thai, ly_do)
                            VALUES (:asset_id, :employee_id, CURRENT_DATE, 'Đang sử dụng', :reason)";
            
            $historyStmt = $this->conn->prepare($historyQuery);
            $historyStmt->bindParam(":asset_id", $asset_id);
            $historyStmt->bindParam(":employee_id", $employee_id);
            $historyStmt->bindParam(":reason", $reason);
            $historyStmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    // Thu hồi tài sản từ nhân viên
    public function recallFromEmployee($asset_id, $reason = '') {
        $this->conn->beginTransaction();
        try {
            // Lấy thông tin tài sản hiện tại
            $getAssetQuery = "SELECT nhan_vien_id FROM " . $this->table_name . " WHERE id = :asset_id";
            $getAssetStmt = $this->conn->prepare($getAssetQuery);
            $getAssetStmt->bindParam(":asset_id", $asset_id);
            $getAssetStmt->execute();
            $asset = $getAssetStmt->fetch(PDO::FETCH_ASSOC);

            if (!$asset || !$asset['nhan_vien_id']) {
                throw new Exception("Tài sản không được cấp phát cho ai");
            }

            // Cập nhật tài sản
            $updateQuery = "UPDATE " . $this->table_name . " 
                           SET nhan_vien_id = NULL, 
                               tinh_trang = 'Mới',
                               updated_at = CURRENT_TIMESTAMP
                           WHERE id = :asset_id";
            
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":asset_id", $asset_id);
            $updateStmt->execute();

            // Cập nhật lịch sử thu hồi
            $historyQuery = "UPDATE lich_su_cap_phat 
                            SET ngay_thu_hoi = CURRENT_DATE, 
                                trang_thai = 'Đã thu hồi',
                                ly_do = CASE WHEN ly_do IS NULL OR ly_do = '' 
                                            THEN :reason 
                                            ELSE ly_do || '; Thu hồi: ' || :reason 
                                        END
                            WHERE tai_san_id = :asset_id 
                              AND nhan_vien_id = :employee_id 
                              AND trang_thai = 'Đang sử dụng'";
            
            $historyStmt = $this->conn->prepare($historyQuery);
            $historyStmt->bindParam(":asset_id", $asset_id);
            $historyStmt->bindParam(":employee_id", $asset['nhan_vien_id']);
            $historyStmt->bindParam(":reason", $reason);
            $historyStmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    // Lấy lịch sử cấp phát của tài sản
    public function getAllocationHistory($asset_id) {
        $query = "SELECT lscp.*, nv.ho_ten, nv.ma_nhan_vien
                  FROM lich_su_cap_phat lscp
                  JOIN nhan_vien nv ON lscp.nhan_vien_id = nv.id
                  WHERE lscp.tai_san_id = :asset_id
                  ORDER BY lscp.ngay_cap_phat DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":asset_id", $asset_id);
        $stmt->execute();
        return $stmt;
    }

    // Lấy tài sản theo trạng thái
    public function getByStatus($status) {
        $query = "SELECT ts.*, nv.ho_ten as ten_nhan_vien, nv.ma_nhan_vien, 
                         cn.ten_chi_nhanh
                  FROM " . $this->table_name . " ts
                  LEFT JOIN nhan_vien nv ON ts.nhan_vien_id = nv.id
                  LEFT JOIN chi_nhanh cn ON ts.vi_tri_chi_nhanh_id = cn.id
                  WHERE ts.tinh_trang = :status
                  ORDER BY ts.id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();
        return $stmt;
    }

    // Import từ Excel data
    public function importFromArray($data) {
        $this->conn->beginTransaction();
        try {
            $successful = 0;
            $errors = [];
            
            foreach ($data as $index => $row) {
                // Validate required fields
                if (empty($row['ten_tai_san']) || empty($row['ma_tai_san'])) {
                    $errors[] = "Dòng " . ($index + 1) . ": Thiếu tên tài sản hoặc mã tài sản";
                    continue;
                }
                
                // Check if asset code already exists
                if ($this->checkMaTaiSanExists($row['ma_tai_san'])) {
                    $errors[] = "Dòng " . ($index + 1) . ": Mã tài sản '{$row['ma_tai_san']}' đã tồn tại";
                    continue;
                }
                
                $this->ten_tai_san = $row['ten_tai_san'];
                $this->ma_tai_san = $row['ma_tai_san'];
                $this->loai_tai_san = $row['loai_tai_san'] ?? '';
                $this->ngay_mua = !empty($row['ngay_mua']) ? $row['ngay_mua'] : date('Y-m-d');
                $this->tinh_trang = $row['tinh_trang'] ?? 'Mới';
                $this->nhan_vien_id = !empty($row['nhan_vien_id']) ? $row['nhan_vien_id'] : null;
                $this->vi_tri_phong = $row['vi_tri_phong'] ?? '';
                $this->vi_tri_chi_nhanh_id = !empty($row['vi_tri_chi_nhanh_id']) ? $row['vi_tri_chi_nhanh_id'] : null;
                $this->so_luong_ton_kho = $row['so_luong_ton_kho'] ?? 1;
                $this->gia_tri = !empty($row['gia_tri']) ? $row['gia_tri'] : null;
                $this->ghi_chu = $row['ghi_chu'] ?? '';
                
                if ($this->create()) {
                    $successful++;
                } else {
                    $errors[] = "Dòng " . ($index + 1) . ": Lỗi khi tạo tài sản";
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