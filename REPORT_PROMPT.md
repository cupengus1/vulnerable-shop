# 📝 Prompt Viết Báo Cáo Tiểu Luận - Vulnerable Shop

> **Hướng dẫn viết báo cáo về dự án Vulnerable Shop - Website thương mại điện tử với lỗ hổng bảo mật có chủ đích phục vụ học tập**

---

## 🎯 Thông Tin Dự Án

**Tên dự án**: Vulnerable Shop - E-commerce Security Testing Platform  
**Công nghệ**: PHP, MySQL, HTML/CSS/JavaScript  
**Repository**: https://github.com/cupengus1/vulnerable-shop  
**Mục đích**: Học tập và nghiên cứu về An toàn Thông tin Web

---

## 📋 YÊU CẦU BÁO CÁO

**Độ dài**: 10-15 trang  
**Font**: Times New Roman, 13pt  
**Giãn dòng**: 1.5  
**Cấu trúc**: 4 chương

---

## 📚 CẤU TRÚC BÁO CÁO

### PHẦN MỞ ĐẦU

#### Trang Bìa
- Tên trường/khoa
- Tên môn học  
- Tiêu đề: **"XÂY DỰNG WEBSITE THƯƠNG MẠI ĐIỆN TỬ VỚI LỖ HỔNG BẢO MẬT PHỤC VỤ HỌC TẬP AN TOÀN THÔNG TIN"**
- Họ tên, MSSV
- Giảng viên hướng dẫn
- Năm học

#### Mục Lục

---

## CHƯƠNG 1: CƠ SỞ LÝ THUYẾT

### 1.1. Tổng quan về An toàn Web Application

- **Định nghĩa**: Web Application Security là lĩnh vực bảo vệ ứng dụng web khỏi các cuộc tấn công
- **Mô hình CIA**: 
  - Confidentiality (Bảo mật)
  - Integrity (Toàn vẹn)
  - Availability (Khả dụng)
- Tầm quan trọng trong thương mại điện tử
- Xu hướng tấn công web hiện nay

### 1.2. OWASP Top 10

**Giới thiệu OWASP**:
- Open Web Application Security Project
- Tổ chức phi lợi nhuận về bảo mật
- OWASP Top 10 - Danh sách 10 lỗ hổng phổ biến nhất

**Các lỗ hổng áp dụng trong dự án**:

#### 1.2.1. SQL Injection (A03:2021)

| Thuộc tính | Nội dung |
|------------|----------|
| **Định nghĩa** | Chèn mã SQL độc hại vào query thông qua input không được sanitize |
| **Nguyên nhân** | String concatenation, không dùng prepared statements |
| **Loại** | In-band (UNION), Blind, Out-of-band |
| **Impact** | Data leak, authentication bypass, RCE |

**Ví dụ**:
```sql
-- Query gốc
SELECT * FROM users WHERE username='admin' AND password='123'

-- Payload tấn công
SELECT * FROM users WHERE username='admin' OR '1'='1'--' AND password='...'
```

#### 1.2.2. Broken Authentication (A07:2021)

| Thuộc tính | Nội dung |
|------------|----------|
| **Định nghĩa** | Lỗi trong cơ chế xác thực cho phép attacker chiếm quyền |
| **Brute Force** | Thử mật khẩu liên tục không bị chặn |
| **Credential Stuffing** | Dùng leaked passwords từ sites khác |
| **Plaintext Password** | Lưu mật khẩu không mã hóa |
| **Impact** | Account takeover, identity theft |

#### 1.2.3. Broken Access Control - IDOR (A01:2021)

| Thuộc tính | Nội dung |
|------------|----------|
| **Định nghĩa** | Truy cập tài nguyên không được phép bằng cách thay đổi ID |
| **IDOR** | Insecure Direct Object Reference |
| **Ví dụ** | Đổi `order_detail.php?id=1` → `id=2` để xem đơn người khác |
| **Impact** | Privacy violation, data exposure |

#### 1.2.4. Stored Cross-Site Scripting - XSS (A03:2021)

