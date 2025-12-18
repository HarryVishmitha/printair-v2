<x-app-layout>
    <x-slot name="sectionTitle">Pricing</x-slot>
    <x-slot name="pageTitle">Product Pricing Manager</x-slot>

    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.pricing.index') }}" class="text-slate-500 hover:text-slate-700">Pricing</a>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">{{ $product->name }}</span>
    </x-slot>

    @php
        $wgId = $selectedWorkingGroup?->id;
        $tab = $tab ?? 'base';

        $publicPricingId = $publicPricing?->id;
        $wgPricingId = $wgPricing?->id;
    @endphp

    <div class="space-y-6"
        data-pricing-manager
        data-product-id="{{ $product->id }}"
        data-working-group-id="{{ $wgId ?? '' }}"
        data-base-url="{{ route('admin.pricing.products.base', $product) }}"
        data-tiers-url="{{ route('admin.pricing.products.tiers.sync', $product) }}"
        data-variants-pricing-url="{{ route('admin.pricing.products.variants.pricing', $product) }}"
        data-finishings-url="{{ route('admin.pricing.products.finishings.pricing', $product) }}"
        data-rolls-url="{{ route('admin.pricing.products.rolls.pricing', $product) }}"
    >

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        <section class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            <div class="p-5 sm:p-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="h-16 w-16 rounded-2xl bg-slate-100 overflow-hidden flex items-center justify-center">
                        @if ($product->primaryImage?->path)
                            <img src="{{ asset('storage/' . $product->primaryImage->path) }}" class="h-full w-full object-cover" alt="{{ $product->name }}">
                        @else
                            <span class="text-xs text-slate-400">No image</span>
                        @endif
                    </div>

                    <div class="min-w-0">
                        <div class="text-lg font-bold text-slate-900 leading-tight">{{ $product->name }}</div>
                        <div class="mt-1 text-xs text-slate-500">
                            Product ID: <span class="font-mono">{{ $product->id }}</span>
                            @if ($product->slug)
                                · <span class="font-mono">{{ $product->slug }}</span>
                            @endif
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                Public
                                <span class="rounded-full bg-white px-2 py-0.5 text-slate-900" data-price-pill="public">{{ $pricePills['public_price_label'] ?? '—' }}</span>
                            </span>

                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                {{ $selectedWorkingGroup?->name ?? 'Working Group' }}
                                <span class="rounded-full bg-white px-2 py-0.5 text-slate-900" data-price-pill="wg">{{ $pricePills['wg_price_label'] ?? '—' }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('admin.pricing.products.show', $product) }}" class="flex items-end gap-3">
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Working Group</label>
                        <select name="working_group_id"
                            class="block w-64 rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                            <option value="">None (Public only)</option>
                            @foreach ($workingGroups as $wg)
                                <option value="{{ $wg->id }}" @selected($selectedWorkingGroup?->id === $wg->id)>{{ $wg->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="tab" value="{{ $tab }}">

                    <button class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                        Apply
                    </button>
                </form>
            </div>

            <div class="border-t border-slate-100 bg-slate-50/60 px-5 sm:px-6 py-3">
                @php
                    $tabs = [
                        'base' => 'Base Pricing',
                        'tiers' => 'Tiers',
                        'variants' => 'Variants',
                        'finishings' => 'Finishings',
                        'rolls' => 'Roll Overrides',
                    ];
                @endphp

                <div class="flex flex-wrap gap-2">
                    @foreach ($tabs as $key => $label)
                        <a href="{{ route('admin.pricing.products.show', $product) }}?tab={{ $key }}{{ $wgId ? '&working_group_id=' . $wgId : '' }}"
                            class="inline-flex items-center rounded-2xl px-4 py-2 text-sm font-semibold transition
                                {{ $tab === $key ? 'bg-white border border-slate-200 text-slate-900 shadow-sm' : 'bg-transparent text-slate-600 hover:bg-white/70 hover:text-slate-900' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        @if ($tab === 'base')
            @include('admin.pricing.tabs.base', [
                'publicPricingId' => $publicPricingId,
                'wgPricingId' => $wgPricingId,
            ])
        @elseif ($tab === 'tiers')
            @include('admin.pricing.tabs.tiers', ['publicPricingId' => $publicPricingId, 'wgPricingId' => $wgPricingId])
        @elseif ($tab === 'variants')
            @include('admin.pricing.tabs.variants', ['publicPricingId' => $publicPricingId, 'wgPricingId' => $wgPricingId])
        @elseif ($tab === 'finishings')
            @include('admin.pricing.tabs.finishings', ['publicPricingId' => $publicPricingId, 'wgPricingId' => $wgPricingId])
        @elseif ($tab === 'rolls')
            @include('admin.pricing.tabs.rolls', ['publicPricingId' => $publicPricingId, 'wgPricingId' => $wgPricingId])
        @endif
    </div>
</x-app-layout>
