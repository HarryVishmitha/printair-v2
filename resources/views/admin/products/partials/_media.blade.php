@php
    /** @var \App\Models\Product $product */
    $images = $product->images ?? collect();
    $files = $product->files ?? collect();
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Step 6 — Media</h3>
        <p class="text-sm text-gray-500 mt-1">Upload product images and attachments (spec sheets, templates, etc.).</p>
    </div>

    <div class="p-6 space-y-10">

        {{-- Images --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-semibold text-gray-900">Images</h4>
                <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                    <input id="pt_img_picker" type="file" accept="image/*" class="hidden" multiple>
                    <span class="px-3 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 cursor-pointer">
                        Upload Images
                    </span>
                </label>
            </div>

            <div id="pt_img_drop"
                 class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/40 p-5 text-sm text-slate-600">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="font-medium text-slate-800">Drag & drop images here</div>
                        <div class="text-xs text-slate-500 mt-1">JPG/PNG/WEBP, max 15MB each. First image becomes featured.</div>
                    </div>
                    <div id="pt_img_upload_state" class="text-xs text-slate-500">—</div>
                </div>
            </div>

            <div class="mt-5">
                <div id="pt_img_grid" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($images as $img)
                        @php
                            $url = \Illuminate\Support\Facades\Storage::disk($img->disk ?? 'public')->url($img->path);
                        @endphp
                        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm"
                             data-id="{{ $img->id }}" draggable="true">
                            <div class="relative">
                                <img src="{{ $url }}" alt="{{ $img->alt_text ?? '' }}" class="h-36 w-full object-cover">
                                @if($img->is_featured)
                                    <span class="absolute top-2 left-2 text-xs font-semibold px-2 py-1 rounded-lg bg-emerald-600 text-white">
                                        Featured
                                    </span>
                                @endif
                            </div>

                            <div class="p-3 space-y-2">
                                <input type="text"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-xs focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20"
                                       placeholder="Alt text (optional)"
                                       value="{{ $img->alt_text ?? '' }}"
                                       data-alt>

                                <div class="flex items-center justify-between gap-2">
                                    <button type="button"
                                            class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50"
                                            data-featured>
                                        Set featured
                                    </button>

                                    <button type="button"
                                            class="text-xs px-2.5 py-1.5 rounded-lg border border-red-200 text-red-700 hover:bg-red-50"
                                            data-delete>
                                        Delete
                                    </button>
                                </div>

                                <div class="text-[11px] text-slate-500">
                                    Drag to reorder
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Attachments --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-semibold text-gray-900">Attachments</h4>
                <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                    <input id="pt_file_picker" type="file" class="hidden" multiple>
                    <span class="px-3 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 cursor-pointer">
                        Upload Files
                    </span>
                </label>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50/30 p-4">
                <div class="text-xs text-slate-500">
                    Upload spec sheets, templates, mockups, etc. Visibility can be <b>Admin</b> or <b>Public</b>.
                </div>

                <div class="mt-4 space-y-3" id="pt_file_list">
                    @foreach($files as $f)
                        @php
                            $url = \Illuminate\Support\Facades\Storage::disk($f->disk ?? 'public')->url($f->path);
                        @endphp
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-3"
                             data-id="{{ $f->id }}" draggable="true">
                            <div class="min-w-0">
                                <a href="{{ $url }}" target="_blank" class="text-sm font-medium text-slate-900 hover:underline truncate block">
                                    {{ $f->label ?: $f->original_name }}
                                </a>
                                <div class="text-xs text-slate-500 truncate">{{ $f->original_name }}</div>
                            </div>

                            <div class="flex items-center gap-2">
                                <select class="rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-xs"
                                        data-visibility>
                                    <option value="admin" {{ ($f->visibility ?? 'admin') === 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="public" {{ ($f->visibility ?? 'admin') === 'public' ? 'selected' : '' }}>Public</option>
                                </select>

                                <button type="button"
                                        class="text-xs px-2.5 py-1.5 rounded-lg border border-red-200 text-red-700 hover:bg-red-50"
                                        data-delete-file>
                                    Delete
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-2 text-[11px] text-slate-500">Drag to reorder files</div>
            </div>
        </div>

    </div>
</div>

<script>
(function () {
    const productId = @json($product->id);
    const csrf = @json(csrf_token());

    const routes = {
        imgUpload: @json(route('admin.products.media.images.upload', $product)),
        imgReorder: @json(route('admin.products.media.images.reorder', $product)),
        imgFeatured: (id) => @json(url('/admin/products')) + `/${productId}/media/images/${id}/featured`,
        imgUpdate: (id) => @json(url('/admin/products')) + `/${productId}/media/images/${id}`,
        imgDelete: (id) => @json(url('/admin/products')) + `/${productId}/media/images/${id}`,

        fileUpload: @json(route('admin.products.media.files.upload', $product)),
        fileReorder: @json(route('admin.products.media.files.reorder', $product)),
        fileUpdate: (id) => @json(url('/admin/products')) + `/${productId}/media/files/${id}`,
        fileDelete: (id) => @json(url('/admin/products')) + `/${productId}/media/files/${id}`,
    };

    const imgPicker = document.getElementById('pt_img_picker');
    const imgDrop = document.getElementById('pt_img_drop');
    const imgGrid = document.getElementById('pt_img_grid');
    const imgState = document.getElementById('pt_img_upload_state');

    const filePicker = document.getElementById('pt_file_picker');
    const fileList = document.getElementById('pt_file_list');

    const req = (url, method, body, isForm = false) => fetch(url, {
        method,
        headers: isForm ? { 'X-CSRF-TOKEN': csrf } : { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
        body: isForm ? body : JSON.stringify(body || {}),
    }).then(r => r.json());

    const uploadImageFiles = async (files) => {
        if (!files || !files.length) return;
        imgState.textContent = 'Uploading...';

        for (const f of files) {
            const fd = new FormData();
            fd.append('image', f);

            const res = await req(routes.imgUpload, 'POST', fd, true);
            if (!res.ok) {
                imgState.textContent = 'Upload failed';
                continue;
            }

            const card = document.createElement('div');
            card.className = "rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm";
            card.setAttribute('data-id', res.image.id);
            card.setAttribute('draggable', 'true');

            card.innerHTML = `
                <div class="relative">
                    <img src="${res.image.url}" class="h-36 w-full object-cover" />
                    ${res.image.is_featured ? `<span class="absolute top-2 left-2 text-xs font-semibold px-2 py-1 rounded-lg bg-emerald-600 text-white">Featured</span>` : ``}
                </div>
                <div class="p-3 space-y-2">
                    <input type="text"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-xs focus:border-[#ff4b5c] focus:bg-white focus:ring-2 focus:ring-[#ff4b5c]/20"
                        placeholder="Alt text (optional)" value="" data-alt>
                    <div class="flex items-center justify-between gap-2">
                        <button type="button" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50" data-featured>Set featured</button>
                        <button type="button" class="text-xs px-2.5 py-1.5 rounded-lg border border-red-200 text-red-700 hover:bg-red-50" data-delete>Delete</button>
                    </div>
                    <div class="text-[11px] text-slate-500">Drag to reorder</div>
                </div>
            `;
            imgGrid.appendChild(card);
        }

        imgState.textContent = 'Done';
        setTimeout(() => imgState.textContent = '—', 1500);
        wireImageActions();
        wireImageDnD();
    };

    imgPicker?.addEventListener('change', (e) => uploadImageFiles([...e.target.files]));
    imgDrop?.addEventListener('dragover', (e) => { e.preventDefault(); imgDrop.classList.add('bg-slate-100'); });
    imgDrop?.addEventListener('dragleave', () => imgDrop.classList.remove('bg-slate-100'));
    imgDrop?.addEventListener('drop', (e) => {
        e.preventDefault();
        imgDrop.classList.remove('bg-slate-100');
        uploadImageFiles([...e.dataTransfer.files].filter(f => f.type.startsWith('image/')));
    });

    const wireImageActions = () => {
        imgGrid.querySelectorAll('[data-id]').forEach(card => {
            const id = card.getAttribute('data-id');

            card.querySelector('[data-delete]')?.addEventListener('click', async () => {
                const res = await req(routes.imgDelete(id), 'DELETE');
                if (res.ok) card.remove();
            });

            card.querySelector('[data-featured]')?.addEventListener('click', async () => {
                const res = await req(routes.imgFeatured(id), 'PATCH');
                if (!res.ok) return;

                imgGrid.querySelectorAll('.bg-emerald-600').forEach(b => b.remove());
                imgGrid.querySelectorAll('[data-id]').forEach(c => {
                    const imgWrap = c.querySelector('.relative');
                    if (!imgWrap) return;
                    if (c.getAttribute('data-id') === id) {
                        const tag = document.createElement('span');
                        tag.className = "absolute top-2 left-2 text-xs font-semibold px-2 py-1 rounded-lg bg-emerald-600 text-white";
                        tag.textContent = "Featured";
                        imgWrap.appendChild(tag);
                    }
                });
            });

            card.querySelector('[data-alt]')?.addEventListener('blur', async (e) => {
                await req(routes.imgUpdate(id), 'PATCH', { alt_text: e.target.value || null });
            });
        });
    };

    const wireImageDnD = () => {
        let dragEl = null;
        imgGrid.querySelectorAll('[data-id]').forEach(el => {
            el.addEventListener('dragstart', () => { dragEl = el; el.classList.add('opacity-60'); });
            el.addEventListener('dragend', () => { dragEl = null; el.classList.remove('opacity-60'); });
            el.addEventListener('dragover', (e) => {
                e.preventDefault();
                if (!dragEl || dragEl === el) return;
                const rect = el.getBoundingClientRect();
                const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
                imgGrid.insertBefore(dragEl, next ? el.nextSibling : el);
            });
        });

        const save = async () => {
            const ids = [...imgGrid.querySelectorAll('[data-id]')].map(x => parseInt(x.getAttribute('data-id')));
            if (ids.length) await req(routes.imgReorder, 'PATCH', { ids });
        };

        imgGrid.addEventListener('drop', () => save());
    };

    const uploadFiles = async (files) => {
        if (!files || !files.length) return;

        for (const f of files) {
            const fd = new FormData();
            fd.append('file', f);
            fd.append('visibility', 'admin');

            const res = await req(routes.fileUpload, 'POST', fd, true);
            if (!res.ok) continue;

            const row = document.createElement('div');
            row.className = "flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-3";
            row.setAttribute('data-id', res.file.id);
            row.setAttribute('draggable', 'true');

            row.innerHTML = `
                <div class="min-w-0">
                    <a href="${res.file.url}" target="_blank" class="text-sm font-medium text-slate-900 hover:underline truncate block">
                        ${res.file.label ? res.file.label : res.file.name}
                    </a>
                    <div class="text-xs text-slate-500 truncate">${res.file.name}</div>
                </div>
                <div class="flex items-center gap-2">
                    <select class="rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-xs" data-visibility>
                        <option value="admin" selected>Admin</option>
                        <option value="public">Public</option>
                    </select>
                    <button type="button" class="text-xs px-2.5 py-1.5 rounded-lg border border-red-200 text-red-700 hover:bg-red-50" data-delete-file>
                        Delete
                    </button>
                </div>
            `;
            fileList.appendChild(row);
        }

        wireFileActions();
        wireFileDnD();
    };

    filePicker?.addEventListener('change', (e) => uploadFiles([...e.target.files]));

    const wireFileActions = () => {
        fileList.querySelectorAll('[data-id]').forEach(row => {
            const id = row.getAttribute('data-id');

            row.querySelector('[data-delete-file]')?.addEventListener('click', async () => {
                const res = await req(routes.fileDelete(id), 'DELETE');
                if (res.ok) row.remove();
            });

            row.querySelector('[data-visibility]')?.addEventListener('change', async (e) => {
                await req(routes.fileUpdate(id), 'PATCH', { visibility: e.target.value });
            });
        });
    };

    const wireFileDnD = () => {
        let dragEl = null;

        fileList.querySelectorAll('[data-id]').forEach(el => {
            el.addEventListener('dragstart', () => { dragEl = el; el.classList.add('opacity-60'); });
            el.addEventListener('dragend', () => { dragEl = null; el.classList.remove('opacity-60'); });
            el.addEventListener('dragover', (e) => {
                e.preventDefault();
                if (!dragEl || dragEl === el) return;
                const rect = el.getBoundingClientRect();
                const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
                fileList.insertBefore(dragEl, next ? el.nextSibling : el);
            });
        });

        const save = async () => {
            const ids = [...fileList.querySelectorAll('[data-id]')].map(x => parseInt(x.getAttribute('data-id')));
            if (ids.length) await req(routes.fileReorder, 'PATCH', { ids });
        };

        fileList.addEventListener('drop', () => save());
    };

    wireImageActions();
    wireImageDnD();
    wireFileActions();
    wireFileDnD();
})();
</script>

