<x-home-layout>
    <x-slot name="sectionTitle">Order</x-slot>
    <x-slot name="pageTitle">Order Status</x-slot>

    <div class="max-w-5xl mx-auto p-6 space-y-6">
        <section class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-lg font-black text-slate-900">Order {{ $order->order_no }}</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Status: <span class="font-semibold">{{ strtoupper((string) $order->status) }}</span>
                    </p>
                </div>
                <div class="text-sm font-semibold text-slate-900">
                    LKR {{ number_format((float) $order->grand_total, 2) }}
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                <h2 class="text-sm font-bold text-slate-900">Items</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach ($order->items as $it)
                    <div class="px-6 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-slate-900">{{ $it->title }}</div>
                                <div class="mt-1 text-xs text-slate-500">
                                    Qty: {{ $it->qty }}
                                    @if ($it->width && $it->height && $it->unit)
                                        · Size: {{ rtrim(rtrim(number_format((float) $it->width, 3), '0'), '.') }} × {{ rtrim(rtrim(number_format((float) $it->height, 3), '0'), '.') }} {{ $it->unit }}
                                    @endif
                                </div>
                            </div>
                            <div class="text-sm font-black text-slate-900">
                                LKR {{ number_format((float) $it->line_total, 2) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-home-layout>

