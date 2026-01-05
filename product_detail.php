<?php
/**
 * Trang Chi Tiết Sản Phẩm
 * LỖ HỎNG: SQL Injection qua parameter id
 */
require_once 'config.php';

$product_id = $_GET['id'] ?? 0;

// LỖ HỎNG: SQL Injection
// Payload: 1 OR 1=1
// Payload: 1 UNION SELECT 1,2,3,4,5,6,7,8
$query = "SELECT * FROM products WHERE id = $product_id";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Lỗi SQL: " . mysqli_error($conn) . "<br>Query: " . $query);
}

$product = mysqli_fetch_assoc($result);

if (!$product) {
    die("Sản phẩm không tồn tại!");
}

// Xử lý gửi đánh giá
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        $review_error = "Bạn cần đăng nhập để đánh giá!";
    } else {
        $user_id = getCurrentUserId();
        $rating = $_POST['rating'] ?? 5;
        $comment = $_POST['comment'] ?? '';
        
        // LỖ HỎNG: SQL Injection & XSS (giống các phần khác của shop)
        $insert_query = "INSERT INTO reviews (product_id, user_id, rating, comment) VALUES ($product_id, $user_id, $rating, '$comment')";
        if (mysqli_query($conn, $insert_query)) {
            $review_success = "Cảm ơn bạn đã đánh giá!";
        } else {
            $review_error = "Lỗi: " . mysqli_error($conn);
        }
    }
}

// Lấy danh sách đánh giá
$reviews_query = "SELECT r.*, u.username, u.full_name, u.role FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = $product_id ORDER BY r.created_at DESC";
$reviews_result = mysqli_query($conn, $reviews_query);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Fashion Shop</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="products.php">Sản phẩm</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $product['name']; ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm overflow-hidden rounded-4">
                    <img src="assets/images/<?php echo $product['image'] ?? 'placeholder.jpg'; ?>" 
                         class="img-fluid" alt="<?php echo $product['name']; ?>"
                         style="width: 100%; height: 500px; object-fit: cover;">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="ps-lg-4">
                    <span class="badge bg-dark mb-2"><?php echo strtoupper($product['category']); ?></span>
                    <h1 class="fw-bold display-5 mb-3"><?php echo $product['name']; ?></h1>
                    <p class="h2 text-primary fw-bold mb-4"><?php echo number_format($product['price']); ?>đ</p>
                    
                    <div class="card bg-light border-0 p-3 mb-4">
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <p class="text-muted small mb-1">Tình trạng</p>
                                <p class="fw-bold mb-0 <?php echo $product['stock'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $product['stock'] > 0 ? 'Còn hàng' : 'Hết hàng'; ?>
                                </p>
                            </div>
                            <div class="col-6">
                                <p class="text-muted small mb-1">Kho hàng</p>
                                <p class="fw-bold mb-0"><?php echo $product['stock']; ?> sản phẩm</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Mô tả sản phẩm</h5>
                        <p class="text-muted leading-relaxed">
                            <?php echo $product['description']; ?>
                        </p>
                    </div>
                    
                    <form method="POST" action="cart.php" class="row g-3 align-items-end">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <div class="col-4 col-md-3">
                            <label class="form-label fw-bold small">Số lượng</label>
                            <input type="number" name="quantity" class="form-control form-control-lg text-center" 
                                   value="1" min="1" max="<?php echo $product['stock']; ?>">
                        </div>
                        <div class="col-8 col-md-9">
                            <button type="submit" class="btn btn-dark btn-lg w-100 py-3 rounded-pill shadow-sm">
                                <i class="bi bi-cart-plus me-2"></i>Thêm Vào Giỏ Hàng
                            </button>
                        </div>
                    </form>

                    <div class="mt-5">
                        <div class="alert alert-info border-0 shadow-sm">
                            <h6 class="fw-bold"><i class="bi bi-lightbulb me-2"></i>Hint SQL Injection:</h6>
                            <ul class="small mb-0">
                                <li><code>?id=1 OR 1=1</code> - Hiển thị sản phẩm đầu tiên</li>
                                <li><code>?id=999 UNION SELECT 1,'Test Product',3,4,5,6,7,8</code></li>
                                <li><code>?id=1 AND 1=2 UNION SELECT * FROM products</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phần Đánh Giá Sản Phẩm -->
        <div class="row mt-5">
            <div class="col-md-8 mx-auto">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h3 class="fw-bold mb-4">Đánh giá sản phẩm</h3>
                    
                    <?php if (isset($review_success)): ?>
                        <div class="alert alert-success"><?php echo $review_success; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($review_error)): ?>
                        <div class="alert alert-danger"><?php echo $review_error; ?></div>
                    <?php endif; ?>

                    <!-- Form gửi đánh giá -->
                    <?php if (isLoggedIn()): ?>
                        <form method="POST" class="mb-5">
                            <div class="mb-3">
                                <p class="small text-muted mb-2">Đang đánh giá với tên: <strong class="text-dark"><?php echo getCurrentUsername(); ?></strong></p>
                                <label class="form-label fw-bold">Đánh giá của bạn</label>
                                <div class="rating-input mb-2">
                                    <?php for($i=5; $i>=1; $i--): ?>
                                        <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" <?php echo $i==5 ? 'checked' : ''; ?>>
                                        <label for="star<?php echo $i; ?>"><i class="bi bi-star-fill"></i></label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nhận xét</label>
                                <textarea name="comment" class="form-control" rows="3" placeholder="Chia sẻ cảm nhận của bạn về sản phẩm..."></textarea>
                            </div>
                            <button type="submit" name="submit_review" class="btn btn-dark rounded-pill px-4">Gửi đánh giá</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-light border mb-5">
                            Vui lòng <a href="login.php">đăng nhập</a> để gửi đánh giá của bạn.
                        </div>
                    <?php endif; ?>

                    <!-- Danh sách đánh giá -->
                    <div class="review-list">
                        <?php if (mysqli_num_rows($reviews_result) > 0): ?>
                            <?php while($review = mysqli_fetch_assoc($reviews_result)): ?>
                                <div class="review-item border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="fw-bold text-primary">
                                            <i class="bi bi-person-circle me-1"></i>
                                            <?php 
                                                // LỖ HỔNG: Hiển thị trực tiếp username (User Enumeration)
                                                // CÁCH FIX: $display_name = !empty($review['full_name']) ? $review['full_name'] : substr($review['username'], 0, 2) . '***';
                                                // echo htmlspecialchars($display_name);
                                                echo $review['username']; 
                                            ?>
                                            <?php if ($review['role'] === 'admin'): ?>
                                                <span class="badge bg-danger ms-1">Admin</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-warning">
                                            <?php for($i=1; $i<=5; $i++): ?>
                                                <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p class="mb-1">
                                        <?php 
                                            // LỖ HỔNG: Stored XSS (Không escape output)
                                            // CÁCH FIX: echo htmlspecialchars($review['comment']);
                                            echo $review['comment']; 
                                        ?>
                                    </p>
                                    <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></small>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-4">Chưa có đánh giá nào cho sản phẩm này.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
