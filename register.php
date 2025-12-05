<?php
/**
 * Trang Đăng Ký
 * LỖ HỎNG: SQL Injection, Password plaintext, No input validation
 */
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    
    // LỖ HỎNG 1: Không validate input
    // LỖ HỎNG 2: SQL Injection - Không dùng prepared statement
    $query = "INSERT INTO users (username, password, email, full_name, role) 
              VALUES ('$username', '$password', '$email', '$full_name', 'user')";
    
    // LỖ HỎNG 3: Mật khẩu lưu dạng plaintext, không hash
    if (mysqli_query($conn, $query)) {
        $success = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
    } else {
        $error = "Lỗi: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - Fashion Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="auth-container">
            <h2>Đăng Ký Tài Khoản</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
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
                
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Họ và tên:</label>
                    <input type="text" name="full_name" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Đăng Ký</button>
                
                <p class="auth-link">
                    Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
                </p>
            </form>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
