<?php
require_once __DIR__ . '/data/products.php';
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$filtered = $products;
if ($keyword !== '') {
    $filtered = array_filter($products, function ($product) use ($keyword) {
        return stripos($product['name'], $keyword) !== false || stripos($product['category'], $keyword) !== false || stripos($product['studio'], $keyword) !== false;
    });
}
$flashSale = array_slice($filtered, 0, 4, true);
$bestSelling = array_slice($products, 0, 4, true);
include __DIR__ . '/includes/header.php';
?>
<main>
<section class="hero section-space"><div class="container"><div class="hero-grid">
<a class="hero-card" href="product-detail.php?id=6"><img src="assets/images/hero/hero-1.svg" alt="Hero 1"></a>
<a class="hero-card" href="product-detail.php?id=4"><img src="assets/images/hero/hero-2.svg" alt="Hero 2"></a>
<a class="hero-card" href="product-detail.php?id=5"><img src="assets/images/hero/hero-3.svg" alt="Hero 3"></a>
<a class="hero-card" href="product-detail.php?id=1"><img src="assets/images/hero/hero-4.svg" alt="Hero 4"></a>
</div></div></section>
<section class="section-space" id="flash-sale"><div class="container">
<div class="section-label">Today's</div>
<div class="section-head"><div><h2>Flash Sales</h2><div class="countdown"><div><span data-unit="d">03</span><small>Days</small></div><div><span data-unit="h">23</span><small>Hours</small></div><div><span data-unit="m">19</span><small>Minutes</small></div><div><span data-unit="s">56</span><small>Seconds</small></div></div></div><a href="#best-selling" class="pill-btn">View All</a></div>
<?php if (empty($flashSale)): ?>
<div class="empty-box">Không tìm thấy sản phẩm phù hợp với từ khóa "<?php echo htmlspecialchars($keyword); ?>".</div>
<?php else: ?>
<div class="product-grid">
<?php foreach ($flashSale as $product): ?>
<article class="product-card"><a href="product-detail.php?id=<?php echo $product['id']; ?>" class="product-image"><img src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"><span class="badge-sale">-15%</span></a><div class="product-meta"><div class="studio"><?php echo htmlspecialchars($product['studio']); ?></div><h3><a href="product-detail.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3><div class="price-line"><strong><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</strong><span><?php echo number_format($product['old_price'], 0, ',', '.'); ?>đ</span></div></div></article>
<?php endforeach; ?>
</div>
<?php endif; ?>
<div class="center-box"><a href="#best-selling" class="primary-btn">View All Products</a></div>
</div></section>
<section class="section-space" id="best-selling"><div class="container"><div class="section-label">This Month</div><div class="section-head"><h2>Best Selling Products</h2><a href="index.php" class="pill-btn">View All</a></div><div class="product-grid">
<?php foreach ($bestSelling as $product): ?>
<article class="product-card"><a href="product-detail.php?id=<?php echo $product['id']; ?>" class="product-image"><img src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"><span class="badge-ribbon">Best Seller</span></a><div class="product-meta"><div class="studio"><?php echo htmlspecialchars($product['studio']); ?></div><h3><a href="product-detail.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3><div class="price-line"><strong><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</strong></div></div></article>
<?php endforeach; ?>
</div><div class="center-box"><a href="#" class="outline-btn">Xem thêm sản phẩm pokemon</a></div></div></section>
<section class="service-row section-space-sm" id="about"><div class="container service-grid"><div class="service-item"><div class="service-icon">🚚</div><h4>FREE AND FAST DELIVERY</h4><p>Free delivery for all orders over 5M</p></div><div class="service-item"><div class="service-icon">🎧</div><h4>24/7 CUSTOMER SERVICE</h4><p>Friendly 24/7 customer support</p></div><div class="service-item"><div class="service-icon">💰</div><h4>MONEY BACK GUARANTEE</h4><p>We return money within 30 days</p></div></div></section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
