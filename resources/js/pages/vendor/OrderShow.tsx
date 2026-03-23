import { useState } from 'react';
import Link from '@/components/Link';
import DashboardLayout from '@/layouts/DashboardLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'sonner';
import {
  ArrowLeft,
  Package,
  Clock,
  CheckCircle,
  XCircle,
  ShoppingBag,
  Mail,
  Calendar,
  Banknote,
  Receipt,
  Hash,
  Tag,
} from 'lucide-react';
import { format, formatDistanceToNow } from 'date-fns';
import { router } from '@inertiajs/react';

interface OrderItemDetail {
  id: number;
  dealId: number | null;
  dealOfferTypeId: number | null;
  dealSlug?: string | null;
  title: string;
  quantity: number;
  unitPrice: number;
  originalPrice: number;
  lineTotal: number;
  image: string;
  offerType: string;
}

interface VendorOrderDetail {
  id: string;
  orderId: number;
  customer: string;
  customerEmail: string;
  cancellationReason?: string | null;
  subtotal: number;
  discountTotal: number;
  taxTotal: number;
  total: number;
  currencyCode: string;
  paymentMethod: string | null;
  paymentReference: string | null;
  paidAt: string | null;
  quantity: number;
  status: string;
  date: string;
  items: OrderItemDetail[];
}

interface OrderShowProps {
  order: VendorOrderDetail;
}

type OrderStatus = 'pending' | 'paid' | 'fulfilled' | 'cancelled' | 'refunded';

const formatRs = (n: number) => `Rs. ${n.toFixed(2)}`;

