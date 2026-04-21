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

<!-- Floating Contact Button -->
<div class="floating-contact">
    <a href="https://www.facebook.com/nguyen.vinh.562690" target="_blank" class="contact-circle contact-support" title="Liên hệ">
        💬
    </a>
    <a href="https://m.me/nguyen.vinh.562690" target="_blank" class="contact-circle contact-messenger" title="Messenger">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="white" aria-hidden="true">
            <path d="M12 2C6.477 2 2 6.145 2 11.259c0 2.914 1.453 5.514 3.724 7.214V22l3.406-1.87c.907.252 1.869.388 2.87.388 5.523 0 10-4.145 10-9.259S17.523 2 12 2zm1.062 12.445-2.544-2.713-4.963 2.713 5.459-5.797 2.468 2.713 5.039-2.713-5.459 5.797z"/>
        </svg>
    </a>
</div>

<style>
.floating-contact {
    position: fixed;
    right: 18px;
    bottom: 18px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.contact-circle {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.contact-circle:hover {
    transform: scale(1.08);
    opacity: 0.95;
}

.contact-support {
    background: #2f2f2f;
    color: #fff;
    font-size: 24px;
}

.contact-messenger {
    background: #1877f2;
}

.contact-messenger svg {
    display: block;
}

@media (max-width: 768px) {
    .floating-contact {
        right: 12px;
        bottom: 12px;
    }

    .contact-circle {
        width: 52px;
        height: 52px;
    }
}
</style>

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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const flashToast = document.getElementById('flashToast');
    if (flashToast) {
        setTimeout(() => {
            flashToast.style.transition = '0.3s ease';
            flashToast.style.opacity = '0';
            flashToast.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                flashToast.style.display = 'none';
            }, 300);
        }, 2500);
    }
});
</script>
</body>
</html>