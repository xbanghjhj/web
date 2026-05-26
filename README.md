# POS System - Hệ Thống Quản Lý Bán Hàng Điện Thoại và Phụ Kiện

Hệ thống POS (Point of Sale) hoàn chỉnh dành cho cửa hàng điện thoại và phụ kiện. Được xây dựng bằng PHP thuần và MySQL, không phụ thuộc vào các framework lớn như Laravel hay Symfony.

---

## Dự Án Giải Quyết Vấn Đề Gì?

Các cửa hàng kinh doanh điện thoại và phụ kiện quy mô nhỏ đến trung bình thường gặp nhiều khó khăn trong vận hành thực tế:

- Bán hàng thủ công, dễ xảy ra nhầm lẫn về giá cả và tồn kho.
- Không theo dõi sát sao được doanh thu, chi phí và lợi nhuận theo thời gian thực.
- Khó khăn trong việc phân quyền cho nhân viên bán hàng và quản trị viên, dẫn đến rủi ro về dữ liệu giá nhập.
- Mất thông tin khách hàng, thiếu cơ sở để chăm sóc khách hàng thân thiết và theo dõi lịch sử mua hàng.
- Hóa đơn bán hàng không chuyên nghiệp và thiếu minh bạch.
- Quy trình cấp tài khoản cho nhân viên mới còn thủ công, thiếu tính năng xác thực qua email an toàn.

Hệ thống này cung cấp giải pháp số hóa toàn diện cho các nghiệp vụ trên.

---

## Tính Năng Chính

### Dành cho Admin
- **Dashboard tổng quan**: Thống kê doanh thu, số đơn, lợi nhuận — lọc theo ngày/tuần/tháng hoặc tùy chọn.
- **Quản lý nhân viên**: Thêm nhân viên, gửi email mời kích hoạt, xem lịch sử bán hàng, khóa/mở tài khoản.
- **Quản lý sản phẩm**: Thêm/sửa/xóa sản phẩm, upload ảnh, phân loại danh mục.
- **Quản lý danh mục**: Tạo/xóa danh mục hàng hóa.
- **Xem đơn hàng**: Xem chi tiết từng đơn, lọc theo thời gian.
- **Báo cáo**: Báo cáo doanh thu và lợi nhuận với biểu đồ trực quan.

### Dành cho Nhân viên (Staff)
- **Bán hàng POS**: Tìm sản phẩm theo tên hoặc quét barcode, thêm vào giỏ, tính tiền thối.
- **Quản lý khách hàng**: Tra cứu khách cũ theo số điện thoại hoặc tự động tạo khách mới.
- **In hóa đơn PDF**: Tự động tạo hóa đơn sau mỗi giao dịch thành công.
- **Đổi mật khẩu**: Bắt buộc đổi mật khẩu lần đầu đăng nhập.

---

## Công Nghệ Sử Dụng

### Backend
- **PHP 8.1+ (PHP thuần)**: Xử lý logic phía máy chủ.
- **MySQL / MariaDB**: Lưu trữ cơ sở dữ liệu quan hệ.
- **MySQLi (Prepared Statements)**: Thực thi truy vấn an toàn, ngăn ngừa triệt để lỗi bảo mật SQL Injection.
- **PHP Sessions**: Duy trì trạng thái đăng nhập và phân quyền phiên làm việc.
- **PHPMailer**: Thư viện gửi thư điện tử qua giao thức SMTP phục vụ cho quy trình xác thực nhân viên.
- **FPDF**: Thư viện kết xuất tài liệu dạng PDF hỗ trợ in hóa đơn thanh toán.

### Frontend
- **Bootstrap 5.3**: Xây dựng layout responsive và các components UI có sẵn.
- **jQuery 3.7**: Hỗ trợ tương tác DOM và gọi các API AJAX không reload trang.
- **Chart.js 4.4**: Hiển thị biểu đồ doanh thu và lợi nhuận trực quan.
- **Font Awesome 6.5**: Bổ sung thư viện biểu tượng hiển thị.
- **Vanilla CSS**: Định nghĩa các phong cách riêng biệt cho giao diện đăng nhập và dashboard.

---

## Tài Liệu Đặc Tả Nghiệp Vụ và Yêu Cầu Chức Năng

