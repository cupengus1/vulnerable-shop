# 🛡️ Hướng Dẫn Khắc Phục Lỗ Hổng Bảo Mật

> **Mục đích**: Tài liệu này hướng dẫn chi tiết cách khắc phục từng lỗ hổng bảo mật trong project Vulnerable Shop, giúp bạn hiểu cách bảo mật ứng dụng web đúng cách.

## 📋 Mục Lục

- [1. Fix Lỗ Hổng Đăng Ký - Đăng Nhập](#1-fix-lỗ-hổng-đăng-ký---đăng-nhập)
  - [1.1. Fix Brute Force Attack](#11-fix-brute-force-attack)
  - [1.2. Fix SQL Injection trong Login](#12-fix-sql-injection-trong-login)
  - [1.3. Fix Plaintext Password Storage](#13-fix-plaintext-password-storage)
- [2. Fix SQL Injection trong Tìm Kiếm](#2-fix-sql-injection-trong-tìm-kiếm)
- [3. Fix IDOR trong Quản Lý Đơn Hàng](#3-fix-idor-trong-quản-lý-đơn-hàng)
- [4. Fix Data Validation trong Quản Lý Sản Phẩm](#4-fix-data-validation-trong-quản-lý-sản-phẩm)
- [5. Best Practices Tổng Hợp](#5-best-practices-tổng-hợp)

---

## 1. Fix Lỗ Hổng Đăng Ký - Đăng Nhập

### 1.1. Fix Brute Force Attack

#### 📍 Vị trí: `login.php`

#### ❌ Code Dễ Bị Tấn Công:

```php
<?php
// login.php - VULNERABLE VERSION
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Không có rate limiting!
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);
    
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

#### ✅ Code An Toàn:

**Phương pháp 1: Rate Limiting với Session**

```php
<?php
// login.php - SECURE VERSION with Rate Limiting
session_start();
require_once 'config.php';

// Khởi tạo biến đếm lỗi
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

// Reset sau 15 phút
if (time() - $_SESSION['last_attempt_time'] > 900) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kiểm tra số lần thử
    if ($_SESSION['login_attempts'] >= 5) {
        $wait_time = 900 - (time() - $_SESSION['last_attempt_time']);
        $error = "Too many failed attempts. Please try again in " . ceil($wait_time / 60) . " minutes.";
    } else {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        
        // Sử dụng prepared statement
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password hash
            if (password_verify($_POST['password'], $user['password'])) {
                // Login thành công - Reset counter
                $_SESSION['login_attempts'] = 0;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header('Location: index.php');
                exit();
            } else {
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();
                $error = "Invalid username or password";
            }
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
            $error = "Invalid username or password";
        }
        $stmt->close();
    }
}
?>
```

**Phương pháp 2: Rate Limiting với Database (Tốt hơn)**

Tạo bảng `login_attempts`:

```sql
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(50),
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip (ip_address),
    INDEX idx_time (attempt_time)
);
```

Code PHP:

```php
<?php
// login.php - SECURE VERSION with Database Tracking
session_start();
require_once 'config.php';

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function checkLoginAttempts($conn, $ip) {
    // Đếm số lần thử trong 15 phút qua
    $stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts 
                           WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['attempts'];
}

function logLoginAttempt($conn, $ip, $username) {
    $stmt = $conn->prepare("INSERT INTO login_attempts (ip_address, username) VALUES (?, ?)");
    $stmt->bind_param("ss", $ip, $username);
    $stmt->execute();
    $stmt->close();
}

function clearLoginAttempts($conn, $ip) {
    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ip = getUserIP();
    $attempts = checkLoginAttempts($conn, $ip);
    
    if ($attempts >= 5) {
        $error = "Too many failed login attempts. Please try again after 15 minutes.";
        http_response_code(429); // Too Many Requests
    } else {
        $username = $_POST['username'];
        
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($_POST['password'], $user['password'])) {
                // Success
                clearLoginAttempts($conn, $ip);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header('Location: index.php');
                exit();
            } else {
                logLoginAttempt($conn, $ip, $username);
                $error = "Invalid credentials. Attempts remaining: " . (5 - $attempts - 1);
            }
        } else {
            logLoginAttempt($conn, $ip, $username);
            $error = "Invalid credentials. Attempts remaining: " . (5 - $attempts - 1);
        }
        $stmt->close();
    }
}
?>
```

**Phương pháp 3: Thêm CAPTCHA (Google reCAPTCHA v3)**

1. Đăng ký tại: https://www.google.com/recaptcha/admin
2. Lấy Site Key và Secret Key

```php
<?php
// config.php - Thêm config
define('RECAPTCHA_SITE_KEY', 'your-site-key-here');
define('RECAPTCHA_SECRET_KEY', 'your-secret-key-here');
?>
```

```html
<!-- login.php - Thêm vào form -->
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <form method="POST">
        <input type="text" name="username" required>
        <input type="password" name="password" required>
        
        <!-- reCAPTCHA v2 -->
        <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
        
        <button type="submit">Login</button>
    </form>
</body>
</html>
```

```php
<?php
// login.php - Verify CAPTCHA
function verifyCaptcha($response) {
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array(
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    );
    
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $resultJson = json_decode($result);
    
    return $resultJson->success;
}

// Trong xử lý POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CAPTCHA
    if (!isset($_POST['g-recaptcha-response']) || !verifyCaptcha($_POST['g-recaptcha-response'])) {
        $error = "Please complete the CAPTCHA verification.";
    } else {
        // Tiếp tục xử lý login...
    }
}
?>
```

---

### 1.2. Fix SQL Injection trong Login

#### ❌ Code Dễ Bị Tấn Công:

```php
// VULNERABLE - String concatenation
$username = $_POST['username'];
$password = $_POST['password'];
$sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
$result = mysqli_query($conn, $sql);
```

#### ✅ Code An Toàn (Prepared Statements):

**Phương pháp 1: MySQLi Prepared Statements**

```php
<?php
// SECURE - MySQLi Prepared Statement
$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username); // "s" = string type
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    // Password verification (xem section 1.3)
    if (password_verify($password, $user['password'])) {
        // Login success
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
    }
}
$stmt->close();
?>
```

**Phương pháp 2: PDO Prepared Statements (Recommended)**

```php
<?php
// config.php - Chuyển sang PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

