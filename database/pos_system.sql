-- ===================================================
-- POS SYSTEM DATABASE - Cửa hàng điện thoại & phụ kiện
-- ===================================================
-- Author: [Tên nhóm]
-- Date: 2026-02-01
-- Version: 1.0
-- ===================================================

-- Tạo database
CREATE DATABASE IF NOT EXISTS pos_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pos_system;

-- ===================================================
-- 1. BẢNG USERS - Người dùng (Admin & Staff)
-- ===================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    avatar VARCHAR(255) DEFAULT 'avatar-default.png',
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    status ENUM('active', 'locked') NOT NULL DEFAULT 'active',
    must_change_password TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- 2. BẢNG EMAIL_TOKENS - Token xác thực email (1 phút)
-- ===================================================
CREATE TABLE email_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    is_used TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at),
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- 3. BẢNG CATEGORIES - Danh mục sản phẩm
-- ===================================================
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- 4. BẢNG PRODUCTS - Sản phẩm
-- ===================================================
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    barcode VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    price_buy DECIMAL(15,2) NOT NULL COMMENT 'Giá nhập - chỉ admin thấy',
    price_sell DECIMAL(15,2) NOT NULL COMMENT 'Giá bán',
    category_id INT NOT NULL,
    image VARCHAR(255) DEFAULT 'no-image.png',
    description TEXT,
    has_sold TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Đã từng bán chưa - để kiểm tra xóa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_barcode (barcode),
    INDEX idx_name (name),
    INDEX idx_category (category_id),
    INDEX idx_has_sold (has_sold),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- 5. BẢNG CUSTOMERS - Khách hàng (tự động tạo khi bán)
-- ===================================================
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    phone VARCHAR(15) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255),
    total_spent DECIMAL(15,2) DEFAULT 0 COMMENT 'Tổng tiền đã chi tiêu',
    total_orders INT DEFAULT 0 COMMENT 'Tổng số đơn hàng',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- 6. BẢNG ORDERS - Đơn hàng
-- ===================================================
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_code VARCHAR(20) UNIQUE NOT NULL COMMENT 'Mã đơn hàng: ORD20260201001',
    customer_id INT NOT NULL,
    staff_id INT NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL COMMENT 'Tổng tiền đơn hàng',
    customer_paid DECIMAL(15,2) NOT NULL COMMENT 'Tiền khách đưa',
    change_amount DECIMAL(15,2) NOT NULL COMMENT 'Tiền thừa trả lại',
    order_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_code (order_code),
    INDEX idx_customer (customer_id),
    INDEX idx_staff (staff_id),
    INDEX idx_order_date (order_date),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- 7. BẢNG ORDER_DETAILS - Chi tiết đơn hàng
-- ===================================================
CREATE TABLE order_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL COMMENT 'Lưu tên SP tại thời điểm bán',
    quantity INT NOT NULL,
    price DECIMAL(15,2) NOT NULL COMMENT 'Giá bán tại thời điểm bán',
    price_buy DECIMAL(15,2) NOT NULL COMMENT 'Giá nhập để tính lợi nhuận',
    subtotal DECIMAL(15,2) NOT NULL COMMENT 'Thành tiền = quantity * price',
    INDEX idx_order (order_id),
    INDEX idx_product (product_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- DỮ LIỆU MẪU - TÀI KHOẢN ADMIN
-- ===================================================
-- Password: admin (đã hash bằng password_hash)
INSERT INTO users (username, password, email, full_name, role, status, must_change_password, created_by) 
VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: admin
    'admin@gmail.com',
    'Quản trị viên',
    'admin',
    'active',
    0, -- Admin không bắt buộc đổi mật khẩu
    NULL
);

-- ===================================================
-- DỮ LIỆU MẪU - DANH MỤC
-- ===================================================
INSERT INTO categories (name, description, created_by) VALUES
('Điện thoại', 'Điện thoại di động các loại', 1),
('Tai nghe', 'Tai nghe có dây, không dây, bluetooth', 1),
('Sạc & Cáp', 'Sạc dự phòng, cáp sạc, củ sạc', 1),
('Ốp lưng', 'Ốp lưng, bao da điện thoại', 1),
('Phụ kiện khác', 'Kính cường lực, giá đỡ, v.v.', 1);

-- ===================================================
-- DỮ LIỆU MẪU - SẢN PHẨM
-- ===================================================
INSERT INTO products (barcode, name, price_buy, price_sell, category_id, created_by) VALUES
-- Điện thoại
('8934968421', 'iPhone 15 Pro Max 256GB', 28000000, 32000000, 1, 1),
('8934968422', 'Samsung Galaxy S24 Ultra', 25000000, 29000000, 1, 1),
('8934968423', 'Xiaomi 14 Pro', 18000000, 21000000, 1, 1),
('8934968424', 'OPPO Find X7', 15000000, 18000000, 1, 1),
('8934968425', 'Realme 12 Pro+', 9000000, 11000000, 1, 1),

