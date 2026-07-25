document.addEventListener('DOMContentLoaded', () => {
    const sliders = document.querySelectorAll('[data-drag-scroll]');

    sliders.forEach((slider) => {
        let isDragging = false;
        let startX = 0;
        let startScrollLeft = 0;
        let lastX = 0;
        let lastTime = 0;
        let velocity = 0;
        let momentumFrame = null;
        let shouldCancelClick = false;

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
    });
});
