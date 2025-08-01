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

### Cách chạy với Docker:

1. **Clone và setup:**
```bash
git clone <repository-url>
cd asset-management
```

2. **Chạy với Docker:**
```bash
docker-compose up -d
```

3. **Cài đặt dependencies:**
```bash
docker-compose exec web composer install
```

4. **Truy cập ứng dụng:**
- URL: http://localhost:8080
- Database: localhost:5432

### Tài khoản demo:
- **Admin**: admin / password
- **Manager**: manager / password  
- **Employee**: user / password

### Cấu trúc cơ sở dữ liệu đã được tự động tạo với dữ liệu mẫu.

## Tính năng đầy đủ:
✅ Đăng nhập với phân quyền  
✅ Dashboard với thống kê  
✅ CRUD tài sản với tìm kiếm  
✅ Quản lý nhóm tài sản  
✅ Quản lý nhân viên & phòng ban  
✅ Quản lý hợp đồng  
✅ Cấp phát & thu hồi tài sản  
✅ Bảo trì & thanh lý  
✅ Export Excel & PDF  
✅ Quản lý người dùng  
✅ Docker deployment  

Hệ thống được thiết kế modular, dễ mở rộng và tuân thủ chuẩn MVC!#   a s s e t  
 