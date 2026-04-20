import Link from '@/components/Link';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Calendar, Package, Banknote, ShoppingBag, ArrowLeft } from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';
import { format, formatDistanceToNow } from 'date-fns';

interface CustomerHistoryProps {
  history?: any[];
  customer?: { id: number; name: string; email: string } | null;
  orders?: any[];
  boughtItems?: string[];
  claimedItems?: string[];
  boughtItemsDetailed?: Array<{ title: string; image?: string }>;
  claimedItemsDetailed?: Array<{ title: string; image?: string }>;
}

const statusBadge = (status: string) => {
  if (status === 'redeemed') return 'bg-emerald-500';
  if (status === 'paid') return 'bg-blue-500';
  if (status === 'pending') return 'bg-amber-500';
  if (status === 'cancelled') return 'bg-red-500';
  if (status === 'refunded') return 'bg-slate-500';
  return '';
};

const CustomerHistory = ({
  history = [],
  customer = null,
  orders = [],
  boughtItems = [],
  claimedItems = [],
  boughtItemsDetailed = [],
  claimedItemsDetailed = [],
}: CustomerHistoryProps) => {
  const hasDetailedData = !!customer;
  const sourceOrders = hasDetailedData ? orders : history;
  const totalRevenue = (sourceOrders || [])
    .filter((h: any) => ['completed', 'redeemed', 'paid'].includes(String(h.status)))
    .reduce((s: number, h: any) => s + Number(h.total || 0), 0);
  const totalOrders = sourceOrders?.length || 0;
  const totalClaimed = hasDetailedData
    ? (orders || []).reduce((sum: number, o: any) => sum + (o.items || []).filter((i: any) => i.isClaimed).length, 0)
    : 0;

  return (
    <div className="space-y-6">
      <div>
        <Link href={route('vendor.customers')} className="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground mb-2">
          <ArrowLeft className="h-4 w-4" />
          Back to Customers
        </Link>
        <h1 className="text-2xl font-bold tracking-tight">
          {hasDetailedData ? `${customer?.name} - Purchase History` : 'Customer History'}
        </h1>
        <p className="text-muted-foreground">
          {hasDetailedData ? customer?.email : 'Track customer purchase history and activity'}
        </p>
      </div>

      <div className="grid gap-3 grid-cols-2 md:grid-cols-4">
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Total Orders</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold">{totalOrders}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Revenue</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold text-primary">Rs. {totalRevenue.toFixed(2)}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Bought Items</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold text-green-600">{hasDetailedData ? boughtItems.length : history?.length || 0}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Claimed</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold text-red-500">{hasDetailedData ? totalClaimed : history?.filter((h: any) => h.status === 'refunded').length}</div></CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>{hasDetailedData ? 'Order Timeline' : 'Purchase History'}</CardTitle>
          <CardDescription>
            {hasDetailedData ? 'All orders and line items from this customer.' : 'Recent customer purchases and transactions'}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            {sourceOrders?.length > 0 ? sourceOrders.map((item: any) => (
              <div key={item.id} className="rounded-xl border bg-background/80 p-3 sm:p-4 space-y-3">
                <div className="flex items-start justify-between gap-3">
                  <div className="min-w-0">
                    <div className="font-semibold truncate">
                      {hasDetailedData ? item.orderNumber : item.customer}
                    </div>
                    <div className="text-xs sm:text-sm text-muted-foreground inline-flex items-center gap-1.5 mt-0.5 whitespace-nowrap">
                      <Calendar className="h-3 w-3" />
                      {item.date ? format(new Date(item.date), 'MMM d, yyyy') : item.date}
                      {item.date && (
                        <span className="text-xs">({formatDistanceToNow(new Date(item.date), { addSuffix: true })})</span>
                      )}
                    </div>
                  </div>
                  <div className="text-right shrink-0 space-y-1">
                    <div className="text-sm font-semibold tabular-nums">Rs. {Number(item.total || 0).toFixed(2)}</div>
                  </div>
                  <Badge variant="secondary" className={statusBadge(item.status)}>
                    {item.status}
                  </Badge>
                </div>

                {hasDetailedData && Array.isArray(item.items) && item.items.length > 0 && (
                  <div className="rounded-lg border bg-muted/20 p-2.5 sm:p-3 space-y-2">
                    {item.items.map((line: any) => (
                      <div key={line.id} className="flex items-start justify-between gap-2.5 text-sm">
                        <div className="min-w-0 flex items-start gap-2.5">
                          {line.image ? (
                            <img src={line.image} alt={line.title} className="h-9 w-9 rounded-md object-cover border shrink-0" />
                          ) : (
                            <div className="h-9 w-9 rounded-md border bg-muted flex items-center justify-center shrink-0">
                              <ShoppingBag className="h-3.5 w-3.5 text-muted-foreground" />
                            </div>
                          )}
                          <div className="min-w-0">
                            <p className="text-sm leading-snug break-words line-clamp-2">{line.title}</p>
                            <p className="text-[11px] text-muted-foreground mt-0.5">
                              {line.offerType} · Qty {line.quantity}
                              {line.isClaimed ? ' · Claimed' : ' · Not claimed'}
                            </p>
                          </div>
                        </div>
                        <span className="font-medium shrink-0 tabular-nums">Rs. {Number(line.lineTotal || 0).toFixed(2)}</span>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            )) : (
              <div className="text-center py-8 text-muted-foreground">
                No history records found.
              </div>
            )}
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

CustomerHistory.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default CustomerHistory;
