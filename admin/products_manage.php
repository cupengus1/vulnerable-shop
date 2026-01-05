<?php
/**
 * Trang Quản Lý Sản Phẩm (Admin)
 * LỖ HỎNG: Không validate giá, số lượng, mô tả, SQL Injection
 */
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$message = '';
$error = '';

// Xử lý thêm/sửa sản phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $category = $_POST['category'] ?? '';
    $image = $_POST['image'] ?? '';
    
    // ========== ⚠️ VULN_START: SQL Injection & No Validation ==========
    // 👉 Để FIX: Thêm /* trước VULN_START và */ sau VULN_END
    
    // LỖ HỎNG: Giá có thể âm, số lượng có thể âm, SQL Injection
    if ($id) {
        $query = "UPDATE products SET 
                  name = '$name', 
                  description = '$description', 
                  price = $price, 
                  stock = $stock, 
                  category = '$category', 
                  image = '$image' 
                  WHERE id = $id";
    } else {
        $query = "INSERT INTO products (name, description, price, stock, category, image) 
                  VALUES ('$name', '$description', $price, $stock, '$category', '$image')";
    }
    
    if (mysqli_query($conn, $query)) {
        $message = $id ? 'Cập nhật thành công!' : 'Thêm mới thành công!';
    } else {
        $error = 'Lỗi: ' . mysqli_error($conn);
    }
    
    // ========== ⚠️ VULN_END: SQL Injection & No Validation ==========
    
    
    // ========== 🔒 FIX_START: Validation + Prepared Statement ==========
    // 👉 Để KÍCH HOẠT: Xóa /* trước FIX_START và */ sau FIX_END
    
   
    // $errors = [];
    
    // // Validate
    // $name = htmlspecialchars(trim($name), ENT_QUOTES, 'UTF-8');
    // $description = htmlspecialchars(trim($description), ENT_QUOTES, 'UTF-8');
    
    // $price = filter_var($price, FILTER_VALIDATE_FLOAT);
    // if ($price === false || $price < 0) {
    //     $errors[] = "Giá phải là số dương";
    // }
    
    // $stock = filter_var($stock, FILTER_VALIDATE_INT);
    // if ($stock === false || $stock < 0) {
    //     $errors[] = "Số lượng phải là số nguyên không âm";
    // }
    
    // $allowed_categories = ['ao-nam', 'quan-nam', 'ao-nu', 'quan-nu', 'vay-nu'];
    // if (!in_array($category, $allowed_categories)) {
    //     $errors[] = "Danh mục không hợp lệ";
    // }
    
    // if (empty($errors)) {
    //     if (!empty($id)) {
    //         $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category=?, image=? WHERE id=?");
    //         $stmt->bind_param("ssdissi", $name, $description, $price, $stock, $category, $image, $id);
    //     } else {
    //         $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category, image) VALUES (?, ?, ?, ?, ?, ?)");
    //         $stmt->bind_param("ssdiss", $name, $description, $price, $stock, $category, $image);
    //     }
        
    //     if ($stmt->execute()) {
    //         $message = !empty($id) ? 'Cập nhật thành công!' : 'Thêm mới thành công!';
    //     } else {
    //         $error = 'Lỗi hệ thống.';
    //     }
    //     $stmt->close();
    // } else {
    //     $error = implode("<br>", $errors);
    // }
   
    
    // ========== 🔒 FIX_END: Validation + Prepared Statement ==========
}

// Xử lý xóa
if (isset($_GET['delete'])) {
    
    // ========== ⚠️ VULN_START: SQL Injection trong xóa ==========
    // 👉 Để FIX: Thêm /* trước VULN_START và */ sau VULN_END
    
    $delete_id = $_GET['delete'];
    $query = "DELETE FROM products WHERE id = $delete_id";
    mysqli_query($conn, $query);
    $message = 'Xóa thành công!';
    
    // ========== ⚠️ VULN_END: SQL Injection trong xóa ==========
    
    
    // ========== 🔒 FIX_START: Prepared Statement cho xóa ==========
    // 👉 Để KÍCH HOẠT: Xóa /* trước FIX_START và */ sau FIX_END
    
   
    // $delete_id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
    // if ($delete_id && $delete_id > 0) {
    //     $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    //     $stmt->bind_param("i", $delete_id);
    //     if ($stmt->execute()) {
    //         $message = 'Xóa thành công!';
    //     }
    //     $stmt->close();
    // }
   
    
    // ========== 🔒 FIX_END: Prepared Statement cho xóa ==========
}

// Lấy danh sách sản phẩm
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");