| Thuộc tính | Nội dung |
|------------|----------|
| **Định nghĩa** | Chèn mã script độc hại được lưu trữ vĩnh viễn trên server |
| **Ví dụ** | Chèn script vào phần đánh giá sản phẩm |
| **Impact** | Đánh cắp session cookie, chiếm quyền tài khoản |

#### 1.2.5. Security Misconfiguration (A05:2021)

| Thuộc tính | Nội dung |
|------------|----------|
| **Định nghĩa** | Cấu hình sai hoặc thiếu validation dẫn đến lỗ hổng |
| **Data Validation** | Không kiểm tra input (giá âm, XSS) |
| **Error Messages** | Hiển thị lỗi chi tiết cho user |
| **Impact** | Data corruption, XSS, information disclosure |

### 1.3. Các phương pháp bảo mật

#### 1.3.1. Prepared Statements / Parameterized Queries
- Tách biệt code SQL và data
- Ngăn chặn SQL Injection hoàn toàn
- Hỗ trợ: PDO, MySQLi

```php
// SECURE
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
```

#### 1.3.2. Password Hashing
- Không lưu plaintext password
- Thuật toán: bcrypt, Argon2
- PHP: `password_hash()`, `password_verify()`

```php
// Hash khi đăng ký
$hash = password_hash($password, PASSWORD_DEFAULT);

// Verify khi đăng nhập
if (password_verify($input, $hash)) { /* OK */ }
```

#### 1.3.3. Rate Limiting
- Giới hạn số request trong khoảng thời gian
- Ngăn brute force, DDoS
- Kết hợp CAPTCHA

#### 1.3.4. Authorization Check
- Kiểm tra quyền trước khi trả dữ liệu
- Verify ownership của resource
- Session-based access control

#### 1.3.5. Input Validation & Output Encoding
- **Validation**: Kiểm tra input hợp lệ (whitelist)
- **Sanitization**: Loại bỏ ký tự nguy hiểm
- **Output Encoding**: `htmlspecialchars()` ngăn XSS

---

## CHƯƠNG 2: CÔNG NGHỆ, PHÂN TÍCH, THIẾT KẾ VÀ XÂY DỰNG

### 2.1. Công nghệ sử dụng

#### 2.1.1. PHP (Hypertext Preprocessor)

| Thuộc tính | Nội dung |
|------------|----------|
| **Phiên bản** | 7.4+ |
| **Loại** | Server-side scripting |
| **Ưu điểm** | Dễ học, tích hợp MySQL tốt, cộng đồng lớn |
| **Nhược điểm** | Dễ viết code không an toàn nếu không cẩn thận |

#### 2.1.2. MySQL

| Thuộc tính | Nội dung |
|------------|----------|
| **Phiên bản** | 5.7+ |
| **Loại** | Relational Database Management System |
| **Ưu điểm** | Open source, hiệu suất cao, phổ biến |
| **Charset** | utf8mb4 (hỗ trợ Unicode đầy đủ) |

#### 2.1.3. XAMPP

| Thành phần | Mô tả |
|------------|-------|
| **X** | Cross-platform |
| **A** | Apache Web Server |
| **M** | MySQL/MariaDB |
| **P** | PHP |
| **P** | Perl |

**Môi trường**: localhost development

### 2.2. Phân tích yêu cầu

#### 2.2.1. Yêu cầu chức năng

**Người dùng (User)**:

| STT | Chức năng | Mô tả |
|-----|-----------|-------|
| 1 | Đăng ký | Tạo tài khoản với username, email, password |
| 2 | Đăng nhập | Xác thực vào hệ thống |
| 3 | Xem sản phẩm | Duyệt danh sách, lọc theo category |
| 4 | Tìm kiếm | Tìm sản phẩm theo tên/mô tả |
| 5 | Giỏ hàng | Thêm, xóa, cập nhật số lượng |
| 6 | Đặt hàng | Checkout, thanh toán COD |
| 7 | Xem đơn hàng | Lịch sử và chi tiết đơn hàng |
| 8 | Đánh giá | Gửi nhận xét và số sao cho sản phẩm |

**Quản trị viên (Admin)**:

| STT | Chức năng | Mô tả |
|-----|-----------|-------|
| 1 | Quản lý sản phẩm | CRUD (Create, Read, Update, Delete) |
| 2 | Quản lý đơn hàng | Xem danh sách, cập nhật trạng thái |

