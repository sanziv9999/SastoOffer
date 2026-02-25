
import { useState } from 'react';
import { Link } from 'react-router-dom';
import { 
  BarChart, 
  DollarSign, 
  Users, 
  ShoppingBag, 
  Tag, 
  Store,
  TrendingUp,
  AlertTriangle,
  FileText,
  CheckCircle,
  XCircle,
  Package,
  Search
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
import { users, vendors, deals, purchases } from '@/data/mockData';

const AdminDashboard = () => {
  const [searchTerm, setSearchTerm] = useState('');
  
  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    console.log('Searching for:', searchTerm);
  };
  
  // Filter for pending deals that need approval
  const pendingDeals = deals.filter(deal => deal.status === 'pending');
  
  // Total stats
  const totalUsers = users.filter(u => u.role === 'user').length;
  const totalVendors = vendors.length;
  const totalDeals = deals.length;
  const activeDeals = deals.filter(d => d.status === 'active').length;
  const totalRevenue = purchases.reduce((sum, purchase) => sum + purchase.totalPrice, 0);
  
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
            <div className="text-2xl font-bold">${totalRevenue.toFixed(2)}</div>
            <p className="text-xs text-muted-foreground">
              +18% from last month
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Users
            </CardTitle>
            <Users className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{totalUsers}</div>
            <p className="text-xs text-muted-foreground">
              {vendors.length} vendors
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
            <div className="text-2xl font-bold">{activeDeals}</div>
            <p className="text-xs text-muted-foreground">
              {pendingDeals.length} pending approval
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
            <div className="text-2xl font-bold">{purchases.length}</div>
            <p className="text-xs text-muted-foreground">
              {purchases.filter(p => p.redeemed).length} redeemed
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
          <Badge variant="outline">{pendingDeals.length}</Badge>
        </CardHeader>
        <CardContent>
          {pendingDeals.length > 0 ? (
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
                      const dealVendor = vendors.find(v => v.id === deal.vendorId);
                      return (
                        <tr 
                          key={deal.id} 
                          className="border-b transition-colors hover:bg-muted/50"
                        >
                          <td className="p-4 align-middle">
                            <div className="flex items-center gap-3">
                              <img 
                                src={deal.image} 
                                alt={deal.title}
                                className="h-10 w-10 rounded object-cover"
                              />
                              <div>
                                <div className="font-medium">
                                  {deal.title.length > 25 
                                    ? `${deal.title.substring(0, 25)}...` 
                                    : deal.title}
                                </div>
                                <div className="text-xs text-muted-foreground">
                                  ID: {deal.id}
                                </div>
                              </div>
                            </div>
                          </td>
                          <td className="p-4 align-middle">
                            {dealVendor ? dealVendor.businessName : 'Unknown Vendor'}
                          </td>
                          <td className="p-4 align-middle">
                            <div>
                              <div className="font-medium">${deal.discountedPrice.toFixed(2)}</div>
                              <div className="text-xs text-muted-foreground line-through">
                                ${deal.originalPrice.toFixed(2)}
                              </div>
                            </div>
                          </td>
                          <td className="p-4 align-middle">
                            <Badge variant="outline">
                              {deal.type === 'percentage' ? 'Discount' :
                               deal.type === 'fixed' ? 'Fixed Price' :
                               deal.type === 'bogo' ? 'BOGO' : 
                               deal.type}
                            </Badge>
                          </td>
                          <td className="p-4 align-middle">
                            {new Date(deal.createdAt).toLocaleDateString()}
                          </td>
                          <td className="p-4 align-middle text-right">
                            <div className="flex justify-end gap-2">
                              <Button 
                                variant="default" 
                                size="sm"
                                className="bg-green-500 hover:bg-green-600"
                              >
                                <CheckCircle className="h-4 w-4 mr-1" />
                                Approve
                              </Button>
                              <Button 
                                variant="destructive" 
                                size="sm"
                              >
                                <XCircle className="h-4 w-4 mr-1" />
                                Reject
                              </Button>
                              <Button 
                                variant="ghost" 
                                size="sm"
                                asChild
                              >
                                <Link to={`/deals/${deal.id}`}>
                                  View
                                </Link>
                              </Button>
                            </div>
                          </td>
                        </tr>
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
            <div className="h-[200px] flex items-center justify-center text-muted-foreground">
              <BarChart className="h-10 w-10" />
              <span className="ml-2">Chart would appear here</span>
            </div>
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
            <div className="h-[200px] flex items-center justify-center text-muted-foreground">
              <TrendingUp className="h-10 w-10" />
              <span className="ml-2">Chart would appear here</span>
            </div>
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
                  <Link to="/admin/users">
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
                      {users.slice(0, 5).map(user => (
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
                                  {user.name.charAt(0)}
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
                              {user.role.charAt(0).toUpperCase() + user.role.slice(1)}
                            </Badge>
                          </td>
                          <td className="p-4 align-middle">
                            {new Date(user.createdAt).toLocaleDateString()}
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
                  <Link to="/admin/vendors">
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
                      {vendors.map(vendor => (
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
                                  {vendor.businessName.charAt(0)}
                                </div>
                              )}
                              <div className="font-medium">{vendor.businessName}</div>
                            </div>
                          </td>
                          <td className="p-4 align-middle">
                            {vendor.contactEmail}
                          </td>
                          <td className="p-4 align-middle">
                            {vendor.averageRating.toFixed(1)}/5.0
                          </td>
                          <td className="p-4 align-middle">
                            {new Date(vendor.createdAt).toLocaleDateString()}
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
                    <Link to="/admin/deals">
                      View All Deals
                    </Link>
                  </Button>
                  <Button variant="outline" asChild>
                    <Link to="/admin/deals/pending">
                      Review Pending ({pendingDeals.length})
                    </Link>
                  </Button>
                </div>
              </div>
            </TabsContent>
            
            <TabsContent value="reports" className="space-y-4">
              <div className="border rounded-md p-8 text-center">
                <FileText className="h-10 w-10 mx-auto mb-4 text-muted-foreground" />
                <h3 className="text-lg font-semibold mb-2">Reports & Analytics</h3>
                <p className="text-muted-foreground mb-4">
                  Access detailed reports and analytics about platform performance.
                </p>
                <div className="flex flex-wrap justify-center gap-2">
                  <Button asChild>
                    <Link to="/admin/reports/revenue">
                      Revenue Reports
                    </Link>
                  </Button>
                  <Button variant="outline" asChild>
                    <Link to="/admin/reports/users">
                      User Analytics
                    </Link>
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
          <Badge>3</Badge>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <div className="flex items-start gap-4 p-4 border rounded-md bg-amber-50 border-amber-200">
              <AlertTriangle className="h-5 w-5 text-amber-500 mt-0.5" />
              <div>
                <h4 className="font-medium">3 deals are about to expire</h4>
                <p className="text-sm text-muted-foreground">
                  These deals will expire in the next 24 hours. You may want to notify vendors.
                </p>
                <Button variant="link" className="px-0" size="sm">
                  View Deals
                </Button>
              </div>
            </div>
            
            <div className="flex items-start gap-4 p-4 border rounded-md">
              <Users className="h-5 w-5 text-primary mt-0.5" />
              <div>
                <h4 className="font-medium">10 new users registered today</h4>
                <p className="text-sm text-muted-foreground">
                  User growth is up 15% compared to last week.
                </p>
                <Button variant="link" className="px-0" size="sm">
                  View User Reports
                </Button>
              </div>
            </div>
            
            <div className="flex items-start gap-4 p-4 border rounded-md">
              <FileText className="h-5 w-5 text-primary mt-0.5" />
              <div>
                <h4 className="font-medium">Monthly report is ready</h4>
                <p className="text-sm text-muted-foreground">
                  The April 2023 performance report is now available for download.
                </p>
                <Button variant="link" className="px-0" size="sm">
                  Download Report
                </Button>
              </div>
            </div>
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

export default AdminDashboard;
