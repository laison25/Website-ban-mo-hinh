<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'Trang chủ - ' . APP_NAME;
$keyword = trim($_GET['keyword'] ?? '');
$category = trim($_GET['category'] ?? '');
$products = fetch_products($conn, $keyword, $category);
$categories = get_categories($conn);
$featured = array_values(array_filter($products, fn($item) => (int) $item['is_featured'] === 1));
$heroSlides = [];
foreach ($featured as $item) {
    $heroSlides[(int) $item['id']] = $item;
    if (count($heroSlides) === 4) {
        break;
    }
}
if (count($heroSlides) < 4) {
    foreach ($products as $item) {
        $heroSlides[(int) $item['id']] = $item;
        if (count($heroSlides) === 4) {
            break;
        }
    }
}
$heroSlides = array_values($heroSlides);
$flashSale = array_slice($products, 0, 4);
$bestSelling = array_slice($products, 4, 4);
if (empty($bestSelling)) {
    $bestSelling = array_slice($products, 0, 4);
}
include __DIR__ . '/includes/header.php';
?>
<main>
    <section class="hero-top-strip">
        <div class="container">
            <a class="shop-top-banner" href="<?= url('index.php#products') ?>">
                <img src="<?= url('assets/images/products/main-banner.jpg') ?>" alt="Shop banner">
                <div class="shop-top-banner__overlay"></div>
                <div class="shop-top-banner__content">
                    <span class="shop-top-banner__eyebrow">New arrivals</span>
                    <h1>Banner Shop Cua Ban</h1>
                    <p>Thay anh nay bang banner shop rieng de hien thi khuyen mai, brand hoac BST noi bat.</p>
                    <span class="shop-top-banner__cta">Mua ngay</span>
                </div>
            </a>
        </div>
    </section>

    <section class="home-shell section-space">
        <div class="container home-layout">
            <aside class="category-sidebar">
                <h3>Danh mục</h3>
                <a class="category-link <?= $category === '' ? 'active' : '' ?>" href="<?= url('index.php') ?>">Tất cả sản phẩm</a>
                <?php foreach ($categories as $cat): ?>
                    <a class="category-link <?= $category === $cat ? 'active' : '' ?>" href="<?= url('index.php?category=' . urlencode($cat)) ?>"><?= e($cat) ?></a>
                <?php endforeach; ?>
            </aside>

            <div class="hero-showcase">
                <?php if (!empty($heroSlides)): ?>
                    <div class="hero-slider" data-hero-slider>
                        <div class="hero-slider-frame">
                            <?php foreach ($heroSlides as $index => $item): ?>
                                <a
                                    class="hero-slide <?= $index === 0 ? 'is-active' : '' ?>"
                                    href="<?= url('product-detail.php?id=' . $item['id']) ?>"
                                    data-hero-slide
                                    aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>"
                                >
                                    <img src="<?= url($item['image_path']) ?>" alt="<?= e($item['name']) ?>">
                                    <div class="hero-slide-shade"></div>
                                    <div class="hero-slide-content">
                                        <span class="hero-eyebrow">Featured Collection</span>
                                        <span class="hero-tag"><?= e($item['category']) ?></span>
                                        <h1><?= e($item['name']) ?></h1>
                                        <p><?= e($item['studio']) ?> • <?= format_currency((float) $item['price']) ?></p>
                                        <span class="hero-cta">Xem chi tiet</span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <?php if (count($heroSlides) > 1): ?>
                            <button class="hero-control prev" type="button" data-hero-prev aria-label="Banner truoc">
                                <span>&lsaquo;</span>
                            </button>
                            <button class="hero-control next" type="button" data-hero-next aria-label="Banner tiep theo">
                                <span>&rsaquo;</span>
                            </button>

                            <div class="hero-dots" aria-label="Danh sach banner">
                                <?php foreach ($heroSlides as $index => $item): ?>
                                    <button
                                        class="hero-dot <?= $index === 0 ? 'is-active' : '' ?>"
                                        type="button"
                                        data-hero-dot="<?= $index ?>"
                                        aria-label="Mo banner <?= $index + 1 ?>"
                                        aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>"
                                    ></button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="section-space sale-section">
        <div class="container">
            <div class="section-label">Today's</div>
            <div class="section-head countdown-head">
                <div>
                    <h2>Flash Sales</h2>
                </div>
                <div class="countdown-boxes" data-countdown>
                    <div><small>Days</small><strong data-unit="days">03</strong></div>
                    <div><small>Hours</small><strong data-unit="hours">23</strong></div>
                    <div><small>Minutes</small><strong data-unit="minutes">19</strong></div>
                    <div><small>Seconds</small><strong data-unit="seconds">56</strong></div>
                </div>
            </div>
            <div class="product-grid">
                <?php foreach ($flashSale as $product): ?>
                    <article class="product-card figma-card" id="product-<?= (int) $product['id'] ?>">
                        <a class="product-image" href="<?= url('product-detail.php?id=' . $product['id']) ?>">
                            <img src="<?= url($product['image_path']) ?>" alt="<?= e($product['name']) ?>">
                            <span class="badge-sale">-15%</span>
                        </a>

                        <?php
                        $wishlist = $_SESSION['wishlist'] ?? [];
                        $isLoved = in_array((int) $product['id'], $wishlist, true);
                        ?>
                        <a href="<?= url('wishlist-toggle.php?id=' . (int) $product['id']) ?>"
                           class="wishlist-btn <?= $isLoved ? 'active' : '' ?>"
                           title="Yêu thích">
                            ♥
                        </a>

                        <div class="product-meta">
                            <h3><a href="<?= url('product-detail.php?id=' . $product['id']) ?>"><?= e($product['name']) ?></a></h3>
                            <div class="price-line">
                                <strong><?= format_currency((float) $product['price']) ?></strong>
                                <?php if ((float) $product['old_price'] > 0): ?>
                                    <span><?= format_currency((float) $product['old_price']) ?></span>
                                <?php endif; ?>
                            </div>

                            <?php
                            $ratingValue = rand(35, 50) / 10;
                            $fullStars = floor($ratingValue);
                            $emptyStars = 5 - $fullStars;
                            ?>
                            <div class="product-rating">
                                <span class="stars"><?= str_repeat('★', $fullStars) . str_repeat('☆', $emptyStars) ?></span>
                                <span class="rating-number">(<?= number_format($ratingValue, 1) ?>)</span>
                            </div>

                            <form action="<?= url('add-to-cart.php') ?>" method="post">
                                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                <input type="hidden" name="qty" value="1">
                                <button class="small-btn" type="submit">Thêm vào giỏ hàng</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="center-actions"><a class="primary-btn" href="#products">Tất cả sản phẩm</a></div>
        </div>
    </section>

    <section class="section-space" id="products">
        <div class="container">
            <div class="section-label">This Month</div>
            <div class="section-head">
                <div>
                    <h2>Best Selling Products</h2>
                    <p class="muted"><?= $keyword !== '' ? 'Kết quả tìm kiếm cho: ' . e($keyword) : 'Danh sách sản phẩm lấy trực tiếp từ MySQL và hiển thị theo phong cách Figma.' ?></p>
                </div>
                <a class="outline-btn" href="<?= url('admin/products.php') ?>">View Admin</a>
            </div>
            <?php if (empty($products)): ?>
                <div class="empty-box">Không tìm thấy sản phẩm phù hợp.</div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <article class="product-card figma-card" id="product-<?= (int) $product['id'] ?>">
                            <a class="product-image" href="<?= url('product-detail.php?id=' . $product['id']) ?>">
                                <img src="<?= url($product['image_path']) ?>" alt="<?= e($product['name']) ?>">
                                <?php if ((int) $product['is_featured'] === 1): ?><span class="badge-ribbon">Best Seller</span><?php endif; ?>
                            </a>

                            <?php
                            $wishlist = $_SESSION['wishlist'] ?? [];
                            $isLoved = in_array((int) $product['id'], $wishlist, true);
                            ?>
                            <a href="<?= url('wishlist-toggle.php?id=' . (int) $product['id']) ?>"
                               class="wishlist-btn <?= $isLoved ? 'active' : '' ?>"
                               title="Yêu thích">
                                ♥
                            </a>

                            <div class="product-meta">
                                <div class="studio"><?= e($product['studio']) ?></div>
                                <h3><a href="<?= url('product-detail.php?id=' . $product['id']) ?>"><?= e($product['name']) ?></a></h3>
                                <div class="price-line"><strong><?= format_currency((float) $product['price']) ?></strong></div>

                                <?php
                                $ratingValue = rand(35, 50) / 10;
                                $fullStars = floor($ratingValue);
                                $emptyStars = 5 - $fullStars;
                                ?>
                                <div class="product-rating">
                                    <span class="stars"><?= str_repeat('★', $fullStars) . str_repeat('☆', $emptyStars) ?></span>
                                    <span class="rating-number">(<?= number_format($ratingValue, 1) ?>)</span>
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
    </section>

    <section class="section-space-sm">
        <div class="container service-grid">
            <div class="service-item"><div class="service-icon">🚚</div><h4>FREE AND FAST DELIVERY</h4><p>Free delivery for orders above 5M</p></div>
            <div class="service-item"><div class="service-icon">🎧</div><h4>24/7 CUSTOMER SERVICE</h4><p>Friendly 24/7 customer support</p></div>
            <div class="service-item"><div class="service-icon">💰</div><h4>MONEY BACK GUARANTEE</h4><p>We return money within 30 days</p></div>
        </div>
    </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>