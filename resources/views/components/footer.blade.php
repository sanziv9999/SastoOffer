<footer class="bg-slate-100 text-gray-700 pb-16 md:pb-0" x-data="{ openSections: {} }">
    <div class="container mx-auto px-6 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {{-- Logo and Social Media Section --}}
            <div>
                <div class="mb-4">
                    <div class="flex items-center gap-1">
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M7 7l3.5 3.5c.3.3.4.7.4 1.1V19c0 .6-.4 1-1 1s-1-.4-1-1v-6.6c0-.4-.1-.8-.4-1.1L5 7.8c-.5-.5-.5-1.4 0-1.9.5-.5 1.4-.5 2 0l.1.1c.5.5.5 1.4 0 1.9L7 7z"></path><path d="M12.5 2h3s2.5 0 2.5 2.5V7c0 2.5-2.5 2.5-2.5 2.5h-3c-2.5 0-2.5-2.5-2.5-2.5V4.5C10 2 12.5 2 12.5 2z"></path><path d="M15 12a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"></path><circle cx="15" cy="15" r="3"></circle></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute bottom-0 right-0 text-secondary"><line x1="19" x2="5" y1="5" y2="19"></line><circle cx="6.5" cy="6.5" r="2.5"></circle><circle cx="17.5" cy="17.5" r="2.5"></circle></svg>
                        </div>
                        <span class="text-2xl font-bold text-primary">Offer Oasis</span>
                    </div>
                </div>
                <p class="text-gray-600 mb-4">
                    Find the best deals and offers from your favorite businesses.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-500 hover:text-primary transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-primary transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path></svg>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-primary transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"></line></svg>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-primary transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect width="4" height="12" x="2" y="9"></rect><circle cx="4" cy="4" r="2"></circle></svg>
                    </a>
                </div>
            </div>

            {{-- Quick Links Section - Desktop --}}
            <div class="hidden md:block">
                <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('home') }}" class="text-gray-600 hover:text-primary transition-colors">Home</a></li>
                    <li><a href="{{ route('search') }}" class="text-gray-600 hover:text-primary transition-colors">Search Deals</a></li>
                    <li><a href="{{ route('search', ['featured' => 'true']) }}" class="text-gray-600 hover:text-primary transition-colors">Featured Deals</a></li>
                    <li><a href="{{ route('search', ['new' => 'true']) }}" class="text-gray-600 hover:text-primary transition-colors">New Arrivals</a></li>
                </ul>
            </div>

            {{-- Categories Section - Desktop --}}
            <div class="hidden md:block">
                <h3 class="text-lg font-semibold mb-4">Categories</h3>
                <ul class="space-y-2">
                    @foreach($footerMainCategories as $category)
                        <li>
                            <a href="{{ route('search', ['category' => $category->slug]) }}" class="text-gray-600 hover:text-primary transition-colors">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Support Section - Desktop --}}
            <div class="hidden md:block">
                <h3 class="text-lg font-semibold mb-4">Support</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-600 hover:text-primary transition-colors">Help Center</a></li>
                    <li><a href="{{ route('contact') }}" class="text-gray-600 hover:text-primary transition-colors">Contact Us</a></li>
                    <li><a href="{{ route('privacy') }}" class="text-gray-600 hover:text-primary transition-colors">Privacy Policy</a></li>
                    <li><a href="{{ route('terms') }}" class="text-gray-600 hover:text-primary transition-colors">Terms of Service</a></li>
                </ul>
            </div>

            {{-- Mobile Footer Accordion --}}
            <div class="md:hidden space-y-4 col-span-1">
                {{-- Quick Links Section - Mobile --}}
                <div class="border-b pb-2">
                    <button @click="openSections['quickLinks'] = !openSections['quickLinks']" class="flex w-full justify-between items-center py-2 group">
                        <h3 class="text-lg font-semibold">Quick Links</h3>
                        <div class="p-0 h-6 w-6 flex items-center justify-center text-muted-foreground group-hover:text-primary transition-colors">
                            <span class="transform transition-transform" :class="openSections['quickLinks'] ? 'rotate-180' : ''">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                            </span>
                        </div>
                    </button>
                    <div x-show="openSections['quickLinks']" x-cloak x-collapse class="space-y-2 py-2 pl-2">
                        <a href="{{ route('home') }}" class="text-gray-600 hover:text-primary transition-colors block py-1">Home</a>
                        <a href="{{ route('search') }}" class="text-gray-600 hover:text-primary transition-colors block py-1">Search Deals</a>
                        <a href="{{ route('search', ['featured' => 'true']) }}" class="text-gray-600 hover:text-primary transition-colors block py-1">Featured Deals</a>
                        <a href="{{ route('search', ['new' => 'true']) }}" class="text-gray-600 hover:text-primary transition-colors block py-1">New Arrivals</a>
                    </div>
                </div>

                {{-- Categories Section - Mobile --}}
                <div class="border-b pb-2">
                    <button @click="openSections['categories'] = !openSections['categories']" class="flex w-full justify-between items-center py-2 group">
                        <h3 class="text-lg font-semibold">Categories</h3>
                        <div class="p-0 h-6 w-6 flex items-center justify-center text-muted-foreground group-hover:text-primary transition-colors">
                            <span class="transform transition-transform" :class="openSections['categories'] ? 'rotate-180' : ''">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                            </span>
                        </div>
                    </button>
                    <div x-show="openSections['categories']" x-cloak x-collapse class="space-y-2 py-2 pl-2">
                        @foreach($footerMainCategories as $category)
                            <a href="{{ route('search', ['category' => $category->slug]) }}" class="text-gray-600 hover:text-primary transition-colors block py-1">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Support Section - Mobile --}}
                <div class="border-b pb-2">
                    <button @click="openSections['support'] = !openSections['support']" class="flex w-full justify-between items-center py-2 group">
                        <h3 class="text-lg font-semibold">Support</h3>
                        <div class="p-0 h-6 w-6 flex items-center justify-center text-muted-foreground group-hover:text-primary transition-colors">
                            <span class="transform transition-transform" :class="openSections['support'] ? 'rotate-180' : ''">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                            </span>
                        </div>
                    </button>
                    <div x-show="openSections['support']" x-cloak x-collapse class="space-y-2 py-2 pl-2">
                        <a href="#" class="text-gray-600 hover:text-primary transition-colors block py-1">Help Center</a>
                        <a href="{{ route('contact') }}" class="text-gray-600 hover:text-primary transition-colors block py-1">Contact Us</a>
                        <a href="{{ route('privacy') }}" class="text-gray-600 hover:text-primary transition-colors block py-1">Privacy Policy</a>
                        <a href="{{ route('terms') }}" class="text-gray-600 hover:text-primary transition-colors block py-1">Terms of Service</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Copyright Section --}}
        <div class="border-t border-gray-200 mt-10 pt-6 flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-500 text-sm">
                &copy; {{ date('Y') }} Offer Oasis. All rights reserved.
            </p>
            <div class="mt-4 md:mt-0">
                <ul class="flex space-x-6">
                    <li><a href="{{ route('privacy') }}" class="text-gray-500 hover:text-primary text-sm transition-colors">Privacy</a></li>
                    <li><a href="{{ route('terms') }}" class="text-gray-500 hover:text-primary text-sm transition-colors">Terms</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-primary text-sm transition-colors">Sitemap</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
