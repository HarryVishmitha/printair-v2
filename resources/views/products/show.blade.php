<x-home-layout :seo="$seo">
    <div x-data="productDetailsPage({
        product: @js($productJson ?? []),
        initialWg: @js($initialWg ?? 'public'),
        pricingQuoteUrl: @js(route('products.price-quote', ['product' => $product->slug])),
        cartAddUrl: @js(route('cart.items.add')),
        cartUrl: @js(route('cart.show')),
        cartUploadUrlTemplate: @js(route('cart.items.artwork.upload', ['item' => 0])),
        whatsappNumber: @js(config('printair.contact_whatsapp', '94768860175')),
        placeholderImage: @js(asset('assets/placeholders/product.png')),
    })" x-init="init()" class="bg-white">

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 lg:py-10">

            {{-- Breadcrumbs --}}
            @php
                $indexUrl = $product->product_type === 'service' ? route('services.index') : route('products.index');
                $indexLabel = $product->product_type === 'service' ? 'Services' : 'Products';
            @endphp
            <nav class="text-sm text-slate-500 mb-6">
                <a href="{{ url('/') }}" class="hover:text-slate-700">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ $indexUrl }}" class="hover:text-slate-700">{{ $indexLabel }}</a>
                <template x-if="product?.category">
                    <span>
                        <span class="mx-2">/</span>
                        <a :href="`{{ $indexUrl }}?category=${product.category.slug}`" class="hover:text-slate-700"
                            x-text="product.category.name"></a>
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
                            <div class="relative rounded-3xl border border-slate-200 overflow-hidden bg-slate-50">
                                <img :src="activeImageUrl" :alt="product?.name ?? 'Product image'"
                                    class="w-full h-[420px] sm:h-[520px] object-cover" @click="lightbox.open = true" />

                                <div
                                    class="absolute bottom-4 left-4 rounded-full bg-black/70 text-white text-xs px-3 py-1.5">
                                    Click to preview
                                </div>
                            </div>

                            <div class="mt-4 flex gap-3 overflow-x-auto pb-2">
                                <template x-for="img in product.images" :key="img.id">
                                    <button type="button"
                                        class="relative shrink-0 h-20 w-20 rounded-2xl border overflow-hidden bg-white"
                                        :class="img.url === activeImageUrl ? 'border-[#ef233c] ring-2 ring-[#ef233c]/20' :
                                            'border-slate-200 hover:border-slate-300'"
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
                                            <div class="text-sm font-semibold text-slate-900" x-text="product?.name">
                                            </div>
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
                                        <div class="mt-2 text-xs font-semibold text-rose-600" x-text="price.error">
                                        </div>
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
                                                        :class="Number(state.options[g.id]) === Number(op.id) ?
                                                            'border-[#ef233c] ring-2 ring-[#ef233c]/20 bg-[#ef233c]/[0.03]' :
                                                            ''">
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
                            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                                            <span class="iconify text-slate-700"
                                                data-icon="mdi:palette-outline"></span>
                                            Artwork
                                        </h3>
                                        <p class="mt-1 text-xs text-slate-500">
                                            Upload your design (≤ 100MB) or paste a share link. You can also hire a
                                            Printair designer.
                                        </p>
                                    </div>

                                    <span
                                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold text-slate-700">
                                        <span class="iconify" data-icon="mdi:shield-check-outline"></span>
                                        Secure & private
                                    </span>
                                </div>

                                {{-- Mode tiles --}}
                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <button type="button"
                                        class="text-left rounded-2xl border p-4 transition hover:-translate-y-[1px] hover:shadow-sm"
                                        :class="state.artwork.mode === 'upload' ?
                                            'border-[#ef233c] ring-2 ring-[#ef233c]/20 bg-[#ef233c]/[0.03]' :
                                            'border-slate-200 bg-white'"
                                        @click="state.artwork.mode='upload'; refreshPrice()">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="h-10 w-10 rounded-2xl bg-slate-900 text-white flex items-center justify-center">
                                                <iconify-icon icon="mdi:cloud-upload-outline"
                                                    class="iconify text-lg"></iconify-icon>
                                            </div>
                                            <div>
                                                <div class="font-extrabold text-slate-900">Upload my design</div>
                                                <div class="text-xs text-slate-500 mt-1">PDF / AI / PSD / JPG / PNG
                                                </div>
                                            </div>
                                        </div>
                                    </button>

                                    <button type="button"
                                        class="text-left rounded-2xl border p-4 transition hover:-translate-y-[1px] hover:shadow-sm"
                                        :class="state.artwork.mode === 'hire' ?
                                            'border-[#ef233c] ring-2 ring-[#ef233c]/20 bg-[#ef233c]/[0.03]' :
                                            'border-slate-200 bg-white'"
                                        @click="state.artwork.mode='hire'; refreshPrice()">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="h-10 w-10 rounded-2xl bg-[#ef233c] text-white flex items-center justify-center">
                                                <iconify-icon icon="mdi:account-star-outline"
                                                    class="iconify text-lg"></iconify-icon>
                                            </div>
                                            <div>
                                                <div class="font-extrabold text-slate-900">Hire Printair designer</div>
                                                <div class="text-xs text-slate-500 mt-1">We’ll design it for you</div>
                                            </div>
                                        </div>
                                    </button>
                                </div>

                                {{-- Upload panel --}}
                                <template x-if="state.artwork.mode === 'upload'">
                                    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="flex items-center justify-between gap-4">
                                            <div class="text-sm font-black text-slate-900 flex items-center gap-2">
                                                <span class="iconify text-slate-700"
                                                    data-icon="mdi:file-upload-outline"></span>
                                                Upload or Share Link
                                            </div>
                                            <div class="text-xs font-semibold text-slate-500">
                                                Max upload: <span class="text-slate-900">100MB</span>
                                            </div>
                                        </div>

                                        <div class="mt-3 gap-4">
                                            {{-- File upload --}}
                                            <div class="rounded-2xl border border-slate-200 bg-white p-4 mb-3">
                                                <label class="block text-xs font-semibold text-slate-600 mb-2">
                                                    Upload file (≤ 100MB)
                                                </label>

                                                <input type="file"
                                                    accept=".pdf,.ai,.psd,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg"
                                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                                    @change="handleArtworkUpload($event)">

                                                <template x-if="state.artwork.file_name">
                                                    <div
                                                        class="mt-3 flex items-start justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                                        <div class="min-w-0">
                                                            <div class="text-xs font-bold text-slate-900 truncate"
                                                                x-text="state.artwork.file_name"></div>
                                                            <div class="text-[11px] text-slate-500"
                                                                x-text="state.artwork.file_size_label"></div>
                                                        </div>
                                                        <button type="button"
                                                            class="text-xs font-bold text-slate-600 hover:text-slate-900"
                                                            @click="clearArtworkFile()">
                                                            Remove
                                                        </button>
                                                    </div>
                                                </template>

                                                <template x-if="state.artwork.too_large">
                                                    <div
                                                        class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                                                        <span class="font-bold">File is larger than 100MB.</span>
                                                        Please upload it to a storage service (Google
                                                        Drive/Dropbox/OneDrive) and paste the link in the field on the
                                                        right.
                                                    </div>
                                                </template>
                                            </div>

                                            {{-- External URL (always visible) --}}
                                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                                <label class="block text-xs font-semibold text-slate-600 mb-2">
                                                    Paste artwork share link (always available)
                                                </label>

                                                <div class="relative">
                                                    <span
                                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                                        <span class="iconify" data-icon="mdi:link-variant"></span>
                                                    </span>
                                                    <input type="url"
                                                        placeholder="https://drive.google.com/... or https://dropbox.com/..."
                                                        class="w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                                        x-model="state.artwork.external_url"
                                                        @input.debounce.400ms="refreshPrice()" />
                                                </div>

                                                <div class="mt-2 text-[11px] text-slate-500">
                                                    If you upload a file, you can still add a link here (optional).
                                                    Admins will see both.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- Hire panel --}}
                                <template x-if="state.artwork.mode === 'hire'">
                                    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <label class="block text-xs font-semibold text-slate-600">Design brief</label>
                                        <textarea
                                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                            rows="4" placeholder="Tell us what you need (text, colors, theme, references, deadline)…"
                                            x-model="state.artwork.brief" @input.debounce.400ms="refreshPrice()"></textarea>

                                        <div class="mt-2 text-[11px] text-slate-500 flex items-center gap-2">
                                            <span class="iconify" data-icon="mdi:information-outline"></span>
                                            We’ll confirm pricing after admin review.
                                        </div>
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
                                                <div class="text-sm font-semibold text-slate-900" x-text="t.name">
                                                </div>
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
                            <template x-if="cart.toast.message">
                                <div class="mb-3 rounded-2xl border px-4 py-3 text-sm"
                                    :class="cart.toast.type === 'success' ?
                                        'border-emerald-200 bg-emerald-50 text-emerald-800' :
                                        'border-rose-200 bg-rose-50 text-rose-800'">
                                    <div class="flex items-start gap-2">
                                        <span class="iconify mt-[2px]"
                                            :data-icon="cart.toast.type === 'success' ? 'mdi:check-circle-outline' :
                                                'mdi:alert-circle-outline'"></span>
                                        <div x-text="cart.toast.message"></div>
                                    </div>
                                </div>
                            </template>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <button type="button"
                                    class="w-full rounded-2xl bg-[#ef233c] text-white font-black py-3 hover:brightness-95 disabled:opacity-60"
                                    :disabled="cart.adding" @click="addToCart()">
                                    <span x-text="cart.adding ? 'Adding…' : 'Add to Cart'"></span>
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
                            {{-- <p x-text="product?.description ?? 'No description provided.'"></p> --}}
                            <div x-html="product?.description ?? 'No description provided.'"></div>
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
                    cartAddUrl,
                    cartUrl,
                    cartUploadUrlTemplate,
                    whatsappNumber,
                    placeholderImage
                }) {
                    return {
                        product: product || {},
                        pricingQuoteUrl,
                        cartAddUrl,
                        cartUrl,
                        cartUploadUrlTemplate,
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
                                file_name: '',
                                file_size: 0,
                                file_size_label: '',
                                too_large: false,
                                external_url: '',
                                brief: '',
                            },
                        },

                        price: {
                            total: 0,
                            breakdown: [],
                            error: null,
                        },

                        cart: {
                            adding: false,
                            toast: {
                                type: null,
                                message: null
                            },
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

                        setCartToast(type, message) {
                            this.cart.toast = {
                                type,
                                message
                            };
                            window.setTimeout(() => {
                                if (this.cart.toast.message === message) {
                                    this.cart.toast = {
                                        type: null,
                                        message: null
                                    };
                                }
                            }, 6500);
                        },

                        async addToCart() {
                            const productId = Number(this.product?.id);
                            if (!Number.isFinite(productId) || productId <= 0) {
                                this.setCartToast('error', 'Invalid product.');
                                return;
                            }

                            const qty = Math.max(1, Number(this.state.qty || 1));

                            const isDimensionBased = !!(this.product?.is_dimension_based || this.product?.requires_dimensions ||
                                this.product?.product_type === 'dimension_based');
                            if (isDimensionBased) {
                                const w = Number(this.state.width || 0);
                                const h = Number(this.state.height || 0);
                                if (!Number.isFinite(w) || !Number.isFinite(h) || w <= 0 || h <= 0) {
                                    this.price.error = 'Width and height are required for this product.';
                                    this.setCartToast('error', 'Please enter width and height.');
                                    return;
                                }
                            }

                            this.cart.adding = true;

                            try {
                                const matchedVariantSetId = (() => {
                                    const matrix = Array.isArray(this.product?.variant_matrix) ? this.product
                                        .variant_matrix : [];
                                    const selected = this.getSelectedOptionsMap();
                                    if (!matrix.length || !Object.keys(selected).length) return null;

                                    const groupIds = [...new Set(matrix.flatMap(r => Object.keys(r?.options || {}).map(
                                        x => Number(x)).filter(Boolean)))];
                                    if (!groupIds.length) return null;

                                    const isComplete = groupIds.every(gid => selected[gid]);
                                    if (!isComplete) return null;

                                    for (const row of matrix) {
                                        const map = row?.options || {};
                                        const ok = groupIds.every(gid => Number(map[gid]) === Number(selected[gid]));
                                        if (ok) return Number(row.variant_set_id || null) || null;
                                    }

                                    return null;
                                })();

                                const meta = {
                                    options: this.getSelectedOptionsMap(),
                                    finishings: this.state.finishings || {},
                                    artwork: {
                                        mode: this.state.artwork.mode,
                                        brief: this.state.artwork.brief,
                                        external_url: this.state.artwork.external_url,
                                        file_name: this.state.artwork.file_name,
                                        file_size: this.state.artwork.file_size,
                                        too_large: this.state.artwork.too_large,
                                    },
                                    artwork_external_url: this.state.artwork.external_url || null,
                                    variant_set_id: matchedVariantSetId,
                                };

                                const pricing_snapshot = {
                                    source: 'product_page',
                                    total: Number(this.price.total || 0),
                                    breakdown: Array.isArray(this.price.breakdown) ? this.price.breakdown : [],
                                    input: {
                                        wg: this.state.wg,
                                        qty,
                                        width: this.state.width,
                                        height: this.state.height,
                                        unit: this.state.unit,
                                        roll_id: this.state.roll_id || null,
                                        options: meta.options,
                                        finishings: meta.finishings,
                                        artwork: meta.artwork,
                                    },
                                };

                                const payload = {
                                    product_id: productId,
                                    qty,
                                    width: this.state.width || null,
                                    height: this.state.height || null,
                                    unit: this.state.unit || null,
                                    roll_id: this.state.roll_id || null,
                                    variant_set_item_id: null,
                                    area_sqft: null,
                                    offcut_sqft: null,
                                    pricing_snapshot,
                                    notes: null,
                                    meta,
                                };

                                const res = await fetch(this.cartAddUrl, {
                                    method: 'POST',
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
                                if (!res.ok || !data.ok) {
                                    throw new Error(data?.message || 'Unable to add to cart.');
                                }

                                // Auto-upload artwork for logged-in users if a file is selected and valid.
                                const dbItemId = Number(data?.data?.id || 0);
                                const canUpload = dbItemId > 0 && !!this.state.artwork.file && !this.state.artwork.too_large;
                                if (canUpload) {
                                    const endpoint = String(this.cartUploadUrlTemplate).replace('/0/', `/${dbItemId}/`);
                                    const fd = new FormData();
                                    fd.append('file', this.state.artwork.file);

                                    const up = await fetch(endpoint, {
                                        method: 'POST',
                                        headers: {
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                                ?.getAttribute('content'),
                                        },
                                        body: fd,
                                    });

                                    const upData = await up.json().catch(() => ({}));
                                    if (!up.ok || !upData.ok) {
                                        this.setCartToast('error', upData?.message || 'Added to cart, but upload failed.');
                                    }
                                }

                                this.setCartToast('success', 'Added to cart.');
                                try {
                                    window.dispatchEvent(new Event('cart-updated'));
                                } catch (e) {}
                            } catch (e) {
                                this.setCartToast('error', e?.message || 'Failed to add to cart.');
                            } finally {
                                this.cart.adding = false;
                            }
                        },

                        formatBytes(bytes) {
                            if (!bytes && bytes !== 0) return '';
                            const sizes = ['B', 'KB', 'MB', 'GB'];
                            const i = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), sizes.length - 1);
                            const val = (bytes / Math.pow(1024, i)).toFixed(i === 0 ? 0 : 2);
                            return `${val} ${sizes[i]}`;
                        },

                        clearArtworkFile() {
                            this.state.artwork.file = null;
                            this.state.artwork.file_name = '';
                            this.state.artwork.file_size = 0;
                            this.state.artwork.file_size_label = '';
                            this.state.artwork.too_large = false;
                        },

                        handleArtworkUpload(e) {
                            const file = e?.target?.files?.[0];
                            if (!file) return;

                            const maxBytes = 104857600; // 100MB

                            this.state.artwork.file_name = file.name;
                            this.state.artwork.file_size = file.size;
                            this.state.artwork.file_size_label = this.formatBytes(file.size);

                            if (file.size > maxBytes) {
                                this.state.artwork.too_large = true;
                                this.state.artwork.file = null;
                                e.target.value = '';
                                return;
                            }

                            this.state.artwork.too_large = false;

                            const allowedExt = ['pdf', 'ai', 'psd', 'png', 'jpg', 'jpeg'];
                            const ext = (file.name.split('.').pop() || '').toLowerCase();
                            if (!allowedExt.includes(ext)) {
                                this.state.artwork.file = null;
                                e.target.value = '';
                                alert('Invalid file type. Allowed: PDF, AI, PSD, JPG, PNG.');
                                return;
                            }

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
                                return beforeGroupIds.every(gidBefore => Number(rowMap[gidBefore]) === Number(selected[
                                    gidBefore]));
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
                                    external_url: this.state.artwork.external_url,
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
