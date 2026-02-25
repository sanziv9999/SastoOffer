import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { TrendingUp, Star, ShoppingBag, DollarSign } from 'lucide-react';
import { Progress } from '@/components/ui/progress';

const mockBestDeals = [
  { id: '1', title: 'Gourmet Pizza Deal', sold: 156, revenue: 3588.44, rating: 4.8, maxQty: 200, image: '/placeholder.svg', category: 'Food' },
  { id: '2', title: 'Spa Relaxation Package', sold: 89, revenue: 7921.00, rating: 4.9, maxQty: 100, image: '/placeholder.svg', category: 'Wellness' },
  { id: '3', title: 'Weekend Brunch Special', sold: 234, revenue: 5265.00, rating: 4.7, maxQty: 300, image: '/placeholder.svg', category: 'Food' },
  { id: '4', title: 'Fitness Class Pack', sold: 67, revenue: 8040.00, rating: 4.6, maxQty: 80, image: '/placeholder.svg', category: 'Fitness' },
  { id: '5', title: 'Coffee Lover Bundle', sold: 312, revenue: 2808.00, rating: 4.5, maxQty: 500, image: '/placeholder.svg', category: 'Food' },
];

const BestDeals = () => {
  const totalRevenue = mockBestDeals.reduce((s, d) => s + d.revenue, 0);
  const totalSold = mockBestDeals.reduce((s, d) => s + d.sold, 0);

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Best Deals</h1>
        <p className="text-muted-foreground">Your top performing deals and bestsellers</p>
      </div>

      <div className="grid gap-4 grid-cols-2 md:grid-cols-3">
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Total Sold</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold">{totalSold}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Total Revenue</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold">${totalRevenue.toLocaleString()}</div></CardContent>
        </Card>
        <Card className="col-span-2 md:col-span-1">
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Avg. Rating</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold flex items-center gap-1"><Star className="h-5 w-5 fill-yellow-400 text-yellow-400" />{(mockBestDeals.reduce((s, d) => s + d.rating, 0) / mockBestDeals.length).toFixed(1)}</div></CardContent>
        </Card>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {mockBestDeals.sort((a, b) => b.sold - a.sold).map((deal, index) => (
          <Card key={deal.id} className="overflow-hidden hover:shadow-md transition-shadow">
            <CardHeader className="pb-3">
              <div className="flex items-start justify-between">
                <div>
                  <Badge variant="outline" className="mb-2">{deal.category}</Badge>
                  <CardTitle className="text-base">{deal.title}</CardTitle>
                </div>
                <div className="flex items-center justify-center h-8 w-8 rounded-full bg-primary/10 text-primary font-bold text-sm">
                  #{index + 1}
                </div>
              </div>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground flex items-center gap-1"><ShoppingBag className="h-3.5 w-3.5" /> Sold</span>
                <span className="font-medium">{deal.sold} / {deal.maxQty}</span>
              </div>
              <Progress value={(deal.sold / deal.maxQty) * 100} className="h-2" />
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground flex items-center gap-1"><DollarSign className="h-3.5 w-3.5" /> Revenue</span>
                <span className="font-medium">${deal.revenue.toLocaleString()}</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground flex items-center gap-1"><Star className="h-3.5 w-3.5" /> Rating</span>
                <span className="font-medium flex items-center gap-1">
                  <Star className="h-3.5 w-3.5 fill-yellow-400 text-yellow-400" />{deal.rating}
                </span>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
};

export default BestDeals;
