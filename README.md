# Website Bán Mô Hình

## Thông Tin Nhóm
- **Lại Nam Sơn** - Nhóm trưởng
- **Nguyễn Văn Phương** - Thành viên
- **Nguyễn Thành Vinh** - Thành viên

## Giới Thiệu
Website Bán Mô Hình là dự án xây dựng hệ thống bán hàng trực tuyến cho các sản phẩm mô hình, figure, resin statue và các bộ sưu tập liên quan. Website hỗ trợ khách hàng xem sản phẩm, tìm kiếm, thêm vào giỏ hàng, lưu sản phẩm yêu thích, đặt hàng và theo dõi lịch sử đơn hàng.

Dự án được xây dựng bằng PHP thuần kết hợp MySQL, giao diện HTML/CSS/JavaScript và chạy thử trên môi trường XAMPP.

## Mục Tiêu Dự Án
- Xây dựng website bán mô hình có giao diện rõ ràng, dễ sử dụng.
- Hiển thị danh sách sản phẩm từ cơ sở dữ liệu MySQL.
- Cho phép người dùng xem chi tiết sản phẩm, tìm kiếm và lọc theo danh mục.
- Xây dựng chức năng đăng ký, đăng nhập, đăng xuất và cài đặt tài khoản.
- Hỗ trợ giỏ hàng, danh sách yêu thích và đặt hàng trực tuyến.
- Tích hợp nhiều phương thức thanh toán theo dạng mô phỏng website thật.
- Cung cấp trang quản trị để admin quản lý sản phẩm, đơn hàng và theo dõi dữ liệu hệ thống.

## Chức Năng Đã Thực Hiện

### 1. Chức năng người dùng
- Đăng ký tài khoản khách hàng.
- Đăng nhập, đăng xuất bằng tài khoản thường.
- Hỗ trợ đăng nhập Google .
- Hỗ trợ đăng nhậpFacebook ở chế độ demo 
- Cài đặt tài khoản: cập nhật họ tên, email và đổi mật khẩu.
- Xem danh sách sản phẩm.
- Xem chi tiết từng sản phẩm.
- Tìm kiếm sản phẩm theo tên, danh mục hoặc studio.
- Gợi ý tìm kiếm sản phẩm bằng AJAX.
- Thêm sản phẩm vào giỏ hàng.
- Cập nhật số lượng và xem tổng tiền trong giỏ hàng.
- Lưu sản phẩm vào danh sách yêu thích.
- Xem trang danh sách yêu thích.
- Đặt hàng và xem lịch sử đơn hàng.
- Xem trạng thái đơn hàng và phương thức thanh toán bằng tiếng Việt dễ hiểu.

### 2. Chức năng thanh toán
- Thiết kế lại trang checkout theo từng bước giống website thương mại điện tử.
- Hỗ trợ các phương thức thanh toán:
  - Thanh toán khi nhận hàng (COD)
  - Chuyển khoản ngân hàng
  - VietQR
  - Ví điện tử demo
  - Thẻ ATM / Visa demo
- Tạo trang thanh toán riêng cho từng phương thức.
- Hiển thị QR thanh toán VietQR theo tổng tiền đơn hàng.
- Cho phép người dùng xác nhận đã thanh toán trong môi trường demo.
- Phân biệt trạng thái đơn hàng: chờ xác nhận, chờ thanh toán, đã thanh toán.

### 3. Chức năng quản trị
- Trang dashboard quản trị.
- Hiển thị số lượng sản phẩm, đơn hàng và người dùng.
- Quản lý danh sách sản phẩm.
- Thêm, sửa, xóa sản phẩm.
- Upload ảnh sản phẩm.
- Quản lý đơn hàng và theo dõi trạng thái.
- Điều hướng nhanh từ admin về website.

### 4. Giao diện và trải nghiệm
- Cải tiến giao diện trang chủ theo phong cách chuyên nghiệp hơn.
- Thiết kế lại header, footer, product card, hero banner và khu vực danh mục.
- Thêm avatar và cụm cài đặt tài khoản trên header.
- Thêm chat box hỗ trợ ở góc phải màn hình.
- Tối ưu responsive cho desktop và mobile.
- Sửa lỗi đường dẫn CSS/JS khi truy cập trang admin.
- Tự nhận diện URL local/hosting để hạn chế lỗi sai đường dẫn khi chạy trên XAMPP.

## Cơ Sở Dữ Liệu
Dự án sử dụng MySQL với các bảng chính:
- `users`: lưu thông tin tài khoản người dùng và admin.
- `products`: lưu thông tin sản phẩm, danh mục, giá, tồn kho và ảnh.
- `orders`: lưu thông tin đơn hàng, khách hàng, phương thức thanh toán và trạng thái.
- `order_items`: lưu chi tiết sản phẩm trong từng đơn hàng.

File database mẫu:
- `database/website_ban_mo_hinh.sql`
- `database/add_10_products.sql` dùng để bổ sung thêm dữ liệu sản phẩm mẫu.

## Công Nghệ Sử Dụng
- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Môi trường chạy thử:** XAMPP
- **Thiết kế giao diện:** Figma
- **Quản lý mã nguồn:** GitHub

