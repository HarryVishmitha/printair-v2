{{-- resources/views/admin/estimates/index.blade.php --}}

<x-app-layout>
    <x-slot name="sectionTitle">Sales</x-slot>
    <x-slot name="pageTitle">Estimates (Quotations)</x-slot>

    <x-slot name="breadcrumbs">
        <span class="text-slate-500">Sales</span>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">Estimates</span>
    </x-slot>

    <div class="space-y-6">

        {{-- Toasts (reuse your pattern) --}}
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
                <button @click="show=false" class="ml-auto text-emerald-500 hover:text-emerald-700">
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
                <button @click="show=false" class="ml-auto text-rose-500 hover:text-rose-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        {{-- HERO BAND --}}
        <section
            class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-5 py-5 sm:px-7 sm:py-6 text-white shadow-lg shadow-slate-900/20">
            <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-16 right-10 h-56 w-56 rounded-full border border-white/10"></div>

            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2 max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium tracking-wide backdrop-blur">
                        <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        Sales Pipeline · Printair v2
                    </div>

                    <div class="flex items-center gap-3 pt-1">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 shadow-inner shadow-black/20">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 7.5h6M9 12h6m-9 7.5h12A2.25 2.25 0 0020.25 17.25V6.75A2.25 2.25 0 0018 4.5H6A2.25 2.25 0 003.75 6.75v10.5A2.25 2.25 0 006 19.5z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold leading-tight">Estimates & Quotation Control</h2>
                            <p class="text-xs sm:text-sm text-white/70">
                                Track pipeline health, send/accept status, conversion to orders, and audit-ready actions.
                            </p>
                        </div>
                    </div>

                    {{-- KPI mini chips --}}
                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/10 text-[11px] font-bold">
                                {{ $kpis['total'] ?? $estimates->total() }}
                            </span>
                            <span class="text-white/90">Total</span>
                        </div>

                        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-500/15 px-3 py-1 text-xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                            <span>Accepted: <span class="font-semibold">{{ $kpis['accepted'] ?? 0 }}</span></span>
                        </div>

                        <div class="inline-flex items-center gap-2 rounded-full bg-sky-500/15 px-3 py-1 text-xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-sky-300"></span>
                            <span>Sent: <span class="font-semibold">{{ $kpis['sent'] ?? 0 }}</span></span>
                        </div>

                        <div class="inline-flex items-center gap-2 rounded-full bg-amber-500/15 px-3 py-1 text-xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
                            <span>Draft: <span class="font-semibold">{{ $kpis['draft'] ?? 0 }}</span></span>
                        </div>

                        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs">
                            <span>Conversion:</span>
                            <span class="font-semibold">{{ $kpis['conversion_rate'] ?? '—' }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-end gap-3 pt-2 lg:pt-0">
                    <a href="{{ route('admin.estimates.create') }}"
                        class="group inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-md shadow-black/10 hover:shadow-lg hover:-translate-y-0.5 transition-all">
                        <svg class="h-4 w-4 text-slate-900 group-hover:rotate-90 transition-transform" fill="none"
                            viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        New Estimate
                    </a>
                </div>
            </div>
        </section>

        {{-- KPI CARD STRIP (enterprise) --}}
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            @php
                $money = fn ($v) => number_format((float) $v, 2);
            @endphp

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Pipeline Value</p>
                        <p class="mt-2 text-2xl font-black text-slate-900">
                            {{ $kpis['currency'] ?? 'LKR' }} {{ $money($kpis['pipeline_value'] ?? 0) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">Total value of open estimates</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Accepted Value</p>
                <p class="mt-2 text-2xl font-black text-emerald-700">
                    {{ $kpis['currency'] ?? 'LKR' }} {{ $money($kpis['accepted_value'] ?? 0) }}
                </p>
                <p class="mt-1 text-xs text-slate-500">Value ready to convert into orders</p>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Avg Estimate</p>
                <p class="mt-2 text-2xl font-black text-slate-900">
                    {{ $kpis['currency'] ?? 'LKR' }} {{ $money($kpis['avg_estimate'] ?? 0) }}
                </p>
                <p class="mt-1 text-xs text-slate-500">Based on selected date range</p>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Expiry Risk</p>
                <p class="mt-2 text-2xl font-black text-rose-600">
                    {{ $kpis['expiring_soon'] ?? 0 }}
                </p>
                <p class="mt-1 text-xs text-slate-500">Valid-until within next 3 days</p>
            </div>
        </section>

        {{-- FILTER STRIP --}}
        @php
            $filters = $filters ?? [];
            $search = $filters['search'] ?? '';
            $filterStatus = $filters['status'] ?? '';
            $wgId = $filters['working_group_id'] ?? '';
            $from = $filters['from'] ?? '';
            $to = $filters['to'] ?? '';
        @endphp

        <section class="rounded-3xl border border-slate-200/80 bg-white px-5 py-5 shadow-sm sm:px-6">
            <form method="GET" action="{{ route('admin.estimates.index') }}"
                class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">

                <div class="flex-1 max-w-xl">
                    <label for="search" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                        Search estimates
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.4 11.4z" />
                            </svg>
                        </span>

                        <input id="search" name="search" value="{{ $search }}" placeholder="Estimate no, customer name, phone…"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 pl-9 pr-10 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10" />

                        @if ($search !== '')
                            <a href="{{ route('admin.estimates.index') }}"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600"
                                title="Clear search">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="flex flex-1 flex-col gap-3 md:flex-row md:items-end md:justify-end">
                    <div class="w-full md:w-48">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Status</label>
                        <select name="status"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                            <option value="">All</option>
                            @foreach (['draft','sent','viewed','accepted','rejected','converted','expired'] as $st)
                                <option value="{{ $st }}" {{ (string)$filterStatus === $st ? 'selected' : '' }}>
                                    {{ strtoupper($st) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full md:w-56">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Working Group</label>
                        <select name="working_group_id"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                            <option value="">All</option>
                            @foreach (($workingGroups ?? []) as $wg)
                                <option value="{{ $wg->id }}" {{ (string)$wgId === (string)$wg->id ? 'selected' : '' }}>
                                    {{ $wg->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full md:w-44">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">From</label>
                        <input type="date" name="from" value="{{ $from }}"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10" />
                    </div>

                    <div class="w-full md:w-44">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">To</label>
                        <input type="date" name="to" value="{{ $to }}"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10" />
                    </div>

                    <div class="flex items-center gap-2 md:gap-3">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6.75h16.5M3.75 12h10.5M3.75 17.25h7.5" />
                            </svg>
                            Apply
                        </button>

                        <a href="{{ route('admin.estimates.index') }}"
                            class="inline-flex items-center gap-1.5 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </section>

        {{-- TABLE CARD --}}
        <section class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            @if ($estimates->count() === 0)
                <div class="px-6 py-12 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.4 11.4z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-900">No estimates found</h3>
                    <p class="mt-1 text-sm text-slate-500">Try adjusting filters or create a new estimate.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50/70">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Estimate</th>
                                <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</th>
                                <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">WG</th>
                                <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Total</th>
                                <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Validity</th>
                                <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($estimates as $e)
                                @php
                                    $rowStatus = $e->status ?? 'draft';
                                    $statusMap = [
                                        'draft' => 'bg-slate-50 text-slate-700 ring-slate-200',
                                        'sent' => 'bg-sky-50 text-sky-700 ring-sky-200',
                                        'viewed' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                                        'accepted' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                        'rejected' => 'bg-rose-50 text-rose-700 ring-rose-200',
                                        'converted' => 'bg-amber-50 text-amber-800 ring-amber-200',
                                        'expired' => 'bg-slate-100 text-slate-600 ring-slate-200',
                                    ];
                                    $badge = $statusMap[$rowStatus] ?? $statusMap['draft'];

                                    $validUntil = $e->valid_until ? \Illuminate\Support\Carbon::parse($e->valid_until) : null;
                                    $isExpired = $validUntil && $validUntil->isPast();
                                    $isExpiring = $validUntil && $validUntil->isFuture() && now()->diffInDays($validUntil) <= 3;
                                    $currency = $e->currency ?? ($kpis['currency'] ?? 'LKR');
                                @endphp

                                <tr class="group hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-4 align-top">
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.estimates.show', $e) }}"
                                                class="text-sm font-semibold text-slate-900 hover:text-slate-700">
                                                {{ $e->estimate_no ?? ('EST-' . $e->id) }}
                                            </a>

                                            <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 ring-1 ring-slate-200">
                                                    <span class="text-slate-400">Items:</span>
                                                    <span class="font-medium text-slate-700">{{ $e->items_count ?? '—' }}</span>
                                                </span>

                                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 ring-1 ring-slate-200">
                                                    <span class="text-slate-400">Created:</span>
                                                    <span class="font-medium text-slate-700">{{ optional($e->created_at)->format('Y-m-d') }}</span>
                                                </span>

                                                @if ($e->locked_at)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-900 px-2 py-0.5 text-white/90">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-white/70"></span>
                                                        Locked
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 align-top">
                                        @php
                                            $snap = is_array($e->customer_snapshot) ? $e->customer_snapshot : [];
                                            $cName = $snap['full_name'] ?? $snap['name'] ?? ($e->customer->full_name ?? '—');
                                            $cPhone = $snap['phone'] ?? ($e->customer->phone ?? null);
                                        @endphp
                                        <div class="text-sm font-medium text-slate-900">{{ $cName }}</div>
                                        <div class="mt-1 text-[12px] text-slate-500">{{ $cPhone ?? '—' }}</div>
                                    </td>

                                    <td class="px-4 py-4 align-top">
                                        <span class="inline-flex items-center gap-1 rounded-xl bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-700 ring-1 ring-slate-200">
                                            {{ $e->workingGroup->name ?? ('WG#' . $e->working_group_id) }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 align-top">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 {{ $badge }}">
                                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                            {{ $rowStatus }}
                                        </span>

                                        @if ($isExpiring)
                                            <div class="mt-2 text-[11px] text-amber-700">
                                                Expiring soon
                                            </div>
                                        @endif

                                        @if ($isExpired)
                                            <div class="mt-2 text-[11px] text-rose-700">
                                                Expired
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4 align-top">
                                        <div class="text-sm font-semibold text-slate-900">
                                            {{ $currency }} {{ number_format((float) $e->grand_total, 2) }}
                                        </div>
                                        <div class="mt-1 text-[11px] text-slate-500">
                                            Sub: {{ number_format((float) $e->subtotal, 2) }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 align-top text-[12px] text-slate-600">
                                        @if ($validUntil)
                                            <div class="font-medium text-slate-700">{{ $validUntil->format('Y-m-d') }}</div>
                                            <div class="mt-1 text-[11px] text-slate-500">
                                                {{ $validUntil->isPast() ? 'Past due' : ('In ' . now()->diffInDays($validUntil) . ' days') }}
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">—</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 align-top text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('admin.estimates.show', $e) }}"
                                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300">
                                                View
                                            </a>

                                            {{-- Context actions (enterprise logic) --}}
                                            @if (in_array($rowStatus, ['draft','viewed'], true))
                                                <button type="button"
                                                    @click="$dispatch('open-send-estimate', { id: {{ $e->id }}, no: @js($e->estimate_no) })"
                                                    class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-slate-800">
                                                    Send
                                                </button>
                                            @endif

                                            @if (in_array($rowStatus, ['sent','viewed'], true))
                                                <button type="button"
                                                    @click="$dispatch('open-accept-estimate', { id: {{ $e->id }}, no: @js($e->estimate_no) })"
                                                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700">
                                                    Accept
                                                </button>

                                                <button type="button"
                                                    @click="$dispatch('open-reject-estimate', { id: {{ $e->id }}, no: @js($e->estimate_no) })"
                                                    class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-rose-600 shadow-sm hover:bg-rose-50">
                                                    Reject
                                                </button>
                                            @endif

                                            @if ($rowStatus === 'accepted')
                                                <button type="button"
                                                    @click="$dispatch('open-convert-order', { id: {{ $e->id }}, no: @js($e->estimate_no) })"
                                                    class="inline-flex items-center gap-1.5 rounded-xl bg-amber-500 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-amber-600">
                                                    Convert → Order
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination footer --}}
                <div class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50/60 px-5 py-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        Showing
                        <span class="font-semibold text-slate-700">{{ $estimates->firstItem() }}</span>
                        to
                        <span class="font-semibold text-slate-700">{{ $estimates->lastItem() }}</span>
                        of
                        <span class="font-semibold text-slate-700">{{ $estimates->total() }}</span>
                        estimates
                    </div>
                    <div class="sm:ml-auto">
                        {{ $estimates->onEachSide(1)->links() }}
                    </div>
                </div>
            @endif
        </section>

        {{-- Modals: Send / Accept / Reject / Convert --}}
        @include('admin.estimates.partials.modals')
    </div>
</x-app-layout>

