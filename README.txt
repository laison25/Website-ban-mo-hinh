WEBSITE BÁN MÔ HÌNH - BẢN 3 (PHP + MySQL + Upload Ảnh + Đăng ký + Lịch sử đơn hàng)

1. Giải nén folder website-ban-mo-hinh-php-v3 vào thư mục htdocs của XAMPP.
2. Bật Apache và MySQL trong XAMPP.
3. Vào phpMyAdmin, import file: database/website_ban_mo_hinh.sql
4. Mở trình duyệt:
   http://localhost/website-ban-mo-hinh-php-v3/
5. Tài khoản demo:
   - Admin: admin / 123456
   - User: user / 123456

TÍNH NĂNG BẢN 3:
- Kết nối MySQL bằng mysqli
- Đăng nhập thật với password_hash/password_verify
- Đăng ký tài khoản khách hàng mới
- Trang lịch sử đơn hàng của user
- Admin CRUD sản phẩm
- Admin upload ảnh sản phẩm thật từ máy tính
- Trang thanh toán tạo đơn hàng thật vào database
- Giao diện chỉnh lại để sát mẫu Figma hơn ở trang chủ, đăng nhập, chi tiết sản phẩm

LƯU Ý:
- Nếu đổi tên thư mục project, sửa APP_URL trong includes/config.php
- Ảnh upload sẽ lưu trong uploads/products/
- Ảnh demo ban đầu vẫn là SVG placeholder để bạn thay dần
