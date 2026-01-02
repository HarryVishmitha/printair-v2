<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-seo.meta :title="$seo['title'] ?? null" :description="$seo['description'] ?? null" :keywords="$seo['keywords'] ?? null" :image="$seo['image'] ?? null" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="canonical" href="https://example.com/page">

    <!-- branding -->
    <link rel="icon" href="{{ asset('assets/printair/favicon.ico') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('assets/printair/favicon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/printair/favicon.png') }}">
    <link rel="manifest" href="{{ asset('assets/printair/site.webmanifest') }}">

    <!-- SEO engines-->
    <meta name="googlebot" content="index, follow">
    <meta name="bingbot" content="index, follow">
    <meta name="google" content="nositelinkssearchbox"> <!-- optional -->
    <meta name="rating" content="general">
    <meta name="referrer" content="no-referrer-when-downgrade">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <!-- Structured Data -->
    <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "Organization",
            "name": "Printair Advertising",
            "url": "https://printair.lk",
            "logo": "https://printair.lk/assets/logo.webp",
            "sameAs": [
                "https://facebook.com/printair",
                "https://instagram.com/printair"
            ]
        }
    </script>


    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:400,500,600,700,800,900&display=swap"
        rel="stylesheet" />
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="antialiased bg-gray-50 text-gray-900 font-sans" x-data="{
    mobileNavOpen: false,

    navMegaOpen: false,
    navLoaded: false,
    navLoading: false,
    navError: null,
    navData: null,

    cartCount: 0,
    cartCountLoading: false,

    async loadNavbarOnce() {
        if (this.navLoaded || this.navLoading) return;

        this.navLoading = true;
        this.navError = null;

        try {
            const res = await fetch('{{ route('api.public.navbar') }}', {
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) throw new Error('Navbar API failed: ' + res.status);

            this.navData = await res.json();
            this.navLoaded = true;
        } catch (e) {
            console.error(e);
            this.navError = 'Unable to load categories right now.';
        } finally {
            this.navLoading = false;
        }
    },

    async loadCartCount() {
        if (this.cartCountLoading) return;
        this.cartCountLoading = true;

        try {
            const res = await fetch('{{ route('cart.show') }}?json=1', {
                headers: { 'Accept': 'application/json' }
            });

            const data = await res.json().catch(() => ({}));
            const items = (data?.cart?.items || []);
            this.cartCount = Array.isArray(items) ?
                items.reduce((sum, it) => sum + Number(it?.qty || 0), 0) :
                0;
        } catch (e) {
            this.cartCount = 0;
        } finally {
            this.cartCountLoading = false;
        }
    },
}" x-init="loadCartCount()"
    @cart-updated.window="loadCartCount()" @keydown.window.escape="navMegaOpen = false; mobileNavOpen = false">

    <!-- Home Layout -->
    <!-- Top bar -->
    <div x-data="smartHeader({ lock: () => mobileNavOpen || navMegaOpen })" x-init="init()">
        <div x-ref="header"
            class="fixed top-0 left-0 right-0 z-[70] will-change-transform transform-gpu transition-transform duration-200 ease-out motion-reduce:transition-none"
            :class="[
                isHidden ? '-translate-y-full' : 'translate-y-0',
                isElevated ? 'shadow-sm shadow-slate-900/10' : 'shadow-none'
            ]">
            <header class="w-full">
                {{-- Top bar --}}
                <div class="hidden md:block w-full border-b border-slate-200 bg-slate-50 text-slate-700">
                    <div
                        class="mx-auto md:flex max-w-7xl items-center justify-between gap-3 px-4 py-2 text-xs md:px-6 lg:px-8 md:text-[13px] hidden">

                        {{-- Left: Contact details --}}
                        <div class="flex flex-wrap items-center gap-3 md:gap-5">

                            <a href="tel:+94114909907"
                                class="inline-flex items-center gap-1.5 font-medium hover:text-red-600 transition-colors">
                                <iconify-icon icon="mdi:phone" class="text-[14px]"></iconify-icon>
                                <span>011 490 9907</span>
                            </a>

                            <a href="mailto:info@printair.lk"
                                class="inline-flex items-center gap-1.5 hover:text-red-600 transition-colors">
                                <iconify-icon icon="mdi:email-outline" class="text-[14px]"></iconify-icon>
                                <span>contact@printair.lk</span>
                            </a>

                        </div>

                        {{-- Right: Service badges --}}
                        <div class="hidden flex-wrap items-center justify-end gap-2 md:flex md:gap-3">

                            <span
                                class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-[11px] font-medium shadow-sm ring-1 ring-slate-200">
                                <iconify-icon icon="mdi:truck-delivery" class="text-[14px] text-red-500"></iconify-icon>
                                <span class="ml-1 text-gray-500">Island-wide Delivery</span>
                            </span>

                            <span
                                class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-[11px] font-medium shadow-sm ring-1 ring-slate-200">
                                <iconify-icon icon="mdi:lightning-bolt-outline"
                                    class="text-[14px] text-red-500"></iconify-icon>
                                <span class="ml-1 text-gray-500">24h Express Printing</span>
                            </span>

                            <span
                                class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-[11px] font-medium shadow-sm ring-1 ring-slate-200">
                                <iconify-icon icon="mdi:shield-lock-outline"
                                    class="text-[14px] text-red-500"></iconify-icon>
                                <span class="ml-1 text-gray-500">Secure Online Payments</span>
                            </span>

                        </div>
                    </div>
                </div>

                {{-- Branding --}}
                <div class="w-full border-b border-slate-200 bg-slate-50 text-slate-700">
                    <div class="mx-auto max-w-7xl px-4 py-3 md:px-6 lg:px-8">

                        {{-- 🔹 MOBILE: Logo + Sidebar Toggle --}}
                        <div class="flex items-center justify-between md:hidden">
                            <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                                <img src="{{ asset('assets/printair/printairlogo.png') }}" alt="Printair Advertising"
                                    class="h-10 w-auto" loading="lazy" />
                                <span class="sr-only">Printair Advertising</span>
                            </a>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('cart.show') }}"
                                    class="relative inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 p-2.5 text-slate-700 shadow-sm hover:border-red-500 hover:text-red-600 transition-colors"
                                    aria-label="Cart">
                                    <iconify-icon icon="mdi:cart-outline" class="text-[22px]"></iconify-icon>
                                    <span x-show="cartCount > 0" x-cloak
                                        class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-[#ef233c] text-white text-[10px] font-extrabold flex items-center justify-center">
                                        <span x-text="cartCount"></span>
                                    </span>
                                </a>

                                <button type="button"
                                    class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 p-2.5 text-slate-700 shadow-sm hover:border-red-500 hover:text-red-600 transition-colors"
                                    aria-label="Open navigation" @click="mobileNavOpen = true; loadNavbarOnce()">
                                    <iconify-icon icon="gg:menu-right" class="text-[22px]"></iconify-icon>
                                </button>
                            </div>
                        </div>

                        {{-- 🔹 DESKTOP BRANDING (your original block, only md+) --}}
                        <div
                            class="mx-auto mt-3 hidden max-w-7xl flex-col gap-3 justify-between md:mt-0 md:flex md:flex-row md:items-center md:gap-6">

                            {{-- Left: Logo --}}
                            <div class="flex items-center gap-3">
                                <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                                    <img src="{{ asset('assets/printair/printairlogo.png') }}"
                                        alt="Printair Advertising" class="h-14 w-auto md:h-15" loading="lazy" />
                                    <span class="sr-only">Printair Advertising</span>
                                </a>
                            </div>

                            {{-- Center: Search Bar (desktop / tablet) --}}
                            <form action="#" method="GET" class="hidden flex-1 items-center md:flex">
                                <label class="relative flex w-full items-center">
                                    <span class="pointer-events-none absolute left-3">
                                        <iconify-icon icon="mdi:magnify"
                                            class="text-slate-400 text-[18px]"></iconify-icon>
                                    </span>

                                    <input type="text" name="q"
                                        placeholder="Search products, services, or job name…"
                                        class="w-full rounded-full border border-slate-200 bg-slate-50 px-9 py-2 text-sm text-slate-800 placeholder:text-slate-400 shadow-sm focus:border-red-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500" />

                                    <button type="submit"
                                        class="ml-2 hidden shrink-0 rounded-full bg-red-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-red-700 transition-colors">
                                        Search
                                    </button>
                                </label>
                            </form>

                            {{-- Cart + Desktop Mega Menu --}}
                            <div class="hidden shrink-0 md:flex items-center gap-2">
                                <a href="{{ route('cart.show') }}"
                                    class="relative inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm hover:border-red-500 hover:text-red-600 transition-colors">
                                    <iconify-icon icon="mdi:cart-outline" class="text-[18px]"></iconify-icon>
                                    <span>Cart</span>
                                    <span x-show="cartCount > 0" x-cloak
                                        class="ml-1 inline-flex min-w-[18px] h-[18px] px-1 rounded-full bg-[#ef233c] text-white text-[10px] font-extrabold items-center justify-center">
                                        <span x-text="cartCount"></span>
                                    </span>
                                </a>

                                <div class="relative" @mouseenter="navMegaOpen = true; loadNavbarOnce()"
                                    @mouseleave="navMegaOpen = false" @click.outside="navMegaOpen = false">

                                    <button type="button"
                                        @click="navMegaOpen = !navMegaOpen; if (navMegaOpen) loadNavbarOnce()"
                                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm hover:border-red-500 hover:text-red-600 transition-colors">
                                        <iconify-icon icon="mdi:apps" class="text-[18px]"></iconify-icon>
                                        <span>Categories</span>
                                        <iconify-icon icon="mdi:chevron-down" class="text-[18px]"></iconify-icon>
                                    </button>

                                    {{-- Panel --}}
                                    <div x-show="navMegaOpen" x-cloak @click.stop
                                        x-transition:enter="transition ease-out duration-150"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-120"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 translate-y-1"
                                        class="absolute right-0 mt-12 w-[920px] max-w-[calc(100vw-2rem)] rounded-2xl border border-slate-200 bg-white shadow-xl ring-1 ring-black/5 overflow-hidden z-50">

                                        <div class="flex min-h-[360px]" x-data="{ activeIdx: 0 }">

                                            {{-- Left: category list --}}
                                            <div class="w-64 border-r border-slate-200 bg-slate-50/70 p-4">
                                                <div
                                                    class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                    Browse
                                                </div>

                                                {{-- Skeleton for category list --}}
                                                <template x-if="navLoading && !navLoaded">
                                                    <div class="space-y-2">
                                                        <div class="h-10 rounded-xl bg-slate-200/70 animate-pulse">
                                                        </div>
                                                        <div class="h-10 rounded-xl bg-slate-200/70 animate-pulse">
                                                        </div>
                                                        <div class="h-10 rounded-xl bg-slate-200/70 animate-pulse">
                                                        </div>
                                                        <div class="h-10 rounded-xl bg-slate-200/70 animate-pulse">
                                                        </div>
                                                        <div class="h-10 rounded-xl bg-slate-200/70 animate-pulse">
                                                        </div>
                                                    </div>
                                                </template>

                                                {{-- Error --}}
                                                <template x-if="navError">
                                                    <div
                                                        class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                                        <div class="font-semibold">Oops</div>
                                                        <div class="mt-1" x-text="navError"></div>
                                                    </div>
                                                </template>

                                                {{-- Categories (loaded) --}}
                                                <template x-if="navLoaded && navData?.categories?.length">
                                                    <div class="space-y-1 max-h-[300px] overflow-y-auto pr-1">
                                                        <template x-for="(cat, idx) in navData.categories"
                                                            :key="cat.id">
                                                            <a :href="cat.url" @mouseenter="activeIdx = idx"
                                                                class="w-full group flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold transition"
                                                                :class="activeIdx === idx ?
                                                                    'bg-white text-slate-900 shadow-sm ring-1 ring-slate-200' :
                                                                    'text-slate-700 hover:bg-white hover:text-slate-900'">
                                                                <span class="truncate" x-text="cat.name"></span>
                                                                <template x-if="cat.cover_image_url">
                                                                    <img :src="cat.cover_image_url"
                                                                        :alt="cat.name"
                                                                        class="h-6 w-6 flex-shrink-0 rounded-full object-cover object-center ring-1 ring-slate-200" />
                                                                </template>
                                                                <template x-if="!cat.cover_image_url">
                                                                    <iconify-icon icon="mdi:chevron-right"
                                                                        class="text-[18px] opacity-60 group-hover:opacity-90"></iconify-icon>
                                                                </template>
                                                            </a>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>

                                            {{-- Right: products grid --}}
                                            <div class="flex-1 p-5">

                                                {{-- Skeleton products grid --}}
                                                <template x-if="navLoading && !navLoaded">
                                                    <div class="grid grid-cols-3 gap-4">
                                                        <template x-for="i in 6" :key="i">
                                                            <div class="rounded-2xl border border-slate-200 p-3">
                                                                <div
                                                                    class="aspect-[4/3] w-full rounded-xl bg-slate-200/70 animate-pulse">
                                                                </div>
                                                                <div
                                                                    class="mt-3 h-4 w-4/5 rounded bg-slate-200/70 animate-pulse">
                                                                </div>
                                                                <div
                                                                    class="mt-2 h-4 w-2/5 rounded bg-slate-200/70 animate-pulse">
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>

                                                {{-- Loaded products --}}
                                                <template x-if="navLoaded && navData?.categories?.length">
                                                    <div>
                                                        <div class="mb-4 flex items-center justify-between">
                                                            <div>
                                                                <div
                                                                    class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                                    Top picks
                                                                </div>
                                                                <div class="text-lg font-bold text-slate-900"
                                                                    x-text="navData.categories[activeIdx]?.name ?? ''">
                                                                </div>
                                                            </div>

                                                            <a class="text-sm font-semibold text-red-600 hover:text-red-700"
                                                                :href="navData.categories[activeIdx]?.url">
                                                                View all →
                                                            </a>
                                                        </div>

                                                        <div class="grid grid-cols-3 gap-4">
                                                            <template
                                                                x-for="p in (navData.categories[activeIdx]?.products ?? [])"
                                                                :key="p.id">
                                                                <a :href="p.url" @click="navMegaOpen = false"
                                                                    class="group rounded-2xl border border-slate-200 bg-white p-3 shadow-sm hover:shadow-md hover:border-red-200 transition">
                                                                    <div
                                                                        class="aspect-[4/3] overflow-hidden rounded-xl bg-slate-100">
                                                                        <img :src="p.image_url"
                                                                            :alt="p.name"
                                                                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.04]" />
                                                                    </div>

                                                                    <div class="mt-3">
                                                                        <div class="line-clamp-1 text-sm font-semibold text-slate-900"
                                                                            x-text="p.name"></div>
                                                                        <div class="mt-1 text-sm font-bold text-slate-800"
                                                                            x-text="p.price?.formatted ?? '—'"></div>
                                                                    </div>
                                                                </a>
                                                            </template>
                                                        </div>

                                                        <template
                                                            x-if="(navData.categories[activeIdx]?.products ?? []).length === 0">
                                                            <div
                                                                class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-sm text-slate-600">
                                                                No products found under this category yet.
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right: Auth / Dashboard --}}
                        <div class="flex items-center justify-between gap-2 md:ml-auto md:justify-end md:gap-3">
                            @auth
                                <a href="{{ route(auth()->user()->dashboardRouteName()) }}"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-800 hover:border-red-500 hover:text-red-600 transition-colors">
                                    <iconify-icon icon="mdi:view-dashboard-outline" class="text-[18px]"></iconify-icon>
                                    <span>Dashboard</span>
                                </a>

                                <span class="hidden text-xs font-medium text-slate-600 sm:inline">
                                    Hi, {{ Str::limit(auth()->user()->name, 14) }}
                                </span>
                            @endauth

                            @guest
                                <a href="{{ route('login') }}"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:border-red-500 hover:text-red-600 transition-colors">
                                    <iconify-icon icon="mdi:login" class="text-[18px]"></iconify-icon>
                                    <span>Login</span>
                                </a>

                                <a href="{{ route('register') }}"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-red-600 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-red-700 transition-colors">
                                    <iconify-icon icon="mdi:account-plus-outline" class="text-[18px]"></iconify-icon>
                                    <span>Register</span>
                                </a>
                            @endguest
                        </div>
                    </div>

                    {{-- 🔹 MOBILE: Full-width search under header --}}
                    {{-- <form action="#" method="GET" class="mt-3 md:hidden">
                    <label class="relative flex w-full items-center">
                    <span class="pointer-events-none absolute left-3">
                        <iconify-icon icon="mdi:magnify" class="text-slate-400 text-[18px]"></iconify-icon>
                    </span>

                    <input type="text" name="q" placeholder="Search products, services, or job name…"
                        class="w-full rounded-full border border-slate-200 bg-slate-50 px-9 py-2 text-sm text-slate-800 placeholder:text-slate-400 shadow-sm focus:border-red-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500" />
                    </label>
                    </form> --}}
                </div>



                {{-- 🔹 QUICK NAV (Primary links) --}}
                @php
                    // Quick nav items (keep this simple + SEO-friendly)
                    $quickNav = [
                        ['label' => 'Home', 'route' => 'home', 'match' => 'home', 'icon' => 'mdi:home-outline'],
                        [
                            'label' => 'Products',
                            'route' => 'products.index',
                            'match' => 'products.*',
                            'icon' => 'mdi:package-variant',
                        ],
                        [
                            'label' => 'Services',
                            'route' => 'services.index',
                            'match' => 'services.*',
                            'icon' => 'mdi:tools',
                        ],
                        [
                            'label' => 'About',
                            'route' => 'about',
                            'match' => 'about',
                            'icon' => 'mdi:information-outline',
                        ],
                        [
                            'label' => 'Contact',
                            'route' => 'contact',
                            'match' => 'contact',
                            'icon' => 'mdi:email-outline',
                        ],
                        [
                            'label' => 'B2B: Co-Operative',
                            'route' => 'coop',
                            'match' => 'coop',
                            'icon' => 'material-symbols:handshake-outline-rounded',
                        ],
                    ];

                    // Helper for active styling (route name matches)
                    $isActive = function (string $pattern) {
                        return request()->routeIs($pattern);
                    };
                @endphp

                <div
                    class="relative z-10 w-full border-b border-slate-200 bg-white/95 backdrop-blur-md supports-[backdrop-filter]:bg-white/90">
                    <div class="mx-auto max-w-7xl px-4 md:px-6 lg:px-8">
                        <div class="flex items-center justify-between gap-3 py-2.5">

                            {{-- Left: quick links (scrollable on mobile) --}}
                            <nav
                                class="min-w-0 flex-1 overflow-x-auto [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                <ul class="flex items-center gap-1 whitespace-nowrap pr-2">
                                    @foreach ($quickNav as $item)
                                        @php
                                            $active = $isActive($item['match']);
                                        @endphp

                                        <li>
                                            <a href="{{ route($item['route']) }}"
                                                class="group relative inline-flex items-center gap-2 rounded-lg px-3.5 py-2 text-[13px] font-semibold transition-all
                                            {{ $active
                                                ? 'bg-gradient-to-br from-red-500 to-red-600 text-white shadow-md shadow-red-500/25'
                                                : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">

                                                <iconify-icon icon="{{ $item['icon'] }}"
                                                    class="text-[16px] {{ $active ? 'text-white' : 'text-slate-500 group-hover:text-red-600' }}"></iconify-icon>

                                                <span class="hidden sm:inline">{{ $item['label'] }}</span>
                                                <span class="sm:hidden">{{ $item['label'] }}</span>

                                                {{-- Active indicator --}}
                                                @if ($active)
                                                    <span
                                                        class="absolute -bottom-2.5 left-1/2 h-1 w-1 -translate-x-1/2 rounded-full bg-red-600"></span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </nav>

                            {{-- Right: CTA Buttons (desktop) --}}
                            <div class="hidden shrink-0 lg:flex items-center gap-2">
                                <a href="{{ route('quotes.create') }}"
                                    class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-lg bg-gradient-to-r from-slate-900 to-slate-800 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white shadow-lg shadow-slate-900/30 transition-all hover:shadow-xl hover:shadow-slate-900/40 hover:scale-[1.02]">
                                    <iconify-icon icon="mdi:chat-outline" class="text-[16px]"></iconify-icon>
                                    <span>Get a Quote</span>
                                    <div
                                        class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-800 to-slate-700 opacity-0 transition-opacity group-hover:opacity-100">
                                    </div>
                                </a>

                                <a href="{{ route('products.index') }}"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg border-2 border-red-200 bg-red-50 px-4 py-2 text-xs font-bold uppercase tracking-wider text-red-700 transition-all hover:border-red-500 hover:bg-red-100 hover:text-red-800 hover:shadow-md hover:scale-[1.02]">
                                    <iconify-icon icon="mdi:grid" class="text-[16px]"></iconify-icon>
                                    <span>Browse All</span>
                                </a>
                            </div>

                            {{-- Mobile Quick Action Button --}}
                            <div class="flex shrink-0 lg:hidden">
                                <a href="{{ route('quotes.create') }}"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-gradient-to-r from-red-500 to-red-600 px-3 py-2 text-xs font-bold uppercase tracking-wide text-white shadow-md shadow-red-500/25 transition-all hover:shadow-lg hover:scale-[1.02]">
                                    <iconify-icon icon="mdi:chat-outline" class="text-[16px]"></iconify-icon>
                                    <span class="hidden sm:inline">Quote</span>
                                </a>
                            </div>

                        </div>

                        {{-- Mobile CTA row (below nav) --}}
                        <div class="pb-3 lg:hidden border-t border-slate-100">
                            <div class="grid grid-cols-2 gap-2 mt-3">
                                <a href="{{ route('quotes.create') }}"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-slate-900 to-slate-800 px-3 py-2.5 text-xs font-bold uppercase tracking-wide text-white shadow-lg shadow-slate-900/20 transition-all hover:shadow-xl active:scale-[0.98]">
                                    <iconify-icon icon="mdi:chat-outline" class="text-[16px]"></iconify-icon>
                                    <span>Get a Quote</span>
                                </a>
                                <a href="{{ route('products.index') }}"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg border-2 border-red-200 bg-red-50 px-3 py-2.5 text-xs font-bold uppercase tracking-wide text-red-700 transition-all hover:border-red-500 hover:bg-red-100 active:scale-[0.98]">
                                    <iconify-icon icon="mdi:grid" class="text-[16px]"></iconify-icon>
                                    <span>Browse All</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
        </div>
    </div>

    <div x-ref="spacer" class="h-[150px] md:h-[168px]" style="height: var(--printair-header-height, 168px)"
        aria-hidden="true"></div>

    {{-- 🔹 OFFCANVAS SIDEBAR (Mobile only) --}}
    <div x-show="mobileNavOpen" x-cloak class="fixed inset-0 z-[80] md:hidden" role="dialog" aria-modal="true">

        {{-- Backdrop --}}
        <div x-show="mobileNavOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="absolute inset-0 bg-black/40" @click="mobileNavOpen = false">
        </div>

        {{-- Sidebar panel --}}
        <div x-show="mobileNavOpen" x-transition:enter="transition ease-in-out duration-300 transform"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in-out duration-300 transform"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
            class="absolute inset-y-0 right-0 flex w-72 max-w-[80%] flex-col bg-white shadow-xl"
            x-data="{ mobileActiveCatIdx: null }">

            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                    <img src="{{ asset('assets/printair/printairlogo.png') }}" alt="Printair Advertising"
                        class="h-8 w-auto" loading="lazy" />
                </a>

                <button type="button"
                    class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 p-1.5 text-slate-700 hover:border-red-500 hover:text-red-600 transition-colors"
                    aria-label="Close navigation" @click="mobileNavOpen = false">
                    <iconify-icon icon="mdi:close" class="text-[20px]"></iconify-icon>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-4 py-4">
                <a href="{{ route('home') }}"
                    class="block rounded-md px-3 py-2 font-medium text-slate-800 hover:bg-slate-100">
                    Home
                </a>

                {{-- Mobile Categories --}}
                <div class="mt-6 pt-4 border-t border-slate-200">
                    <div class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500 px-3">
                        Categories
                    </div>

                    {{-- Skeleton for category list --}}
                    <template x-if="navLoading && !navLoaded">
                        <div class="space-y-2 px-3">
                            <div class="h-10 rounded-md bg-slate-200/70 animate-pulse"></div>
                            <div class="h-10 rounded-md bg-slate-200/70 animate-pulse"></div>
                            <div class="h-10 rounded-md bg-slate-200/70 animate-pulse"></div>
                            <div class="h-10 rounded-md bg-slate-200/70 animate-pulse"></div>
                        </div>
                    </template>

                    {{-- Error --}}
                    <template x-if="navError">
                        <div class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700 mx-3">
                            <div class="font-semibold">Oops</div>
                            <div class="mt-1" x-text="navError"></div>
                        </div>
                    </template>

                    {{-- Categories (loaded) - Dropdown style --}}
                    <template x-if="navLoaded && navData?.categories?.length">
                        <div class="space-y-2">
                            <template x-for="(cat, idx) in navData.categories" :key="cat.id">
                                <div class="border border-slate-200 rounded-lg overflow-hidden">
                                    {{-- Category header (dropdown toggle) --}}
                                    <button @click="mobileActiveCatIdx = mobileActiveCatIdx === idx ? null : idx"
                                        class="w-full flex items-center justify-between px-3 py-3 bg-slate-50 hover:bg-slate-100 transition font-medium text-sm text-slate-800">
                                        <span x-text="cat.name"></span>
                                        <iconify-icon icon="mdi:chevron-down" class="text-[18px] transition-transform"
                                            :style="mobileActiveCatIdx === idx ? 'transform: rotate(180deg)' : ''"></iconify-icon>
                                    </button>

                                    {{-- Products grid (dropdown content) --}}
                                    <template x-if="mobileActiveCatIdx === idx">
                                        <div class="p-3 border-t border-slate-200 bg-white">
                                            {{-- Loading skeleton --}}
                                            <template x-if="navLoading">
                                                <div class="grid grid-cols-2 gap-2">
                                                    <template x-for="i in 4" :key="i">
                                                        <div class="rounded-lg border border-slate-200 p-2">
                                                            <div
                                                                class="aspect-[4/3] w-full rounded bg-slate-200/70 animate-pulse">
                                                            </div>
                                                            <div
                                                                class="mt-2 h-3 rounded bg-slate-200/70 animate-pulse">
                                                            </div>
                                                            <div
                                                                class="mt-1 h-3 w-2/3 rounded bg-slate-200/70 animate-pulse">
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>

                                            {{-- Products --}}
                                            <template x-if="!navLoading && (cat.products ?? []).length">
                                                <div class="grid grid-cols-2 gap-2">
                                                    <template x-for="p in cat.products" :key="p.id">
                                                        <a :href="p.url" @click="mobileNavOpen = false"
                                                            class="group rounded-lg border border-slate-200 bg-white p-2 hover:border-red-200 hover:shadow-md transition">
                                                            <div
                                                                class="aspect-[4/3] overflow-hidden rounded bg-slate-100">
                                                                <img :src="p.image_url" :alt="p.name"
                                                                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.04]" />
                                                            </div>

                                                            <div class="mt-2">
                                                                <div class="line-clamp-2 text-xs font-semibold text-slate-900"
                                                                    x-text="p.name"></div>
                                                                <div class="mt-1 text-xs font-bold text-slate-800"
                                                                    x-text="p.price?.formatted ?? '—'"></div>
                                                            </div>
                                                        </a>
                                                    </template>
                                                </div>
                                            </template>

                                            {{-- No products --}}
                                            <template x-if="!navLoading && (cat.products ?? []).length === 0">
                                                <div
                                                    class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600 text-center">
                                                    No products found
                                                </div>
                                            </template>

                                            {{-- View all link --}}
                                            <a :href="cat.url" @click="mobileNavOpen = false"
                                                class="block mt-3 text-center text-xs font-semibold text-red-600 hover:text-red-700 py-2 rounded-lg hover:bg-red-50 transition">
                                                View all →
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </nav>

            <div class="border-t border-slate-200 px-4 py-3 space-y-2">
                @auth
                    <a href="{{ route(auth()->user()->dashboardRouteName()) }}"
                        class="flex items-center justify-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-800 hover:border-red-500 hover:text-red-600 transition-colors">
                        <span class="icon-[heroicons--squares-2x2] size-5"></span>
                        <span>Go to Dashboard</span>
                    </a>
                @endauth

                @guest
                    <a href="{{ route('login') }}"
                        class="flex items-center justify-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 hover:border-red-500 hover:text-red-600 transition-colors">
                        <span class="icon-[heroicons--arrow-right-end-on-rectangle] size-5"></span>
                        <span>Login</span>
                    </a>

                    <a href="{{ route('register') }}"
                        class="flex items-center justify-center gap-1.5 rounded-full bg-red-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-red-700 transition-colors">
                        <span class="icon-[heroicons--user-plus] size-5"></span>
                        <span>Register</span>
                    </a>
                @endguest

                <a href="#"
                    class="flex items-center justify-center gap-1.5 rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-slate-800 transition-colors">
                    <span class="icon-[heroicons--chat-bubble-left-ellipsis] size-5"></span>
                    <span>Get a Quote</span>
                </a>
            </div>
        </div>
    </div>
    </div>

    <main class="bg-slate-100">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 text-white">
        {{-- Top CTA strip --}}
        <div class="border-b border-white/10">
            <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-2xl sm:text-3xl font-black tracking-tight">
                            Print smarter with <span class="text-red-400">Partner Pricing</span>
                        </h3>
                        <p class="mt-2 max-w-2xl text-sm text-slate-300">
                            Agencies, businesses, and resellers can access lower prices via our Working Group system.
                            Apply once — pricing auto-applies on every order.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <a href="{{ route('coop') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-500 px-6 py-3 text-sm font-black text-white shadow-lg shadow-red-500/20 transition hover:bg-red-600">
                            <iconify-icon icon="solar:handshake-bold" class="text-lg"></iconify-icon>
                            Become a Partner
                        </a>

                        <a href="{{ route('contact') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/5 px-6 py-3 text-sm font-black text-white transition hover:bg-white/10">
                            <iconify-icon icon="solar:chat-round-bold" class="text-lg"></iconify-icon>
                            Talk to us
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main footer --}}
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8" x-data="printairFooterHub()" x-init="init()">

            <div class="grid grid-cols-1 gap-10 lg:grid-cols-12">
                {{-- Brand + social --}}
                <div class="lg:col-span-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('assets/printair/printairlogo.png') }}" alt="Printair Advertising"
                            class="h-20 w-auto" loading="lazy">
                        <div>

                        </div>
                    </div>

                    <p class="mt-4 text-sm text-slate-300 leading-relaxed">
                        Commercial-grade printing with premium finishing, accurate colors, and fast turnaround.
                        Built for brands, businesses, and creators who care about quality.
                    </p>

                    {{-- Contact mini --}}
                    @php
                        $contactPhone = config('contact.phone') ?: env('CONTACT_PHONE');
                        $contactWhatsapp = config('contact.whatsapp') ?: env('CONTACT_WHATSAPP');
                        $contactEmail = config('contact.email') ?: env('CONTACT_EMAIL');
                        $contactAddress = config('contact.address') ?: env('CONTACT_ADDRESS');

                        $telHref = $contactPhone ? preg_replace('/[^0-9+]/', '', (string) $contactPhone) : null;
                        $waHref = $contactWhatsapp ? preg_replace('/\\D+/', '', (string) $contactWhatsapp) : null;
                    @endphp
                    <div class="mt-6 space-y-3 text-sm">
                        {{-- @if ($contactPhone) --}}
                        <a href="tel:{{ $telHref }}"
                            class="flex items-center gap-2 text-slate-200 hover:text-white">
                            <iconify-icon icon="solar:phone-bold" class="text-lg text-red-400"></iconify-icon>
                            <span>+94 76 886 0175</span>
                        </a>
                        {{-- @endif --}}

                        {{-- @if ($contactWhatsapp) --}}
                        <a href="https://wa.me/94768860175" target="_blank" rel="noopener"
                            class="flex items-center gap-2 text-slate-200 hover:text-white">
                            <iconify-icon icon="logos:whatsapp-icon" class="text-lg"></iconify-icon>
                            <span>WhatsApp: +94 76 886 0175</span>
                        </a>
                        {{-- @endif --}}

                        {{-- @if ($contactEmail) --}}
                        <a href="mailto:{{ $contactEmail }}"
                            class="flex items-center gap-2 text-slate-200 hover:text-white">
                            <iconify-icon icon="solar:mailbox-bold" class="text-lg text-red-400"></iconify-icon>
                            <span class="break-all">contact@printair.lk</span>
                        </a>
                        {{-- @endif --}}

                        {{-- @if ($contactAddress) --}}
                        <div class="flex items-start gap-2 text-slate-200">
                            <iconify-icon icon="solar:map-point-bold"
                                class="mt-0.5 text-lg text-red-400"></iconify-icon>
                            <span class="leading-relaxed">No. 67/D/1, Uggashena, Walpola, Ragama, Sri Lanka</span>
                        </div>
                        {{-- @endif --}}
                    </div>

                    {{-- Social --}}
                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <a href="https://www.facebook.com/Printair/" target="blank"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/5 ring-1 ring-white/10 hover:bg-white/10">
                            <iconify-icon icon="ri:facebook-fill" class="text-xl"></iconify-icon>
                        </a>
                        <a href="https://www.instagram.com/printairsl/" target="blank"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/5 ring-1 ring-white/10 hover:bg-white/10">
                            <iconify-icon icon="ri:instagram-fill" class="text-xl"></iconify-icon>
                        </a>
                        <a href="https://www.tiktok.com/@printair2?is_from_webapp=1&sender_device=pc"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/5 ring-1 ring-white/10 hover:bg-white/10">
                            <iconify-icon icon="ri:tiktok-fill" class="text-xl"></iconify-icon>
                        </a>
                        <a href="https://youtube.com/@printairadvertising3730?si=TNTcyKiTcRrESCJm" target="blank"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/5 ring-1 ring-white/10 hover:bg-white/10">
                            <iconify-icon icon="ri:youtube-fill" class="text-xl"></iconify-icon>
                        </a>
                    </div>

                    {{-- Trust chips --}}
                    <div class="mt-7 flex flex-wrap gap-2">
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-1 text-xs font-semibold text-slate-200 ring-1 ring-white/10">
                            <iconify-icon icon="solar:delivery-bold" class="text-base text-red-400"></iconify-icon>
                            Island-wide delivery
                        </span>
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-1 text-xs font-semibold text-slate-200 ring-1 ring-white/10">
                            <iconify-icon icon="solar:shield-check-bold"
                                class="text-base text-red-400"></iconify-icon>
                            Commercial-grade output
                        </span>
                    </div>
                </div>

                {{-- Quick links --}}
                <div class="lg:col-span-2">
                    <h4 class="text-sm font-black tracking-wide text-white">Quick Links</h4>
                    <ul class="mt-4 space-y-3 text-sm text-slate-300">
                        <li><a href="#" class="hover:text-white">Explore Products</a></li>
                        <li><a href="#" class="hover:text-white">Categories</a></li>
                        <li><a href="#" class="hover:text-white">Design Services</a></li>
                        <li><a href="#" class="hover:text-white">Partner Program</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white">Contact Us</a></li>
                    </ul>
                </div>

                {{-- Dynamic Categories --}}
                <div class="lg:col-span-3">
                    <div class="flex items-center justify-between gap-3">
                        <h4 class="text-sm font-black tracking-wide text-white">Categories</h4>
                        <a href="#" class="text-xs font-bold text-red-300 hover:text-red-200">View all →</a>
                    </div>

                    {{-- Skeleton --}}
                    <template x-if="loadingCats">
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <template x-for="i in 8" :key="i">
                                <div class="h-9 rounded-xl bg-white/5 ring-1 ring-white/10 animate-pulse"></div>
                            </template>
                        </div>
                    </template>

                    {{-- List --}}
                    <template x-if="!loadingCats">
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <template x-for="c in categories" :key="c.id">
                                <a :href="c.href"
                                    class="group inline-flex items-center gap-2 rounded-xl bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 ring-1 ring-white/10 hover:bg-white/10">
                                    <span
                                        class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-white/10">
                                        <iconify-icon icon="solar:folder-with-files-bold"
                                            class="text-base text-red-300"></iconify-icon>
                                    </span>
                                    <span class="truncate" x-text="c.name"></span>
                                </a>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Dynamic Popular Products --}}
                <div class="lg:col-span-3">
                    <div class="flex items-center justify-between gap-3">
                        <h4 class="text-sm font-black tracking-wide text-white">Popular Products</h4>
                        <a href="#" class="text-xs font-bold text-red-300 hover:text-red-200">Explore →</a>
                    </div>

                    {{-- Skeleton --}}
                    <template x-if="loadingPopular">
                        <div class="mt-4 space-y-3">
                            <template x-for="i in 6" :key="i">
                                <div class="flex gap-3 rounded-2xl bg-white/5 p-3 ring-1 ring-white/10">
                                    <div class="h-14 w-14 rounded-xl bg-white/10 animate-pulse"></div>
                                    <div class="flex-1 space-y-2">
                                        <div class="h-3 w-3/4 rounded bg-white/10 animate-pulse"></div>
                                        <div class="h-3 w-1/2 rounded bg-white/10 animate-pulse"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- List --}}
                    <template x-if="!loadingPopular">
                        <div class="mt-4 space-y-3">
                            <template x-for="p in popular" :key="p.id">
                                <a :href="p.href"
                                    class="group flex gap-3 rounded-2xl bg-white/5 p-3 ring-1 ring-white/10 transition hover:bg-white/10">
                                    <div class="h-14 w-14 overflow-hidden rounded-xl bg-white/10 ring-1 ring-white/10">
                                        <img :src="p.primaryImage" :alt="p.name"
                                            class="h-full w-full object-cover transition group-hover:scale-105"
                                            loading="lazy" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="truncate text-sm font-black text-white" x-text="p.name"></div>
                                        <div class="mt-1 text-xs text-slate-300 truncate"
                                            x-text="p.price?.label ?? 'Ask for price'"></div>
                                    </div>

                                    <div class="shrink-0 self-center text-slate-300 group-hover:text-white">
                                        <iconify-icon icon="solar:arrow-right-bold" class="text-lg"></iconify-icon>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Newsletter / updates strip --}}
            <div class="mt-12 rounded-3xl bg-white/5 p-6 ring-1 ring-white/10">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h4 class="text-base font-black text-white">Get updates & offers</h4>
                        <p class="mt-1 text-sm text-slate-300">
                            Drop your email — we’ll only send the good stuff (new products, promos, partner pricing
                            updates).
                        </p>
                    </div>

                    <form action="#" method="POST" class="flex w-full flex-col gap-3 sm:flex-row lg:w-auto">
                        @csrf
                        <input type="email" name="email" placeholder="you@email.com"
                            class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-400 outline-none ring-red-500/30 focus:ring-4 sm:w-80" />
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-500 px-6 py-3 text-sm font-black text-white transition hover:bg-red-600">
                            <iconify-icon icon="solar:paper-plane-bold" class="text-lg"></iconify-icon>
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>

            {{-- Bottom legal --}}
            <div class="mt-10 border-t border-white/10 pt-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-xs text-slate-400">
                        © {{ date('Y') }} Printair Advertising. All rights reserved.
                    </div>

                    <div class="flex flex-wrap items-center gap-4 text-xs font-semibold text-slate-300">
                        <a href="{{ route('privacy') }}" class="hover:text-white">Privacy Policy</a>
                        <a href="{{ route('terms.conditions') }}" class="hover:text-white">Terms</a>
                        <a href="{{ route('terms.conditions') }}" class="hover:text-white">Refund Policy</a>
                    </div>
                </div>

                <div class="mt-5 text-center text-xs text-slate-500">
                    Designed and Developed by <span class="font-bold text-slate-300">Thejan Vishmitha</span>
                </div>
            </div>
        </div>
    </footer>


    <div x-data="cookieConsent()" x-init="init()" x-show="visible" x-transition.opacity
        class="fixed bottom-5 right-5 z-[9999] w-[92%] max-w-sm sm:w-full">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/10">
            <div class="flex items-start gap-3">
                {{-- Icon --}}
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-white">
                    🍪
                </div>

                <div class="flex-1">
                    <h3 class="text-sm font-extrabold text-slate-900">
                        Cookies & Privacy
                    </h3>

                    <p class="mt-1 text-xs leading-relaxed text-slate-600">
                        We use cookies to ensure site security, improve performance, and understand usage.
                        No personal data is sold or shared.
                    </p>

                    <p class="mt-2 text-xs text-slate-500">
                        Learn more in our
                        <a href="{{ route('privacy') }}"
                            class="font-semibold text-slate-900 underline underline-offset-2 hover:text-slate-700">
                            Privacy Policy
                        </a>.
                    </p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="mt-5 flex items-center justify-end gap-3">
                <button @click="reject()"
                    class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    Reject
                </button>

                <button @click="accept()"
                    class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                    Accept
                </button>
            </div>
        </div>
    </div>

    <script>
        function cookieConsent() {
            return {
                visible: false,

                init() {
                    const consent = localStorage.getItem('printair_cookie_consent');
                    if (!consent) {
                        this.visible = true;
                    }
                },

                accept() {
                    localStorage.setItem('printair_cookie_consent', 'accepted');
                    this.visible = false;

                    // Tell the site to load analytics now
                    window.dispatchEvent(new Event('printair:cookies-accepted'));
                },


                reject() {
                    localStorage.setItem('printair_cookie_consent', 'rejected');
                    this.visible = false;
                    window.dispatchEvent(new Event('printair:cookies-rejected'));
                }

            }
        }
    </script>

    @stack('scripts')

    <script src="https://apis.google.com/js/platform.js?onload=renderBadge" async defer></script>

    <script>
        window.renderBadge = function() {
            var ratingBadgeContainer = document.createElement("div");
            document.body.appendChild(ratingBadgeContainer);
            window.gapi.load('ratingbadge', function() {
                window.gapi.ratingbadge.render(ratingBadgeContainer, {
                    merchant_id: 5548164916,
                    position: "BOTTOM_RIGHT"
                });
            });
        }
    </script>


    <x-analytics-loader />

</body>

</html>
