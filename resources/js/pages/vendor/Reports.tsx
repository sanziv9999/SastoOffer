import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart3, Banknote, ShoppingBag, Users } from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import Link from '@/components/Link';

interface VendorReportsProps {
  stats: any;
  topDeals: any[];
  topCustomers: Array<{ userId?: number; name: string; email: string; orders: number; items: number; spent: number }>;
  offerMix: Array<{ label: string; itemsSold: number; revenue: number }>;
  categorySales: Array<{ label: string; itemsSold: number; revenue: number }>;
  detailedSales: Array<{
    orderId: number;
    orderNumber: string;
    date: string;
    status: string;
    customerName: string;
    customerEmail: string;
    dealTitle: string;
    offerType: string;
    quantity: number;
    unitPrice: number;
    lineTotal: number;
  }>;
}

const VendorReports = ({
  stats,
  topDeals = [],
  topCustomers = [],
  offerMix = [],
  categorySales = [],
  detailedSales = [],
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

  const topByCustomers = [...topDeals]
    .sort((a, b) => Number(b?.ordersCount || 0) - Number(a?.ordersCount || 0))
    .slice(0, 6);
  const topByRevenue = [...topDeals]
    .sort((a, b) => Number(b?.revenue || 0) - Number(a?.revenue || 0))
    .slice(0, 6);
  const maxCustomers = Math.max(...topByCustomers.map((d) => Number(d?.ordersCount || 0)), 1);
  const maxRevenue = Math.max(...topByRevenue.map((d) => Number(d?.revenue || 0)), 1);
  const revenueCoords = topByRevenue.map((deal, idx) => {
    const x = topByRevenue.length > 1 ? (idx / (topByRevenue.length - 1)) * 100 : 50;
    const y = 92 - (Number(deal?.revenue || 0) / maxRevenue) * 76;
    return { x, y: Math.max(8, Math.min(92, y)) };
  });
  const buildSmoothLinePath = (points: Array<{ x: number; y: number }>) => {
    if (points.length === 0) return '';
    if (points.length === 1) return `M ${points[0].x} ${points[0].y}`;
    let path = `M ${points[0].x} ${points[0].y}`;
    for (let i = 0; i < points.length - 1; i += 1) {
      const curr = points[i];
      const next = points[i + 1];
      const cx = (curr.x + next.x) / 2;
      path += ` Q ${cx} ${curr.y}, ${next.x} ${next.y}`;
    }
    return path;
  };
  const revenueLinePath = buildSmoothLinePath(revenueCoords);
  const revenueAreaPath = revenueCoords.length
    ? `${revenueLinePath} L ${revenueCoords[revenueCoords.length - 1].x} 92 L ${revenueCoords[0].x} 92 Z`
    : '';
  const yTicks = [0, 0.5, 1].map((ratio) => {
    const value = maxRevenue * ratio;
    const y = 92 - (value / Math.max(maxRevenue, 1)) * 76;
    return { y: Math.max(8, Math.min(92, y)), label: formatCurrencyShort(value) };
  });

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

      <div className="grid gap-4 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Top Products by Customers</CardTitle>
            <CardDescription>Bar chart based on how many orders each product received</CardDescription>
          </CardHeader>
          <CardContent className="space-y-3">
            {topByCustomers.length > 0 ? (
              topByCustomers.map((deal) => (
                <div key={deal.id} className="grid grid-cols-[minmax(0,1fr)_80px] items-center gap-2">
                  <div className="min-w-0">
                    <div className="flex items-center justify-between gap-2 text-xs mb-1">
                      <span className="truncate font-medium">{deal.title}</span>
                    </div>
                    <div className="h-2.5 rounded-full bg-muted overflow-hidden">
                      <div
                        className="h-full rounded-full bg-primary"
                        style={{ width: `${Math.max((Number(deal.ordersCount || 0) / maxCustomers) * 100, 6)}%` }}
                      />
                    </div>
                  </div>
                  <div className="text-right text-xs font-semibold text-muted-foreground">
                    {deal.ordersCount || 0} orders
                  </div>
                </div>
              ))
            ) : (
              <p className="text-sm text-muted-foreground">No customer-order data yet.</p>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Top Products by Revenue</CardTitle>
            <CardDescription>Wave chart of highest revenue products (no redundant list)</CardDescription>
          </CardHeader>
          <CardContent>
            {topByRevenue.length > 0 ? (
              <div className="h-[260px] rounded-lg border bg-muted/20 p-3">
                <svg viewBox="0 0 100 100" className="h-full w-full">
                  {yTicks.map((tick, idx) => (
                    <g key={`tick-${idx}`}>
                      <line
                        x1="0"
                        y1={tick.y}
                        x2="100"
                        y2={tick.y}
                        stroke="hsl(var(--border))"
                        strokeWidth="0.8"
                        strokeDasharray="2 2"
                      />
                      <text x="1" y={tick.y - 1.5} fontSize="3" fill="hsl(var(--muted-foreground))">
                        {tick.label}
                      </text>
                    </g>
                  ))}
                  <line x1="0" y1="92" x2="100" y2="92" stroke="hsl(var(--border))" strokeWidth="1" />
                  <line x1="0" y1="8" x2="0" y2="92" stroke="hsl(var(--border))" strokeWidth="1" />
                    <path
                      d={revenueAreaPath}
                      fill="hsl(var(--primary) / 0.14)"
                      stroke="none"
                    />
                    <path
                      d={revenueLinePath}
                      fill="none"
                      stroke="hsl(var(--primary))"
                      strokeWidth="2.2"
                      strokeLinejoin="round"
                      strokeLinecap="round"
                    />
                  {revenueCoords.map((p, idx) => (
                    <g key={`pt-${idx}`}>
                      <circle cx={p.x} cy={p.y} r="1.8" fill="hsl(var(--primary))" />
                      <text
                        x={p.x}
                        y="98"
                        textAnchor="middle"
                        fontSize="2.8"
                        fill="hsl(var(--muted-foreground))"
                      >
                        {String(topByRevenue[idx]?.title || '').slice(0, 12)}
                      </text>
                    </g>
                  ))}
                  </svg>
              </div>
            ) : (
              <p className="text-sm text-muted-foreground">No revenue data yet.</p>
            )}
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
          <CardTitle>Detailed Sales Report</CardTitle>
          <CardDescription>Which customer bought which product, with order and amount details</CardDescription>
        </CardHeader>
        <CardContent>
          {detailedSales.length === 0 ? (
            <p className="text-sm text-muted-foreground">No detailed sales records yet.</p>
          ) : (
            <>
              <div className="md:hidden space-y-2">
                {detailedSales.slice(0, 20).map((row) => (
                  <div key={`${row.orderId}-${row.dealTitle}-${row.offerType}`} className="rounded-md border p-3">
                    <p className="text-sm font-semibold line-clamp-1">{row.dealTitle}</p>
                    <p className="text-xs text-muted-foreground mt-0.5">{row.customerName} · {row.customerEmail || 'No email'}</p>
                    <p className="text-xs text-muted-foreground mt-0.5">Order #{row.orderNumber} · {row.offerType}</p>
                    <div className="mt-2 flex items-center justify-between text-xs">
                      <span>Qty: {row.quantity}</span>
                      <span className="font-semibold mr-2">Rs. {row.lineTotal.toLocaleString()}</span>
                      <Button size="sm" variant="outline" className="h-7 text-xs" asChild>
                        <Link href={route('vendor.reports.orders.show', row.orderId)}>View</Link>
                      </Button>
                    </div>
                  </div>
                ))}
              </div>
              <div className="hidden md:block rounded-md border overflow-auto">
                <table className="w-full min-w-[980px] text-sm">
                  <thead className="bg-muted/30 border-b">
                    <tr>
                      <th className="text-left p-3 font-medium">Date</th>
                      <th className="text-left p-3 font-medium">Customer</th>
                      <th className="text-left p-3 font-medium">Product</th>
                      <th className="text-left p-3 font-medium">Offer Type</th>
                      <th className="text-left p-3 font-medium">Order #</th>
                      <th className="text-left p-3 font-medium">Status</th>
                      <th className="text-right p-3 font-medium">Qty</th>
                      <th className="text-right p-3 font-medium">Unit Price</th>
                      <th className="text-right p-3 font-medium">Line Total</th>
                      <th className="text-right p-3 font-medium">View</th>
                    </tr>
                  </thead>
                  <tbody>
                    {detailedSales.map((row) => (
                      <tr key={`${row.orderId}-${row.dealTitle}-${row.offerType}`} className="border-b">
                        <td className="p-3 text-xs text-muted-foreground">{new Date(row.date).toLocaleDateString()}</td>
                        <td className="p-3">
                          <p className="font-medium">{row.customerName}</p>
                          <p className="text-xs text-muted-foreground">{row.customerEmail || 'No email'}</p>
                        </td>
                        <td className="p-3 max-w-[260px] truncate">{row.dealTitle}</td>
                        <td className="p-3">{row.offerType}</td>
                        <td className="p-3">{row.orderNumber}</td>
                        <td className="p-3 capitalize">{row.status}</td>
                        <td className="p-3 text-right">{row.quantity}</td>
                        <td className="p-3 text-right">Rs. {row.unitPrice.toLocaleString()}</td>
                        <td className="p-3 text-right font-semibold">Rs. {row.lineTotal.toLocaleString()}</td>
                        <td className="p-3 text-right">
                          <Button size="sm" variant="outline" asChild>
                            <Link href={route('vendor.reports.orders.show', row.orderId)}>View</Link>
                          </Button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </>
          )}
        </CardContent>
      </Card>
    </div>
  );
};

VendorReports.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default VendorReports;