#### 2.2.2. Yêu cầu về lỗ hổng bảo mật

| # | Chức năng | Lỗ hổng | Mức độ | File |
|---|-----------|---------|--------|------|
| 1 | Đăng ký - Đăng nhập | Brute Force, SQL Injection, Plaintext Password | 🔴 Critical | `login.php`, `register.php` |
| 2 | Tìm kiếm sản phẩm | SQL Injection (UNION-based) | 🔴 Critical | `products.php` |
| 3 | Xem đơn hàng | IDOR | 🟠 High | `order_detail.php` |
| 4 | Quản lý sản phẩm | Data Validation Issues | 🟡 Medium | `admin/products_manage.php` |
| 5 | Đánh giá sản phẩm | Stored XSS, User Enumeration | 🔴 Critical | `product_detail.php` |

### 2.3. Thiết kế Database

#### 2.3.1. Sơ đồ ERD

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│   users     │       │   orders    │       │  products   │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ id (PK)     │───┐   │ id (PK)     │       │ id (PK)     │
│ username    │   │   │ user_id(FK) │◄──────│ name        │
│ password    │   └──►│ total       │       │ description │
│ email       │       │ status      │       │ price       │
│ full_name   │       │ address     │       │ stock       │
│ phone       │       │ created_at  │       │ category    │
│ address     │       └──────┬──────┘       │ image       │
│ role        │              │              └──────┬──────┘
│ created_at  │              │                     │
└─────────────┘              │                     │
                      ┌──────┴──────┐              │
                      │ order_items │              │
                      ├─────────────┤              │
                      │ id (PK)     │              │
                      │ order_id(FK)│◄─────────────┘
                      │ product_id  │
                      │ quantity    │
                      │ price       │
                      └─────────────┘

┌─────────────┐
│   reviews   │
├─────────────┤
│ id (PK)     │
│ product_id  │
│ user_id     │
│ rating      │
│ comment     │
│ created_at  │
└─────────────┘
```

#### 2.3.2. Mô tả các bảng

**Bảng `users`**:

| Column | Type | Constraint | Mô tả | Lỗ hổng |
|--------|------|------------|-------|---------|
| id | INT | PK, AUTO_INCREMENT | ID người dùng | - |
| username | VARCHAR(50) | UNIQUE, NOT NULL | Tên đăng nhập | - |
| password | VARCHAR(255) | NOT NULL | Mật khẩu | ⚠️ Plaintext |
| email | VARCHAR(100) | NOT NULL | Email | - |
| role | ENUM | DEFAULT 'user' | Vai trò | - |

**Bảng `products`**:

| Column | Type | Mô tả | Lỗ hổng |
|--------|------|-------|---------|
| id | INT | ID sản phẩm | - |
| name | VARCHAR(200) | Tên sản phẩm | - |
| price | DECIMAL(10,2) | Giá | ⚠️ Không validate |
| stock | INT | Tồn kho | ⚠️ Không validate |
| description | TEXT | Mô tả | ⚠️ XSS risk |

**Bảng `reviews`**:

| Column | Type | Mô tả | Lỗ hổng |
|--------|------|-------|---------|
| id | INT | ID đánh giá | - |
| product_id | INT | ID sản phẩm | - |
| user_id | INT | ID người dùng | ⚠️ User Enumeration |
| rating | INT | Số sao | - |
| comment | TEXT | Nội dung | ⚠️ Stored XSS |

### 2.4. Thiết kế kiến trúc

```
┌───────────────────────────────────────────────────────┐
│                      CLIENT                           │
│                    (Browser)                          │
└─────────────────────────┬─────────────────────────────┘
                          │ HTTP Request/Response
                          ▼
┌───────────────────────────────────────────────────────┐
│                   WEB SERVER                          │
│                    (Apache)                           │
└─────────────────────────┬─────────────────────────────┘
                          │
                          ▼
