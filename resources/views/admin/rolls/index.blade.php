<x-app-layout>
    {{-- Top bar titles --}}
    <x-slot name="sectionTitle">Settings</x-slot>
    <x-slot name="pageTitle">Rolls</x-slot>

    {{-- Breadcrumbs --}}
    <x-slot name="breadcrumbs">
        <span class="text-slate-500">Settings</span>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">Rolls</span>
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
        @php
            /** @var \Illuminate\Pagination\LengthAwarePaginator $rolls */
            $totalRolls = $rolls->total();
            $activeRolls = $rolls->count() ? $rolls->where('is_active', true)->count() : 0;

            $q = $filters['q'] ?? '';
            $material = $filters['material_type'] ?? '';
            $status = $filters['status'] ?? '';
        @endphp

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
                        Roll Library · Printair v2
                    </div>

                    <div class="flex items-center gap-3 pt-1">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 shadow-inner shadow-black/10">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4.5 8.25h15m-15 4.5h15M9.75 4.5v15" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold leading-tight">Roll Management for Printair</h2>
                            <p class="text-xs sm:text-sm text-white/80">
                                Define reusable rolls (flex, sticker, vinyl, banners) that products and pricing can
                                attach to.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <div class="inline-flex items-center gap-2 rounded-full bg-black/10 px-3 py-1 text-xs">
                            <span
                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-black/15 text-[11px] font-bold">
                                {{ $totalRolls }}
                            </span>
                            <span class="text-white/90">Total rolls</span>
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                            <span>{{ $activeRolls }} active rolls</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-end gap-3 pt-2 lg:pt-0">
                    @can('create', \App\Models\Roll::class)
                        <a href="{{ route('admin.rolls.create') }}"
                            class="group inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-[#ff4b5c] shadow-md shadow-black/10 hover:shadow-lg hover:-translate-y-0.5 transition-all">
                            <svg class="h-4 w-4 text-[#ff4b5c] group-hover:rotate-90 transition-transform" fill="none"
                                viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            New Roll
                        </a>
                    @endcan
                </div>
            </div>
        </section>

        {{-- FILTER STRIP --}}
        <section class="rounded-3xl border border-slate-200/80 bg-white px-5 py-5 shadow-sm sm:px-6">
            <form method="GET" action="{{ route('admin.rolls.index') }}"
                class="grid gap-4 md:grid-cols-2 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)] md:items-end">

                {{-- Search --}}
                <div>
                    <label for="search"
                        class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                        Search rolls
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

                        <input type="text" id="search" name="q" value="{{ $q }}"
                            placeholder="Search by name, slug or material…"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 pl-9 pr-9 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20" />

                        @if ($q !== '')
                            <a href="{{ route('admin.rolls.index') }}"
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

                {{-- Material + Status --}}
                <div>
                    <div class="mb-3">
                        <label for="material_type"
                            class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Material Type
                        </label>
                        <select id="material_type" name="material_type"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                            <option value="">All materials</option>
                            @foreach ($materialTypes as $mt)
                                <option value="{{ $mt }}" @selected($material === $mt)>{{ $mt }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status"
                            class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Status
                        </label>
                        <select id="status" name="status"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                            <option value="">All statuses</option>
                            <option value="active" @selected($status === 'active')>Active</option>
                            <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                        </select>
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
                            Apply filters
                        </button>

                        <a href="{{ route('admin.rolls.index') }}"
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
            @php
                $noFilters = $q === '' && $material === '' && ($status === '' || $status === null);
            @endphp

            @if ($rolls->count() === 0 && $noFilters)
                {{-- Empty state (no rolls at all) --}}
                <div class="px-6 py-16 text-center">
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-50 text-slate-400 shadow-inner">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.5 8.25h15m-15 4.5h15M9.75 4.5v15" />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900">No rolls yet</h3>
                    <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto">
                        Create your first roll to start managing dimension-based products and pricing.
                    </p>
                    <div class="mt-6">
                        @can('create', \App\Models\Roll::class)
                            <a href="{{ route('admin.rolls.create') }}"
                                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#ff4b5c] to-[#ff7a45] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#ff4b5c]/30 hover:shadow-lg hover:-translate-y-0.5 transition-all">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.4"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Create your first roll
                            </a>
                        @endcan
                    </div>
                </div>
            @elseif ($rolls->count() === 0)
                {{-- Empty search/filters --}}
                <div class="px-6 py-12 text-center">
                    <div
                        class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.4 11.4z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-900">No rolls match your filters</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Try a different keyword or
                        <a href="{{ route('admin.rolls.index') }}"
                            class="font-medium text-[#ff4b5c] hover:text-[#ff2a3c]">
                            clear filters
                        </a>
                        to see all rolls.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50/70">
                            <tr>
                                <th
                                    class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Roll</th>
                                <th
                                    class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Material</th>
                                <th
                                    class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Width (in)</th>
                                <th
                                    class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Width (ft)</th>
                                <th
                                    class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Status</th>
                                <th
                                    class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($rolls as $roll)
                                <tr class="group hover:bg-sky-50/40 transition-colors">
                                    <td class="px-6 py-4 align-top">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#ff4b5c] to-[#ff7a45] text-white shadow-sm">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M4.5 8.25h15m-15 4.5h15M9.75 4.5v15" />
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-sm font-semibold text-slate-900">{{ $roll->name }}
                                                </div>
                                                <p class="mt-0.5 text-xs text-slate-500">{{ $roll->slug }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 align-top">
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            {{ $roll->material_type }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 align-top">
                                        <span
                                            class="font-mono text-sm text-slate-700">{{ number_format((float) $roll->width_in, 3) }}</span>
                                        <span class="ml-1 text-xs text-slate-400">in</span>
                                    </td>

                                    <td class="px-4 py-4 align-top">
                                        <span
                                            class="font-mono text-sm text-slate-700">{{ number_format(((float) $roll->width_in) / 12, 2) }}</span>
                                        <span class="ml-1 text-xs text-slate-400">ft</span>
                                    </td>

                                    <td class="px-4 py-4 align-top">
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $roll->is_active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-200' }}">
                                            <span
                                                class="h-1.5 w-1.5 rounded-full {{ $roll->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            {{ $roll->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right align-top whitespace-nowrap">
                                        <div
                                            class="inline-flex items-center gap-2 opacity-70 group-hover:opacity-100 transition-opacity">
                                            @can('update', $roll)
                                                <a href="{{ route('admin.rolls.edit', $roll) }}"
                                                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                                    </svg>
                                                    Edit
                                                </a>
                                            @endcan

                                            @can('delete', $roll)
                                                <button type="button" x-data
                                                    @click="$dispatch('open-delete-roll-modal', { id: {{ $roll->id }}, name: '{{ addslashes($roll->name) }}' })"
                                                    class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-white px-3 py-1.5 text-[11px] font-medium text-rose-600 shadow-sm hover:bg-rose-50 hover:border-rose-300">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0115.916 21H8.084A2.25 2.25 0 015.84 19.673L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                    Delete
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- FOOTER / PAGINATION (same pattern as Users page) --}}
                <div
                    class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50/60 px-5 py-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        Showing
                        <span class="font-semibold text-slate-700">{{ $rolls->firstItem() }}</span>
                        to
                        <span class="font-semibold text-slate-700">{{ $rolls->lastItem() }}</span>
                        of
                        <span class="font-semibold text-slate-700">{{ $rolls->total() }}</span>
                        rolls
                    </div>
                    <div class="sm:ml-auto">
                        {{ $rolls->onEachSide(1)->links() }}
                    </div>
                </div>
            @endif
        </section>
    </div>

    {{-- DELETE CONFIRMATION MODAL --}}
    <div x-data="{
        open: false,
        rollId: null,
        rollName: '',
        init() {
            this.$watch('open', value => document.body.classList.toggle('overflow-hidden', value));
        }
    }"
        @open-delete-roll-modal.window="open = true; rollId = $event.detail.id; rollName = $event.detail.name"
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

                {{-- Header --}}
                <div class="relative bg-gradient-to-r from-amber-500 to-rose-500 px-6 py-5 text-white">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="relative flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 shadow-inner shadow-black/10">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0115.916 21H8.084A2.25 2.25 0 015.84 19.673L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">Delete Roll</h3>
                            <p class="text-sm text-amber-50">This roll will be removed from the library.</p>
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

                {{-- Body --}}
                <div class="px-6 py-5">
                    <p class="text-sm text-slate-600">
                        Are you sure you want to delete roll
                        <span class="font-semibold text-slate-900" x-text="rollName"></span>?
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
                                <p class="font-semibold">Note</p>
                                <p class="mt-0.5">
                                    If this roll is linked to products or pricing, deletion may be blocked by safety
                                    rules in the system.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                    <button @click="open = false"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition">
                        Cancel
                    </button>
                    <form method="POST" :action="`rolls/${rollId}/delete`">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-rose-500/30 hover:shadow-lg transition-all">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0115.916 21H8.084A2.25 2.25 0 015.84 19.673L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Delete roll
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

