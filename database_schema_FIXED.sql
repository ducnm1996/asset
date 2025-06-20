-- =====================================================
-- Hệ thống Quản lý Nhân viên và Tài sản
-- Database Schema cho PostgreSQL
-- Version: 2.0 (Updated with optimizations)
-- =====================================================

-- Tạo database (chạy riêng nếu cần)
-- CREATE DATABASE employee_management;
-- \c employee_management;

-- Tạo extension để hỗ trợ UUID và full-text search
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";

-- Xóa các đối tượng cũ nếu tồn tại (để tránh lỗi duplicate)
DROP TABLE IF EXISTS hop_dong_lao_dong CASCADE;
DROP TABLE IF EXISTS bao_tri_tai_san CASCADE;
DROP TABLE IF EXISTS lich_su_cap_phat CASCADE;
DROP TABLE IF EXISTS tai_san CASCADE;
DROP TABLE IF EXISTS nhan_vien CASCADE;
DROP TABLE IF EXISTS phong_ban CASCADE;
DROP TABLE IF EXISTS chi_nhanh CASCADE;
DROP TYPE IF EXISTS tinh_trang_tai_san CASCADE;
DROP TYPE IF EXISTS loai_tai_san_enum CASCADE;
DROP MATERIALIZED VIEW IF EXISTS mv_dashboard_stats CASCADE;

-- =====================================================
-- ENUM VÀ TYPES CHO TÀI SẢN
-- =====================================================
CREATE TYPE tinh_trang_tai_san AS ENUM (
    'Mới', 
    'Đang dùng', 
    'Hỏng', 
    'Thanh lý', 
    'Bảo trì',
    'Chờ cấp phát',
    'Đã thu hồi'
);

CREATE TYPE loai_tai_san_enum AS ENUM (
    'Máy tính',
    'Laptop', 
    'Điện thoại',
    'Máy in',
    'Máy photocopy',
    'Thiết bị mạng',
    'Phần mềm',
    'Xe cộ',
    'Nội thất văn phòng',
    'Thiết bị điện tử',
    'Khác'
);

