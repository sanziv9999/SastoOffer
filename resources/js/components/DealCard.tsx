
import { Link } from 'react-router-dom';
import { formatDistance } from 'date-fns';
import { Heart, MapPin, Clock, Tag, ChevronRight } from 'lucide-react';
import { Deal } from '@/types';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { Badge } from '@/components/ui/badge';
import { locations, categories } from '@/data/mockData';

interface DealCardProps {
  deal: Deal;
  featured?: boolean;
}

const DealCard = ({ deal, featured = false }: DealCardProps) => {
  const location = locations.find(loc => loc.id === deal.locationId);
  const category = categories.find(cat => cat.id === deal.categoryId);
  
  const discountPercentage = Math.round(
    ((deal.originalPrice - deal.discountedPrice) / deal.originalPrice) * 100
  );
  
  const timeLeft = formatDistance(
    new Date(deal.endDate),
    new Date(),
    { addSuffix: true }
  );
  
  return (
    <div 
      className={cn(
        "group bg-white border border-gray-200 rounded-lg overflow-hidden transition-all duration-200 hover:shadow-md relative",
        featured && "shadow-md hover:shadow-lg transform hover:-translate-y-1"
      )}
    >
      {/* Featured Badge */}
      {featured && (
        <div className="absolute top-3 left-3 z-10">
          <Badge variant="destructive" className="px-2 py-1 text-xs">
            Featured
          </Badge>
        </div>
      )}
      
      {/* Discount Badge */}
      <div className="absolute top-3 right-3 z-10">
        <Badge variant="default" className="bg-green-600 hover:bg-green-700">
          {discountPercentage}% OFF
        </Badge>
      </div>
      
      {/* Image */}
      <Link to={`/deals/deal/${deal.id}`} className="block relative overflow-hidden h-48">
        <img 
          src={deal.image} 
          alt={deal.title} 
          className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
        />
      </Link>
      
      {/* Content */}
      <div className="p-4">
        {/* Category & Location */}
        <div className="flex justify-between items-center text-xs text-gray-500 mb-2">
          <div className="flex items-center">
            <Tag className="h-3 w-3 mr-1 text-primary" />
            <span>{category?.name || 'Uncategorized'}</span>
          </div>
          {location && (
            <div className="flex items-center">
              <MapPin className="h-3 w-3 mr-1 text-primary" />
              <span>{location.city}</span>
            </div>
          )}
        </div>
        
        {/* Title */}
        <Link to={`/deals/deal/${deal.id}`}>
          <h3 className="font-semibold text-teal-800 mb-2 line-clamp-2 min-h-[3rem] transition-colors group-hover:text-teal-600">
            {deal.title}
          </h3>
        </Link>
        
        {/* Pricing */}
        <div className="flex items-baseline mb-2">
          <span className="text-lg font-bold text-primary mr-2">
            ${deal.discountedPrice}
          </span>
          <span className="text-sm line-through text-gray-400">
            ${deal.originalPrice}
          </span>
        </div>
        
        {/* Time Left */}
        <div className="flex items-center text-xs text-amber-600 mb-3">
          <Clock className="h-3 w-3 mr-1" />
          <span>Ends {timeLeft}</span>
        </div>
        
        {/* Action Button */}
        <div className="flex justify-between items-center">
          <Button asChild variant="outline" size="sm" className="w-full">
            <Link to={`/deals/deal/${deal.id}`} className="flex items-center justify-center">
              View Deal
              <ChevronRight className="h-4 w-4 ml-1" />
            </Link>
          </Button>
        </div>
      </div>
    </div>
  );
};

export default DealCard;