// Lấy sản phẩm cần sửa
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_result = mysqli_query($conn, "SELECT * FROM products WHERE id = $edit_id");
    $edit_product = mysqli_fetch_assoc($edit_result);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm - Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
    <?php include '../includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold mb-0">Quản Lý Sản Phẩm</h1>
            <a href="index.php" class="btn btn-outline-dark rounded-pill">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="alert alert-info border-0 shadow-sm mb-4 small">
            <i class="bi bi-lightbulb me-2"></i>
            <strong>Hint Lỗ hỏng:</strong> Thử nhập giá âm (<code>-1000</code>) hoặc số lượng âm (<code>-50</code>) để kiểm tra validation.
        </div>
        
        <div class="row g-4">
            <!-- Form thêm/sửa -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px; z-index: 100;">
                    <h4 class="fw-bold mb-4"><?php echo $edit_product ? 'Sửa Sản Phẩm' : 'Thêm Sản Phẩm Mới'; ?></h4>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?php echo $edit_product['id'] ?? ''; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Tên sản phẩm</label>
                            <input type="text" name="name" class="form-control bg-light border-0" required 
                                   value="<?php echo htmlspecialchars($edit_product['name'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Mô tả</label>
                            <textarea name="description" class="form-control bg-light border-0" rows="3"><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small">Giá (đ)</label>
                                <input type="number" name="price" class="form-control bg-light border-0" step="0.01" 
                                       value="<?php echo $edit_product['price'] ?? '0'; ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small">Số lượng</label>
                                <input type="number" name="stock" class="form-control bg-light border-0" 
                                       value="<?php echo $edit_product['stock'] ?? '0'; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Danh mục</label>
                            <select name="category" class="form-select bg-light border-0">
                                <option value="ao-nam" <?php echo ($edit_product['category'] ?? '') == 'ao-nam' ? 'selected' : ''; ?>>Áo Nam</option>
                                <option value="quan-nam" <?php echo ($edit_product['category'] ?? '') == 'quan-nam' ? 'selected' : ''; ?>>Quần Nam</option>
                                <option value="ao-nu" <?php echo ($edit_product['category'] ?? '') == 'ao-nu' ? 'selected' : ''; ?>>Áo Nữ</option>
                                <option value="quan-nu" <?php echo ($edit_product['category'] ?? '') == 'quan-nu' ? 'selected' : ''; ?>>Quần Nữ</option>
                                <option value="vay-nu" <?php echo ($edit_product['category'] ?? '') == 'vay-nu' ? 'selected' : ''; ?>>Váy/Đầm</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold small">Hình ảnh (tên file)</label>
                            <input type="text" name="image" class="form-control bg-light border-0" 
                                   value="<?php echo htmlspecialchars($edit_product['image'] ?? 'placeholder.jpg'); ?>">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-dark rounded-pill py-2 fw-bold">
                                <?php echo $edit_product ? 'Cập Nhật Sản Phẩm' : 'Thêm Sản Phẩm'; ?>
                            </button>
                            <?php if ($edit_product): ?>
                                <a href="products_manage.php" class="btn btn-outline-secondary rounded-pill py-2">Hủy</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Danh sách sản phẩm -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 ps-4 border-bottom">
                        <h5 class="fw-bold mb-0">Danh Sách Sản Phẩm</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small text-muted">
                                <tr>
                                    <th class="ps-4 py-3">ID</th>
                                    <th class="py-3">Sản phẩm</th>
                                    <th class="py-3">Giá</th>
                                    <th class="py-3">Kho</th>
                                    <th class="py-3 pe-4 text-end">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($product = mysqli_fetch_assoc($products)): ?>
                                    <tr>
                                        <td class="ps-4 py-3 text-muted small">#<?php echo $product['id']; ?></td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                <img src="../assets/images/<?php echo $product['image'] ?? 'placeholder.jpg'; ?>" 
                                                     class="rounded-3 me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                <div>
                                                    <h6 class="fw-bold mb-0 small"><?php echo htmlspecialchars($product['name']); ?></h6>
                                                    <small class="text-muted"><?php echo $product['category']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <span class="fw-bold small <?php echo $product['price'] < 0 ? 'text-danger' : ''; ?>">
                                                <?php echo number_format($product['price']); ?>đ
                                            </span>
                                            <?php if ($product['price'] < 0): ?>
                                                <span class="badge bg-danger ms-1 small" style="font-size: 0.6rem;">GIÁ ÂM!</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3">
                                            <span class="small <?php echo $product['stock'] < 0 ? 'text-danger fw-bold' : ''; ?>">
                                                <?php echo $product['stock']; ?>
                                            </span>
                                            <?php if ($product['stock'] < 0): ?>
                                                <span class="badge bg-danger ms-1 small" style="font-size: 0.6rem;">SỐ ÂM!</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 pe-4 text-end">
                                            <div class="btn-group">
                                                <a href="?edit=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary rounded-start-pill px-3">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?delete=<?php echo $product['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger rounded-end-pill px-3"
                                                   onclick="return confirm('Xác nhận xóa sản phẩm này?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
