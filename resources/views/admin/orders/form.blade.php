{{-- resources/views/admin/orders/form.blade.php --}}

<x-app-layout>
    <x-slot name="sectionTitle">Sales</x-slot>
    <x-slot name="pageTitle">{{ ($mode ?? 'edit') === 'create' ? 'New Order' : 'Edit Order' }}</x-slot>

    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.orders.index') }}" class="text-slate-500 hover:text-slate-700">Orders</a>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">{{ ($mode ?? 'edit') === 'create' ? 'Create' : 'Edit' }}</span>
    </x-slot>

    @php
        $currency = $order?->currency ?? 'LKR';
        $status = (string) ($order?->status ?? 'draft');

        $snap = is_array($order?->customer_snapshot) ? $order->customer_snapshot : [];
        if (!array_key_exists('full_name', $snap)) $snap['full_name'] = $snap['name'] ?? null;
        if (!array_key_exists('phone', $snap)) $snap['phone'] = $snap['whatsapp_number'] ?? ($snap['whatsapp'] ?? null);

        $initial = [
            'id' => $order?->id,
            'order_no' => $order?->order_no,
            'status' => $status,
            'working_group_id' => $order?->working_group_id ?? (auth()->user()?->working_group_id ?: 1),
            'currency' => $currency,
            'ordered_at' => optional($order?->ordered_at)->format('Y-m-d\\TH:i') ?? now()->format('Y-m-d\\TH:i'),
            'shipping_fee' => (float) ($order?->shipping_fee ?? 0),
            'other_fee' => (float) ($order?->other_fee ?? 0),
            'customer_id' => $order?->customer_id,
            'customer_snapshot' => $snap,
            'items' => ($order?->items ?? collect())->map(fn($it) => [
                'id' => (int) $it->id,
                'product_id' => (int) $it->product_id,
                'variant_set_item_id' => $it->variant_set_item_id ? (int) $it->variant_set_item_id : null,
                'roll_id' => $it->roll_id ? (int) $it->roll_id : null,
                'title' => (string) ($it->title ?? ''),
                'description' => $it->description,
                'qty' => (int) ($it->qty ?? 1),
                'width' => $it->width,
                'height' => $it->height,
                'unit' => $it->unit,
                'area_sqft' => $it->area_sqft,
                'offcut_sqft' => $it->offcut_sqft,
                'pricing_snapshot' => $it->pricing_snapshot,
                'unit_price' => (float) ($it->unit_price ?? 0),
                'line_subtotal' => (float) ($it->line_subtotal ?? 0),
                'discount_amount' => (float) ($it->discount_amount ?? 0),
                'tax_amount' => (float) ($it->tax_amount ?? 0),
                'line_total' => (float) ($it->line_total ?? 0),
                'finishings' => ($it->finishings ?? collect())->map(fn($f) => [
                    'id' => (int) $f->id,
                    'finishing_product_id' => (int) $f->finishing_product_id,
                    'label' => $f->label ?? $f->finishingProduct?->name ?? ('Finishing #' . $f->id),
                    'qty' => (int) ($f->qty ?? 1),
                    'unit_price' => (float) ($f->unit_price ?? 0),
                    'total' => (float) ($f->total ?? 0),
                ])->values()->all(),
            ])->values()->all(),
        ];

        $workingGroupsForJs = ($workingGroups ?? collect())->map(fn($wg) => [
            'id' => $wg->id,
            'name' => $wg->name,
        ])->values()->all();

        $customersForJs = ($customers ?? collect())->map(fn($c) => [
            'id' => $c->id,
            'full_name' => $c->full_name,
            'phone' => $c->phone,
            'email' => $c->email,
            'working_group_id' => $c->working_group_id,
        ])->values()->all();

        $productsForJs = ($products ?? collect())->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'code' => $p->product_code,
        ])->values()->all();

        $finishingsForJs = ($finishings ?? collect())->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'code' => $p->product_code,
        ])->values()->all();
    @endphp

    <div
        x-data="orderForm({
            locked: @js(false),
            initial: @js($initial),
            saveUrl: @js(route('admin.orders.update', $order ?? 0)),
            previewUrl: @js($order ? route('admin.orders.show', $order) : null),
            adminOrdersBaseUrl: @js(url('/admin/orders')),
            workingGroups: @js($workingGroupsForJs),
            customers: @js($customersForJs),
            products: @js($productsForJs),
            finishings: @js($finishingsForJs),
        })"
        x-init="init()"
        class="space-y-6"
    >
        <section class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Order</div>
                    <div class="mt-1 text-xl font-black text-slate-900" x-text="state.order_no || (state.id ? ('ORD-' + state.id) : 'New order')"></div>
                    <div class="mt-1 text-xs text-slate-500">
                        Editable until an invoice is <span class="font-semibold">issued</span>.
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="addItem()"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-md shadow-black/10 hover:shadow-lg hover:-translate-y-0.5 transition-all">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add Item
                    </button>

                    <button type="button" @click="save('stay')" :disabled="saving"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50">
                        <span x-show="!saving">Save</span>
                        <span x-show="saving">Saving…</span>
                    </button>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <section class="space-y-6 xl:col-span-1">
                <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900">Order meta</h3>
                    <div class="mt-4 grid grid-cols-1 gap-3">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Working group</label>
                            <select x-model.number="state.working_group_id" disabled
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm disabled:opacity-60">
                                <template x-for="wg in workingGroups" :key="wg.id">
                                    <option :value="wg.id" x-text="wg.name"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Currency</label>
                            <input type="text" x-model="state.currency"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Ordered at</label>
                            <input type="datetime-local" x-model="state.ordered_at"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Shipping fee</label>
                                <input type="number" min="0" step="0.01" x-model.number="state.shipping_fee"
                                    class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Other fee</label>
                                <input type="number" min="0" step="0.01" x-model.number="state.other_fee"
                                    class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900">Customer</h3>
                    <div class="mt-4 grid grid-cols-1 gap-3">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Existing customer (optional)</label>
                            <select x-model.number="state.customer_id"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                                <option value="">— Snapshot only —</option>
                                <template x-for="c in customers" :key="c.id">
                                    <option :value="c.id" x-text="`${c.full_name} (${c.phone ?? '—'})`"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Full name</label>
                            <input type="text" x-model="state.customer_snapshot.full_name"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Phone</label>
                            <input type="text" x-model="state.customer_snapshot.phone"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Email</label>
                            <input type="email" x-model="state.customer_snapshot.email"
                                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Estimated Total</div>
                    <div class="mt-2 text-2xl font-black text-slate-900">
                        <span x-text="state.currency"></span>
                        <span x-text="formatMoney(grandTotal())"></span>
                    </div>
                    <div class="mt-1 text-xs text-slate-500">Finishings are included.</div>
                </div>
            </section>

            <section class="xl:col-span-2">
                <div class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Items</h3>
                                <p class="mt-1 text-xs text-slate-500">Products and finishings (stored separately).</p>
                            </div>
                            <div class="text-xs text-slate-500">
                                <span x-text="state.items.length"></span> item(s)
                            </div>
                        </div>
                    </div>

                    <template x-if="state.items.length === 0">
                        <div class="px-6 py-10 text-center text-sm text-slate-600">
                            Add at least one item.
                        </div>
                    </template>

                    <div class="divide-y divide-slate-100">
                        <template x-for="(it, idx) in state.items" :key="it._key">
                            <div class="px-6 py-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-sm font-extrabold text-slate-900" x-text="it.title || 'Item'"></div>
                                        <div class="mt-1 text-xs text-slate-500">
                                            Line total:
                                            <span class="font-bold text-slate-900">
                                                <span x-text="state.currency"></span>
                                                <span x-text="formatMoney(itemTotalWithFinishings(it))"></span>
                                            </span>
                                        </div>
                                    </div>
                                    <button type="button" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50"
                                        @click="removeItem(idx)">
                                        Remove
                                    </button>
                                </div>

                                <div class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-12">
                                    <div class="lg:col-span-6">
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Product</label>
                                        <select x-model.number="it.product_id" @change="onProductChanged(it)"
                                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                                            <option value="">— Select —</option>
                                            <template x-for="p in products" :key="p.id">
                                                <option :value="p.id" x-text="p.name"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <div class="lg:col-span-3">
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Qty</label>
                                        <input type="number" min="1" step="1" x-model.number="it.qty" @input="recalcItem(it)"
                                            class="block w-full rounded-2xl border border-slate-200 bg-white py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10">
                                    </div>

                                    <div class="lg:col-span-3">
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Unit price</label>
                                        <input type="number" min="0" step="0.01" x-model.number="it.unit_price" @input="recalcItem(it)"
                                            class="block w-full rounded-2xl border border-slate-200 bg-white py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10">
                                    </div>

                                    <div class="lg:col-span-12">
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Title</label>
                                        <input type="text" x-model="it.title"
                                            class="block w-full rounded-2xl border border-slate-200 bg-white py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10">
                                    </div>

                                    <div class="lg:col-span-12">
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Description</label>
                                        <textarea rows="2" x-model="it.description"
                                            class="block w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10"></textarea>
                                    </div>
                                </div>

                                <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-xs font-black text-slate-900">Finishings</div>
                                            <div class="mt-1 text-[11px] text-slate-500">Stored separately from product pricing.</div>
                                        </div>
                                        <button type="button"
                                            class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-900 shadow-sm hover:bg-slate-50"
                                            @click="addFinishing(it)">
                                            Add finishing
                                        </button>
                                    </div>

                                    <template x-if="(it.finishings || []).length === 0">
                                        <div class="mt-3 text-xs text-slate-500">No finishings.</div>
                                    </template>

                                    <div class="mt-4 space-y-3">
                                        <template x-for="(f, fIdx) in (it.finishings || [])" :key="f._key">
                                            <div class="rounded-2xl border border-slate-200 bg-white p-4" :class="f.remove ? 'border-rose-200 bg-rose-50/40' : ''">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-extrabold text-slate-900 truncate" x-text="f.label || 'Finishing'"></div>
                                                        <div class="mt-1 text-[11px] text-slate-500">
                                                            Total:
                                                            <span class="font-bold text-slate-900">
                                                                <span x-text="state.currency"></span>
                                                                <span x-text="formatMoney(finishingTotal(f))"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-rose-700">
                                                        <input type="checkbox" class="rounded border-slate-300" x-model="f.remove">
                                                        Remove
                                                    </label>
                                                </div>

                                                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                                    <div class="lg:col-span-2">
                                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Finishing product</label>
                                                        <select x-model.number="f.finishing_product_id" @change="onFinishingChanged(f)"
                                                            :disabled="f.remove"
                                                            class="block w-full rounded-2xl border border-slate-200 bg-white py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60">
                                                            <option value="">— Select —</option>
                                                            <template x-for="fp in finishings" :key="fp.id">
                                                                <option :value="fp.id" x-text="fp.name"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Qty</label>
                                                        <input type="number" min="1" step="1" x-model.number="f.qty" :disabled="f.remove"
                                                            class="block w-full rounded-2xl border border-slate-200 bg-white py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60" />
                                                    </div>
                                                    <div>
                                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Unit price</label>
                                                        <input type="number" min="0" step="0.01" x-model.number="f.unit_price" :disabled="f.remove"
                                                            class="block w-full rounded-2xl border border-slate-200 bg-white py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60" />
                                                    </div>
                                                    <div class="lg:col-span-2">
                                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Label</label>
                                                        <input type="text" x-model="f.label" :disabled="f.remove"
                                                            class="block w-full rounded-2xl border border-slate-200 bg-white py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 disabled:opacity-60" />
                                                    </div>
                                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                                        <div class="text-[11px] text-slate-500">Total</div>
                                                        <div class="mt-1 text-sm font-extrabold text-slate-900">
                                                            <span x-text="state.currency"></span>
                                                            <span x-text="formatMoney(finishingTotal(f))"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @push('scripts')
        <script>
            function orderForm({ locked, initial, saveUrl, previewUrl, adminOrdersBaseUrl, workingGroups, customers, products, finishings }) {
                const uuid = () => (globalThis.crypto?.randomUUID?.() ?? (Date.now().toString(16) + Math.random().toString(16).slice(2)));

                return {
                    locked,
                    saveUrl,
                    previewUrl,
                    adminOrdersBaseUrl,
                    workingGroups,
                    customers,
                    products,
                    finishings,
                    saving: false,

                    state: {
                        ...initial,
                        items: (initial.items || []).map(i => ({
                            ...i,
                            _key: uuid(),
                            finishings: (i.finishings || []).map(f => ({
                                ...f,
                                _key: uuid(),
                                remove: false,
                            })),
                        })),
                        customer_snapshot: initial.customer_snapshot || {},
                    },

                    init() {
                        (this.state.items || []).forEach(it => this.recalcItem(it));
                    },

                    formatMoney(v) {
                        const n = Number(v || 0);
                        return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    },

                    addItem() {
                        this.state.items.push({
                            id: null,
                            product_id: null,
                            variant_set_item_id: null,
                            roll_id: null,
                            title: '',
                            description: null,
                            qty: 1,
                            width: null,
                            height: null,
                            unit: null,
                            area_sqft: null,
                            offcut_sqft: 0,
                            pricing_snapshot: null,
                            unit_price: 0,
                            line_subtotal: 0,
                            discount_amount: 0,
                            tax_amount: 0,
                            line_total: 0,
                            finishings: [],
                            _key: uuid(),
                        });
                    },

                    removeItem(idx) {
                        this.state.items.splice(idx, 1);
                    },

                    onProductChanged(it) {
                        const p = this.products.find(x => Number(x.id) === Number(it.product_id));
                        if (p && !it.title) it.title = p.name;
                        this.recalcItem(it);
                    },

                    recalcItem(it) {
                        const qty = Math.max(1, Number(it.qty || 1));
                        it.qty = qty;

                        const unit = Math.max(0, Number(it.unit_price || 0));
                        const subtotal = unit * qty;
                        it.line_subtotal = Math.max(0, subtotal);

                        const discount = Math.max(0, Number(it.discount_amount || 0));
                        it.discount_amount = Math.min(discount, it.line_subtotal);

                        const tax = Math.max(0, Number(it.tax_amount || 0));
                        it.tax_amount = tax;

                        it.line_total = Math.max(0, it.line_subtotal - it.discount_amount + it.tax_amount);
                    },

                    addFinishing(it) {
                        it.finishings = it.finishings || [];
                        it.finishings.push({
                            id: 0,
                            finishing_product_id: null,
                            label: '',
                            qty: 1,
                            unit_price: 0,
                            remove: false,
                            _key: uuid(),
                        });
                    },

                    onFinishingChanged(f) {
                        const fp = this.finishings.find(x => Number(x.id) === Number(f.finishing_product_id));
                        if (fp && !f.label) f.label = fp.name;
                    },

                    finishingTotal(f) {
                        if (f.remove) return 0;
                        const qty = Math.max(1, Number(f.qty || 1));
                        const unit = Math.max(0, Number(f.unit_price || 0));
                        return qty * unit;
                    },

                    finishingsTotal(it) {
                        return (it.finishings || []).reduce((sum, f) => sum + this.finishingTotal(f), 0);
                    },

                    itemTotalWithFinishings(it) {
                        return Number(it.line_total || 0) + this.finishingsTotal(it);
                    },

                    itemsSubtotal() {
                        return (this.state.items || []).reduce((sum, it) => sum + this.itemTotalWithFinishings(it), 0);
                    },

                    grandTotal() {
                        return this.itemsSubtotal()
                            + Math.max(0, Number(this.state.shipping_fee || 0))
                            + Math.max(0, Number(this.state.other_fee || 0));
                    },

                    async save(next) {
                        this.saving = true;

                        const missingProduct = (this.state.items || []).find(it => !it.product_id);
                        if (missingProduct) {
                            alert('Please select a product for every item.');
                            this.saving = false;
                            return;
                        }

                        const payload = {
                            customer_id: this.state.customer_id || null,
                            customer_snapshot: this.state.customer_snapshot || {},
                            currency: this.state.currency || 'LKR',
                            ordered_at: this.state.ordered_at || null,
                            shipping_fee: Number(this.state.shipping_fee || 0),
                            other_fee: Number(this.state.other_fee || 0),
                            items: (this.state.items || []).map((i, idx) => ({
                                id: i.id || null,
                                product_id: Number(i.product_id),
                                variant_set_item_id: i.variant_set_item_id ? Number(i.variant_set_item_id) : null,
                                roll_id: i.roll_id ? Number(i.roll_id) : null,
                                title: i.title || '',
                                description: i.description || null,
                                qty: Number(i.qty || 1),
                                width: i.width === null || i.width === '' ? null : Number(i.width),
                                height: i.height === null || i.height === '' ? null : Number(i.height),
                                unit: i.unit || null,
                                area_sqft: i.area_sqft === null || i.area_sqft === '' ? null : Number(i.area_sqft),
                                offcut_sqft: Number(i.offcut_sqft || 0),
                                pricing_snapshot: i.pricing_snapshot || null,
                                unit_price: Number(i.unit_price || 0),
                                line_subtotal: Number(i.line_subtotal || 0),
                                discount_amount: Number(i.discount_amount || 0),
                                tax_amount: Number(i.tax_amount || 0),
                                line_total: Number(i.line_total || 0),
                                finishings: (i.finishings || []).map(f => ({
                                    id: Number(f.id || 0),
                                    finishing_product_id: f.finishing_product_id ? Number(f.finishing_product_id) : null,
                                    label: f.label || null,
                                    remove: !!f.remove,
                                    qty: Number(f.qty || 1),
                                    unit_price: Number(f.unit_price || 0),
                                })),
                                sort_order: idx,
                            })),
                        };

                        try {
                            const res = await fetch(this.saveUrl, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify(payload),
                            });

                            const data = await res.json().catch(() => ({}));
                            if (!res.ok || !data?.ok) throw new Error(data?.message || 'Save failed');

                            const id = data?.id ?? this.state.id;
                            if (id) this.state.id = id;

                            if (next === 'stay' && data?.edit_url) {
                                window.location.href = data.edit_url;
                                return;
                            }

                            window.location.href = data?.redirect_url || this.previewUrl || `${this.adminOrdersBaseUrl}/${id}`;
                        } catch (e) {
                            alert(e?.message || 'Save failed');
                        } finally {
                            this.saving = false;
                        }
                    },
                }
            }
        </script>
    @endpush
</x-app-layout>

