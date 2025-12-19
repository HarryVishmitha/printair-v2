export function homePopularProductsV2() {
    return {
        items: [],
        loading: true,

        async fetchItems() {
            try {
                const res = await fetch('/ajax/home/popular-products', {
                    headers: { Accept: 'application/json' },
                });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);

                const data = await res.json();
                this.items = (data.items ?? []).map((p) => ({
                    ...p,
                    quoteHref: p.quoteHref ?? null,
                }));
            } catch (e) {
                console.error('Popular products load failed:', e);
                this.items = [];
            } finally {
                this.loading = false;
            }
        },
    };
}

