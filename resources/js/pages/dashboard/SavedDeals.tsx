
import Link from '@/components/Link';
import { Heart, Star, Trash2 } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import DashboardLayout from '@/layouts/DashboardLayout';
import { useState } from 'react';
import { toast } from 'sonner';

interface SavedDealsProps {
  favoriteDeals: any[];
}

const SavedDeals = ({ favoriteDeals }: SavedDealsProps) => {
  const [savedDeals, setSavedDeals] = useState<any[]>(favoriteDeals || []);

  const confirmRemoveWithToast = () =>
    new Promise<boolean>((resolve) => {
      let settled = false;
      const finish = (value: boolean) => {
        if (settled) return;
        settled = true;
        resolve(value);
      };

      toast('Remove this deal from your wishlist?', {
        duration: 10000,
        action: {
          label: 'Remove',
          onClick: () => finish(true),
        },
        cancel: {
          label: 'Cancel',
          onClick: () => finish(false),
        },
        onDismiss: () => finish(false),
      });
    });

  const handleRemove = async (deal: any) => {
    const offerPivotId = deal?.offerPivotId;
    if (!offerPivotId) {
      toast.error('This saved deal cannot be removed right now.');
      return;
    }

    const shouldRemove = await confirmRemoveWithToast();
    if (!shouldRemove) return;

    try {
      await (window as any).axios.post(`/wishlist/toggle/${offerPivotId}`);

      setSavedDeals((prev) => prev.filter((d) => d.id !== deal.id || d.offerPivotId !== offerPivotId));
      toast.success('Removed from wishlist.');
    } catch {
      toast.error('Could not remove this deal. Please try again.');
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Saved Deals</h1>
        <p className="text-muted-foreground">Deals you've saved for later</p>
      </div>

      {savedDeals?.length > 0 ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {savedDeals.map((deal: any, index: number) => (
            <Card key={`${deal.offerPivotId ?? 'offer'}-${deal.id ?? 'deal'}-${index}`} className="hover:shadow-md transition-shadow">
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
                    <a href={`/deals/${deal.slug ?? deal.id}`}>View Deal</a>
                  </Button>
                  <Button
                    variant="outline"
                    size="icon"
                    className="text-destructive hover:text-destructive"
                    onClick={() => handleRemove(deal)}
                  >
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
            <Button asChild><Link href="/">Explore Deals</Link></Button>
          </CardContent>
        </Card>
      )}
    </div>
  );
};

SavedDeals.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default SavedDeals;
