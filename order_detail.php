<?php
/**
 * Trang Chi Tiết Đơn Hàng
 * LỖ HỎNG: IDOR - Không kiểm tra quyền sở hữu đơn hàng
 */
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$order_id = $_GET['id'] ?? 0;

// LỖ HỎNG IDOR: Không check user_id, ai cũng xem được đơn của người khác
$query = "SELECT o.*, u.username, u.full_name, u.email 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          WHERE o.id = $order_id";

$result = mysqli_query($conn, $query);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    die("Đơn hàng không tồn tại!");
}

// Lấy chi tiết sản phẩm trong đơn
$items_query = "SELECT oi.*, p.name as product_name, p.image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Đơn Hàng #<?php echo $order['id']; ?> - Fashion Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Chi Tiết Đơn Hàng #<?php echo $order['id']; ?></h1>
        
        <div class="vulnerability-hint">
            <p style="font-size: 12px; color: #666; margin-bottom: 20px;">
                💡 <strong>IDOR Exploited!</strong> Bạn đang xem đơn hàng của: <?php echo $order['full_name']; ?> 
                (User: <?php echo $order['username']; ?>)
            </p>
        </div>
        
        <div class="order-detail">
            <div class="order-info-section">
                <h2>Thông Tin Đơn Hàng</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Mã đơn:</strong> #<?php echo $order['id']; ?>
                    </div>
                    <div class="info-item">
                        <strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                    </div>
                    <div class="info-item">
                        <strong>Khách hàng:</strong> <?php echo $order['full_name']; ?>
                    </div>
                    <div class="info-item">
                        <strong>Email:</strong> <?php echo $order['email']; ?>
                    </div>
                    <div class="info-item">
                        <strong>Trạng thái:</strong> 
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php 
                            $status_text = [
                                'pending' => 'Chờ xử lý',
                                'processing' => 'Đang xử lý',
                                'shipped' => 'Đang giao',
                                'delivered' => 'Đã giao',
                                'cancelled' => 'Đã hủy'
                            ];
                            echo $status_text[$order['status']] ?? $order['status'];
                            ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <strong>Địa chỉ giao hàng:</strong> <?php echo $order['shipping_address']; ?>
                    </div>
                </div>
            </div>
            
            <div class="order-items-section">
                <h2>Sản Phẩm</h2>
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                            <tr>
                                <td><?php echo $item['product_name']; ?></td>
                                <td><?php echo number_format($item['price']); ?>đ</td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo number_format($item['price'] * $item['quantity']); ?>đ</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"><strong>Tổng cộng:</strong></td>
                            <td><strong><?php echo number_format($order['total_amount']); ?>đ</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <div class="order-actions">
            <a href="orders.php" class="btn btn-secondary">Quay Lại</a>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
