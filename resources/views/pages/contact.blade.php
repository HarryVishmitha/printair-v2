<x-home-layout :seo="$seo">
    @php
        $wa = config('printair.contact_whatsapp', '94768860175');
        $phone = config('printair.contact_phone', '0768860175');
        $email = config('printair.contact_email', 'contact@printair.lk');
        $address = config('printair.contact_address', 'No. 67/D/2, Uggashena Road, Walpola, Ragama, Sri Lanka');
        $mapEmbed = config(
            'printair.contact_map_embed_url',
            'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d416.2046565273029!2d79.92673769212446!3d7.052887018883192!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae2f9e69a99f8bb%3A0x22ee736733e2cc74!2sPrintair!5e0!3m2!1sen!2slk!4v1766143081736!5m2!1sen!2slk',
        );

        $waLink = $wa ? 'https://wa.me/' . preg_replace('/\\D+/', '', $wa) : null;

        // Input style (Printair light theme)
        $inputBase = 'mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 outline-none transition
                      focus:border-red-600 focus:ring-2 focus:ring-red-600/20';
    @endphp

    <section class="bg-white">
        {{-- TOP INTRO --}}
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm sm:p-10">
                <p class="text-xs font-semibold tracking-wide text-red-600">CONTACT</p>

                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                    Contact Printair
                </h1>

                <div class="mt-3 h-1 w-16 rounded-full bg-red-600"></div>

                <p class="mt-4 max-w-3xl text-sm text-slate-600 sm:text-base">
                    Need a quotation, corporate partner pricing (Working Group), or help with a print job?
                    Send us a message — we’ll get back to you quickly.
                </p>

                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => (show = false), 4500)" x-transition.opacity
                        class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-900">
                        <p class="text-sm font-bold">✅ {{ session('success') }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- MAIN GRID --}}
        <div class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">

                {{-- LEFT SIDE --}}
                <div class="space-y-6 lg:col-span-5">

                    {{-- MAP CARD --}}
                    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-200 px-6 py-5">
                            <p class="text-sm font-extrabold text-slate-900">Find us</p>
                            <p class="mt-1 text-xs text-slate-500">Visit our location or send your courier here.</p>
                        </div>

                        <div class="aspect-[16/12] bg-slate-100">
                            @if ($mapEmbed)
                                <iframe src="{{ $mapEmbed }}" class="h-full w-full" style="border:0;"
                                    allowfullscreen="" loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"></iframe>
                            @else
                                <div
                                    class="flex h-full items-center justify-center p-6 text-center text-sm text-slate-600">
                                    Add
                                    <code
                                        class="mx-1 rounded bg-slate-200 px-2 py-1 text-xs">CONTACT_MAP_EMBED_URL</code>
                                    in
                                    <code class="rounded bg-slate-200 px-2 py-1 text-xs">.env</code>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- CONTACT INFO CARDS --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-1">

                        {{-- Email --}}
                        <div
                            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-red-300">
                            <p class="text-xs font-semibold tracking-wide text-red-600">EMAIL</p>
                            <p class="mt-2 text-sm font-extrabold text-slate-900">{{ $email }}</p>
                            <p class="mt-1 text-xs text-slate-500">Best for quotations, invoices, and detailed
                                requirements.</p>
                        </div>

                        {{-- Phone --}}
                        <div
                            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-red-300">
                            <p class="text-xs font-semibold tracking-wide text-red-600">PHONE</p>
                            <p class="mt-2 text-sm font-extrabold text-slate-900">
                                {{ $phone ?: '—' }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">For quick coordination or urgent updates.</p>
                        </div>

                        {{-- Address + WhatsApp --}}
                        <div
                            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-red-300 sm:col-span-2 lg:col-span-1">
                            <p class="text-xs font-semibold tracking-wide text-red-600">ADDRESS</p>
                            <p class="mt-2 text-sm font-extrabold text-slate-900">
                                {{ $address ?: '—' }}
                            </p>

                            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-1">
                                @if ($waLink)
                                    <a href="{{ $waLink }}" target="_blank" rel="noopener"
                                        class="inline-flex w-full items-center justify-center rounded-2xl border-2 border-red-600 px-5 py-3 text-sm font-semibold text-red-600 transition
                                              hover:bg-red-600 hover:text-white">
                                        WhatsApp Us
                                    </a>
                                    <p class="text-center text-xs text-slate-500 sm:col-span-1 lg:col-span-1">
                                        Fastest way to ask a quick question.
                                    </p>
                                @else
                                    <p class="text-xs text-slate-500">
                                        Add <code class="rounded bg-slate-100 px-2 py-0.5">CONTACT_WHATSAPP</code> in
                                        <code class="rounded bg-slate-100 px-2 py-0.5">.env</code>
                                    </p>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                {{-- RIGHT SIDE --}}
                <div class="lg:col-span-7">
                    <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm sm:p-10">
                        <div>
                            <p class="text-xs font-semibold tracking-wide text-red-600">SEND A MESSAGE</p>
                            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">
                                Tell us what you need
                            </h2>
                            <div class="mt-3 h-1 w-12 rounded-full bg-red-600"></div>

                            <p class="mt-4 text-sm text-slate-600">
                                Share product details, sizes, quantities, and deadlines — we’ll reply with the next
                                steps.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('contact.send') }}" class="mt-8 space-y-5">
                            @csrf

                            {{-- Honeypot --}}
                            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">

                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="text-xs font-semibold text-slate-700">Full name</label>
                                    <input name="name" value="{{ old('name') }}" class="{{ $inputBase }}"
                                        placeholder="Your name">
                                    @error('name')
                                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="text-xs font-semibold text-slate-700">Email</label>
                                    <input type="email" name="email" value="{{ old('email') }}"
                                        class="{{ $inputBase }}" placeholder="you@email.com">
                                    @error('email')
                                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="text-xs font-semibold text-slate-700">Phone / WhatsApp
                                        (optional)</label>
                                    <input name="phone" value="{{ old('phone') }}" class="{{ $inputBase }}"
                                        placeholder="+94...">
                                    @error('phone')
                                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="text-xs font-semibold text-slate-700">Subject</label>
                                    <input name="subject" value="{{ old('subject') }}" class="{{ $inputBase }}"
                                        placeholder="Quotation / Corporate partner / Support">
                                    @error('subject')
                                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-slate-700">Message</label>
                                <textarea name="message" rows="7" class="{{ $inputBase }} resize-none"
                                    placeholder="Tell us product type, size, quantity, finishing, deadline...">{{ old('message') }}</textarea>
                                @error('message')
                                    <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-xs text-slate-500">
                                    By submitting, you agree to our
                                    <a href="{{ route('privacy') }}"
                                        class="font-semibold text-slate-900 underline underline-offset-2 hover:text-red-600">
                                        Privacy Policy
                                    </a>.
                                </p>

                                <button type="submit"
                                    class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition
                                           hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20">
                                    Send message
                                </button>
                            </div>
                        </form>

                        {{-- Pro tip --}}
                        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4">
                            <p class="text-xs font-semibold text-red-700">Pro tip</p>
                            <p class="mt-1 text-sm text-slate-700">
                                If you need <span class="font-semibold text-red-600">Working Group pricing</span>,
                                mention your company name and monthly print volume.
                            </p>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>
</x-home-layout>
