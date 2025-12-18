@php
    // Roll overrides belong to base pricing => they follow override_base.
    $editingPricing = null;
    if ($wgPricing && ($wgPricing->override_base ?? false)) {
        $editingPricing = $wgPricing;
    } else {
        $editingPricing = $publicPricing ?: $wgPricing;
    }

    $context = ($editingPricing?->context === 'working_group') ? 'wg' : 'public';
    $activePricingId = $editingPricing?->id;
    $rows = $editingPricing?->rollPricings ?? collect();
    $rollLinks = $product->productRolls ?? collect();
    $activeRollLinks = $rollLinks->filter(fn ($r) => (bool) ($r->is_active ?? false));
@endphp

<section x-data="{ open: true }" class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
    <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-5 py-4 sm:px-6 bg-white">
        <div>
            <div class="text-base font-bold text-slate-900">Roll Overrides</div>
            <div class="text-xs text-slate-500">Optional per-roll rate/offcut/min override.</div>
        </div>
        <span class="text-slate-400" x-text="open ? '−' : '+'"></span>
    </button>

    <div x-show="open" x-transition class="border-t border-slate-100 p-5 sm:p-6 space-y-4">
        @if (! $publicPricing && ! $wgPricing)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Create base pricing first to manage roll overrides.
            </div>
        @else
            @if ($selectedWorkingGroup && $context === 'public')
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Editing <span class="font-semibold">Public</span> roll overrides (WG base override is OFF).
                    Turn on <span class="font-semibold">Base</span> in “Override switches” to edit WG roll overrides.
                </div>
            @elseif ($selectedWorkingGroup && $context === 'wg')
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    Editing <span class="font-semibold">Working Group</span> roll overrides (Base override is ON).
                </div>
            @endif
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="space-y-3" data-repeat="rolls">
                    @foreach ($rows as $r)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4" data-row="rolls" data-row-id="{{ $r->id }}">
                            <div class="grid gap-3 lg:grid-cols-[minmax(0,1.4fr)_repeat(3,minmax(0,1fr))_auto] items-end">
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Roll</label>
                                    <select class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                        data-field="rolls.roll_id">
                                        @foreach ($activeRollLinks as $link)
                                            @if ($link->roll)
                                                <option value="{{ $link->roll->id }}" @selected($r->roll_id == $link->roll->id)>
                                                    {{ $link->roll->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Rate / sqft</label>
                                    <input type="number" step="0.01" min="0"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                        data-field="rolls.rate_per_sqft" value="{{ $r->rate_per_sqft ?? '' }}">
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Offcut / sqft</label>
                                    <input type="number" step="0.01" min="0"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                        data-field="rolls.offcut_rate_per_sqft" value="{{ $r->offcut_rate_per_sqft ?? '' }}">
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Min charge</label>
                                    <input type="number" step="0.01" min="0"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                        data-field="rolls.min_charge" value="{{ $r->min_charge ?? '' }}">
                                </div>

                                <div class="flex items-center gap-2 justify-end">
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                        <input type="checkbox" data-field="rolls.is_active" {{ $r->is_active ? 'checked' : '' }}>
                                        Active
                                    </label>
                                    <button type="button"
                                        class="rounded-2xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50"
                                        data-row-remove="rolls">Remove</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <button type="button"
                        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        data-row-add="rolls">
                        + Add roll override
                    </button>

                    <button type="button"
                        class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                        data-ajax-save="rolls-sync"
                        data-context="{{ $context }}"
                        data-needs-pricing-id="1"
                        data-pricing-id="{{ $activePricingId ?? '' }}">
                        Save roll overrides
                    </button>
                </div>
            </div>
        @endif
    </div>
</section>

<template id="tpl-rolls-row">
    <div class="rounded-2xl border border-slate-200 bg-white p-4" data-row="rolls" data-row-id="">
        <div class="grid gap-3 lg:grid-cols-[minmax(0,1.4fr)_repeat(3,minmax(0,1fr))_auto] items-end">
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Roll</label>
                <select class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                    data-field="rolls.roll_id">
                    @foreach ($activeRollLinks as $link)
                        @if ($link->roll)
                            <option value="{{ $link->roll->id }}">{{ $link->roll->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Rate / sqft</label>
                <input type="number" step="0.01" min="0"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                    data-field="rolls.rate_per_sqft" value="">
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Offcut / sqft</label>
                <input type="number" step="0.01" min="0"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                    data-field="rolls.offcut_rate_per_sqft" value="">
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Min charge</label>
                <input type="number" step="0.01" min="0"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                    data-field="rolls.min_charge" value="">
            </div>

            <div class="flex items-center gap-2 justify-end">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" data-field="rolls.is_active" checked> Active
                </label>
                <button type="button"
                    class="rounded-2xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50"
                    data-row-remove="rolls">Remove</button>
            </div>
        </div>
    </div>
</template>
