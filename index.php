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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Chào Mừng Đến Fashion Shop</h1>
            <p>Khám phá bộ sưu tập thời trang mới nhất</p>
            <a href="products.php" class="btn btn-primary btn-large">Xem Sản Phẩm</a>
        </div>
    </section>
    
    <!-- Warning Banner -->
    <div class="warning-banner">
        <div class="container">
            <p>
                <strong>⚠️ CẢNH BÁO:</strong> Đây là website học tập chứa các lỗ hỏng bảo mật có chủ đích.
                KHÔNG sử dụng trong môi trường thực tế!
            </p>
        </div>
    </div>
    
    <!-- Featured Products -->
    <section class="featured-section">
        <div class="container">
            <h2 class="section-title">Sản Phẩm Nổi Bật</h2>
            
            <div class="products-grid">
                <?php while ($product = mysqli_fetch_assoc($featured_products)): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="assets/images/<?php echo $product['image'] ?? 'placeholder.jpg'; ?>" 
                                 alt="<?php echo $product['name']; ?>">
                        </div>
                        <div class="product-info">
                            <h3><?php echo $product['name']; ?></h3>
                            <p class="product-price"><?php echo number_format($product['price']); ?>đ</p>
                            <a href="product_detail.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-primary">Xem Chi Tiết</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="text-center" style="margin-top: 30px;">
                <a href="products.php" class="btn btn-secondary">Xem Tất Cả Sản Phẩm</a>
            </div>
        </div>
    </section>
    
    <!-- Vulnerabilities Info -->
    <section class="vulnerabilities-info">
        <div class="container">
            <h2 class="section-title">Lỗ Hỏng Bảo Mật Được Tích Hợp</h2>
            <div class="vulnerabilities-grid">
                <div class="vulnerability-card">
                    <h3>🔓 SQL Injection</h3>
                    <p>Trang đăng nhập, đăng ký và tìm kiếm sản phẩm</p>
                </div>
                <div class="vulnerability-card">
                    <h3>🔨 Brute Force</h3>
                    <p>Không giới hạn số lần đăng nhập thất bại</p>
                </div>
                <div class="vulnerability-card">
                    <h3>🔑 Plaintext Password</h3>
                    <p>Mật khẩu lưu không mã hóa trong database</p>
                </div>
                <div class="vulnerability-card">
                    <h3>🎯 IDOR</h3>
                    <p>Truy cập đơn hàng của người khác qua URL</p>
                </div>
                <div class="vulnerability-card">
                    <h3>⚠️ Validation Flaws</h3>
                    <p>Không validate giá, số lượng sản phẩm</p>
                </div>
                <div class="vulnerability-card">
                    <h3>📝 XSS Ready</h3>
                    <p>Không sanitize output HTML</p>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
