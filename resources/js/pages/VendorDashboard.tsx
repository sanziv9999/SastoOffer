
import { useState, useEffect } from 'react';
import Link from '@/components/Link';
import {
  Banknote,
  Users,
  ShoppingBag,
  Tag,
  Plus,
  Package,
  Filter,
  Search,
  Star,
  MessageSquare,
  ArrowUpRight,
  Clock,
} from 'lucide-react';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { formatDistanceToNow, format } from 'date-fns';
import DashboardLayout from '@/layouts/DashboardLayout';
import { useAuth } from '@/context/AuthContext';
import { toast } from 'sonner';
import { usePage } from '@inertiajs/react';

interface MonthlySale {
  month: string;
  amount: number;
  orders: number;
}

interface RecentOrder {
  id: string;
  customer: string;
  total: number;
  status: string;
  itemCount: number;
  date: string;
}

interface VendorDashboardProps {
  vendor?: any;
  stats?: {
    totalRevenue: number;
    totalSales: number;
    totalOrders: number;
    uniqueCustomers: number;
    activeDeals: number;
    totalDeals: number;
    totalReviews: number;
    avgRating: number;
  };
  deals?: any[];
  recentOrders?: RecentOrder[];
  monthlySales?: MonthlySale[];
}

const statusColors: Record<string, string> = {
  active: 'bg-green-100 text-green-700',
  pending: 'bg-yellow-100 text-yellow-700',
  paid: 'bg-blue-100 text-blue-700',
  redeemed: 'bg-emerald-100 text-emerald-700',
  cancelled: 'bg-red-100 text-red-700',
  refunded: 'bg-orange-100 text-orange-700',
  draft: 'bg-gray-100 text-gray-600',
  rejected: 'bg-red-100 text-red-700',
  expired: 'bg-gray-100 text-gray-600',
};