┌───────────────────────────────────────────────────────┐
│                   PHP ENGINE                          │
│              (Business Logic Layer)                   │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐      │
│  │ login.php  │  │products.php│  │ orders.php │      │
│  │  [VULN]    │  │   [VULN]   │  │   [VULN]   │      │
│  └────────────┘  └────────────┘  └────────────┘      │
└─────────────────────────┬─────────────────────────────┘
                          │ SQL Query
                          ▼
┌───────────────────────────────────────────────────────┐
│                     MySQL                             │
│                   (shop_db)                           │
│  ┌────────┐ ┌──────────┐ ┌────────┐ ┌─────────────┐  │
│  │ users  │ │ products │ │ orders │ │ order_items │  │
│  └────────┘ └──────────┘ └────────┘ └─────────────┘  │
└───────────────────────────────────────────────────────┘
```

### 2.5. Cấu trúc thư mục

```
vulnerable-shop/
├── admin/
│   ├── index.php              # Admin dashboard
│   └── products_manage.php    # [VULN #4] Data Validation
├── assets/
│   ├── css/style.css
│   ├── js/main.js
│   └── images/
├── includes/
│   ├── header.php
│   └── footer.php
├── config.php                 # Database connection
├── database.sql               # SQL schema
├── index.php                  # Homepage
├── products.php               # [VULN #2] SQL Injection
├── product_detail.php
├── login.php                  # [VULN #1] Brute Force + SQLi
├── register.php               # [VULN #1] Plaintext Password
├── cart.php
├── checkout.php
├── orders.php
├── order_detail.php           # [VULN #3] IDOR
├── logout.php
├── README.md
├── VULNERABILITIES.md
└── SECURITY_FIXES.md
```

### 2.6. Xây dựng các module có lỗ hổng

#### 2.6.1. Module Đăng nhập (login.php) - VULNERABLE

```php
<?php
// login.php - CODE DỄ BỊ TẤN CÔNG
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];  // Không sanitize
    $password = $_POST['password'];  // Không sanitize
    
    // ❌ LỖI 1: SQL Injection - String concatenation
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);
    
    // ❌ LỖI 2: Không có rate limiting
    // ❌ LỖI 3: Password so sánh plaintext
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: index.php');
    } else {
        $error = "Invalid username or password";
    }
}
?>
```

**Phân tích lỗ hổng**:
- **SQL Injection**: Input được nối trực tiếp vào query
- **Brute Force**: Không giới hạn số lần đăng nhập sai
- **Plaintext**: So sánh password trực tiếp, không hash

#### 2.6.2. Module Tìm kiếm (products.php) - VULNERABLE

```php
<?php
// products.php - CODE DỄ BỊ TẤN CÔNG
require_once 'config.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($search)) {
    // ❌ SQL Injection - UNION attack possible
    $sql = "SELECT * FROM products WHERE name LIKE '%$search%' OR description LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM products";
}

$result = mysqli_query($conn, $sql);

while ($product = mysqli_fetch_assoc($result)) {
    // ❌ XSS - Không escape output
    echo "<h3>" . $product['name'] . "</h3>";
    echo "<p>" . $product['description'] . "</p>";
}
?>
```

**Payload khai thác**:
```
%' UNION SELECT id,CONCAT('User: ',username),CONCAT('Pass: ',password),0,0,'x',email,phone,created_at FROM users#
```

#### 2.6.3. Module Đơn hàng (order_detail.php) - VULNERABLE

```php
<?php
// order_detail.php - CODE DỄ BỊ TẤN CÔNG
session_start();
require_once 'config.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$order_id = $_GET['id'];  // Không validate

// ❌ IDOR - Không kiểm tra ownership
$sql = "SELECT * FROM orders WHERE id = $order_id";
$result = mysqli_query($conn, $sql);
$order = mysqli_fetch_assoc($result);

