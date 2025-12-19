<x-home-layout :seo="$seo">
    <section class="bg-gradient-to-b from-slate-50 via-white to-slate-50">
        {{-- HERO --}}
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-50 via-white to-slate-100 p-7 sm:p-10 shadow-lg"
                x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">
                <div class="relative z-10 grid grid-cols-1 gap-8 lg:grid-cols-12 lg:items-center">
                    <div class="lg:col-span-7" x-show="loaded" x-transition:enter="transition duration-700 delay-100"
                        x-transition:enter-start="opacity-0 -translate-x-8"
                        x-transition:enter-end="opacity-100 translate-x-0">
                        <div
                            class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-red-600 to-red-700 px-4 py-1.5 text-xs font-semibold tracking-wide text-white shadow-lg shadow-red-500/30">
                            <iconify-icon icon="mdi:information-outline" class="text-sm"></iconify-icon>
                            ABOUT PRINTAIR
                        </div>

                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl lg:text-5xl leading-tight">
                            Built for brands that care about
                            <span class="bg-gradient-to-r from-red-600 via-red-700 to-slate-900 bg-clip-text text-transparent">
                                quality.
                            </span>
                        </h1>

                        <p class="mt-4 text-base text-slate-600 sm:text-lg leading-relaxed">
                            Printair Advertising is a modern design & printing partner focused on consistency, accuracy,
                            and speed — from one-off prints to long-term corporate supply. We combine creative design,
                            production-grade workflows, and smart pricing (including Working Group pricing) to keep you
                            moving fast.
                        </p>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <div
                                class="group rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                                <div class="flex items-center gap-2">
                                    <iconify-icon icon="mdi:palette-outline"
                                        class="text-xl text-slate-700 group-hover:text-slate-900 transition-colors"></iconify-icon>
                                    <div>
                                        <p class="text-xs font-semibold text-slate-500">Focus</p>
                                        <p class="mt-0.5 text-sm font-extrabold text-slate-900">Design + Print Execution
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="group rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                                <div class="flex items-center gap-2">
                                    <iconify-icon icon="mdi:quality-high"
                                        class="text-xl text-slate-700 group-hover:text-slate-900 transition-colors"></iconify-icon>
                                    <div>
                                        <p class="text-xs font-semibold text-slate-500">Strength</p>
                                        <p class="mt-0.5 text-sm font-extrabold text-slate-900">Quality & Consistency</p>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="group rounded-2xl border border-red-200 bg-gradient-to-br from-white to-red-50 px-5 py-4 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1 hover:border-red-400">
                                <div class="flex items-center gap-2">
                                    <iconify-icon icon="mdi:chart-line"
                                        class="text-xl text-red-600 group-hover:text-red-700 transition-colors"></iconify-icon>
                                    <div>
                                        <p class="text-xs font-semibold text-red-600">Model</p>
                                        <p class="mt-0.5 text-sm font-extrabold text-slate-900">Public + WG Pricing</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-5" x-show="loaded" x-transition:enter="transition duration-700 delay-300"
                        x-transition:enter-start="opacity-0 translate-x-8"
                        x-transition:enter-end="opacity-100 translate-x-0">
                        <div
                            class="rounded-3xl border border-slate-200 bg-white/80 backdrop-blur-sm p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="rounded-full bg-slate-900 p-2">
                                    <iconify-icon icon="mdi:check-all" class="text-lg text-white"></iconify-icon>
                                </div>
                                <p class="text-base font-extrabold text-slate-900">What we do</p>
                            </div>
                            <ul class="space-y-4 text-sm text-slate-600">
                                <li class="flex gap-3 group">
                                    <div
                                        class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-green-100 text-green-600 group-hover:bg-green-500 group-hover:text-white transition-colors">
                                        <iconify-icon icon="mdi:check" class="text-sm"></iconify-icon>
                                    </div>
                                    <span class="leading-relaxed"><span
                                            class="font-semibold text-slate-900">Print products</span> for events,
                                        retail, corporate, and everyday needs</span>
                                </li>
                                <li class="flex gap-3 group">
                                    <div
                                        class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                                        <iconify-icon icon="mdi:check" class="text-sm"></iconify-icon>
                                    </div>
                                    <span class="leading-relaxed"><span
                                            class="font-semibold text-slate-900">Design services</span> with
                                        production-aware layouts and finishing-ready files</span>
                                </li>
                                <li class="flex gap-3 group">
                                    <div
                                        class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-purple-100 text-purple-600 group-hover:bg-purple-500 group-hover:text-white transition-colors">
                                        <iconify-icon icon="mdi:check" class="text-sm"></iconify-icon>
                                    </div>
                                    <span class="leading-relaxed"><span
                                            class="font-semibold text-slate-900">Reliable finishing</span> (eyelets,
                                        pockets, laminations, mounting, etc.)</span>
                                </li>
                                <li class="flex gap-3 group">
                                    <div
                                        class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 group-hover:bg-red-500 group-hover:text-white transition-colors">
                                        <iconify-icon icon="mdi:check" class="text-sm"></iconify-icon>
                                    </div>
                                    <span class="leading-relaxed"><span
                                            class="font-semibold text-slate-900">Partner pricing</span> via Working
                                        Groups for ongoing supply</span>
                                </li>
                            </ul>

                            <div
                                class="mt-6 rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-4">
                                <div class="flex items-center gap-2">
                                    <iconify-icon icon="mdi:calendar-clock" class="text-lg text-slate-600"></iconify-icon>
                                    <div>
                                        <p class="text-xs font-semibold text-slate-500">Last updated</p>
                                        <p class="mt-0.5 text-sm font-bold text-slate-900">{{ now()->format('F d, Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Decorative blobs --}}
                <div
                    class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-gradient-to-br from-slate-200/40 to-slate-300/40 blur-3xl animate-pulse">
                </div>
                <div
                    class="pointer-events-none absolute -left-24 -bottom-24 h-72 w-72 rounded-full bg-gradient-to-tr from-slate-200/40 to-slate-300/40 blur-3xl animate-pulse"
                    style="animation-delay: 1s;">
                </div>
                <div
                    class="pointer-events-none absolute right-1/3 top-1/3 h-48 w-48 rounded-full bg-slate-100/30 blur-2xl">
                </div>
            </div>
        </div>

        {{-- STATS SECTION --}}
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-red-900/20 bg-gradient-to-br from-slate-900 via-red-950 to-slate-900 p-8 sm:p-12 shadow-xl shadow-red-900/10">
                <div class="text-center mb-10">
                    <h2 class="text-2xl font-black text-white sm:text-3xl">Numbers that speak</h2>
                    <p class="mt-2 text-slate-300">Our journey in numbers</p>
                </div>
                <div class="grid grid-cols-2 gap-6 lg:grid-cols-4">
                    <div class="text-center">
                        <div class="mb-2">
                            <iconify-icon icon="mdi:calendar-check" class="text-4xl text-red-400"></iconify-icon>
                        </div>
                        <p class="text-3xl font-black text-white sm:text-4xl">7+</p>
                        <p class="mt-1 text-sm font-medium text-slate-300">Years in Business</p>
                    </div>
                    <div class="text-center">
                        <div class="mb-2">
                            <iconify-icon icon="mdi:account-group" class="text-4xl text-white"></iconify-icon>
                        </div>
                        <p class="text-3xl font-black text-white sm:text-4xl">500+</p>
                        <p class="mt-1 text-sm font-medium text-slate-300">Happy Clients</p>
                    </div>
                    <div class="text-center">
                        <div class="mb-2">
                            <iconify-icon icon="mdi:package-variant" class="text-4xl text-white"></iconify-icon>
                        </div>
                        <p class="text-3xl font-black text-white sm:text-4xl">10K+</p>
                        <p class="mt-1 text-sm font-medium text-slate-300">Projects Delivered</p>
                    </div>
                    <div class="text-center">
                        <div class="mb-2">
                            <iconify-icon icon="mdi:star" class="text-4xl text-white"></iconify-icon>
                        </div>
                        <p class="text-3xl font-black text-white sm:text-4xl">100%</p>
                        <p class="mt-1 text-sm font-medium text-slate-300">Quality Focus</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- VALUES --}}
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <p class="text-xs font-semibold tracking-wide text-slate-500">OUR VALUES</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">What drives us forward</h2>
            </div>
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                <div class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-xl hover:border-red-200 transition-all duration-300 hover:-translate-y-2">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-red-100 text-red-600 group-hover:bg-red-600 group-hover:text-white transition-colors duration-300">
                        <iconify-icon icon="mdi:diamond-stone" class="text-2xl"></iconify-icon>
                    </div>
                    <p class="text-base font-extrabold text-slate-900">Quality-first</p>
                    <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                        We build outputs to match expectations — with production-aware checks and practical tolerances.
                    </p>
                </div>

                <div class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-green-100 text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors duration-300">
                        <iconify-icon icon="mdi:lightning-bolt" class="text-2xl"></iconify-icon>
                    </div>
                    <p class="text-base font-extrabold text-slate-900">Fast + transparent</p>
                    <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                        Clear timelines, real pricing logic, and less back-and-forth — so your team can move quicker.
                    </p>
                </div>

                <div class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-100 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300">
                        <iconify-icon icon="mdi:handshake" class="text-2xl"></iconify-icon>
                    </div>
                    <p class="text-base font-extrabold text-slate-900">Partner mindset</p>
                    <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                        Working Groups unlock stable supply and better rates for teams that print regularly.
                    </p>
                </div>
            </div>
        </div>

        {{-- MILESTONE SHOWER --}}
        <div class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8" x-data="milestoneShower()" x-init="init()">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-1.5 text-xs font-semibold tracking-wide text-slate-700">
                        <iconify-icon icon="mdi:timeline-clock" class="text-sm"></iconify-icon>
                        OUR JOURNEY
                    </div>
                    <h2 class="mt-4 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                        Milestones that shaped Printair
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-600 sm:text-base">
                        A quick timeline of the moments that upgraded our quality, speed, and partner experience.
                    </p>
                </div>

                {{-- Controls --}}
                <div class="flex items-center gap-2">
                    <button @click="prev()"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-900 transition-all duration-200 flex items-center gap-2">
                        <iconify-icon icon="mdi:chevron-left" class="text-lg"></iconify-icon>
                        <span>Prev</span>
                    </button>
                    <button @click="next()"
                        class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition-all duration-200 flex items-center gap-2">
                        <span>Next</span>
                        <iconify-icon icon="mdi:chevron-right" class="text-lg"></iconify-icon>
                    </button>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
                {{-- Active milestone card --}}
                <div class="lg:col-span-5">
                    <div class="rounded-3xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-8 shadow-lg hover:shadow-xl transition-shadow duration-300"
                        x-transition:enter="transition duration-300"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="rounded-2xl bg-gradient-to-r from-red-600 to-slate-900 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-red-500/30"
                                    x-text="active.year"></div>
                                <iconify-icon icon="mdi:flag-checkered" class="text-2xl text-red-400"></iconify-icon>
                            </div>

                            <div class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"
                                x-text="(activeIndex+1) + ' / ' + milestones.length"></div>
                        </div>

                        <h3 class="mt-6 text-xl font-extrabold text-slate-900 leading-tight" x-text="active.title"></h3>
                        <p class="mt-3 text-sm text-slate-600 leading-relaxed" x-text="active.description"></p>

                        <div class="mt-6 rounded-2xl border-2 border-red-200 bg-gradient-to-br from-white to-red-50 p-5">
                            <div class="flex items-center gap-2 mb-2">
                                <iconify-icon icon="mdi:chart-timeline-variant" class="text-lg text-red-600"></iconify-icon>
                                <p class="text-xs font-semibold text-red-600">Impact</p>
                            </div>
                            <p class="text-sm font-bold text-slate-900" x-text="active.impact"></p>
                        </div>
                    </div>
                </div>

                {{-- Timeline list --}}
                <div class="lg:col-span-7">
                    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-lg">
                        <div class="max-h-[480px] overflow-auto p-2 custom-scrollbar">
                            <template x-for="(m, idx) in milestones" :key="idx">
                                <button @click="setActive(idx)" class="group w-full text-left">
                                    <div class="mb-3 rounded-2xl border px-6 py-5 transition-all duration-300
                                        "
                                        :class="idx === activeIndex ?
                                            'border-slate-900 bg-slate-900 text-white shadow-lg scale-105' :
                                            'border-slate-200 bg-white hover:bg-slate-50 text-slate-900 hover:border-slate-300 hover:shadow-md'">

                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-3">
                                                <div class="rounded-xl px-3 py-1.5 text-xs font-bold transition-colors"
                                                    :class="idx === activeIndex ? 'bg-white/15 text-white' :
                                                        'bg-slate-100 text-slate-700 group-hover:bg-slate-200'"
                                                    x-text="m.year"></div>

                                                <p class="text-sm font-extrabold" x-text="m.title"></p>
                                            </div>

                                            <div class="flex items-center gap-1 text-xs font-semibold opacity-70">
                                                <span>View</span>
                                                <iconify-icon icon="mdi:chevron-right" class="text-base"></iconify-icon>
                                            </div>
                                        </div>

                                        <p class="mt-2 text-sm opacity-90 leading-relaxed" x-text="m.short"></p>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            
            <style>
                .custom-scrollbar::-webkit-scrollbar {
                    width: 6px;
                }
                .custom-scrollbar::-webkit-scrollbar-track {
                    background: #f1f5f9;
                    border-radius: 10px;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb {
                    background: #cbd5e1;
                    border-radius: 10px;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                    background: #94a3b8;
                }
            </style>

            <script>
                function milestoneShower() {
                    return {
                        activeIndex: 0,
                        milestones: [{
                                year: '2018',
                                title: 'Started with a simple promise',
                                short: 'Deliver better print quality without drama.',
                                description: 'We began focusing on practical, reliable production with attention to finishing and real-world usage.',
                                impact: 'Built the foundation for quality-first workflows.'
                            },
                            {
                                year: '2020',
                                title: 'Expanded product range',
                                short: 'More materials, more finishing options.',
                                description: 'We widened our lineup to support events, retail branding, and large-format needs with consistent output.',
                                impact: 'Customers could solve more needs in one place.'
                            },
                            {
                                year: '2023',
                                title: 'Upgraded internal production workflow',
                                short: 'Less back-and-forth, faster turnaround.',
                                description: 'We tightened file checks, proof flows, and production handoff so jobs move smoothly from quote to print.',
                                impact: 'Reduced delays and improved reliability.'
                            },
                            {
                                year: '2025',
                                title: 'Introduced Working Group pricing',
                                short: 'Corporate partners get smarter pricing + access.',
                                description: 'Working Groups allow pricing and availability to adapt for long-term partner accounts—without breaking public pricing.',
                                impact: 'Better partner experience and scalable pricing logic.'
                            },
                        ],

                        get active() {
                            return this.milestones[this.activeIndex] || this.milestones[0];
                        },

                        init() {
                            // Optional: start from latest milestone
                            this.activeIndex = Math.max(0, this.milestones.length - 1);
                        },

                        setActive(i) {
                            this.activeIndex = i;
                        },

                        next() {
                            this.activeIndex = (this.activeIndex + 1) % this.milestones.length;
                        },

                        prev() {
                            this.activeIndex = (this.activeIndex - 1 + this.milestones.length) % this.milestones.length;
                        },
                    }
                }
            </script>
        </div>

        {{-- CTA --}}
        <div class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl border border-red-900/20 bg-gradient-to-br from-slate-900 via-red-950 to-slate-900 p-8 sm:p-12 shadow-2xl shadow-red-900/20">
                {{-- Decorative elements --}}
                <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
                <div class="pointer-events-none absolute -left-16 -bottom-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
                
                <div class="relative z-10 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex-1">
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold text-white mb-4">
                            <iconify-icon icon="mdi:handshake" class="text-sm"></iconify-icon>
                            PARTNERSHIP OPPORTUNITY
                        </div>
                        <h3 class="text-2xl font-black tracking-tight text-white sm:text-3xl">
                            Want to partner with Printair?
                        </h3>
                        <p class="mt-3 text-base text-slate-300">
                            If you print regularly, Working Groups can unlock better rates and smoother ordering.
                        </p>
                        <div class="mt-4 flex items-center flex-wrap gap-4">
                            <div class="flex items-center gap-2">
                                <iconify-icon icon="mdi:check-circle" class="text-lg text-green-400"></iconify-icon>
                                <span class="text-sm text-slate-300">Better pricing</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <iconify-icon icon="mdi:check-circle" class="text-lg text-green-400"></iconify-icon>
                                <span class="text-sm text-slate-300">Priority support</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <iconify-icon icon="mdi:check-circle" class="text-lg text-green-400"></iconify-icon>
                                <span class="text-sm text-slate-300">Faster delivery</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('contact') ?? '#' }}"
                            class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-red-600 to-red-700 px-6 py-4 text-sm font-semibold text-white hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-lg shadow-red-500/30 hover:shadow-xl hover:shadow-red-500/40">
                            <iconify-icon icon="mdi:message" class="text-lg"></iconify-icon>
                            <span>Contact Us</span>
                            <iconify-icon icon="mdi:arrow-right" class="text-lg group-hover:translate-x-1 transition-transform"></iconify-icon>
                        </a>
                        <a href="{{ route('terms.conditions') ?? '#' }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-white/20 bg-white/5 px-6 py-4 text-sm font-semibold text-white hover:bg-white/10 backdrop-blur-sm transition-all duration-200">
                            <iconify-icon icon="mdi:file-document" class="text-lg"></iconify-icon>
                            <span>Terms &amp; Conditions</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </section>
</x-home-layout>
