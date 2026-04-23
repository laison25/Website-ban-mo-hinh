<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'Giỏ hàng - ' . APP_NAME;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['apply_coupon'])) {
        $code = normalize_coupon_code($_POST['coupon_code'] ?? '');
        $coupons = available_coupons();

        if ($code === '' || !isset($coupons[$code])) {
            unset($_SESSION['coupon_code']);
            set_flash('error', 'Mã giảm giá không hợp lệ.');
        } else {
            $_SESSION['coupon_code'] = $code;
            set_flash('success', 'Đã áp dụng mã giảm giá ' . $code . '.');
        }
        redirect_to('cart.php');
    }

    if (isset($_POST['remove_coupon'])) {
        unset($_SESSION['coupon_code']);
        set_flash('success', 'Đã bỏ mã giảm giá.');
        redirect_to('cart.php');
    }

    if (isset($_POST['update_cart']) && isset($_POST['qty'])) {
        foreach ($_POST['qty'] as $productId => $qty) {
            $productId = (int) $productId;
            $qty = max(0, (int) $qty);
            if ($qty === 0) {
                unset($_SESSION['cart'][$productId]);
            } else {
                $_SESSION['cart'][$productId] = $qty;
            }
        }
        set_flash('success', 'Đã cập nhật giỏ hàng.');
        redirect_to('cart.php');
    }
}

if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][(int) $_GET['remove']]);
    set_flash('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    redirect_to('cart.php');
}

$items = get_cart_items($conn);
$subtotal = cart_total($conn);
$coupon = get_applied_coupon();
$discount = calculate_coupon_discount($subtotal, $coupon);
$total = max(0, $subtotal - $discount);
include __DIR__ . '/includes/header.php';
?>
<main class="section-space">
    <div class="container">
        <div class="section-head"><h1>Giỏ hàng</h1><a class="outline-btn" href="<?= url('index.php') ?>">Tiếp tục mua</a></div>
        <?php if (empty($items)): ?>
            <div class="empty-box">Giỏ hàng đang trống.</div>
        <?php else: ?>
            <form method="post" class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): $product = $item['product']; ?>
                            <tr>
                                <td>
                                    <div class="table-product">
                                        <img src="<?= url($product['image_path']) ?>" alt="<?= e($product['name']) ?>">
                                        <div>
                                            <strong><?= e($product['name']) ?></strong>
                                            <div class="muted"><?= e($product['studio']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= format_currency((float) $product['price']) ?></td>
                                <td><input class="qty-field" type="number" min="0" name="qty[<?= (int) $product['id'] ?>]" value="<?= (int) $item['qty'] ?>"></td>
                                <td><?= format_currency((float) $item['subtotal']) ?></td>
                                <td><a class="text-danger" href="<?= url('cart.php?remove=' . $product['id']) ?>">Xóa</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="cart-actions">
                    <button class="outline-btn" type="submit" name="update_cart" value="1">Cập nhật giỏ hàng</button>
                    <div class="total-box">
                        <strong>Tổng cộng: <?= format_currency($total) ?></strong>
                        <a class="primary-btn" href="<?= url('checkout.php') ?>">Tiến hành thanh toán</a>
                    </div>
                </div>
            </form>

            <div class="cart-extra-grid">
                <form method="post" class="coupon-card">
                    <h3>Mã giảm giá</h3>
                    <p class="muted">Thử mã: <strong>LZON10</strong>, <strong>FIGURE500</strong>, <strong>FREESHIP</strong></p>
                    <div class="coupon-form-row">
                        <input type="text" name="coupon_code" value="<?= e($coupon['code'] ?? '') ?>" placeholder="Nhập mã giảm giá">
                        <button class="outline-btn" type="submit" name="apply_coupon" value="1">Áp dụng</button>
                    </div>
                    <?php if ($coupon): ?>
                        <button class="text-danger coupon-remove" type="submit" name="remove_coupon" value="1">Bỏ mã <?= e($coupon['code']) ?></button>
                    <?php endif; ?>
                </form>

                <div class="summary-card cart-summary-card">
                    <h3>Tóm tắt thanh toán</h3>
                    <div class="summary-line"><span>Tạm tính</span><strong><?= format_currency($subtotal) ?></strong></div>
                    <?php if ($discount > 0): ?>
                        <div class="summary-line discount-line"><span>Giảm giá <?= e($coupon['code'] ?? '') ?></span><strong>-<?= format_currency($discount) ?></strong></div>
                    <?php endif; ?>
                    <div class="summary-line total"><span>Cần thanh toán</span><strong><?= format_currency($total) ?></strong></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
