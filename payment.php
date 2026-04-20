<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$orderId = (int)($_GET['id'] ?? 0);

if ($orderId <= 0) {
    die('Không tìm thấy đơn hàng');
}

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die('Đơn hàng không tồn tại');
}

$pageTitle = 'Thanh toán đơn hàng #' . $orderId . ' - ' . APP_NAME;
include __DIR__ . '/includes/header.php';
?>

<main class="section-space">
    <div class="container" style="max-width: 800px;">
        <div class="section-head">
            <h1>Thanh toán đơn hàng #<?= (int)$order['id'] ?></h1>
        </div>

        <div class="form-card" style="margin-bottom: 20px;">
            <p><strong>Khách hàng:</strong> <?= e($order['customer_name']) ?></p>
            <p><strong>Email:</strong> <?= e($order['customer_email']) ?></p>
            <p><strong>SĐT:</strong> <?= e($order['phone']) ?></p>
            <p><strong>Địa chỉ:</strong> <?= e($order['address']) ?></p>
            <p><strong>Tổng tiền:</strong> <?= format_currency((float)$order['total_amount']) ?></p>
            <p><strong>Phương thức:</strong> <?= e($order['payment_method']) ?></p>
            <p><strong>Trạng thái:</strong> <?= e($order['status']) ?></p>
        </div>

        <div class="form-card" style="background:#fff8e8; border:1px solid #f0d58a;">
            <h3>Thông tin chuyển khoản demo</h3>
            <p><strong>Ngân hàng:</strong> MB Bank</p>
            <p><strong>Số tài khoản:</strong> 123456789999</p>
            <p><strong>Chủ tài khoản:</strong> LZON POKE DEMO</p>
            <p><strong>Nội dung chuyển khoản:</strong> THANHTOAN<?= (int)$order['id'] ?></p>

            <div style="margin-top:20px;">
                <form action="confirm-payment.php" method="post" style="display:inline-block;">
                    <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                    <button type="submit" class="primary-btn">Tôi đã thanh toán</button>
                </form>

                <a href="<?= url('order-success.php?id=' . (int)$order['id']) ?>" class="outline-btn" style="margin-left:10px;">
                    Thanh toán sau
                </a>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>