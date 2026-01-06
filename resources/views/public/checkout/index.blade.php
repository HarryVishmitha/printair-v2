<x-guest-layout :seo="$seo ?? []">
    {{-- Iconify (prefer to place in main layout head globally) --}}
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>

    <div
	        x-data="printairCheckout({
	            csrf: @js(csrf_token()),
	            endpoints: {
	                home: @js(url('/')),
	                guestStart: @js(route('checkout.guest.start')),
	                guestVerify: @js(route('checkout.guest.verify')),
	                placeOrder: @js(route('checkout.place')),
	                cartShow: @js(route('cart.show')),
	                addressesIndex: @js(route('checkout.addresses.index')),
                addressesStore: @js(route('checkout.addresses.store')),
            }
        })"
        x-init="init()"
        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
    >
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="iconify text-slate-700" data-icon="mdi:cart-check"></span>
                    Checkout
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Verify your email to securely place an order. Admins will review and issue your invoice.
                </p>
            </div>

            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold text-slate-700">
                <span class="iconify" data-icon="mdi:shield-lock-outline"></span>
                Secure Checkout
            </span>
        </div>

        {{-- Alerts --}}
        <template x-if="flash.message">
            <div class="mt-5 rounded-2xl border px-4 py-3 text-sm"
                 :class="flash.type === 'success'
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                    : 'border-rose-200 bg-rose-50 text-rose-800'">
                <div class="flex items-start gap-2">
                    <span class="iconify mt-[2px]" :data-icon="flash.type === 'success' ? 'mdi:check-circle-outline' : 'mdi:alert-circle-outline'"></span>
                    <div x-text="flash.message"></div>
                </div>
            </div>
        </template>

        {{-- Stepper --}}
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Step 1: Customer --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="iconify text-slate-700" data-icon="mdi:account-outline"></span>
                        Customer
                    </div>
                    <span class="text-[11px] font-bold"
                          :class="isVerified ? 'text-emerald-700' : 'text-slate-500'">
                        <span x-text="isVerified ? 'Verified' : 'Required'"></span>
                    </span>
                </div>

                <div class="mt-4 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Full Name (optional)</label>
                        <input type="text"
                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                               x-model="customer.name"
                               placeholder="Your name">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Email *</label>
                        <input type="email"
                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                               x-model="customer.email"
                               placeholder="you@example.com">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">WhatsApp Number *</label>
                        <input type="text"
                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                               x-model="customer.whatsapp"
                               placeholder="+94XXXXXXXXX">
                        <div class="mt-1 text-[11px] text-slate-500">
                            We'll use WhatsApp for quick order clarifications if needed.
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="button"
                                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-extrabold text-white hover:bg-slate-800 disabled:opacity-50"
                                :disabled="busy.startOtp || !customer.email || !customer.whatsapp"
                                @click="startOtp()">
                            <span class="iconify" data-icon="mdi:email-fast-outline"></span>
                            <span x-text="busy.startOtp ? 'Sending…' : 'Send OTP'"></span>
                        </button>

                        <button type="button"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-extrabold text-slate-700 hover:bg-slate-50"
                                @click="prefillDemo()">
                            <span class="iconify" data-icon="mdi:lightning-bolt-outline"></span>
                            Quick fill
                        </button>
                    </div>
                </div>

                {{-- OTP box --}}
                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs font-extrabold text-slate-800 flex items-center gap-2">
                        <span class="iconify" data-icon="mdi:shield-key-outline"></span>
                        Email verification code
                    </div>
                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <input type="text"
                               maxlength="6"
                               class="sm:col-span-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm tracking-widest font-extrabold focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                               x-model="otp"
                               placeholder="6-digit OTP">

                        <button type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#ef233c] px-4 py-2 text-xs font-extrabold text-white hover:opacity-95 disabled:opacity-50"
                                :disabled="busy.verifyOtp || !customer.email || !otp"
                                @click="verifyOtp()">
                            <span class="iconify" data-icon="mdi:check-decagram-outline"></span>
                            <span x-text="busy.verifyOtp ? 'Verifying…' : 'Verify'"></span>
                        </button>
                    </div>

                    <div class="mt-2 text-[11px] text-slate-500">
                        OTP expires in <span class="font-bold text-slate-700">7 minutes</span>.
                    </div>
                </div>
            </div>

            {{-- Step 2: Address --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="iconify text-slate-700" data-icon="mdi:map-marker-outline"></span>
                        Delivery / Pickup
                    </div>
                    <span class="text-[11px] font-bold text-slate-500">Optional now</span>
                </div>

                <div class="mt-4 space-y-3">
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button"
                                class="rounded-xl border px-4 py-3 text-left transition"
                                :class="shipping.method === 'pickup'
                                    ? 'border-[#ef233c] ring-2 ring-[#ef233c]/20 bg-[#ef233c]/[0.03]'
                                    : 'border-slate-200 bg-white hover:bg-slate-50'"
                                @click="shipping.method='pickup'">
                            <div class="font-extrabold text-slate-900 flex items-center gap-2">
                                <span class="iconify" data-icon="mdi:storefront-outline"></span>
                                Pickup
                            </div>
                            <div class="text-xs text-slate-500 mt-1">Collect from Printair</div>
                        </button>

                        <button type="button"
                                class="rounded-xl border px-4 py-3 text-left transition"
                                :class="shipping.method === 'delivery'
                                    ? 'border-[#ef233c] ring-2 ring-[#ef233c]/20 bg-[#ef233c]/[0.03]'
                                    : 'border-slate-200 bg-white hover:bg-slate-50'"
                                @click="shipping.method='delivery'">
                            <div class="font-extrabold text-slate-900 flex items-center gap-2">
                                <span class="iconify" data-icon="mdi:truck-delivery-outline"></span>
                                Delivery
                            </div>
                            <div class="text-xs text-slate-500 mt-1">Admin will confirm fee</div>
                        </button>
                    </div>

                    <template x-if="isVerified">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-extrabold text-slate-800 flex items-center gap-2">
                                    <span class="iconify" data-icon="mdi:bookmark-multiple-outline"></span>
                                    Saved addresses
                                </div>
                                <template x-if="addressBook.loading">
                                    <div class="text-[11px] text-slate-500">Loading…</div>
                                </template>
                            </div>

                            <template x-if="!addressBook.loading && (addressBook.list?.length)">
                                <div class="mt-3">
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Choose an address</label>
                                    <select class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                            x-model="addressBook.selectedId"
                                            @change="
                                                const a = addressBook.list.find(x => String(x.id) === String(addressBook.selectedId));
                                                applyAddress(a);
                                            ">
                                        <template x-for="a in addressBook.list" :key="a.id">
                                            <option :value="a.id" x-text="(a.label ? (a.label + ' — ') : '') + (a.line1 || '')"></option>
                                        </template>
                                    </select>

                                    <div class="mt-2 text-[11px] text-slate-500">
                                        Selecting an address switches checkout to <b>Delivery</b> and fills the fields.
                                    </div>
                                </div>
                            </template>

                            <template x-if="!addressBook.loading && !(addressBook.list?.length)">
                                <div class="mt-2 text-[11px] text-slate-500">
                                    No saved addresses yet. Add one below or just type manually.
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="shipping.method === 'delivery'">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Address Line 1</label>
                                <input type="text"
                                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                       x-model="shipping.line1"
                                       placeholder="House No, Street">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">City</label>
                                <input type="text"
                                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                       x-model="shipping.city"
                                       placeholder="Ragama">
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">District</label>
                                    <input type="text"
                                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                           x-model="shipping.district"
                                           placeholder="Gampaha">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Postal Code</label>
                                    <input type="text"
                                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                           x-model="shipping.postal_code"
                                           placeholder="11010">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Step 3: Submit --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="iconify text-slate-700" data-icon="mdi:clipboard-check-outline"></span>
                        Submit Order
                    </div>
                    <span class="text-[11px] font-bold text-slate-500">Admin review first</span>
                </div>

                <div class="mt-4 space-y-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-xs font-extrabold text-slate-800 flex items-center gap-2">
                            <span class="iconify" data-icon="mdi:note-text-outline"></span>
                            Notes (optional)
                        </div>
                        <textarea rows="5"
                                  class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ef233c]/20 focus:border-[#ef233c]/40"
                                  x-model="notes"
                                  placeholder="Any special instructions for our team?"></textarea>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs text-slate-600 flex items-start gap-2">
                        <span class="iconify mt-[1px]" data-icon="mdi:lock-outline"></span>
                        <div>Your order will be created as a <b>draft</b>. Admins will confirm pricing and generate the invoice.</div>
                        
                    </div>

                    <button type="button"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#ef233c] px-5 py-3 text-sm font-extrabold text-white hover:opacity-95 disabled:opacity-50"
                            :disabled="busy.placeOrder || !isVerified"
                            @click="placeOrder()">
                        <span class="iconify" data-icon="mdi:send-check-outline"></span>
                        <span x-text="busy.placeOrder ? 'Submitting…' : 'Submit Order for Review'"></span>
                    </button>

                    <template x-if="submitted.secureUrl">
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            <div class="font-extrabold flex items-center gap-2">
                                <span class="iconify" data-icon="mdi:check-circle-outline"></span>
                                Submitted!
                            </div>
                            <div class="mt-1 text-xs">
                                We emailed you a secure link. If you want, you can open it here too:
                            </div>
                            <a class="mt-2 inline-flex items-center gap-2 rounded-xl bg-white border border-emerald-200 px-4 py-2 text-xs font-extrabold text-emerald-800 hover:bg-emerald-50"
                               :href="submitted.secureUrl">
                                <span class="iconify" data-icon="mdi:open-in-new"></span>
                                Open secure order page
                            </a>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        function printairCheckout({ csrf, endpoints }) {
            return {
                csrf,
                endpoints,

                busy: {
                    startOtp: false,
                    verifyOtp: false,
                    placeOrder: false,
                },

                flash: { type: null, message: null },

                customer: {
                    name: '',
                    email: '',
                    whatsapp: '',
                    phone: '',
                },

                otp: '',
                isVerified: false,

                shipping: {
                    method: 'pickup', // pickup|delivery
                    line1: '',
                    line2: '',
                    city: '',
                    district: '',
                    postal_code: '',
                    country: 'LK',
                },

                notes: '',
                submitted: {
                    orderId: null,
                    secureUrl: null,
                },

                addressBook: {
                    loading: false,
                    list: [],
                    selectedId: null,
                },

                async init() {
                    // optional: fetch cart summary / validate cart not empty
                    // await this.loadCart();
                },

                setFlash(type, message) {
                    this.flash = { type, message };
                    window.setTimeout(() => {
                        if (this.flash.message === message) this.flash = { type: null, message: null };
                    }, 7000);
                },

                prefillDemo() {
                    this.customer.name = this.customer.name || 'Customer';
                    this.customer.email = this.customer.email || ('test' + Math.floor(Math.random()*9999) + '@example.com');
                    this.customer.whatsapp = this.customer.whatsapp || '+9477XXXXXXX';
                },

                async post(url, body) {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(body || {}),
                    });

                    const contentType = res.headers.get('content-type') || '';
                    const data = contentType.includes('application/json') ? await res.json() : { message: await res.text() };

                    if (!res.ok) {
                        const msg = data?.message || 'Request failed';
                        throw new Error(msg);
                    }

                    return data;
                },

                async startOtp() {
                    this.busy.startOtp = true;
                    try {
                        const data = await this.post(this.endpoints.guestStart, {
                            name: this.customer.name || null,
                            email: this.customer.email,
                            whatsapp: this.customer.whatsapp,
                        });

                        this.setFlash('success', data.message || 'OTP sent to your email.');
                    } catch (e) {
                        this.setFlash('error', e.message || 'Failed to send OTP.');
                    } finally {
                        this.busy.startOtp = false;
                    }
                },

                async verifyOtp() {
                    this.busy.verifyOtp = true;
                    try {
                        const data = await this.post(this.endpoints.guestVerify, {
                            email: this.customer.email,
                            otp: this.otp,
                        });

                        this.isVerified = true;
                        this.setFlash('success', data.message || 'Verified.');
                        await this.loadAddresses();
                    } catch (e) {
                        this.isVerified = false;
                        this.setFlash('error', e.message || 'Verification failed.');
                    } finally {
                        this.busy.verifyOtp = false;
                    }
                },

                async placeOrder() {
                    this.busy.placeOrder = true;

                    try {
                        const shipping = (this.shipping.method === 'delivery')
                            ? {
                                method: 'delivery',
                                line1: this.shipping.line1 || null,
                                line2: this.shipping.line2 || null,
                                city: this.shipping.city || null,
                                district: this.shipping.district || null,
                                postal_code: this.shipping.postal_code || null,
                                country: this.shipping.country || 'LK',
                            }
                            : { method: 'pickup' };

                        const payload = {
                            working_group_id: null,

                            customer: {
                                name: this.customer.name || null,
                                email: this.customer.email,
                                whatsapp: this.customer.whatsapp,
                                phone: this.customer.phone || null,
                            },

                            shipping,
                            notes: this.notes || null,

                            meta: {
                                source: 'public_checkout',
                            }
                        };

	                        const data = await this.post(this.endpoints.placeOrder, payload);

	                        this.setFlash('success', (data.message || 'Order submitted.') + ' Redirecting…');
	                        this.submitted.orderId = data.order_id || null;
	                        window.setTimeout(() => {
	                            window.location.href = this.endpoints.home || '/';
	                        }, 800);
	                    } catch (e) {
	                        this.setFlash('error', e.message || 'Failed to submit order.');
	                    } finally {
	                        this.busy.placeOrder = false;
	                    }
                },

                async loadAddresses() {
                    this.addressBook.loading = true;

                    try {
                        const res = await fetch(this.endpoints.addressesIndex, {
                            headers: { 'Accept': 'application/json' },
                        });

                        const data = await res.json().catch(() => ({}));
                        if (!res.ok || !data.ok) {
                            throw new Error(data.message || 'Failed to load addresses');
                        }

                        this.addressBook.list = data.addresses || [];

                        const primary = this.addressBook.list.find(a => a.is_primary) || this.addressBook.list[0];
                        this.addressBook.selectedId = primary ? primary.id : null;

                        if (primary && this.shipping.method === 'delivery') {
                            this.applyAddress(primary);
                        }
                    } catch (e) {
                        console.warn(e);
                    } finally {
                        this.addressBook.loading = false;
                    }
                },

                applyAddress(addr) {
                    if (!addr) return;
                    this.shipping.method = 'delivery';
                    this.shipping.line1 = addr.line1 || '';
                    this.shipping.line2 = addr.line2 || '';
                    this.shipping.city = addr.city || '';
                    this.shipping.district = addr.district || '';
                    this.shipping.postal_code = addr.postal_code || '';
                    this.shipping.country = addr.country || 'LK';
                },
            }
        }
    </script>
</x-guest-layout>
