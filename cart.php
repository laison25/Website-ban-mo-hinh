<?php
require_once __DIR__ . '/data/products.php';
require_once __DIR__ . '/includes/cart-functions.php';

function redirect_back(string $fallback, array $params = []): void
{
    $target = $fallback;
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    if ($referer !== '') {
        $parts = parse_url($referer);
        $path = $parts['path'] ?? '';
        if ($path !== '') {
            $target = basename($path);
            if (!empty($parts['query'])) {
                parse_str($parts['query'], $query);
                unset($query['cart_open'], $query['added'], $query['updated'], $query['removed'], $query['checkout']);
                $params = array_merge($query, $params);
            }
        }
    }

    $queryString = http_build_query($params);
    if ($queryString !== '') {
        $target .= '?' . $queryString;
    }

    header('Location: ' . $target);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

    if ($action === 'add') {
        $quantity = isset($_POST['quantity']) ? max(1, (int) $_POST['quantity']) : 1;

        if (isset($products[$productId])) {
            add_to_cart($productId, $quantity);
        }

        redirect_back('cart.php', ['cart_open' => 1, 'added' => 1]);
    }

    if ($action === 'update') {
        $quantity = isset($_POST['quantity']) ? max(0, (int) $_POST['quantity']) : 0;
        update_cart_quantity($productId, $quantity);
        redirect_back('cart.php', ['updated' => 1]);
    }

    if ($action === 'remove') {
        remove_from_cart($productId);
        redirect_back('cart.php', ['removed' => 1]);
    }

    if ($action === 'checkout') {
        clear_cart();
        redirect_back('cart.php', ['checkout' => 1]);
    }
}

$cartItems = get_cart_items($products);
$subtotal = array_sum(array_column($cartItems, 'line_total'));
$shipping = 0;
$total = $subtotal + $shipping;
$notice = '';

if (isset($_GET['added'])) {
    $notice = 'San pham da duoc them vao gio hang.';
} elseif (isset($_GET['updated'])) {
    $notice = 'So luong da duoc cap nhat.';
} elseif (isset($_GET['removed'])) {
    $notice = 'San pham da duoc xoa khoi gio hang.';
} elseif (isset($_GET['checkout'])) {
    $notice = 'Thong tin gio hang da san sang de thanh toan.';
}

include __DIR__ . '/includes/header.php';
?>
<main class="section-space">
  <div class="container cart-page-wrap">
    <div class="cart-head">
      <div>
        <div class="section-label">Cart</div>
        <h1 class="cart-title">Your Shopping Cart</h1>
      </div>
      <a href="index.php#flash-sale" class="outline-btn">Continue Shopping</a>
    </div>

    <?php if ($notice !== ''): ?>
      <div class="alert-box success-box"><?php echo htmlspecialchars($notice); ?></div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
      <div class="empty-box cart-empty">Gio hang cua ban dang trong. Hay them san pham roi quay lai day de thanh toan.</div>
    <?php else: ?>
      <div class="cart-layout">
        <section class="cart-card">
          <div class="cart-table">
            <div class="cart-row cart-row-head">
              <span>Product</span>
              <span>Price</span>
              <span>Quantity</span>
              <span>Total</span>
            </div>

            <?php foreach ($cartItems as $item): ?>
              <?php $product = $item['product']; ?>
              <form method="post" action="cart.php" class="cart-row">
                <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                <div class="cart-product">
                  <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                  <div>
                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                    <small><?php echo htmlspecialchars($product['studio']); ?></small>
                  </div>
                </div>
                <span><?php echo number_format($product['price'], 0, ',', '.'); ?>d</span>
                <label class="cart-qty">
                  <input type="number" name="quantity" value="<?php echo (int) $item['quantity']; ?>" min="0">
                </label>
                <div class="cart-total-cell">
                  <strong><?php echo number_format($item['line_total'], 0, ',', '.'); ?>d</strong>
                  <div class="row-actions">
                    <button type="submit" name="action" value="update" class="outline-btn row-btn">Update</button>
                    <button type="submit" name="action" value="remove" class="cart-remove-btn">Remove</button>
                  </div>
                </div>
              </form>
            <?php endforeach; ?>
          </div>
        </section>

        <aside class="summary-card">
          <h2>Order Summary</h2>
          <div class="summary-line">
            <span>Subtotal</span>
            <strong><?php echo number_format($subtotal, 0, ',', '.'); ?>d</strong>
          </div>
          <div class="summary-line">
            <span>Shipping</span>
            <strong>Free</strong>
          </div>
          <div class="summary-line total-line">
            <span>Total</span>
            <strong><?php echo number_format($total, 0, ',', '.'); ?>d</strong>
          </div>
          <form method="post" action="cart.php">
            <input type="hidden" name="action" value="checkout">
            <button type="submit" class="primary-btn checkout-btn">Thanh toan</button>
          </form>
          <p class="summary-note">Khi bam vao icon gio hang tren header, danh sach san pham da them se duoc hien ra o day de ban xem lai truoc khi thanh toan.</p>
        </aside>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
