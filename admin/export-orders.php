<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$status = trim($_GET['status'] ?? '');
$allowedStatuses = ['', 'pending', 'awaiting_payment', 'paid', 'processing', 'shipping', 'completed', 'cancelled'];
if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}

if ($status !== '') {
    $stmt = $conn->prepare('
        SELECT o.*, COUNT(oi.id) AS item_count
        FROM orders o
        LEFT JOIN order_items oi ON oi.order_id = o.id
        WHERE o.status = ?
        GROUP BY o.id
        ORDER BY o.id DESC
    ');
    $stmt->bind_param('s', $status);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $result = $conn->query('
        SELECT o.*, COUNT(oi.id) AS item_count
        FROM orders o
        LEFT JOIN order_items oi ON oi.order_id = o.id
        GROUP BY o.id
        ORDER BY o.id DESC
    ');
    $orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="orders-' . date('Ymd-His') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, ['ID', 'Khach hang', 'Email', 'Dien thoai', 'Dia chi', 'Ghi chu', 'Tong tien', 'Thanh toan', 'Trang thai', 'So dong san pham', 'Ngay tao']);

foreach ($orders as $order) {
    fputcsv($output, [
        $order['id'],
        $order['customer_name'],
        $order['customer_email'],
        $order['phone'],
        $order['address'],
        $order['note'],
        $order['total_amount'],
        payment_method_label($order['payment_method']),
        order_status_label($order['status']),
        $order['item_count'],
        $order['created_at'],
    ]);
}

fclose($output);
exit;
