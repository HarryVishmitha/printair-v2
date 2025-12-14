{{-- resources/views/admin/categories/_form.blade.php --}}

@php
    $isEdit = isset($category) && $category;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
    class="space-y-6">
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