Dưới đây là đặc tả chi tiết các yêu cầu nghiệp vụ được trích xuất trực tiếp từ các yêu cầu thiết kế của dự án.

### 1. Nghiệp Vụ Đăng Nhập và Truy Cập Hệ Thống

#### Quyền truy cập và đường dẫn xác thực
- **Nhân viên cũ và Admin**: Được phép truy cập trực tiếp thông qua trang đăng nhập hệ thống.
- **Nhân viên mới**: Bắt buộc phải đăng nhập thông qua liên kết (link) kích hoạt gửi trong email xác thực mà Admin cấp phát.
- **Ràng buộc bảo mật**: Nếu nhân viên mới cố tình truy cập trực tiếp vào trang login mà chưa click vào link từ email, hệ thống sẽ chặn và thông báo: *"Vui lòng đăng nhập bằng cách nhấp vào liên kết trong email của bạn"*.
- **Hiệu lực của liên kết**: Link xác nhận được gửi qua email chỉ có giá trị sử dụng trong vòng 1 phút kể từ thời điểm gửi. Quá 1 phút, liên kết sẽ hết hạn và hệ thống yêu cầu liên hệ Admin để gửi lại.

#### Quy tắc định danh tài khoản
- **Username**: Được lấy từ phần tiền tố (prefix) đứng trước ký tự `@` của địa chỉ email đã đăng ký. Ví dụ, nếu email là `nguyenvana@gmail.com`, hệ thống tự động phân tích và tạo username là `nguyenvana`.
- **Tài khoản quản trị**: Username mặc định của Admin luôn là `admin`.
- **Password**:
  - **Admin**: Mật khẩu mặc định ban đầu là `admin`.
  - **Nhân viên mới**: Mật khẩu tạm thời được thiết lập chính là Mã số sinh viên của trưởng nhóm (viết thường) để đảm bảo tính xác thực của mã nguồn tự phát triển.
  - **Nhân viên cũ**: Sử dụng mật khẩu cá nhân đã được thay đổi trước đó.

#### Luồng xử lý sau khi xác thực thành công
- **Trường hợp Đăng nhập lần đầu (Nhân viên mới)**:
  - Hệ thống bắt buộc phải chuyển hướng người dùng đến trang Đổi mật khẩu ngay lập tức.
  - Người dùng không cần nhập mật khẩu cũ trong form này (vì đang sử dụng mật khẩu tạm).
  - **Ràng buộc nghiêm ngặt**: Khi chưa hoàn thành việc đổi mật khẩu lần đầu, nhân viên đó sẽ không thể truy cập bất kỳ chức năng nào khác (như bán hàng, sản phẩm...). Mọi nỗ lực cố tình truy cập vào URL chức năng sẽ bị hệ thống chuyển hướng trở lại trang đổi mật khẩu hoặc thực hiện đăng xuất.
- **Trường hợp tài khoản bị khóa (Locked)**:
  - Hệ thống từ chối đăng nhập và hiển thị thông báo rõ ràng về tình trạng tài khoản đang bị khóa bởi Admin.
- **Trường hợp tài khoản hoạt động bình thường**:
  - Hệ thống kiểm tra vai trò và chuyển hướng về `admin_dashboard.php` (đối với Admin) hoặc `pos.php` (đối với Staff).

---

### 2. Giao Diện Chính và Phân Quyền UI/UX

#### Bố cục chung
Giao diện được xây dựng trên cấu trúc grid của Bootstrap gồm 3 khối chính:
- **Sidebar (Thanh điều hướng bên trái)**: Hiển thị danh sách menu các chức năng phù hợp với quyền hạn của người dùng.
- **Header (Thanh tiêu đề phía trên)**: Hiển thị thông tin tài khoản đang đăng nhập, ảnh đại diện (avatar) và nút đăng xuất khỏi hệ thống.
- **Main Content (Khu vực nội dung chính)**: Render giao diện chức năng tương ứng.

