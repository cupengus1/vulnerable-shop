<?php
/**
 * Cấu hình kết nối Database
 * CẢNH BÁO: File này chứa lỗ hỏng bảo mật, chỉ dùng trong môi trường học tập!
 */

// Thông tin kết nối MySQL (XAMPP mặc định)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP mặc định không có password
define('DB_NAME', 'shop_db');

// Kết nối database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Kiểm tra kết nối
if (!$conn) {
    die("Kết nối database thất bại: " . mysqli_connect_error());
}

// Set charset UTF-8
mysqli_set_charset($conn, 'utf8mb4');

// Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Hàm helper cơ bản
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

// LỖ HỎNG: Không có output escaping function
// Nên có: htmlspecialchars(), mysqli_real_escape_string()
// Nhưng cố ý không dùng để tạo lỗ hỏng XSS và SQL Injection
?>
