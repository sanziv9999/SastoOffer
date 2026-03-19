
import { useState, useRef, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Clock, ArrowRight, ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { deals } from '@/data/mockData';
import { formatDistance } from 'date-fns';

const RecentOffers = () => {
  // Sort deals by creation date (newest first)
  const recentDeals = [...deals]
    .sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime())
    .slice(0, 10);
  
  const scrollContainerRef = useRef<HTMLDivElement>(null);
  const [showControls, setShowControls] = useState(false);
  const [isDragging, setIsDragging] = useState(false);
  const [startX, setStartX] = useState(0);
  const [scrollLeft, setScrollLeft] = useState(0);
  
  const handleScroll = (direction: 'left' | 'right') => {
    const scrollContainer = scrollContainerRef.current;
    if (!scrollContainer) return;
    
    const scrollAmount = 300;
    const currentScroll = scrollContainer.scrollLeft;
    
    scrollContainer.scrollTo({
      left: direction === 'left' 
        ? currentScroll - scrollAmount 
        : currentScroll + scrollAmount,
      behavior: 'smooth'
    });
  };

  // Drag to scroll functionality
  const handleMouseDown = (e: React.MouseEvent) => {
    if (!scrollContainerRef.current) return;
    
    setIsDragging(true);
    setStartX(e.pageX - scrollContainerRef.current.offsetLeft);
    setScrollLeft(scrollContainerRef.current.scrollLeft);
  };

  const handleMouseUp = () => {
    setIsDragging(false);
  };

  const handleMouseMove = (e: React.MouseEvent) => {
    if (!isDragging || !scrollContainerRef.current) return;
    
    e.preventDefault();
    const x = e.pageX - scrollContainerRef.current.offsetLeft;
    const walk = (x - startX) * 2; // Speed multiplier
    scrollContainerRef.current.scrollLeft = scrollLeft - walk;
  };

  useEffect(() => {
    // Add touch support for mobile
    const scrollContainer = scrollContainerRef.current;
    if (!scrollContainer) return;
    
    let touchStartX = 0;
    let touchScrollLeft = 0;
    
    const handleTouchStart = (e: TouchEvent) => {
      touchStartX = e.touches[0].clientX - scrollContainer.offsetLeft;
      touchScrollLeft = scrollContainer.scrollLeft;
    };
    
    const handleTouchMove = (e: TouchEvent) => {
      if (!scrollContainer) return;
      
      const x = e.touches[0].clientX - scrollContainer.offsetLeft;
      const walk = (x - touchStartX) * 2;
      scrollContainer.scrollLeft = touchScrollLeft - walk;
    };
    
    scrollContainer.addEventListener('touchstart', handleTouchStart);
    scrollContainer.addEventListener('touchmove', handleTouchMove);
    
    return () => {
      scrollContainer.removeEventListener('touchstart', handleTouchStart);
      scrollContainer.removeEventListener('touchmove', handleTouchMove);
    };
  }, []);
  
  return (
    <section className="py-8 bg-gray-50">
      <div className="container mx-auto px-4">
        <div className="flex justify-between items-center mb-6">
          <h2 className="text-2xl font-bold text-slate-800 flex items-center">
            <Clock className="text-primary mr-2 h-5 w-5" /> 
            Recent Offers
          </h2>
          <Link to="/search?sort=newest" className="text-primary hover:underline text-sm font-medium flex items-center">
            View all
            <ArrowRight className="ml-1 h-4 w-4" />
          </Link>
        </div>
        
        <div 
          className="relative group overflow-hidden"
          onMouseEnter={() => setShowControls(true)}
          onMouseLeave={() => setShowControls(false)}
        >
          <div 
            ref={scrollContainerRef}
            className="flex gap-4 py-2 overflow-x-auto scrollbar-hide scroll-smooth cursor-grab" 
            style={{ scrollbarWidth: 'none', msOverflowStyle: 'none' }}
            onMouseDown={handleMouseDown}
            onMouseUp={handleMouseUp}
            onMouseLeave={handleMouseUp}
            onMouseMove={handleMouseMove}
          >
            {recentDeals.map((deal) => {
              const discountPercentage = Math.round(
                ((deal.originalPrice - deal.discountedPrice) / deal.originalPrice) * 100
              );
              
              const timeAgo = formatDistance(
                new Date(deal.createdAt),
                new Date(),
                { addSuffix: true }
              );
              const dealPath = `/deals/${(deal as any).slug ?? deal.id}`;
              
              return (
                <Card key={deal.id} className="overflow-hidden flex-shrink-0 w-[280px] hover:shadow-md transition-shadow group">
                  <div className="relative">
                    <img 
                      src={deal.image} 
                      alt={deal.title} 
                      className="h-40 w-full object-cover transition-transform duration-300 group-hover:scale-105" 
                    />
                    <Badge className="absolute top-2 right-2 bg-green-600">
                      {discountPercentage}% OFF
                    </Badge>
                    <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent text-white p-2">
                      <div className="flex items-center text-xs">
                        <Clock className="h-3 w-3 mr-1" />
                        <span>Added {timeAgo}</span>
                      </div>
                    </div>
                  </div>
                  <CardContent className="p-4">
                    <Link to={dealPath}>
                      <h3 className="font-semibold text-slate-800 mb-2 line-clamp-2 hover:text-primary transition-colors">
                        {deal.title}
                      </h3>
                    </Link>
                    <div className="flex items-center gap-2">
                      <span className="font-bold text-primary">Rs. {deal.discountedPrice}</span>
                      <span className="text-xs text-muted-foreground line-through">Rs. {deal.originalPrice}</span>
                    </div>
                  </CardContent>
                </Card>
              );
            })}
          </div>
          
          {/* Manual scroll controls - only visible on desktop */}
          <Button 
            onClick={() => handleScroll('left')}
            variant="outline" 
            size="icon" 
            className={`hidden md:flex absolute left-4 top-1/2 transform -translate-y-1/2 h-8 w-8 rounded-full bg-white/90 shadow-md z-10 opacity-0 transition-opacity duration-200 ${showControls ? 'opacity-100' : 'group-hover:opacity-100'}`}
          >
            <ChevronLeft className="h-5 w-5" />
          </Button>
          
          <Button 
            onClick={() => handleScroll('right')}
            variant="outline" 
            size="icon" 
            className={`hidden md:flex absolute right-4 top-1/2 transform -translate-y-1/2 h-8 w-8 rounded-full bg-white/90 shadow-md z-10 opacity-0 transition-opacity duration-200 ${showControls ? 'opacity-100' : 'group-hover:opacity-100'}`}
          >
            <ChevronRight className="h-5 w-5" />
          </Button>
        </div>
      </div>
    </section>
  );
};

export default RecentOffers;
