
import { useState } from 'react';
import { Deal } from '@/types';
import DealCard from './DealCard';
import { Button } from './ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from './ui/dropdown-menu';
import { ChevronDown } from 'lucide-react';

interface DealGridProps {
  deals: Deal[];
  title: string;
  emptyMessage?: string;
}

type SortOption = 'newest' | 'popular' | 'ending' | 'discount';

const DealGrid = ({ deals, title, emptyMessage = "No deals found" }: DealGridProps) => {
  const [sortBy, setSortBy] = useState<SortOption>('popular');
  
  const sortDeals = (deals: Deal[], sortOption: SortOption): Deal[] => {
    const sorted = [...deals];
    
    switch (sortOption) {
      case 'newest':
        return sorted.sort((a, b) => 
          new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()
        );
      case 'popular':
        return sorted.sort((a, b) => 
          (b.averageRating || 0) - (a.averageRating || 0)
        );
      case 'ending':
        return sorted.sort((a, b) => 
          new Date(a.endDate).getTime() - new Date(b.endDate).getTime()
        );
      case 'discount':
        return sorted.sort((a, b) => {
          const discountA = a.discountPercentage || 
            Math.round(((a.originalPrice - a.discountedPrice) / a.originalPrice) * 100);
          const discountB = b.discountPercentage || 
            Math.round(((b.originalPrice - b.discountedPrice) / b.originalPrice) * 100);
          return discountB - discountA;
        });
      default:
        return sorted;
    }
  };
  
  const sortedDeals = sortDeals(deals, sortBy);
  
  const sortOptions: { value: SortOption; label: string }[] = [
    { value: 'popular', label: 'Most Popular' },
    { value: 'newest', label: 'Newest' },
    { value: 'ending', label: 'Ending Soon' },
    { value: 'discount', label: 'Biggest Discount' },
  ];
  
  return (
    <section className="py-10">
      <div className="container px-4 mx-auto">
        <div className="flex flex-wrap justify-between items-center mb-6 gap-4">
          <h2 className="text-2xl md:text-3xl font-bold">{title}</h2>
          
          {deals.length > 0 && (
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="outline" className="h-9">
                  <span>Sort: {sortOptions.find(opt => opt.value === sortBy)?.label}</span>
                  <ChevronDown className="ml-2 h-4 w-4" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                {sortOptions.map(option => (
                  <DropdownMenuItem 
                    key={option.value}
                    onClick={() => setSortBy(option.value)}
                    className={sortBy === option.value ? "bg-muted" : ""}
                  >
                    {option.label}
                  </DropdownMenuItem>
                ))}
              </DropdownMenuContent>
            </DropdownMenu>
          )}
        </div>
        
        {deals.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {sortedDeals.map(deal => (
              <DealCard key={deal.id} deal={deal} />
            ))}
          </div>
        ) : (
          <div className="py-12 text-center">
            <p className="text-muted-foreground">{emptyMessage}</p>
          </div>
        )}
      </div>
    </section>
  );
};

export default DealGrid;
