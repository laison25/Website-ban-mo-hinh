<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect_to('admin/products.php');
}
$product = get_product($conn, $id);
if ($product) {
    delete_uploaded_image($product['image_path']);
}
$stmt = $conn->prepare('DELETE FROM products WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();
set_flash('success', 'Đã xóa sản phẩm.');
redirect_to('admin/products.php');
