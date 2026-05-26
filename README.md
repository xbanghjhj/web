# POS System - He Thong Quan Ly Ban Hang Dien Thoai va Phu Kien

He thong POS (Point of Sale) hoan chinh danh cho cua hang dien thoai va phu kien. Duoc xay dung bang PHP thuan va MySQL, khong phu thuoc vao cac framework lon nhu Laravel hay Symfony.

---

## Du An Giai Quyet Van De Gi

Cac cua hang kinh doanh dien thoai va phu kien quy mo nho den trung binh thuong gap nhieu kho khan trong van hanh thuc te:

- Ban hang thu con, de xay ra nham lan ve gia ca va ton kho.
- Khong theo doi sat sao duoc doanh thu, chi phi va loi nhuan theo thoi gian thuc.
- Kho khan trong viec phan quyen cho nhan vien ban hang va quan tri vien, dan den rui ro ve du lieu gia nhap.
- Mat thong tin khach hang, thieu co so de cham soc khach hang than thiet va theo doi lich su mua hang.
- Hoa don ban hang khong chuyen nghiep va thieu minh bach.
- Quy trinh cap tai khoan cho nhan vien moi con thu cong, thieu tinh nang xac thuc qua email an toan.

He thong nay cung cap giai phap so hoa toan dien cho cac nghiep vu tren.

---

## Cong Nghe Su Dung

### Backend
- PHP 8.1+ (PHP thuan): Xu ly logic phia may chu.
- MySQL / MariaDB: Luu tru co so du lieu quan he.
- MySQLi (Prepared Statements): Thuc thi truy van an toan, ngan ngua triet de loi bao mat SQL Injection.
- PHP Sessions: Duy tri trang thai dang nhap va phan quyen phien lam viec.
- PHPMailer: Thu vien gui thu dien tu qua giao thuc SMTP phuc vu cho quy trinh xac thuc nhan vien.
- FPDF: Thu vien ket xuat tai lieu dang PDF ho tro in hoa don thanh toan.

### Frontend
- Bootstrap 5.3: Xay dung layout responsive va cac components UI co san.
- jQuery 3.7: Ho tro tuong tac DOM va goi cac API AJAX khong reload trang.
- Chart.js 4.4: Hien thi bieu do doanh thu va loi nhuan truc quan.
- Font Awesome 6.5: Bo sung thu vien bieu tuong hien thi.
- Vanilla CSS: Dinh nghia cac phong cach rieng biet cho giao dien dang nhap va dashboard.

---

## Tai Lieu Dac Ta Nghiep Vu va Yeu Cau Chuc Nang

Duoi day la dac ta chi tiet cac yeu cau nghiep vu duoc trich xuat truc tiep tu cac yeu cau thiet ke cua du an.

### 1. Nghiep Vu Dang Nhap va Truy Cap He Thong

#### Quyen truy cap va duong dan xac thuc
- Nhan vien cu va Admin: Duoc phep truy cap truc tiep thong qua trang dang nhap he thong.
- Nhan vien moi: Bat buoc phai dang nhap thong qua lien ket (link) kich hoat gui trong email xac nhuc ma Admin cap phat.
- Rang buoc bao mat: Neu nhan vien moi co tinh truy cap truc tiep vao trang login ma chua click vao link tu email, he thong se chan va thong bao: "Vui long dang nhap bang cach nhap vao lien ket trong email cua ban".
- Hieu luc cua lien ket: Link xac nhan duoc gui qua email chi co gia tri su dung trong vong 1 phut ke tu thoi diem gui. Qua 1 phut, lien ket se het han va he thong yeu cau lien he Admin de gui lai.

#### Quy tac dinh danh tai khoan
- Username: Duoc lay tu phan tien to (prefix) dung truoc ky tu @ cua dia chi email da dang ky. Vi du, neu email la nguyenvana@gmail.com, he thong tu dong phan tich va tao username la nguyenvana.
- Tai khoan quan tri: Username mac dinh cua Admin luon la admin.
- Password:
  - Admin: Mat khau mac dinh ban dau la admin.
  - Nhan vien moi: Mat khau tam thoi duoc thiet lap chinh la Ma so sinh vien cua truong nhom (viet thuong) de dam bao tinh xac thuc cua ma nguon tu phat trien.
  - Nhan vien cu: Su dung mat khau ca nhan da duoc thay doi truoc do.

