<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$pageTitle = 'Quản lý đơn hàng - ' . APP_NAME;
$status = trim($_GET['status'] ?? '');
$allowedStatuses = ['', 'pending', 'awaiting_payment', 'paid', 'processing', 'shipping', 'completed', 'cancelled'];
if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}

if ($status !== '') {
    $stmt = $conn->prepare('SELECT * FROM orders WHERE status = ? ORDER BY id DESC');
    $stmt->bind_param('s', $status);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $result = $conn->query('SELECT * FROM orders ORDER BY id DESC');
    $orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

include __DIR__ . '/../includes/header.php';
?>
<main class="section-space admin-orders-page">
    <div class="container">
        <div class="section-head">
            <div>
                <div class="section-label">Admin</div>
                <h1>Quản lý đơn hàng</h1>
                <p class="muted">Theo dõi đơn, phương thức thanh toán và trạng thái xử lý.</p>
            </div>
            <div class="card-actions">
                <a class="outline-btn" href="<?= url('admin/export-orders.php' . ($status !== '' ? '?status=' . urlencode($status) : '')) ?>">Xuất đơn hàng CSV</a>
                <a class="outline-btn" href="<?= url('admin/index.php') ?>">Dashboard</a>
            </div>
        </div>

        <form class="admin-filter-bar" method="get">
            <select name="status">
                <option value="">Tất cả trạng thái</option>
                <?php foreach (array_filter($allowedStatuses) as $item): ?>
                    <option value="<?= e($item) ?>" <?= $status === $item ? 'selected' : '' ?>><?= e(order_status_label($item)) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="primary-btn" type="submit">Lọc đơn</button>
            <a class="outline-btn" href="<?= url('admin/orders.php') ?>">Xóa lọc</a>
        </form>

        <div class="table-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Liên hệ</th>
                        <th>Tổng tiền</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="8">Chưa có đơn hàng nào.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= (int) $order['id'] ?></td>
                                <td>
                                    <strong><?= e($order['customer_name']) ?></strong>
                                    <div class="muted"><?= e($order['customer_email']) ?></div>
                                </td>
                                <td><?= e($order['phone']) ?></td>
                                <td><strong><?= format_currency((float) $order['total_amount']) ?></strong></td>
                                <td><?= e(payment_method_label($order['payment_method'])) ?></td>
                                <td><span class="status-pill"><?= e(order_status_label($order['status'])) ?></span></td>
                                <td><?= e(format_datetime($order['created_at'])) ?></td>
                                <td>
                                    <a class="outline-btn small" href="<?= url('payment.php?id=' . (int) $order['id']) ?>">Xem</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
