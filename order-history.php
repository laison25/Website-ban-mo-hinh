<?php
require_once __DIR__ . '/includes/init.php';
require_login();
if (is_admin()) {
    redirect_to('admin/orders.php');
}
$pageTitle = 'Lịch sử đơn hàng - ' . APP_NAME;
$orders = get_user_orders($conn, (int) current_user()['id']);
include __DIR__ . '/includes/header.php';
?>
<main class="section-space">
    <div class="container">
        <div class="section-head">
            <h1>Lịch sử đơn hàng</h1>
            <a class="outline-btn" href="<?= url('index.php') ?>">Tiếp tục mua sắm</a>
        </div>
        <?php if (empty($orders)): ?>
            <div class="empty-box">Bạn chưa có đơn hàng nào.</div>
        <?php else: ?>
            <div class="order-history-list">
                <?php foreach ($orders as $order): ?>
                    <article class="order-card">
                        <div class="order-card-head">
                            <div>
                                <h3>Đơn hàng #<?= (int) $order['id'] ?></h3>
                                <p>Ngày đặt: <?= e(format_datetime($order['created_at'])) ?></p>
                            </div>
                            <div class="order-status-group">
                                <span class="status-pill"><?= e(order_status_label($order['status'])) ?></span>
                                <strong><?= format_currency((float) $order['total_amount']) ?></strong>
                            </div>
                        </div>
                        <div class="order-meta-grid">
                            <div><strong>Người nhận:</strong> <?= e($order['customer_name']) ?></div>
                            <div><strong>Email:</strong> <?= e($order['customer_email']) ?></div>
                            <div><strong>SĐT:</strong> <?= e($order['phone']) ?></div>
                            <div><strong>Thanh toán:</strong> <?= e(payment_method_label($order['payment_method'])) ?></div>
                        </div>
                        <div class="order-items-box">
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="summary-line">
                                    <span><?= e($item['product_name']) ?> x <?= (int) $item['quantity'] ?></span>
                                    <strong><?= format_currency((float) $item['subtotal']) ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
