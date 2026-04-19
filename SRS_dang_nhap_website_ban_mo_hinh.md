# Software Requirement Specification (SRS)
## Chức năng: Đăng nhập hệ thống
**Mã chức năng:** AUTH-01  
**Trạng thái:** Draft  
**Dự án:** Website bán mô hình  

---

### 1. Mô tả tổng quan (Description)
Chức năng đăng nhập cho phép người dùng và quản trị viên truy cập vào hệ thống website bán mô hình bằng tài khoản đã đăng ký trước đó.  
Hệ thống đảm bảo việc xác thực thông tin an toàn, đúng tài khoản và hỗ trợ phân quyền cơ bản giữa người dùng và admin.

### 2. Luồng nghiệp vụ (User Workflow)

| Bước | Hành động người dùng | Phản hồi hệ thống |
|------|-----------------------|-------------------|
| 1 | Truy cập trang đăng nhập | Hiển thị form đăng nhập gồm tên đăng nhập/email và mật khẩu |
| 2 | Nhập thông tin tài khoản | Hệ thống kiểm tra dữ liệu đầu vào |
| 3 | Nhấn nút "Đăng nhập" | Hệ thống gửi dữ liệu để xác thực |
| 4 | Thông tin đúng | Cho phép đăng nhập và chuyển đến trang phù hợp |
| 5 | Thông tin sai | Hiển thị thông báo lỗi và yêu cầu nhập lại |

### 3. Yêu cầu dữ liệu (Data Requirements)

#### 3.1. Dữ liệu đầu vào (Input Fields)
- **Tên đăng nhập hoặc Email:** kiểu chuỗi, bắt buộc nhập
- **Mật khẩu:** kiểu chuỗi, bắt buộc nhập, được ẩn khi gõ

#### 3.2. Dữ liệu lưu trữ (Database - bảng `users`)
- `id`: mã người dùng
- `username`: tên đăng nhập
- `email`: email người dùng
- `password`: mật khẩu đã mã hóa
- `role`: quyền người dùng (`user` hoặc `admin`)

### 4. Ràng buộc kỹ thuật & bảo mật (Technical Constraints)
- Mật khẩu phải được lưu dưới dạng mã hóa, không lưu dạng văn bản thường
- Hệ thống kiểm tra đầy đủ dữ liệu trước khi xác thực
- Nếu nhập sai tài khoản hoặc mật khẩu thì không cho phép đăng nhập
- Sau khi đăng nhập thành công, hệ thống tạo session cho người dùng
- Admin và người dùng thường được chuyển đến giao diện phù hợp với quyền của mình

### 5. Trường hợp ngoại lệ & xử lý lỗi (Edge Cases)
- **Trường hợp:** Bỏ trống tên đăng nhập hoặc mật khẩu  
  - **Xử lý:** Hiển thị thông báo "Vui lòng nhập đầy đủ thông tin"

- **Trường hợp:** Nhập sai tài khoản hoặc mật khẩu  
  - **Xử lý:** Hiển thị thông báo "Tên đăng nhập hoặc mật khẩu không đúng"

- **Trường hợp:** Tài khoản không tồn tại  
  - **Xử lý:** Hiển thị thông báo yêu cầu kiểm tra lại thông tin

### 6. Giao diện (UI/UX)
- Form đăng nhập gồm:
  - Tên đăng nhập hoặc email
  - Mật khẩu
  - Nút Đăng nhập
- Giao diện đơn giản, dễ nhìn, dễ sử dụng
- Hỗ trợ hiển thị thông báo lỗi khi người dùng nhập sai
- Có thể mở rộng thêm chức năng “Hiện mật khẩu” hoặc “Nhớ tài khoản” nếu cần
