<x-layout>
    @section('title', 'SastoOffer - Best Deals & Offers in Your City')

    <div class="flex flex-col">
        {{-- Hero Banner --}}
        <x-sliding-banner />

        {{-- Featured Deals Grid --}}
        <x-featured-products />

        {{-- Popular Categories / Multi Ads --}}
        <x-multi-ads-banner />

        {{-- Recent Offers Horizontal Scroll --}}
        <x-recent-offers />

        {{-- Popular Brands --}}
        <x-brand-logos />
    </div>
</x-layout>
