# Thiết lập các tính năng bảo mật trên nền tảng website bán mô hình

Đồ án môn **Phát triển web an toàn**. Cửa hàng Lzon Poke và khu vực tài khoản Lzon Security chạy cùng một backend Node.js 24 và cùng cơ sở dữ liệu SQLite. Giao diện tiếng Việt viết bằng HTML, CSS và JavaScript. Dự án chạy trực tiếp bằng `node` và không cần tải gói npm.

## Chạy trên Windows

Mở PowerShell trong thư mục `website-ban-mo-hinh-php-v3`. Dùng Node.js 24.15 trở lên trong dòng 24.x.

```powershell
node --version
node server.mjs
```

Giữ cửa sổ này mở và truy cập [http://127.0.0.1:3000](http://127.0.0.1:3000). Tệp SQLite được tạo tại `data/vilock.sqlite` trong lần chạy đầu tiên.

Máy chủ Node.js phục vụ giao diện tại `public/`. Mã PHP gốc nằm trong cùng thư mục để đối chiếu. Chạy `node server.mjs` để dùng bản đồ án.

Đăng ký trên trang web để tạo tài khoản thành viên. Để có tài khoản quản trị, mở một cửa sổ **PowerShell 7** khác tại cùng thư mục và chạy lệnh sau. Nhập mật khẩu do bạn chọn khi được hỏi, với độ dài 15–128 ký tự.

```powershell
$OutputEncoding = [System.Text.UTF8Encoding]::new($false)
Read-Host -Prompt 'Mật khẩu quản trị' -MaskInput | node create-admin.mjs admin@example.test 'Quản trị viên'
```

Đăng nhập với `admin@example.test` và mật khẩu vừa nhập. Dùng một email riêng cho tài khoản thành viên khi trình diễn. Nếu cổng 3000 đã được sử dụng, đặt `$env:PORT = '3100'` trong PowerShell trước khi chạy máy chủ rồi mở `http://127.0.0.1:3100`.

## Chức năng

| Vai trò | Chức năng |
| --- | --- |
| Khách | Duyệt, tìm và lọc mô hình, xem chi tiết, thêm giỏ hàng, áp mã giảm giá, đăng ký và đăng nhập |
| Thành viên | Lưu sản phẩm yêu thích, đặt và theo dõi đơn của mình, chọn COD hoặc thanh toán mô phỏng, quản lý hồ sơ, mật khẩu và các phiên đăng nhập |
| Quản trị viên | Quản lý sản phẩm, tồn kho và đơn hàng tại `/shop-admin`, xuất bảng đang xem ra CSV, quản lý quyền và trạng thái của người dùng tại `/account`, xem nhật ký |

## Kiến trúc

Giao diện gọi API JSON cùng nguồn. `server.mjs` định tuyến, kiểm tra phiên và phân quyền. `store.mjs` quản lý mật khẩu, tài khoản, phiên và nhật ký. `shop.mjs` xử lý sản phẩm, giỏ hàng, đơn hàng và tồn kho. `create-admin.mjs` tạo tài khoản quản trị từ dòng lệnh.

Danh mục khởi tạo gồm 18 mô hình lấy từ bản SQL của shop PHP, cùng ảnh sản phẩm được sao chép sang `public/shop-images`. Tài khoản đã tạo trong Lzon Security tiếp tục dùng được. Bản Node.js không nhập dữ liệu khách hàng và đơn hàng từ MySQL. Thanh toán trực tuyến chạy ở chế độ mô phỏng. Các tích hợp Google/Facebook, trợ lý AI và tải ảnh lên từ trang quản trị chưa được chuyển, biểu mẫu sản phẩm cho phép chọn ảnh có sẵn.

| Bảng | Nội dung |
| --- | --- |
| `users` | Tên, email duy nhất, băm mật khẩu, vai trò, trạng thái và thời điểm tạo |
| `sessions` | Băm mã phiên, người dùng, mã CSRF, thời điểm hết hạn và tên trình duyệt |
| `audit` | Loại sự kiện, người thực hiện, tài khoản liên quan và thời điểm ghi |
| `products` | Danh mục, giá, tồn kho và trạng thái hiển thị |
| `shop_orders`, `shop_order_items` | Chủ đơn, giao nhận, thanh toán, trạng thái, giá và số lượng tại thời điểm mua |
| `wishlist` | Sản phẩm được từng tài khoản lưu yêu thích |

## Cơ chế bảo vệ

- **Mật khẩu** được băm bằng scrypt với salt ngẫu nhiên riêng. Tham số là `N=2^15`, `r=8`, `p=3` theo một cấu hình OWASP công bố. [OWASP Password Storage](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)
- **Phiên** có mã ngẫu nhiên 256 bit và hết hạn sau 8 giờ. Cookie dùng `HttpOnly` và `SameSite=Lax`. Khi ứng dụng chạy sau proxy HTTPS với `APP_ORIGIN` tương ứng, cookie dùng thêm `Secure` và tiền tố `__Host-`. Đổi mật khẩu, đổi vai trò hoặc đổi trạng thái sẽ thu hồi các phiên của tài khoản liên quan. [OWASP Session Management](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html)
- **Kiểm soát truy cập** nằm ở API. Mọi yêu cầu thay đổi dữ liệu đều kiểm tra `Origin`. Các thao tác cần đăng nhập còn kiểm tra mã CSRF theo phiên. Quản trị viên không thể tự khóa hoặc tự hạ quyền qua giao diện quản trị.
- **Chống thử mật khẩu liên tục** bằng giới hạn 10 lượt đăng nhập không thành công theo IP trong 15 phút. Đăng ký và đổi mật khẩu có giới hạn riêng. Bộ đếm nằm trong bộ nhớ tiến trình.
- **Bảo vệ dữ liệu đầu vào** bằng kiểm tra kiểu và độ dài, truy vấn SQLite có tham số, chèn nội dung người dùng qua `textContent` và đặt Content Security Policy.
- **Bảo vệ giao dịch** bằng kiểm tra chủ đơn, tính lại giá và ưu đãi trên máy chủ và trừ tồn kho trong cùng giao dịch SQLite. Gửi lặp cùng mã yêu cầu trả lại đơn đã tạo. Khi quản trị viên hủy đơn, tồn kho được hoàn một lần.

## Kiểm thử và trình diễn

```powershell
node --test
```

Các bài kiểm thử tạo SQLite trong bộ nhớ và gọi máy chủ thật trên một cổng tạm. Chúng bao gồm xác thực, CSRF, quản trị tài khoản, thu hồi phiên, kiểm tra chủ đơn, tính tổng ở máy chủ, chống đặt đơn trùng và cập nhật tồn kho.

1. **Tự bảo vệ tài khoản.** Đăng nhập cùng một tài khoản thành viên trên hai hồ sơ trình duyệt. Ở trang **Phiên đăng nhập**, kết thúc phiên còn lại. Tải lại trang ở trình duyệt thứ hai để thấy yêu cầu đăng nhập lại. Kiểm tra sự kiện trong **Lịch sử bảo mật**.
2. **Quản trị và phân quyền.** Đăng nhập bằng tài khoản quản trị trong một cửa sổ riêng và khóa tài khoản thành viên. Tải lại trang thành viên để thấy phiên bị thu hồi. Mở khóa tài khoản và kiểm tra hai thao tác trong **Nhật ký hoạt động** của quản trị viên.
3. **Đặt hàng an toàn.** Là khách mua, chọn sản phẩm, áp mã `LZON10` và tạo đơn. Mở cùng đường dẫn đơn hàng ở hồ sơ trình duyệt của khách hàng khác để xác nhận họ không truy cập được. Tại **Quản lý cửa hàng**, kiểm tra tồn kho sau khi tạo và hủy đơn.

Phiên bản đồ án chạy trên loopback và lưu dữ liệu cục bộ. Đăng ký dùng email làm tên đăng nhập, chưa gửi thư xác minh hoặc đặt lại mật khẩu.
