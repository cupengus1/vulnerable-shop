<?php
// includes/header.php
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/vulnerable-shop/index.php">
            <span class="fs-3 me-2">🛍️</span>
            <span class="fw-bold">Fashion Shop</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/vulnerable-shop/index.php">Trang Chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/vulnerable-shop/products.php">Sản Phẩm</a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                <a href="/vulnerable-shop/cart.php" class="btn btn-outline-light me-3 position-relative">
                    🛒 Giỏ Hàng
                    <?php 
                    $cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
                    if ($cart_count > 0): 
                    ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo $cart_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
                
                <?php if (isLoggedIn()): ?>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            👤 <?php echo htmlspecialchars(getCurrentUsername()); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="/vulnerable-shop/orders.php">Đơn Hàng của tôi</a></li>
                            <?php if (isAdmin()): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-primary" href="/vulnerable-shop/admin/products_manage.php">Quản Lý Sản Phẩm</a></li>
                                <li><a class="dropdown-item text-danger" href="/vulnerable-shop/dos_test.php">Demo Lỗ Hổng DoS</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/vulnerable-shop/logout.php">Đăng Xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="/vulnerable-shop/login.php" class="btn btn-outline-light me-2">Đăng Nhập</a>
                    <a href="/vulnerable-shop/register.php" class="btn btn-primary">Đăng Ký</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
