@php
    /** @var \App\Models\WorkingGroup $workingGroup */
    $isPublic = $workingGroup->slug === \App\Models\WorkingGroup::PUBLIC_SLUG;
@endphp

<x-app-layout>
    <x-slot name="sectionTitle">Settings</x-slot>
    <x-slot name="pageTitle">Edit Working Group</x-slot>

    <x-slot name="breadcrumbs">
        <span class="text-slate-500">Settings</span>
        <span class="mx-1">/</span>
        <a href="{{ route('admin.working-groups.index') }}" class="text-sky-600 hover:underline">
            Working Groups
        </a>
        <span class="mx-1">/</span>
        <span class="text-slate-900 font-medium">Edit</span>
    </x-slot>

    <div class="space-y-6">
        {{-- Header band --}}
        <div
            class="flex flex-col gap-4 rounded-3xl border border-slate-200/80 bg-gradient-to-r from-[#ff4b5c] via-[#ff7a45] to-[#ffb347] px-5 py-5 text-white shadow-lg shadow-[#ff4b5c]/20 sm:flex-row sm:items-center sm:justify-between sm:px-7 sm:py-6">
            <div class="flex items-start gap-4">
                <div
                    class="mt-0.5 flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-white/15 shadow-inner shadow-black/10">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.862 4.487l1.688-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h2 class="text-xl font-bold leading-tight tracking-tight">
                        Edit working group: {{ $workingGroup->name }}
                    </h2>
                    <p class="text-xs sm:text-sm text-white/85">
                        Update how this group behaves before it’s used for orders, quotations and design
                        sharing inside Printair.
                    </p>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-1 rounded-full bg-black/15 px-2.5 py-0.5 font-medium">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                            Last updated: {{ $workingGroup->updated_at?->format('Y-m-d H:i') }}
                        </span>

                        <span class="inline-flex items-center gap-1 rounded-full bg-black/10 px-2.5 py-0.5 font-medium">
                            Created: {{ $workingGroup->created_at?->format('Y-m-d H:i') }}
                        </span>

                        @if ($isPublic)
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-emerald-50/20 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide text-emerald-100 ring-1 ring-emerald-200/40">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                                Default group
                            </span>
                        @elseif($workingGroup->is_staff_group)
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-amber-50/20 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide text-amber-100 ring-1 ring-amber-200/40">
                                Staff group
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:items-start">
                <a href="{{ route('admin.working-groups.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-2xl bg-white/10 px-4 py-2 text-xs font-medium text-white shadow-sm hover:bg-white/15">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                    Back to list
                </a>
            </div>
        </div>

        {{-- Form card --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white px-5 py-5 shadow-sm sm:px-6 sm:py-6">
            <form method="POST" action="{{ route('admin.working-groups.update', $workingGroup) }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- shared form fields --}}
                @include('admin.working-groups._form', ['workingGroup' => $workingGroup])

                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                    <div class="text-xs text-slate-400">
                        ID: <span class="font-mono">{{ $workingGroup->id }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.working-groups.index') }}"
                            class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50 hover:text-slate-900">
                            Cancel
                        </a>
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#ff4b5c] to-[#ff7a45] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#ff4b5c]/30 hover:shadow-lg hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#ff4b5c]/70 focus:ring-offset-2 transition-all">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 12.75l4.5 4.5 9-9" />
                            </svg>
                            Save changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
