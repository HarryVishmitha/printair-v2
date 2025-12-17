<x-app-layout>
    {{-- Top bar titles --}}
    <x-slot name="sectionTitle">Catalog</x-slot>
    <x-slot name="pageTitle">Products</x-slot>

    {{-- Breadcrumbs --}}
    <x-slot name="breadcrumbs">
        <span class="text-slate-500">Catalog</span>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">Products</span>
    </x-slot>

    @php
        $search = $filters['q'] ?? '';
        $status = $filters['status'] ?? '';
        $type = $filters['type'] ?? '';
        $visibility = $filters['visibility'] ?? '';
        $categoryId = $filters['category_id'] ?? '';

        $hasAnyFilter =
            $search !== '' || $status !== '' || $type !== '' || $visibility !== '' || (string) $categoryId !== '';
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
                <span
                    class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
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
            <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
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

        {{-- PRINTAIR HERO BAND --}}
        <section
            class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-gradient-to-r from-[#ff4b5c] via-[#ff7a45] to-[#ffb347] px-5 py-5 sm:px-7 sm:py-6 text-white shadow-lg shadow-[#ff4b5c]/20">
            <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl">
            </div>
            <div class="pointer-events-none absolute -bottom-16 right-10 h-56 w-56 rounded-full border border-white/15">
            </div>

            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2 max-w-xl">
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium tracking-wide backdrop-blur">
                        <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        Catalog Layer · Printair v2
                    </div>

                    <div class="flex items-center gap-3 pt-1">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 shadow-inner shadow-black/10">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20.25 7.5l-8.25-4.5-8.25 4.5m16.5 0l-8.25 4.5m8.25-4.5v9l-8.25 4.5m0-9l-8.25-4.5m8.25 4.5v9m-8.25-13.5v9l8.25 4.5" />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold leading-tight">Products</h2>
                            <p class="text-xs sm:text-sm text-white/80">
                                Manage product status, visibility, type, and category assignment.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <div class="inline-flex items-center gap-2 rounded-full bg-black/10 px-3 py-1 text-xs">
                            <span
                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-black/15 text-[11px] font-bold">
                                {{ $products->total() }}
                            </span>
                            <span class="text-white/90">Total products</span>
                        </div>

                        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                            <span>
                                Showing
                                <span class="font-semibold">{{ $products->firstItem() ?? 0 }}</span>
                                –
                                <span class="font-semibold">{{ $products->lastItem() ?? 0 }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-end gap-3 pt-2 lg:pt-0">
                    @if (Route::has('admin.products.create'))
                        <a href="{{ route('admin.products.create') }}"
                            class="group inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-[#ff4b5c] shadow-md shadow-black/10 hover:shadow-lg hover:-translate-y-0.5 transition-all">
                            <svg class="h-4 w-4 text-[#ff4b5c] group-hover:rotate-90 transition-transform" fill="none"
                                viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            New Product
                        </a>
                    @endif
                </div>
            </div>
        </section>

        {{-- SEARCH & FILTER STRIP --}}
        <section class="rounded-3xl border border-slate-200/80 bg-white px-5 py-5 shadow-sm sm:px-6">
            <form method="GET" action="{{ route('admin.products.index') }}"
                class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">

                <div class="flex-1 max-w-xl">
                    <label for="search"
                        class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                        Search products
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.4 11.4z" />
                            </svg>
                        </span>

                        <input type="text" id="search" name="q" value="{{ $search }}"
                            placeholder="Search by name, slug, code…"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 pl-9 pr-10 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20" />

                        @if ($search !== '')
                            <a href="{{ route('admin.products.index') }}"
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

                <div class="flex flex-1 flex-col gap-3 md:flex-row md:flex-wrap md:items-end md:justify-end">
                    <div class="w-full md:w-40">
                        <label for="status"
                            class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Status
                        </label>
                        <select id="status" name="status"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
                            <option value="">All</option>
                            @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'draft' => 'Draft'] as $k => $v)
                                <option value="{{ $k }}" @selected($status === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full md:w-52">
                        <label for="type"
                            class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Type
                        </label>
                        <select id="type" name="type"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
                            <option value="">All</option>
                            @foreach (['standard' => 'Standard', 'dimension_based' => 'Dimension-based', 'finishing' => 'Finishing', 'service' => 'Service'] as $k => $v)
                                <option value="{{ $k }}" @selected($type === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full md:w-44">
                        <label for="visibility"
                            class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Visibility
                        </label>
                        <select id="visibility" name="visibility"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
                            <option value="">All</option>
                            @foreach (['public' => 'Public', 'internal' => 'Internal'] as $k => $v)
                                <option value="{{ $k }}" @selected($visibility === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full md:w-64">
                        <label for="category_id"
                            class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Category
                        </label>
                        <select id="category_id" name="category_id"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
                            <option value="">All</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected((string) $categoryId === (string) $cat->id)>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2 md:gap-3">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6.75h16.5M3.75 12h10.5M3.75 17.25h7.5" />
                            </svg>
                            Apply filters
                        </button>

                        <a href="{{ route('admin.products.index') }}"
                            class="inline-flex items-center gap-1.5 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4.5 19.5l15-15m0 0H9.75m9.75 0v9.75" />
                            </svg>
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </section>

        {{-- MAIN TABLE CARD --}}
        <section class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            @if ($products->count() === 0 && !$hasAnyFilter)
                {{-- Empty state --}}
                <div class="px-6 py-16 text-center">
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-50 text-slate-400 shadow-inner">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.25 7.5l-8.25-4.5-8.25 4.5m16.5 0l-8.25 4.5m8.25-4.5v9l-8.25 4.5m0-9l-8.25-4.5m8.25 4.5v9m-8.25-13.5v9l8.25 4.5" />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900">No products yet</h3>
                    <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto">
                        Add products to your catalog, then configure pricing, visibility and category placement.
                    </p>

                    @if (Route::has('admin.products.create'))
                        <div class="mt-6">
                            <a href="{{ route('admin.products.create') }}"
                                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#ff4b5c] to-[#ff7a45] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#ff4b5c]/30 hover:shadow-lg hover:-translate-y-0.5 transition-all">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.4"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Create your first product
                            </a>
                        </div>
                    @endif
                </div>
            @elseif ($products->count() === 0 && $hasAnyFilter)
                {{-- Empty search/filter --}}
                <div class="px-6 py-12 text-center">
                    <div
                        class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.4 11.4z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-900">No products match your filters</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Try a different keyword or
                        <a href="{{ route('admin.products.index') }}" class="font-medium text-[#ff4b5c] hover:text-[#ff2a3c]">
                            clear filters
                        </a>
                        to see all products.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50/70">
                            <tr>
                                <th
                                    class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Code
                                </th>
                                <th
                                    class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Product
                                </th>
                                <th
                                    class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Category
                                </th>
                                <th
                                    class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Type
                                </th>
                                <th
                                    class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Visibility
                                </th>
                                <th
                                    class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Status
                                </th>
                                <th
                                    class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Updated
                                </th>
                                <th
                                    class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($products as $p)
                                @php
                                    $statusColor = match ($p->status) {
                                        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                        'inactive' => 'bg-slate-50 text-slate-600 ring-slate-200',
                                        default => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                                    };

                                    $visibilityColor =
                                        $p->visibility === 'internal'
                                            ? 'bg-amber-50 text-amber-800 ring-amber-200'
                                            : 'bg-emerald-50 text-emerald-700 ring-emerald-200';
                                @endphp

                                <tr class="group hover:bg-sky-50/40 transition-colors">
                                    {{-- CODE --}}
                                    <td class="px-6 py-4 align-top whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center rounded-xl bg-slate-100 px-2.5 py-1 font-mono text-[11px] text-slate-700">
                                            {{ $p->product_code ?? '—' }}
                                        </span>
                                    </td>

                                    {{-- PRODUCT --}}
                                    <td class="px-6 py-4 align-top">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                                                <span class="text-xs font-semibold">
                                                    {{ strtoupper(substr($p->name ?? 'P', 0, 1)) }}
                                                </span>
                                            </div>

                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-sm font-semibold text-slate-900">
                                                        {{ $p->name }}
                                                    </span>

                                                    <span
                                                        class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 {{ $statusColor }}">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                                        {{ ucfirst($p->status) }}
                                                    </span>

                                                    <span
                                                        class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 {{ $visibilityColor }}">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                                        {{ ucfirst($p->visibility) }}
                                                    </span>
                                                </div>

                                                <div class="mt-1 text-[11px] text-slate-500">
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 ring-1 ring-slate-200">
                                                        <span class="text-slate-400">Slug:</span>
                                                        <span class="font-medium text-slate-700">{{ $p->slug }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- CATEGORY --}}
                                    <td class="px-4 py-4 align-top">
                                        <span class="text-sm text-slate-700">
                                            {{ $p->category?->name ?? '—' }}
                                        </span>
                                    </td>

                                    {{-- TYPE --}}
                                    <td class="px-4 py-4 align-top whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center rounded-xl bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-700">
                                            {{ str_replace('_', ' ', $p->product_type) }}
                                        </span>
                                    </td>

                                    {{-- VISIBILITY --}}
                                    <td class="px-4 py-4 align-top whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 {{ $visibilityColor }}">
                                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                            {{ ucfirst($p->visibility) }}
                                        </span>
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="px-4 py-4 align-top whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 {{ $statusColor }}">
                                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                            {{ ucfirst($p->status) }}
                                        </span>
                                    </td>

                                    {{-- UPDATED --}}
                                    <td class="px-4 py-4 align-top whitespace-nowrap">
                                        <span class="text-sm text-slate-600">
                                            {{ optional($p->updated_at)->format('Y-m-d') ?? '—' }}
                                        </span>
                                    </td>

                                    {{-- ACTIONS --}}
                                    <td class="px-6 py-4 align-top text-right whitespace-nowrap">
                                        <div
                                            class="inline-flex items-center gap-2 opacity-70 group-hover:opacity-100 transition-opacity">
                                            @if (Route::has('admin.products.edit'))
                                                <a href="{{ route('admin.products.edit', $p) }}"
                                                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                                    </svg>
                                                    Edit
                                                </a>
                                            @endif

                                            @if (Route::has('admin.products.pricing.index'))
                                                <a href="{{ route('admin.products.pricing.index', $p) }}"
                                                    class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3 py-1.5 text-[11px] font-medium text-white shadow-sm hover:bg-slate-800">
                                                    Pricing
                                                </a>
                                            @endif

                                            @if (!Route::has('admin.products.edit') && !Route::has('admin.products.pricing.index'))
                                                <span class="text-[11px] text-slate-400">—</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- FOOTER / PAGINATION --}}
                <div
                    class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50/60 px-5 py-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        Showing
                        <span class="font-semibold text-slate-700">{{ $products->firstItem() ?? 0 }}</span>
                        to
                        <span class="font-semibold text-slate-700">{{ $products->lastItem() ?? 0 }}</span>
                        of
                        <span class="font-semibold text-slate-700">{{ $products->total() }}</span>
                        products
                    </div>
                    <div class="sm:ml-auto">
                        {{ $products->onEachSide(1)->links() }}
                    </div>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
