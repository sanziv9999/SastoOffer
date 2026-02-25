import { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Search, Package, Clock, CheckCircle, XCircle } from 'lucide-react';

const mockOrders = [
  { id: 'ORD-001', customer: 'Sarah Johnson', deal: 'Gourmet Pizza Deal', quantity: 2, total: 45.98, date: '2024-01-19', status: 'pending' },
  { id: 'ORD-002', customer: 'Mike Chen', deal: 'Spa Relaxation Package', quantity: 1, total: 89.00, date: '2024-01-19', status: 'processing' },
  { id: 'ORD-003', customer: 'Emily Davis', deal: 'Weekend Brunch Special', quantity: 3, total: 67.50, date: '2024-01-18', status: 'completed' },
  { id: 'ORD-004', customer: 'Lisa Anderson', deal: 'Fitness Class Pack', quantity: 1, total: 120.00, date: '2024-01-18', status: 'completed' },
  { id: 'ORD-005', customer: 'James Wilson', deal: 'Coffee Lover Bundle', quantity: 4, total: 35.96, date: '2024-01-17', status: 'cancelled' },
  { id: 'ORD-006', customer: 'Sarah Johnson', deal: 'Spa Relaxation Package', quantity: 1, total: 89.00, date: '2024-01-17', status: 'completed' },
  { id: 'ORD-007', customer: 'Emily Davis', deal: 'Gourmet Pizza Deal', quantity: 1, total: 22.99, date: '2024-01-16', status: 'pending' },
];

const Orders = () => {
  const [searchTerm, setSearchTerm] = useState('');

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
      case 'pending': return 'bg-yellow-500';
      case 'processing': return 'bg-blue-500';
      case 'completed': return 'bg-green-500';
      case 'cancelled': return 'bg-red-500';
      default: return '';
    }
  };

  const filterOrders = (status?: string) => {
    let orders = mockOrders;
    if (status) orders = orders.filter(o => o.status === status);
    if (searchTerm) orders = orders.filter(o =>
      o.customer.toLowerCase().includes(searchTerm.toLowerCase()) ||
      o.deal.toLowerCase().includes(searchTerm.toLowerCase()) ||
      o.id.toLowerCase().includes(searchTerm.toLowerCase())
    );
    return orders;
  };

  const renderOrders = (orders: typeof mockOrders) => (
    <div className="rounded-md border overflow-x-auto">
      <table className="w-full text-sm">
        <thead className="border-b bg-muted/50">
          <tr>
            <th className="h-10 px-4 text-left font-medium">Order ID</th>
            <th className="h-10 px-4 text-left font-medium">Customer</th>
            <th className="h-10 px-4 text-left font-medium hidden md:table-cell">Deal</th>
            <th className="h-10 px-4 text-left font-medium">Qty</th>
            <th className="h-10 px-4 text-left font-medium">Total</th>
            <th className="h-10 px-4 text-left font-medium">Status</th>
          </tr>
        </thead>
        <tbody>
          {orders.map(order => (
            <tr key={order.id} className="border-b hover:bg-muted/50 transition-colors">
              <td className="p-4 font-mono text-xs">{order.id}</td>
              <td className="p-4">
                <div className="font-medium">{order.customer}</div>
                <div className="text-xs text-muted-foreground md:hidden">{order.deal}</div>
              </td>
              <td className="p-4 hidden md:table-cell">{order.deal}</td>
              <td className="p-4">{order.quantity}</td>
              <td className="p-4 font-medium">${order.total.toFixed(2)}</td>
              <td className="p-4">
                <Badge className={`${getStatusColor(order.status)} flex items-center gap-1 w-fit`}>
                  {getStatusIcon(order.status)}
                  {order.status}
                </Badge>
              </td>
            </tr>
          ))}
          {orders.length === 0 && (
            <tr><td colSpan={6} className="p-8 text-center text-muted-foreground">No orders found</td></tr>
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
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Total Orders</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold">{mockOrders.length}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Pending</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold text-yellow-600">{mockOrders.filter(o => o.status === 'pending').length}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Completed</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold text-green-600">{mockOrders.filter(o => o.status === 'completed').length}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Revenue</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold">${mockOrders.filter(o => o.status === 'completed').reduce((s, o) => s + o.total, 0).toFixed(2)}</div></CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <CardTitle>All Orders</CardTitle>
              <CardDescription>View and manage orders</CardDescription>
            </div>
            <div className="relative w-full sm:w-72">
              <Search className="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input placeholder="Search orders..." className="pl-9" value={searchTerm} onChange={e => setSearchTerm(e.target.value)} />
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
            <TabsContent value="all">{renderOrders(filterOrders())}</TabsContent>
            <TabsContent value="pending">{renderOrders(filterOrders('pending'))}</TabsContent>
            <TabsContent value="processing">{renderOrders(filterOrders('processing'))}</TabsContent>
            <TabsContent value="completed">{renderOrders(filterOrders('completed'))}</TabsContent>
            <TabsContent value="cancelled">{renderOrders(filterOrders('cancelled'))}</TabsContent>
          </Tabs>
        </CardContent>
      </Card>
    </div>
  );
};

export default Orders;
