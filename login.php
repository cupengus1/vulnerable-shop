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
    
    // ========== ⚠️ VULN_START: SQL Injection & Brute Force ==========
    // 👉 Để FIX: Thêm /* trước VULN_START và */ sau VULN_END
    
    //LỖ HỎNG 1: SQL Injection - Không sanitize input
    //Payload: admin' OR '1'='1 hoặc admin'#
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    
    // LỖ HỎNG 2: Không có rate limiting - Dễ bị brute force
    // LỖ HỎNG 3: Không có CAPTCHA
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        $error = "Lỗi SQL: " . mysqli_error($conn);
    } elseif (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        
        if ($user['role'] === 'admin') {
            header('Location: admin/index.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
    }
    
    // ========== ⚠️ VULN_END: SQL Injection & Brute Force ==========
    
    
    // ========== 🔒 FIX_START: Prepared Statement + Rate Limiting ==========
    // 👉 Để KÍCH HOẠT: Xóa /* trước FIX_START và */ sau FIX_END
    
  
    // // Rate limiting
    // $max_attempts = 5;
    // $lockout_duration = 900;
    
    // if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
    //     $remaining = ceil(($_SESSION['lockout_time'] - time()) / 60);
    //     $error = "Tài khoản tạm khóa. Thử lại sau $remaining phút.";
    // } else {
    //     // Prepared Statement
    //     $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    //     $stmt->bind_param("s", $username);
    //     $stmt->execute();
    //     $result = $stmt->get_result();
        
    //     if ($result->num_rows > 0) {
    //         $user = $result->fetch_assoc();
    //         if (password_verify($password, $user['password'])) {
    //             unset($_SESSION['failed_attempts']);
    //             unset($_SESSION['lockout_time']);
                
    //             $_SESSION['user_id'] = $user['id'];
    //             $_SESSION['username'] = $user['username'];
    //             $_SESSION['role'] = $user['role'];
    //             $_SESSION['full_name'] = $user['full_name'];
                
    //             header('Location: ' . ($user['role'] === 'admin' ? 'admin/index.php' : 'index.php'));
    //             exit;
    //         }
    //     }
        
    //     // Track failed attempts
    //     $_SESSION['failed_attempts'] = ($_SESSION['failed_attempts'] ?? 0) + 1;
    //     if ($_SESSION['failed_attempts'] >= $max_attempts) {
    //         $_SESSION['lockout_time'] = time() + $lockout_duration;
    //     }
    //     $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
    //     $stmt->close();
    // }

    
    // ========== 🔒 FIX_END: Prepared Statement + Rate Limiting ==========
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Fashion Shop</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .login-image {
            background: url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80');
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
                <div class="card login-card">
                    <div class="row g-0">
                        <div class="col-md-6 d-none d-md-block">
                            <div class="login-image"></div>
                        </div>
                        <div class="col-md-6 p-4 p-lg-5">
                            <div class="text-center mb-4">
                                <h2 class="fw-bold">Chào mừng trở lại!</h2>
                                <p class="text-muted">Đăng nhập để tiếp tục mua sắm</p>
                            </div>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger border-0 shadow-sm mb-4">
                                    <i class="bi bi-exclamation-circle me-2"></i>
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tên đăng nhập</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                                        <input type="text" name="username" class="form-control bg-light border-start-0" 
                                               placeholder="Nhập username" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Mật khẩu</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock"></i></span>
                                        <input type="password" name="password" class="form-control bg-light border-start-0" 
                                               placeholder="Nhập mật khẩu" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-dark w-100 py-3 rounded-pill fw-bold mb-3">
                                    Đăng Nhập
                                </button>
                            </form>
                            
                            <div class="text-center">
                                <p class="mb-0 text-muted">Chưa có tài khoản? <a href="register.php" class="text-primary fw-bold text-decoration-none">Đăng ký ngay</a></p>
                            </div>

                            <div class="mt-5 p-3 bg-light rounded-3 border">
                                <h6 class="fw-bold small mb-2 text-danger"><i class="bi bi-bug me-2"></i>Vulnerability Info:</h6>
                                <p class="small text-muted mb-1"><strong>SQLi:</strong> <code>admin' OR '1'='1</code></p>
                                <p class="small text-muted mb-0"><strong>Brute Force:</strong> No rate limiting</p>
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
