{{-- resources/views/admin/categories/index.blade.php --}}

<x-app-layout>
    {{-- Top bar titles --}}
    <x-slot name="sectionTitle">Categories</x-slot>
    <x-slot name="pageTitle">Product Categories & Navigation</x-slot>

    {{-- Breadcrumbs --}}
    <x-slot name="breadcrumbs">
        <span class="text-slate-500">Catalog</span>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">Categories</span>
    </x-slot>

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

        {{-- HERO BAND --}}
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
                                    d="M3 7.5h6m-6 4.5h18M3 16.5h10m7.5-9.75h.008v.008H20.5V6.75z" />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold leading-tight">
                                Categories & Navigation Control
                            </h2>
                            <p class="text-xs sm:text-sm text-white/80">
                                Manage product grouping, parent-child structure, sorting, and navbar visibility for a
                                clean customer experience.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <div class="inline-flex items-center gap-2 rounded-full bg-black/10 px-3 py-1 text-xs">
                            <span
                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-black/15 text-[11px] font-bold">
                                {{ $categories->total() }}
                            </span>
                            <span class="text-white/90">Total categories</span>
                        </div>

                        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                            <span>Navbar visibility is controllable per category</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-end gap-3 pt-2 lg:pt-0">
                    <a href="{{ route('admin.categories.create') }}"
                        class="group inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-[#ff4b5c] shadow-md shadow-black/10 hover:shadow-lg hover:-translate-y-0.5 transition-all">
                        <svg class="h-4 w-4 text-[#ff4b5c] group-hover:rotate-90 transition-transform" fill="none"
                            viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        New Category
                    </a>
                </div>
            </div>
        </section>

        {{-- SEARCH & FILTER STRIP --}}
        @php
            $search = $filters['search'] ?? '';
            $active = $filters['active'] ?? '';
            $navbar = $filters['navbar'] ?? '';
            $featured = $filters['featured'] ?? '';
            $parentId = $filters['parent_id'] ?? '';
        @endphp

        <section class="rounded-3xl border border-slate-200/80 bg-white px-5 py-5 shadow-sm sm:px-6">
            <form method="GET" action="{{ route('admin.categories.index') }}"
                class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">

                <div class="flex-1 max-w-xl">
                    <label for="search"
                        class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                        Search categories
                    </label>
                    <div class="relative">
                        <span
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.4 11.4z" />
                            </svg>
                        </span>

                        <input type="text" id="search" name="search" value="{{ $search }}"
                            placeholder="Search by name, slug, code…"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 pl-9 pr-10 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20" />

                        @if ($search !== '')
                            <a href="{{ route('admin.categories.index') }}"
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
                    <div class="w-full md:w-40">
                        <label for="active"
                            class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Status
                        </label>
                        <select id="active" name="active"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
                            <option value="">All</option>
                            <option value="1" {{ (string) $active === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ (string) $active === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="w-full md:w-44">
                        <label for="navbar"
                            class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Navbar
                        </label>
                        <select id="navbar" name="navbar"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
                            <option value="">All</option>
                            <option value="1" {{ (string) $navbar === '1' ? 'selected' : '' }}>Visible</option>
                            <option value="0" {{ (string) $navbar === '0' ? 'selected' : '' }}>Hidden</option>
                        </select>
                    </div>

                    <div class="w-full md:w-44">
                        <label for="featured"
                            class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Featured
                        </label>
                        <select id="featured" name="featured"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
                            <option value="">All</option>
                            <option value="1" {{ (string) $featured === '1' ? 'selected' : '' }}>Featured
                            </option>
                            <option value="0" {{ (string) $featured === '0' ? 'selected' : '' }}>Not featured
                            </option>
                        </select>
                    </div>

                    <div class="w-full md:w-56">
                        <label for="parent_id"
                            class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Parent
                        </label>
                        <select id="parent_id" name="parent_id"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
                            <option value="">All (Top + Sub)</option>
                            <option value="__top__" {{ (string) $parentId === '__top__' ? 'selected' : '' }}>
                                Top-level only
                            </option>
                            @foreach ($parents as $p)
                                <option value="{{ $p->id }}"
                                    {{ (string) $parentId === (string) $p->id ? 'selected' : '' }}>
                                    Under: {{ $p->name }}
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

                        <a href="{{ route('admin.categories.index') }}"
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
            @if (
                $categories->count() === 0 &&
                    $search === '' &&
                    $active === '' &&
                    $navbar === '' &&
                    $featured === '' &&
                    $parentId === '')
                {{-- Empty state --}}
                <div class="px-6 py-16 text-center">
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-50 text-slate-400 shadow-inner">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h6m-6 4.5h18M3 16.5h10" />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900">No categories yet</h3>
                    <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto">
                        Start by creating categories for your products. You’ll be able to control navbar visibility,
                        sorting, and parent-child grouping.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('admin.categories.create') }}"
                            class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#ff4b5c] to-[#ff7a45] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#ff4b5c]/30 hover:shadow-lg hover:-translate-y-0.5 transition-all">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.4"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Create your first category
                        </a>
                    </div>
                </div>
            @elseif ($categories->count() === 0)
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
                    <h3 class="text-sm font-semibold text-slate-900">No categories match your filters</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Try a different keyword or
                        <a href="{{ route('admin.categories.index') }}"
                            class="font-medium text-[#ff4b5c] hover:text-[#ff2a3c]">
                            clear filters
                        </a>
                        to see all categories.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50/70">
                            <tr>
                                <th
                                    class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Category
                                </th>
                                <th
                                    class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Parent
                                </th>
                                <th
                                    class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Visibility
                                </th>
                                <th
                                    class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    SEO / Meta
                                </th>
                                <th
                                    class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($categories as $category)
                                @php
                                    $statusColor = $category->is_active
                                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                        : 'bg-slate-50 text-slate-600 ring-slate-200';

                                    $navbarColor = $category->show_in_navbar
                                        ? 'bg-sky-50 text-sky-700 ring-sky-200'
                                        : 'bg-slate-50 text-slate-600 ring-slate-200';

                                    $featuredColor = $category->is_featured
                                        ? 'bg-amber-50 text-amber-800 ring-amber-200'
                                        : 'bg-slate-50 text-slate-600 ring-slate-200';

                                    $indexColor = $category->is_indexable
                                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                        : 'bg-rose-50 text-rose-700 ring-rose-200';
                                @endphp

                                <tr class="group hover:bg-sky-50/40 transition-colors">
                                    {{-- CATEGORY --}}
                                    <td class="px-6 py-4 align-top">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                                                <span class="text-xs font-semibold">
                                                    {{ strtoupper(substr($category->name, 0, 1)) }}
                                                </span>
                                            </div>

                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-sm font-semibold text-slate-900">
                                                        {{ $category->name }}
                                                    </span>

                                                    <span
                                                        class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 {{ $statusColor }}">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                                    </span>

                                                    <span
                                                        class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 {{ $featuredColor }}">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                                        {{ $category->is_featured ? 'Featured' : 'Normal' }}
                                                    </span>
                                                </div>

                                                <div
                                                    class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                                                    <span
                                                        class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 ring-1 ring-slate-200">
                                                        <span class="text-slate-400">Slug:</span>
                                                        <span
                                                            class="font-medium text-slate-700">{{ $category->slug }}</span>
                                                    </span>

                                                    <span
                                                        class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 ring-1 ring-slate-200">
                                                        <span class="text-slate-400">Sort:</span>
                                                        <span
                                                            class="font-medium text-slate-700">{{ $category->sort_order }}</span>
                                                    </span>

                                                    @if ($category->code)
                                                        <span
                                                            class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 ring-1 ring-slate-200">
                                                            <span class="text-slate-400">Code:</span>
                                                            <span
                                                                class="font-medium text-slate-700">{{ $category->code }}</span>
                                                        </span>
                                                    @endif
                                                </div>

                                                @if ($category->short_description)
                                                    <p class="mt-2 text-[12px] text-slate-500 line-clamp-2">
                                                        {{ $category->short_description }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- PARENT --}}
                                    <td class="px-4 py-4 align-top">
                                        <div class="text-xs text-slate-600">
                                            @if ($category->parent)
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-xl bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-700 ring-1 ring-slate-200">
                                                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M7.5 8.25h9m-9 3.75h6" />
                                                    </svg>
                                                    <span>{{ $category->parent->name }}</span>
                                                </span>
                                            @else
                                                <span class="text-[11px] text-slate-400 italic">Top-level</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- VISIBILITY --}}
                                    <td class="px-4 py-4 align-top">
                                        <div class="flex flex-col gap-2 text-xs">
                                            <span
                                                class="inline-flex w-fit items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 {{ $navbarColor }}">
                                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                                Navbar: {{ $category->show_in_navbar ? 'Visible' : 'Hidden' }}
                                            </span>

                                            <span
                                                class="inline-flex w-fit items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 {{ $indexColor }}">
                                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                                {{ $category->is_indexable ? 'Indexable' : 'Noindex' }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- SEO / META --}}
                                    <td class="px-4 py-4 align-top">
                                        <div class="space-y-1 text-[11px] text-slate-500">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-medium text-slate-700">SEO Title:</span>
                                                <span class="truncate">
                                                    {{ $category->seo_title ? \Illuminate\Support\Str::limit($category->seo_title, 32) : '—' }}
                                                </span>
                                            </div>

                                            <div class="flex items-center gap-1.5">
                                                <span class="font-medium text-slate-700">SEO Desc:</span>
                                                <span class="truncate">
                                                    {{ $category->seo_description ? \Illuminate\Support\Str::limit($category->seo_description, 38) : '—' }}
                                                </span>
                                            </div>

                                            <div class="flex items-center gap-1.5">
                                                <span class="font-medium text-slate-700">Meta:</span>
                                                <span>
                                                    @php
                                                        $metaCount = is_array($category->meta)
                                                            ? count($category->meta)
                                                            : 0;
                                                        $settingsCount = is_array($category->settings)
                                                            ? count($category->settings)
                                                            : 0;
                                                    @endphp
                                                    {{ $metaCount + $settingsCount }} keys
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- ACTIONS --}}
                                    <td class="px-6 py-4 align-top text-right whitespace-nowrap">
                                        <div
                                            class="inline-flex items-center gap-2 opacity-70 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('admin.categories.edit', $category) }}"
                                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                                </svg>
                                                Edit
                                            </a>

                                            <button type="button" x-data
                                                @click="$dispatch('open-delete-category-modal', { id: {{ $category->id }}, name: '{{ addslashes($category->name) }}' })"
                                                class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-white px-3 py-1.5 text-[11px] font-medium text-rose-600 shadow-sm hover:bg-rose-50 hover:border-rose-300">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0115.916 21H8.084A2.25 2.25 0 015.84 19.673L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.108 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                                Delete
                                            </button>
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
                        <span class="font-semibold text-slate-700">{{ $categories->firstItem() }}</span>
                        to
                        <span class="font-semibold text-slate-700">{{ $categories->lastItem() }}</span>
                        of
                        <span class="font-semibold text-slate-700">{{ $categories->total() }}</span>
                        categories
                    </div>
                    <div class="sm:ml-auto">
                        {{ $categories->onEachSide(1)->links() }}
                    </div>
                </div>
            @endif
        </section>
    </div>

    {{-- DELETE CONFIRMATION MODAL --}}
    <div x-data="{
        open: false,
        categoryId: null,
        categoryName: '',
        init() {
            this.$watch('open', value => {
                document.body.classList.toggle('overflow-hidden', value);
            });
        }
    }"
        @open-delete-category-modal.window="open = true; categoryId = $event.detail.id; categoryName = $event.detail.name"
        @keydown.escape.window="open = false" x-cloak>

        {{-- Backdrop --}}
        <div x-show="open" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm"
            @click="open = false">
        </div>

        {{-- Modal Panel --}}
        <div x-show="open" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">

            <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl shadow-slate-900/20 overflow-hidden"
                @click.away="open = false">

                {{-- Modal Header --}}
                <div class="relative bg-gradient-to-r from-rose-500 to-rose-600 px-6 py-5 text-white">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="relative flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 shadow-inner shadow-black/10">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">Delete Category</h3>
                            <p class="text-sm text-rose-100">This action cannot be undone</p>
                        </div>
                    </div>

                    <button @click="open = false"
                        class="absolute right-4 top-4 rounded-xl p-1.5 text-white/70 hover:bg-white/10 hover:text-white transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="px-6 py-5">
                    <p class="text-sm text-slate-600">
                        Are you sure you want to delete the category
                        <span class="font-semibold text-slate-900" x-text="categoryName"></span>?
                    </p>

                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                            </div>
                            <div class="text-xs text-amber-800">
                                <p class="font-semibold">Warning</p>
                                <p class="mt-0.5">
                                    If this category has sub-categories or products, consider deactivating instead of
                                    deleting.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                    <button @click="open = false"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition">
                        Cancel
                    </button>

                    <form method="POST" :action="`{{ url('/admin/categories') }}/${categoryId}/delete`">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-rose-500 to-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-rose-500/30 hover:from-rose-600 hover:to-rose-700 hover:shadow-lg transition-all">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0115.916 21H8.084A2.25 2.25 0 015.84 19.673L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 0 00-7.5 0" />
                            </svg>
                            Delete Category
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