const VendorDashboard = ({
  vendor: propVendor,
  stats: propStats,
  deals: propDeals,
  recentOrders = [],
  monthlySales = [],
}: VendorDashboardProps) => {
  const { user } = useAuth();
  const { flash } = usePage().props as any;
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  const [activeTab, setActiveTab] = useState('all');

  useEffect(() => {
    if (flash?.success) toast.success(flash.success);
    if (flash?.error) toast.error(flash.error);
  }, [flash]);

  const vendor = propVendor;
  const vendorDeals = propDeals || [];

  const stats = propStats || {
    totalRevenue: 0,
    totalSales: 0,
    totalOrders: 0,
    uniqueCustomers: 0,
    activeDeals: vendorDeals.filter(d => d.status === 'active').length,
    totalDeals: vendorDeals.length,
    totalReviews: 0,
    avgRating: 0,
  };

  const filteredDeals = vendorDeals.filter(deal => {
    const matchesSearch = deal.title.toLowerCase().includes(searchTerm.toLowerCase()) || deal.id?.toString().includes(searchTerm);
    const matchesStatusDropdown = statusFilter === 'all' ? true : deal.status === statusFilter;
    const matchesTab = activeTab === 'all' ? true : deal.status === activeTab;
    return matchesSearch && matchesStatusDropdown && matchesTab;
  });

  const maxSale = monthlySales.length > 0 ? Math.max(...monthlySales.map(s => s.amount), 1) : 1;

  if (!vendor) {
    return (
      <div className="text-center py-12">
        <h2 className="text-xl font-bold mb-2">Vendor Profile Not Found</h2>
        <p className="text-muted-foreground mb-6">
          Your vendor profile is not set up yet. Please create a vendor profile to get started.
        </p>
        <Button asChild>
          <Link href="/vendor/settings">Create Vendor Profile</Link>
        </Button>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Vendor Dashboard</h1>
          <p className="text-muted-foreground">
            Welcome back, {vendor.business_name || user?.name}
          </p>
        </div>

        <div className="flex flex-col sm:flex-row gap-2">
          <Button asChild>
            <Link href="/vendor/deals/create">
              <Plus className="mr-2 h-4 w-4" />
              Create Deal
            </Link>
          </Button>
          <Button asChild variant="outline">
            <Link href="/vendor/settings">Edit Profile</Link>
          </Button>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
            <Banknote className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">Rs. {stats.totalRevenue.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
            <p className="text-xs text-muted-foreground">
              {stats.totalOrders} {stats.totalOrders === 1 ? 'order' : 'orders'} total
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Items Sold</CardTitle>
            <ShoppingBag className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats.totalSales}</div>
            <p className="text-xs text-muted-foreground">
              {stats.uniqueCustomers} unique {stats.uniqueCustomers === 1 ? 'customer' : 'customers'}
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Active Deals</CardTitle>
            <Tag className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats.activeDeals}</div>
            <p className="text-xs text-muted-foreground">
              {stats.totalDeals - stats.activeDeals} inactive
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Reviews</CardTitle>
            <MessageSquare className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold flex items-center gap-2">
              {stats.totalReviews}
              {stats.avgRating > 0 && (
                <span className="text-sm font-normal flex items-center gap-0.5 text-yellow-600">
                  <Star className="h-3.5 w-3.5 fill-yellow-500 text-yellow-500" />
                  {stats.avgRating}
                </span>
              )}
            </div>
            <p className="text-xs text-muted-foreground">
              {stats.avgRating > 0 ? `${stats.avgRating} avg rating` : 'No reviews yet'}
            </p>
          </CardContent>
        </Card>
      </div>

      {/* Charts + Recent Orders */}
      <div className="grid gap-4 md:grid-cols-2">
        {/* Monthly Sales Chart */}
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-base">Monthly Revenue</CardTitle>
            <CardDescription>
              {monthlySales.length > 0
                ? `Last ${monthlySales.length} months`
                : 'No sales data yet'
              }
            </CardDescription>
          </CardHeader>
          <CardContent>
            {monthlySales.length > 0 ? (
              <div className="h-[200px] flex items-end justify-between gap-2 px-2 pt-4">
                {monthlySales.map((data) => (
                  <div key={data.month} className="flex-1 flex flex-col items-center gap-2 group">
                    <div className="relative w-full flex items-end justify-center h-[150px]">
                      <div
                        className="w-full max-w-[30px] bg-primary/20 rounded-t-sm transition-all group-hover:bg-primary/40 relative"
                        style={{ height: `${Math.max((data.amount / maxSale) * 100, 4)}%` }}
                      >
                        <div className="absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-foreground text-background text-[10px] font-bold px-1.5 py-0.5 rounded pointer-events-none whitespace-nowrap z-10">
                          Rs. {data.amount.toLocaleString()} ({data.orders})
                        </div>
                        <div className="absolute inset-x-0 bottom-0 h-1/2 bg-primary/20" />
                      </div>
                    </div>
                    <span className="text-xs font-bold text-muted-foreground">{data.month}</span>
                  </div>
                ))}
              </div>
            ) : (
              <div className="h-[200px] flex items-center justify-center text-muted-foreground text-sm">
                Sales data will appear here once you have orders.
              </div>
            )}
          </CardContent>
        </Card>

        {/* Recent Orders */}
        <Card>
          <CardHeader className="pb-2 flex flex-row items-center justify-between">
            <div>
              <CardTitle className="text-base">Recent Orders</CardTitle>
              <CardDescription>Latest {recentOrders.length} orders</CardDescription>
            </div>
            <Button variant="ghost" size="sm" asChild>
              <Link href="/vendor/orders" className="text-xs">
                View All <ArrowUpRight className="ml-1 h-3 w-3" />
              </Link>
            </Button>
          </CardHeader>
          <CardContent>
            {recentOrders.length > 0 ? (
              <div className="space-y-3">
                {recentOrders.map((order) => (
                  <div key={order.id} className="flex items-center justify-between py-2 border-b last:border-0">
                    <div className="min-w-0 flex-1">
                      <div className="flex items-center gap-2">
                        <p className="text-sm font-medium truncate">{order.customer}</p>
                        <Badge variant="outline" className={`text-[10px] h-4 border-0 ${statusColors[order.status] || ''}`}>
                          {order.status}
                        </Badge>
                      </div>
                      <div className="flex items-center gap-2 text-xs text-muted-foreground">
                        <span className="font-mono">{order.id}</span>
                        <span>·</span>
                        <span>{order.itemCount} {order.itemCount === 1 ? 'item' : 'items'}</span>
                        <span>·</span>
                        <Clock className="h-3 w-3" />
                        <span>{formatDistanceToNow(new Date(order.date), { addSuffix: true })}</span>
                      </div>
                    </div>
                    <div className="text-sm font-bold ml-4">Rs. {order.total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="h-[200px] flex items-center justify-center text-muted-foreground text-sm">
                No orders yet. They'll appear here once customers purchase your deals.
              </div>
            )}
          </CardContent>
        </Card>
      </div>

      {/* Quick Links */}
      <div className="grid gap-3 grid-cols-2 md:grid-cols-4">
        <Button variant="outline" className="h-auto py-3 flex-col gap-1" asChild>
          <Link href="/vendor/orders">
            <ShoppingBag className="h-5 w-5 text-primary" />
            <span className="text-xs">Orders</span>
          </Link>
        </Button>
        <Button variant="outline" className="h-auto py-3 flex-col gap-1" asChild>
          <Link href="/vendor/customers">
            <Users className="h-5 w-5 text-primary" />
            <span className="text-xs">Customers</span>
          </Link>
        </Button>
        <Button variant="outline" className="h-auto py-3 flex-col gap-1" asChild>
          <Link href="/vendor/reviews">
            <Star className="h-5 w-5 text-primary" />
            <span className="text-xs">Reviews</span>
          </Link>
        </Button>
        <Button variant="outline" className="h-auto py-3 flex-col gap-1" asChild>
          <Link href="/vendor/analytics">
            <Banknote className="h-5 w-5 text-primary" />
            <span className="text-xs">Analytics</span>
          </Link>
        </Button>
      </div>

      {/* Manage Deals */}
      <Card>
        <CardHeader>
          <CardTitle>Manage Deals</CardTitle>
          <CardDescription>Create, edit, and monitor your deals</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
            <form onSubmit={(e) => e.preventDefault()} className="flex w-full md:w-auto">
              <Input
                placeholder="Search deals..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="rounded-r-none w-full md:w-80"
              />
              <Button type="submit" size="icon" className="rounded-l-none">
                <Search className="h-4 w-4" />
              </Button>
            </form>

            <div className="flex items-center gap-2 w-full md:w-auto">
              <Select value={statusFilter} onValueChange={setStatusFilter}>
                <SelectTrigger className="w-full md:w-40">
                  <Filter className="h-4 w-4 mr-2" />
                  <SelectValue placeholder="Status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Statuses</SelectItem>
                  <SelectItem value="active">Active</SelectItem>
                  <SelectItem value="draft">Draft</SelectItem>
                  <SelectItem value="expired">Expired</SelectItem>
                  <SelectItem value="pending">Pending</SelectItem>
                </SelectContent>
              </Select>

              <Button asChild>
                <Link href="/vendor/deals/create">
                  <Plus className="mr-2 h-4 w-4" />
                  New Deal
                </Link>
              </Button>
            </div>
          </div>

          <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-4">
            <TabsList>
              <TabsTrigger value="all">All ({vendorDeals.length})</TabsTrigger>
              <TabsTrigger value="active">Active ({vendorDeals.filter(d => d.status === 'active').length})</TabsTrigger>
              <TabsTrigger value="draft">Draft ({vendorDeals.filter(d => d.status === 'draft').length})</TabsTrigger>
              <TabsTrigger value="pending">Pending ({vendorDeals.filter(d => d.status === 'pending').length})</TabsTrigger>
            </TabsList>

            <TabsContent value={activeTab} className="space-y-4">
              {filteredDeals?.length > 0 ? (
                <div className="rounded-md border">
                  <div className="relative w-full overflow-auto">
                    <table className="w-full caption-bottom text-sm">
                      <thead className="border-b">
                        <tr className="border-b transition-colors hover:bg-muted/50">
                          <th className="h-12 px-4 text-left align-middle font-medium">Deal</th>
                          <th className="h-12 px-4 text-left align-middle font-medium">Price</th>
                          <th className="h-12 px-4 text-left align-middle font-medium">Status</th>
                          <th className="h-12 px-4 text-left align-middle font-medium">Sales</th>
                          <th className="h-12 px-4 text-left align-middle font-medium">Expires</th>
                          <th className="h-12 px-4 text-right align-middle font-medium">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        {filteredDeals.map((deal: any) => (
                          <tr key={deal.id} className="border-b transition-colors hover:bg-muted/50">
                            <td className="p-4 align-middle">
                              <div className="flex items-center gap-3">
                                {deal.image && (
                                  <img src={deal.image} alt={deal.title} className="h-10 w-10 rounded object-cover" />
                                )}
                                <div>
                                  <div className="font-medium">
                                    {deal.title.length > 30 ? `${deal.title.substring(0, 30)}...` : deal.title}
                                  </div>
                                  <div className="text-xs text-muted-foreground">ID: {deal.id}</div>
                                </div>
                              </div>
                            </td>
                            <td className="p-4 align-middle">
                              <div>
                                <div className="font-medium">Rs. {deal.discountedPrice?.toFixed(2)}</div>
                                <div className="text-xs text-muted-foreground line-through">Rs. {deal.originalPrice?.toFixed(2)}</div>
                              </div>
                            </td>
                            <td className="p-4 align-middle">
                              <Badge variant="outline" className={`border-0 ${statusColors[deal.status] || ''}`}>
                                {deal.status?.charAt(0).toUpperCase() + deal.status?.slice(1)}
                              </Badge>
                            </td>
                            <td className="p-4 align-middle">
                              <div>
                                <div className="font-medium">{deal.quantitySold || 0} sold</div>
                                {deal.maxQuantity ? (
                                  <div className="text-xs text-muted-foreground">of {deal.maxQuantity}</div>
                                ) : null}
                              </div>
                            </td>
                            <td className="p-4 align-middle text-sm">
                              {deal.endDate ? formatDistanceToNow(new Date(deal.endDate), { addSuffix: true }) : 'No expiry'}
                            </td>
                            <td className="p-4 align-middle text-right">
                              <div className="flex justify-end gap-2">
                                <Button variant="outline" size="sm" asChild>
                                  <Link href={`/vendor/deals/${deal.id}/edit`}>Edit</Link>
                                </Button>
                                <Button variant="ghost" size="sm" asChild>
                                  <Link href={`/vendor/deals/${deal.id}`}>View</Link>
                                </Button>
                              </div>
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                </div>
              ) : (
                <div className="border rounded-md p-8 text-center">
                  <Package className="h-10 w-10 mx-auto mb-4 text-muted-foreground" />
                  <h3 className="text-lg font-semibold mb-2">No deals found</h3>
                  <p className="text-muted-foreground mb-4">
                    {vendorDeals.length === 0
                      ? "You haven't created any deals yet. Get started by creating your first deal."
                      : 'No deals match the current filters.'}
                  </p>
                  {vendorDeals.length === 0 && (
                    <Button asChild>
                      <Link href="/vendor/deals/create">
                        <Plus className="mr-2 h-4 w-4" />
                        Create Your First Deal
                      </Link>
                    </Button>
                  )}
                </div>
              )}
            </TabsContent>
          </Tabs>
        </CardContent>
      </Card>
    </div>
  );
};

VendorDashboard.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default VendorDashboard;
