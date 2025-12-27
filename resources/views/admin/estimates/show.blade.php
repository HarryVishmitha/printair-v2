{{-- resources/views/admin/estimates/show.blade.php --}}

<x-app-layout>
    <x-slot name="sectionTitle">Sales</x-slot>
    <x-slot name="pageTitle">Estimate</x-slot>

    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.estimates.index') }}" class="text-slate-500 hover:text-slate-700">Estimates</a>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">{{ $estimate->estimate_no ?? ('EST-' . $estimate->id) }}</span>
    </x-slot>

    @php
        $status = $estimate->status ?? 'draft';
        $currency = $estimate->currency ?? 'LKR';
        $locked = (bool) $estimate->locked_at;

        $snap = is_array($estimate->customer_snapshot) ? $estimate->customer_snapshot : [];
        $customerName = $snap['full_name'] ?? $snap['name'] ?? ($estimate->customer->full_name ?? '—');
        $customerPhone = $snap['phone'] ?? ($estimate->customer->phone ?? null);
        $customerEmail = $snap['email'] ?? ($estimate->customer->email ?? null);

        $lineDiscountTotal = (float) ($estimate->items?->sum('discount_amount') ?? 0);
        $headerDiscount = max(0.0, (float) $estimate->discount_total - $lineDiscountTotal);

        $statusMap = [
            'draft' => 'bg-slate-50 text-slate-700 ring-slate-200',
            'sent' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'viewed' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
            'accepted' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'rejected' => 'bg-rose-50 text-rose-700 ring-rose-200',
            'converted' => 'bg-amber-50 text-amber-800 ring-amber-200',
            'expired' => 'bg-slate-100 text-slate-600 ring-slate-200',
            'cancelled' => 'bg-slate-100 text-slate-600 ring-slate-200',
        ];
        $badge = $statusMap[$status] ?? $statusMap['draft'];

        $validUntil = $estimate->valid_until ? \Carbon\Carbon::parse($estimate->valid_until) : null;
        $isExpired = $validUntil ? $validUntil->isPast() : false;
        $isExpiring = $validUntil ? ($validUntil->isFuture() && $validUntil->diffInDays(now()) <= 3) : false;
        $shareToken = session('share_token');
    @endphp

    <div class="space-y-6" x-data="{ copied: false }">

        {{-- Toasts --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => (show = false), 4500)"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-gradient-to-r from-emerald-50 via-emerald-50/90 to-white px-4 py-3.5 text-sm text-emerald-800 shadow-sm">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </span>
                <span class="font-medium">{{ session('success') }}</span>
                <button @click="show=false" class="ml-auto text-emerald-500 hover:text-emerald-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        {{-- One-time share token --}}
        @if ($shareToken)
            @php
                $publicUrl = url('/estimate/' . $shareToken);
            @endphp
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="font-semibold">Share link created (shown once)</div>
                        <div class="mt-1 text-xs text-amber-800">Copy and send to the customer. Don’t store this token in notes.</div>
                    </div>
                    <button type="button"
                        @click="navigator.clipboard.writeText('{{ $publicUrl }}'); copied=true; setTimeout(()=>copied=false, 1500)"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                        <span x-show="!copied">Copy Link</span>
                        <span x-show="copied">Copied</span>
                    </button>
                </div>
                <div class="mt-2 rounded-xl border border-amber-200 bg-white px-3 py-2 text-xs text-slate-700">
                    {{ $publicUrl }}
                </div>
            </div>
        @endif

        {{-- HERO --}}
        <section
            class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-5 py-5 sm:px-7 sm:py-6 text-white shadow-lg shadow-slate-900/20">
            <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-16 right-10 h-56 w-56 rounded-full border border-white/10"></div>

            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-2 max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium tracking-wide backdrop-blur">
                        <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        Sales · Estimates
                    </div>

                    <div class="flex items-start gap-3 pt-1">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 shadow-inner shadow-black/20">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 7.5h6M9 12h6m-9 7.5h12A2.25 2.25 0 0020.25 17.25V6.75A2.25 2.25 0 0018 4.5H6A2.25 2.25 0 003.75 6.75v10.5A2.25 2.25 0 006 19.5z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-xl sm:text-2xl font-black leading-tight truncate">
                                    {{ $estimate->estimate_no ?? ('EST-' . $estimate->id) }}
                                </h2>
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 {{ $badge }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ $status }}
                                </span>
                                @if ($locked)
                                    <span class="inline-flex items-center gap-2 rounded-full bg-black/40 px-2.5 py-1 text-[11px] font-semibold">
                                        <span class="h-1.5 w-1.5 rounded-full bg-white/70"></span>
                                        Locked
                                    </span>
                                @endif
                                @if ($isExpiring)
                                    <span class="inline-flex items-center gap-2 rounded-full bg-amber-500/20 px-2.5 py-1 text-[11px] font-semibold">
                                        Expiring soon
                                    </span>
                                @endif
                                @if ($isExpired)
                                    <span class="inline-flex items-center gap-2 rounded-full bg-rose-500/20 px-2.5 py-1 text-[11px] font-semibold">
                                        Expired
                                    </span>
                                @endif
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-white/80">
                                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">
                                    WG: <span class="font-semibold text-white">{{ $estimate->workingGroup->name ?? ('WG#'.$estimate->working_group_id) }}</span>
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">
                                    Customer: <span class="font-semibold text-white">{{ $customerName }}</span>
                                </span>
                                @if ($customerPhone)
                                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">
                                        {{ $customerPhone }}
                                    </span>
                                @endif
                                @if ($customerEmail)
                                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">
                                        {{ $customerEmail }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 text-xs text-white/70">
                                @if ($validUntil)
                                    Valid until: <span class="font-semibold text-white">{{ $validUntil->format('Y-m-d') }}</span>
                                    <span class="text-white/40">·</span>
                                    <span>
                                        {{ $validUntil->isPast() ? 'Past due' : ('In ' . now()->diffInDays($validUntil) . ' days') }}
                                    </span>
                                @else
                                    Valid until: <span class="text-white/60 italic">—</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-end">
                    @can('update', $estimate)
                        <a href="{{ route('admin.estimates.edit', $estimate) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-md shadow-black/10 hover:shadow-lg hover:-translate-y-0.5 transition-all">
                            Edit
                        </a>
                    @endcan

                    @can('view', $estimate)
                        <a href="{{ route('admin.estimates.pdf', $estimate) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/15">
                            Download PDF
                        </a>
                    @endcan

                    @can('update', $estimate)
                        <form method="POST" action="{{ route('admin.estimates.recalc', $estimate) }}">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-600">
                                Recalc
                            </button>
                        </form>
                    @endcan

                    @can('view', $estimate)
                        <button type="button"
                            @click="$dispatch('est-open-share')"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/15">
                            Share
                        </button>
                    @endcan

                    @can('send', $estimate)
                        <button type="button"
                            @click="$dispatch('est-open-action', { mode: 'send' })"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                            Send
                        </button>
                    @endcan

                    @can('resend', $estimate)
                        <form method="POST" action="{{ route('admin.estimates.resend', $estimate) }}">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/10 px-4 py-2.5 text-sm font-semibold text-white ring-1 ring-white/20 hover:bg-white/15">
                                Send Again
                            </button>
                        </form>
                    @endcan

                    @can('accept', $estimate)
                        <button type="button"
                            @click="$dispatch('est-open-action', { mode: 'accept' })"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                            Accept
                        </button>
                    @endcan

                    @can('reject', $estimate)
                        <button type="button"
                            @click="$dispatch('est-open-action', { mode: 'reject' })"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 shadow-sm hover:bg-rose-50">
                            Reject
                        </button>
                    @endcan

                    @if ($status === 'accepted')
                        <button type="button"
                            @click="$dispatch('est-open-action', { mode: 'convert' })"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-600">
                            Convert → Order
                        </button>
                    @endif
                </div>
            </div>
        </section>

        {{-- KPI STRIP --}}
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Subtotal</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ $currency }} {{ number_format((float) $estimate->subtotal, 2) }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Line Discounts</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ $currency }} {{ number_format((float) $lineDiscountTotal, 2) }}</p>
                <p class="mt-1 text-xs text-slate-500">From item discounts</p>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Estimate Discount</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ $currency }} {{ number_format((float) $headerDiscount, 2) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ strtoupper((string) ($estimate->discount_mode ?? 'none')) }} {{ $estimate->discount_mode === 'percent' ? ('(' . (float) $estimate->discount_value . '%)') : '' }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Tax</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ $currency }} {{ number_format((float) $estimate->tax_total, 2) }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Discount Total</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ $currency }} {{ number_format((float) $estimate->discount_total, 2) }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-slate-900 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-white/60">Grand Total</p>
                <p class="mt-2 text-2xl font-black text-white">{{ $currency }} {{ number_format((float) $estimate->grand_total, 2) }}</p>
                <p class="mt-1 text-xs text-white/60">Final amount</p>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            {{-- Items --}}
            <section class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden xl:col-span-2">
                <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Items</h3>
                            <p class="mt-1 text-xs text-slate-500">Products, sizes, rolls, and line totals.</p>
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ $estimate->items->count() }} item(s)
                        </div>
                    </div>
                </div>

                @if ($estimate->items->count() === 0)
                    <div class="px-6 py-12 text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.4 11.4z" />
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-slate-900">No items</h4>
                        <p class="mt-1 text-sm text-slate-500">Add at least one item before sending.</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
	                        @foreach ($estimate->items as $it)
	                            @php
	                                $p = $it->product;
	                                $imgPath = $p?->primaryImage?->path ? ltrim((string) $p->primaryImage->path, '/') : '';
	                                $imgUrl = $imgPath !== '' ? asset('storage/' . $imgPath) : asset('assets/placeholders/product.png');
	                                $isDim = (bool) ($p?->product_type === 'dimension_based' || $p?->requires_dimensions);
	                                $rollName = $it->roll?->name;
	                                $rollWidth = $it->roll?->width_in;
	                                $snap = is_array($it->pricing_snapshot) ? $it->pricing_snapshot : [];
	                                $rotated = (bool) data_get($snap, 'roll.rotated', false);
	                                $autoRoll = (bool) data_get($snap, 'roll.auto', false);
	                                $finTotal = (float) ($it->finishings?->sum('total') ?? 0);
	                                $variantLabel = (string) (data_get($snap, 'variant_label') ?? '');
	                                if ($variantLabel === '' && $it->variantSetItem) {
	                                    $g = $it->variantSetItem->option?->group?->name;
	                                    $o = $it->variantSetItem->option?->label;
	                                    $variantLabel = trim(($g ? ($g . ': ') : '') . ($o ?: ''));
	                                }
	                                $lineSubtotalWithFin = (float) ($it->line_subtotal ?? 0) + $finTotal;
	                                $lineTotalWithFin = (float) ($it->line_total ?? 0) + $finTotal;
	                            @endphp

                            <div class="px-6 py-5">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex items-start gap-4 min-w-0">
                                        <img src="{{ $imgUrl }}" alt="" class="h-14 w-14 rounded-2xl border border-slate-200 bg-white object-cover">

                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <div class="truncate text-sm font-bold text-slate-900">
                                                    {{ $it->title }}
                                                </div>
                                                @if ($p)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-700 ring-1 ring-slate-200">
                                                        {{ $p->name }}
                                                    </span>
                                                @endif
                                                @if ($p?->product_code)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 text-[11px] font-medium text-slate-600 ring-1 ring-slate-200">
                                                        {{ $p->product_code }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if ($it->description)
                                                <div class="mt-1 text-sm text-slate-600">
                                                    {{ $it->description }}
                                                </div>
                                            @endif

                                            <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px] text-slate-600">
                                                <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 ring-1 ring-slate-200">
                                                    Qty: <span class="font-semibold text-slate-800">{{ $it->qty }}</span>
                                                </span>
                                                <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 ring-1 ring-slate-200">
                                                    Unit: <span class="font-semibold text-slate-800">{{ $currency }} {{ number_format((float) $it->unit_price, 2) }}</span>
                                                </span>
                                                @if ($isDim && $it->width && $it->height && $it->unit)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 ring-1 ring-slate-200">
                                                        Size: <span class="font-semibold text-slate-800">{{ rtrim(rtrim(number_format((float) $it->width, 3), '0'), '.') }} × {{ rtrim(rtrim(number_format((float) $it->height, 3), '0'), '.') }} {{ $it->unit }}</span>
                                                    </span>
                                                @endif
                                                @if ($isDim && $it->area_sqft)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 ring-1 ring-slate-200">
                                                        Area: <span class="font-semibold text-slate-800">{{ number_format((float) $it->area_sqft, 4) }} sqft</span>
                                                    </span>
                                                @endif
                                                @if ($isDim && $it->offcut_sqft && (float) $it->offcut_sqft > 0)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 ring-1 ring-slate-200">
                                                        Offcut: <span class="font-semibold text-slate-800">{{ number_format((float) $it->offcut_sqft, 4) }} sqft</span>
                                                    </span>
                                                @endif
	                                                @if ($rollName)
	                                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 text-slate-700 ring-1 ring-slate-200">
	                                                        Roll: <span class="font-semibold">{{ $rollName }}@if($rollWidth) ({{ number_format((float) $rollWidth, 1) }}in)@endif</span>
	                                                        @if ($autoRoll)
	                                                            <span class="ml-1 text-slate-400">· Auto</span>
	                                                        @endif
	                                                    </span>
	                                                    @if ($rotated)
	                                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-amber-800 ring-1 ring-amber-200">
	                                                            Rotated
	                                                        </span>
	                                                    @endif
	                                                @endif
	                                                @if ($variantLabel !== '')
	                                                    <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-indigo-700 ring-1 ring-indigo-200">
	                                                        Variant: <span class="font-semibold">{{ $variantLabel }}</span>
	                                                    </span>
	                                                @endif
	                                                @if ($it->finishings?->count())
	                                                    <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 ring-1 ring-slate-200">
	                                                        Finishings: <span class="font-semibold text-slate-800">{{ $it->finishings->count() }}</span>
	                                                    </span>
	                                                @endif
	                                            </div>

	                                            @if ($it->finishings?->count())
	                                                <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-700">
	                                                    <div class="flex items-center justify-between gap-3">
	                                                        <div class="font-semibold text-slate-900">Finishings</div>
	                                                        <div class="font-bold text-slate-900">{{ $currency }} {{ number_format((float) $finTotal, 2) }}</div>
	                                                    </div>
	                                                    <div class="mt-2 space-y-1">
	                                                        @foreach ($it->finishings as $f)
	                                                            <div class="flex items-center justify-between gap-3">
	                                                                <div class="min-w-0 truncate">
	                                                                    {{ $f->label ?? ($f->option?->label ?? ('Finishing #' . $f->finishing_product_id)) }}
	                                                                    <span class="text-slate-500">× {{ (int) ($f->qty ?? 1) }}</span>
	                                                                </div>
	                                                                <div class="whitespace-nowrap font-semibold text-slate-900">
	                                                                    {{ $currency }} {{ number_format((float) ($f->total ?? 0), 2) }}
	                                                                </div>
	                                                            </div>
	                                                        @endforeach
	                                                    </div>
	                                                </div>
	                                            @endif
	                                        </div>
	                                    </div>

                                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-1 sm:text-right">
	                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
	                                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Line Total</div>
	                                            <div class="mt-1 text-base font-black text-slate-900">{{ $currency }} {{ number_format((float) $lineTotalWithFin, 2) }}</div>
	                                            <div class="mt-1 text-[11px] text-slate-600">
	                                                Base {{ number_format((float) $it->line_subtotal, 2) }}
	                                                @if ($finTotal > 0)
	                                                    · Fin {{ number_format((float) $finTotal, 2) }}
	                                                @endif
	                                                · Sub {{ number_format((float) $lineSubtotalWithFin, 2) }}
	                                                · Disc {{ number_format((float) $it->discount_amount, 2) }}
	                                                · Tax {{ number_format((float) $it->tax_amount, 2) }}
	                                            </div>
	                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Right rail --}}
            <aside class="space-y-6 xl:col-span-1">
                <section class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900">Document</h3>
                    <div class="mt-4 space-y-3 text-sm text-slate-700">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Created</span>
                            <span class="font-semibold">{{ optional($estimate->created_at)->format('Y-m-d H:i') ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Created by</span>
                            <span class="font-semibold">{{ $estimate->createdBy->full_name ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Updated</span>
                            <span class="font-semibold">{{ optional($estimate->updated_at)->format('Y-m-d H:i') ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Locked</span>
                            <span class="font-semibold">
                                {{ $estimate->locked_at ? \Carbon\Carbon::parse($estimate->locked_at)->format('Y-m-d H:i') : 'No' }}
                            </span>
                        </div>
                        @if ($estimate->locked_at)
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Locked by</span>
                                <span class="font-semibold">{{ $estimate->lockedBy->full_name ?? '—' }}</span>
                            </div>
                        @endif
                        @if ($estimate->order)
                            <div class="pt-2">
                                <a href="{{ route('admin.orders.show', $estimate->order) }}"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    View Order ({{ $estimate->order->order_no ?? ('#'.$estimate->order->id) }})
                                </a>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900">Notes</h3>
                    <div class="mt-4 space-y-3 text-sm text-slate-700">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Internal</div>
                            <div class="mt-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                {{ $estimate->notes_internal ?: '—' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Customer</div>
                            <div class="mt-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                {{ $estimate->notes_customer ?: '—' }}
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900">Audit Trail</h3>
                    <p class="mt-1 text-xs text-slate-500">Append-only status history.</p>

                    <div class="mt-4 space-y-3">
                        @forelse ($estimate->statusHistories as $h)
                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-xs font-semibold text-slate-900">
                                            {{ $h->from_status ? strtoupper($h->from_status) : '—' }}
                                            <span class="text-slate-300">→</span>
                                            {{ strtoupper($h->to_status) }}
                                        </div>
                                        <div class="mt-1 text-[11px] text-slate-500">
                                            {{ \Carbon\Carbon::parse($h->created_at)->format('Y-m-d H:i') }}
                                            <span class="text-slate-300">·</span>
                                            {{ $h->changedBy->full_name ?? 'System/Public' }}
                                        </div>
                                        @if ($h->reason)
                                            <div class="mt-2 text-xs text-slate-700">
                                                {{ $h->reason }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                No status history yet.
                            </div>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>

        {{-- Action modal (send/accept/reject/convert) --}}
        <div
            x-data="{ open: false, mode: null, reason: '' }"
            @est-open-action.window="open=true; mode=$event.detail.mode; reason=''"
            @keydown.escape.window="open=false"
            x-cloak
        >
            <div x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm"
                @click="open=false"></div>

            <div x-show="open"
                x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-3"
                class="fixed inset-0 z-50 flex items-center justify-center p-4">

                <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl shadow-slate-900/20 overflow-hidden"
                    @click.away="open=false">

                    <div class="px-6 py-5 border-b border-slate-100">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900" x-text="
                                    mode === 'send' ? 'Send Estimate' :
                                    mode === 'accept' ? 'Accept Estimate' :
                                    mode === 'reject' ? 'Reject Estimate' :
                                    'Convert to Order'
                                "></h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $estimate->estimate_no ?? ('EST-' . $estimate->id) }}
                                </p>
                            </div>
                            <button @click="open=false" class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Reason
                            </label>
                            <textarea x-model="reason" rows="3"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10"
                                :placeholder="mode==='reject' ? 'Reason is required for rejection…' : 'Optional audit note…'"></textarea>
                            <p class="mt-2 text-[11px] text-slate-500">
                                Saved into status history for audit.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <button @click="open=false"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                            Cancel
                        </button>

                        <form method="POST"
                            :action="
                                mode==='send' ? `{{ url('/admin/estimates/'.$estimate->id.'/send') }}` :
                                mode==='accept' ? `{{ url('/admin/estimates/'.$estimate->id.'/accept') }}` :
                                mode==='reject' ? `{{ url('/admin/estimates/'.$estimate->id.'/reject') }}` :
                                `{{ route('admin.orders.from-estimate', $estimate) }}`
                            ">
                            @csrf
                            <input type="hidden" name="reason" :value="reason">
                            <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-md transition-all"
                                :class="
                                    mode==='send' ? 'bg-slate-900 hover:bg-slate-800' :
                                    mode==='accept' ? 'bg-emerald-600 hover:bg-emerald-700' :
                                    mode==='reject' ? 'bg-rose-600 hover:bg-rose-700' :
                                    'bg-amber-500 hover:bg-amber-600'
                                ">
                                Confirm
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Share modal --}}
        <div
            x-data="{ open: false, expires_at: '' }"
            @est-open-share.window="open=true; expires_at=''"
            @keydown.escape.window="open=false"
            x-cloak
        >
            <div x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm"
                @click="open=false"></div>

            <div x-show="open"
                x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-3"
                class="fixed inset-0 z-50 flex items-center justify-center p-4">

                <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl shadow-slate-900/20 overflow-hidden"
                    @click.away="open=false">

                    <div class="px-6 py-5 border-b border-slate-100">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Create share link</h3>
                                <p class="mt-1 text-sm text-slate-500">Generates a token and stores only its hash.</p>
                            </div>
                            <button @click="open=false" class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Expires at (optional)
                            </label>
                            <input type="date" x-model="expires_at"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10" />
                        </div>
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900">
                            The token is shown once after creation. Copy it immediately.
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <button @click="open=false"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                            Cancel
                        </button>

                        <form method="POST" action="{{ route('admin.estimates.shares.create', $estimate) }}">
                            @csrf
                            <input type="hidden" name="expires_at" :value="expires_at || ''">
                            <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-slate-800">
                                Create
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
