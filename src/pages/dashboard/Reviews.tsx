import { Star, MessageSquare } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { deals } from '@/data/mockData';

const mockReviews = [
  { id: '1', dealId: 'deal1', rating: 5, comment: 'Amazing deal! Got a great discount on the meal.', date: '2024-01-15' },
  { id: '2', dealId: 'deal2', rating: 4, comment: 'Good quality products at a fair price.', date: '2024-01-10' },
  { id: '3', dealId: 'deal3', rating: 3, comment: 'Decent deal but could be better.', date: '2024-01-05' },
];

const Reviews = () => {
  const getDealById = (dealId: string) => deals.find(d => d.id === dealId);

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">My Reviews</h1>
        <p className="text-muted-foreground">Reviews you've left on deals</p>
      </div>

      <div className="grid gap-4 md:grid-cols-3">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Reviews</CardTitle>
            <MessageSquare className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{mockReviews.length}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Average Rating</CardTitle>
            <Star className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {(mockReviews.reduce((sum, r) => sum + r.rating, 0) / mockReviews.length).toFixed(1)}
            </div>
          </CardContent>
        </Card>
      </div>

      <div className="space-y-4">
        {mockReviews.map(review => {
          const deal = getDealById(review.dealId);
          return (
            <Card key={review.id}>
              <CardContent className="p-4">
                <div className="flex items-start justify-between">
                  <div className="flex-grow">
                    <h3 className="font-semibold">{deal?.title || 'Unknown Deal'}</h3>
                    <div className="flex items-center gap-1 my-2">
                      {Array.from({ length: 5 }).map((_, i) => (
                        <Star
                          key={i}
                          className={`h-4 w-4 ${i < review.rating ? 'text-yellow-400 fill-current' : 'text-muted-foreground'}`}
                        />
                      ))}
                    </div>
                    <p className="text-muted-foreground">{review.comment}</p>
                    <p className="text-xs text-muted-foreground mt-2">{review.date}</p>
                  </div>
                  <Button variant="outline" size="sm">Edit</Button>
                </div>
              </CardContent>
            </Card>
          );
        })}
      </div>
    </div>
  );
};

export default Reviews;
