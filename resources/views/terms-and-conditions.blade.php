<x-home-layout :seo="$seo">
    <section class="bg-white">
        {{-- Hero --}}
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-7 sm:p-10">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-xs font-semibold tracking-wide text-slate-500">LEGAL</p>
                        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                            Terms &amp; Conditions
                        </h1>
                        <p class="mt-3 text-sm text-slate-600 sm:text-base">
                            These Terms govern how Printair provides quotations, accepts orders, handles design files,
                            and delivers printing services. Using our website or placing an order means you agree to
                            these Terms.
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
                    <div class="sticky top-24 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-extrabold text-slate-900">On this page</p>

                        <nav class="mt-4 space-y-2 text-sm">
                            <a href="#intro"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">1.
                                Introduction</a>
                            <a href="#quotes-orders"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">2.
                                Quotations & Orders</a>
                            <a href="#working-groups"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">3.
                                Working Groups & Pricing</a>
                            <a href="#design-files"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">4.
                                Design Files & Approval</a>
                            <a href="#production"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">5.
                                Production Standards</a>
                            <a href="#payments"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">6.
                                Payments</a>
                            <a href="#delivery"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">7.
                                Delivery & Collection</a>
                            <a href="#refunds"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">8.
                                Refunds & Reprints</a>
                            <a href="#warranty"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">9.
                                Warranty Disclaimer</a>
                            <a href="#ip"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">10.
                                Intellectual Property</a>
                            <a href="#liability"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">11.
                                Limitation of Liability</a>
                            <a href="#changes"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">12.
                                Changes to Terms</a>
                            <a href="#law"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">13.
                                Governing Law</a>
                            <a href="#contact"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">14.
                                Contact</a>
                        </nav>
                    </div>
                </aside>

                {{-- Main content --}}
                <main class="lg:col-span-8">
                    <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm sm:p-10">
                        <style>
                            .tc h2 {
                                font-weight: 900;
                                letter-spacing: -0.02em;
                                color: #0f172a;
                            }

                            .tc p,
                            .tc ul {
                                color: #475569;
                            }
                        </style>

                        <div class="tc space-y-10">
                            <section id="intro" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">1. Introduction</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    These Terms &amp; Conditions (“Terms”) apply to all quotations, orders, purchases,
                                    printing services, and website usage
                                    provided by <strong class="text-slate-900">Printair Advertising</strong>
                                    (“Printair”, “we”, “us”, “our”).
                                </p>
                                <p class="mt-3 text-sm sm:text-base">
                                    If you do not agree to these Terms, please do not use our services.
                                </p>
                            </section>

                            <section id="quotes-orders" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">2. Quotations &amp; Orders</h2>
                                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm sm:text-base">
                                    <li>Quotations are provided based on the specifications available at the time of
                                        quoting (size, quantity, material, finishing, delivery, etc.).</li>
                                    <li>An order is considered confirmed only after official acceptance by Printair (and
                                        payment/advance where applicable).</li>
                                    <li>Customer is responsible for confirming all order details before approval
                                        (spelling, sizes, quantities, finishing selections).</li>
                                    <li>Any changes after confirmation may impact price, timeline, and availability.
                                    </li>
                                </ul>

                                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                    <p class="text-sm font-extrabold text-slate-900">Timeline note</p>
                                    <p class="mt-2 text-sm">
                                        Production timelines may vary based on workload, material availability, and
                                        complexity. We’ll always communicate realistic timelines where possible.
                                    </p>
                                </div>
                            </section>

                            <section id="working-groups" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">3. Working Groups &amp; Pricing</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    Printair may offer special pricing, products, or access rules to customers under
                                    specific “Working Groups” (e.g., corporate partners).
                                </p>
                                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm sm:text-base">
                                    <li>Working Group pricing and availability are controlled by Printair and may differ
                                        from public pricing.</li>
                                    <li>Access to Working Group pricing is limited to authorized accounts.</li>
                                    <li>Misuse, sharing, or unauthorized distribution of Working Group prices may result
                                        in suspension or removal of access.</li>
                                </ul>
                            </section>

                            <section id="design-files" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">4. Design Files &amp; Customer Approval</h2>
                                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm sm:text-base">
                                    <li>Customers must provide print-ready files or request design support (if offered).
                                    </li>
                                    <li>Once a proof/preview is shared, customer approval is required before production.
                                    </li>
                                    <li>After approval, Printair is not responsible for errors that were present in the
                                        approved proof (typos, incorrect sizes, wrong images, etc.).</li>
                                </ul>

                                <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-5">
                                    <p class="text-sm font-extrabold text-slate-900">Color & screen differences</p>
                                    <p class="mt-2 text-sm text-slate-700">
                                        Screens display colors differently. Printed output may vary depending on
                                        material, ink behavior, profiles, and lighting.
                                        Minor color differences are considered normal in printing.
                                    </p>
                                </div>
                            </section>

                            <section id="production" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">5. Production Standards &amp; Variations</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    Print production can involve minor tolerances. By ordering, you accept reasonable
                                    variations such as:
                                </p>
                                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm sm:text-base">
                                    <li>Color variation between batches or materials</li>
                                    <li>Cutting tolerance / finishing tolerance within standard printing industry limits
                                    </li>
                                    <li>Material texture, shade, or supplier differences</li>
                                </ul>
                                <p class="mt-3 text-sm sm:text-base">
                                    If a strict requirement is critical (exact color matching, brand compliance, etc.),
                                    customer must inform Printair in advance.
                                    Additional steps (proofing, test prints) may apply with extra charges.
                                </p>
                            </section>

                            <section id="payments" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">6. Payments</h2>
                                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm sm:text-base">
                                    <li>Payment terms may vary by product type, customer type, or Working Group
                                        agreement.</li>
                                    <li>Some orders require an advance payment before production begins.</li>
                                    <li>Prices may exclude delivery unless explicitly stated.</li>
                                    <li>Failure to complete payment may delay or cancel order processing.</li>
                                </ul>
                            </section>

                            <section id="delivery" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">7. Delivery &amp; Collection</h2>
                                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm sm:text-base">
                                    <li>Delivery timelines are estimates and may change due to external factors (courier
                                        delays, weather, traffic).</li>
                                    <li>Risk of damage may transfer at the point of collection or delivery handover
                                        (depending on delivery method).</li>
                                    <li>Customers should inspect items upon receipt and report issues within a
                                        reasonable time.</li>
                                </ul>
                            </section>

                            <section id="refunds" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">8. Refunds, Reprints &amp; Complaints</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    Because most printed items are custom-made, refunds are typically limited. However,
                                    we will review complaints fairly.
                                </p>

                                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                    <p class="text-sm font-extrabold text-slate-900">Eligible cases may include</p>
                                    <ul class="mt-3 list-disc space-y-2 pl-5 text-sm sm:text-base">
                                        <li>Production defects clearly caused by Printair</li>
                                        <li>Incorrect product delivered compared to confirmed order</li>
                                        <li>Material/finishing mismatch caused by Printair</li>
                                    </ul>
                                </div>

                                <p class="mt-4 text-sm sm:text-base">
                                    Printair may request photos, videos, or the return of items for inspection before
                                    deciding on a reprint, partial refund, or other resolution.
                                </p>
                            </section>

                            <section id="warranty" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">9. Warranty Disclaimer</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    Some products offered by Printair may include warranty periods, while others may
                                    not. Warranty availability and conditions vary by product.
                                </p>

                                <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-5">
                                    <p class="text-sm font-extrabold text-slate-900">Final decision clause</p>
                                    <p class="mt-2 text-sm text-slate-700">
                                        Warranty eligibility, interpretation, approvals, and final outcomes are
                                        determined solely by Printair Advertising.
                                        The company’s decision is final and binding.
                                    </p>
                                </div>
                            </section>

                            <section id="ip" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">10. Intellectual Property</h2>
                                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm sm:text-base">
                                    <li>Customers confirm they have the legal right to use any content they provide
                                        (logos, images, artworks, text).</li>
                                    <li>Printair is not responsible for copyright/trademark issues arising from
                                        customer-provided materials.</li>
                                    <li>Unless agreed otherwise, Printair may display non-confidential finished work for
                                        portfolio/marketing purposes (customer can request opt-out).</li>
                                </ul>

                                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5">
                                    <p class="text-sm font-extrabold text-slate-900">Opt-out option</p>
                                    <p class="mt-2 text-sm">
                                        If your project is confidential, notify us in writing before production so we
                                        can mark it as restricted.
                                    </p>
                                </div>
                            </section>

                            <section id="liability" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">11. Limitation of Liability</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    To the maximum extent permitted by law, Printair is not liable for indirect,
                                    incidental, or consequential damages including loss of business,
                                    profit, or reputation arising from the use of our services.
                                </p>
                                <p class="mt-3 text-sm sm:text-base">
                                    Printair’s total liability (if any) will not exceed the amount paid for the relevant
                                    order, unless required by law.
                                </p>
                            </section>

                            <section id="changes" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">12. Changes to These Terms</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    We may update these Terms from time to time. Updates will be published on this page
                                    with a revised “Last updated” date.
                                    Continued use of our services indicates acceptance of the revised Terms.
                                </p>
                            </section>

                            <section id="law" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">13. Governing Law</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    These Terms are governed by the laws of Sri Lanka. Any disputes will be handled
                                    under applicable Sri Lankan legal procedures and courts.
                                </p>
                            </section>

                            <section id="contact" class="scroll-mt-24">
                                <h2 class="text-xl sm:text-2xl">14. Contact</h2>
                                <p class="mt-3 text-sm sm:text-base">
                                    For questions about these Terms, contact Printair:
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
                                        Note: This T&C may be updated periodically. Changes will be reflected
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