#### Menu chức năng theo từng vai trò
- **Quyền Admin (Toàn quyền)**:
  - Bảng điều khiển (Dashboard): Xem biểu đồ và các con số thống kê tổng quan.
  - Quản lý nhân viên: Xem danh sách, cấp tài khoản, khóa hoặc mở khóa.
  - Quản lý danh mục: Thêm, sửa, xóa các nhóm phân loại hàng hóa.
  - Quản lý sản phẩm: Quản lý danh sách sản phẩm, giá nhập, giá bán và số lượng tồn kho.
  - Báo cáo và Phân tích: Theo dõi các biểu đồ doanh thu, chi phí và lợi nhuận.
  - Thông tin cá nhân: Cập nhật họ tên, số điện thoại, avatar và đổi mật khẩu.
- **Quyền Nhân viên (Staff - Bị hạn chế)**:
  - Bán hàng (POS): Trang bán hàng chính và là trang mặc định sau khi đăng nhập.
  - Danh sách sản phẩm: Chỉ được phép xem thông tin tên, mã vạch, giá bán và tồn kho. Ẩn toàn bộ giá nhập, không có các nút Thêm, Sửa, Xóa.
  - Quản lý khách hàng: Tra cứu thông tin và xem lịch sử mua hàng của từng khách.
  - Báo cáo cá nhân: Xem tổng doanh số bán hàng do chính mình thực hiện (không xem được lợi nhuận và doanh số của người khác).
  - Thông tin cá nhân: Cập nhật thông tin profile và đổi mật khẩu.

#### Ràng buộc UI/UX và bảo mật hệ thống
- **Chế độ Ẩn/Hiện**: Kiểm tra vai trò của phiên session. Nhân viên Staff sẽ không được hệ thống hiển thị các nút chức năng như Xóa, Sửa, các input giá nhập, menu quản lý nhân viên.
- **Bảo mật phía Server (PHP)**: Toàn bộ logic xử lý nghiệp vụ phía sau (như `delete_product.php`, `add_employee.php`) phải kiểm tra quyền truy cập. Nếu người dùng cố tình gọi API hoặc gửi request từ các công cụ bên ngoài như Postman mà không có quyền hợp lệ, server phải ngay lập tức từ chối.
- **Tương tác AJAX**: Sử dụng thư viện jQuery để thực hiện gửi nhận request bất đồng bộ khi lọc dữ liệu dashboard, giúp thay đổi biểu đồ và thông tin mà không cần tải lại toàn bộ trang web.

---

### 3. Nghiệp Vụ Quản Lý Nhân Viên

#### Quản lý danh sách
- **Hiển thị bảng tổng quan**: Bao gồm ảnh đại diện (avatar), họ tên, gmail, số điện thoại và đặc biệt phải thể hiện rõ trạng thái tài khoản: Hoạt động, Chưa kích hoạt (nhân viên mới chưa đổi mật khẩu) hoặc Bị khóa.
- **Công cụ hỗ trợ**: Bộ lọc tìm kiếm nhanh nhân viên theo họ tên hoặc số điện thoại.

#### Quy trình cấp tài khoản cho nhân viên mới
- **Bước 1**: Admin nhập họ tên và địa chỉ Gmail của nhân viên vào form.
- **Bước 2**: Server tự động sinh username từ Gmail, tạo mật khẩu tạm thời là mã số sinh viên của trưởng nhóm, đồng thời tạo ra một chuỗi token ngẫu nhiên duy nhất và lưu kèm thời gian tạo (timestamp) vào database.
- **Bước 3**: Sử dụng thư viện PHPMailer để gửi một email chứa link đăng nhập có tham số token đến địa chỉ Gmail của nhân viên.
- **Bước 4**: Khi nhân viên click vào link, hệ thống kiểm tra tính hợp lệ của token và thời gian phát sinh phải nhỏ hơn 1 phút. Nếu quá hạn, hệ thống báo lỗi và yêu cầu Admin thực hiện gửi lại.

#### Các hành động của Admin
- Xem chi tiết profile nhân viên.
- Gửi lại email kích hoạt (nếu link 1 phút cũ đã bị hết hạn).
- Khóa / Mở khóa tài khoản nhân viên (người bị khóa sẽ không thể thực hiện đăng nhập).
- Xem lịch sử bán hàng của nhân viên đó để đánh giá hiệu suất làm việc.

---

### 4. Nghiệp Vụ Quản Lý Danh Mục và Sản Phẩm

