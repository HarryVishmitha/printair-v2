@php
    /** @var \App\Models\Roll|null $roll */
    $isEdit = isset($roll) && $roll?->exists;
@endphp

<div class="space-y-6">

    {{-- Basic Info --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Roll Details</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Define a physical roll (fixed width). Pricing comes later through ProductPricing → Roll Overrides.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-700">Status</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               class="sr-only peer"
                               {{ old('is_active', $isEdit ? (int) $roll->is_active : 1) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="mx-6 mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="px-6 py-5">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                {{-- Name --}}
                <div>
                    <label for="roll_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Roll Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="roll_name"
                           name="name"
                           value="{{ old('name', $isEdit ? $roll->name : '') }}"
                           placeholder="e.g. Flex 4ft"
                           class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                {{-- Slug --}}
                <div>
                    <label for="roll_slug" class="block text-sm font-medium text-gray-700 mb-2">
                        Slug
                    </label>
                    <input type="text"
                           id="roll_slug"
                           name="slug"
                           value="{{ old('slug', $isEdit ? $roll->slug : '') }}"
                           placeholder="auto-generated if empty"
                           class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
                    <p class="mt-1 text-xs text-gray-500">Used for stable references. Leave empty to auto-generate.</p>
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Material Type --}}
                <div>
                    <label for="material_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Material Type <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="material_type"
                           name="material_type"
                           value="{{ old('material_type', $isEdit ? $roll->material_type : '') }}"
                           placeholder="flex | sticker | vinyl | banner"
                           class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
                    <p class="mt-1 text-xs text-gray-500">
                        Keep consistent naming (e.g. flex, sticker). This helps filtering + analytics later.
                    </p>
                    @error('material_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Width inches --}}
                <div>
                    <label for="roll_width_in" class="block text-sm font-medium text-gray-700 mb-2">
                        Fixed Width (inches) <span class="text-red-500">*</span>
                    </label>
                    <input type="number"
                           step="0.001"
                           min="0.001"
                           id="roll_width_in"
                           name="width_in"
                           value="{{ old('width_in', $isEdit ? (string) $roll->width_in : '') }}"
                           placeholder="e.g. 48"
                           class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20">
                    <div class="mt-2 flex items-center justify-between">
                        <p class="text-xs text-gray-500">Tip: 4ft = 48in, 5ft = 60in, 10ft = 120in</p>
                        <span class="text-xs font-medium text-gray-600 font-mono" id="roll_width_ft_hint">—</span>
                    </div>
                    @error('width_in')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Advanced Meta --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Advanced Settings</h3>
                    <p class="text-sm text-gray-500 mt-1">Optional metadata for future extensions (supplier, gsm, cost, notes).</p>
                </div>

                <button type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                        onclick="document.getElementById('roll_meta_wrap').classList.toggle('hidden')">
                    <svg class="h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    Toggle Meta
                </button>
            </div>
        </div>

        <div id="roll_meta_wrap" class="hidden px-6 py-5">
            <label for="roll_meta" class="block text-sm font-medium text-gray-700 mb-2">
                Meta (JSON)
            </label>
            <textarea id="roll_meta"
                      name="meta"
                      rows="6"
                      placeholder='{"supplier":"ABC","gsm":440,"note":"best for outdoor"}'
                      class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20 font-mono">{{ old('meta', $isEdit ? (is_array($roll->meta) ? json_encode($roll->meta, JSON_PRETTY_PRINT) : '') : '') }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Valid JSON only. Leave empty if not needed.</p>
            @error('meta')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

</div>

{{-- Helper JS (slug + ft hint) --}}
<script>
    (function () {
        const nameEl = document.getElementById('roll_name');
        const slugEl = document.getElementById('roll_slug');
        const widthEl = document.getElementById('roll_width_in');
        const hintEl = document.getElementById('roll_width_ft_hint');

        const slugify = (str) => (str || '')
            .toString()
            .toLowerCase()
            .trim()
            .replace(/['"]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');

        const updateFtHint = () => {
            const v = parseFloat(widthEl?.value || '0');
            if (!v || v <= 0) { hintEl.textContent = '—'; return; }
            hintEl.textContent = (v / 12).toFixed(2) + ' ft';
        };

        let slugTouched = false;

        if (slugEl) {
            slugEl.addEventListener('input', () => { slugTouched = true; });
        }

        if (nameEl && slugEl) {
            nameEl.addEventListener('input', () => {
                if (slugTouched && slugEl.value.trim() !== '') return;
                slugEl.value = slugify(nameEl.value);
            });
        }

        if (widthEl) {
            widthEl.addEventListener('input', updateFtHint);
            updateFtHint();
        }
    })();
</script>