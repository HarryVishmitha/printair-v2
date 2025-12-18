@php
    $wgId = $selectedWorkingGroup?->id;
@endphp

<div class="grid gap-5 lg:grid-cols-2">
    <section x-data="{ open: true }" class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-5 py-4 sm:px-6 bg-white">
            <div>
                <div class="text-base font-bold text-slate-900">Public Base Pricing</div>
                <div class="text-xs text-slate-500">Fallback pricing used when WG override is not active.</div>
            </div>
            <span class="text-slate-400" x-text="open ? '−' : '+'"></span>
        </button>

        <div x-show="open" x-transition class="border-t border-slate-100 p-5 sm:p-6 space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Base price</label>
                    <input type="number" step="0.01" min="0"
                        data-field="public.base_price"
                        value="{{ $publicPricing?->base_price ?? '' }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm" />
                    <p class="mt-1 text-[11px] text-rose-600 hidden" data-error="public.base_price"></p>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Minimum charge</label>
                    <input type="number" step="0.01" min="0"
                        data-field="public.min_charge"
                        value="{{ $publicPricing?->min_charge ?? '' }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm" />
                    <p class="mt-1 text-[11px] text-rose-600 hidden" data-error="public.min_charge"></p>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Rate per sqft</label>
                    <input type="number" step="0.01" min="0"
                        data-field="public.rate_per_sqft"
                        value="{{ $publicPricing?->rate_per_sqft ?? '' }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm" />
                    <p class="mt-1 text-[11px] text-rose-600 hidden" data-error="public.rate_per_sqft"></p>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Offcut rate per sqft</label>
                    <input type="number" step="0.01" min="0"
                        data-field="public.offcut_rate_per_sqft"
                        value="{{ $publicPricing?->offcut_rate_per_sqft ?? '' }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm" />
                    <p class="mt-1 text-[11px] text-rose-600 hidden" data-error="public.offcut_rate_per_sqft"></p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <button type="button" data-ajax-save="base-public"
                    class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                    Save Public
                </button>
            </div>
        </div>
    </section>

    <section x-data="{ open: true }" class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-5 py-4 sm:px-6 bg-white">
            <div>
                <div class="text-base font-bold text-slate-900">Working Group Base Pricing</div>
                <div class="text-xs text-slate-500">
                    {{ $selectedWorkingGroup ? 'Overrides applied for this working group.' : 'Select a working group to edit.' }}
                </div>
            </div>
            <span class="text-slate-400" x-text="open ? '−' : '+'"></span>
        </button>

        <div x-show="open" x-transition class="border-t border-slate-100 p-5 sm:p-6 space-y-4">
            @if (! $selectedWorkingGroup)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Select a working group to edit WG pricing.
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Base price</label>
                        <input type="number" step="0.01" min="0"
                            data-field="wg.base_price"
                            value="{{ $wgPricing?->base_price ?? '' }}"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm" />
                        <p class="mt-1 text-[11px] text-rose-600 hidden" data-error="wg.base_price"></p>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Minimum charge</label>
                        <input type="number" step="0.01" min="0"
                            data-field="wg.min_charge"
                            value="{{ $wgPricing?->min_charge ?? '' }}"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm" />
                        <p class="mt-1 text-[11px] text-rose-600 hidden" data-error="wg.min_charge"></p>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Rate per sqft</label>
                        <input type="number" step="0.01" min="0"
                            data-field="wg.rate_per_sqft"
                            value="{{ $wgPricing?->rate_per_sqft ?? '' }}"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm" />
                        <p class="mt-1 text-[11px] text-rose-600 hidden" data-error="wg.rate_per_sqft"></p>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Offcut rate per sqft</label>
                        <input type="number" step="0.01" min="0"
                            data-field="wg.offcut_rate_per_sqft"
                            value="{{ $wgPricing?->offcut_rate_per_sqft ?? '' }}"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm" />
                        <p class="mt-1 text-[11px] text-rose-600 hidden" data-error="wg.offcut_rate_per_sqft"></p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400 mb-3">Override switches</div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" data-field="wg.override_base" {{ ($wgPricing?->override_base ?? false) ? 'checked' : '' }}>
                            Base
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" data-field="wg.override_variants" {{ ($wgPricing?->override_variants ?? false) ? 'checked' : '' }}>
                            Variants
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" data-field="wg.override_finishings" {{ ($wgPricing?->override_finishings ?? false) ? 'checked' : '' }}>
                            Finishings
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button type="button" data-ajax-save="base-wg"
                        class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                        Save WG
                    </button>
                </div>
            @endif
        </div>
    </section>
</div>

