<?php $year = date('Y'); ?>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <h4>Lzon Poke</h4>
            <p>Subscribe</p>
            <p>Get 10% off your first order</p>
            <div class="footer-subscribe">
                <input type="text" placeholder="Enter your email">
                <button type="button">➜</button>
            </div>
        </div>
        <div>
            <h4>Support</h4>
            <p>11 Bis Kyson, District 1</p>
            <p>Ho Chi Minh City</p>
            <p>LzonPoke@gmail.com</p>
            <p>+84 8888-8888-9999</p>
        </div>
        <div>
            <h4>Account</h4>
            <p><a href="<?= url('login.php') ?>">Login / Register</a></p>
            <p><a href="<?= url('cart.php') ?>">Cart</a></p>
            <p><a href="<?= url('checkout.php') ?>">Checkout</a></p>
            <p><a href="<?= url('order-history.php') ?>">My Orders</a></p>
        </div>
        <div>
            <h4>Quick Link</h4>
            <p><a href="<?= url('index.php#products') ?>">Shop</a></p>
            <p><a href="<?= url('product-detail.php?id=1') ?>">Product Detail</a></p>
            <p><a href="<?= url('admin/index.php') ?>">Admin</a></p>
        </div>
        <div>
            <h4>Download App</h4>
            <p>Demo UI inspired by your Figma layout</p>
            <div class="download-badges">
                <div class="qr-box">QR</div>
                <div class="store-links">
                    <span>Google Play</span>
                    <span>App Store</span>
                </div>
            </div>
        </div>
    </div>
    <div class="copyright">© Copyright <?= $year ?>. All rights reserved</div>
</footer>

<script src="<?= url('assets/js/main.js') ?>"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchBox = document.getElementById('searchBox');
    const suggestionsBox = document.getElementById('searchSuggestions');

    if (!searchBox || !suggestionsBox) return;

    const baseUrl = '<?= url('') ?>';

    searchBox.addEventListener('keyup', function () {
        const keyword = this.value.trim();

        if (keyword.length < 1) {
            suggestionsBox.innerHTML = '';
            suggestionsBox.style.display = 'none';
            return;
        }

        fetch(baseUrl + 'suggest-search.php?keyword=' + encodeURIComponent(keyword))
            .then(response => response.json())
            .then(data => {
                if (!Array.isArray(data) || data.length === 0) {
                    suggestionsBox.innerHTML = '<div style="padding:10px;">Không tìm thấy sản phẩm</div>';
                    suggestionsBox.style.display = 'block';
                    return;
                }

                let html = '';

                data.forEach(item => {
                    const detailUrl = baseUrl + 'product-detail.php?id=' + item.id;
                    const imageUrl = baseUrl + item.image_path;

                    html += `
                        <a href="${detailUrl}" style="
                            display:flex;
                            align-items:center;
                            gap:10px;
                            padding:10px;
                            text-decoration:none;
                            color:#222;
                            border-bottom:1px solid #eee;
                            background:#fff;
                        ">
                            <img src="${imageUrl}" alt="${item.name}" style="
                                width:50px;
                                height:50px;
                                object-fit:cover;
                                border-radius:6px;
                                flex-shrink:0;
                            ">
                            <div style="display:flex; flex-direction:column;">
                                <strong style="font-size:14px;">${item.name}</strong>
                                <span style="font-size:13px; color:#e63946;">
                                    ${Number(item.price).toLocaleString('vi-VN')} ₫
                                </span>
                            </div>
                        </a>
                    `;
                });

                suggestionsBox.innerHTML = html;
                suggestionsBox.style.display = 'block';
            })
            .catch((error) => {
                console.log(error);
                suggestionsBox.innerHTML = '<div style="padding:10px;">Có lỗi khi tải gợi ý</div>';
                suggestionsBox.style.display = 'block';
            });
    });

    document.addEventListener('click', function (e) {
        if (!searchBox.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });
});
</script>

</body>
</html>