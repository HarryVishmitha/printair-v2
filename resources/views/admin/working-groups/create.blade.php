<x-app-layout>
    <x-slot name="sectionTitle">Settings</x-slot>
    <x-slot name="pageTitle">New Working Group</x-slot>

    <x-slot name="breadcrumbs">
        <span class="text-slate-500">Settings</span>
        <span class="mx-1">/</span>
        <a href="{{ route('admin.working-groups.index') }}" class="text-sky-600 hover:underline">
            Working Groups
        </a>
        <span class="mx-1">/</span>
        <span class="text-slate-900 font-medium">Create</span>
    </x-slot>

    <div class="space-y-6">
        {{-- Header band (Printair v2 style) --}}
        <div
            class="flex flex-col gap-4 rounded-3xl border border-slate-200/80 bg-gradient-to-r from-[#ff4b5c] via-[#ff7a45] to-[#ffb347] px-5 py-5 text-white shadow-lg shadow-[#ff4b5c]/20 sm:flex-row sm:items-center sm:justify-between sm:px-7 sm:py-6">
            <div class="flex items-start gap-4">
                <div
                    class="mt-0.5 flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-white/15 shadow-inner shadow-black/10">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h2 class="text-xl font-bold leading-tight tracking-tight">
                        Create a new working group
                    </h2>
                    <p class="text-xs sm:text-sm text-white/85">
                        Use groups to separate Public clients, staff accounts and special projects across orders,
                        quotations and design collections in Printair.
                    </p>
                    <div class="mt-2 inline-flex items-center gap-2 rounded-full bg-black/10 px-3 py-1 text-xs">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        <span>Create once, then reuse everywhere in the dashboard.</span>
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
            <form method="POST" action="{{ route('admin.working-groups.store') }}" class="space-y-6">
                @csrf

                {{-- shared fields --}}
                @include('admin.working-groups._form', ['workingGroup' => null])

                <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
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
                        Save working group
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
