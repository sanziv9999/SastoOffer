
import { useState } from 'react';
import { Link } from 'react-router-dom';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Deal } from '@/types';
import { Button } from './ui/button';
import DealCard from './DealCard';

interface FeaturedDealsProps {
  deals: Deal[];
}

const FeaturedDeals = ({ deals }: FeaturedDealsProps) => {
  const featuredDeals = deals.filter(deal => deal.featured);
  const [currentIndex, setCurrentIndex] = useState(0);
  const displayCount = Math.min(4, featuredDeals.length); // Changed to 4 columns
  
  const handlePrev = () => {
    setCurrentIndex(prev => 
      prev === 0 ? Math.max(0, featuredDeals.length - displayCount) : prev - 1
    );
  };
  
  const handleNext = () => {
    setCurrentIndex(prev => 
      prev + displayCount >= featuredDeals.length ? 0 : prev + 1
    );
  };
  
  if (featuredDeals.length === 0) return null;
  
  return (
    <section className="py-10">
      <div className="container px-4 mx-auto">
        <div className="flex justify-between items-center mb-6">
          <h2 className="text-2xl md:text-3xl font-bold">Featured Deals</h2>
          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              size="icon"
              onClick={handlePrev}
              className="h-8 w-8 rounded-full"
              disabled={featuredDeals.length <= displayCount}
            >
              <ChevronLeft className="h-4 w-4" />
            </Button>
            <Button
              variant="outline"
              size="icon"
              onClick={handleNext}
              className="h-8 w-8 rounded-full"
              disabled={featuredDeals.length <= displayCount}
            >
              <ChevronRight className="h-4 w-4" />
            </Button>
            <Link to="/search?featured=true">
              <Button variant="link" size="sm">
                View All
              </Button>
            </Link>
          </div>
        </div>
        
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          {featuredDeals.slice(currentIndex, currentIndex + displayCount).map(deal => (
            <DealCard key={deal.id} deal={deal} featured />
          ))}
        </div>
      </div>
    </section>
  );
};

export default FeaturedDeals;
