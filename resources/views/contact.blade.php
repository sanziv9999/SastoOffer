<x-layout>
    @section('title', 'Contact Us - SastoOffer')

    <section class="py-10 md:py-14">
        <div class="container mx-auto px-4">
            <div class="max-w-5xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-foreground">Contact Us</h1>
                    <p class="text-sm md:text-base text-muted-foreground mt-2">
                        Have questions, feedback, or partnership inquiries? We would love to hear from you.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-1 space-y-4">
                        <div class="bg-white border border-border rounded-xl p-5">
                            <h2 class="text-lg font-semibold mb-3">Support</h2>
                            <p class="text-sm text-muted-foreground mb-2">Email</p>
                            <a href="mailto:support@sastooffer.com" class="text-sm font-medium text-primary hover:underline">
                                support@sastooffer.com
                            </a>
                        </div>

                        <div class="bg-white border border-border rounded-xl p-5">
                            <h2 class="text-lg font-semibold mb-3">Business Inquiries</h2>
                            <p class="text-sm text-muted-foreground mb-2">Email</p>
                            <a href="mailto:business@sastooffer.com" class="text-sm font-medium text-primary hover:underline">
                                business@sastooffer.com
                            </a>
                        </div>

                        <div class="bg-white border border-border rounded-xl p-5">
                            <h2 class="text-lg font-semibold mb-3">Office Hours</h2>
                            <p class="text-sm text-muted-foreground">Sunday - Friday</p>
                            <p class="text-sm text-muted-foreground">10:00 AM - 6:00 PM (NPT)</p>
                        </div>
                    </div>

                    <div class="lg:col-span-2 bg-white border border-border rounded-xl p-6 md:p-8">
                        <h2 class="text-xl font-semibold mb-5">Send us a message</h2>
                        <form class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1.5">Full Name</label>
                                    <input
                                        type="text"
                                        class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:ring-2 focus:ring-primary/30"
                                        placeholder="Your name"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1.5">Email</label>
                                    <input
                                        type="email"
                                        class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:ring-2 focus:ring-primary/30"
                                        placeholder="you@example.com"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1.5">Subject</label>
                                <input
                                    type="text"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:ring-2 focus:ring-primary/30"
                                    placeholder="How can we help?"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1.5">Message</label>
                                <textarea
                                    rows="6"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary/30"
                                    placeholder="Write your message..."
                                ></textarea>
                            </div>

                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-5"
                            >
                                Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layout>
