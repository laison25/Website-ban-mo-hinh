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
            <div class="card-actions center">
                <a class="primary-btn" href="<?= url('order-history.php') ?>">Xem lịch sử đơn hàng</a>
                <a class="outline-btn" href="<?= url('index.php') ?>">Về trang chủ</a>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
