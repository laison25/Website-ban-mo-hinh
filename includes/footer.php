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
</body>
</html>