```php
<?php
// login.php - SECURE with PDO
$username = $_POST['username'];

$stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = :username");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

if ($user && password_verify($_POST['password'], $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    header('Location: index.php');
    exit();
}
?>
```

**Input Sanitization (Defense in Depth):**

```php
<?php
// Thêm lớp bảo vệ thứ 2
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

$username = sanitizeInput($_POST['username']);
// Sau đó vẫn dùng prepared statement
?>
```

---

### 1.3. Fix Plaintext Password Storage

#### ❌ Code Dễ Bị Tấn Công:

```php
// register.php - VULNERABLE
$password = $_POST['password'];
$sql = "INSERT INTO users (username, password, email) VALUES ('$username', '$password', '$email')";
mysqli_query($conn, $sql);
```

#### ✅ Code An Toàn:

**Bước 1: Update Database Schema (Optional - tăng độ dài field)**

```sql
-- Tăng độ dài password field để chứa hash
ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL;
```

**Bước 2: Fix Registration (Hash passwords)**

```php
<?php
// register.php - SECURE VERSION
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Hash password với bcrypt (mặc định của PASSWORD_DEFAULT)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Hoặc chỉ định rõ algorithm và cost
        // $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, phone, address) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $hashed_password, $email, $full_name, $phone, $address);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! Please login.";
            header('Location: login.php');
            exit();
        } else {
            $error = "Username or email already exists";
        }
        $stmt->close();
    }
}
?>
```

**Bước 3: Fix Login (Verify hashed passwords)**

```php
<?php
// login.php - Verify password
$stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Verify password hash
    if (password_verify($_POST['password'], $user['password'])) {
        // Password correct
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header('Location: index.php');
        exit();
    } else {
        $error = "Invalid username or password";
    }
} else {
    $error = "Invalid username or password";
}
?>
```

