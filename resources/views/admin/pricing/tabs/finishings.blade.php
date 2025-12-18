@php
    $wgId = $selectedWorkingGroup?->id;
    // Finishings follow override_finishings.
    $editingPricing = null;
    if ($wgPricing && ($wgPricing->override_finishings ?? false)) {
        $editingPricing = $wgPricing;
    } else {
        $editingPricing = $publicPricing ?: $wgPricing;
    }

    $context = ($editingPricing?->context === 'working_group') ? 'wg' : 'public';
    $activePricingId = $editingPricing?->id;
    $modeLabel = $context === 'wg' ? 'Working Group' : 'Public';
    $finishingLinks = $product->finishingLinks ?? collect();
    $existingRows = $editingPricing?->finishingPricings ?? collect();
@endphp

<section x-data="{ open: true }" class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
    <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-5 py-4 sm:px-6 bg-white">
        <div>
            <div class="text-base font-bold text-slate-900">Finishings Pricing</div>
            <div class="text-xs text-slate-500">
                Managing: <span class="font-semibold">{{ $modeLabel }}</span>
                @if ($selectedWorkingGroup)
                    · WG: <span class="font-semibold">{{ $selectedWorkingGroup->name }}</span>
                @endif
            </div>
        </div>
        <span class="text-slate-400" x-text="open ? '−' : '+'"></span>
    </button>

    <div x-show="open" x-transition class="border-t border-slate-100 p-5 sm:p-6 space-y-4">
        @if (! $publicPricing && ! $wgPricing)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Create base pricing first (Public or WG) to manage finishings pricing.
            </div>
        @else
            @if ($selectedWorkingGroup && $context === 'public')
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Editing <span class="font-semibold">Public</span> finishings (WG finishings override is OFF).
                    Turn on <span class="font-semibold">Finishings</span> in “Override switches” to edit WG finishings pricing.
                </div>
            @elseif ($selectedWorkingGroup && $context === 'wg')
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    Editing <span class="font-semibold">Working Group</span> finishings (Finishings override is ON).
                </div>
            @endif
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400 mb-3">Rows</div>

                <div class="space-y-3" data-repeat="finishings">
                    @foreach ($existingRows as $row)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4" data-row="finishings" data-row-id="{{ $row->id }}">
                            <div class="grid gap-3 lg:grid-cols-[minmax(0,1.4fr)_repeat(4,minmax(0,1fr))_auto] items-end">
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Finishing</label>
                                    <select class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                        data-field="finishings.finishing_product_id">
                                        @foreach ($finishingLinks as $link)
                                            @php $fp = $link->finishingProduct; @endphp
                                            @if ($fp)
                                                <option value="{{ $fp->id }}" @selected($row->finishing_product_id == $fp->id)>
                                                    {{ $fp->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Per piece</label>
                                    <input type="number" step="0.01" min="0"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                        data-field="finishings.price_per_piece"
                                        value="{{ $row->price_per_piece ?? '' }}">
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Per side</label>
                                    <input type="number" step="0.01" min="0"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                        data-field="finishings.price_per_side"
                                        value="{{ $row->price_per_side ?? '' }}">
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Flat</label>
                                    <input type="number" step="0.01" min="0"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                        data-field="finishings.flat_price"
                                        value="{{ $row->flat_price ?? '' }}">
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Min</label>
                                        <input type="number" step="1" min="1"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                            data-field="finishings.min_qty"
                                            value="{{ $row->min_qty ?? '' }}">
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Max</label>
                                        <input type="number" step="1" min="1"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                            data-field="finishings.max_qty"
                                            value="{{ $row->max_qty ?? '' }}">
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 justify-end">
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                        <input type="checkbox" data-field="finishings.is_active" {{ $row->is_active ? 'checked' : '' }}>
                                        Active
                                    </label>

                                    <button type="button"
                                        class="rounded-2xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50"
                                        data-row-remove="finishings">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <button type="button"
                        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        data-row-add="finishings">
                        + Add finishing row
                    </button>

                    <button type="button"
                        class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                        data-ajax-save="finishings-sync"
                        data-context="{{ $context }}"
                        data-needs-pricing-id="1"
                        data-pricing-id="{{ $activePricingId ?? '' }}">
                        Save finishings
                    </button>
                </div>
            </div>
        @endif
    </div>
</section>

<template id="tpl-finishings-row">
    <div class="rounded-2xl border border-slate-200 bg-white p-4" data-row="finishings" data-row-id="">
        <div class="grid gap-3 lg:grid-cols-[minmax(0,1.4fr)_repeat(4,minmax(0,1fr))_auto] items-end">
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Finishing</label>
                <select class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                    data-field="finishings.finishing_product_id">
                    @foreach ($finishingLinks as $link)
                        @php $fp = $link->finishingProduct; @endphp
                        @if ($fp)
                            <option value="{{ $fp->id }}">{{ $fp->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Per piece</label>
                <input type="number" step="0.01" min="0"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                    data-field="finishings.price_per_piece" value="">
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Per side</label>
                <input type="number" step="0.01" min="0"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                    data-field="finishings.price_per_side" value="">
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Flat</label>
                <input type="number" step="0.01" min="0"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                    data-field="finishings.flat_price" value="">
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Min</label>
                    <input type="number" step="1" min="1"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                        data-field="finishings.min_qty" value="">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Max</label>
                    <input type="number" step="1" min="1"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                        data-field="finishings.max_qty" value="">
                </div>
            </div>

            <div class="flex items-center gap-2 justify-end">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" data-field="finishings.is_active" checked> Active
                </label>
                <button type="button"
                    class="rounded-2xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50"
                    data-row-remove="finishings">
                    Remove
                </button>
            </div>
        </div>
    </div>
</template>
