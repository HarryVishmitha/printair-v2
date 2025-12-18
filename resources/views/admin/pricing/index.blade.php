<x-app-layout>
    <x-slot name="sectionTitle">Pricing</x-slot>
    <x-slot name="pageTitle">Pricing Hub</x-slot>

    <x-slot name="breadcrumbs">
        <span class="text-slate-500">Admin</span>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">Pricing</span>
    </x-slot>

    <div class="space-y-6" data-pricing-hub>
        {{-- Toasts (same pattern as your Rolls page) --}}
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

        @php
            $wgId = (int) ($filters['working_group_id'] ?? 0);
            $search = $filters['search'] ?? '';
            $sort = $filters['sort'] ?? 'updated_desc';
            $status = $filters['status'] ?? '';
        @endphp

        {{-- HERO BAND --}}
        <section
            class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-gradient-to-r from-[#ff4b5c] via-[#ff7a45] to-[#ffb347] px-5 py-5 sm:px-7 sm:py-6 text-white shadow-lg shadow-[#ff4b5c]/20">
            <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl">
            </div>
            <div class="pointer-events-none absolute -bottom-16 right-10 h-56 w-56 rounded-full border border-white/15">
            </div>

            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2 max-w-2xl">
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium tracking-wide backdrop-blur">
                        <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        Pricing Hub · Printair v2
                    </div>

                    <div class="flex items-center gap-3 pt-1">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 shadow-inner shadow-black/10">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold leading-tight">Manage Product Pricing by Working
                                Group</h2>
                            <p class="text-xs sm:text-sm text-white/80">
                                Switch working groups, quickly hide/show products, and enable WG pricing overrides —
                                then click a card to manage full pricing.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <div class="inline-flex items-center gap-2 rounded-full bg-black/10 px-3 py-1 text-xs">
                            <span
                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-black/15 text-[11px] font-bold">
                                {{ $products->total() }}
                            </span>
                            <span class="text-white/90">Products</span>
                        </div>
                        @if ($selectedWorkingGroup)
                            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                                <span>Selected: {{ $selectedWorkingGroup->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-end gap-3 pt-2 lg:pt-0">
                    <a href="{{ route('admin.products.index') ?? '#' }}"
                        class="group inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-[#ff4b5c] shadow-md shadow-black/10 hover:shadow-lg hover:-translate-y-0.5 transition-all">
                        <svg class="h-4 w-4 text-[#ff4b5c]" fill="none" viewBox="0 0 24 24" stroke-width="2.4"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        Products
                    </a>
                </div>
            </div>
        </section>

        {{-- FILTER STRIP --}}
        <section class="rounded-3xl border border-slate-200/80 bg-white px-5 py-5 shadow-sm sm:px-6">
            <form method="GET" action="{{ route('admin.pricing.index') }}"
                class="grid gap-4 md:grid-cols-2 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)] md:items-end">

                {{-- Search --}}
                <div>
                    <label for="search"
                        class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                        Search products
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
                            placeholder="Search by name / slug…"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 pl-9 pr-9 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20" />

                        @if ($search !== '')
                            <a href="{{ route('admin.pricing.index', array_filter(['working_group_id' => $wgId ?: null])) }}"
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

                {{-- WG + Sort/Status --}}
                <div>
                    <div class="mb-3">
                        <label for="working_group_id"
                            class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Working Group
                        </label>
                        <select id="working_group_id" name="working_group_id"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                            <option value="">Public (default)</option>
                            @foreach ($workingGroups as $wg)
                                <option value="{{ $wg->id }}" @selected((int) $wgId === (int) $wg->id)>{{ $wg->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="sort"
                                class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Sort
                            </label>
                            <select id="sort" name="sort"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                                <option value="updated_desc" @selected($sort === 'updated_desc')>Recently updated</option>
                                <option value="name_asc" @selected($sort === 'name_asc')>Name A → Z</option>
                                <option value="name_desc" @selected($sort === 'name_desc')>Name Z → A</option>
                            </select>
                        </div>

                        <div>
                            <label for="status"
                                class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Status
                            </label>
                            <select id="status" name="status"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                                <option value="">All</option>
                                <option value="active" @selected($status === 'active')>Active</option>
                                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-end">
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

                        <a href="{{ route('admin.pricing.index') }}"
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

        {{-- CARD GRID --}}
        <section class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            @php
                $noFilters =
                    $search === '' && ($status === '' || $status === null) && $sort === 'updated_desc' && $wgId === 0;
            @endphp

            @if ($products->count() === 0 && $noFilters)
                <div class="px-6 py-16 text-center">
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-50 text-slate-400 shadow-inner">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900">No products yet</h3>
                    <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto">
                        Create products first, then come back here to configure pricing by working group.
                    </p>
                </div>
            @elseif ($products->count() === 0)
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
                        Try a different keyword or <a href="{{ route('admin.pricing.index') }}"
                            class="font-medium text-[#ff4b5c] hover:text-[#ff2a3c]">clear filters</a>.
                    </p>
                </div>
            @else
                <div class="p-5 sm:p-6">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($cards as $card)
                            @php
                                $imageUrl = $card['image'] ? asset('storage/' . $card['image']) : null;
                            @endphp
                            <div
                                class="group rounded-3xl border border-slate-200/80 bg-white shadow-sm hover:shadow-md transition overflow-hidden">
                                <a href="{{ $card['manage_url'] }}"
                                    class="block focus:outline-none focus:ring-2 focus:ring-[#ff4b5c]/30 rounded-3xl">
                                    <div class="relative aspect-[16/10] bg-slate-50 overflow-hidden">
                                        @if ($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $card['name'] }}"
                                                class="h-full w-full object-cover group-hover:scale-[1.02] transition-transform duration-300" />
                                        @else
                                            <div class="h-full w-full flex items-center justify-center text-slate-400">
                                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.6" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M3 7h18M3 12h18M3 17h18" />
                                                </svg>
                                            </div>
                                        @endif

                                        <div
                                            class="absolute left-3 top-3 inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1 text-[11px] font-semibold text-slate-700 shadow-sm">
                                            <span
                                                class="h-1.5 w-1.5 rounded-full {{ $card['selected_wg_is_visible'] ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            {{ $card['selected_wg_is_visible'] ? 'Visible' : 'Hidden' }}
                                        </div>
                                    </div>

                                    <div class="p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <h3 class="text-sm font-semibold text-slate-900 truncate">
                                                    {{ $card['name'] }}</h3>
                                                <p class="mt-1 text-xs text-slate-500">
                                                    Public:
                                                    <span class="font-semibold text-slate-700">
                                                        {{ $card['public_price_label'] ?? '—' }}
                                                    </span>
                                                </p>
                                            </div>

                                            <div class="text-right">
                                                <div class="text-[11px] uppercase tracking-wide text-slate-400">
                                                    Selected WG</div>
                                                <div class="text-base font-extrabold text-slate-900">
                                                    {{ $card['selected_wg_price_label'] ?? '—' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>

                                {{-- Actions strip --}}
                                <div class="border-t border-slate-100 bg-slate-50/60 p-4">
                                    @if ($selectedWorkingGroup)
                                        <div class="grid grid-cols-2 gap-3">
                                            <form method="POST"
                                                action="{{ route('admin.pricing.products.wg.visibility', ['product' => $card['id'], 'workingGroup' => $selectedWorkingGroup->id]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="is_visible"
                                                    value="{{ $card['selected_wg_is_visible'] ? 0 : 1 }}">

                                                <button type="submit"
                                                    data-ajax-toggle="1"
                                                    data-url="{{ route('admin.pricing.products.wg.visibility', ['product' => $card['id'], 'workingGroup' => $selectedWorkingGroup->id]) }}"
                                                    data-method="PATCH"
                                                    data-payload='@json(["is_visible" => $card["selected_wg_is_visible"] ? 0 : 1])'
                                                    class="w-full inline-flex items-center justify-center gap-2 rounded-2xl border px-3 py-2 text-xs font-semibold shadow-sm transition
                                                        {{ $card['selected_wg_is_visible'] ? 'border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M12 4.5c7 0 10.5 7.5 10.5 7.5S19 19.5 12 19.5 1.5 12 1.5 12 5 4.5 12 4.5z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                    {{ $card['selected_wg_is_visible'] ? 'Hide' : 'Show' }}
                                                </button>
                                            </form>

                                            <form method="POST"
                                                action="{{ route('admin.pricing.products.wg.override', ['product' => $card['id'], 'workingGroup' => $selectedWorkingGroup->id]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="is_enabled"
                                                    value="{{ $card['selected_wg_override_active'] ? 0 : 1 }}">

                                                <button type="submit"
                                                    data-ajax-toggle="1"
                                                    data-url="{{ route('admin.pricing.products.wg.override', ['product' => $card['id'], 'workingGroup' => $selectedWorkingGroup->id]) }}"
                                                    data-method="PATCH"
                                                    data-payload='@json(["is_enabled" => $card["selected_wg_override_active"] ? 0 : 1])'
                                                    class="w-full inline-flex items-center justify-center gap-2 rounded-2xl border px-3 py-2 text-xs font-semibold shadow-sm transition
                                                        {{ $card['selected_wg_override_active'] ? 'border-[#ff4b5c]/30 bg-white text-[#ff4b5c] hover:bg-[#ff4b5c]/5' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                                                    </svg>
                                                    {{ $card['selected_wg_override_active'] ? 'Override Off' : 'Override On' }}
                                                </button>
                                            </form>
                                        </div>

                                        <p class="mt-3 text-[11px] text-slate-500">
                                            Override affects pricing only. Visibility affects whether the product appears for this WG.
                                        </p>
                                    @else
                                        <div class="grid grid-cols-2 gap-3">
                                            <button disabled
                                                class="w-full inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-400 shadow-sm cursor-not-allowed">
                                                Hide/Show
                                            </button>
                                            <button disabled
                                                class="w-full inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-400 shadow-sm cursor-not-allowed">
                                                Override
                                            </button>
                                        </div>
                                        <p class="mt-3 text-[11px] text-slate-500">
                                            Select a working group to use visibility + override controls.
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Pagination footer --}}
                <div
                    class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50/60 px-5 py-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        Showing
                        <span class="font-semibold text-slate-700">{{ $products->firstItem() }}</span>
                        to
                        <span class="font-semibold text-slate-700">{{ $products->lastItem() }}</span>
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