#### Luong xu ly sau khi xac thuc thanh cong
- Truong hop Dang nhap lan dau (Nhan vien moi):
  - He thong bat buoc phai chuyen huong nguoi dung den trang Doi mat khau ngay lap tuc.
  - Nguoi dung khong can nhap mat khau cu trong form nay (vi dang su dung mat khau tam).
  - Rang buoc nghiem ngat: Khi chua hoan thanh viec doi mat khau lan dau, nhan vien do se khong the truy cap bat ky chuc nang nao khac (nhu ban hang, san pham...). Moi nhat cu co tinh truy cap vao URL chuc nang se bi he thong chuyen huong tro lai trang doi mat khau hoac thuc hien dang xuat.
- Truong hop tai khoan bi khoa (Locked):
  - He thong tu choi dang nhap va hien thi thong bao ro rang ve tinh trang tai khoan dang bi khoa boi Admin.
- Truong hop tai khoan hoat dong binh thuong:
  - He thong kiem tra vai tro va chuyen huong ve admin_dashboard.php (doi voi Admin) hoac pos.php (doi voi Staff).

---

### 2. Giao Dien Chinh va Phan Quyen UI/UX

#### Bo cuc chung
Giao dien duoc xay dung tren cau truc grid cua Bootstrap gom 3 khoi chinh:
- Sidebar (Thanh dieu huong ben trai): Hien thi danh sach menu cac chuc nang phu hop voi quyen han cua nguoi dung.
- Header (Thanh tieu de phia tren): Hien thi thong tin tai khoan dang dang nhap, anh dai dien (avatar) va nut dang xuat khoi he thong.
- Main Content (Khu vuc noi dung chinh): Render giao dien chuc nang tuong ung.

#### Menu chuc nang theo tung vai tro
- Quyen Admin (Toan quyen):
  - Bang dieu khien (Dashboard): Xem bieu do va cac con so thong ke tong quan.
  - Quan ly nhan vien: Xem danh sach, cap tai khoan, khoa hoac mo khoa.
  - Quan ly danh muc: Them, sua, xoa cac nhom phan loai hang hoa.
  - Quan ly san pham: Quan ly danh sach san pham, gia nhap, gia ban va so luong ton kho.
  - Bao cao va Phan tich: Theo doi cac bieu do doanh thu, chi phi va loi nhuan.
  - Thong tin ca nhan: Cap nhat ho ten, so dien thoai, avatar va doi mat khau.
- Quyen Nhan vien (Staff - Biet han che):
  - Ban hang (POS): Trang ban hang chinh va la trang mac dinh sau khi dang nhap.
  - Danh sach san pham: Chi duoc phep xem thong tin ten, ma vach, gia ban va ton kho. An toan bo gia nhap, khong co cac nut Them, Sua, Xoa.
  - Quan ly khach hang: Tra cuu thong tin va xem lich su mua hang cua tung khach.
  - Bao cao ca nhan: Xem tong doanh so ban hang do chinh minh thuc hien (khong xem duoc loi nhuan va doanh so cua nguoi khac).
  - Thong tin ca nhan: Cap nhat thong tin profile va doi mat khau.

#### Rang buoc UI/UX va bao mat he thong
- Che do An/Hien: Kiem tra vai tro cua phien session. Nhan vien Staff se khong duoc he thong hien thi cac nut chuc nang nhu Xoa, Sua, cac input gia nhap, menu quan ly nhan vien.
- Bao mat phia Server (PHP): Toan bo logic xu ly nghiep vu phia sau (nhu delete_product.php, add_employee.php) phai kiem tra quyen truy cap. Neu nguoi dung co tinh goi API hoac goi request tu cac cong cu ben ngoai nhu Postman ma khong co quyen hop le, server phai ngay lap tuc tu choi.
- Tuong tac AJAX: Su dung thu vien jQuery de thuc hien gui nhan request bat dong bo khi loc du lieu dashboard, giup thay doi bieu do va thong tin ma khong can tai lai toan bo trang web.

