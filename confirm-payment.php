<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$orderId = (int) ($_POST['order_id'] ?? 0);

if ($orderId <= 0) {
    set_flash('error', 'Đơn hàng không hợp lệ.');
    redirect_to('order-history.php');
}

$userId = (int) current_user()['id'];

if (is_admin()) {
    $stmt = $conn->prepare('SELECT id FROM orders WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $orderId);
} else {
    $stmt = $conn->prepare('SELECT id FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->bind_param('ii', $orderId, $userId);
}

$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    set_flash('error', 'Không tìm thấy đơn hàng hoặc bạn không có quyền xác nhận.');
    redirect_to('order-history.php');
}

$status = 'paid';
$stmt = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
$stmt->bind_param('si', $status, $orderId);
$stmt->execute();
$stmt->close();

set_flash('success', 'Đã xác nhận thanh toán cho đơn hàng #' . $orderId);
if (is_admin()) {
    redirect_to('admin/orders.php');
}
redirect_to('order-success.php?id=' . $orderId);
