<?php
/**
 * Admin Dashboard
 */
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Thống kê
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE status = 'delivered'"))['total'] ?? 0;

// Đơn hàng gần đây
$recent_orders = mysqli_query($conn, "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Fashion Shop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1>Trang Quản Trị</h1>
        
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <h3>Sản Phẩm</h3>
                <p class="stat-number"><?php echo $total_products; ?></p>
            </div>
            <div class="stat-card stat-success">
                <h3>Đơn Hàng</h3>
                <p class="stat-number"><?php echo $total_orders; ?></p>
            </div>
            <div class="stat-card stat-info">
                <h3>Người Dùng</h3>
                <p class="stat-number"><?php echo $total_users; ?></p>
            </div>
            <div class="stat-card stat-warning">
                <h3>Doanh Thu</h3>
                <p class="stat-number"><?php echo number_format($total_revenue); ?>đ</p>
            </div>
        </div>
        
        <div class="admin-section">
            <h2>Đơn Hàng Gần Đây</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã Đơn</th>
                        <th>Khách Hàng</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Ngày Đặt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo $order['username']; ?></td>
                            <td><?php echo number_format($order['total_amount']); ?>đ</td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div class="admin-actions">
            <a href="products_manage.php" class="btn btn-primary">Quản Lý Sản Phẩm</a>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
