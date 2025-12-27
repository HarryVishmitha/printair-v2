<x-guest-layout :seo="[
    'title' => 'Order Status | Printair',
    'description' => 'Secure order tracking and details.',
    'keywords' => 'printair order tracking, order status',
    'canonical' => url()->current(),
    'image' => asset('assets/printair/printairlogo.png'),
]">
    <div
        x-data="secureOrderPage({ jsonUrl: @js($jsonUrl) })"
        x-init="init()"
        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
    >
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="iconify text-slate-700" data-icon="mdi:clipboard-text-outline"></span>
                    Secure Order
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    This page is protected. Only the owner of this secure link can view the order.
                </p>
            </div>

            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold text-slate-700">
                <span class="iconify" data-icon="mdi:shield-lock-outline"></span>
                Protected Link
            </span>
        </div>

        <template x-if="loading">
            <div class="mt-6 space-y-3">
                <div class="h-6 w-1/3 rounded bg-slate-100 animate-pulse"></div>
                <div class="h-4 w-2/3 rounded bg-slate-100 animate-pulse"></div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4">
                    <div class="h-20 rounded-2xl bg-slate-100 animate-pulse"></div>
                    <div class="h-20 rounded-2xl bg-slate-100 animate-pulse"></div>
                    <div class="h-20 rounded-2xl bg-slate-100 animate-pulse"></div>
                </div>
                <div class="h-52 rounded-2xl bg-slate-100 animate-pulse"></div>
            </div>
        </template>

        <template x-if="error">
            <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <div class="font-extrabold flex items-center gap-2">
                    <span class="iconify" data-icon="mdi:alert-circle-outline"></span>
                    Unable to load order
                </div>
                <div class="mt-1" x-text="error"></div>
            </div>
        </template>

        <template x-if="order">
            <div class="mt-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="text-xs text-slate-500">Order No</div>
                        <div class="mt-1 text-sm font-extrabold text-slate-900" x-text="order.order_no ?? ('ORD-' + order.id)"></div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="text-xs text-slate-500">Status</div>
                        <div class="mt-1 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-800">
                            <span class="iconify" data-icon="mdi:progress-check"></span>
                            <span x-text="(order.status || 'draft').replaceAll('_',' ')"></span>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="text-xs text-slate-500">Payment</div>
                        <div class="mt-1 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-800">
                            <span class="iconify" data-icon="mdi:credit-card-outline"></span>
                            <span x-text="(order.payment_status || 'unpaid').replaceAll('_',' ')"></span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                            <span class="iconify text-slate-700" data-icon="mdi:account-outline"></span>
                            Customer
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                        <div>
                            <div class="text-xs text-slate-500">Name</div>
                            <div class="font-semibold text-slate-900" x-text="order.customer_name ?? '-'"></div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Email</div>
                            <div class="font-semibold text-slate-900 break-all" x-text="order.customer_email ?? '-'"></div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">WhatsApp</div>
                            <div class="font-semibold text-slate-900" x-text="order.customer_whatsapp_number ?? '-'"></div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                            <span class="iconify text-slate-700" data-icon="mdi:shopping-outline"></span>
                            Items
                        </div>
                        <div class="text-xs text-slate-500" x-text="(order.items?.length || 0) + ' item(s)'"></div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <template x-for="it in (order.items || [])" :key="it.id">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="font-extrabold text-slate-900 truncate"
                                            x-text="it.product?.name ?? ('Product #' + it.product_id)"></div>

                                        <div class="mt-1 text-xs text-slate-500 flex flex-wrap gap-x-3 gap-y-1">
                                            <span class="inline-flex items-center gap-1">
                                                <span class="iconify" data-icon="mdi:counter"></span>
                                                Qty: <span class="font-semibold text-slate-700" x-text="it.qty"></span>
                                            </span>

                                            <template x-if="it.width && it.height">
                                                <span class="inline-flex items-center gap-1">
                                                    <span class="iconify" data-icon="mdi:ruler-square"></span>
                                                    <span x-text="it.width + ' x ' + it.height + ' ' + (it.unit || '')"></span>
                                                </span>
                                            </template>

                                            <template x-if="it.meta?.artwork_external_url">
                                                <span class="inline-flex items-center gap-1">
                                                    <span class="iconify" data-icon="mdi:link-variant"></span>
                                                    <a class="font-semibold text-[#ef233c] hover:underline break-all"
                                                        :href="it.meta.artwork_external_url" target="_blank" rel="noopener">
                                                        Artwork link
                                                    </a>
                                                </span>
                                            </template>
                                        </div>

                                        <template x-if="it.meta?.artwork_files?.length">
                                            <div class="mt-2 text-xs text-slate-600">
                                                <div class="font-bold text-slate-700 flex items-center gap-2">
                                                    <span class="iconify" data-icon="mdi:file-multiple-outline"></span>
                                                    Uploaded files
                                                </div>
                                                <ul class="mt-1 list-disc ml-5">
                                                    <template x-for="f in it.meta.artwork_files" :key="f.path">
                                                        <li class="break-all" x-text="f.name || f.path"></li>
                                                    </template>
                                                </ul>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="text-right">
                                        <div class="text-[11px] text-slate-500">Line Total</div>
                                        <div class="text-sm font-extrabold text-slate-900">
                                            <span x-text="money(it.total ?? 0)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="iconify text-slate-700" data-icon="mdi:calculator-variant-outline"></span>
                        Summary
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Subtotal</span>
                            <span class="font-semibold text-slate-900" x-text="money(order.subtotal ?? 0)"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Discount</span>
                            <span class="font-semibold text-slate-900" x-text="money(order.discount_total ?? 0)"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Tax</span>
                            <span class="font-semibold text-slate-900" x-text="money(order.tax_total ?? 0)"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Delivery</span>
                            <span class="font-semibold text-slate-900" x-text="money(order.shipping_fee ?? 0)"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Other</span>
                            <span class="font-semibold text-slate-900" x-text="money(order.other_fee ?? 0)"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-bold">Total</span>
                            <span class="font-extrabold text-slate-900" x-text="money(order.total ?? 0)"></span>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600 flex items-start gap-2">
                        <span class="iconify mt-[1px]" data-icon="mdi:information-outline"></span>
                        <div>
                            Totals may update after admin review and invoice creation.
                            You’ll receive an email once the invoice is generated.
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>

    <script>
        function secureOrderPage({ jsonUrl }) {
            return {
                jsonUrl,
                loading: true,
                error: null,
                order: null,

                async init() {
                    await this.load();
                },

                money(v) {
                    const n = Number(v || 0);
                    return 'LKR ' + n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                normalize(order) {
                    const snap = order?.customer_snapshot || {};
                    order.customer_name = order.customer_name || snap.full_name || order.customer?.full_name || null;
                    order.customer_email = order.customer_email || snap.email || order.customer?.email || null;
                    order.customer_whatsapp_number = order.customer_whatsapp_number || snap.whatsapp_number || order.customer?.whatsapp_number || null;

                    order.total = order.total ?? order.grand_total ?? 0;

                    const items = Array.isArray(order.items) ? order.items : [];
                    for (const it of items) {
                        it.meta = it.meta || it.pricing_snapshot?.meta || {};
                        it.total = it.total ?? it.line_total ?? 0;
                    }

                    return order;
                },

                async load() {
                    this.loading = true;
                    this.error = null;

                    try {
                        const res = await fetch(this.jsonUrl, { headers: { 'Accept': 'application/json' } });
                        if (!res.ok) {
                            const txt = await res.text();
                            throw new Error(txt || 'Request failed');
                        }

                        const data = await res.json();
                        if (!data.ok) throw new Error(data.message || 'Failed');
                        this.order = this.normalize(data.order);
                    } catch (e) {
                        this.error = (e && e.message) ? e.message : 'Unable to load order';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</x-guest-layout>

