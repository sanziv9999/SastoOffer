<x-layout>
    @section('title', 'Terms of Service - SastoOffer')

    <section class="py-10 md:py-14 bg-transparent">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="mb-8 md:mb-10">
                    <p class="text-xs md:text-sm font-semibold text-primary uppercase tracking-wider">Legal</p>
                    <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-foreground mt-2">Terms of Service</h1>
                    <p class="text-sm md:text-base text-muted-foreground mt-3 max-w-3xl">
                        These Terms govern your use of SastoOffer as a customer, vendor, or visitor.
                        They explain platform rules, responsibilities, and the legal framework for using our marketplace.
                    </p>
                    <p class="text-xs text-muted-foreground mt-2">Last updated: {{ date('F j, Y') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="rounded-xl border border-border bg-white p-4">
                        <div class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1">Applies To</div>
                        <p class="text-sm text-foreground">Customers, vendors, and all users of the SastoOffer platform.</p>
                    </div>
                    <div class="rounded-xl border border-border bg-white p-4">
                        <div class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1">Marketplace Role</div>
                        <p class="text-sm text-foreground">SastoOffer acts as a platform intermediary between buyers and vendors.</p>
                    </div>
                    <div class="rounded-xl border border-border bg-white p-4">
                        <div class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1">Support</div>
                        <p class="text-sm text-foreground">support@sastooffer.com</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-6">
                    <aside class="bg-white border border-border rounded-xl p-5 h-fit lg:sticky lg:top-24">
                        <h2 class="text-sm font-bold text-foreground mb-3">On this page</h2>
                        <nav class="space-y-2 text-sm">
                            <a href="#terms-acceptance" class="block text-muted-foreground hover:text-primary transition-colors">1. Acceptance and eligibility</a>
                            <a href="#terms-accounts" class="block text-muted-foreground hover:text-primary transition-colors">2. Accounts and security</a>
                            <a href="#terms-marketplace-role" class="block text-muted-foreground hover:text-primary transition-colors">3. Marketplace role</a>
                            <a href="#terms-orders" class="block text-muted-foreground hover:text-primary transition-colors">4. Orders and vouchers</a>
                            <a href="#terms-prohibited" class="block text-muted-foreground hover:text-primary transition-colors">5. Prohibited activities</a>
                            <a href="#terms-liability" class="block text-muted-foreground hover:text-primary transition-colors">6. Liability and warranty limits</a>
                            <a href="#terms-termination" class="block text-muted-foreground hover:text-primary transition-colors">7. Suspension and termination</a>
                            <a href="#terms-changes" class="block text-muted-foreground hover:text-primary transition-colors">8. Changes to terms</a>
                        </nav>
                    </aside>

                    <div class="bg-white border border-border rounded-xl p-4 md:p-5 space-y-4">
                        <section id="terms-acceptance" class="scroll-mt-28 rounded-xl border border-border/60 bg-muted/20 p-5 md:p-6 space-y-3">
                            <h3 class="text-lg md:text-xl font-semibold text-foreground">1. Acceptance and Eligibility</h3>
                            <p class="text-sm md:text-base text-muted-foreground leading-7">
                                By accessing or using SastoOffer, you agree to these Terms.
                                You must be legally capable of entering a binding agreement under applicable law.
                            </p>
                        </section>

                        <section id="terms-accounts" class="scroll-mt-28 rounded-xl border border-border/60 bg-muted/20 p-5 md:p-6 space-y-3">
                            <h3 class="text-lg md:text-xl font-semibold text-foreground">2. Accounts and Security</h3>
                            <ul class="list-disc pl-8 ml-1 text-sm md:text-base text-muted-foreground leading-8 space-y-3">
                                <li>Users are responsible for account credentials and account activity.</li>
                                <li>Information provided must be accurate and kept current.</li>
                                <li>We may restrict access for suspicious, abusive, or fraudulent behavior.</li>
                            </ul>
                        </section>

                        <section id="terms-marketplace-role" class="scroll-mt-28 rounded-xl border border-border/60 bg-muted/20 p-5 md:p-6 space-y-3">
                            <h3 class="text-lg md:text-xl font-semibold text-foreground">3. Marketplace Role</h3>
                            <p class="text-sm md:text-base text-muted-foreground leading-7">
                                SastoOffer provides technology and listing infrastructure for vendors and buyers.
                                Vendors are responsible for offer details, fulfillment quality, and redemption terms.
                            </p>
                        </section>

                        <section id="terms-orders" class="scroll-mt-28 rounded-xl border border-border/60 bg-muted/20 p-5 md:p-6 space-y-3">
                            <h3 class="text-lg md:text-xl font-semibold text-foreground">4. Orders, Payments, and Vouchers</h3>
                            <ul class="list-disc pl-8 ml-1 text-sm md:text-base text-muted-foreground leading-8 space-y-3">
                                <li>Offer availability and pricing may change without prior notice.</li>
                                <li>Voucher use is subject to vendor-specific validity and redemption conditions.</li>
                                <li>Fraudulent orders or voucher misuse may be cancelled or blocked.</li>
                            </ul>
                        </section>

                        <section id="terms-prohibited" class="scroll-mt-28 rounded-xl border border-border/60 bg-muted/20 p-5 md:p-6 space-y-3">
                            <h3 class="text-lg md:text-xl font-semibold text-foreground">5. Prohibited Activities</h3>
                            <p class="text-sm md:text-base text-muted-foreground leading-7">
                                Users must not attempt unauthorized access, abuse platform features, manipulate pricing/reviews,
                                or engage in unlawful conduct through SastoOffer.
                            </p>
                        </section>

                        <section id="terms-liability" class="scroll-mt-28 rounded-xl border border-border/60 bg-muted/20 p-5 md:p-6 space-y-3">
                            <h3 class="text-lg md:text-xl font-semibold text-foreground">6. Liability and Warranty Limits</h3>
                            <p class="text-sm md:text-base text-muted-foreground leading-7">
                                To the maximum extent permitted by law, SastoOffer provides the platform on an
                                "as available" basis and disclaims indirect or consequential damages.
                            </p>
                        </section>

                        <section id="terms-termination" class="scroll-mt-28 rounded-xl border border-border/60 bg-muted/20 p-5 md:p-6 space-y-3">
                            <h3 class="text-lg md:text-xl font-semibold text-foreground">7. Suspension and Termination</h3>
                            <p class="text-sm md:text-base text-muted-foreground leading-7">
                                We may suspend or terminate accounts that violate these Terms, compromise platform security,
                                or create legal or operational risk.
                            </p>
                        </section>

                        <section id="terms-changes" class="scroll-mt-28 rounded-xl border border-border/60 bg-muted/20 p-5 md:p-6 space-y-3">
                            <h3 class="text-lg md:text-xl font-semibold text-foreground">8. Changes to Terms</h3>
                            <p class="text-sm md:text-base text-muted-foreground leading-7">
                                We may revise these Terms periodically. Continued use of SastoOffer after updates
                                means acceptance of the revised Terms.
                            </p>
                        </section>
                </div>
            </div>
        </div>
    </section>
</x-layout>