#### Quản lý danh mục
- **Thông tin quản lý**: Tên danh mục, mô tả, ngày tạo, người tạo (hệ thống tự động lấy thông tin Admin đang thực hiện).
- **Các thao tác**: Xem danh sách, thêm mới, sửa, xóa.
- **Ràng buộc xóa**: Hệ thống kiểm tra nếu danh mục vẫn còn sản phẩm bên trong, sẽ không cho phép xóa để bảo vệ toàn vẹn dữ liệu.

#### Quản lý sản phẩm
- **Thông tin sản phẩm**: Mã vạch (barcode), tên sản phẩm, giá nhập khẩu (chỉ hiển thị cho Admin), giá bán lẻ, danh mục hàng hóa, ngày tạo.
- **Thao tác**: Xem danh sách, thêm mới, cập nhật và xóa.
- **Ràng buộc về nghiệp vụ xóa sản phẩm**:
  - Chỉ được phép xóa sản phẩm nếu sản phẩm đó chưa từng phát sinh trong bất kỳ đơn hàng nào trước đây.
  - Nếu sản phẩm đã có trong hóa đơn bán hàng của khách hàng, hệ thống sẽ làm ẩn hoặc vô hiệu hóa nút xóa để tránh làm sai lệch báo cáo tài chính.

---

### 5. Nghiệp Vụ Quản Lý Khách Hàng

#### Cơ chế khởi tạo tự động (Automation)
- **Không có nút "Thêm khách hàng" thủ công**: Khách hàng không thể tự dưng được tạo ra trong hệ thống mà phải phát sinh từ giao dịch thực tế.
- **Luồng khởi tạo**: Tại trang POS bán hàng, khi nhân viên gõ số điện thoại mà hệ thống không tìm thấy trong database, nhân viên sẽ nhập thêm họ tên và địa chỉ của khách đó. Sau khi nhấn nút thanh toán đơn hàng thành công, thông tin khách hàng mới chính thức được ghi vào bảng `customers`.

#### Xem danh sách và tra cứu lịch sử
- Tra cứu nhanh khách hàng bằng tên hoặc số điện thoại ngay trên giao diện.
- Xem chi tiết khách hàng gồm: Tổng số đơn hàng đã mua, tổng số tiền đã tích lũy.
- Hiển thị bảng lịch sử giao dịch chi tiết với các thông tin: Ngày giờ mua hàng, tổng tiền hóa đơn, số tiền khách đưa, tiền trả lại và số lượng sản phẩm. Khi bấm vào từng đơn sẽ xem được chi tiết tên các sản phẩm và đơn giá tại thời điểm mua hàng đó.

---

### 6. Nghiệp Vụ POS và Xử Lý Giao Dịch

#### Thêm sản phẩm vào giỏ hàng
- **Hình thức nhập**: Nhân viên nhập từ khóa tìm kiếm tên sản phẩm hoặc sử dụng thiết bị quét barcode sản phẩm vào ô tìm kiếm.
- **Xử lý Real-time**: Khi tìm kiếm hoặc quét mã vạch ra sản phẩm đúng, sản phẩm sẽ tự động được đưa vào danh sách giỏ hàng ngay lập tức bằng cơ chế AJAX mà không cần bấm nút xác nhận hay load lại trang.

#### Giỏ hàng tạm thời (Cart)
- Mỗi dòng sản phẩm trong giỏ hàng bao gồm: Tên sản phẩm, đơn giá bán lẻ, ô cập nhật nhanh số lượng, thành tiền và nút xóa khỏi giỏ hàng.
- **Tính tự động**: Khi thay đổi số lượng hoặc xóa dòng sản phẩm, tổng số tiền và tổng số lượng sản phẩm của toàn đơn hàng phải tự động thay đổi và hiển thị đúng.

