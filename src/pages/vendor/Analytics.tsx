import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart, TrendingUp, DollarSign, ShoppingBag, Users, Eye } from 'lucide-react';
import { deals, purchases, vendors } from '@/data/mockData';
import { useAuth } from '@/context/AuthContext';

const VendorAnalytics = () => {
  const { user } = useAuth();
  const vendor = vendors.find(v => v.userId === user?.id);
  const vendorDeals = vendor ? deals.filter(d => d.vendorId === vendor.id) : [];
  const vendorPurchases = purchases.filter(p => vendorDeals.some(d => d.id === p.dealId));

  const totalRevenue = vendorPurchases.reduce((sum, p) => sum + p.totalPrice, 0);
  const totalSales = vendorPurchases.reduce((sum, p) => sum + p.quantity, 0);
  const avgOrderValue = vendorPurchases.length > 0 ? totalRevenue / vendorPurchases.length : 0;

  const stats = [
    { label: 'Total Revenue', value: `$${totalRevenue.toFixed(2)}`, icon: DollarSign, change: '+12%' },
    { label: 'Total Sales', value: totalSales.toString(), icon: ShoppingBag, change: '+8%' },
    { label: 'Avg Order Value', value: `$${avgOrderValue.toFixed(2)}`, icon: TrendingUp, change: '+3%' },
    { label: 'Page Views', value: '2,847', icon: Eye, change: '+15%' },
    { label: 'Conversion Rate', value: '4.2%', icon: Users, change: '+0.5%' },
    { label: 'Active Deals', value: vendorDeals.filter(d => d.status === 'active').length.toString(), icon: BarChart, change: '' },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Analytics</h1>
        <p className="text-muted-foreground">Track your business performance and insights</p>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {stats.map((stat) => (
          <Card key={stat.label}>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">{stat.label}</CardTitle>
              <stat.icon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stat.value}</div>
              {stat.change && <p className="text-xs text-green-600">{stat.change} from last month</p>}
            </CardContent>
          </Card>
        ))}
      </div>

      <div className="grid gap-4 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Sales Over Time</CardTitle>
            <CardDescription>Monthly sales performance</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="h-[250px] flex items-center justify-center text-muted-foreground">
              <BarChart className="h-10 w-10" />
              <span className="ml-2">Sales chart would appear here</span>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Top Performing Deals</CardTitle>
            <CardDescription>Your best selling deals</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {vendorDeals.slice(0, 5).map((deal, i) => (
                <div key={deal.id} className="flex items-center gap-3">
                  <span className="text-sm font-bold text-muted-foreground w-6">#{i + 1}</span>
                  <img src={deal.image} alt={deal.title} className="h-8 w-8 rounded object-cover" />
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium truncate">{deal.title}</p>
                    <p className="text-xs text-muted-foreground">{deal.quantitySold || 0} sold</p>
                  </div>
                  <span className="text-sm font-medium">${deal.discountedPrice.toFixed(2)}</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default VendorAnalytics;