-- =====================================================
-- BẢNG CHI NHÁNH (BRANCHES)
-- =====================================================
CREATE TABLE chi_nhanh (
    id SERIAL PRIMARY KEY,
    ten_chi_nhanh VARCHAR(255) NOT NULL CHECK (LENGTH(TRIM(ten_chi_nhanh)) > 0),
    dia_chi TEXT,
    sdt VARCHAR(20),
    email VARCHAR(255),
    truong_chi_nhanh VARCHAR(255),
    ma_chi_nhanh VARCHAR(50) UNIQUE,
    trang_thai BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints
    CONSTRAINT chi_nhanh_email_check CHECK (email IS NULL OR email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'),
    CONSTRAINT chi_nhanh_sdt_check CHECK (sdt IS NULL OR sdt ~ '^[0-9+\-\(\)\s]{8,20}$')
);

-- =====================================================
-- BẢNG PHÒNG BAN (DEPARTMENTS)
-- =====================================================
CREATE TABLE phong_ban (
    id SERIAL PRIMARY KEY,
    ten_phong VARCHAR(255) NOT NULL CHECK (LENGTH(TRIM(ten_phong)) > 0),
    ma_phong VARCHAR(50) UNIQUE NOT NULL CHECK (LENGTH(TRIM(ma_phong)) > 0),
    ghi_chu TEXT,
    chi_nhanh_id INTEGER REFERENCES chi_nhanh(id) ON DELETE SET NULL,
    truong_phong_id INTEGER, -- Sẽ được link sau khi tạo bảng nhân viên
    so_luong_nv INTEGER DEFAULT 0,
    trang_thai BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- BẢNG NHÂN VIÊN (EMPLOYEES)
-- =====================================================
CREATE TABLE nhan_vien (
    id SERIAL PRIMARY KEY,
    ho_ten VARCHAR(255) NOT NULL CHECK (LENGTH(TRIM(ho_ten)) > 0),
    ma_nhan_vien VARCHAR(50) UNIQUE NOT NULL CHECK (LENGTH(TRIM(ma_nhan_vien)) > 0),
    ngay_sinh DATE,
    sdt VARCHAR(20),
    email VARCHAR(255),
    gioi_tinh VARCHAR(10) DEFAULT 'Nam' CHECK (gioi_tinh IN ('Nam', 'Nữ', 'Khác')),
    dia_chi TEXT,
    so_cccd VARCHAR(20),
    so_bhxh VARCHAR(20),
    
    -- Thông tin công việc
    phong_ban_id INTEGER REFERENCES phong_ban(id) ON DELETE SET NULL,
    chi_nhanh_id INTEGER REFERENCES chi_nhanh(id) ON DELETE SET NULL,
    chuc_vu VARCHAR(100),
    cap_bac VARCHAR(50),
    ngay_vao_lam DATE DEFAULT CURRENT_DATE,
    ngay_nghi_viec DATE,
    trang_thai_lam_viec VARCHAR(20) DEFAULT 'Đang làm việc' 
        CHECK (trang_thai_lam_viec IN ('Đang làm việc', 'Nghỉ việc', 'Tạm nghỉ', 'Nghỉ thai sản')),
    
    -- Thông tin lương
    luong_co_ban DECIMAL(15,2),
    luong_dong_bhxh DECIMAL(15,2),
    phu_cap DECIMAL(15,2),
    
    -- Thông tin cá nhân
    dan_toc VARCHAR(50),
    ton_giao VARCHAR(50),
    que_quan TEXT,
    noi_sinh TEXT,
    trinh_do_hoc_van VARCHAR(100),
    chuyen_mon VARCHAR(100),
    ngoai_ngu VARCHAR(100),
    
    -- Thông tin liên hệ khẩn cấp
    nguoi_lien_he_khan_cap VARCHAR(255),
    sdt_khan_cap VARCHAR(20),
    moi_quan_he_khan_cap VARCHAR(50),
    
    -- Metadata
    anh_dai_dien TEXT, -- URL hoặc path đến ảnh
    ghi_chu TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints
    CONSTRAINT nhan_vien_email_check CHECK (email IS NULL OR email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'),
    CONSTRAINT nhan_vien_sdt_check CHECK (sdt IS NULL OR sdt ~ '^[0-9+\-\(\)\s]{8,20}$'),
    CONSTRAINT nhan_vien_ngay_sinh_check CHECK (ngay_sinh IS NULL OR ngay_sinh >= '1900-01-01'),
    CONSTRAINT nhan_vien_ngay_vao_lam_check CHECK (ngay_vao_lam >= '1900-01-01'),
    CONSTRAINT nhan_vien_ngay_nghi_check CHECK (ngay_nghi_viec IS NULL OR ngay_nghi_viec >= ngay_vao_lam),
    CONSTRAINT nhan_vien_luong_check CHECK (luong_co_ban IS NULL OR luong_co_ban >= 0)
);

-- Thêm foreign key cho trưởng phòng
ALTER TABLE phong_ban ADD CONSTRAINT fk_truong_phong 
    FOREIGN KEY (truong_phong_id) REFERENCES nhan_vien(id) ON DELETE SET NULL;

-- =====================================================
-- BẢNG TÀI SẢN (ASSETS)
-- =====================================================
CREATE TABLE tai_san (
    id SERIAL PRIMARY KEY,
    ten_tai_san VARCHAR(255) NOT NULL CHECK (LENGTH(TRIM(ten_tai_san)) > 0),
    ma_tai_san VARCHAR(50) UNIQUE NOT NULL CHECK (LENGTH(TRIM(ma_tai_san)) > 0),
    loai_tai_san loai_tai_san_enum DEFAULT 'Khác',
    hang_san_xuat VARCHAR(100),
    model VARCHAR(100),
    serial_number VARCHAR(100),
    
    -- Thông tin mua sắm
    ngay_mua DATE DEFAULT CURRENT_DATE,
    nha_cung_cap VARCHAR(255),
    gia_mua DECIMAL(15,2),
    thoi_gian_bao_hanh INTEGER, -- Tháng
    ngay_het_bao_hanh DATE,
    
    -- Trạng thái và vị trí
    tinh_trang tinh_trang_tai_san DEFAULT 'Mới',
    nhan_vien_id INTEGER REFERENCES nhan_vien(id) ON DELETE SET NULL,
    vi_tri_phong VARCHAR(255),
    vi_tri_chi_nhanh_id INTEGER REFERENCES chi_nhanh(id) ON DELETE SET NULL,
    vi_tri_cu_the TEXT, -- Mô tả chi tiết vị trí
    
    -- Thông tin kỹ thuật
    thong_so_ky_thuat JSONB, -- Lưu thông số kỹ thuật dạng JSON
    so_luong_ton_kho INTEGER DEFAULT 1 CHECK (so_luong_ton_kho >= 0),
    don_vi_tinh VARCHAR(20) DEFAULT 'Cái',
    
    -- Thông tin khấu hao
    gia_tri_hien_tai DECIMAL(15,2),
    ty_le_khau_hao DECIMAL(5,2), -- Phần trăm khấu hao hàng năm
    gia_tri_con_lai DECIMAL(15,2),
    
    -- Metadata
    anh_tai_san TEXT[], -- Mảng chứa URLs ảnh
    ghi_chu TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints
    CONSTRAINT tai_san_gia_mua_check CHECK (gia_mua IS NULL OR gia_mua >= 0),
    CONSTRAINT tai_san_gia_tri_check CHECK (gia_tri_hien_tai IS NULL OR gia_tri_hien_tai >= 0),
    CONSTRAINT tai_san_khau_hao_check CHECK (ty_le_khau_hao IS NULL OR (ty_le_khau_hao >= 0 AND ty_le_khau_hao <= 100))
);

-- =====================================================
-- BẢNG LỊCH SỬ CẤP PHÁT TÀI SẢN
-- =====================================================
CREATE TABLE lich_su_cap_phat (
    id SERIAL PRIMARY KEY,
    tai_san_id INTEGER NOT NULL REFERENCES tai_san(id) ON DELETE CASCADE,
    nhan_vien_id INTEGER NOT NULL REFERENCES nhan_vien(id) ON DELETE CASCADE,
    
    -- Thông tin cấp phát
    ngay_cap_phat DATE DEFAULT CURRENT_DATE,
    nguoi_cap_phat VARCHAR(255),
    ly_do_cap_phat TEXT,
    
    -- Thông tin thu hồi
    ngay_thu_hoi DATE,
    nguoi_thu_hoi VARCHAR(255),
    ly_do_thu_hoi TEXT,
    tinh_trang_khi_thu_hoi tinh_trang_tai_san,
    
    -- Trạng thái
    trang_thai VARCHAR(20) DEFAULT 'Đang sử dụng' 
        CHECK (trang_thai IN ('Đang sử dụng', 'Đã thu hồi', 'Mất', 'Hỏng')),
    
    -- Ghi chú
    ghi_chu TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints
    CONSTRAINT lich_su_ngay_check CHECK (ngay_thu_hoi IS NULL OR ngay_thu_hoi >= ngay_cap_phat)
);

-- =====================================================
-- BẢNG BẢO TRÌ TÀI SẢN
-- =====================================================
CREATE TABLE bao_tri_tai_san (
    id SERIAL PRIMARY KEY,
    tai_san_id INTEGER NOT NULL REFERENCES tai_san(id) ON DELETE CASCADE,
    loai_bao_tri VARCHAR(50) NOT NULL CHECK (loai_bao_tri IN ('Bảo trì định kỳ', 'Sửa chữa', 'Nâng cấp', 'Kiểm tra')),
    
    -- Thông tin bảo trì
    ngay_bao_tri DATE DEFAULT CURRENT_DATE,
    nguoi_bao_tri VARCHAR(255),
    don_vi_bao_tri VARCHAR(255),
    mo_ta_cong_viec TEXT,
    
    -- Chi phí
    chi_phi DECIMAL(15,2),
    
    -- Kết quả
    ket_qua TEXT,
    tinh_trang_sau_bao_tri tinh_trang_tai_san,
    
    -- Lịch bảo trì tiếp theo
    ngay_bao_tri_tiep_theo DATE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints
    CONSTRAINT bao_tri_chi_phi_check CHECK (chi_phi IS NULL OR chi_phi >= 0),
    CONSTRAINT bao_tri_ngay_check CHECK (ngay_bao_tri_tiep_theo IS NULL OR ngay_bao_tri_tiep_theo > ngay_bao_tri)
);

-- =====================================================
-- BẢNG HỢP ĐỒNG LAO ĐỘNG
-- =====================================================
CREATE TABLE hop_dong_lao_dong (
    id SERIAL PRIMARY KEY,
    nhan_vien_id INTEGER NOT NULL REFERENCES nhan_vien(id) ON DELETE CASCADE,
    
    -- Thông tin hợp đồng
    so_hop_dong VARCHAR(50) UNIQUE NOT NULL,
    loai_hop_dong VARCHAR(50) NOT NULL CHECK (loai_hop_dong IN ('Thử việc', 'Có thời hạn', 'Không thời hạn', 'Thời vụ')),
    ngay_ky DATE NOT NULL,
    ngay_hieu_luc DATE NOT NULL,
    ngay_ket_thuc DATE,
    
    -- Điều kiện làm việc
    luong_co_ban DECIMAL(15,2) NOT NULL,
    phu_cap DECIMAL(15,2) DEFAULT 0,
    che_do_nghi_phep TEXT,
    che_do_lam_viec TEXT,
    
    -- Trạng thái
    trang_thai VARCHAR(20) DEFAULT 'Hiệu lực' 
        CHECK (trang_thai IN ('Hiệu lực', 'Hết hạn', 'Chấm dứt', 'Tạm ngưng')),
    
    -- File đính kèm
    file_hop_dong TEXT, -- URL hoặc path đến file
    
    ghi_chu TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints
    CONSTRAINT hop_dong_ngay_check CHECK (ngay_hieu_luc >= ngay_ky),
    CONSTRAINT hop_dong_ket_thuc_check CHECK (ngay_ket_thuc IS NULL OR ngay_ket_thuc >= ngay_hieu_luc),
    CONSTRAINT hop_dong_luong_check CHECK (luong_co_ban > 0)
);

-- =====================================================
-- INDEXES ĐỂ TỐI ƯU HIỆU SUẤT
-- =====================================================

-- Indexes cho bảng nhân viên
CREATE INDEX IF NOT EXISTS idx_nhan_vien_ma ON nhan_vien(ma_nhan_vien);
CREATE INDEX IF NOT EXISTS idx_nhan_vien_ho_ten ON nhan_vien USING gin(to_tsvector('vietnamese', ho_ten));
CREATE INDEX IF NOT EXISTS idx_nhan_vien_phong_ban ON nhan_vien(phong_ban_id);
CREATE INDEX IF NOT EXISTS idx_nhan_vien_chi_nhanh ON nhan_vien(chi_nhanh_id);
CREATE INDEX IF NOT EXISTS idx_nhan_vien_trang_thai ON nhan_vien(trang_thai_lam_viec);
CREATE INDEX IF NOT EXISTS idx_nhan_vien_ngay_vao_lam ON nhan_vien(ngay_vao_lam);
CREATE INDEX IF NOT EXISTS idx_nhan_vien_email ON nhan_vien(email) WHERE email IS NOT NULL;

-- Indexes cho bảng tài sản
CREATE INDEX IF NOT EXISTS idx_tai_san_ma ON tai_san(ma_tai_san);
CREATE INDEX IF NOT EXISTS idx_tai_san_ten ON tai_san USING gin(to_tsvector('vietnamese', ten_tai_san));
CREATE INDEX IF NOT EXISTS idx_tai_san_loai ON tai_san(loai_tai_san);
CREATE INDEX IF NOT EXISTS idx_tai_san_tinh_trang ON tai_san(tinh_trang);
CREATE INDEX IF NOT EXISTS idx_tai_san_nhan_vien ON tai_san(nhan_vien_id);
CREATE INDEX IF NOT EXISTS idx_tai_san_chi_nhanh ON tai_san(vi_tri_chi_nhanh_id);
CREATE INDEX IF NOT EXISTS idx_tai_san_ngay_mua ON tai_san(ngay_mua);

-- Indexes cho bảng lịch sử cấp phát
CREATE INDEX IF NOT EXISTS idx_lich_su_cap_phat_tai_san ON lich_su_cap_phat(tai_san_id);
CREATE INDEX IF NOT EXISTS idx_lich_su_cap_phat_nhan_vien ON lich_su_cap_phat(nhan_vien_id);
CREATE INDEX IF NOT EXISTS idx_lich_su_cap_phat_ngay ON lich_su_cap_phat(ngay_cap_phat);
CREATE INDEX IF NOT EXISTS idx_lich_su_cap_phat_trang_thai ON lich_su_cap_phat(trang_thai);

-- Indexes cho bảng phòng ban và chi nhánh
CREATE INDEX IF NOT EXISTS idx_phong_ban_ma ON phong_ban(ma_phong);
CREATE INDEX IF NOT EXISTS idx_phong_ban_ten ON phong_ban USING gin(to_tsvector('vietnamese', ten_phong));
CREATE INDEX IF NOT EXISTS idx_chi_nhanh_ten ON chi_nhanh USING gin(to_tsvector('vietnamese', ten_chi_nhanh));

-- Indexes cho bảng bảo trì
CREATE INDEX IF NOT EXISTS idx_bao_tri_tai_san ON bao_tri_tai_san(tai_san_id);
CREATE INDEX IF NOT EXISTS idx_bao_tri_ngay ON bao_tri_tai_san(ngay_bao_tri);
CREATE INDEX IF NOT EXISTS idx_bao_tri_loai ON bao_tri_tai_san(loai_bao_tri);

-- Indexes cho bảng hợp đồng
CREATE INDEX IF NOT EXISTS idx_hop_dong_nhan_vien ON hop_dong_lao_dong(nhan_vien_id);
CREATE INDEX IF NOT EXISTS idx_hop_dong_so ON hop_dong_lao_dong(so_hop_dong);
CREATE INDEX IF NOT EXISTS idx_hop_dong_trang_thai ON hop_dong_lao_dong(trang_thai);
CREATE INDEX IF NOT EXISTS idx_hop_dong_ngay_ket_thuc ON hop_dong_lao_dong(ngay_ket_thuc) WHERE ngay_ket_thuc IS NOT NULL;

-- =====================================================
-- TRIGGERS TỰ ĐỘNG CẬP NHẬT
-- =====================================================

-- Function để cập nhật updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Triggers cho các bảng
DROP TRIGGER IF EXISTS update_chi_nhanh_updated_at ON chi_nhanh;
CREATE TRIGGER update_chi_nhanh_updated_at 
    BEFORE UPDATE ON chi_nhanh 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_phong_ban_updated_at ON phong_ban;
CREATE TRIGGER update_phong_ban_updated_at 
    BEFORE UPDATE ON phong_ban 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_nhan_vien_updated_at ON nhan_vien;
CREATE TRIGGER update_nhan_vien_updated_at 
    BEFORE UPDATE ON nhan_vien 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_tai_san_updated_at ON tai_san;
CREATE TRIGGER update_tai_san_updated_at 
    BEFORE UPDATE ON tai_san 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_lich_su_cap_phat_updated_at ON lich_su_cap_phat;
CREATE TRIGGER update_lich_su_cap_phat_updated_at 
    BEFORE UPDATE ON lich_su_cap_phat 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_hop_dong_updated_at ON hop_dong_lao_dong;
CREATE TRIGGER update_hop_dong_updated_at 
    BEFORE UPDATE ON hop_dong_lao_dong 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- =====================================================
-- FUNCTIONS TIỆN ÍCH
-- =====================================================

-- Function tính tuổi
CREATE OR REPLACE FUNCTION tinh_tuoi(ngay_sinh DATE)
RETURNS INTEGER AS $$
BEGIN
    RETURN EXTRACT(YEAR FROM AGE(CURRENT_DATE, ngay_sinh));
END;
$$ LANGUAGE plpgsql;

-- Function tính thâm niên
CREATE OR REPLACE FUNCTION tinh_tham_nien(ngay_vao_lam DATE)
RETURNS INTERVAL AS $$
BEGIN
    RETURN AGE(CURRENT_DATE, ngay_vao_lam);
END;
$$ LANGUAGE plpgsql;

-- Function cập nhật số lượng nhân viên trong phòng ban
CREATE OR REPLACE FUNCTION cap_nhat_so_luong_nv_phong_ban()
RETURNS TRIGGER AS $$
BEGIN
    -- Cập nhật phòng ban cũ
    IF OLD.phong_ban_id IS NOT NULL THEN
        UPDATE phong_ban 
        SET so_luong_nv = (
            SELECT COUNT(*) 
            FROM nhan_vien 
            WHERE phong_ban_id = OLD.phong_ban_id 
            AND trang_thai_lam_viec = 'Đang làm việc'
        )
        WHERE id = OLD.phong_ban_id;
    END IF;
    
    -- Cập nhật phòng ban mới
    IF NEW.phong_ban_id IS NOT NULL THEN
        UPDATE phong_ban 
        SET so_luong_nv = (
            SELECT COUNT(*) 
            FROM nhan_vien 
            WHERE phong_ban_id = NEW.phong_ban_id 
            AND trang_thai_lam_viec = 'Đang làm việc'
        )
        WHERE id = NEW.phong_ban_id;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger cập nhật số lượng nhân viên
DROP TRIGGER IF EXISTS trigger_cap_nhat_so_luong_nv ON nhan_vien;
CREATE TRIGGER trigger_cap_nhat_so_luong_nv
    AFTER UPDATE OF phong_ban_id, trang_thai_lam_viec ON nhan_vien
    FOR EACH ROW
    EXECUTE FUNCTION cap_nhat_so_luong_nv_phong_ban();

-- =====================================================
-- DỮ LIỆU MẪU
-- =====================================================

-- Thêm dữ liệu mẫu cho chi nhánh
INSERT INTO chi_nhanh (ten_chi_nhanh, dia_chi, sdt, email, truong_chi_nhanh, ma_chi_nhanh) VALUES
('Chi nhánh Hà Nội', '123 Trần Hưng Đạo, Hoàn Kiếm, Hà Nội', '024-1234567', 'hanoi@company.com', 'Nguyễn Văn A', 'CN-HN'),
('Chi nhánh TP.HCM', '456 Nguyễn Huệ, Quận 1, TP.HCM', '028-7654321', 'hcm@company.com', 'Trần Thị B', 'CN-HCM'),
('Chi nhánh Đà Nẵng', '789 Lê Duẩn, Hải Châu, Đà Nẵng', '0236-9876543', 'danang@company.com', 'Lê Văn C', 'CN-DN'),
('Chi nhánh Cần Thơ', '321 Trần Phú, Ninh Kiều, Cần Thơ', '0292-8765432', 'cantho@company.com', 'Phạm Văn D', 'CN-CT');

-- Thêm dữ liệu mẫu cho phòng ban
INSERT INTO phong_ban (ten_phong, ma_phong, ghi_chu, chi_nhanh_id) VALUES
('Phòng Nhân sự', 'HR', 'Quản lý nhân sự và tuyển dụng', 1),
('Phòng Kế toán', 'ACC', 'Quản lý tài chính và kế toán', 1),
('Phòng Công nghệ thông tin', 'IT', 'Quản lý hệ thống thông tin và phát triển phần mềm', 1),
('Phòng Kinh doanh', 'SALES', 'Phát triển kinh doanh và bán hàng', 2),
('Phòng Marketing', 'MKT', 'Tiếp thị và quảng bá sản phẩm', 2),
('Phòng Hành chính', 'ADMIN', 'Quản lý hành chính và hỗ trợ', 3),
('Phòng Kỹ thuật', 'TECH', 'Hỗ trợ kỹ thuật và bảo trì', 3);

-- Thêm dữ liệu mẫu cho nhân viên
INSERT INTO nhan_vien (ho_ten, ma_nhan_vien, ngay_sinh, sdt, email, gioi_tinh, dia_chi, phong_ban_id, chi_nhanh_id, chuc_vu, ngay_vao_lam, luong_co_ban) VALUES
('Nguyễn Văn An', 'NV001', '1985-03-15', '0901234567', 'an.nguyen@company.com', 'Nam', '123 Phố Huế, Hà Nội', 1, 1, 'Trưởng phòng', '2020-01-15', 15000000),
('Trần Thị Bình', 'NV002', '1990-07-20', '0902345678', 'binh.tran@company.com', 'Nữ', '456 Lê Lợi, TP.HCM', 4, 2, 'Nhân viên', '2021-03-01', 8000000),
('Lê Văn Cường', 'NV003', '1988-12-10', '0903456789', 'cuong.le@company.com', 'Nam', '789 Bạch Đằng, Đà Nẵng', 3, 3, 'Chuyên viên', '2020-06-15', 12000000),
('Phạm Thị Dung', 'NV004', '1992-05-25', '0904567890', 'dung.pham@company.com', 'Nữ', '321 Nguyễn Thái Học, Cần Thơ', 2, 4, 'Kế toán viên', '2022-01-10', 9000000),
('Hoàng Văn Em', 'NV005', '1987-09-18', '0905678901', 'em.hoang@company.com', 'Nam', '654 Điện Biên Phủ, Hà Nội', 5, 1, 'Marketing Manager', '2019-08-20', 13000000);

-- Thêm dữ liệu mẫu cho tài sản
INSERT INTO tai_san (ten_tai_san, ma_tai_san, loai_tai_san, hang_san_xuat, model, ngay_mua, gia_mua, tinh_trang, vi_tri_phong, vi_tri_chi_nhanh_id, so_luong_ton_kho) VALUES
('Laptop Dell Inspiron 15', 'TS001', 'Laptop', 'Dell', 'Inspiron 15 3000', '2023-01-15', 15000000, 'Đang dùng', 'Phòng IT', 1, 1),
('Máy in HP LaserJet', 'TS002', 'Máy in', 'HP', 'LaserJet Pro M404n', '2023-02-20', 5000000, 'Đang dùng', 'Phòng hành chính', 1, 1),
('Điện thoại iPhone 14', 'TS003', 'Điện thoại', 'Apple', 'iPhone 14', '2023-03-10', 25000000, 'Đang dùng', 'Phòng kinh doanh', 2, 1),
('Máy tính để bàn HP', 'TS004', 'Máy tính', 'HP', 'ProDesk 400 G9', '2023-04-05', 12000000, 'Mới', 'Kho tài sản', 1, 3),
('Máy photocopy Canon', 'TS005', 'Máy photocopy', 'Canon', 'imageRUNNER 2530i', '2023-05-12', 30000000, 'Đang dùng', 'Phòng hành chính', 3, 1);

-- Thêm dữ liệu mẫu cho lịch sử cấp phát
INSERT INTO lich_su_cap_phat (tai_san_id, nhan_vien_id, ngay_cap_phat, nguoi_cap_phat, ly_do_cap_phat, trang_thai) VALUES
(1, 3, '2023-01-20', 'Nguyễn Văn An', 'Cấp phát laptop cho nhân viên IT mới', 'Đang sử dụng'),
(3, 2, '2023-03-15', 'Nguyễn Văn An', 'Cấp phát điện thoại cho nhân viên kinh doanh', 'Đang sử dụng');

-- Thêm dữ liệu mẫu cho hợp đồng lao động
INSERT INTO hop_dong_lao_dong (nhan_vien_id, so_hop_dong, loai_hop_dong, ngay_ky, ngay_hieu_luc, ngay_ket_thuc, luong_co_ban, trang_thai) VALUES
(1, 'HD001', 'Không thời hạn', '2020-01-10', '2020-01-15', NULL, 15000000, 'Hiệu lực'),
(2, 'HD002', 'Có thời hạn', '2021-02-25', '2021-03-01', '2024-02-29', 8000000, 'Hiệu lực'),
(3, 'HD003', 'Không thời hạn', '2020-06-10', '2020-06-15', NULL, 12000000, 'Hiệu lực'),
(4, 'HD004', 'Có thời hạn', '2022-01-05', '2022-01-10', '2025-01-09', 9000000, 'Hiệu lực'),
(5, 'HD005', 'Không thời hạn', '2019-08-15', '2019-08-20', NULL, 13000000, 'Hiệu lực');

-- =====================================================
-- VIEWS TIỆN ÍCH
-- =====================================================

-- View thông tin nhân viên đầy đủ
CREATE OR REPLACE VIEW v_nhan_vien_day_du AS
SELECT 
    nv.id,
    nv.ho_ten,
    nv.ma_nhan_vien,
    nv.ngay_sinh,
    tinh_tuoi(nv.ngay_sinh) as tuoi,
    nv.sdt,
    nv.email,
    nv.gioi_tinh,
    nv.dia_chi,
    nv.chuc_vu,
    nv.ngay_vao_lam,
    tinh_tham_nien(nv.ngay_vao_lam) as tham_nien,
    nv.trang_thai_lam_viec,
    nv.luong_co_ban,
    pb.ten_phong,
    pb.ma_phong,
    cn.ten_chi_nhanh,
    cn.ma_chi_nhanh,
    hd.so_hop_dong,
    hd.loai_hop_dong,
    hd.ngay_ket_thuc as ngay_het_han_hop_dong
FROM nhan_vien nv
LEFT JOIN phong_ban pb ON nv.phong_ban_id = pb.id
LEFT JOIN chi_nhanh cn ON nv.chi_nhanh_id = cn.id
LEFT JOIN hop_dong_lao_dong hd ON nv.id = hd.nhan_vien_id AND hd.trang_thai = 'Hiệu lực';

-- View thông tin tài sản đầy đủ
CREATE OR REPLACE VIEW v_tai_san_day_du AS
SELECT 
    ts.id,
    ts.ten_tai_san,
    ts.ma_tai_san,
    ts.loai_tai_san,
    ts.hang_san_xuat,
    ts.model,
    ts.serial_number,
    ts.ngay_mua,
    ts.gia_mua,
    ts.tinh_trang,
    ts.vi_tri_phong,
    ts.so_luong_ton_kho,
    nv.ho_ten as ten_nhan_vien_su_dung,
    nv.ma_nhan_vien,
    cn.ten_chi_nhanh,
    pb.ten_phong as phong_ban_su_dung,
    CASE 
        WHEN ts.ngay_het_bao_hanh IS NOT NULL AND ts.ngay_het_bao_hanh < CURRENT_DATE 
        THEN 'Hết bảo hành'
        WHEN ts.ngay_het_bao_hanh IS NOT NULL AND ts.ngay_het_bao_hanh >= CURRENT_DATE 
        THEN 'Còn bảo hành'
        ELSE 'Không có thông tin'
    END as trang_thai_bao_hanh,
    ts.ngay_het_bao_hanh,
    ts.gia_tri_hien_tai,
    ts.gia_tri_con_lai
FROM tai_san ts
LEFT JOIN nhan_vien nv ON ts.nhan_vien_id = nv.id
LEFT JOIN chi_nhanh cn ON ts.vi_tri_chi_nhanh_id = cn.id
LEFT JOIN phong_ban pb ON nv.phong_ban_id = pb.id;

-- View báo cáo thống kê tài sản theo loại
CREATE OR REPLACE VIEW v_thong_ke_tai_san AS
SELECT 
    loai_tai_san,
    COUNT(*) as tong_so_luong,
    SUM(CASE WHEN tinh_trang = 'Đang dùng' THEN 1 ELSE 0 END) as dang_su_dung,
    SUM(CASE WHEN tinh_trang = 'Mới' THEN 1 ELSE 0 END) as moi,
    SUM(CASE WHEN tinh_trang = 'Hỏng' THEN 1 ELSE 0 END) as hong,
    SUM(CASE WHEN tinh_trang = 'Bảo trì' THEN 1 ELSE 0 END) as bao_tri,
    SUM(CASE WHEN tinh_trang = 'Thanh lý' THEN 1 ELSE 0 END) as thanh_ly,
    SUM(gia_mua) as tong_gia_tri_mua,
    AVG(gia_mua) as gia_tri_trung_binh,
    SUM(gia_tri_hien_tai) as tong_gia_tri_hien_tai
FROM tai_san
GROUP BY loai_tai_san
ORDER BY tong_so_luong DESC;

-- View nhân viên sắp hết hạn hợp đồng
CREATE OR REPLACE VIEW v_nhan_vien_sap_het_han AS
SELECT 
    nv.ho_ten,
    nv.ma_nhan_vien,
    nv.email,
    nv.sdt,
    pb.ten_phong,
    cn.ten_chi_nhanh,
    hd.so_hop_dong,
    hd.loai_hop_dong,
    hd.ngay_ket_thuc,
    hd.ngay_ket_thuc - CURRENT_DATE as so_ngay_con_lai
FROM nhan_vien nv
JOIN hop_dong_lao_dong hd ON nv.id = hd.nhan_vien_id
LEFT JOIN phong_ban pb ON nv.phong_ban_id = pb.id
LEFT JOIN chi_nhanh cn ON nv.chi_nhanh_id = cn.id
WHERE hd.trang_thai = 'Hiệu lực'
  AND hd.ngay_ket_thuc IS NOT NULL
  AND hd.ngay_ket_thuc <= CURRENT_DATE + INTERVAL '90 days'
ORDER BY hd.ngay_ket_thuc;

-- View tài sản cần bảo trì
CREATE OR REPLACE VIEW v_tai_san_can_bao_tri AS
SELECT 
    ts.ten_tai_san,
    ts.ma_tai_san,
    ts.loai_tai_san,
    ts.vi_tri_phong,
    cn.ten_chi_nhanh,
    nv.ho_ten as nguoi_su_dung,
    bt.ngay_bao_tri_tiep_theo,
    bt.ngay_bao_tri_tiep_theo - CURRENT_DATE as so_ngay_con_lai,
    bt.loai_bao_tri as loai_bao_tri_cuoi
FROM tai_san ts
LEFT JOIN chi_nhanh cn ON ts.vi_tri_chi_nhanh_id = cn.id
LEFT JOIN nhan_vien nv ON ts.nhan_vien_id = nv.id
LEFT JOIN LATERAL (
    SELECT ngay_bao_tri_tiep_theo, loai_bao_tri
    FROM bao_tri_tai_san
    WHERE tai_san_id = ts.id
    ORDER BY ngay_bao_tri DESC
    LIMIT 1
) bt ON true
WHERE bt.ngay_bao_tri_tiep_theo IS NOT NULL
  AND bt.ngay_bao_tri_tiep_theo <= CURRENT_DATE + INTERVAL '30 days'
ORDER BY bt.ngay_bao_tri_tiep_theo;

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

-- Procedure cấp phát tài sản
CREATE OR REPLACE FUNCTION cap_phat_tai_san(
    p_tai_san_id INTEGER,
    p_nhan_vien_id INTEGER,
    p_nguoi_cap_phat VARCHAR(255),
    p_ly_do TEXT DEFAULT NULL
) RETURNS BOOLEAN AS $
DECLARE
    v_tai_san_exists BOOLEAN;
    v_nhan_vien_exists BOOLEAN;
    v_tai_san_available BOOLEAN;
BEGIN
    -- Kiểm tra tài sản tồn tại
    SELECT EXISTS(SELECT 1 FROM tai_san WHERE id = p_tai_san_id) INTO v_tai_san_exists;
    IF NOT v_tai_san_exists THEN
        RAISE EXCEPTION 'Tài sản không tồn tại';
    END IF;
    
    -- Kiểm tra nhân viên tồn tại
    SELECT EXISTS(SELECT 1 FROM nhan_vien WHERE id = p_nhan_vien_id) INTO v_nhan_vien_exists;
    IF NOT v_nhan_vien_exists THEN
        RAISE EXCEPTION 'Nhân viên không tồn tại';
    END IF;
    
    -- Kiểm tra tài sản có thể cấp phát
    SELECT (tinh_trang IN ('Mới', 'Chờ cấp phát') AND nhan_vien_id IS NULL)
    INTO v_tai_san_available
    FROM tai_san WHERE id = p_tai_san_id;
    
    IF NOT v_tai_san_available THEN
        RAISE EXCEPTION 'Tài sản không thể cấp phát (đã được sử dụng hoặc không ở trạng thái phù hợp)';
    END IF;
    
    -- Cập nhật tài sản
    UPDATE tai_san 
    SET nhan_vien_id = p_nhan_vien_id,
        tinh_trang = 'Đang dùng',
        updated_at = CURRENT_TIMESTAMP
    WHERE id = p_tai_san_id;
    
    -- Thêm lịch sử cấp phát
    INSERT INTO lich_su_cap_phat (
        tai_san_id, nhan_vien_id, ngay_cap_phat, 
        nguoi_cap_phat, ly_do_cap_phat, trang_thai
    ) VALUES (
        p_tai_san_id, p_nhan_vien_id, CURRENT_DATE,
        p_nguoi_cap_phat, p_ly_do, 'Đang sử dụng'
    );
    
    RETURN TRUE;
END;
$ LANGUAGE plpgsql;

-- Procedure thu hồi tài sản
CREATE OR REPLACE FUNCTION thu_hoi_tai_san(
    p_tai_san_id INTEGER,
    p_nguoi_thu_hoi VARCHAR(255),
    p_ly_do TEXT DEFAULT NULL,
    p_tinh_trang_khi_thu_hoi tinh_trang_tai_san DEFAULT 'Mới'
) RETURNS BOOLEAN AS $
DECLARE
    v_nhan_vien_id INTEGER;
BEGIN
    -- Lấy thông tin nhân viên đang sử dụng
    SELECT nhan_vien_id INTO v_nhan_vien_id
    FROM tai_san WHERE id = p_tai_san_id;
    
    IF v_nhan_vien_id IS NULL THEN
        RAISE EXCEPTION 'Tài sản chưa được cấp phát cho ai';
    END IF;
    
    -- Cập nhật tài sản
    UPDATE tai_san 
    SET nhan_vien_id = NULL,
        tinh_trang = p_tinh_trang_khi_thu_hoi,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = p_tai_san_id;
    
    -- Cập nhật lịch sử cấp phát
    UPDATE lich_su_cap_phat 
    SET ngay_thu_hoi = CURRENT_DATE,
        nguoi_thu_hoi = p_nguoi_thu_hoi,
        ly_do_thu_hoi = p_ly_do,
        tinh_trang_khi_thu_hoi = p_tinh_trang_khi_thu_hoi,
        trang_thai = 'Đã thu hồi',
        updated_at = CURRENT_TIMESTAMP
    WHERE tai_san_id = p_tai_san_id 
      AND nhan_vien_id = v_nhan_vien_id
      AND trang_thai = 'Đang sử dụng';
    
    RETURN TRUE;
END;
$ LANGUAGE plpgsql;

-- Function báo cáo thống kê nhân viên theo phòng ban
CREATE OR REPLACE FUNCTION bao_cao_nhan_vien_theo_phong_ban()
RETURNS TABLE(
    ten_phong VARCHAR(255),
    ma_phong VARCHAR(50),
    ten_chi_nhanh VARCHAR(255),
    tong_nhan_vien BIGINT,
    dang_lam_viec BIGINT,
    nghi_viec BIGINT,
    tam_nghi BIGINT,
    luong_trung_binh NUMERIC
) AS $
BEGIN
    RETURN QUERY
    SELECT 
        pb.ten_phong,
        pb.ma_phong,
        cn.ten_chi_nhanh,
        COUNT(nv.id) as tong_nhan_vien,
        COUNT(CASE WHEN nv.trang_thai_lam_viec = 'Đang làm việc' THEN 1 END) as dang_lam_viec,
        COUNT(CASE WHEN nv.trang_thai_lam_viec = 'Nghỉ việc' THEN 1 END) as nghi_viec,
        COUNT(CASE WHEN nv.trang_thai_lam_viec = 'Tạm nghỉ' THEN 1 END) as tam_nghi,
        AVG(nv.luong_co_ban) as luong_trung_binh
    FROM phong_ban pb
    LEFT JOIN nhan_vien nv ON pb.id = nv.phong_ban_id
    LEFT JOIN chi_nhanh cn ON pb.chi_nhanh_id = cn.id
    GROUP BY pb.id, pb.ten_phong, pb.ma_phong, cn.ten_chi_nhanh
    ORDER BY pb.ten_phong;
END;
$ LANGUAGE plpgsql;

-- =====================================================
-- MATERIALIZED VIEWS ĐỂ TỐI ƯU HIỆU SUẤT
-- =====================================================

-- Materialized view cho dashboard
CREATE MATERIALIZED VIEW IF NOT EXISTS mv_dashboard_stats AS
SELECT 
    (SELECT COUNT(*) FROM nhan_vien WHERE trang_thai_lam_viec = 'Đang làm việc') as tong_nhan_vien,
    (SELECT COUNT(*) FROM phong_ban WHERE trang_thai = TRUE) as tong_phong_ban,
    (SELECT COUNT(*) FROM chi_nhanh WHERE trang_thai = TRUE) as tong_chi_nhanh,
    (SELECT COUNT(*) FROM tai_san) as tong_tai_san,
    (SELECT COUNT(*) FROM tai_san WHERE tinh_trang = 'Đang dùng') as tai_san_dang_dung,
    (SELECT COUNT(*) FROM tai_san WHERE tinh_trang = 'Mới') as tai_san_moi,
    (SELECT COUNT(*) FROM tai_san WHERE tinh_trang = 'Hỏng') as tai_san_hong,
    (SELECT SUM(gia_mua) FROM tai_san) as tong_gia_tri_tai_san,
    (SELECT AVG(luong_co_ban) FROM nhan_vien WHERE trang_thai_lam_viec = 'Đang làm việc') as luong_trung_binh,
    CURRENT_TIMESTAMP as cap_nhat_cuoi;

-- Index cho materialized view
CREATE INDEX IF NOT EXISTS idx_mv_dashboard_stats_cap_nhat ON mv_dashboard_stats(cap_nhat_cuoi);

-- Function refresh materialized view
CREATE OR REPLACE FUNCTION refresh_dashboard_stats()
RETURNS VOID AS $
BEGIN
    REFRESH MATERIALIZED VIEW mv_dashboard_stats;
END;
$ LANGUAGE plpgsql;

-- =====================================================
-- SECURITY & PERMISSIONS
-- =====================================================

-- Tạo role cho các loại user (chỉ tạo nếu chưa tồn tại)
DO $
BEGIN
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'hr_role') THEN
        CREATE ROLE hr_role;
    END IF;
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'admin_role') THEN
        CREATE ROLE admin_role;
    END IF;
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'employee_role') THEN
        CREATE ROLE employee_role;
    END IF;
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'readonly_role') THEN
        CREATE ROLE readonly_role;
    END IF;
