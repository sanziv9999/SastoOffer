import { ShoppingBag, Calendar, Tag, Package, Clock, CheckCircle, XCircle, CreditCard, Receipt, ChevronDown, ChevronUp, Store, Banknote, ArrowLeft } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { format, formatDistanceToNow } from 'date-fns';
import DashboardLayout from '@/layouts/DashboardLayout';
import { useState } from 'react';
import { router } from '@inertiajs/react';
import Link from '@/components/Link';

interface OrderItem {
  id: number;
  dealId: number | null;
  dealOfferTypeId: number | null;
  dealSlug: string | null;
  title: string;
  quantity: number;
  unitPrice: number;
  originalPrice: number;
  lineTotal: number;
  image: string;
  offerType: string;
  claimToken?: string | null;
  claimedAt?: string | null;
  isClaimed?: boolean;
}

interface Order {
  id: number;
  orderNumber: string;
  status: string;
  canCancel: boolean;
  cancellationReason?: string | null;
  subtotal: number;
  discountTotal: number;
  taxTotal: number;
  grandTotal: number;
  currencyCode: string;
  paymentMethod: string | null;
  paidAt: string | null;
  vendorName: string;
  vendorSlug: string | null;
  createdAt: string;
  itemCount: number;
  items: OrderItem[];
}

interface MyPurchasesProps {
  purchases: Order[];
}

const statusConfig: Record<string, { label: string; color: string; icon: React.ReactNode }> = {
  pending: { label: 'Pending', color: 'bg-amber-100 text-amber-800', icon: <Clock className="h-3 w-3" /> },
  paid: { label: 'Paid', color: 'bg-blue-100 text-blue-800', icon: <CreditCard className="h-3 w-3" /> },
  redeemed: { label: 'Redeemed', color: 'bg-green-100 text-green-800', icon: <CheckCircle className="h-3 w-3" /> },
  cancelled: { label: 'Cancelled', color: 'bg-red-100 text-red-800', icon: <XCircle className="h-3 w-3" /> },
  refunded: { label: 'Refunded', color: 'bg-gray-100 text-gray-800', icon: <XCircle className="h-3 w-3" /> },
};

