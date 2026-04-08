<?php
require_once __DIR__ . '/cart-functions.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$cartCount = get_cart_count();
$cartItems = [];
$cartTotal = 0;
$dataFile = __DIR__ . '/../data/products.php';
$styleVersion = file_exists(__DIR__ . '/../assets/css/style.css') ? filemtime(__DIR__ . '/../assets/css/style.css') : time();

if (file_exists($dataFile)) {
    require_once $dataFile;
    if (isset($products) && is_array($products)) {
        $cartItems = get_cart_items($products);
        $cartTotal = get_cart_total($products);
    }
}

$shouldOpenMiniCart = isset($_GET['cart_open']) && $_GET['cart_open'] === '1';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shop Lzon - Website ban mo hinh</title>
<link rel="stylesheet" href="assets/css/style.css?v=<?php echo $styleVersion; ?>">
</head>
<body data-cart-open="<?php echo $shouldOpenMiniCart ? '1' : '0'; ?>">
<div class="topbar">Summer Sale for All Swim Suits and Free Express Delivery - OFF 60%</div>
<header class="site-header">
  <div class="container nav-wrap">
    <a href="index.php" class="brand">Shop Lzon</a>
    <nav class="main-nav">
      <a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Home</a>
      <a href="index.php#flash-sale">Shop</a>
      <a href="index.php#footer">Contact</a>
      <a href="index.php#about">About</a>
      <a href="login.php" class="<?php echo $currentPage === 'login.php' ? 'active' : ''; ?>">Sign Up</a>
    </nav>
    <div class="nav-actions">
      <form class="search-box" action="index.php" method="get">
        <input type="text" name="keyword" placeholder="What are you looking for?">
        <button type="submit">⌕</button>
      </form>
      <a class="icon-btn" href="login.php" title="Tai khoan">👤</a>
      <a class="icon-btn" href="#" title="Wishlist">♡</a>
      <div class="cart-menu-wrap">
        <button class="icon-btn cart-badge cart-toggle" type="button" title="Gio hang" aria-expanded="<?php echo $shouldOpenMiniCart ? 'true' : 'false'; ?>" aria-controls="miniCartPanel">
          🛒
          <span><?php echo $cartCount; ?></span>
        </button>

        <div id="miniCartPanel" class="mini-cart-panel<?php echo $shouldOpenMiniCart ? ' is-open' : ''; ?>">
          <div class="mini-cart-head">GIO HANG</div>

          <?php if (empty($cartItems)): ?>
            <div class="mini-cart-empty">
              <div class="mini-cart-empty-icon">🛒</div>
              <p>Hien chua co san pham</p>
            </div>
          <?php else: ?>
            <div class="mini-cart-list">
              <?php foreach ($cartItems as $item): ?>
                <?php $miniProduct = $item['product']; ?>
                <div class="mini-cart-item">
                  <img src="<?php echo htmlspecialchars($miniProduct['image']); ?>" alt="<?php echo htmlspecialchars($miniProduct['name']); ?>">
                  <div class="mini-cart-meta">
                    <strong><?php echo htmlspecialchars($miniProduct['name']); ?></strong>
                    <small><?php echo htmlspecialchars($miniProduct['studio']); ?></small>
                    <span><?php echo (int) $item['quantity']; ?> x <?php echo number_format($miniProduct['price'], 0, ',', '.'); ?>d</span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="mini-cart-footer">
            <div class="mini-cart-total">
              <span>TONG TIEN:</span>
              <strong><?php echo number_format($cartTotal, 0, ',', '.'); ?>d</strong>
            </div>
            <a href="cart.php" class="mini-cart-link">XEM GIO HANG</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>
