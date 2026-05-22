# Website Bán Mô Hình - Lzon Poke

## Thông Tin Nhóm
- **Lại Nam Sơn** - Nhóm trưởng
- **Phương** - Thành viên
- **Vinh** - Thành viên

## Giới Thiệu
Website Bán Mô Hình là dự án website thương mại điện tử dùng để bán các sản phẩm mô hình, figure, resin statue và phụ kiện sưu tầm. Website hỗ trợ khách hàng xem sản phẩm, tìm kiếm, thêm giỏ hàng, lưu yêu thích, đặt hàng, thanh toán demo, tra cứu đơn hàng và quản lý tài khoản.

Dự án được xây dựng bằng PHP thuần, MySQL, HTML, CSS và JavaScript. Project có thể chạy local bằng XAMPP hoặc Docker.

## Chức Năng Đã Có Trong Website

### 1. Trang khách hàng
- Trang chủ hiển thị banner, danh mục, sản phẩm nổi bật và danh sách sản phẩm.
- Xem danh sách sản phẩm từ cơ sở dữ liệu MySQL.
- Xem chi tiết sản phẩm gồm ảnh, tên, danh mục, studio, mô tả, giá, tồn kho, đánh giá và SKU.
- Tìm kiếm sản phẩm theo tên, danh mục hoặc studio.
- Gợi ý tìm kiếm sản phẩm bằng AJAX.
- Lọc sản phẩm theo danh mục.
- Sắp xếp sản phẩm theo nổi bật, mới nhất, tên, giá tăng dần và giá giảm dần.
- Giao diện responsive cho desktop và mobile.

### 2. Tài khoản và xác thực
- Đăng ký tài khoản khách hàng.
- Đăng nhập bằng email/username và mật khẩu.
- Đăng xuất.
- Tự động chuyển hướng sau đăng nhập theo vai trò:
  - Admin vào trang quản trị.
  - Khách hàng vào trang chủ.
- Cài đặt tài khoản: cập nhật họ tên, email và đổi mật khẩu.
- Đăng nhập nhanh bằng Google/Facebook ở chế độ demo.
- Có sẵn luồng OAuth callback cho Google/Facebook khi cấu hình client ID, secret và redirect URI thật.
- Tự tạo tài khoản khách hàng khi đăng nhập Google/Facebook lần đầu bằng email chưa tồn tại.

### 3. Giỏ hàng và yêu thích
- Thêm sản phẩm vào giỏ hàng.
- Hiển thị số lượng sản phẩm trong giỏ trên header.
- Xem giỏ hàng, cập nhật số lượng và xóa sản phẩm khỏi giỏ.
- Tính tổng tiền giỏ hàng.
- Lưu sản phẩm vào danh sách yêu thích.
- Bật/tắt yêu thích bằng AJAX.
- Xem trang danh sách sản phẩm yêu thích.

### 4. Đặt hàng và thanh toán
- Checkout theo nhiều bước: thông tin nhận hàng, kiểm tra giỏ hàng và chọn phương thức thanh toán.
- Hỗ trợ mã giảm giá/coupon trong helper xử lý giỏ hàng.
- Tạo đơn hàng và lưu chi tiết sản phẩm vào database.
- Trang đặt hàng thành công.
- Lịch sử đơn hàng của khách hàng.
- Tra cứu đơn hàng.
- Xem trạng thái đơn hàng bằng tiếng Việt.
- Các phương thức thanh toán đang có:
  - Thanh toán khi nhận hàng (COD).
  - Chuyển khoản ngân hàng.
  - VietQR.
  - Ví điện tử demo.
  - Thẻ ATM/Visa demo.
- Hiển thị mã QR VietQR theo tổng tiền đơn hàng.
- Cho phép xác nhận đã thanh toán trong môi trường demo.

