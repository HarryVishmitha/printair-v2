<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    {{-- Base meta --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    {{-- CSRF token for AJAX / Axios --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

    {{-- Structured Data: Organization --}}
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => 'Printair Advertising',
            'url'      => config('app.url', 'https://printair.lk'),
            'logo'     => asset('assets/printair/printairlogo.png'),
            'sameAs'   => [
                'https://facebook.com/printair',
                'https://instagram.com/printair',
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    {{-- Fonts / Icons --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:400,500,600,700,800,900&display=swap"
        rel="stylesheet" />
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js" defer></script>

    {{-- App assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Extra per-page head content --}}
    @stack('head')
</head>
@php
    $user = auth()->user();

    /**
     * CENTRAL NAV CONFIG
     * Edit all navigation links here in one place.
     */
    $navSections = [
        [
            'label' => null,
            'items' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'solar:widget-5-bold-duotone',
                    'route' => route('dashboard'),
                    'active' => request()->routeIs('dashboard'),
                ],
            ],
        ],
        [
            'label' => 'Product Management',
            'items' => [
                [
                    'label' => 'Categories',
                    'icon' => 'solar:add-folder-bold-duotone',
                    'route' => route('admin.categories.index'),
                    'active' => request()->routeIs('admin.categories.*'),
                    'visible' => $user?->can('manage-categories') ?? false,
                ],
                [
                    'label' => 'Products',
                    'icon' => 'solar:box-bold-duotone',
                    'route' => route('admin.products.index'), // change route name if needed
                    'active' => request()->routeIs('admin.products.*'),
                    'visible' => $user?->can('manage-products') ?? false,
                ],
                [
                    'label' => 'Product Pricing Hub',
                    'icon' => 'solar:box-bold-duotone',
                    'route' => route('admin.pricing.index'), // change route name if needed
                    'active' => request()->routeIs('admin.pricing.*'),
                    'visible' => $user?->can('manage-products') ?? false,
                ],
            ],
        ],
        [
            'label' => 'Inventory Management',
            'items' => [
                [
                    'label' => 'Rolls',
                    'icon' => 'solar:box-bold-duotone',
                    'route' => route('admin.rolls.index'), // change route name if needed
                    'active' => request()->routeIs('admin.rolls.*'),
                    'visible' => $user?->can('manage-rolls') ?? false,
                ],
            ],
        ],
        [
            'label' => 'System',
            'items' => [
                [
                    'label' => 'Working Groups',
                    'icon' => 'solar:users-group-rounded-bold-duotone',
                    'route' => route('admin.working-groups.index'),
                    'active' => request()->routeIs('admin.working-groups.*'),
                    // Only visible if user can manage working groups (Super/Admin)
                    'visible' => $user?->can('manage-working-groups') ?? false,
                ],
                [
                    'label' => 'Users',
                    'icon' => 'solar:user-id-bold-duotone',
                    'route' => route('admin.users.index'),
                    'active' => request()->routeIs('admin.users.*'),
                    // Only Super Admin, Admin, Manager
                    'visible' => $user?->can('manage-users') ?? false,
                ],

                // -------------------------
                // CUSTOMER MANAGEMENT
                // -------------------------
                [
                    'label' => 'Customers',
                    'icon' => 'solar:users-group-two-rounded-bold-duotone',
                    'route' => route('admin.customers.index'),
                    'active' => request()->routeIs('admin.customers.*'),
                    // Only Super Admin, Admin, Manager
                    'visible' => $user?->can('manage-customers') ?? false,
                ],
                [
                    'label' => 'Settings',
                    'icon' => 'solar:settings-bold-duotone',
                    'route' => '#', // change route name if needed
                    'active' => request()->routeIs('settings.*'),
                ],
            ],
        ],
    ];
@endphp

<body class="font-sans antialiased bg-slate-50 text-slate-900 h-screen overflow-hidden">
    <div class="h-full flex">

        {{-- Sidebar --}}
        <aside id="app-sidebar"
            class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r border-slate-200
                   flex flex-col transform transition-transform duration-200 ease-out
                   -translate-x-full lg:translate-x-0">
            {{-- Logo only --}}
            <div class="h-16 flex items-center px-4 border-b border-slate-200">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2">
                    <img src="{{ asset('assets/printair/printairlogo.png') }}" alt="Printair" class="h-15 w-auto">
                </a>
            </div>

            {{-- Nav links (from central config) --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3 text-sm space-y-4">
                @foreach ($navSections as $section)
                    @if ($section['label'])
                        <p class="px-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                            {{ $section['label'] }}
                        </p>
                    @endif
                    <div class="space-y-1">
                        @foreach ($section['items'] as $item)
                            @if (isset($item['visible']) && !$item['visible'])
                                @continue
                            @endif
                            <a href="{{ $item['route'] }}"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg border border-transparent
                                  {{ $item['active']
                                      ? 'bg-slate-900 text-white border-slate-900 shadow-sm'
                                      : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">
                                <iconify-icon icon="{{ $item['icon'] }}" class="text-lg"></iconify-icon>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>

            {{-- Sidebar bottom user block --}}
            <div class="border-t border-slate-200 p-3 bg-slate-50">
                <div class="flex items-center justify-between gap-3">
                    {{-- User --}}
                    <div class="flex items-center gap-2">
                        <div
                            class="h-8 w-8 rounded-full bg-sky-500 flex items-center justify-center text-xs font-semibold text-white">
                            {{ strtoupper(substr($user->first_name ?? 'P', 0, 1)) }}
                        </div>

                        <div class="flex flex-col leading-tight">
                            <span class="text-xs font-semibold text-slate-900">
                                {{ $user->first_name }} {{ $user->last_name }}
                            </span>
                            <span class="text-[8px] text-slate-500">
                                {{ $user->email }}
                            </span>
                        </div>
                    </div>

                    {{-- Logout --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-1 rounded-md border border-slate-300
                                   px-2.5 py-1 text-[11px] font-medium text-slate-700 hover:bg-slate-100
                                   transition-all">
                            <iconify-icon icon="solar:logout-2-bold-duotone"
                                class="text-sm text-slate-600"></iconify-icon>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main panel --}}
        <div class="flex-1 flex flex-col h-full lg:ml-64">
            {{-- Top bar --}}
            <header
                class="h-16 border-b border-slate-200 bg-white/90 backdrop-blur flex items-center px-4 lg:px-6 justify-between">
                <div class="flex items-center gap-3">
                    {{-- Mobile menu button --}}
                    <button type="button"
                        class="inline-flex items-center justify-center rounded-md border border-slate-300 px-2.5 py-1.5 text-slate-600 lg:hidden"
                        id="sidebar-toggle" aria-label="Toggle navigation">
                        <iconify-icon icon="solar:hamburger-menu-linear" class="text-xl"></iconify-icon>
                    </button>

                    <div class="flex flex-col">
                        <span class="text-xs uppercase tracking-wide text-slate-400">
                            {{ $sectionTitle ?? 'Dashboard' }}
                        </span>
                        <h1 class="text-sm md:text-base font-semibold text-slate-900">
                            {{ $pageTitle ?? 'Printair Control Panel' }}
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Search --}}
                    <div
                        class="hidden md:flex items-center bg-slate-50 border border-slate-200 rounded-full px-3 py-1.5 text-xs text-slate-500 w-56">
                        <iconify-icon icon="solar:magnifier-linear" class="mr-2 text-base"></iconify-icon>
                        <input type="text" placeholder="Search orders, clients, products..."
                            class="bg-transparent border-0 focus:ring-0 focus:outline-none flex-1 text-xs text-slate-800 placeholder:text-slate-400">
                    </div>

                    @php
                        $user = Auth::user();
                        $unreadCount = $user?->unreadNotifications()->count() ?? 0;
                    @endphp

                    {{-- Notification dropdown --}}
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false"
                        @keydown.escape.window="open = false">
                        {{-- Bell button --}}
                        <button type="button" @click="open = !open"
                            class="relative inline-flex items-center justify-center rounded-full border border-slate-200 h-9 w-9 text-slate-600 hover:bg-slate-50 transition-colors"
                            aria-label="Notifications">
                            <iconify-icon icon="solar:bell-bing-bold-duotone" class="text-lg"></iconify-icon>

                            @if ($unreadCount > 0)
                                <span
                                    class="absolute -top-1 -right-1 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-semibold text-white px-0.5 animate-pulse">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </button>

                        {{-- Dropdown panel --}}
                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                            class="absolute right-0 mt-2 z-50 w-80 rounded-xl shadow-xl bg-white/90 backdrop-blur-md border border-slate-100 ring-1 ring-black/5 focus:outline-none">
                            {{-- Header --}}
                            <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-center">
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Notifications
                                </span>

                                @if ($unreadCount > 0)
                                    <span
                                        class="bg-rose-100 text-rose-600 py-0.5 px-2 rounded-full text-[10px] font-medium">
                                        {{ $unreadCount }} New
                                    </span>
                                @endif
                            </div>

                            {{-- List --}}
                            <div class="max-h-64 overflow-y-auto custom-scrollbar">
                                @php
                                    $notifications = $user?->unreadNotifications()->take(5)->get() ?? collect();
                                @endphp

                                @forelse($notifications as $notification)
                                    @php
                                        $data = $notification->data ?? [];
                                        $type = $data['type'] ?? 'info';
                                        $colorClass = match ($type) {
                                            'success' => 'text-emerald-500 bg-emerald-100',
                                            'error' => 'text-rose-500 bg-rose-100',
                                            'warning' => 'text-amber-500 bg-amber-100',
                                            default => 'text-sky-500 bg-sky-100',
                                        };
                                        $icon = match ($type) {
                                            'success' => 'solar:check-circle-bold',
                                            'error' => 'solar:danger-circle-bold',
                                            'warning' => 'solar:shield-warning-bold',
                                            default => 'solar:info-circle-bold',
                                        };
                                    @endphp

                                    <a href="{{ $data['action_url'] ?? '#' }}"
                                        class="block px-4 py-3 hover:bg-slate-50/70 transition duration-150 ease-in-out border-b border-slate-50 last:border-0">
                                        <div class="flex items-start gap-3">
                                            <div class="shrink-0 pt-1">
                                                <div
                                                    class="h-8 w-8 rounded-full flex items-center justify-center {{ $colorClass }}">
                                                    <iconify-icon icon="{{ $icon }}"
                                                        class="text-lg"></iconify-icon>
                                                </div>
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-slate-900 truncate">
                                                    {{ $data['message'] ?? 'New Notification' }}
                                                </p>
                                                <p class="text-xs text-slate-500 mt-0.5">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="px-4 py-8 text-center">
                                        <div
                                            class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-slate-50 mb-3">
                                            <iconify-icon icon="solar:bell-off-linear"
                                                class="text-2xl text-slate-400"></iconify-icon>
                                        </div>
                                        <p class="text-sm text-slate-500">No new notifications</p>
                                    </div>
                                @endforelse
                            </div>

                            {{-- Footer --}}
                            <div
                                class="p-2 border-t border-slate-100 bg-slate-50/70 rounded-b-xl flex items-center gap-2">
                                <form method="POST" action="{{ route('notifications.markAsRead') }}"
                                    class="flex-1">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center justify-center gap-2 px-4 py-2 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-white rounded-lg transition-all border border-transparent hover:border-slate-200 hover:shadow-sm">
                                        <iconify-icon icon="solar:check-read-linear" class="text-base"></iconify-icon>
                                        Mark all as read
                                    </button>
                                </form>

                                <a href="{{ route('notifications.index') ?? '#' }}"
                                    class="text-[11px] text-slate-500 hover:text-slate-800 whitespace-nowrap px-2">
                                    View all
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Flash messages (fixed, above scroll area) --}}
            @if (session('status'))
                <div class="px-4 lg:px-6 pt-4">
                    <div class="rounded-md border border-emerald-200 bg-emerald-50 text-emerald-800 text-sm px-4 py-3">
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            {{-- Breadcrumbs (fixed, above scroll area) --}}
            @isset($breadcrumbs)
                <div class="px-4 lg:px-6 pt-3 text-xs text-slate-500">
                    {{ $breadcrumbs }}
                </div>
            @endisset

            {{-- Main scrollable content --}}
            <main class="flex-1 overflow-y-auto px-4 lg:px-6 py-4 lg:py-6 bg-slate-100">
                <div class="max-w-7xl mx-auto">
                    {{ $slot }}
                </div>
            </main>

            {{-- Footer (fixed at bottom inside main panel) --}}
            <footer class="border-t border-slate-200 px-4 lg:px-6 py-3 text-[11px] text-slate-500 bg-white">
                <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-2">
                    <span>© {{ now()->year }} Printair Advertising. All rights reserved.</span>
                    <span class="text-slate-500">
                        Designed &amp; Developed by
                        <span class="font-medium text-slate-800">Thejan Vishmitha</span>
                    </span>
                </div>
            </footer>
        </div>
    </div>

    {{-- Small inline JS for sidebar toggle (mobile) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('app-sidebar');

            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('-translate-x-full');
                });
            }
        });
    </script>

    @stack('scripts')

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

    <x-analytics-loader />
</body>

</html>
