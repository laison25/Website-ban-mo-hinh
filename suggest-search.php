<?php
require_once __DIR__ . '/includes/init.php';

header('Content-Type: application/json; charset=utf-8');

$keyword = trim($_GET['keyword'] ?? '');

if ($keyword === '') {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id, name, price, image_path
        FROM products
        WHERE name LIKE ?
        ORDER BY name ASC
        LIMIT 6";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([]);
    exit;
}

$search = "%" . $keyword . "%";
$stmt->bind_param("s", $search);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'price' => (float) $row['price'],
        'image_path' => $row['image_path']
    ];
}

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);