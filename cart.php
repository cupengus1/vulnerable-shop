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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <h1 class="fw-bold mb-4">Giỏ Hàng Của Bạn</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
            <div class="text-center py-5 bg-light rounded-4">
                <i class="bi bi-cart-x display-1 text-muted mb-4"></i>
                <h3 class="fw-bold">Giỏ hàng đang trống</h3>
                <p class="text-muted mb-4">Hãy khám phá những sản phẩm tuyệt vời của chúng tôi!</p>
                <a href="products.php" class="btn btn-dark btn-lg rounded-pill px-5">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <form method="POST" action="cart.php">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 py-3">Sản phẩm</th>
                                            <th class="py-3">Giá</th>
                                            <th class="py-3" style="width: 120px;">Số lượng</th>
                                            <th class="py-3">Tổng</th>
                                            <th class="py-3 pe-4"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cart_items as $item): ?>
                                            <tr>
                                                <td class="ps-4 py-3">
                                                    <div class="d-flex align-items-center">
                                                        <img src="assets/images/<?php echo $item['image'] ?? 'placeholder.jpg'; ?>" 
                                                             class="rounded-3 me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                        <div>
                                                            <h6 class="fw-bold mb-0"><?php echo $item['name']; ?></h6>
                                                            <small class="text-muted"><?php echo $item['category']; ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3"><?php echo number_format($item['price']); ?>đ</td>
                                                <td class="py-3">
                                                    <input type="number" name="quantities[<?php echo $item['id']; ?>]" 
                                                           class="form-control form-control-sm text-center" 
                                                           value="<?php echo $item['cart_quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>">
                                                </td>
                                                <td class="py-3 fw-bold"><?php echo number_format($item['subtotal']); ?>đ</td>
                                                <td class="py-3 pe-4 text-end">
                                                    <a href="cart.php?remove=<?php echo $item['id']; ?>" class="text-danger"
                                                       onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-4 bg-light d-flex justify-content-between">
                                <a href="products.php" class="btn btn-outline-dark rounded-pill">
                                    <i class="bi bi-arrow-left me-2"></i>Tiếp tục mua sắm
                                </a>
                                <button type="submit" name="update_cart" class="btn btn-dark rounded-pill">
                                    Cập nhật giỏ hàng
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h5 class="fw-bold mb-4">Tổng đơn hàng</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Tạm tính</span>
                            <span class="fw-bold"><?php echo number_format($total); ?>đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Phí vận chuyển</span>
                            <span class="text-success fw-bold">Miễn phí</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-5">Tổng cộng</span>
                            <span class="fw-bold fs-5 text-primary"><?php echo number_format($total); ?>đ</span>
                        </div>
                        <a href="checkout.php" class="btn btn-primary btn-lg w-100 rounded-pill py-3 shadow-sm">
                            Tiến hành thanh toán
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
