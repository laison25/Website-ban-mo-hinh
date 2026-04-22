<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('index.php');
}

$productId = (int) ($_POST['product_id'] ?? 0);
$qty = max(1, (int) ($_POST['qty'] ?? 1));
$product = get_product($conn, $productId);

if (!$product) {
    set_flash('error', 'Sản phẩm không tồn tại.');
    redirect_to('index.php');
}

$currentQty = (int) ($_SESSION['cart'][$productId] ?? 0);
$newQty = min($currentQty + $qty, max(1, (int) $product['stock']));
$_SESSION['cart'][$productId] = $newQty;

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $count = array_sum($_SESSION['cart'] ?? []);
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'cart_count' => $count, 'name' => $product['name']]);
    exit;
}

set_flash('success', 'Đã thêm sản phẩm vào giỏ hàng.');
$back = $_SERVER['HTTP_REFERER'] ?? url('index.php');
$back = preg_replace('/#.*$/', '', $back);
$back .= '#product-' . $productId;
header('Location: ' . $back);
exit;
