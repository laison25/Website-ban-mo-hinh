<?php
$flash = get_flash();
$user = current_user();
$currentPath = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<div class="topbar">Summer Sale for all Swim Suits and Free Express Delivery - OFF 50% <span>ShopNow</span></div>

<header class="site-header">
    <div class="container nav-wrap">

        <a class="logo" href="<?= url('index.php') ?>">Lzon Poke</a>

        <nav class="nav-menu">
            <a href="<?= url('index.php') ?>" class="<?= $currentPath === 'index.php' || $currentPath === '' ? 'active' : '' ?>">Home</a>
            <a href="<?= url('index.php#products') ?>">Products</a>
            <a href="<?= url('cart.php') ?>" class="<?= $currentPath === 'cart.php' ? 'active' : '' ?>">Cart</a>

            <?php if ($user && !is_admin()): ?>
                <a href="<?= url('order-history.php') ?>" class="<?= $currentPath === 'order-history.php' ? 'active' : '' ?>">My Orders</a>
            <?php endif; ?>

            <?php if (is_admin()): ?>
                <a href="<?= url('admin/index.php') ?>">Admin</a>
            <?php endif; ?>
        </nav>

        <div class="nav-actions">

            <!-- SEARCH + SUGGEST -->
            <div class="search-suggest-wrap" style="position:relative;">
                <form action="<?= url('index.php') ?>" method="get" class="search-form" autocomplete="off">
                    <input
                        type="text"
                        id="searchBox"
                        name="keyword"
                        placeholder="What are you looking for?"
                        value="<?= e($_GET['keyword'] ?? '') ?>"
                    >
                    <button type="submit" aria-label="Tìm kiếm">🔎</button>
                </form>

                <!-- BOX GỢI Ý -->
                <div id="searchSuggestions" style="
                    position:absolute;
                    top:110%;
                    left:0;
                    width:100%;
                    background:#fff;
                    border:1px solid #ddd;
                    display:none;
                    z-index:999;
                "></div>
            </div>

            <!-- CART -->
            <a class="icon-link" href="<?= url('cart.php') ?>">
                🛒 <span class="counter"><?= cart_count() ?></span>
            </a>

            <!-- USER -->
            <?php if ($user): ?>
                <div class="user-chip">
                    <div>
                        <strong><?= e($user['full_name']) ?></strong>
                        <small><?= is_admin() ? 'Admin' : 'Customer' ?></small>
                    </div>
                    <a href="<?= url('logout.php') ?>">Logout</a>
                </div>
            <?php else: ?>
                <a class="login-link" href="<?= url('login.php') ?>">Login</a>
                <a class="primary-btn small-nav-btn" href="<?= url('register.php') ?>">Sign Up</a>
            <?php endif; ?>

        </div>
    </div>
</header>

<?php if ($flash): ?>
    <div class="container">
        <div class="flash-message <?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
    </div>
<?php endif; ?>