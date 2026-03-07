
import { Star, MessageSquare } from 'lucide-react';
import Link from '@/components/Link';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';

interface ReviewsProps {
  reviews: any[];
  deals: any[];
}

const Reviews = ({ reviews = [], deals = [] }: ReviewsProps) => {
  const getDealById = (id: string) => deals?.find((d: any) => d.id === id);

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">My Reviews</h1>
        <p className="text-muted-foreground">Reviews you've left on deals</p>
      </div>

      <div className="grid gap-4 md:grid-cols-2">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Reviews</CardTitle>
            <MessageSquare className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{reviews?.length || 0}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Average Rating</CardTitle>
            <Star className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {reviews?.length > 0
                ? (reviews.reduce((sum: number, r: any) => sum + r.rating, 0) / reviews.length).toFixed(1)
                : '0.0'}
            </div>
          </CardContent>
        </Card>
      </div>

      <div className="space-y-4">
        {reviews?.length > 0 ? (
          reviews.map((review: any) => {
            const deal = review.deal || getDealById(review.dealId);
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
                      <p className="text-xs text-muted-foreground mt-2">
                        {review.createdAt instanceof Date ? review.createdAt.toLocaleDateString() : review.createdAt}
                      </p>
                    </div>
                    <Button variant="outline" size="sm" asChild>
                      <Link href={`/dashboard/reviews/edit/${review.id}`}>Edit</Link>
                    </Button>
                  </div>
                </CardContent>
              </Card>
            );
          })
        ) : (
          <Card>
            <CardContent className="p-8 text-center">
              <MessageSquare className="h-10 w-10 mx-auto mb-4 text-muted-foreground/50" />
              <h3 className="text-lg font-medium">No reviews yet</h3>
              <p className="text-muted-foreground">You haven't left any reviews on deals yet.</p>
            </CardContent>
          </Card>
        )}
      </div>
    </div>
  );
};

export default Reviews;
