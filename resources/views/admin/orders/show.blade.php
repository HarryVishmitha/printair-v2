<x-app-layout>
    <x-slot name="sectionTitle">Sales</x-slot>
    <x-slot name="pageTitle">Order</x-slot>

    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.orders.index') }}" class="text-slate-500 hover:text-slate-700">Orders</a>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">{{ $order->order_no ?? 'ORD-' . $order->id }}</span>
    </x-slot>

    @php
        $currency = $order->currency ?? 'LKR';
        $status = (string) ($order->status ?? 'draft');
        $paymentStatus = (string) ($order->payment_status ?? 'unpaid');

        $snap = is_array($order->customer_snapshot) ? $order->customer_snapshot : [];
        $customerName =
            $order->customer?->full_name ?? ($order->customer_name ?? ($snap['full_name'] ?? ($snap['name'] ?? '—')));
        $customerEmail = $order->customer?->email ?? ($order->customer_email ?? ($snap['email'] ?? null));
        $customerPhone = $order->customer?->phone ?? ($order->customer_phone ?? ($snap['phone'] ?? null));
        $customerWhatsapp =
            $order->customer?->whatsapp_number ?? ($snap['whatsapp_number'] ?? ($snap['whatsapp'] ?? null));

        $shipping = data_get($order->meta, 'shipping');
        $shipping = is_array($shipping) ? $shipping : [];
        $shippingMethod = (string) ($shipping['method'] ?? 'pickup');

        $orderNotes = data_get($order->meta, 'notes');
        $orderSource = (string) (data_get($order->meta, 'source') ?? '');
        $orderMetaExtra = data_get($order->meta, 'meta');
        $orderMetaExtra = is_array($orderMetaExtra) ? $orderMetaExtra : [];

	        $finalInvoice = $order->invoices?->firstWhere('type', 'final');
	        $finalInvoiceIssued =
	            $finalInvoice && !in_array((string) $finalInvoice->status, ['draft', 'void', 'cancelled'], true);
	        $isTerminal = in_array($status, ['cancelled', 'refunded', 'completed'], true);

	        $statusMap = [
	            'draft' => 'bg-slate-100 border-slate-300 text-slate-700',
	            'confirmed' => 'bg-sky-100 border-sky-300 text-sky-700',
	            'processing' => 'bg-amber-100 border-amber-300 text-amber-800',
	            'in_production' => 'bg-amber-100 border-amber-300 text-amber-800',
	            'ready' => 'bg-indigo-100 border-indigo-300 text-indigo-700',
	            'out_for_delivery' => 'bg-violet-100 border-violet-300 text-violet-700',
	            'completed' => 'bg-emerald-100 border-emerald-300 text-emerald-700',
	            'cancelled' => 'bg-rose-100 border-rose-300 text-rose-700',
	            'refunded' => 'bg-slate-100 border-slate-300 text-slate-700',
	        ];
        $statusColor = $statusMap[$status] ?? $statusMap['draft'];

        $paymentMap = [
            'unpaid' => 'bg-rose-100 border-rose-300 text-rose-700',
            'partially_paid' => 'bg-amber-100 border-amber-300 text-amber-700',
            'paid' => 'bg-emerald-100 border-emerald-300 text-emerald-700',
            'refunded' => 'bg-slate-100 border-slate-300 text-slate-700',
        ];
        $paymentColor = $paymentMap[$paymentStatus] ?? $paymentMap['unpaid'];
    @endphp

    <div class="space-y-6">
        {{-- Toasts --}}
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
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => (show = false), 6500)"
                x-transition:enter="transition ease-out duration-300"
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
	            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
	                <div class="font-semibold">Please fix the errors below.</div>
	                <ul class="mt-2 list-disc pl-5 text-xs">
	                    @foreach ($errors->all() as $msg)
	                        <li>{{ $msg }}</li>
	                    @endforeach
	                </ul>
	            </div>
	        @endif

	        {{-- HERO BAND --}}
	        <section
	            class="relative overflow-visible rounded-3xl border border-slate-200/80 bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 px-5 py-5 sm:px-7 sm:py-6 text-white shadow-lg shadow-indigo-600/20">
	            {{-- keep blobs clipped --}}
	            <div class="pointer-events-none absolute inset-0 overflow-hidden rounded-3xl">
	                <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
	                <div class="pointer-events-none absolute -bottom-16 right-10 h-56 w-56 rounded-full border border-white/15"></div>
	            </div>

	            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-2 max-w-3xl">
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium tracking-wide backdrop-blur">
                        <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        Sales · Orders
                    </div>

                    <div class="flex items-start gap-3 pt-1">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 shadow-inner shadow-black/10">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-xl sm:text-2xl font-black leading-tight">
                                    {{ $order->order_no ?? 'ORD-' . $order->id }}
                                </h2>
	                                <span
	                                    class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $statusColor }}">
	                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
	                                    {{ strtoupper(str_replace('_', ' ', $status)) }}
	                                </span>
	                                <span
	                                    class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $paymentColor }}">
	                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
	                                    {{ strtoupper(str_replace('_', ' ', $paymentStatus)) }}
	                                </span>
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-white/80">
                                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">
                                    Order ID: <span class="font-semibold text-white">#{{ $order->id }}</span>
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">
                                    WG: <span class="font-semibold text-white">{{ $order->working_group_id }}</span>
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">
                                    Customer: <span class="font-semibold text-white">{{ $customerName }}</span>
                                </span>
                                @if ($customerPhone)
                                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">
                                        {{ $customerPhone }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

	                {{-- Actions --}}
	                <div class="flex flex-wrap items-center gap-2 pt-2 lg:pt-0">
	                    @can('changeStatus', $order)
	                        <div class="relative"
	                            x-data="{
	                                open: false,
	                                loading: false,
	                                loaded: false,
	                                error: null,
	                                next: [],
	                                from: @js($status),
	                                shippingMethod: @js($shippingMethod),
	                                finalInvoiceIssued: @js($finalInvoiceIssued),
	                                status: '',
	                                async toggle() {
	                                    this.open = !this.open;
	                                    if (this.open && !this.loaded) await this.load();
	                                },
	                                async load() {
	                                    this.loading = true;
	                                    this.error = null;
	                                    try {
	                                        const res = await fetch(@js(route('admin.orders.status-options', $order)), {
	                                            headers: { 'Accept': 'application/json' },
	                                        });
	                                        const data = await res.json().catch(() => ({}));
	                                        if (!res.ok || !data.ok) throw new Error(data?.message || 'Failed to load status options');

	                                        this.from = data.from || this.from;
	                                        this.shippingMethod = data.shipping_method || this.shippingMethod;
	                                        this.finalInvoiceIssued = !!data.final_invoice_issued;
	                                        this.next = Array.isArray(data.next_statuses) ? data.next_statuses : [];

	                                        this.loaded = true;
	                                    } catch (e) {
	                                        this.error = e?.message || 'Failed to load';
	                                    } finally {
	                                        this.loading = false;
	                                    }
	                                },
	                                get needsWhy() {
	                                    return this.finalInvoiceIssued || ['cancelled','refunded'].includes(this.status);
	                                },
	                                get showDeliveryFields() {
	                                    return this.shippingMethod === 'delivery' && this.status === 'out_for_delivery';
	                                },
	                                get showPickupNote() {
	                                    return this.shippingMethod === 'pickup' && this.status === 'completed';
	                                }
	                            }">
	                            <button type="button"
	                                @click="toggle()"
	                                @disabled($isTerminal)
	                                class="inline-flex items-center gap-2 rounded-2xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur hover:bg-white/20 transition-all disabled:opacity-60 disabled:hover:bg-white/10">
	                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
	                                    stroke="currentColor">
	                                    <path stroke-linecap="round" stroke-linejoin="round"
	                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
	                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125L16.862 4.487" />
	                                </svg>
	                                Update Status
	                                <svg class="h-4 w-4 opacity-90" fill="none" viewBox="0 0 24 24" stroke-width="2"
	                                    stroke="currentColor">
	                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
	                                </svg>
	                            </button>

	                            <div x-cloak x-show="open" @click.outside="open=false"
	                                x-transition:enter="transition ease-out duration-150"
	                                x-transition:enter-start="opacity-0 -translate-y-1"
	                                x-transition:enter-end="opacity-100 translate-y-0"
	                                x-transition:leave="transition ease-in duration-100"
	                                x-transition:leave-start="opacity-100 translate-y-0"
	                                x-transition:leave-end="opacity-0 -translate-y-1"
	                                class="absolute right-0 z-30 mt-2 w-[420px] max-w-[92vw] rounded-2xl border border-slate-200 bg-white p-4 text-slate-900 shadow-xl">
	                                <div class="flex items-start justify-between gap-3">
	                                    <div>
	                                        <div class="text-sm font-black">Change order status</div>
	                                        <div class="mt-1 text-xs text-slate-500">
	                                            Current: <span class="font-semibold text-slate-700" x-text="(from || '').replaceAll('_',' ')"></span>
	                                            <span class="text-slate-300">•</span>
	                                            Shipping: <span class="font-semibold text-slate-700" x-text="shippingMethod"></span>
	                                        </div>
	                                    </div>
	                                    <button type="button" @click="open=false" class="text-slate-400 hover:text-slate-700">
	                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
	                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
	                                        </svg>
	                                    </button>
	                                </div>

	                                <template x-if="loading">
	                                    <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">
	                                        Loading status options…
	                                    </div>
	                                </template>

	                                <template x-if="error">
	                                    <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-800" x-text="error"></div>
	                                </template>

	                                @if ($isTerminal)
	                                    <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
	                                        This order is in a terminal state (<span class="font-bold">{{ \Illuminate\Support\Str::headline($status) }}</span>) and cannot be changed.
	                                    </div>
	                                @else
	                                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-3 space-y-3">
	                                        @csrf

	                                        <div>
	                                            <label class="block text-xs font-semibold text-slate-600 mb-1">Next status</label>
	                                            <select name="status"
	                                                x-model="status"
	                                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
	                                                <option value="" disabled selected>Select status…</option>
	                                                <template x-for="st in next" :key="st">
	                                                    <option :value="st" x-text="st.replaceAll('_',' ')"></option>
	                                                </template>
	                                            </select>
	                                            <template x-if="loaded && !next.length">
	                                                <div class="mt-1 text-[11px] text-slate-500">No transitions available.</div>
	                                            </template>
	                                        </div>

	                                        <template x-if="finalInvoiceIssued">
	                                            <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
	                                                Final invoice already issued. Status change requires override permission + why.
	                                            </div>
	                                        </template>

	                                        <template x-if="showDeliveryFields">
	                                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
	                                                <div class="text-xs font-semibold text-slate-700">Delivery details</div>
	                                                <div class="mt-2 space-y-2">
	                                                    <div>
	                                                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Tracking no (optional)</label>
	                                                        <input name="tracking_no" type="text"
	                                                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"
	                                                            placeholder="Tracking number">
	                                                    </div>
	                                                    <div>
	                                                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Vehicle / rider note (optional)</label>
	                                                        <textarea name="vehicle_note" rows="2"
	                                                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"
	                                                            placeholder="Vehicle no, rider name, phone, etc."></textarea>
	                                                    </div>
	                                                    <div class="text-[11px] text-slate-500">
	                                                        For delivery, at least one of Tracking no or Vehicle note is required.
	                                                    </div>
	                                                </div>
	                                            </div>
	                                        </template>

	                                        <template x-if="showPickupNote">
	                                            <div>
	                                                <label class="block text-xs font-semibold text-slate-600 mb-1">Pickup note (optional)</label>
	                                                <textarea name="pickup_note" rows="2"
	                                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
	                                                    placeholder="Pickup confirmation note…"></textarea>
	                                            </div>
	                                        </template>

	                                        <div>
	                                            <label class="block text-xs font-semibold text-slate-600 mb-1">
	                                                Why <span class="text-slate-400">(required for Cancelled/Refunded and after invoice)</span>
	                                            </label>
	                                            <textarea name="why"
	                                                rows="3"
	                                                x-bind:required="needsWhy"
	                                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
	                                                placeholder="Reason for audit log…"></textarea>
	                                        </div>

	                                        <div class="flex items-center justify-end gap-2">
	                                            <button type="button" @click="open=false"
	                                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-extrabold text-slate-700 hover:bg-slate-50">
	                                                Cancel
	                                            </button>
	                                            <button type="submit"
	                                                x-bind:disabled="!status"
	                                                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-3 py-2 text-xs font-extrabold text-white hover:bg-slate-800 disabled:opacity-50">
	                                                Save
	                                            </button>
	                                        </div>
	                                    </form>
	                                @endif
	                            </div>
	                        </div>
	                    @endcan

		                    @can('createInvoice', $order)
		                        <form method="POST" action="{{ route('admin.invoices.from-order', $order) }}">
		                            @csrf
		                            <input type="hidden" name="type" value="final" />
                            <button
                                class="inline-flex items-center gap-2 rounded-2xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur hover:bg-white/20 transition-all">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                Create Invoice
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </section>

        {{-- KEY METRICS --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Estimated Total</p>
                        <p class="mt-2 text-2xl font-black text-indigo-600">
                            {{ $currency }} {{ number_format((float) $order->grand_total, 2) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            Live estimate. Final total is set when an invoice is issued.
                        </p>
                        @if ($finalInvoiceIssued)
                            <p class="mt-2 text-xs text-slate-700">
                                <span class="font-semibold">Final invoice:</span>
                                {{ $finalInvoice->invoice_no ?? 'INV-' . $finalInvoice->id }}
                                —
                                <span class="font-extrabold text-slate-900">
                                    {{ $currency }} {{ number_format((float) $finalInvoice->grand_total, 2) }}
                                </span>
                            </p>
                        @endif
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Items</p>
                <p class="mt-2 text-2xl font-black text-slate-900">
                    {{ $order->items->count() }}
                </p>
                <p class="mt-1 text-xs text-slate-500">Order line items</p>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Created</p>
                <p class="mt-2 text-base font-black text-slate-900">
                    {{ $order->created_at?->format('M d, Y') ?? '—' }}
                </p>
                <p class="mt-1 text-xs text-slate-500">{{ $order->created_at?->format('h:i A') ?? '—' }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Confirmed</p>
                <p class="mt-2 text-base font-black text-slate-900">
                    {{ $order->confirmed_at ? $order->confirmed_at->format('M d, Y') : 'Not yet' }}
                </p>
                <p class="mt-1 text-xs text-slate-500">{{ $order->confirmed_at?->format('h:i A') ?? '—' }}</p>
            </div>
        </section>

        {{-- MAIN GRID (Customer + Items + Invoices) --}}
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            {{-- CUSTOMER INFORMATION --}}
            <div class="lg:col-span-4">
                <section class="h-full rounded-3xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">Customer Information</h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 p-6">
                        <div>
                            <div class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Full
                                Name</div>
                            <div class="font-semibold text-slate-900">{{ $customerName }}</div>
                        </div>
                        <div>
                            <div class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Email
                                Address</div>
                            <div class="font-semibold text-slate-900 break-all">{{ $customerEmail ?: '—' }}</div>
                        </div>
                        <div>
                            <div class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Phone
                                Number</div>
                            <div class="font-semibold text-slate-900">{{ $customerPhone ?: '—' }}</div>
                        </div>
                        @if ($customerWhatsapp)
                            <div>
                                <div class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                    WhatsApp</div>
                                <div class="font-semibold text-slate-900">{{ $customerWhatsapp }}</div>
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            <div class="lg:col-span-4">
                <section class="h-full rounded-3xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">Delivery Details</h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 p-6">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-black text-slate-800">Delivery / Pickup</div>
                            <div
                                class="mt-2 inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-bold text-slate-700">
                                {{ strtoupper($shippingMethod ?: 'pickup') }}
                            </div>

                            @if ($shippingMethod === 'delivery')
                                <div class="mt-3 space-y-1 text-xs text-slate-700">
                                    <div class="font-semibold text-slate-900">
                                        {{ (string) ($shipping['line1'] ?? '—') }}
                                    </div>
                                    @if (!empty($shipping['line2'] ?? null))
                                        <div>{{ $shipping['line2'] }}</div>
                                    @endif
                                    <div class="text-slate-600">
                                        {{ (string) ($shipping['city'] ?? '') }}
                                        @if (!empty($shipping['district'] ?? null))
                                            <span class="text-slate-400">•</span> {{ $shipping['district'] }}
                                        @endif
                                        @if (!empty($shipping['postal_code'] ?? null))
                                            <span class="text-slate-400">•</span> {{ $shipping['postal_code'] }}
                                        @endif
                                    </div>
                                    @if (!empty($shipping['country'] ?? null))
                                        <div class="text-slate-600">{{ $shipping['country'] }}</div>
                                    @endif
                                </div>
                            @else
                                <div class="mt-3 text-xs text-slate-600">
                                    Customer will collect from Printair (pickup).
                                </div>
                            @endif
                        </div>
                    </div>
                </section>
            </div>

            <div class="lg:col-span-4">
                <section class="h-full rounded-3xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">Special Details</h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 p-6">
                        @if ($orderNotes)
                            <div>
                                <div class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                    Customer Notes</div>
                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-800 whitespace-pre-wrap">
                                    {{ $orderNotes }}
                                </div>
                            </div>
                        @endif

                        @if ($orderSource !== '')
                            <div class="text-xs text-slate-500">
                                Source: <span class="font-semibold text-slate-700">{{ $orderSource }}</span>
                            </div>
                        @endif

                        @if (count($orderMetaExtra) > 0)
                            <details class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <summary class="cursor-pointer text-xs font-black text-slate-800">Extra details
                                </summary>
                                <div class="mt-3 space-y-2 text-xs">
                                    @foreach ($orderMetaExtra as $k => $v)
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="text-slate-500">
                                                {{ \Illuminate\Support\Str::headline((string) $k) }}
                                            </div>
                                            <div class="min-w-0 break-words text-right font-semibold text-slate-900">
                                                @if (is_bool($v))
                                                    {{ $v ? 'Yes' : 'No' }}
                                                @elseif (is_scalar($v) || $v === null)
                                                    {{ $v ?? '—' }}
                                                @else
                                                    {{ json_encode($v) }}
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    </div>
                </section>
            </div>


            {{-- ORDER ITEMS --}}
            <div class="lg:col-span-12">
                <section class="h-full rounded-3xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">Order Items</h3>
                        </div>
                        <div
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                            <span
                                class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-200 text-[10px] font-bold">
                                {{ $order->items->count() }}
                            </span>
                            <span>item(s)</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr class="text-xs font-black uppercase tracking-wider text-slate-700">
                                    <th class="px-6 py-4 text-left">Item Details</th>
                                    <th class="px-6 py-4 text-right whitespace-nowrap">Quantity</th>
                                    <th class="px-6 py-4 text-right whitespace-nowrap">Line Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($order->items as $it)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4 align-top">
                                            @php
                                                $snapItem = is_array($it->pricing_snapshot)
                                                    ? $it->pricing_snapshot
                                                    : [];

                                                $variantLabel = $it->variantSetItem?->option?->label ?? null;
                                                $rollLabel = $it->roll?->name ?? null;

                                                $optionsMap = data_get($snapItem, 'input.options', []);
                                                $selectedOptions = [];
                                                if (is_array($optionsMap)) {
                                                    foreach ($optionsMap as $gid => $oid) {
                                                        $gidInt = is_numeric($gid) ? (int) $gid : 0;
                                                        $oidInt = is_numeric($oid) ? (int) $oid : 0;
                                                        if ($gidInt <= 0 || $oidInt <= 0) {
                                                            continue;
                                                        }

                                                        $selectedOptions[] = [
                                                            'group' =>
                                                                $optionGroupsById[$gidInt]->name ?? 'Group #' . $gidInt,
                                                            'option' =>
                                                                $optionsById[$oidInt]->label ?? 'Option #' . $oidInt,
                                                        ];
                                                    }
                                                }

                                                $artwork = data_get($snapItem, 'input.artwork', []);
                                                $artwork = is_array($artwork) ? $artwork : [];
                                                $artworkMode = (string) (data_get($artwork, 'mode') ?? '');
                                                $artworkBrief =
                                                    (string) (data_get($artwork, 'brief') ??
                                                        (data_get($artwork, 'design_brief') ?? ''));
                                                $artworkUrl =
                                                    (string) (data_get($artwork, 'external_url') ??
                                                        (data_get($snapItem, 'meta.artwork_external_url') ?? ''));

                                                $artworkFiles = data_get($snapItem, 'meta.artwork_files', []);
                                                $artworkFiles = is_array($artworkFiles) ? $artworkFiles : [];
                                            @endphp

                                            <div class="font-bold text-slate-900">
                                                {{ $it->title ?? ($it->product?->name ?? 'Product #' . $it->product_id) }}
                                            </div>

                                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                                @if ($it->width && $it->height)
                                                    <div
                                                        class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2 py-1 text-xs text-slate-600">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                                        </svg>
                                                        {{ $it->width }} × {{ $it->height }} {{ $it->unit }}
                                                    </div>
                                                @endif

                                                @if ($variantLabel)
                                                    <span
                                                        class="inline-flex items-center rounded-lg bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                                        Variant: {{ $variantLabel }}
                                                    </span>
                                                @endif

                                                @if ($rollLabel)
                                                    <span
                                                        class="inline-flex items-center rounded-lg bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                                        Roll: {{ $rollLabel }}
                                                    </span>
                                                @endif

                                                @foreach ($selectedOptions as $opt)
                                                    <span
                                                        class="inline-flex items-center rounded-lg bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                                        {{ $opt['group'] }}: {{ $opt['option'] }}
                                                    </span>
                                                @endforeach
                                            </div>

                                            @if ($it->finishings?->count())
                                                <div class="mt-3 space-y-1.5 rounded-lg bg-slate-50 p-3">
                                                    <div
                                                        class="flex items-center gap-1.5 text-xs font-semibold text-slate-700">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
                                                        </svg>
                                                        Finishings
                                                    </div>
                                                    @foreach ($it->finishings as $f)
                                                        <div class="flex items-center justify-between gap-3 text-xs">
                                                            <div class="min-w-0 truncate text-slate-700">
                                                                {{ $f->label ?? ($f->option?->label ?? ($f->finishingProduct?->name ?? 'Finishing')) }}
                                                                <span class="text-slate-500">×
                                                                    {{ $f->qty }}</span>
                                                            </div>
                                                            <div
                                                                class="font-semibold text-slate-900 whitespace-nowrap">
                                                                {{ $currency }}
                                                                {{ number_format((float) $f->total, 2) }}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if ($artworkMode !== '' || $artworkUrl !== '' || $artworkBrief !== '' || count($artworkFiles) > 0)
                                                <div class="mt-3 rounded-lg border border-slate-200 bg-white p-3">
                                                    <div class="text-xs font-semibold text-slate-700">Artwork</div>

                                                    <div class="mt-2 grid grid-cols-1 gap-2 text-xs">
                                                        @if ($artworkMode !== '')
                                                            <div class="flex items-center justify-between gap-3">
                                                                <div class="text-slate-500">Mode</div>
                                                                <div class="font-semibold text-slate-900">
                                                                    {{ strtoupper($artworkMode) }}</div>
                                                            </div>
                                                        @endif

                                                        @if ($artworkUrl !== '')
                                                            <div class="flex items-start justify-between gap-3">
                                                                <div class="text-slate-500">Link</div>
                                                                <a href="{{ $artworkUrl }}" target="_blank"
                                                                    rel="noopener"
                                                                    class="min-w-0 break-all font-semibold text-indigo-600 hover:underline">
                                                                    {{ $artworkUrl }}
                                                                </a>
                                                            </div>
                                                        @endif

                                                        @if ($artworkBrief !== '')
                                                            <div>
                                                                <div class="text-slate-500">Brief</div>
                                                                <div class="mt-1 whitespace-pre-wrap text-slate-800">
                                                                    {{ $artworkBrief }}</div>
                                                            </div>
                                                        @endif

                                                        @if (count($artworkFiles) > 0)
                                                            <div>
                                                                <div class="text-slate-500">Uploaded files</div>
                                                                <div class="mt-1 space-y-1">
                                                                    @foreach ($artworkFiles as $af)
                                                                        @php
                                                                            $disk = (string) ($af['disk'] ?? 'public');
                                                                            $path = (string) ($af['path'] ?? '');
                                                                            $name = (string) ($af['name'] ?? $path);

                                                                            $url = null;
                                                                            if (
                                                                                $path !== '' &&
                                                                                config("filesystems.disks.{$disk}")
                                                                            ) {
                                                                                $url = \Illuminate\Support\Facades\Storage::disk(
                                                                                    $disk,
                                                                                )->url($path);
                                                                            }
                                                                        @endphp
                                                                        <div
                                                                            class="flex items-center justify-between gap-3">
                                                                            <div
                                                                                class="min-w-0 truncate text-slate-700">
                                                                                {{ $name !== '' ? $name : 'File' }}
                                                                            </div>
                                                                            @if ($url)
                                                                                <a href="{{ $url }}"
                                                                                    target="_blank" rel="noopener"
                                                                                    class="whitespace-nowrap text-xs font-semibold text-indigo-600 hover:underline">
                                                                                    Open
                                                                                </a>
                                                                            @else
                                                                                <div
                                                                                    class="whitespace-nowrap text-[11px] text-slate-500">
                                                                                    {{ $disk }}:{{ $path }}
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right align-top">
                                            <span
                                                class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-2.5 py-1 text-sm font-bold text-slate-700 whitespace-nowrap">
                                                {{ (int) $it->qty }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right align-top">
                                            <div class="text-base font-extrabold text-slate-900 whitespace-nowrap">
                                                {{ $currency }} {{ number_format((float) $it->line_total, 2) }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-50 border-t-2 border-slate-300">
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-right text-sm font-black text-slate-900">
                                        Estimated Total:</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="text-xl font-black text-indigo-600 whitespace-nowrap">
                                            {{ $currency }} {{ number_format((float) $order->grand_total, 2) }}
                                        </div>
                                    </td>
                                </tr>
                                @if ($finalInvoiceIssued)
                                    <tr>
                                        <td colspan="2"
                                            class="px-6 py-3 text-right text-sm font-black text-slate-900">Final
                                            Invoice Total:</td>
                                        <td class="px-6 py-3 text-right">
                                            <div class="text-lg font-black text-slate-900 whitespace-nowrap">
                                                {{ $currency }}
                                                {{ number_format((float) $finalInvoice->grand_total, 2) }}
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </section>
            </div>

            {{-- RELATED INVOICES --}}
            <div class="lg:col-span-12">
                <section class="h-full rounded-3xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">Related Invoices</h3>
                        </div>
                        <a href="{{ route('admin.invoices.index') }}"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                            View all
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr class="text-xs font-black uppercase tracking-wider text-slate-700">
                                    <th class="px-6 py-4 text-left">Invoice</th>
                                    <th class="px-6 py-4 text-left">Status</th>
                                    <th class="px-6 py-4 text-right whitespace-nowrap">Total</th>
                                    <th class="px-6 py-4 text-right whitespace-nowrap">Due</th>
                                    <th class="px-6 py-4 text-right whitespace-nowrap">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($order->invoices as $inv)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-semibold text-slate-900">
                                                {{ $inv->invoice_no ?? 'INV-' . $inv->id }}</div>
                                            <div class="mt-0.5 text-xs text-slate-500">
                                                {{ strtoupper((string) $inv->type) }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $invStatusColors = [
                                                    'draft' => 'bg-slate-100 border-slate-300 text-slate-700',
                                                    'issued' => 'bg-sky-100 border-sky-300 text-sky-700',
                                                    'paid' => 'bg-emerald-100 border-emerald-300 text-emerald-700',
                                                    'partially_paid' => 'bg-amber-100 border-amber-300 text-amber-700',
                                                    'overdue' => 'bg-rose-100 border-rose-300 text-rose-700',
                                                    'cancelled' => 'bg-slate-100 border-slate-300 text-slate-700',
                                                ];
                                                $invColorClass =
                                                    $invStatusColors[$inv->status] ??
                                                    'bg-slate-100 border-slate-300 text-slate-700';
                                            @endphp
                                            <span
                                                class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $invColorClass }}">
                                                {{ strtoupper((string) $inv->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="font-extrabold text-slate-900 whitespace-nowrap">
                                                {{ $currency }} {{ number_format((float) $inv->grand_total, 2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div
                                                class="font-extrabold whitespace-nowrap {{ $inv->amount_due > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                                {{ $currency }} {{ number_format((float) $inv->amount_due, 2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('admin.invoices.show', $inv) }}"
                                                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-emerald-600 hover:bg-emerald-50 transition-colors whitespace-nowrap">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-16 text-center">
                                            <div class="flex flex-col items-center gap-2">
                                                <div
                                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                    </svg>
                                                </div>
                                                <p class="text-sm font-medium text-slate-900">No invoices yet</p>
                                                <p class="text-xs text-slate-500">Create an invoice for this order
                                                    using the action button above</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </section>

        {{-- STATUS + SYSTEM --}}
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-8">
                <section class="h-full rounded-3xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <h3 class="text-lg font-bold text-slate-900">Status History</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr class="text-xs font-black uppercase tracking-wider text-slate-700">
                                    <th class="px-6 py-4 text-left">When</th>
                                    <th class="px-6 py-4 text-left">From</th>
                                    <th class="px-6 py-4 text-left">To</th>
                                    <th class="px-6 py-4 text-left">By</th>
                                    <th class="px-6 py-4 text-left">Reason</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($order->statusHistories as $h)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-semibold text-slate-900">
                                                {{ $h->created_at?->format('Y-m-d') ?? '—' }}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                {{ $h->created_at?->format('H:i') ?? '' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="text-xs font-semibold text-slate-700">{{ $h->from_status ?? '—' }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="text-xs font-semibold text-slate-900">{{ $h->to_status }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-slate-900 font-semibold">
                                                {{ $h->changedBy?->name ?? '—' }}</div>
                                        </td>
	                                        <td class="px-6 py-4">
	                                            <div class="text-slate-700 whitespace-pre-wrap">{{ $h->why ?? $h->reason ?? '—' }}</div>
	                                            @if (!empty($h->tracking_no ?? null))
	                                                <div class="mt-1 text-xs text-slate-500">Tracking: {{ $h->tracking_no }}</div>
	                                            @endif
	                                            @if (!empty($h->vehicle_note ?? null))
	                                                <div class="mt-1 text-xs text-slate-500 whitespace-pre-wrap">Vehicle: {{ $h->vehicle_note }}</div>
	                                            @endif
	                                            @if (!empty($h->pickup_note ?? null))
	                                                <div class="mt-1 text-xs text-slate-500 whitespace-pre-wrap">Pickup: {{ $h->pickup_note }}</div>
	                                            @endif
	                                        </td>
	                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">
                                            No status changes recorded.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="lg:col-span-4">
                <section class="h-full rounded-3xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <h3 class="text-lg font-bold text-slate-900">System</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-4 p-6 text-sm">
                        <div>
                            <div class="mb-1 text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">UUID
                            </div>
                            <div class="font-semibold text-slate-900 break-all">{{ $order->uuid ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="mb-1 text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Created
                                By</div>
                            <div class="font-semibold text-slate-900">{{ $order->createdBy?->name ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="mb-1 text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Updated
                                By</div>
                            <div class="font-semibold text-slate-900">{{ $order->updatedBy?->name ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="mb-1 text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Locked
                                By</div>
                            <div class="font-semibold text-slate-900">{{ $order->lockedBy?->name ?? '—' }}</div>
                        </div>
                    </div>
                </section>
            </div>
        </section>
    </div>
</x-app-layout>
