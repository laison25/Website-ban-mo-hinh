<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$result = $conn->query('SELECT id, name, category, studio, price, old_price, stock, rating, reviews, sku, size_label, image_path, is_featured, created_at FROM products ORDER BY id DESC');
$products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="products-' . date('Ymd-His') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, ['ID', 'Ten san pham', 'Danh muc', 'Studio', 'Gia ban', 'Gia cu', 'Ton kho', 'Rating', 'Reviews', 'SKU', 'Kich thuoc', 'Anh', 'Noi bat', 'Ngay tao']);

foreach ($products as $product) {
    fputcsv($output, [
        $product['id'],
        $product['name'],
        $product['category'],
        $product['studio'],
        $product['price'],
        $product['old_price'],
        $product['stock'],
        $product['rating'],
        $product['reviews'],
        $product['sku'],
        $product['size_label'],
        $product['image_path'],
        ((int) $product['is_featured'] === 1) ? 'Co' : 'Khong',
        $product['created_at'],
    ]);
}

fclose($output);
exit;
