
import { useEffect, useState } from 'react';
import Link from '@/components/Link';
import {
  ShoppingBag,
  Calendar,
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
import { Badge } from '@/components/ui/badge';
import { formatDistanceToNow } from 'date-fns';
import DashboardLayout from '@/layouts/DashboardLayout';
import { useAuth } from '@/context/AuthContext';

interface CustomerDashboardProps {
  stats: {
    totalPurchases: number;
    activeOffers: number;
    totalSavings: number;
    favoriteDealsCount: number;
  };
  recommendations: any[];
  recentActivity: any[];
  deals: any[]; // Lookup for deal details if not provided in activity
  purchases: any[];
}

const CustomerDashboard = ({ stats, recommendations, recentActivity, deals, purchases }: CustomerDashboardProps) => {
  const { user } = useAuth();
  const [dashboardData, setDashboardData] = useState<{
    stats: CustomerDashboardProps['stats'];
    recommendations: any[];
    recentActivity: any[];
    deals: any[];
    purchases: any[];
  }>({
    stats: stats || {
      totalPurchases: 0,
      activeOffers: 0,
      totalSavings: 0,
      favoriteDealsCount: 0,
    },
    recommendations: recommendations || [],
    recentActivity: recentActivity || [],
    deals: deals || [],
    purchases: purchases || [],
  });

  useEffect(() => {
    const controller = new AbortController();

    const loadDashboardData = async () => {
      try {
        const res = await fetch('/dashboard/data', {
          method: 'GET',
          headers: { 'Content-Type': 'application/json' },
          signal: controller.signal,
        });

        if (!res.ok) return;
        const json = await res.json();

        setDashboardData({
          stats: json.stats || {
            totalPurchases: 0,
            activeOffers: 0,
            totalSavings: 0,
            favoriteDealsCount: 0,
          },
          recommendations: Array.isArray(json.recommendations) ? json.recommendations : [],
          recentActivity: Array.isArray(json.recentActivity) ? json.recentActivity : [],
          deals: Array.isArray(json.deals) ? json.deals : [],
          purchases: Array.isArray(json.purchases) ? json.purchases : [],
        });
      } catch {
        // Keep initial props when request fails.
      }
    };

    loadDashboardData();
    return () => controller.abort();
  }, []);

  const getDealById = (dealId: string | number) => {
    return dashboardData.deals?.find((deal: any) => deal.id === dealId);
  };

  const getDealHref = (dealLike?: any, fallbackId?: string | number) => {
    const key = dealLike?.slug ?? dealLike?.dealSlug ?? fallbackId;
    return key ? `/deals/${key}` : '/search';
  };

  return (
    <div className="space-y-6">
      <div>
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Welcome back, {user?.name}!</h1>
          <p className="text-muted-foreground">
            Discover amazing deals and manage your purchases
          </p>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-2 gap-3 md:grid-cols-2 md:gap-4 lg:grid-cols-4">
        <Card>
          <CardHeader className="pb-1 md:flex md:flex-row md:items-center md:justify-between md:space-y-0 md:pb-2">
            <CardTitle className="text-sm font-medium">Total Purchases</CardTitle>
            <ShoppingBag className="hidden md:block h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent className="pt-0 md:pt-0">
            <div className="text-2xl font-bold">{dashboardData.stats?.totalPurchases || 0}</div>
            <p className="hidden md:block text-xs text-muted-foreground">
              Across all categories
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-1 md:flex md:flex-row md:items-center md:justify-between md:space-y-0 md:pb-2">
            <CardTitle className="text-sm font-medium">Active Offers</CardTitle>
            <Tag className="hidden md:block h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent className="pt-0 md:pt-0">
            <div className="text-2xl font-bold">{dashboardData.stats?.activeOffers || 0}</div>
            <p className="hidden md:block text-xs text-muted-foreground">
              Ready to redeem
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-1 md:flex md:flex-row md:items-center md:justify-between md:space-y-0 md:pb-2">
            <CardTitle className="text-sm font-medium">Total Savings</CardTitle>
            <Wallet className="hidden md:block h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent className="pt-0 md:pt-0">
            <div className="text-2xl font-bold">Rs. {dashboardData.stats?.totalSavings?.toFixed(2) || '0.00'}</div>
            <p className="hidden md:block text-xs text-muted-foreground">
              Money saved with deals
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-1 md:flex md:flex-row md:items-center md:justify-between md:space-y-0 md:pb-2">
            <CardTitle className="text-sm font-medium">Favorite Deals</CardTitle>
            <Heart className="hidden md:block h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent className="pt-0 md:pt-0">
            <div className="text-2xl font-bold">{dashboardData.stats?.favoriteDealsCount || 0}</div>
            <p className="hidden md:block text-xs text-muted-foreground">
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
              <a href="/search">
                <Search className="h-6 w-6 mb-2" />
                <span>Find Deals</span>
              </a>
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
            {dashboardData.recommendations?.map((deal: any) => (
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
                    <a href={getDealHref(deal, deal.id)}>
                      View Deal
                    </a>
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
          <CardDescription>Your latest purchases and offer usage</CardDescription>
        </CardHeader>
        <CardContent>
          <Tabs defaultValue="recent" className="space-y-4">
            <TabsList className="w-full justify-start overflow-x-auto">
              <TabsTrigger value="recent">Recent Purchases</TabsTrigger>
              <TabsTrigger value="active">Active Offers</TabsTrigger>
            </TabsList>

            <TabsContent value="recent" className="space-y-4">
              {dashboardData.recentActivity?.length > 0 ? (
                <div className="space-y-3">
                  {dashboardData.recentActivity.map((purchase: any) => {
                    const purchaseDeal = purchase.deal || getDealById(purchase.dealId);
                    return (
                      <div
                        key={purchase.id}
                        className="flex flex-col sm:flex-row sm:items-center gap-4 p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                      >
                        {purchaseDeal && (
                          <img
                            src={purchaseDeal.image}
                            alt={purchaseDeal.title}
                            className="h-12 w-12 rounded object-cover flex-shrink-0"
                          />
                        )}
                        <div className="flex-grow min-w-0">
                          <h4 className="font-medium">
                            {purchaseDeal ? purchaseDeal.title : 'Unknown Deal'}
                          </h4>
                          <div className="flex flex-wrap items-center gap-2 sm:gap-4 text-sm text-muted-foreground">
                            <span className="flex items-center gap-1">
                              <Calendar className="h-3 w-3" />
                              {formatDistanceToNow(new Date(purchase.createdAt), { addSuffix: true })}
                            </span>
                            <span>Qty: {purchase.quantity}</span>
                            <span className="font-medium">Rs. {purchase.totalPrice?.toFixed(2)}</span>
                          </div>
                        </div>
                        <div className="flex w-full sm:w-auto items-center justify-between sm:justify-end gap-2">
                          <Badge
                            variant={purchase.redeemed ? "outline" : "default"}
                            className={purchase.redeemed ? "" : "bg-green-500"}
                          >
                            {purchase.redeemed ? "Used" : "Active"}
                          </Badge>
                          <Button variant="ghost" size="sm" asChild>
                            <a href={getDealHref(purchaseDeal, purchase.dealId)}>
                              View Offer
                            </a>
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
                    <a href="/">Explore Deals</a>
                  </Button>
                </div>
              )}
            </TabsContent>

            <TabsContent value="active" className="space-y-4">
              {dashboardData.purchases?.filter((p: any) => !!p.activeOffer).length > 0 ? (
                <div className="space-y-3">
                  {dashboardData.purchases
                    .filter((p: any) => !!p.activeOffer)
                    .map((purchase: any) => {
                      const purchaseDeal = purchase.deal || getDealById(purchase.dealId);
                      return (
                      <div
                          key={purchase.id}
                          className="flex flex-col sm:flex-row sm:items-center gap-4 p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                        >
                          {purchaseDeal && (
                            <img
                              src={purchaseDeal.image}
                              alt={purchaseDeal.title}
                              className="h-12 w-12 rounded object-cover flex-shrink-0"
                            />
                          )}
                          <div className="flex-grow min-w-0">
                            <h4 className="font-medium">
                              {purchaseDeal ? purchaseDeal.title : 'Unknown Deal'}
                            </h4>
                            <div className="flex flex-wrap items-center gap-2 sm:gap-4 text-sm text-muted-foreground">
                              <span className="flex items-center gap-1">
                                <Calendar className="h-3 w-3" />
                                Purchased {formatDistanceToNow(new Date(purchase.createdAt), { addSuffix: true })}
                              </span>
                              <span>Qty: {purchase.quantity}</span>
                              <span className="font-medium">Rs. {purchase.totalPrice?.toFixed(2)}</span>
                            </div>
                          </div>
                          <div className="flex w-full sm:w-auto items-center justify-between sm:justify-end gap-2">
                            <Badge className="bg-green-500">Active</Badge>
                            <Button variant="ghost" size="sm" asChild>
                              <a href={getDealHref(purchaseDeal, purchase.dealId)}>
                                View Offer
                              </a>
                            </Button>
                          </div>
                        </div>
                      );
                    })}
                </div>
              ) : (
                <div className="text-center p-8 border rounded-md">
                  <Tag className="h-10 w-10 mx-auto mb-4 text-muted-foreground" />
                  <h3 className="text-lg font-semibold mb-2">No active offers</h3>
                  <p className="text-muted-foreground mb-4">
                    You don't have any active offers at the moment.
                  </p>
                  <Button asChild>
                    <a href="/">Find New Deals</a>
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