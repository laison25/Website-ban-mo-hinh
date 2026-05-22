# Software Requirement Specification (SRS)
## Chức năng: Đăng nhập hệ thống

**Mã chức năng:** AUTH-01  
**Trạng thái:** Đã triển khai bước đầu  
**Dự án:** Website bán mô hình  
**Nhóm trưởng:** Lại Sơn  
**Thành viên:** Phương, Vinh  

---

### 1. Mô tả tổng quan (Description)
Chức năng đăng nhập cho phép người dùng và quản trị viên truy cập vào hệ thống website bán mô hình bằng tài khoản đã đăng ký trước đó hoặc bằng tài khoản Google.

Hệ thống đảm bảo việc xác thực thông tin an toàn, đúng tài khoản, tạo phiên đăng nhập sau khi xác thực thành công và hỗ trợ phân quyền cơ bản giữa khách hàng và quản trị viên.

### 2. Phạm vi chức năng (Scope)
- Đăng nhập bằng email/username và mật khẩu.
- Đăng nhập nhanh bằng Google thông qua OAuth.
- Tự động tạo tài khoản khách hàng khi người dùng đăng nhập Google lần đầu và email chưa tồn tại trong hệ thống.
- Chuyển hướng người dùng sau đăng nhập theo vai trò:
  - `admin`: chuyển đến trang quản trị.
  - `customer`: chuyển đến trang chủ website.
- Hiển thị thông báo lỗi khi đăng nhập thất bại hoặc thông tin xác thực không hợp lệ.

### 3. Luồng nghiệp vụ (User Workflow)

#### 3.1. Đăng nhập bằng tài khoản thường

| Bước | Hành động người dùng | Phản hồi hệ thống |
|------|----------------------|-------------------|
| 1 | Truy cập trang đăng nhập | Hiển thị form đăng nhập gồm email/username, mật khẩu và các nút đăng nhập mạng xã hội |
| 2 | Nhập email/username và mật khẩu | Hệ thống kiểm tra dữ liệu đầu vào |
| 3 | Nhấn nút "Đăng nhập" | Hệ thống truy vấn tài khoản theo email hoặc username |
| 4 | Thông tin hợp lệ và tài khoản đang hoạt động | Tạo session đăng nhập và chuyển đến trang phù hợp với vai trò |
| 5 | Thông tin sai hoặc tài khoản không hoạt động | Hiển thị thông báo lỗi và yêu cầu nhập lại |

#### 3.2. Đăng nhập bằng Google

| Bước | Hành động người dùng | Phản hồi hệ thống |
|------|----------------------|-------------------|
| 1 | Nhấn nút "Đăng nhập bằng Google" | Hệ thống chuyển người dùng đến endpoint đăng nhập Google |
| 2 | Người dùng chọn tài khoản Google và cấp quyền | Google trả mã xác thực về callback của hệ thống |
| 3 | Hệ thống xử lý callback | Kiểm tra `state`, đổi mã xác thực lấy access token và lấy thông tin hồ sơ gồm email, tên hiển thị |
| 4 | Email Google đã tồn tại trong bảng `users` | Đăng nhập vào tài khoản tương ứng |
| 5 | Email Google chưa tồn tại | Tạo tài khoản mới với role mặc định `customer`, username sinh từ email và mật khẩu ngẫu nhiên đã mã hóa |
| 6 | Đăng nhập thành công | Tạo session và chuyển người dùng đến trang phù hợp |
| 7 | Không lấy được email hoặc phiên OAuth không hợp lệ | Hiển thị thông báo lỗi và quay lại trang đăng nhập |

### 4. Yêu cầu dữ liệu (Data Requirements)

#### 4.1. Dữ liệu đầu vào khi đăng nhập thường
- **Email hoặc username:** kiểu chuỗi, bắt buộc nhập.
- **Mật khẩu:** kiểu chuỗi, bắt buộc nhập, được ẩn khi gõ.

#### 4.2. Dữ liệu đầu vào khi đăng nhập Google
- **Provider:** giá trị `google`.
- **OAuth code:** mã xác thực do Google trả về callback.
- **State:** chuỗi chống giả mạo yêu cầu đăng nhập, phải khớp với session hiện tại.
- **Thông tin hồ sơ Google:** email và tên hiển thị sau khi xác thực thành công.

#### 4.3. Dữ liệu lưu trữ (Database - bảng `users`)
- `id`: mã người dùng.
- `full_name`: họ tên hoặc tên hiển thị của người dùng.
- `username`: tên đăng nhập, duy nhất.
- `email`: email người dùng, duy nhất.
- `password_hash`: mật khẩu đã mã hóa.
- `role`: quyền người dùng (`admin` hoặc `customer`).
- `status`: trạng thái tài khoản, `1` là đang hoạt động.
- `created_at`: thời điểm tạo tài khoản.

