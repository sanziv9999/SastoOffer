import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { DollarSign, TrendingUp, CreditCard, ArrowUpRight, ArrowDownRight } from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';

const AdminRevenueReports = () => {
    // Mock statistical data
    const summaryStats = [
        { label: "Total Revenue", value: "$34,250.00", icon: DollarSign, trend: "+12.5%", positive: true },
        { label: "Platform Comission", value: "$3,425.00", icon: CreditCard, trend: "+15.2%", positive: true },
        { label: "Average Order Value", value: "$45.20", icon: TrendingUp, trend: "+2.1%", positive: true },
        { label: "Refunds", value: "$1,240.00", icon: ArrowDownRight, trend: "-5.4%", positive: true },
    ];

    const monthlyRevenue = [
        { month: 'Jan', revenue: 4500, comission: 450 },
        { month: 'Feb', revenue: 5200, comission: 520 },
        { month: 'Mar', revenue: 4800, comission: 480 },
        { month: 'Apr', revenue: 6100, comission: 610 },
        { month: 'May', revenue: 5900, comission: 590 },
        { month: 'Jun', revenue: 7750, comission: 775 },
    ];

    const maxRevenue = Math.max(...monthlyRevenue.map(d => d.revenue));

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-2xl font-bold tracking-tight">Revenue Reports</h1>
                <p className="text-muted-foreground">Detailed breakdown of platform revenue, commissions, and financial metrics.</p>
            </div>

            {/* Top Summaries */}
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                {summaryStats.map((stat, i) => (
                    <Card key={i}>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">{stat.label}</CardTitle>
                            <stat.icon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stat.value}</div>
                            <p className={`text-xs flex items-center ${stat.positive ? 'text-green-600' : 'text-red-600'}`}>
                                {stat.positive ? <ArrowUpRight className="h-3 w-3 mr-1" /> : <ArrowDownRight className="h-3 w-3 mr-1" />}
                                {stat.trend} from last month
                            </p>
                        </CardContent>
                    </Card>
                ))}
            </div>

            <div className="grid gap-4 md:grid-cols-3">
                <Card className="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Revenue Output (Last 6 Months)</CardTitle>
                        <CardDescription>Total transaction volume and platform comissions</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {/* High fidelity CSS chart */}
                        <div className="h-[300px] flex items-end justify-between items-center gap-2 pt-6">
                            {monthlyRevenue.map((data, idx) => (
                                <div key={idx} className="flex flex-col items-center gap-2 flex-1 group">
                                    <div className="flex w-full justify-center gap-1 h-48 items-end relative">
                                        {/* Revenue Bar */}
                                        <div
                                            className="w-1/3 bg-primary/70 rounded-t-sm transition-all duration-300 group-hover:bg-primary"
                                            style={{ height: `${(data.revenue / maxRevenue) * 100}%` }}
                                        />
                                        {/* Comission Bar */}
                                        <div
                                            className="w-1/3 bg-blue-500/70 rounded-t-sm transition-all duration-300 group-hover:bg-blue-600"
                                            style={{ height: `${(data.comission / maxRevenue) * 100}%` }}
                                        />

                                        {/* Tooltip */}
                                        <div className="opacity-0 group-hover:opacity-100 absolute -top-14 bg-black text-white text-xs p-2 rounded pointer-events-none transition-opacity z-10 whitespace-nowrap">
                                            <div>Rev: ${data.revenue}</div>
                                            <div>Com: ${data.comission}</div>
                                        </div>
                                    </div>
                                    <span className="text-xs text-muted-foreground font-medium">{data.month}</span>
                                </div>
                            ))}
                        </div>
                        <div className="flex gap-4 justify-center mt-6">
                            <div className="flex items-center gap-2 text-sm">
                                <div className="w-3 h-3 bg-primary/70 rounded-sm"></div>
                                Total Revenue
                            </div>
                            <div className="flex items-center gap-2 text-sm">
                                <div className="w-3 h-3 bg-blue-500/70 rounded-sm"></div>
                                Platform Comission
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Top Revenue Streams</CardTitle>
                        <CardDescription>By deal category</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-6">
                            {[
                                { category: 'Food & Dining', amount: '$15,400', percent: 45 },
                                { category: 'Health & Beauty', amount: '$8,200', percent: 24 },
                                { category: 'Activities', amount: '$6,500', percent: 19 },
                                { category: 'Travel', amount: '$4,150', percent: 12 },
                            ].map((item, i) => (
                                <div key={i} className="space-y-2">
                                    <div className="flex items-center justify-between text-sm">
                                        <span className="font-medium">{item.category}</span>
                                        <span>{item.amount}</span>
                                    </div>
                                    <div className="w-full bg-secondary h-2 rounded-full overflow-hidden">
                                        <div
                                            className="bg-primary h-full rounded-full"
                                            style={{ width: `${item.percent}%` }}
                                        />
                                    </div>
                                    <div className="text-right text-xs text-muted-foreground">{item.percent}%</div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
};

AdminRevenueReports.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default AdminRevenueReports;
