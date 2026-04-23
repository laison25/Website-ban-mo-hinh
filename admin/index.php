<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$pageTitle = 'Admin Dashboard - ' . APP_NAME;
$productCount = (int) (isset($conn) && $conn->query('SELECT COUNT(*) AS total FROM products') ? $conn->query('SELECT COUNT(*) AS total FROM products')->fetch_assoc()['total'] : 0);
$orderCount = (int) (isset($conn) && $conn->query('SELECT COUNT(*) AS total FROM orders') ? $conn->query('SELECT COUNT(*) AS total FROM orders')->fetch_assoc()['total'] : 0);
$userCount = (int) (isset($conn) && $conn->query('SELECT COUNT(*) AS total FROM users') ? $conn->query('SELECT COUNT(*) AS total FROM users')->fetch_assoc()['total'] : 0);
include __DIR__ . '/../includes/header.php';
?>
<main class="section-space">
    <div class="container">
        <div class="section-head"><h1>Trang quản trị</h1><a class="primary-btn" href="<?= url('admin/product-form.php') ?>">+ Thêm sản phẩm</a></div>
        <div class="stats-grid">
            <div class="stat-card"><span>Sản phẩm</span><strong><?= $productCount ?></strong></div>
            <div class="stat-card"><span>Đơn hàng</span><strong><?= $orderCount ?></strong></div>
            <div class="stat-card"><span>Người dùng</span><strong><?= $userCount ?></strong></div>
        </div>
        <div class="admin-shortcuts">
            <a class="panel-link" href="<?= url('admin/products.php') ?>">Quản lý sản phẩm</a>
            <a class="panel-link" href="<?= url('admin/orders.php') ?>">Quản lý đơn hàng</a>
            <a class="panel-link" href="<?= url('index.php') ?>">Xem website</a>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
