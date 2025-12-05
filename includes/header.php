<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Header</title>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="/vulnerable-shop/index.php">
                        <h1>🛍️ Fashion Shop</h1>
                    </a>
                </div>
                
                <nav class="main-nav">
                    <a href="/vulnerable-shop/index.php">Trang Chủ</a>
                    <a href="/vulnerable-shop/products.php">Sản Phẩm</a>
                    <a href="/vulnerable-shop/cart.php">
                        🛒 Giỏ Hàng 
                        <?php 
                        $cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
                        if ($cart_count > 0): 
                        ?>
                            <span style="background: #e74c3c; color: white; padding: 0.2rem 0.5rem; border-radius: 10px; font-size: 0.8rem; margin-left: 0.3rem;"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="/vulnerable-shop/orders.php">Đơn Hàng</a>
                        
                        <?php if (isAdmin()): ?>
                            <a href="/vulnerable-shop/admin/products_manage.php">Quản Lý SP</a>
                        <?php endif; ?>
                        
                        <div class="user-menu">
                            <span>Xin chào, <?php echo getCurrentUsername(); ?></span>
                            <a href="/vulnerable-shop/logout.php" class="btn btn-sm btn-secondary">Đăng Xuất</a>
                        </div>
                    <?php else: ?>
                        <a href="/vulnerable-shop/login.php">Đăng Nhập</a>
                        <a href="/vulnerable-shop/register.php">Đăng Ký</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>
</body>
</html>
