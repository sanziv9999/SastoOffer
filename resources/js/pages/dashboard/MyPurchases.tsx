import { ShoppingBag, Calendar, Tag, Package, Clock, CheckCircle, XCircle, CreditCard, Receipt, ChevronDown, ChevronUp, Store, Banknote } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { format, formatDistanceToNow } from 'date-fns';
import DashboardLayout from '@/layouts/DashboardLayout';
import { useState } from 'react';
import { router } from '@inertiajs/react';

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
}

interface Order {
  id: number;
  orderNumber: string;
  status: string;
  canCancel: boolean;
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

type SelectedOrderItem = OrderItem & { dealUrl: string | null };

const statusConfig: Record<string, { label: string; color: string; icon: React.ReactNode }> = {
  pending: { label: 'Pending', color: 'bg-amber-100 text-amber-800', icon: <Clock className="h-3 w-3" /> },
  paid: { label: 'Paid', color: 'bg-blue-100 text-blue-800', icon: <CreditCard className="h-3 w-3" /> },
  fulfilled: { label: 'Fulfilled', color: 'bg-green-100 text-green-800', icon: <CheckCircle className="h-3 w-3" /> },
  cancelled: { label: 'Cancelled', color: 'bg-red-100 text-red-800', icon: <XCircle className="h-3 w-3" /> },
  refunded: { label: 'Refunded', color: 'bg-gray-100 text-gray-800', icon: <XCircle className="h-3 w-3" /> },
};

const MyPurchases = ({ purchases }: MyPurchasesProps) => {
  const orders = purchases || [];
  const activeOrders = orders.filter(o => ['pending', 'paid'].includes(o.status));
  const completedOrders = orders.filter(o => ['fulfilled', 'cancelled', 'refunded'].includes(o.status));
  const totalSpent = orders.reduce((sum, o) => sum + o.grandTotal, 0);
  const totalSaved = orders.reduce((sum, o) => sum + o.discountTotal, 0);
  const [expandedOrders, setExpandedOrders] = useState<Set<number>>(new Set());
  const [selectedItem, setSelectedItem] = useState<SelectedOrderItem | null>(null);
  const [isItemModalOpen, setIsItemModalOpen] = useState(false);
  const [cancellingOrderNumber, setCancellingOrderNumber] = useState<string | null>(null);

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

  const openItemModal = (item: OrderItem) => {
    setSelectedItem({
      ...item,
      dealUrl: getDealUrl(item),
    });
    setIsItemModalOpen(true);
  };

  const handleCancelOrder = (order: Order) => {
    if (!order.canCancel) {
      return;
    }

    const ok = window.confirm(`Cancel order ${order.orderNumber}? This action cannot be undone.`);
    if (!ok) {
      return;
    }

    setCancellingOrderNumber(order.orderNumber);
    router.patch(
      route('dashboard.purchases.cancel', { orderNumber: order.orderNumber }),
      {},
      {
        preserveScroll: true,
        onFinish: () => setCancellingOrderNumber(null),
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
                  handleCancelOrder(order);
                }}
                disabled={cancellingOrderNumber === order.orderNumber}
              >
                {cancellingOrderNumber === order.orderNumber ? 'Cancelling...' : 'Cancel'}
              </Button>
            )}
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
                      onClick={() => openItemModal(item)}
                      className="flex-shrink-0 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                    >
                      <img src={item.image} alt={item.title} className="h-12 w-12 rounded-lg object-cover border hover:opacity-90 transition-opacity" />
                    </button>
                  ) : (
                    <button
                      type="button"
                      onClick={() => openItemModal(item)}
                      className="h-12 w-12 rounded-lg bg-muted flex items-center justify-center flex-shrink-0"
                    >
                      <ShoppingBag className="h-4 w-4 text-muted-foreground" />
                    </button>
                  )}
                  <div className="flex-1 min-w-0">
                    <button
                      type="button"
                      onClick={() => openItemModal(item)}
                      className="text-sm font-medium truncate block hover:text-primary transition-colors text-left max-w-full"
                    >
                      {item.title}
                    </button>
                    <div className="flex items-center gap-2 text-xs text-muted-foreground">
                      <span>{item.offerType}</span>
                      <span className="text-muted-foreground/40">·</span>
                      <span>Qty: {item.quantity}</span>
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
              <div className="flex justify-between text-xs text-muted-foreground">
                <span>Shipping</span>
                <span className="text-primary font-semibold uppercase text-[10px] tracking-wider">Free</span>
              </div>
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
            </div>
          </>
        )}
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
      <div>
        <h1 className="text-2xl font-bold tracking-tight">My Orders</h1>
        <p className="text-muted-foreground">Track all your orders and purchases</p>
      </div>

      <div className="grid gap-4 grid-cols-2 md:grid-cols-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Orders</CardTitle>
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

      <Dialog open={isItemModalOpen} onOpenChange={setIsItemModalOpen}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle className="pr-8">{selectedItem?.title ?? 'Deal details'}</DialogTitle>
            <DialogDescription>
              Offer: {selectedItem?.offerType ?? 'Offer'} · Quantity: {selectedItem?.quantity ?? 0}
            </DialogDescription>
          </DialogHeader>

          <div className="space-y-4">
            {selectedItem?.image ? (
              <img
                src={selectedItem.image}
                alt={selectedItem.title}
                className="w-full h-48 object-cover rounded-lg border"
              />
            ) : (
              <div className="w-full h-48 rounded-lg bg-muted flex items-center justify-center border">
                <ShoppingBag className="h-8 w-8 text-muted-foreground" />
              </div>
            )}

            <div className="rounded-lg border p-3 space-y-1.5 text-sm">
              <div className="flex justify-between">
                <span className="text-muted-foreground">Unit Price</span>
                <span className="font-medium">Rs. {selectedItem?.unitPrice?.toFixed(2) ?? '0.00'}</span>
              </div>
              {selectedItem && selectedItem.originalPrice > selectedItem.unitPrice && (
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Original Price</span>
                  <span className="line-through text-muted-foreground">Rs. {selectedItem.originalPrice.toFixed(2)}</span>
                </div>
              )}
              <div className="flex justify-between">
                <span className="text-muted-foreground">Line Total</span>
                <span className="font-semibold">Rs. {selectedItem?.lineTotal?.toFixed(2) ?? '0.00'}</span>
              </div>
            </div>

            {selectedItem?.dealUrl ? (
              <Button
                className="w-full"
                onClick={() => {
                  setIsItemModalOpen(false);
                  window.location.href = selectedItem.dealUrl!;
                }}
              >
                Open Full Deal Page
              </Button>
            ) : (
              <Button className="w-full" disabled>
                Deal page not available
              </Button>
            )}
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
};

MyPurchases.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default MyPurchases;
