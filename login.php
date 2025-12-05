<?php
/**
 * Trang Đăng Nhập
 * LỖ HỎNG: SQL Injection, Brute Force, No rate limiting
 */
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // LỖ HỎNG 1: SQL Injection - Không sanitize input
    // Có thể bypass bằng: admin' OR '1'='1
    // Hoặc: admin'-- (comment phần password)
    // Hoặc: admin' # (MySQL comment)
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    
    // LỖ HỎNG 2: Không có rate limiting - Dễ bị brute force
    // LỖ HỎNG 3: Không có CAPTCHA
    // LỖ HỎNG 4: Không log failed attempts
    
    // Debug (chỉ để học tập - KHÔNG BAO GIỜ làm thế này trong thực tế!)
    // echo "<!-- DEBUG Query: $query -->";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        // Hiển thị lỗi SQL để học viên biết injection đã hoạt động
        $error = "Lỗi SQL: " . mysqli_error($conn);
    } elseif (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Đăng nhập thành công
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        
        // Redirect
        if ($user['role'] === 'admin') {
            header('Location: admin/index.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
        // LỖ HỎNG: Không có delay hoặc lockout sau nhiều lần thất bại
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Fashion Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="auth-container">
            <h2>Đăng Nhập</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label>Tên đăng nhập:</label>
                    <input type="text" name="username" required>
                </div>
                
                <div class="form-group">
                    <label>Mật khẩu:</label>
                    <input type="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Đăng Nhập</button>
                
                <p class="auth-link">
                    Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
                </p>
            </form>
            
            <div class="vulnerability-hint">
                <p style="font-size: 12px; color: #666; margin-top: 20px;">
                    💡 <strong>Hint cho học viên:</strong><br>
                    <strong>SQL Injection Payloads:</strong><br>
                    1. Username: <code>admin' OR '1'='1</code> - Password: (bất kỳ)<br>
                    2. Username: <code>admin'#</code> - Password: (bất kỳ)<br>
                    3. Username: <code>' OR 1=1#</code> - Password: (bất kỳ)<br>
                    <br>
                    <strong>Brute Force:</strong> Không có giới hạn số lần thử<br>
                    <strong>Tài khoản hợp lệ:</strong> admin/admin123, user1/password123
                </p>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
