<?php
require_once __DIR__ . '/includes/init.php';

header('Content-Type: application/json; charset=utf-8');

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID sản phẩm không hợp lệ.'
    ]);
    exit;
}

if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

if (in_array($id, $_SESSION['wishlist'], true)) {
    $_SESSION['wishlist'] = array_values(array_filter(
        $_SESSION['wishlist'],
        fn($item) => (int) $item !== $id
    ));

    echo json_encode([
        'success' => true,
        'action' => 'removed',
        'message' => 'Đã bỏ khỏi yêu thích.'
    ]);
    exit;
}

$_SESSION['wishlist'][] = $id;
$_SESSION['wishlist'] = array_values(array_unique($_SESSION['wishlist']));

echo json_encode([
    'success' => true,
    'action' => 'added',
    'message' => 'Đã thêm vào yêu thích.'
]);
exit;