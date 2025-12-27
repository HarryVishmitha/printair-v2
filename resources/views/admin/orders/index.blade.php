<x-app-layout>
    <x-slot name="sectionTitle">Sales</x-slot>
    <x-slot name="pageTitle">Orders</x-slot>

    <x-slot name="breadcrumbs">
        <span class="text-slate-500">Sales</span>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">Orders</span>
    </x-slot>

    @php
        $filters = [
            'search' => (string) request('search', ''),
            'status' => (string) request('status', ''),
            'payment_status' => (string) request('payment_status', ''),
            'working_group_id' => (string) request('working_group_id', ''),
        ];

        // Calculate KPIs
        $kpis = [
            'total' => $orders->total(),
            'confirmed' => $orders->where('status', 'confirmed')->count(),
            'processing' => $orders->where('status', 'processing')->count(),
            'completed' => $orders->where('status', 'completed')->count(),
            'total_value' => $orders->sum('grand_total'),
            'unpaid_value' => $orders->where('payment_status', '!=', 'paid')->sum('grand_total'),
            'currency' => $orders->first()->currency ?? 'LKR',
        ];
    @endphp

    <div class="space-y-6">
        {{-- Toasts --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => (show = false), 4500)"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-gradient-to-r from-emerald-50 via-emerald-50/90 to-white px-4 py-3.5 text-sm text-emerald-800 shadow-sm">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </span>
                <span class="font-medium">{{ session('success') }}</span>
                <button @click="show = false" class="ml-auto text-emerald-500 hover:text-emerald-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => (show = false), 6500)"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                class="flex items-center gap-3 rounded-xl border border-rose-200 bg-gradient-to-r from-rose-50 via-rose-50/90 to-white px-4 py-3.5 text-sm text-rose-800 shadow-sm">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m0 3h.008v.008H12V15.75zm9-.75a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <span class="font-medium">{{ session('error') }}</span>
                <button @click="show = false" class="ml-auto text-rose-500 hover:text-rose-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        {{-- HERO BAND --}}
        <section
            class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 px-5 py-5 sm:px-7 sm:py-6 text-white shadow-lg shadow-indigo-600/20">
            <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-16 right-10 h-56 w-56 rounded-full border border-white/15"></div>

            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2 max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium tracking-wide backdrop-blur">
                        <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        Order Management · Printair v2
                    </div>

                    <div class="flex items-center gap-3 pt-1">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 shadow-inner shadow-black/10">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold leading-tight">Orders & Fulfillment Control</h2>
                            <p class="text-xs sm:text-sm text-white/80">
                                Track order lifecycle, payment status, and fulfillment operations across working groups.
                            </p>
                        </div>
                    </div>

                    {{-- KPI mini chips --}}
                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/15 text-[11px] font-bold">
                                {{ $kpis['total'] }}
                            </span>
                            <span class="text-white/90">Total</span>
                        </div>

                        <div class="inline-flex items-center gap-2 rounded-full bg-sky-500/20 px-3 py-1 text-xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-sky-300"></span>
                            <span>Confirmed: <span class="font-semibold">{{ $kpis['confirmed'] }}</span></span>
                        </div>

                        <div class="inline-flex items-center gap-2 rounded-full bg-amber-500/20 px-3 py-1 text-xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
                            <span>Processing: <span class="font-semibold">{{ $kpis['processing'] }}</span></span>
                        </div>

                        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-500/20 px-3 py-1 text-xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                            <span>Completed: <span class="font-semibold">{{ $kpis['completed'] }}</span></span>
                        </div>
                    </div>
                </div>

                <div class="flex items-end gap-3 pt-2 lg:pt-0">
                    @if (Route::has('admin.orders.create'))
                        <a href="{{ route('admin.orders.create') }}"
                            class="group inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-600 shadow-md shadow-black/10 hover:shadow-lg hover:-translate-y-0.5 transition-all">
                            <svg class="h-4 w-4 text-indigo-600 group-hover:rotate-90 transition-transform" fill="none"
                                viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            New Order
                        </a>
                    @endif
                </div>
            </div>
        </section>

        {{-- KPI CARD STRIP --}}
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            @php
                $money = fn ($v) => number_format((float) $v, 2);
            @endphp

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Total Value</p>
                        <p class="mt-2 text-2xl font-black text-slate-900">
                            {{ $kpis['currency'] }} {{ $money($kpis['total_value']) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">All orders in current view</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Unpaid Value</p>
                <p class="mt-2 text-2xl font-black text-rose-600">
                    {{ $kpis['currency'] }} {{ $money($kpis['unpaid_value']) }}
                </p>
                <p class="mt-1 text-xs text-slate-500">Orders with pending payment</p>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Avg Order</p>
                <p class="mt-2 text-2xl font-black text-slate-900">
                    {{ $kpis['currency'] }} {{ $kpis['total'] > 0 ? $money($kpis['total_value'] / $kpis['total']) : '0.00' }}
                </p>
                <p class="mt-1 text-xs text-slate-500">Average order value</p>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Orders Today</p>
                <p class="mt-2 text-2xl font-black text-emerald-600">
                    {{ $orders->where('created_at', '>=', now()->startOfDay())->count() }}
                </p>
                <p class="mt-1 text-xs text-slate-500">Created since midnight</p>
            </div>
        </section>

        {{-- SEARCH & FILTER STRIP --}}
        <section class="rounded-3xl border border-slate-200/80 bg-white px-5 py-5 shadow-sm sm:px-6">
            <form method="GET" action="{{ route('admin.orders.index') }}"
                class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">

                <div class="flex-1 max-w-xl">
                    <label for="search" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                        Search orders
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.4 11.4z" />
                            </svg>
                        </span>

                        <input type="text" id="search" name="search" value="{{ $filters['search'] }}"
                            placeholder="Order no, customer name, email…"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 pl-9 pr-10 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-indigo-600 focus:bg-white focus:ring-2 focus:ring-indigo-600/20" />

                        @if ($filters['search'] !== '')
                            <a href="{{ route('admin.orders.index') }}"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600"
                                title="Clear search">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="flex flex-1 flex-col gap-3 md:flex-row md:items-end md:justify-end">
                    <div class="w-full md:w-48">
                        <label for="status" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Status
                        </label>
                        <select id="status" name="status"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-indigo-600 focus:bg-white focus:ring-2 focus:ring-indigo-600/20">
                            <option value="">All</option>
                            @foreach (['draft' => 'Draft', 'confirmed' => 'Confirmed', 'processing' => 'Processing', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $k => $v)
                                <option value="{{ $k }}" {{ $filters['status'] === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full md:w-48">
                        <label for="payment_status" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Payment
                        </label>
                        <select id="payment_status" name="payment_status"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-indigo-600 focus:bg-white focus:ring-2 focus:ring-indigo-600/20">
                            <option value="">All</option>
                            @foreach (['unpaid' => 'Unpaid', 'partially_paid' => 'Partially Paid', 'paid' => 'Paid', 'refunded' => 'Refunded'] as $k => $v)
                                <option value="{{ $k }}" {{ $filters['payment_status'] === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full md:w-36">
                        <label for="working_group_id" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Working Group
                        </label>
                        <input type="text" id="working_group_id" name="working_group_id" value="{{ $filters['working_group_id'] }}"
                            placeholder="1"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-indigo-600 focus:bg-white focus:ring-2 focus:ring-indigo-600/20" />
                    </div>

                    <div class="flex items-center gap-2 md:gap-3">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6.75h16.5M3.75 12h10.5M3.75 17.25h7.5" />
                            </svg>
                            Apply filters
                        </button>

                        <a href="{{ route('admin.orders.index') }}"
                            class="inline-flex items-center gap-1.5 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4.5 19.5l15-15m0 0H9.75m9.75 0v9.75" />
                            </svg>
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </section>

        {{-- DATA TABLE --}}
        <section class="overflow-x-auto rounded-3xl border border-slate-200/80 bg-white shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-xs font-black uppercase tracking-wider text-slate-700">
                        <th class="px-5 py-4 text-left">Order</th>
                        <th class="px-5 py-4 text-left">Customer</th>
                        <th class="px-5 py-4 text-left">Status</th>
                        <th class="px-5 py-4 text-left">Payment</th>
                        <th class="px-5 py-4 text-right">Total</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $o)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900">
                                    {{ $o->order_no ?? ('ORD-' . $o->id) }}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    #{{ $o->id }} · WG {{ $o->working_group_id }}
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900">
                                    {{ $o->customer?->full_name ?? $o->customer_name ?? data_get($o->customer_snapshot, 'full_name') ?? '—' }}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ $o->customer?->email ?? $o->customer_email ?? data_get($o->customer_snapshot, 'email') ?? '—' }}
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $statusColors = [
                                        'draft' => 'bg-slate-100 border-slate-300 text-slate-700',
                                        'confirmed' => 'bg-sky-100 border-sky-300 text-sky-700',
                                        'processing' => 'bg-amber-100 border-amber-300 text-amber-700',
                                        'completed' => 'bg-emerald-100 border-emerald-300 text-emerald-700',
                                        'cancelled' => 'bg-rose-100 border-rose-300 text-rose-700',
                                    ];
                                    $colorClass = $statusColors[$o->status] ?? 'bg-slate-100 border-slate-300 text-slate-700';
                                @endphp
                                <span
                                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $colorClass }}">
                                    {{ strtoupper((string) $o->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $paymentColors = [
                                        'unpaid' => 'bg-rose-100 border-rose-300 text-rose-700',
                                        'partially_paid' => 'bg-amber-100 border-amber-300 text-amber-700',
                                        'paid' => 'bg-emerald-100 border-emerald-300 text-emerald-700',
                                        'refunded' => 'bg-slate-100 border-slate-300 text-slate-700',
                                    ];
                                    $paymentColorClass = $paymentColors[$o->payment_status ?? 'unpaid'] ?? 'bg-slate-100 border-slate-300 text-slate-700';
                                @endphp
                                <span
                                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $paymentColorClass }}">
                                    {{ strtoupper((string) ($o->payment_status ?? 'UNPAID')) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="font-extrabold text-slate-900">
                                    {{ $o->currency ?? 'LKR' }} {{ number_format((float) $o->grand_total, 2) }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.orders.show', $o) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-slate-900">No orders found</p>
                                    <p class="text-xs text-slate-500">Try adjusting your search or filter criteria</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <div>
            {{ $orders->links() }}
        </div>
    </div>
</x-app-layout>

