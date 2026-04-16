<x-layout>
    @section('title', 'Privacy Policy - SastoOffer')

    <section class="py-10 md:py-14 bg-transparent">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="mb-8 md:mb-10">
                    <p class="text-xs md:text-sm font-semibold text-primary uppercase tracking-wider">Legal</p>
                    <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-foreground mt-2">Privacy Policy</h1>
                    <p class="text-sm md:text-base text-muted-foreground mt-3 max-w-3xl">
                        This Privacy Policy explains what information SastoOffer collects, how we use it, when we share it,
                        and the choices available to users. We use clear language so customers and vendors understand how data is handled.
                    </p>
                    <p class="text-xs text-muted-foreground mt-2">Last updated: {{ date('F j, Y') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="rounded-xl border border-border bg-white p-4">
                        <div class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1">Scope</div>
                        <p class="text-sm text-foreground">Applies to all website visitors, customers, and vendors using SastoOffer.</p>
                    </div>
                    <div class="rounded-xl border border-border bg-white p-4">
                        <div class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1">Contact</div>
                        <p class="text-sm text-foreground">support@sastooffer.com</p>
                    </div>
                    <div class="rounded-xl border border-border bg-white p-4">
                        <div class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1">Update Cycle</div>
                        <p class="text-sm text-foreground">Reviewed regularly and updated as platform features evolve.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-6">
                    <aside class="bg-white border border-border rounded-xl p-5 h-fit lg:sticky lg:top-24">
                        <h2 class="text-sm font-bold text-foreground mb-3">On this page</h2>
                        <nav class="space-y-2 text-sm">
                            <a href="#privacy-collect" class="block text-muted-foreground hover:text-primary transition-colors">1. Data we collect</a>
                            <a href="#privacy-use" class="block text-muted-foreground hover:text-primary transition-colors">2. How we use data</a>
                            <a href="#privacy-share" class="block text-muted-foreground hover:text-primary transition-colors">3. Data sharing</a>
                            <a href="#privacy-cookies" class="block text-muted-foreground hover:text-primary transition-colors">4. Cookies and analytics</a>
                            <a href="#privacy-security" class="block text-muted-foreground hover:text-primary transition-colors">5. Security and retention</a>
                            <a href="#privacy-rights" class="block text-muted-foreground hover:text-primary transition-colors">6. Your rights and choices</a>
                            <a href="#privacy-contact" class="block text-muted-foreground hover:text-primary transition-colors">7. Contact and updates</a>
                        </nav>
                    </aside>

                    <div class="bg-white border border-border rounded-xl p-4 md:p-5 space-y-4">
                        <section id="privacy-collect" class="scroll-mt-28 rounded-xl border border-border/60 bg-muted/20 p-5 md:p-6 space-y-3">
                            <h3 class="text-lg md:text-xl font-semibold text-foreground">1. Data We Collect</h3>
                            <p class="text-sm md:text-base text-muted-foreground leading-7">
                                We collect information directly from users and through platform usage. This may include:
                            </p>
                            <ul class="list-disc pl-8 ml-1 text-sm md:text-base text-muted-foreground leading-8 space-y-3">
                                <li>Identity and account details (name, email, password hash, profile settings).</li>
                                <li>Order and voucher records (purchase details, redemption status, support history).</li>
                                <li>Technical information (device type, browser, IP-related logs, session behavior).</li>
                            </ul>
                        </section>

                        <section id="privacy-use" class="scroll-mt-28 rounded-xl border border-border/60 bg-muted/20 p-5 md:p-6 space-y-3">
                            <h3 class="text-lg md:text-xl font-semibold text-foreground">2. How We Use Data</h3>
                            <p class="text-sm md:text-base text-muted-foreground leading-7">
                                We use personal data to operate the marketplace, process transactions, prevent fraud,
                                provide customer support, improve platform performance, and communicate important account updates.
                            </p>
                        </section>

                        <section id="privacy-share" class="scroll-mt-28 rounded-xl border border-border/60 bg-muted/20 p-5 md:p-6 space-y-3">
                            <h3 class="text-lg md:text-xl font-semibold text-foreground">3. Data Sharing</h3>
                            <p class="text-sm md:text-base text-muted-foreground leading-7">
                                We share only the minimum necessary information with vendors and service providers to fulfill orders,
                                verify redemptions, process payments, and run platform infrastructure. We do not sell personal data.
                            </p>
                        </section>

                        <section id="privacy-cookies" class="scroll-mt-28 rounded-xl border border-border/60 bg-muted/20 p-5 md:p-6 space-y-3">
                            <h3 class="text-lg md:text-xl font-semibold text-foreground">4. Cookies and Analytics</h3>
                            <p class="text-sm md:text-base text-muted-foreground leading-7">
                                We use cookies and similar technologies for core functionality (login, cart, session),
                                performance monitoring, and user experience improvements.
                            </p>
                        </section>

                        <section id="privacy-security" class="scroll-mt-28 rounded-xl border border-border/60 bg-muted/20 p-5 md:p-6 space-y-3">
                            <h3 class="text-lg md:text-xl font-semibold text-foreground">5. Security and Retention</h3>
                            <p class="text-sm md:text-base text-muted-foreground leading-7">
                                We apply reasonable security controls and retain data only as long as necessary for legal,
                                operational, and contractual purposes.
                            </p>
                        </section>

                        <section id="privacy-rights" class="scroll-mt-28 rounded-xl border border-border/60 bg-muted/20 p-5 md:p-6 space-y-3">
                            <h3 class="text-lg md:text-xl font-semibold text-foreground">6. Your Rights and Choices</h3>
                            <p class="text-sm md:text-base text-muted-foreground leading-7">
                                You may request access, correction, or deletion of personal data, subject to legal limitations.
                                You can also update profile details directly from account settings where available.
                            </p>
                        </section>

                        <section id="privacy-contact" class="scroll-mt-28 rounded-xl border border-border/60 bg-muted/20 p-5 md:p-6 space-y-3">
                            <h3 class="text-lg md:text-xl font-semibold text-foreground">7. Contact and Policy Updates</h3>
                            <p class="text-sm md:text-base text-muted-foreground leading-7">
                                For privacy-related questions, contact
                                <a href="mailto:support@sastooffer.com" class="text-primary hover:underline">support@sastooffer.com</a>.
                                We may revise this policy when features or legal requirements change.
                            </p>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layout>
