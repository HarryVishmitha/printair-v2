<x-home-layout :seo="$seo">
    <section class="bg-white">
        {{-- Hero --}}
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-7 sm:p-10">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-xs font-semibold tracking-wide text-slate-500">LEGAL</p>
                        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                            Privacy Policy
                        </h1>
                        <p class="mt-3 text-sm text-slate-600 sm:text-base">
                            We respect your privacy. This page explains what we collect, why we collect it, and how we
                            keep your information secure when you use Printair.
                        </p>
                    </div>

                    <div class="shrink-0">
                        <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                            <p class="text-xs font-semibold text-slate-500">Last updated</p>
                            <p class="mt-1 text-sm font-bold text-slate-900">{{ now()->format('F d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                {{-- Sticky quick nav --}}
                <aside class="lg:col-span-4">
                    <div class="sticky top-40 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-extrabold text-slate-900">On this page</p>

                        <nav class="mt-4 space-y-2 text-sm">
                            <a href="#info-we-collect"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                1. Information We Collect
                            </a>
                            <a href="#how-we-use"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                2. How We Use Information
                            </a>
                            <a href="#design-files"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                3. Design Files & Confidentiality
                            </a>
                            <a href="#working-groups"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                4. Working Groups & Access
                            </a>
                            <a href="#payments"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                5. Payments & Financial Data
                            </a>
                            <a href="#warranty"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                6. Warranty Disclaimer
                            </a>
                            <a href="#cookies"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                7. Cookies
                            </a>
                            <a href="#security"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                8. Security
                            </a>
                            <a href="#retention"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                9. Data Retention
                            </a>
                            <a href="#rights"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                10. Your Rights
                            </a>
                            <a href="#contact"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                11. Contact
                            </a>
                        </nav>
                    </div>
                </aside>

                {{-- Main content --}}
                <main class="lg:col-span-8">
                    <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm sm:p-10">
                        {{-- Helper style --}}
                        <style>
                            .pp h2 {
                                font-weight: 900;
                                letter-spacing: -0.02em;
                                color: #0f172a;
                            }

                            .pp p {
                                color: #475569;
                            }

                            .pp ul {
                                color: #475569;
                            }
                        </style>

                        <div class="pp space-y-10">
                            <section id="info-we-collect" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">1. Information We Collect</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    We collect information to provide accurate quotations, produce your prints
                                    correctly, and communicate order updates.
                                </p>

                                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                        <p class="text-sm font-extrabold text-slate-900">Personal Information</p>
                                        <ul class="mt-3 list-disc space-y-1 pl-5 text-sm">
                                            <li>Name</li>
                                            <li>Email</li>
                                            <li>Phone / WhatsApp</li>
                                            <li>Company name (if applicable)</li>
                                            <li>Billing / delivery address</li>
                                        </ul>
                                    </div>

                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                        <p class="text-sm font-extrabold text-slate-900">Order & Design Data</p>
                                        <ul class="mt-3 list-disc space-y-1 pl-5 text-sm">
                                            <li>Product selections & specifications</li>
                                            <li>Uploaded design files (artworks, PDFs, images)</li>
                                            <li>Quotation / order history</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5">
                                    <p class="text-sm font-extrabold text-slate-900">Technical Information</p>
                                    <p class="mt-2 text-sm">
                                        We may collect technical data like IP address, device type, and basic usage
                                        analytics to maintain security and performance.
                                    </p>
                                </div>
                            </section>

                            <section id="how-we-use" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">2. How We Use Information</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    We use your information only for business operations such as:
                                </p>
                                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm sm:text-base">
                                    <li>Preparing quotations and processing orders</li>
                                    <li>Producing printed materials accurately</li>
                                    <li>Order updates via email or WhatsApp</li>
                                    <li>Customer support and issue resolution</li>
                                    <li>Security monitoring and internal auditing</li>
                                </ul>

                                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                    <p class="text-sm font-extrabold text-slate-900">No selling of data</p>
                                    <p class="mt-2 text-sm">
                                        We do not sell, rent, or trade your personal data to third parties.
                                    </p>
                                </div>
                            </section>

                            <section id="design-files" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">3. Design Files & Confidentiality</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    Uploaded designs are treated as confidential and used only for production, previews,
                                    quality checks, and order fulfillment.
                                </p>
                                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm sm:text-base">
                                    <li>Design access is limited to authorized staff and systems</li>
                                    <li>Files may be retained temporarily for reprints, audits, or production safety
                                    </li>
                                    <li>Restricted files are not shared externally without permission</li>
                                </ul>
                            </section>

                            <section id="working-groups" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">4. Working Groups & Access</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    Some pricing, products, or design visibility may depend on “Working Groups” (e.g.,
                                    corporate partners or internal teams).
                                </p>
                                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm sm:text-base">
                                    <li>Users only see content assigned to their allowed group</li>
                                    <li>Unauthorized access attempts may be logged for security</li>
                                </ul>
                            </section>

                            <section id="payments" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">5. Payments & Financial Data</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    We do not store sensitive card information on our servers. If online payments are
                                    enabled, they are processed via secure payment providers.
                                </p>
                                <p class="mt-3 text-sm sm:text-base">
                                    We may retain transaction references for accounting and legal compliance.
                                </p>
                            </section>

                            <section id="warranty" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">6. Warranty Disclaimer</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    Some Printair products may include warranty periods, while others may not. Warranty
                                    availability, duration, and conditions may vary by product.
                                </p>

                                <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-5">
                                    <p class="text-sm font-extrabold text-slate-900">Final decision clause</p>
                                    <p class="mt-2 text-sm text-slate-700">
                                        All warranty-related decisions, interpretations, and approvals are made solely
                                        at the discretion of Printair Advertising.
                                        The company’s decision is final and binding.
                                    </p>
                                </div>

                                <p class="mt-4 text-sm sm:text-base">
                                    For clarity, customers should confirm warranty details before finalizing an order.
                                </p>
                            </section>

                            <section id="cookies" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">7. Cookies</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    We may use cookies to maintain sessions, improve site performance, and enhance
                                    security. You can disable cookies in your browser settings,
                                    but some features may not work correctly.
                                </p>
                            </section>

                            <section id="security" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">8. Data Security</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    We use access controls, authentication, secure hosting, and monitoring to protect
                                    your data. No platform is 100% risk-free, but we continuously
                                    improve our safeguards.
                                </p>
                            </section>

                            <section id="retention" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">9. Data Retention</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    We retain personal and order data only as long as needed for business, legal,
                                    accounting, or operational purposes.
                                    Where legally applicable, you may request deletion.
                                </p>
                            </section>

                            <section id="rights" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">10. Your Rights</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    Depending on applicable laws, you may request:
                                </p>
                                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm sm:text-base">
                                    <li>Access to your personal data</li>
                                    <li>Corrections / updates</li>
                                    <li>Deletion requests (where legally applicable)</li>
                                </ul>
                            </section>

                            <section id="contact" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">11. Contact</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    If you have questions about this Privacy Policy, contact us:
                                </p>

                                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                        <p class="text-sm font-extrabold text-slate-900">Email</p>
                                        <p class="mt-2 text-sm">
                                            {{ config('mail.contact_email') ?? env('CONTACT_EMAIL', 'contact@printair.lk') }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                        <p class="text-sm font-extrabold text-slate-900">Phone / WhatsApp</p>
                                        <p class="mt-2 text-sm">+94 76 886 0175</p>
                                    </div>
                                </div>

                                <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                                    <p class="text-xs text-slate-500">
                                        Note: This Privacy Policy may be updated periodically. Changes will be reflected
                                        on this page with an updated date.
                                    </p>
                                </div>
                            </section>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </section>
</x-home-layout>
