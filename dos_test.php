<?php
/**
 * Trang Demo Lỗ Hổng Denial of Service (DoS)
 */
require_once 'config.php';

$type = $_GET['type'] ?? '';

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DoS Demo - Vulnerable Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h1 class="fw-bold mb-4 text-danger"><i class="bi bi-lightning-charge-fill me-2"></i>Denial of Service (DoS) Demo</h1>
                    <p class="text-muted">Trang này mô phỏng các loại lỗ hổng có thể dẫn đến từ chối dịch vụ (DoS) ở cấp độ ứng dụng.</p>

                    <div class="list-group list-group-flush">
                        <!-- 1. Resource Exhaustion via Database -->
                        <div class="list-group-item border-0 px-0 py-4">
                            <h5 class="fw-bold text-dark">1. Cạn kiệt tài nguyên (Resource Exhaustion)</h5>
                            <p class="small text-muted">Xảy ra khi ứng dụng cho phép người dùng yêu cầu một lượng lớn dữ liệu hoặc tài nguyên mà không có giới hạn.</p>
                            <div class="bg-light p-3 rounded-3 mb-3">
                                <code>SELECT * FROM products LIMIT [user_input]</code>
                            </div>
                            <a href="products.php?limit=1000000" class="btn btn-outline-danger btn-sm rounded-pill">
                                Thử nghiệm: Request 1 triệu bản ghi
                            </a>
                        </div>

                        <!-- 2. Algorithmic Complexity (ReDoS) -->
                        <div class="list-group-item border-0 px-0 py-4">
                            <h5 class="fw-bold text-dark">2. Độ phức tạp thuật toán (ReDoS)</h5>
                            <p class="small text-muted">Sử dụng các biểu thức chính quy (Regex) không tối ưu có thể dẫn đến việc CPU bị treo khi xử lý các chuỗi đầu vào đặc biệt.</p>
                            <form action="dos_test.php" method="GET" class="row g-2">
                                <input type="hidden" name="type" value="redos">
                                <div class="col-md-8">
                                    <input type="text" name="pattern" class="form-control form-control-sm" placeholder="Regex pattern (e.g., (a+)+$)" value="(a+)+$">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-danger btn-sm w-100 rounded-pill">Chạy ReDoS</button>
                                </div>
                            </form>
                            <?php
                            if ($type === 'redos' && isset($_GET['pattern'])) {
                                $pattern = $_GET['pattern'];
                                $test_str = "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaab"; // Chuỗi gây backtracking dài hơn
                                echo "<div class='alert alert-warning mt-3 small'>Đang thực thi regex <code>$pattern</code> trên chuỗi dài...</div>";
                                // Cảnh báo: Điều này có thể làm treo PHP process của bạn!
                                preg_match("/$pattern/", $test_str);
                                echo "<div class='alert alert-success mt-2 small'>Hoàn thành! (Nếu bạn thấy dòng này, server vẫn chưa sập)</div>";
                            }
                            ?>
                        </div>

                        <!-- 3. Large Response Body -->
                        <div class="list-group-item border-0 px-0 py-4">
                            <h5 class="fw-bold text-dark">3. Phản hồi cực lớn (Large Response Body)</h5>
                            <p class="small text-muted">Ứng dụng tạo ra một phản hồi HTTP cực lớn, làm cạn kiệt băng thông hoặc bộ nhớ của trình duyệt/server.</p>
                            <a href="dos_test.php?type=large_body" class="btn btn-outline-danger btn-sm rounded-pill">
                                Thử nghiệm: Tạo phản hồi 100MB
                            </a>
                            <?php
                            if ($type === 'large_body') {
                                // Tăng giới hạn bộ nhớ để demo
                                ini_set('memory_limit', '512M');
                                echo str_repeat("VULNERABLE ", 10000000); // Tạo chuỗi cực lớn
                                exit;
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold"><i class="bi bi-shield-check me-2"></i>Cách phòng chống DoS</h5>
                    <ul class="mb-0 small">
                        <li>Luôn giới hạn (Hard limit) số lượng bản ghi trả về từ Database.</li>
                        <li>Sử dụng Rate Limiting để giới hạn số lượng request từ một IP.</li>
                        <li>Kiểm tra và tối ưu hóa các biểu thức Regex (tránh Nested Quantifiers).</li>
                        <li>Giới hạn kích thước dữ liệu đầu vào (POST body size, Upload size).</li>
                        <li>Sử dụng các dịch vụ bảo vệ như Cloudflare để chặn các cuộc tấn công DDoS.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
