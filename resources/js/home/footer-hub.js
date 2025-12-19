export function printairFooterHub() {
    return {
        loadingCats: true,
        loadingPopular: true,
        categories: [],
        popular: [],

        async init() {
            this.fetchCategories();
            this.fetchPopularProducts();
        },

        async fetchCategories() {
            this.loadingCats = true;
            try {
                const res = await fetch('/ajax/home/categories', {
                    headers: { Accept: 'application/json' },
                });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);

                const data = await res.json();
                const items = Array.isArray(data) ? data : (data.items ?? []);

                this.categories = items
                    .slice(0, 8)
                    .map((c) => ({
                        id: c.id,
                        name: c.name,
                        slug: c.slug,
                        href: c.href ?? `/categories/${c.slug}`,
                    }));
            } catch (e) {
                console.error('Footer categories load failed:', e);
                this.categories = [];
            } finally {
                this.loadingCats = false;
            }
        },

        async fetchPopularProducts() {
            this.loadingPopular = true;
            try {
                const res = await fetch('/ajax/home/popular-products?limit=6', {
                    headers: { Accept: 'application/json' },
                });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);

                const data = await res.json();
                this.popular = (data.items ?? []).slice(0, 6);
            } catch (e) {
                console.error('Footer popular products load failed:', e);
                this.popular = [];
            } finally {
                this.loadingPopular = false;
            }
        },
    };
}