**Bước 4: Migrate Existing Passwords (One-time script)**

```php
<?php
// migrate_passwords.php - Chạy 1 lần để hash passwords hiện có
require_once 'config.php';

// Lấy tất cả users có plaintext password
$result = mysqli_query($conn, "SELECT id, password FROM users");

while ($user = mysqli_fetch_assoc($result)) {
    // Kiểm tra xem password đã hash chưa
    if (strlen($user['password']) < 60) { // Bcrypt hash có length 60
        $hashed = password_hash($user['password'], PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $user['id']);
        $stmt->execute();
        
        echo "Migrated user ID: " . $user['id'] . "<br>";
    }
}

echo "Migration complete!";
// SAU KHI CHẠY XONG, XÓA FILE NÀY ĐI!
?>
```

**Password Strength Requirements:**

```php
<?php
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    if (!preg_match("/[A-Z]/", $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match("/[a-z]/", $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match("/[0-9]/", $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match("/[^A-Za-z0-9]/", $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    return $errors;
}

// Usage in register.php
$password_errors = validatePasswordStrength($_POST['password']);
if (!empty($password_errors)) {
    $error = implode("<br>", $password_errors);
}
?>
```

---

## 2. Fix SQL Injection trong Tìm Kiếm

#### 📍 Vị trí: `products.php`

#### ❌ Code Dễ Bị Tấn Công:

```php
// products.php - VULNERABLE
$search = $_GET['search'];
$sql = "SELECT * FROM products WHERE name LIKE '%$search%' OR description LIKE '%$search%'";
$result = mysqli_query($conn, $sql);
```

#### ✅ Code An Toàn:

**Phương pháp 1: MySQLi Prepared Statements**

```php
<?php
// products.php - SECURE VERSION
require_once 'config.php';

// Get search parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

if (!empty($search)) {
    // Prepared statement với LIKE
    $searchTerm = "%$search%";
    $stmt = $conn->prepare("SELECT * FROM products WHERE (name LIKE ? OR description LIKE ?) AND category LIKE ?");
    
    // Nếu không filter category
    $categoryTerm = !empty($category) ? $category : '%';
    
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $categoryTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif (!empty($category)) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = ?");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // No filter
    $result = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC");
}

// Display products
while ($product = $result->fetch_assoc()) {
    // Escape output để tránh XSS
    echo '<h3>' . htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') . '</h3>';
    echo '<p>' . htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<p>Price: $' . number_format($product['price'], 2) . '</p>';
}

if (isset($stmt)) {
    $stmt->close();
}
?>
```

**Phương pháp 2: PDO (Recommended)**

```php
<?php
// products.php - SECURE with PDO
require_once 'config.php'; // File có PDO connection

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

try {
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $categoryTerm = !empty($category) ? $category : '%';
        
        $stmt = $pdo->prepare("SELECT * FROM products 
                              WHERE (name LIKE :search OR description LIKE :search) 
                              AND category LIKE :category
                              ORDER BY created_at DESC");
        
        $stmt->execute([
            'search' => $searchTerm,
            'category' => $categoryTerm
        ]);
    } else {
        $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    }
    
    $products = $stmt->fetchAll();
    
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "An error occurred while searching products.";
}
?>

<!-- Display products -->
<?php foreach ($products as $product): ?>
    <div class="product-card">
        <h3><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h3>
        <p><?= htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8') ?></p>
        <p class="price">$<?= number_format($product['price'], 2) ?></p>
    </div>
<?php endforeach; ?>
```

**Input Sanitization & Validation:**

```php
<?php
// Thêm validation cho search term
function sanitizeSearchTerm($term) {
    // Remove special SQL characters
    $term = str_replace(['%', '_'], ['\\%', '\\_'], $term);
    // Limit length
    $term = substr($term, 0, 100);
    // Remove non-printable characters
    $term = preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $term);
    return trim($term);
}

$search = sanitizeSearchTerm($_GET['search'] ?? '');
?>
```

---

## 3. Fix IDOR trong Quản Lý Đơn Hàng

