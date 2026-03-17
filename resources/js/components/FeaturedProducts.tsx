
import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Star } from 'lucide-react';
import { deals } from '@/data/mockData';

const FeaturedProducts = () => {
  const [isLoading] = useState(false);
  
  const featuredDeals = deals
    .filter(deal => deal.averageRating && deal.averageRating >= 4.0)
    .slice(0, 8);
    
  return (
    <section className="py-8 bg-background">
      <div className="container mx-auto px-4">
        <div className="flex justify-between items-center mb-5">
          <h2 className="text-lg md:text-xl font-bold text-foreground flex items-center">
            <Star className="text-yellow-500 mr-2 h-5 w-5" /> 
            Discover Amazing Deals
          </h2>
          <Link to="/search?featured=true" className="text-primary hover:underline text-sm font-medium">
            View all
          </Link>
        </div>
        
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 md:gap-4">
          {isLoading
            ? Array(8).fill(0).map((_, i) => (
                <Card key={i} className="overflow-hidden">
                  <Skeleton className="h-28 w-full" />
                  <CardContent className="p-3">
                    <Skeleton className="h-3 w-3/4 mb-2" />
                    <Skeleton className="h-3 w-1/2" />
                  </CardContent>
                </Card>
              ))
            : featuredDeals.map((deal) => {
                const discountPercentage = Math.round(
                  ((deal.originalPrice - deal.discountedPrice) / deal.originalPrice) * 100
                );
                
                return (
                  <Link key={deal.id} to={`/deals/deal/${deal.id}`} className="block">
                    <Card className="overflow-hidden h-full hover:shadow-md transition-all duration-200 group border-border">
                      <div className="relative">
                        <img 
                          src={deal.image} 
                          alt={deal.title} 
                          className="h-28 sm:h-32 md:h-36 w-full object-cover transition-transform duration-300 group-hover:scale-105" 
                          loading="lazy"
                        />
                        <Badge className="absolute top-1.5 right-1.5 bg-green-600 text-xs px-1.5 py-0.5">
                          {discountPercentage}% OFF
                        </Badge>
                      </div>
                      <CardContent className="p-2.5 sm:p-3">
                        <div className="flex items-center gap-2">
                          <span className="text-lg font-bold text-primary">Rs. {deal.discountedPrice}</span>
                          <span className="text-sm text-muted-foreground line-through">Rs. {deal.originalPrice}</span>
                        </div>
                      </CardContent>
                    </Card>
                  </Link>
                );
              })}
        </div>
      </div>
    </section>
  );
};

export default FeaturedProducts;