// Hiển thị đơn hàng mà không check user_id
?>
```

**Khai thác**: Thay đổi parameter `id` để xem đơn hàng của user khác

#### 2.6.4. Module Admin (products_manage.php) - VULNERABLE

```php
<?php
// admin/products_manage.php - CODE DỄ BỊ TẤN CÔNG
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];        // ❌ Không validate - có thể âm
    $stock = $_POST['stock'];        // ❌ Không validate - có thể âm
    $description = $_POST['description'];  // ❌ Không sanitize - XSS
    $category = $_POST['category'];
    
    // ❌ SQL Injection + No validation
    $sql = "INSERT INTO products (name, description, price, stock, category) 
            VALUES ('$name', '$description', $price, $stock, '$category')";
    mysqli_query($conn, $sql);
}
?>
```

**Các lỗi**:
- `price = -1000000` → Sản phẩm giá âm
- `stock = -999` → Tồn kho âm
- `description = <script>alert('XSS')</script>` → Stored XSS

---

## CHƯƠNG 3: KIỂM THỬ, ĐÁNH GIÁ VÀ KHẮC PHỤC

### 3.1. Kiểm thử chức năng

| Test Case | Mô tả | Input | Expected | Status |
|-----------|-------|-------|----------|--------|
| TC01 | Đăng ký user mới | username, email, pass | Tạo account | ✅ Pass |
| TC02 | Đăng nhập hợp lệ | admin / admin123 | Login OK | ✅ Pass |
| TC03 | Tìm kiếm sản phẩm | "áo" | Hiển thị kết quả | ✅ Pass |
| TC04 | Thêm giỏ hàng | Click Add | Vào cart | ✅ Pass |
| TC05 | Đặt hàng | Checkout | Tạo order | ✅ Pass |
| TC06 | Xem đơn hàng | Click order | Hiển thị detail | ✅ Pass |

### 3.2. Kiểm thử lỗ hổng bảo mật

#### 3.2.1. Test SQL Injection

**Test VUL-01: SQL Injection trong Search**

| Thuộc tính | Nội dung |
|------------|----------|
| **URL** | `products.php?search=PAYLOAD` |
| **Payload** | `%' UNION SELECT id,username,password,0,0,'x',email,phone,created_at FROM users#` |
| **Expected** | Hiển thị username và password trên giao diện |
| **Result** | ✅ **PASS** - Exploit thành công |

**Screenshot**: [Chèn ảnh kết quả - hiển thị passwords]

**Test VUL-02: SQL Injection trong Login**

| Thuộc tính | Nội dung |
|------------|----------|
| **Payload** | Username: `admin' OR '1'='1'--`, Password: `anything` |
| **Expected** | Bypass authentication, login as admin |
| **Result** | ✅ **PASS** - Login thành công không cần password |

#### 3.2.2. Test Brute Force

**Test VUL-03: Brute Force Login**

| Thuộc tính | Nội dung |
|------------|----------|
| **Tool** | Burp Suite Intruder |
| **Target** | `login.php` (POST) |
| **Wordlist** | Top 1000 passwords |
| **Expected** | Không bị block sau nhiều lần thử |
| **Result** | ✅ **PASS** - Thử 1000 lần không bị chặn |

**Kết quả Burp Suite**: Password `admin123` found sau 45 attempts

#### 3.2.3. Test IDOR

**Test VUL-04: IDOR trong Order Detail**

| Thuộc tính | Nội dung |
|------------|----------|
| **Setup** | Login as user1 (có order id=1) |
| **Action** | Đổi URL từ `?id=1` → `?id=2` |
| **Expected** | Xem được đơn hàng của user2 |
| **Result** | ✅ **PASS** - Xem được đơn hàng người khác |

**Data leaked**: Địa chỉ giao hàng, SĐT, sản phẩm đã mua, tổng tiền

#### 3.2.4. Test Data Validation

**Test VUL-05: Negative Price**

| Thuộc tính | Nội dung |
|------------|----------|
| **Input** | `price = -1000000` |
| **Expected** | Từ chối, báo lỗi |
| **Actual** | Tạo sản phẩm giá âm thành công |
| **Result** | ✅ **PASS** - Lỗ hổng confirmed |

**Test VUL-06: XSS trong Description**

| Thuộc tính | Nội dung |
|------------|----------|
| **Input** | `<script>alert('XSS')</script>` |
| **Expected** | Escape, không thực thi |
| **Actual** | Script được lưu và thực thi |
| **Result** | ✅ **PASS** - Stored XSS confirmed |

### 3.3. Tổng hợp kết quả đánh giá