#### 📍 Vị trí: `order_detail.php`

#### ❌ Code Dễ Bị Tấn Công:

```php
// order_detail.php - VULNERABLE
$order_id = $_GET['id'];
$sql = "SELECT * FROM orders WHERE id = $order_id";
$result = mysqli_query($conn, $sql);
$order = mysqli_fetch_assoc($result);

// Không kiểm tra quyền sở hữu!
```

#### ✅ Code An Toàn:

**Cách 1: Session-based Authorization**

```php
<?php
// order_detail.php - SECURE VERSION
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// QUAN TRỌNG: Kiểm tra quyền sở hữu
$stmt = $conn->prepare("SELECT o.*, u.full_name, u.email, u.phone 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ? AND o.user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Đơn hàng không tồn tại HOẶC không thuộc về user này
    http_response_code(403); // Forbidden
    die("Access denied! You don't have permission to view this order.");
}

$order = $result->fetch_assoc();

// Get order items
$stmt2 = $conn->prepare("SELECT oi.*, p.name, p.image 
                        FROM order_items oi 
                        JOIN products p ON oi.product_id = p.id 
                        WHERE oi.order_id = ?");
$stmt2->bind_param("i", $order_id);
$stmt2->execute();
$items = $stmt2->get_result();

$stmt->close();
$stmt2->close();
?>

<!-- HTML hiển thị đơn hàng -->
<h2>Order #<?= htmlspecialchars($order['id']) ?></h2>
<p>Status: <?= htmlspecialchars($order['status']) ?></p>
<p>Total: $<?= number_format($order['total_amount'], 2) ?></p>

<h3>Items:</h3>
<?php while ($item = $items->fetch_assoc()): ?>
    <div>
        <p><?= htmlspecialchars($item['name']) ?></p>
        <p>Quantity: <?= $item['quantity'] ?></p>
        <p>Price: $<?= number_format($item['price'], 2) ?></p>
    </div>
<?php endwhile; ?>
```

**Cách 2: PDO với Named Parameters**

```php
<?php
// order_detail.php - SECURE with PDO
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$order_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

try {
    // Authorization check
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :order_id AND user_id = :user_id");
    $stmt->execute([
        'order_id' => $order_id,
        'user_id' => $user_id
    ]);
    
    $order = $stmt->fetch();
    
    if (!$order) {
        http_response_code(403);
        die("Access denied!");
    }
    
    // Get items
    $stmt = $pdo->prepare("SELECT oi.*, p.name, p.image 
                          FROM order_items oi 
                          JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = :order_id");
    $stmt->execute(['order_id' => $order_id]);
    $items = $stmt->fetchAll();
    
} catch(PDOException $e) {
    error_log($e->getMessage());
    die("An error occurred");
}
?>
```

**Admin Override (Cho phép admin xem mọi đơn hàng):**

```php
<?php
// order_detail.php - With Admin Override
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] ?? '') === 'admin';

// Admin có thể xem tất cả đơn hàng
if ($is_admin) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
} else {
    // User thường chỉ xem đơn của mình
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    http_response_code(403);
    die("Access denied!");
}

$order = $result->fetch_assoc();
?>
```

**Logging Unauthorized Access Attempts:**

```php
<?php
// Ghi log khi có người cố truy cập đơn hàng không phải của họ
function logUnauthorizedAccess($user_id, $attempted_order_id) {
    global $conn;
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO security_logs 
                           (user_id, event_type, ip_address, user_agent, details) 
                           VALUES (?, 'unauthorized_order_access', ?, ?, ?)");
    
    $details = "Attempted to access order ID: $attempted_order_id";
    $stmt->bind_param("isss", $user_id, $ip, $user_agent, $details);
    $stmt->execute();
    $stmt->close();
}

// Sử dụng
if ($result->num_rows == 0) {
    logUnauthorizedAccess($_SESSION['user_id'], $order_id);
    http_response_code(403);
    die("Access denied!");
}
?>
```

---

## 4. Fix Data Validation trong Quản Lý Sản Phẩm

#### 📍 Vị trí: `admin/products_manage.php`

