# Bo sung cho BCCD mon Ma nguon mo

## Xuat file CSV

- `admin/export-products.php`: xuat danh sach san pham.
- `admin/export-orders.php`: xuat danh sach don hang.
- Nut xuat CSV nam trong trang quan ly san pham, trang quan ly don hang va dashboard admin.

## Bieu do thong ke admin

- Dashboard admin hien tong doanh thu, so san pham, so don hang va so nguoi dung.
- Bieu do cot hien doanh thu 6 thang gan day.
- Bang thong ke trang thai don hang giup trinh bay phan quan tri ro hon.

## Docker

- `Dockerfile`: dong goi source PHP Apache.
- `docker-compose.yml`: chay 3 dich vu `web`, `db`, `phpmyadmin`.
- `DEPLOY_DOCKER.md`: huong dan chay va noi dung can trinh bay khi bao cao.

## Box chat AI

- `ai-chat.php`: endpoint nhan cau hoi cua khach hang va tra loi dang JSON.
- Neu hosting co bien moi truong `GEMINI_API_KEY` hoac file `includes/gemini-config.php`, box chat goi Gemini API free tier de tu van theo du lieu san pham.
- Neu hosting co bien moi truong `OPENAI_API_KEY` hoac file `includes/openai-config.php`, box chat co the goi OpenAI Responses API.
- Neu chua co API key, box chat van co che do tu van cuc bo dua tren MySQL: loc san pham theo keyword, ngan sach, ton kho, ship va thanh toan.
- Giao dien chat duoc gan trong `includes/footer.php`, xu ly gui/nhan tin trong `assets/js/main.js`.
