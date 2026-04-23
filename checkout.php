<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$pageTitle = 'Thanh toán - ' . APP_NAME;
$items = get_cart_items($conn);

if (empty($items)) {
    set_flash('error', 'Giỏ hàng đang trống.');
    redirect_to('cart.php');
}

$total = cart_total($conn);
$user = current_user();
$error = '';

$paymentMethods = [
    'COD' => [
        'title' => 'Thanh toán khi nhận hàng',
        'subtitle' => 'Nhận hàng, kiểm tra và thanh toán cho shipper.',
        'icon' => '🚚',
        'badge' => 'Phổ biến',
        'status' => 'pending',
    ],
    'BANK' => [
        'title' => 'Chuyển khoản ngân hàng',
        'subtitle' => 'Shop giữ đơn sau khi bạn chuyển khoản và xác nhận.',
        'icon' => '🏦',
        'badge' => 'Thủ công',
        'status' => 'awaiting_payment',
    ],
    'QR_CODE' => [
        'title' => 'VietQR',
        'subtitle' => 'Quét mã QR đúng số tiền và nội dung đơn hàng.',
        'icon' => '📱',
        'badge' => 'Nhanh',
        'status' => 'awaiting_payment',
    ],
    'MOMO' => [
        'title' => 'Ví điện tử',
        'subtitle' => 'Mô phỏng thanh toán qua ví Momo/ZaloPay.',
        'icon' => '👛',
        'badge' => 'Demo',
        'status' => 'awaiting_payment',
    ],
    'CARD' => [
        'title' => 'Thẻ ATM / Visa',
        'subtitle' => 'Mô phỏng cổng thanh toán thẻ an toàn.',
        'icon' => '💳',
        'badge' => 'Demo',
        'status' => 'awaiting_payment',
    ],
];