#### ❌ Code Dễ Bị Tấn Công:

```php
// admin/products_manage.php - VULNERABLE
$name = $_POST['name'];
$price = $_POST['price'];
$stock = $_POST['stock'];
$description = $_POST['description'];

$sql = "INSERT INTO products (name, description, price, stock, category) 
        VALUES ('$name', '$description', $price, $stock, '$category')";
mysqli_query($conn, $sql);
```

#### ✅ Code An Toàn:

```php
<?php
// admin/products_manage.php - SECURE VERSION
session_start();
require_once 'config.php';

// Check admin authorization
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    die("Access denied! Admin only.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    
    // ==== INPUT VALIDATION ====
    $errors = [];
    
    // 1. Validate Name
    $name = trim($_POST['name']);
    if (empty($name)) {
        $errors[] = "Product name is required";
    } elseif (strlen($name) < 3) {
        $errors[] = "Product name must be at least 3 characters";
    } elseif (strlen($name) > 200) {
        $errors[] = "Product name is too long (max 200 characters)";
    }
    
    // 2. Validate Price
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    if ($price === false) {
        $errors[] = "Invalid price format";
    } elseif ($price < 0) {
        $errors[] = "Price cannot be negative";
    } elseif ($price > 999999999) {
        $errors[] = "Price is too high";
    }
    
    // 3. Validate Stock
    $stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
    if ($stock === false) {
        $errors[] = "Invalid stock quantity";
    } elseif ($stock < 0) {
        $errors[] = "Stock cannot be negative";
    } elseif ($stock > 1000000) {
        $errors[] = "Stock quantity is unrealistic";
    }
    
    // 4. Validate Category
    $allowed_categories = ['ao-nam', 'ao-nu', 'quan-nam', 'quan-nu', 'vay-nu', 'dam-nu'];
    $category = $_POST['category'];
    if (!in_array($category, $allowed_categories)) {
        $errors[] = "Invalid category";
    }
    
    // 5. Sanitize Description (Prevent XSS)
    $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
    if (strlen($description) > 5000) {
        $errors[] = "Description is too long (max 5000 characters)";
    }
    
    // 6. Validate Image Upload
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = "Invalid image type. Only JPG, PNG, WEBP allowed.";
        } elseif ($_FILES['image']['size'] > $max_size) {
            $errors[] = "Image too large. Max 5MB.";
        } else {
            // Generate safe filename
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid('product_') . '.' . $ext;
            $upload_path = '../assets/images/' . $image_name;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $errors[] = "Failed to upload image";
            }
        }
    }
    
    // ==== IF NO ERRORS, INSERT ====
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO products 
                                   (name, description, price, stock, category, image) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("ssdiss", 
                $name, 
                $description, 
                $price, 
                $stock, 
                $category, 
                $image_name
            );
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Product added successfully!";
                header('Location: products_manage.php');
                exit();
            } else {
                $errors[] = "Database error: " . $stmt->error;
            }
            
            $stmt->close();
            
        } catch(Exception $e) {
            error_log($e->getMessage());
            $errors[] = "An error occurred. Please try again.";
        }
    }
    
    // Display errors
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo '<div class="error">' . htmlspecialchars($error) . '</div>';
        }
    }
}
?>
```

**Helper Functions cho Validation:**