#### Thanh toán đơn hàng
- **Quy trình**: Nhân viên nhập số điện thoại khách hàng. Nếu là khách cũ, hệ thống tự động điền họ tên, địa chỉ và hiển thị thông tin. Nếu là khách mới thì nhập thông tin mới.
- **Tính toán tiền mặt**: Nhân viên nhập số tiền khách đưa. Hệ thống tự động tính toán số tiền trả lại cho khách (Tiền trả lại = Tiền khách đưa - Tổng tiền đơn hàng).
- **Hoàn tất giao dịch**: Bấm nút Thanh toán, hệ thống gửi request lưu trữ đơn hàng vào database, tạo mới thông tin khách hàng nếu là khách mới, làm trống giỏ hàng phiên session hiện tại và mở khóa một modal thông báo thanh toán thành công kèm theo link để in hóa đơn PDF được tạo bởi thư viện FPDF.

---

### 7. Nghiệp Vụ Báo Cáo và Phân Tích

#### Lọc thông tin linh hoạt
Hệ thống cho phép lọc toàn bộ doanh thu, số đơn và lợi nhuận theo các mốc thời gian:
- Hôm nay
- Hôm qua
- Trong vòng 7 ngày qua
- Tháng nay
- Khoảng thời gian tùy chọn (ví dụ từ ngày 03/07 đến 16/07).

Toàn bộ đơn hàng phải được sắp xếp theo thứ tự thời gian giảm dần (mới nhất lên đầu).

#### Hiển thị các chỉ số tài chính quan trọng
Đối với mỗi mốc thời gian được lọc, hệ thống phải làm nổi bật:
- Tổng doanh thu nhận được (doanh thu gộp từ các đơn).
- Tổng số đơn hàng đã thực hiện.
- Tổng số lượng sản phẩm đã bán.
- Danh sách các đơn hàng phát sinh chi tiết.

#### Hiệu suất và phân quyền trong báo cáo
- **Quyền Nhân viên (Staff)**: Chỉ được xem doanh thu gộp, số lượng đơn hàng và sản phẩm của chính bản thân làm ra.
- **Quyền Admin**: Xem được toàn bộ chỉ số trên của tất cả nhân viên, đồng thời được xem thêm cột Tổng lợi nhuận (công thức tính: Lợi nhuận = (Giá bán lẻ - Giá nhập) * Số lượng).
- **UI/UX**: Báo cáo được hiển thị qua cả dạng bảng liệt kê chi tiết và biểu đồ trực quan để so sánh sự tăng trưởng doanh thu qua các ngày trong tuần hoặc so sánh tỷ trọng của các danh mục sản phẩm.

---

## 📁 Cấu Trúc Dự Án

```
Demo DA21/
│
├── 📄 index.php                   # Trang đăng nhập
├── 📄 logout.php                  # Xử lý đăng xuất
├── 📄 verify-email.php            # Xác thực email kích hoạt tài khoản
├── 📄 insert_sample_data.php      # Script thêm dữ liệu mẫu
│
├── 📂 config/
│   ├── config.php                 # Cấu hình trung tâm + helper functions
│   ├── database.php               # Kết nối MySQL + query helpers
│   └── email_config.php           # Cấu hình SMTP gửi email
│
├── 📂 includes/
│   ├── auth_check.php             # Middleware kiểm tra đăng nhập
│   ├── header.php                 # Header chung (navbar top)
│   └── sidebar.php                # Sidebar menu chung
│
├── 📂 modules/
│   ├── 📂 auth/                   # Đăng nhập, đổi mật khẩu
│   ├── 📂 dashboard/              # Dashboard admin & staff
│   ├── 📂 pos/                    # Giao diện bán hàng POS
│   │   ├── pos.php                # Màn hình bán hàng chính
│   │   ├── cart_api.php           # API thêm/xóa/cập nhật giỏ hàng
│   │   ├── cart_helpers.php       # Helper tính giỏ hàng từ session
│   │   ├── checkout.php           # Xử lý thanh toán
│   │   ├── find_product.php       # API tìm kiếm sản phẩm (fuzzy)
│   │   ├── customer_lookup.php    # API tra cứu khách hàng theo SĐT
│   │   └── invoice.php / in_hoa_don.php  # Xuất hóa đơn PDF
│   ├── 📂 products/               # Quản lý sản phẩm
│   ├── 📂 categories/             # Quản lý danh mục
│   ├── 📂 employees/              # Quản lý nhân viên
│   ├── 📂 customers/              # Quản lý khách hàng
│   ├── 📂 orders/                 # Xem đơn hàng
│   ├── 📂 reports/                # Báo cáo doanh thu & lợi nhuận
│   ├── 📂 profile/                # Hồ sơ cá nhân
│   └── 📂 api/                    # API fuzzy search
│
├── 📂 assets/
│   ├── css/                       # Style sheets
│   ├── js/                        # JavaScript files
│   ├── images/                    # Ảnh hệ thống (logo, ảnh mặc định)
│   └── uploads/                   # Ảnh do người dùng upload
│
├── 📂 libs/
│   ├── fpdf/                      # Thư viện tạo PDF (hóa đơn)
│   └── phpmailer/                 # Thư viện gửi email SMTP
│
├── 📂 database/
│   └── pos_system.sql             # File SQL khởi tạo database
│
└── 📂 storage/
    └── email_logs/                # Log email đã gửi (dạng HTML)
```

