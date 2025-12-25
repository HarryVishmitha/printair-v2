{{-- resources/views/public/estimate-share/show.blade.php --}}

<x-guest-layout :seo="$seo ?? []">
    @php
        $no = $estimate->estimate_no ?? 'EST-' . $estimate->id;
        $currency = $estimate->currency ?? 'LKR';

        $snap = is_array($estimate->customer_snapshot) ? $estimate->customer_snapshot : [];
        $customerName =
            data_get($snap, 'full_name') ?? (data_get($snap, 'name') ?? ($estimate->customer?->full_name ?? '—'));

        $status = strtolower((string) ($estimate->status ?? 'draft'));
        $canRespond = in_array($status, ['sent', 'viewed'], true);

        $logo = asset('assets/printair/printairlogo.png');
        $created = optional($estimate->created_at)->format('Y-m-d');
        $validUntil = $estimate->valid_until ? \Carbon\Carbon::parse($estimate->valid_until)->format('Y-m-d') : null;

        $showMoney = !(($otpRequired ?? false) && !($verified ?? false));
        $emailAvailable = (bool) (data_get($snap, 'email') || ($estimate->customer?->email ?? null));
    @endphp

    <div x-data="publicEstimateView()" class="space-y-6">

        {{-- TOP BRAND BAR --}}
        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-[#ff2828] via-[#ff4b5c] to-[#ff7a45] px-6 py-5 text-white">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        {{-- <div class="h-12 w-12 rounded-2xl bg-white/15 flex items-center justify-center overflow-hidden">
                            <img src="{{ $logo }}" alt="Printair" class="h-10 w-auto" />
                        </div> --}}
                        <div class="min-w-0">
                            <div class="text-xs uppercase tracking-[0.14em] text-white/80 font-semibold">
                                Printair Advertising
                            </div>
                            <h1 class="text-xl sm:text-2xl font-extrabold leading-tight truncate">
                                Quotation {{ $no }}
                            </h1>
                            <div class="mt-1 text-sm text-white/80">
                                Customer: <span class="font-semibold text-white">{{ $customerName }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-end justify-between sm:justify-end gap-4">
                        <div class="text-right">
                            <div class="text-xs uppercase tracking-[0.14em] text-white/80 font-semibold">Status</div>
                            <div
                                class="mt-1 inline-flex items-center gap-2 rounded-full bg-black/15 px-3 py-1 text-xs font-bold">
                                <span class="h-1.5 w-1.5 rounded-full bg-white/80"></span>
                                {{ strtoupper($status) }}
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-xs uppercase tracking-[0.14em] text-white/80 font-semibold">Grand Total
                            </div>
                            <div class="mt-1 text-2xl font-black text-white">
                                @if ($showMoney)
                                    {{ $currency }} {{ number_format((float) $estimate->grand_total, 2) }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-white/80">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">
                        <span class="font-semibold">Created:</span> {{ $created }}
                    </span>
                    @if ($validUntil)
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">
                            <span class="font-semibold">Valid Until:</span> {{ $validUntil }}
                        </span>
                    @endif

                    @if (!empty($waLink))
                        <a href="{{ $waLink }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 hover:bg-white/15">
                            <span class="font-semibold">Need help?</span>
                            WhatsApp Support →
                        </a>
                    @endif
                </div>
            </div>

            {{-- KPI STRIP --}}
            <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2 lg:grid-cols-4">
                @php
                    $kpis = [
                        ['label' => 'Subtotal', 'value' => (float) $estimate->subtotal],
                        ['label' => 'Discount', 'value' => (float) $estimate->discount_total],
                        ['label' => 'Tax', 'value' => (float) $estimate->tax_total],
                        [
                            'label' => 'Fees',
                            'value' => (float) (($estimate->shipping_fee ?? 0) + ($estimate->other_fee ?? 0)),
                        ],
                    ];
                @endphp

                @foreach ($kpis as $k)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            {{ $k['label'] }}</div>
                        <div class="mt-2 text-lg font-black text-slate-900">
                            @if ($showMoney)
                                {{ $currency }} {{ number_format($k['value'], 2) }}
                            @else
                                —
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- FLASHES --}}
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        {{-- OTP GATE --}}
        @if (($otpRequired ?? false) && !($verified ?? false))
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="max-w-xl">
                        <h2 class="text-sm font-extrabold text-slate-900">Verify to view & respond</h2>
                        <p class="mt-1 text-sm text-slate-600">
                            We sent a 6-digit OTP to <span
                                class="font-semibold text-slate-900">{{ $maskedEmail ?? 'your email' }}</span>.
                        </p>
                        <div
                            class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600">
                            This step protects your quotation from being accessed by unintended viewers.
                        </div>
                    </div>

                    <form method="POST" action="{{ route('estimates.public.otp.send', request()->route('token')) }}">
                        @csrf
                        <button
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            Resend OTP
                        </button>
                    </form>
                </div>

                <form method="POST" action="{{ route('estimates.public.otp.verify', request()->route('token')) }}"
                    class="mt-5">
                    @csrf
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_auto] sm:items-center">
                        <input name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" minlength="6"
                            required placeholder="Enter OTP (6 digits)"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#ff2828] focus:ring-2 focus:ring-[#ff2828]/20" />

                        <button
                            class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                            Verify
                        </button>
                    </div>
                </form>
            </section>
        @else
            {{-- ITEMS + TOTALS GRID --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {{-- Items --}}
                <section class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden lg:col-span-2">
                    <div class="bg-slate-50/70 px-5 py-4 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-extrabold text-slate-900">Items</div>
                            <div class="mt-1 text-xs text-slate-500">Review what’s included in this quotation.</div>
                        </div>
                        <div class="text-xs text-slate-500">
                            Items: <span
                                class="font-semibold text-slate-900">{{ ($estimate->items ?? collect())->count() }}</span>
                        </div>
                    </div>

                    @if (($estimate->items ?? collect())->count() === 0)
                        <div class="px-5 py-10 text-sm text-slate-500">No items.</div>
                    @else
                        {{-- Desktop table --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-white">
                                    <tr>
                                        <th
                                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Product</th>
                                        <th
                                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Title</th>
                                        <th
                                            class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Qty</th>
                                        <th
                                            class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach ($estimate->items as $it)
                                        @php
                                            $p = $it->product;
                                            $placeholder = asset('assets/placeholders/product.png');
                                            $imgPath = $p?->primaryImage?->path ? ltrim((string) $p->primaryImage->path, '/') : '';
                                            $imgUrl = $imgPath !== '' ? asset('storage/' . $imgPath) : $placeholder;
                                            $isPublicProduct = $p && $p->status === 'active' && $p->visibility === 'public';
                                            $productUrl = $isPublicProduct ? route('products.show', ['product' => $p->slug]) : null;
                                        @endphp
                                        <tr class="hover:bg-slate-50/60">
                                            <td class="px-5 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="h-12 w-12 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                                                        @if ($productUrl)
                                                            <a href="{{ $productUrl }}" target="_blank" rel="noopener">
                                                                <img src="{{ $imgUrl }}" alt="{{ $p?->name ?? 'Product' }}"
                                                                    class="h-full w-full object-cover" />
                                                            </a>
                                                        @else
                                                            <img src="{{ $imgUrl }}" alt="{{ $p?->name ?? 'Product' }}"
                                                                class="h-full w-full object-cover" />
                                                        @endif
                                                    </div>

                                                    <div class="min-w-0">
                                                        @if ($productUrl)
                                                            <a href="{{ $productUrl }}" target="_blank" rel="noopener"
                                                                class="block truncate font-semibold text-slate-900 hover:text-slate-700">
                                                                {{ $p->name }}
                                                            </a>
                                                            <div class="mt-0.5 text-xs text-slate-500">View product</div>
                                                        @else
                                                            <div class="truncate font-semibold text-slate-900">
                                                                {{ $p?->name ?? '#' . $it->product_id }}
                                                            </div>
                                                            <div class="mt-0.5 text-xs text-slate-500">No public link</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4">
                                                <div class="font-semibold text-slate-900">{{ $it->title }}</div>
                                                @if ($it->description)
                                                    <div class="mt-1 text-xs text-slate-500">{{ $it->description }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 text-right">
                                                {{ rtrim(rtrim(number_format((float) $it->qty, 2), '0'), '.') }}</td>
                                            <td class="px-5 py-4 text-right font-extrabold text-slate-900">
                                                @if ($showMoney)
                                                    {{ $currency }}
                                                    {{ number_format((float) $it->line_total, 2) }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile cards --}}
                        <div class="md:hidden divide-y divide-slate-100 bg-white">
                            @foreach ($estimate->items as $it)
                                @php
                                    $p = $it->product;
                                    $placeholder = asset('assets/placeholders/product.png');
                                    $imgPath = $p?->primaryImage?->path ? ltrim((string) $p->primaryImage->path, '/') : '';
                                    $imgUrl = $imgPath !== '' ? asset('storage/' . $imgPath) : $placeholder;
                                    $isPublicProduct = $p && $p->status === 'active' && $p->visibility === 'public';
                                    $productUrl = $isPublicProduct ? route('products.show', ['product' => $p->slug]) : null;
                                @endphp
                                <div class="p-5">
                                    <div class="flex items-center gap-3">
                                        <div class="h-12 w-12 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                                            @if ($productUrl)
                                                <a href="{{ $productUrl }}" target="_blank" rel="noopener">
                                                    <img src="{{ $imgUrl }}" alt="{{ $p?->name ?? 'Product' }}" class="h-full w-full object-cover" />
                                                </a>
                                            @else
                                                <img src="{{ $imgUrl }}" alt="{{ $p?->name ?? 'Product' }}" class="h-full w-full object-cover" />
                                            @endif
                                        </div>

                                        <div class="min-w-0">
                                            @if ($productUrl)
                                                <a href="{{ $productUrl }}" target="_blank" rel="noopener"
                                                    class="block truncate text-sm font-semibold text-slate-900 hover:text-slate-700">
                                                    {{ $p->name }}
                                                </a>
                                                <div class="mt-0.5 text-xs text-slate-500">Tap to view product</div>
                                            @else
                                                <div class="truncate text-sm font-semibold text-slate-900">
                                                    {{ $p?->name ?? '#' . $it->product_id }}
                                                </div>
                                                <div class="mt-0.5 text-xs text-slate-500">No public link</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-1 font-extrabold text-slate-900">{{ $it->title }}</div>
                                    @if ($it->description)
                                        <div class="mt-1 text-sm text-slate-600">{{ $it->description }}</div>
                                    @endif

                                    <div class="mt-3 flex items-center justify-between text-sm">
                                        <div class="text-slate-600">Qty</div>
                                        <div class="font-semibold text-slate-900">
                                            {{ rtrim(rtrim(number_format((float) $it->qty, 2), '0'), '.') }}</div>
                                    </div>

                                    <div class="mt-1 flex items-center justify-between text-sm">
                                        <div class="text-slate-600">Line Total</div>
                                        <div class="font-extrabold text-slate-900">
                                            @if ($showMoney)
                                                {{ $currency }} {{ number_format((float) $it->line_total, 2) }}
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                {{-- Totals + Notes --}}
                <aside class="space-y-6">
                    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-extrabold text-slate-900">Totals</div>
                        <div class="mt-4 space-y-2 text-sm">
                            @php
                                $rows = [
                                    ['Subtotal', (float) $estimate->subtotal],
                                    ['Discount', (float) $estimate->discount_total],
                                    ['Tax', (float) $estimate->tax_total],
                                    ['Shipping', (float) ($estimate->shipping_fee ?? 0)],
                                    ['Other', (float) ($estimate->other_fee ?? 0)],
                                ];
                            @endphp

                            @foreach ($rows as [$label, $val])
                                <div class="flex items-center justify-between">
                                    <div class="text-slate-600">{{ $label }}</div>
                                    <div class="font-semibold text-slate-900">
                                        @if ($showMoney)
                                            {{ $currency }} {{ number_format($val, 2) }}
                                        @else
                                            —
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <div class="pt-3 mt-3 border-t border-slate-200 flex items-center justify-between">
                                <div class="text-slate-900 font-extrabold">Grand Total</div>
                                <div class="text-slate-900 font-black text-lg">
                                    @if ($showMoney)
                                        {{ $currency }} {{ number_format((float) $estimate->grand_total, 2) }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>

                    @if ($estimate->notes_customer)
                        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="text-sm font-extrabold text-slate-900">Notes</div>
                            <div class="mt-2 text-sm text-slate-600 whitespace-pre-wrap">
                                {{ $estimate->notes_customer }}</div>
                        </section>
                    @endif

                    @if ($estimate->terms)
                        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="text-sm font-extrabold text-slate-900">Terms</div>
                            <div class="mt-2 text-sm text-slate-600 whitespace-pre-wrap">{{ $estimate->terms }}</div>
                        </section>
                    @endif

                    @if (!empty($waLink))
                        <section class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5">
                            <div class="text-sm font-extrabold text-emerald-900">Need help?</div>
                            <div class="mt-1 text-sm text-emerald-900/80">
                                Chat with Printair admins via WhatsApp for quick clarifications.
                            </div>
                            <a href="{{ $waLink }}" target="_blank" rel="noopener"
                                class="mt-3 inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                                WhatsApp Support
                            </a>
                        </section>
                    @endif
                </aside>
            </div>
        @endif

        {{-- ACTIONS (Sticky on mobile) --}}
        <div class="lg:static lg:bg-transparent lg:p-0">
            <div class="fixed inset-x-0 bottom-0 z-40 lg:relative">
                <div class="mx-auto max-w-5xl px-4 pb-4 lg:px-0">
                    <div class="rounded-3xl border border-slate-200 bg-white shadow-lg p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="text-sm text-slate-600">
                                @if (($otpRequired ?? false) && !($verified ?? false))
                                    Verify OTP to view and respond.
                                @elseif (!$emailAvailable)
                                    Online accept/reject is disabled because no email is attached to this estimate.
                                @elseif ($canRespond)
                                    Ready to respond? Accept or reject below.
                                @else
                                    This quotation is not accepting responses at this time.
                                @endif
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end sm:gap-3">
                                @if (($otpRequired ?? false) && !($verified ?? false))
                                    <div class="text-xs text-slate-500">OTP required</div>
                                @elseif (!$emailAvailable)
                                    @if (!empty($waLink))
                                        <a class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                            href="{{ $waLink }}" target="_blank" rel="noopener">
                                            Contact via WhatsApp
                                        </a>
                                    @endif
                                @elseif ($canRespond)
                                    <form method="POST"
                                        action="{{ route('estimates.public.accept', request()->route('token')) }}">
                                        @csrf
                                        <button
                                            class="w-full sm:w-auto inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                                            Accept Quotation
                                        </button>
                                    </form>

                                    <button type="button" @click="openReject = true"
                                        class="w-full sm:w-auto inline-flex items-center justify-center rounded-2xl border border-rose-200 bg-white px-5 py-2.5 text-sm font-semibold text-rose-600 shadow-sm hover:bg-rose-50">
                                        Reject
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Spacer so fixed bar doesn't cover content --}}
            <div class="h-24 lg:hidden"></div>
        </div>

        {{-- Reject Modal --}}
        @if (!($otpRequired ?? false) || ($verified ?? false))
            <div x-show="openReject" x-cloak>
                <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm" @click="openReject = false"></div>
                <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl overflow-hidden"
                        @click.away="openReject = false">
                        <div class="bg-gradient-to-r from-rose-500 to-rose-600 px-6 py-5 text-white">
                            <div class="text-lg font-extrabold">Reject Quotation</div>
                            <div class="mt-1 text-sm text-white/80">Tell us why you’re rejecting so we can improve.
                            </div>
                        </div>

                        <form method="POST"
                            action="{{ route('estimates.public.reject', request()->route('token')) }}"
                            class="p-6">
                            @csrf
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Reason
                            </label>
                            <textarea name="reason" required maxlength="500" rows="4"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-500 focus:bg-white focus:ring-2 focus:ring-rose-500/20"
                                placeholder="Please share the reason (max 500 chars)…"></textarea>

                            <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:justify-end sm:gap-3">
                                <button type="button" @click="openReject = false"
                                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    Cancel
                                </button>

                                <button
                                    class="inline-flex items-center justify-center rounded-2xl bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">
                                    Submit Rejection
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    </div>

    <script>
        function publicEstimateView() {
            return {
                openReject: false,
            }
        }
    </script>
</x-guest-layout>
