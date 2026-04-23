<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Tra cứu đơn hàng - ' . APP_NAME;
$order = null;
$items = [];
$error = '';
$form = [
    'order_id' => trim($_GET['order_id'] ?? ''),
    'phone' => trim($_GET['phone'] ?? ''),
];

if ($form['order_id'] !== '' || $form['phone'] !== '') {
    $orderId = (int) $form['order_id'];

    if ($orderId <= 0 || $form['phone'] === '') {
        $error = 'Vui lòng nhập mã đơn hàng và số điện thoại.';
    } else {
        $stmt = $conn->prepare('SELECT * FROM orders WHERE id = ? AND phone = ? LIMIT 1');
        $stmt->bind_param('is', $orderId, $form['phone']);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$order) {
            $error = 'Không tìm thấy đơn hàng phù hợp.';
        } else {
            $items = get_order_items($conn, (int) $order['id']);
        }
    }
}

$steps = [
    'pending' => 'Chờ xác nhận',
    'awaiting_payment' => 'Chờ thanh toán',
    'paid' => 'Đã thanh toán',
    'processing' => 'Đang xử lý',
    'shipping' => 'Đang giao',
    'completed' => 'Hoàn thành',
];
$currentIndex = $order ? array_search($order['status'], array_keys($steps), true) : -1;
if ($currentIndex === false) {
    $currentIndex = 0;
}

include __DIR__ . '/includes/header.php';
?>
<main class="section-space track-page">
    <div class="container narrow-box">
        <div class="section-head">
            <div>
                <div class="section-label">Tracking</div>
                <h1>Tra cứu đơn hàng</h1>
                <p class="muted">Nhập mã đơn và số điện thoại đã đặt hàng để xem trạng thái xử lý.</p>
            </div>
        </div>

        <form class="form-card track-form" method="get">
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Mã đơn hàng</label>
                    <input type="number" name="order_id" value="<?= e($form['order_id']) ?>" placeholder="Ví dụ: 12">
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" value="<?= e($form['phone']) ?>" placeholder="Số điện thoại nhận hàng">
                </div>
            </div>
            <button class="primary-btn" type="submit">Tra cứu</button>
        </form>

        <?php if ($error): ?>
            <div class="flash-message error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($order): ?>
            <section class="order-card track-result">
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

                <div class="tracking-steps">
                    <?php foreach (array_values($steps) as $index => $label): ?>
                        <div class="tracking-step <?= $index <= $currentIndex ? 'is-done' : '' ?>">
                            <span><?= $index + 1 ?></span>
                            <strong><?= e($label) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-meta-grid">
                    <div><strong>Người nhận:</strong> <?= e($order['customer_name']) ?></div>
                    <div><strong>SĐT:</strong> <?= e($order['phone']) ?></div>
                    <div><strong>Thanh toán:</strong> <?= e(payment_method_label($order['payment_method'])) ?></div>
                    <div><strong>Email:</strong> <?= e($order['customer_email']) ?></div>
                </div>

                <div class="order-items-box">
                    <?php foreach ($items as $item): ?>
                        <div class="summary-line">
                            <span><?= e($item['product_name']) ?> x <?= (int) $item['quantity'] ?></span>
                            <strong><?= format_currency((float) $item['subtotal']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
