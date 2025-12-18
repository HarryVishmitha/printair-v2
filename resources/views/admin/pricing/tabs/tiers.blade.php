@php
    // Tiers belong to base pricing => they follow override_base.
    $editingPricing = null;
    if ($wgPricing && ($wgPricing->override_base ?? false)) {
        $editingPricing = $wgPricing;
    } else {
        $editingPricing = $publicPricing ?: $wgPricing;
    }

    $context = ($editingPricing?->context === 'working_group') ? 'wg' : 'public';
    $activePricingId = $editingPricing?->id;
    $tiers = $editingPricing?->tiers ?? collect();
@endphp

<section x-data="{ open: true }" class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
    <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-5 py-4 sm:px-6 bg-white">
        <div>
            <div class="text-base font-bold text-slate-900">Price Tiers</div>
            <div class="text-xs text-slate-500">Quantity/range-based pricing for this pricing context.</div>
        </div>
        <span class="text-slate-400" x-text="open ? '−' : '+'"></span>
    </button>

    <div x-show="open" x-transition class="border-t border-slate-100 p-5 sm:p-6 space-y-4">
        @if (! $publicPricing && ! $wgPricing)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Create base pricing first to manage tiers.
            </div>
        @else
            @if ($selectedWorkingGroup && $context === 'public')
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Editing <span class="font-semibold">Public</span> tiers (WG base override is OFF).
                    Turn on <span class="font-semibold">Base</span> in “Override switches” to edit WG tiers.
                </div>
            @elseif ($selectedWorkingGroup && $context === 'wg')
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    Editing <span class="font-semibold">Working Group</span> tiers (Base override is ON).
                </div>
            @endif
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="space-y-3" data-repeat="tiers">
                    @foreach ($tiers as $t)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4" data-row="tiers" data-row-id="{{ $t->id }}">
                            <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-4 items-end">
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Min qty</label>
                                    <input type="number" min="1" step="1"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                        data-field="tiers.min_qty" value="{{ $t->min_qty }}">
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Max qty</label>
                                    <input type="number" min="1" step="1"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                        data-field="tiers.max_qty" value="{{ $t->max_qty ?? '' }}">
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Price</label>
                                    <input type="number" min="0" step="0.01"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                        data-field="tiers.price" value="{{ $t->price }}">
                                </div>
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button"
                                        class="rounded-2xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50"
                                        data-row-remove="tiers">Remove</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <button type="button"
                        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        data-row-add="tiers">
                        + Add tier
                    </button>

                    <button type="button"
                        class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                        data-ajax-save="tiers-sync"
                        data-context="{{ $context }}"
                        data-needs-pricing-id="1"
                        data-pricing-id="{{ $activePricingId ?? '' }}">
                        Save tiers
                    </button>
                </div>
            </div>
        @endif
    </div>
</section>

<template id="tpl-tiers-row">
    <div class="rounded-2xl border border-slate-200 bg-white p-4" data-row="tiers" data-row-id="">
        <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-4 items-end">
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Min qty</label>
                <input type="number" min="1" step="1"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                    data-field="tiers.min_qty" value="">
            </div>
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Max qty</label>
                <input type="number" min="1" step="1"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                    data-field="tiers.max_qty" value="">
            </div>
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Price</label>
                <input type="number" min="0" step="0.01"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                    data-field="tiers.price" value="">
            </div>
            <div class="flex items-center justify-end gap-2">
                <button type="button"
                    class="rounded-2xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50"
                    data-row-remove="tiers">Remove</button>
            </div>
        </div>
    </div>
</template>