END
$;

-- Cấp quyền cho hr_role
GRANT SELECT, INSERT, UPDATE ON nhan_vien, phong_ban, chi_nhanh, hop_dong_lao_dong TO hr_role;
GRANT SELECT ON tai_san, lich_su_cap_phat TO hr_role;
GRANT USAGE ON ALL SEQUENCES IN SCHEMA public TO hr_role;

-- Cấp quyền cho admin_role
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO admin_role;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO admin_role;
GRANT ALL PRIVILEGES ON ALL FUNCTIONS IN SCHEMA public TO admin_role;

-- Cấp quyền cho employee_role
GRANT SELECT ON nhan_vien, phong_ban, chi_nhanh TO employee_role;
GRANT SELECT ON tai_san TO employee_role;

-- Cấp quyền cho readonly_role
GRANT SELECT ON ALL TABLES IN SCHEMA public TO readonly_role;

-- =====================================================
-- CLEANUP & MAINTENANCE
-- =====================================================

-- Function dọn dẹp dữ liệu cũ
CREATE OR REPLACE FUNCTION cleanup_old_data()
RETURNS VOID AS $
BEGIN
    -- Xóa log cũ hơn 2 năm (nếu có bảng logs)
    -- DELETE FROM activity_logs WHERE created_at < CURRENT_DATE - INTERVAL '2 years';
    
    -- Xóa session hết hạn (nếu có bảng sessions)
    -- DELETE FROM user_sessions WHERE expires_at < CURRENT_TIMESTAMP;
    
    -- Cập nhật thống kê
    ANALYZE;
    
    -- Refresh materialized views
    REFRESH MATERIALIZED VIEW mv_dashboard_stats;
    
    RAISE NOTICE 'Cleanup completed at %', CURRENT_TIMESTAMP;
