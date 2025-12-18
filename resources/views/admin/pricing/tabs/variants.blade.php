@php
    $variantSets = $product->activeVariantSets ?? collect();
    // Variant pricing rows follow override_variants.
    $editingPricing = null;
    if ($wgPricing && ($wgPricing->override_variants ?? false)) {
        $editingPricing = $wgPricing;
    } else {
        $editingPricing = $publicPricing ?: $wgPricing;
    }

    $context = ($editingPricing?->context === 'working_group') ? 'wg' : 'public';
    $activePricingId = $editingPricing?->id;
    $rows = $editingPricing?->variantPricings ?? collect();
    $wgId = $selectedWorkingGroup?->id;
@endphp

<div class="space-y-5">
    <section x-data="{ open: true }" class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <button type="button" @click="open=!open" class="w-full flex items-center justify-between px-5 py-4 sm:px-6 bg-white">
            <div>
                <div class="text-base font-bold text-slate-900">Variant Availability</div>
                <div class="text-xs text-slate-500">Enable/disable variant sets per working group.</div>
            </div>
            <span class="text-slate-400" x-text="open ? '−' : '+'"></span>
        </button>

        <div x-show="open" x-transition class="border-t border-slate-100 p-5 sm:p-6">
            @if (! $selectedWorkingGroup)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Select a working group to manage availability toggles.
                </div>
            @elseif ($variantSets->isEmpty())
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                    No variant sets found for this product.
                </div>
            @else
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($variantSets as $vs)
                        @php
                            $enabled = $availabilityMap[(int) $vs->id] ?? true;
                            $toggleUrl = route('admin.pricing.products.variants.availability', ['product' => $product, 'variantSet' => $vs]);
                        @endphp

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-slate-900 truncate">
                                    {{ $vs->code ?: ('Variant set #' . $vs->id) }}
                                </div>
                                <div class="text-xs text-slate-500 truncate">ID: {{ $vs->id }}</div>
                            </div>

                            <button type="button"
                                data-ajax-toggle="1"
                                data-url="{{ $toggleUrl }}"
                                data-method="PATCH"
                                data-payload='@json(["working_group_id" => $wgId, "is_enabled" => $enabled ? 0 : 1])'
                                class="rounded-2xl px-4 py-2 text-xs font-semibold border shadow-sm transition
                                    {{ $enabled ? 'border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                                {{ $enabled ? 'Enabled' : 'Disabled' }}
                            </button>
                        </div>
                    @endforeach
                </div>

                <p class="mt-4 text-xs text-slate-500">
                    Tip: availability affects product options shown to that working group.
                </p>
            @endif
        </div>
    </section>

    <section x-data="{ open: true }" class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <button type="button" @click="open=!open" class="w-full flex items-center justify-between px-5 py-4 sm:px-6 bg-white">
            <div>
                <div class="text-base font-bold text-slate-900">Variant Pricing</div>
                <div class="text-xs text-slate-500">Optional overrides per variant set.</div>
            </div>
            <span class="text-slate-400" x-text="open ? '−' : '+'"></span>
        </button>

        <div x-show="open" x-transition class="border-t border-slate-100 p-5 sm:p-6 space-y-4">
            @if (! $publicPricing && ! $wgPricing)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Create base pricing first to manage variant pricing.
                </div>
            @else
                @if ($selectedWorkingGroup && $context === 'public')
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Editing <span class="font-semibold">Public</span> variant pricing (WG variants override is OFF).
                        Turn on <span class="font-semibold">Variants</span> in “Override switches” to edit WG variant pricing.
                    </div>
                @elseif ($selectedWorkingGroup && $context === 'wg')
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        Editing <span class="font-semibold">Working Group</span> variant pricing (Variants override is ON).
                    </div>
                @endif
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="space-y-3" data-repeat="variants">
                        @foreach ($rows as $r)
                            <div class="rounded-2xl border border-slate-200 bg-white p-4" data-row="variants" data-row-id="{{ $r->id }}">
                                <div class="grid gap-3 lg:grid-cols-[minmax(0,1.3fr)_repeat(4,minmax(0,1fr))_auto] items-end">
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Variant set</label>
                                        <select class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                            data-field="variants.variant_set_id">
                                            @foreach ($variantSets as $vs)
                                                <option value="{{ $vs->id }}" @selected($r->variant_set_id == $vs->id)>{{ $vs->code ?: ('Variant set #' . $vs->id) }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Fixed</label>
                                        <input type="number" step="0.01" min="0"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                            data-field="variants.fixed_price" value="{{ $r->fixed_price ?? '' }}">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Rate/sqft</label>
                                        <input type="number" step="0.01" min="0"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                            data-field="variants.rate_per_sqft" value="{{ $r->rate_per_sqft ?? '' }}">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Offcut/sqft</label>
                                        <input type="number" step="0.01" min="0"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                            data-field="variants.offcut_rate_per_sqft" value="{{ $r->offcut_rate_per_sqft ?? '' }}">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Min</label>
                                        <input type="number" step="0.01" min="0"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                                            data-field="variants.min_charge" value="{{ $r->min_charge ?? '' }}">
                                    </div>

                                    <div class="flex items-center gap-2 justify-end">
                                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                            <input type="checkbox" data-field="variants.is_active" {{ $r->is_active ? 'checked' : '' }}>
                                            Active
                                        </label>
                                        <button type="button"
                                            class="rounded-2xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50"
                                            data-row-remove="variants">Remove</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <button type="button"
                            class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            data-row-add="variants">
                            + Add variant row
                        </button>

                        <button type="button"
                            class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                            data-ajax-save="variants-sync"
                            data-context="{{ $context }}"
                            data-needs-pricing-id="1"
                            data-pricing-id="{{ $activePricingId ?? '' }}">
                            Save variant pricing
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>

<template id="tpl-variants-row">
    <div class="rounded-2xl border border-slate-200 bg-white p-4" data-row="variants" data-row-id="">
        <div class="grid gap-3 lg:grid-cols-[minmax(0,1.3fr)_repeat(4,minmax(0,1fr))_auto] items-end">
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Variant set</label>
                <select class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                    data-field="variants.variant_set_id">
                    @foreach ($variantSets as $vs)
                        <option value="{{ $vs->id }}">{{ $vs->code ?: ('Variant set #' . $vs->id) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Fixed</label>
                <input type="number" step="0.01" min="0"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                    data-field="variants.fixed_price" value="">
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Rate/sqft</label>
                <input type="number" step="0.01" min="0"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                    data-field="variants.rate_per_sqft" value="">
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Offcut/sqft</label>
                <input type="number" step="0.01" min="0"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                    data-field="variants.offcut_rate_per_sqft" value="">
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Min</label>
                <input type="number" step="0.01" min="0"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm"
                    data-field="variants.min_charge" value="">
            </div>

            <div class="flex items-center gap-2 justify-end">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" data-field="variants.is_active" checked> Active
                </label>
                <button type="button"
                    class="rounded-2xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50"
                    data-row-remove="variants">Remove</button>
            </div>
        </div>
    </div>
</template>
