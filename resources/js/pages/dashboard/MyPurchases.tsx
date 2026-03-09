
import Link from '@/components/Link';
import { ShoppingBag, Calendar, Tag } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { formatDistanceToNow } from 'date-fns';

interface MyPurchasesProps {
  purchases: any[];
  deals: any[];
}

const MyPurchases = ({ purchases, deals }: MyPurchasesProps) => {
  const getDealById = (dealId: string) => deals?.find((d: any) => d.id === dealId);
  const activePurchases = purchases?.filter((p: any) => !p.redeemed) || [];
  const redeemedPurchases = purchases?.filter((p: any) => p.redeemed) || [];

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
            <div className="text-2xl font-bold">{purchases?.length || 0}</div>
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
              ${purchases?.reduce((sum: number, p: any) => sum + p.totalPrice, 0).toFixed(2) || '0.00'}
            </div>
          </CardContent>
        </Card>
      </div>

      <Tabs defaultValue="all" className="space-y-4">
        <TabsList>
          <TabsTrigger value="all">All ({purchases?.length || 0})</TabsTrigger>
          <TabsTrigger value="active">Active ({activePurchases.length})</TabsTrigger>
          <TabsTrigger value="redeemed">Redeemed ({redeemedPurchases.length})</TabsTrigger>
        </TabsList>

        <TabsContent value="all" className="space-y-3">
          {purchases?.map((purchase: any) => {
            const deal = purchase.deal || getDealById(purchase.dealId);
            return (
              <Link key={purchase.id} href={`/dashboard/purchases/${purchase.id}`} className="block">
                <div className="flex items-center gap-4 p-4 border rounded-lg hover:bg-muted/70 transition-colors bg-card">
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
                    <code className="text-xs font-mono text-muted-foreground bg-muted px-1.5 py-0.5 rounded">{purchase.couponCode}</code>
                  </div>
                  <div className="text-right flex flex-col items-end gap-1">
                    <div className="font-semibold text-primary">${purchase.totalPrice?.toFixed(2)}</div>
                    <Badge variant={purchase.redeemed ? "outline" : "default"} className={purchase.redeemed ? "text-muted-foreground" : "bg-green-500 hover:bg-green-600"}>
                      {purchase.redeemed ? "Used" : "Active"}
                    </Badge>
                  </div>
                </div>
              </Link>
            );
          })}
        </TabsContent>
        <TabsContent value="active" className="space-y-3">
          {activePurchases.map((purchase: any) => {
            const deal = purchase.deal || getDealById(purchase.dealId);
            return (
              <Link key={purchase.id} href={`/dashboard/purchases/${purchase.id}`} className="block">
                <div className="flex items-center gap-4 p-4 border rounded-lg hover:bg-muted/70 transition-colors bg-card">
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
                    <code className="text-xs font-mono text-muted-foreground bg-muted px-1.5 py-0.5 rounded">{purchase.couponCode}</code>
                  </div>
                  <div className="text-right flex flex-col items-end gap-1">
                    <div className="font-semibold text-primary">${purchase.totalPrice?.toFixed(2)}</div>
                    <Badge variant="default" className="bg-green-500 hover:bg-green-600">
                      Active
                    </Badge>
                  </div>
                </div>
              </Link>
            );
          })}
        </TabsContent>
        <TabsContent value="redeemed" className="space-y-3">
          {redeemedPurchases.map((purchase: any) => {
            const deal = purchase.deal || getDealById(purchase.dealId);
            return (
              <Link key={purchase.id} href={`/dashboard/purchases/${purchase.id}`} className="block">
                <div className="flex items-center gap-4 p-4 border rounded-lg hover:bg-muted/70 transition-colors bg-card">
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
                    <code className="text-xs font-mono text-muted-foreground bg-muted px-1.5 py-0.5 rounded">{purchase.couponCode}</code>
                  </div>
                  <div className="text-right flex flex-col items-end gap-1">
                    <div className="font-semibold text-muted-foreground">${purchase.totalPrice?.toFixed(2)}</div>
                    <Badge variant="outline" className="text-muted-foreground">
                      Used
                    </Badge>
                  </div>
                </div>
              </Link>
            );
          })}
        </TabsContent>
      </Tabs>
    </div>
  );
};

export default MyPurchases;