END;
$ LANGUAGE plpgsql;

-- =====================================================
-- COMMENTS CHO DOCUMENTATION
-- =====================================================

-- Bảng comments
COMMENT ON TABLE chi_nhanh IS 'Bảng quản lý thông tin các chi nhánh của công ty';
COMMENT ON TABLE phong_ban IS 'Bảng quản lý thông tin các phòng ban';
COMMENT ON TABLE nhan_vien IS 'Bảng quản lý thông tin nhân viên';
COMMENT ON TABLE tai_san IS 'Bảng quản lý tài sản công ty';
COMMENT ON TABLE lich_su_cap_phat IS 'Bảng lưu lịch sử cấp phát và thu hồi tài sản';
COMMENT ON TABLE bao_tri_tai_san IS 'Bảng quản lý lịch sử bảo trì tài sản';
COMMENT ON TABLE hop_dong_lao_dong IS 'Bảng quản lý hợp đồng lao động của nhân viên';

-- Column comments
COMMENT ON COLUMN nhan_vien.ma_nhan_vien IS 'Mã nhân viên duy nhất';
COMMENT ON COLUMN nhan_vien.ho_ten IS 'Họ và tên đầy đủ của nhân viên';
COMMENT ON COLUMN nhan_vien.luong_co_ban IS 'Lương cơ bản tính bằng VNĐ';
COMMENT ON COLUMN tai_san.ma_tai_san IS 'Mã tài sản duy nhất';
COMMENT ON COLUMN tai_san.gia_mua IS 'Giá mua ban đầu tính bằng VNĐ';
COMMENT ON COLUMN tai_san.thong_so_ky_thuat IS 'Thông số kỹ thuật dạng JSON';

-- =====================================================
-- KHAI BÁO HOÀN THÀNH
-- =====================================================

-- Thông báo hoàn thành
DO $
BEGIN
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Database schema đã được tạo thành công!';
    RAISE NOTICE 'Bao gồm:';
    RAISE NOTICE '- % bảng chính', (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE');
    RAISE NOTICE '- % views', (SELECT COUNT(*) FROM information_schema.views WHERE table_schema = 'public');
    RAISE NOTICE '- % functions', (SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema = 'public');
    RAISE NOTICE '- Dữ liệu mẫu đã được thêm';
    RAISE NOTICE '- Indexes đã được tối ưu';
    RAISE NOTICE '- Triggers đã được thiết lập';
    RAISE NOTICE '========================================';
END $;