| Vulnerability | Severity | OWASP | Confirmed | Exploitable |
|---------------|----------|-------|-----------|-------------|
| SQL Injection (Search) | 🔴 Critical | A03 | ✅ Yes | 100% |
| SQL Injection (Login) | 🔴 Critical | A03 | ✅ Yes | 100% |
| Plaintext Password | 🔴 Critical | A07 | ✅ Yes | 100% |
| Brute Force | 🟠 High | A07 | ✅ Yes | 100% |
| IDOR | 🟠 High | A01 | ✅ Yes | 100% |
| Data Validation | 🟡 Medium | A05 | ✅ Yes | 100% |

### 3.4. Hướng dẫn khắc phục

#### 3.4.1. Fix SQL Injection

**❌ Before (Vulnerable)**:
```php
$search = $_GET['search'];
$sql = "SELECT * FROM products WHERE name LIKE '%$search%'";
$result = mysqli_query($conn, $sql);
```

**✅ After (Secure)**:
```php
$search = $_GET['search'];
$stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ?");
$searchTerm = "%$search%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
```

**Giải thích**: Prepared Statement tách biệt SQL code và data, ngăn injection.

#### 3.4.2. Fix Brute Force

**✅ Thêm Rate Limiting**:
```php
session_start();

// Khởi tạo counter
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = time();
}

// Reset sau 15 phút
if (time() - $_SESSION['last_attempt'] > 900) {
    $_SESSION['login_attempts'] = 0;
}

// Check limit
if ($_SESSION['login_attempts'] >= 5) {
    die("Too many attempts. Try again in 15 minutes.");
}

// Nếu login fail:
$_SESSION['login_attempts']++;
$_SESSION['last_attempt'] = time();
```

#### 3.4.3. Fix Plaintext Password

**✅ Hash khi đăng ký**:
```php
// register.php
$password = $_POST['password'];
$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $hashed, $email);
$stmt->execute();
```

**✅ Verify khi đăng nhập**:
```php
// login.php
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user && password_verify($_POST['password'], $user['password'])) {
    // Login success
    $_SESSION['user_id'] = $user['id'];
}
```

#### 3.4.4. Fix IDOR

**✅ Kiểm tra ownership**:
```php
session_start();
$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Check ownership
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Access denied! This order does not belong to you.");
}

$order = $result->fetch_assoc();
```

#### 3.4.5. Fix Data Validation

**✅ Validate và Sanitize**:
```php
// Validate price
$price = floatval($_POST['price']);
if ($price < 0 || $price > 999999999) {
    die("Invalid price!");
}

// Validate stock
$stock = intval($_POST['stock']);
if ($stock < 0) {
    die("Invalid stock!");
}

// Sanitize description (prevent XSS)
$description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');

// Use prepared statement
$stmt = $conn->prepare("INSERT INTO products (name, description, price, stock) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssdi", $name, $description, $price, $stock);
$stmt->execute();
```

### 3.5. Bảng tổng hợp giải pháp

| Lỗ hổng | Nguyên nhân | Giải pháp | Công nghệ |
|---------|-------------|-----------|-----------|
| SQL Injection | String concatenation | Prepared Statements | PDO, MySQLi |
| Brute Force | No rate limiting | Session counter + CAPTCHA | PHP Session, reCAPTCHA |
| Plaintext Pass | Không hash | Password hashing | `password_hash()`, bcrypt |
| IDOR | No auth check | Ownership verification | Session + DB check |
| Data Validation | No validation | Input validation + sanitize | `filter_var()`, `htmlspecialchars()` |

---

## CHƯƠNG 4: TỔNG KẾT

### 4.1. Kết quả đạt được

#### 4.1.1. Về sản phẩm

✅ **Website hoàn chỉnh**:
- Đầy đủ chức năng e-commerce cơ bản
- User: đăng ký, đăng nhập, xem sản phẩm, giỏ hàng, đặt hàng
- Admin: quản lý sản phẩm, đơn hàng
- Giao diện responsive, dễ sử dụng

✅ **Tích hợp lỗ hổng**:
- 4 loại lỗ hổng theo OWASP Top 10
- Có thể exploit được 100%
- Phục vụ mục đích học tập

#### 4.1.2. Về tài liệu

