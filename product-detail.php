<?php
require_once __DIR__ . '/includes/init.php';
$id = (int) ($_GET['id'] ?? 0);
$product = get_product($conn, $id);

if (!$product) {
    set_flash('error', 'Không tìm thấy sản phẩm.');
    redirect_to('index.php');
}

$pageTitle = $product['name'] . ' - ' . APP_NAME;
$stmt = $conn->prepare('SELECT * FROM products WHERE category = ? AND id <> ? ORDER BY id DESC LIMIT 4');
$stmt->bind_param('si', $product['category'], $product['id']);
$stmt->execute();
$related = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include __DIR__ . '/includes/header.php';
?>
<main class="detail-page">
    <div class="container">
        <div class="breadcrumb">Account / Gaming / <span><?= e($product['name']) ?></span></div>
        <section class="detail-grid section-space">
            <div class="gallery-wrap refined-gallery">
                <div class="thumb-list">
                    <?php for ($i = 0; $i < 4; $i++): ?>
                        <button class="thumb-btn <?= $i === 0 ? 'active' : '' ?>" type="button" data-target="<?= url($product['image_path']) ?>">
                            <img src="<?= url($product['image_path']) ?>" alt="Thumbnail <?= $i + 1 ?>">
                        </button>
                    <?php endfor; ?>
                </div>
                <div class="main-preview figma-preview">
                    <img id="mainProductImage" src="<?= url($product['image_path']) ?>" alt="<?= e($product['name']) ?>">
                </div>
            </div>
            <div class="detail-info">
                <h1><?= e($product['name']) ?></h1>
                <div class="rating-row">
                    <span class="stars"><?= str_repeat('★', (int) $product['rating']) . str_repeat('☆', max(0, 5 - (int) $product['rating'])) ?></span>
                    <span>(<?= (int) $product['reviews'] ?> Reviews)</span>
                    <span class="stock-tag"><?= (int) $product['stock'] > 0 ? 'In Stock' : 'Sold Out' ?></span>
                </div>
                <div class="detail-price"><?= format_currency((float) $product['price']) ?></div>
                <p class="detail-desc"><?= nl2br(e($product['description'])) ?></p>
                <div class="product-spec-box">
                    <div><strong>Studio:</strong> <?= e($product['studio']) ?></div>
                    <div><strong>Category:</strong> <?= e($product['category']) ?></div>
                    <div><strong>SKU:</strong> <?= e($product['sku']) ?></div>
                    <div><strong>Size:</strong> <?= e($product['size_label']) ?></div>
                </div>
                <form action="<?= url('add-to-cart.php') ?>" method="post" class="purchase-box inline-purchase">
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <div class="quantity-box">
                        <button type="button" class="qty-btn" data-qty="minus">−</button>
                        <input type="text" name="qty" value="1" id="qtyInput" readonly>
                        <button type="button" class="qty-btn plus" data-qty="plus">+</button>
                    </div>
                    <button class="primary-btn buy-btn" type="submit">Buy Now</button>
                    <button class="wish-btn" type="button">♡</button>
                </form>
                <div class="feature-box">
                    <div class="feature-item"><div class="feature-icon">🚚</div><div><h4>Free Delivery</h4><p>Enter your postal code for Delivery Availability</p></div></div>
                    <div class="feature-item"><div class="feature-icon">↩</div><div><h4>Return Delivery</h4><p>Free 30 Days Delivery Returns. Details</p></div></div>
                </div>
            </div>
        </section>

        <section class="section-space">
            <div class="section-label">Related Item</div>
            <div class="product-grid">
                <?php foreach ($related as $item): ?>
                    <article class="product-card figma-card">
                        <a class="product-image" href="<?= url('product-detail.php?id=' . $item['id']) ?>">
                            <img src="<?= url($item['image_path']) ?>" alt="<?= e($item['name']) ?>">
                            <span class="badge-ribbon">New</span>
                        </a>
                        <div class="product-meta">
                            <div class="studio"><?= e($item['studio']) ?></div>
                            <h3><a href="<?= url('product-detail.php?id=' . $item['id']) ?>"><?= e($item['name']) ?></a></h3>
                            <div class="price-line"><strong><?= format_currency((float) $item['price']) ?></strong></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
