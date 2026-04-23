<?php $year = date('Y'); ?>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <h4>Lzon Poke</h4>
            <p>Figure store dành cho người sưu tầm Pokemon, anime và resin statue.</p>
            <p>Nhận tin mẫu mới và ưu đãi sớm.</p>
            <div class="footer-subscribe">
                <input type="text" placeholder="Email của bạn">
                <button type="button">➜</button>
            </div>
        </div>
        <div>
            <h4>Hỗ trợ</h4>
            <p>11 Bis Kỳ Sơn, Quận 1</p>
            <p>TP. Hồ Chí Minh</p>
            <p>LzonPoke@gmail.com</p>
            <p>+84 8888-8888-9999</p>
        </div>
        <div>
            <h4>Tài khoản</h4>
            <p><a href="<?= url('login.php') ?>">Đăng nhập / Đăng ký</a></p>
            <p><a href="<?= url('cart.php') ?>">Giỏ hàng</a></p>
            <p><a href="<?= url('wishlist.php') ?>">Sản phẩm yêu thích</a></p>
            <p><a href="<?= url('account-settings.php') ?>">Cài đặt tài khoản</a></p>
            <p><a href="<?= url('checkout.php') ?>">Thanh toán</a></p>
            <p><a href="<?= url('track-order.php') ?>">Tra cứu đơn hàng</a></p>
            <p><a href="<?= url('order-history.php') ?>">Lịch sử đơn hàng</a></p>
        </div>
        <div>
            <h4>Liên kết nhanh</h4>
            <p><a href="<?= url('index.php#products') ?>">Cửa hàng</a></p>
            <p><a href="<?= url('product-detail.php?id=1') ?>">Chi tiết sản phẩm</a></p>
            <p><a href="<?= url('admin/index.php') ?>">Trang quản trị</a></p>
        </div>
        <div>
            <h4>Theo dõi shop</h4>
            <p>Cập nhật pre-order, restock và các mẫu resin mới.</p>
            <div class="download-badges">
                <div class="qr-box">QR</div>
                <div class="store-links">
                    <span>Facebook</span>
                    <span>Messenger</span>
                </div>
            </div>
        </div>
    </div>

    <div class="copyright">© <?= $year ?> Lzon Poke. All rights reserved.</div>
</footer>

<div class="chat-widget" data-chat-widget>
    <button class="chat-toggle" type="button" data-chat-toggle aria-label="Mở hộp chat" aria-expanded="false">
        <span class="chat-toggle__icon">💬</span>
        <span class="chat-toggle__badge"></span>
    </button>

    <section class="chat-panel" data-chat-panel aria-label="Hộp chat hỗ trợ">
        <div class="chat-panel__head">
            <div>
                <strong>Lzon Poke Support</strong>
                <span><i></i> Đang online</span>
            </div>
            <button type="button" data-chat-close aria-label="Đóng hộp chat">×</button>
        </div>

        <div class="chat-panel__body" data-chat-messages>
            <div class="chat-message bot">Chào bạn, shop có thể tư vấn mẫu figure, giá hoặc tình trạng hàng nhé.</div>
            <div class="chat-quick-actions">
                <button type="button" data-chat-suggest="Mình muốn tư vấn một mẫu figure Pokemon.">Tư vấn mẫu</button>
                <button type="button" data-chat-suggest="Shop còn hàng mẫu nào dưới 3 triệu không?">Dưới 3 triệu</button>
                <button type="button" data-chat-suggest="Mình muốn hỏi về phí ship và đóng gói.">Phí ship</button>
            </div>
        </div>

        <form class="chat-panel__form" data-chat-form>
            <input type="text" data-chat-input placeholder="Nhập tin nhắn..." autocomplete="off">
            <button type="submit">Gửi</button>
        </form>

        <div class="chat-panel__links">
            <a href="https://m.me/nguyen.vinh.562690" target="_blank" rel="noopener">Messenger</a>
            <a href="https://www.facebook.com/nguyen.vinh.562690" target="_blank" rel="noopener">Facebook</a>
        </div>
    </section>
</div>

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
