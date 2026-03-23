import { useState, useEffect } from 'react';
import Link from '@/components/Link';
import { useAuth } from '@/context/AuthContext';
import {
  ShoppingBag,
  Calendar,
  Clock,
  Tag,
  Star,
  Search
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

const UserDashboard = () => {
  const { user } = useAuth();
  const [stats, setStats] = useState<any>(null);
  const [deals, setDeals] = useState<any[]>([]);
  const [userPurchases, setUserPurchases] = useState<any[]>([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    setIsLoading(true);

    const controller = new AbortController();
    const load = async () => {
      try {
        const res = await fetch('/dashboard/data', {
          method: 'GET',
          headers: { 'Content-Type': 'application/json' },
          signal: controller.signal,
        });

        if (!res.ok) {
          throw new Error(`Failed to load dashboard data (${res.status})`);
        }

        const json = await res.json();
        setStats(json.stats || null);
        setDeals(Array.isArray(json.deals) ? json.deals : []);
        setUserPurchases(Array.isArray(json.purchases) ? json.purchases : []);
      } catch (e) {
        // If the API isn't reachable, keep rendering an empty dashboard state
        setStats(null);
        setDeals([]);
        setUserPurchases([]);
      } finally {
        setIsLoading(false);
      }
    };

    load();
    return () => controller.abort();
  }, [user]);

  const getDealById = (dealId: string | number) => {
    return deals.find(deal => deal.id === dealId);
  };

  const getDealHref = (dealLike?: any, fallbackId?: string | number) => {
    const key = dealLike?.slug ?? dealLike?.dealSlug ?? fallbackId;
    return key ? `/deals/${key}` : '/search';
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    // Local filter can be added later if needed.
    // For now, keep behavior as a no-op to avoid breaking UX.
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Welcome back, {user?.name}</h1>
          <p className="text-muted-foreground">
            Here's an overview of your purchases and saved deals.
          </p>
        </div>

        <form onSubmit={handleSearch} className="flex w-full md:w-auto">
          <Input
            placeholder="Search purchases..."
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
            <CardTitle className="text-sm font-medium">
              Total Purchases
            </CardTitle>
            <ShoppingBag className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats?.totalPurchases ?? userPurchases.length}</div>
            <p className="text-xs text-muted-foreground">
              Purchases across all deals
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Active Coupons
            </CardTitle>
            <Tag className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats?.activeCoupons ?? userPurchases.filter(p => !p.redeemed).length}</div>
            <p className="text-xs text-muted-foreground">
              Ready to redeem
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Savings
            </CardTitle>
            <div className="h-4 w-4 text-muted-foreground">Rs.</div>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">Rs. {typeof stats?.totalSavings === 'number' ? stats.totalSavings.toFixed(2) : '0.00'}</div>
            <p className="text-xs text-muted-foreground">
              Total savings from all deals
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Reviews
            </CardTitle>
            <Star className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats?.reviewsCount ?? 0}</div>
            <p className="text-xs text-muted-foreground">
              You've left {stats?.reviewsCount ?? 0} reviews
            </p>
          </CardContent>
        </Card>
      </div>

      {/* Tabs for purchases */}
      <Tabs defaultValue="all" className="space-y-4">
        <TabsList>
          <TabsTrigger value="all">All Purchases</TabsTrigger>
          <TabsTrigger value="active">Active Coupons</TabsTrigger>
          <TabsTrigger value="redeemed">Redeemed</TabsTrigger>
        </TabsList>

        <TabsContent value="all" className="space-y-4">
          <div className="rounded-md border">
            {isLoading ? (
              <div className="p-8 flex justify-center">
                <div className="animate-pulse space-y-4 w-full">
                  {[1, 2, 3].map((_, i) => (
                    <div key={i} className="h-16 bg-muted rounded w-full"></div>
                  ))}
                </div>
              </div>
            ) : userPurchases.length > 0 ? (
              <div className="relative w-full overflow-auto">
                <table className="w-full caption-bottom text-sm">
                  <thead className="border-b">
                    <tr className="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                      <th className="h-12 px-4 text-left align-middle font-medium">
                        Deal
                      </th>
                      <th className="h-12 px-4 text-left align-middle font-medium">
                        Purchased
                      </th>
                      <th className="h-12 px-4 text-left align-middle font-medium">
                        Price
                      </th>
                      <th className="h-12 px-4 text-left align-middle font-medium">
                        Status
                      </th>
                      <th className="h-12 px-4 text-left align-middle font-medium">
                        Coupon
                      </th>
                      <th className="h-12 px-4 text-right align-middle font-medium">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    {userPurchases.map(purchase => {
                          const purchaseDeal = getDealById(purchase.dealId);
                      return (
                        <tr
                          key={purchase.id}
                          className="border-b transition-colors hover:bg-muted/50"
                        >
                          <td className="p-4 align-middle">
                            <div className="flex items-center gap-3">
                              {purchaseDeal ? (
                                <>
                                  <img
                                    src={purchaseDeal.image}
                                    alt={purchaseDeal.title}
                                    className="h-10 w-10 rounded object-cover"
                                  />
                                  <div>
                                    <div className="font-medium">
                                      {purchaseDeal.title.length > 30
                                        ? `${purchaseDeal.title.substring(0, 30)}...`
                                        : purchaseDeal.title}
                                    </div>
                                    <div className="text-xs text-muted-foreground">
                                      Qty: {purchase.quantity}
                                    </div>
                                  </div>
                                </>
                              ) : (
                                <div className="font-medium">Unknown Deal</div>
                              )}
                            </div>
                          </td>
                          <td className="p-4 align-middle">
                            <div className="flex items-center gap-1">
                              <Calendar className="h-4 w-4 text-muted-foreground" />
                              <span>
                                {formatDistanceToNow(new Date(purchase.createdAt), { addSuffix: true })}
                              </span>
                            </div>
                          </td>
                          <td className="p-4 align-middle font-medium">
                            Rs. {purchase.totalPrice.toFixed(2)}
                          </td>
                          <td className="p-4 align-middle">
                            <Badge
                              variant={purchase.redeemed ? "outline" : "default"}
                              className={purchase.redeemed ? "" : "bg-green-500"}
                            >
                              {purchase.redeemed ? "Redeemed" : "Active"}
                            </Badge>
                          </td>
                          <td className="p-4 align-middle">
                            <code className="relative rounded bg-muted px-[0.3rem] py-[0.2rem] font-mono text-sm">
                              {purchase.couponCode}
                            </code>
                          </td>
                          <td className="p-4 align-middle text-right">
                            <Button
                              variant="outline"
                              size="sm"
                              asChild
                            >
                              <Link href={getDealHref(purchaseDeal, purchase.dealId)}>
                                View Deal
                              </Link>
                            </Button>
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            ) : (
              <div className="p-8 text-center">
                <h3 className="text-lg font-semibold mb-2">No purchases yet</h3>
                <p className="text-muted-foreground mb-4">
                  You haven't made any purchases yet.
                </p>
                <Button asChild>
                  <Link href="/">Explore Deals</Link>
                </Button>
              </div>
            )}
          </div>
        </TabsContent>

        <TabsContent value="active" className="space-y-4">
          {userPurchases.filter(p => !p.redeemed).length > 0 ? (
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
              {userPurchases
                .filter(p => !p.redeemed)
                .map(purchase => {
                  const purchaseDeal = getDealById(purchase.dealId);
                  return (
                    <Card key={purchase.id}>
                      <CardHeader className="p-4 pb-2">
                        <div className="flex justify-between items-start">
                          <div>
                            <CardTitle className="text-base">
                              {purchaseDeal ? purchaseDeal.title : 'Unknown Deal'}
                            </CardTitle>
                            <CardDescription className="text-xs">
                              Purchased {formatDistanceToNow(new Date(purchase.createdAt), { addSuffix: true })}
                            </CardDescription>
                          </div>
                          <Badge variant="outline" className="bg-green-500 text-white">
                            Active
                          </Badge>
                        </div>
                      </CardHeader>
                      <CardContent className="p-4 pt-2">
                        <div className="bg-muted p-3 rounded-md mb-3">
                          <div className="text-xs mb-1">Coupon Code:</div>
                          <code className="text-lg font-bold font-mono">
                            {purchase.couponCode}
                          </code>
                        </div>

                        <div className="flex items-center text-sm text-muted-foreground">
                          <Clock className="h-4 w-4 mr-1" />
                          {purchaseDeal && (
                            <span>
                              Valid until {formatDistanceToNow(new Date(purchaseDeal.endDate), { addSuffix: true })}
                            </span>
                          )}
                        </div>

                        <div className="flex gap-2 mt-4">
                          <Button className="w-full" size="sm">
                            Show QR Code
                          </Button>
                          <Button variant="outline" size="sm" asChild>
                            <Link href={getDealHref(purchaseDeal, purchase.dealId)}>
                              Details
                            </Link>
                          </Button>
                        </div>
                      </CardContent>
                    </Card>
                  );
                })}
            </div>
          ) : (
            <div className="p-8 text-center border rounded-md">
              <h3 className="text-lg font-semibold mb-2">No active coupons</h3>
              <p className="text-muted-foreground mb-4">
                You don't have any active coupons at the moment.
              </p>
              <Button asChild>
                <Link href="/">Explore Deals</Link>
              </Button>
            </div>
          )}
        </TabsContent>

        <TabsContent value="redeemed" className="space-y-4">
          {userPurchases.filter(p => p.redeemed).length > 0 ? (
            <div className="rounded-md border">
              <div className="relative w-full overflow-auto">
                <table className="w-full caption-bottom text-sm">
                  <thead className="border-b">
                    <tr className="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                      <th className="h-12 px-4 text-left align-middle font-medium">
                        Deal
                      </th>
                      <th className="h-12 px-4 text-left align-middle font-medium">
                        Purchased
                      </th>
                      <th className="h-12 px-4 text-left align-middle font-medium">
                        Redeemed
                      </th>
                      <th className="h-12 px-4 text-left align-middle font-medium">
                        Price
                      </th>
                      <th className="h-12 px-4 text-right align-middle font-medium">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    {userPurchases
                      .filter(p => p.redeemed)
                      .map(purchase => {
                        const purchaseDeal = getDealById(purchase.dealId);
                        return (
                          <tr
                            key={purchase.id}
                            className="border-b transition-colors hover:bg-muted/50"
                          >
                            <td className="p-4 align-middle">
                              <div className="flex items-center gap-3">
                                {purchaseDeal ? (
                                  <>
                                    <img
                                      src={purchaseDeal.image}
                                      alt={purchaseDeal.title}
                                      className="h-10 w-10 rounded object-cover"
                                    />
                                    <div>
                                      <div className="font-medium">
                                        {purchaseDeal.title.length > 30
                                          ? `${purchaseDeal.title.substring(0, 30)}...`
                                          : purchaseDeal.title}
                                      </div>
                                      <div className="text-xs text-muted-foreground">
                                        Qty: {purchase.quantity}
                                      </div>
                                    </div>
                                  </>
                                ) : (
                                  <div className="font-medium">Unknown Deal</div>
                                )}
                              </div>
                            </td>
                            <td className="p-4 align-middle">
                              <div className="flex items-center gap-1">
                                <Calendar className="h-4 w-4 text-muted-foreground" />
                                <span>
                                  {formatDistanceToNow(new Date(purchase.createdAt), { addSuffix: true })}
                                </span>
                              </div>
                            </td>
                            <td className="p-4 align-middle">
                              <div className="flex items-center gap-1">
                                <Clock className="h-4 w-4 text-muted-foreground" />
                                <span>
                                  {purchase.redeemedAt ?
                                    formatDistanceToNow(new Date(purchase.redeemedAt), { addSuffix: true }) :
                                    'N/A'}
                                </span>
                              </div>
                            </td>
                            <td className="p-4 align-middle font-medium">
                              Rs. {purchase.totalPrice.toFixed(2)}
                            </td>
                            <td className="p-4 align-middle text-right">
                              <Button
                                variant="outline"
                                size="sm"
                                asChild
                              >
                                <Link href={getDealHref(purchaseDeal, purchase.dealId)}>
                                  View Deal
                                </Link>
                              </Button>
                            </td>
                          </tr>
                        );
                      })}
                  </tbody>
                </table>
              </div>
            </div>
          ) : (
            <div className="p-8 text-center border rounded-md">
              <h3 className="text-lg font-semibold mb-2">No redeemed coupons</h3>
              <p className="text-muted-foreground mb-4">
                You haven't redeemed any coupons yet.
              </p>
              <Button asChild>
                <Link href="/dashboard">View Active Coupons</Link>
              </Button>
            </div>
          )}
        </TabsContent>
      </Tabs>
    </div>
  );
};

UserDashboard.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default UserDashboard;
