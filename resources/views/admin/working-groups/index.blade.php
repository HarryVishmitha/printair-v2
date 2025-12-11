<x-app-layout>
    {{-- Top bar titles --}}
    <x-slot name="sectionTitle">Settings</x-slot>
    <x-slot name="pageTitle">Working Groups</x-slot>

    {{-- Breadcrumbs --}}
    <x-slot name="breadcrumbs">
        <span class="text-slate-500">Settings</span>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">Working Groups</span>
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

        {{-- PRINTAIR HERO BAND --}}
        <section
            class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-gradient-to-r from-[#ff4b5c] via-[#ff7a45] to-[#ffb347] px-5 py-5 sm:px-7 sm:py-6 text-white shadow-lg shadow-[#ff4b5c]/20">
            {{-- subtle pattern --}}
            <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl">
            </div>
            <div class="pointer-events-none absolute -bottom-16 right-10 h-56 w-56 rounded-full border border-white/15">
            </div>

            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2 max-w-xl">
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium tracking-wide backdrop-blur">
                        <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        Organisation Layer · Printair v2
                    </div>
                    <div class="flex items-center gap-3 pt-1">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 shadow-inner shadow-black/10">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198.001.031A11.944 11.944 0 0112 21a11.944 11.944 0 01-5.999-1.55L6 18.72m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772M9 7.5a3 3 0 116 0 3 3 0 01-6 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold leading-tight">
                                Working Groups for Printair Teams
                            </h2>
                            <p class="text-xs sm:text-sm text-white/80">
                                Define how orders, quotations and design collections are segmented across Public,
                                Staff and project-based groups.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <div class="inline-flex items-center gap-2 rounded-full bg-black/10 px-3 py-1 text-xs">
                            <span
                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-black/15 text-[11px] font-bold">
                                {{ $workingGroups->total() }}
                            </span>
                            <span class="text-white/90">Total groups in system</span>
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                            <span>Default group: <span class="font-semibold">Public</span></span>
                        </div>
                    </div>
                </div>

                <div class="flex items-end gap-3 pt-2 lg:pt-0">
                    <a href="{{ route('admin.working-groups.create') }}"
                        class="group inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-[#ff4b5c] shadow-md shadow-black/10 hover:shadow-lg hover:-translate-y-0.5 transition-all">
                        <svg class="h-4 w-4 text-[#ff4b5c] group-hover:rotate-90 transition-transform" fill="none"
                            viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        New Working Group
                    </a>
                </div>
            </div>
        </section>

        {{-- SEARCH & QUICK FILTER STRIP --}}
        <section class="rounded-3xl border border-slate-200/80 bg-white px-5 py-5 shadow-sm sm:px-6">
            <form method="GET" action="{{ route('admin.working-groups.index') }}"
                class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div class="flex-1 max-w-xl">
                    <label for="search"
                        class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                        Search groups
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
                            placeholder="Search by name, slug, description or tag…"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 pl-9 pr-10 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20" />
                        @if ($search !== '')
                            <a href="{{ route('admin.working-groups.index') }}"
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

                    <a href="{{ route('admin.working-groups.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.5 19.5l15-15m0 0H9.75m9.75 0v9.75" />
                        </svg>
                        Reset
                    </a>
                </div>
            </form>
        </section>

        {{-- MAIN TABLE CARD --}}
        <section class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            @if ($workingGroups->count() === 0 && $search === '')
                {{-- Empty state --}}
                <div class="px-6 py-16 text-center">
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-50 text-slate-400 shadow-inner">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72M6 18.72a9.094 9.094 0 01-3.741-.479 3 3 0 014.682-2.72M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900">No working groups yet</h3>
                    <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto">
                        Create your first group to separate Public clients, staff logins or special projects in
                        the Printair dashboard.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('admin.working-groups.create') }}"
                            class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#ff4b5c] to-[#ff7a45] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#ff4b5c]/30 hover:shadow-lg hover:-translate-y-0.5 transition-all">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.4"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Create your first group
                        </a>
                    </div>
                </div>
            @elseif ($workingGroups->count() === 0 && $search !== '')
                {{-- Empty search --}}
                <div class="px-6 py-12 text-center">
                    <div
                        class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.4 11.4z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-900">No groups match your search</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Try a different keyword or
                        <a href="{{ route('admin.working-groups.index') }}"
                            class="font-medium text-[#ff4b5c] hover:text-[#ff2a3c]">
                            clear filters
                        </a>
                        to see all working groups.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50/70">
                            <tr>
                                <th
                                    class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Group
                                </th>
                                <th
                                    class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Slug
                                </th>
                                <th
                                    class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Description
                                </th>
                                <th
                                    class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Properties
                                </th>
                                <th
                                    class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($workingGroups as $group)
                                @php
                                    $isPublic = $group->slug === \App\Models\WorkingGroup::PUBLIC_SLUG;
                                @endphp
                                <tr class="group hover:bg-sky-50/40 transition-colors">
                                    {{-- GROUP INFO --}}
                                    <td class="px-6 py-4 align-top">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl
                                            @if ($isPublic) bg-emerald-100 text-emerald-600
                                            @elseif($group->is_staff_group)
                                                bg-amber-100 text-amber-700
                                            @else
                                                bg-slate-100 text-slate-500 @endif">
                                                @if ($isPublic)
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M12 6.75a3 3 0 11-3 3m6 0a3 3 0 11-3-3m0 0V4.5m0 5.25v10.125" />
                                                    </svg>
                                                @elseif($group->is_staff_group)
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.118a7.5 7.5 0 0115 0A19 19 0 0112 21.75a19 19 0 01-7.5-1.632z" />
                                                    </svg>
                                                @else
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M3.75 5.25h16.5v4.5H3.75zM3.75 14.25h16.5v4.5H3.75z" />
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-sm font-semibold text-slate-900">
                                                        {{ $group->name }}
                                                    </span>
                                                    @if ($isPublic)
                                                        <span
                                                            class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-700 ring-1 ring-emerald-200">
                                                            <span
                                                                class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                            Default
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="mt-1 text-[11px] text-slate-400">
                                                    Created {{ $group->created_at?->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- SLUG --}}
                                    <td class="px-4 py-4 align-top whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center rounded-xl bg-slate-100 px-2.5 py-1 font-mono text-[11px] text-slate-700">
                                            {{ $group->slug }}
                                        </span>
                                    </td>

                                    {{-- DESCRIPTION --}}
                                    <td class="px-4 py-4 align-top">
                                        <p class="text-xs sm:text-sm text-slate-600 line-clamp-2">
                                            {{ $group->description ?: '—' }}
                                        </p>
                                    </td>

                                    {{-- FLAGS --}}
                                    <td class="px-4 py-4 align-top">
                                        <div class="flex flex-wrap gap-1.5">
                                            @if ($group->is_shareable)
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-xl bg-sky-50 px-2 py-1 text-[11px] font-medium text-sky-700 ring-1 ring-sky-100">
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M7.5 13.5L3 9l4.5-4.5M16.5 10.5L21 15l-4.5 4.5M8.25 9h7.5M15.75 15h-7.5" />
                                                    </svg>
                                                    Shareable
                                                </span>
                                            @endif

                                            @if ($group->is_restricted)
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-xl bg-rose-50 px-2 py-1 text-[11px] font-medium text-rose-700 ring-1 ring-rose-100">
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5A2.25 2.25 0 0019.5 19.5v-6.75A2.25 2.25 0 0017.25 10.5H6.75A2.25 2.25 0 004.5 12.75v6.75A2.25 2.25 0 006.75 21.75z" />
                                                    </svg>
                                                    Restricted
                                                </span>
                                            @endif

                                            @if ($group->is_staff_group)
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-xl bg-amber-50 px-2 py-1 text-[11px] font-medium text-amber-700 ring-1 ring-amber-100">
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.118a7.5 7.5 0 0115 0A19 19 0 0112 21.75a19 19 0 01-7.5-1.632z" />
                                                    </svg>
                                                    Staff
                                                </span>
                                            @endif

                                            @if (!$group->is_shareable && !$group->is_restricted && !$group->is_staff_group)
                                                <span class="text-[11px] text-slate-400 italic">
                                                    No special flags
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- ACTIONS --}}
                                    <td class="px-6 py-4 align-top text-right whitespace-nowrap">
                                        <div
                                            class="inline-flex items-center gap-2 opacity-70 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('admin.working-groups.edit', $group) }}"
                                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                                </svg>
                                                Edit
                                            </a>

                                            @unless ($isPublic)
                                                <form method="POST"
                                                    action="{{ route('admin.working-groups.destroy', $group) }}"
                                                    onsubmit="return confirm('Delete this working group? This cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-white px-3 py-1.5 text-[11px] font-medium text-rose-600 shadow-sm hover:bg-rose-50 hover:border-rose-300">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0115.916 21H8.084A2.25 2.25 0 015.84 19.673L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                        </svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            @endunless
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
                        <span class="font-semibold text-slate-700">{{ $workingGroups->firstItem() }}</span>
                        to
                        <span class="font-semibold text-slate-700">{{ $workingGroups->lastItem() }}</span>
                        of
                        <span class="font-semibold text-slate-700">{{ $workingGroups->total() }}</span>
                        groups
                    </div>
                    <div class="sm:ml-auto">
                        {{ $workingGroups->onEachSide(1)->links() }}
                    </div>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