### 5. API Box AI tư vấn sản phẩm
- Website có Box AI tư vấn sản phẩm ở góc phải giao diện.
- API xử lý Box AI nằm tại `ai-chat.php`.
- Box AI có thể gọi Gemini API thông qua file cấu hình `includes/gemini-config.php`.
- Nếu không có API key hoặc API lỗi, hệ thống fallback về tư vấn local dựa trên dữ liệu sản phẩm trong MySQL.
- AI được cấp dữ liệu sản phẩm như tên, danh mục, studio, giá và tồn kho để tư vấn sản phẩm phù hợp.

### 6. Trang quản trị admin
- Dashboard quản trị.
- Thống kê số lượng sản phẩm, đơn hàng, người dùng và doanh thu.
- Biểu đồ doanh thu theo các tháng gần đây.
- Thống kê trạng thái đơn hàng.
- Quản lý sản phẩm.
- Thêm, sửa, xóa sản phẩm.
- Upload ảnh sản phẩm.
- Quản lý đơn hàng và lọc theo trạng thái.
- Xem chi tiết đơn hàng qua trang thanh toán/đơn hàng.
- Xuất danh sách sản phẩm ra CSV.
- Xuất danh sách đơn hàng ra CSV.
- Điều hướng nhanh từ admin về website.

### 7. Giao diện và triển khai
- Header, footer, product card, banner và layout trang chủ đã được thiết kế lại.
- Có logo thương hiệu Lzon Poke.
- Có ảnh giao diện, ảnh sản phẩm mẫu và ảnh upload sản phẩm.
- Tự nhận diện URL local/hosting trong `includes/config.php`.
- Đã xử lý đường dẫn CSS/JS, ảnh và link điều hướng để chạy ổn hơn giữa local và hosting.
- Có cấu hình Docker gồm PHP/Apache, MySQL và phpMyAdmin.
- Có hướng dẫn triển khai Docker trong `DEPLOY_DOCKER.md`.

## Công Nghệ Sử Dụng
- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP thuần
- **Database:** MySQL
- **Local server:** XAMPP
- **Container:** Docker, Docker Compose
- **AI chat:** Gemini API hoặc fallback local
- **OAuth:** Google/Facebook OAuth demo và callback
- **Quản lý mã nguồn:** Git/GitHub

## Cơ Sở Dữ Liệu
Dự án sử dụng MySQL với các bảng chính:
- `users`: lưu tài khoản khách hàng và admin.
- `products`: lưu sản phẩm, danh mục, studio, giá, tồn kho, SKU, ảnh và trạng thái nổi bật.
- `orders`: lưu đơn hàng, thông tin khách hàng, phương thức thanh toán, tổng tiền và trạng thái.
- `order_items`: lưu chi tiết sản phẩm trong từng đơn hàng.

File SQL:
- `database/website_ban_mo_hinh.sql`: file database chính.
- `database/website_ban_mo_hinh_fix.sql`: bản database fix.
- `database/add_10_products.sql`: thêm dữ liệu sản phẩm mẫu.
- `database/add_10_products_fix.sql`: bản fix của dữ liệu 10 sản phẩm.
- `database/add_9_products_100_109.sql`: thêm 9 sản phẩm dùng ảnh `100.jpg` đến `107.jpg` và `109.jpg`.