---

### 3. Nghiep Vu Quan Ly Nhan Vien

#### Quan ly danh sach
- Hien thi bang tong quan: Bao gom anh dai dien (avatar), ho ten, gmail, so dien thoai va dac biet phai the hien ro trang thai tai khoan: Hoat dong, Chua kich hoat (nhan vien moi chua doi mat khau) hoac Bi khoa.
- Cong cu ho tro: Bo loc tim kiem nhanh nhan vien theo ho ten hoac so dien thoai.

#### Quy trinh cap tai khoan cho nhan vien moi
- Buoc 1: Admin nhap ho ten va dia chi Gmail cua nhan vien vao form.
- Buoc 2: Server tu dong sinh username tu Gmail, tao mat khau tam thoi la ma so sinh vien cua truong nhom, dong thoi tao ra mot chuoi token ngau nhien duy nhat va luu kem thoi gian tao (timestamp) vao database.
- Buoc 3: Su dung thu vien PHPMailer de gui mot email chua link dang nhap co tham so token den dia chi Gmail cua nhan vien.
- Buoc 4: Khi nhan vien click vao link, he thong kiem tra tinh hop le cua token va thoi gian phat sinh phai nho hon 1 phut. Neu qua han, he thong bao loi va yeu cau Admin thuc hien gui lai.

#### Cac hanh dong cua Admin
- Xem chi tiet profile nhan vien.
- Gui lai email kich hoat (neu link 1 phut cu da bi het han).
- Khoa / Mo khoa tai khoan nhan vien (nguoi bi khoa se khong the thuc hien dang nhap).
- Xem lich su ban hang cua nhan vien do de danh gia hieu suat lam viec.

---

### 4. Nghiep Vu Quan Ly Danh Muc va San Pham

#### Quan ly danh muc
- Thong tin quan ly: Ten danh mục, mo ta, ngay tao, nguoi tao (he thong tu dong lay thong tin Admin dang thuc hien).
- Cac thao tac: Xem danh sach, them moi, sua, xoa.
- Rang buoc xoa: He thong kiem tra neu danh muc van con san pham ben trong, se khong cho phep xoa de bao ve toan ven du lieu.

#### Quan ly san pham
- Thong tin san pham: Ma vach (barcode), ten san pham, gia nhap khau (chi hien thi cho Admin), gia ban le, danh muc hang hoa, ngay tao.
- Thao tac: Xem danh sach, them moi, cap nhat va xoa.
- Rang buoc ve nghiep vu xoa san pham:
  - Chi duoc phep xoa san pham neu san pham do chua tung phat sinh trong bat ky don hang nao truoc day.
  - Neu san pham da co trong hoa don ban hang cua khach hang, he thong se lam an hoac vo hieu hoa nut xoa de tranh lam sai lech bao cao tai chinh.

---

### 5. Nghiep Vu Quan Ly Khach Hang

#### Co che khoi tao tu dong (Automation)
- Khong co nut "Them khach hang" thu cong: Khach hang khong the tu dung duoc tao ra trong he thong ma phai phat sinh tu giao dich thuc te.
- Luong khoi tao: Tai trang POS ban hang, khi nhan vien go so dien thoai ma he thong khong tim thay trong database, nhan vien se nhap them ho ten va dia chi cua khach do. Sau khi nhan nut thanh toan don hang thanh cong, thong tin khach hang moi chinh thuc duoc ghi vao bang customers.

#### Xem danh sach va tra cuu lich su
- Tra cuu nhanh khach hang bang ten hoac so dien thoai ngay tren giao dien.
- Xem chi tiet khach hang gom: Tong so don hang da mua, tong so tien da tich luy.
- Hien thi bang lich su giao dich chi tiet voi cac thong tin: Ngay gio mua hang, tong tien hoa don, so tien khach dua, tien tra lai va so luong san pham. Khi bam vao tung don se xem duoc chi tiet ten cac san pham va don gia tai thoi diem mua hang do.

