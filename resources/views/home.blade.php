<x-home-layout :seo="$seo">

    {{-- Hero Section --}}
    @php
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Safe WG label (customize property names to your DB)
        $wgName = $user?->workingGroup?->name ?? ($user?->working_group?->name ?? ($user?->working_group_name ?? null));

        // Optional: wg "badge" color logic (simple & safe)
        $wgTone = $wgName ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700';
    @endphp

    <section class="relative overflow-hidden bg-slate-50">
        <!-- Soft background decor -->
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-40 -right-40 h-[420px] w-[420px] rounded-full bg-red-400/20 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 h-[420px] w-[420px] rounded-full bg-purple-500/20 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Mobile-first spacing -->
            <div class="grid grid-cols-1 gap-10 py-14 sm:py-16 lg:grid-cols-2 lg:items-center lg:gap-14 lg:py-24">

                <!-- LEFT -->
                <div class="order-2 lg:order-1">
                    <!-- Personalization row -->
                    <div class="mb-5 flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $wgTone }}">
                            <iconify-icon icon="solar:shield-check-bold" class="text-base"></iconify-icon>

                            @if ($wgName)
                                Your Working Group: <span class="opacity-90">{{ $wgName }}</span>
                            @else
                                Printing made simple & fast
                            @endif
                        </span>

                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200/70">
                            <iconify-icon icon="solar:delivery-bold" class="text-base text-red-500"></iconify-icon>
                            Island-wide delivery
                        </span>
                    </div>

                    <!-- Headline -->
                    <h1
                        class="text-4xl font-be-vietnam-pro font-black leading-tight text-slate-900 sm:text-5xl lg:text-6xl">
                        Where
                        <span class="hero-animated-gradient">Design</span><br class="hidden sm:block" />
                        Meets Industrial Printing
                    </h1>

                    <p class="mt-5 max-w-xl text-base text-slate-600 sm:mt-6 sm:text-lg">
                        High-precision custom printing for brands, businesses, and creators.
                        Built for speed, accuracy, and scale.
                    </p>

                    <!-- Feature bullets -->
                    <ul class="mt-6 space-y-3 text-sm text-slate-700 sm:text-base">
                        <li class="flex items-start gap-3">
                            <span
                                class="mt-0.5 inline-flex h-7 w-7 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200/70">
                                <iconify-icon icon="solar:layers-bold" class="text-lg text-slate-900"></iconify-icon>
                            </span>
                            <span><span class="font-semibold text-slate-900">Large-format</span> & custom
                                solutions</span>
                        </li>

                        <li class="flex items-start gap-3">
                            <span
                                class="mt-0.5 inline-flex h-7 w-7 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200/70">
                                <iconify-icon icon="solar:palette-round-bold"
                                    class="text-lg text-slate-900"></iconify-icon>
                            </span>
                            <span>Accurate colors & <span class="font-semibold text-slate-900">premium
                                    finishes</span></span>
                        </li>

                        <li class="flex items-start gap-3">
                            <span
                                class="mt-0.5 inline-flex h-7 w-7 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200/70">
                                <iconify-icon icon="solar:clock-circle-bold"
                                    class="text-lg text-slate-900"></iconify-icon>
                            </span>
                            <span>Fast turnaround, <span class="font-semibold text-slate-900">reliable
                                    delivery</span></span>
                        </li>
                    </ul>

                    <!-- CTA -->
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                        <a href="#"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-500 px-6 py-3 text-sm font-semibold text-white shadow-sm shadow-red-500/20 transition hover:bg-red-600 sm:w-auto">
                            <iconify-icon icon="solar:magic-stick-3-bold" class="text-lg"></iconify-icon>
                            Start Designing
                        </a>

                        <a href="#"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-red-500 bg-white px-6 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-50 sm:w-auto">
                            <iconify-icon icon="solar:document-add-bold" class="text-lg"></iconify-icon>
                            Ask for Quote
                        </a>

                        <a href="#"
                            class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 sm:w-auto">
                            <iconify-icon icon="solar:cart-large-2-bold" class="text-lg"></iconify-icon>
                            Explore Products
                            <iconify-icon icon="solar:arrow-right-bold"
                                class="text-lg transition group-hover:translate-x-0.5"></iconify-icon>
                        </a>
                    </div>

                    <!-- Trust row -->
                    <div class="mt-8 grid grid-cols-1 gap-3 text-sm text-slate-600 sm:grid-cols-2">
                        <div class="flex items-center gap-2 rounded-xl bg-white/80 px-4 py-3 ring-1 ring-slate-200/70">
                            <iconify-icon icon="solar:star-bold" class="text-lg text-red-500"></iconify-icon>
                            Trusted by 1000+ clients
                        </div>
                        <div class="flex items-center gap-2 rounded-xl bg-white/80 px-4 py-3 ring-1 ring-slate-200/70">
                            <iconify-icon icon="solar:cpu-bolt-bold" class="text-lg text-red-500"></iconify-icon>
                            Commercial-grade printing
                        </div>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="order-1 lg:order-2">
                    <div class="relative mx-auto max-w-2xl lg:max-w-none" x-data="{ loaded: false, errored: false }"
                        x-init="const img = $refs.heroImg;
                        if (img && img.complete) {
                            if (img.naturalWidth > 0) loaded = true;
                            else errored = true;
                        }">

                        <!-- Glow behind image -->
                        <div
                            class="absolute inset-0 rounded-[2rem] bg-gradient-to-br from-red-400/25 to-fuchsia-500/20 blur-2xl">
                        </div>

                        <!-- Card wrapper -->
                        <div
                            class="relative overflow-hidden rounded-[2rem] bg-white/60 p-2 ring-1 ring-slate-200/70 backdrop-blur">

                            <!-- Skeleton (shown until image loads) -->
                            <div x-show="!loaded && !errored" class="absolute inset-2 rounded-[1.6rem] overflow-hidden"
                                aria-hidden="true">
                                <!-- Base -->
                                <div class="h-full w-full bg-slate-200/70"></div>

                                <!-- Shimmer -->
                                <div class="hero-shimmer absolute inset-0"></div>

                                <!-- Optional placeholder lines -->
                                <div class="absolute bottom-6 left-6 space-y-3">
                                    <div class="h-4 w-40 rounded-full bg-slate-300/70"></div>
                                    <div class="h-3 w-56 rounded-full bg-slate-300/60"></div>
                                </div>
                            </div>

                            <!-- Actual image -->
                            <img src="/assets/printair/printing-machine.png" alt="Printair Printing Machine"
                                x-ref="heroImg"
                                class="w-full opacity-0 rounded-[1.6rem] object-cover drop-shadow-2xl transition-opacity duration-500"
                                x-bind:class="loaded ? 'opacity-100' : 'opacity-0'" x-on:load="loaded = true"
                                x-on:error="errored = true" />

                            <!-- Error fallback -->
                            <div x-show="errored"
                                class="absolute inset-2 rounded-[1.6rem] bg-slate-100 ring-1 ring-slate-200/70 flex items-center justify-center text-slate-500">
                                <div class="text-center px-6">
                                    <div
                                        class="mx-auto mb-2 h-10 w-10 rounded-xl bg-white ring-1 ring-slate-200/70 flex items-center justify-center">
                                        <iconify-icon icon="solar:gallery-remove-bold"
                                            class="text-2xl text-slate-700"></iconify-icon>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700">Preview unavailable</p>
                                    <p class="text-xs text-slate-500 mt-1">Image failed to load.</p>
                                </div>
                            </div>

                            <!-- Mini floating stat (only after load) -->
                            <div x-show="loaded && !errored" x-transition.opacity.duration.300ms
                                class="absolute bottom-5 left-5 rounded-2xl bg-white/90 px-4 py-3 text-sm text-slate-700 shadow-lg shadow-slate-900/10 ring-1 ring-slate-200/70">
                                <div class="flex items-center gap-2 font-semibold text-slate-900">
                                    <iconify-icon icon="solar:bolt-circle-bold"
                                        class="text-lg text-red-500"></iconify-icon>
                                    Fast Turnaround
                                </div>
                                <p class="mt-0.5 text-xs text-slate-600">Ready for urgent jobs too.</p>
                            </div>

                            <!-- Top-right tag (only after load) -->
                            <div x-show="loaded && !errored" x-transition.opacity.duration.300ms
                                class="absolute right-5 top-5 rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">
                                <span class="inline-flex items-center gap-2">
                                    <iconify-icon icon="solar:printer-bold" class="text-base"></iconify-icon>
                                    Pro Print
                                </span>
                            </div>

                        </div>
                    </div>
                </div>

                <style>
                    .hero-shimmer {
                        background: linear-gradient(110deg,
                                rgba(255, 255, 255, 0) 0%,
                                rgba(255, 255, 255, 0.65) 45%,
                                rgba(255, 255, 255, 0) 90%);
                        transform: translateX(-100%);
                        animation: heroShimmer 1.35s ease-in-out infinite;
                    }

                    @keyframes heroShimmer {
                        0% {
                            transform: translateX(-100%);
                        }

                        100% {
                            transform: translateX(100%);
                        }
                    }

                    @media (prefers-reduced-motion: reduce) {
                        .hero-shimmer {
                            animation: none;
                        }
                    }
                </style>



            </div>
        </div>

        <!-- Animated gradient text CSS -->
        <style>
            .hero-animated-gradient {
                background: linear-gradient(90deg, #ef4444, #a855f7, #ef4444);
                background-size: 200% 200%;
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
                animation: heroGradient 6s ease-in-out infinite;
            }

            @keyframes heroGradient {
                0% {
                    background-position: 0% 50%;
                }

                50% {
                    background-position: 100% 50%;
                }

                100% {
                    background-position: 0% 50%;
                }
            }
        </style>
    </section>

    {{-- Categories Section --}}
    <section class="w-full bg-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-2xl font-semibold text-slate-900">
                    Explore Our Categories
                </h2>
            </div>

            <div x-data="homeCategories()" x-init="fetchCategories()">
                {{-- Skeleton --}}
                <template x-if="loading">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                        <template x-for="i in 8">
                            <div class="h-40 rounded-2xl bg-slate-200 animate-pulse"></div>
                        </template>
                    </div>
                </template>

                {{-- Categories --}}
                <template x-if="!loading">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                        <template x-for="category in categories" :key="category.id">
                            <a :href="`/categories/${category.slug}`"
                                class="group rounded-2xl border border-slate-200 bg-white overflow-hidden transition hover:-translate-y-1 hover:shadow-lg">
                                <div class="h-28 bg-blue-100 overflow-hidden flex items-center justify-center">
                                    <iconify-icon :icon="category.icon ?? 'solar:layers-bold'"
                                        class="text-6xl text-slate-600 transition group-hover:text-red-500"></iconify-icon>
                                </div>

                                <div class="p-4 text-center">
                                    <h3 class="text-sm font-medium text-slate-900">
                                        <span x-text="category.name"></span>
                                    </h3>
                                </div>
                            </a>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </section>

    {{-- Popular Products Section --}}
    <section class="max-w-7xl mx-auto px-4 py-14">
        <div class="mb-7 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900">Popular Products</h2>
                <p class="mt-2 text-sm sm:text-base text-slate-600 max-w-2xl">
                    Hand-picked best-sellers that most customers order first — fast to produce, easy to customize.
                </p>
            </div>

            <a href="{{ route('products.index') }}"
                class="hidden sm:inline-flex items-center gap-2 text-sm font-semibold text-red-600 hover:text-red-700">
                View all
                <iconify-icon icon="solar:arrow-right-bold" class="text-base"></iconify-icon>
            </a>
        </div>

        <div x-data="homePopularProductsV2()" x-init="fetchItems()">

            {{-- Skeleton --}}
            <template x-if="loading">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-7">
                    <template x-for="i in 6" :key="i">
                        <div class="rounded-3xl border border-slate-200 bg-white overflow-hidden">
                            <div class="h-52 bg-slate-200 animate-pulse"></div>
                            <div class="p-6 space-y-3">
                                <div class="h-4 w-2/3 bg-slate-200 rounded animate-pulse"></div>
                                <div class="h-3 w-full bg-slate-200 rounded animate-pulse"></div>
                                <div class="h-3 w-3/4 bg-slate-200 rounded animate-pulse"></div>
                                <div class="pt-2 flex gap-3">
                                    <div class="h-10 w-32 bg-slate-200 rounded-xl animate-pulse"></div>
                                    <div class="h-10 w-28 bg-slate-200 rounded-xl animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Items --}}
            <template x-if="!loading">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-7">
                    <template x-for="p in items" :key="p.id">
                        <article
                            class="group rounded-3xl border border-slate-200 bg-white overflow-hidden transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                            {{-- Image --}}
                            <a :href="p.href" class="block relative h-56 bg-slate-100 overflow-hidden">
                                <img :src="p.primaryImage" :alt="p.name"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105 aspect-square"
                                    loading="lazy" />

                                {{-- Top-left badge --}}
                                <div class="absolute left-4 top-4">
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1 text-xs font-bold text-slate-900 ring-1 ring-slate-200/70">
                                        <iconify-icon icon="solar:fire-bold"
                                            class="text-base text-red-500"></iconify-icon>
                                        Popular
                                    </span>
                                </div>

                                {{-- Bottom gradient for readability --}}
                                <div
                                    class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-slate-900/50 to-transparent">
                                </div>
                            </a>

                            {{-- Body --}}
                            <div class="p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <a :href="p.href" class="min-w-0">
                                        <h3 class="text-lg font-black text-slate-900 leading-snug line-clamp-2"
                                            x-text="p.name"></h3>

                                        <p class="mt-2 text-sm text-slate-600 line-clamp-2"
                                            x-text="p.seoDescription ?? 'Premium-quality printing with clean finishes and reliable turnaround.'">
                                        </p>
                                    </a>

                                    {{-- Price chip --}}
                                    <div class="shrink-0">
                                        <div class="rounded-2xl bg-slate-900 px-3 py-2 text-right text-white">
                                            <div class="text-[11px] font-semibold opacity-80">Starting</div>

                                            <template x-if="p.price?.type !== 'none'">
                                                <div class="text-sm font-black whitespace-nowrap"
                                                    x-text="p.price.label">
                                                </div>
                                            </template>

                                            <template x-if="p.price?.type === 'none'">
                                                <div class="text-sm font-black whitespace-nowrap">Ask</div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- Micro trust row --}}
                                <div
                                    class="mt-4 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-700">
                                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                                        <iconify-icon icon="solar:delivery-bold"
                                            class="text-base text-red-500"></iconify-icon>
                                        Island-wide delivery
                                    </span>
                                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                                        <iconify-icon icon="solar:clock-circle-bold"
                                            class="text-base text-red-500"></iconify-icon>
                                        Fast turnaround
                                    </span>
                                </div>

                                {{-- CTAs --}}
                                <div class="mt-5 flex flex-col sm:flex-row gap-3">
                                    <a :href="p.href"
                                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-red-500 px-4 py-3 text-sm font-bold text-white shadow-sm shadow-red-500/20 transition hover:bg-red-600">
                                        <iconify-icon icon="solar:eye-bold" class="text-base"></iconify-icon>
                                        View Product
                                    </a>

                                    <a :href="p.quoteHref ?? '{{ route('quotes.create') }}'"
                                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 transition hover:bg-slate-50">
                                        <iconify-icon icon="solar:document-add-bold"
                                            class="text-base text-red-500"></iconify-icon>
                                        Ask Quote
                                    </a>
                                </div>
                            </div>
                        </article>
                    </template>
                </div>
            </template>

            {{-- Mobile View all --}}
            <div class="mt-8 sm:hidden">
                <a href="{{ route('products.index') }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white">
                    View all products
                    <iconify-icon icon="solar:arrow-right-bold" class="text-base"></iconify-icon>
                </a>
            </div>
        </div>
    </section>

    {{-- Temporary inline script for debugging - remove once Vite is working --}}
    <script>
        document.addEventListener('alpine:init', () => {
            if (!window.homeCategories) {
                window.homeCategories = function() {
                    return {
                        categories: [],
                        loading: true,

                        async fetchCategories() {
                            console.log('🔍 Fetching categories...');
                            try {
                                const res = await fetch('/ajax/home/categories', {
                                    headers: {
                                        'Accept': 'application/json'
                                    }
                                });

                                if (!res.ok) throw new Error(`HTTP ${res.status}`);

                                this.categories = await res.json();
                                console.log('✅ Categories loaded:', this.categories.length);
                            } catch (e) {
                                console.error('❌ Failed to load categories:', e);
                                this.categories = [];
                            } finally {
                                this.loading = false;
                            }
                        }
                    }
                }
            }

            // Popular products fallback (in case Vite bundle isn't loaded)
            if (!window.homePopularProducts) {
                window.homePopularProducts = function() {
                    return {
                        items: [],
                        loading: true,

                        async fetchItems() {
                            try {
                                const res = await fetch('/ajax/home/popular-products', {
                                    headers: {
                                        'Accept': 'application/json'
                                    }
                                });

                                if (!res.ok) throw new Error(`HTTP ${res.status}`);

                                const data = await res.json();
                                this.items = data.items ?? [];
                            } catch (e) {
                                console.error('❌ Popular products load failed:', e);
                                this.items = [];
                            } finally {
                                this.loading = false;
                            }
                        },
                    }
                }
            }

            // Popular products V2 fallback (in case Vite bundle isn't loaded)
            if (!window.homePopularProductsV2) {
                window.homePopularProductsV2 = function() {
                    return {
                        items: [],
                        loading: true,

                        async fetchItems() {
                            try {
                                const res = await fetch('/ajax/home/popular-products', {
                                    headers: {
                                        'Accept': 'application/json'
                                    }
                                });

                                if (!res.ok) throw new Error(`HTTP ${res.status}`);

                                const data = await res.json();
                                this.items = (data.items ?? []).map((p) => ({
                                    ...p,
                                    quoteHref: p.quoteHref ?? null,
                                }));
                            } catch (e) {
                                console.error('❌ Popular products load failed:', e);
                                this.items = [];
                            } finally {
                                this.loading = false;
                            }
                        },
                    }
                }
            }

            // Typing text fallback (in case Vite bundle isn't loaded)
            if (!window.typingText) {
                window.typingText = function({
                    text,
                    texts,
                    speed = 80,
                    deleteSpeed = 40,
                    delay = 500,
                    hold = 15000,
                    gap = 600,
                    loop = true,
                }) {
                    const list = Array.isArray(texts) ? texts : (typeof text === 'string' ? [text] : []);
                    return {
                        displayText: '',
                        texts: list,
                        textIndex: 0,
                        charIndex: 0,
                        direction: 'typing',
                        _timer: null,

                        start() {
                            if (!this.texts.length) return;
                            this.stop();
                            this.displayText = '';
                            this.textIndex = 0;
                            this.charIndex = 0;
                            this.direction = 'typing';
                            this._timer = setTimeout(() => this.tick(), delay);
                        },

                        stop() {
                            if (this._timer) {
                                clearTimeout(this._timer);
                                this._timer = null;
                            }
                        },

                        currentText() {
                            return this.texts[this.textIndex] ?? '';
                        },

                        tick() {
                            const current = this.currentText();

                            if (this.direction === 'typing') {
                                if (this.charIndex < current.length) {
                                    this.displayText += current[this.charIndex];
                                    this.charIndex++;
                                    this._timer = setTimeout(() => this.tick(), speed);
                                    return;
                                }

                                this.direction = 'deleting';
                                this._timer = setTimeout(() => this.tick(), hold);
                                return;
                            }

                            if (this.charIndex > 0) {
                                this.displayText = this.displayText.slice(0, -1);
                                this.charIndex--;
                                this._timer = setTimeout(() => this.tick(), deleteSpeed);
                                return;
                            }

                            if (!loop && this.textIndex >= this.texts.length - 1) {
                                this.stop();
                                return;
                            }

                            this.textIndex = (this.textIndex + 1) % this.texts.length;
                            this.direction = 'typing';
                            this._timer = setTimeout(() => this.tick(), gap);
                        },
                    };
                }
            }
        });
    </script>


    {{-- Design Services Section --}}
    <section class="bg-slate-50/70">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-18">

            {{-- Heading --}}
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                        Design Services
                    </h2>
                    <p class="mt-3 max-w-2xl text-sm text-slate-600 sm:text-base">
                        No design? No stress. Our creative team crafts clean, print-ready visuals that match your brand
                        and
                        your purpose.
                    </p>
                </div>

                {{-- Small trust chips --}}
                <div class="flex flex-wrap gap-2">
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200/70">
                        <iconify-icon icon="solar:check-circle-bold" class="text-base text-red-500"></iconify-icon>
                        Print-ready output
                    </span>
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200/70">
                        <iconify-icon icon="solar:pen-new-square-bold" class="text-base text-red-500"></iconify-icon>
                        Revisions supported
                    </span>
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200/70">
                        <iconify-icon icon="solar:palette-round-bold" class="text-base text-red-500"></iconify-icon>
                        Brand-consistent
                    </span>
                </div>
            </div>

            {{-- Service cards --}}
            <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">

                {{-- Card: Logo & Brand --}}
                <div
                    class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 ring-1 ring-red-100 transition group-hover:bg-red-100">
                            <iconify-icon icon="solar:crown-bold" class="text-2xl text-red-500"></iconify-icon>
                        </div>

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                            Branding
                        </span>
                    </div>

                    <h3 class="mt-5 text-xl font-black text-slate-900">
                        Logo & Brand Identity
                    </h3>

                    <p class="mt-2 text-sm text-slate-600">
                        Build a strong first impression with a clean logo and consistent brand visuals that work across
                        print
                        and digital.
                    </p>

                    <ul class="mt-5 space-y-2 text-sm text-slate-700">
                        <li class="flex items-start gap-2">
                            <iconify-icon icon="solar:check-circle-bold"
                                class="mt-0.5 text-base text-red-500"></iconify-icon>
                            Logo concepts & refinements
                        </li>
                        <li class="flex items-start gap-2">
                            <iconify-icon icon="solar:check-circle-bold"
                                class="mt-0.5 text-base text-red-500"></iconify-icon>
                            Color palette & typography guidance
                        </li>
                        <li class="flex items-start gap-2">
                            <iconify-icon icon="solar:check-circle-bold"
                                class="mt-0.5 text-base text-red-500"></iconify-icon>
                            Files for print & social use
                        </li>
                    </ul>
                </div>

                {{-- Card: Marketing Prints --}}
                <div
                    class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 ring-1 ring-red-100 transition group-hover:bg-red-100">
                            <iconify-icon icon="solar:bill-list-bold" class="text-2xl text-red-500"></iconify-icon>
                        </div>

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                            Print
                        </span>
                    </div>

                    <h3 class="mt-5 text-xl font-black text-slate-900">
                        Flyers, Cards & Posters
                    </h3>

                    <p class="mt-2 text-sm text-slate-600">
                        Campaign-ready designs made for high conversion — correctly sized with bleed and safe margins.
                    </p>

                    <ul class="mt-5 space-y-2 text-sm text-slate-700">
                        <li class="flex items-start gap-2">
                            <iconify-icon icon="solar:check-circle-bold"
                                class="mt-0.5 text-base text-red-500"></iconify-icon>
                            Flyers, brochures, business cards
                        </li>
                        <li class="flex items-start gap-2">
                            <iconify-icon icon="solar:check-circle-bold"
                                class="mt-0.5 text-base text-red-500"></iconify-icon>
                            Posters, menu cards, vouchers
                        </li>
                        <li class="flex items-start gap-2">
                            <iconify-icon icon="solar:check-circle-bold"
                                class="mt-0.5 text-base text-red-500"></iconify-icon>
                            Print-proof provided before final
                        </li>
                    </ul>
                </div>

                {{-- Card: Social Media --}}
                <div
                    class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 ring-1 ring-red-100 transition group-hover:bg-red-100">
                            <iconify-icon icon="solar:gallery-wide-bold" class="text-2xl text-red-500"></iconify-icon>
                        </div>

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                            Digital
                        </span>
                    </div>

                    <h3 class="mt-5 text-xl font-black text-slate-900">
                        Social Media Creatives
                    </h3>

                    <p class="mt-2 text-sm text-slate-600">
                        Scroll-stopping posts, stories, and promo creatives designed to match your brand and boost
                        reach.
                    </p>

                    <ul class="mt-5 space-y-2 text-sm text-slate-700">
                        <li class="flex items-start gap-2">
                            <iconify-icon icon="solar:check-circle-bold"
                                class="mt-0.5 text-base text-red-500"></iconify-icon>
                            Posts, stories, cover banners
                        </li>
                        <li class="flex items-start gap-2">
                            <iconify-icon icon="solar:check-circle-bold"
                                class="mt-0.5 text-base text-red-500"></iconify-icon>
                            Monthly promo packs available
                        </li>
                        <li class="flex items-start gap-2">
                            <iconify-icon icon="solar:check-circle-bold"
                                class="mt-0.5 text-base text-red-500"></iconify-icon>
                            Exported in platform-ready sizes
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Deliverables strip --}}
            <div class="mt-10 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-start gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-white">
                            <iconify-icon icon="solar:shield-check-bold" class="text-2xl"></iconify-icon>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900">What you get</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                Every design is prepared with correct sizes, bleed, and export formats — ready for
                                professional
                                printing.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                            <iconify-icon icon="solar:check-circle-bold"
                                class="text-base text-red-500"></iconify-icon>
                            Proof before print
                        </span>
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                            <iconify-icon icon="solar:check-circle-bold"
                                class="text-base text-red-500"></iconify-icon>
                            Print-ready exports
                        </span>
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                            <iconify-icon icon="solar:check-circle-bold"
                                class="text-base text-red-500"></iconify-icon>
                            Revisions supported
                        </span>
                    </div>
                </div>
            </div>

            {{-- CTAs --}}
            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                <a href="{{ route('quotes.create') }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-500 px-6 py-3 text-sm font-bold text-white shadow-sm shadow-red-500/20 transition hover:bg-red-600 sm:w-auto">
                    <iconify-icon icon="solar:magic-stick-3-bold" class="text-lg"></iconify-icon>
                    Request a Design
                </a>

                <a href="{{ route('products.index') }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-6 py-3 text-sm font-bold text-white transition hover:bg-slate-800 sm:w-auto">
                    <iconify-icon icon="solar:cart-large-2-bold" class="text-lg"></iconify-icon>
                    View Printing Products
                    <iconify-icon icon="solar:arrow-right-bold" class="text-lg"></iconify-icon>
                </a>
            </div>

        </div>
    </section>

    {{-- Why Choose Printair Section --}}
    <section class="bg-slate-50/60">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-18">

            {{-- Heading --}}
            <div class="text-center">
                <h2 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                    Why Choose <span class="text-red-500">Printair Advertising</span>?
                </h2>

                <p class="mx-auto mt-3 max-w-2xl text-sm text-slate-600 sm:text-base">
                    Built for speed, quality, and consistency — from one-off prints to large-scale production.
                </p>
            </div>

            {{-- Cards --}}
            <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {{-- Card 1 --}}
                <div
                    class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 ring-1 ring-red-100">
                        <iconify-icon icon="solar:delivery-bold" class="text-2xl text-red-500"></iconify-icon>
                    </div>

                    <h3 class="mt-5 text-xl font-black text-slate-900 text-center">
                        Fast Turnaround
                    </h3>

                    <p class="mt-2 text-center text-sm text-slate-600">
                        Get most jobs ready within <span class="font-semibold text-slate-900">24–72 hours</span>,
                        with island-wide delivery available.
                    </p>

                    <div class="mt-5 flex justify-center">
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                            <iconify-icon icon="solar:clock-circle-bold"
                                class="text-base text-red-500"></iconify-icon>
                            Urgent jobs supported
                        </span>
                    </div>
                </div>

                {{-- Card 2 --}}
                <div
                    class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 ring-1 ring-red-100">
                        <iconify-icon icon="solar:palette-round-bold" class="text-2xl text-red-500"></iconify-icon>
                    </div>

                    <h3 class="mt-5 text-xl font-black text-slate-900 text-center">
                        Premium Print Quality
                    </h3>

                    <p class="mt-2 text-center text-sm text-slate-600">
                        Accurate colors, sharp details, and clean finishing using
                        <span class="font-semibold text-slate-900">industrial-grade</span> materials.
                    </p>

                    <div class="mt-5 flex justify-center">
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                            <iconify-icon icon="solar:stars-bold" class="text-base text-red-500"></iconify-icon>
                            Consistent results
                        </span>
                    </div>
                </div>

                {{-- Card 3 --}}
                <div
                    class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 ring-1 ring-red-100">
                        <iconify-icon icon="solar:users-group-two-rounded-bold"
                            class="text-2xl text-red-500"></iconify-icon>
                    </div>

                    <h3 class="mt-5 text-xl font-black text-slate-900 text-center">
                        <span class="text-red-500">1000+ </span>Trusted Clients
                    </h3>

                    <p class="mt-2 text-center text-sm text-slate-600">
                        Chosen by businesses, brands, and creators across Sri Lanka — for reliable output every time.
                    </p>

                    <div class="mt-5 flex justify-center">
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                            <iconify-icon icon="solar:shield-check-bold"
                                class="text-base text-red-500"></iconify-icon>
                            Proven & trusted
                        </span>
                    </div>
                </div>
            </div>

            {{-- Micro CTA --}}
            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                <a href="{{ route('products.index') }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-6 py-3 text-sm font-bold text-white transition hover:bg-slate-800 sm:w-auto">
                    <iconify-icon icon="solar:cart-large-2-bold" class="text-lg"></iconify-icon>
                    Explore Products
                    <iconify-icon icon="solar:arrow-right-bold" class="text-lg"></iconify-icon>
                </a>

                <a href="{{ route('quotes.create') }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-red-500 bg-white px-6 py-3 text-sm font-bold text-red-600 transition hover:bg-red-50 sm:w-auto">
                    <iconify-icon icon="solar:document-add-bold" class="text-lg"></iconify-icon>
                    Ask for Quote
                </a>
            </div>

        </div>
    </section>

    {{-- Typing Slogan Section --}}
    <section class="bg-red-500">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:py-18 text-center">

            <h2 x-data="typingText({
                texts: [
                    'You think it, We ink it',
                    'Where creativity meets quality.',
                    'Your ideas. Our ink. Endless possibilities.',
                    'Prints that speak louder than words.',
                ],
                speed: 70,
                deleteSpeed: 40,
                delay: 600,
                hold: 15000,
                gap: 600,
            })" x-init="start()"
                class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight">
                <span x-text="displayText"></span>
                <span class="ml-1 inline-block w-[2px] h-[1.1em] bg-white align-middle animate-blink"
                    aria-hidden="true"></span>
            </h2>

        </div>
    </section>


    {{-- How It Works Section --}}
    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-18">

            {{-- Heading --}}
            <div class="text-center">
                <h2 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                    How It Works
                </h2>
                <p class="mx-auto mt-3 max-w-2xl text-sm text-slate-600 sm:text-base">
                    From idea to delivery — a streamlined workflow that’s fast, clear, and reliable.
                </p>
            </div>

            {{-- Steps --}}
            <div class="relative mt-10">
                {{-- Desktop connector line --}}
                <div class="pointer-events-none absolute left-0 right-0 top-[58px] hidden lg:block">
                    <div class="mx-auto h-px max-w-6xl bg-slate-200"></div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    {{-- Step 1 --}}
                    <div
                        class="group relative rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 ring-1 ring-red-100 transition group-hover:bg-red-100">
                            <iconify-icon icon="solar:lightbulb-bolt-bold"
                                class="text-2xl text-red-500"></iconify-icon>
                        </div>

                        <div class="mt-4 text-center">
                            <div class="text-xs font-bold text-red-600">Step 01</div>
                            <h3 class="mt-2 text-xl font-semibold text-slate-900">Share Your Idea</h3>
                            <p class="mt-2 text-sm text-slate-600">
                                Tell us what you need — design, print, or both. We’ll guide you to the best option.
                            </p>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div
                        class="group relative rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 ring-1 ring-red-100 transition group-hover:bg-red-100">
                            <iconify-icon icon="solar:pen-new-square-bold"
                                class="text-2xl text-red-500"></iconify-icon>
                        </div>

                        <div class="mt-4 text-center">
                            <div class="text-xs font-bold text-red-600">Step 02</div>
                            <h3 class="mt-2 text-xl font-semibold text-slate-900">We Design It</h3>
                            <p class="mt-2 text-sm text-slate-600">
                                Our team prepares a clean, print-ready layout with accurate sizing and bleed.
                            </p>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div
                        class="group relative rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 ring-1 ring-red-100 transition group-hover:bg-red-100">
                            <iconify-icon icon="solar:checklist-minimalistic-bold"
                                class="text-2xl text-red-500"></iconify-icon>
                        </div>

                        <div class="mt-4 text-center">
                            <div class="text-xs font-bold text-red-600">Step 03</div>
                            <h3 class="mt-2 text-xl font-semibold text-slate-900">Approve & Confirm</h3>
                            <p class="mt-2 text-sm text-slate-600">
                                You review the proof and request final edits if needed — then approve for production.
                            </p>
                        </div>
                    </div>

                    {{-- Step 4 --}}
                    <div
                        class="group relative rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 ring-1 ring-red-100 transition group-hover:bg-red-100">
                            <iconify-icon icon="solar:delivery-bold" class="text-2xl text-red-500"></iconify-icon>
                        </div>

                        <div class="mt-4 text-center">
                            <div class="text-xs font-bold text-red-600">Step 04</div>
                            <h3 class="mt-2 text-xl font-semibold text-slate-900">Print & Deliver</h3>
                            <p class="mt-2 text-sm text-slate-600">
                                We print with commercial-grade precision and deliver island-wide.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Micro CTA --}}
            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                <a href="#"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-500 px-6 py-3 text-sm font-bold text-white shadow-sm shadow-red-500/20 transition hover:bg-red-600 sm:w-auto">
                    <iconify-icon icon="solar:magic-stick-3-bold" class="text-lg"></iconify-icon>
                    Start Designing
                </a>

                <a href="#"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-900 transition hover:bg-slate-50 sm:w-auto">
                    <iconify-icon icon="solar:document-add-bold" class="text-lg text-red-500"></iconify-icon>
                    Ask for Quote
                </a>
            </div>

        </div>
    </section>


    {{-- Testimonials Section --}}
    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-18">

            {{-- Heading --}}
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                        What Our Clients Say
                    </h2>
                    <p class="mt-3 max-w-2xl text-sm text-slate-600 sm:text-base">
                        Real feedback from businesses and creators who trust Printair for consistent quality and fast
                        delivery.
                    </p>
                </div>

                {{-- Trust chips --}}
                <div class="flex flex-wrap gap-2">
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                        <iconify-icon icon="solar:shield-check-bold" class="text-base text-red-500"></iconify-icon>
                        Verified quality
                    </span>
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                        <iconify-icon icon="solar:delivery-bold" class="text-base text-red-500"></iconify-icon>
                        Island-wide delivery
                    </span>
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                        <iconify-icon icon="solar:star-bold" class="text-base text-red-500"></iconify-icon>
                        Trusted by 500+
                    </span>
                </div>
            </div>

            {{-- Horizontal snap carousel (no library) --}}
            <div class="mt-10">
                <div
                    class="flex gap-6 overflow-x-auto pb-4 snap-x snap-mandatory scroll-smooth
                        [-ms-overflow-style:none] [scrollbar-width:none]
                        [&::-webkit-scrollbar]:hidden">

                    {{-- Testimonial Card --}}
                    @php
                        $testimonials = [
                            [
                                'name' => 'Nimal Perera',
                                'role' => 'Owner',
                                'company' => 'Nimal Traders',
                                'quote' =>
                                    'Fast delivery and the print quality is super clean. The colors came out exactly as expected. Highly recommended.',
                                'rating' => 5,
                                'tag' => 'Fast turnaround',
                            ],
                            [
                                'name' => 'Tharushi Silva',
                                'role' => 'Marketing Executive',
                                'company' => 'Bloom Cafe',
                                'quote' =>
                                    'They helped us with design + printing. The final output looked premium and the finishing was perfect.',
                                'rating' => 5,
                                'tag' => 'Premium finishing',
                            ],
                            [
                                'name' => 'Kasun Jayasinghe',
                                'role' => 'Founder',
                                'company' => 'Kasun Events',
                                'quote' =>
                                    'Our banners and stickers were on point. Great communication, proof approval was smooth, and delivery was quick.',
                                'rating' => 5,
                                'tag' => 'Smooth process',
                            ],
                            [
                                'name' => 'Dilshan Fernando',
                                'role' => 'Store Manager',
                                'company' => 'Urban Wear LK',
                                'quote' =>
                                    'Reliable printing partner. Consistent quality across batches and the team is easy to work with.',
                                'rating' => 5,
                                'tag' => 'Consistent quality',
                            ],
                        ];
                    @endphp

                    @foreach ($testimonials as $t)
                        <article
                            class="snap-start w-[88%] sm:w-[58%] lg:w-[32%] shrink-0 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">

                            {{-- Top row --}}
                            <div class="flex items-start gap-3">
                                {{-- Avatar --}}
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-white">
                                    <span class="text-sm font-black">
                                        {{ mb_substr($t['name'], 0, 1) }}
                                    </span>
                                </div>

                                <div class="min-w-0">
                                    {{-- Name + verified --}}
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-black text-slate-900 truncate">
                                            {{ $t['name'] }}
                                        </h3>

                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-bold text-green-700 ring-1 ring-green-100">
                                            <iconify-icon icon="solar:verified-check-bold"
                                                class="text-sm"></iconify-icon>
                                            Verified
                                        </span>
                                    </div>

                                    {{-- Role + company --}}
                                    <p class="mt-0.5 text-xs text-slate-600">
                                        {{ $t['role'] }} • <span
                                            class="font-semibold text-slate-900">{{ $t['company'] }}</span>
                                    </p>

                                    {{-- Rating (moved here) --}}
                                    <div class="mt-2 flex items-center gap-1">
                                        @for ($i = 0; $i < $t['rating']; $i++)
                                            <iconify-icon icon="solar:star-bold"
                                                class="text-sm text-red-400"></iconify-icon>
                                        @endfor

                                        <span class="ml-1 text-xs font-semibold text-slate-600">
                                            {{ $t['rating'] }}.0
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Quote --}}
                            <div class="mt-5">
                                <iconify-icon icon="solar:quote-up-bold"
                                    class="text-2xl text-red-500/70"></iconify-icon>
                                <p class="mt-3 text-sm leading-relaxed text-slate-700">
                                    {{ $t['quote'] }}
                                </p>
                            </div>

                            {{-- Bottom tag --}}
                            <div class="mt-6 flex flex-wrap items-center gap-2">
                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                    <iconify-icon icon="solar:bolt-circle-bold"
                                        class="text-base text-red-500"></iconify-icon>
                                    {{ $t['tag'] }}
                                </span>

                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                    <iconify-icon icon="solar:chat-round-bold"
                                        class="text-base text-red-500"></iconify-icon>
                                    Friendly support
                                </span>
                            </div>
                        </article>
                    @endforeach

                </div>

                {{-- Hint text --}}
                <p class="mt-3 text-center text-xs text-slate-500">
                    Tip: Swipe horizontally to see more reviews.
                </p>
            </div>

            {{-- CTA --}}
            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                <a href="#"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-500 px-6 py-3 text-sm font-bold text-white shadow-sm shadow-red-500/20 transition hover:bg-red-600 sm:w-auto">
                    <iconify-icon icon="solar:document-add-bold" class="text-lg"></iconify-icon>
                    Ask for Quote
                </a>

                <a href="#"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-6 py-3 text-sm font-bold text-white transition hover:bg-slate-800 sm:w-auto">
                    <iconify-icon icon="solar:cart-large-2-bold" class="text-lg"></iconify-icon>
                    Explore Products
                    <iconify-icon icon="solar:arrow-right-bold" class="text-lg"></iconify-icon>
                </a>
            </div>

        </div>
    </section>


    {{-- Partner Program / Working Group Section --}}
    <section class="bg-slate-900 text-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">

            {{-- Heading --}}
            <div class="mx-auto max-w-3xl text-center">
                <h2 class="text-3xl font-bold tracking-tight sm:text-4xl text-red-500">
                    Printair Partner Program (B2B)
                </h2>
                <p class="mt-4 text-sm text-slate-300 sm:text-base">
                    Built for agencies, businesses, and resellers who print regularly and expect better pricing,
                    priority handling, and full control.
                </p>
            </div>

            {{-- Benefits --}}
            <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">

                {{-- Benefit --}}
                <div class="rounded-3xl bg-white/5 p-6 ring-1 ring-white/10 backdrop-blur">
                    <iconify-icon icon="solar:wallet-money-bold" class="text-3xl text-red-400"></iconify-icon>
                    <h3 class="mt-4 text-lg font-black">Lower Partner Pricing</h3>
                    <p class="mt-2 text-sm text-slate-300">
                        Access exclusive partner rates designed for bulk, repeat, and high-volume printing.
                    </p>
                </div>

                {{-- Benefit --}}
                <div class="rounded-3xl bg-white/5 p-6 ring-1 ring-white/10 backdrop-blur">
                    <iconify-icon icon="solar:layers-bold" class="text-3xl text-red-400"></iconify-icon>
                    <h3 class="mt-4 text-lg font-black">Dedicated Pricing Group</h3>
                    <p class="mt-2 text-sm text-slate-300">
                        Each partner operates under a private pricing group with controlled access and visibility.
                    </p>
                </div>

                {{-- Benefit --}}
                <div class="rounded-3xl bg-white/5 p-6 ring-1 ring-white/10 backdrop-blur">
                    <iconify-icon icon="solar:shield-check-bold" class="text-3xl text-red-400"></iconify-icon>
                    <h3 class="mt-4 text-lg font-black">Consistent & Locked Rates</h3>
                    <p class="mt-2 text-sm text-slate-300">
                        Your prices remain stable across orders — no surprises, no fluctuations.
                    </p>
                </div>

                {{-- Benefit --}}
                <div class="rounded-3xl bg-white/5 p-6 ring-1 ring-white/10 backdrop-blur">
                    <iconify-icon icon="solar:cpu-bolt-bold" class="text-3xl text-red-400"></iconify-icon>
                    <h3 class="mt-4 text-lg font-black">Priority Production</h3>
                    <p class="mt-2 text-sm text-slate-300">
                        Faster turnaround and priority handling for approved partner orders.
                    </p>
                </div>

            </div>

            {{-- WG Explanation --}}
            <div class="mt-12 rounded-3xl bg-white/5 p-8 ring-1 ring-white/10 backdrop-blur">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

                    <div>
                        <h3 class="text-xl font-black">
                            How Partner Access Works
                        </h3>
                        <p class="mt-2 max-w-2xl text-sm text-slate-300">
                            Once approved, your business is assigned to a dedicated partner group.
                            You’ll see your own pricing, eligible products, and services — automatically applied
                            every time you place an order.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-semibold">
                            <iconify-icon icon="solar:user-id-bold" class="text-base text-red-400"></iconify-icon>
                            Partner login
                        </span>
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-semibold">
                            <iconify-icon icon="solar:tag-price-bold" class="text-base text-red-400"></iconify-icon>
                            Auto-applied pricing
                        </span>
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-semibold">
                            <iconify-icon icon="solar:lock-keyhole-bold"
                                class="text-base text-red-400"></iconify-icon>
                            Private access
                        </span>
                    </div>

                </div>
            </div>

            {{-- CTA --}}
            <div class="mt-12 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-500 px-7 py-3 text-sm font-bold text-white shadow-lg shadow-red-500/20 transition hover:bg-red-600">
                    <iconify-icon icon="solar:handshake-bold" class="text-lg"></iconify-icon>
                    Become a Printair Partner
                </a>

                <a href="#"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 px-7 py-3 text-sm font-bold text-white transition hover:bg-white/10">
                    <iconify-icon icon="solar:document-add-bold" class="text-lg"></iconify-icon>
                    Request Wholesale Pricing
                </a>
            </div>

        </div>
    </section>

    {{-- Contact Us Section --}}
    <section id="contact" class="bg-slate-50/70">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-18">

            {{-- Heading --}}
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                        Contact Us
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-600 sm:text-base">
                        Tell us what you need — printing, design, or a partner account. We’ll guide you fast.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="https://wa.me/94768860175" target="_blank"
                        class="inline-flex items-center gap-2 rounded-full bg-green-50 px-4 py-2 text-xs font-bold text-green-700 ring-1 ring-green-100 hover:bg-green-100">
                        <iconify-icon icon="logos:whatsapp-icon" class="text-base"></iconify-icon>
                        WhatsApp
                    </a>

                    <a href="tel:+94768860175"
                        class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-bold text-slate-700 ring-1 ring-slate-200/70 hover:bg-slate-50">
                        <iconify-icon icon="solar:phone-bold" class="text-base text-red-500"></iconify-icon>
                        Call us
                    </a>
                </div>
            </div>

            {{-- Alerts --}}
            <div class="mt-6 space-y-3">
                @if (session('success'))
                    <div
                        class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div
                        class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
                        {{ session('error') }}
                    </div>
                @endif
            </div>

            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-12">

                {{-- Map --}}
                <div class="lg:col-span-7">
                    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                            <div class="flex items-center gap-2">
                                <iconify-icon icon="solar:map-point-bold" class="text-xl text-red-500"></iconify-icon>
                                <div>
                                    <div class="text-sm font-black text-slate-900">Visit Printair</div>
                                    <div class="text-xs text-slate-500">{{ config('contact.address') }}</div>
                                </div>
                            </div>

                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(config('contact.address')) }}"
                                target="_blank" class="text-xs font-bold text-red-600 hover:text-red-700">
                                Open in Maps →
                            </a>
                        </div>

                        <div class="aspect-[16/10] bg-slate-100">

                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d416.2046565273029!2d79.92673769212446!3d7.052887018883192!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae2f9e69a99f8bb%3A0x22ee736733e2cc74!2sPrintair!5e0!3m2!1sen!2slk!4v1766143081736!5m2!1sen!2slk"
                                class="h-full w-full" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>

                        </div>
                    </div>

                    {{-- Contact cards --}}
                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-red-50 ring-1 ring-red-100">
                                    <iconify-icon icon="solar:phone-bold" class="text-xl text-red-500"></iconify-icon>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-slate-900">Phone</div>
                                    <a href="tel:+94768860175"
                                        class="mt-1 inline-block text-sm font-semibold text-slate-700 hover:text-slate-900">
                                        +94768860175
                                    </a>
                                    <div class="mt-1 text-xs text-slate-500">Mon–Sat • 9am–7pm</div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-green-50 ring-1 ring-green-100">
                                    <iconify-icon icon="logos:whatsapp-icon" class="text-xl"></iconify-icon>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-slate-900">WhatsApp</div>
                                    <a href="https://wa.me/94768860175" target="_blank"
                                        class="mt-1 inline-block text-sm font-semibold text-slate-700 hover:text-slate-900">
                                        Chat on WhatsApp
                                    </a>
                                    <div class="mt-1 text-xs text-slate-500">Fast replies for quotes</div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:col-span-2">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 ring-1 ring-slate-200/70">
                                    <iconify-icon icon="solar:mailbox-bold"
                                        class="text-xl text-red-500"></iconify-icon>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-slate-900">Email</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-700">
                                        contact@printair.lk
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">For partner pricing & corporate printing
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form --}}
                <div class="lg:col-span-5">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-xl font-black text-slate-900">Send us a message</h3>
                                <p class="mt-1 text-sm text-slate-600">
                                    We’ll respond as soon as possible. Partner inquiries are welcome.
                                </p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('contact.submit') }}" class="mt-6 space-y-4">
                            @csrf

                            <div>
                                <label class="text-sm font-bold text-slate-700">Name</label>
                                <input name="name" value="{{ old('name') }}"
                                    class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none ring-red-500/30 focus:ring-4"
                                    placeholder="Your name" />
                                @error('name')
                                    <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm font-bold text-slate-700">Email (optional)</label>
                                    <input name="email" type="email" value="{{ old('email') }}"
                                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none ring-red-500/30 focus:ring-4"
                                        placeholder="you@email.com" />
                                    @error('email')
                                        <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="text-sm font-bold text-slate-700">Phone / WhatsApp (optional)</label>
                                    <input name="phone" value="{{ old('phone') }}"
                                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none ring-red-500/30 focus:ring-4"
                                        placeholder="+94 77 123 4567" />
                                    @error('phone')
                                        <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-bold text-slate-700">Subject</label>
                                <input name="subject" value="{{ old('subject') }}"
                                    class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none ring-red-500/30 focus:ring-4"
                                    placeholder="e.g., Need a quote for X-banners / Partner pricing request" />
                                @error('subject')
                                    <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="text-sm font-bold text-slate-700">Message</label>
                                <textarea name="message" rows="5"
                                    class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none ring-red-500/30 focus:ring-4"
                                    placeholder="Tell us what you need, quantity, size, timeline, delivery location, etc.">{{ old('message') }}</textarea>
                                @error('message')
                                    <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="pt-2 flex flex-col gap-3 sm:flex-row">
                                <button type="submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-500 px-6 py-3 text-sm font-black text-white shadow-sm shadow-red-500/20 transition hover:bg-red-600 sm:w-auto">
                                    <iconify-icon icon="solar:paper-plane-bold" class="text-lg"></iconify-icon>
                                    Send Message
                                </button>

                                <a href="https://wa.me/{{ config('contact.whatsapp') }}" target="_blank"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-black text-slate-900 transition hover:bg-slate-50 sm:w-auto">
                                    <iconify-icon icon="logos:whatsapp-icon" class="text-lg"></iconify-icon>
                                    WhatsApp Instead
                                </a>
                            </div>
                        </form>
                    </div>

                    <p class="mt-4 text-xs text-slate-500">
                        By submitting, you agree to be contacted by Printair regarding your request.
                    </p>
                </div>

            </div>
        </div>
    </section>

</x-home-layout>
