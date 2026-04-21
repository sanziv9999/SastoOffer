import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart3, Banknote, ShoppingBag, Users } from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';
import { Badge } from '@/components/ui/badge';

interface VendorReportsProps {
  stats: any;
  topDeals: any[];
  monthlySales: Array<{ month: string; amount: number; orders: number }>;
  dailySales: Array<{ day: string; amount: number; orders: number }>;
  topCustomers: Array<{ userId?: number; name: string; email: string; orders: number; items: number; spent: number }>;
  offerMix: Array<{ label: string; itemsSold: number; revenue: number }>;
  categorySales: Array<{ label: string; itemsSold: number; revenue: number }>;
}

const VendorReports = ({
  stats,
  topDeals = [],
  monthlySales = [],
  dailySales = [],
  topCustomers = [],
  offerMix = [],
  categorySales = [],
}: VendorReportsProps) => {
  const formatCurrencyShort = (value: number): string => {
    if (value >= 1_000_000) return `Rs. ${(value / 1_000_000).toFixed(1)}M`;
    if (value >= 1_000) return `Rs. ${(value / 1_000).toFixed(1)}K`;
    return `Rs. ${value.toFixed(0)}`;
  };

  const statCards = [
    { label: 'Revenue', value: `Rs. ${stats?.totalRevenue?.toFixed(2) || '0.00'}`, icon: Banknote },
    { label: 'Orders', value: (stats?.totalOrders || 0).toString(), icon: BarChart3 },
    { label: 'Items Sold', value: (stats?.totalSales || 0).toString(), icon: ShoppingBag },
    { label: 'Avg Order', value: `Rs. ${stats?.avgOrderValue?.toFixed(2) || '0.00'}`, icon: Users },
  ];

  const maxMonthly = Math.max(...monthlySales.map((m) => m.amount), 1);
  const maxDaily = Math.max(...dailySales.map((d) => d.amount), 1);

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Reports</h1>
          <p className="text-muted-foreground">Top-selling products and customer buying insights.</p>
        </div>
        <Badge variant="outline" className="w-fit">Live data</Badge>
      </div>

      <div className="grid grid-cols-2 gap-3 md:grid-cols-2 lg:grid-cols-4">
        {statCards.map((s) => (
          <Card key={s.label}>
            <CardHeader className="pb-1 md:flex md:flex-row md:items-center md:justify-between md:space-y-0 md:pb-2">
              <CardTitle className="text-sm font-medium">{s.label}</CardTitle>
              <s.icon className="hidden md:block h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent className="pt-0">
              <div className="text-lg md:text-2xl font-bold">{s.value}</div>
            </CardContent>
          </Card>
        ))}
      </div>

      <div className="grid gap-4 lg:grid-cols-7">
        <Card className="lg:col-span-4">
          <CardHeader>
            <CardTitle>Monthly Revenue (NPR)</CardTitle>
            <CardDescription>Each bar = total order revenue for that month (last 6 months)</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="h-[220px] flex items-end gap-2">
              {monthlySales.map((m) => (
                <div key={m.month} className="flex-1 flex flex-col items-center gap-2">
                  <div className="w-full h-40 flex items-end justify-center">
                    <div
                      className="w-full max-w-[34px] bg-primary/70 rounded-t"
                      style={{ height: `${Math.max((m.amount / maxMonthly) * 100, 4)}%` }}
                      title={`${m.month}: Rs. ${m.amount.toLocaleString()} from ${m.orders} orders`}
                    />
                  </div>
                  <span className="text-[10px] text-muted-foreground">{formatCurrencyShort(m.amount)}</span>
                  <span className="text-xs text-muted-foreground">{m.month}</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        <Card className="lg:col-span-3">
          <CardHeader>
            <CardTitle>Top Selling Products</CardTitle>
            <CardDescription>Ranked by units sold</CardDescription>
          </CardHeader>
          <CardContent className="space-y-3">
            {topDeals.slice(0, 6).map((deal) => (
              <div key={deal.id} className="rounded-lg border p-3">
                <p className="font-semibold text-sm line-clamp-2">{deal.title}</p>
                <div className="mt-1 text-xs text-muted-foreground">
                  {deal.quantitySold || 0} sold · {deal.ordersCount || 0} orders
                </div>
                <div className="mt-1 text-sm font-semibold">Rs. {(deal.revenue || 0).toLocaleString()}</div>
              </div>
            ))}
            {topDeals.length === 0 && <p className="text-sm text-muted-foreground">No product sales yet.</p>}
          </CardContent>
        </Card>
      </div>

      <div className="grid gap-4 lg:grid-cols-3">
        <Card>
          <CardHeader>
            <CardTitle className="text-base">What Customers Buy (Offer Mix)</CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            {offerMix.slice(0, 6).map((row) => (
              <div key={row.label} className="flex items-center justify-between text-sm">
                <span className="truncate pr-2">{row.label}</span>
                <span className="font-medium">{row.itemsSold} items</span>
              </div>
            ))}
            {offerMix.length === 0 && <p className="text-sm text-muted-foreground">No offer-type data yet.</p>}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="text-base">Category Demand</CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            {categorySales.slice(0, 6).map((row) => (
              <div key={row.label} className="flex items-center justify-between text-sm">
                <span className="truncate pr-2">{row.label}</span>
                <span className="font-medium">{row.itemsSold}</span>
              </div>
            ))}
            {categorySales.length === 0 && <p className="text-sm text-muted-foreground">No category demand yet.</p>}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="text-base">Top Customers</CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            {topCustomers.map((c) => (
              <div key={`${c.userId}-${c.name}`} className="rounded-md border p-2">
                <p className="text-sm font-medium truncate">{c.name}</p>
                <p className="text-xs text-muted-foreground">{c.orders} orders · {c.items} items</p>
                <p className="text-sm font-semibold mt-1">Rs. {c.spent.toLocaleString()}</p>
              </div>
            ))}
            {topCustomers.length === 0 && <p className="text-sm text-muted-foreground">No customer purchases yet.</p>}
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Daily Revenue Trend (NPR, last 7 days)</CardTitle>
          <CardDescription>Each bar = total order revenue for that day</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="h-[180px] flex items-end gap-2">
            {dailySales.map((d) => (
              <div key={d.day} className="flex-1 flex flex-col items-center gap-2">
                <div className="w-full h-28 flex items-end justify-center">
                  <div
                    className="w-full max-w-[28px] bg-blue-500/70 rounded-t"
                    style={{ height: `${Math.max((d.amount / maxDaily) * 100, 4)}%` }}
                    title={`${d.day}: Rs. ${d.amount.toLocaleString()} from ${d.orders} orders`}
                  />
                </div>
                <span className="text-[10px] text-muted-foreground">{formatCurrencyShort(d.amount)}</span>
                <span className="text-xs text-muted-foreground">{d.day}</span>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

VendorReports.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default VendorReports;

