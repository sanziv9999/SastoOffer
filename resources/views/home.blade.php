<x-layout>
    @section('title', 'SastoOffer - Best Deals & Offers in Your City')

    <div class="flex flex-col">
        {{-- Hero Banner --}}
        <x-sliding-banner />

        {{-- Featured Deals Grid --}}
        <x-featured-products :featuredDeals="$featuredDeals" />

        {{-- Popular Categories / Multi Ads --}}
        <x-multi-ads-banner :categories="$categories" />

        {{-- Recent Offers Horizontal Scroll --}}
        <x-recent-offers :recentOffers="$recentOffers" />

        {{-- Popular Brands --}}
        <x-brand-logos :vendors="$topRatedVendors" />
    </div>
</x-layout>
