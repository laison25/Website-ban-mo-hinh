<?php
function e(?string $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string {
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

function redirect_to(string $path): void {
    header('Location: ' . url($path));
    exit;
}

function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array {
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function is_logged_in(): bool {
    return !empty($_SESSION['user']);
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool {
    return is_logged_in() && (($_SESSION['user']['role'] ?? '') === 'admin');
}

function require_login(): void {
    if (!is_logged_in()) {
        set_flash('error', 'Bạn cần đăng nhập để tiếp tục.');
        redirect_to('login.php');
    }
}

function require_admin(): void {
    if (!is_admin()) {
        set_flash('error', 'Bạn không có quyền truy cập trang quản trị.');
        redirect_to('login.php');
    }
}

function format_currency(float $amount): string {
    return number_format($amount, 0, ',', '.') . 'đ';
}

function format_datetime(?string $datetime): string {
    if (!$datetime) {
        return '';
    }
    return date('d/m/Y H:i', strtotime($datetime));
}

function cart_count(): int {
    return array_sum($_SESSION['cart'] ?? []);
}

function starts_with(string $haystack, string $prefix): bool {
    return substr($haystack, 0, strlen($prefix)) === $prefix;
}

function project_root(): string {
    return dirname(__DIR__);
}

function slugify(string $value): string {
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? 'image';
    $value = trim($value, '-');
    return $value !== '' ? $value : 'image';
}

function handle_image_upload(array $file, string $targetDir = 'uploads/products'): ?string {
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Tải ảnh lên thất bại.');
    }

    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
    if (!in_array($extension, $allowed, true)) {
        throw new RuntimeException('Chỉ hỗ trợ file ảnh jpg, jpeg, png, webp, gif hoặc svg.');
    }

    $absoluteDir = project_root() . '/' . trim($targetDir, '/');
    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0777, true) && !is_dir($absoluteDir)) {
        throw new RuntimeException('Không thể tạo thư mục lưu ảnh.');
    }

    $baseName = slugify(pathinfo($file['name'], PATHINFO_FILENAME));
    $newName = date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '-' . $baseName . '.' . $extension;
    $absolutePath = $absoluteDir . '/' . $newName;

    if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
        throw new RuntimeException('Không thể lưu ảnh tải lên.');
    }

    return trim($targetDir, '/') . '/' . $newName;
}

function delete_uploaded_image(string $path): void {
    if ($path === '' || !starts_with($path, 'uploads/')) {
        return;
    }
    $absolute = project_root() . '/' . ltrim($path, '/');
    if (is_file($absolute)) {
        @unlink($absolute);
    }
}

function get_product(mysqli $conn, int $id): ?array {
    $stmt = $conn->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result ?: null;
}

function fetch_products(mysqli $conn, string $keyword = '', string $category = ''): array {
    $conditions = [];
    $params = [];
    $types = '';

    if ($keyword !== '') {
        $conditions[] = '(name LIKE ? OR category LIKE ? OR studio LIKE ?)';
        $like = '%' . $keyword . '%';
        $params = array_merge($params, [$like, $like, $like]);
        $types .= 'sss';
    }

    if ($category !== '') {
        $conditions[] = 'category = ?';
        $params[] = $category;
        $types .= 's';
    }

    $sql = 'SELECT * FROM products';
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
    $sql .= ' ORDER BY is_featured DESC, id DESC';

    if ($types === '') {
        $result = $conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function get_categories(mysqli $conn): array {
    $result = $conn->query('SELECT DISTINCT category FROM products WHERE category <> "" ORDER BY category ASC');
    if (!$result) {
        return [];
    }
    return array_column($result->fetch_all(MYSQLI_ASSOC), 'category');
}

function get_cart_items(mysqli $conn): array {
    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        return [];
    }

    $ids = array_map('intval', array_keys($cart));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $items = [];
    foreach ($products as $product) {
        $qty = max(1, (int) ($cart[$product['id']] ?? 1));
        $items[] = [
            'product' => $product,
            'qty' => $qty,
            'subtotal' => $qty * (float) $product['price'],
        ];
    }

    usort($items, fn($a, $b) => $b['product']['id'] <=> $a['product']['id']);
    return $items;
}

function cart_total(mysqli $conn): float {
    $total = 0;
    foreach (get_cart_items($conn) as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}

function get_order_items(mysqli $conn, int $orderId): array {
    $stmt = $conn->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC');
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $items;
}

function get_user_orders(mysqli $conn, int $userId): array {
    $stmt = $conn->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($orders as &$order) {
        $order['items'] = get_order_items($conn, (int) $order['id']);
    }
    unset($order);
    return $orders;
}
