<x-home-layout :seo="$seo">
    <div x-data="productDetailsPage({
        product: @js($productJson ?? []),
        initialWg: @js($initialWg ?? 'public'),
        pricingQuoteUrl: @js(route('products.price-quote', ['product' => $product->slug])),
        whatsappNumber: @js(config('printair.contact_whatsapp', '94768860175')),
        placeholderImage: @js(asset('assets/placeholders/product.png')),
    })" x-init="init()" class="bg-white">

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 lg:py-10">

            {{-- Breadcrumbs --}}
            @php
                $indexUrl = $product->product_type === 'service'
                    ? route('services.index')
                    : route('products.index');
                $indexLabel = $product->product_type === 'service' ? 'Services' : 'Products';
            @endphp
            <nav class="text-sm text-slate-500 mb-6">
                <a href="{{ url('/') }}" class="hover:text-slate-700">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ $indexUrl }}" class="hover:text-slate-700">{{ $indexLabel }}</a>
                <template x-if="product?.category">
                    <span>
                        <span class="mx-2">/</span>
                        <a :href="`{{ $indexUrl }}?category=${product.category.slug}`"
                            class="hover:text-slate-700" x-text="product.category.name"></a>
                    </span>
                </template>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium" x-text="product?.name ?? 'Product'"></span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
                {{-- Left: Gallery --}}
                <section class="lg:col-span-7">
                    <template x-if="loading.product">
                        <div class="space-y-4">
                            <div class="h-[420px] rounded-3xl bg-slate-100 animate-pulse"></div>
                            <div class="flex gap-3">
                                <div class="h-20 w-20 rounded-2xl bg-slate-100 animate-pulse"></div>
                                <div class="h-20 w-20 rounded-2xl bg-slate-100 animate-pulse"></div>
                                <div class="h-20 w-20 rounded-2xl bg-slate-100 animate-pulse"></div>
                                <div class="h-20 w-20 rounded-2xl bg-slate-100 animate-pulse"></div>
                            </div>
                        </div>
                    </template>

                    <template x-if="!loading.product">
                        <div>
                            <div
                                class="relative rounded-3xl border border-slate-200 overflow-hidden bg-slate-50">
                                <img :src="activeImageUrl" :alt="product?.name ?? 'Product image'"
                                    class="w-full h-[420px] sm:h-[520px] object-cover"
                                    @click="lightbox.open = true" />

                                <div
                                    class="absolute bottom-4 left-4 rounded-full bg-black/70 text-white text-xs px-3 py-1.5">
                                    Click to preview
                                </div>
                            </div>

                            <div class="mt-4 flex gap-3 overflow-x-auto pb-2">
                                <template x-for="img in product.images" :key="img.id">
                                    <button type="button"
                                        class="relative shrink-0 h-20 w-20 rounded-2xl border overflow-hidden bg-white"
                                        :class="img.url === activeImageUrl ? 'border-[#ef233c] ring-2 ring-[#ef233c]/20' : 'border-slate-200 hover:border-slate-300'"
                                        @click="activeImageUrl = img.url">
                                        <img :src="img.url" class="h-full w-full object-cover" />
                                    </button>
                                </template>

                                <template x-if="!product.images?.length">
                                    <div
                                        class="h-20 w-20 rounded-2xl border border-slate-200 bg-white flex items-center justify-center text-xs text-slate-500">
                                        No images
                                    </div>
                                </template>
                            </div>

                            <template x-if="lightbox.open">
                                <div class="fixed inset-0 z-[999] bg-black/80 flex items-center justify-center p-4"
                                    @click.self="lightbox.open=false">
                                    <div class="max-w-5xl w-full rounded-3xl overflow-hidden bg-white">
                                        <div class="flex items-center justify-between p-4 border-b border-slate-200">
                                            <div class="text-sm font-semibold text-slate-900"
                                                x-text="product?.name"></div>
                                            <button class="text-sm font-semibold text-slate-600 hover:text-slate-900"
                                                @click="lightbox.open=false">Close</button>
                                        </div>
                                        <img :src="activeImageUrl"
                                            class="w-full max-h-[80vh] object-contain bg-slate-50" />
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </section>

                {{-- Right: Configurator --}}
                <aside class="lg:col-span-5">
                    <div class="sticky top-24 space-y-6">
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-black text-slate-900"
                                x-text="product?.name ?? 'Product'"></h1>
                            <p class="mt-2 text-slate-600" x-text="product?.short_description ?? ''"></p>
                        </div>

                        {{-- Price card --}}
                        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Estimated Price
                                    </div>

                                    <template x-if="loading.price">
                                        <div class="mt-2 h-9 w-44 rounded-xl bg-slate-100 animate-pulse"></div>
                                    </template>

                                    <template x-if="!loading.price">
                                        <div class="mt-2 text-3xl font-black text-slate-900">
                                            <span x-text="formatMoney(price.total)"></span>
                                        </div>
                                    </template>

                                    <div class="mt-1 text-xs text-slate-500">
                                        Live estimate — final confirmation at admin review
                                    </div>

                                    <template x-if="!loading.price && price.error">
                                        <div class="mt-2 text-xs font-semibold text-rose-600" x-text="price.error"></div>
                                    </template>
                                </div>

                                <div class="text-right">
                                    <div class="text-xs text-slate-500">Qty</div>
                                    <input type="number" min="1"
                                        class="mt-1 w-20 text-center rounded-xl border border-slate-200 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                        x-model.number="state.qty" @input.debounce.350ms="refreshPrice()" />
                                </div>
                            </div>

                            <template x-if="!loading.price && price.breakdown?.length">
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <template x-for="b in price.breakdown" :key="b.label">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold text-slate-700">
                                            <span x-text="b.label"></span>
                                            <span class="text-slate-500">•</span>
                                            <span x-text="formatMoney(b.amount)"></span>
                                        </span>
                                    </template>
                                </div>
                            </template>
                        </div>

                        {{-- Dimension inputs --}}
                        <template x-if="product?.is_dimension_based">
                            <div class="rounded-3xl border border-slate-200 bg-white p-5">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-black text-slate-900">Custom Size</h3>
                                    <span class="text-xs text-slate-500">Enter dimensions</span>
                                </div>

                                <div class="mt-4 grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600">Width</label>
                                        <input type="number" min="0"
                                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                            x-model.number="state.width" @input.debounce.350ms="refreshPrice()" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600">Height</label>
                                        <input type="number" min="0"
                                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                            x-model.number="state.height" @input.debounce.350ms="refreshPrice()" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600">Unit</label>
                                        <select
                                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                            x-model="state.unit" @change="refreshPrice()">
                                            <option value="in">Inches</option>
                                            <option value="ft">Feet</option>
                                            <option value="cm">CM</option>
                                            <option value="mm">MM</option>
                                            <option value="m">Meters</option>
                                        </select>
                                    </div>
                                </div>

                                <template x-if="product?.allowed_rolls?.length">
                                    <div class="mt-4">
                                        <label class="block text-xs font-semibold text-slate-600">Material Roll</label>
                                        <select
                                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                            x-model="state.roll_id" @change="refreshPrice()">
                                            <option value="">Auto</option>
                                            <template x-for="r in product.allowed_rolls" :key="r.roll_id">
                                                <option :value="r.roll_id" x-text="r.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Variants (Option Groups + Valid Combinations) --}}
                        <template x-if="product?.option_groups?.length">
                            <div class="rounded-3xl border border-slate-200 bg-white p-5">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-black text-slate-900">Variants</h3>
                                    <span class="text-xs text-slate-500">Single select</span>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <template x-for="g in product.option_groups" :key="g.id">
                                        <div class="rounded-2xl border border-slate-200 p-4">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="font-semibold text-slate-900" x-text="g.name"></div>
                                                <template x-if="g.is_required">
                                                    <span
                                                        class="text-[11px] rounded-full bg-red-50 text-red-700 px-2.5 py-1 font-semibold">Required</span>
                                                </template>
                                                <template x-if="!g.is_required">
                                                    <span
                                                        class="text-[11px] rounded-full bg-slate-100 text-slate-700 px-2.5 py-1 font-semibold">Optional</span>
                                                </template>
                                            </div>

                                            <div class="mt-3 grid grid-cols-2 gap-2">
                                                <template x-for="op in getGroupOptions(g)" :key="op.id">
                                                    <label
                                                        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 hover:border-[#ef233c]/40 cursor-pointer"
                                                        :class="Number(state.options[g.id]) === Number(op.id) ? 'border-[#ef233c] ring-2 ring-[#ef233c]/20 bg-[#ef233c]/[0.03]' : ''">
                                                        <input type="radio" class="accent-[#ef233c]"
                                                            :name="`option_group_${g.id}`" :value="op.id"
                                                            x-model="state.options[g.id]"
                                                            @change="syncDependentSelections(); refreshPrice()" />
                                                        <span class="text-sm font-semibold text-slate-800"
                                                            x-text="op.name"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Artwork / Design (not for services) --}}
                        <template x-if="product?.product_type !== 'service'">
                            <div class="rounded-3xl border border-slate-200 bg-white p-5">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-black text-slate-900">Artwork</h3>
                                    <span class="text-xs text-slate-500">Choose one</span>
                                </div>

                                <div class="mt-4 space-y-2">
                                    <label
                                        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 cursor-pointer hover:border-[#ef233c]/40"
                                        :class="state.artwork.mode === 'upload' ? 'border-[#ef233c] ring-2 ring-[#ef233c]/20 bg-[#ef233c]/[0.03]' : ''">
                                        <input type="radio" class="accent-[#ef233c]" value="upload"
                                            x-model="state.artwork.mode" @change="refreshPrice()">
                                        <span class="text-sm font-semibold text-slate-800">Upload my design</span>
                                    </label>

                                    <label
                                        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 cursor-pointer hover:border-[#ef233c]/40"
                                        :class="state.artwork.mode === 'hire' ? 'border-[#ef233c] ring-2 ring-[#ef233c]/20 bg-[#ef233c]/[0.03]' : ''">
                                        <input type="radio" class="accent-[#ef233c]" value="hire"
                                            x-model="state.artwork.mode" @change="refreshPrice()">
                                        <span class="text-sm font-semibold text-slate-800">Hire Printair designer</span>
                                    </label>
                                </div>

                                <template x-if="state.artwork.mode === 'upload'">
                                    <div class="mt-4">
                                        <label class="block text-xs font-semibold text-slate-600">Upload file</label>
                                        <input type="file"
                                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                            @change="handleArtworkUpload($event)">
                                        <p class="mt-2 text-xs text-slate-500">
                                            AI, PSD, PDF, PNG, JPG accepted (final rules handled in admin).
                                        </p>
                                    </div>
                                </template>

                                <template x-if="state.artwork.mode === 'hire'">
                                    <div class="mt-4">
                                        <label class="block text-xs font-semibold text-slate-600">Design brief</label>
                                        <textarea
                                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                            rows="4"
                                            placeholder="Tell us what you need (text, colors, theme, references, deadline)..."
                                            x-model="state.artwork.brief" @input.debounce.400ms="refreshPrice()"></textarea>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Finishings --}}
                        <template x-if="product?.finishings?.length">
                            <div class="rounded-3xl border border-slate-200 bg-white p-5">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-black text-slate-900">Finishing Options</h3>
                                    <span class="text-xs text-slate-500">Add-ons</span>
                                </div>

                                <div class="mt-4 space-y-3">
                                    <template x-for="f in product.finishings" :key="f.finishing_product_id">
                                        <div
                                            class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 p-4">
                                            <div>
                                                <div class="font-semibold text-slate-900" x-text="f.name"></div>
                                                <div class="text-xs text-slate-500">
                                                    <template x-if="f.max_qty">
                                                        <span x-text="`Max: ${f.max_qty}`"></span>
                                                    </template>
                                                    <template x-if="!f.max_qty">
                                                        <span>Unlimited</span>
                                                    </template>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <button type="button"
                                                    class="h-10 w-10 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-black"
                                                    @click="setFinishingQty(f.finishing_product_id, (state.finishings[f.finishing_product_id] ?? 0) - 1, f)">
                                                    −
                                                </button>

                                                <input type="number" min="0"
                                                    class="h-10 w-16 rounded-xl border border-slate-200 text-center text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                                    :value="state.finishings[f.finishing_product_id] ?? 0"
                                                    @input="setFinishingQty(f.finishing_product_id, Number($event.target.value), f)" />

                                                <button type="button"
                                                    class="h-10 w-10 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-black"
                                                    @click="setFinishingQty(f.finishing_product_id, (state.finishings[f.finishing_product_id] ?? 0) + 1, f)">
                                                    +
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Templates --}}
                        <template x-if="templates?.length">
                            <div class="rounded-3xl border border-slate-200 bg-white p-5">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-black text-slate-900">Download Templates</h3>
                                    <span class="text-xs text-slate-500">Quick</span>
                                </div>

                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <template x-for="t in templates" :key="t.id">
                                        <a :href="t.url"
                                            class="inline-flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 hover:border-slate-300">
                                            <div>
                                                <div class="text-sm font-semibold text-slate-900" x-text="t.name"></div>
                                                <div class="text-xs text-slate-500">Template</div>
                                            </div>
                                            <span class="text-xs font-black text-[#ef233c]">Download</span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- CTA --}}
                        <div class="rounded-3xl border border-slate-200 bg-white p-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <button type="button"
                                    class="w-full rounded-2xl bg-[#ef233c] text-white font-black py-3 hover:brightness-95">
                                    Add to Cart
                                </button>

                                <a :href="whatsappLink"
                                    class="w-full rounded-2xl border border-slate-200 bg-white text-slate-900 font-black py-3 text-center hover:bg-slate-50">
                                    WhatsApp
                                </a>
                            </div>

                            <div class="mt-3 text-xs text-slate-500">
                                You can place an order even if some selections are pending — admins will confirm before
                                finalizing.
                            </div>
                        </div>

                        {{-- SEO Hash Tags (AFTER CTA) --}}
                        <template x-if="seoTags?.length">
                            <div class="rounded-3xl border border-slate-200 bg-white p-5">
                                <div class="text-xs font-black text-slate-900">Popular searches</div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <template x-for="t in seoTags" :key="t">
                                        <span
                                            class="text-[12px] font-semibold px-3 py-1.5 rounded-full bg-[#ef233c]/10 text-[#ef233c]">
                                            <span x-text="`#${t}`"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </aside>
            </div>

            <div class="mt-10">
                {{-- Description (FULL WIDTH) --}}
                <section class="w-full">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6">
                        <h2 class="text-lg font-black text-slate-900">Description</h2>
                        <div class="mt-3 prose prose-slate max-w-none">
                            <p x-text="product?.description ?? 'No description provided.'"></p>
                        </div>
                    </div>
                </section>

                <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
                    {{-- Specs + resources --}}
                    <aside class="lg:col-span-5 space-y-6">
                        <template x-if="product?.spec_groups?.length">
                            <div class="rounded-3xl border border-slate-200 bg-white p-6">
                                <h2 class="text-lg font-black text-slate-900">Specifications</h2>

                                <div class="mt-4 space-y-3">
                                    <template x-for="sg in product.spec_groups" :key="sg.id">
                                        <details class="rounded-2xl border border-slate-200 bg-white p-4">
                                            <summary class="cursor-pointer font-semibold text-slate-900"
                                                x-text="sg.name"></summary>
                                            <div class="mt-3 space-y-2">
                                                <template x-for="sp in sg.specs" :key="sp.id">
                                                    <div class="flex items-start justify-between gap-4 text-sm">
                                                        <div class="text-slate-600" x-text="sp.label"></div>
                                                        <div class="font-semibold text-slate-900 text-right"
                                                            x-text="sp.value"></div>
                                                    </div>
                                                </template>
                                            </div>
                                        </details>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="resources?.length">
                            <div class="rounded-3xl border border-slate-200 bg-white p-6">
                                <h2 class="text-lg font-black text-slate-900">Resources</h2>

                                <div class="mt-4 space-y-3">
                                    <template x-for="a in resources" :key="a.id">
                                        <a :href="a.url"
                                            class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 hover:border-slate-300">
                                            <div>
                                                <div class="font-semibold text-slate-900" x-text="a.name"></div>
                                                <div class="text-xs text-slate-500"
                                                    x-text="(a.type ?? 'resource').toUpperCase()"></div>
                                            </div>
                                            <span class="text-xs font-black text-[#ef233c]">Download</span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </aside>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                function productDetailsPage({
                    product,
                    initialWg,
                    pricingQuoteUrl,
                    whatsappNumber,
                    placeholderImage
                }) {
                    return {
                        product: product || {},
                        pricingQuoteUrl,
                        whatsappNumber,
                        placeholderImage,

                        loading: {
                            product: true,
                            price: true
                        },

                        lightbox: {
                            open: false
                        },
                        activeImageUrl: null,

                        state: {
                            wg: initialWg || 'public',
                            qty: 1,

                            width: null,
                            height: null,
                            unit: 'in',
                            roll_id: '',

                            options: {},
                            finishings: {},

                            artwork: {
                                mode: 'upload',
                                file: null,
                                brief: '',
                            },
                        },

                        price: {
                            total: 0,
                            breakdown: [],
                            error: null,
                        },

	                        _priceRequestId: 0,
	                        _priceAbortController: null,

	                        get templates() {
	                            const at = this.product?.attachments || [];
	                            return at.filter(x => String(x.type || '').toLowerCase() === 'template');
	                        },

                        get resources() {
                            const at = this.product?.attachments || [];
                            return at.filter(x => String(x.type || '').toLowerCase() !== 'template');
                        },

                        get seoTags() {
                            const raw =
                                this.product?.seo_keywords ??
                                this.product?.seo?.keywords ??
                                this.product?.keywords ??
                                '';

                            const text = Array.isArray(raw) ? raw.join(',') : String(raw || '');
                            return text
                                .split(',')
                                .map(s => s.trim())
                                .filter(Boolean)
                                .slice(0, 18);
                        },

                        get whatsappLink() {
                            const msg = encodeURIComponent(
                                `Hi Printair, I need ${this.product?.name || 'this product'}. Can you help me?`
                            );
                            return `https://wa.me/${this.whatsappNumber}?text=${msg}`;
                        },

	                        init() {
	                            this.loading.product = false;

	                            const imgs = Array.isArray(this.product?.images) ? [...this.product.images] : [];
	                            imgs.sort((a, b) => (a.sort_index ?? 0) - (b.sort_index ?? 0));
	                            this.product.images = imgs;

	                            const featured = imgs.find(i => i.is_featured);
	                            this.activeImageUrl = (featured?.url || imgs[0]?.url || this.placeholderImage);

	                            const finishings = Array.isArray(this.product?.finishings) ? this.product.finishings : [];
	                            for (const f of finishings) {
	                                const finishingId = Number(f?.finishing_product_id);
	                                if (!Number.isFinite(finishingId) || finishingId <= 0) continue;

	                                const existing = this.state.finishings?.[finishingId];
	                                if (existing !== undefined && existing !== null && existing !== '') continue;

	                                const defaultQty = Number(f?.default_qty ?? (f?.is_required ? (f?.min_qty ?? 1) : 0));
	                                if (Number.isFinite(defaultQty) && defaultQty > 0) {
	                                    this.state.finishings[finishingId] = defaultQty;
	                                }
	                            }

	                            this.refreshPrice();
	                        },

                        handleArtworkUpload(e) {
                            const file = e?.target?.files?.[0] || null;
                            this.state.artwork.file = file;
                            this.refreshPrice();
                        },

                        getSelectedOptionsMap() {
                            const map = {};
                            for (const [gid, oid] of Object.entries(this.state.options || {})) {
                                if (oid) map[Number(gid)] = Number(oid);
                            }
                            return map;
                        },

                        getGroupOptions(group) {
                            const matrix = Array.isArray(this.product?.variant_matrix) ? this.product.variant_matrix : null;
                            if (!matrix?.length) return group.options || [];

                            const gid = Number(group.id);
                            const orderedGroups = Array.isArray(this.product?.option_groups) ? this.product.option_groups : [];
                            const groupIndex = orderedGroups.findIndex(g => Number(g.id) === gid);
                            if (groupIndex < 0) return group.options || [];

                            // selections for groups BEFORE this group
                            const selected = this.getSelectedOptionsMap();
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
                                    .map(row => Number((row.options || row)[gid]))
                                    .filter(v => Number.isFinite(v))
                            );

                            // If matrix doesn't cover this group, fall back to all options
                            if (validOptionIds.size === 0) return group.options || [];

                            return (group.options || []).filter(op => validOptionIds.has(Number(op.id)));
                        },

                        syncDependentSelections() {
                            const groups = Array.isArray(this.product?.option_groups) ? this.product.option_groups : [];
                            const matrix = Array.isArray(this.product?.variant_matrix) ? this.product.variant_matrix : null;
                            if (!matrix?.length || groups.length === 0) return;

                            const selected = this.getSelectedOptionsMap();

                            for (let i = 0; i < groups.length; i++) {
                                const g = groups[i];
                                const gid = Number(g.id);
                                const current = selected[gid];

                                if (!current) {
                                    continue;
                                }

                                const validIds = this.getGroupOptions(g).map(o => Number(o.id));
                                if (!validIds.includes(Number(current))) {
                                    for (let j = i; j < groups.length; j++) {
                                        const gid2 = Number(groups[j].id);
                                        delete this.state.options[gid2];
                                    }
                                    break;
                                }
                            }
                        },

                        setFinishingQty(finishingId, qty, finishing) {
                            const min = Number(finishing?.min_qty ?? 0);
                            const max = finishing?.max_qty ? Number(finishing.max_qty) : null;

                            let next = Number.isFinite(qty) ? qty : 0;
                            if (next < min) next = min;
                            if (max !== null && next > max) next = max;

                            this.state.finishings[finishingId] = next;
                            this.refreshPrice();
                        },

                        formatMoney(value) {
                            const n = Number(value || 0);
                            return new Intl.NumberFormat('en-LK', {
                                style: 'currency',
                                currency: 'LKR'
                            }).format(n);
                        },

                        async refreshPrice() {
                            const requestId = ++this._priceRequestId;

                            const canAbort = typeof AbortController !== 'undefined';
                            if (canAbort) {
	                                if (this._priceAbortController) {
	                                    try {
	                                        this._priceAbortController.abort();
	                                    } catch (e) {}
	                                }
	                                this._priceAbortController = new AbortController();
	                            } else {
	                                this._priceAbortController = null;
                            }

                            this.loading.price = true;
                            this.price.error = null;

                            const payload = {
                                wg: this.state.wg,
                                qty: this.state.qty,
                                width: this.state.width,
                                height: this.state.height,
                                unit: this.state.unit,
                                roll_id: this.state.roll_id || null,
                                options: this.state.options,
                                finishings: this.state.finishings,
                                artwork: {
                                    mode: this.state.artwork.mode,
                                    brief: this.state.artwork.brief,
                                },
                            };

	                            try {
	                                const res = await fetch(this.pricingQuoteUrl, {
	                                    method: 'POST',
	                                    ...(canAbort && this._priceAbortController ? {
	                                        signal: this._priceAbortController.signal
	                                    } : {}),
	                                    headers: {
	                                        'Accept': 'application/json',
	                                        'Content-Type': 'application/json',
	                                        'X-Requested-With': 'XMLHttpRequest',
	                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                            ?.getAttribute('content'),
                                    },
                                    body: JSON.stringify(payload),
                                });

                                const data = await res.json().catch(() => ({}));
                                if (!res.ok) {
                                    throw new Error(data?.message || `Pricing error (${res.status})`);
                                }

                                if (requestId !== this._priceRequestId) {
                                    return;
                                }

                                this.price.total = Number(data.total || 0);
                                this.price.breakdown = Array.isArray(data.breakdown) ? data.breakdown : [];
                                this.price.error = null;
                            } catch (e) {
                                if (e?.name === 'AbortError') {
                                    return;
                                }

                                if (requestId !== this._priceRequestId) {
                                    return;
                                }

                                this.price.total = 0;
                                this.price.breakdown = [];
                                this.price.error = e?.message ? String(e.message) : 'Unable to calculate price.';
                                console.warn(e);
                            } finally {
                                if (requestId === this._priceRequestId) {
                                    this.loading.price = false;
                                }
                            }
                        },
	                    }
	                }
	            </script>
        @endpush
    </div>
</x-home-layout>
