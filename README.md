# Website Bán Mô Hình - Lzon Poke

## Tên Đề Tài
Website Bán Mô Hình Anime - Lzon Poke

## Mã Nguồn Github
- Repository: https://github.com/laison25/Website-ban-mo-hinh

## Giới Thiệu Website/Hệ Thống
Website Bán Mô Hình là dự án website thương mại điện tử dùng để bán các sản phẩm mô hình, figure, resin statue và phụ kiện sưu tầm. Website hỗ trợ khách hàng xem sản phẩm, tìm kiếm, thêm giỏ hàng, lưu yêu thích, đặt hàng, thanh toán demo, tra cứu đơn hàng và quản lý tài khoản.

Dự án được xây dựng bằng PHP thuần, MySQL, HTML, CSS và JavaScript. Project có thể chạy local bằng XAMPP hoặc Docker.

## Danh Sách Thành Viên Và MSSV
| STT | Họ tên | MSSV | Vai trò |
| :-- | :-- | :-- | :-- |
| 1 | Lại Nam Sơn | 23810310088 | Nhóm trưởng |
| 2 | Nguyễn Văn Phương | 23810310101 | Thành viên |
| 3 | Nguyễn Thành Vinh | 23810310107 | Thành viên |


## Phân Công Nhiệm Vụ Cụ Thể
| Thành viên | Nhiệm vụ |
| :-- | :-- |
| Lại Nam Sơn | Xây dựng source PHP/MySQL, giao diện chính, tài khoản, OAuth GOOGLE , giỏ hàng, thanh toán, quản trị admin, API Box AI, Docker và tài liệu dự án. |
| Nguyễn Văn Phương | Hỗ trợ nội dung, góp ý giao diện, kiểm thử luồng khách hàng, kiểm thử thanh toán demo/VietQR và rà soát trải nghiệm Box AI. |
| Nguyễn Thành Vinh | Hỗ trợ phân tích luồng giỏ hàng, đặt hàng, trạng thái đơn hàng, quản lý đơn hàng admin, danh sách yêu thích và kiểm thử chức năng quản trị. |

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
- Đăng nhập nhanh bằng Google và luồng OAuth callback.
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
- **OAuth:** Google/Facebook OAuth và callback
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

## Hướng Dẫn Cài Đặt
1. Cài XAMPP có Apache, PHP và MySQL.
2. Clone hoặc tải source code về máy.
3. Copy thư mục dự án vào `htdocs` của XAMPP.
4. Mở phpMyAdmin và tạo database tên `website_ban_mo_hinh`.
5. Import file `database/website_ban_mo_hinh.sql`.
6. Kiểm tra cấu hình trong `includes/config.php`.
7. Nếu chạy trên hosting, cấu hình các biến môi trường cần thiết:
   - `APP_URL`
   - `APP_DB_HOST`, `APP_DB_PORT`, `APP_DB_NAME`, `APP_DB_USER`, `APP_DB_PASS`
   - `QR_BANK_ID`, `QR_ACCOUNT_NO`, `QR_ACCOUNT_NAME`, `QR_TEMPLATE`
   - `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`
   - `FACEBOOK_APP_ID`, `FACEBOOK_APP_SECRET`, `FACEBOOK_REDIRECT_URI`
   - `GEMINI_API_KEY` hoặc file `includes/gemini-config.php` nếu dùng Box AI với Gemini API

## Hướng Dẫn Chạy Project

### Chạy bằng XAMPP
1. Bật Apache và MySQL trong XAMPP Control Panel.
2. Mở website trên trình duyệt:
   - `http://localhost/website-ban-mo-hinh-php-v3/`
3. Truy cập trang quản trị:
   - `http://localhost/website-ban-mo-hinh-php-v3/admin/`
4. Đăng nhập tài khoản demo nếu đã import database:
   - Admin: `admin / 123456`
   - User: `user / 123456`

## Triển Khai Hosting
- Dự án đã được cấu hình để có thể chạy trên hosting thật, không chỉ chạy local bằng XAMPP.
- File `includes/config.php` có logic tự nhận diện môi trường local/hosting dựa trên `SERVER_NAME`.
- Khi chạy local, website dùng URL dạng `http://localhost/website-ban-mo-hinh-php-v3/` và database local.
- Khi chạy trên hosting, website dùng URL public được cấu hình trong `APP_URL`.
- Cấu hình database hosting gồm host, port, database name, username và password được tách riêng với cấu hình local.
- Đường dẫn CSS, JavaScript, ảnh sản phẩm và link điều hướng được xử lý qua helper `url()` để hạn chế lỗi sai đường dẫn khi upload lên hosting.
- Các chức năng chính đã được chuẩn bị để kiểm tra trên hosting:
  - Trang chủ.
  - Đăng ký, đăng nhập và đăng xuất.
  - Đăng nhập Google/Facebook nếu cấu hình OAuth redirect URI đúng.
  - Danh sách sản phẩm và chi tiết sản phẩm.
  - Giỏ hàng, yêu thích, checkout và thanh toán demo.
  - API Box AI qua `ai-chat.php`.
  - Trang quản trị admin.
- Khi upload hosting cần đảm bảo upload đủ các thư mục/file chính: `admin/`, `assets/`, `database/`, `includes/`, `uploads/`, các file PHP ở thư mục gốc và file cấu hình cần thiết.
- Không upload công khai file chứa API key thật nếu repository hoặc hosting có thể bị lộ mã nguồn.

