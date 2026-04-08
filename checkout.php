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

    if ($form['customer_name'] === '' || $form['customer_email'] === '' || $form['phone'] === '' || $form['address'] === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin nhận hàng.';
    } else {
        $conn->begin_transaction();
        try {
            foreach ($items as $item) {
                if ((int) $item['product']['stock'] < (int) $item['qty']) {
                    throw new Exception('Một số sản phẩm không đủ tồn kho để thanh toán.');
                }
            }

            $stmt = $conn->prepare('INSERT INTO orders (user_id, customer_name, customer_email, phone, address, note, payment_method, status, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $status = 'pending';
            $userId = (int) $user['id'];
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
<main class="section-space">
    <div class="container checkout-grid">
        <section>
            <div class="section-head"><h1>Billing Details</h1></div>
            <?php if ($error): ?><div class="flash-message error"><?= e($error) ?></div><?php endif; ?>
            <form method="post" class="form-card checkout-form">
                <div class="form-grid-2">
                    <div class="form-group"><label>Họ tên</label><input type="text" name="customer_name" value="<?= e($form['customer_name']) ?>"></div>
                    <div class="form-group"><label>Email</label><input type="email" name="customer_email" value="<?= e($form['customer_email']) ?>"></div>
                </div>
                <div class="form-grid-2">
                    <div class="form-group"><label>Số điện thoại</label><input type="text" name="phone" value="<?= e($form['phone']) ?>"></div>
                    <div class="form-group"><label>Phương thức thanh toán</label>
                        <select name="payment_method">
                            <option value="COD" <?= $form['payment_method'] === 'COD' ? 'selected' : '' ?>>Cash on Delivery</option>
                            <option value="BANK" <?= $form['payment_method'] === 'BANK' ? 'selected' : '' ?>>Bank Transfer</option>
                        </select>
                    </div>
                </div>
                <div class="form-group"><label>Địa chỉ nhận hàng</label><textarea name="address" rows="3"><?= e($form['address']) ?></textarea></div>
                <div class="form-group"><label>Ghi chú</label><textarea name="note" rows="3"><?= e($form['note']) ?></textarea></div>
                <button class="primary-btn" type="submit">Place Order</button>
            </form>
        </section>
        <aside class="summary-card">
            <h3>Your Order</h3>
            <?php foreach ($items as $item): ?>
                <div class="summary-line">
                    <span><?= e($item['product']['name']) ?> x <?= (int) $item['qty'] ?></span>
                    <strong><?= format_currency((float) $item['subtotal']) ?></strong>
                </div>
            <?php endforeach; ?>
            <hr>
            <div class="summary-line"><span>Shipping</span><strong>Free</strong></div>
            <div class="summary-line total"><span>Total</span><strong><?= format_currency($total) ?></strong></div>
        </aside>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
