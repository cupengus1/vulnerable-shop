-- Database cho Website Bán Quần Áo (Vulnerable Version)
-- CẢNH BÁO: Database này chứa lỗ hỏng bảo mật, chỉ dùng trong môi trường học tập!

-- Tạo database
CREATE DATABASE IF NOT EXISTS shop_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shop_db;

-- Bảng users - LỖ HỎNG: Mật khẩu lưu plaintext
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- LỖ HỎNG: Plaintext password
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng products
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL, -- LỖ HỎNG: Không validate, có thể âm
    stock INT NOT NULL DEFAULT 0, -- LỖ HỎNG: Không validate
    category VARCHAR(50),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng orders
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Bảng order_items
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Bảng reviews
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert dữ liệu mẫu

-- Users (mật khẩu plaintext!)
INSERT INTO users (username, password, email, full_name, phone, address, role) VALUES
('admin', 'admin123', 'admin@shop.com', 'Quản Trị Viên', '0123456789', 'Hà Nội', 'admin'),
('user1', 'password123', 'user1@email.com', 'Nguyễn Văn A', '0987654321', 'TP.HCM', 'user'),
('user2', 'mypass456', 'user2@email.com', 'Trần Thị B', '0912345678', 'Đà Nẵng', 'user');

-- Products
INSERT INTO products (name, description, price, stock, category, image) VALUES
('Áo Thun Nam Basic', 'Áo thun nam chất liệu cotton 100%, thoáng mát', 150000, 50, 'ao-nam', 'ao-thun-nam-1.jpg'),
('Áo Sơ Mi Nam Công Sở', 'Áo sơ mi nam form slim fit, phù hợp đi làm', 250000, 30, 'ao-nam', 'ao-somi-nam-1.jpg'),
('Quần Jeans Nam Slim Fit', 'Quần jeans nam co giãn nhẹ, ôm dáng', 350000, 40, 'quan-nam', 'quan-jeans-nam-1.jpg'),
('Áo Thun Nữ Croptop', 'Áo thun nữ dáng croptop trẻ trung', 120000, 60, 'ao-nu', 'ao-thun-nu-1.jpg'),
('Váy Liền Nữ Công Sở', 'Váy liền thanh lịch cho nữ văn phòng', 280000, 25, 'vay-nu', 'vay-lien-nu-1.jpg'),
('Quần Jean Nữ Ống Rộng', 'Quần jean nữ phong cách Hàn Quốc', 320000, 35, 'quan-nu', 'quan-jean-nu-1.jpg'),
('Áo Khoác Nam Bomber', 'Áo khoác bomber phong cách thể thao', 450000, 20, 'ao-nam', 'ao-khoac-nam-1.jpg'),
('Đầm Maxi Nữ', 'Đầm maxi hoa nhí dịu dàng', 380000, 15, 'dam-nu', 'dam-maxi-nu-1.jpg'),
('Áo Polo Nam', 'Áo polo nam cổ bẻ lịch sự', 180000, 45, 'ao-nam', 'ao-polo-nam-1.jpg'),
('Chân Váy Nữ Xòe', 'Chân váy xòe dễ phối đồ', 220000, 30, 'vay-nu', 'chan-vay-nu-1.jpg');

-- Orders (dữ liệu mẫu)
INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES
(2, 500000, 'delivered', 'Số 123, Quận 1, TP.HCM'),
(2, 350000, 'processing', 'Số 123, Quận 1, TP.HCM'),
(3, 720000, 'pending', 'Số 456, Quận Hải Châu, Đà Nẵng');

-- Order Items
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 2, 150000),
(1, 4, 1, 120000),
(2, 3, 1, 350000),
(3, 5, 1, 280000),
(3, 6, 1, 320000),
(3, 4, 1, 120000);

-- Tạo thêm một số đơn hàng khác
INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES
(2, 900000, 'shipped', 'Số 123, Quận 1, TP.HCM');

INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(4, 7, 2, 450000);

-- Reviews (dữ liệu mẫu)
INSERT INTO reviews (product_id, user_id, rating, comment) VALUES 
(1, 1, 5, 'Sản phẩm chất lượng cao, khuyên dùng! - Quản trị viên'),
(1, 2, 5, 'Áo rất đẹp, vải mát, mặc rất thích!'),
(1, 3, 4, 'Giao hàng nhanh, đóng gói cẩn thận. Áo hơi rộng một chút.'),
(2, 2, 5, 'Sơ mi form đẹp, rất hợp đi làm.'),
(3, 3, 3, 'Quần hơi dài so với mình, nhưng chất lượng ổn.');
