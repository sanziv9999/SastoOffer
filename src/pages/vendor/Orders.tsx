import { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Search, Package, Clock, CheckCircle, XCircle } from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';

interface OrdersProps {
  orders: any[];
}

const Orders = ({ orders }: OrdersProps) => {
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
    let filteredOrders = orders || [];
    if (status && status !== 'all') filteredOrders = filteredOrders.filter((o: any) => o.status === status);
    if (searchTerm) filteredOrders = filteredOrders.filter((o: any) =>
      o.customer?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      o.deal?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      o.id?.toLowerCase().includes(searchTerm.toLowerCase())
    );
    return filteredOrders;
  };

  const renderOrdersTable = (ordersList: any[]) => (
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
          {ordersList.map(order => (
            <tr key={order.id} className="border-b hover:bg-muted/50 transition-colors">
              <td className="p-4 font-mono text-xs">{order.id}</td>
              <td className="p-4">
                <div className="font-medium">{order.customer}</div>
                <div className="text-xs text-muted-foreground md:hidden">{order.deal}</div>
              </td>
              <td className="p-4 hidden md:table-cell">{order.deal}</td>
              <td className="p-4">{order.quantity}</td>
              <td className="p-4 font-medium">${order.total?.toFixed(2)}</td>
              <td className="p-4">
                <Badge className={`${getStatusColor(order.status)} flex items-center gap-1 w-fit`}>
                  {getStatusIcon(order.status)}
                  {order.status}
                </Badge>
              </td>
            </tr>
          ))}
          {ordersList.length === 0 && (
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
          <CardContent><div className="text-2xl font-bold">{orders?.length || 0}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Pending</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold text-yellow-600">{orders?.filter((o: any) => o.status === 'pending').length}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Completed</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold text-green-600">{orders?.filter((o: any) => o.status === 'completed').length}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Revenue</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold">${orders?.filter((o: any) => o.status === 'completed').reduce((s: number, o: any) => s + o.total, 0).toFixed(2)}</div></CardContent>
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