| File | Nội dung | Số dòng |
|------|----------|---------|
| README.md | Hướng dẫn cài đặt, tổng quan | ~300 |
| VULNERABILITIES.md | Chi tiết lỗ hổng, payloads, kịch bản tấn công | ~540 |
| SECURITY_FIXES.md | Hướng dẫn khắc phục, code examples | ~1,400 |

#### 4.1.3. Đánh giá mức độ hoàn thành

| Mục tiêu | Hoàn thành | Ghi chú |
|----------|------------|---------|
| Website e-commerce | ✅ 100% | Đầy đủ chức năng |
| 4 lỗ hổng OWASP | ✅ 100% | Đã test thành công |
| Tài liệu khai thác | ✅ 100% | Chi tiết, có ví dụ |
| Hướng dẫn khắc phục | ✅ 100% | Code before/after |

### 4.2. Ý nghĩa của đề tài

#### 4.2.1. Ý nghĩa học thuật
- Minh họa trực quan các lỗ hổng OWASP Top 10
- Môi trường thực hành an toàn, có kiểm soát
- Tài liệu tiếng Việt cho sinh viên

#### 4.2.2. Ý nghĩa thực tiễn
- Nâng cao kỹ năng secure coding
- Hiểu mindset của attacker
- Chuẩn bị cho công việc pentester/developer

### 4.3. Hạn chế

| Hạn chế | Mô tả |
|---------|-------|
| **Chức năng** | Chưa có review sản phẩm, payment gateway |
| **Lỗ hổng** | Chưa cover hết OWASP Top 10 (XSS, CSRF, XXE...) |
| **UI/UX** | Giao diện còn đơn giản |
| **Testing** | Chưa có automated testing scripts |

### 4.4. Hướng phát triển

#### 4.4.1. Ngắn hạn (1-3 tháng)
- Thêm lỗ hổng: Stored XSS, CSRF, File Upload
- Cải thiện UI/UX
- Thêm tính năng: Email notification, password reset

#### 4.4.2. Dài hạn (6-12 tháng)
- Multiple difficulty levels (Easy/Medium/Hard)
- Docker deployment
- Automated scoring system
- CTF integration

### 4.5. Kết luận

Dự án **Vulnerable Shop** đã hoàn thành mục tiêu xây dựng một platform học tập về An toàn Thông tin Web với:

1. **Website thực tế**: E-commerce đầy đủ chức năng
2. **Lỗ hổng có chủ đích**: 4 loại theo OWASP Top 10
3. **Tài liệu chi tiết**: Khai thác + Khắc phục

Dự án góp phần vào việc đào tạo nguồn nhân lực An toàn Thông tin, giúp sinh viên và lập trình viên hiểu rõ các rủi ro bảo mật và cách phòng chống.

---

## TÀI LIỆU THAM KHẢO

[1] OWASP Foundation. (2021). *OWASP Top 10 - 2021*. https://owasp.org/Top10/

[2] PortSwigger. (2023). *Web Security Academy*. https://portswigger.net/web-security

[3] PHP Group. (2023). *PHP Manual - Security*. https://www.php.net/manual/en/security.php

[4] Stuttard, D., & Pinto, M. (2011). *The Web Application Hacker's Handbook* (2nd ed.). Wiley.

---

## PHỤ LỤC

### A. Source Code (database.sql)

### B. Screenshots giao diện website

### C. Screenshots khai thác lỗ hổng

### D. Tài khoản test

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Admin |
| user1 | password123 | User |
| user2 | mypass456 | User |

---

## 📝 CHECKLIST HOÀN THÀNH

- [ ] Trang bìa đầy đủ
- [ ] Mục lục tự động
- [ ] Chương 1: Cơ sở lý thuyết
- [ ] Chương 2: Công nghệ, thiết kế, xây dựng
- [ ] Chương 3: Kiểm thử, đánh giá, khắc phục
- [ ] Chương 4: Tổng kết
- [ ] Tài liệu tham khảo
- [ ] Phụ lục (screenshots, code)
- [ ] Đánh số trang
- [ ] Kiểm tra chính tả

---

**Chúc bạn viết báo cáo thành công! 📚**

*Repository: https://github.com/cupengus1/vulnerable-shop*
