<?php
/**
 * Trang Thanh Toán (Checkout)
 * Hoàn tất đơn hàng
 */
session_start();
require_once 'config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit;
}

// Kiểm tra giỏ hàng
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Lấy thông tin user
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Tính tổng giỏ hàng
$product_ids = array_keys($_SESSION['cart']);
$ids_string = implode(',', $product_ids);
$query = "SELECT * FROM products WHERE id IN ($ids_string)";
$result = mysqli_query($conn, $query);

$cart_items = [];
$total = 0;

while ($product = mysqli_fetch_assoc($result)) {
    $quantity = $_SESSION['cart'][$product['id']];
    $subtotal = $product['price'] * $quantity;
    $total += $subtotal;
    $cart_items[] = [
        'product' => $product,
        'quantity' => $quantity,
        'subtotal' => $subtotal
    ];
}

// Xử lý đặt hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = mysqli_real_escape_string($conn, $_POST['shipping_address']);
    
    if (empty($shipping_address)) {
        $error = "Vui lòng nhập địa chỉ giao hàng!";
    } else {
        // Bắt đầu transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Tạo đơn hàng
            $order_query = "INSERT INTO orders (user_id, total_amount, status, shipping_address) 
                           VALUES ($user_id, $total, 'pending', '$shipping_address')";
            mysqli_query($conn, $order_query);
            $order_id = mysqli_insert_id($conn);
            
            // Thêm chi tiết đơn hàng
            foreach ($cart_items as $item) {
                $product_id = $item['product']['id'];
                $quantity = $item['quantity'];
                $price = $item['product']['price'];
                
                $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                              VALUES ($order_id, $product_id, $quantity, $price)";
                mysqli_query($conn, $item_query);
                
                // Cập nhật tồn kho (không check vì demo)
                $new_stock = $item['product']['stock'] - $quantity;
                mysqli_query($conn, "UPDATE products SET stock = $new_stock WHERE id = $product_id");
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            // Xóa giỏ hàng
            unset($_SESSION['cart']);
            
            // Redirect đến trang đơn hàng
            header("Location: order_detail.php?id=$order_id");
            exit;
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - Fashion Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Thanh Toán</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="checkout-container" style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 2rem;">
            <!-- Form thông tin -->
            <div class="checkout-form" style="background: white; padding: 2rem; border-radius: 12px;">
                <h2>Thông Tin Giao Hàng</h2>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Họ tên:</label>
                        <input type="text" value="<?php echo $user['full_name'] ?? $user['username']; ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" value="<?php echo $user['email']; ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Số điện thoại:</label>
                        <input type="tel" value="<?php echo $user['phone'] ?? 'Chưa cập nhật'; ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Địa chỉ giao hàng: <span style="color: red;">*</span></label>
                        <textarea name="shipping_address" rows="3" required><?php echo $user['address'] ?? ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-large" style="width: 100%; margin-top: 2rem;">
                        Đặt Hàng (<?php echo number_format($total); ?>đ)
                    </button>
                </form>
            </div>
            
            <!-- Tóm tắt đơn hàng -->
            <div class="order-summary" style="background: white; padding: 2rem; border-radius: 12px; height: fit-content;">
                <h2>Đơn Hàng</h2>
                
                <div style="margin-top: 1.5rem;">
                    <?php foreach ($cart_items as $item): ?>
                        <div style="display: flex; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid #e0e0e0;">
                            <div>
                                <strong><?php echo $item['product']['name']; ?></strong><br>
                                <small>SL: <?php echo $item['quantity']; ?> x <?php echo number_format($item['product']['price']); ?>đ</small>
                            </div>
                            <div>
                                <strong><?php echo number_format($item['subtotal']); ?>đ</strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div style="padding: 1.5rem 0; border-top: 2px solid #667eea; margin-top: 1rem;">
                        <div style="display: flex; justify-content: space-between;">
                            <strong>Tổng cộng:</strong>
                            <strong style="color: #e74c3c; font-size: 1.5rem;"><?php echo number_format($total); ?>đ</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
