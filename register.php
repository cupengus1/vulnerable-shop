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
    
    // ========== ⚠️ VULN_START: SQL Injection & Plaintext Password ==========
    // 👉 Để FIX: Thêm /* trước VULN_START và */ sau VULN_END
    
    // LỖ HỎNG 1: Không validate input
    // LỖ HỎNG 2: SQL Injection - Không dùng prepared statement
    // LỖ HỎNG 3: Mật khẩu lưu dạng plaintext
    $query = "INSERT INTO users (username, password, email, full_name, role) 
              VALUES ('$username', '$password', '$email', '$full_name', 'user')";
    
    if (mysqli_query($conn, $query)) {
        $success = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
    } else {
        $error = "Lỗi: " . mysqli_error($conn);
    }
    
    // ========== ⚠️ VULN_END: SQL Injection & Plaintext Password ==========
    
    
    // ========== 🔒 FIX_START: Input Validation + Password Hashing ==========
    // 👉 Để KÍCH HOẠT: Xóa /* trước FIX_START và */ sau FIX_END
    
    /*
    $errors = [];
    
    // Validate username
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $errors[] = "Username phải từ 3-20 ký tự, chỉ gồm chữ, số và underscore";
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }
    
    // Validate password
    if (strlen($password) < 8) {
        $errors[] = "Mật khẩu phải có ít nhất 8 ký tự";
    }
    
    // Sanitize full_name
    $full_name = htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8');
    
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Check duplicate
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "Username hoặc email đã được sử dụng!";
        } else {
            // Insert with prepared statement
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, 'user')");
            $stmt->bind_param("ssss", $username, $hashed_password, $email, $full_name);
            
            if ($stmt->execute()) {
                $success = "Đăng ký thành công!";
            } else {
                $error = "Lỗi hệ thống.";
            }
            $stmt->close();
        }
        $check_stmt->close();
    } else {
        $error = implode("<br>", $errors);
    }
    */
    
    // ========== 🔒 FIX_END: Input Validation + Password Hashing ==========
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - Fashion Shop</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .register-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .register-image {
            background: url('https://images.unsplash.com/photo-1483985988355-763728e1935b?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80');
            background-size: cover;
            background-position: center;
            min-height: 100%;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card register-card">
                    <div class="row g-0">
                        <div class="col-md-6 d-none d-md-block">
                            <div class="register-image"></div>
                        </div>
                        <div class="col-md-6 p-4 p-lg-5">
                            <div class="text-center mb-4">
                                <h2 class="fw-bold">Tạo tài khoản mới</h2>
                                <p class="text-muted">Tham gia cùng cộng đồng Fashion Shop</p>
                            </div>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger border-0 shadow-sm mb-4">
                                    <i class="bi bi-exclamation-circle me-2"></i>
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success border-0 shadow-sm mb-4">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <?php echo $success; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Tên đăng nhập</label>
                                        <input type="text" name="username" class="form-control bg-light" 
                                               placeholder="Username" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Mật khẩu</label>
                                        <input type="password" name="password" class="form-control bg-light" 
                                               placeholder="Mật khẩu" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Email</label>
                                        <input type="email" name="email" class="form-control bg-light" 
                                               placeholder="example@gmail.com" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Họ và tên</label>
                                        <input type="text" name="full_name" class="form-control bg-light" 
                                               placeholder="Nguyễn Văn A" required>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-dark w-100 py-3 rounded-pill fw-bold mt-4 mb-3">
                                    Đăng Ký Ngay
                                </button>
                            </form>
                            
                            <div class="text-center">
                                <p class="mb-0 text-muted">Đã có tài khoản? <a href="login.php" class="text-primary fw-bold text-decoration-none">Đăng nhập</a></p>
                            </div>

                            <div class="mt-4 p-3 bg-light rounded-3 border">
                                <h6 class="fw-bold small mb-2 text-danger"><i class="bi bi-bug me-2"></i>Vulnerability Info:</h6>
                                <ul class="small text-muted mb-0 ps-3">
                                    <li>SQL Injection in all fields</li>
                                    <li>Passwords stored in plaintext</li>
                                    <li>No input validation</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
