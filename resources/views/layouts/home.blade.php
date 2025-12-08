<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-seo.meta :title="$seo['title'] ?? null" :description="$seo['description'] ?? null" :keywords="$seo['keywords'] ?? null" :image="$seo['image'] ?? null" />

    <link rel="canonical" href="https://example.com/page">

    <!-- branding -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

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
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="antialiased bg-gray-50 text-gray-900 font-sans" x-data="{ mobileNavOpen: false }">

    <!-- Home Layout -->
    <!-- Top bar -->
    <header class="w-full border-b border-slate-200 bg-slate-50 text-slate-700">
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
                    <span>info@printair.lk</span>
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
                    <iconify-icon icon="mdi:lightning-bolt-outline" class="text-[14px] text-red-500"></iconify-icon>
                    <span class="ml-1 text-gray-500">24h Express Printing</span>
                </span>

                <span
                    class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-[11px] font-medium shadow-sm ring-1 ring-slate-200">
                    <iconify-icon icon="mdi:shield-lock-outline" class="text-[14px] text-red-500"></iconify-icon>
                    <span class="ml-1 text-gray-500">Secure Online Payments</span>
                </span>

            </div>
        </div>

        <hr class="border-slate-200">

        {{-- branding --}}
        <div class="mx-auto max-w-7xl px-4 py-3 md:px-6 lg:px-8">

            {{-- 🔹 MOBILE: Logo + Sidebar Toggle --}}
            <div class="flex items-center justify-between md:hidden">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                    <img src="{{ asset('assets/printair/printairlogo.png') }}" alt="Printair Advertising"
                        class="h-10 w-auto" loading="lazy" />
                    <span class="sr-only">Printair Advertising</span>
                </a>

                <button type="button"
                    class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 p-2.5 text-slate-700 shadow-sm hover:border-red-500 hover:text-red-600 transition-colors"
                    aria-label="Open navigation" @click="mobileNavOpen = true">
                    <iconify-icon icon="gg:menu-right" class="text-[22px]"></iconify-icon>

                </button>
            </div>

            {{-- 🔹 DESKTOP BRANDING (your original block, only md+) --}}
            <div
                class="mx-auto mt-3 hidden max-w-7xl flex-col gap-3 justify-between md:mt-0 md:flex md:flex-row md:items-center md:gap-6">

                {{-- Left: Logo --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                        <img src="{{ asset('assets/printair/printairlogo.png') }}" alt="Printair Advertising"
                            class="h-14 w-auto md:h-15" loading="lazy" />
                        <span class="sr-only">Printair Advertising</span>
                    </a>
                </div>

                {{-- Center: Search Bar (desktop / tablet) --}}
                <form action="#" method="GET" class="hidden flex-1 items-center md:flex">
                    <label class="relative flex w-full items-center">
                        <span class="pointer-events-none absolute left-3">
                            <iconify-icon icon="mdi:magnify" class="text-slate-400 text-[18px]"></iconify-icon>
                        </span>

                        <input type="text" name="q" placeholder="Search products, services, or job name…"
                            class="w-full rounded-full border border-slate-200 bg-slate-50 px-9 py-2 text-sm text-slate-800 placeholder:text-slate-400 shadow-sm focus:border-red-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500" />

                        <button type="submit"
                            class="ml-2 hidden shrink-0 rounded-full bg-red-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-red-700 transition-colors">
                            Search
                        </button>
                    </label>
                </form>

                {{-- Right: Auth / Dashboard --}}
                <div class="flex items-center justify-between gap-2 md:ml-auto md:justify-end md:gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}"
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

        {{-- 🔹 OFFCANVAS SIDEBAR (Mobile only) --}}
        <div x-show="mobileNavOpen" x-cloak class="fixed inset-0 z-40 md:hidden" role="dialog" aria-modal="true">

            {{-- Backdrop --}}
            <div x-show="mobileNavOpen" x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-300"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-black/40" @click="mobileNavOpen = false">
            </div>

            {{-- Sidebar panel --}}
            <div x-show="mobileNavOpen" x-transition:enter="transition ease-in-out duration-300 transform"
                x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in-out duration-300 transform"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                class="absolute inset-y-0 right-0 flex w-72 max-w-[80%] flex-col bg-white shadow-xl">

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

                <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1.5 text-sm">
                    <a href="{{ route('home') }}"
                        class="block rounded-md px-3 py-2 font-medium text-slate-800 hover:bg-slate-100">
                        Home
                    </a>
                </nav>

                <div class="border-t border-slate-200 px-4 py-3 space-y-2">
                    @auth
                        <a href="{{ route('dashboard') }}"
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
    </header>


    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->

</body>

</html>
