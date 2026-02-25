import { Link } from 'react-router-dom';
import { ShoppingBag, Calendar, Clock, Tag } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { deals, purchases } from '@/data/mockData';
import { formatDistanceToNow } from 'date-fns';

const MyPurchases = () => {
  const getDealById = (dealId: string) => deals.find(d => d.id === dealId);
  const activePurchases = purchases.filter(p => !p.redeemed);
  const redeemedPurchases = purchases.filter(p => p.redeemed);

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">My Purchases</h1>
        <p className="text-muted-foreground">Track all your purchases and coupons</p>
      </div>

      <div className="grid gap-4 md:grid-cols-3">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Purchases</CardTitle>
            <ShoppingBag className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{purchases.length}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Active Coupons</CardTitle>
            <Tag className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{activePurchases.length}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Spent</CardTitle>
            <ShoppingBag className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              ${purchases.reduce((sum, p) => sum + p.totalPrice, 0).toFixed(2)}
            </div>
          </CardContent>
        </Card>
      </div>

      <Tabs defaultValue="all" className="space-y-4">
        <TabsList>
          <TabsTrigger value="all">All ({purchases.length})</TabsTrigger>
          <TabsTrigger value="active">Active ({activePurchases.length})</TabsTrigger>
          <TabsTrigger value="redeemed">Redeemed ({redeemedPurchases.length})</TabsTrigger>
        </TabsList>

        {['all', 'active', 'redeemed'].map(tab => (
          <TabsContent key={tab} value={tab} className="space-y-3">
            {(tab === 'all' ? purchases : tab === 'active' ? activePurchases : redeemedPurchases).map(purchase => {
              const deal = getDealById(purchase.dealId);
              return (
                <div key={purchase.id} className="flex items-center gap-4 p-4 border rounded-lg hover:bg-muted/50 transition-colors">
                  {deal && <img src={deal.image} alt={deal.title} className="h-14 w-14 rounded object-cover flex-shrink-0" />}
                  <div className="flex-grow">
                    <h4 className="font-medium">{deal?.title || 'Unknown Deal'}</h4>
                    <div className="flex items-center gap-4 text-sm text-muted-foreground">
                      <span className="flex items-center gap-1">
                        <Calendar className="h-3 w-3" />
                        {formatDistanceToNow(new Date(purchase.createdAt), { addSuffix: true })}
                      </span>
                      <span>Qty: {purchase.quantity}</span>
                    </div>
                    <code className="text-xs font-mono text-muted-foreground">{purchase.couponCode}</code>
                  </div>
                  <div className="text-right">
                    <div className="font-semibold">${purchase.totalPrice.toFixed(2)}</div>
                    <Badge variant={purchase.redeemed ? "outline" : "default"} className={purchase.redeemed ? "" : "bg-green-500"}>
                      {purchase.redeemed ? "Used" : "Active"}
                    </Badge>
                  </div>
                </div>
              );
            })}
          </TabsContent>
        ))}
      </Tabs>
    </div>
  );
};

export default MyPurchases;
