<x-app-layout>
    <x-slot name="sectionTitle">Sales</x-slot>
    <x-slot name="pageTitle">Payment</x-slot>

    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.orders.index') }}" class="text-slate-500 hover:text-slate-700">Orders</a>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">{{ $payment->reference_no ?: ('PAY-' . $payment->id) }}</span>
    </x-slot>

    @php
        $currency = $payment->currency ?? 'LKR';
        $status = (string) ($payment->status ?? 'pending');

        $badgeMap = [
            'pending' => 'bg-amber-50 text-amber-800 ring-amber-200',
            'confirmed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'failed' => 'bg-rose-50 text-rose-700 ring-rose-200',
            'void' => 'bg-slate-100 text-slate-600 ring-slate-200',
            'refunded' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
        ];
        $badge = $badgeMap[$status] ?? $badgeMap['pending'];

        $allocated = (float) ($payment->allocations?->sum('amount') ?? 0);
        $amount = (float) ($payment->amount ?? 0);
        $remaining = max(0.0, $amount - $allocated);
    @endphp

    <div class="space-y-6" x-data="{ invoiceId: '', allocating: false }">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <section class="rounded-3xl border border-slate-200 bg-white p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-xs text-slate-500">Reference</div>
                    <div class="text-xl font-black text-slate-900">{{ $payment->reference_no ?: ('PAY-' . $payment->id) }}</div>
                    <div class="mt-1 text-xs text-slate-500">
                        Method: <span class="font-semibold text-slate-800">{{ strtoupper((string) $payment->method) }}</span>
                        · WG: <span class="font-semibold text-slate-800">{{ $payment->working_group_id }}</span>
                    </div>
                </div>

                <div class="text-right">
                    <div class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 {{ $badge }}">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        {{ $status }}
                    </div>
                    <div class="mt-2 text-xs text-slate-500">Amount</div>
                    <div class="text-lg font-black text-slate-900">{{ $currency }} {{ number_format($amount, 2) }}</div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="text-xs text-slate-500">Allocated</div>
                    <div class="mt-1 font-extrabold text-slate-900">{{ $currency }} {{ number_format($allocated, 2) }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="text-xs text-slate-500">Remaining</div>
                    <div class="mt-1 font-extrabold text-slate-900">{{ $currency }} {{ number_format($remaining, 2) }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="text-xs text-slate-500">Received at</div>
                    <div class="mt-1 font-extrabold text-slate-900">
                        {{ $payment->received_at ? $payment->received_at->format('Y-m-d H:i') : '—' }}
                    </div>
                </div>
            </div>

            @can('confirm', $payment)
                @if ($payment->status === 'pending')
                    <form method="POST" action="{{ route('admin.payments.confirm', $payment) }}" class="mt-6">
                        @csrf
                        <button class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-extrabold text-white hover:bg-slate-800">
                            Confirm payment
                        </button>
                    </form>
                @endif
            @endcan
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6">
            <div class="flex items-center justify-between gap-3">
                <div class="text-sm font-black text-slate-900">Allocations</div>
                <div class="text-xs text-slate-500">Allocate confirmed payments to issued invoices</div>
            </div>

            <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-xs font-black text-slate-700">
                            <th class="px-4 py-3 text-left">Invoice</th>
                            <th class="px-4 py-3 text-left">Order</th>
                            <th class="px-4 py-3 text-right">Amount</th>
                            <th class="px-4 py-3 text-left">Allocated at</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payment->allocations as $a)
                            <tr class="border-t border-slate-200">
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.invoices.show', $a->invoice_id) }}" class="font-semibold text-slate-900 hover:underline">
                                        {{ $a->invoice?->invoice_no ?? ('INV-' . $a->invoice_id) }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600">
                                    {{ $a->invoice?->order?->order_no ?? ('ORD-' . ($a->invoice?->order_id ?? '')) }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold">{{ $currency }} {{ number_format((float) $a->amount, 2) }}</td>
                                <td class="px-4 py-3 text-xs text-slate-600">
                                    {{ $a->created_at ? $a->created_at->format('Y-m-d H:i') : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr class="border-t border-slate-200">
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">
                                    No allocations yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (auth()->user()?->can('manage-orderFlow'))
                <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="text-sm font-black text-slate-900">Allocate to invoice</div>
                    <div class="mt-1 text-xs text-slate-600">
                        Enter an invoice ID (numeric) and allocation amount.
                    </div>

                    <form method="POST"
                        x-data="{
                            get action() {
                                const id = String(this.invoiceId || '').trim();
                                return id ? '{{ url('/admin/payments/' . $payment->id . '/allocate') }}/' + id : '#';
                            }
                        }"
                        :action="action"
                        class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3"
                        @submit="if (!invoiceId) { $event.preventDefault(); }">
                        @csrf

                        <div class="md:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600">Invoice ID</label>
                            <input x-model="invoiceId"
                                class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                placeholder="123">
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600">Amount</label>
                            <input name="amount"
                                class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                placeholder="5000">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600">Reason (optional)</label>
                            <input name="reason"
                                class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                placeholder="Bank slip allocation">
                        </div>

                        <div class="md:col-span-4 flex justify-end">
                            <button
                                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-extrabold text-white hover:bg-slate-800 disabled:opacity-60"
                                :disabled="!invoiceId">
                                Allocate
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
