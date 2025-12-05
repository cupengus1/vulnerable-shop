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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Fashion Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="product-detail">
            <div class="product-detail-image">
                <img src="assets/images/<?php echo $product['image'] ?? 'placeholder.jpg'; ?>" 
                     alt="<?php echo $product['name']; ?>">
            </div>
            
            <div class="product-detail-info">
                <h1><?php echo $product['name']; ?></h1>
                <p class="product-detail-price"><?php echo number_format($product['price']); ?>đ</p>
                
                <div class="product-meta">
                    <p><strong>Tình trạng:</strong> 
                        <?php echo $product['stock'] > 0 ? 'Còn hàng' : 'Hết hàng'; ?>
                    </p>
                    <p><strong>Số lượng còn:</strong> <?php echo $product['stock']; ?></p>
                    <p><strong>Danh mục:</strong> <?php echo $product['category']; ?></p>
                </div>
                
                <div class="product-description">
                    <h3>Mô tả sản phẩm</h3>
                    <p><?php echo $product['description']; ?></p>
                </div>
                
                <div class="product-actions">
                    <form method="POST" action="cart.php">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <div class="quantity-selector">
                            <label>Số lượng:</label>
                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary btn-large">Thêm Vào Giỏ</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="vulnerability-hint">
            <p style="font-size: 12px; color: #666; margin-top: 20px;">
                💡 <strong>Hint SQL Injection:</strong><br>
                1. <code>?id=1 OR 1=1</code> - Hiển thị sản phẩm đầu tiên<br>
                2. <code>?id=999 UNION SELECT 1,'Test Product',3,4,5,6,7,8</code><br>
                3. <code>?id=1 AND 1=2 UNION SELECT * FROM products</code>
            </p>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
