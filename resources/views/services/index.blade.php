<x-home-layout :seo="$seo">
    @php
        $jsonUrl = route('ajax.services.index');
    @endphp

    <section class="bg-white">
        {{-- Top hero / header --}}
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-10 pb-6">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-slate-900">
                        Services
                    </h1>
                    <p class="mt-2 text-slate-600 max-w-2xl">
                        Browse Printair services with starting prices. Filter by category, search fast, and jump into
                        details.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ url('/contact') }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50">
                        Contact Us
                    </a>
                    <a href="https://wa.me/{{ config('printair.contact_whatsapp', '94768860175') }}" target="_blank"
                        class="inline-flex items-center justify-center rounded-2xl bg-[#ef233c] px-4 py-2 text-sm font-semibold text-white shadow hover:brightness-95">
                        WhatsApp
                    </a>
                </div>
            </div>
        </div>

        {{-- Filters + Grid --}}
        <div x-data="servicesPage({
            jsonUrl: @js($jsonUrl),
            placeholder: @js(asset('assets/placeholders/product.png')),
            initial: {
                q: @js(request('q', '')),
                category: @js(request('category', 'all')),
                sort: @js(request('sort', 'featured'))
            }
        })" x-init="init()" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-16">
            {{-- Filter Bar --}}
            <div class="sticky top-0 z-10 bg-white/85 backdrop-blur border-y border-slate-100 py-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-1 flex-col sm:flex-row gap-2 sm:items-center">
                        {{-- Search --}}
                        <div class="relative flex-1">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m21 21-4.3-4.3M10.8 18.2a7.4 7.4 0 1 1 0-14.8 7.4 7.4 0 0 1 0 14.8Z" />
                                </svg>
                            </span>
                            <input type="text" x-model="filters.q" @keyup.debounce.250ms="applyFilters()"
                                @keyup.enter.prevent="applyFilters()" placeholder="Search services…"
                                class="w-full rounded-2xl border border-slate-200 bg-white pl-10 pr-24 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/25" />

                            <button type="button" @click="applyFilters()"
                                class="absolute inset-y-1.5 right-1.5 inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 text-xs font-semibold text-white hover:bg-[#ef233c] transition">
                                Search
                            </button>
                        </div>

                        {{-- Category --}}
                        <select x-model="filters.category" @change="applyFilters()"
                            class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/25">
                            <option value="all">All Categories</option>
                            <template x-for="c in categories" :key="c.slug">
                                <option :value="c.slug" x-text="c.name"></option>
                            </template>
                        </select>

                        {{-- Sort --}}
                        <select x-model="filters.sort" @change="applyFilters()"
                            class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/25">
                            <option value="featured">Featured</option>
                            <option value="price_asc">Price: Low → High</option>
                            <option value="price_desc">Price: High → Low</option>
                            <option value="name_asc">Name: A → Z</option>
                            <option value="name_desc">Name: Z → A</option>
                        </select>
                    </div>

                    {{-- Reset --}}
                    <button type="button" @click="resetAll()"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50">
                        Reset
                    </button>
                </div>
            </div>

            {{-- Loading / Error --}}
            <template x-if="loading">
                <div class="py-14 text-center text-slate-500">
                    Loading services…
                </div>
            </template>

            <template x-if="!loading && error">
                <div class="py-14 text-center">
                    <div class="text-slate-900 font-black">Couldn’t load services</div>
                    <div class="mt-2 text-sm text-slate-500" x-text="error"></div>
                </div>
            </template>

            {{-- Grid --}}
            <template x-if="!loading && !error">
                <div>
                    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <template x-for="p in paged()" :key="p.id">
                            <a :href="p.href"
                                class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition">
                                <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-slate-50">
                                    <img :src="p.primaryImage ? p.primaryImage : placeholder" :alt="p.name"
                                        class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.04]"
                                        loading="lazy" />
                                </div>

                                <div class="mt-4 flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="font-black text-slate-900 truncate" x-text="p.name"></div>
                                        <div class="mt-1 text-xs text-slate-500 truncate"
                                            x-text="p.category?.name ?? '—'"></div>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <div class="text-[11px] text-slate-500">Starting</div>
                                        <div class="font-black text-slate-900">
                                            <span x-text="p.starting_label ?? money(p.starting_price, p.currency)"></span>
                                        </div>
                                        <template x-if="p.starting_hint">
                                            <div class="mt-0.5 text-[10px] text-slate-500" x-text="p.starting_hint"></div>
                                        </template>
                                    </div>
                                </div>

                                <div class="mt-3 text-sm text-slate-600 line-clamp-2" x-text="p.short ?? ''"></div>

                                <div
                                    class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white group-hover:bg-[#ef233c] transition">
                                    View details
                                </div>
                            </a>
                        </template>
                    </div>

                    {{-- Empty state --}}
                    <div class="mt-12 text-center text-slate-500" x-show="filtered.length === 0">
                        No services match your filters.
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-8 flex items-center justify-between" x-show="filtered.length > perPage">
                        <button
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50 disabled:opacity-40"
                            :disabled="page <= 1" @click="page = Math.max(1, page - 1); syncUrl()">
                            Prev
                        </button>

                        <div class="text-sm text-slate-600">
                            Page <span class="font-semibold text-slate-900" x-text="page"></span>
                            of <span class="font-semibold text-slate-900" x-text="totalPages()"></span>
                        </div>

                        <button
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50 disabled:opacity-40"
                            :disabled="page >= totalPages()"
                            @click="page = Math.min(totalPages(), page + 1); syncUrl()">
                            Next
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </section>

    {{-- Alpine module --}}
    @push('scripts')
        <script>
            function servicesPage({
                jsonUrl,
                placeholder,
                initial
            }) {
                return {
                    jsonUrl,
                    placeholder,
                    loading: true,
                    error: null,

                    // state
                    products: [],
                    categories: [],
                    filtered: [],
                    filters: {
                        q: initial.q || '',
                        category: initial.category || 'all',
                        sort: initial.sort || 'featured',
                    },
                    page: 1,
                    perPage: 12,

                    init() {
                        this.fetchProducts();
                    },

                    async fetchProducts() {
                        this.loading = true;
                        this.error = null;

                        try {
                            const res = await fetch(this.jsonUrl, {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            if (!res.ok) throw new Error(`HTTP ${res.status} while fetching services`);
                            const payload = await res.json();

                            this.products = Array.isArray(payload.data) ? payload.data : [];
                            this.categories = this.buildCategories(this.products);

                            this.applyFilters(false);
                        } catch (e) {
                            this.error = e?.message ?? 'Unknown error';
                        } finally {
                            this.loading = false;
                        }
                    },

                    buildCategories(products) {
                        const map = new Map();
                        for (const p of products) {
                            const c = p.category;
                            if (c?.slug && !map.has(c.slug)) map.set(c.slug, {
                                name: c.name,
                                slug: c.slug
                            });
                        }
                        return [{
                                name: 'All',
                                slug: 'all'
                            }, ...Array.from(map.values())]
                            .filter(c => c.slug !== 'all'); // we already render all in UI
                    },

                    applyFilters(sync = true) {
                        const q = (this.filters.q || '').trim().toLowerCase();
                        const cat = this.filters.category || 'all';

                        let items = [...this.products];

                        // category filter
                        if (cat !== 'all') {
                            items = items.filter(p => (p.category?.slug ?? '') === cat);
                        }

                        // search
                        if (q) {
                            items = items.filter(p => {
                                const hay = `${p.name ?? ''} ${p.short ?? ''} ${p.category?.name ?? ''}`.toLowerCase();
                                return hay.includes(q);
                            });
                        }

                        // sort
                        const s = this.filters.sort || 'featured';
                        if (s === 'price_asc') items.sort((a, b) => (a.starting_price ?? 0) - (b.starting_price ?? 0));
                        if (s === 'price_desc') items.sort((a, b) => (b.starting_price ?? 0) - (a.starting_price ?? 0));
                        if (s === 'name_asc') items.sort((a, b) => String(a.name ?? '').localeCompare(String(b.name ?? '')));
                        if (s === 'name_desc') items.sort((a, b) => String(b.name ?? '').localeCompare(String(a.name ?? '')));

                        this.filtered = items;

                        // reset page if current page exceeds total after filtering
                        this.page = 1;

                        if (sync) this.syncUrl();
                    },

                    totalPages() {
                        return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
                    },

                    paged() {
                        const start = (this.page - 1) * this.perPage;
                        return this.filtered.slice(start, start + this.perPage);
                    },

                    resetAll() {
                        this.filters.q = '';
                        this.filters.category = 'all';
                        this.filters.sort = 'featured';
                        this.page = 1;
                        this.applyFilters();
                    },

                    syncUrl() {
                        const params = new URLSearchParams();
                        if (this.filters.q) params.set('q', this.filters.q);
                        if (this.filters.category && this.filters.category !== 'all') params.set('category', this.filters
                            .category);
                        if (this.filters.sort && this.filters.sort !== 'featured') params.set('sort', this.filters.sort);
                        params.set('page', String(this.page));

                        const newUrl = `${window.location.pathname}?${params.toString()}`;
                        window.history.replaceState({}, '', newUrl);
                    },

                    money(amount, currency = 'LKR') {
                        const n = Number(amount ?? 0);
                        return `${currency} ${n.toLocaleString('en-LK')}`;
                    }
                }
            }
        </script>
    @endpush
</x-home-layout>

