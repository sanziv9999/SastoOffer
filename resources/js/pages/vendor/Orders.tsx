import { Fragment, useState } from 'react';
import Link from '@/components/Link';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { toast } from 'sonner';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Search, Package, Clock, CheckCircle, XCircle, ChevronDown, ChevronUp, ShoppingBag, Mail, Calendar, Banknote, Receipt, Tag, ExternalLink } from 'lucide-react';
import { format, formatDistanceToNow } from 'date-fns';
import DashboardLayout from '@/layouts/DashboardLayout';
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

interface VendorOrder {
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
  paymentReference?: string | null;
  paidAt: string | null;
  quantity: number;
  status: string;
  date: string;
  items: OrderItemDetail[];
}

interface OrdersProps {
  orders: VendorOrder[];
}

type OrderStatus = 'pending' | 'paid' | 'fulfilled' | 'cancelled' | 'refunded';

const Orders = ({ orders }: OrdersProps) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [localOrders, setLocalOrders] = useState<VendorOrder[]>(orders || []);
  const [expandedRows, setExpandedRows] = useState<Set<string>>(new Set());
  const [updatingOrderId, setUpdatingOrderId] = useState<number | null>(null);

  const toggleRow = (orderId: string) => {
    setExpandedRows(prev => {
      const next = new Set(prev);
      next.has(orderId) ? next.delete(orderId) : next.add(orderId);
      return next;
    });
  };

  const updateOrderStatus = (order: VendorOrder, newStatus: OrderStatus) => {
    const previousOrders = [...localOrders];
    setUpdatingOrderId(order.orderId);
    setLocalOrders(prev => prev.map(o => o.orderId === order.orderId ? { ...o, status: newStatus } : o));

    router.patch(
      route('vendor.orders.status', order.orderId),
      { status: newStatus },
      {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          toast.success(`Order ${order.id} updated to ${newStatus}`);
        },
        onError: () => {
          setLocalOrders(previousOrders);
          toast.error('Could not update order status. Please try again.');
        },
        onFinish: () => {
          setUpdatingOrderId(null);
        },
      }
    );
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'pending': return <Clock className="h-3.5 w-3.5" />;
      case 'paid': return <Package className="h-3.5 w-3.5" />;
      case 'fulfilled': return <CheckCircle className="h-3.5 w-3.5" />;
      case 'cancelled': return <XCircle className="h-3.5 w-3.5" />;
      case 'refunded': return <XCircle className="h-3.5 w-3.5" />;
      default: return null;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pending': return 'bg-amber-500 text-white hover:bg-amber-600 focus:bg-amber-600';
      case 'paid': return 'bg-blue-500 text-white hover:bg-blue-600 focus:bg-blue-600';
      case 'fulfilled': return 'bg-emerald-500 text-white hover:bg-emerald-600 focus:bg-emerald-600';
      case 'cancelled': return 'bg-red-500 text-white hover:bg-red-600 focus:bg-red-600';
      case 'refunded': return 'bg-slate-500 text-white hover:bg-slate-600 focus:bg-slate-600';
      default: return '';
    }
  };

  const rowStatusAccent = (status: string) => {
    switch (status) {
      case 'pending': return 'border-l-4 border-l-amber-400';
      case 'paid': return 'border-l-4 border-l-blue-400';
      case 'fulfilled': return 'border-l-4 border-l-emerald-400';
      case 'cancelled': return 'border-l-4 border-l-red-400';
      case 'refunded': return 'border-l-4 border-l-slate-400';
      default: return 'border-l-4 border-l-transparent';
    }
  };

  const filterOrders = (status?: string) => {
    let filteredOrders = localOrders || [];
    if (status && status !== 'all') filteredOrders = filteredOrders.filter((o) => o.status === status);
    if (searchTerm) filteredOrders = filteredOrders.filter((o) =>
      o.customer?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      o.customerEmail?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      o.id?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      o.items?.some(item => item.title.toLowerCase().includes(searchTerm.toLowerCase()))
    );
    return filteredOrders;
  };

  const totalRevenue = localOrders?.filter(o => o.status === 'fulfilled').reduce((s, o) => s + o.total, 0) || 0;
  const totalDiscount = localOrders?.reduce((s, o) => s + (o.discountTotal || 0), 0) || 0;

  const renderExpandedRow = (order: VendorOrder) => (
    <tr>
      <td colSpan={8} className="p-0">
        <div className="bg-muted/30 border-t">
          {/* Customer & order meta */}
          <div className="flex flex-wrap items-center gap-x-5 gap-y-1.5 px-5 py-3 border-b bg-muted/20 text-xs text-muted-foreground">
            <span className="flex items-center gap-1.5">
              <Mail className="h-3 w-3" />
              {order.customerEmail || 'No email'}
            </span>
            <span className="flex items-center gap-1.5">
              <Calendar className="h-3 w-3" />
              {order.date ? format(new Date(order.date), 'MMM d, yyyy h:mm a') : '—'}
            </span>
            {order.paymentMethod && (
              <span className="flex items-center gap-1.5">
                <Banknote className="h-3 w-3" />
                {order.paymentMethod}
              </span>
            )}
            {order.paymentReference && (
              <span className="flex items-center gap-1.5 font-mono text-[11px]">
                Ref: {order.paymentReference}
              </span>
            )}
            {order.paidAt && (
              <span className="flex items-center gap-1.5">
                <CheckCircle className="h-3 w-3 text-green-500" />
                Paid {formatDistanceToNow(new Date(order.paidAt), { addSuffix: true })}
              </span>
            )}
            {order.cancellationReason && (
              <span className="w-full text-red-600">
                Cancellation reason: {order.cancellationReason}
              </span>
            )}
          </div>

          <div className="flex items-center justify-between gap-2 px-5 py-2 border-b bg-muted/10">
            <span className="text-xs font-medium text-muted-foreground uppercase tracking-wide">Items in this order</span>
            <Button variant="ghost" size="sm" className="h-8 gap-1.5 text-primary" asChild>
              <Link href={route('vendor.orders.show', order.orderId)} onClick={(e) => e.stopPropagation()}>
                Open full page
                <ExternalLink className="h-3.5 w-3.5" />
              </Link>
            </Button>
          </div>

          {/* Items */}
          <div className="divide-y">
            {order.items?.map((item) => (
              <div key={item.id} className="flex items-start gap-4 px-5 py-3.5">
                {item.dealId ? (
                  <Link href={route('vendor.deals.view', item.dealId)} className="flex-shrink-0">
                    {item.image ? (
                      <img src={item.image} alt={item.title} className="h-14 w-14 rounded-xl object-cover border shadow-sm hover:opacity-90 transition-opacity" />
                    ) : (
                      <div className="h-14 w-14 rounded-xl bg-muted flex items-center justify-center border">
                        <ShoppingBag className="h-5 w-5 text-muted-foreground" />
                      </div>
                    )}
                  </Link>
                ) : item.image ? (
                  <img src={item.image} alt={item.title} className="h-14 w-14 rounded-xl object-cover flex-shrink-0 border shadow-sm" />
                ) : (
                  <div className="h-14 w-14 rounded-xl bg-muted flex items-center justify-center flex-shrink-0 border">
                    <ShoppingBag className="h-5 w-5 text-muted-foreground" />
                  </div>
                )}
                <div className="flex-1 min-w-0">
                  {item.dealId ? (
                    <Link href={route('vendor.deals.view', item.dealId)} className="text-sm font-semibold leading-snug block hover:text-primary transition-colors">
                      {item.title}
                    </Link>
                  ) : (
                    <p className="text-sm font-semibold leading-snug">{item.title}</p>
                  )}
                  <div className="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-muted-foreground mt-1">
                    <span>{item.offerType}</span>
                    <span className="text-muted-foreground/40">·</span>
                    <span>Qty {item.quantity}</span>
                    {item.originalPrice > item.unitPrice && (
                      <>
                        <span className="text-muted-foreground/40">·</span>
                        <span className="line-through text-muted-foreground/50">Rs. {item.originalPrice.toFixed(2)}</span>
                        <span className="text-emerald-600 font-medium">Rs. {item.unitPrice.toFixed(2)} ea</span>
                      </>
                    )}
                  </div>
                </div>
                <p className="text-sm font-bold tabular-nums flex-shrink-0 pt-0.5">Rs. {item.lineTotal.toFixed(2)}</p>
              </div>
            ))}
          </div>

          {/* Financial summary */}
          <div className="grid grid-cols-2 sm:flex sm:flex-wrap gap-x-6 gap-y-2 px-5 py-4 border-t bg-muted/15 text-sm">
            <span className="text-muted-foreground col-span-2 sm:col-auto">
              Subtotal{' '}
              <span className="font-semibold text-foreground tabular-nums">Rs. {order.subtotal.toFixed(2)}</span>
            </span>
            {order.discountTotal > 0 && (
              <span className="text-emerald-600">
                Discount{' '}
                <span className="font-semibold tabular-nums">− Rs. {order.discountTotal.toFixed(2)}</span>
              </span>
            )}
            {order.taxTotal > 0 && (
              <span className="text-muted-foreground">
                Tax <span className="font-semibold text-foreground tabular-nums">Rs. {order.taxTotal.toFixed(2)}</span>
              </span>
            )}
            <span className="text-muted-foreground col-span-2 sm:ml-auto sm:col-auto border-t sm:border-t-0 pt-2 sm:pt-0 mt-1 sm:mt-0">
              <span className="text-xs uppercase tracking-wide mr-2">Total</span>
              <span className="font-bold text-foreground text-lg tabular-nums">Rs. {order.total.toFixed(2)}</span>
            </span>
          </div>
        </div>
      </td>
    </tr>
  );

  const renderOrdersTable = (ordersList: VendorOrder[]) => (
    <div className="rounded-xl border border-border/80 bg-card shadow-sm overflow-x-auto">
      <table className="w-full text-sm min-w-[720px]">
        <thead className="border-b bg-muted/40">
          <tr>
            <th className="h-11 px-3 text-left font-semibold text-xs uppercase tracking-wide text-muted-foreground w-10"></th>
            <th className="h-11 px-3 text-left font-semibold text-xs uppercase tracking-wide text-muted-foreground">Order</th>
            <th className="h-11 px-3 text-left font-semibold text-xs uppercase tracking-wide text-muted-foreground">Customer</th>
            <th className="h-11 px-3 text-left font-semibold text-xs uppercase tracking-wide text-muted-foreground hidden lg:table-cell">Placed</th>
            <th className="h-11 px-3 text-left font-semibold text-xs uppercase tracking-wide text-muted-foreground">Summary</th>
            <th className="h-11 px-3 text-right font-semibold text-xs uppercase tracking-wide text-muted-foreground">Amount</th>
            <th className="h-11 px-3 text-center font-semibold text-xs uppercase tracking-wide text-muted-foreground w-[100px]">View</th>
            <th className="h-11 px-3 text-left font-semibold text-xs uppercase tracking-wide text-muted-foreground min-w-[140px]">Status</th>
          </tr>
        </thead>
        <tbody>
          {ordersList.map(order => {
            const isExpanded = expandedRows.has(order.id);
            return (
              <Fragment key={order.id}>
                <tr
                  className={`border-b border-border/60 hover:bg-muted/40 transition-colors cursor-pointer ${isExpanded ? 'bg-muted/25' : ''} ${rowStatusAccent(order.status)}`}
                  onClick={() => toggleRow(order.id)}
                >
                  <td className="p-3 w-10 align-middle">
                    {isExpanded
                      ? <ChevronUp className="h-4 w-4 text-muted-foreground" />
                      : <ChevronDown className="h-4 w-4 text-muted-foreground" />
                    }
                  </td>
                  <td className="p-3 align-middle">
                    <Link
                      href={route('vendor.orders.show', order.orderId)}
                      onClick={(e) => e.stopPropagation()}
                      className="font-mono text-sm font-semibold text-primary hover:underline"
                    >
                      {order.id}
                    </Link>
                  </td>
                  <td className="p-3 align-middle">
                    <div className="font-semibold text-foreground">{order.customer}</div>
                    <div className="text-xs text-muted-foreground truncate max-w-[200px]">{order.customerEmail}</div>
                  </td>
                  <td className="p-3 align-middle hidden lg:table-cell">
                    <div className="text-sm font-medium">
                      {order.date ? format(new Date(order.date), 'MMM d, yyyy') : '—'}
                    </div>
                    <div className="text-xs text-muted-foreground">
                      {order.date ? formatDistanceToNow(new Date(order.date), { addSuffix: true }) : ''}
                    </div>
                  </td>
                  <td className="p-3 align-middle">
                    <div className="font-medium">{order.quantity} line{order.items && order.items.length !== 1 ? 's' : ''}</div>
                    <div className="text-xs text-muted-foreground truncate max-w-[220px] hidden md:block" title={order.items?.map(i => i.title).join(', ')}>
                      {order.items?.map(i => i.title).join(', ')}
                    </div>
                  </td>
                  <td className="p-3 align-middle text-right">
                    <div className="font-semibold tabular-nums">Rs. {order.total?.toFixed(2)}</div>
                    {order.discountTotal > 0 && (
                      <div className="text-xs text-emerald-600 font-medium">− Rs. {order.discountTotal.toFixed(2)}</div>
                    )}
                  </td>
                  <td className="p-3 align-middle text-center" onClick={(e) => e.stopPropagation()}>
                    <Button variant="outline" size="sm" className="h-8 gap-1" asChild>
                      <Link href={route('vendor.orders.show', order.orderId)}>
                        View
                        <ExternalLink className="h-3 w-3 opacity-70" />
                      </Link>
                    </Button>
                  </td>
                  <td className="p-3 align-middle" onClick={(e) => e.stopPropagation()}>
                    <Select
                      value={order.status}
                      onValueChange={(value) => updateOrderStatus(order, value as OrderStatus)}
                    >
                      <SelectTrigger
                        disabled={updatingOrderId === order.orderId}
                        className={`w-full max-w-[150px] h-9 rounded-lg border-none shadow-none text-xs font-semibold ${getStatusColor(order.status)}`}
                      >
                        <div className="flex items-center gap-1.5 focus:outline-none">
                          {getStatusIcon(order.status)}
                          <SelectValue placeholder="Status" className="capitalize" />
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
                  </td>
                </tr>
                {isExpanded && renderExpandedRow(order)}
              </Fragment>
            );
          })}
          {ordersList.length === 0 && (
            <tr><td colSpan={8} className="p-12 text-center text-muted-foreground">No orders match your filters.</td></tr>
          )}
        </tbody>
      </table>
    </div>
  );

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Orders</h1>
        <p className="text-muted-foreground">Manage and track all customer orders</p>
      </div>

      <div className="grid gap-4 grid-cols-2 md:grid-cols-4">
        <Card>
          <CardHeader className="pb-2">
            <div className="flex items-center justify-between">
              <CardTitle className="text-sm font-medium">Total Orders</CardTitle>
              <Package className="h-4 w-4 text-muted-foreground" />
            </div>
          </CardHeader>
          <CardContent><div className="text-2xl font-bold">{localOrders?.length || 0}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <div className="flex items-center justify-between">
              <CardTitle className="text-sm font-medium">Pending</CardTitle>
              <Clock className="h-4 w-4 text-yellow-500" />
            </div>
          </CardHeader>
          <CardContent><div className="text-2xl font-bold text-yellow-600">{localOrders?.filter(o => o.status === 'pending').length}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <div className="flex items-center justify-between">
              <CardTitle className="text-sm font-medium">Revenue</CardTitle>
              <Receipt className="h-4 w-4 text-muted-foreground" />
            </div>
          </CardHeader>
          <CardContent><div className="text-2xl font-bold">Rs. {totalRevenue.toFixed(2)}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <div className="flex items-center justify-between">
              <CardTitle className="text-sm font-medium">Discounts Given</CardTitle>
              <Tag className="h-4 w-4 text-green-500" />
            </div>
          </CardHeader>
          <CardContent><div className="text-2xl font-bold text-green-600">Rs. {totalDiscount.toFixed(2)}</div></CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <CardTitle>All Orders</CardTitle>
              <CardDescription>
                Click a row for a quick summary, or use <strong className="text-foreground font-medium">View</strong> for the full order page.
              </CardDescription>
            </div>
            <div className="relative w-full sm:w-72">
              <Search className="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input placeholder="Search by customer, email, deal..." className="pl-9" value={searchTerm} onChange={e => setSearchTerm(e.target.value)} />
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <Tabs defaultValue="all" className="space-y-4">
            <TabsList>
              <TabsTrigger value="all">All</TabsTrigger>
              <TabsTrigger value="pending">Pending</TabsTrigger>
              <TabsTrigger value="paid">Paid</TabsTrigger>
              <TabsTrigger value="fulfilled">Fulfilled</TabsTrigger>
              <TabsTrigger value="cancelled">Cancelled</TabsTrigger>
              <TabsTrigger value="refunded">Refunded</TabsTrigger>
            </TabsList>
            <TabsContent value="all">{renderOrdersTable(filterOrders('all'))}</TabsContent>
            <TabsContent value="pending">{renderOrdersTable(filterOrders('pending'))}</TabsContent>
            <TabsContent value="paid">{renderOrdersTable(filterOrders('paid'))}</TabsContent>
            <TabsContent value="fulfilled">{renderOrdersTable(filterOrders('fulfilled'))}</TabsContent>
            <TabsContent value="cancelled">{renderOrdersTable(filterOrders('cancelled'))}</TabsContent>
            <TabsContent value="refunded">{renderOrdersTable(filterOrders('refunded'))}</TabsContent>
          </Tabs>
        </CardContent>
      </Card>
    </div>
  );
};

Orders.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Orders;
