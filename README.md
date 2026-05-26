# 🛒 POS System — Hệ Thống Quản Lý Bán Hàng Điện Thoại & Phụ Kiện

<div align="center">

![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![jQuery](https://img.shields.io/badge/jQuery-3.7-0769AD?style=for-the-badge&logo=jquery&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-Local%20Server-FB7A24?style=for-the-badge&logo=xampp&logoColor=white)

**Hệ thống POS (Point of Sale) hoàn chỉnh dành cho cửa hàng điện thoại và phụ kiện.  
Được xây dựng bằng PHP thuần + MySQL, không phụ thuộc framework lớn.**

[📸 Xem Demo](#-giao-diện-demo) · [⚡ Cài đặt nhanh](#-cài-đặt-nhanh) · [📖 Tài liệu đầy đủ](#-cấu-trúc-dự-án)

</div>

---

## 📌 Dự Án Giải Quyết Vấn Đề Gì?

Các cửa hàng điện thoại và phụ kiện nhỏ đến vừa thường gặp các bài toán vận hành sau:

| ❌ Vấn đề thực tế | ✅ Giải pháp trong hệ thống |
|---|---|
| Bán hàng thủ công, dễ nhầm lẫn giá | Giao diện POS trực quan, tìm kiếm sản phẩm tức thì |
| Không theo dõi được doanh thu hàng ngày | Dashboard thống kê real-time với biểu đồ Chart.js |
| Khó quản lý nhiều nhân viên | Phân quyền Admin/Staff, khóa/mở tài khoản |
| Mất thông tin khách hàng, không chăm sóc lại | Module quản lý khách hàng + lịch sử mua hàng |
| Không có hóa đơn chuyên nghiệp | Xuất hóa đơn PDF tự động sau mỗi giao dịch |
| Khó kiểm soát tồn kho và lợi nhuận | Báo cáo lợi nhuận, doanh thu theo danh mục |
| Nhân viên mới không biết mật khẩu | Tự động gửi email + link kích hoạt tài khoản |

---

## 🚀 Tính Năng Chính

### 👑 Dành cho Admin
- **Dashboard tổng quan**: Thống kê doanh thu, số đơn, lợi nhuận — lọc theo ngày/tuần/tháng hoặc tùy chọn.
- **Quản lý nhân viên**: Thêm nhân viên, gửi email mời kích hoạt, xem lịch sử bán hàng, khóa/mở tài khoản.
- **Quản lý sản phẩm**: Thêm/sửa/xóa sản phẩm, upload ảnh, phân loại danh mục.
- **Quản lý danh mục**: Tạo/xóa danh mục hàng hóa.
- **Xem đơn hàng**: Xem chi tiết từng đơn, lọc theo thời gian.
- **Báo cáo**: Báo cáo doanh thu và lợi nhuận với biểu đồ trực quan.

### 🧑‍💼 Dành cho Nhân viên (Staff)
- **Bán hàng POS**: Tìm sản phẩm theo tên hoặc quét barcode, thêm vào giỏ, tính tiền thối.
- **Quản lý khách hàng**: Tra cứu khách cũ theo số điện thoại hoặc tự động tạo khách mới.
- **In hóa đơn PDF**: Tự động tạo hóa đơn sau mỗi giao dịch thành công.
- **Đổi mật khẩu**: Bắt buộc đổi mật khẩu lần đầu đăng nhập.

---

## 🛠️ Công Nghệ Sử Dụng

### Backend
| Công nghệ | Vai trò |
|---|---|
| **PHP 8.1+** | Ngôn ngữ lập trình chính, xử lý logic server-side |
| **MySQL / MariaDB** | Cơ sở dữ liệu quan hệ, lưu trữ toàn bộ dữ liệu |
| **MySQLi (Prepared Statements)** | Giao tiếp với database an toàn, chống SQL Injection |
| **PHP Sessions** | Quản lý phiên đăng nhập người dùng |
| **PHPMailer** | Gửi email xác thực khi tạo tài khoản nhân viên |
| **FPDF** | Tạo file hóa đơn PDF sau khi thanh toán |

### Frontend
| Công nghệ | Vai trò |
|---|---|
| **Bootstrap 5.3** | Framework CSS, layout responsive, components UI |
| **jQuery 3.7** | Xử lý DOM, AJAX gọi API không reload trang |
| **Chart.js 4.4** | Vẽ biểu đồ doanh thu (line chart, bar chart) |
| **Font Awesome 6.5** | Icon toàn bộ giao diện |
| **Vanilla CSS** | CSS tùy chỉnh thêm (glassmorphism, animation, gradient) |

### Môi trường
| Công cụ | Phiên bản |
|---|---|
| **XAMPP** | 8.x (Apache + MySQL + PHP) |
| **Apache** | Web server local |
| **phpMyAdmin** | Quản lý database qua giao diện |

---

## 📚 Lý Thuyết Nền Tảng PHP Cần Biết

> Đây là phần lý thuyết cốt lõi giúp bạn hiểu và mở rộng dự án này.

### 1. PHP Session — Quản lý phiên đăng nhập

```php
// Khởi tạo session (bắt buộc đầu mỗi file)
session_start();

// Lưu thông tin user vào session sau khi đăng nhập
$_SESSION['user_id']  = $user['id'];
$_SESSION['role']     = $user['role'];   // 'admin' hoặc 'staff'
$_SESSION['username'] = $user['username'];

// Kiểm tra đã đăng nhập chưa
function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

// Xóa session khi đăng xuất
session_destroy();
```

**Cách hoạt động**: Mỗi trình duyệt được server cấp một Session ID duy nhất (lưu trong cookie `PHPSESSID`). Server dùng ID này để nhận ra người dùng qua các request khác nhau.

---

### 2. Prepared Statements — Chống SQL Injection

SQL Injection là kỹ thuật tấn công bằng cách chèn mã SQL vào input. **Prepared Statements** ngăn điều này bằng cách tách query khỏi dữ liệu:

```php
// ❌ NGUY HIỂM — Dễ bị SQL Injection
$query = "SELECT * FROM users WHERE username = '$username'";

// ✅ AN TOÀN — Dùng Prepared Statement
function fetchOne($query, $types = '', $params = []) {
    $conn = getDbConnection();
    $stmt = $conn->prepare($query);  // Biên dịch query trước
    
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);  // Gắn dữ liệu sau
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Sử dụng
$user = fetchOne(
    "SELECT * FROM users WHERE username = ? AND status = ?",
    "ss",              // s = string, i = integer, d = double
    [$username, 'active']
);
```

---

### 3. Phân Quyền (RBAC — Role-Based Access Control)

Hệ thống có 2 vai trò: `admin` và `staff`. Mỗi trang kiểm tra quyền trước khi render:

```php
// Định nghĩa hằng số vai trò
define('ROLE_ADMIN', 'admin');
define('ROLE_STAFF', 'staff');

// Hàm kiểm tra và điều hướng nếu không đủ quyền
function requireRole($roles) {
    requireLogin();                        // Phải đăng nhập trước
    $roles = (array) $roles;
    $role  = $_SESSION['role'] ?? null;
    
    if (!in_array($role, $roles, true)) {
        setFlashMessage('error', 'Không có quyền truy cập.');
        redirect(routeByRole());           // Redirect về trang phù hợp
    }
}

// Dùng ở đầu mỗi trang cần bảo vệ
requireRole(ROLE_ADMIN);           // Chỉ admin
requireRole([ROLE_ADMIN, ROLE_STAFF]); // Cả hai vai trò
```

---

### 4. Password Hashing — Bảo mật mật khẩu

**Không bao giờ** lưu mật khẩu dạng plaintext vào database:

```php
// Hash mật khẩu khi tạo tài khoản (dùng bcrypt)
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
    // Kết quả: $2y$10$abc123... (không thể dịch ngược)
}

// Xác thực mật khẩu khi đăng nhập
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
    // So sánh mật khẩu người dùng nhập với hash trong DB
}
```

---

### 5. Flash Message — Thông báo một lần

Kỹ thuật lưu thông báo vào session và hiển thị **một lần duy nhất** sau khi redirect:

```php
// Lưu thông báo
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
}

// Lấy và xóa thông báo (chỉ đọc được 1 lần)
function getFlashMessage() {
    $msg = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);
    return $msg;
}

// Dùng khi xử lý form
setFlashMessage('success', 'Thêm sản phẩm thành công!');
redirect(url('modules/products/list_products.php'));

// Hiển thị trong giao diện
$flash = getFlashMessage();
if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>">
        <?= e($flash['message']) ?>
    </div>
<?php endif;
```

---

### 6. AJAX với jQuery — Cập nhật giao diện không reload

Module POS dùng AJAX để thêm sản phẩm vào giỏ hàng mà không reload trang:

```javascript
// Gửi request POST tới server
$.post('cart_api.php', {
    action: 'add',
    product_id: productId
}, function(response) {
    if (response.success) {
        renderCart(response.cart);  // Cập nhật giao diện
    } else {
        alert(response.message);
    }
}, 'json');

// PHP trả về JSON
function jsonResponse($payload, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit();
}
```

---

### 7. Upload File Ảnh — Xử lý hình ảnh sản phẩm

```php
function uploadFile($file, $destination = 'uploads') {
    // Kiểm tra lỗi upload
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    
    // Kiểm tra kích thước (tối đa 5MB)
    if ($file['size'] > MAX_FILE_SIZE) return false;
    
    // Kiểm tra MIME type (chỉ cho phép ảnh)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) return false;
    
    // Tạo tên file ngẫu nhiên để tránh trùng
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid('', true) . '_' . time() . '.' . $extension;
    
    // Di chuyển file từ tmp vào thư mục uploads
    move_uploaded_file($file['tmp_name'], $directory . '/' . $filename);
    
    return $filename;
}
```

---

### 8. Singleton Pattern — Kết nối Database một lần

Tránh tạo nhiều kết nối database trong một request:

```php
function getDbConnection() {
    static $conn = null;         // Biến static giữ giá trị giữa các lần gọi
    
    if ($conn === null) {        // Chỉ kết nối nếu chưa có
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset('utf8mb4');
        $conn->query("SET time_zone = '+07:00'");
    }
    
    return $conn;                // Luôn trả về cùng một kết nối
}
```

---

## 📁 Cấu Trúc Dự Án

```
Demo DA21/
│
├── 📄 index.php                   # Trang đăng nhập
├── 📄 logout.php                  # Xử lý đăng xuất
├── 📄 verify-email.php            # Xác thực email kích hoạt tài khoản
├── 📄 insert_sample_data.php      # Script thêm dữ liệu mẫu
│
├── 📂 config/
│   ├── config.php                 # Cấu hình trung tâm + helper functions
│   ├── database.php               # Kết nối MySQL + query helpers
│   └── email_config.php           # Cấu hình SMTP gửi email
│
├── 📂 includes/
│   ├── auth_check.php             # Middleware kiểm tra đăng nhập
│   ├── header.php                 # Header chung (navbar top)
│   └── sidebar.php                # Sidebar menu chung
│
├── 📂 modules/
│   ├── 📂 auth/                   # Đăng nhập, đổi mật khẩu
│   ├── 📂 dashboard/              # Dashboard admin & staff
│   ├── 📂 pos/                    # Giao diện bán hàng POS
│   │   ├── pos.php                # Màn hình bán hàng chính
│   │   ├── cart_api.php           # API thêm/xóa/cập nhật giỏ hàng
│   │   ├── cart_helpers.php       # Helper tính giỏ hàng từ session
│   │   ├── checkout.php           # Xử lý thanh toán
│   │   ├── find_product.php       # API tìm kiếm sản phẩm (fuzzy)
│   │   ├── customer_lookup.php    # API tra cứu khách hàng theo SĐT
│   │   └── invoice.php / in_hoa_don.php  # Xuất hóa đơn PDF
│   ├── 📂 products/               # Quản lý sản phẩm
│   ├── 📂 categories/             # Quản lý danh mục
│   ├── 📂 employees/              # Quản lý nhân viên
│   ├── 📂 customers/              # Quản lý khách hàng
│   ├── 📂 orders/                 # Xem đơn hàng
│   ├── 📂 reports/                # Báo cáo doanh thu & lợi nhuận
│   ├── 📂 profile/                # Hồ sơ cá nhân
│   └── 📂 api/                    # API fuzzy search
│
├── 📂 assets/
│   ├── css/                       # Style sheets
│   ├── js/                        # JavaScript files
│   ├── images/                    # Ảnh hệ thống (logo, ảnh mặc định)
│   └── uploads/                   # Ảnh do người dùng upload
│
├── 📂 libs/
│   ├── fpdf/                      # Thư viện tạo PDF (hóa đơn)
│   └── phpmailer/                 # Thư viện gửi email SMTP
│
├── 📂 database/
│   └── pos_system.sql             # File SQL khởi tạo database
│
└── 📂 storage/
    └── email_logs/                # Log email đã gửi (dạng HTML)
```

---

## ⚡ Cài Đặt Nhanh

### Yêu cầu hệ thống

- ✅ **XAMPP** (hoặc WAMP/Laragon) đã cài đặt
- ✅ **PHP** >= 8.0
- ✅ **MySQL** >= 5.7 (hoặc MariaDB >= 10.3)
- ✅ Trình duyệt Chrome / Firefox / Edge phiên bản mới

---

### Bước 1 — Tải & Đặt Dự Án Vào XAMPP

**Cách 1: Clone từ GitHub**
```bash
cd C:\xampp\htdocs
git clone https://github.com/xbanghjhj/web.git "Demo DA21"
```

**Cách 2: Tải ZIP**
1. Truy cập: https://github.com/xbanghjhj/web
2. Click **Code** → **Download ZIP**
3. Giải nén vào `C:\xampp\htdocs\Demo DA21\`

---

### Bước 2 — Khởi Động XAMPP

1. Mở **XAMPP Control Panel**
2. Nhấn **Start** cho cả **Apache** và **MySQL**
3. Đảm bảo cả hai hiển thị trạng thái xanh (running)

---

### Bước 3 — Tạo Database

**Cách A — Qua phpMyAdmin (dễ nhất):**

1. Mở trình duyệt, truy cập: `http://localhost/phpmyadmin`
2. Click **New** (bên trái)
3. Đặt tên database: `pos_system`
4. Chọn **Collation**: `utf8mb4_unicode_ci`
5. Click **Create**
6. Chọn database `pos_system` vừa tạo → tab **Import**
7. Chọn file `database/pos_system.sql` → click **Import**

**Cách B — Qua Command Line:**
```bash
# Mở CMD với quyền admin
cd C:\xampp\mysql\bin

# Tạo database và import
mysql -u root -p -e "CREATE DATABASE pos_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p pos_system < "C:\xampp\htdocs\Demo DA21\database\pos_system.sql"
```

---

### Bước 4 — Cấu Hình Kết Nối Database

Mở file `config/database.php` và điều chỉnh thông tin:

```php
define('DB_HOST', 'localhost');   // Không đổi nếu dùng XAMPP mặc định
define('DB_USER', 'root');        // Username MySQL (mặc định: root)
define('DB_PASS', '');            // Password MySQL (mặc định: rỗng)
define('DB_NAME', 'pos_system');  // Tên database vừa tạo
```

---

### Bước 5 — (Tuỳ chọn) Cấu Hình Email

Để tính năng gửi email kích hoạt tài khoản nhân viên hoạt động, mở `config/email_config.php`:

```php
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your_email@gmail.com');  // ← Gmail của bạn
define('MAIL_PASSWORD', 'your_app_password');      // ← App Password (không phải mật khẩu Gmail!)
define('MAIL_FROM',  'your_email@gmail.com');
define('MAIL_FROM_NAME', 'POS System');
```

> 💡 **Tạo App Password Gmail**: Google Account → Security → 2-Step Verification → App Passwords → Tạo mật khẩu cho "Mail"

---

### Bước 6 — Chạy Ứng Dụng

Mở trình duyệt và truy cập:

```
http://localhost/Demo%20DA21/
```

hoặc (nếu tên thư mục không có dấu cách):

```
http://localhost/DemoDA21/
```

---

### Bước 7 — Đăng Nhập Lần Đầu

| Trường | Giá trị |
|---|---|
| **Tên đăng nhập** | `admin` |
| **Mật khẩu** | `admin` |

> ⚠️ Hãy đổi mật khẩu ngay sau khi đăng nhập lần đầu!

---

### Bước 8 — Thêm Dữ Liệu Mẫu (Tuỳ chọn)

Để có sản phẩm và dữ liệu mẫu sẵn để test, truy cập:

```
http://localhost/Demo%20DA21/insert_sample_data.php
```

---

## 🗄️ Cấu Trúc Database

```sql
-- Bảng người dùng (admin + nhân viên)
users (id, username, email, password_hash, full_name, role, status, 
       must_change_password, email_token, avatar, created_at)

-- Bảng danh mục sản phẩm
categories (id, name, description, created_at)

-- Bảng sản phẩm
products (id, category_id, name, barcode, price_import, price_sell, 
          stock, image, description, created_at)

-- Bảng khách hàng
customers (id, name, phone, address, created_at)

-- Bảng đơn hàng
orders (id, order_code, customer_id, user_id, total_amount, 
        customer_paid, change_amount, created_at)

-- Bảng chi tiết đơn hàng
order_items (id, order_id, product_id, quantity, price_sell, 
             price_import, subtotal)
```

---

## 🔐 Luồng Hoạt Động Hệ Thống

```
[Người dùng] → [index.php - Đăng nhập]
                      ↓
              [process_login.php - Xác thực]
                      ↓
        ┌─────────────┴─────────────┐
     [Admin]                    [Staff]
        ↓                          ↓
[admin_dashboard.php]       [pos.php - Bán hàng]
        ↓
 ┌──────┼──────┬──────┬──────┬──────┐
[SX]  [DM]  [NV]  [KH]  [DH]  [BC]
Sản  Danh  Nhân  Khách  Đơn  Báo
phẩm  mục  viên  hàng  hàng cáo
```

---

## 🔧 Xử Lý Sự Cố Thường Gặp

### ❌ Lỗi "Lỗi kết nối database"
- Kiểm tra MySQL đã chạy trong XAMPP chưa
- Kiểm tra lại thông tin trong `config/database.php`
- Đảm bảo database `pos_system` đã được tạo và import SQL

### ❌ Trang trắng hoặc lỗi PHP
- Mở `config/config.php`, đổi `define('DEBUG_MODE', true)` để xem lỗi chi tiết
- Kiểm tra PHP version: `http://localhost/phpinfo.php`

### ❌ Upload ảnh không được
- Kiểm tra thư mục `assets/uploads/` có tồn tại không
- Đảm bảo Apache có quyền ghi vào thư mục này (Windows: thường không cần cấu hình)

### ❌ Email không gửi được
- Kiểm tra App Password Gmail (không dùng mật khẩu Google thường)
- Đảm bảo đã bật **2-Step Verification** trên tài khoản Gmail
- Thử đổi MAIL_PORT sang `465` và `MAIL_SMTPSECURE` sang `ssl`

### ❌ URL bị sai / redirect lỗi
- Mở `config/config.php`, thêm biến môi trường:
  ```php
  // Hoặc set thủ công BASE_URL nếu auto-detect sai
  define('BASE_URL', 'http://localhost/Demo DA21');
  ```

---

## 👥 Tài Khoản Mặc Định

| Loại | Username | Password | Quyền |
|---|---|---|---|
| Quản trị viên | `admin` | `admin` | Toàn quyền |
| Nhân viên mới | *(do admin tạo)* | `52000148` (mặc định) | Bán hàng, đổi mật khẩu |

---

## 📝 Ghi Chú Bảo Mật Quan Trọng

> ⚠️ Dự án này được thiết kế cho môi trường **học tập và demo**.  
> Trước khi triển khai thực tế (production), cần thực hiện thêm:

- [ ] Đổi `DEBUG_MODE` thành `false`
- [ ] Thêm CSRF Token cho tất cả form POST
- [ ] Cấu hình HTTPS (SSL)
- [ ] Giới hạn rate-limit cho trang đăng nhập
- [ ] Thay thế tài khoản `root` MySQL bằng user có quyền hạn chế
- [ ] Backup database định kỳ

---

## 🎓 Về Dự Án

Đây là đồ án thực hành môn **Phát triển ứng dụng Web** — Demo DA21.  
Dự án được xây dựng nhằm minh họa các kỹ thuật PHP cơ bản đến nâng cao trong việc xây dựng một hệ thống quản lý thực tế.

---

<div align="center">

**Made with ❤️ by Demo DA21 Team**  
*PHP · MySQL · Bootstrap · jQuery*

</div>
