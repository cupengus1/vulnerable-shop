<?php
/**
 * Trang chủ
 */
require_once 'config.php';

// Lấy sản phẩm nổi bật
$featured_query = "SELECT * FROM products ORDER BY RAND() LIMIT 8";
$featured_products = mysqli_query($conn, $featured_query);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fashion Shop - Thời Trang Chất Lượng</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/images/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
        }
        .product-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .vuln-card {
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-3 fw-bold mb-4">Chào Mừng Đến Fashion Shop</h1>
            <p class="lead mb-5">Khám phá bộ sưu tập thời trang mới nhất với phong cách hiện đại và trẻ trung.</p>
            <a href="products.php" class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow">Xem Sản Phẩm Ngay</a>
        </div>
    </section>
    
    <!-- Warning Banner -->
    <div class="alert alert-warning border-0 rounded-0 mb-0 text-center py-3">
        <div class="container">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>CẢNH BÁO:</strong> Đây là website học tập chứa các lỗ hỏng bảo mật có chủ đích. 
            <span class="d-none d-md-inline">KHÔNG sử dụng trong môi trường thực tế!</span>
        </div>
    </div>
    
    <!-- Featured Products -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="fw-bold mb-0">Sản Phẩm Nổi Bật</h2>
                    <p class="text-muted">Những mẫu thiết kế được yêu thích nhất tuần này</p>
                </div>
                <a href="products.php" class="btn btn-outline-primary rounded-pill">Xem tất cả</a>
            </div>
            
            <div class="row g-4">
                <?php while ($product = mysqli_fetch_assoc($featured_products)): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card h-100 product-card">
                            <div class="position-relative overflow-hidden">
                                <img src="assets/images/<?php echo $product['image'] ?? 'placeholder.jpg'; ?>" 
                                     class="card-img-top" alt="<?php echo $product['name']; ?>"
                                     style="height: 250px; object-fit: cover;">
                                <div class="position-absolute top-0 end-0 p-2">
                                    <span class="badge bg-primary">New</span>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold text-truncate"><?php echo $product['name']; ?></h5>
                                <p class="card-text text-primary fw-bold fs-5 mb-3">
                                    <?php echo number_format($product['price']); ?>đ
                                </p>
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-dark mt-auto w-100 rounded-pill">Xem Chi Tiết</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    
    <!-- Vulnerabilities Info -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Lỗ Hỏng Bảo Mật Được Tích Hợp</h2>
                <p class="text-muted">Dành cho mục đích nghiên cứu và thực hành Pentest</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 vuln-card shadow-sm p-3">
                        <div class="card-body">
                            <h5 class="fw-bold text-danger mb-3"><i class="bi bi-unlock me-2"></i>SQL Injection</h5>
                            <p class="card-text text-muted small">Trang đăng nhập, đăng ký và tìm kiếm sản phẩm không được bảo vệ.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 vuln-card shadow-sm p-3">
                        <div class="card-body">
                            <h5 class="fw-bold text-danger mb-3"><i class="bi bi-hammer me-2"></i>Brute Force</h5>
                            <p class="card-text text-muted small">Không giới hạn số lần đăng nhập thất bại, cho phép tấn công từ điển.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 vuln-card shadow-sm p-3">
                        <div class="card-body">
                            <h5 class="fw-bold text-danger mb-3"><i class="bi bi-key me-2"></i>Plaintext Password</h5>
                            <p class="card-text text-muted small">Mật khẩu người dùng được lưu trữ trực tiếp không qua mã hóa.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 vuln-card shadow-sm p-3">
                        <div class="card-body">
                            <h5 class="fw-bold text-danger mb-3"><i class="bi bi-target me-2"></i>IDOR</h5>
                            <p class="card-text text-muted small">Có thể truy cập thông tin đơn hàng của người khác qua ID trên URL.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 vuln-card shadow-sm p-3">
                        <div class="card-body">
                            <h5 class="fw-bold text-danger mb-3"><i class="bi bi-exclamation-circle me-2"></i>Validation Flaws</h5>
                            <p class="card-text text-muted small">Thiếu kiểm tra dữ liệu đầu vào cho giá và số lượng sản phẩm.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 vuln-card shadow-sm p-3">
                        <div class="card-body">
                            <h5 class="fw-bold text-danger mb-3"><i class="bi bi-code-slash me-2"></i>XSS Ready</h5>
                            <p class="card-text text-muted small">Dữ liệu người dùng nhập vào được hiển thị trực tiếp lên trình duyệt.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