---

### 6. Nghiep Vu POS va Xu Ly Giao Dich

#### Them san pham vao gio hang
- Hinh thuc nhap: Nhan vien nhap tu khoa tim kiem ten san pham hoac su dung thiet bi quet barcode san pham vao o tim kiem.
- Xu ly Real-time: Khi tim kiem hoac quet ma vach ra san pham dung, san pham se tu dong duoc dua vao danh sach gio hang ngay lap tuc bang co che AJAX ma khong can bam nut xac nhan hay load lai trang.

#### Gio hang tam thoi (Cart)
- Moi dong san pham trong gio hang bao gom: Ten san pham, don gia ban le, o cap nhat nhanh so luong, thanh tien va nut xoa khoi gio hang.
- Tinh tu dong: Khi thay doi so luong hoac xoa dong san pham, tong so tien va tong so luong san pham cua toan don hang phai tu dong thay doi va hien thi dung.

#### Thanh toan don hang
- Quy trinh: Nhan vien nhap so dien thoai khach hang. Neu la khach cu, he thong tu dong dien ho ten, dia chi va hien thi thong tin. Neu la khach moi thi nhap thong tin moi.
- Tinh toan tien mat: Nhan vien nhap so tien khach dua. He thong tu dong tinh toan so tien tra lai cho khach (Tien tra lai = Tien khach dua - Tong tien don hang).
- Hoan tat giao dich: Bam nut Thanh toan, he thong gui request luu tru don hang vao database, tao moi thong tin khach hang neu la khach moi, lam trong gio hang phien session hien tai va mo khoa mot modal thong bao thanh toan thanh cong kem theo link de in hoa don PDF duoc tao boi thu vien FPDF.

---

### 7. Nghiep Vu Bao Cao va Phan Tich

#### Loc thong tin linh hoat
He thong cho phep loc toan bo doanh thu, so don va loi nhuan theo cac moc thoi gian:
- Hom nay
- Hom qua
- Trong vong 7 ngay qua
- Thang nay
- Khoang thoi gian tuy chon (vi du tu ngay 03/07 den 16/07).
Toan bo don hang phai duoc sap xep theo thu tu thoi gian giam dan (moi nhat len dau).

#### Hien thi cac chi so tai chinh quan trong
Doi voi moi moc thoi gian duoc loc, he thong phai lam noi bat:
- Tong doanh thu nhan duoc (doanh thu gop tu cac don).
- Tong so don hang da thuc hien.
- Tong so luong san pham da ban.
- Danh sach cac don hang phat sinh chi tiet.

#### Hieu suat va phan quyen trong bao cao
- Quyen Nhan vien (Staff): Chi duoc xem doanh thu gop, so luong don hang va san pham cua chinh ban than lam ra.
- Quyen Admin: Xem duoc toan bo chi so tren cua tat ca nhan vien, dong thoi duoc xem them cot Tong loi nhuan (cong thuc tinh: Loi nhuan = (Gia ban le - Gia nhap) * So luong).
- UI/UX: Bao cao duoc hien thi qua ca dang bang liet ke chi tiet va bieu do truc quan de so sanh su tang truong doanh thu qua cac ngay trong tuan hoac so sanh ti trong cua cac danh muc san pham.

---

## Huong Dan Cai Dat Local

### Yeu cau he thong
- Phan mem gia lap may chu Apache va MySQL (Khuyen nghi su dung XAMPP phien ban 8.x tro len).
- Phien ban PHP dat tu 8.0 tro len.
- MySQL phien ban tu 5.7 hoac MariaDB tu 10.3 tro len.

### Buoc 1 - Dat ma nguon vao thu muc XAMPP
Hay dua toan bo ma nguon du an vao duong dan sau cua XAMPP:
```
C:\xampp\htdocs\Demo DA21\
```

