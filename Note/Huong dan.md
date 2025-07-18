# HƯỚNG DẪN CÀI ĐẶT VÀ CHẠY HỆ THỐNG QUẢN LÝ TÀI SẢN

## Cấu trúc thư mục dự án
```
# Phần mềm Quản lý Tài sản Doanh nghiệp

## Cấu trúc thư mục

```
asset-management/
├── docker-compose.yml
├── Dockerfile
├── nginx.conf
├── init.sql
├── README.md
├── app/
│   ├── Config/
│   │   ├── Database.php
│   │   └── Config.php
│   ├── Controllers/
│   │   ├── BaseController.php
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── AssetController.php
│   │   ├── AssetCategoryController.php
│   │   ├── EmployeeController.php
│   │   ├── DepartmentController.php
│   │   ├── ContractController.php
│   │   ├── AllocationController.php
│   │   ├── MaintenanceController.php
│   │   └── UserController.php
│   ├── Models/
│   │   ├── BaseModel.php
│   │   ├── User.php
│   │   ├── Asset.php
│   │   ├── AssetCategory.php
│   │   ├── Employee.php
│   │   ├── Department.php
│   │   ├── Contract.php
│   │   ├── Allocation.php
│   │   └── Maintenance.php
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── header.php
│   │   │   ├── footer.php
│   │   │   └── sidebar.php
│   │   ├── auth/
│   │   │   └── login.php
│   │   ├── dashboard/
│   │   │   └── index.php
│   │   ├── assets/
│   │   │   ├── index.php
│   │   │   ├── create.php
│   │   │   └── edit.php
│   │   └── ...
│   ├── Core/
│   │   ├── Router.php
│   │   ├── Session.php
│   │   └── Auth.php
│   └── Helpers/
│       ├── ExcelExporter.php
│       └── PDFExporter.php
├── public/
│   ├── index.php
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── uploads/
│   └── .htaccess
├── vendor/
└── composer.json
```

## Hướng dẫn cài đặt

### 1. Clone dự án và setup
```bash
git clone <repository-url>
cd asset-management
cp .env.example .env
```

### 2. Chạy với Docker
```bash
docker-compose up -d
```

### 3. Cài đặt dependencies và migration
```bash
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate:fresh --seed
```

### 4. Truy cập ứng dụng
- URL: http://localhost:8080
- Admin login: admin@admin.com / password

## Các tính năng chính

1. **Dashboard**: Thống kê tổng quan tài sản, trạng thái, phòng ban
2. **Quản lý tài sản**: CRUD tài sản với phân loại, trạng thái
3. **Quản lý nhân viên**: CRUD thông tin nhân viên theo phòng ban
4. **Cấp phát tài sản**: Gán/thu hồi tài sản cho nhân viên
5. **Bảo trì & Sửa chữa**: Ghi nhận lịch sử thao tác
6. **Phân quyền RBAC**: Quản lý user, role, permission

## API Endpoints

### Assets
- GET /assets - Danh sách tài sản
- POST /assets - Tạo tài sản mới
- GET /assets/{id} - Chi tiết tài sản
- PUT /assets/{id} - Cập nhật tài sản
- DELETE /assets/{id} - Xóa tài sản

### Assignments
- POST /assignments - Cấp phát tài sản
- DELETE /assignments/{id} - Thu hồi tài sản

### Dashboard
- GET /dashboard - Thống kê tổng quan
- GET /dashboard/charts - Dữ liệu biểu đồ