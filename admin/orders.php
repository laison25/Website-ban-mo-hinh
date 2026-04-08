<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$pageTitle = 'Quản lý đơn hàng - ' . APP_NAME;
$result = $conn->query('SELECT * FROM orders ORDER BY id DESC');
$orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
include __DIR__ . '/../includes/header.php';
?>
<main class="section-space">
    <div class="container">
        <div class="section-head"><h1>Danh sách đơn hàng</h1><a class="outline-btn" href="<?= url('admin/index.php') ?>">Dashboard</a></div>
        <div class="table-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th>Tổng tiền</th>
                        <th>Ngày tạo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= (int) $order['id'] ?></td>
                            <td><?= e($order['customer_name']) ?></td>
                            <td><?= e($order['customer_email']) ?></td>
                            <td><?= e($order['phone']) ?></td>
                            <td><?= e($order['payment_method']) ?></td>
                            <td><span class="status-pill"><?= e($order['status']) ?></span></td>
                            <td><?= format_currency((float) $order['total_amount']) ?></td>
                            <td><?= e(format_datetime($order['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
