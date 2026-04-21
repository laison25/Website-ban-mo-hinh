<?php
require_once __DIR__ . '/includes/init.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    redirect_to('index.php');
}

if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

if (in_array($id, $_SESSION['wishlist'], true)) {
    $_SESSION['wishlist'] = array_values(array_filter(
        $_SESSION['wishlist'],
        fn($item) => (int) $item !== $id
    ));
    set_flash('success', 'Đã bỏ khỏi yêu thích.');
} else {
    $_SESSION['wishlist'][] = $id;
    $_SESSION['wishlist'] = array_values(array_unique($_SESSION['wishlist']));
    set_flash('success', 'Đã thêm vào yêu thích.');
}

$back = $_SERVER['HTTP_REFERER'] ?? url('index.php');
$back = preg_replace('/#.*$/', '', $back);
header('Location: ' . $back . '#product-' . $id);
exit;