
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart, TrendingUp, DollarSign, ShoppingBag, Users, Eye } from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';

interface VendorAnalyticsProps {
  stats: any;
  topDeals: any[];
}

const VendorAnalytics = ({ stats, topDeals }: VendorAnalyticsProps) => {
  const displayStats = [
    { label: 'Total Revenue', value: `$${stats?.totalRevenue?.toFixed(2) || '0.00'}`, icon: DollarSign, change: stats?.revenueChange },
    { label: 'Total Sales', value: (stats?.totalSales || 0).toString(), icon: ShoppingBag, change: stats?.salesChange },
    { label: 'Avg Order Value', value: `$${stats?.avgOrderValue?.toFixed(2) || '0.00'}`, icon: TrendingUp, change: stats?.aovChange },
    { label: 'Page Views', value: (stats?.pageViews || 0).toLocaleString(), icon: Eye, change: stats?.viewsChange },
    { label: 'Conversion Rate', value: `${stats?.conversionRate || 0}%`, icon: Users, change: stats?.conversionChange },
    { label: 'Active Deals', value: (stats?.activeDealsCount || 0).toString(), icon: BarChart, change: '' },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Analytics</h1>
        <p className="text-muted-foreground">Track your business performance and insights</p>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {displayStats.map((stat) => (
          <Card key={stat.label}>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">{stat.label}</CardTitle>
              <stat.icon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stat.value}</div>
              {stat.change && <p className={`text-xs ${stat.change.startsWith('+') ? 'text-green-600' : 'text-red-600'}`}>
                {stat.change} from last month
              </p>}
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
            <div className="h-[250px] flex items-center justify-center text-muted-foreground border border-dashed rounded-lg">
              <BarChart className="h-10 w-10" />
              <span className="ml-2">Sales chart placeholder</span>
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
              {topDeals?.length > 0 ? topDeals.map((deal, i) => (
                <div key={deal.id} className="flex items-center gap-3">
                  <span className="text-sm font-bold text-muted-foreground w-6">#{i + 1}</span>
                  {deal.image && <img src={deal.image} alt={deal.title} className="h-8 w-8 rounded object-cover" />}
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium truncate">{deal.title}</p>
                    <p className="text-xs text-muted-foreground">{deal.quantitySold || 0} sold</p>
                  </div>
                  <span className="text-sm font-medium">${deal.discountedPrice?.toFixed(2)}</span>
                </div>
              )) : (
                <div className="text-center py-8 text-muted-foreground">
                  No data available yet.
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

VendorAnalytics.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default VendorAnalytics;