## Cấu Trúc Dự Án
- `index.php`: trang chủ và danh sách sản phẩm.
- `product-detail.php`: trang chi tiết sản phẩm.
- `cart.php`: trang giỏ hàng.
- `checkout.php`: trang nhập thông tin và chọn phương thức thanh toán.
- `payment.php`: trang xử lý/mô phỏng thanh toán.
- `order-success.php`: trang đặt hàng thành công.
- `order-history.php`: lịch sử đơn hàng của khách hàng.
- `login.php`, `register.php`, `logout.php`: chức năng tài khoản.
- `account-settings.php`: trang cài đặt tài khoản.
- `wishlist.php`: trang danh sách yêu thích.
- `admin/`: các trang quản trị.
- `includes/`: cấu hình, kết nối database, header, footer và hàm dùng chung.
- `assets/`: CSS, JavaScript và hình ảnh giao diện.
- `database/`: file SQL tạo database và dữ liệu mẫu.
- `reports/`: báo cáo tiến độ.

## Hướng Dẫn Chạy Dự Án
1. Copy thư mục dự án vào `htdocs` của XAMPP.
2. Tạo database tên `website_ban_mo_hinh`.
3. Import file `database/website_ban_mo_hinh.sql` vào MySQL/phpMyAdmin.
4. Kiểm tra cấu hình database trong `includes/config.php`.
5. Mở website trên trình duyệt, ví dụ:
   - `http://localhost/website-ban-mo-hinh-php-v3/`
6. Đăng nhập tài khoản demo nếu đã import database:
   - Admin: `admin / 123456`
   - User: `user / 123456`

## Đối Tượng Sử Dụng
- **Khách hàng:** xem sản phẩm, tìm kiếm, yêu thích, thêm giỏ hàng, đặt hàng và xem lịch sử đơn.
- **Quản trị viên:** quản lý sản phẩm, đơn hàng, ảnh sản phẩm và dữ liệu hệ thống.

## Phân Công 

1. Công việc chung của nhóm
Thống nhất đề tài website bán mô hình.
Xác định đối tượng sử dụng gồm khách hàng và quản trị viên.
Xây dựng bố cục các trang chính dựa trên định hướng giao diện Figma.
Triển khai website bằng PHP thuần, MySQL, HTML, CSS và JavaScript.
Triển khai website lên hosting thật và cấu hình domain/URL public.
Cập nhật tài liệu README và báo cáo tiến độ theo chức năng đã hoàn thiện.
2. Công việc của Lại Nam Sơn
Xây dựng cấu trúc dự án PHP/MySQL.
Kết nối database MySQL và tạo dữ liệu mẫu sản phẩm.
Xây dựng trang chủ hiển thị sản phẩm từ database.
Xây dựng chức năng tìm kiếm và gợi ý tìm kiếm bằng AJAX.
Xây dựng chức năng đăng ký, đăng nhập, đăng xuất.
Thêm đăng nhập Google/Facebook ở chế độ demo và cấu hình OAuth callback.
Thêm trang cài đặt tài khoản để cập nhật họ tên, email và mật khẩu.
Xây dựng giỏ hàng và xử lý thêm sản phẩm vào giỏ.
Xây dựng luồng đặt hàng và lưu lịch sử đơn hàng.
Cải tiến checkout thành các phương thức thanh toán riêng:
COD
Chuyển khoản ngân hàng
VietQR
Ví điện tử demo
Thẻ ATM / Visa demo
Cải tiến giao diện header, footer, product card, hero banner, account chip và responsive.
Cấu hình môi trường local và hosting trong includes/config.php.
Deploy website lên hosting thật.
Cấu hình domain/URL public để truy cập website ngoài môi trường local.
3. Công việc của Phương
Xây dựng trang thanh toán riêng theo từng phương thức.
Thêm chat box hỗ trợ ở góc phải màn hình.
Kiểm tra các chức năng chính sau khi deploy: trang chủ, sản phẩm, đăng nhập, giỏ hàng, thanh toán demo và trang admin.
Hỗ trợ xây dựng ý tưởng nội dung website bán mô hình.
Góp ý bố cục trang chủ, trang sản phẩm và giao diện mua hàng.
Phối hợp hoàn thiện định hướng trình bày sản phẩm và thông tin khách hàng.
4. Công việc của Vinh
Xây dựng trang chi tiết sản phẩm.
Xây dựng quản lý sản phẩm, thêm/sửa/xóa và upload ảnh sản phẩm.
Xây dựng danh sách sản phẩm yêu thích.
Xây dựng trang admin dashboard.
Hỗ trợ phân tích luồng giỏ hàng và đặt hàng.
Hỗ trợ định hướng các trạng thái đơn hàng.
Góp ý cho phần quản lý đơn hàng và thanh toán.

## Tài Liệu Liên Quan

- [SRS chức năng Đăng nhập hệ thống](./SRS_dang_nhap_website_ban_mo_hinh.md)
- [Báo cáo tiến độ ngày 23/04/2026](./reports/REPORT_03042026.md)

## Ghi Chú
Dự án đã hoàn thiện các chức năng cơ bản của một website bán mô hình: sản phẩm, tài khoản, giỏ hàng, yêu thích, checkout nhiều phương thức, thanh toán demo, lịch sử đơn hàng, chat hỗ trợ và trang quản trị. Một số phần như thanh toán ví điện tử/thẻ và đăng nhập social đang ở mức mô phỏng/demo để phù hợp với môi trường đồ án.
