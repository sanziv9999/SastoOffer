
import { useState, useEffect } from 'react';
import Link from '@/components/Link';
import {
  DollarSign,
  Users,
  ShoppingBag,
  Tag,
  Plus,
  Package,
  Filter,
  Search
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
import { formatDistanceToNow } from 'date-fns';
import DashboardLayout from '@/layouts/DashboardLayout';
import { useAuth } from '@/context/AuthContext';
import { vendors } from '@/data/mockData';
import { toast } from 'sonner';
import { usePage } from '@inertiajs/react';

interface VendorDashboardProps {
  vendor?: any;
  stats?: {
    totalRevenue: number;
    totalSales: number;
    activeDeals: number;
    totalDeals: number;
  };
  deals?: any[];
}

const VendorDashboard = ({ vendor: propVendor, stats: propStats, deals: propDeals }: VendorDashboardProps) => {
  const { user } = useAuth();
  const { flash } = usePage().props as any;
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  const [activeTab, setActiveTab] = useState('all');

  useEffect(() => {
    if (flash?.success) {
      toast.success(flash.success);
    }
    if (flash?.error) {
      toast.error(flash.error);
    }
  }, [flash]);

  // Find vendor profile from props or mock data
  const vendor = propVendor || vendors.find(v => v.contactEmail === user?.email || v.userId === user?.id) || vendors[0];

  // Get deals for this vendor - prioritize real data from props
  const vendorDeals = propDeals || [];

  // Default stats if not provided
  const stats = propStats || {
    totalRevenue: 0,
    totalSales: 0,
    activeDeals: vendorDeals.filter(d => d.status === 'active').length,
    totalDeals: vendorDeals.length
  };

  const filteredDeals = vendorDeals.filter(deal => {
    const matchesSearch = deal.title.toLowerCase().includes(searchTerm.toLowerCase()) || deal.id?.toString().includes(searchTerm);
    const matchesStatusDropdown = statusFilter === 'all' ? true : deal.status === statusFilter;
    const matchesTab = activeTab === 'all' ? true : deal.status === activeTab;
    return matchesSearch && matchesStatusDropdown && matchesTab;
  });

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
  };

  const monthlySales = [
    { month: 'Jan', amount: 1200 },
    { month: 'Feb', amount: 1800 },
    { month: 'Mar', amount: 1500 },
    { month: 'Apr', amount: 2200 },
    { month: 'May', amount: 2800 },
    { month: 'Jun', amount: 2400 },
  ];
  const maxSale = Math.max(...monthlySales.map(s => s.amount));

  const trafficData = [
    { source: 'Organic Search', value: 45, color: 'bg-blue-500' },
    { source: 'Direct', value: 25, color: 'bg-green-500' },
    { source: 'Social Media', value: 20, color: 'bg-purple-500' },
    { source: 'Referral', value: 10, color: 'bg-orange-500' },
  ];

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
            Manage your deals and track your business performance
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
            <Link href="/vendor/settings">
              Edit Profile
            </Link>
          </Button>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Total Revenue
            </CardTitle>
            <DollarSign className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">${stats?.totalRevenue?.toFixed(2) || '0.00'}</div>
            <p className="text-xs text-muted-foreground">
              +12% from last month
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Sales
            </CardTitle>
            <ShoppingBag className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats?.totalSales || 0}</div>
            <p className="text-xs text-muted-foreground">
              +5 since yesterday
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Active Deals
            </CardTitle>
            <Tag className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats?.activeDeals || 0}</div>
            <p className="text-xs text-muted-foreground">
              {(stats?.totalDeals || 0) - (stats?.activeDeals || 0)} inactive deals
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Customer Rating
            </CardTitle>
            <Users className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{vendor.averageRating || 'N/A'}</div>
            <p className="text-xs text-muted-foreground">
              Based on recent reviews
            </p>
          </CardContent>
        </Card>
      </div>

      {/* Charts */}
      <div className="grid gap-4 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Recent Sales</CardTitle>
            <CardDescription>
              Your recent sales activity
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="h-[200px] flex items-end justify-between gap-2 px-2 pt-4">
              {monthlySales.map((data) => (
                <div key={data.month} className="flex-1 flex flex-col items-center gap-2 group">
                  <div className="relative w-full flex items-end justify-center h-[150px]">
                    <div
                      className="w-full max-w-[30px] bg-primary/20 rounded-t-sm transition-all group-hover:bg-primary/40 relative"
                      style={{ height: `${(data.amount / maxSale) * 100}%` }}
                    >
                      <div className="absolute -top-7 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-foreground text-background text-[10px] font-bold px-1.5 py-0.5 rounded pointer-events-none">
                        ${data.amount}
                      </div>
                      <div className="absolute inset-x-0 bottom-0 h-1/2 bg-primary/20" />
                    </div>
                  </div>
                  <span className="text-xs font-bold text-muted-foreground">{data.month}</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Traffic Sources</CardTitle>
            <CardDescription>
              Where your customers come from
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="h-[200px] flex flex-col justify-center space-y-4 px-2">
              {trafficData.map((item) => (
                <div key={item.source} className="space-y-1">
                  <div className="flex justify-between text-xs font-medium">
                    <span>{item.source}</span>
                    <span className="text-muted-foreground">{item.value}%</span>
                  </div>
                  <div className="h-2 w-full bg-secondary rounded-full overflow-hidden">
                    <div
                      className={`h-full ${item.color} rounded-full`}
                      style={{ width: `${item.value}%` }}
                    />
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Manage Deals */}
      <Card>
        <CardHeader>
          <CardTitle>Manage Deals</CardTitle>
          <CardDescription>
            Create, edit, and monitor your deals
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
            <form onSubmit={handleSearch} className="flex w-full md:w-auto">
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
                  <SelectItem value="pending">Pending Approval</SelectItem>
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
              <TabsTrigger value="all">All Deals</TabsTrigger>
              <TabsTrigger value="active">Active</TabsTrigger>
              <TabsTrigger value="draft">Draft</TabsTrigger>
              <TabsTrigger value="expired">Expired</TabsTrigger>
            </TabsList>

            <TabsContent value={activeTab} className="space-y-4">
              {filteredDeals?.length > 0 ? (
                <div className="rounded-md border">
                  <div className="relative w-full overflow-auto">
                    <table className="w-full caption-bottom text-sm">
                      <thead className="border-b">
                        <tr className="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
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
                                  <img
                                    src={deal.image}
                                    alt={deal.title}
                                    className="h-10 w-10 rounded object-cover"
                                  />
                                )}
                                <div>
                                  <div className="font-medium">
                                    {deal.title.length > 30
                                      ? `${deal.title.substring(0, 30)}...`
                                      : deal.title}
                                  </div>
                                  <div className="text-xs text-muted-foreground">ID: {deal.id}</div>
                                </div>
                              </div>
                            </td>
                            <td className="p-4 align-middle">
                              <div>
                                <div className="font-medium">${deal.discountedPrice?.toFixed(2)}</div>
                                <div className="text-xs text-muted-foreground line-through">
                                  ${deal.originalPrice?.toFixed(2)}
                                </div>
                              </div>
                            </td>
                            <td className="p-4 align-middle">
                              <Badge
                                variant={
                                  deal.status === 'active' ? 'secondary' :
                                    deal.status === 'pending' ? 'outline' :
                                      deal.status === 'rejected' ? 'destructive' :
                                        'outline'
                                }
                                className={
                                  deal.status === 'active' ? 'bg-green-100 text-green-700 hover:bg-green-200 border-none' :
                                    deal.status === 'pending' ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200 border-none' :
                                      ''
                                }
                              >
                                {deal.status?.charAt(0).toUpperCase() + deal.status?.slice(1)}
                              </Badge>
                            </td>
                            <td className="p-4 align-middle">
                              <div>
                                <div className="font-medium">{deal.quantitySold || 0} sold</div>
                                <div className="text-xs text-muted-foreground">
                                  {deal.maxQuantity ? `of ${deal.maxQuantity}` : ''}
                                </div>
                              </div>
                            </td>
                            <td className="p-4 align-middle">
                              <span>
                                {deal.endDate ? formatDistanceToNow(new Date(deal.endDate), { addSuffix: true }) : 'Never'}
                              </span>
                            </td>
                            <td className="p-4 align-middle text-right">
                              <div className="flex justify-end gap-2">
                                <Button variant="outline" size="sm" asChild>
                                  <a href={`/vendor/deals/${deal.id}/edit`}>Edit</a>
                                </Button>
                                <Button variant="ghost" size="sm" asChild>
                                  <a href={`/deals/${deal.id}`}>View</a>
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
                    You haven't created any deals yet. Get started by creating your first deal.
                  </p>
                  <Button asChild>
                    <Link href="/vendor/deals/create">
                      <Plus className="mr-2 h-4 w-4" />
                      Create Your First Deal
                    </Link>
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

VendorDashboard.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default VendorDashboard;
