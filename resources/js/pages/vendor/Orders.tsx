import { useState } from 'react';
import Link from '@/components/Link';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'sonner';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Search, Package, Clock, CheckCircle, XCircle, ChevronDown, ChevronUp, ShoppingBag, Mail, Calendar, Banknote, Receipt, Tag } from 'lucide-react';
import { format, formatDistanceToNow } from 'date-fns';
import DashboardLayout from '@/layouts/DashboardLayout';

interface OrderItemDetail {
  id: number;
  dealId: number | null;
  dealOfferTypeId: number | null;
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
  subtotal: number;
  discountTotal: number;
  taxTotal: number;
  total: number;
  currencyCode: string;
  paymentMethod: string | null;
  paidAt: string | null;
  quantity: number;
  status: string;
  date: string;
  items: OrderItemDetail[];
}

interface OrdersProps {
  orders: VendorOrder[];
}

const Orders = ({ orders }: OrdersProps) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [localOrders, setLocalOrders] = useState<VendorOrder[]>(orders || []);
  const [expandedRows, setExpandedRows] = useState<Set<string>>(new Set());

  const toggleRow = (orderId: string) => {
    setExpandedRows(prev => {
      const next = new Set(prev);
      next.has(orderId) ? next.delete(orderId) : next.add(orderId);
      return next;
    });
  };

  const updateOrderStatus = (orderId: string, newStatus: string) => {
    setLocalOrders(prev => prev.map(o => o.id === orderId ? { ...o, status: newStatus } : o));
    toast.success(`Order ${orderId} updated to ${newStatus}`);
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'pending': return <Clock className="h-3.5 w-3.5" />;
      case 'processing': return <Package className="h-3.5 w-3.5" />;
      case 'completed': return <CheckCircle className="h-3.5 w-3.5" />;
      case 'cancelled': return <XCircle className="h-3.5 w-3.5" />;
      default: return null;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pending': return 'bg-yellow-500 text-white hover:bg-yellow-600 focus:bg-yellow-600';
      case 'processing': return 'bg-blue-500 text-white hover:bg-blue-600 focus:bg-blue-600';
      case 'completed': return 'bg-green-500 text-white hover:bg-green-600 focus:bg-green-600';
      case 'cancelled': return 'bg-red-500 text-white hover:bg-red-600 focus:bg-red-600';
      default: return '';
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

  const totalRevenue = localOrders?.filter(o => o.status === 'completed').reduce((s, o) => s + o.total, 0) || 0;
  const totalDiscount = localOrders?.reduce((s, o) => s + (o.discountTotal || 0), 0) || 0;

  const renderExpandedRow = (order: VendorOrder) => (
    <tr>
      <td colSpan={7} className="p-0">
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
            {order.paidAt && (
              <span className="flex items-center gap-1.5">
                <CheckCircle className="h-3 w-3 text-green-500" />
                Paid {formatDistanceToNow(new Date(order.paidAt), { addSuffix: true })}
              </span>
            )}
          </div>

          {/* Items */}
          <div className="divide-y">
            {order.items?.map((item) => (
              <div key={item.id} className="flex items-center gap-3 px-5 py-3">
                {item.dealId ? (
                  <Link href={route('vendor.deals.view', item.dealId)} className="flex-shrink-0">
                    {item.image ? (
                      <img src={item.image} alt={item.title} className="h-10 w-10 rounded-lg object-cover border hover:opacity-90 transition-opacity" />
                    ) : (
                      <div className="h-10 w-10 rounded-lg bg-muted flex items-center justify-center">
                        <ShoppingBag className="h-4 w-4 text-muted-foreground" />
                      </div>
                    )}
                  </Link>
                ) : item.image ? (
                  <img src={item.image} alt={item.title} className="h-10 w-10 rounded-lg object-cover flex-shrink-0 border" />
                ) : (
                  <div className="h-10 w-10 rounded-lg bg-muted flex items-center justify-center flex-shrink-0">
                    <ShoppingBag className="h-4 w-4 text-muted-foreground" />
                  </div>
                )}
                <div className="flex-1 min-w-0">
                  {item.dealId ? (
                    <Link href={route('vendor.deals.view', item.dealId)} className="text-sm font-medium truncate block hover:text-primary transition-colors">
                      {item.title}
                    </Link>
                  ) : (
                    <p className="text-sm font-medium truncate">{item.title}</p>
                  )}
                  <div className="flex items-center gap-2 text-xs text-muted-foreground">
                    <span>{item.offerType}</span>
                    <span className="text-muted-foreground/40">·</span>
                    <span>Qty: {item.quantity}</span>
                    {item.originalPrice > item.unitPrice && (
                      <>
                        <span className="text-muted-foreground/40">·</span>
                        <span className="line-through text-muted-foreground/50">Rs. {item.originalPrice.toFixed(2)}</span>
                        <span className="text-green-600 font-medium">Rs. {item.unitPrice.toFixed(2)}</span>
                      </>
                    )}
                  </div>
                </div>
                <p className="text-sm font-semibold flex-shrink-0">Rs. {item.lineTotal.toFixed(2)}</p>
              </div>
            ))}
          </div>

          {/* Financial summary */}
          <div className="flex flex-wrap gap-x-6 gap-y-1 px-5 py-3 border-t bg-muted/10 text-xs">
            <span className="text-muted-foreground">
              Subtotal: <span className="font-medium text-foreground">Rs. {order.subtotal.toFixed(2)}</span>
            </span>
            {order.discountTotal > 0 && (
              <span className="text-green-600">
                Discount: <span className="font-medium">- Rs. {order.discountTotal.toFixed(2)}</span>
              </span>
            )}
            {order.taxTotal > 0 && (
              <span className="text-muted-foreground">
                Tax: <span className="font-medium text-foreground">Rs. {order.taxTotal.toFixed(2)}</span>
              </span>
            )}
            <span className="text-muted-foreground ml-auto">
              Grand Total: <span className="font-bold text-foreground text-sm">Rs. {order.total.toFixed(2)}</span>
            </span>
          </div>
        </div>
      </td>
    </tr>
  );

  const renderOrdersTable = (ordersList: VendorOrder[]) => (
    <div className="rounded-md border overflow-x-auto">
      <table className="w-full text-sm">
        <thead className="border-b bg-muted/50">
          <tr>
            <th className="h-10 px-4 text-left font-medium w-8"></th>
            <th className="h-10 px-4 text-left font-medium">Order ID</th>
            <th className="h-10 px-4 text-left font-medium">Customer</th>
            <th className="h-10 px-4 text-left font-medium hidden lg:table-cell">Date</th>
            <th className="h-10 px-4 text-left font-medium">Items</th>
            <th className="h-10 px-4 text-left font-medium">Total</th>
            <th className="h-10 px-4 text-left font-medium">Status</th>
          </tr>
        </thead>
        <tbody>
          {ordersList.map(order => {
            const isExpanded = expandedRows.has(order.id);
            return (
              <>
                <tr key={order.id} className={`border-b hover:bg-muted/50 transition-colors cursor-pointer ${isExpanded ? 'bg-muted/30' : ''}`} onClick={() => toggleRow(order.id)}>
                  <td className="p-4 w-8">
                    {isExpanded
                      ? <ChevronUp className="h-4 w-4 text-muted-foreground" />
                      : <ChevronDown className="h-4 w-4 text-muted-foreground" />
                    }
                  </td>
                  <td className="p-4">
                    <span className="font-mono text-xs">{order.id}</span>
                  </td>
                  <td className="p-4">
                    <div className="font-medium">{order.customer}</div>
                    <div className="text-xs text-muted-foreground truncate max-w-[180px]">{order.customerEmail}</div>
                  </td>
                  <td className="p-4 hidden lg:table-cell">
                    <div className="text-xs">
                      {order.date ? format(new Date(order.date), 'MMM d, yyyy') : '—'}
                    </div>
                    <div className="text-xs text-muted-foreground">
                      {order.date ? formatDistanceToNow(new Date(order.date), { addSuffix: true }) : ''}
                    </div>
                  </td>
                  <td className="p-4">
                    <div className="font-medium">{order.quantity} item{order.quantity !== 1 ? 's' : ''}</div>
                    <div className="text-xs text-muted-foreground truncate max-w-[200px] hidden md:block">
                      {order.items?.map(i => i.title).join(', ')}
                    </div>
                  </td>
                  <td className="p-4">
                    <div className="font-medium">Rs. {order.total?.toFixed(2)}</div>
                    {order.discountTotal > 0 && (
                      <div className="text-xs text-green-600">- Rs. {order.discountTotal.toFixed(2)} off</div>
                    )}
                  </td>
                  <td className="p-4" onClick={(e) => e.stopPropagation()}>
                    <Select
                      value={order.status}
                      onValueChange={(value) => updateOrderStatus(order.id, value)}
                    >
                      <SelectTrigger className={`w-[130px] h-8 rounded-full border-none shadow-none text-xs font-medium px-2.5 ${getStatusColor(order.status)}`}>
                        <div className="flex items-center gap-1.5 focus:outline-none">
                          {getStatusIcon(order.status)}
                          <SelectValue placeholder="Status" className="capitalize" />
                        </div>
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="processing">Processing</SelectItem>
                        <SelectItem value="completed">Completed</SelectItem>
                        <SelectItem value="cancelled">Cancelled</SelectItem>
                      </SelectContent>
                    </Select>
                  </td>
                </tr>
                {isExpanded && renderExpandedRow(order)}
              </>
            );
          })}
          {ordersList.length === 0 && (
            <tr><td colSpan={7} className="p-8 text-center text-muted-foreground">No orders found</td></tr>
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
              <CardDescription>Click any row to expand order details</CardDescription>
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
              <TabsTrigger value="processing">Processing</TabsTrigger>
              <TabsTrigger value="completed">Completed</TabsTrigger>
              <TabsTrigger value="cancelled">Cancelled</TabsTrigger>
            </TabsList>
            <TabsContent value="all">{renderOrdersTable(filterOrders('all'))}</TabsContent>
            <TabsContent value="pending">{renderOrdersTable(filterOrders('pending'))}</TabsContent>
            <TabsContent value="processing">{renderOrdersTable(filterOrders('processing'))}</TabsContent>
            <TabsContent value="completed">{renderOrdersTable(filterOrders('completed'))}</TabsContent>
            <TabsContent value="cancelled">{renderOrdersTable(filterOrders('cancelled'))}</TabsContent>
          </Tabs>
        </CardContent>
      </Card>
    </div>
  );
};

Orders.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Orders;
