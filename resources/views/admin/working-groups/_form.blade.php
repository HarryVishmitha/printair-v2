@php
    /** @var \App\Models\WorkingGroup|null $workingGroup */
    $isPublic = isset($workingGroup) && $workingGroup->slug === \App\Models\WorkingGroup::PUBLIC_SLUG;
@endphp

<div class="space-y-8">

    {{-- SECTION: Basic Information --}}
    <div class="rounded-2xl border border-slate-200/80 bg-slate-50/40 p-6 shadow-inner shadow-slate-200/40">
        <div class="flex items-center gap-3 mb-5">
            <div
                class="h-10 w-10 flex items-center justify-center rounded-xl bg-gradient-to-br from-[#ff4b5c] to-[#ff7a45] text-white shadow-md shadow-[#ff4b5c]/30">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900 tracking-wide uppercase">Basic Details</h3>
                <p class="text-xs text-slate-500">Define how this working group appears across Printair.</p>
            </div>
        </div>

        {{-- Name + Slug --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1.5">Name <span
                        class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $workingGroup->name ?? '') }}"
                    required
                    class="block w-full rounded-xl border-slate-200 bg-white/80 px-4 py-3 text-sm shadow-sm ring-1 ring-slate-200/60 placeholder:text-slate-400 focus:border-[#ff4b5c] focus:ring-2 focus:ring-[#ff4b5c]/30 focus:bg-white transition-all duration-200" />
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1.5">
                    Slug
                    <span class="text-slate-400 font-normal normal-case">(optional)</span>
                </label>

                <input type="text" id="slug" name="slug" value="{{ old('slug', $workingGroup->slug ?? '') }}"
                    @if ($isPublic) disabled @endif
                    class="block w-full rounded-xl border-slate-200 bg-white/80 px-4 py-3 text-sm shadow-sm ring-1 ring-slate-200/60 placeholder:text-slate-400 focus:border-[#ff4b5c] focus:ring-2 focus:ring-[#ff4b5c]/30 focus:bg-white transition-all duration-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:ring-0" />

                @if ($isPublic)
                    <p class="mt-1 text-xs text-slate-500">Slug for the default public group cannot be changed.</p>
                @else
                    <p class="mt-1 text-xs text-slate-500">Leave empty to auto-generate from the name.</p>
                @endif

                @error('slug')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Description --}}
        <div class="mt-6">
            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1.5">Description</label>
            <textarea id="description" name="description" rows="3"
                class="block w-full rounded-xl border-slate-200 bg-white/80 px-4 py-3 text-sm shadow-sm ring-1 ring-slate-200/60 placeholder:text-slate-400 focus:border-[#ff4b5c] focus:ring-2 focus:ring-[#ff4b5c]/30 focus:bg-white transition-all duration-200 resize-none">{{ old('description', $workingGroup->description ?? '') }}</textarea>
            @error('description')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- SECTION: Flags --}}
    <div class="rounded-2xl border border-slate-200/80 bg-slate-50/40 p-6 shadow-inner shadow-slate-200/40">
        <div class="flex items-center gap-3 mb-5">
            <div
                class="h-10 w-10 flex items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white shadow-md shadow-indigo-500/30">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M12 3v18" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900 tracking-wide uppercase">Group Properties</h3>
                <p class="text-xs text-slate-500">Control how this group behaves in sharing and staff workflows.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Shareable --}}
            <label for="is_shareable"
                class="cursor-pointer flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm hover:border-[#ff7a45]/50 transition">
                <input type="checkbox" id="is_shareable" name="is_shareable" value="1" @checked(old('is_shareable', $workingGroup->is_shareable ?? false))
                    class="mt-1 h-5 w-5 rounded border-slate-300 text-[#ff4b5c] focus:ring-[#ff4b5c]" />
                <div>
                    <span class="block text-sm font-semibold text-slate-800">Shareable</span>
                    <span class="block text-xs text-slate-500">Used for public or semi-public sharing.</span>
                </div>
            </label>

            {{-- Restricted --}}
            <label for="is_restricted"
                class="cursor-pointer flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm hover:border-rose-400/50 transition">
                <input type="checkbox" id="is_restricted" name="is_restricted" value="1"
                    @checked(old('is_restricted', $workingGroup->is_restricted ?? false))
                    class="mt-1 h-5 w-5 rounded border-slate-300 text-rose-600 focus:ring-rose-500" />
                <div>
                    <span class="block text-sm font-semibold text-slate-800">Restricted</span>
                    <span class="block text-xs text-slate-500">Sensitive designs. Sharing blocked/reduced.</span>
                </div>
            </label>

            {{-- Staff group --}}
            <label for="is_staff_group"
                class="cursor-pointer flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm hover:border-amber-400/50 transition">
                <input type="checkbox" id="is_staff_group" name="is_staff_group" value="1"
                    @checked(old('is_staff_group', $workingGroup->is_staff_group ?? false))
                    class="mt-1 h-5 w-5 rounded border-slate-300 text-amber-600 focus:ring-amber-500" />
                <div>
                    <span class="block text-sm font-semibold text-slate-800">Staff Group</span>
                    <span class="block text-xs text-slate-500">Internal workflows and management only.</span>
                </div>
            </label>

        </div>
    </div>

</div>
