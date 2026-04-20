<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$orderId = (int) ($_GET['id'] ?? 0);
$stmt = $conn->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
$userId = (int) current_user()['id'];
$stmt->bind_param('ii', $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    set_flash('error', 'Không tìm thấy đơn hàng.');
    redirect_to('index.php');
}

$pageTitle = 'Đặt hàng thành công - ' . APP_NAME;
include __DIR__ . '/includes/header.php';
?>
<main class="section-space">
    <div class="container narrow-box">
        <div class="success-card">
            <div class="big-icon">✅</div>
            <h1>Đặt hàng thành công</h1>
           <p>Mã đơn hàng của bạn là <strong>#<?= (int) $order['id'] ?></strong>.</p>
            <p>Tổng tiền: <strong><?= format_currency((float) $order['total_amount']) ?></strong></p>

            <?php if ($order['payment_method'] === 'QR_CODE'): ?>
            <div style="margin:1.5rem 0; padding:1.5rem; background:#fffbf0; border:2px dashed #e9b96e; border-radius:12px; text-align:center;">
                <p style="font-weight:700; color:#c0392b; margin-bottom:1rem;">
                    ⚠️ Đơn hàng chờ thanh toán — vui lòng quét QR bên dưới
                </p>
                <?php
                    $qrAmount = (int) $order['total_amount'];
                    $qrInfo   = urlencode('DH' . $order['id'] . ' ' . $order['customer_name']);
                    $qrUrl    = 'https://img.vietqr.io/image/' . QR_BANK_ID . '-' . QR_ACCOUNT_NO
                              . '-' . QR_TEMPLATE . '.jpg'
                              . '?amount='      . $qrAmount
                              . '&addInfo='     . $qrInfo
                              . '&accountName=' . urlencode(QR_ACCOUNT_NAME);
                ?>
                <img src="<?= htmlspecialchars($qrUrl) ?>" alt="QR thanh toán"
                     style="width:240px; height:auto; border-radius:8px; box-shadow:0 2px 14px rgba(0,0,0,.15);">
                <p style="margin-top:.75rem; font-size:.9rem; color:#555;">
                    Ngân hàng: <strong><?= QR_BANK_ID ?></strong> &nbsp;|&nbsp;
                    STK: <strong><?= QR_ACCOUNT_NO ?></strong><br>
                    Tên: <strong><?= QR_ACCOUNT_NAME ?></strong> &nbsp;|&nbsp;
                    Số tiền: <strong><?= format_currency((float) $order['total_amount']) ?></strong>
                </p>
                <p style="font-size:.82rem; color:#888; margin-top:.4rem;">
                    Nội dung chuyển khoản: <code style="background:#eee; padding:2px 6px; border-radius:4px;">DH<?= (int)$order['id'] ?> <?= htmlspecialchars($order['customer_name']) ?></code>
                </p>
            </div>
            <?php endif; ?>

            <div class="card-actions center">
                <a class="primary-btn" href="<?= url('order-history.php') ?>">Xem lịch sử đơn hàng</a>
                <a class="outline-btn" href="<?= url('index.php') ?>">Về trang chủ</a>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
