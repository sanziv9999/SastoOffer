import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Users, UserPlus, UserMinus, Activity, ArrowUpRight, ArrowDownRight, ChevronRight } from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';
import { users } from '@/data/mockData';
import { Button } from '@/components/ui/button';
import Link from '@/components/Link';

const AdminUserAnalytics = () => {
    // Analytics specific metrics
    const metrics = [
        { label: "Active Users", value: "8,450", icon: Users, trend: "+5.1%", positive: true, desc: "Users active in last 30 days" },
        { label: "New Signups", value: "1,240", icon: UserPlus, trend: "+12.3%", positive: true, desc: "Registrations this month" },
        { label: "Churn Rate", value: "2.4%", icon: UserMinus, trend: "-0.5%", positive: true, desc: "Inactive after 90 days" },
        { label: "Engagement", value: "45m", icon: Activity, trend: "+4.1%", positive: true, desc: "Avg. session duration" },
    ];

    const weeklyGrowth = [
        { week: 'W1', signups: 240, active: 7800 },
        { week: 'W2', signups: 310, active: 8000 },
        { week: 'W3', signups: 280, active: 8150 },
        { week: 'W4', signups: 410, active: 8450 },
    ];

    const maxSignups = Math.max(...weeklyGrowth.map(d => d.signups));

    const recentSignups = users.slice(0, 5); // Example grab

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-2xl font-bold tracking-tight">User Analytics</h1>
                <p className="text-muted-foreground">Detailed insights into user acquisition, retention, and engagement.</p>
            </div>

            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                {metrics.map((stat, i) => (
                    <Card key={i}>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">{stat.label}</CardTitle>
                            <stat.icon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stat.value}</div>
                            <div className="flex items-center justify-between mt-1">
                                <p className={`text-xs flex items-center ${stat.positive ? 'text-green-600' : 'text-red-600'}`}>
                                    {stat.positive ? <ArrowUpRight className="h-3 w-3 mr-1" /> : <ArrowDownRight className="h-3 w-3 mr-1" />}
                                    {stat.trend}
                                </p>
                                <p className="text-xs text-muted-foreground">{stat.desc}</p>
                            </div>
                        </CardContent>
                    </Card>
                ))}
            </div>

            <div className="grid gap-4 md:grid-cols-3">
                <Card className="md:col-span-2">
                    <CardHeader>
                        <CardTitle>User Acquisition Trends</CardTitle>
                        <CardDescription>New signups per week over the last month</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="h-[300px] flex items-end justify-between items-center gap-4 pt-6 px-4">
                            {weeklyGrowth.map((data, idx) => (
                                <div key={idx} className="flex flex-col items-center gap-3 flex-1 group">
                                    <div className="flex w-full justify-center h-48 items-end relative">
                                        <div
                                            className="w-16 bg-blue-500 rounded-t-md transition-all duration-300 group-hover:bg-blue-600"
                                            style={{ height: `${(data.signups / maxSignups) * 100}%` }}
                                        />
                                        {/* Tooltip */}
                                        <div className="opacity-0 group-hover:opacity-100 absolute -top-10 bg-black text-white text-xs p-2 rounded pointer-events-none transition-opacity z-10 whitespace-nowrap">
                                            {data.signups} New Users
                                        </div>
                                    </div>
                                    <div className="text-center">
                                        <span className="text-sm font-medium block">{data.week}</span>
                                        <span className="text-xs text-muted-foreground block">{data.active} total active</span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>Recent Signups</CardTitle>
                            <CardDescription>Newest users on the platform</CardDescription>
                        </div>
                        <Button variant="ghost" size="icon" asChild>
                            <Link href="/admin/users"><ChevronRight className="h-4 w-4" /></Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {recentSignups.map((user) => (
                                <div key={user.id} className="flex items-center gap-3">
                                    {user.avatar ? (
                                        <img src={user.avatar} alt={user.name} className="h-9 w-9 rounded-full object-cover" />
                                    ) : (
                                        <div className="h-9 w-9 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm font-medium">
                                            {user.name?.charAt(0)}
                                        </div>
                                    )}
                                    <div className="flex-1 min-w-0">
                                        <p className="text-sm font-medium truncate">{user.name}</p>
                                        <p className="text-xs text-muted-foreground truncate">{user.email}</p>
                                    </div>
                                    <div className="text-xs text-muted-foreground whitespace-nowrap">
                                        Today
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
};

AdminUserAnalytics.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default AdminUserAnalytics;
