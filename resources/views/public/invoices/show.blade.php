<x-guest-layout :seo="[
    'title' => 'Invoice ' . ($invoice->invoice_no ?? ''),
    'description' => 'View your invoice securely.',
    'keywords' => 'printair invoice',
    'canonical' => url()->current(),
    'image' => asset('assets/printair/printairlogo.png'),
]">
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="iconify text-slate-700" data-icon="mdi:file-document-outline"></span>
                    Invoice <span class="text-[#ef233c]">{{ $invoice->invoice_no }}</span>
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    This is a secure link. For your safety, it expires soon.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:items-end">
                <a href="{{ route('invoices.public.download', ['invoice' => $invoice->id, 'token' => $token]) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-extrabold text-white hover:bg-slate-800">
                    <span class="iconify" data-icon="mdi:download"></span>
                    Download PDF
                </a>

                <div class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] font-semibold text-amber-900">
                    <span class="iconify" data-icon="mdi:timer-sand"></span>
                    Link expires in ~7 minutes
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="text-xs text-slate-500">Order</div>
                <div class="mt-1 text-sm font-extrabold text-slate-900">
                    {{ $order->order_no ?? ('ORD-' . $order->id) }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="text-xs text-slate-500">Total</div>
                <div class="mt-1 text-sm font-extrabold text-slate-900">
                    LKR {{ number_format((float) $invoice->grand_total, 2) }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="text-xs text-slate-500">Balance Due</div>
                <div class="mt-1 text-sm font-extrabold text-slate-900">
                    LKR {{ number_format((float) $invoice->amount_due, 2) }}
                </div>
                <div class="mt-1 text-[11px] text-slate-500">
                    Status: <span class="font-bold text-slate-800">{{ strtoupper((string) $invoice->status) }}</span>
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-4 py-3 text-xs font-extrabold text-slate-700 flex items-center gap-2">
                <span class="iconify" data-icon="mdi:format-list-bulleted"></span>
                Items
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-white">
                        <tr class="border-b border-slate-200 text-xs text-slate-500">
                            <th class="px-4 py-3 text-left font-bold">Item</th>
                            <th class="px-4 py-3 text-right font-bold">Qty</th>
                            <th class="px-4 py-3 text-right font-bold">Unit</th>
                            <th class="px-4 py-3 text-right font-bold">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->items as $it)
                            <tr class="border-b border-slate-200">
                                <td class="px-4 py-3">
                                    <div class="font-extrabold text-slate-900">{{ $it->title ?? 'Item' }}</div>
                                    @if ($it->width && $it->height)
                                        <div class="text-xs text-slate-500 mt-1">
                                            <span class="iconify" data-icon="mdi:ruler-square"></span>
                                            {{ $it->width }} × {{ $it->height }} {{ $it->unit }}
                                        </div>
                                    @endif

                                    @if ($it->relationLoaded('finishings') && $it->finishings->count())
                                        <div class="mt-2 text-xs text-slate-500">
                                            <div class="font-bold text-slate-700 flex items-center gap-2">
                                                <span class="iconify" data-icon="mdi:tools"></span>
                                                Finishings
                                            </div>
                                            <ul class="list-disc ml-5 mt-1">
                                                @foreach ($it->finishings as $f)
                                                    <li>
                                                        {{ $f->label ?? ($f->option?->label ?? 'Finishing') }} — LKR
                                                        {{ number_format((float) $f->total, 2) }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">{{ (int) $it->qty }}</td>
                                <td class="px-4 py-3 text-right">LKR {{ number_format((float) $it->unit_price, 2) }}</td>
                                <td class="px-4 py-3 text-right font-extrabold">
                                    LKR {{ number_format((float) $it->line_total, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-white p-4">
                <div class="max-w-md ml-auto space-y-2 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span class="font-bold text-slate-900">LKR {{ number_format((float) $invoice->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Discount</span>
                        <span class="font-bold text-slate-900">LKR {{ number_format((float) $invoice->discount_total, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Tax</span>
                        <span class="font-bold text-slate-900">LKR {{ number_format((float) $invoice->tax_total, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Delivery</span>
                        <span class="font-bold text-slate-900">LKR {{ number_format((float) $invoice->shipping_fee, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Other</span>
                        <span class="font-bold text-slate-900">LKR {{ number_format((float) $invoice->other_fee, 2) }}</span>
                    </div>

                    <div class="pt-3 mt-3 border-t border-slate-200 flex justify-between">
                        <span class="font-extrabold text-slate-900">Grand Total</span>
                        <span class="font-extrabold text-slate-900">LKR {{ number_format((float) $invoice->grand_total, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-slate-600">
                        <span>Paid</span>
                        <span class="font-bold text-slate-900">LKR {{ number_format((float) $invoice->amount_paid, 2) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="font-extrabold text-slate-900">Balance Due</span>
                        <span class="font-extrabold text-slate-900">LKR {{ number_format((float) $invoice->amount_due, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if ($invoice->payments && $invoice->payments->count())
            <div class="mt-6 rounded-2xl border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-4 py-3 text-xs font-extrabold text-slate-700 flex items-center gap-2">
                    <span class="iconify" data-icon="mdi:cash-multiple"></span>
                    Payment History
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-white">
                            <tr class="border-b border-slate-200 text-xs text-slate-500">
                                <th class="px-4 py-3 text-left font-bold">Date</th>
                                <th class="px-4 py-3 text-left font-bold">Method</th>
                                <th class="px-4 py-3 text-left font-bold">Reference</th>
                                <th class="px-4 py-3 text-right font-bold">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->payments as $p)
                                <tr class="border-b border-slate-200">
                                    <td class="px-4 py-3">{{ optional($p->paid_at)->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-3">{{ strtoupper((string) $p->method) }}</td>
                                    <td class="px-4 py-3">{{ $p->reference ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right font-extrabold">
                                        LKR {{ number_format((float) $p->amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 text-[11px] text-slate-500">
                    Note: Payment adjustments (including refunds) are managed by Printair admin team.
                </div>
            </div>
        @endif
    </div>
</x-guest-layout>

