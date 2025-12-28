{{-- resources/views/admin/categories/_form.blade.php --}}

@php
    $isEdit = isset($category) && $category;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
    enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    {{-- BASIC INFO --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div>
            <label for="name" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                Category name <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="name" name="name"
                value="{{ old('name', $category->name ?? '') }}"
                placeholder="e.g. Roll-up Banners"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20" />
            @error('name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="slug" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                Slug <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="slug" name="slug"
                value="{{ old('slug', $category->slug ?? '') }}"
                placeholder="e.g. roll-up-banners"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20" />
            <p class="mt-1 text-[11px] text-slate-400">
                Tip: Keep it stable. Slug is used in URLs and internal linking.
            </p>
            @error('slug')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="parent_id" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                Parent category
            </label>
            <select id="parent_id" name="parent_id"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
                <option value="">Top-level (no parent)</option>
                @foreach ($parents as $p)
                    <option value="{{ $p->id }}"
                        {{ (string) old('parent_id', $category->parent_id ?? '') === (string) $p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
            @error('parent_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="code" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                Internal code
            </label>
            <input type="text" id="code" name="code"
                value="{{ old('code', $category->code ?? '') }}"
                placeholder="e.g. CAT-BNR"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20" />
            @error('code')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- DESCRIPTIONS --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div>
            <label for="short_description" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                Short description
            </label>
            <input type="text" id="short_description" name="short_description"
                value="{{ old('short_description', $category->short_description ?? '') }}"
                placeholder="One-liner used in cards / menus"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20" />
            @error('short_description')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sort_order" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                Sort order
            </label>
            <input type="number" id="sort_order" name="sort_order" min="0"
                value="{{ old('sort_order', $category->sort_order ?? 0) }}"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm transition-all focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20" />
            <p class="mt-1 text-[11px] text-slate-400">Lower numbers appear earlier in menus.</p>
            @error('sort_order')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lg:col-span-2">
            <label for="description" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                Description
            </label>
            <textarea id="description" name="description" rows="4"
                placeholder="Optional longer description for category landing page…"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">{{ old('description', $category->description ?? '') }}</textarea>
            @error('description')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- MEDIA --}}
    <div class="rounded-3xl border border-slate-200/80 bg-white p-5">
        @php
            $coverPath = old('cover_image_path', $category->cover_image_path ?? null);
            $coverPath = is_string($coverPath) ? trim($coverPath) : '';
            $coverUrl = null;
            if ($coverPath !== '') {
                if (str_starts_with($coverPath, 'http://') || str_starts_with($coverPath, 'https://')) {
                    $coverUrl = $coverPath;
                } elseif (str_starts_with($coverPath, '/')) {
                    $coverUrl = url($coverPath);
                } elseif (str_starts_with($coverPath, 'storage/')) {
                    $coverUrl = asset($coverPath);
                } else {
                    $coverUrl = Storage::disk('public')->url($coverPath);
                }
            }
        @endphp

        <div class="flex items-center gap-2 mb-4">
            <div
                class="h-9 w-9 rounded-2xl bg-slate-50 flex items-center justify-center ring-1 ring-slate-200 text-slate-500">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 7.5h10.5M6.75 12h10.5m-10.5 4.5h10.5" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Cover image</h3>
                <p class="text-xs text-slate-500">Used on the public category page header.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div>
                <label for="cover_image" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                    Upload cover image
                </label>
                <input type="file" id="cover_image" name="cover_image" accept="image/*"
                    class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2 px-3 text-sm text-slate-900 file:mr-3 file:rounded-xl file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-[#ff4b5c] focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20" />
                <p class="mt-1 text-[11px] text-slate-400">
                    Recommended: 1600×900 (JPG/PNG/WebP). Max ~4MB.
                </p>
                @error('cover_image')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                    Current image
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-3">
                    @if ($coverUrl)
                        <div class="relative overflow-hidden rounded-xl bg-slate-100 aspect-[16/10]">
                            <img src="{{ $coverUrl }}" alt="Category cover image"
                                class="h-full w-full object-cover" loading="lazy">
                        </div>

                        <label class="mt-3 flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                            <span class="text-sm font-medium text-slate-700">Remove current cover</span>
                            <input type="hidden" name="remove_cover_image" value="0">
                            <input type="checkbox" name="remove_cover_image" value="1" {{ old('remove_cover_image') ? 'checked' : '' }}
                                class="h-5 w-5 rounded border-slate-300 text-[#ff4b5c] focus:ring-[#ff4b5c]/30" />
                        </label>
                    @else
                        <div class="text-sm text-slate-600">No cover image set.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- VISIBILITY / FLAGS --}}
    <div class="rounded-3xl border border-slate-200/80 bg-slate-50/50 p-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="h-9 w-9 rounded-2xl bg-white flex items-center justify-center ring-1 ring-slate-200 text-slate-500">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6v6l4 2" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Visibility & behavior</h3>
                <p class="text-xs text-slate-500">Control navbar display, active state, featured badge, and SEO indexing.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $isActive     = old('is_active', $category->is_active ?? true);
                $isFeatured   = old('is_featured', $category->is_featured ?? false);
                $showInNavbar = old('show_in_navbar', $category->show_in_navbar ?? true);
                $isIndexable  = old('is_indexable', $category->is_indexable ?? true);
            @endphp

            <label class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                <span class="text-sm font-medium text-slate-700">Active</span>
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ (string)$isActive === '1' || $isActive === true ? 'checked' : '' }}
                    class="h-5 w-5 rounded border-slate-300 text-[#ff4b5c] focus:ring-[#ff4b5c]/30" />
            </label>

            <label class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                <span class="text-sm font-medium text-slate-700">Featured</span>
                <input type="hidden" name="is_featured" value="0">
                <input type="checkbox" name="is_featured" value="1" {{ (string)$isFeatured === '1' || $isFeatured === true ? 'checked' : '' }}
                    class="h-5 w-5 rounded border-slate-300 text-[#ff4b5c] focus:ring-[#ff4b5c]/30" />
            </label>

            <label class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                <span class="text-sm font-medium text-slate-700">Show in Navbar</span>
                <input type="hidden" name="show_in_navbar" value="0">
                <input type="checkbox" name="show_in_navbar" value="1" {{ (string)$showInNavbar === '1' || $showInNavbar === true ? 'checked' : '' }}
                    class="h-5 w-5 rounded border-slate-300 text-[#ff4b5c] focus:ring-[#ff4b5c]/30" />
            </label>

            <label class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                <span class="text-sm font-medium text-slate-700">Indexable</span>
                <input type="hidden" name="is_indexable" value="0">
                <input type="checkbox" name="is_indexable" value="1" {{ (string)$isIndexable === '1' || $isIndexable === true ? 'checked' : '' }}
                    class="h-5 w-5 rounded border-slate-300 text-[#ff4b5c] focus:ring-[#ff4b5c]/30" />
            </label>
        </div>
    </div>

    {{-- SEO --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div>
            <label for="seo_title" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                SEO title
            </label>
            <input type="text" id="seo_title" name="seo_title"
                value="{{ old('seo_title', $category->seo_title ?? '') }}"
                placeholder="Optional — shown in Google results"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm transition-all focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20" />
            @error('seo_title')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="seo_keywords" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                SEO keywords
            </label>
            <input type="text" id="seo_keywords" name="seo_keywords"
                value="{{ old('seo_keywords', $category->seo_keywords ?? '') }}"
                placeholder="Comma separated keywords"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm transition-all focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20" />
            @error('seo_keywords')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lg:col-span-2">
            <label for="seo_description" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                SEO description
            </label>
            <textarea id="seo_description" name="seo_description" rows="3"
                placeholder="Optional — short description for search engines"
                class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-sm text-slate-900 shadow-sm transition-all focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">{{ old('seo_description', $category->seo_description ?? '') }}</textarea>
            @error('seo_description')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- ACTIONS --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
        <a href="{{ route('admin.categories.index') }}"
            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
            Cancel
        </a>

        <button type="submit"
            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#ff4b5c] to-[#ff7a45] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#ff4b5c]/30 hover:shadow-lg hover:-translate-y-0.5 transition-all">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            {{ $isEdit ? 'Update Category' : 'Create Category' }}
        </button>
    </div>
</form>
