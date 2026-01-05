<?php
/**
 * Trang Danh Sách Sản Phẩm
 * LỖ HỎNG: SQL Injection trong tìm kiếm
 */
require_once 'config.php';

// ========== ⚠️ VULN_START: SQL Injection trong tìm kiếm ==========
// 👉 Để FIX: Thêm /* trước VULN_START và */ sau VULN_END

// LỖ HỎNG: SQL Injection - Không sanitize search input
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$limit = $_GET['limit'] ?? 12; // LỖ HỎNG DOS: Không giới hạn số lượng bản ghi trả về

if ($search) {
    // VULNERABLE: Có thể inject
    $query = "SELECT * FROM products WHERE name LIKE '%$search%' OR description LIKE '%$search%' LIMIT $limit";
} elseif ($category) {
    $query = "SELECT * FROM products WHERE category = '$category' LIMIT $limit";
} else {
    $query = "SELECT * FROM products ORDER BY created_at DESC LIMIT $limit";
}

$result = mysqli_query($conn, $query);

// Hiển verbose error để dễ debug khi học SQL injection
if (!$result) {
    echo "<div class='container'><div class='alert alert-error'>Lỗi SQL: " . mysqli_error($conn) . "</div></div>";
}

// ========== ⚠️ VULN_END: SQL Injection trong tìm kiếm ==========


// ========== 🔒 FIX_START: Prepared Statement cho Search ==========
// 👉 Để KÍCH HOẠT: Xóa /* trước FIX_START và */ sau FIX_END

/*
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

if ($search) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ?");
    $search_param = "%{$search}%";
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($category) {
    $allowed_categories = ['ao-nam', 'quan-nam', 'ao-nu', 'quan-nu', 'vay-nu'];
    if (in_array($category, $allowed_categories)) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE category = ?");
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC");
    }
} else {
    $result = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC");
}

if (!$result) {
    error_log("SQL Error: " . mysqli_error($conn));
    echo "<div class='container'><div class='alert alert-error'>Có lỗi xảy ra.</div></div>";
}
*/  

// ========== 🔒 FIX_END: Prepared Statement cho Search ==========

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản Phẩm - Fashion Shop</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .product-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .category-link {
            text-decoration: none;
            color: #333;
            padding: 8px 15px;
            border-radius: 20px;
            background: #f8f9fa;
            display: inline-block;
            margin-bottom: 10px;
            transition: all 0.2s;
        }
        .category-link:hover, .category-link.active {
            background: #000;
            color: #fff;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="fw-bold mb-3">Bộ Sưu Tập Sản Phẩm</h1>
                <p class="text-muted">Khám phá những xu hướng thời trang mới nhất tại Fashion Shop</p>
                
                <!-- Form tìm kiếm -->
                <form method="GET" class="mt-4">
                    <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden">
                        <input type="text" name="search" class="form-control border-0 px-4" 
                               placeholder="Tìm kiếm sản phẩm..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-dark px-4" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
                
                <div class="mt-3">
                    <div class="alert alert-info d-inline-block py-2 px-3 small border-0 shadow-sm me-2">
                        <i class="bi bi-lightbulb me-2"></i>
                        <strong>Hint SQLi:</strong> <code>%' AND 1=0 UNION SELECT id,username,password,0,0,'user-data',email,phone,created_at FROM users -- -</code>
                    </div>
                    <div class="alert alert-warning d-inline-block py-2 px-3 small border-0 shadow-sm">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Hint DoS:</strong> Thử thêm <code>?limit=1000000</code> vào URL để làm treo server (Resource Exhaustion).
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar Categories -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm p-3">
                    <h5 class="fw-bold mb-3">Danh Mục</h5>
                    <div class="d-flex flex-column">
                        <a href="products.php" class="category-link <?php echo !$category ? 'active' : ''; ?>">Tất cả sản phẩm</a>
                        <a href="products.php?category=ao-nam" class="category-link <?php echo $category == 'ao-nam' ? 'active' : ''; ?>">Áo Nam</a>
                        <a href="products.php?category=quan-nam" class="category-link <?php echo $category == 'quan-nam' ? 'active' : ''; ?>">Quần Nam</a>
                        <a href="products.php?category=ao-nu" class="category-link <?php echo $category == 'ao-nu' ? 'active' : ''; ?>">Áo Nữ</a>
                        <a href="products.php?category=quan-nu" class="category-link <?php echo $category == 'quan-nu' ? 'active' : ''; ?>">Quần Nữ</a>
                        <a href="products.php?category=vay-nu" class="category-link <?php echo $category == 'vay-nu' ? 'active' : ''; ?>">Váy Nữ</a>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="row g-4">
                        <?php while ($product = mysqli_fetch_assoc($result)): ?>
                            <div class="col-md-6 col-xl-4">
                                <div class="card h-100 product-card">
                                    <img src="assets/images/<?php echo $product['image'] ?? 'placeholder.jpg'; ?>" 
                                         class="card-img-top" alt="<?php echo $product['name']; ?>"
                                         style="height: 250px; object-fit: cover;">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title fw-bold text-truncate"><?php echo $product['name']; ?></h5>
                                        <p class="card-text text-muted small mb-2 text-truncate-2">
                                            <?php echo $product['description']; ?>
                                        </p>
                                        <p class="card-text text-primary fw-bold fs-5 mb-3">
                                            <?php echo number_format($product['price']); ?>đ
                                        </p>
                                        <a href="product_detail.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-dark mt-auto w-100 rounded-pill">Xem Chi Tiết</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
                        <h3>Không tìm thấy sản phẩm nào</h3>
                        <p class="text-muted">Thử tìm kiếm với từ khóa khác hoặc quay lại danh sách sản phẩm.</p>
                        <a href="products.php" class="btn btn-primary rounded-pill px-4">Xem tất cả sản phẩm</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
        <!-- Bộ lọc danh mục -->
        <div class="categories">
            <a href="products.php" class="category-link <?php echo !$category ? 'active' : ''; ?>">Tất cả</a>
            <a href="?category=ao-nam" class="category-link <?php echo $category == 'ao-nam' ? 'active' : ''; ?>">Áo Nam</a>
            <a href="?category=quan-nam" class="category-link <?php echo $category == 'quan-nam' ? 'active' : ''; ?>">Quần Nam</a>
            <a href="?category=ao-nu" class="category-link <?php echo $category == 'ao-nu' ? 'active' : ''; ?>">Áo Nữ</a>
            <a href="?category=quan-nu" class="category-link <?php echo $category == 'quan-nu' ? 'active' : ''; ?>">Quần Nữ</a>
            <a href="?category=vay-nu" class="category-link <?php echo $category == 'vay-nu' ? 'active' : ''; ?>">Váy/Đầm</a>
        </div>
        
        <!-- Danh sách sản phẩm -->
        <div class="products-grid">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($product = mysqli_fetch_assoc($result)): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="assets/images/<?php echo $product['image'] ?? 'placeholder.jpg'; ?>" 
                                 alt="<?php echo $product['name']; ?>">
                        </div>
                        <div class="product-info">
                            <h3><?php echo $product['name']; ?></h3>
                            <?php if (!empty($product['description'])): ?>
                                <p style="color: #666; font-size: 0.9rem; margin: 0.5rem 0;"><?php echo $product['description']; ?></p>
                            <?php endif; ?>
                            <p class="product-price"><?php echo number_format($product['price']); ?>đ</p>
                            <p class="product-stock">Còn: <?php echo $product['stock']; ?> sản phẩm</p>
                            <a href="product_detail.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-primary">Xem Chi Tiết</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Không tìm thấy sản phẩm nào.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
