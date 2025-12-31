export function smartHeader(options = {}) {
    const threshold = Number(options.threshold ?? 10);
    const revealOffset = Number(options.revealOffset ?? 80);
    const elevateOffset = Number(options.elevateOffset ?? 10);

    return {
        lastY: 0,
        isHidden: false,
        isElevated: false,
        ticking: false,

        threshold,
        revealOffset,
        elevateOffset,

        lock: typeof options.lock === 'function' ? options.lock : () => false,

        init() {
            const y = window.scrollY || 0;
            this.lastY = y;
            this.isHidden = false;
            this.isElevated = y > this.elevateOffset;

            this.syncSpacer();

            const onScroll = () => {
                if (this.ticking) return;
                this.ticking = true;

                requestAnimationFrame(() => {
                    this.update(window.scrollY || 0);
                    this.ticking = false;
                });
            };

            window.addEventListener('scroll', onScroll, { passive: true });

            const onResize = () => this.syncSpacer();
            window.addEventListener('resize', onResize, { passive: true });

            if (typeof ResizeObserver !== 'undefined') {
                const observer = new ResizeObserver(() => this.syncSpacer());
                if (this.$refs?.header) observer.observe(this.$refs.header);
            }
        },

        update(y) {
            y = Math.max(0, y);
            const delta = y - this.lastY;

            this.isElevated = y > this.elevateOffset;

            if (this.lock?.()) {
                this.isHidden = false;
                this.lastY = y;
                return;
            }

            if (y <= this.revealOffset) {
                this.isHidden = false;
                this.lastY = y;
                return;
            }

            if (Math.abs(delta) < this.threshold) {
                this.lastY = y;
                return;
            }

            this.isHidden = delta > 0;
            this.lastY = y;
        },

        syncSpacer() {
            const headerEl = this.$refs?.header;
            const spacerEl = this.$refs?.spacer;
            if (!headerEl || !spacerEl) return;

            const height = Math.ceil(headerEl.getBoundingClientRect().height);
            spacerEl.style.height = `${height - 90 }px`;
            document.documentElement.style.setProperty('--printair-header-height', `${height}px`);
        },
    };
}
