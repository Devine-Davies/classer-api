document.addEventListener('DOMContentLoaded', () => {
    const carousels = document.querySelectorAll('[data-card-carousel]');

    carousels.forEach((carousel) => {
        const slider = carousel.querySelector('[data-drag-scroll]');
        if (!slider) {
            return;
        }

        const cards = Array.from(slider.children);
        const prevButton = carousel.querySelector('[data-carousel-prev]');
        const nextButton = carousel.querySelector('[data-carousel-next]');
        const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));

        let isDragging = false;
        let startX = 0;
        let startScrollLeft = 0;
        let lastX = 0;
        let lastTime = 0;
        let velocity = 0;
        let momentumFrame = null;
        let shouldCancelClick = false;
        let rafSync = null;

        const getStep = () => {
            if (cards.length === 0) {
                return 1;
            }

            const first = cards[0];
            const second = cards[1];

            if (second) {
                return Math.max(1, Math.round(second.offsetLeft - first.offsetLeft));
            }

            return Math.max(1, Math.round(first.getBoundingClientRect().width));
        };

        const maxIndex = () => Math.max(0, cards.length - 1);

        const getCurrentIndex = () => {
            const step = getStep();
            const index = Math.round(slider.scrollLeft / step);
            return Math.min(maxIndex(), Math.max(0, index));
        };

        const updateControls = () => {
            const index = getCurrentIndex();

            if (prevButton) {
                prevButton.disabled = index <= 0;
            }

            if (nextButton) {
                nextButton.disabled = index >= maxIndex();
            }

            dots.forEach((dot, dotIndex) => {
                const isActive = dotIndex === index;
                dot.classList.toggle('is-active', isActive);
                dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        };

        const scrollToIndex = (index, behavior = 'smooth') => {
            const bounded = Math.min(maxIndex(), Math.max(0, index));
            const step = getStep();

            slider.scrollTo({
                left: bounded * step,
                behavior,
            });
        };

        const syncOnScroll = () => {
            if (rafSync) {
                cancelAnimationFrame(rafSync);
            }

            rafSync = requestAnimationFrame(() => {
                updateControls();
                rafSync = null;
            });
        };

        const stopMomentum = () => {
            if (momentumFrame) {
                cancelAnimationFrame(momentumFrame);
                momentumFrame = null;
            }
        };

        const runMomentum = () => {
            const friction = 0.92;
            const minVelocity = 0.08;

            if (Math.abs(velocity) < minVelocity) {
                momentumFrame = null;
                return;
            }

            slider.scrollLeft -= velocity;
            velocity *= friction;

            const atStart = slider.scrollLeft <= 0;
            const atEnd = slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 1;

            if ((atStart && velocity > 0) || (atEnd && velocity < 0)) {
                momentumFrame = null;
                return;
            }

            momentumFrame = requestAnimationFrame(runMomentum);
        };

        slider.addEventListener('pointerdown', (event) => {
            const interactiveElement = event.target.closest('a, button, input, textarea, select, label');
            if (interactiveElement || event.button !== 0) {
                return;
            }

            stopMomentum();

            isDragging = true;
            shouldCancelClick = false;

            startX = event.clientX;
            startScrollLeft = slider.scrollLeft;
            lastX = event.clientX;
            lastTime = performance.now();
            velocity = 0;

            slider.classList.add('is-dragging');
            slider.setPointerCapture(event.pointerId);
        });

        slider.addEventListener('pointermove', (event) => {
            if (!isDragging) {
                return;
            }

            const now = performance.now();
            const currentX = event.clientX;
            const dragDistance = currentX - startX;

            const deltaX = currentX - lastX;
            const deltaTime = Math.max(now - lastTime, 1);

            if (Math.abs(dragDistance) > 6) {
                shouldCancelClick = true;
            }

            slider.scrollLeft = startScrollLeft - dragDistance;

            const instantVelocity = (deltaX / deltaTime) * 16.67;
            velocity = velocity * 0.8 + instantVelocity * 0.2;

            lastX = currentX;
            lastTime = now;

            event.preventDefault();
        });

        const endDrag = (event) => {
            if (!isDragging) {
                return;
            }

            isDragging = false;
            slider.classList.remove('is-dragging');

            try {
                slider.releasePointerCapture(event.pointerId);
            } catch (error) {
                // Ignore pointer capture cleanup errors.
            }

            if (Math.abs(velocity) > 0.08) {
                stopMomentum();
                momentumFrame = requestAnimationFrame(runMomentum);
            }

            syncOnScroll();
        };

        slider.addEventListener('pointerup', endDrag);
        slider.addEventListener('pointercancel', endDrag);
        slider.addEventListener('lostpointercapture', endDrag);

        slider.addEventListener(
            'click',
            (event) => {
                if (!shouldCancelClick) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                shouldCancelClick = false;
            },
            true,
        );

        slider.addEventListener('scroll', syncOnScroll, { passive: true });
        window.addEventListener('resize', updateControls);

        if (prevButton) {
            prevButton.addEventListener('click', () => {
                stopMomentum();
                scrollToIndex(getCurrentIndex() - 1);
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', () => {
                stopMomentum();
                scrollToIndex(getCurrentIndex() + 1);
            });
        }

        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                const targetIndex = Number.parseInt(dot.dataset.index || '0', 10);
                stopMomentum();
                scrollToIndex(targetIndex);
            });
        });

        carousel.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowRight') {
                event.preventDefault();
                scrollToIndex(getCurrentIndex() + 1);
            }

            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                scrollToIndex(getCurrentIndex() - 1);
            }
        });

        updateControls();
    });
});
