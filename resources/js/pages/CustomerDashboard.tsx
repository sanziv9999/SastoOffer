
import { useState } from 'react';
import Link from '@/components/Link';
import {
  ShoppingBag,
  Calendar,
  Clock,
  Tag,
  Star,
  Search,
  Heart,
  Wallet,
  MapPin
} from 'lucide-react';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle
} from '@/components/ui/card';
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger
} from '@/components/ui/tabs';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { formatDistanceToNow } from 'date-fns';
import DashboardLayout from '@/layouts/DashboardLayout';
import { useAuth } from '@/context/AuthContext';

interface CustomerDashboardProps {
  stats: {
    totalPurchases: number;
    activeCoupons: number;
    totalSavings: number;
    favoriteDealsCount: number;
  };
  recommendations: any[];
  recentActivity: any[];
  deals: any[]; // Lookup for deal details if not provided in activity
}

const CustomerDashboard = ({ stats, recommendations, recentActivity, deals }: CustomerDashboardProps) => {
  const { user } = useAuth();
  const [searchTerm, setSearchTerm] = useState('');

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    // router.get('/dashboard', { search: searchTerm }, { preserveState: true });
    console.log('Searching for:', searchTerm);
  };

  const getDealById = (dealId: string) => {
    return deals?.find((deal: any) => deal.id === dealId);
  };

  const getDealHref = (dealLike?: any, fallbackId?: string | number) => {
    const key = dealLike?.slug ?? dealLike?.dealSlug ?? fallbackId;
    return key ? `/deals/${key}` : '/search';
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Welcome back, {user?.name}!</h1>
          <p className="text-muted-foreground">
            Discover amazing deals and manage your purchases
          </p>
        </div>

        <form onSubmit={handleSearch} className="flex w-full md:w-auto">
          <Input
            placeholder="Search your purchases..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="md:w-64 rounded-r-none"
          />
          <Button type="submit" size="icon" className="rounded-l-none">
            <Search className="h-4 w-4" />
          </Button>
        </form>
      </div>

      {/* Stats Cards */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Purchases</CardTitle>
            <ShoppingBag className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats?.totalPurchases || 0}</div>
            <p className="text-xs text-muted-foreground">
              Across all categories
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Active Coupons</CardTitle>
            <Tag className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats?.activeCoupons || 0}</div>
            <p className="text-xs text-muted-foreground">
              Ready to redeem
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Savings</CardTitle>
            <Wallet className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">Rs. {stats?.totalSavings?.toFixed(2) || '0.00'}</div>
            <p className="text-xs text-muted-foreground">
              Money saved with deals
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Favorite Deals</CardTitle>
            <Heart className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats?.favoriteDealsCount || 0}</div>
            <p className="text-xs text-muted-foreground">
              Saved for later
            </p>
          </CardContent>
        </Card>
      </div>

      {/* Quick Actions */}
      <Card>
        <CardHeader>
          <CardTitle>Quick Actions</CardTitle>
          <CardDescription>Jump to your most used features</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
            <Button asChild variant="outline" className="h-20 flex-col">
              <Link href="/search">
                <Search className="h-6 w-6 mb-2" />
                <span>Find Deals</span>
              </Link>
            </Button>
            <Button asChild variant="outline" className="h-20 flex-col">
              <Link href="/dashboard/favorites">
                <Heart className="h-6 w-6 mb-2" />
                <span>My Favorites</span>
              </Link>
            </Button>

            <Button asChild variant="outline" className="h-20 flex-col">
              <Link href="/dashboard/settings">
                <MapPin className="h-6 w-6 mb-2" />
                <span>Preferences</span>
              </Link>
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Personalized Recommendations */}
      <Card>
        <CardHeader>
          <CardTitle>Recommended for You</CardTitle>
          <CardDescription>Deals we think you'll love based on your activity</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {recommendations?.map((deal: any) => (
              <Card key={deal.id} className="hover:shadow-md transition-shadow">
                <div className="aspect-video relative overflow-hidden rounded-t-lg">
                  <img
                    src={deal.image}
                    alt={deal.title}
                    className="w-full h-full object-cover"
                  />
                  <Badge className="absolute top-2 right-2 bg-red-500">
                    {Math.round(((deal.originalPrice - deal.discountedPrice) / deal.originalPrice) * 100)}% OFF
                  </Badge>
                </div>
                <CardContent className="p-4">
                  <h3 className="font-semibold mb-2 line-clamp-2">{deal.title}</h3>
                  <div className="flex items-center justify-between mb-3">
                    <div>
                      <span className="text-lg font-bold">Rs. {deal.discountedPrice}</span>
                      <span className="text-sm text-muted-foreground line-through ml-2">
                        Rs. {deal.originalPrice}
                      </span>
                    </div>
                    <div className="flex items-center">
                      <Star className="h-4 w-4 text-yellow-400 fill-current" />
                      <span className="text-sm ml-1">4.5</span>
                    </div>
                  </div>
                  <Button asChild className="w-full" size="sm">
                    <Link href={getDealHref(deal, deal.id)}>
                      View Deal
                    </Link>
                  </Button>
                </CardContent>
              </Card>
            ))}
          </div>
        </CardContent>
      </Card>

      {/* Recent Activity */}
      <Card>
        <CardHeader>
          <CardTitle>Recent Activity</CardTitle>
          <CardDescription>Your latest purchases and coupon usage</CardDescription>
        </CardHeader>
        <CardContent>
          <Tabs defaultValue="recent" className="space-y-4">
            <TabsList>
              <TabsTrigger value="recent">Recent Purchases</TabsTrigger>
              <TabsTrigger value="active">Active Coupons</TabsTrigger>
            </TabsList>

            <TabsContent value="recent" className="space-y-4">
              {recentActivity?.length > 0 ? (
                <div className="space-y-3">
                  {recentActivity.map((purchase: any) => {
                    const purchaseDeal = purchase.deal || getDealById(purchase.dealId);
                    return (
                      <div
                        key={purchase.id}
                        className="flex items-center gap-4 p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                      >
                        {purchaseDeal && (
                          <img
                            src={purchaseDeal.image}
                            alt={purchaseDeal.title}
                            className="h-12 w-12 rounded object-cover flex-shrink-0"
                          />
                        )}
                        <div className="flex-grow">
                          <h4 className="font-medium">
                            {purchaseDeal ? purchaseDeal.title : 'Unknown Deal'}
                          </h4>
                          <div className="flex items-center gap-4 text-sm text-muted-foreground">
                            <span className="flex items-center gap-1">
                              <Calendar className="h-3 w-3" />
                              {formatDistanceToNow(new Date(purchase.createdAt), { addSuffix: true })}
                            </span>
                            <span>Qty: {purchase.quantity}</span>
                            <span className="font-medium">Rs. {purchase.totalPrice?.toFixed(2)}</span>
                          </div>
                        </div>
                        <div className="flex items-center gap-2">
                          <Badge
                            variant={purchase.redeemed ? "outline" : "default"}
                            className={purchase.redeemed ? "" : "bg-green-500"}
                          >
                            {purchase.redeemed ? "Used" : "Active"}
                          </Badge>
                          <Button variant="ghost" size="sm" asChild>
                            <Link href={`/dashboard/purchases/${purchase.id}`}>
                              View Voucher
                            </Link>
                          </Button>
                        </div>
                      </div>
                    );
                  })}
                </div>
              ) : (
                <div className="text-center p-8 border rounded-md">
                  <ShoppingBag className="h-10 w-10 mx-auto mb-4 text-muted-foreground" />
                  <h3 className="text-lg font-semibold mb-2">No purchases yet</h3>
                  <p className="text-muted-foreground mb-4">
                    Start exploring amazing deals and make your first purchase!
                  </p>
                  <Button asChild>
                    <Link href="/">Explore Deals</Link>
                  </Button>
                </div>
              )}
            </TabsContent>

            <TabsContent value="active" className="space-y-4">
              {recentActivity?.filter((p: any) => !p.redeemed).length > 0 ? (
                <div className="grid gap-4 md:grid-cols-2">
                  {recentActivity
                    .filter((p: any) => !p.redeemed)
                    .map((purchase: any) => {
                      const purchaseDeal = purchase.deal || getDealById(purchase.dealId);
                      return (
                        <Card key={purchase.id} className="border-green-200 bg-green-50">
                          <CardHeader className="pb-2">
                            <div className="flex justify-between items-start">
                              <div>
                                <CardTitle className="text-base">
                                  {purchaseDeal ? purchaseDeal.title : 'Unknown Deal'}
                                </CardTitle>
                                <CardDescription>
                                  Purchased {formatDistanceToNow(new Date(purchase.createdAt), { addSuffix: true })}
                                </CardDescription>
                              </div>
                              <Badge className="bg-green-500">Active</Badge>
                            </div>
                          </CardHeader>
                          <CardContent>
                            <div className="bg-white p-3 rounded-md mb-3 border">
                              <div className="text-xs text-muted-foreground mb-1">Coupon Code:</div>
                              <code className="text-lg font-bold font-mono text-green-600">
                                {purchase.couponCode}
                              </code>
                            </div>

                            <div className="flex items-center text-sm text-muted-foreground mb-3">
                              <Clock className="h-4 w-4 mr-1" />
                              {purchaseDeal && (
                                <span>
                                  Valid until {formatDistanceToNow(new Date(purchaseDeal.endDate), { addSuffix: true })}
                                </span>
                              )}
                            </div>

                            <div className="flex gap-2">
                              <Button className="flex-1" size="sm" asChild>
                                <Link href={`/dashboard/purchases/${purchase.id}`}>
                                  View Voucher
                                </Link>
                              </Button>
                              <Button variant="outline" size="sm" asChild>
                                <Link href={getDealHref(purchaseDeal, purchase.dealId)}>
                                  Deal Page
                                </Link>
                              </Button>
                            </div>
                          </CardContent>
                        </Card>
                      );
                    })}
                </div>
              ) : (
                <div className="text-center p-8 border rounded-md">
                  <Tag className="h-10 w-10 mx-auto mb-4 text-muted-foreground" />
                  <h3 className="text-lg font-semibold mb-2">No active coupons</h3>
                  <p className="text-muted-foreground mb-4">
                    You don't have any active coupons at the moment.
                  </p>
                  <Button asChild>
                    <Link href="/">Find New Deals</Link>
                  </Button>
                </div>
              )}
            </TabsContent>
          </Tabs>
        </CardContent>
      </Card>
    </div>
  );
};

CustomerDashboard.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default CustomerDashboard;