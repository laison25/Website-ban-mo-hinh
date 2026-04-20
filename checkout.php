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
                                <option value="COD"  <?= $form['payment_method'] === 'COD'      ? 'selected' : '' ?>>Thanh toán khi nhận hàng (COD)</option>
                                <option value="BANK" <?= $form['payment_method'] === 'BANK'     ? 'selected' : '' ?>>Chuyển khoản ngân hàng</option>
                                <option value="QR_CODE" <?= $form['payment_method'] === 'QR_CODE' ? 'selected' : '' ?>>Quét mã QR Code</option>
                        </select>
                    </div>
                </div>
                <div class="form-group"><label>Địa chỉ nhận hàng</label><textarea name="address" rows="3"><?= e($form['address']) ?></textarea></div>
                <div class="form-group"><label>Ghi chú</label><textarea name="note" rows="3"><?= e($form['note']) ?></textarea></div>
                <!-- Hộp QR hiện khi chọn QR_CODE -->
                <div id="qr-preview-box" style="display:none; margin-bottom:1.5rem; padding:1.25rem; background:#fffbf0; border:2px dashed #e9b96e; border-radius:12px; text-align:center;">
                    <p style="font-weight:600; margin-bottom:.75rem; color:#333;">📱 Quét mã QR để chuyển khoản</p>
                    <img id="qr-preview-img" src="" alt="QR thanh toán"
                         style="width:220px; height:auto; border-radius:8px; box-shadow:0 2px 12px rgba(0,0,0,.15);">
                    <p style="margin-top:.75rem; font-size:.85rem; color:#555;">
                        Ngân hàng: <strong><?= QR_BANK_ID ?></strong> &nbsp;|&nbsp;
                        STK: <strong><?= QR_ACCOUNT_NO ?></strong><br>
                        Tên TK: <strong><?= QR_ACCOUNT_NAME ?></strong>
                    </p>
                    <p style="font-size:.8rem; color:#999; margin-top:.4rem;">
                        Sau khi chuyển khoản xong, nhấn <em>Đặt hàng</em> để xác nhận.
                    </p>
                </div>

                <button class="primary-btn" type="submit">Đặt hàng</button>
            </form>

            <script>
            (function () {
                var sel   = document.querySelector('select[name="payment_method"]');
                var box   = document.getElementById('qr-preview-box');
                var img   = document.getElementById('qr-preview-img');
                var total = <?= json_encode((float) $total) ?>;

                function buildQrUrl(amount) {
                    var bank = <?= json_encode(QR_BANK_ID) ?>;
                    var acct = <?= json_encode(QR_ACCOUNT_NO) ?>;
                    var tpl  = <?= json_encode(QR_TEMPLATE) ?>;
                    var name = <?= json_encode(QR_ACCOUNT_NAME) ?>;
                    return 'https://img.vietqr.io/image/' + bank + '-' + acct + '-' + tpl
                         + '.jpg?amount=' + Math.round(amount)
                         + '&addInfo=' + encodeURIComponent('Thanh toan don hang')
                         + '&accountName=' + encodeURIComponent(name);
                }

                function update() {
                    if (sel.value === 'QR_CODE') {
                        img.src = buildQrUrl(total);
                        box.style.display = 'block';
                    } else {
                        box.style.display = 'none';
                    }
                }

                sel.addEventListener('change', update);
                update(); // chạy lần đầu khi load trang
            })();
            </script>
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
