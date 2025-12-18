<x-app-layout>
    <x-slot name="sectionTitle">Catalog</x-slot>
    <x-slot name="pageTitle">New Product</x-slot>

    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.products.index') }}" class="text-slate-500 hover:text-slate-700">Products</a>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">Create</span>
    </x-slot>

    <div class="space-y-6"
        x-data="productWizard({
            initialStep: 1,
            old: @js(old()),
        })"
        x-init="init()"
    >

        {{-- Top hero --}}
        <section class="rounded-3xl border border-slate-200/80 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-6 py-6 text-white shadow-lg shadow-slate-900/30">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium tracking-wide">
                        <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        Product Wizard · Printair v2
                    </div>
                    <h2 class="text-2xl font-bold">Create a Product</h2>
                    <p class="text-sm text-white/75 max-w-2xl">
                        Step-by-step setup for product basics, pricing rules, variants, finishings, media, and SEO — all in one flow.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" @click="resetWizard()"
                        class="rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/15">
                        Reset
                    </button>
                    <a href="{{ route('admin.products.index') }}"
                        class="rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                        Back to Products
                    </a>
                </div>
            </div>
        </section>

        {{-- Progress bar + steps --}}
        <section class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-slate-900">
                    Step <span x-text="step"></span> of <span x-text="steps.length"></span>
                    <span class="ml-2 text-slate-400 font-medium" x-text="steps[step-1]?.label"></span>
                </div>

                <div class="text-xs text-slate-500">
                    <span class="font-semibold text-slate-700" x-text="progress()"></span>% complete
                </div>
            </div>

            <div class="mt-3 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-[#ff4b5c] to-[#ff7a45]"
                    :style="`width:${progress()}%`"></div>
            </div>

            {{-- Step pills --}}
            <div class="mt-4 flex flex-wrap gap-2">
                <template x-for="(s, i) in steps" :key="s.key">
                    <button type="button"
                        @click="go(i+1)"
                        class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold ring-1 transition"
                        :class="(step === i+1)
                            ? 'bg-slate-900 text-white ring-slate-900'
                            : (i+1 < step ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-50 text-slate-600 ring-slate-200 hover:bg-slate-100')"
                    >
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full text-[11px]"
                            :class="i+1 < step ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200' "
                            x-text="i+1"></span>
                        <span x-text="s.short"></span>
                    </button>
                </template>
            </div>
        </section>

        {{-- FORM --}}
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data"
            class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            @csrf

            {{-- GLOBAL ERRORS --}}
            @if ($errors->any())
                <div class="border-b border-rose-200 bg-rose-50 px-6 py-4 text-rose-800">
                    <div class="font-semibold">Please fix the highlighted fields.</div>
                    <ul class="mt-2 list-disc pl-5 text-sm">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="p-6 space-y-6">

                {{-- STEP 1: BASICS --}}
                <section x-show="step === 1" x-cloak class="space-y-6">
                    <h3 class="text-lg font-bold text-slate-900">Basics</h3>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Product Name
                            </label>
                            <input type="text" name="name" x-model="form.name"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20"
                                placeholder="Eg: Flex Banner Printing" required>
                            @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Product Code
                            </label>
                            <input type="text" name="product_code" x-model="form.product_code"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20"
                                placeholder="Eg: PRD-FLEX-001">
                            @error('product_code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Slug
                            </label>
                            <div class="grid gap-2 md:grid-cols-[minmax(0,1fr)_auto]">
                                <input type="text" name="slug" x-model="form.slug"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20"
                                    placeholder="auto-generated-if-empty">
                                <button type="button" @click="generateSlug()"
                                    class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    Generate
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Leave empty to auto-generate from name.</p>
                            @error('slug') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Long Description
                            </label>
                            <textarea
                                name="long_description"
                                x-model="form.long_description"
                                rows="10"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-3 text-sm font-mono leading-relaxed focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20"
                                placeholder="<div class=&quot;space-y-4&quot;>\n  <p class=&quot;text-slate-700&quot;>Write your product description here…</p>\n</div>"></textarea>
                            <p class="mt-1 text-xs text-slate-500">
                                Add HTML with Tailwind classes (no editor) — this content will be rendered on the product page.
                            </p>
                            @error('long_description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Category
                            </label>
                            <select name="category_id" x-model="form.category_id"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                                <option value="">Select…</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Product Type
                            </label>
                            <select name="product_type" x-model="form.product_type"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10">
                                <option value="standard">Standard</option>
                                <option value="dimension_based">Dimension Based</option>
                                <option value="service">Service</option>
                                <option value="finishing">Finishing (internal)</option>
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Finishing is internal-only and attachable to products.</p>
                            @error('product_type') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Status
                            </label>
                            <select name="status" x-model="form.status"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="draft">Draft</option>
                            </select>
                            @error('status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Visibility
                            </label>
                            <select name="visibility" x-model="form.visibility"
                                :disabled="form.product_type === 'finishing'"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm">
                                <option value="public">Public</option>
                                <option value="internal">Internal</option>
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Internal products won’t appear in storefront listings.</p>
                            @error('visibility') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                {{-- STEP 2: PRICING RULES (PUBLIC) --}}
                <section x-show="step === 2" x-cloak class="space-y-6">
                    <h3 class="text-lg font-bold text-slate-900">Public Pricing Rules</h3>
                    <p class="text-sm text-slate-600">
                        This defines the public/base pricing. Working-group overrides can be edited later without duplicating products.
                    </p>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Minimum Quantity
                            </label>
                            <input type="number" name="pricing[min_qty]" x-model="pricing.min_qty" min="1"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm">
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Allow Tiered Pricing
                            </label>
                            <select name="pricing[allow_tiers]" x-model="pricing.allow_tiers"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>

                        <template x-if="form.product_type === 'dimension_based'">
                            <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="font-semibold text-slate-900">Dimension-based defaults</div>
                                <p class="mt-1 text-sm text-slate-600">
                                    You’ll set base rate/offcut in Product Pricing first, and optionally override per roll later.
                                </p>
                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                            Base Rate per Sqft
                                        </label>
                                        <input type="number" step="0.01" name="pricing[rate_per_sqft]" x-model="pricing.rate_per_sqft"
                                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm">
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                            Offcut Rate per Sqft
                                        </label>
                                        <input type="number" step="0.01" name="pricing[offcut_per_sqft]" x-model="pricing.offcut_per_sqft"
                                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm">
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="form.product_type === 'service'">
                            <div class="md:col-span-2 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                                <div class="font-semibold text-amber-900">Service Pricing</div>
                                <p class="mt-1 text-sm text-amber-800">
                                    Manual price is allowed in estimates/orders, but you can still define a default base price here.
                                </p>
                                <div class="mt-4">
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-amber-700">
                                        Default Base Price
                                    </label>
                                    <input type="number" step="0.01" name="pricing[base_price]" x-model="pricing.base_price"
                                        class="w-full rounded-2xl border border-amber-200 bg-white px-4 py-2.5 text-sm">
                                </div>
                            </div>
                        </template>

                        <template x-if="form.product_type === 'standard' || form.product_type === 'finishing'">
                            <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="font-semibold text-slate-900">Standard / Finishing Pricing</div>
                                <p class="mt-1 text-sm text-slate-600">
                                    Set the public base price used when no special pricing overrides are applied.
                                </p>
                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                            Public Base Price
                                        </label>
                                        <input type="number" step="0.01" name="pricing[base_price]" x-model="pricing.base_price"
                                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>

                {{-- STEP 3: OPTIONS & VARIANTS --}}
                <section x-show="step === 3" x-cloak class="space-y-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Options & Variants</h3>
                            <p class="text-sm text-slate-600">
                                Define option groups (Color, Type, Size) and then enable only valid combinations as variants.
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="addOptionGroup()"
                                class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                                + Add Option Group
                            </button>

                            <button type="button" @click="rebuildCombinations()"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Rebuild Variants
                            </button>
                        </div>
                    </div>

                    {{-- Hidden payload to backend --}}
                    <input type="hidden" name="variants_payload" :value="JSON.stringify(variantsPayload())">

                    {{-- OPTION GROUPS --}}
                    <div class="grid gap-4">
                        <template x-for="(g, gi) in options" :key="g.id">
                            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex-1">
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                            Option Group Name
                                        </label>
                                        <input type="text" x-model="g.name" @input.debounce.300ms="syncGroupSlug(g)"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20"
                                            placeholder="Eg: Color, Mug Type, Size" />
                                        <p class="mt-1 text-xs text-slate-500">
                                            Slug: <span class="font-mono text-slate-700" x-text="g.slug"></span>
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="addOptionValue(g)"
                                            class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                            + Add Value
                                        </button>

                                        <button type="button" @click="removeOptionGroup(gi)"
                                            class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                                            Remove
                                        </button>
                                    </div>
                                </div>

                                {{-- VALUES --}}
                                <div class="mt-4">
                                    <div class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                        Values
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="(v, vi) in g.values" :key="v.id">
                                            <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2">
                                                <input type="text" x-model="v.label" @input.debounce.250ms="syncValueSlug(g, v)"
                                                    class="w-40 bg-transparent text-sm outline-none"
                                                    placeholder="Eg: Black" />
                                                <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-600 ring-1 ring-slate-200"
                                                    x-text="v.slug"></span>

                                                <button type="button" @click="removeOptionValue(g, vi)"
                                                    class="text-slate-400 hover:text-rose-600">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>

                                        <template x-if="g.values.length === 0">
                                            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                                No values yet — click “Add Value”.
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="options.length === 0">
                            <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                                <h4 class="text-sm font-semibold text-slate-900">No option groups yet</h4>
                                <p class="mt-1 text-sm text-slate-500">
                                    Add groups like “Color”, “Material”, “Mug Type”, “Size” then generate variants.
                                </p>
                            </div>
                        </template>
                    </div>

                    {{-- VARIANT COMBINATIONS --}}
                    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="text-sm font-bold text-slate-900">Generated Variants</div>
                                    <div class="text-xs text-slate-500">
                                        Toggle off invalid combinations to exclude them from creation.
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button type="button" @click="enableAllVariants()"
                                        class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                        Enable all
                                    </button>
                                    <button type="button" @click="disableAllVariants()"
                                        class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                        Disable all
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <template x-if="variants.length === 0">
                                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-8 text-center text-sm text-slate-600">
                                    Add option groups + values, then click <span class="font-semibold">Rebuild Variants</span>.
                                </div>
                            </template>

                            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3" x-show="variants.length > 0" x-cloak>
                                <template x-for="(vr, i) in variants" :key="vr.key">
                                    <label class="flex items-start gap-3 rounded-2xl border px-4 py-3 cursor-pointer transition"
                                        :class="vr.enabled ? 'border-emerald-200 bg-emerald-50/60' : 'border-slate-200 bg-white opacity-70'">

                                        <input type="checkbox" class="mt-1.5"
                                            x-model="vr.enabled">

                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-slate-900 truncate" x-text="vr.label"></div>
                                            <div class="mt-1 flex flex-wrap gap-1.5">
                                                <template x-for="t in vr.tags" :key="t">
                                                    <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-600 ring-1 ring-slate-200"
                                                        x-text="t"></span>
                                                </template>
                                            </div>

                                            <div class="mt-2 text-[11px] text-slate-500 font-mono truncate" x-text="vr.key"></div>

                                            <div class="mt-3 flex items-center gap-2">
                                                <span class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">
                                                    Variant Price (optional)
                                                </span>
                                                <input type="number" step="0.01" min="0"
                                                    x-model="vr.price"
                                                    class="w-28 rounded-2xl border border-slate-200 bg-white px-2 py-1 text-xs focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10"
                                                    placeholder="0.00" />
                                            </div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Note for your future WG disable logic --}}
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm text-slate-700">
                        <span class="font-semibold">FYI:</span> Step 3 decides which variants exist globally.
                        Later, in Working Group pricing, admins can disable variants per group without deleting them globally.
                    </div>
                </section>

                {{-- STEP 4: ROLLS --}}
                <section x-show="step === 4" x-cloak class="space-y-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Allowed Rolls</h3>
                            <p class="text-sm text-slate-600">
                                Only required for <span class="font-semibold">dimension-based</span> products.
                                Select which roll widths/materials can be used for this product.
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button"
                                @click="selectAllRolls(true)"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Enable all
                            </button>
                            <button type="button"
                                @click="selectAllRolls(false)"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Clear
                            </button>
                        </div>
                    </div>

                    {{-- Show only when dimension_based --}}
                    <template x-if="form.product_type !== 'dimension_based'">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm text-slate-700">
                            Rolls are not needed for this product type.
                        </div>
                    </template>

                    <template x-if="form.product_type === 'dimension_based'">
                        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                            <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div class="text-sm font-bold text-slate-900">Roll Library</div>

                                    <div class="flex items-center gap-2">
                                        <input type="text" x-model="rollSearch"
                                            placeholder="Search roll name/material…"
                                            class="w-full md:w-72 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-[#ff4b5c] focus:ring-2 focus:ring-[#ff4b5c]/20">
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-white">
                                        <tr>
                                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Allow
                                            </th>
                                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Roll
                                            </th>
                                            <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Material
                                            </th>
                                            <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Width (in)
                                            </th>
                                            <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Width (ft)
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @foreach ($rolls as $roll)
                                            <tr class="hover:bg-sky-50/40 transition"
                                                x-show="rollMatches('{{ addslashes($roll->name) }}', '{{ addslashes($roll->material_type) }}', '{{ addslashes($roll->slug) }}')">
                                                <td class="px-6 py-4 align-top">
                                                    <label class="inline-flex items-center gap-2">
                                                        <input type="checkbox"
                                                            name="roll_ids[]"
                                                            value="{{ $roll->id }}"
                                                            x-model="selectedRollIds"
                                                            class="rounded border-slate-300">
                                                        <span class="text-xs text-slate-500">Allow</span>
                                                    </label>
                                                </td>

                                                <td class="px-6 py-4 align-top">
                                                    <div class="font-semibold text-slate-900">{{ $roll->name }}</div>
                                                    <div class="text-xs text-slate-500">{{ $roll->slug }}</div>
                                                </td>

                                                <td class="px-4 py-4 align-top">
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                                        {{ $roll->material_type }}
                                                    </span>
                                                </td>

                                                <td class="px-4 py-4 align-top">
                                                    <span class="font-mono text-slate-700">{{ number_format((float) $roll->width_in, 3) }}</span>
                                                </td>

                                                <td class="px-4 py-4 align-top">
                                                    <span class="font-mono text-slate-700">{{ number_format(((float) $roll->width_in) / 12, 2) }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Helpful note --}}
                            <div class="border-t border-slate-100 bg-slate-50/60 px-6 py-4 text-xs text-slate-600">
                                Tip: If a banner is bigger than your roll width, the estimator can rotate width/height later — but only
                                among rolls selected here.
                            </div>
                        </div>
                    </template>

                    {{-- Error slot --}}
                    @error('roll_ids')
                        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm text-rose-800">
                            {{ $message }}
                        </div>
                    @enderror
                </section>

                {{-- STEP 5: FINISHINGS --}}
                <section x-show="step === 5" x-cloak class="space-y-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Finishings</h3>
                            <p class="text-sm text-slate-600">
                                Attach finishing options (internal products) like eyelets, pockets, lamination, etc.
                                Pricing is handled later in the Pricing module.
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button"
                                @click="selectAllFinishings(true)"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Enable all
                            </button>
                            <button type="button"
                                @click="selectAllFinishings(false)"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Clear
                            </button>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div class="text-sm font-bold text-slate-900">Finishing Library</div>

                                <div class="flex items-center gap-2">
                                    <input type="text" x-model="finishingSearch"
                                        placeholder="Search finishing name/code…"
                                        class="w-full md:w-72 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-[#ff4b5c] focus:ring-2 focus:ring-[#ff4b5c]/20">
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Enable
                                        </th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Finishing
                                        </th>
                                        <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Mode
                                        </th>
                                        <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Min
                                        </th>
                                        <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Max
                                        </th>
                                        <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Required
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach ($finishings as $f)
                                        <tr class="hover:bg-sky-50/40 transition"
                                            x-show="finishingMatches('{{ addslashes($f->name) }}', '{{ addslashes($f->product_code ?? '') }}', '{{ addslashes($f->slug ?? '') }}')">
                                            {{-- Enable --}}
                                            <td class="px-6 py-4 align-top">
                                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox"
                                        name="finishings[]"
                                        value="{{ (string) $f->id }}"
                                        x-model="selectedFinishingIds"
                                        class="rounded border-slate-300">
                                                    <span class="text-xs text-slate-500">Enable</span>
                                                </label>
                                            </td>

                                            {{-- Finishing --}}
                                            <td class="px-6 py-4 align-top">
                                                <div class="font-semibold text-slate-900">{{ $f->name }}</div>
                                                <div class="text-xs text-slate-500">
                                                    {{ $f->product_code ? $f->product_code . ' · ' : '' }}{{ $f->slug }}
                                                </div>
                                            </td>

                                            {{-- Mode --}}
                                            <td class="px-4 py-4 align-top">
                                                <select
                                                    :disabled="!selectedFinishingIds.includes('{{ (string) $f->id }}')"
                                                    name="finishing_config[{{ $f->id }}][pricing_mode]"
                                                    class="w-44 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 disabled:bg-slate-100 disabled:text-slate-500">
                                                    <option value="per_piece">Per piece</option>
                                                    <option value="per_side">Per side</option>
                                                    <option value="flat">Flat</option>
                                                </select>
                                            </td>

                                            {{-- Min --}}
                                            <td class="px-4 py-4 align-top">
                                                <input type="number" min="0"
                                                    :disabled="!selectedFinishingIds.includes('{{ (string) $f->id }}')"
                                                    name="finishing_config[{{ $f->id }}][min_qty]"
                                                    placeholder="0"
                                                    class="w-24 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 disabled:bg-slate-100 disabled:text-slate-500">
                                            </td>

                                            {{-- Max --}}
                                            <td class="px-4 py-4 align-top">
                                                <input type="number" min="0"
                                                    :disabled="!selectedFinishingIds.includes('{{ (string) $f->id }}')"
                                                    name="finishing_config[{{ $f->id }}][max_qty]"
                                                    placeholder="—"
                                                    class="w-24 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 disabled:bg-slate-100 disabled:text-slate-500">
                                            </td>

                                            {{-- Required --}}
                                            <td class="px-4 py-4 align-top">
                                                <label class="inline-flex items-center gap-2">
                                                    <input type="checkbox"
                                                        :disabled="!selectedFinishingIds.includes('{{ (string) $f->id }}')"
                                                        name="finishing_config[{{ $f->id }}][is_required]"
                                                        value="1"
                                                        class="rounded border-slate-300 disabled:opacity-50">
                                                    <span class="text-xs text-slate-600">Required</span>
                                                </label>
                                            </td>

                                            {{-- optional sort --}}
                                            <input type="hidden"
                                                name="finishing_config[{{ $f->id }}][sort_index]"
                                                :value="finishingSortIndex('{{ (string) $f->id }}')">
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="border-t border-slate-100 bg-slate-50/60 px-6 py-4 text-xs text-slate-600">
                            Tip: Min/Max controls the allowed quantity range in estimates/orders (e.g., Eyelets max 20, Pockets max 2).
                        </div>
                    </div>

                    @error('finishings')
                        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm text-rose-800">
                            {{ $message }}
                        </div>
                    @enderror
                </section>

                <section x-show="step === 6" x-cloak class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Media</h3>
                        <p class="text-sm text-slate-600">
                            Upload product images and optional attachments (print templates / guides / PDFs). Set a primary image and arrange order.
                        </p>
                    </div>

                    {{-- IMAGES --}}
                    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4 flex items-center justify-between">
                            <div class="text-sm font-bold text-slate-900">Product Images</div>
                            <div class="text-xs text-slate-500">Max 12 · JPG/PNG/WEBP · 10MB each</div>
                        </div>

                        <div class="p-6 space-y-4"
                            x-data="productMediaImages()"
                            x-init="init()">

                            <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/40 px-5 py-6">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">Upload images</div>
                                        <div class="text-xs text-slate-500">Drag & drop or choose files</div>
                                    </div>

                                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V7.5m0 0L8.25 11.25M12 7.5l3.75 3.75M6 20.25h12" />
                                        </svg>
                                        Choose images
                                        <input type="file" name="images[]" multiple accept="image/*" class="hidden" @change="handleFiles($event)">
                                    </label>
                                </div>
                            </div>

                            {{-- Preview grid --}}
                            <template x-if="previews.length">
                                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <template x-for="(img, idx) in previews" :key="img.key">
                                        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                                            <div class="relative">
                                                <img :src="img.url" class="h-44 w-full object-cover">

                                                {{-- Primary badge --}}
                                                <div class="absolute left-3 top-3">
                                                    <label class="inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1 text-xs font-bold text-slate-800 shadow">
                                                        <input type="radio" name="primary_image_index" :value="idx" x-model="primaryIndex" class="rounded border-slate-300">
                                                        Primary
                                                    </label>
                                                </div>

                                                {{-- Remove --}}
                                                <button type="button"
                                                    @click="remove(idx)"
                                                    class="absolute right-3 top-3 rounded-xl bg-black/50 p-2 text-white hover:bg-black/70">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="p-4 flex items-center justify-between gap-2">
                                                <div class="text-xs text-slate-500 truncate" x-text="img.name"></div>

                                                <div class="flex items-center gap-2">
                                                    <button type="button" @click="move(idx, -1)"
                                                        class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                        ↑
                                                    </button>
                                                    <button type="button" @click="move(idx, 1)"
                                                        class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                        ↓
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- sort index hidden --}}
                                            <input type="hidden" name="image_sort[]" :value="idx">
                                        </div>
                                    </template>
                                </div>
                            </template>

                            @error('images') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                            @error('images.*') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- ATTACHMENTS --}}
                    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4 flex items-center justify-between">
                            <div class="text-sm font-bold text-slate-900">Attachments (Optional)</div>
                            <div class="text-xs text-slate-500">PDF/AI/PSD/SVG/ZIP… · 25MB each</div>
                        </div>

                        <div class="p-6 space-y-4" x-data="productMediaAttachments()" x-init="init()">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">Upload attachments</div>
                                    <div class="text-xs text-slate-500">e.g., print guide PDF, template file, etc.</div>
                                </div>

                                <label class="inline-flex cursor-pointer items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V7.5m0 0L8.25 11.25M12 7.5l3.75 3.75M6 20.25h12" />
                                    </svg>
                                    Choose files
                                    <input type="file" name="attachments[]" multiple class="hidden" @change="handleFiles($event)">
                                </label>
                            </div>

                            <template x-if="files.length">
                                <div class="rounded-2xl border border-slate-200 overflow-hidden">
                                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                                        <thead class="bg-slate-50/60">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">File</th>
                                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Order</th>
                                                <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            <template x-for="(f, idx) in files" :key="f.key">
                                                <tr>
                                                    <td class="px-4 py-3">
                                                        <div class="font-semibold text-slate-900" x-text="f.name"></div>
                                                        <div class="text-xs text-slate-500" x-text="f.type"></div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" @click="move(idx, -1)"
                                                                class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">↑</button>
                                                            <button type="button" @click="move(idx, 1)"
                                                                class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">↓</button>
                                                        </div>
                                                        <input type="hidden" name="attachment_sort[]" :value="idx">
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <button type="button" @click="remove(idx)"
                                                            class="rounded-xl bg-rose-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-rose-700">
                                                            Remove
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>

                            @error('attachments') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                            @error('attachments.*') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <section x-show="step === 7" x-cloak class="space-y-6">
                    <div class="space-y-1">
                        <h3 class="text-lg font-bold text-slate-900">SEO & Social</h3>
                        <p class="text-sm text-slate-600">
                            Optimize how this product appears in search engines and when shared on social. Defaults are prefilled from Basics, but you can fine‑tune everything.
                        </p>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-3">
                        <div class="lg:col-span-2 space-y-5">
                            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm space-y-4">
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-900">Search Metadata</h4>
                                    <p class="text-xs text-slate-500">
                                        These fields map to &lt;title&gt; and meta description/keywords tags.
                                    </p>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                            SEO Title
                                        </label>
                                        <input type="text"
                                            name="seo[seo_title]"
                                            x-model="seo.seo_title"
                                            maxlength="160"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10"
                                            placeholder="Eg: {{ config('app.name') }} – High Quality Printing">
                                        <p class="mt-1 text-[11px] text-slate-500">
                                            Recommended up to 60–70 characters. Uses product name by default.
                                        </p>
                                        @error('seo.seo_title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                            SEO Description
                                        </label>
                                        <textarea
                                            name="seo[seo_description]"
                                            x-model="seo.seo_description"
                                            rows="3"
                                            maxlength="255"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10"
                                            placeholder="Short, compelling summary that will appear under the title in search results."></textarea>
                                        <p class="mt-1 text-[11px] text-slate-500">
                                            Recommended 120–160 characters. Defaults to product short description when available.
                                        </p>
                                        @error('seo.seo_description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                            SEO Keywords
                                        </label>
                                        <input type="text"
                                            name="seo[seo_keywords]"
                                            x-model="seo.seo_keywords"
                                            maxlength="255"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm"
                                            placeholder="Eg: flex banner printing, outdoor vinyl, large format">
                                        <p class="mt-1 text-[11px] text-slate-500">
                                            Optional. Comma‑separated phrases you want associated with this product.
                                        </p>
                                        @error('seo.seo_keywords') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm space-y-4">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <h4 class="text-sm font-semibold text-slate-900">Indexing & Canonical</h4>
                                        <p class="text-xs text-slate-500">
                                            Control whether search engines can index this product and set an optional canonical URL.
                                        </p>
                                    </div>
                                    <label class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white shadow-sm">
                                        <input type="checkbox"
                                            name="seo[is_indexable]"
                                            x-model="seo.is_indexable"
                                            :true-value="1"
                                            :false-value="0"
                                            class="h-4 w-4 rounded border-slate-300 text-slate-900">
                                        <span>Allow indexing</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                        Canonical URL (optional)
                                    </label>
                                    <input type="url"
                                        name="seo[canonical_url]"
                                        x-model="seo.canonical_url"
                                        maxlength="500"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm"
                                        placeholder="Eg: https://example.com/products/flex-banner-printing">
                                    <p class="mt-1 text-[11px] text-slate-500">
                                        Useful when the product is duplicated across multiple URLs. Leave empty in most cases.
                                    </p>
                                    @error('seo.canonical_url') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm space-y-4">
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-900">Social Sharing (Open Graph)</h4>
                                    <p class="text-xs text-slate-500">
                                        Controls how this product looks when shared on platforms like Facebook, LinkedIn, and WhatsApp.
                                    </p>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                            OG Title
                                        </label>
                                        <input type="text"
                                            name="seo[og_title]"
                                            x-model="seo.og_title"
                                            maxlength="160"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm">
                                        @error('seo.og_title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                            OG Description
                                        </label>
                                        <textarea
                                            name="seo[og_description]"
                                            x-model="seo.og_description"
                                            rows="2"
                                            maxlength="255"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm"></textarea>
                                        @error('seo.og_description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="space-y-2">
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                            OG Image
                                        </label>
                                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 px-4 py-4 space-y-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="text-xs text-slate-500">
                                                    Recommended 1200×630px · JPG/PNG/WEBP · up to 10MB.
                                                </div>
                                                <label class="inline-flex cursor-pointer items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V7.5m0 0L8.25 11.25M12 7.5l3.75 3.75M6 20.25h12" />
                                                    </svg>
                                                    Upload image
                                                    <input type="file"
                                                        name="seo[og_image]"
                                                        accept="image/*"
                                                        class="hidden"
                                                        @change="handleOgImagePreview($event)">
                                                </label>
                                            </div>

                                            <template x-if="seoOgPreviewUrl">
                                                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                                    <img :src="seoOgPreviewUrl" class="h-32 w-full object-cover" alt="OG image preview">
                                                </div>
                                            </template>
                                        </div>
                                        @error('seo.og_image') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section x-show="step === 8" x-cloak class="space-y-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Review & Create</h3>
                            <p class="text-sm text-slate-600">
                                Quick preview of how this product will behave in the storefront once created. You can go back and adjust any step.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold"
                                :class="form.status === 'active'
                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                    : (form.status === 'inactive'
                                        ? 'border-slate-200 bg-slate-50 text-slate-600'
                                        : 'border-amber-200 bg-amber-50 text-amber-700')">
                                <span class="h-1.5 w-1.5 rounded-full"
                                    :class="form.status === 'active'
                                        ? 'bg-emerald-500'
                                        : (form.status === 'inactive' ? 'bg-slate-400' : 'bg-amber-500')"></span>
                                <span x-text="(form.status || 'draft').toUpperCase()"></span>
                            </span>

                            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold"
                                :class="seo.is_indexable ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'">
                                <span class="h-1.5 w-1.5 rounded-full"
                                    :class="seo.is_indexable ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                <span x-text="seo.is_indexable ? 'INDEXABLE' : 'NOINDEX'"></span>
                            </span>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
                        <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4 flex items-center justify-between">
                            <div class="text-sm font-bold text-slate-900">Public-like Preview</div>
                            <div class="text-xs text-slate-500">Visual summary only · final data saves on Create</div>
                        </div>

                        <div class="p-6 grid gap-6 lg:grid-cols-3">
                            {{-- Left: main details + SEO snippet --}}
                            <div class="lg:col-span-2 space-y-5">
                                <div>
                                    <div class="text-xs text-slate-500 font-semibold uppercase tracking-wide">
                                        <span x-text="form.product_code || 'NO-CODE'"></span>
                                    </div>
                                    <h1 class="mt-1 text-2xl font-extrabold text-slate-900 leading-tight truncate">
                                        <span x-text="form.name || 'Untitled product'"></span>
                                    </h1>
                                    <p class="mt-2 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            <span x-text="form.product_type"></span>
                                        </span>
                                        <span class="ml-2 inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            <span x-text="form.visibility"></span>
                                        </span>
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                        SEO Preview
                                    </div>
                                    <div class="mt-2 space-y-1">
                                        <div class="text-sm font-semibold text-slate-900 truncate">
                                            <span x-text="seo.seo_title || form.name || 'Page title will use product name'"></span>
                                        </div>
                                        <div class="text-xs text-slate-600 line-clamp-2">
                                            <span x-text="seo.seo_description || 'Meta description not set yet. Consider adding a 120–160 character summary.'"></span>
                                        </div>
                                        <div class="text-[11px] text-emerald-700 mt-1">
                                            <span x-text="(window.location.origin || '') + '/products/' + (form.slug || 'slug-auto')"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                        Description Preview
                                    </div>

                                    <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 overflow-x-auto">
                                        <template x-if="form.long_description && form.long_description.trim().length">
                                            <div class="text-sm text-slate-800" x-html="form.long_description"></div>
                                        </template>
                                        <template x-if="!(form.long_description && form.long_description.trim().length)">
                                            <div class="text-sm text-slate-500">
                                                No description added yet. Go back to Step 1 to add it.
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Right: checklist / summary --}}
                            <div class="space-y-4">
                                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-sm font-bold text-slate-900">Quick Checklist</div>
                                    <ul class="mt-3 space-y-2 text-xs text-slate-700">
                                        <li>
                                            <span class="font-semibold">Images:</span>
                                            <span>Uploaded via Step 6 when you submit.</span>
                                        </li>
                                        <li>
                                            <span class="font-semibold">Pricing:</span>
                                            <span x-text="pricingSummary()"></span>
                                        </li>
                                        <li>
                                            <span class="font-semibold">Variants:</span>
                                            <span x-text="variantSummary()"></span>
                                        </li>
                                        <li>
                                            <span class="font-semibold">Rolls:</span>
                                            <span x-text="rollSummary()"></span>
                                        </li>
                                        <li>
                                            <span class="font-semibold">Finishings:</span>
                                            <span x-text="finishingSummary()"></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Fix Issues checklist (wizard-state based) --}}
                    <div
                        class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden"
                        x-data="{
                            goTo(stepNo) {
                                if (typeof step !== 'undefined') {
                                    step = stepNo;
                                    this.$nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
                                }
                            }
                        }"
                    >
                        <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-bold text-slate-900">Fix Issues Checklist</div>
                                <div class="text-xs text-slate-500">Uses current wizard data. Click to jump to the step to fix.</div>
                            </div>

                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold ring-1"
                                :class="issuesCountWizard() === 0
                                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                    : 'bg-amber-50 text-amber-700 ring-amber-200'">
                                <span class="h-1.5 w-1.5 rounded-full"
                                    :class="issuesCountWizard() === 0 ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                <span x-text="issuesCountWizard() === 0 ? 'READY TO CREATE' : (issuesCountWizard() + ' ISSUE(S) FOUND')"></span>
                            </span>
                        </div>

                        <div class="p-6">
                            <div class="grid gap-3">
                                {{-- Pricing --}}
                                <div class="flex items-start justify-between rounded-2xl border px-4 py-3"
                                    :class="hasPublicPricingWizard() ? 'border-emerald-200 bg-emerald-50/40' : 'border-amber-200 bg-amber-50/60'">
                                    <div class="flex gap-3">
                                        <div class="mt-0.5">
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl"
                                                :class="hasPublicPricingWizard() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800'">
                                                <span x-text="hasPublicPricingWizard() ? '✓' : '!'"></span>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-900">Public pricing</div>
                                            <div class="text-xs text-slate-600">
                                                Required so storefront knows how to estimate this product.
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button"
                                        x-show="!hasPublicPricingWizard()"
                                        @click="goTo(2)"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800">
                                        Go to Step 2
                                    </button>
                                </div>

                                {{-- Media --}}
                                <div class="flex items-start justify-between rounded-2xl border px-4 py-3"
                                    :class="hasImagesWizard() ? 'border-emerald-200 bg-emerald-50/40' : 'border-amber-200 bg-amber-50/60'">
                                    <div class="flex gap-3">
                                        <div class="mt-0.5">
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl"
                                                :class="hasImagesWizard() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800'">
                                                <span x-text="hasImagesWizard() ? '✓' : '!'"></span>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-900">Images</div>
                                            <div class="text-xs text-slate-600">
                                                Strongly recommended. Impacts trust and conversions.
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button"
                                        x-show="!hasImagesWizard()"
                                        @click="goTo(6)"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800">
                                        Go to Step 6
                                    </button>
                                </div>

                                {{-- Rolls (dimension-based only) --}}
                                <div class="flex items-start justify-between rounded-2xl border px-4 py-3"
                                    x-show="form.product_type === 'dimension_based'"
                                    :class="hasRollBindingsWizard() ? 'border-emerald-200 bg-emerald-50/40' : 'border-amber-200 bg-amber-50/60'">
                                    <div class="flex gap-3">
                                        <div class="mt-0.5">
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl"
                                                :class="hasRollBindingsWizard() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800'">
                                                <span x-text="hasRollBindingsWizard() ? '✓' : '!'"></span>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-900">Roll bindings</div>
                                            <div class="text-xs text-slate-600">
                                                Recommended for dimension-based products (width constraints).
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button"
                                        x-show="!hasRollBindingsWizard()"
                                        @click="goTo(4)"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800">
                                        Go to Step 4
                                    </button>
                                </div>

                                {{-- SEO --}}
                                <div class="flex items-start justify-between rounded-2xl border px-4 py-3"
                                    :class="hasSeoOkWizard() ? 'border-emerald-200 bg-emerald-50/40' : 'border-amber-200 bg-amber-50/60'">
                                    <div class="flex gap-3">
                                        <div class="mt-0.5">
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl"
                                                :class="hasSeoOkWizard() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800'">
                                                <span x-text="hasSeoOkWizard() ? '✓' : '!'"></span>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-900">SEO meta</div>
                                            <div class="text-xs text-slate-600">
                                                Recommended (title + description). Improves search & sharing.
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button"
                                        x-show="!hasSeoOkWizard()"
                                        @click="goTo(7)"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800">
                                        Go to Step 7
                                    </button>
                                </div>

                                {{-- Optional panels --}}
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3">
                                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Variants</div>
                                        <div class="mt-1 text-sm font-bold text-slate-900" x-text="hasVariantsWizard() ? 'Configured' : 'None added'"></div>
                                        <div class="mt-2">
                                            <button type="button" @click="goTo(3)" class="text-xs font-bold text-[#ff4b5c] hover:text-[#ff2a3c]">
                                                Manage variants →
                                            </button>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3">
                                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Finishings</div>
                                        <div class="mt-1 text-sm font-bold text-slate-900" x-text="hasFinishingsWizard() ? 'Linked' : 'None linked'"></div>
                                        <div class="mt-2">
                                            <button type="button" @click="goTo(5)" class="text-xs font-bold text-[#ff4b5c] hover:text-[#ff2a3c]">
                                                Manage finishings →
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Footer controls --}}
            <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                <button type="button" @click="prev()"
                    class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    :disabled="step === 1"
                    :class="step === 1 ? 'opacity-50 cursor-not-allowed' : ''">
                    Back
                </button>

                <div class="flex items-center gap-2">
                    <button type="button" x-show="step < steps.length" @click="next()"
                        class="rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                        Next
                    </button>

                    <button type="submit" x-show="step === steps.length"
                        class="rounded-2xl bg-gradient-to-r from-[#ff4b5c] to-[#ff7a45] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#ff4b5c]/20 hover:shadow-lg">
                        Create Product
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Alpine logic --}}
    <script>
        function productWizard({ initialStep = 1, old = {} }) {
            return {
                step: initialStep,
                steps: [
                    { key: 'basics', label: 'Basics', short: 'Basics' },
                    { key: 'pricing', label: 'Public Pricing Rules', short: 'Pricing' },
                    { key: 'variants', label: 'Options & Variants', short: 'Variants' },
                    { key: 'rolls', label: 'Allowed Rolls', short: 'Rolls' },
                    { key: 'finishings', label: 'Finishings', short: 'Finishings' },
                    { key: 'media', label: 'Images & Attachments', short: 'Media' },
                    { key: 'seo', label: 'SEO', short: 'SEO' },
                    { key: 'review', label: 'Review & Create', short: 'Review' },
                ],

                form: {
                    name: old.name ?? '',
                    product_code: old.product_code ?? '',
                    slug: old.slug ?? '',
                    long_description: old.long_description ?? old.description ?? '',
                    category_id: old.category_id ?? '',
                    product_type: old.product_type ?? 'standard',
                    status: old.status ?? 'active',
                    visibility: old.visibility ?? 'public',
                },

                pricing: {
                    min_qty: old?.pricing?.min_qty ?? 1,
                    allow_tiers: old?.pricing?.allow_tiers ?? '1',
                    rate_per_sqft: old?.pricing?.rate_per_sqft ?? '',
                    offcut_per_sqft: old?.pricing?.offcut_per_sqft ?? '',
                    base_price: old?.pricing?.base_price ?? '',
                },

                options: old?.variants_payload ? (JSON.parse(old.variants_payload)?.options ?? []) : [],
                variants: old?.variants_payload ? (JSON.parse(old.variants_payload)?.variants ?? []) : [],

                seo: {
                    seo_title: old?.seo?.seo_title ?? '',
                    seo_description: old?.seo?.seo_description ?? '',
                    seo_keywords: old?.seo?.seo_keywords ?? '',
                    og_title: old?.seo?.og_title ?? '',
                    og_description: old?.seo?.og_description ?? '',
                    canonical_url: old?.seo?.canonical_url ?? '',
                    is_indexable: old?.seo?.is_indexable ?? 1,
                },

                seoOgPreviewUrl: null,

                rollSearch: '',
                selectedRollIds: @json(old('roll_ids', [])),

                finishingSearch: '',
                selectedFinishingIds: @json(collect(old('finishings', []))->map(fn($v) => (string) $v)->values()),

                init() {
                    const hasOld = !!Object.keys(old || {}).length;

                    if (!hasOld) {
                        try {
                            const cached = localStorage.getItem('productWizard.create.state');
                            if (cached) {
                                const parsed = JSON.parse(cached);
                                if (parsed && typeof parsed === 'object') {
                                    if (parsed.step) this.step = parsed.step;
                                    if (parsed.form) this.form = { ...this.form, ...parsed.form };
                                    if (parsed.pricing) this.pricing = { ...this.pricing, ...parsed.pricing };
                                    if (parsed.seo) this.seo = { ...this.seo, ...parsed.seo };
                                }
                            }
                        } catch (e) {
                            // ignore malformed cache
                        }
                    }

                    this.$watch('form.product_type', (t) => {
                        if (t === 'finishing') {
                            this.form.visibility = 'internal';
                        }
                    });

                    if (this.form.product_type === 'finishing') {
                        this.form.visibility = 'internal';
                    }
                },

                progress() {
                    return Math.round((this.step - 1) / (this.steps.length - 1) * 100);
                },

                go(n) {
                    if (n < 1 || n > this.steps.length) return;
                    this.step = n;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    this.persistState();
                },

                next() {
                    // Minimal front-end gating: don’t allow moving forward if basic required fields are empty.
                    if (this.step === 1) {
                        if (!this.form.name || this.form.name.trim().length < 2) {
                            alert('Please enter a valid product name.');
                            return;
                        }
                    }
                    if (this.step === 3) {
                        const payload = this.variantsPayload();
                        if (payload.options.length > 0 && payload.variants.length === 0) {
                            alert('You added options but disabled all variants. Enable at least one valid combination.');
                            return;
                        }
                    }
                    if (this.step === 4 && this.form.product_type === 'dimension_based') {
                        if (!this.selectedRollIds || this.selectedRollIds.length === 0) {
                            alert('Select at least one roll for dimension-based products.');
                            return;
                        }
                    }
                    if (this.step === 6) {
                        this.seedSeoDefaults();
                    }
                    this.go(this.step + 1);
                },

                prev() {
                    this.go(this.step - 1);
                },

                resetWizard() {
                    if (!confirm('Reset the wizard? All unsaved fields will be cleared.')) return;
                    try {
                        localStorage.removeItem('productWizard.create.state');
                    } catch (e) {
                        // ignore
                    }
                    window.location.href = "{{ route('admin.products.create') }}";
                },

                generateSlug() {
                    const s = (this.form.name || '')
                        .toLowerCase()
                        .trim()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-');
                    this.form.slug = s;
                },

                selectAllRolls(enable) {
                    if (this.form.product_type !== 'dimension_based') return;

                    if (enable) {
                        // Select all rolls visible in Blade: simplest is to take all IDs from backend
                        this.selectedRollIds = @json($rolls->pluck('id')->map(fn($v) => (string)$v)->values());
                    } else {
                        this.selectedRollIds = [];
                    }
                },

                rollMatches(name, material, slug) {
                    const q = (this.rollSearch || '').toLowerCase().trim();
                    if (!q) return true;
                    return (name + ' ' + material + ' ' + slug).toLowerCase().includes(q);
                },

                selectAllFinishings(enable) {
                    if (enable) {
                        this.selectedFinishingIds = @json($finishings->pluck('id')->map(fn($v) => (string)$v)->values());
                    } else {
                        this.selectedFinishingIds = [];
                    }
                },

                finishingMatches(name, code, slug) {
                    const q = (this.finishingSearch || '').toLowerCase().trim();
                    if (!q) return true;
                    return (name + ' ' + code + ' ' + slug).toLowerCase().includes(q);
                },

                finishingSortIndex(id) {
                    const i = this.selectedFinishingIds.indexOf(id);
                    return i === -1 ? 9999 : i;
                },

                uid(prefix = 'id') {
                    return `${prefix}_${Math.random().toString(16).slice(2)}_${Date.now()}`;
                },

                slugify(str) {
                    return (str || '')
                        .toLowerCase()
                        .trim()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-');
                },

                addOptionGroup() {
                    this.options.push({
                        id: this.uid('grp'),
                        name: '',
                        slug: '',
                        values: [],
                    });
                },

                removeOptionGroup(index) {
                    if (!confirm('Remove this option group?')) return;
                    this.options.splice(index, 1);
                    this.rebuildCombinations();
                },

                syncGroupSlug(g) {
                    if (this.isDbId(g?.id)) return;
                    g.slug = this.slugify(g.name);
                },

                addOptionValue(group) {
                    group.values.push({
                        id: this.uid('val'),
                        label: '',
                        slug: '',
                    });
                },

                removeOptionValue(group, index) {
                    group.values.splice(index, 1);
                    this.rebuildCombinations();
                },

                syncValueSlug(group, v) {
                    if (this.isDbId(v?.id)) return;
                    v.slug = this.slugify(v.label);
                },

                rebuildCombinations() {
                    // Build only from groups that have a slug + at least one valid value.
                    const groups = this.options
                        .map(g => ({
                            ...g,
                            slug: this.slugify(g.slug || g.name),
                            values: (g.values || []).map(v => ({
                                ...v,
                                slug: this.slugify(v.slug || v.label),
                            })).filter(v => v.slug.length > 0),
                        }))
                        .filter(g => g.slug.length > 0 && g.values.length > 0);

                    if (groups.length === 0) {
                        this.variants = [];
                        return;
                    }

                    // Cartesian product:
                    const cartesian = (arrs) =>
                        arrs.reduce((acc, cur) => acc.flatMap(a => cur.map(b => a.concat([b]))), [[]]);

                    const combos = cartesian(groups.map(g => g.values.map(v => ({ group: g, value: v }))));

                    // Preserve enabled state and price if key already exists
                    const existing = new Map((this.variants || []).map(v => [v.key, v]));

                    this.variants = combos.map(items => {
                        const key = items.map(x => `${x.group.slug}:${x.value.slug}`).join('|');
                        const tags = items.map(x => `${x.group.slug}:${x.value.slug}`);
                        const label = items.map(x => x.value.label || x.value.slug).join(' · ');

                        const prev = existing.get(key) || {};

                        return {
                            key,
                            label,
                            tags,
                            enabled: Object.prototype.hasOwnProperty.call(prev, 'enabled') ? !!prev.enabled : true,
                            price: prev.price ?? '',
                            selections: items.map(x => ({
                                group_name: x.group.name,
                                group_slug: x.group.slug,
                                value_label: x.value.label,
                                value_slug: x.value.slug,
                            })),
                        };
                    });
                },

                isDbId(v) {
                    if (v === null || v === undefined) return false;
                    return /^\d+$/.test(String(v));
                },

                enableAllVariants() {
                    this.variants = (this.variants || []).map(v => ({ ...v, enabled: true }));
                },

                disableAllVariants() {
                    this.variants = (this.variants || []).map(v => ({ ...v, enabled: false }));
                },

                variantsPayload() {
                    // This is what backend receives.
                    return {
                        options: (this.options || []).map((g, gi) => ({
                            id: this.isDbId(g?.id) ? Number(g.id) : null,
                            name: g.name,
                            slug: this.slugify(g.slug || g.name),
                            sort_order: g?.sort_order ?? (gi + 1),
                            is_active: true,
                            values: (g.values || []).map((v, vi) => ({
                                id: this.isDbId(v?.id) ? Number(v.id) : null,
                                label: v.label,
                                slug: this.slugify(v.slug || v.label),
                                sort_order: v?.sort_order ?? (vi + 1),
                                is_active: (typeof v?.is_active === 'boolean') ? v.is_active : true,
                            })).filter(v => v.slug.length > 0),
                        })).filter(g => g.slug && g.values.length > 0),

                        variants: (this.variants || [])
                            .filter(v => v.enabled)
                            .map(v => ({
                                id: this.isDbId(v?.id) ? Number(v.id) : null,
                                key: v.key,
                                label: v.label,
                                price: v.price ?? null,
                                selections: v.selections,
                            })),
                    };
                },

                seedSeoDefaults() {
                    if (!this.seo.seo_title && this.form.name) {
                        this.seo.seo_title = this.form.name;
                    }
                    if (!this.seo.og_title && this.seo.seo_title) {
                        this.seo.og_title = this.seo.seo_title;
                    }
                    if (!this.seo.og_description && this.seo.seo_description) {
                        this.seo.og_description = this.seo.seo_description;
                    }
                    if (!this.seo.canonical_url && this.form.slug) {
                        try {
                            this.seo.canonical_url = `${window.location.origin}/products/${this.form.slug}`;
                        } catch (e) {
                            // no-op in non-browser environments
                        }
                    }
                },

                handleOgImagePreview(e) {
                    const file = (e.target.files || [])[0];
                    if (!file) {
                        this.seoOgPreviewUrl = null;
                        return;
                    }
                    this.seoOgPreviewUrl = URL.createObjectURL(file);
                },

                persistState() {
                    try {
                        const payload = {
                            step: this.step,
                            form: this.form,
                            pricing: this.pricing,
                            seo: this.seo,
                        };
                        localStorage.setItem('productWizard.create.state', JSON.stringify(payload));
                    } catch (e) {
                        // ignore storage issues
                    }
                },

                hasPublicPricingWizard() {
                    if (this.form.product_type === 'dimension_based') {
                        return !!(this.pricing.rate_per_sqft || this.pricing.offcut_per_sqft);
                    }

                    return !!this.pricing.base_price;
                },

                hasImagesWizard() {
                    return !!window.__wizardHasImages;
                },

                hasRollBindingsWizard() {
                    if (this.form.product_type !== 'dimension_based') return true;
                    return Array.isArray(this.selectedRollIds) && this.selectedRollIds.length > 0;
                },

                hasSeoOkWizard() {
                    return !!(this.seo.seo_title && this.seo.seo_description);
                },

                hasVariantsWizard() {
                    return Array.isArray(this.options) && this.options.length > 0;
                },

                hasFinishingsWizard() {
                    return Array.isArray(this.selectedFinishingIds) && this.selectedFinishingIds.length > 0;
                },

                issuesCountWizard() {
                    let n = 0;
                    if (!this.hasPublicPricingWizard()) n++;
                    if (!this.hasImagesWizard()) n++;
                    if (!this.hasRollBindingsWizard()) n++;
                    if (!this.hasSeoOkWizard()) n++;
                    return n;
                },

                pricingSummary() {
                    if (this.form.product_type === 'dimension_based') {
                        const rate = this.pricing.rate_per_sqft || 0;
                        const offcut = this.pricing.offcut_per_sqft || 0;
                        if (!rate && !offcut) {
                            return 'Dimension-based pricing not configured yet.';
                        }
                        return `Rate ${rate || 0} /sqft · Offcut ${offcut || 0} /sqft`;
                    }

                    const base = this.pricing.base_price || 0;
                    if (!base) {
                        return 'Base price not set.';
                    }
                    return `Base price Rs. ${base}`;
                },

                variantSummary() {
                    const groups = (this.options || []).length;
                    const enabled = (this.variants || []).filter(v => v.enabled).length;
                    if (!groups) {
                        return 'No option groups defined.';
                    }
                    if (!enabled) {
                        return `${groups} groups · 0 enabled variants.`;
                    }
                    return `${groups} groups · ${enabled} enabled variants.`;
                },

                rollSummary() {
                    if (this.form.product_type !== 'dimension_based') {
                        return 'Not required for this product type.';
                    }
                    const count = (this.selectedRollIds || []).length;
                    if (!count) {
                        return 'No rolls selected yet.';
                    }
                    return `${count} roll(s) selected.`;
                },

                finishingSummary() {
                    const count = (this.selectedFinishingIds || []).length;
                    if (!count) {
                        return 'No finishings attached.';
                    }
                    return `${count} finishing product(s) attached.`;
                },
            }
        }
    </script>

    <script>
        function productMediaImages() {
            return {
                previews: [],
                primaryIndex: 0,
                init() {},
                handleFiles(e) {
                    const files = Array.from(e.target.files || []);
                    files.forEach((file) => {
                        const key = `${file.name}-${file.size}-${Date.now()}-${Math.random()}`;
                        const url = URL.createObjectURL(file);
                        this.previews.push({ key, url, name: file.name });
                    });
                    if (this.previews.length && (this.primaryIndex === null || this.primaryIndex === undefined)) {
                        this.primaryIndex = 0;
                    }
                    window.__wizardHasImages = this.previews.length > 0;
                },
                remove(idx) {
                    if (idx === this.primaryIndex) this.primaryIndex = 0;
                    this.previews.splice(idx, 1);
                    if (this.primaryIndex >= this.previews.length) this.primaryIndex = 0;
                    window.__wizardHasImages = this.previews.length > 0;
                },
                move(idx, dir) {
                    const newIndex = idx + dir;
                    if (newIndex < 0 || newIndex >= this.previews.length) return;

                    const tmp = this.previews[idx];
                    this.previews[idx] = this.previews[newIndex];
                    this.previews[newIndex] = tmp;

                    if (this.primaryIndex === idx) this.primaryIndex = newIndex;
                    else if (this.primaryIndex === newIndex) this.primaryIndex = idx;
                },
            }
        }

        function productMediaAttachments() {
            return {
                files: [],
                init() {},
                handleFiles(e) {
                    const list = Array.from(e.target.files || []);
                    list.forEach((file) => {
                        const key = `${file.name}-${file.size}-${Date.now()}-${Math.random()}`;
                        this.files.push({ key, name: file.name, type: file.type || 'file' });
                    });
                },
                remove(idx) {
                    this.files.splice(idx, 1);
                },
                move(idx, dir) {
                    const newIndex = idx + dir;
                    if (newIndex < 0 || newIndex >= this.files.length) return;
                    const tmp = this.files[idx];
                    this.files[idx] = this.files[newIndex];
                    this.files[newIndex] = tmp;
                },
            }
        }
    </script>
</x-app-layout>
