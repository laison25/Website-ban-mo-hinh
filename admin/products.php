<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$pageTitle = 'Quản lý sản phẩm - ' . APP_NAME;
$result = $conn->query('SELECT * FROM products ORDER BY id DESC');
$products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
include __DIR__ . '/../includes/header.php';
?>
<main class="section-space">
    <div class="container">
        <div class="section-head">
            <h1>Quản lý sản phẩm</h1>
            <div class="card-actions">
                <a class="outline-btn" href="<?= url('admin/index.php') ?>">Dashboard</a>
                <a class="outline-btn" href="<?= url('admin/export-products.php') ?>">Xuất sản phẩm CSV</a>
                <a class="primary-btn" href="<?= url('admin/product-form.php') ?>">+ Thêm sản phẩm</a>
            </div>
        </div>
        <div class="table-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= (int) $product['id'] ?></td>
                            <td><img class="admin-thumb" src="<?= url($product['image_path']) ?>" alt="<?= e($product['name']) ?>"></td>
                            <td><?= e($product['name']) ?></td>
                            <td><?= e($product['category']) ?></td>
                            <td><?= format_currency((float) $product['price']) ?></td>
                            <td><?= (int) $product['stock'] ?></td>
                            <td>
                                <div class="card-actions">
                                    <a class="outline-btn small" href="<?= url('admin/product-form.php?id=' . $product['id']) ?>">Sửa</a>
                                    <a class="danger-btn small" href="<?= url('admin/product-delete.php?id=' . $product['id']) ?>" onclick="return confirm('Xóa sản phẩm này?')">Xóa</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
