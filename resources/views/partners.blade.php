<x-home-layout :seo="$seo ?? null">
    @php
        $wa = config('printair.contact_whatsapp', '94768860175'); // no +
        $phone = config('printair.contact_phone', '0768860175');
        $email = config('printair.contact_email', 'contact@printair.lk');
        $address = config('printair.contact_address', 'No. 67/D/2, Uggashena Road, Walpola, Ragama, Sri Lanka');

        // WhatsApp message (URL-encoded)
        $waText = rawurlencode(
            "Hello Printair,\n\nI’m interested in your Partner Program.\n\nBusiness Name:\nType (Print shop/Agency/Reseller/Corporate):\nMonthly print volume:\nLocation:\n\nPlease share the partner pricing / process.\nThank you.",
        );
        $waUrl = "https://wa.me/{$wa}?text={$waText}";
    @endphp

    {{-- HERO --}}
    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-10 sm:pt-14">
            <div
                class="rounded-3xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-7 sm:p-10 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                    <div class="max-w-2xl">
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-red-500"></span>
                            Printair Partner Program
                        </div>

                        <h1 class="mt-5 text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-slate-900">
                            Partner with Printair — unlock better prices & priority production
                        </h1>

                        <p class="mt-4 text-slate-600 text-base sm:text-lg leading-relaxed">
                            Built for print shops, agencies, resellers, and co-operate buyers who print frequently.
                            We handle partnerships directly to ensure fair pricing and smooth collaboration.
                        </p>

                        <div class="mt-6 flex flex-col sm:flex-row gap-3">
                            <a href="{{ $waUrl }}"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#25D366] px-6 py-3 text-white font-semibold shadow-sm hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#25D366]">
                                {{-- WhatsApp icon --}}
                                <svg class="h-5 w-5" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
                                    <path
                                        d="M19.11 17.73c-.27-.14-1.6-.79-1.85-.88-.25-.09-.43-.14-.61.14-.18.27-.7.88-.86 1.06-.16.18-.32.2-.59.07-.27-.14-1.15-.42-2.19-1.34-.81-.72-1.36-1.6-1.52-1.87-.16-.27-.02-.42.12-.56.12-.12.27-.32.41-.48.14-.16.18-.27.27-.45.09-.18.05-.34-.02-.48-.07-.14-.61-1.47-.84-2.02-.22-.53-.44-.46-.61-.47h-.52c-.18 0-.48.07-.73.34-.25.27-.95.93-.95 2.27 0 1.33.98 2.62 1.11 2.8.14.18 1.93 2.95 4.68 4.13.65.28 1.16.45 1.56.58.65.21 1.24.18 1.71.11.52-.08 1.6-.65 1.82-1.28.23-.63.23-1.17.16-1.28-.07-.11-.25-.18-.52-.32z" />
                                    <path
                                        d="M26.67 5.33C23.84 2.5 20.08.94 16.07.94 7.93.94 1.32 7.55 1.32 15.69c0 2.6.68 5.14 1.97 7.38L1.2 31.06l8.19-2.05c2.19 1.19 4.66 1.82 7.2 1.82 8.14 0 14.75-6.61 14.75-14.75 0-4.01-1.56-7.77-4.4-10.75zm-10.6 23.02c-2.33 0-4.61-.62-6.59-1.8l-.47-.28-4.86 1.22 1.3-4.74-.3-.49c-1.26-2.06-1.93-4.44-1.93-6.88C3.22 8.6 9 2.82 16.07 2.82c3.42 0 6.63 1.33 9.05 3.75 2.42 2.42 3.75 5.63 3.75 9.05 0 7.07-5.78 12.73-12.8 12.73z" />
                                </svg>
                                Contact Us on WhatsApp
                            </a>

                            <a href="#benefits"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-3 text-slate-900 font-semibold shadow-sm hover:bg-slate-50">
                                View Partner Benefits
                            </a>
                        </div>

                        <div class="mt-5 text-sm text-slate-500">
                            Prefer a call? <span class="font-semibold text-slate-800">{{ $phone }}</span> •
                            Email:
                            <span class="font-semibold text-slate-800">{{ $email }}</span>
                        </div>
                    </div>

                    {{-- Side card --}}
                    <div class="w-full lg:w-[420px]">
                        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-start gap-3">
                                <div
                                    class="h-11 w-11 rounded-2xl bg-slate-900 text-white flex items-center justify-center">
                                    {{-- handshake-ish icon (simple) --}}
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8 11l3-3a2 2 0 012.8 0l1.2 1.2a2 2 0 010 2.8l-3 3" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M7 12l-2 2a2 2 0 000 2.8l1.2 1.2a2 2 0 002.8 0l2-2" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 14l1 1" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 17l1 1" />
                                    </svg>
                                </div>

                                <div>
                                    <h3 class="font-bold text-slate-900 text-lg">Fast onboarding</h3>
                                    <p class="mt-1 text-slate-600 text-sm leading-relaxed">
                                        We keep it human. Send your details on WhatsApp and we’ll guide you through
                                        partner pricing & ordering flow.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 grid grid-cols-2 gap-3">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-xs text-slate-500">Response</div>
                                    <div class="mt-1 font-bold text-slate-900">Quick</div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-xs text-slate-500">Pricing</div>
                                    <div class="mt-1 font-bold text-slate-900">Partner Rates</div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-xs text-slate-500">Support</div>
                                    <div class="mt-1 font-bold text-slate-900">Direct</div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-xs text-slate-500">Focus</div>
                                    <div class="mt-1 font-bold text-slate-900">B2B</div>
                                </div>
                            </div>

                            <a href="{{ $waUrl }}"
                                class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-red-600 px-5 py-3 text-white font-semibold shadow-sm hover:bg-red-700">
                                Start Partner Chat
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M3 10a1 1 0 011-1h9.586l-2.293-2.293a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L13.586 11H4a1 1 0 01-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- WHO IS THIS FOR --}}
    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-10 sm:pt-12">
            <div class="flex items-end justify-between gap-6">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">Who this is for</h2>
                    <p class="mt-2 text-slate-600">If you print frequently or in bulk, this program is built for you.
                    </p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ([['title' => 'Print Shops', 'desc' => 'Sub-printing, daily orders, production partners'], ['title' => 'Agencies', 'desc' => 'Campaign work, events, recurring client needs'], ['title' => 'Resellers', 'desc' => 'Sell print products with your own margins'], ['title' => 'Co-operate', 'desc' => 'Bulk signage, promotions, brand materials']] as $item)
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition">
                        <div class="h-10 w-10 rounded-2xl bg-slate-900 text-white flex items-center justify-center">
                            <span class="text-sm font-bold">P</span>
                        </div>
                        <div class="mt-4 font-bold text-slate-900">{{ $item['title'] }}</div>
                        <div class="mt-1 text-sm text-slate-600 leading-relaxed">{{ $item['desc'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- BENEFITS --}}
    <section id="benefits" class="bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-10 sm:pt-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">Why partner with Printair</h2>
            <p class="mt-2 text-slate-600">Simple promise: better prices, faster coordination, consistent quality.</p>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ([['t' => 'Wholesale & special pricing', 'd' => 'Partner-friendly rates for frequent/bulk printing.'], ['t' => 'Priority production support', 'd' => 'Smoother timelines for partner jobs whenever possible.'], ['t' => 'Consistent quality', 'd' => 'Reliable materials + controlled finishing standards.'], ['t' => 'Direct communication', 'd' => 'Talk to a real person, not a ticket queue.'], ['t' => 'Long-term collaboration', 'd' => 'We grow with you — not one-off deals.'], ['t' => 'Better ordering flow', 'd' => 'Guidance for formats, specs, and finishing options.']] as $b)
                    <div
                        class="rounded-3xl border border-slate-200 bg-slate-50/40 p-6 shadow-inner shadow-slate-200/40">
                        <div class="flex items-start gap-3">
                            <div
                                class="h-10 w-10 rounded-2xl bg-red-600 text-white flex items-center justify-center shadow-sm">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-7.5 7.5a1 1 0 01-1.414 0l-3.5-3.5a1 1 0 011.414-1.414l2.793 2.793 6.793-6.793a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-slate-900">{{ $b['t'] }}</div>
                                <div class="mt-1 text-sm text-slate-600 leading-relaxed">{{ $b['d'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- HOW TO START --}}
    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-10 sm:pt-12 pb-24">
            <div class="rounded-3xl border border-slate-200 bg-white p-7 sm:p-10 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                    <div>
                        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">How to get started</h2>
                        <p class="mt-2 text-slate-600">
                            We keep onboarding simple: contact us on WhatsApp and we’ll take it from there.
                        </p>

                        <ol class="mt-6 space-y-4">
                            @foreach (['Click the WhatsApp button and share your business details.', 'Tell us what products you print and your estimated monthly volume.', 'We’ll discuss partner pricing, workflow, and next steps.'] as $step)
                                <li class="flex items-start gap-3">
                                    <span
                                        class="mt-0.5 inline-flex h-7 w-7 items-center justify-center rounded-xl bg-slate-900 text-white text-sm font-bold">
                                        {{ $loop->iteration }}
                                    </span>
                                    <span class="text-slate-700 leading-relaxed">{{ $step }}</span>
                                </li>
                            @endforeach
                        </ol>

                        <div class="mt-7 flex flex-col sm:flex-row gap-3">
                            <a href="{{ $waUrl }}"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#25D366] px-6 py-3 text-white font-semibold shadow-sm hover:opacity-95">
                                Contact Us on WhatsApp
                            </a>
                            <a href="{{ route('contact') ?? '#contact' }}"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-3 text-slate-900 font-semibold shadow-sm hover:bg-slate-50">
                                View Contact Details
                            </a>
                        </div>

                        <div class="mt-4 text-sm text-slate-500">
                            Address: <span class="text-slate-800 font-semibold">{{ $address }}</span>
                        </div>
                    </div>

                    <div class="w-full lg:w-[420px]">
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                            <div class="text-sm text-slate-600">Suggested message</div>
                            <div
                                class="mt-3 rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-700 whitespace-pre-line">
                                Hello Printair,

                                I’m interested in your Partner Program.

                                Business Name:
                                Type (Print shop/Agency/Reseller/Corporate):
                                Monthly print volume:
                                Location:

                                Please share the partner pricing / process.
                                Thank you.
                            </div>

                            <a href="{{ $waUrl }}"
                                class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-red-600 px-5 py-3 text-white font-semibold hover:bg-red-700">
                                Open WhatsApp Chat
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sticky WhatsApp CTA (Mobile-friendly) --}}
        <div class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 w-[92%] max-w-lg">
            <div class="rounded-3xl border border-slate-200 bg-white/95 backdrop-blur p-3 shadow-lg">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-sm font-bold text-slate-900 truncate">Want partner pricing?</div>
                        <div class="text-xs text-slate-600 truncate">Chat with Printair on WhatsApp.</div>
                    </div>
                    <a href="{{ $waUrl }}"
                        class="shrink-0 inline-flex items-center justify-center rounded-2xl bg-[#25D366] px-4 py-2 text-white font-semibold text-sm hover:opacity-95">
                        WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-home-layout>
