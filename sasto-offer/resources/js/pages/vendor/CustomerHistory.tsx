
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Calendar, Package, DollarSign, ArrowRight } from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';

interface CustomerHistoryProps {
  history: any[];
}

const CustomerHistory = ({ history }: CustomerHistoryProps) => {
  const totalRevenue = (history || []).filter((h: any) => h.status === 'completed').reduce((s: number, h: any) => s + (h.total || 0), 0);
  const totalOrders = history?.length || 0;

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
          <CardContent><div className="text-2xl font-bold text-green-600">{history?.filter((h: any) => h.status === 'completed').length}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Refunded</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold text-red-500">{history?.filter((h: any) => h.status === 'refunded').length}</div></CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Purchase History</CardTitle>
          <CardDescription>Recent customer purchases and transactions</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            {history?.length > 0 ? history.map((item: any) => (
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
                    <div className="font-medium">${item.total?.toFixed(2)}</div>
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
