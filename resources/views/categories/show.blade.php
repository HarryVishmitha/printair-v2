<x-home-layout :seo="$seo">
    @php
        $desc = trim((string) ($category->seo_description ?: $category->short_description ?: $category->description ?: ''));
        $desc = $desc !== '' ? $desc : null;

        $urlFor = function (?string $path, string $fallback) {
            $path = is_string($path) ? trim($path) : '';
            if ($path === '') {
                return $fallback;
            }
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            if (str_starts_with($path, '/')) {
                return url($path);
            }
            if (str_starts_with($path, 'storage/')) {
                return asset($path);
            }

            return Storage::disk('public')->url($path);
        };
    @endphp

    <section class="bg-white">
        {{-- Hero --}}
        <div class="relative overflow-hidden border-b border-slate-100">
            <div class="absolute inset-0">
                <img src="{{ $coverUrl }}" alt="{{ $category->name }}"
                    class="h-full w-full object-cover" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-b from-slate-950/70 via-slate-950/45 to-white"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-10 pb-10">
                {{-- Breadcrumbs --}}
                <nav aria-label="Breadcrumb" class="text-sm">
                    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-white/90">
                        @foreach ($breadcrumbs as $i => $bc)
                            <li class="inline-flex items-center gap-2">
                                @if (!empty($bc['href']))
                                    <a href="{{ $bc['href'] }}" class="hover:text-white font-semibold">
                                        {{ $bc['label'] }}
                                    </a>
                                @else
                                    <span class="font-semibold text-white">{{ $bc['label'] }}</span>
                                @endif
                                @if ($i < count($breadcrumbs) - 1)
                                    <span class="text-white/50">/</span>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>

                <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        @if ($parent)
                            <a href="{{ route('categories.show', ['category' => $parent->slug]) }}"
                                class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-black tracking-wide text-white ring-1 ring-white/20 hover:bg-white/15">
                                <span class="text-white/70">Parent:</span>
                                <span>{{ $parent->name }}</span>
                            </a>
                        @endif

                        <h1 class="mt-3 text-3xl sm:text-4xl font-black tracking-tight text-white">
                            {{ $category->name }}
                        </h1>

                        @if ($desc)
                            <p class="mt-3 text-base sm:text-lg text-white/85 leading-relaxed">
                                {{ $desc }}
                            </p>
                        @endif

                        @if (!empty($keywords))
                            <div class="mt-5 flex flex-wrap gap-2">
                                @foreach ($keywords as $kw)
                                    <a href="{{ route('products.index', ['q' => $kw, 'category' => $category->slug]) }}"
                                        class="inline-flex items-center rounded-full bg-white/95 px-3 py-1 text-xs font-extrabold text-slate-900 ring-1 ring-slate-200 hover:bg-white">
                                        #{{ $kw }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#ef233c] transition">
                            Browse Products
                        </a>
                        <a href="{{ url('/contact') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50">
                            Get a Quote
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
            {{-- Subcategories --}}
            @if ($children->count() > 0)
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black tracking-tight text-slate-900">Subcategories</h2>
                        <p class="mt-1 text-sm text-slate-600">Explore more specific options under {{ $category->name }}.</p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($children as $child)
                        @php
                            $childCover = $urlFor($child->cover_image_path, asset('assets/placeholders/product-placeholder.svg'));
                            $childDesc = trim((string) ($child->seo_description ?: $child->short_description ?: ''));
                            $childDesc = $childDesc !== '' ? $childDesc : null;
                        @endphp

                        <a href="{{ route('categories.show', ['category' => $child->slug]) }}"
                            class="group rounded-3xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-xl hover:shadow-slate-900/10 transition">
                            <div class="relative overflow-hidden rounded-2xl bg-slate-100 aspect-[16/10]">
                                <img src="{{ $childCover }}" alt="{{ $child->name }}"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]" loading="lazy">
                            </div>
                            <div class="mt-4">
                                <div class="font-black text-slate-900">{{ $child->name }}</div>
                                @if ($childDesc)
                                    <div class="mt-1 text-sm text-slate-600 line-clamp-2">{{ $childDesc }}</div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="my-10 border-t border-slate-100"></div>
            @endif

            {{-- Products --}}
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-xl font-black tracking-tight text-slate-900">Products</h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} items.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50">
                        View All in Products Page
                    </a>
                </div>
            </div>

            @if ($products->count() === 0)
                <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-8 text-center">
                    <div class="text-lg font-black text-slate-900">No products found yet</div>
                    <div class="mt-1 text-sm text-slate-600">We’ll add products under {{ $category->name }} soon.</div>
                    <div class="mt-4 flex justify-center gap-2">
                        <a href="{{ route('products.index') }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-[#ef233c] transition">
                            Browse All Products
                        </a>
                        <a href="{{ url('/contact') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                            Contact Us
                        </a>
                    </div>
                </div>
            @else
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @foreach ($products as $p)
                        @php
                            $primary = $p->images?->firstWhere('is_featured', true) ?? $p->images?->first();
                            $imgUrl = $urlFor($primary?->path, asset('assets/placeholders/product.png'));
                            $pDesc = trim((string) ($p->seo?->seo_description ?: $p->short_description ?: ''));
                            $pDesc = $pDesc !== '' ? $pDesc : null;
                        @endphp

                        <a href="{{ route('products.show', ['product' => $p->slug]) }}"
                            class="group rounded-3xl border border-slate-200 bg-white p-3 sm:p-4 shadow-sm hover:shadow-xl hover:shadow-slate-900/10 transition">
                            <div class="relative overflow-hidden rounded-2xl bg-slate-100 aspect-[4/3]">
                                <img src="{{ $imgUrl }}" alt="{{ $p->name }}"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.04]" loading="lazy" />
                            </div>

                            <div class="mt-4 flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-black text-slate-900 truncate">{{ $p->name }}</div>
                                    <div class="mt-1 text-xs text-slate-500 truncate">{{ $category->name }}</div>
                                </div>
                                @if (!empty($p->min_qty))
                                    <div class="shrink-0 text-right">
                                        <div class="text-[11px] text-slate-500">Min qty</div>
                                        <div class="font-black text-slate-900">{{ (int) $p->min_qty }}</div>
                                    </div>
                                @endif
                            </div>

                            @if ($pDesc)
                                <div class="mt-3 text-sm text-slate-600 line-clamp-2">{{ $pDesc }}</div>
                            @endif

                            <div class="mt-4 flex flex-wrap gap-2 text-[11px] text-slate-600">
                                @if ($p->allow_custom_size)
                                    <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-1 ring-1 ring-slate-200">
                                        Custom size
                                    </span>
                                @endif
                                @if ($p->allow_predefined_sizes)
                                    <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-1 ring-1 ring-slate-200">
                                        Predefined sizes
                                    </span>
                                @endif
                                @if ($p->requires_dimensions)
                                    <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-1 ring-1 ring-slate-200">
                                        Needs dimensions
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </section>
</x-home-layout>
