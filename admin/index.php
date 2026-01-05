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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stat-card {
            border: none;
            border-radius: 1rem;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .icon-box {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="fw-bold mb-0">Trang Quản Trị</h1>
            <a href="products_manage.php" class="btn btn-dark rounded-pill px-4">
                <i class="bi bi-plus-circle me-2"></i>Quản Lý Sản Phẩm
            </a>
        </div>
        
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card stat-card shadow-sm p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-box-seam fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Sản Phẩm</p>
                            <h3 class="fw-bold mb-0"><?php echo $total_products; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                            <i class="bi bi-cart-check fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Đơn Hàng</p>
                            <h3 class="fw-bold mb-0"><?php echo $total_orders; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-info bg-opacity-10 text-info me-3">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Người Dùng</p>
                            <h3 class="fw-bold mb-0"><?php echo $total_users; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning me-3">
                            <i class="bi bi-currency-dollar fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Doanh Thu</p>
                            <h3 class="fw-bold mb-0"><?php echo number_format($total_revenue); ?>đ</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 ps-4 border-bottom">
                <h5 class="fw-bold mb-0">Đơn Hàng Gần Đây</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light small text-muted">
                        <tr>
                            <th class="ps-4 py-3">Mã Đơn</th>
                            <th class="py-3">Khách Hàng</th>
                            <th class="py-3">Tổng Tiền</th>
                            <th class="py-3">Trạng Thái</th>
                            <th class="py-3 pe-4">Ngày Đặt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
                            <tr>
                                <td class="ps-4 py-3 fw-bold text-primary">#<?php echo $order['id']; ?></td>
                                <td class="py-3"><?php echo htmlspecialchars($order['username']); ?></td>
                                <td class="py-3 fw-bold"><?php echo number_format($order['total_amount']); ?>đ</td>
                                <td class="py-3">
                                    <span class="badge rounded-pill bg-secondary px-3 py-2">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                                <td class="py-3 pe-4 text-muted small"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
