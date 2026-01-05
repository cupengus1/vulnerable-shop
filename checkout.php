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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <h1 class="fw-bold mb-5 text-center">Thanh Toán Đơn Hàng</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <i class="bi bi-exclamation-circle me-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="row g-4">
            <!-- Form thông tin -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                    <h4 class="fw-bold mb-4"><i class="bi bi-geo-alt me-2"></i>Thông Tin Giao Hàng</h4>
                    
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Họ tên</label>
                                <input type="text" class="form-control bg-light border-0 py-2" 
                                       value="<?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?>" readonly>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Email</label>
                                <input type="email" class="form-control bg-light border-0 py-2" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-bold small">Số điện thoại</label>
                                <input type="tel" class="form-control bg-light border-0 py-2" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? 'Chưa cập nhật'); ?>" readonly>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-bold small">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                                <textarea name="shipping_address" class="form-control bg-light border-0 py-2" 
                                          rows="4" placeholder="Nhập địa chỉ nhận hàng chi tiết..." required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="mt-5">
                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill py-3 shadow-sm fw-bold">
                                <i class="bi bi-bag-check me-2"></i>Xác Nhận Đặt Hàng
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Tóm tắt đơn hàng -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h4 class="fw-bold mb-4"><i class="bi bi-receipt me-2"></i>Tóm Tắt Đơn Hàng</h4>
                    
                    <div class="order-items mb-4">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="position-relative">
                                        <img src="assets/images/<?php echo $item['product']['image'] ?? 'placeholder.jpg'; ?>" 
                                             class="rounded-3 me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary">
                                            <?php echo $item['quantity']; ?>
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 small"><?php echo $item['product']['name']; ?></h6>
                                        <small class="text-muted"><?php echo number_format($item['product']['price']); ?>đ</small>
                                    </div>
                                </div>
                                <div class="fw-bold small">
                                    <?php echo number_format($item['subtotal']); ?>đ
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="bg-light rounded-3 p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Tạm tính</span>
                            <span class="small fw-bold"><?php echo number_format($total); ?>đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Phí vận chuyển</span>
                            <span class="text-success small fw-bold">Miễn phí</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Tổng cộng</span>
                            <span class="fw-bold fs-4 text-primary"><?php echo number_format($total); ?>đ</span>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="alert alert-info border-0 small mb-0">
                            <i class="bi bi-shield-check me-2"></i>
                            Thanh toán an toàn và bảo mật.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
