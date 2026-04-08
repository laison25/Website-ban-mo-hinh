document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-hero-slider]').forEach(function (slider) {
        const slides = Array.from(slider.querySelectorAll('[data-hero-slide]'));
        const dots = Array.from(slider.querySelectorAll('[data-hero-dot]'));
        const prevButton = slider.querySelector('[data-hero-prev]');
        const nextButton = slider.querySelector('[data-hero-next]');
        let activeIndex = slides.findIndex(function (slide) {
            return slide.classList.contains('is-active');
        });
        let autoPlay = null;
        let startX = 0;
        let deltaX = 0;

        if (!slides.length) return;
        if (activeIndex < 0) activeIndex = 0;

        const renderSlide = function (index) {
            activeIndex = (index + slides.length) % slides.length;

            slides.forEach(function (slide, slideIndex) {
                const isActive = slideIndex === activeIndex;
                slide.classList.toggle('is-active', isActive);
                slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
            });

            dots.forEach(function (dot, dotIndex) {
                const isActive = dotIndex === activeIndex;
                dot.classList.toggle('is-active', isActive);
                dot.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        };

        const stopAutoPlay = function () {
            if (autoPlay) {
                window.clearInterval(autoPlay);
                autoPlay = null;
            }
        };

        const startAutoPlay = function () {
            if (slides.length <= 1) return;
            stopAutoPlay();
            autoPlay = window.setInterval(function () {
                renderSlide(activeIndex + 1);
            }, 5000);
        };

        if (prevButton) {
            prevButton.addEventListener('click', function () {
                renderSlide(activeIndex - 1);
                startAutoPlay();
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', function () {
                renderSlide(activeIndex + 1);
                startAutoPlay();
            });
        }

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                renderSlide(parseInt(dot.getAttribute('data-hero-dot') || '0', 10));
                startAutoPlay();
            });
        });

        slider.addEventListener('mouseenter', stopAutoPlay);
        slider.addEventListener('mouseleave', startAutoPlay);

        slider.addEventListener('touchstart', function (event) {
            startX = event.changedTouches[0].clientX;
            deltaX = 0;
        }, { passive: true });

        slider.addEventListener('touchmove', function (event) {
            deltaX = event.changedTouches[0].clientX - startX;
        }, { passive: true });

        slider.addEventListener('touchend', function () {
            if (Math.abs(deltaX) > 45) {
                renderSlide(activeIndex + (deltaX < 0 ? 1 : -1));
                startAutoPlay();
            }
        });

        renderSlide(activeIndex);
        startAutoPlay();
    });

    const mainProductImage = document.getElementById('mainProductImage');
    document.querySelectorAll('.thumb-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.thumb-btn').forEach(function (item) {
                item.classList.remove('active');
            });
            btn.classList.add('active');
            if (mainProductImage) {
                mainProductImage.src = btn.getAttribute('data-target');
            }
        });
    });

    const qtyInput = document.getElementById('qtyInput');
    document.querySelectorAll('[data-qty]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!qtyInput) return;
            let current = parseInt(qtyInput.value || '1', 10);
            if (button.getAttribute('data-qty') === 'plus') {
                current += 1;
            } else {
                current = Math.max(1, current - 1);
            }
            qtyInput.value = current;
        });
    });

    const countdown = document.querySelector('[data-countdown]');
    if (countdown) {
        let remaining = (3 * 24 * 60 * 60) + (23 * 60 * 60) + (19 * 60) + 56;
        const render = function () {
            const days = Math.floor(remaining / 86400);
            const hours = Math.floor((remaining % 86400) / 3600);
            const minutes = Math.floor((remaining % 3600) / 60);
            const seconds = remaining % 60;
            countdown.querySelector('[data-unit="days"]').textContent = String(days).padStart(2, '0');
            countdown.querySelector('[data-unit="hours"]').textContent = String(hours).padStart(2, '0');
            countdown.querySelector('[data-unit="minutes"]').textContent = String(minutes).padStart(2, '0');
            countdown.querySelector('[data-unit="seconds"]').textContent = String(seconds).padStart(2, '0');
            if (remaining > 0) remaining -= 1;
        };
        render();
        setInterval(render, 1000);
    }
});
