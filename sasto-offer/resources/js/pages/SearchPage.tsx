
import { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import { Filter, Search as SearchIcon, SlidersHorizontal, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import DealCard from '@/components/DealCard';
import { Separator } from '@/components/ui/separator';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '@/components/ui/accordion';
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
  SheetFooter,
} from '@/components/ui/sheet';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Slider } from '@/components/ui/slider';
import { deals, categories } from '@/data/mockData';
import { Deal } from '@/types';

const sortOptions = [
  { value: 'relevance', label: 'Relevance' },
  { value: 'newest', label: 'Newest' },
  { value: 'priceAsc', label: 'Price: Low to High' },
  { value: 'priceDesc', label: 'Price: High to Low' },
  { value: 'discountDesc', label: 'Biggest Discount' },
  { value: 'endingSoon', label: 'Ending Soon' },
];

type DealType = 'all' | 'percentage' | 'fixed' | 'bogo' | 'bundle' | 'flash';

const SearchPage = () => {
  const [searchParams, setSearchParams] = useSearchParams();
  const [filteredDeals, setFilteredDeals] = useState<Deal[]>([]);
  const [loading, setLoading] = useState(true);
  
  // Get query parameters
  const query = searchParams.get('q') || '';
  const categorySlug = searchParams.get('category') || 'all';
  const sortBy = searchParams.get('sort') || 'relevance';
  const featured = searchParams.get('featured') === 'true';
  const dealType = searchParams.get('type') as DealType || 'all';
  const minPrice = Number(searchParams.get('minPrice') || 0);
  const maxPrice = Number(searchParams.get('maxPrice') || 1000);
  
  // Local state for form values
  const [searchQuery, setSearchQuery] = useState(query);
  const [selectedCategory, setSelectedCategory] = useState(categorySlug);
  const [selectedSortBy, setSelectedSortBy] = useState(sortBy);
  const [priceRange, setPriceRange] = useState<[number, number]>([minPrice, maxPrice]);
  const [selectedDealType, setSelectedDealType] = useState<DealType>(dealType);
  const [isFeatured, setIsFeatured] = useState(featured);
  
  // Filter and sort deals
  useEffect(() => {
    setLoading(true);
    
    // Simulate API call delay
    const timer = setTimeout(() => {
      let results = [...deals];
      
      // Filter by search query
      if (query) {
        results = results.filter(deal => 
          deal.title.toLowerCase().includes(query.toLowerCase()) ||
          deal.description.toLowerCase().includes(query.toLowerCase())
        );
      }
      
      // Filter by category
      if (categorySlug && categorySlug !== 'all') {
        const category = categories.find(c => c.slug === categorySlug);
        if (category) {
          results = results.filter(deal => deal.categoryId === category.id);
        }
      }
      
      // Filter by featured
      if (featured) {
        results = results.filter(deal => deal.featured);
      }
      
      // Filter by deal type
      if (dealType !== 'all') {
        results = results.filter(deal => deal.type === dealType);
      }
      
      // Filter by price range
      results = results.filter(deal => 
        deal.discountedPrice >= minPrice && deal.discountedPrice <= maxPrice
      );
      
      // Sort results
      switch (sortBy) {
        case 'newest':
          results.sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime());
          break;
        case 'priceAsc':
          results.sort((a, b) => a.discountedPrice - b.discountedPrice);
          break;
        case 'priceDesc':
          results.sort((a, b) => b.discountedPrice - a.discountedPrice);
          break;
        case 'discountDesc':
          results.sort((a, b) => {
            const discountA = a.discountPercentage || 
              Math.round(((a.originalPrice - a.discountedPrice) / a.originalPrice) * 100);
            const discountB = b.discountPercentage || 
              Math.round(((b.originalPrice - b.discountedPrice) / b.originalPrice) * 100);
            return discountB - discountA;
          });
          break;
        case 'endingSoon':
          results.sort((a, b) => new Date(a.endDate).getTime() - new Date(b.endDate).getTime());
          break;
        // default is relevance
      }
      
      setFilteredDeals(results);
      setLoading(false);
    }, 500);
    
    return () => clearTimeout(timer);
  }, [query, categorySlug, sortBy, featured, dealType, minPrice, maxPrice]);
  
  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    
    const newParams = new URLSearchParams(searchParams);
    newParams.set('q', searchQuery);
    setSearchParams(newParams);
  };
  
  const applyFilters = () => {
    const newParams = new URLSearchParams();
    
    if (searchQuery) newParams.set('q', searchQuery);
    if (selectedCategory !== 'all') newParams.set('category', selectedCategory);
    if (selectedSortBy !== 'relevance') newParams.set('sort', selectedSortBy);
    if (isFeatured) newParams.set('featured', 'true');
    if (selectedDealType !== 'all') newParams.set('type', selectedDealType);
    if (priceRange[0] > 0) newParams.set('minPrice', priceRange[0].toString());
    if (priceRange[1] < 1000) newParams.set('maxPrice', priceRange[1].toString());
    
    setSearchParams(newParams);
  };
  
  const resetFilters = () => {
    setSearchQuery('');
    setSelectedCategory('all');
    setSelectedSortBy('relevance');
    setIsFeatured(false);
    setSelectedDealType('all');
    setPriceRange([0, 1000]);
    
    setSearchParams({});
  };
  
  const handleSortChange = (value: string) => {
    setSelectedSortBy(value);
    
    const newParams = new URLSearchParams(searchParams);
    if (value === 'relevance') {
      newParams.delete('sort');
    } else {
      newParams.set('sort', value);
    }
    setSearchParams(newParams);
  };
  
  const hasActiveFilters = query || categorySlug !== 'all' || sortBy !== 'relevance' || 
    featured || dealType !== 'all' || minPrice > 0 || maxPrice < 1000;
  
  return (
    <div className="container py-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl md:text-3xl font-bold">
          {query ? `Search results for "${query}"` : 'All Deals'}
        </h1>
        
        {hasActiveFilters && (
          <Button variant="ghost" onClick={resetFilters} className="hidden md:flex items-center gap-2">
            <X className="h-4 w-4" />
            Clear Filters
          </Button>
        )}
      </div>
      
      <div className="grid grid-cols-1 md:grid-cols-[250px_1fr] gap-8">
        {/* Sidebar - Desktop Filter */}
        <div className="hidden md:block">
          <div className="bg-card shadow-sm rounded-lg p-5 sticky top-20">
            <div className="mb-4">
              <h3 className="font-medium mb-2">Search</h3>
              <form onSubmit={handleSearch} className="flex">
                <Input
                  type="search"
                  placeholder="Search deals..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="rounded-r-none"
                />
                <Button type="submit" size="icon" className="rounded-l-none">
                  <SearchIcon className="h-4 w-4" />
                </Button>
              </form>
            </div>
            
            <Separator className="my-4" />
            
            <Accordion type="multiple" defaultValue={['category', 'price', 'type', 'options']}>
              <AccordionItem value="category">
                <AccordionTrigger>Categories</AccordionTrigger>
                <AccordionContent>
                  <div className="space-y-2">
                    <div className="flex items-center">
                      <input
                        type="radio"
                        id="all-categories"
                        name="category"
                        value="all"
                        checked={selectedCategory === 'all'}
                        onChange={() => setSelectedCategory('all')}
                        className="mr-2"
                      />
                      <Label htmlFor="all-categories">All Categories</Label>
                    </div>
                    {categories.map(category => (
                      <div key={category.id} className="flex items-center">
                        <input
                          type="radio"
                          id={`category-${category.id}`}
                          name="category"
                          value={category.slug}
                          checked={selectedCategory === category.slug}
                          onChange={() => setSelectedCategory(category.slug)}
                          className="mr-2"
                        />
                        <Label htmlFor={`category-${category.id}`}>{category.name}</Label>
                      </div>
                    ))}
                  </div>
                </AccordionContent>
              </AccordionItem>
              
              <AccordionItem value="price">
                <AccordionTrigger>Price Range</AccordionTrigger>
                <AccordionContent>
                  <div className="space-y-4">
                    <Slider
                      min={0}
                      max={1000}
                      step={10}
                      value={priceRange}
                      onValueChange={(value) => setPriceRange(value as [number, number])}
                      className="mt-6"
                    />
                    <div className="flex items-center justify-between">
                      <div className="bg-muted px-2 py-1 rounded">
                        ${priceRange[0]}
                      </div>
                      <div className="bg-muted px-2 py-1 rounded">
                        ${priceRange[1]}
                      </div>
                    </div>
                  </div>
                </AccordionContent>
              </AccordionItem>
              
              <AccordionItem value="type">
                <AccordionTrigger>Deal Type</AccordionTrigger>
                <AccordionContent>
                  <div className="space-y-2">
                    <div className="flex items-center">
                      <input
                        type="radio"
                        id="all-types"
                        name="dealType"
                        value="all"
                        checked={selectedDealType === 'all'}
                        onChange={() => setSelectedDealType('all')}
                        className="mr-2"
                      />
                      <Label htmlFor="all-types">All Types</Label>
                    </div>
                    <div className="flex items-center">
                      <input
                        type="radio"
                        id="percentage"
                        name="dealType"
                        value="percentage"
                        checked={selectedDealType === 'percentage'}
                        onChange={() => setSelectedDealType('percentage')}
                        className="mr-2"
                      />
                      <Label htmlFor="percentage">Percentage Off</Label>
                    </div>
                    <div className="flex items-center">
                      <input
                        type="radio"
                        id="fixed"
                        name="dealType"
                        value="fixed"
                        checked={selectedDealType === 'fixed'}
                        onChange={() => setSelectedDealType('fixed')}
                        className="mr-2"
                      />
                      <Label htmlFor="fixed">Fixed Price</Label>
                    </div>
                    <div className="flex items-center">
                      <input
                        type="radio"
                        id="bogo"
                        name="dealType"
                        value="bogo"
                        checked={selectedDealType === 'bogo'}
                        onChange={() => setSelectedDealType('bogo')}
                        className="mr-2"
                      />
                      <Label htmlFor="bogo">Buy One Get One</Label>
                    </div>
                    <div className="flex items-center">
                      <input
                        type="radio"
                        id="bundle"
                        name="dealType"
                        value="bundle"
                        checked={selectedDealType === 'bundle'}
                        onChange={() => setSelectedDealType('bundle')}
                        className="mr-2"
                      />
                      <Label htmlFor="bundle">Bundle Deals</Label>
                    </div>
                  </div>
                </AccordionContent>
              </AccordionItem>
              
              <AccordionItem value="options">
                <AccordionTrigger>Options</AccordionTrigger>
                <AccordionContent>
                  <div className="space-y-2">
                    <div className="flex items-center">
                      <Checkbox
                        id="featured-only"
                        checked={isFeatured}
                        onCheckedChange={(checked) => setIsFeatured(!!checked)}
                        className="mr-2"
                      />
                      <Label htmlFor="featured-only">Featured Deals Only</Label>
                    </div>
                  </div>
                </AccordionContent>
              </AccordionItem>
            </Accordion>
            
            <div className="flex flex-col gap-2 mt-6">
              <Button onClick={applyFilters}>Apply Filters</Button>
              {hasActiveFilters && (
                <Button variant="outline" onClick={resetFilters}>
                  Reset Filters
                </Button>
              )}
            </div>
          </div>
        </div>
        
        {/* Main Content */}
        <div>
          {/* Mobile Search & Filter Controls */}
          <div className="md:hidden mb-4 space-y-4">
            <form onSubmit={handleSearch} className="flex">
              <Input
                type="search"
                placeholder="Search deals..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="rounded-r-none"
              />
              <Button type="submit" size="icon" className="rounded-l-none">
                <SearchIcon className="h-4 w-4" />
              </Button>
            </form>
            
            <div className="flex gap-2">
              <Sheet>
                <SheetTrigger asChild>
                  <Button variant="outline" className="flex-1">
                    <Filter className="mr-2 h-4 w-4" />
                    Filters
                  </Button>
                </SheetTrigger>
                <SheetContent side="left">
                  <SheetHeader>
                    <SheetTitle>Filters</SheetTitle>
                    <SheetDescription>
                      Narrow down deals to find exactly what you're looking for.
                    </SheetDescription>
                  </SheetHeader>
                  
                  <div className="py-4 space-y-6">
                    <div>
                      <h3 className="font-medium mb-2">Categories</h3>
                      <div className="space-y-2">
                        <div className="flex items-center">
                          <input
                            type="radio"
                            id="m-all-categories"
                            name="m-category"
                            value="all"
                            checked={selectedCategory === 'all'}
                            onChange={() => setSelectedCategory('all')}
                            className="mr-2"
                          />
                          <Label htmlFor="m-all-categories">All Categories</Label>
                        </div>
                        {categories.map(category => (
                          <div key={`m-${category.id}`} className="flex items-center">
                            <input
                              type="radio"
                              id={`m-category-${category.id}`}
                              name="m-category"
                              value={category.slug}
                              checked={selectedCategory === category.slug}
                              onChange={() => setSelectedCategory(category.slug)}
                              className="mr-2"
                            />
                            <Label htmlFor={`m-category-${category.id}`}>{category.name}</Label>
                          </div>
                        ))}
                      </div>
                    </div>
                    
                    <Separator />
                    
                    <div>
                      <h3 className="font-medium mb-2">Price Range</h3>
                      <div className="space-y-4">
                        <Slider
                          min={0}
                          max={1000}
                          step={10}
                          value={priceRange}
                          onValueChange={(value) => setPriceRange(value as [number, number])}
                          className="mt-6"
                        />
                        <div className="flex items-center justify-between">
                          <div className="bg-muted px-2 py-1 rounded">
                            ${priceRange[0]}
                          </div>
                          <div className="bg-muted px-2 py-1 rounded">
                            ${priceRange[1]}
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <Separator />
                    
                    <div>
                      <h3 className="font-medium mb-2">Deal Type</h3>
                      <div className="space-y-2">
                        <div className="flex items-center">
                          <input
                            type="radio"
                            id="m-all-types"
                            name="m-dealType"
                            value="all"
                            checked={selectedDealType === 'all'}
                            onChange={() => setSelectedDealType('all')}
                            className="mr-2"
                          />
                          <Label htmlFor="m-all-types">All Types</Label>
                        </div>
                        <div className="flex items-center">
                          <input
                            type="radio"
                            id="m-percentage"
                            name="m-dealType"
                            value="percentage"
                            checked={selectedDealType === 'percentage'}
                            onChange={() => setSelectedDealType('percentage')}
                            className="mr-2"
                          />
                          <Label htmlFor="m-percentage">Percentage Off</Label>
                        </div>
                        <div className="flex items-center">
                          <input
                            type="radio"
                            id="m-fixed"
                            name="m-dealType"
                            value="fixed"
                            checked={selectedDealType === 'fixed'}
                            onChange={() => setSelectedDealType('fixed')}
                            className="mr-2"
                          />
                          <Label htmlFor="m-fixed">Fixed Price</Label>
                        </div>
                        <div className="flex items-center">
                          <input
                            type="radio"
                            id="m-bogo"
                            name="m-dealType"
                            value="bogo"
                            checked={selectedDealType === 'bogo'}
                            onChange={() => setSelectedDealType('bogo')}
                            className="mr-2"
                          />
                          <Label htmlFor="m-bogo">Buy One Get One</Label>
                        </div>
                        <div className="flex items-center">
                          <input
                            type="radio"
                            id="m-bundle"
                            name="m-dealType"
                            value="bundle"
                            checked={selectedDealType === 'bundle'}
                            onChange={() => setSelectedDealType('bundle')}
                            className="mr-2"
                          />
                          <Label htmlFor="m-bundle">Bundle Deals</Label>
                        </div>
                      </div>
                    </div>
                    
                    <Separator />
                    
                    <div>
                      <h3 className="font-medium mb-2">Options</h3>
                      <div className="space-y-2">
                        <div className="flex items-center">
                          <Checkbox
                            id="m-featured-only"
                            checked={isFeatured}
                            onCheckedChange={(checked) => setIsFeatured(!!checked)}
                            className="mr-2"
                          />
                          <Label htmlFor="m-featured-only">Featured Deals Only</Label>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <SheetFooter className="flex-col sm:flex-row gap-2 mt-4">
                    <Button onClick={applyFilters} className="w-full">
                      Apply Filters
                    </Button>
                    {hasActiveFilters && (
                      <Button variant="outline" onClick={resetFilters} className="w-full">
                        Reset Filters
                      </Button>
                    )}
                  </SheetFooter>
                </SheetContent>
              </Sheet>
              
              <Select
                value={selectedSortBy}
                onValueChange={handleSortChange}
              >
                <SelectTrigger className="flex-1">
                  <SlidersHorizontal className="mr-2 h-4 w-4" />
                  <SelectValue placeholder="Sort by" />
                </SelectTrigger>
                <SelectContent>
                  {sortOptions.map(option => (
                    <SelectItem key={option.value} value={option.value}>
                      {option.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            
            {hasActiveFilters && (
              <Button variant="ghost" onClick={resetFilters} className="flex items-center gap-2 w-full">
                <X className="h-4 w-4" />
                Clear All Filters
              </Button>
            )}
          </div>
          
          {/* Results Control - Desktop */}
          <div className="hidden md:flex items-center justify-between mb-6">
            <div>
              {loading ? (
                <div className="h-6 w-32 bg-muted animate-pulse rounded"></div>
              ) : (
                <p className="text-muted-foreground">
                  {filteredDeals.length} {filteredDeals.length === 1 ? 'result' : 'results'}
                </p>
              )}
            </div>
            
            <Select
              value={selectedSortBy}
              onValueChange={handleSortChange}
            >
              <SelectTrigger className="w-48">
                <SelectValue placeholder="Sort by" />
              </SelectTrigger>
              <SelectContent>
                {sortOptions.map(option => (
                  <SelectItem key={option.value} value={option.value}>
                    {option.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          
          {/* Results */}
          {loading ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {Array.from({ length: 6 }).map((_, index) => (
                <div key={index} className="animate-pulse">
                  <div className="bg-muted rounded-lg h-48 mb-3"></div>
                  <div className="h-6 bg-muted rounded mb-2 w-3/4"></div>
                  <div className="h-4 bg-muted rounded mb-2 w-1/2"></div>
                  <div className="h-8 bg-muted rounded w-1/3"></div>
                </div>
              ))}
            </div>
          ) : filteredDeals.length > 0 ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {filteredDeals.map(deal => (
                <DealCard key={deal.id} deal={deal} featured={deal.featured} />
              ))}
            </div>
          ) : (
            <div className="py-20 text-center">
              <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-muted mb-4">
                <SearchIcon className="h-8 w-8 text-muted-foreground" />
              </div>
              <h2 className="text-xl font-semibold mb-2">No deals found</h2>
              <p className="text-muted-foreground mb-6">
                Try adjusting your search or filter criteria
              </p>
              <Button onClick={resetFilters}>Reset All Filters</Button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default SearchPage;
