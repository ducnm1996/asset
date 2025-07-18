-- Users table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'employee',
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Departments table
CREATE TABLE departments (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Employees table
CREATE TABLE employees (
    id SERIAL PRIMARY KEY,
    employee_code VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    department_id INTEGER REFERENCES departments(id),
    position VARCHAR(100),
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Asset categories table
CREATE TABLE asset_categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Assets table
CREATE TABLE assets (
    id SERIAL PRIMARY KEY,
    asset_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    category_id INTEGER REFERENCES asset_categories(id),
    description TEXT,
    purchase_date DATE,
    purchase_price DECIMAL(15,2),
    warranty_end_date DATE,
    status VARCHAR(20) DEFAULT 'available',
    location VARCHAR(200),
    serial_number VARCHAR(100),
    model VARCHAR(100),
    manufacturer VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contracts table
CREATE TABLE contracts (
    id SERIAL PRIMARY KEY,
    contract_number VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    supplier VARCHAR(200) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    value DECIMAL(15,2),
    description TEXT,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Asset allocations table
CREATE TABLE asset_allocations (
    id SERIAL PRIMARY KEY,
    asset_id INTEGER REFERENCES assets(id),
    employee_id INTEGER REFERENCES employees(id),
    allocated_date DATE NOT NULL,
    returned_date DATE,
    status VARCHAR(20) DEFAULT 'allocated',
    notes TEXT,
    allocated_by INTEGER REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Maintenance records table
CREATE TABLE maintenance_records (
    id SERIAL PRIMARY KEY,
    asset_id INTEGER REFERENCES assets(id),
    type VARCHAR(50) NOT NULL, -- maintenance, repair, disposal
    description TEXT,
    cost DECIMAL(15,2),
    maintenance_date DATE NOT NULL,
    performed_by VARCHAR(200),
    status VARCHAR(20) DEFAULT 'completed',
    file_path VARCHAR(500),
    created_by INTEGER REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin'),
('manager', 'manager@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Manager', 'manager'),
('user', 'user@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Employee', 'employee');

INSERT INTO departments (name, description) VALUES
('IT Department', 'Information Technology'),
('HR Department', 'Human Resources'),
('Finance Department', 'Finance and Accounting'),
('Marketing Department', 'Marketing and Sales');

INSERT INTO employees (employee_code, full_name, email, phone, department_id, position) VALUES
('EMP001', 'Nguyen Van A', 'nva@company.com', '0123456789', 1, 'IT Manager'),
('EMP002', 'Tran Thi B', 'ttb@company.com', '0987654321', 2, 'HR Specialist'),
('EMP003', 'Le Van C', 'lvc@company.com', '0111222333', 3, 'Accountant');

INSERT INTO asset_categories (name, description) VALUES
('Computer', 'Desktop and laptop computers'),
('Printer', 'Printing devices'),
('Furniture', 'Office furniture'),
('Network Equipment', 'Routers, switches, etc.');

INSERT INTO assets (asset_code, name, category_id, purchase_date, purchase_price, warranty_end_date, status, location) VALUES
('AST001', 'Dell Laptop Inspiron 15', 1, '2023-01-15', 15000000, '2025-01-15', 'allocated', 'IT Department'),
('AST002', 'HP LaserJet Pro', 2, '2023-02-10', 5000000, '2024-02-10', 'available', 'Office Floor 1'),
('AST003', 'Office Chair Executive', 3, '2023-03-05', 2000000, '2025-03-05', 'allocated', 'HR Department');