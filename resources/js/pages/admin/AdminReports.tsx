
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart, TrendingUp, DollarSign, ShoppingBag, Users, Store } from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';

interface AdminReportsProps {
  statsData: {
    totalRevenue: number;
    totalSales: number;
    totalUsers: number;
    totalVendors: number;
    activeDeals: number;
    conversionRate: string;
    revenueChange: string;
    salesChange: string;
    usersChange: string;
    vendorsChange: string;
    dealsChange: string;
    conversionChange: string;
  };
  monthlyRevenue: Array<{ month: string; amount: number }>;
  userGrowth: Array<{ month: string; users: number }>;
}

const AdminReports = ({ statsData, monthlyRevenue = [], userGrowth = [] }: AdminReportsProps) => {
  const maxRev = monthlyRevenue.length > 0 ? Math.max(...monthlyRevenue.map((d) => d.amount), 1) : 1;
  const maxUsers = userGrowth.length > 0 ? Math.max(...userGrowth.map((d) => d.users), 1) : 1;

  const stats = [
    { label: 'Total Revenue', value: `Rs. ${statsData?.totalRevenue?.toFixed(2) || '0.00'}`, icon: DollarSign, change: statsData?.revenueChange || '0%' },
    { label: 'Total Sales', value: statsData?.totalSales?.toString() || '0', icon: ShoppingBag, change: statsData?.salesChange || '0%' },
    { label: 'Total Users', value: statsData?.totalUsers?.toString() || '0', icon: Users, change: statsData?.usersChange || '0%' },
    { label: 'Total Vendors', value: statsData?.totalVendors?.toString() || '0', icon: Store, change: statsData?.vendorsChange || '0%' },
    { label: 'Active Deals', value: statsData?.activeDeals?.toString() || '0', icon: BarChart, change: statsData?.dealsChange || '0%' },
    { label: 'Conversion Rate', value: statsData?.conversionRate || '0.0%', icon: TrendingUp, change: statsData?.conversionChange || '0%' },
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
              <p className={`text-xs ${stat.change.startsWith('+') ? 'text-green-600' : 'text-red-600'}`}>
                {stat.change} from last month
              </p>
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
            {monthlyRevenue.length > 0 ? (
              <div className="h-[250px] flex items-end gap-2 pt-8">
                {monthlyRevenue.map((data, idx) => (
                  <div key={idx} className="flex flex-col items-center flex-1 gap-2 group relative">
                    <div className="w-full h-40 flex items-end justify-center relative">
                      <div
                        className="w-full max-w-[48px] bg-primary/80 rounded-t-sm transition-all duration-300 group-hover:bg-primary"
                        style={{ height: `${Math.max((data.amount / maxRev) * 100, 5)}%` }}
                      />
                      <div className="opacity-0 group-hover:opacity-100 absolute -top-10 bg-black text-white text-xs p-1.5 rounded pointer-events-none transition-opacity whitespace-nowrap z-10">
                        Rs. {data.amount.toLocaleString()}
                      </div>
                    </div>
                    <span className="text-xs text-muted-foreground font-medium">{data.month}</span>
                  </div>
                ))}
              </div>
            ) : (
              <div className="h-[250px] flex items-center justify-center text-sm text-muted-foreground">
                No revenue data available.
              </div>
            )}
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>User Growth</CardTitle>
            <CardDescription>New user registrations over time</CardDescription>
          </CardHeader>
          <CardContent>
            {userGrowth.length > 0 ? (
              <div className="h-[250px] flex items-end gap-2 pt-8">
                {userGrowth.map((data, idx) => (
                  <div key={idx} className="flex flex-col items-center flex-1 gap-2 group relative">
                    <div className="w-full h-40 flex items-end justify-center relative border-b border-primary/20">
                      <div
                        className="w-full bg-blue-500/80 rounded-t-sm transition-all duration-300 group-hover:bg-blue-600"
                        style={{ height: `${Math.max((data.users / maxUsers) * 100, 5)}%` }}
                      />
                      <div className="opacity-0 group-hover:opacity-100 absolute -top-10 bg-black text-white text-xs p-1.5 rounded pointer-events-none transition-opacity whitespace-nowrap z-10">
                        {data.users} Users
                      </div>
                    </div>
                    <span className="text-xs text-muted-foreground font-medium">{data.month}</span>
                  </div>
                ))}
              </div>
            ) : (
              <div className="h-[250px] flex items-center justify-center text-sm text-muted-foreground">
                No user growth data available.
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

AdminReports.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default AdminReports;