const OrderShow = ({ order }: OrderShowProps) => {
  const [status, setStatus] = useState(order.status);
  const [updating, setUpdating] = useState(false);

  const updateOrderStatus = (newStatus: OrderStatus) => {
    const previous = status;
    setUpdating(true);
    setStatus(newStatus);

    router.patch(
      route('vendor.orders.status', order.orderId),
      { status: newStatus },
      {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          toast.success(`Order updated to ${newStatus}`);
        },
        onError: () => {
          setStatus(previous);
          toast.error('Could not update order status. Please try again.');
        },
        onFinish: () => {
          setUpdating(false);
        },
      }
    );
  };

  const getStatusIcon = (s: string) => {
    switch (s) {
      case 'pending':
        return <Clock className="h-4 w-4" />;
      case 'paid':
        return <Package className="h-4 w-4" />;
      case 'fulfilled':
        return <CheckCircle className="h-4 w-4" />;
      case 'cancelled':
      case 'refunded':
        return <XCircle className="h-4 w-4" />;
      default:
        return null;
    }
  };

  const getStatusBadgeClass = (s: string) => {
    switch (s) {
      case 'pending':
        return 'border-amber-500/50 bg-amber-500/10 text-amber-900 dark:text-amber-100';
      case 'paid':
        return 'border-blue-500/50 bg-blue-500/10 text-blue-900 dark:text-blue-100';
      case 'fulfilled':
        return 'border-emerald-500/50 bg-emerald-500/10 text-emerald-900 dark:text-emerald-100';
      case 'cancelled':
        return 'border-red-500/50 bg-red-500/10 text-red-900 dark:text-red-100';
      case 'refunded':
        return 'border-slate-500/50 bg-slate-500/10 text-slate-900 dark:text-slate-100';
      default:
        return '';
    }
  };

  const getSelectColor = (s: string) => {
    switch (s) {
      case 'pending':
        return 'bg-amber-500 text-white border-none';
      case 'paid':
        return 'bg-blue-500 text-white border-none';
      case 'fulfilled':
        return 'bg-emerald-500 text-white border-none';
      case 'cancelled':
        return 'bg-red-500 text-white border-none';
      case 'refunded':
        return 'bg-slate-500 text-white border-none';
      default:
        return '';
    }
  };

  return (
    <div className="space-y-6 max-w-5xl">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div className="space-y-1">
          <Button variant="ghost" size="sm" className="-ml-2 w-fit gap-1.5 text-muted-foreground" asChild>
            <Link href={route('vendor.orders')}>
              <ArrowLeft className="h-4 w-4" />
              All orders
            </Link>
          </Button>
          <div className="flex flex-wrap items-center gap-3">
            <h1 className="text-2xl font-bold tracking-tight font-mono">{order.id}</h1>
            <Badge variant="outline" className={`gap-1 px-2.5 py-0.5 text-sm capitalize ${getStatusBadgeClass(status)}`}>
              {getStatusIcon(status)}
              {status}
            </Badge>
          </div>
          <p className="text-muted-foreground text-sm flex flex-wrap items-center gap-x-3 gap-y-1">
            <span className="inline-flex items-center gap-1.5">
              <Calendar className="h-3.5 w-3.5 shrink-0" />
              {order.date ? format(new Date(order.date), 'EEEE, MMM d, yyyy · h:mm a') : '—'}
            </span>
            {order.date && (
              <span className="text-muted-foreground/80">
                ({formatDistanceToNow(new Date(order.date), { addSuffix: true })})
              </span>
            )}
          </p>
        </div>

        <div className="flex flex-col gap-2 sm:items-end shrink-0">
          <span className="text-xs font-medium text-muted-foreground uppercase tracking-wide">Update status</span>
          <Select value={status} onValueChange={(v) => updateOrderStatus(v as OrderStatus)} disabled={updating}>
            <SelectTrigger className={`w-full sm:w-[200px] h-10 rounded-lg ${getSelectColor(status)}`}>
              <div className="flex items-center gap-2">
                {getStatusIcon(status)}
                <SelectValue className="capitalize" />
              </div>
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="pending">Pending</SelectItem>
              <SelectItem value="paid">Paid</SelectItem>
              <SelectItem value="fulfilled">Fulfilled</SelectItem>
              <SelectItem value="cancelled">Cancelled</SelectItem>
              <SelectItem value="refunded">Refunded</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <div className="grid gap-4 md:grid-cols-2">
        <Card className="border-border/80 shadow-sm">
          <CardHeader className="pb-3">
            <CardTitle className="text-base flex items-center gap-2">
              <Mail className="h-4 w-4 text-muted-foreground" />
              Customer
            </CardTitle>
            <CardDescription>Who placed this order</CardDescription>
          </CardHeader>
          <CardContent className="space-y-1 text-sm">
            <p className="text-lg font-semibold leading-tight">{order.customer}</p>
            <p className="text-muted-foreground break-all">{order.customerEmail || 'No email on file'}</p>
          </CardContent>
        </Card>

        <Card className="border-border/80 shadow-sm">
          <CardHeader className="pb-3">
            <CardTitle className="text-base flex items-center gap-2">
              <Banknote className="h-4 w-4 text-muted-foreground" />
              Payment
            </CardTitle>
            <CardDescription>How and when it was paid</CardDescription>
          </CardHeader>
          <CardContent className="space-y-3 text-sm">
            <div className="flex justify-between gap-4">
              <span className="text-muted-foreground">Method</span>
              <span className="font-medium text-right">{order.paymentMethod || '—'}</span>
            </div>
            {order.paymentReference && (
              <div className="flex justify-between gap-4">
                <span className="text-muted-foreground inline-flex items-center gap-1">
                  <Hash className="h-3.5 w-3.5" />
                  Reference
                </span>
                <span className="font-mono text-xs break-all text-right">{order.paymentReference}</span>
              </div>
            )}
            <div className="flex justify-between gap-4">
              <span className="text-muted-foreground">Paid at</span>
              <span className="font-medium text-right">
                {order.paidAt ? (
                  <>
                    {format(new Date(order.paidAt), 'MMM d, yyyy h:mm a')}
                    <span className="block text-xs font-normal text-muted-foreground">
                      {formatDistanceToNow(new Date(order.paidAt), { addSuffix: true })}
                    </span>
                  </>
                ) : (
                  '—'
                )}
              </span>
            </div>
            <div className="flex justify-between gap-4 text-xs text-muted-foreground pt-1 border-t">
              <span>Currency</span>
              <span>{order.currencyCode}</span>
            </div>
          </CardContent>
        </Card>
      </div>

      {order.cancellationReason && (
        <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-100">
          <span className="font-semibold">Cancellation reason: </span>
          {order.cancellationReason}
        </div>
      )}

      <Card className="border-border/80 shadow-sm overflow-hidden">
        <CardHeader className="pb-4 border-b bg-muted/30">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
              <CardTitle className="text-lg flex items-center gap-2">
                <Receipt className="h-5 w-5 text-muted-foreground" />
                Line items
              </CardTitle>
              <CardDescription>
                {order.quantity} unit{order.quantity !== 1 ? 's' : ''} across {order.items?.length ?? 0} line
                {order.items?.length !== 1 ? 's' : ''}
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b bg-muted/20 text-left text-xs uppercase tracking-wide text-muted-foreground">
                  <th className="px-4 py-3 font-medium">Item</th>
                  <th className="px-4 py-3 font-medium hidden sm:table-cell">Type</th>
                  <th className="px-4 py-3 font-medium text-right w-24">Qty</th>
                  <th className="px-4 py-3 font-medium text-right hidden md:table-cell w-32">Unit</th>
                  <th className="px-4 py-3 font-medium text-right w-36">Line total</th>
                </tr>
              </thead>
              <tbody className="divide-y">
                {order.items?.map((item) => (
                  <tr key={item.id} className="align-top hover:bg-muted/20 transition-colors">
                    <td className="px-4 py-4">
                      <div className="flex gap-4">
                        {item.dealId ? (
                          <Link href={route('vendor.deals.view', item.dealId)} className="shrink-0">
                            {item.image ? (
                              <img
                                src={item.image}
                                alt=""
                                className="h-16 w-16 rounded-xl object-cover border bg-background shadow-sm"
                              />
                            ) : (
                              <div className="h-16 w-16 rounded-xl bg-muted flex items-center justify-center border">
                                <ShoppingBag className="h-6 w-6 text-muted-foreground" />
                              </div>
                            )}
                          </Link>
                        ) : item.image ? (
                          <img
                            src={item.image}
                            alt=""
                            className="h-16 w-16 rounded-xl object-cover shrink-0 border bg-background shadow-sm"
                          />
                        ) : (
                          <div className="h-16 w-16 rounded-xl bg-muted flex items-center justify-center shrink-0 border">
                            <ShoppingBag className="h-6 w-6 text-muted-foreground" />
                          </div>
                        )}
                        <div className="min-w-0 pt-0.5">
                          {item.dealId ? (
                            <Link
                              href={route('vendor.deals.view', item.dealId)}
                              className="font-semibold text-base leading-snug hover:text-primary transition-colors block"
                            >
                              {item.title}
                            </Link>
                          ) : (
                            <p className="font-semibold text-base leading-snug">{item.title}</p>
                          )}
                          <p className="text-muted-foreground text-xs mt-1 sm:hidden">{item.offerType}</p>
                          {item.originalPrice > item.unitPrice && (
                            <p className="text-xs mt-1">
                              <span className="line-through text-muted-foreground">{formatRs(item.originalPrice)}</span>
                              <span className="text-emerald-600 font-medium ml-2">{formatRs(item.unitPrice)} each</span>
                            </p>
                          )}
                        </div>
                      </div>
                    </td>
                    <td className="px-4 py-4 text-muted-foreground hidden sm:table-cell align-middle">{item.offerType}</td>
                    <td className="px-4 py-4 text-right font-medium align-middle">{item.quantity}</td>
                    <td className="px-4 py-4 text-right hidden md:table-cell align-middle">
                      {item.originalPrice > item.unitPrice ? (
                        <span>
                          <span className="line-through text-muted-foreground text-xs block">{formatRs(item.originalPrice)}</span>
                          <span className="font-medium">{formatRs(item.unitPrice)}</span>
                        </span>
                      ) : (
                        formatRs(item.unitPrice)
                      )}
                    </td>
                    <td className="px-4 py-4 text-right font-semibold tabular-nums text-base align-middle">
                      {formatRs(item.lineTotal)}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <Separator />

          <div className="px-4 py-5 space-y-2 bg-muted/10 max-w-md ml-auto">
            <div className="flex justify-between text-sm">
              <span className="text-muted-foreground">Subtotal</span>
              <span className="tabular-nums">{formatRs(order.subtotal)}</span>
            </div>
            {order.discountTotal > 0 && (
              <div className="flex justify-between text-sm text-emerald-600">
                <span className="inline-flex items-center gap-1">
                  <Tag className="h-3.5 w-3.5" />
                  Discount
                </span>
                <span className="tabular-nums font-medium">− {formatRs(order.discountTotal)}</span>
              </div>
            )}
            {order.taxTotal > 0 && (
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Tax</span>
                <span className="tabular-nums">{formatRs(order.taxTotal)}</span>
              </div>
            )}
            <Separator className="my-3" />
            <div className="flex justify-between items-baseline gap-4">
              <span className="text-base font-semibold">Order total</span>
              <span className="text-2xl font-bold tabular-nums tracking-tight">{formatRs(order.total)}</span>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

OrderShow.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default OrderShow;
