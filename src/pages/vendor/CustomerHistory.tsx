import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Calendar, Package, DollarSign, ArrowRight } from 'lucide-react';

const mockHistory = [
  { id: '1', customer: 'Sarah Johnson', deal: 'Gourmet Pizza Deal', quantity: 2, total: 45.98, date: '2024-01-15', status: 'completed' },
  { id: '2', customer: 'Mike Chen', deal: 'Spa Relaxation Package', quantity: 1, total: 89.00, date: '2024-01-14', status: 'completed' },
  { id: '3', customer: 'Emily Davis', deal: 'Weekend Brunch Special', quantity: 3, total: 67.50, date: '2024-01-13', status: 'completed' },
  { id: '4', customer: 'Lisa Anderson', deal: 'Gourmet Pizza Deal', quantity: 1, total: 22.99, date: '2024-01-12', status: 'refunded' },
  { id: '5', customer: 'James Wilson', deal: 'Coffee Lover Bundle', quantity: 4, total: 35.96, date: '2024-01-11', status: 'completed' },
  { id: '6', customer: 'Sarah Johnson', deal: 'Fitness Class Pack', quantity: 1, total: 120.00, date: '2024-01-10', status: 'completed' },
  { id: '7', customer: 'Emily Davis', deal: 'Weekend Brunch Special', quantity: 2, total: 45.00, date: '2024-01-09', status: 'completed' },
  { id: '8', customer: 'Mike Chen', deal: 'Gourmet Pizza Deal', quantity: 2, total: 45.98, date: '2024-01-08', status: 'pending' },
];

const CustomerHistory = () => {
  const totalRevenue = mockHistory.filter(h => h.status === 'completed').reduce((s, h) => s + h.total, 0);
  const totalOrders = mockHistory.length;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Customer History</h1>
        <p className="text-muted-foreground">Track customer purchase history and activity</p>
      </div>

      <div className="grid gap-4 grid-cols-2 md:grid-cols-4">
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Total Orders</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold">{totalOrders}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Revenue</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold">${totalRevenue.toFixed(2)}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Completed</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold text-green-600">{mockHistory.filter(h => h.status === 'completed').length}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Refunded</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold text-red-500">{mockHistory.filter(h => h.status === 'refunded').length}</div></CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Purchase History</CardTitle>
          <CardDescription>Recent customer purchases and transactions</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            {mockHistory.map(item => (
              <div key={item.id} className="flex items-center justify-between p-3 rounded-lg border hover:bg-muted/50 transition-colors">
                <div className="flex-1 min-w-0">
                  <div className="font-medium truncate">{item.customer}</div>
                  <div className="text-sm text-muted-foreground flex items-center gap-1">
                    <Package className="h-3 w-3 flex-shrink-0" />
                    <span className="truncate">{item.deal} × {item.quantity}</span>
                  </div>
                </div>
                <div className="flex items-center gap-3 flex-shrink-0 ml-3">
                  <div className="text-right">
                    <div className="font-medium">${item.total.toFixed(2)}</div>
                    <div className="text-xs text-muted-foreground flex items-center gap-1">
                      <Calendar className="h-3 w-3" />{item.date}
                    </div>
                  </div>
                  <Badge variant={item.status === 'completed' ? 'default' : item.status === 'refunded' ? 'destructive' : 'secondary'}
                    className={item.status === 'completed' ? 'bg-green-500' : ''}>
                    {item.status}
                  </Badge>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default CustomerHistory;
