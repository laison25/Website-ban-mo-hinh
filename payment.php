<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$orderId = (int) ($_GET['id'] ?? 0);

if ($orderId <= 0) {
    set_flash('error', 'Không tìm thấy đơn hàng.');
    redirect_to('order-history.php');
}

$user = current_user();
$userId = (int) $user['id'];

if (is_admin()) {
    $stmt = $conn->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $orderId);
} else {
    $stmt = $conn->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->bind_param('ii', $orderId, $userId);
}

$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    set_flash('error', 'Đơn hàng không tồn tại hoặc bạn không có quyền truy cập.');
    redirect_to('order-history.php');
}

$paymentMethod = $order['payment_method'];
$methodTitle = payment_method_label($paymentMethod);

$paymentCode = 'DH' . (int) $order['id'] . ' ' . preg_replace('/\s+/', '', $order['customer_name']);
$qrUrl = 'https://img.vietqr.io/image/' . QR_BANK_ID . '-' . QR_ACCOUNT_NO
    . '-' . QR_TEMPLATE . '.jpg'
    . '?amount=' . (int) $order['total_amount']
    . '&addInfo=' . urlencode($paymentCode)
    . '&accountName=' . urlencode(QR_ACCOUNT_NAME);

$pageTitle = 'Thanh toán đơn hàng #' . $orderId . ' - ' . APP_NAME;
include __DIR__ . '/includes/header.php';
?>

<main class="section-space payment-page">
    <div class="container payment-layout">
        <section class="payment-panel">
            <div class="section-label">Payment</div>
            <h1>Thanh toán đơn hàng #<?= (int) $order['id'] ?></h1>
            <p class="muted">Phương thức đã chọn: <strong><?= e($methodTitle) ?></strong></p>

            <?php if ($order['status'] === 'paid'): ?>
                <div class="payment-paid-box">
                    <h2>Đơn hàng đã được xác nhận thanh toán</h2>
                    <p>Bạn có thể theo dõi đơn trong lịch sử đơn hàng.</p>
                    <a class="primary-btn" href="<?= url('order-success.php?id=' . (int) $order['id']) ?>">Xem đơn hàng</a>
                </div>
            <?php elseif ($paymentMethod === 'BANK'): ?>
                <div class="payment-method-view bank-view">
                    <div class="payment-bank-card">
                        <h2>Thông tin chuyển khoản</h2>
                        <div><span>Ngân hàng</span><strong><?= e(QR_BANK_ID) ?></strong></div>
                        <div><span>Số tài khoản</span><strong><?= e(QR_ACCOUNT_NO) ?></strong></div>
                        <div><span>Chủ tài khoản</span><strong><?= e(QR_ACCOUNT_NAME) ?></strong></div>
                        <div><span>Số tiền</span><strong><?= format_currency((float) $order['total_amount']) ?></strong></div>
                        <div><span>Nội dung</span><strong><?= e($paymentCode) ?></strong></div>
                    </div>
                    <p class="payment-hint">Sau khi chuyển khoản, bấm nút bên dưới để shop ghi nhận trạng thái thanh toán demo.</p>
                </div>
            <?php elseif ($paymentMethod === 'QR_CODE'): ?>
                <div class="payment-method-view qr-view">
                    <div class="qr-payment-wrap">
                        <img src="<?= e($qrUrl) ?>" alt="QR thanh toán đơn hàng #<?= (int) $order['id'] ?>">
                        <div>
                            <h2>Quét VietQR để thanh toán</h2>
                            <p>Số tiền: <strong><?= format_currency((float) $order['total_amount']) ?></strong></p>
                            <p>Nội dung: <code><?= e($paymentCode) ?></code></p>
                            <p class="muted">Mở app ngân hàng, quét mã và kiểm tra đúng tên tài khoản trước khi chuyển.</p>
                        </div>
                    </div>
                </div>
            <?php elseif ($paymentMethod === 'MOMO'): ?>
                <div class="payment-method-view wallet-view">
                    <div class="wallet-demo-card">
                        <span>👛</span>
                        <h2>Ví điện tử demo</h2>
                        <p>Đây là màn mô phỏng thanh toán ví điện tử cho đồ án. Không phát sinh giao dịch thật.</p>
                        <div class="wallet-pin-row">
                            <i></i><i></i><i></i><i></i><i></i><i></i>
                        </div>
                    </div>
                </div>
            <?php elseif ($paymentMethod === 'CARD'): ?>
                <div class="payment-method-view card-view">
                    <div class="card-demo-preview">
                        <span>Secure Card</span>
                        <strong>•••• •••• •••• 4242</strong>
                        <small>DEMO PAYMENT</small>
                    </div>
                    <div class="card-demo-copy">
                        <h2>Cổng thẻ demo</h2>
                        <p>Không nhập hoặc lưu thông tin thẻ thật. Bấm xác nhận để mô phỏng giao dịch thành công.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="payment-method-view cod-view">
                    <h2>Thanh toán khi nhận hàng</h2>
                    <p>Đơn hàng sẽ được shop xác nhận và giao đến địa chỉ của bạn. Bạn thanh toán trực tiếp cho shipper.</p>
                </div>
            <?php endif; ?>

            <?php if ($order['status'] !== 'paid' && $paymentMethod !== 'COD'): ?>
                <div class="payment-actions">
                    <form action="<?= url('confirm-payment.php') ?>" method="post">
                        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                        <button type="submit" class="primary-btn">Tôi đã thanh toán</button>
                    </form>
                    <a href="<?= url('order-success.php?id=' . (int) $order['id']) ?>" class="outline-btn">Thanh toán sau</a>
                </div>
            <?php else: ?>
                <div class="payment-actions">
                    <a href="<?= url('order-success.php?id=' . (int) $order['id']) ?>" class="primary-btn">Xem đơn hàng</a>
                    <a href="<?= url('index.php#products') ?>" class="outline-btn">Tiếp tục mua sắm</a>
                </div>
            <?php endif; ?>
        </section>

        <aside class="summary-card payment-summary-card">
            <h3>Tóm tắt đơn hàng</h3>
            <div class="summary-line"><span>Khách hàng</span><strong><?= e($order['customer_name']) ?></strong></div>
            <div class="summary-line"><span>Email</span><strong><?= e($order['customer_email']) ?></strong></div>
            <div class="summary-line"><span>SĐT</span><strong><?= e($order['phone']) ?></strong></div>
            <div class="summary-line"><span>Trạng thái</span><strong><?= e(order_status_label($order['status'])) ?></strong></div>
            <div class="summary-line total"><span>Tổng tiền</span><strong><?= format_currency((float) $order['total_amount']) ?></strong></div>
            <p class="muted">Địa chỉ: <?= e($order['address']) ?></p>
        </aside>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
