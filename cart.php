<?php
/**
 * Trang Giỏ Hàng
 * Quản lý giỏ hàng với SESSION
 */
session_start();
require_once 'config.php';

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Xử lý thêm sản phẩm vào giỏ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    if ($quantity > 0) {
        // Kiểm tra sản phẩm đã có trong giỏ chưa
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        $_SESSION['cart_message'] = "Đã thêm sản phẩm vào giỏ hàng!";
    }
    
    // Redirect để tránh re-submit
    header("Location: cart.php");
    exit;
}

// Xử lý cập nhật số lượng
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $product_id => $quantity) {
        $quantity = (int)$quantity;
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }
    $_SESSION['cart_message'] = "Cập nhật giỏ hàng thành công!";
    header("Location: cart.php");
    exit;
}

// Xử lý xóa sản phẩm
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$product_id]);
    $_SESSION['cart_message'] = "Đã xóa sản phẩm khỏi giỏ hàng!";
    header("Location: cart.php");
    exit;
}

// Lấy thông tin sản phẩm trong giỏ
$cart_items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', $product_ids);
    
    $query = "SELECT * FROM products WHERE id IN ($ids_string)";
    $result = mysqli_query($conn, $query);
    
    while ($product = mysqli_fetch_assoc($result)) {
        $product['cart_quantity'] = $_SESSION['cart'][$product['id']];
        $product['subtotal'] = $product['price'] * $product['cart_quantity'];
        $total += $product['subtotal'];
        $cart_items[] = $product;
    }
}

$message = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng - Fashion Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Giỏ Hàng</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Giỏ hàng của bạn đang trống.</p>
                <a href="products.php" class="btn btn-primary">Tiếp Tục Mua Sắm</a>
            </div>
        <?php else: ?>
            <form method="POST" action="cart.php">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="cart-item-info">
                                        <img src="assets/images/<?php echo $item['image'] ?? 'placeholder.jpg'; ?>" 
                                             alt="<?php echo $item['name']; ?>" 
                                             style="width: 60px; height: 60px; object-fit: cover;">
                                        <div>
                                            <strong><?php echo $item['name']; ?></strong><br>
                                            <small><?php echo $item['category']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo number_format($item['price']); ?>đ</td>
                                <td>
                                    <input type="number" 
                                           name="quantities[<?php echo $item['id']; ?>]" 
                                           value="<?php echo $item['cart_quantity']; ?>" 
                                           min="1" 
                                           max="<?php echo $item['stock']; ?>"
                                           style="width: 60px;">
                                </td>
                                <td><strong><?php echo number_format($item['subtotal']); ?>đ</strong></td>
                                <td>
                                    <a href="cart.php?remove=<?php echo $item['id']; ?>" 
                                       class="btn btn-danger btn-small"
                                       onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">Xóa</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Tổng cộng:</strong></td>
                            <td colspan="2"><strong style="color: #e74c3c; font-size: 1.2em;"><?php echo number_format($total); ?>đ</strong></td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="cart-actions">
                    <button type="submit" name="update_cart" class="btn btn-secondary">Cập Nhật Giỏ Hàng</button>
                    <a href="products.php" class="btn btn-secondary">Tiếp Tục Mua Sắm</a>
                    <a href="checkout.php" class="btn btn-primary btn-large">Thanh Toán</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
