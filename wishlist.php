<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Danh sách yêu thích - ' . APP_NAME;
$products = get_wishlist_products($conn);

include __DIR__ . '/includes/header.php';
?>
<main class="section-space wishlist-page">
    <div class="container">
        <div class="section-head">
            <div>
                <div class="section-label">Yêu thích</div>
                <h1>Danh sách sản phẩm đã lưu</h1>
                <p class="muted">Các mẫu figure bạn đã bấm trái tim sẽ được giữ tại đây trong phiên truy cập hiện tại.</p>
            </div>
            <a class="outline-btn" href="<?= url('index.php#products') ?>">Tiếp tục mua sắm</a>
        </div>

        <?php if (empty($products)): ?>
            <div class="empty-state">
                <h2>Chưa có sản phẩm yêu thích</h2>
                <p>Hãy bấm biểu tượng trái tim trên sản phẩm bạn muốn theo dõi để quay lại xem nhanh hơn.</p>
                <a class="primary-btn" href="<?= url('index.php#products') ?>">Khám phá sản phẩm</a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <article class="product-card figma-card" id="product-<?= (int) $product['id'] ?>">
                        <a class="product-image" href="<?= url('product-detail.php?id=' . $product['id']) ?>">
                            <img src="<?= url($product['image_path']) ?>" alt="<?= e($product['name']) ?>">
                            <?php if ((int) $product['is_featured'] === 1): ?><span class="badge-ribbon">Best Seller</span><?php endif; ?>
                        </a>

                        <a href="<?= url('wishlist-toggle.php?id=' . (int) $product['id']) ?>"
                           class="wishlist-btn active"
                           title="Bỏ yêu thích">
                            ♥
                        </a>

                        <div class="product-meta">
                            <div class="studio"><?= e($product['studio']) ?></div>
                            <h3><a href="<?= url('product-detail.php?id=' . $product['id']) ?>"><?= e($product['name']) ?></a></h3>
                            <div class="price-line">
                                <strong><?= format_currency((float) $product['price']) ?></strong>
                                <?php if ((float) $product['old_price'] > 0): ?>
                                    <span><?= format_currency((float) $product['old_price']) ?></span>
                                <?php endif; ?>
                            </div>

                            <?php
                            $ratingValue = max(0, min(5, (int) $product['rating']));
                            $emptyStars = 5 - $ratingValue;
                            ?>
                            <div class="product-rating">
                                <span class="stars"><?= str_repeat('★', $ratingValue) . str_repeat('☆', $emptyStars) ?></span>
                                <span class="rating-number">(<?= (int) $product['reviews'] ?>)</span>
                            </div>

                            <div class="card-actions stretch">
                                <a class="outline-btn small" href="<?= url('product-detail.php?id=' . $product['id']) ?>">Chi tiết</a>
                                <a class="small-btn" href="<?= url('product-detail.php?id=' . $product['id']) ?>" style="background:#111;">Mua ngay</a>
                            </div>
                            <form action="<?= url('add-to-cart.php') ?>" method="post" style="margin-top:8px;">
                                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                <input type="hidden" name="qty" value="1">
                                <button class="small-btn" type="submit" style="width:100%;">🛒 Thêm vào giỏ hàng</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
