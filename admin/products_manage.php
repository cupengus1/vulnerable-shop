<?php
/**
 * Trang Quản Lý Sản Phẩm (Admin)
 * LỖ HỎNG: Không validate giá, số lượng, mô tả
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
    $price = $_POST['price'] ?? 0; // LỖ HỎNG: Không validate, có thể âm
    $stock = $_POST['stock'] ?? 0; // LỖ HỎNG: Không validate
    $category = $_POST['category'] ?? '';
    $image = $_POST['image'] ?? '';
    
    // LỖ HỎNG: Không validate input
    // - Giá có thể âm
    // - Số lượng có thể âm
    // - Mô tả có thể sai lệch hoàn toàn
    // - Không kiểm tra XSS trong description
    
    if ($id) {
        // Cập nhật
        $query = "UPDATE products SET 
                  name = '$name', 
                  description = '$description', 
                  price = $price, 
                  stock = $stock, 
                  category = '$category', 
                  image = '$image' 
                  WHERE id = $id";
    } else {
        // Thêm mới
        $query = "INSERT INTO products (name, description, price, stock, category, image) 
                  VALUES ('$name', '$description', $price, $stock, '$category', '$image')";
    }
    
    if (mysqli_query($conn, $query)) {
        $message = $id ? 'Cập nhật sản phẩm thành công!' : 'Thêm sản phẩm thành công!';
    } else {
        $error = 'Lỗi: ' . mysqli_error($conn);
    }
}

// Xử lý xóa
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $query = "DELETE FROM products WHERE id = $delete_id";
    mysqli_query($conn, $query);
    $message = 'Xóa sản phẩm thành công!';
}

// Lấy danh sách sản phẩm
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");

// Lấy thông tin sản phẩm cần sửa
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
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1>Quản Lý Sản Phẩm</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="vulnerability-hint">
            <p style="font-size: 12px; color: #666; margin-bottom: 20px;">
                💡 <strong>Hint Lỗ hỏng:</strong><br>
                - Thử nhập giá âm: -1000<br>
                - Thử nhập số lượng âm: -50<br>
                - Thử nhập mô tả sai lệch hoàn toàn so với sản phẩm
            </p>
        </div>
        
        <!-- Form thêm/sửa -->
        <div class="admin-form-section">
            <h2><?php echo $edit_product ? 'Sửa Sản Phẩm' : 'Thêm Sản Phẩm Mới'; ?></h2>
            <form method="POST" class="product-form">
                <input type="hidden" name="id" value="<?php echo $edit_product['id'] ?? ''; ?>">
                
                <div class="form-group">
                    <label>Tên sản phẩm:</label>
                    <input type="text" name="name" required 
                           value="<?php echo $edit_product['name'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Mô tả:</label>
                    <textarea name="description" rows="4"><?php echo $edit_product['description'] ?? ''; ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Giá (đ):</label>
                        <input type="number" name="price" step="0.01" 
                               value="<?php echo $edit_product['price'] ?? '0'; ?>">
                        <small>⚠️ Không validate - có thể nhập giá âm!</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Số lượng:</label>
                        <input type="number" name="stock" 
                               value="<?php echo $edit_product['stock'] ?? '0'; ?>">
                        <small>⚠️ Không validate - có thể nhập số âm!</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Danh mục:</label>
                    <select name="category">
                        <option value="ao-nam" <?php echo ($edit_product['category'] ?? '') == 'ao-nam' ? 'selected' : ''; ?>>Áo Nam</option>
                        <option value="quan-nam" <?php echo ($edit_product['category'] ?? '') == 'quan-nam' ? 'selected' : ''; ?>>Quần Nam</option>
                        <option value="ao-nu" <?php echo ($edit_product['category'] ?? '') == 'ao-nu' ? 'selected' : ''; ?>>Áo Nữ</option>
                        <option value="quan-nu" <?php echo ($edit_product['category'] ?? '') == 'quan-nu' ? 'selected' : ''; ?>>Quần Nữ</option>
                        <option value="vay-nu" <?php echo ($edit_product['category'] ?? '') == 'vay-nu' ? 'selected' : ''; ?>>Váy/Đầm</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Hình ảnh (tên file):</label>
                    <input type="text" name="image" 
                           value="<?php echo $edit_product['image'] ?? 'placeholder.jpg'; ?>">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_product ? 'Cập Nhật' : 'Thêm Mới'; ?>
                    </button>
                    <?php if ($edit_product): ?>
                        <a href="products_manage.php" class="btn btn-secondary">Hủy</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Danh sách sản phẩm -->
        <div class="products-table-section">
            <h2>Danh Sách Sản Phẩm</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Danh mục</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = mysqli_fetch_assoc($products)): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo $product['name']; ?></td>
                            <td>
                                <?php echo number_format($product['price']); ?>đ
                                <?php if ($product['price'] < 0): ?>
                                    <span class="warning-badge">⚠️ GIÁ ÂM!</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $product['stock']; ?>
                                <?php if ($product['stock'] < 0): ?>
                                    <span class="warning-badge">⚠️ SỐ ÂM!</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $product['category']; ?></td>
                            <td>
                                <a href="?edit=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">Sửa</a>
                                <a href="?delete=<?php echo $product['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Xác nhận xóa?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
