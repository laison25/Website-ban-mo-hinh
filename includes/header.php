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
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body>
<div class="topbar">Miễn phí giao hàng cho đơn từ 5.000.000đ <span>Nhận tư vấn sưu tầm ngay</span></div>

<header class="site-header">
    <div class="container nav-wrap">

        <a class="logo" href="<?= url('index.php') ?>">Lzon Poke</a>

        <nav class="nav-menu">
            <a href="<?= url('index.php') ?>" class="<?= $currentPath === 'index.php' || $currentPath === '' ? 'active' : '' ?>">Trang chủ</a>
            <a href="<?= url('index.php#products') ?>">Sản phẩm</a>
            <a href="<?= url('cart.php') ?>" class="<?= $currentPath === 'cart.php' ? 'active' : '' ?>">Giỏ hàng</a>
            <a href="<?= url('wishlist.php') ?>" class="<?= $currentPath === 'wishlist.php' ? 'active' : '' ?>">Yêu thích</a>
            <a href="<?= url('track-order.php') ?>" class="<?= $currentPath === 'track-order.php' ? 'active' : '' ?>">Tra cứu</a>

            <?php if ($user && !is_admin()): ?>
                <a href="<?= url('order-history.php') ?>" class="<?= $currentPath === 'order-history.php' ? 'active' : '' ?>">Đơn hàng</a>
            <?php endif; ?>

            <?php if (is_admin()): ?>
                <a href="<?= url('admin/index.php') ?>">Admin</a>
            <?php endif; ?>
        </nav>

        <div class="nav-actions">

            <div class="search-suggest-wrap">
                <form action="<?= url('index.php') ?>" method="get" class="search-form" autocomplete="off">
                    <input
                        type="text"
                        id="searchBox"
                        name="keyword"
                        placeholder="Tìm figure, studio, dòng sản phẩm..."
                        value="<?= e($_GET['keyword'] ?? '') ?>"
                    >
                    <button type="submit" aria-label="Tìm kiếm">🔎</button>
                </form>

                <div id="searchSuggestions"></div>
            </div>

            <a class="icon-link cart-link" href="<?= url('cart.php') ?>">
                <span class="cart-icon">🛒</span>
                <?php if (cart_count() > 0): ?>
                    <span class="cart-counter"><?= cart_count() ?></span>
                <?php endif; ?>
            </a>

            <?php if ($user): ?>
                <div class="header-user-box">
                    <span class="header-user-avatar"><?= e(strtoupper(substr($user['full_name'], 0, 1))) ?></span>
                    <div class="header-user-info">
                        <strong><?= e($user['full_name']) ?></strong>
                        <span><?= is_admin() ? 'Quản trị' : 'Khách hàng' ?></span>
                    </div>
                    <div class="header-user-actions">
                        <a class="header-account-btn" href="<?= url('account-settings.php') ?>">Cài đặt</a>
                        <a class="header-logout-btn" href="<?= url('logout.php') ?>">Đăng xuất</a>
                    </div>
                </div>
            <?php else: ?>
                <a class="login-link" href="<?= url('login.php') ?>">Đăng nhập</a>
                <a class="primary-btn small-nav-btn" href="<?= url('register.php') ?>">Đăng ký</a>
            <?php endif; ?>

        </div>
    </div>
</header>

<?php if ($flash): ?>
    <div class="flash-toast <?= e($flash['type']) ?>" id="flashToast">
        <span><?= e($flash['message']) ?></span>
        <button type="button" class="flash-close" onclick="document.getElementById('flashToast').style.display='none'">×</button>
    </div>
<?php endif; ?>
