

import { useState } from 'react';
import Link from '@/components/Link';
import {
  DollarSign,
  Users,
  ShoppingBag,
  Tag,
  Store,
  AlertTriangle,
  FileText,
  CheckCircle,
  Package,
  Search,
  ArrowUpRight,
  ShieldCheck,
  LayoutDashboard,
  ChevronDown,
  ChevronUp
} from 'lucide-react';
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import DashboardLayout from '@/layouts/DashboardLayout';

interface AdminDashboardProps {
  stats: any;
  pendingDeals: any[];
  recentUsers: any[];
  vendorsList: any[];
  systemAlerts: any[];
  monthlyRevenue: Array<{ month: string; amount: number }>;
  userGrowth: Array<{ month: string; users: number }>;
  reportData?: {
    currentMonthRevenue?: number;
    previousMonthRevenue?: number;
    revenueChangeText?: string;
    newUsersThisMonth?: number;
    newVendorsThisMonth?: number;
    pendingDealsCount?: number;
  };
}

const AdminDashboard = ({
  stats,
  pendingDeals,
  recentUsers,
  vendorsList,
  systemAlerts,
  monthlyRevenue = [],
  userGrowth = [],
  reportData,
}: AdminDashboardProps) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [expandedDealIds, setExpandedDealIds] = useState<Set<number>>(new Set());

  const togglePendingDeal = (dealId: number) => {
    setExpandedDealIds((prev) => {
      const next = new Set(prev);
      if (next.has(dealId)) next.delete(dealId);
      else next.add(dealId);
      return next;
    });
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    // Search logic would normally trigger an Inertia visit or local filter
  };

  const maxRev = monthlyRevenue.length > 0 ? Math.max(...monthlyRevenue.map(d => d.amount), 1) : 1;
  const maxUsers = userGrowth.length > 0 ? Math.max(...userGrowth.map(d => d.users), 1) : 1;

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Admin Dashboard</h1>
          <p className="text-muted-foreground">
            Monitor platform performance and manage users, vendors, and deals
          </p>
        </div>

        <form onSubmit={handleSearch} className="flex w-full md:w-auto">
          <Input
            placeholder="Search users, deals..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="md:w-80 rounded-r-none"
          />
          <Button type="submit" size="icon" className="rounded-l-none">
            <Search className="h-4 w-4" />
          </Button>
        </form>
      </div>

      {/* Quick Access */}
      <div className="grid gap-3 grid-cols-2 md:grid-cols-4">
        <Button variant="outline" className="h-auto py-3 flex-col gap-1.5" asChild>
          <Link href="/admin/deals/pending">
            <ShieldCheck className="h-4 w-4 text-amber-600" />
            <span className="text-xs font-medium">Review Pending</span>
          </Link>
        </Button>
        <Button variant="outline" className="h-auto py-3 flex-col gap-1.5" asChild>
          <Link href="/admin/users">
            <Users className="h-4 w-4 text-blue-600" />
            <span className="text-xs font-medium">Manage Users</span>
          </Link>
        </Button>
        <Button variant="outline" className="h-auto py-3 flex-col gap-1.5" asChild>
          <Link href="/admin/vendors">
            <Store className="h-4 w-4 text-violet-600" />
            <span className="text-xs font-medium">Manage Vendors</span>
          </Link>
        </Button>
        <Button variant="outline" className="h-auto py-3 flex-col gap-1.5" asChild>
          <Link href="/admin/reports">
            <LayoutDashboard className="h-4 w-4 text-emerald-600" />
            <span className="text-xs font-medium">Platform Reports</span>
          </Link>
        </Button>
      </div>

      {/* Stats Cards */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card className="border-l-4 border-l-emerald-500">
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Total Revenue
            </CardTitle>
            <DollarSign className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">Rs. {stats?.totalRevenue?.toFixed(2) || '0.00'}</div>
            {stats?.revenueChange && <p className={`text-xs ${stats.revenueChange.startsWith('+') ? 'text-green-600' : 'text-red-600'}`}>
              {stats.revenueChange} from last month
            </p>}
          </CardContent>
        </Card>
        <Card className="border-l-4 border-l-blue-500">
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Users
            </CardTitle>
            <Users className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats?.totalUsers || 0}</div>
            <p className="text-xs text-muted-foreground">
              {stats?.totalVendors || 0} vendors
            </p>
          </CardContent>
        </Card>
        <Card className="border-l-4 border-l-violet-500">
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Active Deals
            </CardTitle>
            <Tag className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats?.activeDealsCount || 0}</div>
            <p className="text-xs text-muted-foreground">
              {pendingDeals?.length || 0} pending approval
            </p>
          </CardContent>
        </Card>
        <Card className="border-l-4 border-l-amber-500">
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Sales
            </CardTitle>
            <ShoppingBag className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats?.totalSales || 0}</div>
            <p className="text-xs text-muted-foreground">
              {stats?.redeemedSalesCount || 0} redeemed
            </p>
          </CardContent>
        </Card>
      </div>

      {/* Pending Approvals */}
      <Card>
        <CardHeader className="flex flex-row items-center justify-between">
          <div>
            <CardTitle>Pending Approvals</CardTitle>
            <CardDescription>
              Deals waiting for your approval
            </CardDescription>
          </div>
          <Badge variant="outline">{pendingDeals?.length || 0}</Badge>
        </CardHeader>
        <CardContent>
          {pendingDeals?.length > 0 ? (
            <div className="rounded-md border">
              <div className="relative w-full overflow-auto">
                <table className="w-full caption-bottom text-sm">
                  <thead className="border-b">
                    <tr className="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                      <th className="h-12 px-4 text-left align-middle font-medium">
                        Deal
                      </th>
                      <th className="h-12 px-4 text-left align-middle font-medium">
                        Vendor
                      </th>
                      <th className="h-12 px-4 text-left align-middle font-medium">
                        Price
                      </th>
                      <th className="h-12 px-4 text-left align-middle font-medium">
                        Type
                      </th>
                      <th className="h-12 px-4 text-left align-middle font-medium">
                        Submitted
                      </th>
                      <th className="h-12 px-4 text-right align-middle font-medium">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    {pendingDeals.map(deal => {
                      const isExpanded = expandedDealIds.has(deal.id);
                      const offers = Array.isArray(deal.offers) ? deal.offers : [];
                      return (
                        <>
                          <tr
                            key={deal.id}
                            className="border-b transition-colors hover:bg-muted/50 cursor-pointer"
                            onClick={() => togglePendingDeal(deal.id)}
                          >
                            <td className="p-4 align-middle">
                              <div className="flex items-center gap-3">
                                <div className="text-muted-foreground">
                                  {isExpanded ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}
                                </div>
                                {deal.image && <img
                                  src={deal.image}
                                  alt={deal.title}
                                  className="h-10 w-10 rounded object-cover"
                                />}
                                <div>
                                  <div className="font-medium">
                                    {deal.title.length > 25
                                      ? `${deal.title.substring(0, 25)}...`
                                      : deal.title}
                                  </div>
                                  <div className="text-xs text-muted-foreground">
                                    ID: {deal.id} · {offers.length} offer{offers.length !== 1 ? 's' : ''}
                                  </div>
                                </div>
                              </div>
                            </td>
                            <td className="p-4 align-middle">
                              {deal.vendorName || 'Unknown Vendor'}
                            </td>
                            <td className="p-4 align-middle">
                              <div>
                                <div className="font-medium">Rs. {deal.discountedPrice?.toFixed(2)}</div>
                                <div className="text-xs text-muted-foreground line-through">
                                  Rs. {deal.originalPrice?.toFixed(2)}
                                </div>
                              </div>
                            </td>
                            <td className="p-4 align-middle">
                              <Badge variant="outline">{deal.type}</Badge>
                            </td>
                            <td className="p-4 align-middle">
                              {deal.createdAt ? new Date(deal.createdAt).toLocaleDateString() : 'N/A'}
                            </td>
                            <td className="p-4 align-middle text-right" onClick={(e) => e.stopPropagation()}>
                              <div className="flex justify-end gap-2">
                                <Button
                                  variant="outline"
                                  size="sm"
                                  asChild
                                >
                                  <Link href={`/admin/deals/${deal.id}/view`}>
                                    Review <ArrowUpRight className="h-3.5 w-3.5 ml-1" />
                                  </Link>
                                </Button>
                              </div>
                            </td>
                          </tr>
                          {isExpanded && (
                            <tr>
                              <td colSpan={6} className="p-0">
                                <div className="bg-muted/20 border-b px-6 py-4">
                                  <h4 className="text-sm font-semibold mb-3">Child Offers</h4>
                                  {offers.length > 0 ? (
                                    <div className="space-y-2">
                                      {offers.map((offer: any) => (
                                        <div key={offer.id} className="flex items-center justify-between rounded-md border bg-background px-3 py-2 text-sm">
                                          <div>
                                            <p className="font-medium">{offer.offerTypeTitle}</p>
                                            <p className="text-xs text-muted-foreground">
                                              #{offer.id}
                                              {offer.endDate ? ` · Ends ${new Date(offer.endDate).toLocaleDateString()}` : ''}
                                            </p>
                                          </div>
                                          <div className="text-right">
                                            <p className="font-semibold">Rs. {Number(offer.discountedPrice ?? 0).toFixed(2)}</p>
                                            <p className="text-xs text-muted-foreground line-through">Rs. {Number(offer.originalPrice ?? 0).toFixed(2)}</p>
                                          </div>
                                        </div>
                                      ))}
                                    </div>
                                  ) : (
                                    <p className="text-sm text-muted-foreground">No child offers attached.</p>
                                  )}
                                </div>
                              </td>
                            </tr>
                          )}
                        </>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            </div>
          ) : (
            <div className="border rounded-md p-8 text-center">
              <CheckCircle className="h-10 w-10 mx-auto mb-4 text-green-500" />
              <h3 className="text-lg font-semibold mb-2">No pending approvals</h3>
              <p className="text-muted-foreground">
                All deals have been reviewed. Great job!
              </p>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Charts */}
      <div className="grid gap-4 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Revenue Overview</CardTitle>
            <CardDescription>
              Monthly platform revenue
            </CardDescription>
          </CardHeader>
          <CardContent>
            {monthlyRevenue.length > 0 ? (
            <div className="h-[200px] flex items-end gap-2 pt-4">
              {monthlyRevenue.map((data, idx) => (
                <div key={idx} className="flex flex-col items-center flex-1 gap-2 group relative">
                  <div className="w-full h-32 flex items-end justify-center relative">
                    <div
                      className="w-full max-w-[40px] bg-primary/80 rounded-t-sm transition-all duration-300 group-hover:bg-primary"
                      style={{ height: `${Math.max((data.amount / maxRev) * 100, 5)}%` }}
                    />
                    <div className="opacity-0 group-hover:opacity-100 absolute -top-10 bg-black text-white text-xs p-1.5 rounded pointer-events-none transition-opacity whitespace-nowrap z-10">
                      Rs. {data.amount.toLocaleString()}
                    </div>
                  </div>
                  <span className="text-xs text-muted-foreground font-medium">{data.month}</span>
                </div>
              ))}
            </div>
            ) : (
              <div className="h-[200px] flex items-center justify-center text-sm text-muted-foreground">
                No revenue data yet.
              </div>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Platform Growth</CardTitle>
            <CardDescription>
              User and vendor acquisition
            </CardDescription>
          </CardHeader>
          <CardContent>
            {userGrowth.length > 0 ? (
            <div className="h-[200px] flex items-end gap-2 pt-4">
              {userGrowth.map((data, idx) => (
                <div key={idx} className="flex flex-col items-center flex-1 gap-2 group relative">
                  <div className="w-full h-32 flex items-end justify-center relative border-b border-primary/20">
                    {/* We'll use a simulated line/area chart look with bars that touch */}
                    <div
                      className="w-full bg-blue-500/80 rounded-t-sm transition-all duration-300 group-hover:bg-blue-600"
                      style={{ height: `${Math.max((data.users / maxUsers) * 100, 5)}%` }}
                    />
                    <div className="opacity-0 group-hover:opacity-100 absolute -top-10 bg-black text-white text-xs p-1.5 rounded pointer-events-none transition-opacity whitespace-nowrap z-10">
                      {data.users} Users
                    </div>
                  </div>
                  <span className="text-xs text-muted-foreground font-medium">{data.month}</span>
                </div>
              ))}
            </div>
            ) : (
              <div className="h-[200px] flex items-center justify-center text-sm text-muted-foreground">
                No user growth data yet.
              </div>
            )}
          </CardContent>
        </Card>
      </div>

      {/* Management Tabs */}
      <Card>
        <CardHeader>
          <CardTitle>Platform Management</CardTitle>
          <CardDescription>
            Manage users, vendors, deals, and other platform settings
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Tabs defaultValue="users" className="space-y-4">
            <TabsList className="grid grid-cols-2 md:grid-cols-4 gap-2">
              <TabsTrigger value="users">
                <Users className="h-4 w-4 mr-2" />
                Users
              </TabsTrigger>
              <TabsTrigger value="vendors">
                <Store className="h-4 w-4 mr-2" />
                Vendors
              </TabsTrigger>
              <TabsTrigger value="deals">
                <Package className="h-4 w-4 mr-2" />
                Deals
              </TabsTrigger>
              <TabsTrigger value="reports">
                <FileText className="h-4 w-4 mr-2" />
                Reports
              </TabsTrigger>
            </TabsList>

            <TabsContent value="users" className="space-y-4">
              <div className="flex justify-between items-center mb-4">
                <h3 className="text-lg font-semibold">Registered Users</h3>
                <Button asChild>
                  <Link href="/admin/users">
                    View All Users
                  </Link>
                </Button>
              </div>

              <div className="rounded-md border">
                <div className="relative w-full overflow-auto">
                  <table className="w-full caption-bottom text-sm">
                    <thead className="border-b">
                      <tr className="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                        <th className="h-12 px-4 text-left align-middle font-medium">
                          User
                        </th>
                        <th className="h-12 px-4 text-left align-middle font-medium">
                          Email
                        </th>
                        <th className="h-12 px-4 text-left align-middle font-medium">
                          Role
                        </th>
                        <th className="h-12 px-4 text-left align-middle font-medium">
                          Joined
                        </th>
                        <th className="h-12 px-4 text-right align-middle font-medium">
                          Actions
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      {recentUsers?.map(user => (
                        <tr
                          key={user.id}
                          className="border-b transition-colors hover:bg-muted/50"
                        >
                          <td className="p-4 align-middle">
                            <div className="flex items-center gap-3">
                              {user.avatar ? (
                                <img
                                  src={user.avatar}
                                  alt={user.name}
                                  className="h-8 w-8 rounded-full object-cover"
                                />
                              ) : (
                                <div className="h-8 w-8 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                                  {user.name?.charAt(0)}
                                </div>
                              )}
                              <div className="font-medium">{user.name}</div>
                            </div>
                          </td>
                          <td className="p-4 align-middle">
                            {user.email}
                          </td>
                          <td className="p-4 align-middle">
                            <Badge
                              variant={
                                user.role === 'admin' ? 'default' :
                                  user.role === 'vendor' ? 'secondary' : 'outline'
                              }
                            >
                              {user.role?.charAt(0).toUpperCase() + user.role?.slice(1)}
                            </Badge>
                          </td>
                          <td className="p-4 align-middle">
                            {user.createdAt ? new Date(user.createdAt).toLocaleDateString() : 'N/A'}
                          </td>
                          <td className="p-4 align-middle text-right">
                            <Button variant="ghost" size="sm">
                              View
                            </Button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </TabsContent>

            <TabsContent value="vendors" className="space-y-4">
              <div className="flex justify-between items-center mb-4">
                <h3 className="text-lg font-semibold">Registered Vendors</h3>
                <Button asChild>
                  <Link href="/admin/vendors">
                    View All Vendors
                  </Link>
                </Button>
              </div>

              <div className="rounded-md border">
                <div className="relative w-full overflow-auto">
                  <table className="w-full caption-bottom text-sm">
                    <thead className="border-b">
                      <tr className="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                        <th className="h-12 px-4 text-left align-middle font-medium">
                          Business
                        </th>
                        <th className="h-12 px-4 text-left align-middle font-medium">
                          Contact
                        </th>
                        <th className="h-12 px-4 text-left align-middle font-medium">
                          Rating
                        </th>
                        <th className="h-12 px-4 text-left align-middle font-medium">
                          Joined
                        </th>
                        <th className="h-12 px-4 text-right align-middle font-medium">
                          Actions
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      {vendorsList?.map(vendor => (
                        <tr
                          key={vendor.id}
                          className="border-b transition-colors hover:bg-muted/50"
                        >
                          <td className="p-4 align-middle">
                            <div className="flex items-center gap-3">
                              {vendor.logo ? (
                                <img
                                  src={vendor.logo}
                                  alt={vendor.businessName}
                                  className="h-8 w-8 rounded-full object-cover"
                                />
                              ) : (
                                <div className="h-8 w-8 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                                  {vendor.businessName?.charAt(0)}
                                </div>
                              )}
                              <div className="font-medium">{vendor.businessName}</div>
                            </div>
                          </td>
                          <td className="p-4 align-middle">
                            {vendor.contactEmail}
                          </td>
                          <td className="p-4 align-middle">
                            {vendor.averageRating?.toFixed(1) || '0.0'}/5.0
                          </td>
                          <td className="p-4 align-middle">
                            {vendor.createdAt ? new Date(vendor.createdAt).toLocaleDateString() : 'N/A'}
                          </td>
                          <td className="p-4 align-middle text-right">
                            <div className="flex justify-end gap-2">
                              <Button variant="outline" size="sm">
                                View Deals
                              </Button>
                              <Button variant="ghost" size="sm">
                                View
                              </Button>
                            </div>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </TabsContent>

            <TabsContent value="deals" className="space-y-4">
              <div className="border rounded-md p-8 text-center">
                <Package className="h-10 w-10 mx-auto mb-4 text-muted-foreground" />
                <h3 className="text-lg font-semibold mb-2">Deal Management</h3>
                <p className="text-muted-foreground mb-4">
                  Monitor, approve, and manage deals across the platform.
                </p>
                <div className="flex flex-wrap justify-center gap-2">
                  <Button asChild>
                    <Link href="/admin/deals">
                      View All Deals
                    </Link>
                  </Button>
                  <Button variant="outline" asChild>
                    <Link href="/admin/deals/pending">
                      Review Pending ({pendingDeals?.length || 0})
                    </Link>
                  </Button>
                </div>
              </div>
            </TabsContent>

            <TabsContent value="reports" className="space-y-4">
              <div className="grid gap-4 md:grid-cols-3">
                <Card>
                  <CardHeader className="pb-2">
                    <CardTitle className="text-sm">Current Month Revenue</CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="text-xl font-bold">
                      Rs. {Number(reportData?.currentMonthRevenue ?? 0).toFixed(2)}
                    </div>
                    <p className={`text-xs ${(reportData?.revenueChangeText || '').startsWith('+') ? 'text-green-600' : 'text-red-600'}`}>
                      {reportData?.revenueChangeText || '0.0%'} vs last month
                    </p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardTitle className="text-sm">New Accounts (This Month)</CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="text-xl font-bold">{Number(reportData?.newUsersThisMonth ?? 0)}</div>
                    <p className="text-xs text-muted-foreground">
                      Includes customers + admins
                    </p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="pb-2">
                    <CardTitle className="text-sm">Vendor & Deal Queue</CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="text-xl font-bold">{Number(reportData?.newVendorsThisMonth ?? 0)} new vendors</div>
                    <p className="text-xs text-muted-foreground">
                      {Number(reportData?.pendingDealsCount ?? 0)} deals waiting approval
                    </p>
                  </CardContent>
                </Card>
              </div>

              <div className="border rounded-md p-6">
                <h3 className="text-lg font-semibold mb-2">Reports & Analytics</h3>
                <p className="text-muted-foreground mb-4">
                  Report section now reflects live platform metrics from the database.
                </p>
                <div className="flex flex-wrap gap-2">
                  <Button variant="outline" asChild>
                    <Link href="/admin/deals">Open Deal Reports</Link>
                  </Button>
                  <Button variant="outline" asChild>
                    <Link href="/admin/users">Open User Reports</Link>
                  </Button>
                  <Button variant="outline" asChild>
                    <Link href="/admin/vendors">Open Vendor Reports</Link>
                  </Button>
                </div>
              </div>
            </TabsContent>
          </Tabs>
        </CardContent>
      </Card>

      {/* System Alerts */}
      <Card>
        <CardHeader className="flex flex-row items-center justify-between">
          <div>
            <CardTitle>System Alerts</CardTitle>
            <CardDescription>
              Important system notifications
            </CardDescription>
          </div>
          <Badge>{systemAlerts?.length || 0}</Badge>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {systemAlerts?.length > 0 ? systemAlerts.map((alert: any, idx: number) => (
              <div key={idx} className={`flex items-start gap-4 p-4 border rounded-md ${alert.type === 'warning' ? 'bg-amber-50 border-amber-200' : ''}`}>
                {alert.type === 'warning' ? <AlertTriangle className="h-5 w-5 text-amber-500 mt-0.5" /> : <FileText className="h-5 w-5 text-primary mt-0.5" />}
                <div>
                  <h4 className="font-medium">{alert.title}</h4>
                  <p className="text-sm text-muted-foreground">
                    {alert.description}
                  </p>
                  {alert.actionLabel && (
                    <Button variant="link" className="px-0" size="sm">
                      {alert.actionLabel}
                    </Button>
                  )}
                </div>
              </div>
            )) : (
              <div className="text-center py-4 text-muted-foreground">
                No active system alerts.
              </div>
            )}
          </div>
        </CardContent>
        <CardFooter>
          <Button variant="outline" className="w-full">
            View All Notifications
          </Button>
        </CardFooter>
      </Card>
    </div>
  );
};

AdminDashboard.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default AdminDashboard;
