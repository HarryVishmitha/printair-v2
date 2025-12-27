<x-app-layout>
    <x-slot name="sectionTitle">Sales</x-slot>
    <x-slot name="pageTitle">Invoices</x-slot>

    <x-slot name="breadcrumbs">
        <span class="text-slate-500">Sales</span>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">Invoices</span>
    </x-slot>

    @php
        $filters = [
            'search' => (string) request('search', ''),
            'status' => (string) request('status', ''),
            'type' => (string) request('type', ''),
            'working_group_id' => (string) request('working_group_id', ''),
        ];

        // Calculate KPIs
        $kpis = [
            'total' => $invoices->total(),
            'issued' => $invoices->where('status', 'issued')->count(),
            'paid' => $invoices->where('status', 'paid')->count(),
            'overdue' => $invoices->where('status', 'overdue')->count(),
            'total_value' => $invoices->sum('grand_total'),
            'amount_due' => $invoices->sum('amount_due'),
            'currency' => $invoices->first()->currency ?? 'LKR',
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
            class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 px-5 py-5 sm:px-7 sm:py-6 text-white shadow-lg shadow-emerald-600/20">
            <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-16 right-10 h-56 w-56 rounded-full border border-white/15"></div>

            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2 max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium tracking-wide backdrop-blur">
                        <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        Invoice Management · Printair v2
                    </div>

                    <div class="flex items-center gap-3 pt-1">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 shadow-inner shadow-black/10">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold leading-tight">Invoice & Billing Control</h2>
                            <p class="text-xs sm:text-sm text-white/80">
                                Track invoice lifecycle, payment collection, and account receivables across orders.
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
                            <span>Issued: <span class="font-semibold">{{ $kpis['issued'] }}</span></span>
                        </div>

                        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-500/20 px-3 py-1 text-xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                            <span>Paid: <span class="font-semibold">{{ $kpis['paid'] }}</span></span>
                        </div>

                        <div class="inline-flex items-center gap-2 rounded-full bg-rose-500/20 px-3 py-1 text-xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-rose-300"></span>
                            <span>Overdue: <span class="font-semibold">{{ $kpis['overdue'] }}</span></span>
                        </div>
                    </div>
                </div>

                <div class="flex items-end gap-3 pt-2 lg:pt-0">
                    @if (Route::has('admin.invoices.create'))
                        <a href="{{ route('admin.invoices.create') }}"
                            class="group inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-emerald-600 shadow-md shadow-black/10 hover:shadow-lg hover:-translate-y-0.5 transition-all">
                            <svg class="h-4 w-4 text-emerald-600 group-hover:rotate-90 transition-transform" fill="none"
                                viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            New Invoice
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
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Total Invoiced</p>
                        <p class="mt-2 text-2xl font-black text-slate-900">
                            {{ $kpis['currency'] }} {{ $money($kpis['total_value']) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">All invoices in current view</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Amount Due</p>
                <p class="mt-2 text-2xl font-black text-rose-600">
                    {{ $kpis['currency'] }} {{ $money($kpis['amount_due']) }}
                </p>
                <p class="mt-1 text-xs text-slate-500">Outstanding receivables</p>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Collection Rate</p>
                <p class="mt-2 text-2xl font-black text-emerald-600">
                    {{ $kpis['total_value'] > 0 ? number_format((($kpis['total_value'] - $kpis['amount_due']) / $kpis['total_value']) * 100, 1) : '0.0' }}%
                </p>
                <p class="mt-1 text-xs text-slate-500">Payment collection efficiency</p>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Avg Invoice</p>
                <p class="mt-2 text-2xl font-black text-slate-900">
                    {{ $kpis['currency'] }} {{ $kpis['total'] > 0 ? $money($kpis['total_value'] / $kpis['total']) : '0.00' }}
                </p>
                <p class="mt-1 text-xs text-slate-500">Average invoice value</p>
            </div>
        </section>

        {{-- SEARCH & FILTER STRIP --}}

        {{-- SEARCH & FILTER STRIP --}}
        <section class="rounded-3xl border border-slate-200/80 bg-white px-5 py-5 shadow-sm sm:px-6">
            <form method="GET" action="{{ route('admin.invoices.index') }}"
                class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">

                <div class="flex-1 max-w-xl">
                    <label for="search" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                        Search invoices
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.4 11.4z" />
                            </svg>
                        </span>

                        <input type="text" id="search" name="search" value="{{ $filters['search'] }}"
                            placeholder="Invoice no, Order no, customer…"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 pl-9 pr-10 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/20" />

                        @if ($filters['search'] !== '')
                            <a href="{{ route('admin.invoices.index') }}"
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
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/20">
                            <option value="">All</option>
                            @foreach (['draft' => 'Draft', 'issued' => 'Issued', 'paid' => 'Paid', 'partially_paid' => 'Partially Paid', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled'] as $k => $v)
                                <option value="{{ $k }}" {{ $filters['status'] === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full md:w-44">
                        <label for="type" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Type
                        </label>
                        <select id="type" name="type"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/20">
                            <option value="">All</option>
                            @foreach (['final' => 'Final', 'partial' => 'Partial', 'proforma' => 'Proforma'] as $k => $v)
                                <option value="{{ $k }}" {{ $filters['type'] === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full md:w-36">
                        <label for="working_group_id" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Working Group
                        </label>
                        <input type="text" id="working_group_id" name="working_group_id" value="{{ $filters['working_group_id'] }}"
                            placeholder="1"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/20" />
                    </div>

                    <div class="flex items-center gap-2 md:gap-3">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6.75h16.5M3.75 12h10.5M3.75 17.25h7.5" />
                            </svg>
                            Apply filters
                        </button>

                        <a href="{{ route('admin.invoices.index') }}"
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
                        <th class="px-5 py-4 text-left">Invoice</th>
                        <th class="px-5 py-4 text-left">Order</th>
                        <th class="px-5 py-4 text-left">Status</th>
                        <th class="px-5 py-4 text-right">Total</th>
                        <th class="px-5 py-4 text-right">Due</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($invoices as $inv)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900">{{ $inv->invoice_no ?? ('INV-' . $inv->id) }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    #{{ $inv->id }} · WG {{ $inv->working_group_id }} · {{ strtoupper((string) $inv->type) }}
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @if ($inv->order_id && Route::has('admin.orders.show'))
                                    <a href="{{ route('admin.orders.show', $inv->order_id) }}"
                                        class="font-semibold text-emerald-600 hover:text-emerald-700 hover:underline">
                                        {{ $inv->order?->order_no ?? ('ORD-' . $inv->order_id) }}
                                    </a>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $statusColors = [
                                        'draft' => 'bg-slate-100 border-slate-300 text-slate-700',
                                        'issued' => 'bg-sky-100 border-sky-300 text-sky-700',
                                        'paid' => 'bg-emerald-100 border-emerald-300 text-emerald-700',
                                        'partially_paid' => 'bg-amber-100 border-amber-300 text-amber-700',
                                        'overdue' => 'bg-rose-100 border-rose-300 text-rose-700',
                                        'cancelled' => 'bg-slate-100 border-slate-300 text-slate-700',
                                    ];
                                    $colorClass = $statusColors[$inv->status] ?? 'bg-slate-100 border-slate-300 text-slate-700';
                                @endphp
                                <span
                                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $colorClass }}">
                                    {{ strtoupper((string) $inv->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="font-extrabold text-slate-900">
                                    {{ $inv->currency ?? 'LKR' }} {{ number_format((float) $inv->grand_total, 2) }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="font-extrabold {{ $inv->amount_due > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $inv->currency ?? 'LKR' }} {{ number_format((float) $inv->amount_due, 2) }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.invoices.show', $inv) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-emerald-600 hover:bg-emerald-50 transition-colors">
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
                                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-slate-900">No invoices found</p>
                                    <p class="text-xs text-slate-500">Try adjusting your search or filter criteria</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <div>
            {{ $invoices->links() }}
        </div>
    </div>
</x-app-layout>

