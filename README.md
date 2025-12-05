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

Dự án này chứa các lỗ hổng bảo mật sau (chi tiết trong [VULNERABILITIES.md](VULNERABILITIES.md)):

### 1. SQL Injection ⚠️ **Critical**
- **Vị trí**: `products.php` (tham số `search`)
- **Mô tả**: Tìm kiếm sản phẩm không sanitize input, cho phép khai thác UNION-based SQL Injection
- **Impact**: Dump toàn bộ database, bypass authentication

### 2. Insecure Password Storage 🔑 **High**
- **Vị trí**: Bảng `users` trong database
- **Mô tả**: Mật khẩu lưu dạng plaintext (không mã hóa)
- **Impact**: Attacker có thể đọc trực tiếp mật khẩu nếu có quyền truy cập database

### 3. Brute Force Attack 🔨 **Medium**
- **Vị trí**: `login.php`
- **Mô tả**: Không có rate limiting, không có CAPTCHA
- **Impact**: Attacker có thể brute force password với tools như Hydra, Burp Suite

### 4. Insecure Direct Object Reference (IDOR) 🎯 **High**
- **Vị trí**: `order_detail.php` (tham số `id`)
- **Mô tả**: Không kiểm tra quyền sở hữu đơn hàng
- **Impact**: User có thể xem đơn hàng của người khác bằng cách thay đổi ID

### 5. Reflected XSS 💉 **Medium**
- **Vị trí**: Các trang có output trực tiếp từ GET/POST parameters
- **Mô tả**: Input không được escape trước khi hiển thị
- **Impact**: Thực thi JavaScript độc hại trên trình duyệt nạn nhân

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

### Đăng Ký và Đăng Nhập

1. Truy cập trang đăng ký: `register.php`
2. Điền thông tin (username, password, email, v.v.)
3. Đăng nhập tại `login.php`

### Mua Hàng

1. Browse sản phẩm tại `products.php` hoặc `index.php`
2. Click vào sản phẩm để xem chi tiết
3. Thêm vào giỏ hàng
4. Xem giỏ hàng tại `cart.php`
5. Checkout và hoàn tất đơn hàng

### Quản Trị (Admin)

1. Đăng nhập với tài khoản admin
2. Truy cập `/admin/products_manage.php`
3. Thêm, sửa, xóa sản phẩm

## 🔑 Tài Khoản Mặc Định

| Username | Password | Role | Email |
|----------|----------|------|-------|
| `admin` | `admin123` | Admin | admin@shop.com |
| `user1` | `password123` | User | user1@email.com |
| `user2` | `mypass456` | User | user2@email.com |

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
└── VULNERABILITIES.md         # Chi tiết lỗ hổng bảo mật
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

**Lưu ý**: Để học tập, hãy tự thực hành fix các lỗ hổng. Gợi ý:

1. **SQL Injection**: Sử dụng Prepared Statements (PDO)
2. **Password Storage**: Hash với `password_hash()` và `password_verify()`
3. **Brute Force**: Implement rate limiting, CAPTCHA
4. **IDOR**: Kiểm tra quyền sở hữu tài nguyên
5. **XSS**: Escape output với `htmlspecialchars()`

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
