<x-guest-layout :seo="[
    'title' => 'Cart | Printair',
    'description' => 'Review your cart and submit your order for admin review.',
    'keywords' => 'printair cart, printair checkout',
    'canonical' => url('/cart'),
    'image' => asset('assets/printair/printairlogo.png'),
]">
    {{-- Iconify --}}
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>

    <div
        x-data="printairCart({
            csrf: @js(csrf_token()),
            jsonUrl: @js(route('cart.show') . '?json=1'),
            productQuoteBaseUrl: @js(url('/products')),
            initialToast: @js(session('toast')),
            endpoints: {
                saveUrlDb: @js(route('cart.items.artwork.url', ['item' => 0])),
                uploadDb: @js(route('cart.items.artwork.upload', ['item' => 0])),
                saveUrlGuest: @js(route('cart.guest.items.artwork.url')),
                updateDb: @js(route('cart.items.update', ['item' => 0])),
                deleteDb: @js(route('cart.items.delete', ['item' => 0])),
                updateGuest: @js(route('cart.guest.items.update')),
                deleteGuest: @js(route('cart.guest.items.delete')),
                checkout: @js(route('checkout.page')),
            },
            isAuthed: @js(auth()->check()),
        })"
        x-init="init()"
        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
    >
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="iconify text-slate-700" data-icon="mdi:cart-outline"></span>
                    Cart
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Prices shown here are live estimates. Admins will review your order and confirm pricing ASAP.
                </p>
            </div>

            <a :href="endpoints.checkout"
               @click.prevent="
                    if (loading) return;
                    if ((items || []).length === 0) return setToast('error', 'Your cart is empty. Please add products to cart first.');
                    window.location.href = endpoints.checkout;
               "
               class="inline-flex items-center gap-2 rounded-xl bg-[#ef233c] px-4 py-2 text-xs font-extrabold text-white hover:opacity-95">
                <span class="iconify" data-icon="mdi:cart-check"></span>
                Checkout
            </a>
        </div>

        <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-700 flex items-start gap-2">
            <span class="iconify mt-[1px]" data-icon="mdi:information-outline"></span>
            <div>
                <div class="font-extrabold text-slate-900">Live estimate only</div>
                <div class="mt-0.5 text-slate-600">
                    Final price may change after admin review (materials, roll fitting, artwork checks, finishing confirmations).
                </div>
            </div>
        </div>

        {{-- Alerts --}}
        <template x-if="toast.message">
            <div class="mt-5 rounded-2xl border px-4 py-3 text-sm"
                 :class="toast.type === 'success'
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                    : 'border-rose-200 bg-rose-50 text-rose-800'">
                <div class="flex items-start gap-2">
                    <span class="iconify mt-[2px]" :data-icon="toast.type === 'success' ? 'mdi:check-circle-outline' : 'mdi:alert-circle-outline'"></span>
                    <div x-text="toast.message"></div>
                </div>
            </div>
        </template>

        {{-- Loading --}}
        <template x-if="loading">
            <div class="mt-6 space-y-3">
                <div class="h-6 w-1/3 rounded bg-slate-100 animate-pulse"></div>
                <div class="h-24 rounded-2xl bg-slate-100 animate-pulse"></div>
                <div class="h-24 rounded-2xl bg-slate-100 animate-pulse"></div>
            </div>
        </template>

        {{-- Empty --}}
        <template x-if="!loading && items.length === 0">
            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-white">
                    <span class="iconify text-xl" data-icon="mdi:cart-off"></span>
                </div>
                <div class="mt-3 text-sm font-extrabold text-slate-900">Your cart is empty</div>
                <div class="mt-1 text-xs text-slate-500">Add a product to continue.</div>
            </div>
        </template>

        {{-- Items --}}
        <template x-if="!loading && items.length">
            <div class="mt-6 space-y-4">
                <template x-for="it in items" :key="it.key">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-4 min-w-0">
                                <div class="h-20 w-20 rounded-2xl border border-slate-200 bg-slate-50 overflow-hidden shrink-0">
                                    <img x-show="it.primary_image_url" x-cloak :src="it.primary_image_url"
                                        class="h-full w-full object-cover" alt="" />
                                </div>

                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold text-slate-900 truncate" x-text="it.product_name"></div>

                                    <div class="mt-1 text-xs text-slate-500 flex flex-wrap gap-x-3 gap-y-1">
                                        <template x-if="it.width && it.height">
                                            <span class="inline-flex items-center gap-1">
                                                <span class="iconify" data-icon="mdi:ruler-square"></span>
                                                <span x-text="it.width + ' x ' + it.height + ' ' + (it.unit || '')"></span>
                                            </span>
                                        </template>
                                    </div>

                                    <div class="mt-3 flex items-center gap-3">
                                        <div>
                                            <div class="text-[11px] text-slate-500">Qty</div>
                                            <input type="number" min="1"
                                                class="mt-1 w-24 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                                x-model.number="it.qty"
                                                @input.debounce.350ms="queueRepriceAndSave(it)" />
                                        </div>

                                        <div class="flex-1">
                                            <div class="text-[11px] text-slate-500">Estimated total</div>
                                            <div class="mt-1 text-sm font-extrabold text-slate-900">
                                                <span x-text="money(it.pricing_total)"></span>
                                            </div>
                                            <template x-if="it.pricing_error">
                                                <div class="mt-1 text-[11px] text-rose-600" x-text="it.pricing_error"></div>
                                            </template>
                                        </div>

                                        <button type="button"
                                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-extrabold text-slate-700 hover:bg-slate-50 disabled:opacity-60"
                                            :disabled="it.busy_remove"
                                            @click="removeItem(it)">
                                            <span class="iconify" data-icon="mdi:trash-can-outline"></span>
                                            <span x-text="it.busy_remove ? 'Removing…' : 'Remove'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="text-[11px] text-slate-500">Snapshot</div>
                                <div class="text-xs font-bold text-slate-900" x-text="it.has_snapshot ? 'Saved' : '—'"></div>
                            </div>
                        </div>

                        {{-- Variants --}}
                        <template x-if="it.product?.option_groups?.length">
                            <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-extrabold text-slate-800 flex items-center gap-2">
                                    <span class="iconify" data-icon="mdi:shape-outline"></span>
                                    Variants
                                </div>

                                <div class="mt-3 space-y-3">
                                    <template x-for="g in it.product.option_groups" :key="g.id">
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="text-xs font-extrabold text-slate-900" x-text="g.name"></div>
                                                <template x-if="g.is_required">
                                                    <span class="text-[10px] rounded-full bg-red-50 text-red-700 px-2 py-0.5 font-bold">Required</span>
                                                </template>
                                            </div>

                                            <div class="mt-2 grid grid-cols-2 gap-2">
                                                <template x-for="op in getGroupOptions(it, g)" :key="op.id">
                                                    <label
                                                        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 hover:border-[#ef233c]/40 cursor-pointer"
                                                        :class="Number(it.options[g.id]) === Number(op.id) ? 'border-[#ef233c] ring-2 ring-[#ef233c]/20 bg-[#ef233c]/[0.03]' : ''">
                                                        <input type="radio" class="accent-[#ef233c]"
                                                            :name="`cart_item_${it.key}_group_${g.id}`"
                                                            :value="op.id"
                                                            x-model="it.options[g.id]"
                                                            @change="syncDependentSelections(it); queueRepriceAndSave(it)" />
                                                        <span class="text-sm font-semibold text-slate-800" x-text="op.name"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Finishings --}}
                        <template x-if="it.product?.finishings?.length">
                            <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-extrabold text-slate-800 flex items-center gap-2">
                                    <span class="iconify" data-icon="mdi:scissors-cutting"></span>
                                    Finishings
                                </div>

                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <template x-for="f in it.product.finishings" :key="f.finishing_product_id">
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="text-xs font-extrabold text-slate-900" x-text="f.name"></div>
                                                <template x-if="f.is_required">
                                                    <span class="text-[10px] rounded-full bg-red-50 text-red-700 px-2 py-0.5 font-bold">Required</span>
                                                </template>
                                            </div>

                                            <div class="mt-2 flex items-center gap-2">
                                                <button type="button"
                                                    class="h-9 w-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-black"
                                                    @click="setFinishingQty(it, f, (it.finishings[f.finishing_product_id] ?? 0) - 1)">
                                                    −
                                                </button>
                                                <input type="number" min="0"
                                                    class="h-9 w-20 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-center"
                                                    :value="it.finishings[f.finishing_product_id] ?? 0"
                                                    @input="setFinishingQty(it, f, Number($event.target.value))" />
                                                <button type="button"
                                                    class="h-9 w-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-black"
                                                    @click="setFinishingQty(it, f, (it.finishings[f.finishing_product_id] ?? 0) + 1)">
                                                    +
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Dimensions / Roll --}}
                        <template x-if="it.product?.is_dimension_based">
                            <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-extrabold text-slate-800 flex items-center gap-2">
                                    <span class="iconify" data-icon="mdi:ruler-square"></span>
                                    Dimensions
                                </div>

                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600">Width</label>
                                        <input type="number" step="0.01" min="0"
                                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                            x-model.number="it.width"
                                            @input.debounce.350ms="queueRepriceAndSave(it)" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600">Height</label>
                                        <input type="number" step="0.01" min="0"
                                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                            x-model.number="it.height"
                                            @input.debounce.350ms="queueRepriceAndSave(it)" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600">Unit</label>
                                        <select
                                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                            x-model="it.unit"
                                            @change="queueRepriceAndSave(it)">
                                            <option value="in">in</option>
                                            <option value="ft">ft</option>
                                            <option value="mm">mm</option>
                                            <option value="cm">cm</option>
                                            <option value="m">m</option>
                                        </select>
                                    </div>
                                </div>

                                <template x-if="it.product?.allowed_rolls?.length">
                                    <div class="mt-3">
                                        <label class="block text-[11px] font-semibold text-slate-600">Roll</label>
                                        <select
                                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                            x-model="it.roll_id"
                                            @change="queueRepriceAndSave(it)">
                                            <option value="">Auto</option>
                                            <template x-for="r in it.product.allowed_rolls" :key="r.roll_id">
                                                <option :value="r.roll_id" x-text="r.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Artwork box --}}
                        <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-xs font-extrabold text-slate-800 flex items-center gap-2">
                                    <span class="iconify" data-icon="mdi:palette-outline"></span>
                                    Artwork
                                </div>
                                <span class="text-[11px] font-semibold text-slate-500">
                                    Upload ≤ <span class="text-slate-900 font-extrabold">100MB</span> or paste a link
                                </span>
                            </div>

                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <button type="button"
                                    class="text-left rounded-2xl border p-3 transition"
                                    :class="it.artwork.mode === 'upload' ? 'border-[#ef233c] ring-2 ring-[#ef233c]/20 bg-[#ef233c]/[0.03]' : 'border-slate-200 bg-white'"
                                    @click="it.artwork.mode='upload'; queueRepriceAndSave(it)">
                                    <div class="font-extrabold text-slate-900 flex items-center gap-2 text-xs">
                                        <span class="iconify" data-icon="mdi:cloud-upload-outline"></span>
                                        Upload my design
                                    </div>
                                </button>

                                <button type="button"
                                    class="text-left rounded-2xl border p-3 transition"
                                    :class="it.artwork.mode === 'hire' ? 'border-[#ef233c] ring-2 ring-[#ef233c]/20 bg-[#ef233c]/[0.03]' : 'border-slate-200 bg-white'"
                                    @click="it.artwork.mode='hire'; queueRepriceAndSave(it)">
                                    <div class="font-extrabold text-slate-900 flex items-center gap-2 text-xs">
                                        <span class="iconify" data-icon="mdi:account-star-outline"></span>
                                        Hire Printair designer
                                    </div>
                                </button>
                            </div>

                            <div class="mt-3 grid grid-cols-1 lg:grid-cols-2 gap-3">
                                {{-- Upload (only for logged-in DB cart items) --}}
                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                    <div class="text-[11px] font-semibold text-slate-600 mb-2 flex items-center gap-2">
                                        <span class="iconify" data-icon="mdi:cloud-upload-outline"></span>
                                        Upload file (PDF/AI/PSD/JPG/PNG)
                                    </div>

                                    <template x-if="!isAuthed">
                                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">
                                            Login to upload files. Guests can paste a share link on the right.
                                        </div>
                                    </template>

                                    <template x-if="isAuthed">
                                        <div>
                                            <input type="file"
                                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                                accept=".pdf,.ai,.psd,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg"
                                                @change="handleDbUpload(it, $event)">

                                            <template x-if="it.upload_state?.too_large">
                                                <div class="mt-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                                                    <span class="font-extrabold">File is larger than 100MB.</span>
                                                    Upload it to Drive/Dropbox and paste the link on the right (visible always).
                                                </div>
                                            </template>

                                            <template x-if="it.upload_state?.uploaded_files?.length">
                                                <div class="mt-3 text-xs text-slate-700">
                                                    <div class="font-extrabold flex items-center gap-2">
                                                        <span class="iconify" data-icon="mdi:file-multiple-outline"></span>
                                                        Uploaded files
                                                    </div>
                                                    <ul class="mt-1 list-disc ml-5">
                                                        <template x-for="f in it.upload_state.uploaded_files" :key="f.id">
                                                            <li class="break-all" x-text="f.original_name || f.path"></li>
                                                        </template>
                                                    </ul>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                {{-- URL (always visible) --}}
                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                    <div class="text-[11px] font-semibold text-slate-600 mb-2 flex items-center gap-2">
                                        <span class="iconify" data-icon="mdi:link-variant"></span>
                                        Artwork share link (always available)
                                    </div>

                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                            <span class="iconify" data-icon="mdi:link-variant"></span>
                                        </span>
                                        <input type="url"
                                            class="w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                            placeholder="https://drive.google.com/... (optional)"
                                            x-model="it.artwork.external_url" />
                                    </div>

                                    <div class="mt-2 flex gap-2">
                                        <button type="button"
                                            class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-extrabold text-white hover:bg-slate-800 disabled:opacity-50"
                                            :disabled="it.busy_save_url"
                                            @click="saveArtworkUrl(it)">
                                            <span class="iconify" data-icon="mdi:content-save-outline"></span>
                                            <span x-text="it.busy_save_url ? 'Saving…' : 'Save link'"></span>
                                        </button>

                                        <template x-if="it.artwork.saved_url">
                                            <a class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-extrabold text-slate-700 hover:bg-slate-50"
                                               :href="it.artwork.saved_url" target="_blank" rel="noopener">
                                                <span class="iconify" data-icon="mdi:open-in-new"></span>
                                                Open
                                            </a>
                                        </template>
                                    </div>

                                    <template x-if="it.artwork.mode === 'hire'">
                                        <div class="mt-3">
                                            <div class="text-[11px] font-semibold text-slate-600 mb-1 flex items-center gap-2">
                                                <span class="iconify" data-icon="mdi:note-text-outline"></span>
                                                Design brief
                                            </div>
                                            <textarea rows="3"
                                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                                x-model="it.artwork.brief"
                                                @input.debounce.400ms="queueRepriceAndSave(it)"
                                                placeholder="Tell us what you need (text, colors, deadline, references)…"></textarea>
                                        </div>
                                    </template>

                                    <div class="mt-2 text-[11px] text-slate-500">
                                        Tip: If your upload is over 100MB, use Drive/Dropbox and paste the link here.
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <template x-if="it.notes">
                            <div class="mt-3 text-xs text-slate-600">
                                <span class="font-extrabold text-slate-800">Notes:</span>
                                <span x-text="it.notes"></span>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Footer CTA --}}
                <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="text-sm text-slate-700">
                        <div class="font-extrabold text-slate-900">Next: Checkout</div>
                        <div class="text-xs text-slate-500 mt-1">
                            Guest users will verify email before submitting.
                        </div>
                        <div class="mt-2 text-xs text-slate-700">
                            Estimated total: <span class="font-extrabold text-slate-900" x-text="money(totalEstimate)"></span>
                        </div>
                    </div>

                    <a :href="endpoints.checkout"
                       class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#ef233c] px-5 py-3 text-sm font-extrabold text-white hover:opacity-95">
                        <span class="iconify" data-icon="mdi:arrow-right-bold-circle-outline"></span>
                        Continue to checkout
                    </a>
                </div>
            </div>
        </template>
    </div>

    <script>
        function printairCart({ csrf, jsonUrl, productQuoteBaseUrl, endpoints, isAuthed, initialToast }) {
            return {
                csrf, jsonUrl, productQuoteBaseUrl, endpoints, isAuthed,
                loading: true,
                toast: (initialToast && initialToast.message)
                    ? { type: initialToast.type || 'error', message: initialToast.message }
                    : { type: null, message: null },
                items: [],
                saveTimers: {},

                async init() {
                    await this.load();
                    try { window.dispatchEvent(new Event('cart-updated')); } catch (e) {}
                },

                setToast(type, message) {
                    this.toast = { type, message };
                    window.setTimeout(() => {
                        if (this.toast.message === message) this.toast = { type: null, message: null };
                    }, 6500);
                },

                money(v) {
                    const n = Number(v || 0);
                    return 'LKR ' + n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                get totalEstimate() {
                    return (this.items || []).reduce((sum, it) => sum + Number(it?.pricing_total || 0), 0);
                },

                formatBytes(bytes) {
                    if (!bytes && bytes !== 0) return '';
                    const sizes = ['B','KB','MB','GB'];
                    const i = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), sizes.length - 1);
                    const val = (bytes / Math.pow(1024, i)).toFixed(i === 0 ? 0 : 2);
                    return `${val} ${sizes[i]}`;
                },

                async load() {
                    this.loading = true;
                    try {
                        const res = await fetch(this.jsonUrl, { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        if (!res.ok) throw new Error(data?.message || 'Failed to load cart');

                        const mode = data.mode;
                        const cart = data.cart;

                        this.items = this.normalize(mode, cart);
                    } catch (e) {
                        this.setToast('error', e.message || 'Unable to load cart');
                        this.items = [];
                    } finally {
                        this.loading = false;
                    }
                },

                normalize(mode, cart) {
                    const out = [];
                    if (mode === 'db') {
                        const items = cart?.items || [];
                        for (const it of items) {
                            const meta = it.meta || {};
                            const savedUrl = meta.artwork_external_url || meta?.artwork?.external_url || null;
                            const options = (meta.options && typeof meta.options === 'object') ? meta.options : {};
                            const finishings = (meta.finishings && typeof meta.finishings === 'object') ? meta.finishings : {};
                            const product = it.product || null;
                            const img = product?.primary_image_url || (product?.images?.find(x => x.is_featured)?.url || product?.images?.[0]?.url) || null;
                            const ps = it.pricing_snapshot || null;

                            out.push({
                                key: 'db-' + it.id,
                                mode: 'db',
                                db_id: it.id,
                                product_id: it.product_id,
                                product,
                                primary_image_url: img,
                                product_name: product?.name || ('Product #' + it.product_id),
                                meta,
                                qty: it.qty || 1,
                                width: it.width || null,
                                height: it.height || null,
                                unit: it.unit || 'in',
                                roll_id: it.roll_id || null,
                                notes: it.notes || null,

                                options,
                                finishings,

                                has_snapshot: !!ps,
                                pricing_total: Number(ps?.total || 0),
                                pricing_error: null,
                                pricing_snapshot: ps,

                                artwork: {
                                    mode: meta?.artwork?.mode || 'upload',
                                    brief: meta?.artwork?.brief || '',
                                    external_url: savedUrl || '',
                                    saved_url: savedUrl,
                                },

                                busy_save_url: false,
                                busy_remove: false,
                                busy_update: false,

                                upload_state: {
                                    too_large: false,
                                    uploaded_files: (it.files || []),
                                }
                            });

                            this.applyFinishingDefaults(out[out.length - 1]);
                        }
                    } else {
                        const items = cart?.items || [];
                        for (const it of items) {
                            const meta = it.meta || {};
                            const savedUrl = meta.artwork_external_url || meta?.artwork?.external_url || null;
                            const options = (meta.options && typeof meta.options === 'object') ? meta.options : {};
                            const finishings = (meta.finishings && typeof meta.finishings === 'object') ? meta.finishings : {};
                            const product = it.product || null;
                            const img = product?.primary_image_url || (product?.images?.find(x => x.is_featured)?.url || product?.images?.[0]?.url) || null;
                            const ps = it.pricing_snapshot || null;

                            out.push({
                                key: 'gs-' + it.id,
                                mode: 'guest',
                                id: it.id,
                                product_id: it.product_id,
                                product,
                                primary_image_url: img,
                                product_name: product?.name || ('Product #' + it.product_id),
                                meta,
                                qty: it.qty || 1,
                                width: it.width || null,
                                height: it.height || null,
                                unit: it.unit || 'in',
                                roll_id: it.roll_id || null,
                                notes: it.notes || null,

                                options,
                                finishings,

                                has_snapshot: !!ps,
                                pricing_total: Number(ps?.total || 0),
                                pricing_error: null,
                                pricing_snapshot: ps,

                                artwork: {
                                    mode: meta?.artwork?.mode || 'upload',
                                    brief: meta?.artwork?.brief || '',
                                    external_url: savedUrl || '',
                                    saved_url: savedUrl,
                                },

                                busy_save_url: false,
                                busy_remove: false,
                                busy_update: false,
                                upload_state: { too_large: false, uploaded_files: [] },
                            });

                            this.applyFinishingDefaults(out[out.length - 1]);
                        }
                    }
                    return out;
                },

                applyFinishingDefaults(it) {
                    const fins = Array.isArray(it.product?.finishings) ? it.product.finishings : [];
                    if (!fins.length) return;

                    it.finishings = it.finishings || {};

                    for (const f of fins) {
                        const fid = Number(f?.finishing_product_id);
                        if (!Number.isFinite(fid) || fid <= 0) continue;

                        const existing = it.finishings[fid];
                        if (existing !== undefined && existing !== null && existing !== '') continue;

                        const defaultQty = Number(f?.default_qty ?? (f?.is_required ? (f?.min_qty ?? 1) : 0));
                        if (Number.isFinite(defaultQty) && defaultQty > 0) {
                            it.finishings[fid] = defaultQty;
                        }
                    }
                },

                getSelectedOptionsMap(it) {
                    const map = {};
                    for (const [gid, oid] of Object.entries(it.options || {})) {
                        if (oid) map[Number(gid)] = Number(oid);
                    }
                    return map;
                },

                getGroupOptions(it, group) {
                    const matrix = Array.isArray(it.product?.variant_matrix) ? it.product.variant_matrix : null;
                    if (!matrix?.length) return group.options || [];

                    const gid = Number(group.id);
                    const orderedGroups = Array.isArray(it.product?.option_groups) ? it.product.option_groups : [];
                    const groupIndex = orderedGroups.findIndex(g => Number(g.id) === gid);
                    if (groupIndex < 0) return group.options || [];

                    const selected = this.getSelectedOptionsMap(it);
                    const beforeGroupIds = orderedGroups
                        .slice(0, Math.max(0, groupIndex))
                        .map(g => Number(g.id))
                        .filter(gidBefore => selected[gidBefore]);

                    const validRows = matrix.filter(row => {
                        const rowMap = row.options || row;
                        return beforeGroupIds.every(gidBefore => Number(rowMap[gidBefore]) === Number(selected[gidBefore]));
                    });

                    const validOptionIds = new Set(
                        validRows
                            .map(row => row.options || row)
                            .map(m => m[gid])
                            .filter(Boolean)
                            .map(Number)
                    );

                    return (group.options || []).filter(op => validOptionIds.size === 0 || validOptionIds.has(Number(op.id)));
                },

                syncDependentSelections(it) {
                    const groups = Array.isArray(it.product?.option_groups) ? it.product.option_groups : [];
                    const matrix = Array.isArray(it.product?.variant_matrix) ? it.product.variant_matrix : [];
                    if (!groups.length || !matrix.length) return;

                    const selected = this.getSelectedOptionsMap(it);

                    for (let i = 0; i < groups.length; i++) {
                        const gid = Number(groups[i].id);
                        if (!selected[gid]) continue;

                        const filtered = matrix.filter(row => {
                            const map = row.options || row;
                            for (let j = 0; j <= i; j++) {
                                const gid2 = Number(groups[j].id);
                                if (selected[gid2] && Number(map[gid2]) !== Number(selected[gid2])) {
                                    return false;
                                }
                            }
                            return true;
                        });

                        for (let j = i + 1; j < groups.length; j++) {
                            const gid2 = Number(groups[j].id);
                            const valid = new Set(filtered.map(r => Number((r.options || r)[gid2])).filter(Boolean));
                            if (selected[gid2] && !valid.has(Number(selected[gid2]))) {
                                delete it.options[gid2];
                            }
                        }
                    }
                },

                setFinishingQty(it, finishing, qty) {
                    const fid = Number(finishing?.finishing_product_id);
                    if (!Number.isFinite(fid) || fid <= 0) return;

                    const min = finishing?.is_required ? (Number(finishing?.min_qty ?? 1) || 1) : (Number(finishing?.min_qty ?? 0) || 0);
                    const maxRaw = finishing?.max_qty ?? null;
                    const max = (maxRaw === null || maxRaw === undefined || maxRaw === '') ? null : Number(maxRaw);

                    let next = Number.isFinite(qty) ? qty : 0;
                    if (next < min) next = min;
                    if (max !== null && Number.isFinite(max) && next > max) next = max;

                    it.finishings[fid] = next;
                    this.queueRepriceAndSave(it);
                },

                queueRepriceAndSave(it) {
                    const k = it.key;
                    if (this.saveTimers[k]) {
                        clearTimeout(this.saveTimers[k]);
                    }
                    this.saveTimers[k] = setTimeout(() => {
                        this.repriceAndSave(it);
                    }, 450);
                },

                async repriceAndSave(it) {
                    await this.quoteItem(it);
                    await this.saveItem(it);
                },

                async quoteItem(it) {
                    it.pricing_error = null;

                    if (!it.product?.slug) {
                        it.pricing_total = Number(it.pricing_total || 0);
                        return;
                    }

                    const isDimensionBased = !!it.product?.is_dimension_based;
                    if (isDimensionBased) {
                        const w = Number(it.width || 0);
                        const h = Number(it.height || 0);
                        if (!Number.isFinite(w) || !Number.isFinite(h) || w <= 0 || h <= 0) {
                            it.pricing_error = 'Width and height are required.';
                            it.pricing_total = 0;
                            return;
                        }
                    }

                    try {
                        const url = `${this.productQuoteBaseUrl}/${it.product.slug}/price-quote`;

                        const payload = {
                            qty: it.qty || 1,
                            width: it.width,
                            height: it.height,
                            unit: it.unit,
                            roll_id: it.roll_id || null,
                            options: it.options || {},
                            finishings: it.finishings || {},
                            artwork: {
                                mode: it.artwork.mode,
                                brief: it.artwork.brief,
                                external_url: it.artwork.external_url,
                            },
                        };

                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': this.csrf,
                            },
                            body: JSON.stringify(payload),
                        });

                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            throw new Error(data?.message || `Pricing error (${res.status})`);
                        }

                        it.pricing_total = Number(data.total || 0);
                        it.has_snapshot = true;

                        it.pricing_snapshot = {
                            source: 'cart_page',
                            total: Number(data.total || 0),
                            breakdown: Array.isArray(data.breakdown) ? data.breakdown : [],
                            input: payload,
                        };
                    } catch (e) {
                        it.pricing_error = e?.message || 'Unable to calculate price.';
                        it.pricing_total = 0;
                    }
                },

                async saveItem(it) {
                    it.busy_update = true;
                    try {
                        const meta = it.meta || {};
                        meta.options = it.options || {};
                        meta.finishings = it.finishings || {};
                        meta.artwork = {
                            mode: it.artwork.mode,
                            brief: it.artwork.brief,
                            external_url: it.artwork.external_url,
                        };
                        meta.artwork_external_url = it.artwork.external_url || null;

                        if (it.mode === 'db') {
                            const endpoint = endpoints.updateDb.replace('/0', `/${it.db_id}`);
                            const res = await fetch(endpoint, {
                                method: 'PATCH',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                },
                                body: JSON.stringify({
                                    qty: it.qty,
                                    width: it.width,
                                    height: it.height,
                                    unit: it.unit,
                                    roll_id: it.roll_id || null,
                                    notes: it.notes || null,
                                    meta,
                                    finishings: meta.finishings,
                                    pricing_snapshot: it.pricing_snapshot || null,
                                }),
                            });

                            const data = await res.json().catch(() => ({}));
                            if (!res.ok || !data.ok) throw new Error(data?.message || 'Failed saving item');
                        } else {
                            const res = await fetch(endpoints.updateGuest, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                },
                                body: JSON.stringify({
                                    item_uuid: it.id,
                                    qty: it.qty,
                                    width: it.width,
                                    height: it.height,
                                    unit: it.unit,
                                    roll_id: it.roll_id || null,
                                    notes: it.notes || null,
                                    meta,
                                    finishings: meta.finishings,
                                    pricing_snapshot: it.pricing_snapshot || null,
                                }),
                            });

                            const data = await res.json().catch(() => ({}));
                            if (!res.ok || !data.ok) throw new Error(data?.message || 'Failed saving item');
                        }

                        try { window.dispatchEvent(new Event('cart-updated')); } catch (e) {}
                    } catch (e) {
                        this.setToast('error', e?.message || 'Failed to save changes');
                    } finally {
                        it.busy_update = false;
                    }
                },

                async saveArtworkUrl(it) {
                    it.busy_save_url = true;

                    try {
                        const url = (it.artwork.external_url || '').trim();

                        if (it.mode === 'db') {
                            const endpoint = endpoints.saveUrlDb.replace('/0/', `/${it.db_id}/`);
                            const res = await fetch(endpoint, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ url }),
                            });

                            const data = await res.json().catch(() => ({}));
                            if (!res.ok) throw new Error(data?.message || 'Failed saving link');
                        } else {
                            const res = await fetch(endpoints.saveUrlGuest, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ item_uuid: it.id, url }),
                            });

                            const data = await res.json().catch(() => ({}));
                            if (!res.ok) throw new Error(data?.message || 'Failed saving link');
                        }

                        it.artwork.saved_url = url || null;
                        it.artwork.external_url = url || '';
                        it.meta = it.meta || {};
                        it.meta.artwork_external_url = url || null;
                        this.setToast('success', 'Artwork link saved.');
                        this.queueRepriceAndSave(it);

                        try { window.dispatchEvent(new Event('cart-updated')); } catch (e) {}
                    } catch (e) {
                        this.setToast('error', e.message || 'Failed to save');
                    } finally {
                        it.busy_save_url = false;
                    }
                },

                async removeItem(it) {
                    it.busy_remove = true;

                    try {
                        if (it.mode === 'db') {
                            const endpoint = endpoints.deleteDb.replace('/0', `/${it.db_id}`);
                            const res = await fetch(endpoint, {
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                },
                            });

                            const data = await res.json().catch(() => ({}));
                            if (!res.ok || !data.ok) throw new Error(data?.message || 'Failed to remove item');
                        } else {
                            const res = await fetch(endpoints.deleteGuest, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                },
                                body: JSON.stringify({ item_uuid: it.id }),
                            });

                            const data = await res.json().catch(() => ({}));
                            if (!res.ok || !data.ok) throw new Error(data?.message || 'Failed to remove item');
                        }

                        this.items = (this.items || []).filter(x => x.key !== it.key);
                        this.setToast('success', 'Item removed.');
                        try { window.dispatchEvent(new Event('cart-updated')); } catch (e) {}
                    } catch (e) {
                        this.setToast('error', e?.message || 'Failed to remove item');
                    } finally {
                        it.busy_remove = false;
                    }
                },

                async handleDbUpload(it, e) {
                    if (!this.isAuthed) return;
                    const file = e?.target?.files?.[0];
                    if (!file) return;

                    const maxBytes = 104857600; // 100MB

                    if (file.size > maxBytes) {
                        it.upload_state.too_large = true;
                        e.target.value = '';
                        this.setToast('error', 'File larger than 100MB. Please paste a share link instead.');
                        return;
                    }

                    it.upload_state.too_large = false;

                    const ext = (file.name.split('.').pop() || '').toLowerCase();
                    const allowed = ['pdf','ai','psd','png','jpg','jpeg'];
                    if (!allowed.includes(ext)) {
                        e.target.value = '';
                        this.setToast('error', 'Invalid file type. Allowed: PDF, AI, PSD, JPG, PNG.');
                        return;
                    }

                    try {
                        const endpoint = endpoints.uploadDb.replace('/0/', `/${it.db_id}/`);
                        const fd = new FormData();
                        fd.append('file', file);

                        const res = await fetch(endpoint, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': this.csrf,
                                'Accept': 'application/json',
                            },
                            body: fd,
                        });

                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(data?.message || 'Upload failed');

                        if (data.file) {
                            it.upload_state.uploaded_files = it.upload_state.uploaded_files || [];
                            it.upload_state.uploaded_files.unshift(data.file);
                        }

                        this.setToast('success', 'File uploaded.');
                        this.queueRepriceAndSave(it);
                    } catch (e) {
                        this.setToast('error', e.message || 'Upload failed');
                    } finally {
                        e.target.value = '';
                    }
                },
            }
        }
    </script>
</x-guest-layout>