---

## Hướng Dẫn Cài Đặt Local

### Yêu cầu hệ thống
- Phần mềm giả lập máy chủ Apache và MySQL (Khuyến nghị sử dụng XAMPP phiên bản 8.x trở lên).
- Phiên bản PHP đạt từ 8.0 trở lên.
- MySQL phiên bản từ 5.7 hoặc MariaDB từ 10.3 trở lên.

### Bước 1 - Đặt mã nguồn vào thư mục XAMPP
Hãy đưa toàn bộ mã nguồn dự án vào đường dẫn sau của XAMPP:
```
C:\xampp\htdocs\Demo DA21\
```

### Bước 2 - Khởi động máy chủ local
1. Mở phần mềm **XAMPP Control Panel**.
2. Nhấn nút **Start** cho cả hai dịch vụ **Apache** và **MySQL**.
3. Kiểm tra để chắc chắn cả hai dịch vụ đã chuyển sang màu xanh lá cây (Running).

### Bước 3 - Khởi tạo cơ sở dữ liệu
1. Truy cập vào trình duyệt web theo địa chỉ: `http://localhost/phpmyadmin`
2. Nhấn nút **New** ở danh sách menu bên trái để tạo database mới.
3. Nhập tên database là: `pos_system`
4. Phần bảng mã ký tự (Collation), hãy chọn: `utf8mb4_unicode_ci` sau đó nhấn **Create**.
5. Chọn database `pos_system` vừa tạo, chuyển sang tab **Import** ở thanh menu phía trên.
6. Bấm nút **Choose File** và tìm đến file nguồn SQL của dự án tại đường dẫn: `database/pos_system.sql`.
7. Kéo xuống dưới cùng và nhấp nút **Import** để thực hiện import database.

### Bước 4 - Thiết lập thông số database
Mở file `config/database.php` và điều chỉnh thông tin cho khớp với tài khoản MySQL trên máy của bạn:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Mặc định của XAMPP là để rỗng
define('DB_NAME', 'pos_system');
```

### Bước 5 - Thiết lập gửi mail xác thực (Tùy chọn)
Để chức năng gửi email xác thực cho nhân viên mới hoạt động, mở file `config/email_config.php` và điền thông tin tài khoản SMTP:
```php
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'dia_chi_gmail_cua_ban@gmail.com');
define('MAIL_PASSWORD', 'ma_mat_khau_ung_dung_gmail_cua_ban');
define('MAIL_FROM', 'dia_chi_gmail_cua_ban@gmail.com');
define('MAIL_FROM_NAME', 'POS System');
```

### Bước 6 - Khởi chạy và trải nghiệm hệ thống
Mở trình duyệt web và truy cập vào địa chỉ sau để vào trang đăng nhập:
```
http://localhost/Demo%20DA21/
```

### Bước 7 - Thông tin tài khoản đăng nhập mặc định
- **Quyền Admin**:
  - Username: `admin`
  - Password: `admin`
- **Quyền Nhân viên mới**:
  - Cần tạo mới tài khoản từ trang Admin, sau đó kiểm tra hoặc xem thư mục log để lấy link xác thực đăng nhập và thiết lập mật khẩu.

### Bước 8 - Chèn dữ liệu giả lập để test chức năng
Để hệ thống tự động nạp sẵn dữ liệu về danh mục, sản phẩm, khách hàng và hóa đơn mẫu để bạn test các biểu đồ thống kê, hãy truy cập link sau trên trình duyệt:
```
http://localhost/Demo%20DA21/insert_sample_data.php
```

---

## Sơ Đồ Cấu Trúc Dữ Liệu (Database Schema)

Dưới đây là danh sách các trường thông tin chính của hệ thống database để bạn tiện theo dõi:

```sql
-- Bảng người dùng
users (id, username, email, password_hash, full_name, role, status, must_change_password, email_token, avatar, created_at)