## Cấu Hình Cần Lưu Ý
- Cấu hình database, URL website, VietQR và social login nằm trong `includes/config.php` và có thể ghi đè bằng biến môi trường.
- File mẫu cấu hình Gemini nằm tại `includes/gemini-config.example.php`.
- File mẫu cấu hình OpenAI nằm tại `includes/openai-config.example.php`.
- Không public hoặc commit file chứa API key, mật khẩu database, tài khoản ngân hàng thật như `includes/gemini-config.php`, `includes/openai-config.php` hoặc thông tin hosting production.

## Tài Khoản Demo
- **Admin:** `admin / 123456`
- **User:** `user / 123456`

## Hình Ảnh Minh Họa Hệ Thống

## Screenshot / Minh Chứng

### 1. Giao diện trang chủ
<img width="1911" height="964" alt="image" src="https://github.com/user-attachments/assets/90ffb378-a569-4e9d-ae58-38f4b822ff22" />


### 2. Giao diện trang đăng nhập
<img width="1914" height="958" alt="image" src="https://github.com/user-attachments/assets/1973bbb5-a607-454d-aadd-563b0463c265" />


### 3. Giao diện trang chi tiết sản phẩm
<img width="1917" height="956" alt="image" src="https://github.com/user-attachments/assets/ccb2e558-a78d-4c8e-9522-79c06351301b" />


### 4. Giao diện giỏ hàng
<img width="1913" height="958" alt="image" src="https://github.com/user-attachments/assets/1734e074-4745-4084-b43f-5c13699320de" />


### 5. Giao diện checkout / thanh toán
<img width="1910" height="956" alt="image" src="https://github.com/user-attachments/assets/20afa267-2b8f-48aa-bc02-6c2d7af77421" />


### 6. Giao diện lịch sử hoặc tra cứu đơn hàng
<img width="1916" height="951" alt="image" src="https://github.com/user-attachments/assets/b67d2da7-67e3-4af6-992f-c015c4afc44a" />
<img width="1919" height="956" alt="image" src="https://github.com/user-attachments/assets/707bc529-0a5b-4213-bb09-ad2aa791e930" />


### 7. Giao diện Box AI tư vấn sản phẩm
<img width="716" height="780" alt="image" src="https://github.com/user-attachments/assets/adf02e2a-b8de-4a93-917f-341d9980368f" />


### 8. Giao diện dashboard admin
<img width="1918" height="953" alt="image" src="https://github.com/user-attachments/assets/bbab3b8d-e52e-4757-8427-cb556ede8d94" />


### 9. Giao diện quản lý sản phẩm admin
<img width="1912" height="960" alt="image" src="https://github.com/user-attachments/assets/e80c1c0b-ee6b-45de-b4e8-a9c1a4ee9c7e" />


### 10. Giao diện quản lý đơn hàng admin
<img width="1911" height="954" alt="image" src="https://github.com/user-attachments/assets/15eb4519-54c5-4f2c-bf44-c4b8af9fcdf5" />


### 11. Minh chứng xuất file CSV
<img width="1371" height="648" alt="image" src="https://github.com/user-attachments/assets/863fdff0-46da-4645-a211-722da982b8c4" />


### 12. Minh chứng chạy bằng Docker hoặc hosting
<img width="1915" height="1033" alt="image" src="https://github.com/user-attachments/assets/6e2de7a7-4415-4187-9ce8-a298c62939bc" />


### 13. Tổng quan file thiết kế Figma
<img width="1892" height="970" alt="Ảnh chụp màn hình Figma" src="https://github.com/user-attachments/assets/e9fcc83f-ebf3-416a-9a6e-32e8e2a6b4b8" />


## Link Video Demo
- Cần bổ sung link video demo: `https://...`

## Link Online Đã Deploy
- https://modelshop-laison.rf.gd/

## Đối Tượng Sử Dụng
- **Khách hàng:** xem sản phẩm, tìm kiếm, yêu thích, thêm giỏ hàng, đặt hàng, thanh toán demo, tra cứu đơn và xem lịch sử đơn hàng.
- **Quản trị viên:** quản lý sản phẩm, ảnh sản phẩm, đơn hàng, thống kê doanh thu và xuất dữ liệu CSV.

## Tài Liệu Liên Quan
- [SRS chức năng Đăng nhập hệ thống](./SRS_dang_nhap_website_ban_mo_hinh.md)
- [Báo cáo tiến độ ngày 22/05/2026](./reports/REPORT_03042026.md)

## Ghi Chú
Dự án đã có các chức năng cốt lõi của một website bán mô hình: sản phẩm, tài khoản, đăng nhập social , giỏ hàng, yêu thích, checkout, thanh toán demo, VietQR, lịch sử đơn hàng, tra cứu đơn hàng, chat AI tư vấn, dashboard admin, quản lý sản phẩm, quản lý đơn hàng, xuất CSV và cấu hình chạy bằng Docker.

Một số phần như thanh toán ví điện tử/thẻ và đăng nhập social đang ở mức demo/mô phỏng để phù hợp với môi trường đồ án. Khi triển khai thật cần cấu hình API key, OAuth client, callback URL, thông tin ngân hàng và kiểm tra bảo mật trước khi public.
