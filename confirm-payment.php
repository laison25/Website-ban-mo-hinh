<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$orderId = (int)($_POST['order_id'] ?? 0);

if ($orderId <= 0) {
    die('Đơn hàng không hợp lệ');
}

$status = 'paid';

$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $orderId);
$stmt->execute();
$stmt->close();

set_flash('success', 'Đã xác nhận thanh toán cho đơn hàng #' . $orderId);
redirect_to('order-success.php?id=' . $orderId);