-- Tai nghe
('8934968426', 'AirPods Pro 2', 5500000, 6500000, 2, 1),
('8934968427', 'Samsung Galaxy Buds 2 Pro', 3500000, 4200000, 2, 1),
('8934968428', 'Sony WH-1000XM5', 7000000, 8500000, 2, 1),

-- Sạc & Cáp
('8934968429', 'Sạc dự phòng Anker 20000mAh', 800000, 1200000, 3, 1),
('8934968430', 'Cáp sạc Type-C Anker', 150000, 250000, 3, 1),
('8934968431', 'Củ sạc nhanh 65W GaN', 450000, 650000, 3, 1),

-- Ốp lưng
('8934968432', 'Ốp lưng iPhone 15 Pro Max', 200000, 350000, 4, 1),
('8934968433', 'Ốp lưng Samsung S24 Ultra', 180000, 320000, 4, 1),

-- Phụ kiện khác
('8934968434', 'Kính cường lực 9H', 100000, 200000, 5, 1),
('8934968435', 'Giá đỡ điện thoại ô tô', 150000, 280000, 5, 1);

-- ===================================================
-- DỮ LIỆU MẪU - KHÁCH HÀNG
-- ===================================================
INSERT INTO customers (phone, name, address) VALUES
('0901234567', 'Nguyễn Văn A', '123 Lê Lợi, Q1, TP.HCM'),
('0912345678', 'Trần Thị B', '456 Nguyễn Huệ, Q1, TP.HCM'),
('0923456789', 'Lê Văn C', '789 Hai Bà Trưng, Q3, TP.HCM');

-- ===================================================
-- TRIGGER: Cập nhật has_sold khi sản phẩm được bán
-- ===================================================
DELIMITER //
CREATE TRIGGER update_product_sold 
AFTER INSERT ON order_details
FOR EACH ROW
BEGIN
    UPDATE products 
    SET has_sold = 1 
    WHERE id = NEW.product_id;
END//
DELIMITER ;

-- ===================================================
-- TRIGGER: Cập nhật total_spent và total_orders của khách hàng
-- ===================================================
DELIMITER //
CREATE TRIGGER update_customer_stats
AFTER INSERT ON orders
FOR EACH ROW
BEGIN
    UPDATE customers 
    SET 
        total_spent = total_spent + NEW.total_amount,
        total_orders = total_orders + 1
    WHERE id = NEW.customer_id;
END//
DELIMITER ;

-- ===================================================
-- VIEW: Báo cáo doanh thu theo ngày
-- ===================================================
CREATE OR REPLACE VIEW daily_sales_report AS
SELECT 
    DATE(o.created_at) as sale_date,
    COUNT(DISTINCT o.id) as total_orders,
    SUM(od.quantity) as total_products_sold,
    SUM(o.total_amount) as total_revenue,
    SUM(od.quantity * (od.price - od.price_buy)) as total_profit
FROM orders o
JOIN order_details od ON o.id = od.order_id
GROUP BY DATE(o.created_at)
ORDER BY sale_date DESC;

-- ===================================================
-- VIEW: Doanh số theo nhân viên
-- ===================================================
CREATE OR REPLACE VIEW staff_sales_report AS
SELECT 
    u.id as staff_id,
    u.full_name as staff_name,
    u.avatar,
    COUNT(DISTINCT o.id) as total_orders,
    SUM(o.total_amount) as total_sales,
    SUM(od.quantity * (od.price - od.price_buy)) as total_profit
FROM users u
LEFT JOIN orders o ON u.id = o.staff_id
LEFT JOIN order_details od ON o.id = od.order_id
WHERE u.role = 'staff'
GROUP BY u.id, u.full_name, u.avatar
ORDER BY total_sales DESC;

-- ===================================================
-- STORED PROCEDURE: Tạo mã đơn hàng tự động
-- ===================================================
DELIMITER //
CREATE PROCEDURE generate_order_code(OUT order_code VARCHAR(20))
BEGIN
    DECLARE next_number INT;
    DECLARE today VARCHAR(8);
    
    SET today = DATE_FORMAT(CURDATE(), '%Y%m%d');
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(order_code, 12) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM orders
    WHERE order_code LIKE CONCAT('ORD', today, '%');
    
    SET order_code = CONCAT('ORD', today, LPAD(next_number, 3, '0'));
END//
DELIMITER ;

-- ===================================================
-- KẾT THÚC FILE SQL
-- ===================================================
-- Kiểm tra các bảng đã tạo
SHOW TABLES;

-- Kiểm tra cấu trúc bảng users
DESCRIBE users;

-- Test xem admin account đã tạo chưa
SELECT id, username, email, full_name, role FROM users WHERE role = 'admin';
