import Link from '@/components/Link';
import { ShoppingBag, Calendar, Tag, Package, Clock, CheckCircle, XCircle } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { formatDistanceToNow } from 'date-fns';
import DashboardLayout from '@/layouts/DashboardLayout';

interface OrderItem {
  id: number;
  title: string;
  quantity: number;
  unitPrice: number;
  lineTotal: number;
  image: string;
  offerType: string;
}

interface Order {
  id: number;
  orderNumber: string;
  status: string;
  grandTotal: number;
  discountTotal: number;
  vendorName: string;
  createdAt: string;
  itemCount: number;
  items: OrderItem[];
}

interface MyPurchasesProps {
  purchases: Order[];
}

const statusConfig: Record<string, { label: string; color: string; icon: React.ReactNode }> = {
  pending: { label: 'Pending', color: 'bg-amber-100 text-amber-800', icon: <Clock className="h-3 w-3" /> },
  paid: { label: 'Paid', color: 'bg-blue-100 text-blue-800', icon: <Package className="h-3 w-3" /> },
  fulfilled: { label: 'Fulfilled', color: 'bg-green-100 text-green-800', icon: <CheckCircle className="h-3 w-3" /> },
  cancelled: { label: 'Cancelled', color: 'bg-red-100 text-red-800', icon: <XCircle className="h-3 w-3" /> },
  refunded: { label: 'Refunded', color: 'bg-gray-100 text-gray-800', icon: <XCircle className="h-3 w-3" /> },
};

const MyPurchases = ({ purchases }: MyPurchasesProps) => {
  const orders = purchases || [];
  const activeOrders = orders.filter(o => ['pending', 'paid'].includes(o.status));
  const completedOrders = orders.filter(o => ['fulfilled', 'cancelled', 'refunded'].includes(o.status));
  const totalSpent = orders.reduce((sum, o) => sum + o.grandTotal, 0);

  const renderOrderCard = (order: Order) => {
    const cfg = statusConfig[order.status] || statusConfig.pending;
    const firstImage = order.items?.[0]?.image;

    return (
      <div key={order.id} className="border rounded-xl bg-card hover:bg-muted/30 transition-colors overflow-hidden">
        <div className="flex items-center justify-between gap-4 p-4 border-b bg-muted/20">
          <div className="min-w-0">
            <p className="font-mono text-xs text-muted-foreground">{order.orderNumber}</p>
            <p className="text-sm font-medium text-teal-950 truncate">{order.vendorName}</p>
          </div>
          <div className="flex items-center gap-2 flex-shrink-0">
            <span className={`inline-flex items-center gap-1.5 rounded-full text-xs font-semibold px-2.5 py-1 ${cfg.color}`}>
              {cfg.icon}
              {cfg.label}
            </span>
          </div>
        </div>
        <div className="divide-y">
          {order.items.map((item) => (
            <div key={item.id} className="flex items-center gap-3 p-3">
              {item.image ? (
                <img src={item.image} alt={item.title} className="h-11 w-11 rounded-lg object-cover flex-shrink-0 border" />
              ) : (
                <div className="h-11 w-11 rounded-lg bg-muted flex items-center justify-center flex-shrink-0">
                  <ShoppingBag className="h-4 w-4 text-muted-foreground" />
                </div>
              )}
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium truncate">{item.title}</p>
                <p className="text-xs text-muted-foreground">{item.offerType} &middot; Qty: {item.quantity}</p>
              </div>
              <p className="text-sm font-semibold text-teal-950 flex-shrink-0">Rs. {item.lineTotal.toFixed(2)}</p>
            </div>
          ))}
        </div>
        <div className="flex items-center justify-between px-4 py-3 border-t bg-muted/10">
          <span className="text-xs text-muted-foreground flex items-center gap-1">
            <Calendar className="h-3 w-3" />
            {formatDistanceToNow(new Date(order.createdAt), { addSuffix: true })}
          </span>
          <span className="font-bold text-teal-950">Rs. {order.grandTotal.toFixed(2)}</span>
        </div>
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
        <p className="text-muted-foreground">Track all your orders</p>
      </div>

      <div className="grid gap-4 md:grid-cols-3">
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
            <CardTitle className="text-sm font-medium">Active Orders</CardTitle>
            <Tag className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{activeOrders.length}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Spent</CardTitle>
            <ShoppingBag className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">Rs. {totalSpent.toFixed(2)}</div>
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
    </div>
  );
};

MyPurchases.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default MyPurchases;