-- Bảng danh mục hàng hóa
categories (id, name, description, created_at)

-- Bảng sản phẩm
products (id, category_id, name, barcode, price_import, price_sell, stock, image, description, created_at)

-- Bảng khách hàng
customers (id, name, phone, address, created_at)

-- Bảng đơn hàng
orders (id, order_code, customer_id, user_id, total_amount, customer_paid, change_amount, created_at)

-- Bảng chi tiết đơn hàng
order_items (id, order_id, product_id, quantity, price_sell, price_import, subtotal)
```

---

## Luồng Hoạt Động Hệ Thống

```
[Người dùng] → [index.php - Đăng nhập]
                      ↓
              [process_login.php - Xác thực]
                      ↓
        ┌─────────────┴─────────────┐
     [Admin]                    [Staff]
        ↓                          ↓
[admin_dashboard.php]       [pos.php - Bán hàng]
        ↓
 ┌──────┼──────┬──────┬──────┬──────┐
[SX]  [DM]  [NV]  [KH]  [DH]  [BC]
Sản  Danh  Nhân  Khách  Đơn  Báo
phẩm  mục  viên  hàng  hàng cáo
```

---

## Xử Lý Sự Cố Thường Gặp

### Lỗi "Lỗi kết nối database"
- Kiểm tra MySQL đã chạy trong XAMPP chưa
- Kiểm tra lại thông tin trong `config/database.php`
- Đảm bảo database `pos_system` đã được tạo và import SQL

### Lỗi trang trắng hoặc lỗi PHP
- Mở `config/config.php`, đổi `define('DEBUG_MODE', true)` để xem lỗi chi tiết
- Kiểm tra PHP version: `http://localhost/phpinfo.php`

### Lỗi upload ảnh không được
- Kiểm tra thư mục `assets/uploads/` có tồn tại không
- Đảm bảo Apache có quyền ghi vào thư mục này (Windows: thường không cần cấu hình)

### Lỗi Email không gửi được
- Kiểm tra App Password Gmail (không dùng mật khẩu Google thường)
- Đảm bảo đã bật **2-Step Verification** trên tài khoản Gmail
- Thử đổi MAIL_PORT sang `465` và `MAIL_SMTPSECURE` sang `ssl`

### Lỗi URL bị sai / redirect lỗi
- Mở `config/config.php`, thêm biến môi trường:
  ```php
  // Hoặc set thủ công BASE_URL nếu auto-detect sai
  define('BASE_URL', 'http://localhost/Demo DA21');
  ```

---

## 👥 Tài Khoản Mặc Định

| Loại | Username | Password | Quyền |
|---|---|---|---|
| Quản trị viên | `admin` | `admin` | Toàn quyền |
| Nhân viên mới | *(do admin tạo)* | `52000148` (mặc định) | Bán hàng, đổi mật khẩu |

---

## Ghi Chú Bảo Mật Quan Trọng

> Dự án này được thiết kế cho môi trường **học tập và demo**.  
> Trước khi triển khai thực tế (production), cần thực hiện thêm:

- [ ] Đổi `DEBUG_MODE` thành `false`
- [ ] Thêm CSRF Token cho tất cả form POST
- [ ] Cấu hình HTTPS (SSL)
- [ ] Giới hạn rate-limit cho trang đăng nhập
- [ ] Thay thế tài khoản `root` MySQL bằng user có quyền hạn chế
- [ ] Backup database định kỳ

---

## 🎓 Về Dự Án

Đây là đồ án thực hành môn **Phát triển ứng dụng Web** — Demo DA21.  
Dự án được xây dựng nhằm minh họa các kỹ thuật PHP cơ bản đến nâng cao trong việc xây dựng một hệ thống quản lý thực tế.
