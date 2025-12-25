{{-- resources/views/public/estimate-share/unavailable.blade.php --}}

<x-guest-layout :seo="$seo ?? []">
    <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm text-center">
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.008v.008H12V15.75zm9-.75a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="text-lg font-extrabold text-slate-900">Link unavailable</h1>
        <p class="mt-2 text-sm text-slate-600">This estimate link has expired or was revoked.</p>
        <a href="{{ route('home') }}" class="mt-6 inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
            Back to Home
        </a>
    </div>
</x-guest-layout>

