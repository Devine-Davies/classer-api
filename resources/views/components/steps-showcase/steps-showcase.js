window.classerStepReveal = () => ({
    isVisible: false,
    hasMounted: false,
    observer: null,
    init() {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (prefersReducedMotion || !('IntersectionObserver' in window)) {
            this.isVisible = true;
            this.hasMounted = true;
            return;
        }

        this.observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    this.isVisible = true;
                    this.observer?.unobserve(this.$el);
                });
            },
            {
                threshold: 0.25,
                rootMargin: '0px 0px -10% 0px',
            },
        );

        this.observer.observe(this.$el);
        this.hasMounted = true;
    },
});
