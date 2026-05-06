<?php
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_OFF);

$dbHost = getenv('APP_DB_HOST') ?: DB_HOST;
$dbPort = getenv('APP_DB_PORT') ?: DB_PORT;
$dbName = getenv('APP_DB_NAME') ?: DB_NAME;
$dbUser = getenv('APP_DB_USER') ?: DB_USER;
$dbPass = getenv('APP_DB_PASS') ?: DB_PASS;

$conn = @new mysqli($dbHost, $dbUser, $dbPass, $dbName, (int) $dbPort);

if ($conn->connect_error) {
    http_response_code(500);
    echo '<!doctype html><html lang="vi"><head><meta charset="utf-8"><title>Lỗi kết nối CSDL</title>';
    echo '<style>body{font-family:Arial,sans-serif;padding:32px;line-height:1.6}code{background:#f4f4f4;padding:2px 6px;border-radius:4px}</style>';
    echo '</head><body>';
    echo '<h1>Không thể kết nối MySQL</h1>';
    echo '<p>Hãy kiểm tra lại file <code>includes/config.php</code> và import file <code>database/website_ban_mo_hinh.sql</code> vào phpMyAdmin.</p>';
    echo '<p><strong>Chi tiết lỗi:</strong> ' . htmlspecialchars($conn->connect_error) . '</p>';
    echo '</body></html>';
    exit;
}

$conn->set_charset('utf8mb4');
