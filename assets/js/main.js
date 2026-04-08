document.addEventListener('DOMContentLoaded', function () {
  const cartWrap = document.querySelector('.cart-menu-wrap');
  const cartToggle = document.querySelector('.cart-toggle');
  const miniCartPanel = document.getElementById('miniCartPanel');
  const shouldOpenCart = document.body && document.body.getAttribute('data-cart-open') === '1';

  if (cartWrap && cartToggle && miniCartPanel) {
    const setCartOpen = (open) => {
      cartWrap.classList.toggle('is-open', open);
      miniCartPanel.classList.toggle('is-open', open);
      cartToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    cartToggle.addEventListener('click', function (event) {
      event.preventDefault();
      setCartOpen(!miniCartPanel.classList.contains('is-open'));
    });

    document.addEventListener('click', function (event) {
      if (!cartWrap.contains(event.target)) {
        setCartOpen(false);
      }
    });

    if (shouldOpenCart) {
      setCartOpen(true);
    }
  }

  const countdown = document.querySelector('.countdown');
  if (countdown) {
    let totalSeconds = 3 * 24 * 60 * 60 + 23 * 60 * 60 + 19 * 60 + 56;
    setInterval(() => {
      if (totalSeconds <= 0) return;
      totalSeconds--;
      const days = Math.floor(totalSeconds / 86400);
      const hours = Math.floor((totalSeconds % 86400) / 3600);
      const minutes = Math.floor((totalSeconds % 3600) / 60);
      const seconds = totalSeconds % 60;
      countdown.querySelector('[data-unit="d"]').textContent = String(days).padStart(2, '0');
      countdown.querySelector('[data-unit="h"]').textContent = String(hours).padStart(2, '0');
      countdown.querySelector('[data-unit="m"]').textContent = String(minutes).padStart(2, '0');
      countdown.querySelector('[data-unit="s"]').textContent = String(seconds).padStart(2, '0');
    }, 1000);
  }
  const mainImage = document.getElementById('mainProductImage');
  const thumbs = document.querySelectorAll('.thumb-btn');
  thumbs.forEach(btn => btn.addEventListener('click', function () {
    const target = this.getAttribute('data-target');
    if (mainImage && target) {
      mainImage.src = target;
      thumbs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');
    }
  }));
  const qtyInput = document.getElementById('qtyInput');
  document.querySelectorAll('[data-qty]').forEach(button => button.addEventListener('click', function () {
    if (!qtyInput) return;
    let value = parseInt(qtyInput.value || '1', 10);
    value = this.getAttribute('data-qty') === 'plus' ? value + 1 : Math.max(1, value - 1);
    qtyInput.value = value;
  }));
  document.querySelectorAll('[data-accordion-toggle]').forEach(button => button.addEventListener('click', function () {
    const section = this.closest('[data-accordion]');
    const panel = section ? section.querySelector('[data-accordion-panel]') : null;
    if (!panel) return;
    const isOpen = panel.classList.contains('is-open');
    panel.classList.toggle('is-open', !isOpen);
    this.classList.toggle('is-open', !isOpen);
    this.setAttribute('aria-expanded', String(!isOpen));
    const icon = this.querySelector('.accordion-icon');
    if (icon) {
      icon.textContent = isOpen ? '+' : '-';
    }
  }));
});
