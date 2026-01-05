<?php
/**
 * Trang Quản Lý Đơn Hàng
 * LỖ HỎNG: IDOR - Có thể xem đơn hàng của người khác
 */
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = getCurrentUserId();

// ========== ⚠️ VULN_START: Information Disclosure - Xem tất cả đơn hàng ==========
// 👉 Để DEMO: Uncomment đoạn dưới, comment đoạn fix

/*
$query = "SELECT * FROM orders ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
*/

// ========== ⚠️ VULN_END: Information Disclosure ==========


// ========== 🔒 FIX_START: Chỉ hiện đơn hàng của tôi ==========
// 👉 Đoạn này đang được KÍCH HOẠT để list đơn hàng hiển thị đúng user

$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// ========== 🔒 FIX_END: Chỉ hiện đơn hàng của tôi ==========
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn Hàng Của Tôi - Fashion Shop</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold mb-0">Đơn Hàng Của Tôi</h1>
            <div class="alert alert-info border-0 shadow-sm py-2 px-3 mb-0 small">
                <i class="bi bi-lightbulb me-2"></i>
                <strong>Hint IDOR:</strong> Thử thay đổi <code>id=X</code> trên URL trang chi tiết
            </div>
        </div>
        
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">Mã Đơn</th>
                                <th class="py-3">Ngày Đặt</th>
                                <th class="py-3">Tổng Tiền</th>
                                <th class="py-3">Trạng Thái</th>
                                <th class="py-3 pe-4 text-end">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="ps-4 py-3 fw-bold text-primary">#<?php echo $order['id']; ?></td>
                                    <td class="py-3 text-muted small"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td class="py-3 fw-bold"><?php echo number_format($order['total_amount']); ?>đ</td>
                                    <td class="py-3">
                                        <?php 
                                        $status_class = [
                                            'pending' => 'bg-warning text-dark',
                                            'processing' => 'bg-info text-white',
                                            'shipped' => 'bg-primary text-white',
                                            'delivered' => 'bg-success text-white',
                                            'cancelled' => 'bg-danger text-white'
                                        ];
                                        $status_text = [
                                            'pending' => 'Chờ xử lý',
                                            'processing' => 'Đang xử lý',
                                            'shipped' => 'Đang giao',
                                            'delivered' => 'Đã giao',
                                            'cancelled' => 'Đã hủy'
                                        ];
                                        $badge_class = $status_class[$order['status']] ?? 'bg-secondary text-white';
                                        $text = $status_text[$order['status']] ?? $order['status'];
                                        ?>
                                        <span class="badge rounded-pill <?php echo $badge_class; ?> px-3 py-2">
                                            <?php echo $text; ?>
                                        </span>
                                    </td>
                                    <td class="py-3 pe-4 text-end">
                                        <a href="order_detail.php?id=<?php echo $order['id']; ?>" 
                                           class="btn btn-dark btn-sm rounded-pill px-3">
                                            <i class="bi bi-eye me-1"></i>Chi Tiết
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-bag-x display-1 text-muted mb-4"></i>
                    <h3 class="fw-bold">Bạn chưa có đơn hàng nào</h3>
                    <p class="text-muted mb-4">Hãy bắt đầu mua sắm để tạo đơn hàng đầu tiên!</p>
                    <a href="products.php" class="btn btn-primary rounded-pill px-5">Mua sắm ngay</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
