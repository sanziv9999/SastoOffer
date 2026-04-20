import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart, TrendingUp, Banknote, ShoppingBag, Users, Eye } from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';

interface VendorAnalyticsProps {
  stats: any;
  topDeals: any[];
  monthlySales: Array<{ month: string; amount: number; orders: number }>;
}

const VendorAnalytics = ({ stats, topDeals, monthlySales = [] }: VendorAnalyticsProps) => {
  const displayStats = [
    { label: 'Total Revenue', value: `Rs. ${stats?.totalRevenue?.toFixed(2) || '0.00'}`, icon: Banknote, change: stats?.revenueChange },
    { label: 'Total Items Sold', value: (stats?.totalSales || 0).toString(), icon: ShoppingBag, change: stats?.salesChange },
    { label: 'Total Orders', value: (stats?.totalOrders || 0).toString(), icon: BarChart, change: stats?.ordersChange },
    { label: 'Avg Order Value', value: `Rs. ${stats?.avgOrderValue?.toFixed(2) || '0.00'}`, icon: TrendingUp, change: stats?.aovChange },
    { label: 'Page Views', value: (stats?.pageViews || 0).toLocaleString(), icon: Eye, change: stats?.viewsChange },
    { label: 'Conversion Rate', value: `${stats?.conversionRate || 0}%`, icon: Users, change: stats?.conversionChange },
    { label: 'Active Deals', value: (stats?.activeDealsCount || 0).toString(), icon: ShoppingBag, change: '' },
  ];
  const maxSale = monthlySales.length > 0 ? Math.max(...monthlySales.map((s) => s.amount), 1) : 1;

  return (
    <div className="space-y-4 sm:space-y-6">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3 sm:gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold tracking-tight">Analytics & Insights</h1>
          <p className="text-sm sm:text-base text-muted-foreground">Track your business performance and customer trends.</p>
        </div>
        <div className="flex items-center gap-2">
          <Badge variant="outline" className="px-2.5 py-1 text-xs sm:text-sm bg-primary/5 text-primary border-primary/20 font-bold">
            Last 30 Days
          </Badge>
        </div>
      </div>

      <div className="grid gap-2.5 sm:gap-4 grid-cols-2 lg:grid-cols-3">
        {displayStats.map((stat) => (
          <Card key={stat.label} className="overflow-hidden border shadow-sm sm:shadow-md bg-gradient-to-br from-background to-muted/30">
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-1.5 px-3 pt-3 sm:pb-2 sm:px-6 sm:pt-6">
              <CardTitle className="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-muted-foreground leading-tight">{stat.label}</CardTitle>
              <div className="h-6 w-6 sm:h-8 sm:w-8 rounded-full bg-primary/10 flex items-center justify-center">
                <stat.icon className="h-3.5 w-3.5 sm:h-4 sm:w-4 text-primary" />
              </div>
            </CardHeader>
            <CardContent className="px-3 pb-3 sm:px-6 sm:pb-6">
              <div className="text-sm sm:text-2xl font-bold tracking-tight break-words">{stat.value}</div>
              {stat.change && (
                <div className="flex items-center mt-1">
                  <span className={`text-[10px] sm:text-xs font-bold ${stat.change.startsWith('+') ? 'text-green-600' : 'text-red-600'}`}>
                    {stat.change}
                  </span>
                  <span className="text-[9px] sm:text-[10px] text-muted-foreground ml-1 font-medium">vs last month</span>
                </div>
              )}
            </CardContent>
          </Card>
        ))}
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
        <Card className="lg:col-span-4">
          <CardHeader className="pb-3">
            <CardTitle className="text-base sm:text-lg">Sales Revenue</CardTitle>
            <CardDescription className="text-xs sm:text-sm">Monthly revenue based on actual order data.</CardDescription>
          </CardHeader>
          <CardContent>
            {monthlySales.length > 0 ? (
            <div className="mt-2 sm:mt-4 h-[190px] sm:h-[250px] flex items-end justify-between gap-1.5 sm:gap-2 px-1 sm:px-2">
              {monthlySales.map((data) => (
                <div key={data.month} className="flex-1 flex flex-col items-center gap-2 group">
                  <div className="relative w-full flex items-end justify-center h-[145px] sm:h-[200px]">
                    {/* Bar */}
                    <div
                      className="w-full max-w-[26px] sm:max-w-[40px] bg-primary/20 rounded-t-sm transition-all group-hover:bg-primary/40 relative"
                      style={{ height: `${Math.max((data.amount / maxSale) * 100, 4)}%` }}
                    >
                      <div className="absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-foreground text-background text-[10px] font-bold px-1.5 py-0.5 rounded pointer-events-none whitespace-nowrap z-10">
                        Rs. {data.amount} ({data.orders})
                      </div>
                      {/* Inner highlight */}
                      <div className="absolute inset-x-0 bottom-0 h-1/2 bg-primary/20" />
                    </div>
                  </div>
                  <span className="text-[10px] sm:text-xs font-bold text-muted-foreground">{data.month}</span>
                </div>
              ))}
            </div>
            ) : (
              <div className="h-[190px] sm:h-[250px] flex items-center justify-center text-muted-foreground text-xs sm:text-sm">
                No sales chart data yet.
              </div>
            )}
          </CardContent>
        </Card>

        <Card className="lg:col-span-3">
          <CardHeader className="pb-3">
            <CardTitle className="text-base sm:text-lg">Top Performing Deals</CardTitle>
            <CardDescription className="text-xs sm:text-sm">Best-sellers by units sold.</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-2.5 sm:space-y-4">
              {topDeals?.length > 0 ? topDeals.slice(0, 5).map((deal) => (
                <div key={deal.id} className="rounded-lg border border-border/70 p-2.5 sm:p-3 bg-background/70">
                  <div className="flex items-center gap-2.5 sm:gap-3 group cursor-pointer">
                    <div className="h-10 w-10 sm:h-11 sm:w-11 rounded-lg overflow-hidden shrink-0 border bg-muted">
                    {deal.image ? (
                      <img src={deal.image} alt={deal.title} className="h-full w-full object-cover transition-transform group-hover:scale-110" />
                    ) : (
                      <div className="h-full w-full flex items-center justify-center text-xs font-bold">DEAL</div>
                    )}
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-semibold leading-tight line-clamp-2 group-hover:text-primary transition-colors">
                        {deal.title}
                      </p>
                      <div className="flex flex-wrap items-center gap-1.5 mt-1">
                        <span className="text-[10px] font-bold bg-muted px-1.5 py-0.5 rounded text-muted-foreground uppercase">
                          {deal.quantitySold || 0} Sold
                        </span>
                        <span className="text-[11px] font-bold text-green-600">
                          Rs. {deal.discountedPrice?.toFixed(2)}
                        </span>
                      </div>
                    </div>
                    <div className="text-right shrink-0 pl-2">
                      <div className="text-xs font-bold tabular-nums">Rs. {(deal.revenue || 0).toLocaleString()}</div>
                      <div className="text-[10px] text-muted-foreground font-medium uppercase">Revenue</div>
                    </div>
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