### Buoc 2 - Khoi dong may chu local
1. Mo phan mem **XAMPP Control Panel**.
2. Nhap nut **Start** cho ca hai dich vu **Apache** va **MySQL**.
3. Kiem tra de chac chan ca hai dich vu da chuyen sang mau xanh la cay (Running).

### Buoc 3 - Khoi tao co so du lieu
1. Truy cap vao trinh duyet web theo dia chi: `http://localhost/phpmyadmin`
2. Nhap nut **New** o danh sach menu ben trai de tao database moi.
3. Nhap ten database la: `pos_system`
4. Phan bang ma ky tu (Collation), hay chon: `utf8mb4_unicode_ci` sau do nhan **Create**.
5. Chon database `pos_system` vua tao, chuyen sang tab **Import** o thanh menu phia tren.
6. Bam nut **Choose File** va tim den file nguon SQL cua du an tai duong dan: `database/pos_system.sql`.
7. Keo xuong duoi cung va nhap nut **Import** de thuc hien import database.

### Buoc 4 - Thiet lap thong so database
Mo file `config/database.php` va dieu chinh thong tin cho khop voi tai khoan MySQL tren may cua ban:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Mac dinh cua XAMPP la de rong
define('DB_NAME', 'pos_system');
```

### Buoc 5 - Thiet lap gui mail xac thuc (Tuy chon)
De chuc nang gui email xac thuc cho nhan vien moi hoat dong, mo file `config/email_config.php` va dien thong tin tai khoan SMTP:
```php
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'dia_chi_gmail_cua_ban@gmail.com');
define('MAIL_PASSWORD', 'ma_mat_khau_ung_dung_gmail_cua_ban');
define('MAIL_FROM', 'dia_chi_gmail_cua_ban@gmail.com');
define('MAIL_FROM_NAME', 'POS System');
```

### Buoc 6 - Khoi chay va trai nghiem he thong
Mo trinh duyet web va truy cap vao dia chi sau de vao trang dang nhap:
```
http://localhost/Demo%20DA21/
```

### Buoc 7 - Thong tin tai khoan dang nhap mac dinh
- Quyen Admin:
  - Username: `admin`
  - Password: `admin`
- Quyen Nhan vien moi:
  - Can tao moi tai khoan tu trang Admin, sau do kiem tra hoac xem thu muc log de lay link xac thuc dang nhap va thiet lap mat khau.

### Buoc 8 - Chen du lieu gia lap de test chuc nang
De he thong tu dong nap san du lieu ve danh muc, san pham, khach hang va hoa don mau de ban test cac bieu do thong ke, hay truy cap link sau tren trinh duyet:
```
http://localhost/Demo%20DA21/insert_sample_data.php
```

---

## So Do Cau Truc Du Lieu (Database Schema)

Duoi day la danh sach cac truong thong tin chinh cua he thong database de ban tien theo doi:

```sql
-- Bang nguoi dung
users (id, username, email, password_hash, full_name, role, status, must_change_password, email_token, avatar, created_at)

-- Bang danh muc hang hoa
categories (id, name, description, created_at)

-- Bang san pham
products (id, category_id, name, barcode, price_import, price_sell, stock, image, description, created_at)

-- Bang khach hang
customers (id, name, phone, address, created_at)

-- Bang don hang
orders (id, order_code, customer_id, user_id, total_amount, customer_paid, change_amount, created_at)

-- Bang chi tiet don hang
order_items (id, order_id, product_id, quantity, price_sell, price_import, subtotal)
```

---

## Luu Y Bao Mat Truoc Khi Trien Khai Thuc Te
- Chuyen hang so `DEBUG_MODE` ve gia tri `false` trong file `config/config.php` de tranh de lo thong tin loi he thong cho nguoi dung.
- Them token CSRF vao cac form nhap lieu de phong chong tan cong CSRF.
- Thiet lap cau hinh SSL (HTTPS) cho web server de ma hoa thong tin phien session cookie.
- Thay the tai khoan root mac dinh cua MySQL bang mot tai khoan gioi han quyen thao tac chi tren database `pos_system`.
