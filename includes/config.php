<?php
const APP_NAME = 'Website Bán Mô Hình';

// ── Tự nhận diện môi trường local / hosting ───────────────────────────────
$serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
$isLocal = in_array($serverName, ['localhost', '127.0.0.1']);

// APP_URL
if ($isLocal) {
    define('APP_URL', 'http://localhost/website-ban-mo-hinh-php-v3/');
} else {
    define('APP_URL', 'http://modelshop-laison.rf.gd/');
}

// DB Config
if ($isLocal) {
    define('DB_HOST', '127.0.0.1');
    define('DB_PORT', 3306); // nếu XAMPP máy bạn dùng 3306 thì đổi lại 3306
    define('DB_NAME', 'website_ban_mo_hinh');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    define('DB_HOST', 'sql309.infinityfree.com');
    define('DB_PORT', 3306);
    define('DB_NAME', 'if0_41732375_shoplaison');
    define('DB_USER', 'if0_41732375');
    define('DB_PASS', 'hDLmaZFgGpZLo');
}

// ── Cấu hình Thanh toán QR Code (VietQR) ──────────────────────────────────
const QR_BANK_ID      = 'MB';
const QR_ACCOUNT_NO   = '5519052005';
const QR_ACCOUNT_NAME = 'NGUYEN VAN PHUONG';
const QR_TEMPLATE     = 'compact2';

// ── Cấu hình đăng nhập Google/Facebook ───────────────────────────────────
// Demo mode: bấm đăng nhập social thì tạo/tự đăng nhập tài khoản mẫu.
// Khi có Client ID/Secret thật, đổi false và điền bên dưới.
const SOCIAL_LOGIN_DEMO_MODE = true;