```php
<?php
// includes/validation.php

class ProductValidator {
    
    public static function validateName($name) {
        $name = trim($name);
        if (empty($name)) {
            return "Product name is required";
        }
        if (strlen($name) < 3 || strlen($name) > 200) {
            return "Product name must be between 3 and 200 characters";
        }
        if (preg_match('/<script|<iframe|javascript:/i', $name)) {
            return "Product name contains invalid characters";
        }
        return null; // No error
    }
    
    public static function validatePrice($price) {
        if (!is_numeric($price)) {
            return "Price must be a number";
        }
        $price = (float)$price;
        if ($price < 0) {
            return "Price cannot be negative";
        }
        if ($price > 999999999) {
            return "Price exceeds maximum allowed value";
        }
        if ($price == 0) {
            return "Price cannot be zero. Use 'Free' in description if needed.";
        }
        return null;
    }
    
    public static function validateStock($stock) {
        if (!is_numeric($stock) || $stock != (int)$stock) {
            return "Stock must be a whole number";
        }
        $stock = (int)$stock;
        if ($stock < 0) {
            return "Stock cannot be negative";
        }
        if ($stock > 1000000) {
            return "Stock quantity unrealistic";
        }
        return null;
    }
    
    public static function sanitizeDescription($description) {
        // Remove all HTML tags except safe ones
        $allowed_tags = '<p><br><b><i><u><strong><em>';
        $description = strip_tags($description, $allowed_tags);
        
        // Encode special characters
        $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
        
        return $description;
    }
}

// Usage
require_once 'includes/validation.php';

$errors = [];

if ($error = ProductValidator::validateName($_POST['name'])) {
    $errors[] = $error;
}
if ($error = ProductValidator::validatePrice($_POST['price'])) {
    $errors[] = $error;
}
if ($error = ProductValidator::validateStock($_POST['stock'])) {
    $errors[] = $error;
}

$description = ProductValidator::sanitizeDescription($_POST['description']);
?>
```

**Server-side Image Validation:**

```php
<?php
// includes/image_upload.php

class ImageUploader {
    
    private $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    private $max_size = 5242880; // 5MB
    private $upload_dir = '../assets/images/';
    
    public function validate($file) {
        $errors = [];
        
        // Check if file was uploaded
        if (!isset($file) || $file['error'] !== 0) {
            if ($file['error'] === 1) {
                $errors[] = "File too large";
            } else {
                $errors[] = "File upload error";
            }
            return $errors;
        }
        
        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, $this->allowed_types)) {
            $errors[] = "Invalid file type: $mime";
        }
        
        // Validate size
        if ($file['size'] > $this->max_size) {
            $errors[] = "File too large. Max 5MB.";
        }
        
        // Validate image dimensions (optional)
        $image_info = getimagesize($file['tmp_name']);
        if ($image_info === false) {
            $errors[] = "File is not a valid image";
        } else {
            list($width, $height) = $image_info;
            if ($width > 5000 || $height > 5000) {
                $errors[] = "Image dimensions too large";
            }
        }
        
        return $errors;
    }
    
    public function upload($file) {
        $errors = $this->validate($file);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Generate safe filename
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('product_', true) . '.' . $ext;
        $filepath = $this->upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => true, 'filename' => $filename];
        } else {
            return ['success' => false, 'errors' => ['Upload failed']];
        }
    }
}

// Usage
$uploader = new ImageUploader();
$result = $uploader->upload($_FILES['image']);

if ($result['success']) {
    $image_name = $result['filename'];
} else {
    $errors = array_merge($errors, $result['errors']);
}
?>
```

---

## 5. Best Practices Tổng Hợp

### 5.1. Defense in Depth (Bảo mật nhiều lớp)

```php
<?php
// Áp dụng nhiều lớp bảo vệ cùng lúc

// Layer 1: Input Validation
$input = trim($_POST['input']);
if (!preg_match('/^[a-zA-Z0-9\s]+$/', $input)) {
    die("Invalid input");
}

// Layer 2: Sanitization
$input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

// Layer 3: Prepared Statement
$stmt = $pdo->prepare("SELECT * FROM table WHERE column = ?");
$stmt->execute([$input]);

// Layer 4: Output Encoding
echo htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
?>
```

### 5.2. Secure Session Management

```php
<?php
// config.php - Session security

// Regenerate session ID sau khi login
session_start();

if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Session timeout (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit();
}
$_SESSION['last_activity'] = time();

// Bind session to IP (optional, có thể gây vấn đề với mobile users)
if (isset($_SESSION['ip']) && $_SESSION['ip'] !== $_SERVER['REMOTE_ADDR']) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}
$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];

// Secure cookie settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Only if using HTTPS
ini_set('session.cookie_samesite', 'Strict');
?>
```

### 5.3. Error Handling & Logging

