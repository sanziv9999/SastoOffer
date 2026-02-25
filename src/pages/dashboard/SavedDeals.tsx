import { Link } from 'react-router-dom';
import { Heart, Star, Trash2 } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { deals } from '@/data/mockData';

const SavedDeals = () => {
  const favoriteDeals = deals.slice(0, 5);

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Saved Deals</h1>
        <p className="text-muted-foreground">Deals you've saved for later</p>
      </div>

      {favoriteDeals.length > 0 ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {favoriteDeals.map(deal => (
            <Card key={deal.id} className="hover:shadow-md transition-shadow">
              <div className="aspect-video relative overflow-hidden rounded-t-lg">
                <img src={deal.image} alt={deal.title} className="w-full h-full object-cover" />
                <Badge className="absolute top-2 right-2 bg-red-500">
                  {Math.round(((deal.originalPrice - deal.discountedPrice) / deal.originalPrice) * 100)}% OFF
                </Badge>
              </div>
              <CardContent className="p-4">
                <h3 className="font-semibold mb-2 line-clamp-2">{deal.title}</h3>
                <div className="flex items-center justify-between mb-3">
                  <div>
                    <span className="text-lg font-bold">${deal.discountedPrice}</span>
                    <span className="text-sm text-muted-foreground line-through ml-2">${deal.originalPrice}</span>
                  </div>
                  <div className="flex items-center">
                    <Star className="h-4 w-4 text-yellow-400 fill-current" />
                    <span className="text-sm ml-1">4.5</span>
                  </div>
                </div>
                <div className="flex gap-2">
                  <Button asChild className="flex-1" size="sm">
                    <Link to={`/deals/${deal.id}`}>View Deal</Link>
                  </Button>
                  <Button variant="outline" size="icon" className="text-destructive hover:text-destructive">
                    <Trash2 className="h-4 w-4" />
                  </Button>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      ) : (
        <Card>
          <CardContent className="text-center p-8">
            <Heart className="h-10 w-10 mx-auto mb-4 text-muted-foreground" />
            <h3 className="text-lg font-semibold mb-2">No saved deals</h3>
            <p className="text-muted-foreground mb-4">Start browsing and save deals you love!</p>
            <Button asChild><Link to="/">Explore Deals</Link></Button>
          </CardContent>
        </Card>
      )}
    </div>
  );
};

export default SavedDeals;
