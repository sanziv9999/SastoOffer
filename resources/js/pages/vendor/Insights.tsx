
import {
    BarChart,
    LineChart,
    TrendingUp,
    TrendingDown,
    Users,
    MousePointer2,
    Clock,
    Calendar,
    Download
} from 'lucide-react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
    CardFooter
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import DashboardLayout from '@/layouts/DashboardLayout';

const Insights = () => {
    return (
        <div className="space-y-6">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Customer Insights</h1>
                    <p className="text-muted-foreground">Deep dive into customer behavior and deal performance.</p>
                </div>
                <div className="flex gap-2">
                    <Select defaultValue="30d">
                        <SelectTrigger className="w-[180px]">
                            <Calendar className="mr-2 h-4 w-4" />
                            <SelectValue placeholder="Time Range" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="7d">Last 7 Days</SelectItem>
                            <SelectItem value="30d">Last 30 Days</SelectItem>
                            <SelectItem value="90d">Last 3 Months</SelectItem>
                            <SelectItem value="1y">Last Year</SelectItem>
                        </SelectContent>
                    </Select>
                    <Button variant="outline" size="icon">
                        <Download className="h-4 w-4" />
                    </Button>
                </div>
            </div>

            {/* Top Level Metrics */}
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Repeat Customer Rate</CardTitle>
                        <Users className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">24.5%</div>
                        <div className="flex items-center text-xs text-green-600 font-medium">
                            <TrendingUp className="h-3 w-3 mr-1" />
                            +2.1% from last month
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Avg. Redemption Time</CardTitle>
                        <Clock className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">3.2 Days</div>
                        <p className="text-xs text-muted-foreground">After purchase</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Deal Click-Through</CardTitle>
                        <MousePointer2 className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">12.8%</div>
                        <div className="flex items-center text-xs text-destructive font-medium">
                            <TrendingDown className="h-3 w-3 mr-1" />
                            -0.5% from last month
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Peak Redemption Day</CardTitle>
                        <Calendar className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">Friday</div>
                        <p className="text-xs text-muted-foreground">6 PM - 9 PM</p>
                    </CardContent>
                </Card>
            </div>

            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
                {/* Main Chart Card */}
                <Card className="lg:col-span-4">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle>Redemption Trends</CardTitle>
                                <CardDescription>Daily voucher redemptions for the current period.</CardDescription>
                            </div>
                            <Tabs defaultValue="vouchers">
                                <TabsList>
                                    <TabsTrigger value="vouchers">Vouchers</TabsTrigger>
                                    <TabsTrigger value="revenue">Revenue</TabsTrigger>
                                </TabsList>
                            </Tabs>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="h-[300px] w-full flex flex-col items-center justify-center border-2 border-dashed rounded-lg bg-muted/20">
                            <LineChart className="h-12 w-12 text-muted-foreground opacity-20 mb-2" />
                            <p className="text-sm text-muted-foreground font-medium">Redemption Velocity Visualization</p>
                            <div className="flex gap-4 mt-8 opacity-40">
                                <div className="h-24 w-4 bg-primary rounded-t-sm" />
                                <div className="h-32 w-4 bg-primary rounded-t-sm" />
                                <div className="h-16 w-4 bg-primary rounded-t-sm" />
                                <div className="h-40 w-4 bg-primary rounded-t-sm" />
                                <div className="h-28 w-4 bg-primary rounded-t-sm" />
                                <div className="h-48 w-4 bg-primary rounded-t-sm" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Customer Breakdown */}
                <Card className="lg:col-span-3">
                    <CardHeader>
                        <CardTitle>Customer Type</CardTitle>
                        <CardDescription>New vs. returning shoppers.</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-8">
                        <div className="flex items-center justify-center pt-4">
                            <div className="relative h-48 w-48 rounded-full border-[16px] border-primary flex items-center justify-center">
                                <div className="absolute inset-[-16px] rounded-full border-[16px] border-muted border-t-transparent border-r-transparent rotate-45" />
                                <div className="text-center">
                                    <span className="text-3xl font-bold">75%</span>
                                    <p className="text-[10px] text-muted-foreground font-semibold">NEW CUSTOMERS</p>
                                </div>
                            </div>
                        </div>
                        <div className="space-y-3">
                            <div className="flex items-center justify-between text-sm">
                                <div className="flex items-center gap-2">
                                    <div className="h-3 w-3 rounded-full bg-primary" />
                                    <span>New Customers</span>
                                </div>
                                <span className="font-bold">755</span>
                            </div>
                            <div className="flex items-center justify-between text-sm">
                                <div className="flex items-center gap-2">
                                    <div className="h-3 w-3 rounded-full bg-muted" />
                                    <span>Returning</span>
                                </div>
                                <span className="font-bold">245</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Popular Times</CardTitle>
                        <CardDescription>When are customers most likely to visit?</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="h-[200px] flex items-center justify-center border-2 border-dashed rounded-lg">
                            <BarChart className="h-10 w-10 text-muted-foreground opacity-10" />
                            <span className="text-sm text-muted-foreground ml-2">Heatmap Placeholder</span>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>Top Performing Deals</CardTitle>
                        <CardDescription>Deals with the highest conversion rate.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {[1, 2, 3].map((i) => (
                                <div key={i} className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="h-8 w-8 rounded bg-muted flex items-center justify-center text-[10px] font-bold">{i}</div>
                                        <div className="text-sm font-medium line-clamp-1">Luxury 5-Course Dinner {i}</div>
                                    </div>
                                    <Badge variant="secondary" className="text-[10px] font-mono">18.2% CVR</Badge>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                    <CardFooter className="pt-0">
                        <Button variant="link" className="text-xs p-0 h-auto">View Full Performance Report</Button>
                    </CardFooter>
                </Card>
            </div>
        </div>
    );
};

Insights.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Insights;
