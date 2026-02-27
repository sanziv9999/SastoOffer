
import { useRef } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';

const brands = [
  { id: '1', name: 'Gourmet Delights', logo: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=200&h=200&auto=format&fit=crop' },
  { id: '2', name: 'Tech Haven', logo: 'https://images.unsplash.com/photo-1622748907213-7d9328be76bc?w=200&h=200&auto=format&fit=crop' },
  { id: '3', name: 'Wellness Spa', logo: 'https://images.unsplash.com/photo-1560750588-73207b1ef5b8?w=200&h=200&auto=format&fit=crop' },
  { id: '4', name: 'Adventure Tours', logo: 'https://images.unsplash.com/photo-1551632436-cbf8dd35adfa?w=200&h=200&auto=format&fit=crop' },
  { id: '5', name: 'Style Boutique', logo: 'https://images.unsplash.com/photo-1589363360147-4f2d51541551?w=200&h=200&auto=format&fit=crop' },
  { id: '6', name: 'Fitness Pro', logo: 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=200&h=200&auto=format&fit=crop' },
  { id: '7', name: 'Home Decor', logo: 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=200&h=200&auto=format&fit=crop' },
  { id: '8', name: 'Bookworm', logo: 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=200&h=200&auto=format&fit=crop' },
];

const BrandLogos = () => {
  const scrollRef = useRef<HTMLDivElement>(null);

  const scrollBrands = (direction: 'left' | 'right') => {
    if (!scrollRef.current) return;
    const scrollAmount = 200;
    scrollRef.current.scrollBy({ left: direction === 'left' ? -scrollAmount : scrollAmount, behavior: 'smooth' });
  };

  return (
    <div className="bg-background py-10">
      <div className="container mx-auto px-4">
        <h2 className="text-xl font-bold text-center mb-8 text-foreground">Popular Brands</h2>
        
        <div className="relative group">
          <div 
            ref={scrollRef} 
            className="flex overflow-x-auto gap-6 py-4 scrollbar-hide items-center justify-start md:justify-center"
            style={{ scrollbarWidth: 'none', msOverflowStyle: 'none' }}
          >
            {brands.map(brand => (
              <a 
                key={brand.id}
                href={`/vendor/${brand.id}`}
                className="flex-shrink-0 group/brand"
              >
                <div className="w-24 h-24 sm:w-28 sm:h-28 md:w-32 md:h-32 rounded-xl overflow-hidden border border-border bg-background shadow-sm hover:shadow-md transition-all duration-200 hover:scale-105">
                  <img 
                    src={brand.logo} 
                    alt={brand.name} 
                    className="w-full h-full object-cover" 
                    loading="lazy"
                  />
                </div>
                <p className="text-xs text-center mt-2 text-muted-foreground font-medium truncate max-w-[7rem] mx-auto">{brand.name}</p>
              </a>
            ))}
          </div>
          
          <Button 
            onClick={() => scrollBrands('left')}
            variant="outline" 
            size="icon" 
            className="absolute left-0 top-1/2 transform -translate-y-1/2 h-8 w-8 rounded-full bg-background/90 shadow z-10 opacity-0 md:group-hover:opacity-100 transition-opacity"
          >
            <ChevronLeft className="h-4 w-4" />
          </Button>
          
          <Button 
            onClick={() => scrollBrands('right')}
            variant="outline" 
            size="icon" 
            className="absolute right-0 top-1/2 transform -translate-y-1/2 h-8 w-8 rounded-full bg-background/90 shadow z-10 opacity-0 md:group-hover:opacity-100 transition-opacity"
          >
            <ChevronRight className="h-4 w-4" />
          </Button>
        </div>
      </div>
    </div>
  );
};

export default BrandLogos;
