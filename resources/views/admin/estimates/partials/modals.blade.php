{{-- resources/views/admin/estimates/partials/modals.blade.php --}}

<div
    x-data="{
        open: false,
        mode: null,
        id: null,
        no: '',
        reason: '',
        init() { this.$watch('open', v => document.body.classList.toggle('overflow-hidden', v)); }
    }"
    @open-send-estimate.window="open=true; mode='send'; id=$event.detail.id; no=$event.detail.no; reason=''"
    @open-accept-estimate.window="open=true; mode='accept'; id=$event.detail.id; no=$event.detail.no; reason=''"
    @open-reject-estimate.window="open=true; mode='reject'; id=$event.detail.id; no=$event.detail.no; reason=''"
    @open-convert-order.window="open=true; mode='convert'; id=$event.detail.id; no=$event.detail.no; reason=''"
    @keydown.escape.window="open=false"
    x-cloak
>
    {{-- Backdrop --}}
    <div x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm"
        @click="open=false"></div>

    {{-- Panel --}}
    <div x-show="open"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 scale-95 translate-y-3"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-3"
        class="fixed inset-0 z-50 flex items-center justify-center p-4">

        <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl shadow-slate-900/20 overflow-hidden"
            @click.away="open=false">

            <div class="px-6 py-5 border-b border-slate-100">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900" x-text="
                            mode === 'send' ? 'Send Estimate' :
                            mode === 'accept' ? 'Accept Estimate' :
                            mode === 'reject' ? 'Reject Estimate' :
                            'Convert to Order'
                        "></h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Estimate: <span class="font-semibold text-slate-900" x-text="no"></span>
                        </p>
                    </div>

                    <button @click="open=false" class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                        Reason <span x-show="mode === 'reject'" class="text-rose-600">*</span>
                    </label>
                    <textarea x-model="reason" rows="3"
                        class="block w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10"
                        :placeholder="mode === 'reject' ? 'Reason is required…' : 'Add an internal note for audit trail…'"></textarea>

                    <p class="mt-2 text-[11px] text-slate-500">
                        This will be saved to status history as an audit note.
                    </p>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
                    <span class="font-semibold">Heads up:</span>
                    <span x-show="mode==='send'">Sending locks the estimate and prevents item edits.</span>
                    <span x-show="mode==='reject'">Rejected estimates are treated as terminal decisions (unless your policy allows re-open).</span>
                    <span x-show="mode==='convert'">Conversion is idempotent — it won’t duplicate orders if already converted.</span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                <button @click="open=false"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                    Cancel
                </button>

                <form method="POST"
                    :action="
                        mode==='send' ? `{{ url('/admin/estimates') }}/${id}/send` :
                        mode==='accept' ? `{{ url('/admin/estimates') }}/${id}/accept` :
                        mode==='reject' ? `{{ url('/admin/estimates') }}/${id}/reject` :
                        `{{ url('/admin/estimates') }}/${id}/convert-to-order`
                    ">
                    @csrf
                    <input type="hidden" name="reason" :value="mode === 'reject' ? (reason || 'Rejected by admin') : reason">

                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-md transition-all"
                        :class="
                            mode==='send' ? 'bg-slate-900 hover:bg-slate-800' :
                            mode==='accept' ? 'bg-emerald-600 hover:bg-emerald-700' :
                            mode==='reject' ? 'bg-rose-600 hover:bg-rose-700' :
                            'bg-amber-500 hover:bg-amber-600'
                        ">
                        Confirm
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
