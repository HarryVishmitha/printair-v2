<x-app-layout>
    @php
        /** @var \App\Models\User|null $user */
        $user = Auth::user(); // you prefer Auth::user()
        $isStaff = $user?->isStaff() ?? false;
    @endphp

    <x-slot name="sectionTitle">{{ $isStaff ? 'Operations' : 'Account' }}</x-slot>
    <x-slot name="pageTitle">{{ $isStaff ? 'Control Panel' : 'User Dashboard' }}</x-slot>

    <x-slot name="breadcrumbs">
        <span class="text-slate-500">{{ $isStaff ? 'Admin' : 'Portal' }}</span>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-slate-900 font-medium">Dashboard</span>
    </x-slot>

    {{-- ===========================================
        USER DASHBOARD (polished portal)
    ============================================ --}}
    @if (!$isStaff)
        <div x-data="userDashboard({
            url: '{{ route('portal.dashboard.data') }}',
            whatsapp: '94768860175',
            homeUrl: '{{ route('home') }}'
        })" x-init="init()" class="space-y-6">

            {{-- Top Welcome / Profile Card --}}
            <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 text-white shadow-lg">
                            <iconify-icon icon="mdi:account-circle" class="text-3xl"></iconify-icon>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-400 flex items-center gap-2">
                                <iconify-icon icon="mdi:account" class="text-sm"></iconify-icon>
                                Welcome
                            </div>
                            <div class="mt-1 text-lg font-extrabold text-slate-900">
                                {{ $user?->first_name ?? 'User' }} {{ $user?->last_name ?? '' }}
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-bold text-slate-700">
                                    <iconify-icon icon="mdi:office-building" class="text-sm"></iconify-icon>
                                    {{ $user?->workingGroup?->name ?? 'Public' }}
                                </span>
                                @if ($user?->workingGroup?->slug)
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold text-slate-500">
                                        WG: {{ $user->workingGroup->slug }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-2 text-xs text-slate-500">
                                <span x-text="meta.lastUpdatedLabel"></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a :href="homeUrl"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                            <iconify-icon icon="mdi:arrow-left" class="text-sm"></iconify-icon>
                            Back to website
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition-colors">
                                <iconify-icon icon="mdi:logout" class="text-sm"></iconify-icon>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>

                {{-- User KPIs --}}
                <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                    <template x-if="loading">
                        <template x-for="i in 4" :key="i">
                            <div class="h-24 rounded-2xl border border-slate-200 bg-white p-4 animate-pulse"></div>
                        </template>
                    </template>

                    <template x-if="!loading">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between">
                                <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">My Orders
                                </div>
                                <iconify-icon icon="mdi:cart" class="text-xl text-cyan-600 opacity-80"></iconify-icon>
                            </div>
                            <div class="mt-2 text-2xl font-extrabold text-slate-900" x-text="kpis.orders_total"></div>
                            <div class="mt-1 text-xs text-slate-500">All time</div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between">
                                <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Active</div>
                                <iconify-icon icon="mdi:progress-clock"
                                    class="text-xl text-amber-600 opacity-80"></iconify-icon>
                            </div>
                            <div class="mt-2 text-2xl font-extrabold text-slate-900" x-text="kpis.orders_active"></div>
                            <div class="mt-1 text-xs text-slate-500">In production / delivery</div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between">
                                <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">My Quotes
                                </div>
                                <iconify-icon icon="mdi:file-document-edit"
                                    class="text-xl text-purple-600 opacity-80"></iconify-icon>
                            </div>
                            <div class="mt-2 text-2xl font-extrabold text-slate-900" x-text="kpis.quotes_total"></div>
                            <div class="mt-1 text-xs text-slate-500">Draft / sent / accepted</div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between">
                                <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Outstanding
                                </div>
                                <iconify-icon icon="mdi:receipt-text-clock"
                                    class="text-xl text-rose-600 opacity-80"></iconify-icon>
                            </div>
                            <div class="mt-2 text-2xl font-extrabold text-slate-900" x-text="kpis.outstanding_label">
                            </div>
                            <div class="mt-1 text-xs text-slate-500">Invoices due</div>
                        </div>
                    </template>
                </div>

                {{-- Quick help cards --}}
                <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div class="text-sm font-bold text-slate-900">Artwork & Files</div>
                        <p class="mt-2 text-xs leading-relaxed text-slate-600">
                            Upload designs, approve proofs, and track revisions (coming soon).
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div class="text-sm font-bold text-slate-900">Order via website</div>
                        <p class="mt-2 text-xs leading-relaxed text-slate-600">
                            For now, place orders as a visitor on the website — your working group prices still apply.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div class="text-sm font-bold text-slate-900">Need help?</div>
                        <p class="mt-2 text-xs leading-relaxed text-slate-600">
                            Contact admins via WhatsApp for quick support.
                        </p>
                        <a :href="waLink" target="_blank" rel="noopener"
                            class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-extrabold text-white hover:bg-emerald-700 transition-colors">
                            <iconify-icon icon="mdi:whatsapp" class="text-base"></iconify-icon>
                            WhatsApp Support
                        </a>
                    </div>
                </div>
            </div>

            {{-- Recent Lists --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <iconify-icon icon="mdi:package-variant-closed-check"
                                class="text-xl text-cyan-600"></iconify-icon>
                            <div>
                                <div class="text-sm font-extrabold text-slate-900">My recent orders</div>
                                <div class="mt-0.5 text-xs text-slate-500">Latest updates</div>
                            </div>
                        </div>
                        <a href="{{ route('home') }}"
                            class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 hover:text-slate-900 transition-colors">
                            View site
                            <iconify-icon icon="mdi:arrow-right" class="text-sm"></iconify-icon>
                        </a>
                    </div>

                    <div class="mt-4 divide-y divide-slate-100">
                        <template x-if="loading">
                            <div class="space-y-3">
                                <div class="h-12 rounded-xl border border-slate-200 bg-white animate-pulse"></div>
                                <div class="h-12 rounded-xl border border-slate-200 bg-white animate-pulse"></div>
                                <div class="h-12 rounded-xl border border-slate-200 bg-white animate-pulse"></div>
                            </div>
                        </template>

                        <template x-if="!loading && recent.orders.length === 0">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-600">
                                No orders yet.
                            </div>
                        </template>

                        <template x-for="o in recent.orders" :key="o.id">
                            <div
                                class="group flex items-center justify-between gap-4 py-3 hover:bg-slate-50 -mx-2 px-2 rounded-lg transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div
                                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-50 text-cyan-600 flex-shrink-0">
                                        <iconify-icon icon="mdi:cart" class="text-lg"></iconify-icon>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-extrabold text-slate-900 truncate"
                                            x-text="o.order_no"></div>
                                        <div class="mt-0.5 text-[11px] text-slate-500 truncate" x-text="o.subtitle">
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-xs font-bold text-slate-900" x-text="o.total_label"></div>
                                    <div class="mt-0.5 text-[11px] text-slate-500" x-text="o.created_at"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <iconify-icon icon="mdi:file-document-edit"
                                class="text-xl text-purple-600"></iconify-icon>
                            <div>
                                <div class="text-sm font-extrabold text-slate-900">My quotes</div>
                                <div class="mt-0.5 text-xs text-slate-500">Drafts & sent</div>
                            </div>
                        </div>
                        <div class="text-xs text-slate-500">Coming soon</div>
                    </div>

                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-600">
                        We’ll enable quote saving + downloads next. Your working group pricing system is already wired.
                    </div>
                </div>
            </div>

            @push('scripts')
                <script>
                    window.userDashboard = (opts) => ({
                        url: opts.url,
                        homeUrl: opts.homeUrl,
                        waLink: `https://wa.me/${opts.whatsapp}`,

                        loading: true,
                        kpis: {
                            orders_total: '—',
                            orders_active: '—',
                            quotes_total: '—',
                            outstanding_label: '—',
                        },
                        recent: {
                            orders: []
                        },
                        meta: {
                            lastUpdatedLabel: 'Last updated: —'
                        },

                        init() {
                            this.fetchData();
                        },

                        async fetchData() {
                            this.loading = true;
                            try {
                                const res = await window.axios.get(this.url, {
                                    headers: {
                                        'Accept': 'application/json'
                                    }
                                });
                                const payload = res?.data || {};
                                this.kpis = payload.kpis || this.kpis;
                                this.recent = payload.recent || this.recent;
                                this.meta.lastUpdatedLabel = payload?.meta?.last_updated_label ||
                                    `Last updated: ${new Date().toLocaleString()}`;
                            } catch (e) {
                                // user dashboard can fail gracefully
                                this.meta.lastUpdatedLabel = 'Last updated: unavailable (offline or server error)';
                            } finally {
                                this.loading = false;
                            }
                        },
                    });
                </script>
            @endpush

        </div>

        {{-- ===========================================
        STAFF DASHBOARD (enterprise operations)
    ============================================ --}}
    @else
        <div x-data="staffDashboardPlus({ url: '{{ route('admin.dashboard.data') }}' })" x-init="init()" class="space-y-6">

            {{-- Header actions row --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <iconify-icon icon="mdi:view-dashboard" class="text-xl text-slate-700"></iconify-icon>
                        Operations Overview
                    </div>
                    <div class="mt-1 text-xs text-slate-500">
                        <span x-text="meta.last_updated_label"></span>
                        <span class="mx-2 opacity-40">•</span>
                        <span x-text="meta.server_time"></span>
                        <span class="mx-2 opacity-40">•</span>
                        <span :class="online ? 'text-emerald-700' : 'text-rose-700'" class="font-bold">
                            <span x-text="online ? 'Online' : 'Offline'"></span>
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="fetchData(true)"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                        <iconify-icon icon="mdi:refresh" class="text-base"></iconify-icon>
                        Refresh
                    </button>

                    <button type="button" @click="toggleAutoRefresh()"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-extrabold text-white transition-colors"
                        :class="autoRefresh ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-slate-900 hover:bg-slate-800'">
                        <iconify-icon :icon="autoRefresh ? 'mdi:sync' : 'mdi:sync-off'"
                            class="text-base"></iconify-icon>
                        <span x-text="autoRefresh ? 'Auto refresh ON' : 'Auto refresh OFF'"></span>
                    </button>

                    <div
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
                        <iconify-icon icon="mdi:filter" class="text-base text-slate-500"></iconify-icon>
                        <select x-model="filters.range" @change="fetchData()"
                            class="bg-transparent text-xs font-bold text-slate-700 outline-none">
                            <option value="7d">Last 7d</option>
                            <option value="14d">Last 14d</option>
                            <option value="30d">Last 30d</option>
                            <option value="90d">Last 90d</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Loading skeletons --}}
            <template x-if="loading">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                        <template x-for="i in 6" :key="'kpi-' + i">
                            <div class="h-28 rounded-2xl border border-slate-200 bg-white p-4 animate-pulse"></div>
                        </template>
                    </div>
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <div class="h-96 rounded-2xl border border-slate-200 bg-white p-4 animate-pulse lg:col-span-2">
                        </div>
                        <div class="h-96 rounded-2xl border border-slate-200 bg-white p-4 animate-pulse"></div>
                    </div>
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <div class="h-80 rounded-2xl border border-slate-200 bg-white p-4 animate-pulse"></div>
                        <div class="h-80 rounded-2xl border border-slate-200 bg-white p-4 animate-pulse"></div>
                        <div class="h-80 rounded-2xl border border-slate-200 bg-white p-4 animate-pulse"></div>
                    </div>
                </div>
            </template>

            {{-- Error --}}
            <template x-if="!loading && error">
                <div class="rounded-2xl border border-red-200 bg-gradient-to-br from-red-50 to-white p-6 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-100 text-red-600 flex-shrink-0">
                            <iconify-icon icon="mdi:alert-circle" class="text-2xl"></iconify-icon>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-extrabold text-red-900">Dashboard data failed to load</div>
                            <div class="mt-2 text-xs text-red-700" x-text="error"></div>
                            <button type="button" @click="fetchData()"
                                class="mt-4 inline-flex items-center gap-2 justify-center rounded-xl bg-red-600 px-4 py-2 text-xs font-bold text-white hover:bg-red-700 transition-colors">
                                <iconify-icon icon="mdi:refresh" class="text-base"></iconify-icon>
                                Retry
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Loaded --}}
            <template x-if="!loading && data">
                <div class="space-y-6">

                    {{-- KPI Row (expanded) --}}
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <template x-for="card in kpiCards" :key="card.key">
                            <div
                                class="group relative overflow-hidden rounded-3xl border p-6 transition-all duration-300 hover:shadow-xl hover:-translate-y-1"
                                :class="{
                                    'border-blue-200 bg-gradient-to-br from-blue-50 via-white to-blue-50/30 hover:border-blue-300': card.key === 'users',
                                    'border-purple-200 bg-gradient-to-br from-purple-50 via-white to-purple-50/30 hover:border-purple-300': card.key === 'customers',
                                    'border-amber-200 bg-gradient-to-br from-amber-50 via-white to-amber-50/30 hover:border-amber-300': card.key === 'estimates_open',
                                    'border-cyan-200 bg-gradient-to-br from-cyan-50 via-white to-cyan-50/30 hover:border-cyan-300': card.key === 'orders_active',
                                    'border-rose-200 bg-gradient-to-br from-rose-50 via-white to-rose-50/30 hover:border-rose-300': card.key === 'invoices_outstanding',
                                    'border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-emerald-50/30 hover:border-emerald-300': card.key === 'revenue_30d',
                                    'border-indigo-200 bg-gradient-to-br from-indigo-50 via-white to-indigo-50/30 hover:border-indigo-300': card.key === 'active_customers_30d',
                                    'border-teal-200 bg-gradient-to-br from-teal-50 via-white to-teal-50/30 hover:border-teal-300': card.key === 'avg_order_value'
                                }">
                                
                                <!-- Background decoration -->
                                <div class="absolute top-0 right-0 w-32 h-32 opacity-5 -mr-8 -mt-8 transition-all duration-300 group-hover:opacity-10 group-hover:scale-110">
                                    <iconify-icon :icon="card.icon" class="text-[120px]"></iconify-icon>
                                </div>

                                <!-- Content -->
                                <div class="relative">
                                    <!-- Header with icon -->
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500"
                                            x-text="card.label"></div>
                                        <div class="flex h-11 w-11 items-center justify-center rounded-xl shadow-sm transition-all duration-300 group-hover:shadow-md group-hover:scale-110"
                                            :class="{
                                                'bg-gradient-to-br from-blue-400 to-blue-600 text-white': card.key === 'users',
                                                'bg-gradient-to-br from-purple-400 to-purple-600 text-white': card.key === 'customers',
                                                'bg-gradient-to-br from-amber-400 to-amber-600 text-white': card.key === 'estimates_open',
                                                'bg-gradient-to-br from-cyan-400 to-cyan-600 text-white': card.key === 'orders_active',
                                                'bg-gradient-to-br from-rose-400 to-rose-600 text-white': card.key === 'invoices_outstanding',
                                                'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white': card.key === 'revenue_30d',
                                                'bg-gradient-to-br from-indigo-400 to-indigo-600 text-white': card.key === 'active_customers_30d',
                                                'bg-gradient-to-br from-teal-400 to-teal-600 text-white': card.key === 'avg_order_value'
                                            }">
                                            <iconify-icon :icon="card.icon" class="text-2xl"></iconify-icon>
                                        </div>
                                    </div>

                                    <!-- Main value -->
                                    <div class="mb-2">
                                        <div class="text-3xl font-black tracking-tight"
                                            :class="{
                                                'text-blue-900': card.key === 'users',
                                                'text-purple-900': card.key === 'customers',
                                                'text-amber-900': card.key === 'estimates_open',
                                                'text-cyan-900': card.key === 'orders_active',
                                                'text-rose-900': card.key === 'invoices_outstanding',
                                                'text-emerald-900': card.key === 'revenue_30d',
                                                'text-indigo-900': card.key === 'active_customers_30d',
                                                'text-teal-900': card.key === 'avg_order_value'
                                            }"
                                            x-text="card.value"></div>
                                    </div>

                                    <!-- Hint text -->
                                    <div class="text-xs font-medium text-slate-600 mb-3" x-text="card.hint"></div>

                                    <!-- Delta badge -->
                                    <template x-if="card.delta_label">
                                        <div class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold shadow-sm transition-all duration-200"
                                            :class="card.delta_positive 
                                                ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white' 
                                                : 'bg-gradient-to-r from-rose-500 to-rose-600 text-white'">
                                            <iconify-icon
                                                :icon="card.delta_positive ? 'mdi:trending-up' : 'mdi:trending-down'"
                                                class="text-sm"></iconify-icon>
                                            <span x-text="card.delta_label"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Main charts row --}}
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <iconify-icon icon="mdi:chart-areaspline"
                                        class="text-xl text-slate-600"></iconify-icon>
                                    <div>
                                        <div class="text-sm font-extrabold text-slate-900"
                                            x-text="`Performance (${filters.range})`"></div>
                                        <div class="mt-0.5 text-xs text-slate-500">Orders, invoices, revenue,
                                            conversion</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="setChartMode('daily')"
                                        class="rounded-xl px-3 py-2 text-xs font-bold border transition-colors"
                                        :class="chartMode === 'daily' ? 'bg-slate-900 text-white border-slate-900' :
                                            'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'">
                                        Daily
                                    </button>
                                    <button type="button" @click="setChartMode('weekly')"
                                        class="rounded-xl px-3 py-2 text-xs font-bold border transition-colors"
                                        :class="chartMode === 'weekly' ? 'bg-slate-900 text-white border-slate-900' :
                                            'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'">
                                        Weekly
                                    </button>
                                </div>
                            </div>

                            <div class="mt-5">
                                <canvas id="chartMain" height="110"></canvas>
                            </div>

                            <div class="mt-5 grid grid-cols-1 gap-3 md:grid-cols-4">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-[11px] uppercase tracking-wide text-slate-400 font-bold">Estimate
                                        → Order</div>
                                    <div class="mt-1 text-lg font-extrabold text-slate-900"
                                        x-text="data.metrics.conversion_label"></div>
                                    <div class="mt-1 text-[11px] text-slate-500">Acceptance rate</div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-[11px] uppercase tracking-wide text-slate-400 font-bold">Avg
                                        fulfillment</div>
                                    <div class="mt-1 text-lg font-extrabold text-slate-900"
                                        x-text="data.metrics.avg_fulfillment_label"></div>
                                    <div class="mt-1 text-[11px] text-slate-500">Order → delivered</div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-[11px] uppercase tracking-wide text-slate-400 font-bold">Overdue
                                        invoices</div>
                                    <div class="mt-1 text-lg font-extrabold text-slate-900"
                                        x-text="data.metrics.overdue_invoices"></div>
                                    <div class="mt-1 text-[11px] text-slate-500">Need follow-up</div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-[11px] uppercase tracking-wide text-slate-400 font-bold">Delivery
                                        risk</div>
                                    <div class="mt-1 text-lg font-extrabold text-slate-900"
                                        x-text="data.metrics.delivery_risk_label"></div>
                                    <div class="mt-1 text-[11px] text-slate-500">SLA watch</div>
                                </div>
                            </div>
                        </div>

                        {{-- Status breakdown + funnel --}}
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <iconify-icon icon="mdi:chart-donut"
                                        class="text-xl text-slate-600"></iconify-icon>
                                    <div>
                                        <div class="text-sm font-extrabold text-slate-900">Workload</div>
                                        <div class="mt-0.5 text-xs text-slate-500">Orders & estimates status</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <canvas id="chartDonut" height="150"></canvas>
                            </div>

                            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center justify-between">
                                    <div class="text-xs font-extrabold text-slate-900 flex items-center gap-2">
                                        <iconify-icon icon="mdi:filter-variant"
                                            class="text-base text-slate-600"></iconify-icon>
                                        Funnel
                                    </div>
                                    <div class="text-[11px] text-slate-500">This period</div>
                                </div>

                                <div class="mt-3 space-y-2">
                                    <template x-for="f in data.funnel" :key="f.key">
                                        <div>
                                            <div class="flex items-center justify-between text-[11px] text-slate-600">
                                                <div class="font-bold" x-text="f.label"></div>
                                                <div class="font-extrabold text-slate-900" x-text="f.value"></div>
                                            </div>
                                            <div
                                                class="mt-1 h-2 rounded-full bg-white border border-slate-200 overflow-hidden">
                                                <div class="h-full bg-slate-900" :style="`width:${f.pct}%`"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Insights row --}}
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-center gap-2">
                                <iconify-icon icon="mdi:star-circle" class="text-xl text-amber-600"></iconify-icon>
                                <div>
                                    <div class="text-sm font-extrabold text-slate-900">Top products</div>
                                    <div class="mt-0.5 text-xs text-slate-500">By orders & revenue</div>
                                </div>
                            </div>

                            <div class="mt-4 divide-y divide-slate-100">
                                <template x-for="p in data.top.products" :key="p.id">
                                    <div class="py-3 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="text-xs font-extrabold text-slate-900 truncate"
                                                x-text="p.name"></div>
                                            <div class="mt-0.5 text-[11px] text-slate-500"
                                                x-text="`${p.orders} orders • ${p.revenue_label}`"></div>
                                        </div>
                                        <div class="text-[11px] font-bold text-slate-700" x-text="p.avg_label"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-center gap-2">
                                <iconify-icon icon="mdi:account-tie" class="text-xl text-purple-600"></iconify-icon>
                                <div>
                                    <div class="text-sm font-extrabold text-slate-900">Top customers</div>
                                    <div class="mt-0.5 text-xs text-slate-500">VIP / high frequency</div>
                                </div>
                            </div>

                            <div class="mt-4 divide-y divide-slate-100">
                                <template x-for="c in data.top.customers" :key="c.id">
                                    <div class="py-3 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="text-xs font-extrabold text-slate-900 truncate"
                                                x-text="c.name"></div>
                                            <div class="mt-0.5 text-[11px] text-slate-500"
                                                x-text="`${c.orders} orders • ${c.total_label}`"></div>
                                        </div>
                                        <span
                                            class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] font-bold text-slate-700"
                                            x-text="c.type"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-center gap-2">
                                <iconify-icon icon="mdi:domain" class="text-xl text-cyan-600"></iconify-icon>
                                <div>
                                    <div class="text-sm font-extrabold text-slate-900">Working group insights</div>
                                    <div class="mt-0.5 text-xs text-slate-500">Revenue split & volume</div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <canvas id="chartWG" height="160"></canvas>
                            </div>

                            <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-extrabold text-slate-900">System health</div>
                                <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-3">
                                    <div class="rounded-xl border border-slate-200 bg-white p-3">
                                        <div class="text-[11px] uppercase tracking-wide text-slate-400 font-bold">PHP
                                        </div>
                                        <div class="mt-1 text-xs font-extrabold text-slate-900"
                                            x-text="data.system.php"></div>
                                    </div>
                                    <div class="rounded-xl border border-slate-200 bg-white p-3">
                                        <div class="text-[11px] uppercase tracking-wide text-slate-400 font-bold">
                                            Laravel</div>
                                        <div class="mt-1 text-xs font-extrabold text-slate-900"
                                            x-text="data.system.laravel"></div>
                                    </div>
                                    <div class="rounded-xl border border-slate-200 bg-white p-3">
                                        <div class="text-[11px] uppercase tracking-wide text-slate-400 font-bold">Queue
                                        </div>
                                        <div class="mt-1 text-xs font-extrabold text-slate-900"
                                            x-text="data.system.queue_connection"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Recent Activity row (keep yours, but richer labels) --}}
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <iconify-icon icon="mdi:package-variant-closed-check"
                                        class="text-xl text-cyan-500"></iconify-icon>
                                    <div>
                                        <div class="text-sm font-extrabold text-slate-900">Recent orders</div>
                                        <div class="mt-0.5 text-xs text-slate-500">Latest system activity</div>
                                    </div>
                                </div>
                                <a href="{{ route('admin.orders.index') }}"
                                    class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 hover:text-slate-900 transition-colors">
                                    View all
                                    <iconify-icon icon="mdi:arrow-right" class="text-sm"></iconify-icon>
                                </a>
                            </div>

                            <div class="mt-4 divide-y divide-slate-100">
                                <template x-for="o in data.recent.orders" :key="o.id">
                                    <div
                                        class="group flex items-center justify-between gap-4 py-3 hover:bg-slate-50 -mx-2 px-2 rounded-lg transition-colors">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div
                                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-50 text-cyan-600 flex-shrink-0">
                                                <iconify-icon icon="mdi:cart" class="text-lg"></iconify-icon>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-extrabold text-slate-900 truncate"
                                                    x-text="o.order_no"></div>
                                                <div class="mt-0.5 text-[11px] text-slate-500 truncate"
                                                    x-text="o.subtitle"></div>
                                            </div>
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <div class="text-xs font-bold text-slate-900" x-text="o.total_label">
                                            </div>
                                            <div class="mt-0.5 text-[11px] text-slate-500" x-text="o.created_at">
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <iconify-icon icon="mdi:receipt-text-check"
                                        class="text-xl text-blue-500"></iconify-icon>
                                    <div>
                                        <div class="text-sm font-extrabold text-slate-900">Recent invoices</div>
                                        <div class="mt-0.5 text-xs text-slate-500">Billing workflow updates</div>
                                    </div>
                                </div>
                                <a href="{{ route('admin.invoices.index') }}"
                                    class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 hover:text-slate-900 transition-colors">
                                    View all
                                    <iconify-icon icon="mdi:arrow-right" class="text-sm"></iconify-icon>
                                </a>
                            </div>

                            <div class="mt-4 divide-y divide-slate-100">
                                <template x-for="inv in data.recent.invoices" :key="inv.id">
                                    <div
                                        class="group flex items-center justify-between gap-4 py-3 hover:bg-slate-50 -mx-2 px-2 rounded-lg transition-colors">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div
                                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 flex-shrink-0">
                                                <iconify-icon icon="mdi:file-document" class="text-lg"></iconify-icon>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-extrabold text-slate-900 truncate"
                                                    x-text="inv.invoice_no"></div>
                                                <div class="mt-0.5 text-[11px] text-slate-500 truncate"
                                                    x-text="inv.subtitle"></div>
                                            </div>
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <div class="text-xs font-bold text-slate-900" x-text="inv.total_label">
                                            </div>
                                            <div class="mt-0.5 text-[11px] text-slate-500" x-text="inv.created_at">
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                </div>
            </template>

        </div>

        @push('scripts')
            {{-- Chart.js (CDN). If you prefer local, tell me and I'll convert to Vite asset. --}}
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

            <script>
                window.staffDashboardPlus = (opts) => ({
                    url: opts.url,
                    loading: true,
                    error: null,
                    data: null,

                    online: navigator.onLine,
                    autoRefresh: true,
                    autoRefreshMs: 30000,
                    timer: null,

                    filters: {
                        range: '14d'
                    },
                    chartMode: 'daily',

                    charts: {
                        main: null,
                        donut: null,
                        wg: null
                    },

                    meta: {
                        last_updated_label: 'Last updated: —',
                        server_time: '—'
                    },

                    init() {
                        window.addEventListener('online', () => this.online = true);
                        window.addEventListener('offline', () => this.online = false);

                        this.fetchData();
                        this.startAutoRefresh();
                    },

                    toggleAutoRefresh() {
                        this.autoRefresh = !this.autoRefresh;
                        if (this.autoRefresh) this.startAutoRefresh();
                        else this.stopAutoRefresh();
                    },

                    startAutoRefresh() {
                        this.stopAutoRefresh();
                        if (!this.autoRefresh) return;
                        this.timer = setInterval(() => {
                            if (this.online) this.fetchData(true);
                        }, this.autoRefreshMs);
                    },

                    stopAutoRefresh() {
                        if (this.timer) clearInterval(this.timer);
                        this.timer = null;
                    },

                    setChartMode(mode) {
                        this.chartMode = mode;
                        this.renderCharts();
                    },

                    async fetchData(silent = false) {
                        if (!silent) this.loading = true;
                        this.error = null;

                        try {
                            const res = await window.axios.get(this.url, {
                                params: {
                                    range: this.filters.range
                                },
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });

                            this.data = res.data;
                            this.meta.last_updated_label = this.data?.meta?.last_updated_label ||
                                `Last updated: ${new Date().toLocaleString()}`;
                            this.meta.server_time = this.data?.meta?.server_time || '—';

                            this.renderCharts();

                        } catch (e) {
                            this.error = (e?.response?.data?.message) || e?.message || 'Unknown error';
                        } finally {
                            this.loading = false;
                        }
                    },

                    get kpiCards() {
                        const k = this.data?.kpis || {};
                        // ✅ This is where we expand to 8 KPIs (enterprise style)
                        return [{
                                key: 'users',
                                label: 'Users',
                                value: k.users_total ?? '—',
                                hint: 'System accounts',
                                icon: 'mdi:account-group'
                            },
                            {
                                key: 'customers',
                                label: 'Customers',
                                value: k.customers_total ?? '—',
                                hint: 'Profiles',
                                icon: 'mdi:account-heart'
                            },
                            {
                                key: 'est_open',
                                label: 'Open Estimates',
                                value: k.estimates_open ?? '—',
                                hint: 'Draft / sent / viewed',
                                icon: 'mdi:file-document-edit'
                            },
                            {
                                key: 'orders_active',
                                label: 'Active Orders',
                                value: k.orders_active ?? '—',
                                hint: 'In progress',
                                icon: 'mdi:package-variant'
                            },
                            {
                                key: 'inv_out',
                                label: 'Outstanding',
                                value: k.invoices_outstanding ?? '—',
                                hint: 'Issued / overdue',
                                icon: 'mdi:receipt-text-clock'
                            },
                            {
                                key: 'rev',
                                label: 'Revenue',
                                value: k.revenue_label ?? '—',
                                hint: `${this.filters.range} confirmed`,
                                icon: 'mdi:currency-usd',
                                delta_label: k.revenue_delta_label,
                                delta_positive: !!k.revenue_delta_positive
                            },
                            {
                                key: 'sla',
                                label: 'SLA Risk',
                                value: k.sla_risk ?? '—',
                                hint: 'Due soon / late',
                                icon: 'mdi:clock-alert'
                            },
                            {
                                key: 'conv',
                                label: 'Conversion',
                                value: k.conversion_label ?? '—',
                                hint: 'Estimate → order',
                                icon: 'mdi:swap-horizontal'
                            },
                        ];
                    },

                    renderCharts() {
                        if (!this.data) return;

                        // MAIN CHART (line / multi-series)
                        const series = this.chartMode === 'weekly' ?
                            (this.data?.series?.weekly || null) :
                            (this.data?.series?.daily || null);

                        if (series) {
                            const ctx = document.getElementById('chartMain')?.getContext('2d');
                            if (ctx) {
                                if (this.charts.main) this.charts.main.destroy();
                                this.charts.main = new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: series.labels || [],
                                        datasets: [{
                                                label: 'Orders',
                                                data: series.orders || [],
                                                tension: 0.35
                                            },
                                            {
                                                label: 'Invoices',
                                                data: series.invoices || [],
                                                tension: 0.35
                                            },
                                            {
                                                label: 'Revenue',
                                                data: series.revenue || [],
                                                tension: 0.35
                                            },
                                            {
                                                label: 'Conversion %',
                                                data: series.conversion_pct || [],
                                                tension: 0.35
                                            },
                                        ]
                                    },
                                    options: {
                                        responsive: true,
                                        plugins: {
                                            legend: {
                                                position: 'bottom'
                                            },
                                            tooltip: {
                                                mode: 'index',
                                                intersect: false
                                            }
                                        },
                                        interaction: {
                                            mode: 'index',
                                            intersect: false
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true
                                            }
                                        }
                                    }
                                });
                            }
                        }

                        // DONUT CHART (workload breakdown)
                        const donut = this.data?.charts?.donut;
                        const dctx = document.getElementById('chartDonut')?.getContext('2d');
                        if (donut && dctx) {
                            if (this.charts.donut) this.charts.donut.destroy();
                            this.charts.donut = new Chart(dctx, {
                                type: 'doughnut',
                                data: {
                                    labels: donut.labels || [],
                                    datasets: [{
                                        data: donut.values || []
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: {
                                            position: 'bottom'
                                        }
                                    }
                                }
                            });
                        }

                        // WG CHART (bar)
                        const wg = this.data?.charts?.working_groups;
                        const wctx = document.getElementById('chartWG')?.getContext('2d');
                        if (wg && wctx) {
                            if (this.charts.wg) this.charts.wg.destroy();
                            this.charts.wg = new Chart(wctx, {
                                type: 'bar',
                                data: {
                                    labels: wg.labels || [],
                                    datasets: [{
                                            label: 'Orders',
                                            data: wg.orders || []
                                        },
                                        {
                                            label: 'Revenue',
                                            data: wg.revenue || []
                                        },
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: {
                                            position: 'bottom'
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                        }
                    },
                });
            </script>
        @endpush
    @endif

</x-app-layout>
