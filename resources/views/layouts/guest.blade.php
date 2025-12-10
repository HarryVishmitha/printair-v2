<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- Core SEO meta (title / description / keywords / og image) --}}
    <x-seo.meta :title="$seo['title'] ?? null" :description="$seo['description'] ?? null" :keywords="$seo['keywords'] ?? null" :image="$seo['image'] ?? null" />

    {{-- Canonical URL (fallback to current URL) --}}
    <link rel="canonical" href="{{ $seo['canonical'] ?? url()->current() }}">

    {{-- Branding / Icons --}}
    <link rel="icon" href="{{ asset('assets/printair/favicon.ico') }}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/printair/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/printair/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/printair/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets/printair/site.webmanifest') }}">

    {{-- Crawlers --}}
    <meta name="googlebot" content="index, follow">
    <meta name="bingbot" content="index, follow">
    <meta name="google" content="nositelinkssearchbox">
    <meta name="rating" content="general">
    <meta name="referrer" content="no-referrer-when-downgrade">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Structured Data: Organization --}}
    <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "Organization",
            "name": "Printair Advertising",
            "url": "https://printair.lk",
            "logo": "{{ asset('assets/printair/printairlogo.png') }}",
            "sameAs": [
                "https://facebook.com/printair", 
                "https://instagram.com/printair"
            ]
        }
    </script>

    {{-- Fonts / Icons / Alpine --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- App assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="antialiased bg-slate-100 text-slate-900 font-sans" x-data="{}">
    <div class="min-h-screen flex flex-col">

        {{-- Top bar: logo + 'Back to website' --}}
        <header class="w-full border-b border-slate-200 bg-white/80 backdrop-blur">
            <div class="max-w-6xl mx-auto flex items-center justify-between px-4 py-3">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <img src="{{ asset('assets/printair/printairlogo.png') }}" alt="Printair Advertising"
                        class="h-15 w-auto" loading="lazy" />
                    <span class="sr-only">Printair Advertising</span>
                </a>

                <a href="{{ route('home') }}"
                    class="inline-flex items-center gap-1 text-xs font-medium text-slate-600 hover:text-slate-900 transition">
                    <iconify-icon icon="lucide:arrow-left" class="w-4 h-4"></iconify-icon>
                    <span>Back to Home</span>
                </a>
            </div>
        </header>

        {{-- Main auth shell --}}
        <main class="flex-1 flex items-center justify-center px-4 py-10">
            <div class="max-w-6xl w-full grid gap-10 md:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)] items-center">

                {{-- Left: brand / copy (hidden on small screens) --}}
                <section class="hidden md:flex flex-col gap-6">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-medium text-amber-800">
                        <span class="inline-flex h-2 w-2 rounded-full bg-amber-500"></span>
                        <span>Printair v2 · Secure Portal</span>
                    </div>

                    <div>
                        <h1 class="text-3xl lg:text-4xl font-semibold text-slate-900 tracking-tight">
                            Sign in to manage your <span class="text-amber-600">designs &amp; printing</span>.
                        </h1>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600 max-w-md">
                            Access quotations, orders, proofs, and production updates through a single
                            secure dashboard powered by Printair Advertising.
                        </p>
                    </div>

                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                            <dt class="font-semibold text-slate-800">Fast approvals</dt>
                            <dd class="mt-1 text-slate-600">Review and confirm your print jobs online.</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                            <dt class="font-semibold text-slate-800">Centralised files</dt>
                            <dd class="mt-1 text-slate-600">Keep designs, specs, and records in one place.</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-900 px-4 py-3 shadow-sm">
                            <dt class="font-semibold text-amber-300">Secure access</dt>
                            <dd class="mt-1 text-slate-200">Protected by modern authentication and encryption.</dd>
                        </div>
                    </dl>
                </section>

                {{-- Right: auth card --}}
                <section>
                    <div class="mx-auto w-full max-w-md">
                        <div
                            class="bg-white shadow-xl shadow-slate-200/40 border border-slate-200 rounded-2xl px-6 py-6 sm:px-8 sm:py-7">
                            {{-- Optional small logo for mobile --}}
                            <div class="flex items-center justify-center mb-5 md:hidden">
                                <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                                    <x-application-logo class="w-12 h-12 text-slate-500" />
                                    <span class="sr-only">Printair Advertising</span>
                                </a>
                            </div>

                            {{ $slot }}
                        </div>

                        {{-- Helper text under card --}}
                        <p class="mt-4 text-[11px] text-center text-slate-500">
                            By continuing you agree to Printair’s
                            <a href="{{ route('terms') }}"
                                class="font-medium text-slate-700 hover:text-amber-600 underline underline-offset-2">
                                Terms of Service
                            </a>
                            and
                            <a href="{{ route('privacy') }}"
                                class="font-medium text-slate-700 hover:text-amber-600 underline underline-offset-2">
                                Privacy Policy
                            </a>.
                        </p>
                    </div>
                </section>

            </div>
        </main>

        {{-- Footer --}}
        <footer class="border-t border-slate-200 bg-white">
            <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 px-4 py-4">
                <p class="text-[11px] text-slate-500">
                    © {{ now()->year }} Printair Advertising. All rights reserved.
                </p>
                <p class="text-[11px] text-slate-500">
                    Designed &amp; Developed by <span class="font-medium text-slate-700">Thejan Vishmitha</span>
                </p>
            </div>
        </footer>
    </div>
</body>

</html>