## Cấu Trúc Dự Án
- `index.php`: trang chủ, danh sách sản phẩm, tìm kiếm và lọc sản phẩm.
- `product-detail.php`: trang chi tiết sản phẩm.
- `login.php`, `register.php`, `logout.php`: đăng nhập, đăng ký và đăng xuất.
- `social-login.php`, `oauth_callback.php`: đăng nhập Google/Facebook và callback OAuth.
- `account-settings.php`: cài đặt tài khoản.
- `cart.php`, `add-to-cart.php`: giỏ hàng và thêm sản phẩm vào giỏ.
- `wishlist.php`, `wishlist-toggle.php`, `wishlist-toggle-ajax.php`: danh sách yêu thích.
- `checkout.php`: nhập thông tin nhận hàng và chọn phương thức thanh toán.
- `payment.php`: trang thanh toán/mô phỏng thanh toán.
- `confirm-payment.php`: xác nhận thanh toán demo.
- `order-success.php`: đặt hàng thành công.
- `order-history.php`: lịch sử đơn hàng.
- `track-order.php`: tra cứu đơn hàng.
- `suggest-search.php`: API gợi ý tìm kiếm AJAX.
- `ai-chat.php`: endpoint chat AI tư vấn sản phẩm.
- `admin/index.php`: dashboard admin.
- `admin/products.php`: danh sách sản phẩm admin.
- `admin/product-form.php`: thêm/sửa sản phẩm.
- `admin/product-delete.php`: xóa sản phẩm.
- `admin/orders.php`: quản lý đơn hàng.
- `admin/export-products.php`: xuất sản phẩm CSV.
- `admin/export-orders.php`: xuất đơn hàng CSV.
- `includes/`: cấu hình, kết nối database, header, footer và hàm dùng chung.
- `assets/`: CSS, JavaScript và hình ảnh giao diện.
- `uploads/`: ảnh sản phẩm upload từ admin.
- `database/`: file SQL tạo database và dữ liệu mẫu.
- `reports/`: báo cáo tiến độ.

## Hướng Dẫn Chạy Bằng XAMPP
1. Copy thư mục dự án vào `htdocs` của XAMPP.
2. Tạo database tên `website_ban_mo_hinh`.
3. Import file `database/website_ban_mo_hinh.sql` vào MySQL/phpMyAdmin.
4. Kiểm tra cấu hình database trong `includes/config.php`.
5. Mở website trên trình duyệt:
   - `http://localhost/website-ban-mo-hinh-php-v3/`
6. Đăng nhập tài khoản demo nếu đã import database:
   - Admin: `admin / 123456`
   - User: `user / 123456`

## Hướng Dẫn Chạy Bằng Docker
Project đã có sẵn:
- `Dockerfile`
- `docker-compose.yml`
- `DEPLOY_DOCKER.md`

Chạy nhanh:
```bash
docker compose up -d
```

Docker Compose gồm các dịch vụ PHP/Apache, MySQL và phpMyAdmin. Xem thêm chi tiết trong `DEPLOY_DOCKER.md`.

## Cấu Hình Cần Lưu Ý
- Cấu hình database, URL website, VietQR và social login nằm trong `includes/config.php`.
- File mẫu cấu hình Gemini nằm tại `includes/gemini-config.example.php`.
- File mẫu cấu hình OpenAI nằm tại `includes/openai-config.example.php`.
- Không public hoặc commit file chứa API key thật như `includes/gemini-config.php` và `includes/openai-config.php`.

## Tài Khoản Demo
- **Admin:** `admin / 123456`
- **User:** `user / 123456`

## Đối Tượng Sử Dụng
- **Khách hàng:** xem sản phẩm, tìm kiếm, yêu thích, thêm giỏ hàng, đặt hàng, thanh toán demo, tra cứu đơn và xem lịch sử đơn hàng.
- **Quản trị viên:** quản lý sản phẩm, ảnh sản phẩm, đơn hàng, thống kê doanh thu và xuất dữ liệu CSV.

## Tài Liệu Liên Quan
- [SRS chức năng Đăng nhập hệ thống](./SRS_dang_nhap_website_ban_mo_hinh.md)
- [Báo cáo tiến độ ngày 03/04/2026](./reports/REPORT_03042026.md)

## Ghi Chú
Dự án đã có các chức năng cốt lõi của một website bán mô hình: sản phẩm, tài khoản, đăng nhập social demo, giỏ hàng, yêu thích, checkout, thanh toán demo, VietQR, lịch sử đơn hàng, tra cứu đơn hàng, chat AI tư vấn, dashboard admin, quản lý sản phẩm, quản lý đơn hàng, xuất CSV và cấu hình chạy bằng Docker.

Một số phần như thanh toán ví điện tử/thẻ và đăng nhập social đang ở mức demo/mô phỏng để phù hợp với môi trường đồ án. Khi triển khai thật cần cấu hình API key, OAuth client, callback URL, thông tin ngân hàng và kiểm tra bảo mật trước khi public.
