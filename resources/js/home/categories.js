// resources/js/home/categories.js

export function homeCategories() {
    return {
        categories: [],
        loading: true,

        async fetchCategories() {
            try {
                const response = await fetch('/ajax/home/categories');
                this.categories = await response.json();
            } catch (e) {
                console.error('Failed to load categories', e);
            } finally {
                this.loading = false;
            }
        }
    }
}
