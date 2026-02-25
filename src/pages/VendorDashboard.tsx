
import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext';
import { 
  BarChart, 
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
  CardFooter, 
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
import { vendors, deals, purchases } from '@/data/mockData';
import { formatDistanceToNow } from 'date-fns';
import { Deal, Vendor } from '@/types';

const VendorDashboard = () => {
  const { user } = useAuth();
  const [vendor, setVendor] = useState<Vendor | null>(null);
  const [vendorDeals, setVendorDeals] = useState<Deal[]>([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [isLoading, setIsLoading] = useState(true);
  
  useEffect(() => {
    // Simulate API call to fetch vendor data
    setTimeout(() => {
      if (user?.role === 'vendor') {
        const foundVendor = vendors.find(v => v.userId === user.id);
        setVendor(foundVendor || null);
        
        if (foundVendor) {
          const foundDeals = deals.filter(deal => deal.vendorId === foundVendor.id);
          setVendorDeals(foundDeals);
        }
      }
      
      setIsLoading(false);
    }, 1000);
  }, [user]);
  
  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    // In a real app, this would filter from the backend
    console.log('Searching for:', searchTerm);
  };
  
  const getDealPurchases = (dealId: string) => {
    return purchases.filter(p => p.dealId === dealId);
  };
  
  // Stats calculations
  const totalDeals = vendorDeals.length;
  const activeDeals = vendorDeals.filter(deal => deal.status === 'active').length;
  const totalRevenue = deals.reduce((sum, deal) => {
    const dealPurchases = getDealPurchases(deal.id);
    return sum + dealPurchases.reduce((total, purchase) => total + purchase.totalPrice, 0);
  }, 0);
  const totalSales = deals.reduce((sum, deal) => {
    const dealPurchases = getDealPurchases(deal.id);
    return sum + dealPurchases.reduce((total, purchase) => total + purchase.quantity, 0);
  }, 0);
  
  if (isLoading) {
    return (
      <div className="animate-pulse space-y-6">
        <div className="h-8 bg-muted rounded w-1/4"></div>
        <div className="h-4 bg-muted rounded w-2/4"></div>
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          {[1, 2, 3, 4].map((_, i) => (
            <div key={i} className="h-28 bg-muted rounded"></div>
          ))}
        </div>
      </div>
    );
  }
  
  if (!vendor) {
    return (
      <div className="text-center py-12">
        <h2 className="text-xl font-bold mb-2">Vendor Profile Not Found</h2>
        <p className="text-muted-foreground mb-6">
          Your vendor profile is not set up yet. Please create a vendor profile to get started.
        </p>
        <Button>Create Vendor Profile</Button>
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
            <Link to="/vendor/create-deal">
              <Plus className="mr-2 h-4 w-4" />
              Create Deal
            </Link>
          </Button>
          
          <Button asChild variant="outline">
            <Link to="/vendor/profile">
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
            <div className="text-2xl font-bold">${totalRevenue.toFixed(2)}</div>
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
            <div className="text-2xl font-bold">{totalSales}</div>
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
            <div className="text-2xl font-bold">{activeDeals}</div>
            <p className="text-xs text-muted-foreground">
              {totalDeals - activeDeals} inactive deals
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
            <div className="text-2xl font-bold">{vendor.averageRating}</div>
            <p className="text-xs text-muted-foreground">
              Based on 15 reviews
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
            <div className="h-[200px] flex items-center justify-center text-muted-foreground">
              <BarChart className="h-10 w-10" />
              <span className="ml-2">Chart would appear here</span>
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
            <div className="h-[200px] flex items-center justify-center text-muted-foreground">
              <BarChart className="h-10 w-10" />
              <span className="ml-2">Chart would appear here</span>
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
              <Select defaultValue="all">
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
                <Link to="/vendor/create-deal">
                  <Plus className="mr-2 h-4 w-4" />
                  New Deal
                </Link>
              </Button>
            </div>
          </div>
          
          <Tabs defaultValue="all" className="space-y-4">
            <TabsList>
              <TabsTrigger value="all">All Deals</TabsTrigger>
              <TabsTrigger value="active">Active</TabsTrigger>
              <TabsTrigger value="draft">Draft</TabsTrigger>
              <TabsTrigger value="expired">Expired</TabsTrigger>
            </TabsList>
            
            <TabsContent value="all" className="space-y-4">
              {vendorDeals.length > 0 ? (
                <div className="rounded-md border">
                  <div className="relative w-full overflow-auto">
                    <table className="w-full caption-bottom text-sm">
                      <thead className="border-b">
                        <tr className="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                          <th className="h-12 px-4 text-left align-middle font-medium">
                            Deal
                          </th>
                          <th className="h-12 px-4 text-left align-middle font-medium">
                            Price
                          </th>
                          <th className="h-12 px-4 text-left align-middle font-medium">
                            Status
                          </th>
                          <th className="h-12 px-4 text-left align-middle font-medium">
                            Sales
                          </th>
                          <th className="h-12 px-4 text-left align-middle font-medium">
                            Expires
                          </th>
                          <th className="h-12 px-4 text-right align-middle font-medium">
                            Actions
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        {vendorDeals.map(deal => (
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
                                    {deal.title.length > 30 
                                      ? `${deal.title.substring(0, 30)}...` 
                                      : deal.title}
                                  </div>
                                  <div className="text-xs text-muted-foreground">
                                    ID: {deal.id}
                                  </div>
                                </div>
                              </div>
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
                              <Badge 
                                variant={
                                  deal.status === 'active' ? 'default' :
                                  deal.status === 'expired' ? 'secondary' :
                                  deal.status === 'draft' ? 'outline' :
                                  'destructive'
                                }
                                className={
                                  deal.status === 'active' ? 'bg-green-500' : undefined
                                }
                              >
                                {deal.status.charAt(0).toUpperCase() + deal.status.slice(1)}
                              </Badge>
                            </td>
                            <td className="p-4 align-middle">
                              <div>
                                <div className="font-medium">
                                  {deal.quantitySold || 0} sold
                                </div>
                                <div className="text-xs text-muted-foreground">
                                  {deal.maxQuantity ? `of ${deal.maxQuantity}` : ''}
                                </div>
                              </div>
                            </td>
                            <td className="p-4 align-middle">
                              <div className="flex items-center gap-1">
                                <span>
                                  {formatDistanceToNow(new Date(deal.endDate), { addSuffix: true })}
                                </span>
                              </div>
                            </td>
                            <td className="p-4 align-middle text-right">
                              <div className="flex justify-end gap-2">
                                <Button 
                                  variant="outline" 
                                  size="sm"
                                  asChild
                                >
                                  <Link to={`/vendor/deals/${deal.id}/edit`}>
                                    Edit
                                  </Link>
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
                    <Link to="/vendor/create-deal">
                      <Plus className="mr-2 h-4 w-4" />
                      Create Your First Deal
                    </Link>
                  </Button>
                </div>
              )}
            </TabsContent>
            
            <TabsContent value="active" className="space-y-4">
              {/* Content for active deals tab - similar structure to "all" tab */}
              {/* Filter the deals by status === 'active' */}
              <div className="rounded-md border p-8 text-center">
                <p>Active deals would be listed here.</p>
              </div>
            </TabsContent>
            
            <TabsContent value="draft" className="space-y-4">
              <div className="rounded-md border p-8 text-center">
                <p>Draft deals would be listed here.</p>
              </div>
            </TabsContent>
            
            <TabsContent value="expired" className="space-y-4">
              <div className="rounded-md border p-8 text-center">
                <p>Expired deals would be listed here.</p>
              </div>
            </TabsContent>
          </Tabs>
        </CardContent>
      </Card>
    </div>
  );
};

export default VendorDashboard;
