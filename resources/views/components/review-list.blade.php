@props(['reviews', 'userReview' => null, 'reviewableType', 'reviewableId'])

@php
    $reviewsList = collect($reviews ?? []);
    $avgRating = $reviewsList->count() > 0 ? round($reviewsList->avg('rating'), 1) : 0;
    $ratingCounts = [];
    for ($i = 5; $i >= 1; $i--) { $ratingCounts[$i] = $reviewsList->where('rating', $i)->count(); }
@endphp

<div x-data="{
    filterRating: 'all',
    sortBy: 'newest',
    get filteredReviews() {
        let reviews = {{ Js::from($reviewsList->values()) }};
        if (this.filterRating !== 'all') {
            reviews = reviews.filter(r => r.rating === parseInt(this.filterRating));
        }
        if (this.sortBy === 'newest') reviews.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
        else if (this.sortBy === 'oldest') reviews.sort((a, b) => new Date(a.createdAt) - new Date(b.createdAt));
        else if (this.sortBy === 'highest') reviews.sort((a, b) => b.rating - a.rating);
        else if (this.sortBy === 'lowest') reviews.sort((a, b) => a.rating - b.rating);
        return reviews;
    }
}">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        {{-- Summary --}}
        <div class="bg-card rounded-xl border p-6 text-center">
            <div class="text-4xl font-bold text-primary mb-1">{{ $avgRating > 0 ? $avgRating : '-' }}</div>
            <div class="flex justify-center gap-0.5 mb-2">
                @for($i = 1; $i <= 5; $i++)
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="{{ $i <= round($avgRating) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" class="h-4 w-4 {{ $i <= round($avgRating) ? 'text-yellow-500' : 'text-gray-300' }}"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                @endfor
            </div>
            <p class="text-sm text-muted-foreground">Based on {{ $reviewsList->count() }} {{ Str::plural('review', $reviewsList->count()) }}</p>

            <div class="mt-4 space-y-1.5">
                @for($i = 5; $i >= 1; $i--)
                    @php $pct = $reviewsList->count() > 0 ? round(($ratingCounts[$i] / $reviewsList->count()) * 100) : 0; @endphp
                    <button
                        @click="filterRating = filterRating === '{{ $i }}' ? 'all' : '{{ $i }}'"
                        class="flex items-center gap-2 text-xs w-full rounded-md px-1 py-0.5 transition-colors"
                        :class="filterRating === '{{ $i }}' ? 'bg-primary/10 ring-1 ring-primary/30' : 'hover:bg-muted'"
                    >
                        <span class="w-3 font-medium">{{ $i }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" class="h-3 w-3 text-yellow-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <div class="flex-1 h-2 bg-muted rounded-full overflow-hidden">
                            <div class="h-full bg-yellow-500 rounded-full" style="width:{{ $pct }}%"></div>
                        </div>
                        <span class="w-6 text-right text-muted-foreground">{{ $ratingCounts[$i] }}</span>
                    </button>
                @endfor
            </div>
        </div>

        {{-- Write Review Form --}}
        <div class="lg:col-span-2 bg-card rounded-xl border p-6">
            @auth
                @if($userReview)
                    <h3 class="font-semibold mb-3 text-lg">Update Your Review</h3>
                    <form method="POST" action="{{ route('reviews.update', $userReview['id']) }}" x-data="{ rating: {{ $userReview['rating'] }}, hoveredStar: 0 }" class="space-y-4">
                        @csrf
                        @method('PUT')
                @else
                    <h3 class="font-semibold mb-3 text-lg">{{ $reviewableType === 'vendor' ? 'Rate This Vendor' : 'Write a Review' }}</h3>
                    <form method="POST" action="{{ route('reviews.store') }}" x-data="{ rating: 0, hoveredStar: 0 }" class="space-y-4">
                        @csrf
                        <input type="hidden" name="reviewable_type" value="{{ $reviewableType }}">
                        <input type="hidden" name="reviewable_id" value="{{ $reviewableId }}">
                @endif
                    @if ($errors->any())
                        <div class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Rating</label>
                        <div class="flex gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button"
                                    @click="rating = {{ $i }}"
                                    @mouseenter="hoveredStar = {{ $i }}"
                                    @mouseleave="hoveredStar = 0"
                                    class="focus:outline-none transition-transform hover:scale-110">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                                        :fill="{{ $i }} <= (hoveredStar || rating) ? 'currentColor' : 'none'"
                                        stroke="currentColor" stroke-width="2"
                                        :class="{{ $i }} <= (hoveredStar || rating) ? 'text-yellow-500' : 'text-gray-300'"
                                        class="h-7 w-7 cursor-pointer"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" :value="rating">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Comment</label>
                        <textarea name="comment" rows="3" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring" placeholder="{{ $reviewableType === 'vendor' ? 'How was your experience with this vendor?' : 'Share your experience...' }}">{{ ($userReview['comment'] ?? '') }}</textarea>
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="submit"
                            :disabled="rating < 1"
                            class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2 disabled:pointer-events-none disabled:opacity-50"
                        >
                            {{ $userReview ? 'Update Review' : 'Submit Review' }}
                        </button>
                    </div>
                </form>

                @if($userReview)
                    <form method="POST" action="{{ route('reviews.destroy', $userReview['id']) }}" class="mt-2 inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Delete your review?')" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 text-destructive">
                            Delete
                        </button>
                    </form>
                @endif
            @else
                <div class="text-center py-8">
                    <p class="text-muted-foreground mb-3">Please log in to write a review.</p>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2">Log In</a>
                </div>
            @endauth
        </div>
    </div>

    {{-- Filter / Sort Bar --}}
    @if($reviewsList->count() > 0)
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-sm text-muted-foreground" x-text="'Showing ' + filteredReviews.length + ' review' + (filteredReviews.length !== 1 ? 's' : '')"></span>
                <template x-if="filterRating !== 'all'">
                    <button @click="filterRating = 'all'" class="text-xs text-primary hover:underline font-medium">Clear filter</button>
                </template>
            </div>
            <select
                x-model="sortBy"
                class="rounded-md border border-input bg-background px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
            >
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="highest">Highest Rating</option>
                <option value="lowest">Lowest Rating</option>
            </select>
        </div>
    @endif

    {{-- Review Cards (Alpine-rendered) --}}
    @if($reviewsList->count() > 0)
        <div class="space-y-4">
            <template x-for="rv in filteredReviews" :key="rv.id">
                <div class="bg-card rounded-xl border p-5">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 rounded-full bg-muted flex items-center justify-center font-bold text-sm text-muted-foreground" x-text="rv.userName.charAt(0).toUpperCase()"></div>
                            <div>
                                <p class="font-medium text-sm" x-text="rv.userName"></p>
                                <div class="flex items-center gap-0.5">
                                    <template x-for="s in 5" :key="s">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                            :fill="s <= rv.rating ? 'currentColor' : 'none'"
                                            stroke="currentColor" stroke-width="2"
                                            :class="s <= rv.rating ? 'text-yellow-500' : 'text-gray-300'"
                                            class="h-3 w-3"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <span class="text-xs text-muted-foreground" x-text="new Date(rv.createdAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })"></span>
                    </div>
                    <template x-if="rv.comment">
                        <p class="text-sm text-foreground/80 leading-relaxed mt-2" x-text="rv.comment"></p>
                    </template>
                    <template x-if="rv.vendorReply">
                        <div class="mt-3 bg-primary/5 border border-primary/10 rounded-lg p-3">
                            <p class="text-xs font-semibold text-primary mb-1">Vendor Reply</p>
                            <p class="text-sm italic">"<span x-text="rv.vendorReply"></span>"</p>
                            <template x-if="rv.vendorRepliedAt">
                                <span class="text-[10px] text-muted-foreground mt-1 block" x-text="new Date(rv.vendorRepliedAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })"></span>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="filteredReviews.length === 0">
                <div class="py-10 text-center border-2 border-dashed rounded-xl">
                    <p class="text-muted-foreground">No reviews match the selected filter.</p>
                    <button @click="filterRating = 'all'" class="text-primary hover:underline text-sm font-medium mt-2">Show all reviews</button>
                </div>
            </template>
        </div>
    @else
        <div class="py-12 text-center border-2 border-dashed rounded-xl">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto mb-3 text-muted-foreground/30"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <p class="text-muted-foreground">No reviews yet. Be the first to review!</p>
        </div>
    @endif
</div>
