-- =====================================================
-- Hệ thống Quản lý Nhân viên và Tài sản - CLEAN VERSION
-- Database Schema cho PostgreSQL
-- =====================================================

-- Tạo extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";

-- Xóa các đối tượng cũ
DROP TABLE IF EXISTS hop_dong_lao_dong CASCADE;
DROP TABLE IF EXISTS bao_tri_tai_san CASCADE;
DROP TABLE IF EXISTS lich_su_cap_phat CASCADE;
DROP TABLE IF EXISTS tai_san CASCADE;
DROP TABLE IF EXISTS nhan_vien CASCADE;
DROP TABLE IF EXISTS phong_ban CASCADE;
DROP TABLE IF EXISTS chi_nhanh CASCADE;
DROP TYPE IF EXISTS tinh_trang_tai_san CASCADE;
DROP TYPE IF EXISTS loai_tai_san_enum CASCADE;

-- =====================================================
-- TẠO TYPES
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
-- TẠO BẢNG CHI NHÁNH
-- =====================================================
CREATE TABLE chi_nhanh (
    id SERIAL PRIMARY KEY,
    ten_chi_nhanh VARCHAR(255) NOT NULL,
    dia_chi TEXT,
    sdt VARCHAR(20),
    email VARCHAR(255),
    truong_chi_nhanh VARCHAR(255),
    ma_chi_nhanh VARCHAR(50) UNIQUE,
    trang_thai BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TẠO BẢNG PHÒNG BAN
-- =====================================================
CREATE TABLE phong_ban (
    id SERIAL PRIMARY KEY,
    ten_phong VARCHAR(255) NOT NULL,
    ma_phong VARCHAR(50) UNIQUE NOT NULL,
    ghi_chu TEXT,
    chi_nhanh_id INTEGER REFERENCES chi_nhanh(id) ON DELETE SET NULL,
    truong_phong_id INTEGER,
    so_luong_nv INTEGER DEFAULT 0,
    trang_thai BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TẠO BẢNG NHÂN VIÊN
-- =====================================================
CREATE TABLE nhan_vien (
    id SERIAL PRIMARY KEY,
    ho_ten VARCHAR(255) NOT NULL,
    ma_nhan_vien VARCHAR(50) UNIQUE NOT NULL,
    ngay_sinh DATE,
    sdt VARCHAR(20),
    email VARCHAR(255),
    gioi_tinh VARCHAR(10) DEFAULT 'Nam',
    dia_chi TEXT,
    so_cccd VARCHAR(20),
    so_bhxh VARCHAR(20),
    phong_ban_id INTEGER REFERENCES phong_ban(id) ON DELETE SET NULL,
    chi_nhanh_id INTEGER REFERENCES chi_nhanh(id) ON DELETE SET NULL,
    chuc_vu VARCHAR(100),
    cap_bac VARCHAR(50),
    ngay_vao_lam DATE DEFAULT CURRENT_DATE,
    ngay_nghi_viec DATE,
    trang_thai_lam_viec VARCHAR(20) DEFAULT 'Đang làm việc',
    luong_co_ban DECIMAL(15,2),
    luong_dong_bhxh DECIMAL(15,2),
    phu_cap DECIMAL(15,2),
    dan_toc VARCHAR(50),
    ton_giao VARCHAR(50),
    que_quan TEXT,
    noi_sinh TEXT,
    trinh_do_hoc_van VARCHAR(100),
    chuyen_mon VARCHAR(100),
    ngoai_ngu VARCHAR(100),
    nguoi_lien_he_khan_cap VARCHAR(255),
    sdt_khan_cap VARCHAR(20),
    moi_quan_he_khan_cap VARCHAR(50),
    anh_dai_dien TEXT,
    ghi_chu TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Thêm foreign key cho trưởng phòng
ALTER TABLE phong_ban ADD CONSTRAINT fk_truong_phong 
    FOREIGN KEY (truong_phong_id) REFERENCES nhan_vien(id) ON DELETE SET NULL;

-- =====================================================
-- TẠO BẢNG TÀI SẢN
-- =====================================================
CREATE TABLE tai_san (
    id SERIAL PRIMARY KEY,
    ten_tai_san VARCHAR(255) NOT NULL,
    ma_tai_san VARCHAR(50) UNIQUE NOT NULL,
    loai_tai_san loai_tai_san_enum DEFAULT 'Khác',
    hang_san_xuat VARCHAR(100),
    model VARCHAR(100),
    serial_number VARCHAR(100),
    ngay_mua DATE DEFAULT CURRENT_DATE,
    nha_cung_cap VARCHAR(255),
    gia_mua DECIMAL(15,2),
    thoi_gian_bao_hanh INTEGER,
    ngay_het_bao_hanh DATE,
    tinh_trang tinh_trang_tai_san DEFAULT 'Mới',
    nhan_vien_id INTEGER REFERENCES nhan_vien(id) ON DELETE SET NULL,
    vi_tri_phong VARCHAR(255),
    vi_tri_chi_nhanh_id INTEGER REFERENCES chi_nhanh(id) ON DELETE SET NULL,
    vi_tri_cu_the TEXT,
    thong_so_ky_thuat JSONB,
    so_luong_ton_kho INTEGER DEFAULT 1,
    don_vi_tinh VARCHAR(20) DEFAULT 'Cái',
    gia_tri_hien_tai DECIMAL(15,2),
    ty_le_khau_hao DECIMAL(5,2),
    gia_tri_con_lai DECIMAL(15,2),
    anh_tai_san TEXT[],
    ghi_chu TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TẠO BẢNG LỊCH SỬ CẤP PHÁT
-- =====================================================
CREATE TABLE lich_su_cap_phat (
    id SERIAL PRIMARY KEY,
    tai_san_id INTEGER NOT NULL REFERENCES tai_san(id) ON DELETE CASCADE,
    nhan_vien_id INTEGER NOT NULL REFERENCES nhan_vien(id) ON DELETE CASCADE,
    ngay_cap_phat DATE DEFAULT CURRENT_DATE,
    nguoi_cap_phat VARCHAR(255),
    ly_do_cap_phat TEXT,
    ngay_thu_hoi DATE,
    nguoi_thu_hoi VARCHAR(255),
    ly_do_thu_hoi TEXT,
    tinh_trang_khi_thu_hoi tinh_trang_tai_san,
    trang_thai VARCHAR(20) DEFAULT 'Đang sử dụng',
    ghi_chu TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TẠO BẢNG BẢO TRÌ TÀI SẢN
-- =====================================================
CREATE TABLE bao_tri_tai_san (
    id SERIAL PRIMARY KEY,
    tai_san_id INTEGER NOT NULL REFERENCES tai_san(id) ON DELETE CASCADE,
    loai_bao_tri VARCHAR(50) NOT NULL,
    ngay_bao_tri DATE DEFAULT CURRENT_DATE,
    nguoi_bao_tri VARCHAR(255),
    don_vi_bao_tri VARCHAR(255),
    mo_ta_cong_viec TEXT,
    chi_phi DECIMAL(15,2),
    ket_qua TEXT,
    tinh_trang_sau_bao_tri tinh_trang_tai_san,
    ngay_bao_tri_tiep_theo DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TẠO BẢNG HỢP ĐỒNG LAO ĐỘNG
-- =====================================================
CREATE TABLE hop_dong_lao_dong (
    id SERIAL PRIMARY KEY,
    nhan_vien_id INTEGER NOT NULL REFERENCES nhan_vien(id) ON DELETE CASCADE,
    so_hop_dong VARCHAR(50) UNIQUE NOT NULL,
    loai_hop_dong VARCHAR(50) NOT NULL,
    ngay_ky DATE NOT NULL,
    ngay_hieu_luc DATE NOT NULL,
    ngay_ket_thuc DATE,
    luong_co_ban DECIMAL(15,2) NOT NULL,
    phu_cap DECIMAL(15,2) DEFAULT 0,
    che_do_nghi_phep TEXT,
    che_do_lam_viec TEXT,
    trang_thai VARCHAR(20) DEFAULT 'Hiệu lực',
    file_hop_dong TEXT,
    ghi_chu TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TẠO INDEXES (BỎ VIETNAMESE TEXT SEARCH)
-- =====================================================
CREATE INDEX idx_nhan_vien_ma ON nhan_vien(ma_nhan_vien);
CREATE INDEX idx_nhan_vien_ho_ten ON nhan_vien(ho_ten);
CREATE INDEX idx_nhan_vien_phong_ban ON nhan_vien(phong_ban_id);
CREATE INDEX idx_nhan_vien_chi_nhanh ON nhan_vien(chi_nhanh_id);
CREATE INDEX idx_nhan_vien_trang_thai ON nhan_vien(trang_thai_lam_viec);
CREATE INDEX idx_nhan_vien_ngay_vao_lam ON nhan_vien(ngay_vao_lam);
CREATE INDEX idx_nhan_vien_email ON nhan_vien(email) WHERE email IS NOT NULL;

CREATE INDEX idx_tai_san_ma ON tai_san(ma_tai_san);
CREATE INDEX idx_tai_san_ten ON tai_san(ten_tai_san);
CREATE INDEX idx_tai_san_loai ON tai_san(loai_tai_san);
CREATE INDEX idx_tai_san_tinh_trang ON tai_san(tinh_trang);
CREATE INDEX idx_tai_san_nhan_vien ON tai_san(nhan_vien_id);
CREATE INDEX idx_tai_san_chi_nhanh ON tai_san(vi_tri_chi_nhanh_id);
CREATE INDEX idx_tai_san_ngay_mua ON tai_san(ngay_mua);

CREATE INDEX idx_lich_su_cap_phat_tai_san ON lich_su_cap_phat(tai_san_id);
CREATE INDEX idx_lich_su_cap_phat_nhan_vien ON lich_su_cap_phat(nhan_vien_id);
CREATE INDEX idx_lich_su_cap_phat_ngay ON lich_su_cap_phat(ngay_cap_phat);
CREATE INDEX idx_lich_su_cap_phat_trang_thai ON lich_su_cap_phat(trang_thai);

CREATE INDEX idx_phong_ban_ma ON phong_ban(ma_phong);
CREATE INDEX idx_phong_ban_ten ON phong_ban(ten_phong);
CREATE INDEX idx_chi_nhanh_ten ON chi_nhanh(ten_chi_nhanh);

CREATE INDEX idx_bao_tri_tai_san ON bao_tri_tai_san(tai_san_id);
CREATE INDEX idx_bao_tri_ngay ON bao_tri_tai_san(ngay_bao_tri);
CREATE INDEX idx_bao_tri_loai ON bao_tri_tai_san(loai_bao_tri);

CREATE INDEX idx_hop_dong_nhan_vien ON hop_dong_lao_dong(nhan_vien_id);
CREATE INDEX idx_hop_dong_so ON hop_dong_lao_dong(so_hop_dong);
CREATE INDEX idx_hop_dong_trang_thai ON hop_dong_lao_dong(trang_thai);
CREATE INDEX idx_hop_dong_ngay_ket_thuc ON hop_dong_lao_dong(ngay_ket_thuc) WHERE ngay_ket_thuc IS NOT NULL;

-- =====================================================
-- TẠO FUNCTION CẬP NHẬT TIMESTAMP
-- =====================================================
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS 
$$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$
LANGUAGE plpgsql;

-- =====================================================
-- TẠO TRIGGERS
-- =====================================================
CREATE TRIGGER update_chi_nhanh_updated_at 
    BEFORE UPDATE ON chi_nhanh 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_phong_ban_updated_at 
    BEFORE UPDATE ON phong_ban 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_nhan_vien_updated_at 
    BEFORE UPDATE ON nhan_vien 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_tai_san_updated_at 
    BEFORE UPDATE ON tai_san 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_lich_su_cap_phat_updated_at 
    BEFORE UPDATE ON lich_su_cap_phat 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_hop_dong_updated_at 
    BEFORE UPDATE ON hop_dong_lao_dong 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- =====================================================
-- FUNCTIONS TIỆN ÍCH
-- =====================================================

-- Function tính tuổi
CREATE OR REPLACE FUNCTION tinh_tuoi(ngay_sinh DATE)
RETURNS INTEGER AS 
$$
BEGIN
    RETURN EXTRACT(YEAR FROM AGE(CURRENT_DATE, ngay_sinh));
END;
$$
LANGUAGE plpgsql;

-- Function tính thâm niên
CREATE OR REPLACE FUNCTION tinh_tham_nien(ngay_vao_lam DATE)
RETURNS INTERVAL AS 
$$
BEGIN
    RETURN AGE(CURRENT_DATE, ngay_vao_lam);
END;
$$
LANGUAGE plpgsql;

-- =====================================================
-- THÊM DỮ LIỆU MẪU
-- =====================================================

-- Thêm chi nhánh
INSERT INTO chi_nhanh (ten_chi_nhanh, dia_chi, sdt, email, truong_chi_nhanh, ma_chi_nhanh) VALUES
('Chi nhánh Hà Nội', '123 Trần Hưng Đạo, Hoàn Kiếm, Hà Nội', '024-1234567', 'hanoi@company.com', 'Nguyễn Văn A', 'CN-HN'),
('Chi nhánh TP.HCM', '456 Nguyễn Huệ, Quận 1, TP.HCM', '028-7654321', 'hcm@company.com', 'Trần Thị B', 'CN-HCM'),
('Chi nhánh Đà Nẵng', '789 Lê Duẩn, Hải Châu, Đà Nẵng', '0236-9876543', 'danang@company.com', 'Lê Văn C', 'CN-DN'),
('Chi nhánh Cần Thơ', '321 Trần Phú, Ninh Kiều, Cần Thơ', '0292-8765432', 'cantho@company.com', 'Phạm Văn D', 'CN-CT');

-- Thêm phòng ban
INSERT INTO phong_ban (ten_phong, ma_phong, ghi_chu, chi_nhanh_id) VALUES
('Phòng Nhân sự', 'HR', 'Quản lý nhân sự và tuyển dụng', 1),
('Phòng Kế toán', 'ACC', 'Quản lý tài chính và kế toán', 1),
('Phòng Công nghệ thông tin', 'IT', 'Quản lý hệ thống thông tin và phát triển phần mềm', 1),
('Phòng Kinh doanh', 'SALES', 'Phát triển kinh doanh và bán hàng', 2),
('Phòng Marketing', 'MKT', 'Tiếp thị và quảng bá sản phẩm', 2),
('Phòng Hành chính', 'ADMIN', 'Quản lý hành chính và hỗ trợ', 3),
('Phòng Kỹ thuật', 'TECH', 'Hỗ trợ kỹ thuật và bảo trì', 3);

-- Thêm nhân viên
INSERT INTO nhan_vien (ho_ten, ma_nhan_vien, ngay_sinh, sdt, email, gioi_tinh, dia_chi, phong_ban_id, chi_nhanh_id, chuc_vu, ngay_vao_lam, luong_co_ban) VALUES
('Nguyễn Văn An', 'NV001', '1985-03-15', '0901234567', 'an.nguyen@company.com', 'Nam', '123 Phố Huế, Hà Nội', 1, 1, 'Trưởng phòng', '2020-01-15', 15000000),
('Trần Thị Bình', 'NV002', '1990-07-20', '0902345678', 'binh.tran@company.com', 'Nữ', '456 Lê Lợi, TP.HCM', 4, 2, 'Nhân viên', '2021-03-01', 8000000),
('Lê Văn Cường', 'NV003', '1988-12-10', '0903456789', 'cuong.le@company.com', 'Nam', '789 Bạch Đằng, Đà Nẵng', 3, 3, 'Chuyên viên', '2020-06-15', 12000000),
('Phạm Thị Dung', 'NV004', '1992-05-25', '0904567890', 'dung.pham@company.com', 'Nữ', '321 Nguyễn Thái Học, Cần Thơ', 2, 4, 'Kế toán viên', '2022-01-10', 9000000),
('Hoàng Văn Em', 'NV005', '1987-09-18', '0905678901', 'em.hoang@company.com', 'Nam', '654 Điện Biên Phủ, Hà Nội', 5, 1, 'Marketing Manager', '2019-08-20', 13000000);

-- Thêm tài sản
INSERT INTO tai_san (ten_tai_san, ma_tai_san, loai_tai_san, hang_san_xuat, model, ngay_mua, gia_mua, tinh_trang, vi_tri_phong, vi_tri_chi_nhanh_id, so_luong_ton_kho) VALUES
('Laptop Dell Inspiron 15', 'TS001', 'Laptop', 'Dell', 'Inspiron 15 3000', '2023-01-15', 15000000, 'Đang dùng', 'Phòng IT', 1, 1),
('Máy in HP LaserJet', 'TS002', 'Máy in', 'HP', 'LaserJet Pro M404n', '2023-02-20', 5000000, 'Đang dùng', 'Phòng hành chính', 1, 1),
('Điện thoại iPhone 14', 'TS003', 'Điện thoại', 'Apple', 'iPhone 14', '2023-03-10', 25000000, 'Đang dùng', 'Phòng kinh doanh', 2, 1),
('Máy tính để bàn HP', 'TS004', 'Máy tính', 'HP', 'ProDesk 400 G9', '2023-04-05', 12000000, 'Mới', 'Kho tài sản', 1, 3),
('Máy photocopy Canon', 'TS005', 'Máy photocopy', 'Canon', 'imageRUNNER 2530i', '2023-05-12', 30000000, 'Đang dùng', 'Phòng hành chính', 3, 1);

-- Thêm lịch sử cấp phát
INSERT INTO lich_su_cap_phat (tai_san_id, nhan_vien_id, ngay_cap_phat, nguoi_cap_phat, ly_do_cap_phat, trang_thai) VALUES
(1, 3, '2023-01-20', 'Nguyễn Văn An', 'Cấp phát laptop cho nhân viên IT mới', 'Đang sử dụng'),
(3, 2, '2023-03-15', 'Nguyễn Văn An', 'Cấp phát điện thoại cho nhân viên kinh doanh', 'Đang sử dụng');

-- Thêm hợp đồng lao động
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

-- =====================================================
-- COMMENTS
-- =====================================================
COMMENT ON TABLE chi_nhanh IS 'Bảng quản lý thông tin các chi nhánh của công ty';
COMMENT ON TABLE phong_ban IS 'Bảng quản lý thông tin các phòng ban';
COMMENT ON TABLE nhan_vien IS 'Bảng quản lý thông tin nhân viên';
COMMENT ON TABLE tai_san IS 'Bảng quản lý tài sản công ty';
COMMENT ON TABLE lich_su_cap_phat IS 'Bảng lưu lịch sử cấp phát và thu hồi tài sản';
COMMENT ON TABLE bao_tri_tai_san IS 'Bảng quản lý lịch sử bảo trì tài sản';
COMMENT ON TABLE hop_dong_lao_dong IS 'Bảng quản lý hợp đồng lao động của nhân viên';

-- =====================================================
-- HOÀN THÀNH
-- =====================================================
SELECT 'Database schema đã được tạo thành công!' as message;
