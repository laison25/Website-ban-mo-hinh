<?php
function e(?string $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string {
    $configuredUrl = trim(APP_URL);

    if ($configuredUrl !== '') {
        return rtrim($configuredUrl, '/') . '/' . ltrim($path, '/');
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $segments = array_values(array_filter(explode('/', $scriptPath), 'strlen'));
    $basePath = '';

    if (!empty($segments) && strpos($segments[0], '.') === false && $segments[0] !== 'admin') {
        $basePath = '/' . $segments[0];
    }

    return rtrim($scheme . '://' . $host . $basePath, '/') . '/' . ltrim($path, '/');
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

function payment_method_label(string $method): string {
    return [
        'COD' => 'Thanh toán khi nhận hàng',
        'BANK' => 'Chuyển khoản ngân hàng',
        'QR_CODE' => 'VietQR',
        'MOMO' => 'Ví điện tử',
        'CARD' => 'Thẻ ATM / Visa',
    ][$method] ?? $method;
}

function order_status_label(string $status): string {
    return [
        'pending' => 'Chờ xác nhận',
        'awaiting_payment' => 'Chờ thanh toán',
        'paid' => 'Đã thanh toán',
        'processing' => 'Đang xử lý',
        'shipping' => 'Đang giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ][$status] ?? $status;
}

function available_coupons(): array {
    return [
        'LZON10' => [
            'label' => 'Giảm 10% đơn hàng',
            'type' => 'percent',
            'value' => 10,
            'max_discount' => 500000,
        ],
        'FREESHIP' => [
            'label' => 'Ưu đãi phí vận chuyển',
            'type' => 'fixed',
            'value' => 150000,
            'max_discount' => 150000,
        ],
        'FIGURE500' => [
            'label' => 'Giảm 500.000đ cho đơn từ 5.000.000đ',
            'type' => 'fixed',
            'value' => 500000,
            'min_total' => 5000000,
            'max_discount' => 500000,
        ],
    ];
}

function normalize_coupon_code(string $code): string {
    return strtoupper(trim($code));
}

function get_applied_coupon(): ?array {
    $code = normalize_coupon_code($_SESSION['coupon_code'] ?? '');
    $coupons = available_coupons();

    if ($code === '' || !isset($coupons[$code])) {
        return null;
    }

    return ['code' => $code] + $coupons[$code];
}

function calculate_coupon_discount(float $subtotal, ?array $coupon): float {
    if (!$coupon || $subtotal <= 0) {
        return 0;
    }

    if (!empty($coupon['min_total']) && $subtotal < (float) $coupon['min_total']) {
        return 0;
    }

    if (($coupon['type'] ?? '') === 'percent') {
        $discount = $subtotal * ((float) $coupon['value'] / 100);
    } else {
        $discount = (float) ($coupon['value'] ?? 0);
    }

    if (isset($coupon['max_discount'])) {
        $discount = min($discount, (float) $coupon['max_discount']);
    }

    return min($subtotal, max(0, $discount));
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

function get_current_user_record(mysqli $conn): ?array {
    $userId = (int) ($_SESSION['user']['id'] ?? 0);
    if ($userId <= 0) {
        return null;
    }

    $stmt = $conn->prepare('SELECT id, full_name, username, email, password_hash, role, status, created_at FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $user ?: null;
}

function get_wishlist_products(mysqli $conn): array {
    $wishlist = array_values(array_unique(array_map('intval', $_SESSION['wishlist'] ?? [])));
    $wishlist = array_values(array_filter($wishlist, fn($id) => $id > 0));

    if (empty($wishlist)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($wishlist), '?'));
    $types = str_repeat('i', count($wishlist));
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders) ORDER BY is_featured DESC, id DESC");
    $stmt->bind_param($types, ...$wishlist);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $products;
}

function fetch_products(mysqli $conn, string $keyword = '', string $category = '', string $sort = 'featured', float $minPrice = 0, float $maxPrice = 0): array {
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

    if ($minPrice > 0) {
        $conditions[] = 'price >= ?';
        $params[] = $minPrice;
        $types .= 'd';
    }

    if ($maxPrice > 0) {
        $conditions[] = 'price <= ?';
        $params[] = $maxPrice;
        $types .= 'd';
    }

    $sql = 'SELECT * FROM products';
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
    switch ($sort) {
        case 'price_asc':
            $orderBy = 'price ASC, id DESC';
            break;
        case 'price_desc':
            $orderBy = 'price DESC, id DESC';
            break;
        case 'name_asc':
            $orderBy = 'name ASC';
            break;
        case 'newest':
            $orderBy = 'id DESC';
            break;
        default:
            $orderBy = 'is_featured DESC, id DESC';
            break;
    }
    $sql .= ' ORDER BY ' . $orderBy;

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
