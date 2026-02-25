import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart, TrendingUp, DollarSign, ShoppingBag, Users, Store } from 'lucide-react';
import { users, vendors, deals, purchases } from '@/data/mockData';

const AdminReports = () => {
  const totalRevenue = purchases.reduce((sum, p) => sum + p.totalPrice, 0);
  const totalSales = purchases.length;
  const totalUsers = users.filter(u => u.role === 'user').length;

  const stats = [
    { label: 'Total Revenue', value: `$${totalRevenue.toFixed(2)}`, icon: DollarSign, change: '+18%' },
    { label: 'Total Sales', value: totalSales.toString(), icon: ShoppingBag, change: '+12%' },
    { label: 'Total Users', value: totalUsers.toString(), icon: Users, change: '+25%' },
    { label: 'Total Vendors', value: vendors.length.toString(), icon: Store, change: '+8%' },
    { label: 'Active Deals', value: deals.filter(d => d.status === 'active').length.toString(), icon: BarChart, change: '+5%' },
    { label: 'Conversion Rate', value: '3.8%', icon: TrendingUp, change: '+0.4%' },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Reports & Analytics</h1>
        <p className="text-muted-foreground">Platform-wide performance reports</p>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {stats.map((stat) => (
          <Card key={stat.label}>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">{stat.label}</CardTitle>
              <stat.icon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stat.value}</div>
              <p className="text-xs text-green-600">{stat.change} from last month</p>
            </CardContent>
          </Card>
        ))}
      </div>

      <div className="grid gap-4 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Revenue Over Time</CardTitle>
            <CardDescription>Monthly platform revenue</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="h-[250px] flex items-center justify-center text-muted-foreground">
              <BarChart className="h-10 w-10" />
              <span className="ml-2">Revenue chart would appear here</span>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>User Growth</CardTitle>
            <CardDescription>New user registrations over time</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="h-[250px] flex items-center justify-center text-muted-foreground">
              <TrendingUp className="h-10 w-10" />
              <span className="ml-2">Growth chart would appear here</span>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default AdminReports;
