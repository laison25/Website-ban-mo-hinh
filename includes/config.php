<?php
const APP_NAME = 'Website Bán Mô Hình';
const APP_URL  = 'http://localhost:8888/Website-ban-mo-hinh/';

const DB_HOST = '127.0.0.1';
const DB_PORT = 3307;
const DB_NAME = 'website_ban_mo_hinh';
const DB_USER = 'root';

const DB_PASS = '';
// ── Cấu hình Thanh toán QR Code (VietQR) ──────────────────────────────────
// ⚠️ Thay bằng thông tin tài khoản ngân hàng thật của bạn
const QR_BANK_ID      = 'MB';           // Mã NH: MB, VCB, TCB, BIDV, ACB, VPB...
const QR_ACCOUNT_NO   = '5519052005';   // Số tài khoản nhận tiền
const QR_ACCOUNT_NAME = 'NGUYEN VAN PHUONG'; // Tên chủ TK (IN HOA, không dấu)
const QR_TEMPLATE     = 'compact2';     // compact | compact2 | qr_only

