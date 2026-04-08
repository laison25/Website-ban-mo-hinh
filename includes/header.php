<?php
require_once __DIR__ . '/cart-functions.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$cartCount = get_cart_count();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shop Lzon - Website ban mo hinh</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
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
      <a class="icon-btn cart-badge" href="cart.php" title="Gio hang">🛒<span><?php echo $cartCount; ?></span></a>
    </div>
  </div>
</header>