const MyPurchases = ({ purchases }: MyPurchasesProps) => {
  const orders = purchases || [];
  const activeOrders = orders.filter(o => ['pending', 'paid'].includes(o.status));
  const completedOrders = orders.filter(o => ['redeemed', 'cancelled', 'refunded'].includes(o.status));
  const totalSpent = orders.reduce((sum, o) => sum + o.grandTotal, 0);
  const totalSaved = orders.reduce((sum, o) => sum + o.discountTotal, 0);
  const [expandedOrders, setExpandedOrders] = useState<Set<number>>(new Set());
  const [activeOrder, setActiveOrder] = useState<Order | null>(null);
  const [cancellingOrderNumber, setCancellingOrderNumber] = useState<string | null>(null);
  const [cancelTargetOrder, setCancelTargetOrder] = useState<Order | null>(null);
  const [cancelReason, setCancelReason] = useState('');

  const toggleExpand = (orderId: number) => {
    setExpandedOrders(prev => {
      const next = new Set(prev);
      next.has(orderId) ? next.delete(orderId) : next.add(orderId);
      return next;
    });
  };

  const hasDiscount = (item: OrderItem) => item.originalPrice > item.unitPrice;
  const getDealUrl = (item: OrderItem) => {
    const dealKey = item.dealSlug || item.dealId;
    if (!dealKey) {
      return null;
    }

    const base = route('deals.show.by-deal', { deal: dealKey });
    return item.dealOfferTypeId ? `${base}?offer=${item.dealOfferTypeId}` : base;
  };

  const openCancelDialog = (order: Order) => {
    if (!order.canCancel) {
      return;
    }
    setCancelTargetOrder(order);
    setCancelReason('');
  };

  const handleCancelOrderSubmit = () => {
    if (!cancelTargetOrder) {
      return;
    }
    if (cancelReason.trim().length < 5) {
      return;
    }

    setCancellingOrderNumber(cancelTargetOrder.orderNumber);
    router.patch(
      route('dashboard.purchases.cancel', { orderNumber: cancelTargetOrder.orderNumber }),
      { reason: cancelReason.trim() },
      {
        preserveScroll: true,
        onFinish: () => setCancellingOrderNumber(null),
        onSuccess: () => {
          setCancelTargetOrder(null);
          setCancelReason('');
          setActiveOrder(null);
        },
      },
    );
  };

  const renderOrderCard = (order: Order) => {
    const cfg = statusConfig[order.status] || statusConfig.pending;
    const isExpanded = expandedOrders.has(order.id);

    return (
      <div key={order.id} className="border rounded-xl bg-card hover:shadow-sm transition-all overflow-hidden">
        {/* Header */}
        <div
          className="flex items-center justify-between gap-4 p-4 border-b bg-muted/20 cursor-pointer"
          onClick={() => toggleExpand(order.id)}
        >
          <div className="min-w-0 flex-1">
            <div className="flex items-center gap-2 mb-0.5">
              <p className="font-mono text-xs text-muted-foreground">{order.orderNumber}</p>
              <span className="text-muted-foreground/40">·</span>
              <p className="text-xs text-muted-foreground">
                {format(new Date(order.createdAt), 'MMM d, yyyy')}
              </p>
            </div>
            <div className="flex items-center gap-1.5">
              <Store className="h-3.5 w-3.5 text-muted-foreground" />
              {order.vendorSlug ? (
                <a
                  href={`/vendor-profile/${order.vendorSlug}`}
                  className="text-sm font-medium text-teal-950 truncate hover:text-primary hover:underline"
                  onClick={(e) => e.stopPropagation()}
                >
                  {order.vendorName}
                </a>
              ) : (
                <span className="text-sm font-medium text-teal-950 truncate">
                  {order.vendorName}
                </span>
              )}
            </div>
          </div>
          <div className="flex items-center gap-2 flex-shrink-0">
            {order.canCancel && (
              <Button
                size="sm"
                variant="outline"
                className="h-7 text-[11px]"
                onClick={(e) => {
                  e.stopPropagation();
                  openCancelDialog(order);
                }}
                disabled={cancellingOrderNumber === order.orderNumber}
              >
                {cancellingOrderNumber === order.orderNumber ? 'Cancelling...' : 'Cancel'}
              </Button>
            )}
            <Button
              size="sm"
              variant="outline"
              className="h-7 text-[11px]"
              onClick={(e) => {
                e.stopPropagation();
                setActiveOrder(order);
              }}
            >
              View
            </Button>
            <span className={`inline-flex items-center gap-1.5 rounded-full text-xs font-semibold px-2.5 py-1 ${cfg.color}`}>
              {cfg.icon}
              {cfg.label}
            </span>
            {isExpanded ? (
              <ChevronUp className="h-4 w-4 text-muted-foreground" />
            ) : (
              <ChevronDown className="h-4 w-4 text-muted-foreground" />
            )}
          </div>
        </div>

        {/* Quick summary when collapsed */}
        {!isExpanded && (
          <div className="flex items-center justify-between px-4 py-3 border-b bg-muted/5">
            <span className="text-xs text-muted-foreground">
              {order.itemCount} item{order.itemCount !== 1 ? 's' : ''}
              <span className="mx-1.5 text-muted-foreground/40">·</span>
              {formatDistanceToNow(new Date(order.createdAt), { addSuffix: true })}
            </span>
            <span className="font-bold text-teal-950">Rs. {order.grandTotal.toFixed(2)}</span>
          </div>
        )}

        {/* Expanded item details */}
        {isExpanded && (
          <>
            <div className="divide-y">
              {order.items.map((item) => (
                <div key={item.id} className="flex items-center gap-3 p-3 px-4">
                  {item.image ? (
                    <button
                      type="button"
                      onClick={() => {
                        const url = getDealUrl(item);
                        if (url) window.location.href = url;
                      }}
                      className="flex-shrink-0 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                    >
                      <img src={item.image} alt={item.title} className="h-12 w-12 rounded-lg object-cover border hover:opacity-90 transition-opacity" />
                    </button>
                  ) : (
                    <button
                      type="button"
                      onClick={() => {
                        const url = getDealUrl(item);
                        if (url) window.location.href = url;
                      }}
                      className="h-12 w-12 rounded-lg bg-muted flex items-center justify-center flex-shrink-0"
                    >
                      <ShoppingBag className="h-4 w-4 text-muted-foreground" />
                    </button>
                  )}
                  <div className="flex-1 min-w-0">
                    <button
                      type="button"
                      onClick={() => {
                        const url = getDealUrl(item);
                        if (url) window.location.href = url;
                      }}
                      className="text-sm font-medium truncate block hover:text-primary transition-colors text-left max-w-full"
                    >
                      {item.title}
                    </button>
                    <div className="flex items-center gap-2 text-xs text-muted-foreground">
                      <span>{item.offerType}</span>
                      <span className="text-muted-foreground/40">·</span>
                      <span>Qty: {item.quantity}</span>
                    </div>
                    <div className="mt-1 text-[11px]">
                      <span className="text-muted-foreground">Claim code: </span>
                      <span className="font-mono font-semibold">{item.claimToken || 'Not available'}</span>
                      <span className="mx-1 text-muted-foreground/40">·</span>
                      <span className={item.isClaimed ? 'text-green-600 font-medium' : 'text-amber-600 font-medium'}>
                        {item.isClaimed ? 'Claimed' : 'Pending claim'}
                      </span>
                    </div>
                    {hasDiscount(item) && (
                      <div className="flex items-center gap-1.5 mt-0.5">
                        <span className="text-xs line-through text-muted-foreground/60">Rs. {item.originalPrice.toFixed(2)}</span>
                        <span className="text-xs font-medium text-green-600">Rs. {item.unitPrice.toFixed(2)}</span>
                      </div>
                    )}
                  </div>
                  <p className="text-sm font-semibold text-teal-950 flex-shrink-0">Rs. {item.lineTotal.toFixed(2)}</p>
                </div>
              ))}
            </div>

            {/* Financial breakdown */}
            <div className="px-4 py-3 border-t bg-muted/10 space-y-1.5">
              <div className="flex justify-between text-xs text-muted-foreground">
                <span>Subtotal ({order.itemCount} item{order.itemCount !== 1 ? 's' : ''})</span>
                <span>Rs. {order.subtotal.toFixed(2)}</span>
              </div>
              {order.discountTotal > 0 && (
                <div className="flex justify-between text-xs">
                  <span className="text-green-600">Savings</span>
                  <span className="text-green-600 font-medium">- Rs. {order.discountTotal.toFixed(2)}</span>
                </div>
              )}
              {order.taxTotal > 0 && (
                <div className="flex justify-between text-xs text-muted-foreground">
                  <span>Tax</span>
                  <span>Rs. {order.taxTotal.toFixed(2)}</span>
                </div>
              )}
              <div className="border-t pt-2 mt-2 flex justify-between items-center">
                <span className="text-sm font-bold text-teal-950">Total</span>
                <span className="text-base font-bold text-teal-950">Rs. {order.grandTotal.toFixed(2)}</span>
              </div>
            </div>

            {/* Meta info footer */}
            <div className="flex flex-wrap items-center gap-x-4 gap-y-1 px-4 py-2.5 border-t bg-muted/5 text-xs text-muted-foreground">
              <span className="flex items-center gap-1">
                <Calendar className="h-3 w-3" />
                {format(new Date(order.createdAt), 'MMM d, yyyy h:mm a')}
              </span>
              {order.paymentMethod && (
                <span className="flex items-center gap-1">
                  <Banknote className="h-3 w-3" />
                  {order.paymentMethod}
                </span>
              )}
              {order.paidAt && (
                <span className="flex items-center gap-1">
                  <CheckCircle className="h-3 w-3 text-green-500" />
                  Paid {formatDistanceToNow(new Date(order.paidAt), { addSuffix: true })}
                </span>
              )}
              {order.cancellationReason && (
                <span className="w-full text-red-600">
                  Cancel reason: {order.cancellationReason}
                </span>
              )}
            </div>
          </>
        )}
      </div>
    );
  };

  const renderOrderSinglePage = (order: Order) => {
    const cfg = statusConfig[order.status] || statusConfig.pending;
    const placedLabel = order.createdAt ? format(new Date(order.createdAt), 'MMM d, yyyy h:mm a') : '—';
    const paidAtLabel = order.paidAt ? formatDistanceToNow(new Date(order.paidAt), { addSuffix: true }) : null;

    const formatMoney = (value: number) => `Rs. ${value.toFixed(2)}`;

    return (
      <div className="space-y-6">
        <div className="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
          <div className="space-y-2">
            <div className="flex items-center gap-2">
              <Button variant="ghost" size="sm" className="h-8 px-2 gap-1" onClick={() => setActiveOrder(null)}>
                <ArrowLeft className="h-4 w-4" />
                Back
              </Button>
              <span className="text-xs text-muted-foreground font-mono">{order.orderNumber}</span>
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <span className={`inline-flex items-center gap-1.5 rounded-full text-xs font-semibold px-2.5 py-1 ${cfg.color}`}>
                {cfg.icon}
                {cfg.label}
              </span>
              <span className="text-xs text-muted-foreground">
                {placedLabel}
              </span>
            </div>
            <div className="text-sm text-muted-foreground">
              Vendor:{' '}
              {order.vendorSlug ? (
                <a
                  href={`/vendor-profile/${order.vendorSlug}`}
                  className="font-medium text-foreground hover:underline"
                >
                  {order.vendorName}
                </a>
              ) : (
                <span className="font-medium text-foreground">{order.vendorName}</span>
              )}
            </div>
          </div>

          <div className="flex flex-wrap items-center gap-2 justify-start sm:justify-end">
            {order.canCancel && (
              <Button
                size="sm"
                variant="outline"
                onClick={() => openCancelDialog(order)}
              >
                Cancel order
              </Button>
            )}
          </div>
        </div>

        {order.cancellationReason && (
          <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
            <span className="font-semibold">Cancellation reason:</span> {order.cancellationReason}
          </div>
        )}

        <div className="grid gap-4 md:grid-cols-3">
          <Card className="md:col-span-1 border-border/80 shadow-sm">
            <CardHeader className="pb-3">
              <CardTitle className="text-sm font-medium flex items-center gap-2">
                <Store className="h-4 w-4 text-muted-foreground" />
                Order meta
              </CardTitle>
              <CardDescription className="text-xs">Items and timing</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3 text-sm">
              <div className="flex items-center justify-between gap-4">
                <span className="text-muted-foreground">Items</span>
                <span className="font-semibold tabular-nums">{order.itemCount}</span>
              </div>
              <div className="flex items-center justify-between gap-4">
                <span className="text-muted-foreground">Placed</span>
                <span className="font-medium">{placedLabel}</span>
              </div>
              <div className="flex items-center justify-between gap-4">
                <span className="text-muted-foreground">Currency</span>
                <span className="font-mono text-xs">{order.currencyCode}</span>
              </div>
            </CardContent>
          </Card>

          <Card className="md:col-span-1 border-border/80 shadow-sm">
            <CardHeader className="pb-3">
              <CardTitle className="text-sm font-medium flex items-center gap-2">
                <Receipt className="h-4 w-4 text-muted-foreground" />
                Payment
              </CardTitle>
              <CardDescription className="text-xs">Method and paid time</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3 text-sm">
              <div className="flex items-center justify-between gap-4">
                <span className="text-muted-foreground">Method</span>
                <span className="font-medium">{order.paymentMethod || '—'}</span>
              </div>
              <div className="flex items-center justify-between gap-4">
                <span className="text-muted-foreground">Paid</span>
                <span className="font-medium">
                  {order.paidAt ? (
                    <>
                      {paidAtLabel}
                    </>
                  ) : (
                    '—'
                  )}
                </span>
              </div>
            </CardContent>
          </Card>

          <Card className="md:col-span-1 border-border/80 shadow-sm">
            <CardHeader className="pb-3">
              <CardTitle className="text-sm font-medium flex items-center gap-2">
                <Tag className="h-4 w-4 text-muted-foreground" />
                Summary
              </CardTitle>
              <CardDescription className="text-xs">Totals and savings</CardDescription>
            </CardHeader>
            <CardContent className="space-y-2 text-sm">
              <div className="flex items-center justify-between gap-4">
                <span className="text-muted-foreground">Subtotal</span>
                <span className="font-medium tabular-nums">{formatMoney(order.subtotal)}</span>
              </div>
              {order.discountTotal > 0 && (
                <div className="flex items-center justify-between gap-4">
                  <span className="text-emerald-600">Savings</span>
                  <span className="font-medium tabular-nums text-emerald-600">- {formatMoney(order.discountTotal)}</span>
                </div>
              )}
              {order.taxTotal > 0 && (
                <div className="flex items-center justify-between gap-4">
                  <span className="text-muted-foreground">Tax</span>
                  <span className="font-medium tabular-nums">{formatMoney(order.taxTotal)}</span>
                </div>
              )}
              <div className="pt-2 border-t flex items-center justify-between gap-4">
                <span className="font-semibold text-foreground">Total</span>
                <span className="font-bold text-foreground tabular-nums">{formatMoney(order.grandTotal)}</span>
              </div>
            </CardContent>
          </Card>
        </div>

        <Card className="border-border/80 shadow-sm overflow-hidden">
          <CardHeader className="pb-3 border-b bg-muted/20">
            <CardTitle className="text-base flex items-center gap-2">
              <ShoppingBag className="h-5 w-5 text-muted-foreground" />
              Items in this order
            </CardTitle>
            <CardDescription>
              Click an item to open the full deal page.
            </CardDescription>
          </CardHeader>
          <CardContent className="p-0">
            <div className="overflow-x-auto">
              <table className="w-full caption-bottom text-sm min-w-[760px]">
                <thead>
                  <tr className="border-b bg-muted/30 text-left text-xs uppercase tracking-wide text-muted-foreground">
                    <th className="px-4 py-3">Item</th>
                    <th className="px-4 py-3 hidden md:table-cell">Offer</th>
                    <th className="px-4 py-3 text-right">Qty</th>
                    <th className="px-4 py-3 text-right hidden lg:table-cell">Unit</th>
                    <th className="px-4 py-3 text-right">Line total</th>
                  </tr>
                </thead>
                <tbody>
                  {order.items.map((item) => {
                    const dealUrl = getDealUrl(item);
                    const discount = hasDiscount(item);
                    return (
                      <tr key={item.id} className="border-b hover:bg-muted/40 transition-colors">
                        <td className="px-4 py-4">
                          <div className="flex items-start gap-3">
                            <button
                              type="button"
                              onClick={() => {
                                const url = getDealUrl(item);
                                if (url) window.location.href = url;
                              }}
                              className="flex-shrink-0 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                            >
                              {item.image ? (
                                <img
                                  src={item.image}
                                  alt={item.title}
                                  className="h-12 w-12 rounded-lg object-cover border hover:opacity-90 transition-opacity"
                                />
                              ) : (
                                <div className="h-12 w-12 rounded-lg bg-muted flex items-center justify-center">
                                  <ShoppingBag className="h-4 w-4 text-muted-foreground" />
                                </div>
                              )}
                            </button>

                            <div className="min-w-0 pt-0.5">
                              {dealUrl ? (
                                <Link
                                  href={dealUrl}
                                  className="font-semibold text-sm block truncate hover:underline"
                                >
                                  {item.title}
                                </Link>
                              ) : (
                                <div className="font-semibold text-sm block truncate">{item.title}</div>
                              )}

                              <div className="text-xs text-muted-foreground mt-1">
                                {item.offerType} · Qty {item.quantity}
                              </div>
                              <div className="text-[11px] mt-1">
                                <span className="text-muted-foreground">Claim code: </span>
                                <span className="font-mono">{item.claimToken || 'Not available'}</span>
                                <span className="mx-1 text-muted-foreground/40">·</span>
                                <span className={item.isClaimed ? 'text-green-600 font-medium' : 'text-amber-600 font-medium'}>
                                  {item.isClaimed ? 'Claimed' : 'Pending claim'}
                                </span>
                              </div>

                              {discount && (
                                <div className="text-xs mt-1">
                                  <span className="line-through text-muted-foreground/60">Rs. {item.originalPrice.toFixed(2)}</span>
                                  <span className="text-emerald-600 font-medium ml-2">Rs. {item.unitPrice.toFixed(2)}</span>
                                </div>
                              )}
                            </div>
                          </div>
                        </td>

                        <td className="px-4 py-4 hidden md:table-cell align-middle text-muted-foreground">
                          {item.offerType}
                        </td>
                        <td className="px-4 py-4 text-right align-middle font-medium tabular-nums">
                          {item.quantity}
                        </td>
                        <td className="px-4 py-4 text-right align-middle hidden lg:table-cell tabular-nums text-muted-foreground">
                          {formatMoney(item.unitPrice)}
                        </td>
                        <td className="px-4 py-4 text-right align-middle font-semibold tabular-nums">
                          {formatMoney(item.lineTotal)}
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>
    );
  };

  const renderEmpty = (message: string) => (
    <div className="flex flex-col items-center justify-center py-12 text-center">
      <ShoppingBag className="h-10 w-10 text-muted-foreground/30 mb-3" />
      <p className="text-muted-foreground">{message}</p>
    </div>
  );

  return (
    <div className="space-y-6">
      {activeOrder ? (
        renderOrderSinglePage(activeOrder)
      ) : (
        <>
          <div>
            <h1 className="text-2xl font-bold tracking-tight">My Claimed Offers</h1>
            <p className="text-muted-foreground">Track your claimed and redeemable offers</p>
          </div>

          <div className="grid gap-4 grid-cols-2 md:grid-cols-4">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Total Claims</CardTitle>
                <ShoppingBag className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{orders.length}</div>
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Active</CardTitle>
                <Clock className="h-4 w-4 text-amber-500" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold text-amber-600">{activeOrders.length}</div>
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Total Spent</CardTitle>
                <Receipt className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">Rs. {totalSpent.toFixed(2)}</div>
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Total Saved</CardTitle>
                <Tag className="h-4 w-4 text-green-500" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold text-green-600">Rs. {totalSaved.toFixed(2)}</div>
              </CardContent>
            </Card>
          </div>

          <Tabs defaultValue="all" className="space-y-4">
            <TabsList>
              <TabsTrigger value="all">All ({orders.length})</TabsTrigger>
              <TabsTrigger value="active">Active ({activeOrders.length})</TabsTrigger>
              <TabsTrigger value="completed">Completed ({completedOrders.length})</TabsTrigger>
            </TabsList>

            <TabsContent value="all" className="space-y-3">
              {orders.length > 0 ? orders.map(renderOrderCard) : renderEmpty('No orders yet. Start shopping!')}
            </TabsContent>
            <TabsContent value="active" className="space-y-3">
              {activeOrders.length > 0 ? activeOrders.map(renderOrderCard) : renderEmpty('No active orders.')}
            </TabsContent>
            <TabsContent value="completed" className="space-y-3">
              {completedOrders.length > 0 ? completedOrders.map(renderOrderCard) : renderEmpty('No completed orders yet.')}
            </TabsContent>
          </Tabs>
        </>
      )}

      <Dialog open={!!cancelTargetOrder} onOpenChange={(open) => !open && setCancelTargetOrder(null)}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Cancel Order</DialogTitle>
            <DialogDescription>
              Tell the vendor why you are cancelling order {cancelTargetOrder?.orderNumber}.
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-3">
            <Textarea
              value={cancelReason}
              onChange={(e) => setCancelReason(e.target.value)}
              placeholder="Enter cancellation reason (minimum 5 characters)"
              rows={4}
            />
            <div className="flex items-center justify-end gap-2">
              <Button variant="outline" onClick={() => setCancelTargetOrder(null)}>
                Close
              </Button>
              <Button
                variant="destructive"
                onClick={handleCancelOrderSubmit}
                disabled={!cancelTargetOrder || cancelReason.trim().length < 5 || cancellingOrderNumber === cancelTargetOrder.orderNumber}
              >
                {cancelTargetOrder && cancellingOrderNumber === cancelTargetOrder.orderNumber ? 'Cancelling...' : 'Confirm Cancel'}
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
};

MyPurchases.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default MyPurchases;
