{{-- resources/views/admin/categories/create.blade.php --}}

<x-app-layout>
    <x-slot name="sectionTitle">Categories</x-slot>
    <x-slot name="pageTitle">New Category</x-slot>

    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.categories.index') }}" class="text-slate-500 hover:text-slate-700">Categories</a>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">Create</span>
    </x-slot>

    <div class="space-y-6">
        {{-- Global feedback (backend errors / success) --}}
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

        @if ($errors->any())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
                <div class="flex gap-2">
                    <div class="mt-0.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m0 3h.008v.008H12V15.75zm9-.75a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold">Please fix the errors below before continuing.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Header Card --}}
        <section
            class="rounded-3xl border border-slate-200/80 bg-slate-50/70 px-5 py-4 sm:px-6 shadow-sm flex items-start gap-3">
            <div
                class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-[#ff4b5c] to-[#ff7a45] text-white shadow-md shadow-[#ff4b5c]/30">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 7.5h6m-6 4.5h18M3 16.5h10m7.5-9.75h.008v.008H20.5V6.75z" />
                </svg>
            </div>
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-slate-900">
                    Create New Category
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 max-w-2xl">
                    Use categories to group products, control navbar visibility, and keep your catalog structure
                    scalable for future pricing and filtering modules.
                </p>
            </div>
        </section>

        {{-- Form Card --}}
        <section class="rounded-3xl border border-slate-200/80 bg-white px-5 py-6 sm:px-6 shadow-sm">
            @include('admin.categories._form', ['category' => null, 'parents' => $parents ?? collect()])
        </section>
    </div>
</x-app-layout>
