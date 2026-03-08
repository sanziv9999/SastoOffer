import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart, TrendingUp, DollarSign, ShoppingBag, Users, Eye } from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';

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

  const monthlySales = [
    { month: 'Jan', amount: 4500 },
    { month: 'Feb', amount: 5200 },
    { month: 'Mar', amount: 4800 },
    { month: 'Apr', amount: 6100 },
    { month: 'May', amount: 5900 },
    { month: 'Jun', amount: 7200 },
  ];

  const maxSale = Math.max(...monthlySales.map(s => s.amount));

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Analytics & Insights</h1>
          <p className="text-muted-foreground">Track your business performance and customer trends.</p>
        </div>
        <div className="flex items-center gap-2">
          <Badge variant="outline" className="px-3 py-1 bg-primary/5 text-primary border-primary/20 font-bold">
            Last 30 Days
          </Badge>
        </div>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {displayStats.map((stat) => (
          <Card key={stat.label} className="overflow-hidden border-none shadow-md bg-gradient-to-br from-background to-muted/30">
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-xs font-bold uppercase tracking-wider text-muted-foreground">{stat.label}</CardTitle>
              <div className="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                <stat.icon className="h-4 w-4 text-primary" />
              </div>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold tracking-tight">{stat.value}</div>
              {stat.change && (
                <div className="flex items-center mt-1">
                  <span className={`text-xs font-bold ${stat.change.startsWith('+') ? 'text-green-600' : 'text-red-600'}`}>
                    {stat.change}
                  </span>
                  <span className="text-[10px] text-muted-foreground ml-1 font-medium">vs last month</span>
                </div>
              )}
            </CardContent>
          </Card>
        ))}
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
        <Card className="lg:col-span-4">
          <CardHeader>
            <CardTitle>Sales Revenue</CardTitle>
            <CardDescription>Monthly revenue performance for the current year.</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="mt-4 h-[250px] flex items-end justify-between gap-2 px-2">
              {monthlySales.map((data) => (
                <div key={data.month} className="flex-1 flex flex-col items-center gap-2 group">
                  <div className="relative w-full flex items-end justify-center h-[200px]">
                    {/* Bar */}
                    <div
                      className="w-full max-w-[40px] bg-primary/20 rounded-t-sm transition-all group-hover:bg-primary/40 relative"
                      style={{ height: `${(data.amount / maxSale) * 100}%` }}
                    >
                      <div className="absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-foreground text-background text-[10px] font-bold px-1.5 py-0.5 rounded pointer-events-none">
                        ${data.amount}
                      </div>
                      {/* Inner highlight */}
                      <div className="absolute inset-x-0 bottom-0 h-1/2 bg-primary/20" />
                    </div>
                  </div>
                  <span className="text-xs font-bold text-muted-foreground">{data.month}</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        <Card className="lg:col-span-3">
          <CardHeader>
            <CardTitle>Top Performing Deals</CardTitle>
            <CardDescription>Best-sellers by units sold.</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-5">
              {topDeals?.length > 0 ? topDeals.slice(0, 5).map((deal) => (
                <div key={deal.id} className="flex items-center gap-3 group cursor-pointer">
                  <div className="h-10 w-10 rounded-lg overflow-hidden shrink-0 border bg-muted">
                    {deal.image ? (
                      <img src={deal.image} alt={deal.title} className="h-full w-full object-cover transition-transform group-hover:scale-110" />
                    ) : (
                      <div className="h-full w-full flex items-center justify-center text-xs font-bold">DEAL</div>
                    )}
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-bold truncate group-hover:text-primary transition-colors">{deal.title}</p>
                    <div className="flex items-center gap-2 mt-0.5">
                      <span className="text-[10px] font-bold bg-muted px-1.5 py-0.5 rounded text-muted-foreground uppercase">{deal.quantitySold || 0} Sold</span>
                      <span className="text-[10px] font-bold text-green-600">${deal.discountedPrice?.toFixed(2)}</span>
                    </div>
                  </div>
                  <div className="text-right">
                    <div className="text-xs font-bold">${((deal.quantitySold || 0) * (deal.discountedPrice || 0)).toLocaleString()}</div>
                    <div className="text-[10px] text-muted-foreground font-medium uppercase">Revenue</div>
                  </div>
                </div>
              )) : (
                <div className="flex flex-col items-center justify-center py-12 text-muted-foreground">
                  <ShoppingBag className="h-10 w-10 opacity-20 mb-2" />
                  <p className="text-sm">No sales data recorded yet.</p>
                </div>
              )}
            </div>
            {topDeals?.length > 5 && (
              <Button variant="ghost" className="w-full mt-4 text-xs font-bold text-primary" size="sm">
                View Full Inventory Report
              </Button>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

VendorAnalytics.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default VendorAnalytics;
