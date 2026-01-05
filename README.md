# 🛍️ Vulnerable Shop - E-commerce Website với Lỗ Hổng Bảo Mật

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://www.mysql.com/)

> ⚠️ **CẢNH BÁO BẢO MẬT**: Website này được thiết kế với các lỗ hổng bảo mật có chủ đích để phục vụ mục đích học tập và nghiên cứu. **KHÔNG ĐƯỢC** triển khai lên môi trường production hoặc sử dụng code này cho ứng dụng thực tế!

## 📋 Mục Lục

- [Giới Thiệu](#-giới-thiệu)
- [Tính Năng](#-tính-năng)
- [Lỗ Hổng Bảo Mật](#-lỗ-hổng-bảo-mật)
- [Yêu Cầu Hệ Thống](#-yêu-cầu-hệ-thống)
- [Cài Đặt](#-cài-đặt)
- [Hướng Dẫn Sử Dụng](#-hướng-dẫn-sử-dụng)
- [Tài Khoản Mặc Định](#-tài-khoản-mặc-định)
- [Cấu Trúc Dự Án](#-cấu-trúc-dự-án)
- [Demo Khai Thác](#-demo-khai-thác)
- [License](#-license)

## 🎯 Giới Thiệu

**Vulnerable Shop** là một website thương mại điện tử bán quần áo được xây dựng với mục đích **giáo dục** trong lĩnh vực An toàn Thông tin. Dự án này tích hợp các lỗ hổng bảo mật phổ biến để sinh viên, học viên và các chuyên gia bảo mật có thể:

- 🎓 Học cách nhận diện các lỗ hổng bảo mật
- 🔍 Thực hành kỹ thuật penetration testing
- 🛡️ Hiểu cách khắc phục các lỗ hổng
- 📚 Nghiên cứu về Web Application Security

## ✨ Tính Năng

### Chức năng người dùng (User)
- ✅ Đăng ký tài khoản và đăng nhập
- ✅ Xem danh sách sản phẩm theo danh mục
- ✅ Tìm kiếm sản phẩm
- ✅ Xem chi tiết sản phẩm
- ✅ Đánh giá sản phẩm (Rating & Comment)
- ✅ Thêm sản phẩm vào giỏ hàng
- ✅ Quản lý giỏ hàng (thêm, xóa, cập nhật số lượng)
- ✅ Đặt hàng và thanh toán (COD)
- ✅ Xem lịch sử đơn hàng
- ✅ Xem chi tiết đơn hàng

### Chức năng quản trị (Admin)
- ✅ Quản lý sản phẩm (CRUD)
- ✅ Xem danh sách đơn hàng
- ✅ Cập nhật trạng thái đơn hàng

## 🔓 Lỗ Hổng Bảo Mật

Dự án tập trung vào **6 lỗ hổng bảo mật chính** được thiết kế để học tập và nghiên cứu. 

📚 **Tài liệu liên quan**:
- [VULNERABILITIES.md](VULNERABILITIES.md) - Chi tiết các lỗ hổng và cách khai thác
- [SECURITY_FIXES.md](SECURITY_FIXES.md) - Hướng dẫn khắc phục từng lỗ hổng

### 📋 Tổng Quan 6 Lỗ Hổng

| # | Chức năng | Lỗ hổng | Mức độ | File |
|---|-----------|---------|--------|------|
| 1 | Đăng ký - Đăng nhập | Brute Force, SQL Injection, Plaintext Password | 🔴 Critical | `login.php`, `register.php` |
| 2 | Tìm kiếm sản phẩm | SQL Injection | 🔴 Critical | `products.php` |
| 3 | Quản lý đơn hàng | IDOR | 🟠 High | `order_detail.php` |
| 4 | Quản lý sản phẩm | Data Validation Issues | 🟡 Medium | `admin/products_manage.php` |
| 5 | Đánh giá sản phẩm | Stored XSS, User Enumeration | 🔴 Critical | `product_detail.php` |
| 6 | Toàn hệ thống | Denial of Service (DoS) | 🟠 High | `products.php`, `dos_test.php` |

---

### 🔐 1. Lỗ Hổng Đăng Ký - Đăng Nhập

**Chức năng**: Đăng ký tài khoản và đăng nhập hệ thống  
**Files**: `login.php`, `register.php`

#### Rủi ro A: Brute Force Attack 🔨
- ❌ Không có rate limiting
- ❌ Không có CAPTCHA  
- ❌ Không khóa tài khoản sau N lần sai
- **Impact**: Chiếm quyền tài khoản, kể cả admin

#### Rủi ro B: SQL Injection trong Login
- ❌ Input không được sanitize
- ❌ Không dùng Prepared Statements
- **Payload mẫu**: `admin' OR '1'='1' --`
- **Impact**: Bypass authentication hoàn toàn

#### Rủi ro C: Plaintext Password Storage 🔑
- ❌ Mật khẩu lưu dạng plaintext (không hash)
- ❌ Vi phạm nguyên tắc bảo mật cơ bản
- **Impact**: Database leak → tất cả password bị lộ

---

### 🔍 2. Lỗ Hổng Tìm Kiếm Sản Phẩm

**Chức năng**: Tìm kiếm sản phẩm theo tên/mô tả  
**File**: `products.php`

#### SQL Injection (UNION-based) ⚠️
- ❌ Tham số `search` không được validate
- ❌ Concatenation trực tiếp vào SQL query
- **Payload dump users**:
  ```
  %' UNION SELECT id,CONCAT('User: ',username),CONCAT('Pass: ',password),0,0,'leaked',email,phone,created_at FROM users#
  ```
- **Impact**: 
  - Lộ toàn bộ database (users, orders, products)
  - Lộ password plaintext
  - Lộ cấu trúc database

---

### 📦 3. Lỗ Hổng Quản Lý Đơn Hàng

**Chức năng**: Xem chi tiết đơn hàng  
**File**: `order_detail.php`

#### IDOR (Insecure Direct Object Reference) 🎯
- ❌ Không kiểm tra quyền sở hữu đơn hàng
- ❌ User có thể xem đơn của người khác bằng cách đổi `id` trên URL
- **Demo**: `order_detail.php?id=1` → `order_detail.php?id=2`
- **Impact**:
  - Vi phạm quyền riêng tư
  - Lộ địa chỉ, số điện thoại khách hàng
  - Lộ thói quen mua hàng

---

### 🛠️ 4. Lỗ Hổng Quản Lý Sản Phẩm

**Chức năng**: Thêm/sửa sản phẩm (Admin)  
**File**: `admin/products_manage.php`

#### Data Validation & Integrity Issues 🟡
- ❌ Không validate giá (có thể âm hoặc = 0)
- ❌ Không validate tồn kho (có thể âm)
- ❌ Không sanitize mô tả (XSS risk)
- ❌ SQL Injection trong admin panel
- **Impact**:
  - Thiệt hại tài chính (giá âm/0)
  - Mất uy tín (thông tin sai lệch)
  - Khiếu nại, trả hàng
  - XSS → Chiếm quyền admin khác

## 💻 Yêu Cầu Hệ Thống

- **XAMPP** (hoặc tương đương):
  - PHP 7.4 trở lên
  - MySQL 5.7 trở lên
  - Apache Server
- **Browser**: Chrome, Firefox, hoặc Edge (phiên bản mới nhất)
- **Tools (tùy chọn)**: 
  - Burp Suite Community Edition
  - SQLMap
  - Postman

## 🚀 Cài Đặt

### Bước 1: Clone Repository

```bash
git clone https://github.com/cupengus1/vulnerable-shop.git
cd vulnerable-shop
```

Hoặc tải trực tiếp về và giải nén vào thư mục:
```
C:\xampp\htdocs\vulnerable-shop\
```

### Bước 2: Import Database

1. Mở XAMPP Control Panel và khởi động **Apache** và **MySQL**
2. Truy cập phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
3. Tạo database mới có tên `shop_db`
4. Import file `database.sql`:
   - Click vào database `shop_db`
   - Chọn tab **Import**
   - Chọn file `database.sql`
   - Click **Go**

### Bước 3: Cấu Hình Kết Nối Database

Mở file `config.php` và kiểm tra thông tin kết nối:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Mật khẩu MySQL (mặc định là rỗng)
define('DB_NAME', 'shop_db');
?>
```

### Bước 4: Truy Cập Website

Mở trình duyệt và truy cập:
- **Trang chủ**: [http://localhost/vulnerable-shop/](http://localhost/vulnerable-shop/)
- **Admin Panel**: [http://localhost/vulnerable-shop/admin/](http://localhost/vulnerable-shop/admin/)

## 📖 Hướng Dẫn Sử Dụng 

## 📁 Cấu Trúc Dự Án

```
vulnerable-shop/
├── admin/                      # Admin panel
│   ├── index.php              # Admin dashboard
│   └── products_manage.php    # Quản lý sản phẩm
├── assets/                     # Tài nguyên tĩnh
│   ├── css/
│   │   └── style.css         # Stylesheet chính
│   ├── js/
│   │   └── main.js           # JavaScript
│   └── images/               # Hình ảnh sản phẩm
├── includes/                   # Các file dùng chung
│   ├── header.php            # Header template
│   └── footer.php            # Footer template
├── config.php                  # Cấu hình database
├── database.sql               # Database schema và sample data
├── index.php                  # Trang chủ
├── products.php               # Danh sách sản phẩm (🔓 SQL Injection)
├── product_detail.php         # Chi tiết sản phẩm
├── cart.php                   # Giỏ hàng
├── checkout.php               # Thanh toán
├── orders.php                 # Lịch sử đơn hàng
├── order_detail.php           # Chi tiết đơn hàng (🔓 IDOR)
├── login.php                  # Đăng nhập (🔓 Brute Force)
├── register.php               # Đăng ký
├── logout.php                 # Đăng xuất
├── test_sql.php               # Test SQL injection
├── README.md                  # Tài liệu này
├── VULNERABILITIES.md         # Chi tiết lỗ hổng bảo mật
└── SECURITY_FIXES.md          # Hướng dẫn khắc phục lỗ hổng
```

## 🎯 Demo Khai Thác

### SQL Injection Example

**Payload 1**: Dump thông tin users
```
http://localhost/vulnerable-shop/products.php?search=%' UNION SELECT id,CONCAT('User: ',username),CONCAT('Pass: ',password),0,0,'user-data',email,phone,created_at FROM users#
```

**Payload 2**: Bypass login
```sql
Username: admin' OR '1'='1
Password: anything
```

### IDOR Example

Thay đổi ID đơn hàng để xem đơn của người khác:
```
http://localhost/vulnerable-shop/order_detail.php?id=1
http://localhost/vulnerable-shop/order_detail.php?id=2  ← Đơn hàng của user khác
```

### Brute Force Example

Sử dụng Burp Suite Intruder:
1. Capture request đăng nhập
2. Chọn password field làm payload position
3. Load wordlist (ví dụ: rockyou.txt)
4. Chạy attack

## 🛡️ Khắc Phục Lỗ Hổng

Xem hướng dẫn chi tiết cách khắc phục từng lỗ hổng tại **[SECURITY_FIXES.md](SECURITY_FIXES.md)** với:

- ✅ Code before/after cho từng lỗ hổng
- ✅ Giải thích từng bước
- ✅ Multiple phương pháp fix (basic → advanced)
- ✅ Best practices và testing checklist

**Gợi ý nhanh**:

1. **SQL Injection**: Sử dụng Prepared Statements (PDO/MySQLi)
2. **Password Storage**: Hash với `password_hash()` và `password_verify()`
3. **Brute Force**: Implement rate limiting + CAPTCHA
4. **IDOR**: Kiểm tra quyền sở hữu tài nguyên
5. **Data Validation**: Validate & sanitize tất cả input

## 🤝 Đóng Góp

Dự án này phục vụ mục đích giáo dục. Nếu bạn muốn đóng góp:

1. Fork repository
2. Tạo branch mới: `git checkout -b feature/new-vulnerability`
3. Commit changes: `git commit -m 'Add new vulnerability: XXX'`
4. Push: `git push origin feature/new-vulnerability`
5. Tạo Pull Request

## ⚖️ License

Dự án này được phân phối dưới giấy phép [MIT License](https://opensource.org/licenses/MIT).

## 📞 Liên Hệ

- **GitHub**: [@cupengus1](https://github.com/cupengus1)
- **Repository**: [vulnerable-shop](https://github.com/cupengus1/vulnerable-shop)

---

### ⚠️ DISCLAIMER

Dự án này chỉ dành cho mục đích **học tập và nghiên cứu** trong môi trường **localhost**. Không sử dụng các kỹ thuật khai thác trên website thực tế mà không có sự cho phép. Tác giả không chịu trách nhiệm về bất kỳ hành vi vi phạm pháp luật nào.

**Sử dụng có trách nhiệm! 🙏**