```php
<?php
// config.php - Error handling

// Tắt hiển thị lỗi ra ngoài (production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $message = "Error [$errno]: $errstr in $errfile on line $errline";
    error_log($message);
    
    // Hiển thị message generic cho user
    echo "An error occurred. Please try again later.";
    exit();
}

set_error_handler("customErrorHandler");

// Exception handler
function customExceptionHandler($exception) {
    error_log("Exception: " . $exception->getMessage());
    echo "An unexpected error occurred.";
    exit();
}

set_exception_handler("customExceptionHandler");
?>
```

### 5.4. HTTPS & Security Headers

```php
<?php
// includes/security_headers.php

// Force HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// Security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://www.google.com; style-src 'self' 'unsafe-inline';");

// HSTS (HTTP Strict Transport Security)
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
?>
```

### 5.5. Input Validation Whitelist

```php
<?php
// Whitelist approach (an toàn hơn blacklist)

function validateCategory($category) {
    $allowed = ['ao-nam', 'ao-nu', 'quan-nam', 'quan-nu', 'vay-nu', 'dam-nu'];
    return in_array($category, $allowed) ? $category : null;
}

function validateOrderStatus($status) {
    $allowed = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    return in_array($status, $allowed) ? $status : 'pending';
}

function validateSortOrder($sort) {
    $allowed = ['price_asc', 'price_desc', 'name_asc', 'date_desc'];
    return in_array($sort, $allowed) ? $sort : 'date_desc';
}
?>
```

### 5.6. CSRF Protection

```php
<?php
// includes/csrf.php

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Usage in forms
?>
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
    <!-- other fields -->
</form>

<?php
// Verify in POST handler
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed");
    }
    // Process form...
}
?>
```

### 5.7. Database Configuration Security

```php
<?php
// config.php - SECURE VERSION

// Không hard-code credentials
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'shop_db');

// PDO with proper error handling
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection error. Please contact administrator.");
}

// Tạo file .env (không commit vào git!)
// DB_HOST=localhost
// DB_USER=shop_user
// DB_PASS=secure_password_here
// DB_NAME=shop_db
?>
```

---

## 📝 Testing Checklist

Sau khi fix các lỗ hổng, kiểm tra lại:

### ✅ Authentication Testing
- [ ] Thử brute force → Phải bị block sau 5 lần
- [ ] Thử SQL injection payloads → Phải không work
- [ ] Kiểm tra password đã hash trong database
- [ ] CAPTCHA hoạt động đúng
- [ ] Session timeout sau 30 phút inactive

### ✅ SQL Injection Testing
- [ ] Thử payload: `' OR '1'='1` → Không work
- [ ] Thử UNION injection → Không work
- [ ] Dùng SQLMap scan → Không tìm thấy lỗ hổng
- [ ] Kiểm tra tất cả input fields

### ✅ Authorization Testing
- [ ] User A không xem được đơn hàng của User B
- [ ] Non-admin không vào được admin panel
- [ ] Thử truy cập URL trực tiếp → 403 Forbidden

### ✅ Input Validation Testing
- [ ] Thử nhập giá âm → Bị reject
- [ ] Thử nhập stock âm → Bị reject
- [ ] Thử XSS payload trong description → Bị escape
- [ ] Upload file PHP thay vì image → Bị reject

---

## 🎓 Kết Luận

Việc khắc phục lỗ hổng bảo mật yêu cầu:

1. **Hiểu rõ nguyên nhân** gốc rễ của lỗ hổng
2. **Áp dụng nhiều lớp bảo vệ** (Defense in Depth)
3. **Validate & Sanitize** mọi input
4. **Sử dụng Prepared Statements** cho database queries
5. **Kiểm tra authorization** cho mọi tài nguyên
6. **Hash passwords** với bcrypt/Argon2
7. **Test kỹ lưỡng** sau khi fix

### 📚 Tài Liệu Tham Khảo

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [PDO Prepared Statements](https://www.php.net/manual/en/pdo.prepared-statements.php)
- [Password Hashing](https://www.php.net/manual/en/function.password-hash.php)

---

**Happy Secure Coding! 🔒**

*Last updated: 2025-12-07*