### 5. Ràng buộc kỹ thuật & bảo mật (Technical Constraints)
- Mật khẩu phải được lưu dưới dạng hash, không lưu ở dạng văn bản thường.
- Hệ thống phải kiểm tra đầy đủ dữ liệu trước khi xác thực.
- Nếu nhập sai tài khoản hoặc mật khẩu thì không cho phép đăng nhập.
- Sau khi đăng nhập thành công, hệ thống tạo session chứa thông tin người dùng cần thiết.
- Tài khoản có `status` khác `1` không được phép đăng nhập.
- Google OAuth phải dùng `client_id`, `client_secret`, `redirect_uri` đúng cấu hình.
- Khi xử lý Google callback, hệ thống phải kiểm tra `state` để giảm rủi ro CSRF.
- Hệ thống chỉ chấp nhận email hợp lệ lấy từ Google trước khi tạo hoặc đăng nhập tài khoản.
- Tài khoản tạo từ Google lần đầu phải có role mặc định là `customer`.
- Mật khẩu ngẫu nhiên của tài khoản Google phải được hash trước khi lưu để vẫn đảm bảo an toàn dữ liệu.

### 6. Trường hợp ngoại lệ & xử lý lỗi (Edge Cases)
- **Trường hợp:** Bỏ trống email/username hoặc mật khẩu.  
  - **Xử lý:** Hiển thị thông báo "Vui lòng nhập đầy đủ tài khoản và mật khẩu."

- **Trường hợp:** Nhập sai tài khoản hoặc mật khẩu.  
  - **Xử lý:** Hiển thị thông báo "Thông tin đăng nhập không đúng."

- **Trường hợp:** Tài khoản không tồn tại.  
  - **Xử lý:** Không tạo session, hiển thị thông báo lỗi đăng nhập.

- **Trường hợp:** Tài khoản bị khóa hoặc không hoạt động.  
  - **Xử lý:** Không cho phép đăng nhập và hiển thị thông báo tài khoản không khả dụng.

- **Trường hợp:** Người dùng hủy đăng nhập Google hoặc Google không trả email.  
  - **Xử lý:** Hiển thị thông báo không lấy được email từ Google và yêu cầu thử lại hoặc đăng nhập bằng tài khoản thường.

- **Trường hợp:** `state` OAuth không hợp lệ hoặc hết phiên.  
  - **Xử lý:** Hủy luồng đăng nhập Google, xóa thông tin OAuth tạm trong session và chuyển về trang đăng nhập.

- **Trường hợp:** Email Google đã tồn tại trong hệ thống.  
  - **Xử lý:** Không tạo tài khoản mới, đăng nhập vào tài khoản có email tương ứng nếu tài khoản đang hoạt động.

- **Trường hợp:** Username sinh từ email Google bị trùng.  
  - **Xử lý:** Tự động thêm hậu tố số vào username cho đến khi không trùng.

### 7. Giao diện (UI/UX)
- Form đăng nhập gồm:
  - Email hoặc username.
  - Mật khẩu.
  - Nút "Đăng nhập".
  - Link tạo tài khoản mới.
  - Khu vực đăng nhập nhanh.
- Khu vực đăng nhập nhanh gồm:
  - Nút "Đăng nhập bằng Google".
  - Có thể hiển thị thêm nút đăng nhập bằng Facebook nếu hệ thống bật provider này.
- Giao diện đơn giản, dễ nhìn, dễ sử dụng.
- Thông báo lỗi cần hiển thị rõ ràng gần form đăng nhập.
- Sau khi đăng nhập thành công, hệ thống hiển thị thông báo thành công và chuyển hướng đúng vai trò.

### 8. Tiêu chí chấp nhận (Acceptance Criteria)
- Người dùng đăng nhập thành công bằng email/username và mật khẩu hợp lệ.
- Người dùng không đăng nhập được khi thiếu dữ liệu, sai mật khẩu hoặc tài khoản không hoạt động.
- Người dùng có thể đăng nhập bằng Google khi cấu hình OAuth hợp lệ.
- Nếu email Google chưa tồn tại, hệ thống tạo tài khoản mới với role `customer`.
- Nếu email Google đã tồn tại, hệ thống đăng nhập vào đúng tài khoản hiện có.
- Session sau đăng nhập có đủ `id`, `full_name`, `username`, `email`, `role`.
- Admin được chuyển đến `admin/index.php`; khách hàng được chuyển đến `index.php`.

### 9. Ghi chú
Tài liệu này mô tả chức năng đăng nhập của dự án website bán mô hình, bao gồm đăng nhập truyền thống và đăng nhập bằng Google. Nội dung có thể tiếp tục được cập nhật khi nhóm phát triển bổ sung provider mạng xã hội, thay đổi chính sách bảo mật hoặc mở rộng phân quyền.
