<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$pageTitle = 'Admin Dashboard - ' . APP_NAME;

$productCount = 0;
$orderCount = 0;
$userCount = 0;
$revenueTotal = 0;
$monthlyStats = [];
$statusStats = [];

$productResult = $conn->query('SELECT COUNT(*) AS total FROM products');
if ($productResult) {
    $productCount = (int) $productResult->fetch_assoc()['total'];
}

$orderResult = $conn->query('SELECT COUNT(*) AS total FROM orders');
if ($orderResult) {
    $orderCount = (int) $orderResult->fetch_assoc()['total'];
}

$userResult = $conn->query('SELECT COUNT(*) AS total FROM users');
if ($userResult) {
    $userCount = (int) $userResult->fetch_assoc()['total'];
}

$revenueResult = $conn->query("SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders WHERE status <> 'cancelled'");
if ($revenueResult) {
    $revenueTotal = (float) $revenueResult->fetch_assoc()['total'];
}

$monthlyResult = $conn->query("
    SELECT DATE_FORMAT(created_at, '%m/%Y') AS label, COUNT(*) AS order_count, COALESCE(SUM(total_amount), 0) AS revenue
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY YEAR(created_at), MONTH(created_at)
");
if ($monthlyResult) {
    $monthlyStats = $monthlyResult->fetch_all(MYSQLI_ASSOC);
}

$statusResult = $conn->query('SELECT status, COUNT(*) AS total FROM orders GROUP BY status ORDER BY total DESC');
if ($statusResult) {
    $statusStats = $statusResult->fetch_all(MYSQLI_ASSOC);
}

$maxRevenue = 1;
foreach ($monthlyStats as $row) {
    $maxRevenue = max($maxRevenue, (float) $row['revenue']);
}

include __DIR__ . '/../includes/header.php';
?>
<main class="section-space">
    <div class="container">
        <div class="section-head">
            <h1>Trang quản trị</h1>
            <div class="card-actions">
                <a class="outline-btn" href="<?= url('admin/export-orders.php') ?>">Xuất đơn hàng CSV</a>
                <a class="primary-btn" href="<?= url('admin/product-form.php') ?>">+ Thêm sản phẩm</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><span>Sản phẩm</span><strong><?= $productCount ?></strong></div>
            <div class="stat-card"><span>Đơn hàng</span><strong><?= $orderCount ?></strong></div>
            <div class="stat-card"><span>Người dùng</span><strong><?= $userCount ?></strong></div>
            <div class="stat-card"><span>Doanh thu</span><strong><?= format_currency($revenueTotal) ?></strong></div>
        </div>

        <div class="admin-analytics-grid">
            <section class="analytics-panel">
                <div class="panel-title-row">
                    <div>
                        <span class="section-label">Thống kê</span>
                        <h2>Doanh thu 6 tháng gần đây</h2>
                    </div>
                    <a class="outline-btn small" href="<?= url('admin/export-orders.php') ?>">Xuất CSV</a>
                </div>
                <?php if (empty($monthlyStats)): ?>
                    <p class="muted">Chưa có dữ liệu đơn hàng để vẽ biểu đồ.</p>
                <?php else: ?>
                    <div class="revenue-chart" aria-label="Biểu đồ doanh thu theo tháng">
                        <?php foreach ($monthlyStats as $row): ?>
                            <?php $height = max(8, round(((float) $row['revenue'] / $maxRevenue) * 100)); ?>
                            <div class="chart-column">
                                <div class="chart-value"><?= format_currency((float) $row['revenue']) ?></div>
                                <div class="chart-bar-wrap">
                                    <span class="chart-bar" style="height: <?= (int) $height ?>%"></span>
                                </div>
                                <strong><?= e($row['label']) ?></strong>
                                <small><?= (int) $row['order_count'] ?> đơn</small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="analytics-panel">
                <div class="panel-title-row">
                    <div>
                        <span class="section-label">Đơn hàng</span>
                        <h2>Trạng thái xử lý</h2>
                    </div>
                    <a class="outline-btn small" href="<?= url('admin/orders.php') ?>">Xem chi tiết</a>
                </div>
                <div class="status-stat-list">
                    <?php if (empty($statusStats)): ?>
                        <p class="muted">Chưa có đơn hàng.</p>
                    <?php else: ?>
                        <?php foreach ($statusStats as $row): ?>
                            <div class="status-stat-row">
                                <span><?= e(order_status_label($row['status'])) ?></span>
                                <strong><?= (int) $row['total'] ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <div class="admin-shortcuts">
            <a class="panel-link" href="<?= url('admin/products.php') ?>">Quản lý sản phẩm</a>
            <a class="panel-link" href="<?= url('admin/orders.php') ?>">Quản lý đơn hàng</a>
            <a class="panel-link" href="<?= url('index.php') ?>">Xem website</a>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
