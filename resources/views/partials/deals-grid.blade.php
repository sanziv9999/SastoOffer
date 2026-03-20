<div id="search-results-grid">
    {{-- Results Control - Desktop --}}
    <div class="hidden md:flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-muted-foreground">
                <span>{{ count($deals) }}</span> {{ count($deals) === 1 ? 'result' : 'results' }}
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            <select 
                x-model="sortBy" 
                @change="applyFilters"
                class="flex h-9 w-48 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            >
                @foreach($sortByOptions as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Results Grid --}}
    @if(count($deals) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($deals as $deal)
                <x-deal-card :deal="$deal" :featured="$deal['featured']" />
            @endforeach
        </div>
    @else
        <div class="flex min-h-[400px] flex-col items-center justify-center rounded-md border border-dashed p-8 text-center animate-in fade-in-50">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-muted">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-10 w-10 text-muted-foreground"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
            </div>
            <h2 class="mt-6 text-xl font-semibold">No deals found</h2>
            <p class="mb-8 mt-2 text-center text-sm font-normal leading-6 text-muted-foreground max-w-sm">
                Try adjusting your search or filter criteria
            </p>
            <button 
                @click="resetFilters"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2"
            >
                Reset All Filters
            </button>
        </div>
    @endif
</div>