$form = [
    'customer_name' => $user['full_name'] ?? '',
    'customer_email' => $user['email'] ?? '',
    'phone' => '',
    'address' => '',
    'note' => '',
    'payment_method' => 'COD',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($form as $key => $value) {
        $form[$key] = trim($_POST[$key] ?? $value);
    }

    if (!isset($paymentMethods[$form['payment_method']])) {
        $error = 'Phương thức thanh toán không hợp lệ.';
    } elseif ($form['customer_name'] === '' || $form['customer_email'] === '' || $form['phone'] === '' || $form['address'] === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin nhận hàng.';
    } elseif (!filter_var($form['customer_email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không đúng định dạng.';
    } else {
        $conn->begin_transaction();
        try {
            foreach ($items as $item) {
                if ((int) $item['product']['stock'] < (int) $item['qty']) {
                    throw new Exception('Một số sản phẩm không đủ tồn kho để thanh toán.');
                }
            }

            $status = $paymentMethods[$form['payment_method']]['status'];
            $userId = (int) $user['id'];
            $stmt = $conn->prepare('INSERT INTO orders (user_id, customer_name, customer_email, phone, address, note, payment_method, status, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('isssssssd', $userId, $form['customer_name'], $form['customer_email'], $form['phone'], $form['address'], $form['note'], $form['payment_method'], $status, $total);
            $stmt->execute();
            $orderId = $stmt->insert_id;
            $stmt->close();

            $itemStmt = $conn->prepare('INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)');
            $stockStmt = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');

            foreach ($items as $item) {
                $product = $item['product'];
                $qty = (int) $item['qty'];
                $price = (float) $product['price'];
                $subtotal = (float) $item['subtotal'];
                $itemStmt->bind_param('iisdid', $orderId, $product['id'], $product['name'], $price, $qty, $subtotal);
                $itemStmt->execute();
                $stockStmt->bind_param('ii', $qty, $product['id']);
                $stockStmt->execute();
            }

            $itemStmt->close();
            $stockStmt->close();
            $conn->commit();
            unset($_SESSION['cart']);

            if ($status === 'awaiting_payment') {
                set_flash('success', 'Đơn hàng đã được tạo. Vui lòng hoàn tất thanh toán.');
                redirect_to('payment.php?id=' . $orderId);
            }

            set_flash('success', 'Đặt hàng thành công.');
            redirect_to('order-success.php?id=' . $orderId);
        } catch (Throwable $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<main class="section-space checkout-page">
    <div class="container checkout-grid">
        <section>
            <div class="section-head">
                <div>
                    <div class="section-label">Checkout</div>
                    <h1>Thông tin thanh toán</h1>
                    <p class="muted">Chọn hình thức thanh toán phù hợp, sau đó shop sẽ giữ đơn và xử lý theo trạng thái.</p>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="flash-message error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" class="form-card checkout-form" data-checkout-form>
                <div class="checkout-step">
                    <span>1</span>
                    <div>
                        <h3>Thông tin nhận hàng</h3>
                        <p>Shop dùng thông tin này để giao hàng và liên hệ xác nhận.</p>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Họ tên</label>
                        <input type="text" name="customer_name" value="<?= e($form['customer_name']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="customer_email" value="<?= e($form['customer_email']) ?>">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" value="<?= e($form['phone']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Ghi chú nhanh</label>
                        <input type="text" name="note" value="<?= e($form['note']) ?>" placeholder="Ví dụ: gọi trước khi giao">
                    </div>
                </div>

                <div class="form-group">
                    <label>Địa chỉ nhận hàng</label>
                    <textarea name="address" rows="3"><?= e($form['address']) ?></textarea>
                </div>

                <div class="checkout-step payment-step">
                    <span>2</span>
                    <div>
                        <h3>Phương thức thanh toán</h3>
                        <p>Mỗi phương thức sẽ có hướng dẫn riêng sau khi đặt hàng.</p>
                    </div>
                </div>

                <div class="payment-method-grid">
                    <?php foreach ($paymentMethods as $code => $method): ?>
                        <label class="payment-option <?= $form['payment_method'] === $code ? 'is-selected' : '' ?>" data-payment-option>
                            <input
                                type="radio"
                                name="payment_method"
                                value="<?= e($code) ?>"
                                <?= $form['payment_method'] === $code ? 'checked' : '' ?>
                            >
                            <span class="payment-option__icon"><?= e($method['icon']) ?></span>
                            <span class="payment-option__content">
                                <strong><?= e($method['title']) ?></strong>
                                <small><?= e($method['subtitle']) ?></small>
                            </span>
                            <em><?= e($method['badge']) ?></em>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="payment-detail-box" data-payment-detail="COD">
                    <h4>Thanh toán COD</h4>
                    <p>Bạn thanh toán trực tiếp khi nhận hàng. Shop sẽ gọi xác nhận đơn trước khi giao.</p>
                </div>

                <div class="payment-detail-box" data-payment-detail="BANK" hidden>
                    <h4>Chuyển khoản ngân hàng</h4>
                    <p>Sau khi bấm đặt hàng, bạn sẽ thấy thông tin tài khoản, nội dung chuyển khoản và nút xác nhận đã thanh toán.</p>
                </div>

                <div class="payment-detail-box" data-payment-detail="QR_CODE" hidden>
                    <h4>VietQR</h4>
                    <p>Hệ thống tạo mã QR đúng số tiền <strong><?= format_currency($total) ?></strong>. Bạn chỉ cần quét và chuyển khoản theo nội dung đơn hàng.</p>
                </div>

                <div class="payment-detail-box" data-payment-detail="MOMO" hidden>
                    <h4>Ví điện tử demo</h4>
                    <p>Trang tiếp theo sẽ mô phỏng màn thanh toán ví điện tử. Không thu tiền thật trong bản demo.</p>
                </div>

                <div class="payment-detail-box" data-payment-detail="CARD" hidden>
                    <h4>Thẻ ATM / Visa demo</h4>
                    <p>Trang tiếp theo sẽ mô phỏng cổng thanh toán thẻ. Không nhập hoặc lưu thông tin thẻ thật.</p>
                </div>

                <button class="primary-btn checkout-submit" type="submit">Xác nhận đặt hàng</button>
            </form>
        </section>

        <aside class="summary-card checkout-summary">
            <h3>Đơn hàng của bạn</h3>
            <div class="checkout-items">
                <?php foreach ($items as $item): ?>
                    <div class="checkout-item">
                        <img src="<?= url($item['product']['image_path']) ?>" alt="<?= e($item['product']['name']) ?>">
                        <div>
                            <strong><?= e($item['product']['name']) ?></strong>
                            <span>Số lượng: <?= (int) $item['qty'] ?></span>
                        </div>
                        <b><?= format_currency((float) $item['subtotal']) ?></b>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="summary-line"><span>Tạm tính</span><strong><?= format_currency($total) ?></strong></div>
            <div class="summary-line"><span>Vận chuyển</span><strong>Miễn phí</strong></div>
            <div class="summary-line total"><span>Tổng cộng</span><strong><?= format_currency($total) ?></strong></div>
            <div class="checkout-secure-note">🔒 Thông tin đơn hàng được xử lý an toàn trong hệ thống demo.</div>
        </aside>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('[data-checkout-form]');
    if (!form) return;

    const options = Array.from(form.querySelectorAll('[data-payment-option]'));
    const details = Array.from(form.querySelectorAll('[data-payment-detail]'));

    function updatePaymentUI() {
        const checked = form.querySelector('input[name="payment_method"]:checked');
        const value = checked ? checked.value : 'COD';

        options.forEach(function (option) {
            const input = option.querySelector('input[name="payment_method"]');
            option.classList.toggle('is-selected', input && input.value === value);
        });

        details.forEach(function (detail) {
            detail.hidden = detail.getAttribute('data-payment-detail') !== value;
        });
    }

    form.addEventListener('change', function (event) {
        if (event.target && event.target.name === 'payment_method') {
            updatePaymentUI();
        }
    });

    updatePaymentUI();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
