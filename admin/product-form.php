<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
$isEdit = $id > 0;
$product = [
    'name' => '',
    'category' => '',
    'studio' => '',
    'description' => '',
    'price' => '0',
    'old_price' => '0',
    'stock' => '0',
    'rating' => '5',
    'reviews' => '0',
    'sku' => '',
    'size_label' => '',
    'image_path' => 'assets/images/products/product-1.jpg',
    'is_featured' => '0',
];
$error = '';

if ($isEdit) {
    $existing = get_product($conn, $id);
    if (!$existing) {
        set_flash('error', 'Không tìm thấy sản phẩm.');
        redirect_to('admin/products.php');
    }
    $product = array_merge($product, $existing);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($product as $key => $value) {
        if ($key === 'image_path') {
            continue;
        }
        $product[$key] = trim($_POST[$key] ?? (string) $value);
    }
    $product['is_featured'] = isset($_POST['is_featured']) ? '1' : '0';
    $product['image_path'] = trim($_POST['image_path'] ?? $product['image_path']);
    $oldImagePath = trim($_POST['old_image_path'] ?? $product['image_path']);

    try {
        $uploadedPath = handle_image_upload($_FILES['image_file'] ?? []);
        if ($uploadedPath) {
            if ($isEdit && $oldImagePath !== '') {
                delete_uploaded_image($oldImagePath);
            }
            $product['image_path'] = $uploadedPath;
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }

    if ($error === '' && ($product['name'] === '' || $product['category'] === '' || $product['price'] === '' || $product['stock'] === '' || $product['image_path'] === '')) {
        $error = 'Vui lòng nhập các trường bắt buộc.';
    }

    if ($error === '') {
        if ($isEdit) {
            $stmt = $conn->prepare('UPDATE products SET name=?, category=?, studio=?, description=?, price=?, old_price=?, stock=?, rating=?, reviews=?, sku=?, size_label=?, image_path=?, is_featured=? WHERE id=?');
            $stmt->bind_param('ssssddiiisssii', $product['name'], $product['category'], $product['studio'], $product['description'], $product['price'], $product['old_price'], $product['stock'], $product['rating'], $product['reviews'], $product['sku'], $product['size_label'], $product['image_path'], $product['is_featured'], $id);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Cập nhật sản phẩm thành công.');
        } else {
            $stmt = $conn->prepare('INSERT INTO products (name, category, studio, description, price, old_price, stock, rating, reviews, sku, size_label, image_path, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssddiiisssi', $product['name'], $product['category'], $product['studio'], $product['description'], $product['price'], $product['old_price'], $product['stock'], $product['rating'], $product['reviews'], $product['sku'], $product['size_label'], $product['image_path'], $product['is_featured']);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Thêm sản phẩm thành công.');
        }
        redirect_to('admin/products.php');
    }
}

$pageTitle = ($isEdit ? 'Sửa' : 'Thêm') . ' sản phẩm - ' . APP_NAME;
include __DIR__ . '/../includes/header.php';
?>
<main class="section-space">
    <div class="container narrow-box wide-form">
        <div class="section-head"><h1><?= $isEdit ? 'Sửa sản phẩm' : 'Thêm sản phẩm' ?></h1><a class="outline-btn" href="<?= url('admin/products.php') ?>">Quay lại</a></div>
        <?php if ($error): ?><div class="flash-message error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="form-card">
            <input type="hidden" name="old_image_path" value="<?= e($product['image_path']) ?>">
            <div class="form-grid-2">
                <div class="form-group"><label>Tên sản phẩm *</label><input type="text" name="name" value="<?= e($product['name']) ?>"></div>
                <div class="form-group"><label>Danh mục *</label><input type="text" name="category" value="<?= e($product['category']) ?>"></div>
            </div>
            <div class="form-grid-2">
                <div class="form-group"><label>Studio</label><input type="text" name="studio" value="<?= e($product['studio']) ?>"></div>
                <div class="form-group"><label>SKU</label><input type="text" name="sku" value="<?= e($product['sku']) ?>"></div>
            </div>
            <div class="form-grid-2">
                <div class="form-group"><label>Giá bán *</label><input type="number" step="0.01" name="price" value="<?= e((string) $product['price']) ?>"></div>
                <div class="form-group"><label>Giá cũ</label><input type="number" step="0.01" name="old_price" value="<?= e((string) $product['old_price']) ?>"></div>
            </div>
            <div class="form-grid-2">
                <div class="form-group"><label>Tồn kho *</label><input type="number" name="stock" value="<?= e((string) $product['stock']) ?>"></div>
                <div class="form-group"><label>Kích thước</label><input type="text" name="size_label" value="<?= e($product['size_label']) ?>"></div>
            </div>
            <div class="form-grid-2">
                <div class="form-group"><label>Rating</label><input type="number" name="rating" min="1" max="5" value="<?= e((string) $product['rating']) ?>"></div>
                <div class="form-group"><label>Reviews</label><input type="number" name="reviews" min="0" value="<?= e((string) $product['reviews']) ?>"></div>
            </div>
            <div class="form-grid-2 image-upload-grid">
                <div class="form-group">
                    <label>Upload ảnh sản phẩm</label>
                    <input type="file" name="image_file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg">
                    <small class="muted">Có thể upload ảnh thật từ máy tính.</small>
                </div>
                <div class="form-group">
                    <label>Hoặc dùng đường dẫn ảnh</label>
                    <input type="text" name="image_path" value="<?= e($product['image_path']) ?>">
                </div>
            </div>
            <div class="image-preview-box">
                <img src="<?= url($product['image_path']) ?>" alt="Preview">
            </div>
            <div class="form-group"><label>Mô tả</label><textarea name="description" rows="5"><?= e($product['description']) ?></textarea></div>
            <div class="form-group inline-check">
                <label><input type="checkbox" name="is_featured" value="1" <?= (int) $product['is_featured'] === 1 ? 'checked' : '' ?>> Đánh dấu nổi bật</label>
            </div>
            <button class="primary-btn" type="submit"><?= $isEdit ? 'Lưu thay đổi' : 'Thêm sản phẩm' ?></button>
        </form>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
