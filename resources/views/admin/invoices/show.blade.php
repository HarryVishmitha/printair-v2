<x-app-layout>
    <x-slot name="sectionTitle">Sales</x-slot>
    <x-slot name="pageTitle">Invoice</x-slot>

    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.orders.index') }}" class="text-slate-500 hover:text-slate-700">Orders</a>
        <span class="mx-1 opacity-60">/</span>
        <a href="{{ route('admin.orders.show', $invoice->order_id) }}" class="text-slate-500 hover:text-slate-700">
            {{ $invoice->order?->order_no ?? ('ORD-' . $invoice->order_id) }}
        </a>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">{{ $invoice->invoice_no ?? ('INV-' . $invoice->id) }}</span>
    </x-slot>

    @php
        $currency = $invoice->currency ?? 'LKR';
        $status = (string) ($invoice->status ?? 'draft');
        $type = (string) ($invoice->type ?? 'final');

        $badgeMap = [
            'draft' => 'bg-slate-50 text-slate-700 ring-slate-200',
            'issued' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'partial' => 'bg-amber-50 text-amber-800 ring-amber-200',
            'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'overdue' => 'bg-rose-50 text-rose-700 ring-rose-200',
            'void' => 'bg-slate-100 text-slate-600 ring-slate-200',
            'refunded' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
        ];
        $badge = $badgeMap[$status] ?? $badgeMap['draft'];

        $grand = (float) ($invoice->grand_total ?? 0);
        $paid = (float) ($invoice->amount_paid ?? 0);
        $due = (float) ($invoice->amount_due ?? 0);
        $depositRequired = $invoice->deposit_required_amount;
        $pricingFrozenAt = $invoice->pricing_frozen_at;

        $snap = is_array($invoice->customer_snapshot) ? $invoice->customer_snapshot : [];
        $customerName = $snap['full_name'] ?? $snap['name'] ?? ($invoice->order?->customer?->full_name ?? '—');
        $customerEmail = $snap['email'] ?? ($invoice->order?->customer?->email ?? null);
        $customerPhone = $snap['phone'] ?? ($invoice->order?->customer?->phone ?? null);
    @endphp

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Header --}}
        <section
            class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-5 py-5 sm:px-7 sm:py-6 text-white shadow-lg shadow-slate-900/20">
            <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-16 right-10 h-56 w-56 rounded-full border border-white/10"></div>

            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-2 max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium tracking-wide backdrop-blur">
                        <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        Billing · Invoices
                    </div>

                    <div class="flex items-start gap-3 pt-1">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 shadow-inner shadow-black/20">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 7.5h6M9 12h6m-9 7.5h12A2.25 2.25 0 0020.25 17.25V6.75A2.25 2.25 0 0018 4.5H6A2.25 2.25 0 003.75 6.75v10.5A2.25 2.25 0 006 19.5z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-xl sm:text-2xl font-black leading-tight truncate">
                                    {{ $invoice->invoice_no ?? ('INV-' . $invoice->id) }}
                                </h2>
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 {{ $badge }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ $status }}
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide">
                                    {{ $type }}
                                </span>
                            </div>
                            <div class="mt-1 text-xs text-white/70">
                                Order: {{ $invoice->order?->order_no ?? ('ORD-' . $invoice->order_id) }}
                                · WG: {{ $invoice->working_group_id }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <a href="{{ route('admin.orders.show', $invoice->order_id) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white hover:bg-white/15">
                        <span>View order</span>
                    </a>

                    <a href="{{ route('admin.invoices.pdf', $invoice) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white hover:bg-white/15">
                        <span>PDF</span>
                    </a>

                    <form method="POST" action="{{ route('admin.invoices.email', $invoice) }}">
                        @csrf
                        <button
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-white hover:bg-white/15">
                            Email to customer
                        </button>
                    </form>

                    @can('issue', $invoice)
                        @if ($invoice->status === 'draft')
                            <form method="POST" action="{{ route('admin.invoices.issue', $invoice) }}">
                                @csrf
                                <button
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2 text-xs font-extrabold text-slate-900 hover:bg-slate-100">
                                    Issue invoice
                                </button>
                            </form>
                        @endif
                    @endcan

                    @can('void', $invoice)
                        @if (!in_array($invoice->status, ['void', 'refunded'], true))
                            <button type="button"
                                onclick="document.getElementById('voidInvoiceModal').showModal()"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-500/90 px-4 py-2 text-xs font-extrabold text-white hover:bg-rose-500">
                                Void
                            </button>
                        @endif
                    @endcan
                </div>
            </div>
        </section>

        {{-- Summary --}}
        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="text-xs text-slate-500">Total</div>
                <div class="mt-1 text-lg font-black text-slate-900">{{ $currency }} {{ number_format($grand, 2) }}</div>
                <div class="mt-2 text-[11px] text-slate-500">
                    Issued: {{ $invoice->issued_at ? $invoice->issued_at->format('Y-m-d H:i') : '—' }}
                </div>
                <div class="mt-1 text-[11px] text-slate-500">
                    Deposit required: {{ $depositRequired !== null ? ($currency . ' ' . number_format((float) $depositRequired, 2)) : '—' }}
                </div>
                <div class="mt-1 text-[11px] text-slate-500">
                    Pricing frozen: {{ $pricingFrozenAt ? $pricingFrozenAt->format('Y-m-d H:i') : '—' }}
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="text-xs text-slate-500">Paid</div>
                <div class="mt-1 text-lg font-black text-slate-900">{{ $currency }} {{ number_format($paid, 2) }}</div>
                <div class="mt-2 text-[11px] text-slate-500">
                    Paid at: {{ $invoice->paid_at ? $invoice->paid_at->format('Y-m-d H:i') : '—' }}
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="text-xs text-slate-500">Amount due</div>
                <div class="mt-1 text-lg font-black text-slate-900">{{ $currency }} {{ number_format($due, 2) }}</div>
                <div class="mt-2 text-[11px] text-slate-500">
                    Due: {{ $invoice->due_at ? $invoice->due_at->format('Y-m-d H:i') : '—' }}
                </div>
            </div>
        </section>

        {{-- Customer --}}
        <section class="rounded-3xl border border-slate-200 bg-white p-6">
            <div class="text-sm font-black text-slate-900">Customer</div>
            <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                <div>
                    <div class="text-xs text-slate-500">Name</div>
                    <div class="font-semibold text-slate-900">{{ $customerName }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Email</div>
                    <div class="font-semibold text-slate-900 break-all">{{ $customerEmail ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Phone</div>
                    <div class="font-semibold text-slate-900">{{ $customerPhone ?: '—' }}</div>
                </div>
            </div>
        </section>

        {{-- Items --}}
        <section class="rounded-3xl border border-slate-200 bg-white p-6">
            <div class="flex items-center justify-between gap-3">
                <div class="text-sm font-black text-slate-900">Invoice items</div>
                <div class="text-xs text-slate-500">{{ $invoice->items->count() }} item(s)</div>
            </div>

            <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-xs font-black text-slate-700">
                            <th class="px-4 py-3 text-left">Item</th>
                            <th class="px-4 py-3 text-right">Qty</th>
                            <th class="px-4 py-3 text-right">Unit</th>
                            <th class="px-4 py-3 text-right">Line</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->items as $it)
                            <tr class="border-t border-slate-200">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900">{{ $it->title ?? $it->product?->name ?? 'Item' }}</div>
                                    @if ($it->width && $it->height)
                                        <div class="mt-0.5 text-xs text-slate-500">
                                            {{ $it->width }} × {{ $it->height }} {{ $it->unit }}
                                        </div>
                                    @endif
                                    @if ($it->variantSetItem?->option)
                                        <div class="mt-0.5 text-xs text-slate-500">
                                            Variant: {{ $it->variantSetItem->option->label }}
                                        </div>
                                    @endif
                                    @if ($it->roll)
                                        <div class="mt-0.5 text-xs text-slate-500">Roll: {{ $it->roll->name }}</div>
                                    @endif
                                    @if ($it->finishings?->count())
                                        <div class="mt-2 space-y-1 text-xs text-slate-600">
                                            <div class="font-semibold text-slate-700">Finishings</div>
                                            @foreach ($it->finishings as $f)
                                                <div class="flex items-center justify-between gap-3">
                                                    <div class="min-w-0 truncate">
                                                        {{ $f->label ?? $f->option?->label ?? $f->finishingProduct?->name ?? 'Finishing' }}
                                                        <span class="text-slate-500">× {{ $f->qty }}</span>
                                                    </div>
                                                    <div class="font-semibold text-slate-700 whitespace-nowrap">
                                                        {{ $currency }} {{ number_format((float) $f->total, 2) }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">{{ $it->qty }}</td>
                                <td class="px-4 py-3 text-right">{{ $currency }} {{ number_format((float) $it->unit_price, 2) }}</td>
                                <td class="px-4 py-3 text-right font-bold">{{ $currency }} {{ number_format((float) $it->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Payments / Allocations --}}
        <section class="rounded-3xl border border-slate-200 bg-white p-6">
            <div class="flex items-center justify-between gap-3">
                <div class="text-sm font-black text-slate-900">Payments</div>
                <div class="text-xs text-slate-500">Invoice-level ledger + allocated receipts</div>
            </div>

            {{-- Invoice payment ledger (supports negative adjustments) --}}
            <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm font-black text-slate-900">Invoice payment entries</div>
                    <div class="text-xs text-slate-600">
                        Total: <span class="font-bold text-slate-900">{{ $currency }} {{ number_format((float) ($invoice->payments?->sum('amount') ?? 0), 2) }}</span>
                    </div>
                </div>
                <div class="mt-1 text-xs text-slate-600">
                    These entries affect <span class="font-semibold">amount_paid</span> and <span class="font-semibold">amount_due</span>. Use negative amounts for adjustments.
                </div>

                <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200 bg-white">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-xs font-black text-slate-700">
                                <th class="px-4 py-3 text-left">Method</th>
                                <th class="px-4 py-3 text-right">Amount</th>
                                <th class="px-4 py-3 text-left">Paid at</th>
                                <th class="px-4 py-3 text-left">Reference</th>
                                <th class="px-4 py-3 text-left">Note</th>
                                <th class="px-4 py-3 text-left">By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invoice->payments as $p)
                                <tr class="border-t border-slate-200">
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-bold text-slate-700">
                                            {{ strtoupper((string) $p->method) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold">
                                        {{ $currency }} {{ number_format((float) $p->amount, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-600">
                                        {{ $p->paid_at ? $p->paid_at->format('Y-m-d H:i') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-600">
                                        {{ $p->reference ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-600">
                                        {{ $p->note ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-600">
                                        {{ $p->createdBy?->first_name ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr class="border-t border-slate-200">
                                    <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">
                                        No invoice payment entries yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @can('addPayment', $invoice)
                    <div class="mt-5">
                        @if ($status === 'draft')
                            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900">
                                Issue the invoice before adding payment entries.
                            </div>
                        @else
                            <form method="POST" action="{{ route('admin.invoices.payments.add', $invoice) }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
                                @csrf

                                <div class="md:col-span-1">
                                    <label class="block text-xs font-semibold text-slate-600">Method</label>
                                    <select name="method" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                                        <option value="cash">cash</option>
                                        <option value="bank">bank</option>
                                        <option value="card">card</option>
                                        <option value="online">online</option>
                                        <option value="adjustment">adjustment</option>
                                    </select>
                                </div>

                                <div class="md:col-span-1">
                                    <label class="block text-xs font-semibold text-slate-600">Amount (can be -)</label>
                                    <input name="amount"
                                        class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                        placeholder="5000 or -500">
                                </div>

                                <div class="md:col-span-1">
                                    <label class="block text-xs font-semibold text-slate-600">Paid at</label>
                                    <input name="paid_at" type="datetime-local"
                                        class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                                </div>

                                <div class="md:col-span-1">
                                    <label class="block text-xs font-semibold text-slate-600">Reference</label>
                                    <input name="reference"
                                        class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                        placeholder="Slip/Txn ID">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-600">Note</label>
                                    <input name="note"
                                        class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                        placeholder="Deposit / adjustment note">
                                </div>

                                <div class="md:col-span-6 flex justify-end">
                                    <button class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-extrabold text-white hover:bg-slate-800">
                                        Add entry
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @endcan
            </div>

            {{-- Allocated receipts (payments -> allocations) --}}
            <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-xs font-black text-slate-700">
                            <th class="px-4 py-3 text-left">Payment</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Allocated</th>
                            <th class="px-4 py-3 text-left">Received</th>
                            <th class="px-4 py-3 text-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoice->allocations as $alloc)
                            <tr class="border-t border-slate-200">
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">
                                        {{ $alloc->payment?->reference_no ?: ('PAY-' . $alloc->payment_id) }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ strtoupper((string) ($alloc->payment?->method ?? '')) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-bold text-slate-700">
                                        {{ strtoupper((string) ($alloc->payment?->status ?? '')) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-bold">{{ $currency }} {{ number_format((float) $alloc->amount, 2) }}</td>
                                <td class="px-4 py-3 text-xs text-slate-600">
                                    {{ $alloc->payment?->received_at ? $alloc->payment->received_at->format('Y-m-d H:i') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a class="text-xs font-semibold text-slate-700 hover:text-slate-900"
                                        href="{{ route('admin.payments.show', $alloc->payment_id) }}">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr class="border-t border-slate-200">
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">
                                    No payments allocated yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <div class="text-sm font-black text-slate-900">Record a payment receipt</div>
                <div class="mt-1 text-xs text-slate-600">
                    This creates a payment record. Allocate it to this invoice from the Payment page.
                </div>

                <form method="POST" action="{{ route('admin.payments.store') }}" class="mt-4 grid grid-cols-1 md:grid-cols-6 gap-3">
                    @csrf
                    <input type="hidden" name="working_group_id" value="{{ $invoice->working_group_id }}">
                    <input type="hidden" name="customer_id" value="{{ $invoice->order?->customer_id }}">

                    <div class="md:col-span-1">
                        <label class="block text-xs font-semibold text-slate-600">Method</label>
                        <select name="method" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                            <option value="cash">cash</option>
                            <option value="card">card</option>
                            <option value="bank_transfer">bank_transfer</option>
                            <option value="online_gateway">online_gateway</option>
                        </select>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-xs font-semibold text-slate-600">Amount</label>
                        <input name="amount" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" placeholder="5000">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600">Reference</label>
                        <input name="reference_no" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" placeholder="Slip/Txn ID">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600">Received at (optional)</label>
                        <input name="received_at" type="datetime-local"
                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                    </div>

                    <div class="md:col-span-6 flex justify-end">
                        <button class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-extrabold text-white hover:bg-slate-800">
                            Create payment
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    {{-- Void modal --}}
    <dialog id="voidInvoiceModal" class="rounded-2xl border border-slate-200 p-0 w-[92%] max-w-lg">
        <form method="POST" action="{{ route('admin.invoices.void', $invoice) }}" class="p-6">
            @csrf
            <div class="text-sm font-black text-slate-900">Void invoice</div>
            <div class="mt-1 text-sm text-slate-600">Add a reason for voiding this invoice.</div>

            <div class="mt-4">
                <label class="block text-xs font-semibold text-slate-600">Reason</label>
                <textarea name="reason" rows="3"
                    class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                    required></textarea>
            </div>

            <div class="mt-5 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('voidInvoiceModal').close()"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>
                <button
                    class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-extrabold text-white hover:bg-rose-700">
                    Void invoice
                </button>
            </div>
        </form>
    </dialog>
</x-app-layout>
