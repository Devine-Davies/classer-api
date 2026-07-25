(() => {
    const DRAG_THRESHOLD_PX = 5;
    const CLICK_SUPPRESSION_MS = 150;

    const wireScroller = (scroller) => {
        if (scroller.dataset.galleryScrollerReady === 'true') {
            return;
        }

        scroller.dataset.galleryScrollerReady = 'true';
        scroller.style.cursor = 'grab';
        scroller.style.touchAction = 'pan-y';

        let activePointerId = null;
        let startX = 0;
        let startScrollLeft = 0;
        let isDragging = false;
        let suppressClicksUntil = 0;

        const resetInteraction = () => {
            activePointerId = null;
            isDragging = false;

            scroller.style.cursor = 'grab';
            delete scroller.dataset.dragging;
        };

        scroller.addEventListener('pointerdown', (event) => {
            if (
                event.button !== 0 ||
                activePointerId !== null
            ) {
                return;
            }

            activePointerId = event.pointerId;
            startX = event.clientX;
            startScrollLeft = scroller.scrollLeft;
            isDragging = false;

            scroller.style.cursor = 'grabbing';
        });

        scroller.addEventListener('pointermove', (event) => {
            if (event.pointerId !== activePointerId) {
                return;
            }

            const deltaX = event.clientX - startX;

            if (
                !isDragging &&
                Math.abs(deltaX) >= DRAG_THRESHOLD_PX
            ) {
                isDragging = true;
                scroller.dataset.dragging = 'true';
                scroller.setPointerCapture(event.pointerId);
            }

            if (!isDragging) {
                return;
            }

            event.preventDefault();
            scroller.scrollLeft = startScrollLeft - deltaX;
        });

        const stopInteraction = (event) => {
            if (event.pointerId !== activePointerId) {
                return;
            }

            if (isDragging) {
                suppressClicksUntil =
                    performance.now() + CLICK_SUPPRESSION_MS;
            }

            if (scroller.hasPointerCapture(event.pointerId)) {
                scroller.releasePointerCapture(event.pointerId);
            }

            resetInteraction();
        };

        scroller.addEventListener('pointerup', stopInteraction);
        scroller.addEventListener('pointercancel', stopInteraction);

        scroller.addEventListener(
            'click',
            (event) => {
                if (performance.now() > suppressClicksUntil) {
                    return;
                }

                event.preventDefault();
                event.stopImmediatePropagation();
            },
            true,
        );
    };

    const wireAllScrollers = () => {
        document
            .querySelectorAll('[data-gallery-scroller]')
            .forEach(wireScroller);
    };

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            wireAllScrollers,
            { once: true },
        );
    } else {
        wireAllScrollers();
    }
})();